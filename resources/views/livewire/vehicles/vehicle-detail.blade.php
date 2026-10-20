@assets
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endassets

<div>
    <!-- Success Alert -->
    @if(session()->has('success'))
    <div style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;display:flex;align-items:center;gap:12px;color:#065f46;box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;flex-shrink:0;color:#10b981;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span style="font-weight:600;font-size:14px;">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="page-header" style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary" style="padding:8px 14px;border-radius:8px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span style="display:inline-block;padding:4px 12px;background:#1e293b;color:#fff;border-radius:6px;font-family:monospace;font-size:18px;font-weight:800;letter-spacing:1.5px;border:1.5px solid #cbd5e1;box-shadow:0 2px 4px rgba(0,0,0,0.15);">
                        {{ strtoupper($vehicle->license_plate) }}
                    </span>
                    <span class="badge badge-{{ $vehicle->status_badge_color }}">
                        {{ ucfirst($vehicle->status) }}
                    </span>
                    @if($vehicle->is_halal_dedicated)
                    <span class="badge badge-green">✓ Halal Dedicated</span>
                    @endif
                </div>
                <p class="page-subtitle" style="margin-top:6px;">{{ $vehicle->brand }} {{ $vehicle->model }} &bull; Kode: <strong>{{ $vehicle->vehicle_code ?? '–' }}</strong> &bull; Tipe: {{ ucfirst($vehicle->vehicle_type ?? '–') }}</p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            @if($vehicle->assignedDriver)
                <button wire:click="openReturnModal" class="btn btn-warning" style="background:#f59e0b;color:#fff;border:none;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Check-in Kembali ke Gudang
                </button>
            @else
                <button wire:click="openDispatchModal" class="btn btn-primary">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tugaskan Sopir (Dispatch)
                </button>
            @endif

            @can('edit vehicles')
            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-secondary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Kendaraan
            </a>
            @endcan
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Left Column: Photo & Key Overview -->
        <div class="flex flex-col gap-6">
            <div class="card" style="overflow:hidden;">
                @if($vehicle->photo)
                    <img src="{{ Storage::disk(config('filesystems.default_public_disk'))->url($vehicle->photo) }}" alt="{{ $vehicle->license_plate }}" style="width:100%;height:220px;object-fit:cover;border-bottom:1px solid #eff2f7;">
                @else
                    <div style="width:100%;height:180px;background:linear-gradient(135deg, #1e293b 0%, #334155 100%);color:#94a3b8;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:52px;height:52px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span style="font-size:12px;font-weight:600;letter-spacing:0.5px;">FOTO ARMADA BELUM DIUNGGAH</span>
                    </div>
                @endif

                <div class="card-body">
                    <div class="grid grid-cols-2 gap-3" style="margin-bottom:16px;">
                        <div style="padding:12px;background:#f8f9fa;border-radius:8px;text-align:center;">
                            <div style="font-size:11px;color:#74788d;text-transform:uppercase;font-weight:600;">Odometer</div>
                            <div style="font-size:16px;font-weight:700;color:#0d6efd;margin-top:2px;">
                                {{ number_format($vehicle->current_odometer_km, 0, ',', '.') }} <span style="font-size:11px;color:#6c757d;">KM</span>
                            </div>
                        </div>
                        <div style="padding:12px;background:#f8f9fa;border-radius:8px;text-align:center;">
                            <div style="font-size:11px;color:#74788d;text-transform:uppercase;font-weight:600;">Kapasitas Muat</div>
                            <div style="font-size:16px;font-weight:700;color:#2a3042;margin-top:2px;">
                                {{ number_format($vehicle->max_capacity_kg, 0, ',', '.') }} <span style="font-size:11px;color:#6c757d;">KG</span>
                            </div>
                        </div>
                        <div style="padding:12px;background:#f8f9fa;border-radius:8px;text-align:center;">
                            <div style="font-size:11px;color:#74788d;text-transform:uppercase;font-weight:600;">Tahun / Warna</div>
                            <div style="font-size:13px;font-weight:700;color:#2a3042;margin-top:4px;">
                                {{ $vehicle->year ?? '–' }} &bull; {{ $vehicle->color ?? '–' }}
                            </div>
                        </div>
                        <div style="padding:12px;background:#f8f9fa;border-radius:8px;text-align:center;">
                            <div style="font-size:11px;color:#74788d;text-transform:uppercase;font-weight:600;">Bahan Bakar</div>
                            <div style="font-size:13px;font-weight:700;color:#2a3042;margin-top:4px;">
                                {{ ucfirst($vehicle->fuel_type ?? '–') }}
                            </div>
                        </div>
                    </div>

                    @if($vehicle->is_halal_dedicated)
                    <div style="padding:12px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            ✓
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#065f46;">Armada Terdedikasi Halal</div>
                            <div style="font-size:11.5px;color:#047857;">Hanya mengangkut komoditas halal sesuai SOP LPPOM / BPJPH.</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Driver Assignment Box -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 class="card-title">Status Penugasan & Sopir</h3>
                    @if($vehicle->assignedDriver)
                        <span class="badge badge-green">Sedang Bertugas</span>
                    @else
                        <span class="badge badge-yellow">Tersedia di Gudang</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($vehicle->assignedDriver)
                        <div style="display:flex;align-items:center;gap:14px;padding:12px;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;margin-bottom:14px;">
                            @if($vehicle->assignedDriver->photo)
                                <img src="{{ Storage::disk(config('filesystems.default_public_disk'))->url($vehicle->assignedDriver->photo) }}" alt="{{ $vehicle->assignedDriver->name }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
                            @else
                                <div style="width:48px;height:48px;border-radius:50%;background:#0d6efd;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;flex-shrink:0;">
                                    {{ strtoupper(substr($vehicle->assignedDriver->name, 0, 2)) }}
                                </div>
                            @endif
                            <div style="flex:1;">
                                <div style="font-size:14px;font-weight:700;color:#2a3042;">
                                    {{ $vehicle->assignedDriver->name }}
                                </div>
                                <div style="font-size:12px;color:#74788d;">
                                    {{ $vehicle->assignedDriver->position ?? 'Sopir' }} &bull; SIM {{ $vehicle->assignedDriver->sim_type ?? '–' }}
                                </div>
                                @if($vehicle->assignedDriver->phone)
                                <div style="font-size:12px;color:#0d6efd;margin-top:2px;">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $vehicle->assignedDriver->phone) }}" target="_blank" style="color:#0d6efd;text-decoration:none;">
                                        {{ $vehicle->assignedDriver->phone }}
                                    </a>
                                </div>
                                @endif
                            </div>
                            <a href="{{ route('employees.show', $vehicle->assignedDriver) }}" class="btn btn-soft-primary btn-sm" title="Lihat Profil Sopir">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>

                        <!-- Active Assignment Trip Details -->
                        @if($activeAssignment)
                        <div style="padding:12px;background:#f0f7ff;border:1px solid #bae6fd;border-radius:8px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:11px;font-weight:700;color:#0369a1;text-transform:uppercase;">Rincian Penugasan Aktif</span>
                                <span class="badge badge-{{ $activeAssignment->status_badge_color }}">{{ $activeAssignment->status_label }}</span>
                            </div>
                            <div style="font-size:13px;color:#0f172a;margin-bottom:4px;">
                                <strong>Tujuan:</strong> {{ $activeAssignment->destination ?: '– (Pengiriman reguler)' }}
                            </div>
                            <div style="font-size:12px;color:#475569;">
                                <strong>Berangkat:</strong> {{ $activeAssignment->departure_time ? $activeAssignment->departure_time->format('d/m/Y H:i') : '–' }} &bull; KM Awal: {{ number_format($activeAssignment->start_odometer, 0, ',', '.') }}
                            </div>

                            @if($activeAssignment->status === 'assigned')
                            <div style="margin-top:10px;padding:10px 12px;background:#fff1f2;border:1px solid #fecdd3;border-radius:6px;display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                                <div>
                                    <div style="font-size:11.5px;font-weight:700;color:#9f1239;">⚠️ Menunggu Konfirmasi Sopir</div>
                                    <div style="font-size:10.5px;color:#be123c;">Sopir belum menekan konfirmasi di HP.</div>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <button type="button"
                                            wire:click="recallDriverNotification({{ $activeAssignment->id }})"
                                            wire:loading.attr="disabled"
                                            style="background:#e11d48;color:#fff;border:none;padding:5px 10px;font-size:11.5px;font-weight:700;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                                        <span wire:loading.remove wire:target="recallDriverNotification({{ $activeAssignment->id }})">🔔 Deringkan Ulang</span>
                                        <span wire:loading wire:target="recallDriverNotification({{ $activeAssignment->id }})">Memanggil...</span>
                                    </button>
                                    <button type="button"
                                            wire:click="startTripManual({{ $activeAssignment->id }})"
                                            wire:loading.attr="disabled"
                                            wire:confirm="Sopir belum konfirmasi di HP. Tetap berangkatkan armada {{ $vehicle->license_plate }} sekarang?"
                                            style="background:#0d6efd;color:#fff;border:none;padding:5px 10px;font-size:11.5px;font-weight:700;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;"
                                            title="Bypass konfirmasi dan langsung aktifkan perjalanan">
                                        <span wire:loading.remove wire:target="startTripManual({{ $activeAssignment->id }})">🚀 Berangkatkan</span>
                                        <span wire:loading wire:target="startTripManual({{ $activeAssignment->id }})">...</span>
                                    </button>
                                </div>
                            </div>
                            @elseif($activeAssignment->status === 'confirmed')
                            <div style="margin-top:10px;padding:10px 12px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:6px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                                <div>
                                    <div style="font-size:11.5px;font-weight:700;color:#065f46;">✓ Tugas Dikonfirmasi Sopir (Siap Berangkat)</div>
                                    <div style="font-size:10.5px;color:#047857;">Sopir lupa tekan tombol di HP? Berangkatkan langsung dari sini:</div>
                                </div>
                                <button type="button"
                                        wire:click="startTripManual({{ $activeAssignment->id }})"
                                        wire:loading.attr="disabled"
                                        wire:confirm="Berangkatkan armada {{ $vehicle->license_plate }} sekarang? Status akan berubah ke On-Trip dan aktif di Live GPS."
                                        style="background:#0d6efd;color:#fff;border:none;padding:7px 14px;font-size:12px;font-weight:700;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 4px rgba(13,110,253,0.3);">
                                    <span wire:loading.remove wire:target="startTripManual({{ $activeAssignment->id }})">🚀 Mulai Perjalanan (Berangkatkan)</span>
                                    <span wire:loading wire:target="startTripManual({{ $activeAssignment->id }})">Memproses...</span>
                                </button>
                            </div>
                            @elseif($activeAssignment->status === 'on_trip')
                            <div style="margin-top:10px;padding:10px 12px;background:#f5f3ff;border:1px solid #ddd6fe;border-radius:6px;display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="width:8px;height:8px;border-radius:50%;background:#8b5cf6;display:inline-block;animation:pulse 1.5s infinite;"></span>
                                    <div>
                                        <div style="font-size:11.5px;font-weight:700;color:#6d28d9;">Sedang Dalam Perjalanan (On-Trip)</div>
                                        <div style="font-size:10.5px;color:#7c3aed;">Pelacakan GPS aktif & dipantau Control Tower.</div>
                                    </div>
                                </div>
                                <a href="{{ route('tracking.index') }}" class="btn btn-sm" style="background:#7c3aed;color:#fff;padding:5px 12px;font-size:11.5px;font-weight:700;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                    Pantau di Live GPS →
                                </a>
                            </div>
                            @endif
                        </div>
                        @endif

                        <div style="margin-top:14px;">
                            <button wire:click="openReturnModal" class="btn btn-warning w-full" style="width:100%;justify-content:center;background:#f59e0b;color:#fff;border:none;">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Check-in Kembali ke Gudang (Release)
                            </button>
                        </div>
                    @else
                        <div style="text-align:center;padding:20px 8px;color:#adb5bd;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 10px;display:block;color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <div style="font-size:14px;color:#334155;font-weight:600;">Armada Tersedia di Gudang</div>
                            <div style="font-size:12px;color:#64748b;margin-top:4px;margin-bottom:16px;">Kendaraan dalam posisi standby dan siap ditugaskan kepada sopir mana pun.</div>
                            <button wire:click="openDispatchModal" class="btn btn-primary w-full" style="width:100%;justify-content:center;">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tugaskan Sopir Sekarang
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Specs, History & Documents -->
        <div class="xl:col-span-2 flex flex-col gap-6">

            <!-- Data Teknis & Spesifikasi Lengkap -->
            <div class="card">
                <div class="card-header"><h3 class="card-title">Identitas & Spesifikasi Teknis</h3></div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Nomor Rangka (Chassis Number)</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;font-family:monospace;">
                                {{ $vehicle->chassis_number ?? '–' }}
                            </div>
                        </div>
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Nomor Mesin (Engine Number)</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;font-family:monospace;">
                                {{ $vehicle->engine_number ?? '–' }}
                            </div>
                        </div>
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Tipe Bodi / Kendaraan</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;">
                                {{ ucfirst($vehicle->vehicle_type ?? '–') }}
                            </div>
                        </div>
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Device ID GPS Tracker</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;font-family:monospace;">
                                {{ $vehicle->gps_device_id ?? '– (Belum terpasang)' }}
                            </div>
                        </div>
                    </div>

                    @if($vehicle->notes)
                    <div style="margin-top:16px;padding:12px;background:#fff9db;border-left:4px solid #f59e0b;border-radius:4px;font-size:13px;color:#78350f;">
                        <strong>Catatan Khusus Armada:</strong> {{ $vehicle->notes }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Riwayat Penugasan & Perjalanan (Trip History) -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 class="card-title">Riwayat Penugasan & Perjalanan Armada</h3>
                    <span style="font-size:12px;color:#74788d;">10 Perjalanan Terakhir</span>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sopir</th>
                                <th>Tujuan</th>
                                <th>Waktu Berangkat / Pulang</th>
                                <th>Jarak Tempuh</th>
                                <th>Status / Kondisi</th>
                                <th style="text-align:center;width:90px;">Rute GPS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignmentHistory as $history)
                            <tr>
                                <td>
                                    <div style="font-weight:600;color:#2a3042;">{{ $history->driver->name ?? '–' }}</div>
                                    <div style="font-size:11.5px;color:#adb5bd;">SIM {{ $history->driver->sim_type ?? '–' }}</div>
                                </td>
                                <td>
                                    <span style="font-size:13px;color:#495057;">{{ $history->destination ?: '–' }}</span>
                                </td>
                                <td>
                                    <div style="font-size:12.5px;color:#2a3042;">{{ $history->departure_time ? $history->departure_time->format('d/m/Y H:i') : '–' }}</div>
                                    <div style="font-size:11.5px;color:#64748b;">
                                        Pulang: {{ $history->return_time ? $history->return_time->format('d/m/Y H:i') : '(Sedang Berjalan)' }}
                                    </div>
                                </td>
                                <td>
                                    @if($history->distance_traveled !== null)
                                        <span style="font-weight:700;color:#0d6efd;">{{ number_format($history->distance_traveled, 0, ',', '.') }} KM</span>
                                        <div style="font-size:11px;color:#adb5bd;">{{ number_format($history->start_odometer, 0) }} &rarr; {{ number_format($history->end_odometer, 0) }}</div>
                                    @else
                                        <span style="color:#adb5bd;font-size:12px;">KM Awal: {{ number_format($history->start_odometer, 0) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $history->status_badge_color }}">
                                        {{ $history->status_label }}
                                    </span>
                                    @if($history->status === 'assigned')
                                    <div style="margin-top:6px;">
                                        <button type="button"
                                                wire:click="recallDriverNotification({{ $history->id }})"
                                                wire:loading.attr="disabled"
                                                class="btn btn-sm"
                                                style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:3px 8px;font-size:11px;font-weight:700;border-radius:4px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;"
                                                title="Kirim ulang notifikasi darurat ke HP sopir">
                                            <span wire:loading.remove wire:target="recallDriverNotification({{ $history->id }})">🔔 Deringkan Ulang</span>
                                            <span wire:loading wire:target="recallDriverNotification({{ $history->id }})">Memanggil...</span>
                                        </button>
                                    </div>
                                    @endif
                                    @if($history->vehicle_condition_on_return)
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                            Kondisi: {{ ucfirst(str_replace('_', ' ', $history->vehicle_condition_on_return)) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <button type="button"
                                            wire:click="openRouteModal({{ $history->id }})"
                                            class="btn btn-sm"
                                            style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:4px 8px;font-size:11px;font-weight:700;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:3px;"
                                            title="Lihat peta rute yang dilalui">
                                        🗺️ Rute
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:24px;color:#adb5bd;">
                                    Belum ada catatan riwayat penugasan untuk armada ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dokumen Legalitas Kendaraan -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 class="card-title">Dokumen Legalitas Armada (STNK, KIR, Asuransi, dll.) ({{ $vehicle->documents->count() }})</h3>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tipe Dokumen</th>
                                <th>Nomor / Judul</th>
                                <th>Instansi Penerbit</th>
                                <th>Masa Berlaku</th>
                                <th>Status</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicle->documents as $doc)
                            <tr>
                                <td>
                                    <span class="badge badge-purple">{{ strtoupper($doc->document_type) }}</span>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:#2a3042;">{{ $doc->title ?? $doc->document_number }}</div>
                                    @if($doc->document_number && $doc->title)
                                    <div style="font-size:11.5px;color:#adb5bd;">{{ $doc->document_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size:13px;color:#495057;">{{ $doc->issuing_authority ?? '–' }}</span>
                                </td>
                                <td>
                                    @if($doc->expiry_date)
                                        @php $dLeft = now()->diffInDays($doc->expiry_date, false); @endphp
                                        <div style="font-size:13px;">{{ $doc->expiry_date->format('d/m/Y') }}</div>
                                        @if($dLeft <= 30)
                                            <span class="badge badge-{{ $dLeft <= 7 ? 'red' : ($dLeft <= 14 ? 'orange' : 'yellow') }}">
                                                {{ $dLeft <= 0 ? 'Expired' : 'H-'.$dLeft }}
                                            </span>
                                        @endif
                                    @else
                                        <span style="color:#adb5bd;font-size:13px;">–</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $doc->status === 'active' ? 'green' : 'red' }}">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                        @if($doc->file_path)
                                        <a href="{{ Storage::disk(config('filesystems.default_public_disk'))->url($doc->file_path) }}" target="_blank" class="btn btn-soft-primary btn-sm" title="Lihat Berkas">
                                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:24px;color:#adb5bd;">
                                    Belum ada berkas dokumen (STNK/KIR/dll.) yang diunggah untuk armada ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================
         MODAL 1: TUGASKAN SOPIR (DISPATCH)
    ======================================================== -->
    @if($showDispatchModal)
    <div style="position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div class="card" style="width:100%;max-width:620px;max-height:92vh;display:flex;flex-direction:column;background:#fff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden;animation:fadeIn 0.15s ease-out;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding:16px 20px;flex-shrink:0;">
                <div>
                    <h3 class="card-title" style="margin:0;font-size:16px;font-weight:700;">Tugaskan Sopir ke Armada</h3>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Plat: <strong>{{ strtoupper($vehicle->license_plate) }}</strong> ({{ $vehicle->brand }} {{ $vehicle->model }})</div>
                </div>
                <button wire:click="closeModal" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="dispatchVehicle" style="display:flex;flex-direction:column;overflow:hidden;flex:1;">
                <div class="card-body" style="padding:20px;display:flex;flex-direction:column;gap:14px;overflow-y:auto;flex:1;">
                    <div>
                        <label class="form-label">Pilih Sopir Tersedia *</label>
                        <select wire:model="selectedDriverId" class="form-select @error('selectedDriverId') form-input-error @enderror">
                            <option value="">-- Pilih Sopir --</option>
                            @foreach($availableDrivers as $driver)
                                <option value="{{ $driver->id }}">
                                    {{ $driver->name }} (SIM {{ $driver->sim_type ?? '–' }}) - {{ $driver->phone ?? 'Tanpa No HP' }}
                                </option>
                            @endforeach
                        </select>
                        @error('selectedDriverId') <p class="form-error">{{ $message }}</p> @enderror
                        @if($availableDrivers->isEmpty())
                            <p style="font-size:12px;color:#ef4444;margin-top:4px;">Tidak ada sopir yang tersedia saat ini (semua sedang bertugas atau tidak aktif).</p>
                        @endif
                    </div>

                    <!-- Destination with Map Picker & Warehouse Shortcut -->
                    <div x-data="{ showMap: false }">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:6px;">
                            <label class="form-label" style="margin-bottom:0;">Tujuan / Rute Pengiriman</label>
                            <div style="display:flex;align-items:center;gap:6px;">
                                @if(isset($warehouses) && $warehouses->isNotEmpty())
                                <select onchange="if(this.value){ window.selectWarehousePreset(this.value); this.value=''; }"
                                        style="font-size:11px;padding:3px 8px;border:1px solid #cbd5e1;border-radius:6px;background:#f8fafc;color:#334155;cursor:pointer;max-width:170px;">
                                    <option value="">🏢 Gudang Terdaftar...</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ json_encode(['name' => $wh->name, 'address' => $wh->address, 'lat' => $wh->latitude, 'lng' => $wh->longitude]) }}">
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @endif

                                <button type="button"
                                        @click="showMap = !showMap; if(showMap) { $nextTick(() => { window.initDestinationPicker(); }); }"
                                        style="font-size:11.5px;font-weight:600;padding:4px 10px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                                    <span x-show="!showMap">🗺️ Cari via Map</span>
                                    <span x-show="showMap" style="display:none;">✕ Tutup Map</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <input wire:model="destination" id="dispatch-destination-input" type="text" class="form-input" placeholder="Contoh: Jl. Daan Mogot KM 12 / Gudang Cakung / -6.2088, 106.8456">
                            @error('destination') <p class="form-error">{{ $message }}</p> @enderror
                            @if($destinationLat && $destinationLng)
                            <div style="font-size:11px;color:#059669;font-weight:600;margin-top:4px;display:flex;align-items:center;gap:4px;">
                                <span>🎯 Koordinat tersimpan:</span>
                                <span style="font-family:monospace;background:#ecfdf5;padding:1px 6px;border-radius:4px;border:1px solid #a7f3d0;">{{ number_format($destinationLat, 6) }}, {{ number_format($destinationLng, 6) }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Interactive Leaflet Map Picker Panel -->
                        <div x-show="showMap" x-transition
                             style="display:none;margin-top:10px;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:10px;padding:12px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <div style="font-size:12px;font-weight:700;color:#1e293b;">
                                    📍 Tentukan Lokasi Tujuan di Peta
                                </div>
                                <div style="font-size:11px;color:#64748b;">
                                    Klik titik peta / geser pin untuk memilih
                                </div>
                            </div>

                            <!-- Search Input inside Map -->
                            <div style="display:flex;gap:6px;margin-bottom:6px;">
                                <input type="text" id="dest-map-search"
                                       placeholder="Ketik alamat/nama jalan/gedung atau koordinat Google Maps..."
                                       style="flex:1;padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;outline:none;"
                                       onkeydown="if(event.key==='Enter'){event.preventDefault();window.searchDestinationOnMap();}">
                                <button type="button" onclick="window.searchDestinationOnMap()"
                                        style="padding:7px 12px;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">
                                    Cari
                                </button>
                            </div>
                            <div id="dest-map-status" style="font-size:11px;margin-bottom:6px;"></div>

                            <!-- Leaflet Map Container -->
                            <div id="dest-picker-map" wire:ignore style="width:100%;height:250px;border-radius:8px;border:1px solid #cbd5e1;z-index:1;"></div>

                            <!-- Selected Destination Display & Apply -->
                            <div style="margin-top:8px;display:flex;align-items:center;justify-content:space-between;gap:8px;background:#fff;padding:8px 10px;border-radius:6px;border:1px solid #e2e8f0;">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Titik Lokasi Terdeteksi:</div>
                                    <div id="dest-map-picked-label" style="font-size:11.5px;color:#0f172a;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        Belum ada titik yang dipilih di peta
                                    </div>
                                </div>
                                <button type="button"
                                        onclick="window.applyDestinationPicked()"
                                        style="background:#10b981;color:#fff;border:none;padding:6px 12px;font-size:11.5px;font-weight:700;border-radius:6px;cursor:pointer;white-space:nowrap;">
                                    ✓ Gunakan Lokasi
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Odometer Awal Keberangkatan (KM) *</label>
                        <input wire:model="dispatchOdometer" type="number" step="0.1" class="form-input @error('dispatchOdometer') form-input-error @enderror">
                        @error('dispatchOdometer') <p class="form-error">{{ $message }}</p> @enderror
                        <p style="font-size:11.5px;color:#64748b;margin-top:3px;">Odometer saat armada meninggalkan gudang.</p>
                    </div>

                    <div>
                        <label class="form-label">Catatan / Instruksi Muatan</label>
                        <textarea wire:model="dispatchNotes" class="form-input" rows="3" style="width:100%;min-height:76px;resize:vertical;font-family:inherit;" placeholder="Catatan khusus, nomor surat jalan, atau kontak penerima..."></textarea>
                    </div>
                </div>

                <div style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:flex-end;gap:10px;">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" @if($availableDrivers->isEmpty()) disabled @endif>
                        Kirim Penugasan & Lepas Armada
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ========================================================
         MODAL 2: CHECK-IN KEMBALI KE GUDANG (RELEASE)
    ======================================================== -->
    @if($showReturnModal)
    <div style="position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div class="card" style="width:100%;max-width:540px;background:#fff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden;animation:fadeIn 0.15s ease-out;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding:16px 20px;background:#fffbeb;">
                <div>
                    <h3 class="card-title" style="margin:0;font-size:16px;font-weight:700;color:#92400e;">Konfirmasi Masuk Gudang (Check-in)</h3>
                    <div style="font-size:12px;color:#b45309;margin-top:2px;">
                        Armada: <strong>{{ strtoupper($vehicle->license_plate) }}</strong> &bull; Sopir: <strong>{{ $vehicle->assignedDriver?->name }}</strong>
                    </div>
                </div>
                <button wire:click="closeModal" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="returnToWarehouse">
                <div class="card-body" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                    <div style="padding:12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;color:#334155;">
                        Dengan konfirmasi ini, penugasan sopir <strong>{{ $vehicle->assignedDriver?->name }}</strong> akan diselesaikan. Unit armada akan kembali berstatus <strong>Tersedia di Gudang</strong> dan dapat ditugaskan ke sopir lain sewaktu-waktu.
                    </div>

                    <div>
                        <label class="form-label">Odometer Akhir Kepulangan (KM) *</label>
                        <input wire:model="returnOdometer" type="number" step="0.1" class="form-input @error('returnOdometer') form-input-error @enderror">
                        @error('returnOdometer') <p class="form-error">{{ $message }}</p> @enderror
                        <p style="font-size:11.5px;color:#64748b;margin-top:3px;">
                            KM Keberangkatan: {{ number_format($activeAssignment->start_odometer ?? $vehicle->current_odometer_km, 0) }} KM.
                        </p>
                    </div>

                    <div>
                        <label class="form-label">Kondisi Armada Saat Masuk Gudang *</label>
                        <select wire:model="returnCondition" class="form-select">
                            <option value="baik">Kondisi Baik & Siap Operasi</option>
                            <option value="perlu_cuci">Perlu Dicuci / Dibersihkan</option>
                            <option value="perlu_perawatan">Perlu Servis / Perawatan Ringan (Ganti Oli/Rem)</option>
                            <option value="rusak">Ada Kerusakan / Perlu Bengkel (Unit Masuk Status Maintenance)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Catatan Pemeriksaan Armada</label>
                        <textarea wire:model="returnNotes" class="form-input" rows="3" style="width:100%;min-height:76px;resize:vertical;font-family:inherit;" placeholder="Catatan kondisi fisik, sisa bensin, keluhan sopir selama perjalanan..."></textarea>
                    </div>
                </div>

                <div style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:flex-end;gap:10px;">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-warning" style="background:#f59e0b;color:#fff;border:none;">
                        Konfirmasi Masuk Gudang & Lepas Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL POPUP: VISUALISASI PETA JEJAK RUTE GPS PERJALANAN -->
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
                <div id="vehicle-route-history-map" style="width:100%;height:100%;min-height:480px;z-index:1;"></div>

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
    let destPickerMap = null;
    let destPickerMarker = null;
    let currentPickedText = '';
    let currentPickedLat = null;
    let currentPickedLng = null;
    let vehicleRouteMapInstance = null;

    $wire.on('render-route-map', (data) => {
        const route = data.routeData || {};
        setTimeout(() => {
            initVehicleRouteMap(route);
        }, 150);
    });

    function initVehicleRouteMap(route) {
        const container = document.getElementById('vehicle-route-history-map');
        if (!container) return;

        if (vehicleRouteMapInstance) {
            vehicleRouteMapInstance.remove();
            vehicleRouteMapInstance = null;
        }

        const warehouseCoords = [route.warehouse_lat || -6.2088, route.warehouse_lng || 106.8456];

        vehicleRouteMapInstance = L.map('vehicle-route-history-map', {
            center: warehouseCoords,
            zoom: 12,
            zoomControl: true
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(vehicleRouteMapInstance);

        // Marker Gudang Asal
        const startIcon = L.divIcon({
            className: 'wh-start-pin',
            html: '<div style="width:30px;height:30px;border-radius:8px;background:#f59e0b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 3px 8px rgba(245,158,11,0.5);border:2px solid #fff;">🏢</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        L.marker(warehouseCoords, { icon: startIcon })
            .addTo(vehicleRouteMapInstance)
            .bindPopup(`<b>Titik Keberangkatan</b><br>${route.warehouse_name || 'Gudang Utama'}<br><span style="font-size:11px;color:#64748b;">Waktu: ${route.departure_time}</span>`);

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
                .addTo(vehicleRouteMapInstance)
                .bindPopup(`<b>Destinasi Tujuan</b><br>${route.destination}<br><span style="font-size:11px;color:#64748b;">Waktu Selesai: ${route.return_time}</span>`);

            bounds.push(destCoords);
        }

        // Draw Historical Trail
        const trailPoints = route.trail || [];
        if (trailPoints.length > 0) {
            const latlngs = [];
            latlngs.push(warehouseCoords);

            trailPoints.forEach((point, i) => {
                const pt = [parseFloat(point.lat), parseFloat(point.lng)];
                latlngs.push(pt);
                bounds.push(pt);

                if (i === trailPoints.length - 1 || i % Math.max(1, Math.floor(trailPoints.length / 8)) === 0) {
                    const circle = L.circleMarker(pt, {
                        radius: 5,
                        fillColor: '#4f46e5',
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.9
                    }).addTo(vehicleRouteMapInstance);

                    circle.bindTooltip(`
                        <div style="font-size:11px;font-family:'Plus Jakarta Sans',sans-serif;">
                            <div>⏱️ <b>${point.time} WIB</b></div>
                            <div>⚡ Kecepatan: <b>${point.speed} KM/H</b></div>
                        </div>
                    `);
                }
            });

            L.polyline(latlngs, {
                color: '#4f46e5',
                weight: 5,
                opacity: 0.85,
                lineJoin: 'round'
            }).addTo(vehicleRouteMapInstance);

            L.polyline(latlngs, {
                color: '#818cf8',
                weight: 9,
                opacity: 0.35,
                lineJoin: 'round'
            }).addTo(vehicleRouteMapInstance);
        }

        if (bounds.length > 1) {
            vehicleRouteMapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
        } else {
            vehicleRouteMapInstance.setView(warehouseCoords, 13);
        }

        setTimeout(() => {
            if (vehicleRouteMapInstance) {
                vehicleRouteMapInstance.invalidateSize();
            }
        }, 300);
    }

    const defaultLat = {{ (float) ($primaryWarehouse->latitude ?? -6.2088) }};
    const defaultLng = {{ (float) ($primaryWarehouse->longitude ?? 106.8456) }};

    window.initDestinationPicker = function() {
        const container = document.getElementById('dest-picker-map');
        if (!container) return;

        if (typeof L === 'undefined') {
            console.warn('Leaflet belum dimuat.');
            return;
        }

        // Jika container map telah dibuat ulang oleh Livewire
        if (destPickerMap) {
            try {
                if (destPickerMap._container !== container) {
                    destPickerMap.remove();
                    destPickerMap = null;
                }
            } catch(e) {
                destPickerMap = null;
            }
        }

        if (!destPickerMap) {
            destPickerMap = L.map('dest-picker-map', {
                center: [defaultLat, defaultLng],
                zoom: 13,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(destPickerMap);

            destPickerMarker = L.marker([defaultLat, defaultLng], { draggable: true })
                .addTo(destPickerMap)
                .bindPopup('📍 Geser atau klik peta untuk memilih tujuan')
                .openPopup();

            destPickerMap.on('click', function(e) {
                const { lat, lng } = e.latlng;
                window.updateDestMarker(lat, lng);
            });

            destPickerMarker.on('dragend', function(e) {
                const { lat, lng } = e.target.getLatLng();
                window.updateDestMarker(lat, lng);
            });
        }

        setTimeout(() => {
            if (destPickerMap) {
                destPickerMap.invalidateSize();
            }
        }, 200);
    };

    window.updateDestMarker = function(lat, lng, customLabel = null) {
        currentPickedLat = lat;
        currentPickedLng = lng;

        if (destPickerMarker && destPickerMap) {
            destPickerMarker.setLatLng([lat, lng]);
            destPickerMap.panTo([lat, lng]);
        }

        const labelEl = document.getElementById('dest-map-picked-label');

        if (customLabel) {
            currentPickedText = customLabel;
            if (labelEl) labelEl.innerText = customLabel;
            if (destPickerMarker) destPickerMarker.bindPopup(customLabel).openPopup();
            return;
        }

        if (labelEl) labelEl.innerText = `Mencari alamat... (${lat.toFixed(5)}, ${lng.toFixed(5)})`;

        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&accept-language=id`)
            .then(r => r.json())
            .then(data => {
                if (data && data.display_name) {
                    currentPickedText = data.display_name;
                    if (labelEl) labelEl.innerText = data.display_name;
                    if (destPickerMarker) {
                        destPickerMarker.bindPopup(data.display_name).openPopup();
                    }
                } else {
                    currentPickedText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    if (labelEl) labelEl.innerText = currentPickedText;
                }
            })
            .catch(() => {
                currentPickedText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                if (labelEl) labelEl.innerText = currentPickedText;
            });
    };

    window.searchDestinationOnMap = function() {
        const inputEl = document.getElementById('dest-map-search');
        if (!inputEl) return;
        const q = inputEl.value.trim();
        const statusEl = document.getElementById('dest-map-status');
        if (!q) return;

        // 1. Cek apakah format koordinat Google Maps (misal: -6.2202, 106.8579)
        const coordMatch = q.match(/(-?\d{1,2}\.\d+)[,\s]+(-?\d{1,3}\.\d+)/);
        if (coordMatch) {
            const latF = parseFloat(coordMatch[1]);
            const lngF = parseFloat(coordMatch[2]);
            if (latF >= -90 && latF <= 90 && lngF >= -180 && lngF <= 180) {
                if (destPickerMap) destPickerMap.setView([latF, lngF], 16);
                window.updateDestMarker(latF, lngF);
                if (statusEl) {
                    statusEl.innerHTML = '<span style="color:#059669;font-weight:600;">✅ Koordinat Google Maps terdeteksi!</span>';
                    setTimeout(() => { if (statusEl) statusEl.innerHTML = ''; }, 3500);
                }
                return;
            }
        }

        // 2. Cari via Nominatim OpenStreetMap
        if (statusEl) statusEl.innerHTML = '<span style="color:#2563eb;">Mencari lokasi di OpenStreetMap...</span>';

        fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1&accept-language=id`)
            .then(r => r.json())
            .then(data => {
                if (data && data.length > 0) {
                    const { lat, lon, display_name } = data[0];
                    const latF = parseFloat(lat);
                    const lngF = parseFloat(lon);

                    if (destPickerMap) {
                        destPickerMap.setView([latF, lngF], 16);
                        destPickerMap.invalidateSize();
                    }
                    window.updateDestMarker(latF, lngF, display_name);
                    if (statusEl) {
                        statusEl.innerHTML = '<span style="color:#059669;font-weight:600;">✅ Lokasi ditemukan: ' + display_name + '</span>';
                        setTimeout(() => { if (statusEl) statusEl.innerHTML = ''; }, 4500);
                    }
                } else {
                    if (statusEl) {
                        statusEl.innerHTML = '<span style="color:#ef4444;">❌ Lokasi tidak ditemukan. Coba gunakan nama jalan/daerah atau paste koordinat Google Maps.</span>';
                    }
                }
            })
            .catch(() => {
                if (statusEl) statusEl.innerHTML = '<span style="color:#ef4444;">❌ Gagal menghubungi server pencari peta.</span>';
            });
    };

    window.applyDestinationPicked = function() {
        if (!currentPickedText) return;
        $wire.set('destination', currentPickedText);
        if (currentPickedLat !== null && currentPickedLng !== null) {
            $wire.set('destinationLat', currentPickedLat);
            $wire.set('destinationLng', currentPickedLng);
        }
        const input = document.getElementById('dispatch-destination-input');
        if (input) {
            input.value = currentPickedText;
        }
        const labelEl = document.getElementById('dest-map-picked-label');
        if (labelEl) {
            labelEl.innerHTML = '<span style="color:#059669;font-weight:700;">✓ Alamat & koordinat berhasil diterapkan ke form!</span>';
        }
    };

    window.selectWarehousePreset = function(jsonStr) {
        try {
            const wh = JSON.parse(jsonStr);
            const text = wh.name + (wh.address ? ' - ' + wh.address : '');
            $wire.set('destination', text);
            if (wh.lat && wh.lng) {
                currentPickedLat = parseFloat(wh.lat);
                currentPickedLng = parseFloat(wh.lng);
                $wire.set('destinationLat', currentPickedLat);
                $wire.set('destinationLng', currentPickedLng);
            }
            const input = document.getElementById('dispatch-destination-input');
            if (input) input.value = text;

            if (wh.lat && wh.lng) {
                window.initDestinationPicker();
                setTimeout(() => {
                    if (destPickerMap) {
                        destPickerMap.setView([wh.lat, wh.lng], 16);
                    }
                    window.updateDestMarker(wh.lat, wh.lng, text);
                }, 250);
            }
        } catch(e) {}
    };
</script>
@endscript

