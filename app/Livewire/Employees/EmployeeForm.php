<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EmployeeForm extends Component
{
    use WithFileUploads;

    public ?Employee $employee = null;
    public bool $isEdit = false;

    // Form fields
    public string $employee_number = '';
    public string $name = '';
    public string $type = 'karyawan';
    public string $employment_status = 'tetap';
    public ?string $position = null;
    public ?string $department = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $address = null;
    public ?string $date_of_birth = null;
    public ?string $place_of_birth = null;
    public ?string $gender = null;
    public ?string $nik = null;
    public ?string $npwp = null;
    public ?string $join_date = null;
    public ?string $contract_end_date = null;
    public ?string $sim_number = null;
    public ?string $sim_type = null;
    public ?string $sim_expiry = null;
    public bool $has_skck = false;
    public ?string $skck_expiry = null;
    public ?string $id_card_number = null;
    public ?string $id_card_expiry = null;
    public bool $id_card_active = true;
    public string $status = 'active';
    public ?string $notes = null;
    public ?string $existingPhoto = null;
    public $photo = null;

    protected function rules(): array
    {
        $uniqueNik = 'nullable|string|size:16|unique:employees,nik';
        $uniqueEmpNum = 'required|string|max:20|unique:employees,employee_number';

        if ($this->isEdit && $this->employee) {
            $uniqueNik .= ',' . $this->employee->id;
            $uniqueEmpNum .= ',' . $this->employee->id;
        }

        return [
            'employee_number'   => $uniqueEmpNum,
            'name'              => 'required|string|max:150',
            'type'              => 'required|in:karyawan,sopir,mitra',
            'employment_status' => 'required|in:tetap,kontrak,magang,mitra',
            'position'          => 'nullable|string|max:100',
            'department'        => 'nullable|string|max:100',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:150',
            'address'           => 'nullable|string|max:500',
            'date_of_birth'     => 'nullable|date|before:today',
            'place_of_birth'    => 'nullable|string|max:100',
            'gender'            => 'nullable|in:L,P',
            'nik'               => $uniqueNik,
            'npwp'              => 'nullable|string|max:30',
            'join_date'         => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:join_date',
            'sim_number'        => 'nullable|string|max:30',
            'sim_type'          => 'nullable|string|max:10',
            'sim_expiry'        => 'nullable|date',
            'has_skck'          => 'boolean',
            'skck_expiry'       => 'nullable|date',
            'id_card_number'    => 'nullable|string|max:30',
            'id_card_expiry'    => 'nullable|date',
            'id_card_active'    => 'boolean',
            'status'            => 'required|in:active,inactive,terminated',
            'notes'             => 'nullable|string|max:1000',
            'photo'             => 'nullable|image|max:2048',
        ];
    }

    protected $validationAttributes = [
        'employee_number'   => 'Nomor Karyawan',
        'name'              => 'Nama',
        'type'              => 'Tipe',
        'employment_status' => 'Status Kepegawaian',
        'date_of_birth'     => 'Tanggal Lahir',
        'sim_expiry'        => 'Kadaluarsa SIM',
        'skck_expiry'       => 'Kadaluarsa SKCK',
    ];

    public function mount(?Employee $employee = null): void
    {
        if ($employee && $employee->exists) {
            $this->employee = $employee;
            $this->isEdit   = true;
            $this->fill($employee->toArray());
            $this->existingPhoto = $employee->photo;
            $this->photo = null;
            // Format dates to Y-m-d for input
            foreach (['date_of_birth','join_date','contract_end_date','sim_expiry','skck_expiry','id_card_expiry'] as $field) {
                if ($employee->$field) {
                    $this->$field = $employee->$field->format('Y-m-d');
                } else {
                    $this->$field = null;
                }
            }
        } else {
            $nextId = ((int) Employee::max('id')) + 1;
            $this->employee_number = 'EMP-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Convert empty strings to null for all nullable fields (dates, strings, etc.)
        $validated = array_map(function ($value) {
            return ($value === '') ? null : $value;
        }, $validated);

        // Preserve boolean types
        $validated['has_skck'] = (bool) ($this->has_skck ?? false);
        $validated['id_card_active'] = (bool) ($this->id_card_active ?? true);

        // Handle photo upload
        if ($this->photo && !is_string($this->photo)) {
            // hapus file lama jika ada saat edit
            if ($this->isEdit && $this->employee?->photo && Storage::disk(config('filesystems.default_public_disk'))->exists($this->employee->photo)) {
                Storage::disk(config('filesystems.default_public_disk'))->delete($this->employee->photo);
            }
            $validated['photo'] = $this->photo->store('employees/photos', config('filesystems.default_public_disk'));
        } elseif ($this->isEdit) {
            $validated['photo'] = $this->existingPhoto ?? $this->employee?->photo;
        } else {
            unset($validated['photo']);
        }

        if ($this->isEdit && $this->employee) {
            $this->employee->update($validated);
            session()->flash('success', "Data karyawan '{$this->employee->name}' berhasil diperbarui.");
        } else {
            $employee = Employee::create($validated);
            session()->flash('success', "Karyawan '{$employee->name}' berhasil ditambahkan.");
        }

        $this->redirect(route('employees.index'), navigate: true);
    }

    public function render()
    {
        $title = $this->isEdit ? 'Edit Karyawan: ' . $this->employee?->name : 'Tambah Karyawan';
        return view('livewire.employees.employee-form', array_merge(get_object_vars($this), [
            'title' => $title,
        ]))->layout('layouts.app', ['title' => $title]);
    }
}
