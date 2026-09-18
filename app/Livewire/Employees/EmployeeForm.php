<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class EmployeeForm extends Component
{
    use WithFileUploads;

    public ?Employee $employee = null;
    public bool $isEdit = false;

    // Form fields Karyawan
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

    // Form fields Akun Pengguna / Akses Login
    public bool $create_user_account = false;
    public ?int $user_id = null;
    public string $user_username = '';
    public string $user_role = 'Sopir';
    public string $user_password = '';
    public bool $user_is_active = true;

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

    public function updatedType($value): void
    {
        if ($value === 'sopir') {
            $this->user_role = 'Sopir';
            if (!$this->isEdit) {
                $this->create_user_account = true;
            }
        } elseif ($value === 'karyawan') {
            if ($this->user_role === 'Sopir') {
                $this->user_role = 'Staff';
            }
        }
    }

    public function updatedName($value): void
    {
        if (!$this->isEdit && empty($this->user_username) && !empty($value)) {
            $cleaned = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', trim($value))[0]));
            if (!empty($cleaned)) {
                $this->user_username = $cleaned . rand(10, 99);
            }
        }
    }

    public function mount(?Employee $employee = null): void
    {
        if ($employee && $employee->exists) {
            $this->employee = $employee;
            $this->isEdit   = true;
            $this->fill($employee->toArray());
            $this->existingPhoto = $employee->photo;
            $this->photo = null;

            // Load data akun pengguna jika sudah terhubung
            if ($employee->user_id) {
                $user = $employee->user;
                if ($user) {
                    $this->create_user_account = true;
                    $this->user_id             = $user->id;
                    $this->user_username       = $user->username ?? '';
                    $this->user_role           = $user->roles->first()?->name ?? 'Sopir';
                    $this->user_is_active      = (bool) $user->is_active;
                }
            }

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
            // Default untuk sopir adalah langsung buat akun login
            if ($this->type === 'sopir') {
                $this->create_user_account = true;
                $this->user_role = 'Sopir';
            }
        }
    }

    public function save(): void
    {
        // Normalisasi input string kosong menjadi null sebelum validasi
        $nullableFields = [
            'position', 'department', 'phone', 'email', 'address',
            'date_of_birth', 'place_of_birth', 'gender', 'nik', 'npwp',
            'join_date', 'contract_end_date', 'sim_number', 'sim_type',
            'sim_expiry', 'skck_expiry', 'id_card_number', 'id_card_expiry', 'notes'
        ];
        foreach ($nullableFields as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

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

        // --- PROSES PEMBUATAN / PEMBARUAN AKUN PENGGUNA OTOMATIS ---
        if ($this->create_user_account) {
            $userRules = [
                'user_role'     => 'required|exists:roles,name',
                'user_username' => 'nullable|string|alpha_dash|max:50',
            ];

            if (!$this->user_id || !empty($this->user_password)) {
                $userRules['user_password'] = 'required|string|min:6';
            }

            $this->validate($userRules, [
                'user_role.required'     => 'Peran (Role) akses wajib dipilih.',
                'user_password.required' => 'Kata sandi wajib diisi untuk akun login baru.',
                'user_password.min'      => 'Kata sandi minimal 6 karakter.',
            ]);

            $targetEmail = $this->email ?: (strtolower(preg_replace('/[^a-z0-9]/', '', $this->name)) . rand(100, 999) . '@simventra.internal');
            $targetUsername = $this->user_username ?: strtolower(preg_replace('/[^a-z0-9]/', '', explode(' ', trim($this->name))[0])) . rand(10, 99);

            if ($this->user_id) {
                $existingUser = User::find($this->user_id);
                if ($existingUser) {
                    $userData = [
                        'name'      => $this->name,
                        'phone'     => $this->phone ?: $existingUser->phone,
                        'is_active' => $this->user_is_active,
                    ];
                    if (!empty($this->email)) {
                        $userData['email'] = $this->email;
                    }
                    if (!empty($this->user_username)) {
                        $userData['username'] = $this->user_username;
                    }
                    if (!empty($this->user_password)) {
                        $userData['password'] = Hash::make($this->user_password);
                    }
                    $existingUser->update($userData);
                    $existingUser->syncRoles([$this->user_role]);
                    $validated['user_id'] = $existingUser->id;
                }
            } else {
                // Buat akun user baru
                if (User::where('email', $targetEmail)->exists()) {
                    $targetEmail = strtolower(preg_replace('/[^a-z0-9]/', '', $this->name)) . rand(1000, 9999) . '@simventra.internal';
                }

                $newUser = User::create([
                    'name'      => $this->name,
                    'username'  => $targetUsername,
                    'email'     => $targetEmail,
                    'phone'     => $this->phone ?: null,
                    'password'  => Hash::make($this->user_password ?: 'password123'),
                    'is_active' => $this->user_is_active,
                ]);

                $newUser->assignRole($this->user_role);
                $this->user_id = $newUser->id;
                $validated['user_id'] = $newUser->id;
            }
        } else {
            if (!$this->isEdit) {
                $validated['user_id'] = null;
            }
        }

        if ($this->isEdit && $this->employee) {
            $this->employee->update($validated);
            session()->flash('success', "Data karyawan '{$this->employee->name}' berhasil diperbarui.");
        } else {
            $employee = Employee::create($validated);
            session()->flash('success', "Karyawan '{$employee->name}' berhasil ditambahkan" . ($this->create_user_account ? " beserta akun login pengguna." : "."));
        }

        $this->redirect(route('employees.index'), navigate: true);
    }

    public function render()
    {
        $title = $this->isEdit ? 'Edit Karyawan: ' . $this->employee?->name : 'Tambah Karyawan';
        $roles = Role::orderBy('name')->get();

        return view('livewire.employees.employee-form', array_merge(get_object_vars($this), [
            'title' => $title,
            'roles' => $roles,
        ]))->layout('layouts.app', ['title' => $title]);
    }
}
