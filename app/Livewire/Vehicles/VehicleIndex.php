<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;

class VehicleIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';
    public bool $filterHalal = false;
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterType'   => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $vehicle = Vehicle::findOrFail($this->deleteId);
            $vehicle->delete();
            session()->flash('success', "Kendaraan '{$vehicle->license_plate}' berhasil dihapus.");
        }
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $vehicles = Vehicle::with('assignedDriver')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('license_plate', 'like', "%{$this->search}%")
                  ->orWhere('vehicle_code', 'like', "%{$this->search}%")
                  ->orWhere('brand', 'like', "%{$this->search}%")
                  ->orWhere('model', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('vehicle_type', 'like', "%{$this->filterType}%"))
            ->when($this->filterHalal, fn($q) => $q->where('is_halal_dedicated', true))
            ->orderBy('license_plate')
            ->paginate(15);

        return view('livewire.vehicles.vehicle-index', compact('vehicles'))
            ->layout('layouts.app', ['title' => 'Kendaraan']);
    }
}
