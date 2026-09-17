<?php

namespace App\Livewire\Monitoring;

use App\Models\VehicleAssignment;
use App\Models\Warehouse;
use Livewire\Component;

class LiveTrackingMap extends Component
{
    public function render()
    {
        $activeTrips = VehicleAssignment::with(['vehicle', 'driver', 'latestLocation'])
            ->where('status', 'on_trip')
            ->latest('updated_at')
            ->get();

        $warehouse = Warehouse::getPrimary();

        return view('livewire.monitoring.live-tracking-map', [
            'activeTrips' => $activeTrips,
            'warehouse'   => $warehouse,
        ])->layout('layouts.app', ['title' => 'Monitoring Armada Live GPS']);
    }
}
