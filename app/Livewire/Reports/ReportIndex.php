<?php

namespace App\Livewire\Reports;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportIndex extends Component
{
    public string $activeTab = 'documents'; // documents, employees, vehicles
    public string $docTypeFilter = 'all'; // all, vehicle, employee
    public string $expiryStatusFilter = 'all'; // all, expired, critical_7, warning_30, valid

    public function exportCsv(): StreamedResponse
    {
        return match ($this->activeTab) {
            'employees' => $this->exportEmployeesCsv(),
            'vehicles'  => $this->exportVehiclesCsv(),
            default     => $this->exportDocumentsCsv(),
        };
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

        return view('livewire.reports.report-index', compact('stats'))
            ->layout('layouts.app', ['title' => 'Laporan & Export Data']);
    }
}
