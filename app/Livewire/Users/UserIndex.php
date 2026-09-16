<?php

namespace App\Livewire\Users;

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
        $this->reset(['name', 'username', 'email', 'phone', 'password', 'password_confirmation', 'role', 'selectedUserId']);
        $this->is_active = true;
        $this->role = Role::first()?->name ?? 'HR';
        $this->showCreateModal = true;
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

        session()->flash('success', "Pengguna {$user->name} berhasil ditambahkan.");
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
            'username' => ['nullable', 'string', 'alpha_dash', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);

        $userData = [
            'name'      => $this->name,
            'username'  => $this->username ?: null,
            'email'     => $this->email,
            'phone'     => $this->phone ?: null,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $user->update($userData);
        $user->syncRoles([$this->role]);

        session()->flash('success', "Data pengguna {$user->name} berhasil diperbarui.");
        $this->showEditModal = false;
    }

    public function toggleActive(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
            return;
        }

        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Akun {$user->name} berhasil {$status}.");
    }

    public function confirmDelete(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

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
        $user->delete();

        session()->flash('success', "Pengguna {$name} berhasil dihapus.");
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $users = User::with('roles')
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

        return view('livewire.users.user-index', compact('users', 'roles'))
            ->layout('layouts.app', ['title' => 'Manajemen Pengguna']);
    }
}
