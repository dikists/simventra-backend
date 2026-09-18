<?php

namespace App\Livewire\Users;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    public ?int $selectedUserId = null;
    public ?int $selectedEmployeeId = null;

    // Form fields
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';
    public bool $is_active = true;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'username', 'email', 'phone', 'password', 'password_confirmation', 'role', 'selectedUserId', 'selectedEmployeeId']);
        $this->is_active = true;
        $this->role = Role::first()?->name ?? 'HR';
        $this->showCreateModal = true;
    }

    public function updatedSelectedEmployeeId($employeeId): void
    {
        if (!$employeeId) return;

        $emp = Employee::find($employeeId);
        if ($emp) {
            $this->name  = $emp->name;
            $this->email = $emp->email ?: strtolower(preg_replace('/[^a-z0-9]/', '', $emp->name)) . '@simventra.internal';
            $this->phone = $emp->phone ?? '';

            // Auto-generate username
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', trim($emp->name))[0]));
            $this->username = $cleanName . rand(10, 99);

            // Auto-detect role
            if ($emp->type === 'sopir') {
                $this->role = 'Sopir';
            } elseif (stripos($emp->position ?? '', 'fleet') !== false || stripos($emp->department ?? '', 'armada') !== false) {
                $this->role = 'Fleet Officer';
            } elseif (stripos($emp->department ?? '', 'hr') !== false) {
                $this->role = 'HR';
            }
        }
    }

    public function createUser(): void
    {
        $this->validate([
            'name'     => 'required|string|max:255',
            'username' => 'nullable|string|alpha_dash|max:50|unique:users,username',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);

        $finalUsername = $this->username ?: strtolower(explode('@', $this->email)[0]);
        $finalUsername = preg_replace('/[^a-z0-9_.]/', '', $finalUsername);

        $user = User::create([
            'name'      => $this->name,
            'username'  => $finalUsername ?: null,
            'email'     => $this->email,
            'phone'     => $this->phone ?: null,
            'password'  => Hash::make($this->password),
            'is_active' => $this->is_active,
        ]);

        $user->assignRole($this->role);

        // Jika dipilih dari karyawan, hubungkan user_id ke employee
        if ($this->selectedEmployeeId) {
            $employee = Employee::find($this->selectedEmployeeId);
            if ($employee) {
                $employee->update(['user_id' => $user->id]);
            }
        }

        session()->flash('success', "Pengguna {$user->name} berhasil ditambahkan" . ($this->selectedEmployeeId ? " dan dihubungkan ke data Karyawan." : "."));
        $this->showCreateModal = false;
    }

    public function openEditModal(int $userId): void
    {
        $this->resetValidation();
        $this->selectedUserId = $userId;
        $user = User::findOrFail($userId);

        $this->name = $user->name;
        $this->username = $user->username ?? '';
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->is_active = (bool) $user->is_active;
        $this->role = $user->roles->first()?->name ?? '';
        $this->password = '';
        $this->password_confirmation = '';

        $this->showEditModal = true;
    }

    public function updateUser(): void
    {
        $user = User::findOrFail($this->selectedUserId);

        $this->validate([
            'name'     => 'required|string|max:255',
            'username' => ['nullable', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);

        $finalUsername = $this->username ?: strtolower(explode('@', $this->email)[0]);
        $finalUsername = preg_replace('/[^a-z0-9_.]/', '', $finalUsername);

        $data = [
            'name'      => $this->name,
            'username'  => $finalUsername ?: null,
            'email'     => $this->email,
            'phone'     => $this->phone ?: null,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);
        $user->syncRoles([$this->role]);

        session()->flash('success', "Pengguna {$user->name} berhasil diperbarui.");
        $this->showEditModal = false;
    }

    public function confirmDelete(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        if ($this->selectedUserId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            $this->showDeleteModal = false;
            return;
        }

        $user = User::findOrFail($this->selectedUserId);
        $name = $user->name;

        // Unlink employee if linked
        Employee::where('user_id', $user->id)->update(['user_id' => null]);

        $user->delete();

        session()->flash('success', "Pengguna {$name} berhasil dihapus.");
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $users = User::with(['roles', 'employee'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', fn($q) => $q->where('name', $this->roleFilter));
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $roles = Role::orderBy('name')->get();
        $availableEmployees = Employee::whereNull('user_id')->orderBy('name')->get();

        return view('livewire.users.user-index', compact('users', 'roles', 'availableEmployees'))
            ->layout('layouts.app', ['title' => 'Manajemen Pengguna']);
    }
}
