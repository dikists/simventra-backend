<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_employees' => Employee::active()->count(),
            'total_drivers'   => Employee::active()->drivers()->count(),
            'total_vehicles'  => Vehicle::active()->count(),
            'expiring_soon_7'  => VehicleDocument::expiringSoon(7)->count() + EmployeeDocument::expiringSoon(7)->count(),
            'expiring_soon_30' => VehicleDocument::expiringSoon(30)->count() + EmployeeDocument::expiringSoon(30)->count(),
            'expired_vehicle_docs' => VehicleDocument::where('status', 'expired')->count(),
            'expired_employee_docs'=> EmployeeDocument::where('status', 'expired')->count(),
        ];

        $recentExpiringVehicleDocs = VehicleDocument::with('vehicle')
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->limit(5)
            ->get();

        $recentExpiringEmployeeDocs = EmployeeDocument::with('employee')
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->limit(5)
            ->get();

        return view('livewire.dashboard', compact('stats', 'recentExpiringVehicleDocs', 'recentExpiringEmployeeDocs'))
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
