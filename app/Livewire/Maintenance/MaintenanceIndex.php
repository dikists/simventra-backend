<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceLog;
use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MaintenanceIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $activeTab = 'logs'; // logs, schedules
    public string $search = '';
    public string $filterVehicle = '';
    public string $filterType = '';

    // Modal Form Log Perbaikan
    public bool $showLogModal = false;
    public ?int $vehicle_id = null;
    public ?int $schedule_id = null;
    public string $maintenance_date = '';
    public string $maintenance_type = 'servis_berkala';
    public string $description = '';
    public string $workshop = '';
    public string $mechanic = '';
    public float $cost = 0;
    public ?float $odometer_km = null;
    public string $status = 'completed';
    public string $parts_replaced = '';
    public $receipt_photo;

    // Modal Form Schedule Preventive
    public bool $showScheduleModal = false;
    public ?int $sched_vehicle_id = null;
    public string $sched_maintenance_type = 'oli_mesin';
    public string $sched_trigger_type = 'km';
    public ?float $sched_trigger_km = 5000;
    public ?int $sched_trigger_days = 90;
    public ?float $sched_last_km = null;
    public string $sched_last_date = '';
    public string $sched_notes = '';

    // Modal Detail
    public bool $showDetailModal = false;
    public ?MaintenanceLog $selectedLog = null;

    protected $queryString = [
        'activeTab'     => ['except' => 'logs'],
        'search'        => ['except' => ''],
        'filterVehicle' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->maintenance_date = now()->toDateString();
        $this->sched_last_date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openLogModal(?int $scheduleId = null): void
    {
        $this->reset([
            'vehicle_id', 'schedule_id', 'description', 'workshop', 'mechanic',
            'cost', 'odometer_km', 'parts_replaced', 'receipt_photo'
        ]);
        $this->maintenance_date = now()->toDateString();
        $this->maintenance_type = 'servis_berkala';
        $this->status = 'completed';

        if ($scheduleId) {
            $sched = MaintenanceSchedule::with('vehicle')->find($scheduleId);
            if ($sched) {
                $this->schedule_id = $sched->id;
                $this->vehicle_id = $sched->vehicle_id;
                $this->maintenance_type = $sched->maintenance_type;
                $this->odometer_km = $sched->vehicle?->current_odometer_km;
            }
        }

        $this->showLogModal = true;
    }

    public function saveLog(): void
    {
        $this->validate([
            'vehicle_id'       => 'required|exists:vehicles,id',
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|string|max:100',
            'cost'             => 'required|numeric|min:0',
        ]);

        $receiptPath = null;
        if ($this->receipt_photo) {
            $receiptPath = $this->receipt_photo->store('receipts', config('filesystems.default_public_disk', 'public'));
        }

        $log = MaintenanceLog::create([
            'vehicle_id'       => $this->vehicle_id,
            'schedule_id'      => $this->schedule_id ?: null,
            'maintenance_date' => $this->maintenance_date,
            'maintenance_type' => $this->maintenance_type,
            'description'      => $this->description ?: null,
            'workshop'         => $this->workshop ?: null,
            'mechanic'         => $this->mechanic ?: null,
            'cost'             => $this->cost,
            'odometer_km'      => $this->odometer_km,
            'status'           => $this->status,
            'parts_replaced'   => $this->parts_replaced ?: null,
            'receipt_photo'    => $receiptPath,
            'reported_by'      => auth()->id(),
        ]);

        // Update odometer kendaraan bila lebih tinggi
        $vehicle = Vehicle::find($this->vehicle_id);
        if ($vehicle && $this->odometer_km && $this->odometer_km > $vehicle->current_odometer_km) {
            $vehicle->update(['current_odometer_km' => $this->odometer_km]);
        }

        // Jika terhubung dengan schedule, update last_done dan hitung next_due
        if ($this->schedule_id) {
            $sched = MaintenanceSchedule::find($this->schedule_id);
            if ($sched) {
                $nextKm = null;
                $nextDate = null;

                if ($sched->trigger_km && $this->odometer_km) {
                    $nextKm = $this->odometer_km + $sched->trigger_km;
                }
                if ($sched->trigger_days) {
                    $nextDate = now()->addDays($sched->trigger_days)->toDateString();
                }

                $sched->update([
                    'last_done_km'   => $this->odometer_km ?: $sched->last_done_km,
                    'last_done_date' => $this->maintenance_date,
                    'next_due_km'    => $nextKm ?: $sched->next_due_km,
                    'next_due_date'  => $nextDate ?: $sched->next_due_date,
                ]);
            }
        }

        session()->flash('success', 'Catatan maintenance/perbaikan berhasil disimpan.');
        $this->showLogModal = false;
    }

    public function openScheduleModal(): void
    {
        $this->reset(['sched_vehicle_id', 'sched_notes']);
        $this->sched_maintenance_type = 'oli_mesin';
        $this->sched_trigger_type = 'km';
        $this->sched_trigger_km = 5000;
        $this->sched_trigger_days = 90;
        $this->sched_last_date = now()->toDateString();
        $this->showScheduleModal = true;
    }

    public function saveSchedule(): void
    {
        $this->validate([
            'sched_vehicle_id'       => 'required|exists:vehicles,id',
            'sched_maintenance_type' => 'required|string',
            'sched_trigger_type'     => 'required|in:km,waktu,manual',
        ]);

        $nextKm = null;
        $nextDate = null;

        if ($this->sched_trigger_km && $this->sched_last_km) {
            $nextKm = $this->sched_last_km + $this->sched_trigger_km;
        }
        if ($this->sched_trigger_days && $this->sched_last_date) {
            $nextDate = \Carbon\Carbon::parse($this->sched_last_date)->addDays($this->sched_trigger_days)->toDateString();
        }

        MaintenanceSchedule::create([
            'vehicle_id'       => $this->sched_vehicle_id,
            'maintenance_type' => $this->sched_maintenance_type,
            'trigger_type'     => $this->sched_trigger_type,
            'trigger_km'       => $this->sched_trigger_km,
            'trigger_days'     => $this->sched_trigger_days,
            'last_done_km'     => $this->sched_last_km,
            'last_done_date'   => $this->sched_last_date ?: null,
            'next_due_km'      => $nextKm,
            'next_due_date'    => $nextDate,
            'is_active'        => true,
            'notes'            => $this->sched_notes ?: null,
        ]);

        session()->flash('success', 'Jadwal preventive maintenance berhasil dibuat.');
        $this->showScheduleModal = false;
    }

    public function openDetailLog(int $id): void
    {
        $this->selectedLog = MaintenanceLog::with(['vehicle', 'reporter', 'schedule'])->find($id);
        $this->showDetailModal = true;
    }

    public function deleteLog(int $id): void
    {
        $log = MaintenanceLog::findOrFail($id);
        if ($log->receipt_photo) {
            Storage::disk(config('filesystems.default_public_disk', 'public'))->delete($log->receipt_photo);
        }
        $log->delete();
        session()->flash('success', 'Riwayat servis berhasil dihapus.');
    }

    public function deleteSchedule(int $id): void
    {
        MaintenanceSchedule::where('id', $id)->delete();
        session()->flash('success', 'Jadwal preventive maintenance berhasil dihapus.');
    }

    public function render()
    {
        $vehicles = Vehicle::orderBy('license_plate')->get();

        // Query Logs
        $logsQuery = MaintenanceLog::with(['vehicle', 'reporter'])
            ->when($this->search, function ($q) {
                $q->whereHas('vehicle', fn($v) => $v->where('license_plate', 'like', "%{$this->search}%"))
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhere('workshop', 'like', "%{$this->search}%");
            })
            ->when($this->filterVehicle, fn($q) => $q->where('vehicle_id', $this->filterVehicle))
            ->latest('maintenance_date');

        $logs = $logsQuery->paginate(10);

        // Query Schedules
        $schedulesQuery = MaintenanceSchedule::with('vehicle')
            ->when($this->search, function ($q) {
                $q->whereHas('vehicle', fn($v) => $v->where('license_plate', 'like', "%{$this->search}%"))
                    ->orWhere('maintenance_type', 'like', "%{$this->search}%");
            })
            ->when($this->filterVehicle, fn($q) => $q->where('vehicle_id', $this->filterVehicle))
            ->latest();

        $schedules = $schedulesQuery->get();

        // Summary Stats
        $currentMonthCost = MaintenanceLog::whereYear('maintenance_date', now()->year)
            ->whereMonth('maintenance_date', now()->month)
            ->sum('cost');

        $dueSchedulesCount = $schedules->filter(fn($s) => $s->isDue($s->vehicle?->current_odometer_km ?? 0))->count();
        $inMaintenanceCount = Vehicle::where('status', 'maintenance')->count();
        $monthlyCompletedCount = MaintenanceLog::whereYear('maintenance_date', now()->year)
            ->whereMonth('maintenance_date', now()->month)
            ->where('status', 'completed')
            ->count();

        return view('livewire.maintenance.maintenance-index', [
            'logs'                  => $logs,
            'schedules'             => $schedules,
            'vehicles'              => $vehicles,
            'currentMonthCost'      => $currentMonthCost,
            'dueSchedulesCount'     => $dueSchedulesCount,
            'inMaintenanceCount'    => $inMaintenanceCount,
            'monthlyCompletedCount' => $monthlyCompletedCount,
        ])->layout('layouts.app', ['title' => 'Maintenance & Perawatan Kendaraan']);
    }
}
