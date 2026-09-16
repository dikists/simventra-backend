<div>
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Log Aktivitas Sistem</h1>
            <p class="page-subtitle">Jejak audit seluruh perubahan data, dokumen, dan aktivitas pengguna</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-5">
        <div class="card-body flex flex-wrap gap-3 items-center justify-between">
            <div class="flex-1 min-w-[240px] relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari keterangan, pengguna, atau modul..." class="form-input" style="padding-left:38px;">
                <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:#adb5bd;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div class="flex gap-3">
                <select wire:model.live="eventFilter" class="form-select" style="min-width:140px;">
                    <option value="">Semua Aksi</option>
                    <option value="created">Dibuat (Created)</option>
                    <option value="updated">Diubah (Updated)</option>
                    <option value="deleted">Dihapus (Deleted)</option>
                </select>
                <select wire:model.live="dateFilter" class="form-select" style="min-width:140px;">
                    <option value="">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="week">7 Hari Terakhir</option>
                    <option value="month">30 Hari Terakhir</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Keterangan</th>
                        <th>Entitas / Subjek</th>
                        <th style="text-align:right;">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $act)
                    @php
                        $eventColor = match($act->event) {
                            'created' => 'badge-green',
                            'updated' => 'badge-blue',
                            'deleted' => 'badge-red',
                            default   => 'badge-gray',
                        };
                    @endphp
                    <tr>
                        <td style="font-size:12.5px;color:#74788d;white-space:nowrap;">
                            <div>{{ $act->created_at->format('d/m/Y') }}</div>
                            <div style="font-size:11px;color:#adb5bd;">{{ $act->created_at->format('H:i:s') }} WIB</div>
                        </td>
                        <td>
                            @if($act->causer)
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;border-radius:6px;background:rgba(85,110,230,.12);color:#556ee6;font-weight:700;font-size:12px;display:flex;align-items:center;justify-content:center;">
                                    {{ strtoupper(substr($act->causer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:13px;color:#343a40;">{{ $act->causer->name }}</div>
                                    <div style="font-size:11px;color:#adb5bd;">{{ $act->causer->email }}</div>
                                </div>
                            </div>
                            @else
                            <span class="badge badge-gray">Sistem Otomatis</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $eventColor }}">
                                {{ ucfirst($act->event ?? 'aksi') }}
                            </span>
                        </td>
                        <td style="font-size:13px;color:#495057;max-width:280px;">
                            {{ $act->description }}
                        </td>
                        <td>
                            <span class="badge badge-teal" style="font-size:11.5px;">
                                {{ class_basename($act->subject_type ?? 'Sistem') }}
                                @if($act->subject_id) #{{ $act->subject_id }} @endif
                            </span>
                        </td>
                        <td style="text-align:right;">
                            @if($act->properties && $act->properties->count() > 0)
                            <button wire:click="showDetails({{ $act->id }})" class="btn-action" title="Lihat Perubahan">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            @else
                            <span style="color:#ced4da;font-size:12px;">–</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <div class="empty-state-title">Belum ada riwayat aktivitas</div>
                                <div class="empty-state-desc">Setiap operasi penambahan, perubahan, dan penghapusan data akan tercatat otomatis di sini</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #eff2f7;">
            {{ $activities->links() }}
        </div>
        @endif
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedActivity)
    <div class="modal-overlay">
        <div class="modal-box" style="max-width:560px;">
            <div class="modal-header">
                <h3 class="modal-title">Rincian Perubahan Data</h3>
                <button wire:click="$set('showDetailModal', false)" style="border:none;background:none;cursor:pointer;color:#adb5bd;">✕</button>
            </div>
            <div class="modal-body" style="max-height:480px;overflow-y:auto;">
                <div style="font-size:13px;color:#495057;margin-bottom:16px;">
                    <strong>{{ $selectedActivity->description }}</strong> oleh <strong>{{ $selectedActivity->causer?->name ?? 'Sistem' }}</strong> pada {{ $selectedActivity->created_at->translatedFormat('d F Y, H:i') }}
                </div>
                <div style="background:#f8f9fa;border-radius:8px;padding:14px;border:1px solid #eff2f7;">
                    <pre style="margin:0;font-size:12px;color:#343a40;white-space:pre-wrap;font-family:monospace;">{{ json_encode($selectedActivity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showDetailModal', false)" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
    @endif
</div>
