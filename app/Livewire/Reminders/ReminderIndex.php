<?php

namespace App\Livewire\Reminders;

use App\Models\EmployeeDocument;
use App\Models\VehicleDocument;
use Livewire\Component;
use Livewire\WithPagination;

class ReminderIndex extends Component
{
    use WithPagination;

    public string $filterType = 'all';  // all, vehicle, employee
    public int $filterDays = 30;

    public function render()
    {
        $vehicleDocs = VehicleDocument::with('vehicle')
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($this->filterDays))
            ->whereDate('expiry_date', '>=', now()->subDays(1))
            ->orderBy('expiry_date')
            ->get()
            ->map(fn($d) => [
                'type'        => 'vehicle',
                'title'       => $d->title,
                'related'     => $d->vehicle?->license_plate . ' – ' . $d->vehicle?->brand,
                'expiry_date' => $d->expiry_date,
                'days_left'   => now()->diffInDays($d->expiry_date, false),
                'doc_type'    => $d->document_type,
                'model'       => $d,
            ]);

        $employeeDocs = EmployeeDocument::with('employee')
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($this->filterDays))
            ->whereDate('expiry_date', '>=', now()->subDays(1))
            ->orderBy('expiry_date')
            ->get()
            ->map(fn($d) => [
                'type'        => 'employee',
                'title'       => $d->title,
                'related'     => $d->employee?->name,
                'expiry_date' => $d->expiry_date,
                'days_left'   => now()->diffInDays($d->expiry_date, false),
                'doc_type'    => $d->document_type,
                'model'       => $d,
            ]);

        $allDocs = match ($this->filterType) {
            'vehicle'  => $vehicleDocs,
            'employee' => $employeeDocs,
            default    => $vehicleDocs->concat($employeeDocs),
        };

        $sorted = $allDocs->sortBy('days_left')->values();

        $summary = [
            'critical' => $sorted->where('days_left', '<=', 7)->count(),
            'warning'  => $sorted->whereBetween('days_left', [8, 14])->count(),
            'notice'   => $sorted->whereBetween('days_left', [15, 30])->count(),
            'expired'  => $sorted->where('days_left', '<', 0)->count(),
        ];

        return view('livewire.reminders.reminder-index', compact('sorted', 'summary'))
            ->layout('layouts.app', ['title' => 'Reminder Dokumen']);
    }
}
