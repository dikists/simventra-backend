<div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom:24px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <h1 class="page-title" style="margin:0;">Dashboard Sopir</h1>
                <span style="background:#e0e7ff;color:#4338ca;font-size:12px;font-weight:700;padding:4px 10px;border-radius:20px;">
                    Portal Pengemudi
                </span>
            </div>
            <p class="page-subtitle" style="margin-top:4px;">
                Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong> ({{ $employee?->employee_number ?? 'Sopir' }}) · {{ now()->translatedFormat('l, d F Y') }}
            </p>
        </div>

        <a href="{{ route('driver.app') }}" target="_blank" class="btn btn-primary" style="background:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);box-shadow:0 4px 14px rgba(37,99,235,0.35);gap:8px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Buka Aplikasi Driver PWA ↗
        </a>
    </div>

    <!-- Alert Sinyal GPS Khusus Driver (Jika sedang on_trip tapi GPS terputus) -->
    @if($isGpsSilent)
    <div style="background:linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);border:2px solid #f59e0b;border-radius:16px;padding:18px 22px;margin-bottom:24px;box-shadow:0 8px 20px -4px rgba(245,158,11,0.2);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div style="width:44px;height:44px;border-radius:12px;background:#f59e0b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                    📡
                </div>
                <div>
                    <h3 style="margin:0;font-size:15px;font-weight:800;color:#92400e;">
                        Perhatian: Sinyal GPS Anda Terputus ({{ $silenceMinutes }} Menit yang lalu)
                    </h3>
                    <p style="margin:4px 0 0 0;font-size:13px;color:#78350f;line-height:1.5;">
                        Sistem mendeteksi pelacakan GPS dari HP Anda belum terkirim. Mohon buka aplikasi SIMVENTRA Driver agar rute perjalanan armada Anda tetap terpantau aman oleh Tim Control Tower.
                    </p>
                </div>
            </div>
            <a href="{{ route('driver.app') }}" target="_blank" class="btn" style="background:#d97706;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;text-decoration:none;font-size:13px;box-shadow:0 4px 12px rgba(217,119,6,0.3);">
                Buka Aplikasi & Kirim GPS →
            </a>
        </div>
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:24px;margin-bottom:24px;" class="driver-grid">

        <!-- KARTU UTAMA: STATUS TUGAS & ARMADA -->
        <div style="background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:18px;">
                            🚚
                        </div>
                        <h2 style="font-size:16px;font-weight:800;color:#1e293b;margin:0;">Status Tugas Pengiriman</h2>
                    </div>

                    @if($activeAssignment)
                        @if($activeAssignment->status === 'on_trip')
                            <span style="background:#dcfce7;color:#15803d;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:800;display:inline-flex;align-items:center;gap:6px;">
                                <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;animation:pulse 1.5s infinite;"></span>
                                Dalam Perjalanan (On Trip)
                            </span>
                        @elseif($activeAssignment->status === 'confirmed')
                            <span style="background:#e0e7ff;color:#4338ca;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:800;">
                                ✓ Tugas Diterima (Siap Jalan)
                            </span>
                        @else
                            <span style="background:#fef3c7;color:#b45309;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:800;">
                                🔔 Menunggu Konfirmasi Anda
                            </span>
                        @endif
                    @else
                        <span style="background:#f1f5f9;color:#475569;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;">
                            🟢 Siaga (Standby)
                        </span>
                    @endif
                </div>

                @if($activeAssignment)
                <!-- Detail Armada Aktif -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:18px;margin-bottom:18px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <div style="font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Kendaraan Ditugaskan</div>
                            <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;">
                                {{ $activeAssignment->vehicle?->brand }} {{ $activeAssignment->vehicle?->model }}
                            </div>
                            <div style="font-size:12.5px;color:#64748b;margin-top:2px;">
                                Tipe: {{ $activeAssignment->vehicle?->vehicle_type ?? 'Armada Angkutan' }} · Warna: {{ ucfirst($activeAssignment->vehicle?->color ?? '-') }}
                            </div>
                        </div>
                        <div style="background:#0f172a;color:#fff;padding:8px 16px;border-radius:10px;font-family:monospace;font-weight:800;font-size:16px;letter-spacing:1.5px;box-shadow:0 3px 8px rgba(0,0,0,0.15);">
                            {{ strtoupper($activeAssignment->vehicle?->license_plate ?? '-') }}
                        </div>
                    </div>

                    <div style="border-top:1px dashed #cbd5e1;padding-top:14px;display:grid;grid-template-columns:1fr;gap:10px;">
                        <div>
                            <span style="font-size:11.5px;color:#64748b;font-weight:600;display:block;">TUJUAN PENGIRIMAN:</span>
                            <span style="font-size:13.5px;font-weight:700;color:#1e293b;line-height:1.4;">
                                📍 {{ $activeAssignment->destination ?? 'Gudang / Rute belum ditentukan' }}
                            </span>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;">
                            <div style="background:#fff;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;">
                                <div style="font-size:11px;color:#64748b;font-weight:600;">Waktu Berangkat</div>
                                <div style="font-size:13px;font-weight:700;color:#1e293b;margin-top:2px;">
                                    {{ $activeAssignment->departure_time ? \Carbon\Carbon::parse($activeAssignment->departure_time)->format('H:i · d M Y') : '-' }}
                                </div>
                            </div>
                            <div style="background:#fff;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;">
                                <div style="font-size:11px;color:#64748b;font-weight:600;">Odometer Awal</div>
                                <div style="font-size:13px;font-weight:700;color:#1e293b;margin-top:2px;">
                                    {{ $activeAssignment->start_odometer ? number_format($activeAssignment->start_odometer) . ' KM' : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <!-- Kosong: Standby -->
                <div style="text-align:center;padding:36px 20px;background:#f8fafc;border-radius:14px;border:1px dashed #cbd5e1;margin-bottom:18px;">
                    <div style="font-size:40px;margin-bottom:10px;">☕</div>
                    <h3 style="font-size:15px;font-weight:700;color:#1e293b;margin:0;">Tidak Ada Penugasan Aktif</h3>
                    <p style="font-size:13px;color:#64748b;margin:6px auto 0;max-width:380px;line-height:1.5;">
                        Saat ini Anda sedang dalam status siaga (standby). Jadwal perjalanan dan armada akan otomatis tampil di sini saat tim Dispatcher menugaskan Anda.
                    </p>
                </div>
                @endif
            </div>

            <!-- Tombol Aksi Driver -->
            <div>
                <a href="{{ route('driver.app') }}" target="_blank" class="btn" style="background:#2563eb;color:#fff;font-weight:700;padding:14px 20px;border-radius:12px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:10px;font-size:14px;box-shadow:0 4px 14px rgba(37,99,235,0.3);width:100%;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Masuk ke Aplikasi SIMVENTRA Driver (Kirim GPS & Check-In)</span>
                    <span style="font-size:16px;">→</span>
                </a>
            </div>
        </div>

        <!-- KOLOM KANAN: STATUS LISENSI & DOKUMEN PRIBADI -->
        <div style="display:flex;flex-direction:column;gap:20px;">

            <!-- Dokumen & Berkas Pengemudi (Sinkron dengan Modul Dokumen Karyawan) -->
            <div style="background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:20px;box-shadow:0 4px 12px rgba(0,0,0,0.03);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:8px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:17px;">
                            📄
                        </div>
                        <div>
                            <h3 style="font-size:15px;font-weight:700;color:#1e293b;margin:0;">Dokumen & Berkas Pengemudi</h3>
                            <div style="font-size:12px;color:#64748b;">Berkas terverifikasi di sistem (SIM, SKCK, KTP)</div>
                        </div>
                    </div>
                    @if($personalDocs->isNotEmpty())
                    <span style="background:#dcfce7;color:#15803d;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;">
                        {{ $personalDocs->count() }} Berkas
                    </span>
                    @else
                    <span style="background:#f1f5f9;color:#64748b;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;">
                        Belum Ada Berkas
                    </span>
                    @endif
                </div>

                @if($personalDocs->isEmpty())
                <!-- State Kosong: Jika belum ada berkas dokumen yang diunggah -->
                <div style="text-align:center;padding:28px 16px;background:#f8fafc;border-radius:12px;border:1px dashed #cbd5e1;">
                    <div style="font-size:32px;margin-bottom:8px;">📁</div>
                    <div style="font-size:13.5px;font-weight:700;color:#1e293b;">Belum Ada Dokumen Terdaftar</div>
                    <p style="font-size:12px;color:#64748b;margin:6px auto 0;max-width:300px;line-height:1.5;">
                        Belum ada berkas dokumen (SIM, SKCK, KTP) yang diunggah untuk akun Anda di modul Dokumen Karyawan.
                    </p>
                    <div style="margin-top:10px;font-size:11.5px;color:#94a3b8;">
                        Silakan hubungi admin HR / Operasional untuk kelengkapan berkas.
                    </div>
                </div>
                @else
                <!-- List Dokumen Riil dari Tabel employee_documents -->
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach($personalDocs as $doc)
                    @php
                        $daysLeft = $doc->expiry_date ? now()->diffInDays($doc->expiry_date, false) : null;
                    @endphp
                    <div style="background:#f8fafc;padding:12px 14px;border-radius:10px;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:10px;">
                        <div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="background:#e0e7ff;color:#4338ca;font-size:10.5px;font-weight:800;padding:2px 8px;border-radius:6px;text-transform:uppercase;">
                                    {{ $doc->document_type ?? 'Dokumen' }}
                                </span>
                                <span style="font-size:13px;font-weight:700;color:#1e293b;">
                                    {{ $doc->title }}
                                </span>
                            </div>
                            @if($doc->document_number)
                            <div style="font-size:11.5px;color:#64748b;font-family:monospace;margin-top:3px;">
                                No: {{ $doc->document_number }}
                            </div>
                            @endif
                            <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                Exp: {{ $doc->expiry_date ? $doc->expiry_date->format('d/m/Y') : 'Seumur Hidup' }}
                            </div>
                        </div>

                        <div style="text-align:right;flex-shrink:0;">
                            @if(is_null($daysLeft))
                                <span class="badge badge-green" style="background:#dcfce7;color:#15803d;font-weight:700;font-size:11px;">Aktif</span>
                            @elseif($daysLeft < 0)
                                <span class="badge badge-red" style="background:#fee2e2;color:#dc2626;font-weight:700;font-size:11px;">Expired</span>
                            @elseif($daysLeft <= 30)
                                <span class="badge badge-yellow" style="background:#fef3c7;color:#b45309;font-weight:700;font-size:11px;">H-{{ $daysLeft }}</span>
                            @else
                                <span class="badge badge-green" style="background:#dcfce7;color:#15803d;font-weight:700;font-size:11px;">Berlaku</span>
                            @endif

                            @if($doc->file_path)
                            <div style="margin-top:4px;">
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" style="font-size:11px;color:#2563eb;text-decoration:none;font-weight:600;">
                                    Lihat Berkas ↗
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

    </div>

    <style>
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        @media (max-width: 992px) {
            .driver-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</div>
