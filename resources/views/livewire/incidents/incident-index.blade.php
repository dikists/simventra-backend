<div>
    <!-- Flash Messages -->
    @if(session()->has('success'))
    <div style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:12px 16px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:13.5px;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="page-header">
        <div>
            <h1 class="page-title">Log Insiden & Keamanan Operasional</h1>
            <p class="page-subtitle">Pencatatan insiden keselamatan, kontaminasi kargo, kecelakaan, dan tindak lanjut eskalasi PTT.FM.043.02</p>
        </div>
        @can('manage incidents')
        <button wire:click="openCreateModal" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Lapor Insiden Baru
        </button>
        @endcan
    </div>

    <!-- Summary Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px;">
        <div style="background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #edf2f9;box-shadow:0 2px 4px rgba(0,0,0,.02);border-left:4px solid #f1b44c;">
            <div style="font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;">Insiden Terbuka (Open)</div>
            <div style="font-size:24px;font-weight:800;color:#f1b44c;margin-top:6px;">{{ $openCount }} Kasus</div>
            <div style="font-size:12px;color:#98a6ad;margin-top:4px;">Menunggu penanganan</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #edf2f9;box-shadow:0 2px 4px rgba(0,0,0,.02);border-left:4px solid #50a5f1;">
            <div style="font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;">Sedang Investigasi</div>
            <div style="font-size:24px;font-weight:800;color:#50a5f1;margin-top:6px;">{{ $investigatingCount }} Kasus</div>
            <div style="font-size:12px;color:#98a6ad;margin-top:4px;">Dalam tahap klarifikasi/tindakan</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #edf2f9;box-shadow:0 2px 4px rgba(0,0,0,.02);border-left:4px solid #f46a6a;">
            <div style="font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;">Kasus Kritis Aktif</div>
            <div style="font-size:24px;font-weight:800;color:#f46a6a;margin-top:6px;">{{ $criticalCount }} Kasus</div>
            <div style="font-size:12px;color:#98a6ad;margin-top:4px;">Tingkat bahaya tinggi / kritis</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #edf2f9;box-shadow:0 2px 4px rgba(0,0,0,.02);border-left:4px solid #34c38f;">
            <div style="font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;">Telah Ditangani</div>
            <div style="font-size:24px;font-weight:800;color:#34c38f;margin-top:6px;">{{ $resolvedCount }} Kasus</div>
            <div style="font-size:12px;color:#98a6ad;margin-top:4px;">Status Resolved / Closed</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
        <div class="search-box" style="flex:1;min-width:240px;">
            <input wire:model.live.debounce.300ms="search" type="text"
                class="form-input" placeholder="Cari nomor insiden, judul, lokasi, sopir...">
        </div>
        <select wire:model.live="filterType" class="form-select" style="width:160px;">
            <option value="">Semua Tipe</option>
            <option value="keamanan">Keamanan / Pembajakan</option>
            <option value="kecelakaan">Kecelakaan Lalu Lintas</option>
            <option value="kontaminasi">Kontaminasi Kargo</option>
            <option value="pelanggaran">Pelanggaran SOP / Rute</option>
            <option value="lainnya">Lainnya</option>
        </select>
        <select wire:model.live="filterSeverity" class="form-select" style="width:160px;">
            <option value="">Semua Tingkat</option>
            <option value="low">Rendah (Low)</option>
            <option value="medium">Sedang (Medium)</option>
            <option value="high">Tinggi (High)</option>
            <option value="critical">Kritis (Critical)</option>
        </select>
        <select wire:model.live="filterStatus" class="form-select" style="width:160px;">
            <option value="">Semua Status</option>
            <option value="open">Open (Baru)</option>
            <option value="investigating">Investigating</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
        </select>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="card-title-icon" style="background:rgba(244,106,106,.1);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#f46a6a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="card-title">Daftar Insiden Operasional</div>
            </div>
            <div style="font-size:12.5px;color:#adb5bd;">{{ $incidents->total() }} insiden</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Insiden & Tanggal</th>
                        <th>Judul & Tipe</th>
                        <th>Tingkat Keparahan</th>
                        <th>Kendaraan & Sopir</th>
                        <th>Status</th>
                        <th>Pelapor</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidents as $item)
                    <tr>
                        <td>
                            <div style="font-weight:700;font-size:13.5px;font-family:monospace;color:#556ee6;">{{ $item->incident_number }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;margin-top:2px;">
                                {{ \Carbon\Carbon::parse($item->occurred_at)->format('d M Y H:i') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:700;font-size:13.5px;color:#343a40;">{{ $item->title }}</div>
                            <div style="font-size:11.5px;color:#74788d;margin-top:2px;">
                                Tipe: <b style="text-transform:capitalize;">{{ $item->type }}</b>
                                @if($item->location) &bull; 📍 {{ Str::limit($item->location, 30) }} @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $sevBadge = match($item->severity) {
                                    'critical' => 'background:#fee2e2;color:#dc2626;border:1px solid #fecaca;',
                                    'high'     => 'background:#ffedd5;color:#ea580c;border:1px solid #fed7aa;',
                                    'medium'   => 'background:#fef9c3;color:#ca8a04;border:1px solid #fef08a;',
                                    'low'      => 'background:#e0f2fe;color:#0284c7;border:1px solid #bae6fd;',
                                    default    => 'background:#f1f5f9;color:#64748b;'
                                };
                            @endphp
                            <span style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:6px;display:inline-block;text-transform:uppercase;{{ $sevBadge }}">
                                {{ $item->severity }}
                            </span>
                        </td>
                        <td>
                            @if($item->vehicle)
                                <div style="font-weight:600;font-size:13px;font-family:monospace;color:#343a40;">{{ $item->vehicle->license_plate }}</div>
                            @endif
                            <div style="font-size:12px;color:#64748b;">{{ $item->driver?->name ?? 'Tidak ada unit terkait' }}</div>
                        </td>
                        <td>
                            @php
                                $statusBadge = match($item->status) {
                                    'open'          => 'background:#fef9c3;color:#ca8a04;',
                                    'investigating' => 'background:#e0f2fe;color:#0284c7;',
                                    'resolved'      => 'background:#dcfce7;color:#16a34a;',
                                    'closed'        => 'background:#f1f5f9;color:#64748b;',
                                    default         => 'background:#f1f5f9;color:#64748b;'
                                };
                            @endphp
                            <span style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:6px;display:inline-block;{{ $statusBadge }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size:12.5px;color:#495057;">{{ $item->reporter?->name ?? 'Sistem / Sopir' }}</div>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                <button wire:click="openDetail({{ $item->id }})"
                                    style="padding:6px 12px;font-size:12px;font-weight:600;background:#556ee6;color:#fff;border-radius:6px;border:none;cursor:pointer;">
                                    Tindak Lanjut
                                </button>
                                @can('manage incidents')
                                <button wire:click="delete({{ $item->id }})"
                                    wire:confirm="Hapus catatan insiden ini?"
                                    style="padding:6px 10px;font-size:12px;font-weight:600;background:#fee2e2;color:#dc2626;border-radius:6px;border:none;cursor:pointer;">
                                    Hapus
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:36px;color:#adb5bd;">
                            Belum ada laporan insiden yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px;">
            {{ $incidents->links() }}
        </div>
    </div>

    <!-- Modal Form Lapor Insiden -->
    @if($showModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:999;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 25px rgba(0,0,0,.15);">
            <div style="padding:16px 20px;border-bottom:1px solid #edf2f9;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:16px;font-weight:700;color:#343a40;margin:0;">Lapor Insiden Keamanan & Operasional</h3>
                <button wire:click="$set('showModal', false)" style="background:none;border:none;font-size:20px;cursor:pointer;color:#adb5bd;">&times;</button>
            </div>
            <form wire:submit="save" style="padding:20px;">
                <div style="margin-bottom:14px;">
                    <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Judul Singkat Insiden *</label>
                    <input wire:model="title" type="text" class="form-input" placeholder="Misal: Truk tersenggol kendaraan lain di tol Cikampek" required>
                    @error('title') <span style="color:#ef4444;font-size:12px;">{{ $message }}</span> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Tipe Insiden *</label>
                        <select wire:model="type" class="form-select" required>
                            <option value="kecelakaan">Kecelakaan Lalu Lintas</option>
                            <option value="keamanan">Keamanan / Upaya Pencurian</option>
                            <option value="kontaminasi">Kontaminasi Kargo / Non-Halal</option>
                            <option value="pelanggaran">Pelanggaran SOP Sopir</option>
                            <option value="lainnya">Lain-lain</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Tingkat Keparahan *</label>
                        <select wire:model="severity" class="form-select" required>
                            <option value="low">Rendah (Kerusakan minor / lecet)</option>
                            <option value="medium">Sedang (Perlu perbaikan segera)</option>
                            <option value="high">Tinggi (Kargo rusak / tertunda lama)</option>
                            <option value="critical">Kritis (Korban jiwa / ancaman keamanan)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Waktu Terjadi *</label>
                        <input wire:model="occurred_at" type="datetime-local" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Lokasi Kejadian</label>
                        <input wire:model="location" type="text" class="form-input" placeholder="KM 32 Tol Jakarta-Cikampek">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Kendaraan Terkait</label>
                        <select wire:model="vehicle_id" class="form-select">
                            <option value="">-- Pilih Kendaraan (Opsional) --</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->license_plate }} ({{ $v->brand }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Sopir / Personel Terkait</label>
                        <select wire:model="driver_id" class="form-select">
                            <option value="">-- Pilih Sopir (Opsional) --</option>
                            @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Uraian Kronologi Kejadian *</label>
                    <textarea wire:model="description" class="form-input" rows="3" placeholder="Jelaskan secara rinci apa yang terjadi, kondisi armada, muatan, dan pihak yang terlibat" required></textarea>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Foto Bukti Kejadian</label>
                    <input wire:model="photo" type="file" accept="image/*" class="form-input" style="padding:5px;">
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #edf2f9;padding-top:16px;">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Laporan Insiden</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Detail & Tindak Lanjut -->
    @if($showDetailModal && $selectedIncident)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:999;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:720px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 10px 25px rgba(0,0,0,.15);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #edf2f9;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
                <div>
                    <span style="font-family:monospace;font-size:12px;font-weight:700;color:#556ee6;">{{ $selectedIncident->incident_number }}</span>
                    <h3 style="font-size:16px;font-weight:700;color:#343a40;margin:2px 0 0 0;">{{ $selectedIncident->title }}</h3>
                </div>
                <button wire:click="$set('showDetailModal', false)" style="background:none;border:none;font-size:22px;cursor:pointer;color:#adb5bd;">&times;</button>
            </div>

            @if(session()->has('detail_success'))
            <div style="margin:12px 20px 0 20px;background:#d4edda;color:#155724;padding:8px 14px;border-radius:8px;font-size:13px;">
                {{ session('detail_success') }}
            </div>
            @endif

            <div style="overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:18px;">
                <!-- Ringkasan Kasus -->
                <div style="background:#f8fafc;border-radius:10px;padding:14px;border:1px solid #e2e8f0;display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12.5px;">
                    <div><b>Waktu Kejadian:</b> {{ \Carbon\Carbon::parse($selectedIncident->occurred_at)->format('d M Y H:i') }}</div>
                    <div><b>Tipe:</b> <span style="text-transform:capitalize;">{{ $selectedIncident->type }}</span></div>
                    <div><b>Tingkat Keparahan:</b> <span style="font-weight:700;text-transform:uppercase;">{{ $selectedIncident->severity }}</span></div>
                    <div><b>Status Saat Ini:</b> <span style="font-weight:700;">{{ ucfirst($selectedIncident->status) }}</span></div>
                    <div><b>Armada:</b> {{ $selectedIncident->vehicle?->license_plate ?? '-' }}</div>
                    <div><b>Sopir:</b> {{ $selectedIncident->driver?->name ?? '-' }}</div>
                    <div><b>Lokasi:</b> {{ $selectedIncident->location ?? '-' }}</div>
                    <div><b>Dilaporkan Oleh:</b> {{ $selectedIncident->reporter?->name ?? 'Sistem' }}</div>
                </div>

                <div>
                    <b style="font-size:13px;color:#334155;">Kronologi Kejadian:</b>
                    <p style="font-size:13px;color:#475569;margin-top:6px;line-height:1.6;background:#fff;padding:12px;border-radius:8px;border:1px solid #edf2f9;">
                        {{ $selectedIncident->description }}
                    </p>
                </div>

                @if($selectedIncident->photo)
                <div>
                    <b style="font-size:13px;color:#334155;">Foto Bukti Kejadian:</b>
                    <div style="margin-top:6px;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">
                        <img src="{{ Storage::url($selectedIncident->photo) }}" alt="Foto Insiden" style="width:100%;max-height:240px;object-fit:cover;">
                    </div>
                </div>
                @endif

                <!-- Histori Tindak Lanjut -->
                <div>
                    <b style="font-size:13px;color:#334155;">Audit Trail & Tindak Lanjut Penanganan:</b>
                    <div style="margin-top:8px;display:flex;flex-direction:column;gap:10px;">
                        @forelse($selectedIncident->followups as $fl)
                        <div style="background:#f1f5f9;border-radius:8px;padding:10px 14px;font-size:12.5px;border-left:3px solid #556ee6;">
                            <div style="display:flex;justify-content:space-between;color:#64748b;font-size:11.5px;margin-bottom:4px;">
                                <span>👤 <b>{{ $fl->user?->name ?? 'Petugas' }}</b></span>
                                <span>{{ $fl->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div style="color:#1e293b;font-weight:500;">{{ $fl->action_taken }}</div>
                            @if($fl->status_change)
                            <div style="margin-top:4px;font-size:11px;color:#556ee6;">
                                Status diubah menjadi: <b>{{ ucfirst($fl->status_change) }}</b>
                            </div>
                            @endif
                        </div>
                        @empty
                        <div style="font-size:12px;color:#94a3b8;font-style:italic;">Belum ada tindakan penanganan yang dicatat.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Form Tambah Tindak Lanjut -->
                @can('manage incidents')
                <div style="border-top:1px solid #edf2f9;padding-top:14px;">
                    <b style="font-size:13px;color:#334155;">Catat Tindakan Penanganan Baru:</b>
                    <div style="margin-top:8px;display:flex;flex-direction:column;gap:10px;">
                        <textarea wire:model="followupAction" class="form-input" rows="2" placeholder="Tindakan yang diambil (misal: Menghubungi pihak derek, mengganti unit armada, mediasi dengan pihak lawan)"></textarea>
                        @error('followupAction') <span style="color:#ef4444;font-size:12px;">{{ $message }}</span> @enderror

                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <label style="font-size:12.5px;font-weight:600;color:#334155;">Ubah Status:</label>
                                <select wire:model="followupStatus" class="form-select" style="width:160px;font-size:12px;">
                                    <option value="open">Open (Baru)</option>
                                    <option value="investigating">Investigating</option>
                                    <option value="resolved">Resolved (Selesai)</option>
                                    <option value="closed">Closed (Tutup Kasus)</option>
                                </select>
                            </div>
                            <button wire:click="addFollowup" class="btn btn-primary" style="padding:6px 16px;font-size:12.5px;">
                                Simpan Tindakan
                            </button>
                        </div>
                    </div>
                </div>
                @endcan
            </div>

            <div style="padding:14px 20px;border-top:1px solid #edf2f9;display:flex;justify-content:flex-end;background:#f8fafc;">
                <button type="button" wire:click="$set('showDetailModal', false)" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
    @endif
</div>
