<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterStatus = '';
    public string $sortBy = 'name';
    public string $sortDir = 'asc';
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterType'   => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'asc';
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $employee = Employee::findOrFail($this->deleteId);
            $employee->delete();
            session()->flash('success', "Data karyawan '{$employee->name}' berhasil dihapus.");
        }
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $employees = Employee::query()
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('employee_number', 'like', "%{$this->search}%")
                  ->orWhere('nik', 'like', "%{$this->search}%")
                  ->orWhere('position', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(15);

        return view('livewire.employees.employee-index', compact('employees'))
            ->layout('layouts.app', ['title' => 'Karyawan & Sopir']);
    }
}
