<?php

namespace App\Livewire\Incidents;

use App\Models\Employee;
use App\Models\Incident;
use App\Models\IncidentFollowup;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class IncidentIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterType = '';
    public string $filterSeverity = '';
    public string $filterStatus = '';

    // Modal Form Insiden Baru
    public bool $showModal = false;
    public string $type = 'kecelakaan';
    public string $severity = 'medium';
    public string $title = '';
    public string $description = '';
    public string $occurred_at = '';
    public string $location = '';
    public ?int $vehicle_id = null;
    public ?int $driver_id = null;
    public $photo;

    // Modal Detail & Tindak Lanjut (Followup)
    public bool $showDetailModal = false;
    public ?Incident $selectedIncident = null;
    public string $followupAction = '';
    public string $followupStatus = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'filterType'     => ['except' => ''],
        'filterSeverity' => ['except' => ''],
        'filterStatus'   => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->occurred_at = now()->format('Y-m-d\TH:i');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['title', 'description', 'location', 'vehicle_id', 'driver_id', 'photo']);
        $this->type = 'kecelakaan';
        $this->severity = 'medium';
        $this->occurred_at = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:keamanan,kontaminasi,kecelakaan,pelanggaran,lainnya',
            'severity'    => 'required|in:low,medium,high,critical',
            'occurred_at' => 'required|date',
            'description' => 'required|string',
            'photo'       => 'nullable|image|max:3072',
        ]);

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('incidents', config('filesystems.default_public_disk', 'public'));
        }

        $incidentNumber = Incident::generateIncidentNumber();

        Incident::create([
            'incident_number' => $incidentNumber,
            'type'            => $this->type,
            'severity'        => $this->severity,
            'title'           => $this->title,
            'description'     => $this->description,
            'occurred_at'     => $this->occurred_at,
            'location'        => $this->location ?: null,
            'vehicle_id'      => $this->vehicle_id ?: null,
            'driver_id'       => $this->driver_id ?: null,
            'reported_by'     => auth()->id(),
            'status'          => 'open',
            'photo'           => $photoPath,
        ]);

        session()->flash('success', "Insiden baru '{$incidentNumber}' berhasil dilaporkan.");
        $this->showModal = false;
    }

    public function openDetail(int $id): void
    {
        $this->loadIncident($id);
        $this->followupAction = '';
        $this->followupStatus = $this->selectedIncident->status;
        $this->showDetailModal = true;
    }

    public function loadIncident(int $id): void
    {
        $this->selectedIncident = Incident::with(['vehicle', 'driver', 'reporter', 'followups.user'])->find($id);
    }

    public function addFollowup(): void
    {
        $this->validate([
            'followupAction' => 'required|string|min:5',
        ]);

        IncidentFollowup::create([
            'incident_id'   => $this->selectedIncident->id,
            'user_id'       => auth()->id(),
            'action_taken'  => $this->followupAction,
            'status_change' => $this->followupStatus !== $this->selectedIncident->status ? $this->followupStatus : null,
        ]);

        $updateData = [];
        if ($this->followupStatus !== $this->selectedIncident->status) {
            $updateData['status'] = $this->followupStatus;
            if (in_array($this->followupStatus, ['resolved', 'closed'])) {
                $updateData['resolved_at'] = now();
                $updateData['resolution'] = $this->followupAction;
            }
            $this->selectedIncident->update($updateData);
        }

        $this->followupAction = '';
        $this->loadIncident($this->selectedIncident->id);
        session()->flash('detail_success', 'Tindakan penanganan insiden berhasil dicatat.');
    }

    public function delete(int $id): void
    {
        $incident = Incident::findOrFail($id);
        if ($incident->photo) {
            Storage::disk(config('filesystems.default_public_disk', 'public'))->delete($incident->photo);
        }
        $incident->delete();
        session()->flash('success', 'Laporan insiden berhasil dihapus.');
    }

    public function render()
    {
        $query = Incident::with(['vehicle', 'driver', 'reporter'])
            ->when($this->search, function ($q) {
                $q->where('incident_number', 'like', "%{$this->search}%")
                    ->orWhere('title', 'like', "%{$this->search}%")
                    ->orWhere('location', 'like', "%{$this->search}%")
                    ->orWhereHas('driver', fn($d) => $d->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('vehicle', fn($v) => $v->where('license_plate', 'like', "%{$this->search}%"));
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterSeverity, fn($q) => $q->where('severity', $this->filterSeverity))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest('occurred_at');

        $incidents = $query->paginate(10);

        $vehicles = Vehicle::orderBy('license_plate')->get();
        $drivers = Employee::where('status', 'active')->orderBy('name')->get();

        // Summary metrics
        $openCount = Incident::where('status', 'open')->count();
        $investigatingCount = Incident::where('status', 'investigating')->count();
        $criticalCount = Incident::where('severity', 'critical')->whereIn('status', ['open', 'investigating'])->count();
        $resolvedCount = Incident::whereIn('status', ['resolved', 'closed'])->count();

        return view('livewire.incidents.incident-index', [
            'incidents'          => $incidents,
            'vehicles'           => $vehicles,
            'drivers'            => $drivers,
            'openCount'          => $openCount,
            'investigatingCount' => $investigatingCount,
            'criticalCount'      => $criticalCount,
            'resolvedCount'      => $resolvedCount,
        ])->layout('layouts.app', ['title' => 'Log Insiden & Keamanan']);
    }
}
