<?php

namespace App\Livewire\Monitoring;

use App\Models\VehicleAssignment;
use Livewire\Component;

class LiveTrackingMap extends Component
{
    public function render()
    {
        $activeTrips = VehicleAssignment::with(['vehicle', 'driver', 'latestLocation'])
            ->where('status', 'on_trip')
            ->latest('updated_at')
            ->get();

        return view('livewire.monitoring.live-tracking-map', [
            'activeTrips' => $activeTrips,
        ])->layout('layouts.app', ['title' => 'Monitoring Armada Live GPS']);
    }
}
