<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\User;
use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class VehicleDetail extends Component
{
    public Vehicle $vehicle;

    // Dispatch Modal State
    public bool $showDispatchModal = false;
    public ?string $selectedDriverId = null;
    public ?string $destination = null;
    public ?string $dispatchOdometer = null;
    public ?string $dispatchNotes = null;

    // Return / Check-in Modal State
    public bool $showReturnModal = false;
    public ?string $returnOdometer = null;
    public string $returnCondition = 'baik';
    public ?string $returnNotes = null;

    public function mount(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
        $this->loadVehicleData();
    }

    public function loadVehicleData(): void
    {
        $this->vehicle->load(['assignedDriver', 'documents', 'activeAssignment.driver', 'assignments.driver', 'assignments.assignedBy']);
    }

    public function openDispatchModal(): void
    {
        $this->resetValidation();
        $this->dispatchOdometer = (string) $this->vehicle->current_odometer_km;
        $this->selectedDriverId = null;
        $this->destination = '';
        $this->dispatchNotes = '';
        $this->showDispatchModal = true;
    }

    public function dispatchVehicle(): void
    {
        $this->validate([
            'selectedDriverId' => 'required|exists:employees,id',
            'destination'      => 'nullable|string|max:255',
            'dispatchOdometer' => 'required|numeric|min:0',
            'dispatchNotes'    => 'nullable|string|max:1000',
        ], [
            'selectedDriverId.required' => 'Pilih sopir yang akan ditugaskan.',
            'dispatchOdometer.required' => 'Odometer awal keberangkatan wajib diisi.',
            'dispatchOdometer.numeric'  => 'Odometer harus berupa angka.',
        ]);

        $driver = Employee::findOrFail($this->selectedDriverId);

        // Check if driver is already on active assignment
        if ($driver->activeAssignment()->exists()) {
            $this->addError('selectedDriverId', 'Sopir ini sedang memiliki tugas aktif di armada lain.');
            return;
        }

        // 1. Create VehicleAssignment record
        $assignment = VehicleAssignment::create([
            'vehicle_id'     => $this->vehicle->id,
            'driver_id'      => $driver->id,
            'assigned_by'    => auth()->id(),
            'status'         => 'assigned',
            'origin'         => 'Gudang Utama',
            'destination'    => $this->destination,
            'start_odometer' => (float) $this->dispatchOdometer,
            'departure_time' => now(),
            'notes'          => $this->dispatchNotes,
        ]);

        // 2. Auto-link driver to User account if not yet linked
        $driverUser = $driver->user ?? User::find($driver->user_id);
        if (!$driverUser && !empty($driver->email)) {
            $driverUser = User::where('email', $driver->email)->first();
        }
        if (!$driverUser && !empty($driver->phone)) {
            $driverUser = User::where('phone', $driver->phone)->first();
        }
        if (!$driverUser && !empty($driver->name)) {
            $driverUser = User::whereRaw('LOWER(name) = ?', [strtolower(trim($driver->name))])->first();
            if (!$driverUser) {
                $driverUser = User::where('name', 'LIKE', '%' . trim($driver->name) . '%')->first();
            }
        }

        if ($driverUser && !$driver->user_id) {
            $driver->update(['user_id' => $driverUser->id]);
        }

        // 3. Send Push Notification to Driver's smartphone if push_token is registered
        if ($driverUser && !empty($driverUser->push_token)) {
            try {
                Http::timeout(5)->post('https://exp.host/--/api/v2/push/send', [
                    'to'        => $driverUser->push_token,
                    'title'     => '🔔 Penugasan Armada Baru!',
                    'body'      => "Armada {$this->vehicle->license_plate} siap ditugaskan ke {$this->destination}. Buka aplikasi untuk konfirmasi.",
                    'sound'     => 'default',
                    'priority'  => 'high',
                    'channelId' => 'driver-tasks',
                    'data'      => [
                        'assignment_id' => $assignment->id,
                        'license_plate' => $this->vehicle->license_plate,
                        'status'        => 'assigned',
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('[PushNotification]: Gagal mengirim notif ke sopir ' . $driver->name . ': ' . $e->getMessage());
            }
        }

        // 4. Update Vehicle with current driver and updated odometer
        $this->vehicle->update([
            'assigned_driver_id'  => $driver->id,
            'current_odometer_km' => (float) $this->dispatchOdometer,
        ]);

        activity()
            ->performedOn($this->vehicle)
            ->causedBy(auth()->user())
            ->withProperties([
                'driver_name' => $driver->name,
                'destination' => $this->destination,
                'start_km'    => $this->dispatchOdometer,
            ])
            ->log('Menugaskan armada ke sopir ' . $driver->name);

        $this->showDispatchModal = false;
        $this->loadVehicleData();
        session()->flash('success', "Armada {$this->vehicle->license_plate} berhasil ditugaskan ke {$driver->name}!");
    }

    public function openReturnModal(): void
    {
        $this->resetValidation();
        $this->returnOdometer = (string) $this->vehicle->current_odometer_km;
        $this->returnCondition = 'baik';
        $this->returnNotes = '';
        $this->showReturnModal = true;
    }

    public function returnToWarehouse(): void
    {
        $minOdo = (float) ($this->vehicle->activeAssignment->start_odometer ?? $this->vehicle->current_odometer_km);

        $this->validate([
            'returnOdometer'  => ['required', 'numeric', 'min:' . $minOdo],
            'returnCondition' => 'required|in:baik,perlu_cuci,perlu_perawatan,rusak',
            'returnNotes'     => 'nullable|string|max:1000',
        ], [
            'returnOdometer.required' => 'Odometer akhir kepulangan wajib diisi.',
            'returnOdometer.min'      => "Odometer akhir tidak boleh lebih kecil dari KM awal ({$minOdo} KM).",
        ]);

        $activeAssignment = $this->vehicle->activeAssignment;
        $driverName = $this->vehicle->assignedDriver?->name ?? 'Sopir';

        // 1. Complete the active assignment
        if ($activeAssignment) {
            $notes = $activeAssignment->notes ? $activeAssignment->notes . "\n" : '';
            $notes .= "[Kembali ke Gudang]: " . ($this->returnNotes ?? 'Kondisi: ' . ucfirst($this->returnCondition));

            $activeAssignment->update([
                'status'                      => 'completed',
                'end_odometer'                => (float) $this->returnOdometer,
                'return_time'                 => now(),
                'vehicle_condition_on_return' => $this->returnCondition,
                'notes'                       => $notes,
            ]);
        }

        // 2. Release driver from vehicle, update vehicle status & odometer
        $newStatus = match ($this->returnCondition) {
            'perlu_perawatan', 'rusak' => 'maintenance',
            default                    => 'active',
        };

        $this->vehicle->update([
            'assigned_driver_id'  => null,
            'current_odometer_km' => (float) $this->returnOdometer,
            'status'              => $newStatus,
        ]);

        activity()
            ->performedOn($this->vehicle)
            ->causedBy(auth()->user())
            ->withProperties([
                'driver_name' => $driverName,
                'end_km'      => $this->returnOdometer,
                'condition'   => $this->returnCondition,
            ])
            ->log("Armada kembali ke gudang dari tugas {$driverName}. Status unit dilepas.");

        $this->showReturnModal = false;
        $this->loadVehicleData();
        session()->flash('success', "Armada {$this->vehicle->license_plate} telah kembali ke gudang dan unit siap ditugaskan kembali!");
    }

    public function closeModal(): void
    {
        $this->showDispatchModal = false;
        $this->showReturnModal = false;
    }

    public function render()
    {
        // Available drivers: Active employees (type = 'sopir' or has SIM) who don't have active assignment
        $availableDrivers = Employee::where('status', 'active')
            ->where(function ($q) {
                $q->where('type', 'sopir')->orWhereNotNull('sim_number');
            })
            ->whereDoesntHave('assignments', function ($q) {
                $q->whereIn('status', ['assigned', 'confirmed', 'on_trip']);
            })
            ->orderBy('name')
            ->get();

        $assignmentHistory = $this->vehicle->assignments()
            ->with(['driver', 'assignedBy'])
            ->latest('id')
            ->take(10)
            ->get();

        return view('livewire.vehicles.vehicle-detail', [
            'vehicle'           => $this->vehicle,
            'availableDrivers'  => $availableDrivers,
            'assignmentHistory' => $assignmentHistory,
            'activeAssignment'  => $this->vehicle->activeAssignment,
        ])->layout('layouts.app', ['title' => 'Detail Armada - ' . $this->vehicle->license_plate]);
    }
}
