<div>
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Manajemen Pengguna</h1>
            <p class="page-subtitle">Kelola akun pengguna, hak akses, dan status operasional sistem</p>
        </div>
        @can('create users')
        <button wire:click="openCreateModal" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Tambah Pengguna
        </button>
        @endcan
    </div>

    <!-- Filter Card -->
    <div class="card mb-5">
        <div class="card-body flex flex-wrap gap-3 items-center justify-between">
            <div class="flex-1 min-w-[240px] relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, email, no telepon..." class="form-input" style="padding-left:38px;">
                <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#adb5bd;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div class="flex gap-3">
                <select wire:model.live="roleFilter" class="form-select" style="min-width:160px;">
                    <option value="">Semua Peran</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="statusFilter" class="form-select" style="min-width:140px;">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Email & Kontak</th>
                        <th>Peran (Role)</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#556ee6,#6f42c1);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:13.5px;color:#343a40;">{{ $user->name }}</div>
                                    @if($user->username)
                                        <div style="font-size:11.5px;color:#556ee6;font-weight:500;">&#64;{{ $user->username }}</div>
                                    @endif
                                    @if($user->employee)
                                        <span class="badge badge-gray" style="font-size:10px;padding:2px 6px;margin-top:2px;" title="{{ $user->employee->name }}">
                                            👤 {{ $user->employee->employee_number }} • {{ ucfirst($user->employee->type) }}
                                        </span>
                                    @endif
                                    @if($user->id === auth()->id())
                                        <span class="badge badge-blue" style="font-size:10.5px;padding:2px 6px;">Akun Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px;color:#495057;">{{ $user->email }}</div>
                            <div style="font-size:11.5px;color:#74788d;">{{ $user->phone ?? 'Tidak ada telepon' }}</div>
                        </td>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge badge-teal" style="font-size:11.5px;margin-right:4px;">{{ $role->name }}</span>
                            @empty
                                <span class="badge badge-gray">Tanpa Peran</span>
                            @endforelse
                        </td>
                        <td>
                            <button wire:click="toggleActive({{ $user->id }})"
                                    class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}"
                                    style="border:none;cursor:pointer;"
                                    title="Klik untuk mengubah status">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td style="font-size:12.5px;color:#74788d;">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex;gap:6px;">
                                <button wire:click="openEditModal({{ $user->id }})" class="btn-action" title="Edit">
                                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @if($user->id !== auth()->id())
                                <button wire:click="confirmDelete({{ $user->id }})" class="btn-action btn-action-danger" title="Hapus">
                                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <div class="empty-state-title">Tidak ada pengguna ditemukan</div>
                                <div class="empty-state-desc">Ubah kata kunci pencarian atau buat pengguna baru</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #eff2f7;">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Create User Modal -->
    @if($showCreateModal)
    <div class="modal-overlay">
        <div class="modal-box" style="max-width:540px;">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Pengguna Baru</h3>
                <button wire:click="$set('showCreateModal', false)" style="border:none;background:none;cursor:pointer;color:#adb5bd;">✕</button>
            </div>
            <form wire:submit="createUser">
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">
                    <!-- Opsi Hubungkan dengan Karyawan -->
                    <div style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:10px;padding:12px;">
                        <label class="form-label" style="margin-bottom:6px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:6px;">
                            <span>🔗</span> Hubungkan dengan Data Karyawan (Opsional)
                        </label>
                        <select wire:model.live="selectedEmployeeId" class="form-select">
                            <option value="">-- Input Pengguna Bebas (Bukan Karyawan) --</option>
                            @foreach($availableEmployees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->name }} ({{ ucfirst($emp->type) }} - {{ $emp->employee_number }})
                                </option>
                            @endforeach
                        </select>
                        <p style="font-size:11px;color:#64748b;margin-top:5px;margin-bottom:0;">
                            Pilih karyawan untuk mengisi Nama, Email, No. HP, dan Role secara otomatis.
                        </p>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Nama Lengkap *</label>
                            <input wire:model="name" type="text" class="form-input @error('name') form-input-error @enderror" placeholder="Nama pengguna">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Username (Opsional)</label>
                            <input wire:model="username" type="text" class="form-input @error('username') form-input-error @enderror" placeholder="contoh: budi123">
                            @error('username') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Email *</label>
                            <input wire:model="email" type="email" class="form-input @error('email') form-input-error @enderror" placeholder="email@perusahaan.com">
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">No. Telepon / WhatsApp</label>
                            <input wire:model="phone" type="text" class="form-input" placeholder="08xxxxxxxx">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Peran (Role) *</label>
                            <select wire:model="role" class="form-select @error('role') form-input-error @enderror">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div style="display:flex;align-items:center;padding-top:24px;gap:8px;">
                            <input wire:model="is_active" type="checkbox" id="create_active" style="width:18px;height:18px;">
                            <label for="create_active" style="font-size:13px;color:#495057;cursor:pointer;">Status Aktif</label>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Password *</label>
                            <input wire:model="password" type="password" class="form-input @error('password') form-input-error @enderror" placeholder="Min. 8 karakter">
                            @error('password') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Konfirmasi Password *</label>
                            <input wire:model="password_confirmation" type="password" class="form-input" placeholder="Ulangi password">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Edit User Modal -->
    @if($showEditModal)
    <div class="modal-overlay">
        <div class="modal-box" style="max-width:540px;">
            <div class="modal-header">
                <h3 class="modal-title">Edit Pengguna</h3>
                <button wire:click="$set('showEditModal', false)" style="border:none;background:none;cursor:pointer;color:#adb5bd;">✕</button>
            </div>
            <form wire:submit="updateUser">
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Nama Lengkap *</label>
                            <input wire:model="name" type="text" class="form-input @error('name') form-input-error @enderror">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Username</label>
                            <input wire:model="username" type="text" class="form-input @error('username') form-input-error @enderror" placeholder="contoh: budi123">
                            @error('username') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Email *</label>
                            <input wire:model="email" type="email" class="form-input @error('email') form-input-error @enderror">
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">No. Telepon / WhatsApp</label>
                            <input wire:model="phone" type="text" class="form-input">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Peran (Role) *</label>
                            <select wire:model="role" class="form-select @error('role') form-input-error @enderror">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div style="display:flex;align-items:center;padding-top:24px;gap:8px;">
                            <input wire:model="is_active" type="checkbox" id="edit_active" style="width:18px;height:18px;">
                            <label for="edit_active" style="font-size:13px;color:#495057;cursor:pointer;">Status Aktif</label>
                        </div>
                    </div>
                    <div style="padding:12px;background:#f8f9fa;border-radius:8px;border:1px dashed #ced4da;">
                        <div style="font-size:12px;font-weight:600;color:#74788d;margin-bottom:8px;">Ganti Password (Kosongkan jika tidak ingin mengubah)</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label class="form-label">Password Baru</label>
                                <input wire:model="password" type="password" class="form-input @error('password') form-input-error @enderror" placeholder="Min. 8 karakter">
                                @error('password') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label">Konfirmasi Password</label>
                                <input wire:model="password_confirmation" type="password" class="form-input" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="$set('showEditModal', false)" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="modal-overlay">
        <div class="modal-box" style="max-width:420px;">
            <div class="modal-body" style="text-align:center;padding:36px 24px;">
                <div style="width:60px;height:60px;border-radius:50%;background:rgba(244,106,106,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#f46a6a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h4 style="font-size:16px;font-weight:700;color:#343a40;margin-bottom:8px;">Hapus Pengguna?</h4>
                <p style="font-size:13.5px;color:#74788d;">Pengguna yang dihapus tidak akan dapat masuk kembali ke dalam sistem SIMVENTRA.</p>
            </div>
            <div class="modal-footer" style="justify-content:center;gap:12px;">
                <button wire:click="$set('showDeleteModal', false)" class="btn btn-secondary">Batal</button>
                <button wire:click="deleteUser" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
