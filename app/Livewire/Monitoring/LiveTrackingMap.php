<?php

namespace App\Livewire\Monitoring;

use App\Models\VehicleAssignment;
use App\Models\Warehouse;
use Livewire\Component;

class LiveTrackingMap extends Component
{
    public function startTripManual(int $assignmentId): void
    {
        $assignment = VehicleAssignment::with(['vehicle', 'driver'])->find($assignmentId);

        if (!$assignment || !in_array($assignment->status, ['assigned', 'confirmed'])) {
            return;
        }

        $assignment->update([
            'status'         => 'on_trip',
            'departure_time' => now(),
        ]);

        $driver = $assignment->driver;
        $driverUser = $driver ? \App\Services\DriverMatchingService::findUserForDriver($driver) : null;

        if ($driverUser && !empty($driverUser->push_token)) {
            \App\Services\PushNotificationService::sendToUser(
                $driverUser,
                '🚀 Perjalanan Telah Dimulai!',
                "Koordinator telah memberangkatkan armada {$assignment->vehicle?->license_plate}. Pelacakan GPS aktif di HP Anda.",
                [
                    'assignment_id' => $assignment->id,
                    'license_plate' => $assignment->vehicle?->license_plate,
                    'status'        => 'on_trip',
                ]
            );
        }

        activity()
            ->performedOn($assignment)
            ->causedBy(auth()->user())
            ->log("Dispatcher memberangkatkan armada {$assignment->vehicle?->license_plate} via Peta Live GPS.");

        session()->flash('success', "Armada {$assignment->vehicle?->license_plate} berhasil diberangkatkan! Marker kini aktif di Live GPS.");

        $this->dispatch('trip-started');
    }

    public function render()
    {
        $activeTrips = VehicleAssignment::with(['vehicle', 'driver', 'latestLocation'])
            ->where('status', 'on_trip')
            ->latest('updated_at')
            ->get();

        $stagedTrips = VehicleAssignment::with(['vehicle', 'driver'])
            ->whereIn('status', ['assigned', 'confirmed'])
            ->latest('updated_at')
            ->get();

        $warehouse = Warehouse::getPrimary();

        return view('livewire.monitoring.live-tracking-map', [
            'activeTrips' => $activeTrips,
            'stagedTrips' => $stagedTrips,
            'warehouse'   => $warehouse,
        ])->layout('layouts.app', ['title' => 'Monitoring Armada Live GPS']);
    }
}
