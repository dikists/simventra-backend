@assets
    <!-- Leaflet CSS & JS for Route Map Playback -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endassets

<div>
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Laporan & Ekspor Data</h1>
            <p class="page-subtitle">Pusat pelaporan riwayat perjalanan sopir, audit rute GPS, dan unduh data operasional SIMVENTRA</p>
        </div>
        <button wire:click="exportCsv" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Unduh CSV / Excel
        </button>
    </div>

    <!-- Tab Bar Navigasi -->
    <div class="card mb-4" style="border:none;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div class="card-body" style="padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button wire:click="setTab('trips')"
                        class="btn {{ $activeTab === 'trips' ? 'btn-primary' : 'btn-secondary' }}"
                        style="border-radius:20px;display:inline-flex;align-items:center;gap:6px;font-weight:700;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    Riwayat Perjalanan & Rute Sopir
                </button>
                <button wire:click="setTab('documents')"
                        class="btn {{ $activeTab === 'documents' ? 'btn-primary' : 'btn-secondary' }}"
                        style="border-radius:20px;display:inline-flex;align-items:center;gap:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Dokumen & Masa Berlaku
                </button>
                <button wire:click="setTab('employees')"
                        class="btn {{ $activeTab === 'employees' ? 'btn-primary' : 'btn-secondary' }}"
                        style="border-radius:20px;display:inline-flex;align-items:center;gap:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Data Personalia & Sopir
                </button>
                <button wire:click="setTab('vehicles')"
                        class="btn {{ $activeTab === 'vehicles' ? 'btn-primary' : 'btn-secondary' }}"
                        style="border-radius:20px;display:inline-flex;align-items:center;gap:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Master Armada Kendaraan
                </button>
            </div>

            @if($activeTab === 'documents')
            <div style="display:flex;gap:10px;align-items:center;">
                <select wire:model.live="docTypeFilter" class="form-select" style="min-width:140px;">
                    <option value="all">Semua Kategori</option>
                    <option value="vehicle">Kendaraan Saja</option>
                    <option value="employee">Karyawan Saja</option>
                </select>
                <select wire:model.live="expiryStatusFilter" class="form-select" style="min-width:160px;">
                    <option value="all">Semua Status</option>
                    <option value="critical_7">Kritis (H-7)</option>
                    <option value="warning_30">Mendekati Habis (H-30)</option>
                    <option value="expired">Sudah Expired</option>
                    <option value="valid">Masa Berlaku Aman</option>
                </select>
            </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: LAPORAN PERJALANAN & RUTE SOPIR (MAIN FEATURE)    -->
    <!-- ======================================================== -->
    @if($activeTab === 'trips')

        <!-- Filter Bar Khusus Perjalanan Sopir -->
        <div class="card mb-4" style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div class="card-body" style="padding:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#475569"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <h4 style="font-size:13px;font-weight:700;color:#1e293b;margin:0;text-transform:uppercase;letter-spacing:0.5px;">Filter Pencarian Perjalanan</h4>
                    </div>
                    @if($tripStartDate || $tripEndDate || $tripDriverFilter !== 'all' || $tripVehicleFilter !== 'all' || $tripStatusFilter !== 'all')
                    <button wire:click="resetTripFilters" class="btn btn-sm btn-secondary" style="font-size:11.5px;padding:3px 10px;">
                        ✕ Reset Filter
                    </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div>
                        <label style="font-size:11.5px;font-weight:600;color:#64748b;margin-bottom:4px;display:block;">Dari Tanggal</label>
                        <input type="date" wire:model.live="tripStartDate" class="form-control" style="font-size:13px;padding:6px 10px;">
                    </div>

                    <div>
                        <label style="font-size:11.5px;font-weight:600;color:#64748b;margin-bottom:4px;display:block;">Sampai Tanggal</label>
                        <input type="date" wire:model.live="tripEndDate" class="form-control" style="font-size:13px;padding:6px 10px;">
                    </div>

                    <div>
                        <label style="font-size:11.5px;font-weight:600;color:#64748b;margin-bottom:4px;display:block;">Pilih Sopir</label>
                        <select wire:model.live="tripDriverFilter" class="form-select" style="font-size:13px;padding:6px 10px;">
                            <option value="all">Semua Sopir</option>
                            @foreach($drivers as $drv)
                                <option value="{{ $drv->id }}">{{ $drv->name }} (SIM {{ $drv->sim_type ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-size:11.5px;font-weight:600;color:#64748b;margin-bottom:4px;display:block;">Pilih Armada</label>
                        <select wire:model.live="tripVehicleFilter" class="form-select" style="font-size:13px;padding:6px 10px;">
                            <option value="all">Semua Kendaraan</option>
                            @foreach($vehicles as $veh)
                                <option value="{{ $veh->id }}">{{ strtoupper($veh->license_plate) }} - {{ $veh->brand }} {{ $veh->model }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-size:11.5px;font-weight:600;color:#64748b;margin-bottom:4px;display:block;">Status Perjalanan</label>
                        <select wire:model.live="tripStatusFilter" class="form-select" style="font-size:13px;padding:6px 10px;">
                            <option value="all">Semua Status</option>
                            <option value="completed">Selesai & Masuk Gudang</option>
                            <option value="on_trip">Sedang Berjalan (On-Trip)</option>
                            <option value="confirmed">Dikonfirmasi Sopir</option>
                            <option value="assigned">Ditugaskan</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Statistik Perjalanan Hasil Filter -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;" class="stats-grid">
            <div class="stat-card" style="padding:16px;">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Total Perjalanan Terfilter</div>
                        <div class="stat-value" style="font-size:22px;">{{ number_format($tripStats['total_trips'] ?? 0) }} <span style="font-size:13px;color:#64748b;font-weight:400;">Trip</span></div>
                    </div>
                    <div class="stat-icon stat-icon-blue">
                        🚚
                    </div>
                </div>
            </div>

            <div class="stat-card" style="padding:16px;">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Total Jarak Tempuh</div>
                        <div class="stat-value" style="font-size:22px;color:#0d6efd;">{{ number_format($tripStats['total_distance'] ?? 0, 0, ',', '.') }} <span style="font-size:13px;color:#64748b;font-weight:400;">KM</span></div>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        🛣️
                    </div>
                </div>
            </div>

            <div class="stat-card" style="padding:16px;">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Sopir Bertugas</div>
                        <div class="stat-value" style="font-size:22px;">{{ number_format($tripStats['total_drivers'] ?? 0) }} <span style="font-size:13px;color:#64748b;font-weight:400;">Orang</span></div>
                    </div>
                    <div class="stat-icon stat-icon-purple">
                        👤
                    </div>
                </div>
            </div>

            <div class="stat-card" style="padding:16px;">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Titik GPS Terekam</div>
                        <div class="stat-value" style="font-size:22px;color:#10b981;">{{ number_format($tripStats['total_gps'] ?? 0) }} <span style="font-size:13px;color:#64748b;font-weight:400;">Titik</span></div>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        📍
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Perjalanan & Rute -->
        <div class="card" style="margin-bottom:24px;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;">
                <div>
                    <h3 class="card-title" style="margin:0;font-size:15px;">Daftar Perjalanan Armada & Jejak Rute Sopir</h3>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">
                        Klik tombol <strong>"Lihat Rute GPS"</strong> pada baris perjalanan untuk melihat peta rute yang dilalui supir.
                    </div>
                </div>
                <button wire:click="exportCsv" class="btn btn-sm btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Excel/CSV
                </button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Waktu Berangkat / Pulang</th>
                            <th>Sopir</th>
                            <th>Armada Kendaraan</th>
                            <th>Tujuan Pengiriman</th>
                            <th>Jarak Tempuh</th>
                            <th>Status & Kondisi</th>
                            <th style="text-align:center;width:150px;">Jejak Rute GPS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trips as $index => $trip)
                        @php
                            $duration = '-';
                            if ($trip->departure_time && $trip->return_time) {
                                $duration = $trip->departure_time->diffForHumans($trip->return_time, true);
                            } elseif ($trip->departure_time) {
                                $duration = 'Sedang Berjalan';
                            }
                        @endphp
                        <tr>
                            <td style="color:#94a3b8;font-size:12px;">{{ $trips->firstItem() + $index }}</td>
                            <td>
                                <div style="font-weight:700;color:#1e293b;font-size:13px;">
                                    {{ $trip->departure_time ? $trip->departure_time->format('d/m/Y H:i') : '–' }}
                                </div>
                                <div style="font-size:11.5px;color:#64748b;margin-top:2px;">
                                    Pulang: {{ $trip->return_time ? $trip->return_time->format('d/m/Y H:i') : '(Sedang Berjalan)' }}
                                </div>
                                <div style="font-size:11px;color:#0284c7;font-weight:600;margin-top:2px;">
                                    ⏱️ Durasi: {{ $duration }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:700;color:#0f172a;font-size:13px;">
                                    {{ $trip->driver?->name ?? '–' }}
                                </div>
                                <div style="font-size:11.5px;color:#64748b;">
                                    SIM {{ $trip->driver?->sim_type ?? '–' }} &bull; {{ $trip->driver?->phone ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div style="display:inline-block;background:#0f172a;color:#fff;padding:2px 8px;border-radius:4px;font-family:monospace;font-weight:800;font-size:12px;letter-spacing:1px;">
                                    {{ strtoupper($trip->vehicle?->license_plate ?? '-') }}
                                </div>
                                <div style="font-size:12px;color:#475569;margin-top:3px;">
                                    {{ $trip->vehicle?->brand }} {{ $trip->vehicle?->model }}
                                </div>
                                <div style="font-size:11px;color:#94a3b8;">
                                    {{ ucfirst($trip->vehicle?->vehicle_type ?? 'Armada') }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600;color:#1e293b;font-size:12.5px;max-width:280px;line-height:1.4;">
                                    📍 {{ $trip->destination ?: '– (Pengiriman reguler)' }}
                                </div>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                    Asal: {{ $trip->origin ?: 'Gudang Utama' }}
                                </div>
                                @if($trip->notes)
                                <div style="font-size:11px;color:#b45309;background:#fef3c7;padding:2px 6px;border-radius:4px;display:inline-block;margin-top:3px;">
                                    Note: {{ Str::limit($trip->notes, 40) }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($trip->distance_traveled !== null)
                                    <span style="font-weight:800;color:#0d6efd;font-size:14px;">
                                        {{ number_format($trip->distance_traveled, 0, ',', '.') }} KM
                                    </span>
                                    <div style="font-size:11px;color:#64748b;">
                                        KM: {{ number_format($trip->start_odometer, 0) }} &rarr; {{ number_format($trip->end_odometer, 0) }}
                                    </div>
                                @else
                                    <div style="font-size:12px;color:#64748b;">KM Awal: {{ number_format($trip->start_odometer, 0) }}</div>
                                    <div style="font-size:11px;color:#f59e0b;font-weight:600;">(Belum Selesai)</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $trip->status_badge_color }}">
                                    {{ $trip->status_label }}
                                </span>
                                @if($trip->vehicle_condition_on_return)
                                <div style="font-size:11px;color:#475569;margin-top:4px;">
                                    Kondisi: <strong>{{ ucfirst($trip->vehicle_condition_on_return) }}</strong>
                                </div>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <button type="button"
                                        wire:click="openRouteModal({{ $trip->id }})"
                                        class="btn btn-sm"
                                        style="background:#2563eb;color:#fff;border:none;padding:6px 12px;font-size:12px;font-weight:700;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 6px rgba(37,99,235,0.25);">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    <span>Lihat Rute GPS</span>
                                </button>
                                <div style="font-size:10.5px;color:#64748b;margin-top:4px;">
                                    @if($trip->locations_count > 0)
                                        <span style="color:#059669;font-weight:700;">✓ {{ $trip->locations_count }} titik GPS</span>
                                    @else
                                        <span style="color:#94a3b8;">Belum ada titik GPS</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:48px 16px;color:#94a3b8;">
                                <div style="font-size:36px;margin-bottom:8px;">🚚</div>
                                <div style="font-size:15px;font-weight:700;color:#334155;">Tidak Ada Data Perjalanan Ditemukan</div>
                                <div style="font-size:13px;color:#64748b;margin-top:4px;">Coba ubah rentang tanggal atau filter sopir/kendaraan di atas.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($trips->hasPages())
            <div style="padding:16px 20px;border-top:1px solid #e2e8f0;">
                {{ $trips->links() }}
            </div>
            @endif
        </div>

    @else
        <!-- ======================================================== -->
        <!-- TAB LAINNYA: DOKUMEN / KARYAWAN / KENDARAAN (EXISTING)  -->
        <!-- ======================================================== -->

        <!-- Stats Summary Row -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:24px;" class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Total Karyawan & Sopir</div>
                        <div class="stat-value">{{ number_format($stats['total_employees']) }}</div>
                    </div>
                    <div class="stat-icon stat-icon-blue">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Total Armada Kendaraan</div>
                        <div class="stat-value">{{ number_format($stats['total_vehicles']) }}</div>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Total Arsip Dokumen</div>
                        <div class="stat-value">{{ number_format($stats['total_docs']) }}</div>
                    </div>
                    <div class="stat-icon stat-icon-purple">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-top">
                    <div>
                        <div class="stat-label">Dokumen Kritis (H-7)</div>
                        <div class="stat-value" style="{{ $stats['critical_docs'] > 0 ? 'color:#f46a6a;' : '' }}">{{ number_format($stats['critical_docs']) }}</div>
                    </div>
                    <div class="stat-icon stat-icon-red">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Action Box -->
        <div class="card" style="padding:36px;text-align:center;">
            <div style="width:68px;height:68px;border-radius:18px;background:rgba(85,110,230,.1);color:#556ee6;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="34" height="34" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:6px;">
                Ekspor {{ $activeTab === 'documents' ? 'Laporan Dokumen & Masa Berlaku' : ($activeTab === 'employees' ? 'Laporan Data Personalia & Sopir' : 'Laporan Data Armada Kendaraan') }}
            </h3>
            <p style="font-size:13.5px;color:#64748b;max-width:540px;margin:0 auto 20px;line-height:1.5;">
                File akan diekspor dalam format CSV terstruktur (UTF-8 Excel Compatible) yang dapat langsung dibuka dengan rapi menggunakan Microsoft Excel atau Google Sheets.
            </p>
            <button wire:click="exportCsv" class="btn btn-primary" style="padding:10px 24px;font-size:14px;border-radius:10px;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Laporan Sekarang (.CSV)
            </button>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- MODAL POPUP: VISUALISASI PETA JEJAK RUTE GPS PERJALANAN -->
    <!-- ======================================================== -->
    @if($showRouteModal)
    <div style="position:fixed;inset:0;background:rgba(15,23,42,0.7);backdrop-filter:blur(4px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div class="card" style="width:100%;max-width:960px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,0.25);">
            
            <!-- Modal Header -->
            <div style="padding:16px 20px;background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #334155;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="background:#2563eb;color:#fff;padding:4px 10px;border-radius:8px;font-family:monospace;font-weight:800;font-size:14px;letter-spacing:1px;">
                        {{ $selectedRoute['license_plate'] ?? '-' }}
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;color:#fff;margin:0;">
                            Audit Rute: {{ $selectedRoute['driver_name'] ?? 'Sopir' }} &bull; {{ $selectedRoute['vehicle_name'] ?? 'Armada' }}
                        </h3>
                        <div style="font-size:12px;color:#94a3b8;margin-top:2px;">
                            🏁 Tujuan: <strong>{{ $selectedRoute['destination'] ?? '-' }}</strong> &bull; {{ $selectedRoute['departure_time'] ?? '-' }}
                        </div>
                    </div>
                </div>

                <button type="button"
                        wire:click="closeRouteModal"
                        style="background:transparent;border:none;color:#94a3b8;font-size:20px;cursor:pointer;padding:4px 8px;border-radius:6px;"
                        title="Tutup Peta">
                    ✕
                </button>
            </div>

            <!-- Modal Telemetry Bar -->
            <div style="background:#f8fafc;padding:10px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;font-size:12px;">
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <div>
                        <span style="color:#64748b;">Jarak Odometer:</span>
                        <strong style="color:#0f172a;margin-left:4px;">{{ $selectedRoute['distance_km'] }}</strong>
                    </div>
                    <div style="color:#cbd5e1;">&bull;</div>
                    <div>
                        <span style="color:#64748b;">Durasi:</span>
                        <strong style="color:#0f172a;margin-left:4px;">{{ $selectedRoute['duration'] }}</strong>
                    </div>
                    <div style="color:#cbd5e1;">&bull;</div>
                    <div>
                        <span style="color:#64748b;">Kecepatan Maks:</span>
                        <strong style="color:#dc2626;margin-left:4px;">{{ $selectedRoute['max_speed'] }} KM/H</strong>
                    </div>
                    <div style="color:#cbd5e1;">&bull;</div>
                    <div>
                        <span style="color:#64748b;">Kecepatan Rata2:</span>
                        <strong style="color:#2563eb;margin-left:4px;">{{ $selectedRoute['avg_speed'] }} KM/H</strong>
                    </div>
                    <div style="color:#cbd5e1;">&bull;</div>
                    <div>
                        <span style="color:#64748b;">Titik GPS:</span>
                        <strong style="color:#10b981;margin-left:4px;">{{ $selectedRoute['points_count'] }} Titik</strong>
                    </div>
                </div>

                <div>
                    <span class="badge badge-{{ $selectedRoute['status_badge_color'] ?? 'gray' }}">
                        {{ $selectedRoute['status_label'] ?? 'Status' }}
                    </span>
                </div>
            </div>

            <!-- Map Container -->
            <div style="flex:1;position:relative;background:#f1f5f9;min-height:480px;">
                <div id="route-history-map" style="width:100%;height:100%;min-height:480px;z-index:1;"></div>

                @if(($selectedRoute['points_count'] ?? 0) === 0)
                <div style="position:absolute;top:20px;left:50%;transform:translateX(-50%);z-index:999;background:rgba(255,255,255,0.96);padding:12px 20px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,0.12);border:1px solid #fecdd3;max-width:480px;text-align:center;">
                    <div style="font-weight:700;color:#9f1239;font-size:13px;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <span>⚠️ Data GPS Riil Tidak Terekam</span>
                    </div>
                    <div style="font-size:11.5px;color:#881337;margin-top:4px;line-height:1.4;">
                        Sopir tidak mengaktifkan GPS atau perangkat offline saat perjalanan ini berlangsung. Peta menampilkan titik awal gudang dan titik estimasi tujuan.
                    </div>
                </div>
                @endif

                <!-- Map Overlay Legend -->
                <div style="position:absolute;bottom:16px;left:16px;z-index:999;background:rgba(255,255,255,0.95);backdrop-filter:blur(4px);padding:8px 14px;border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,0.1);border:1px solid #e2e8f0;font-size:11.5px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:14px;">🏢</span>
                        <span style="font-weight:600;color:#334155;">Gudang Asal</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:14px;">🏁</span>
                        <span style="font-weight:600;color:#334155;">Titik Tujuan</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-weight:800;color:#4f46e5;font-size:16px;">━</span>
                        <span style="font-weight:600;color:#334155;">Jalur Yang Dilewati</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding:12px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
                <div style="font-size:12px;color:#64748b;">
                    Asal: <strong>{{ $selectedRoute['origin'] }}</strong> &bull; Kondisi Unit: <strong>{{ $selectedRoute['condition'] }}</strong>
                </div>
                <button type="button" wire:click="closeRouteModal" class="btn btn-secondary" style="font-size:13px;padding:6px 16px;">
                    Tutup Peta
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

@script
<script>
    let routeMapInstance = null;

    $wire.on('render-route-map', (data) => {
        const route = data.routeData || {};
        setTimeout(() => {
            initRouteMap(route);
        }, 150);
    });

    function initRouteMap(route) {
        const container = document.getElementById('route-history-map');
        if (!container) return;

        // Reset map instance if exists
        if (routeMapInstance) {
            routeMapInstance.remove();
            routeMapInstance = null;
        }

        const warehouseCoords = [route.warehouse_lat || -6.2088, route.warehouse_lng || 106.8456];

        routeMapInstance = L.map('route-history-map', {
            center: warehouseCoords,
            zoom: 12,
            zoomControl: true
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(routeMapInstance);

        // Marker Gudang Asal
        const startIcon = L.divIcon({
            className: 'warehouse-start-pin',
            html: '<div style="width:30px;height:30px;border-radius:8px;background:#f59e0b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 3px 8px rgba(245,158,11,0.5);border:2px solid #fff;">🏢</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        L.marker(warehouseCoords, { icon: startIcon })
            .addTo(routeMapInstance)
            .bindPopup(`<b>Titik Keberangkatan (Gudang Asal)</b><br>${route.warehouse_name || 'Gudang Utama'}<br><span style="font-size:11px;color:#64748b;">Waktu: ${route.departure_time}</span>`);

        const bounds = [warehouseCoords];

        // Marker Titik Tujuan
        if (route.destination_lat && route.destination_lng) {
            const destCoords = [parseFloat(route.destination_lat), parseFloat(route.destination_lng)];
            const destIcon = L.divIcon({
                className: 'dest-flag-pin',
                html: '<div style="width:30px;height:30px;border-radius:8px;background:#ef4444;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 3px 8px rgba(239,68,68,0.5);border:2px solid #fff;">🏁</div>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            L.marker(destCoords, { icon: destIcon })
                .addTo(routeMapInstance)
                .bindPopup(`<b>Destinasi Tujuan</b><br>${route.destination}<br><span style="font-size:11px;color:#64748b;">Waktu Selesai: ${route.return_time}</span>`);

            bounds.push(destCoords);
        }

        // Draw Historical Trail (Polyline yang dilewati supir)
        const trailPoints = route.trail || [];
        if (trailPoints.length > 0) {
            const latlngs = [];

            // Titik awal warehouse
            latlngs.push(warehouseCoords);

            trailPoints.forEach((point, i) => {
                const pt = [parseFloat(point.lat), parseFloat(point.lng)];
                latlngs.push(pt);
                bounds.push(pt);

                // Tambahkan marker kecil setiap beberapa titik untuk inspeksi kecepatan
                if (i === trailPoints.length - 1 || i % Math.max(1, Math.floor(trailPoints.length / 8)) === 0) {
                    const circle = L.circleMarker(pt, {
                        radius: 5,
                        fillColor: '#4f46e5',
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.9
                    }).addTo(routeMapInstance);

                    circle.bindTooltip(`
                        <div style="font-size:11px;font-family:'Plus Jakarta Sans',sans-serif;">
                            <div>⏱️ <b>${point.time} WIB</b></div>
                            <div>⚡ Kecepatan: <b>${point.speed} KM/H</b></div>
                        </div>
                    `);
                }
            });

            // Polyline rute ungu tebal
            const polyline = L.polyline(latlngs, {
                color: '#4f46e5',
                weight: 5,
                opacity: 0.85,
                lineJoin: 'round'
            }).addTo(routeMapInstance);

            // Polyline glow / outer border
            L.polyline(latlngs, {
                color: '#818cf8',
                weight: 9,
                opacity: 0.35,
                lineJoin: 'round'
            }).addTo(routeMapInstance);
        }

        // Fit map view to encompass all points
        if (bounds.length > 1) {
            routeMapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
        } else {
            routeMapInstance.setView(warehouseCoords, 13);
        }

        // Trigger map resize after rendering
        setTimeout(() => {
            if (routeMapInstance) {
                routeMapInstance.invalidateSize();
            }
        }, 300);
    }
</script>
@endscript
