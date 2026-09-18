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
                            <div style="margin-top:10px;padding:8px 10px;background:#fff1f2;border:1px solid #fecdd3;border-radius:6px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <div>
                                    <div style="font-size:11.5px;font-weight:700;color:#9f1239;">⚠️ Menunggu Konfirmasi Sopir</div>
                                    <div style="font-size:10.5px;color:#be123c;">Sopir belum menekan konfirmasi di HP.</div>
                                </div>
                                <button type="button"
                                        wire:click="recallDriverNotification({{ $activeAssignment->id }})"
                                        wire:loading.attr="disabled"
                                        style="background:#e11d48;color:#fff;border:none;padding:5px 10px;font-size:11.5px;font-weight:700;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                                    <span wire:loading.remove wire:target="recallDriverNotification({{ $activeAssignment->id }})">🔔 Deringkan Ulang</span>
                                    <span wire:loading wire:target="recallDriverNotification({{ $activeAssignment->id }})">Memanggil...</span>
                                </button>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:24px;color:#adb5bd;">
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
        <div class="card" style="width:100%;max-width:540px;background:#fff;border-radius:12px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden;animation:fadeIn 0.15s ease-out;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding:16px 20px;">
                <div>
                    <h3 class="card-title" style="margin:0;font-size:16px;font-weight:700;">Tugaskan Sopir ke Armada</h3>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Plat: <strong>{{ strtoupper($vehicle->license_plate) }}</strong> ({{ $vehicle->brand }} {{ $vehicle->model }})</div>
                </div>
                <button wire:click="closeModal" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="dispatchVehicle">
                <div class="card-body" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
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

                    <div>
                        <label class="form-label">Tujuan / Rute Pengiriman</label>
                        <input wire:model="destination" type="text" class="form-input" placeholder="Contoh: Pengiriman Logistik Jakarta - Surabaya">
                        @error('destination') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Odometer Awal Keberangkatan (KM) *</label>
                        <input wire:model="dispatchOdometer" type="number" step="0.1" class="form-input @error('dispatchOdometer') form-input-error @enderror">
                        @error('dispatchOdometer') <p class="form-error">{{ $message }}</p> @enderror
                        <p style="font-size:11.5px;color:#64748b;margin-top:3px;">Odometer saat armada meninggalkan gudang.</p>
                    </div>

                    <div>
                        <label class="form-label">Catatan / Instruksi Muatan</label>
                        <textarea wire:model="dispatchNotes" class="form-textarea" rows="2" placeholder="Catatan khusus, nomor surat jalan, atau kontak penerima..."></textarea>
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
                        <textarea wire:model="returnNotes" class="form-textarea" rows="2" placeholder="Catatan kondisi fisik, sisa bensin, keluhan sopir selama perjalanan..."></textarea>
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
</div>
