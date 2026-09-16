<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">Kendaraan</h1>
            <p class="page-subtitle">Kelola data armada kendaraan operasional</p>
        </div>
        @can('create vehicles')
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kendaraan
        </a>
        @endcan
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="search-box" style="flex:1;min-width:220px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                class="form-input" placeholder="Cari plat nomor, merek, kode...">
        </div>
        <select wire:model.live="filterStatus" class="form-select" style="width:160px;">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="maintenance">Maintenance</option>
            <option value="inactive">Tidak Aktif</option>
            <option value="scrapped">Scrap</option>
        </select>
        <label style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:8px;border:1px solid #ced4da;cursor:pointer;font-size:13px;font-weight:600;color:#495057;background:#fff;white-space:nowrap;">
            <input wire:model.live="filterHalal" type="checkbox" style="width:15px;height:15px;border-radius:4px;accent-color:#34c38f;">
            <span style="color:#34c38f;">🟢</span> Halal Dedicated
        </label>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="card-title-icon" style="background:rgba(52,195,143,.1);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#34c38f"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div class="card-title">Daftar Armada Kendaraan</div>
            </div>
            <div style="font-size:12.5px;color:#adb5bd;">{{ $vehicles->total() }} kendaraan</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kendaraan</th>
                        <th>Jenis</th>
                        <th>Kapasitas</th>
                        <th>Sopir Tetap</th>
                        <th>Status</th>
                        <th>Halal</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#34c38f,#20a373);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:14px;font-family:monospace;color:#343a40;letter-spacing:.5px;">{{ $vehicle->license_plate }}</div>
                                    <div style="font-size:11.5px;color:#adb5bd;">{{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px;color:#495057;">{{ $vehicle->vehicle_type }}</td>
                        <td style="font-size:13px;">
                            {{ $vehicle->max_capacity_kg ? number_format($vehicle->max_capacity_kg) . ' kg' : '–' }}
                        </td>
                        <td>
                            @if($vehicle->assignedDriver)
                            <div style="font-size:13px;font-weight:600;color:#343a40;">{{ $vehicle->assignedDriver->name }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;">{{ $vehicle->assignedDriver->employee_number }}</div>
                            @else
                            <span class="badge badge-gray">Belum Ditugaskan</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColor = match($vehicle->status) {
                                    'active' => 'green',
                                    'maintenance' => 'yellow',
                                    'inactive' => 'gray',
                                    default => 'red',
                                };
                            @endphp
                            <span class="badge badge-{{ $statusColor }}">{{ ucfirst($vehicle->status) }}</span>
                        </td>
                        <td>
                            @if($vehicle->is_halal_dedicated)
                            <span class="badge badge-green">✓ Halal</span>
                            @else
                            <span style="color:#adb5bd;font-size:13px;">–</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-soft-primary btn-sm">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @can('edit vehicles')
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-secondary btn-sm">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                                @can('delete vehicles')
                                <button wire:click="confirmDelete({{ $vehicle->id }})" class="btn btn-soft-danger btn-sm">
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
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <div class="empty-state-title">Belum ada kendaraan</div>
                                <div class="empty-state-desc">{{ $search ? 'Tidak ditemukan hasil untuk "' . $search . '"' : 'Mulai tambahkan data armada kendaraan' }}</div>
                                @can('create vehicles')
                                <a href="{{ route('vehicles.create') }}" class="btn btn-primary" style="display:inline-flex;">Tambah Kendaraan</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #eff2f7;">
            {{ $vehicles->links() }}
        </div>
        @endif
    </div>

    @if($showDeleteModal)
    <div class="modal-overlay">
        <div class="modal-box" style="max-width:420px;">
            <div class="modal-body" style="text-align:center;padding:36px 24px;">
                <div style="width:60px;height:60px;border-radius:50%;background:rgba(244,106,106,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#f46a6a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h4 style="font-size:16px;font-weight:700;color:#343a40;margin-bottom:8px;">Hapus Kendaraan?</h4>
                <p style="font-size:13.5px;color:#74788d;">Data kendaraan dan seluruh dokumen terkait akan dihapus permanen.</p>
            </div>
            <div class="modal-footer" style="justify-content:center;gap:12px;">
                <button wire:click="$set('showDeleteModal', false)" class="btn btn-secondary">Batal</button>
                <button wire:click="delete" class="btn btn-danger">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
