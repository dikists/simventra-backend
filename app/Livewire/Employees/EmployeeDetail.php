<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Livewire\Component;

class EmployeeDetail extends Component
{
    public Employee $employee;

    public function mount(Employee $employee): void
    {
        $this->employee = $employee->load(['documents', 'vehicle', 'relatives', 'user']);
    }

    public function render()
    {
        return view('livewire.employees.employee-detail', [
            'employee' => $this->employee,
        ])->layout('layouts.app', ['title' => 'Detail Karyawan - ' . $this->employee->name]);
    }
}
