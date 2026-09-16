<?php

namespace App\Livewire\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $eventFilter = '';
    public string $dateFilter = '';

    public ?Activity $selectedActivity = null;
    public bool $showDetailModal = false;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEventFilter(): void { $this->resetPage(); }
    public function updatingDateFilter(): void { $this->resetPage(); }

    public function showDetails(int $activityId): void
    {
        $this->selectedActivity = Activity::with('causer')->findOrFail($activityId);
        $this->showDetailModal = true;
    }

    public function render()
    {
        $activities = Activity::with(['causer', 'subject'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('log_name', 'like', '%' . $this->search . '%')
                      ->orWhere('event', 'like', '%' . $this->search . '%')
                      ->orWhereHasMorph('causer', [\App\Models\User::class], function ($uq) {
                          $uq->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->eventFilter, function ($query) {
                $query->where('event', $this->eventFilter);
            })
            ->when($this->dateFilter, function ($query) {
                if ($this->dateFilter === 'today') {
                    $query->whereDate('created_at', today());
                } elseif ($this->dateFilter === 'week') {
                    $query->where('created_at', '>=', now()->subDays(7));
                } elseif ($this->dateFilter === 'month') {
                    $query->where('created_at', '>=', now()->subDays(30));
                }
            })
            ->latest()
            ->paginate(15);

        return view('livewire.activity-logs.activity-log-index', compact('activities'))
            ->layout('layouts.app', ['title' => 'Log Aktivitas']);
    }
}
