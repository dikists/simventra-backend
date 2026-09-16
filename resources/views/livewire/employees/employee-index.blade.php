<div>
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Karyawan & Sopir</h1>
            <p class="page-subtitle">Kelola data personalia dan sopir armada</p>
        </div>
        @can('create employees')
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Karyawan
        </a>
        @endcan
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="search-box" style="flex:1;min-width:220px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                class="form-input" placeholder="Cari nama, no. karyawan, NIK...">
        </div>
        <select wire:model.live="filterType" class="form-select" style="width:160px;">
            <option value="">Semua Tipe</option>
            <option value="karyawan">Karyawan</option>
            <option value="sopir">Sopir</option>
            <option value="mitra">Mitra</option>
        </select>
        <select wire:model.live="filterStatus" class="form-select" style="width:160px;">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Tidak Aktif</option>
            <option value="terminated">Dihentikan</option>
        </select>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="card-title-icon" style="background:rgba(85,110,230,.1);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#556ee6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="card-title">Daftar Karyawan</div>
            </div>
            <div style="font-size:12.5px;color:#adb5bd;">{{ $employees->total() }} data ditemukan</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>
                            <button wire:click="sort('employee_number')" style="background:none;border:none;cursor:pointer;font:inherit;color:inherit;display:flex;align-items:center;gap:4px;">
                                No. Karyawan
                                @if($sortBy === 'employee_number')
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDir === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </button>
                        </th>
                        <th>
                            <button wire:click="sort('name')" style="background:none;border:none;cursor:pointer;font:inherit;color:inherit;display:flex;align-items:center;gap:4px;">
                                Nama
                                @if($sortBy === 'name')
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDir === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </button>
                        </th>
                        <th>Tipe</th>
                        <th>Jabatan / Dept.</th>
                        <th>SIM Kadaluarsa</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>
                            <span style="font-family:monospace;font-size:12.5px;font-weight:600;color:#495057;background:#f8f9fa;padding:3px 8px;border-radius:4px;">{{ $employee->employee_number }}</span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#556ee6,#6f42c1);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:13.5px;color:#343a40;">{{ $employee->name }}</div>
                                    <div style="font-size:11.5px;color:#adb5bd;">{{ $employee->phone ?? 'Tidak ada telepon' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $employee->type === 'sopir' ? 'purple' : ($employee->type === 'mitra' ? 'teal' : 'blue') }}">
                                {{ ucfirst($employee->type) }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size:13px;color:#495057;">{{ $employee->position ?? '–' }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;">{{ $employee->department ?? '' }}</div>
                        </td>
                        <td>
                            @if($employee->sim_expiry)
                                @php $daysLeft = now()->diffInDays($employee->sim_expiry, false); @endphp
                                <div style="font-size:13px;">{{ $employee->sim_expiry->format('d/m/Y') }}</div>
                                @if($daysLeft <= 30)
                                <span class="badge badge-{{ $daysLeft <= 7 ? 'red' : ($daysLeft <= 14 ? 'orange' : 'yellow') }}" style="margin-top:4px;">
                                    H-{{ max(0, $daysLeft) }}
                                </span>
                                @endif
                            @else
                                <span style="color:#adb5bd;font-size:13px;">–</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $employee->status === 'active' ? 'green' : ($employee->status === 'inactive' ? 'gray' : 'red') }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                <a href="{{ route('employees.show', $employee) }}" class="btn btn-soft-primary btn-sm" title="Lihat Detail">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @can('edit employees')
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary btn-sm" title="Edit">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                                @can('delete employees')
                                <button wire:click="confirmDelete({{ $employee->id }})" class="btn btn-soft-danger btn-sm" title="Hapus">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <div class="empty-state-title">Belum ada karyawan</div>
                                <div class="empty-state-desc">{{ $search ? 'Tidak ditemukan hasil untuk pencarian "' . $search . '"' : 'Mulai tambahkan data karyawan atau sopir' }}</div>
                                @can('create employees')
                                <a href="{{ route('employees.create') }}" class="btn btn-primary" style="display:inline-flex;">Tambah Karyawan</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #eff2f7;">
            {{ $employees->links() }}
        </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="modal-overlay">
        <div class="modal-box" style="max-width:420px;">
            <div class="modal-body" style="text-align:center;padding:36px 24px;">
                <div style="width:60px;height:60px;border-radius:50%;background:rgba(244,106,106,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#f46a6a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h4 style="font-size:16px;font-weight:700;color:#343a40;margin-bottom:8px;">Hapus Data Karyawan?</h4>
                <p style="font-size:13.5px;color:#74788d;">Data yang dihapus tidak dapat dipulihkan. Yakin ingin melanjutkan?</p>
            </div>
            <div class="modal-footer" style="justify-content:center;gap:12px;">
                <button wire:click="$set('showDeleteModal', false)" class="btn btn-secondary">Batal</button>
                <button wire:click="delete" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
