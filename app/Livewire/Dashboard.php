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
        $user = auth()->user();

        // Tampilan Khusus Pengemudi (Sopir)
        if ($user && $user->hasRole('Sopir') && !$user->can('view vehicles')) {
            $employee = $user->employee;
            $activeAssignment = $employee ? \App\Models\VehicleAssignment::with(['vehicle'])
                ->where('driver_id', $employee->id)
                ->whereIn('status', ['assigned', 'confirmed', 'on_trip'])
                ->latest()
                ->first() : null;

            $isGpsSilent = false;
            $silenceMinutes = 0;
            if ($activeAssignment && $activeAssignment->status === 'on_trip') {
                $timeoutMinutes = (int) config('simventra.heartbeat.timeout_minutes', 5);
                $refTime = $activeAssignment->last_ping_at ?? $activeAssignment->departure_time ?? $activeAssignment->updated_at;
                if ($refTime && \Carbon\Carbon::instance($refTime)->isBefore(now()->subMinutes($timeoutMinutes))) {
                    $isGpsSilent = true;
                    $silenceMinutes = max(1, (int) abs(now()->diffInMinutes($refTime)));
                }
            }

            $personalDocs = $employee ? $employee->documents()->orderBy('expiry_date')->get() : collect();

            return view('livewire.dashboard-driver', compact(
                'user',
                'employee',
                'activeAssignment',
                'isGpsSilent',
                'silenceMinutes',
                'personalDocs'
            ))->layout('layouts.app', ['title' => 'Dashboard Sopir']);
        }
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

        $timeoutMinutes = (int) config('simventra.heartbeat.timeout_minutes', 5);
        $cutoffAt = now()->subMinutes($timeoutMinutes);

        // Armada yang sedang dalam perjalanan
        $activeTrips = \App\Models\VehicleAssignment::with(['vehicle', 'driver'])
            ->where('status', 'on_trip')
            ->get();

        $silentTrips = [];
        foreach ($activeTrips as $trip) {
            $refTime = $trip->last_ping_at ?? $trip->departure_time ?? $trip->updated_at;
            if ($refTime && \Carbon\Carbon::instance($refTime)->isBefore($cutoffAt)) {
                $silenceMins = max(1, (int) abs(now()->diffInMinutes($refTime)));
                $silentTrips[] = [
                    'assignment'    => $trip,
                    'driver_name'   => $trip->driver?->name ?? 'Unknown',
                    'driver_phone'  => $trip->driver?->phone,
                    'license_plate' => strtoupper($trip->vehicle?->license_plate ?? '-'),
                    'brand_model'   => $trip->vehicle ? "{$trip->vehicle->brand} {$trip->vehicle->model}" : '-',
                    'destination'   => $trip->destination ?? '-',
                    'last_ping'     => \Carbon\Carbon::instance($refTime)->format('H:i, d M'),
                    'silence_mins'  => $silenceMins,
                ];
            }
        }

        $stats['on_trip_count']      = $activeTrips->count();
        $stats['silent_trips_count'] = count($silentTrips);

        return view('livewire.dashboard', compact(
            'stats',
            'recentExpiringVehicleDocs',
            'recentExpiringEmployeeDocs',
            'silentTrips'
        ))->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
