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
            <h1 class="page-title">Maintenance & Perawatan Armada</h1>
            <p class="page-subtitle">Pencatatan riwayat servis berkala, biaya perbaikan, dan jadwal preventive maintenance PTT.FM.043.02</p>
        </div>
        @can('manage maintenance')
        <div style="display:flex;gap:10px;">
            <button wire:click="openScheduleModal" class="btn btn-secondary">
                + Jadwal Baru
            </button>
            <button wire:click="openLogModal()" class="btn btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Servis / Perbaikan
            </button>
        </div>
        @endcan
    </div>

    <!-- Summary Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px;">
        <div style="background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #edf2f9;box-shadow:0 2px 4px rgba(0,0,0,.02);border-left:4px solid #556ee6;">
            <div style="font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;">Biaya Servis Bulan Ini</div>
            <div style="font-size:22px;font-weight:800;color:#343a40;margin-top:6px;">Rp {{ number_format($currentMonthCost, 0, ',', '.') }}</div>
            <div style="font-size:12px;color:#98a6ad;margin-top:4px;">Periode {{ now()->format('F Y') }}</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #edf2f9;box-shadow:0 2px 4px rgba(0,0,0,.02);border-left:4px solid #f46a6a;">
            <div style="font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;">Jadwal Perlu Servis</div>
            <div style="font-size:22px;font-weight:800;color:#f46a6a;margin-top:6px;">{{ $dueSchedulesCount }} Unit</div>
            <div style="font-size:12px;color:#98a6ad;margin-top:4px;">Mencapai batas KM / Waktu</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #edf2f9;box-shadow:0 2px 4px rgba(0,0,0,.02);border-left:4px solid #f1b44c;">
            <div style="font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;">Unit Di Bengkel</div>
            <div style="font-size:22px;font-weight:800;color:#f1b44c;margin-top:6px;">{{ $inMaintenanceCount }} Unit</div>
            <div style="font-size:12px;color:#98a6ad;margin-top:4px;">Status unit: Maintenance</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #edf2f9;box-shadow:0 2px 4px rgba(0,0,0,.02);border-left:4px solid #34c38f;">
            <div style="font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;">Servis Selesai</div>
            <div style="font-size:22px;font-weight:800;color:#34c38f;margin-top:6px;">{{ $monthlyCompletedCount }} Kali</div>
            <div style="font-size:12px;color:#98a6ad;margin-top:4px;">Pekerjaan beres bulan ini</div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div style="display:flex;gap:12px;border-bottom:2px solid #e2e8f0;margin-bottom:20px;">
        <button wire:click="$set('activeTab', 'logs')"
            style="padding:10px 18px;font-weight:700;font-size:14px;background:none;border:none;cursor:pointer;border-bottom:3px solid {{ $activeTab === 'logs' ? '#556ee6' : 'transparent' }};color:{{ $activeTab === 'logs' ? '#556ee6' : '#64748b' }};margin-bottom:-2px;">
            🛠️ Riwayat Servis & Bengkel
        </button>
        <button wire:click="$set('activeTab', 'schedules')"
            style="padding:10px 18px;font-weight:700;font-size:14px;background:none;border:none;cursor:pointer;border-bottom:3px solid {{ $activeTab === 'schedules' ? '#556ee6' : 'transparent' }};color:{{ $activeTab === 'schedules' ? '#556ee6' : '#64748b' }};margin-bottom:-2px;">
            📅 Jadwal Preventive Maintenance
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
        <div class="search-box" style="flex:1;min-width:240px;">
            <input wire:model.live.debounce.300ms="search" type="text"
                class="form-input" placeholder="Cari plat nomor, bengkel, tipe servis...">
        </div>
        <select wire:model.live="filterVehicle" class="form-select" style="width:200px;">
            <option value="">Semua Kendaraan</option>
            @foreach($vehicles as $v)
            <option value="{{ $v->id }}">{{ $v->license_plate }} ({{ $v->brand }})</option>
            @endforeach
        </select>
    </div>

    @if($activeTab === 'logs')
    <!-- Table Logs -->
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="card-title-icon" style="background:rgba(85,110,230,.1);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#556ee6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                </div>
                <div class="card-title">Riwayat Pelaksanaan Servis & Perbaikan</div>
            </div>
            <div style="font-size:12.5px;color:#adb5bd;">{{ $logs->total() }} catatan servis</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kendaraan</th>
                        <th>Tanggal Servis</th>
                        <th>Tipe Perawatan</th>
                        <th>Bengkel & Mekanik</th>
                        <th>Odometer</th>
                        <th>Biaya Perbaikan</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $item)
                    <tr>
                        <td>
                            <div style="font-weight:700;font-size:14px;font-family:monospace;color:#343a40;">{{ $item->vehicle?->license_plate }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;">{{ $item->vehicle?->brand }} {{ $item->vehicle?->model }}</div>
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:13px;color:#343a40;">{{ \Carbon\Carbon::parse($item->maintenance_date)->format('d M Y') }}</div>
                        </td>
                        <td>
                            <span style="font-size:12px;font-weight:600;padding:3px 8px;border-radius:6px;background:#edf2f7;color:#4a5568;text-transform:capitalize;">
                                {{ str_replace('_', ' ', $item->maintenance_type) }}
                            </span>
                            @if($item->description)
                            <div style="font-size:11px;color:#74788d;margin-top:2px;">{{ Str::limit($item->description, 35) }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:600;color:#343a40;">{{ $item->workshop ?: 'Bengkel Internal' }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;">{{ $item->mechanic ? 'Mekanik: '.$item->mechanic : '-' }}</div>
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:600;color:#343a40;">{{ $item->odometer_km ? number_format($item->odometer_km, 0, ',', '.') . ' KM' : '-' }}</div>
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:700;color:#16a34a;">Rp {{ number_format($item->cost, 0, ',', '.') }}</div>
                        </td>
                        <td>
                            @php
                                $statusStyle = match($item->status) {
                                    'completed' => 'background:#dcfce7;color:#16a34a;',
                                    'pending' => 'background:#fef9c3;color:#ca8a04;',
                                    'cancelled' => 'background:#fee2e2;color:#dc2626;',
                                    default => 'background:#f1f5f9;color:#64748b;'
                                };
                            @endphp
                            <span style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:6px;display:inline-block;{{ $statusStyle }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                <button wire:click="openDetailLog({{ $item->id }})"
                                    style="padding:6px 10px;font-size:12px;font-weight:600;background:#eff2f7;color:#495057;border-radius:6px;border:none;cursor:pointer;">
                                    Detail
                                </button>
                                @can('manage maintenance')
                                <button wire:click="deleteLog({{ $item->id }})"
                                    wire:confirm="Hapus catatan servis ini?"
                                    style="padding:6px 10px;font-size:12px;font-weight:600;background:#fee2e2;color:#dc2626;border-radius:6px;border:none;cursor:pointer;">
                                    Hapus
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:36px;color:#adb5bd;">
                            Belum ada riwayat pelaksanaan servis tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px;">
            {{ $logs->links() }}
        </div>
    </div>
    @else
    <!-- Table Schedules -->
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="card-title-icon" style="background:rgba(52,195,143,.1);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#34c38f"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="card-title">Jadwal Preventive Maintenance Terdaftar</div>
            </div>
            <div style="font-size:12.5px;color:#adb5bd;">{{ $schedules->count() }} aturan jadwal</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kendaraan</th>
                        <th>Tipe Perawatan</th>
                        <th>Interval Pemicu</th>
                        <th>Terakhir Servis</th>
                        <th>Jatuh Tempo Berikutnya</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $sched)
                    @php
                        $isDue = $sched->isDue($sched->vehicle?->current_odometer_km ?? 0);
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:700;font-size:14px;font-family:monospace;color:#343a40;">{{ $sched->vehicle?->license_plate }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;">Odo Saat Ini: {{ number_format($sched->vehicle?->current_odometer_km ?? 0, 0, ',', '.') }} KM</div>
                        </td>
                        <td>
                            <span style="font-weight:600;font-size:13px;color:#343a40;text-transform:capitalize;">
                                {{ str_replace('_', ' ', $sched->maintenance_type) }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size:12.5px;color:#495057;">
                                @if($sched->trigger_type === 'km')
                                    Tiap <b>{{ number_format($sched->trigger_km, 0, ',', '.') }} KM</b>
                                @elseif($sched->trigger_type === 'waktu')
                                    Tiap <b>{{ $sched->trigger_days }} Hari</b>
                                @else
                                    Kombinasi KM / Hari
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12.5px;color:#343a40;">
                                {{ $sched->last_done_date ? \Carbon\Carbon::parse($sched->last_done_date)->format('d M Y') : '-' }}
                            </div>
                            <div style="font-size:11px;color:#adb5bd;">
                                {{ $sched->last_done_km ? number_format($sched->last_done_km, 0, ',', '.') . ' KM' : '' }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12.5px;font-weight:700;color:{{ $isDue ? '#dc2626' : '#343a40' }};">
                                {{ $sched->next_due_date ? \Carbon\Carbon::parse($sched->next_due_date)->format('d M Y') : '-' }}
                            </div>
                            <div style="font-size:11.5px;font-weight:600;color:{{ $isDue ? '#dc2626' : '#64748b' }};">
                                {{ $sched->next_due_km ? number_format($sched->next_due_km, 0, ',', '.') . ' KM' : '' }}
                            </div>
                        </td>
                        <td>
                            @if($isDue)
                                <span style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:6px;background:#fee2e2;color:#dc2626;display:inline-block;">
                                    ⚠️ Perlu Servis (Due)
                                </span>
                            @else
                                <span style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:6px;background:#dcfce7;color:#16a34a;display:inline-block;">
                                    🟢 Normal (Aman)
                                </span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                @can('manage maintenance')
                                <button wire:click="openLogModal({{ $sched->id }})"
                                    style="padding:6px 12px;font-size:12px;font-weight:600;background:#556ee6;color:#fff;border-radius:6px;border:none;cursor:pointer;">
                                    Servis Sekarang
                                </button>
                                <button wire:click="deleteSchedule({{ $sched->id }})"
                                    wire:confirm="Hapus jadwal preventive maintenance ini?"
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
                            Belum ada aturan jadwal preventive maintenance yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Modal Catat Servis (Log) -->
    @if($showLogModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:999;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 25px rgba(0,0,0,.15);">
            <div style="padding:16px 20px;border-bottom:1px solid #edf2f9;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:16px;font-weight:700;color:#343a40;margin:0;">Catat Riwayat Servis / Perbaikan</h3>
                <button wire:click="$set('showLogModal', false)" style="background:none;border:none;font-size:20px;cursor:pointer;color:#adb5bd;">&times;</button>
            </div>
            <form wire:submit="saveLog" style="padding:20px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Kendaraan *</label>
                        <select wire:model="vehicle_id" class="form-select" required>
                            <option value="">-- Pilih Kendaraan --</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->license_plate }} ({{ $v->brand }})</option>
                            @endforeach
                        </select>
                        @error('vehicle_id') <span style="color:#ef4444;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Tanggal Servis *</label>
                        <input wire:model="maintenance_date" type="date" class="form-input" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Tipe Perawatan *</label>
                        <select wire:model="maintenance_type" class="form-select" required>
                            <option value="oli_mesin">Ganti Oli Mesin & Filter</option>
                            <option value="servis_berkala">Servis Berkala (Tune Up)</option>
                            <option value="ban">Pergantian / Rotasi Ban</option>
                            <option value="rem">Pemeriksaan Rem / Kampas</option>
                            <option value="ac">Servis AC & Kebersihan Kabin</option>
                            <option value="kelistrikan">Kelistrikan & Aki</option>
                            <option value="perbaikan_berat">Perbaikan Berat (Turun Mesin/Kopling)</option>
                            <option value="lainnya">Lain-lain</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Biaya Total (Rp) *</label>
                        <input wire:model="cost" type="number" min="0" class="form-input" placeholder="0" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Nama Bengkel</label>
                        <input wire:model="workshop" type="text" class="form-input" placeholder="Bengkel Resmi / Rekanan">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Odometer (KM)</label>
                        <input wire:model="odometer_km" type="number" step="0.1" class="form-input" placeholder="KM saat servis">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Spare Part yang Diganti</label>
                    <input wire:model="parts_replaced" type="text" class="form-input" placeholder="Misal: Oli Shell 10W-40 4L, Filter Oli, Kampas Rem Depan">
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Deskripsi Pekerjaan / Keluhan</label>
                    <textarea wire:model="description" class="form-input" rows="2" placeholder="Catatan teknis perbaikan"></textarea>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Foto Kwitansi / Faktur Pembayaran</label>
                    <input wire:model="receipt_photo" type="file" accept="image/*" class="form-input" style="padding:5px;">
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #edf2f9;padding-top:16px;">
                    <button type="button" wire:click="$set('showLogModal', false)" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Catatan Servis</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Form Schedule -->
    @if($showScheduleModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:999;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:520px;box-shadow:0 10px 25px rgba(0,0,0,.15);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #edf2f9;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:16px;font-weight:700;color:#343a40;margin:0;">Buat Jadwal Preventive Maintenance</h3>
                <button wire:click="$set('showScheduleModal', false)" style="background:none;border:none;font-size:20px;cursor:pointer;color:#adb5bd;">&times;</button>
            </div>
            <form wire:submit="saveSchedule" style="padding:20px;">
                <div style="margin-bottom:14px;">
                    <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Kendaraan *</label>
                    <select wire:model="sched_vehicle_id" class="form-select" required>
                        <option value="">-- Pilih Kendaraan --</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->license_plate }} ({{ $v->brand }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Tipe Perawatan *</label>
                        <select wire:model="sched_maintenance_type" class="form-select" required>
                            <option value="oli_mesin">Ganti Oli Mesin</option>
                            <option value="servis_berkala">Servis Berkala (Tune Up)</option>
                            <option value="ban">Cek & Rotasi Ban</option>
                            <option value="rem">Pemeriksaan Rem</option>
                            <option value="ac">Servis AC</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Metode Pemicu *</label>
                        <select wire:model="sched_trigger_type" class="form-select" required>
                            <option value="km">Berdasarkan Jarak (KM)</option>
                            <option value="waktu">Berdasarkan Waktu (Hari)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Interval KM</label>
                        <input wire:model="sched_trigger_km" type="number" class="form-input" placeholder="Contoh: 5000">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Interval Hari</label>
                        <input wire:model="sched_trigger_days" type="number" class="form-input" placeholder="Contoh: 90">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">KM Terakhir Servis</label>
                        <input wire:model="sched_last_km" type="number" class="form-input" placeholder="KM awal">
                    </div>
                    <div>
                        <label class="form-label" style="font-weight:600;font-size:13px;display:block;margin-bottom:5px;">Tanggal Terakhir Servis</label>
                        <input wire:model="sched_last_date" type="date" class="form-input">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #edf2f9;padding-top:16px;">
                    <button type="button" wire:click="$set('showScheduleModal', false)" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Detail Log -->
    @if($showDetailModal && $selectedLog)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:999;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:540px;box-shadow:0 10px 25px rgba(0,0,0,.15);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #edf2f9;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:16px;font-weight:700;color:#343a40;margin:0;">Detail Servis: {{ $selectedLog->vehicle?->license_plate }}</h3>
                <button wire:click="$set('showDetailModal', false)" style="background:none;border:none;font-size:20px;cursor:pointer;color:#adb5bd;">&times;</button>
            </div>
            <div style="padding:20px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;font-size:13px;">
                    <div><b>Tanggal:</b> {{ \Carbon\Carbon::parse($selectedLog->maintenance_date)->format('d M Y') }}</div>
                    <div><b>Tipe:</b> {{ ucfirst(str_replace('_', ' ', $selectedLog->maintenance_type)) }}</div>
                    <div><b>Bengkel:</b> {{ $selectedLog->workshop ?? '-' }}</div>
                    <div><b>Biaya:</b> <span style="color:#16a34a;font-weight:700;">Rp {{ number_format($selectedLog->cost, 0, ',', '.') }}</span></div>
                    <div><b>Odometer:</b> {{ $selectedLog->odometer_km ? number_format($selectedLog->odometer_km, 0, ',', '.') . ' KM' : '-' }}</div>
                    <div><b>Status:</b> {{ ucfirst($selectedLog->status) }}</div>
                </div>

                @if($selectedLog->parts_replaced)
                <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:16px;font-size:12.5px;">
                    <b>Spare Part Diganti:</b>
                    <p style="color:#334155;margin-top:4px;">{{ $selectedLog->parts_replaced }}</p>
                </div>
                @endif

                @if($selectedLog->description)
                <div style="margin-bottom:16px;font-size:13px;">
                    <b>Catatan Pekerjaan:</b>
                    <p style="color:#64748b;margin-top:4px;">{{ $selectedLog->description }}</p>
                </div>
                @endif

                @if($selectedLog->receipt_photo)
                <div style="margin-bottom:16px;">
                    <b>Foto Kwitansi / Faktur Pembayaran:</b>
                    <div style="margin-top:6px;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">
                        <img src="{{ Storage::url($selectedLog->receipt_photo) }}" alt="Kwitansi" style="width:100%;max-height:260px;object-fit:contain;background:#f8fafc;">
                    </div>
                </div>
                @endif

                <div style="display:flex;justify-content:flex-end;border-top:1px solid #edf2f9;padding-top:14px;">
                    <button type="button" wire:click="$set('showDetailModal', false)" class="btn btn-secondary">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
