<?php

namespace App\Livewire\Reports;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\VehicleAssignment;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportIndex extends Component
{
    use WithPagination;

    public string $activeTab = 'trips'; // trips, documents, employees, vehicles
    public string $docTypeFilter = 'all'; // all, vehicle, employee
    public string $expiryStatusFilter = 'all'; // all, expired, critical_7, warning_30, valid

    // Filter Perjalanan Sopir
    public string $tripStartDate = '';
    public string $tripEndDate = '';
    public string $tripDriverFilter = 'all';
    public string $tripVehicleFilter = 'all';
    public string $tripStatusFilter = 'all';

    // Route Map Modal State
    public bool $showRouteModal = false;
    public array $selectedRoute = [];

    public function mount(): void
    {
        $this->tripStartDate = now()->subDays(30)->format('Y-m-d');
        $this->tripEndDate   = now()->format('Y-m-d');

        if (request()->has('tab')) {
            $this->activeTab = (string) request('tab');
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedTripStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedTripEndDate(): void
    {
        $this->resetPage();
    }

    public function updatedTripDriverFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTripVehicleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTripStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetTripFilters(): void
    {
        $this->tripStartDate     = now()->subDays(30)->format('Y-m-d');
        $this->tripEndDate       = now()->format('Y-m-d');
        $this->tripDriverFilter  = 'all';
        $this->tripVehicleFilter = 'all';
        $this->tripStatusFilter  = 'all';
        $this->resetPage();
    }

    public function exportCsv(): StreamedResponse
    {
        return match ($this->activeTab) {
            'trips'     => $this->exportTripsCsv(),
            'employees' => $this->exportEmployeesCsv(),
            'vehicles'  => $this->exportVehiclesCsv(),
            default     => $this->exportDocumentsCsv(),
        };
    }

    protected function getTripsQuery()
    {
        $query = VehicleAssignment::with(['vehicle', 'driver', 'locations'])
            ->withCount('locations');

        if (!empty($this->tripStartDate)) {
            $query->whereDate('departure_time', '>=', $this->tripStartDate);
        }
        if (!empty($this->tripEndDate)) {
            $query->whereDate('departure_time', '<=', $this->tripEndDate);
        }
        if ($this->tripDriverFilter !== 'all') {
            $query->where('driver_id', (int) $this->tripDriverFilter);
        }
        if ($this->tripVehicleFilter !== 'all') {
            $query->where('vehicle_id', (int) $this->tripVehicleFilter);
        }
        if ($this->tripStatusFilter !== 'all') {
            $query->where('status', $this->tripStatusFilter);
        }

        return $query->latest('departure_time');
    }

    public function openRouteModal(int $assignmentId): void
    {
        $assignment = VehicleAssignment::with([
            'vehicle',
            'driver',
            'locations' => fn($q) => $q->orderBy('recorded_at', 'asc')->orderBy('id', 'asc'),
        ])->findOrFail($assignmentId);

        $locations = $assignment->locations;
        $warehouse = Warehouse::getPrimary();

        $trail = $locations->map(fn($loc) => [
            'lat'       => (float) $loc->latitude,
            'lng'       => (float) $loc->longitude,
            'speed'     => (float) $loc->speed,
            'heading'   => (float) $loc->heading,
            'time'      => $loc->recorded_at ? $loc->recorded_at->format('H:i:s') : '-',
            'full_time' => $loc->recorded_at ? $loc->recorded_at->format('d/m/Y H:i') : '-',
        ])->values()->toArray();

        $speeds = $locations->pluck('speed')->filter(fn($s) => $s > 0);
        $maxSpeed = $speeds->isNotEmpty() ? round($speeds->max(), 1) : 0;
        $avgSpeed = $speeds->isNotEmpty() ? round($speeds->avg(), 1) : 0;

        $duration = '-';
        if ($assignment->departure_time && $assignment->return_time) {
            $duration = $assignment->departure_time->diffForHumans($assignment->return_time, true);
        } elseif ($assignment->departure_time) {
            $duration = $assignment->departure_time->diffForHumans(now(), true) . ' (Sedang Berjalan)';
        }

        $this->selectedRoute = [
            'id'                    => $assignment->id,
            'license_plate'         => strtoupper($assignment->vehicle?->license_plate ?? '-'),
            'vehicle_name'          => trim(($assignment->vehicle?->brand ?? '') . ' ' . ($assignment->vehicle?->model ?? '')),
            'vehicle_type'          => ucfirst($assignment->vehicle?->vehicle_type ?? 'Mobil Barang'),
            'driver_name'           => $assignment->driver?->name ?? 'Sopir',
            'driver_sim'            => $assignment->driver?->sim_type ? "SIM {$assignment->driver->sim_type}" : '-',
            'driver_phone'          => $assignment->driver?->phone ?? '-',
            'origin'                => $assignment->origin ?: $warehouse->name,
            'destination'           => $assignment->destination ?: 'Rute Pengiriman',
            'departure_time'        => $assignment->departure_time ? $assignment->departure_time->format('d/m/Y H:i') : '-',
            'return_time'           => $assignment->return_time ? $assignment->return_time->format('d/m/Y H:i') : '(Sedang Berjalan)',
            'duration'              => $duration,
            'start_odometer'        => $assignment->start_odometer ? number_format((float) $assignment->start_odometer, 0, ',', '.') : '-',
            'end_odometer'          => $assignment->end_odometer ? number_format((float) $assignment->end_odometer, 0, ',', '.') : '-',
            'distance_km'           => $assignment->distance_traveled !== null ? number_format((float) $assignment->distance_traveled, 0, ',', '.') . ' KM' : '-',
            'status_label'          => $assignment->status_label,
            'status_badge_color'    => $assignment->status_badge_color,
            'condition'             => ucfirst($assignment->vehicle_condition_on_return ?? 'Baik'),
            'notes'                 => $assignment->notes ?: '-',
            'warehouse_lat'         => (float) $warehouse->latitude,
            'warehouse_lng'         => (float) $warehouse->longitude,
            'warehouse_name'        => $warehouse->name,
            'warehouse_address'     => $warehouse->address,
            'destination_lat'       => $assignment->destination_latitude ? (float) $assignment->destination_latitude : null,
            'destination_lng'       => $assignment->destination_longitude ? (float) $assignment->destination_longitude : null,
            'points_count'          => $locations->count(),
            'max_speed'             => $maxSpeed,
            'avg_speed'             => $avgSpeed,
            'trail'                 => $trail,
        ];

        $this->showRouteModal = true;
        $this->dispatch('render-route-map', routeData: $this->selectedRoute);
    }

    public function closeRouteModal(): void
    {
        $this->showRouteModal = false;
        $this->selectedRoute = [];
    }

    protected function exportTripsCsv(): StreamedResponse
    {
        $filename = 'laporan-perjalanan-sopir-' . date('Y-m-d-His') . '.csv';
        $trips = $this->getTripsQuery()->get();

        return response()->streamDownload(function () use ($trips) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, [
                'No',
                'Tanggal Berangkat',
                'Tanggal Kembali',
                'Durasi',
                'Nama Sopir',
                'No SIM Sopir',
                'Nomor Polisi Armada',
                'Merek / Tipe Armada',
                'Titik Asal',
                'Tujuan Pengiriman',
                'Odometer Awal (KM)',
                'Odometer Akhir (KM)',
                'Jarak Tempuh (KM)',
                'Status Perjalanan',
                'Kondisi Unit Saat Kembali',
                'Jumlah Titik Sinyal GPS',
                'Catatan Perjalanan',
            ]);

            foreach ($trips as $i => $t) {
                $duration = '-';
                if ($t->departure_time && $t->return_time) {
                    $duration = $t->departure_time->diffForHumans($t->return_time, true);
                } elseif ($t->departure_time) {
                    $duration = 'Sedang Berjalan';
                }

                fputcsv($handle, [
                    $i + 1,
                    $t->departure_time ? $t->departure_time->format('d/m/Y H:i') : '-',
                    $t->return_time ? $t->return_time->format('d/m/Y H:i') : 'Sedang Berjalan',
                    $duration,
                    $t->driver?->name ?? '–',
                    $t->driver?->sim_type ? "SIM {$t->driver->sim_type}" : '-',
                    strtoupper($t->vehicle?->license_plate ?? '-'),
                    trim(($t->vehicle?->brand ?? '') . ' ' . ($t->vehicle?->model ?? '') . ' (' . ($t->vehicle?->vehicle_type ?? '') . ')'),
                    $t->origin ?? 'Gudang Utama',
                    $t->destination ?? '–',
                    $t->start_odometer ? (float) $t->start_odometer : 0,
                    $t->end_odometer ? (float) $t->end_odometer : 0,
                    $t->distance_traveled !== null ? (float) $t->distance_traveled : 0,
                    $t->status_label,
                    ucfirst($t->vehicle_condition_on_return ?? '-'),
                    $t->locations_count ?? 0,
                    str_replace(["\r", "\n"], ' ', $t->notes ?? '-'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function exportEmployeesCsv(): StreamedResponse
    {
        $filename = 'laporan-karyawan-' . date('Y-m-d-His') . '.csv';
        $employees = Employee::orderBy('name')->get();

        return response()->streamDownload(function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, ['No', 'NIK', 'No Karyawan', 'Nama', 'Tipe', 'Status Kepegawaian', 'Jabatan', 'Departemen', 'Telepon', 'Email', 'No SIM', 'Kadaluarsa SIM', 'Status']);

            foreach ($employees as $i => $e) {
                fputcsv($handle, [
                    $i + 1,
                    $e->nik ?? '-',
                    $e->employee_number,
                    $e->name,
                    ucfirst($e->type),
                    ucfirst($e->employment_status),
                    $e->position ?? '-',
                    $e->department ?? '-',
                    $e->phone ?? '-',
                    $e->email ?? '-',
                    $e->sim_number ?? '-',
                    $e->sim_expiry ? \Carbon\Carbon::parse($e->sim_expiry)->format('d/m/Y') : '-',
                    ucfirst($e->status),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function exportVehiclesCsv(): StreamedResponse
    {
        $filename = 'laporan-kendaraan-' . date('Y-m-d-His') . '.csv';
        $vehicles = Vehicle::with('assignedDriver')->orderBy('license_plate')->get();

        return response()->streamDownload(function () use ($vehicles) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, ['No', 'Kode Kendaraan', 'Nomor Polisi', 'Merek', 'Model', 'Jenis', 'Tahun', 'Warna', 'Bahan Bakar', 'Dedicated Halal', 'Sopir Ditugaskan', 'Status']);

            foreach ($vehicles as $i => $v) {
                fputcsv($handle, [
                    $i + 1,
                    $v->vehicle_code,
                    $v->license_plate,
                    $v->brand,
                    $v->model ?? '-',
                    $v->vehicle_type,
                    $v->year ?? '-',
                    $v->color ?? '-',
                    ucfirst($v->fuel_type),
                    $v->is_halal_dedicated ? 'Ya' : 'Tidak',
                    $v->assignedDriver?->name ?? 'Belum Ditugaskan',
                    ucfirst($v->status),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function exportDocumentsCsv(): StreamedResponse
    {
        $filename = 'laporan-dokumen-' . date('Y-m-d-His') . '.csv';

        $vDocs = VehicleDocument::with('vehicle')->get()->map(fn($d) => [
            'kategori'    => 'Kendaraan',
            'pemilik'     => $d->vehicle?->license_plate . ' (' . $d->vehicle?->brand . ')',
            'tipe'        => $d->document_type,
            'nomor'       => $d->document_number ?? '-',
            'judul'       => $d->title,
            'terbit'      => $d->issued_date ? \Carbon\Carbon::parse($d->issued_date)->format('d/m/Y') : '-',
            'kadaluarsa'  => $d->expiry_date ? \Carbon\Carbon::parse($d->expiry_date)->format('d/m/Y') : '-',
            'sisa_hari'   => $d->expiry_date ? now()->diffInDays($d->expiry_date, false) : null,
            'status'      => $d->status,
        ]);

        $eDocs = EmployeeDocument::with('employee')->get()->map(fn($d) => [
            'kategori'    => 'Karyawan',
            'pemilik'     => $d->employee?->name . ' (' . $d->employee?->employee_number . ')',
            'tipe'        => $d->document_type,
            'nomor'       => $d->document_number ?? '-',
            'judul'       => $d->title,
            'terbit'      => $d->issued_date ? \Carbon\Carbon::parse($d->issued_date)->format('d/m/Y') : '-',
            'kadaluarsa'  => $d->expiry_date ? \Carbon\Carbon::parse($d->expiry_date)->format('d/m/Y') : '-',
            'sisa_hari'   => $d->expiry_date ? now()->diffInDays($d->expiry_date, false) : null,
            'status'      => $d->status,
        ]);

        $docs = $vDocs->concat($eDocs);

        if ($this->docTypeFilter === 'vehicle') {
            $docs = $vDocs;
        } elseif ($this->docTypeFilter === 'employee') {
            $docs = $eDocs;
        }

        if ($this->expiryStatusFilter === 'expired') {
            $docs = $docs->filter(fn($d) => $d['sisa_hari'] !== null && $d['sisa_hari'] < 0);
        } elseif ($this->expiryStatusFilter === 'critical_7') {
            $docs = $docs->filter(fn($d) => $d['sisa_hari'] !== null && $d['sisa_hari'] >= 0 && $d['sisa_hari'] <= 7);
        } elseif ($this->expiryStatusFilter === 'warning_30') {
            $docs = $docs->filter(fn($d) => $d['sisa_hari'] !== null && $d['sisa_hari'] >= 0 && $d['sisa_hari'] <= 30);
        } elseif ($this->expiryStatusFilter === 'valid') {
            $docs = $docs->filter(fn($d) => $d['sisa_hari'] === null || $d['sisa_hari'] > 30);
        }

        return response()->streamDownload(function () use ($docs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['No', 'Kategori', 'Pemilik / Terkait', 'Jenis Dokumen', 'Nomor Dokumen', 'Judul', 'Tgl Terbit', 'Tgl Kadaluarsa', 'Sisa Hari', 'Status']);

            $i = 1;
            foreach ($docs as $d) {
                fputcsv($handle, [
                    $i++,
                    $d['kategori'],
                    $d['pemilik'],
                    $d['tipe'],
                    $d['nomor'],
                    $d['judul'],
                    $d['terbit'],
                    $d['kadaluarsa'],
                    $d['sisa_hari'] !== null ? ($d['sisa_hari'] < 0 ? 'Expired (' . abs($d['sisa_hari']) . ' hari)' : $d['sisa_hari'] . ' hari') : '-',
                    ucfirst($d['status']),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        $stats = [
            'total_employees' => Employee::count(),
            'total_vehicles'  => Vehicle::count(),
            'total_docs'      => VehicleDocument::count() + EmployeeDocument::count(),
            'critical_docs'   => VehicleDocument::expiringSoon(7)->count() + EmployeeDocument::expiringSoon(7)->count(),
        ];

        $trips = null;
        $tripStats = [];
        $drivers = collect();
        $vehicles = collect();

        if ($this->activeTab === 'trips') {
            $tripsQuery = $this->getTripsQuery();

            // Hitung statistik trip sesuai filter
            $allFilteredTrips = (clone $tripsQuery)->get();
            $totalDistance = $allFilteredTrips->sum(fn($t) => (float) ($t->distance_traveled ?? 0));
            $totalGpsPoints = $allFilteredTrips->sum('locations_count');
            $uniqueDrivers = $allFilteredTrips->pluck('driver_id')->filter()->unique()->count();

            $tripStats = [
                'total_trips'    => $allFilteredTrips->count(),
                'total_distance' => $totalDistance,
                'total_drivers'  => $uniqueDrivers,
                'total_gps'      => $totalGpsPoints,
            ];

            $trips = $tripsQuery->paginate(15);
            $drivers = Employee::active()->orderBy('name')->get();
            $vehicles = Vehicle::orderBy('license_plate')->get();
        }

        return view('livewire.reports.report-index', compact('stats', 'trips', 'tripStats', 'drivers', 'vehicles'))
            ->layout('layouts.app', ['title' => 'Laporan & Export Data']);
    }
}
