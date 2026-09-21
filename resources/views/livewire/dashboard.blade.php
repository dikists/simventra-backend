<div>
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong> · {{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <a href="{{ route('reminders.index') }}" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Reminder Dokumen
        </a>
    </div>

    <!-- Control Tower Fleet GPS Alert -->
    @if(isset($silentTrips) && count($silentTrips) > 0)
    <div style="background:linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%);border:2px solid #ef4444;border-radius:16px;padding:20px 24px;margin-bottom:24px;box-shadow:0 10px 25px -5px rgba(239,68,68,0.2);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div style="width:46px;height:46px;border-radius:12px;background:#ef4444;color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;box-shadow:0 4px 14px rgba(239,68,68,0.4);">
                    🚨
                </div>
                <div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <h3 style="margin:0;font-size:16px;font-weight:800;color:#991b1b;letter-spacing:0.3px;">
                            ALERT CONTROL TOWER: {{ count($silentTrips) }} ARMADA TERPUTUS SINYAL GPS!
                        </h3>
                        <span style="background:#dc2626;color:#fff;font-size:11px;font-weight:800;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;">
                            Pelacakan Hilang
                        </span>
                    </div>
                    <p style="margin:5px 0 0 0;font-size:13px;color:#7f1d1d;line-height:1.5;">
                        Sistem mendeteksi HP sopir tidak mengirim koordinat GPS lebih dari 5 menit saat perjalanan aktif. Kemungkinan aplikasi tertutup, HP mati, atau di area blank spot.
                    </p>
                </div>
            </div>
            <a href="{{ route('tracking.index') }}" class="btn" style="background:#dc2626;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:13px;box-shadow:0 4px 12px rgba(220,38,38,0.35);">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Buka Peta Live GPS →
            </a>
        </div>

        <!-- Cards Unit yang Hilang Sinyal -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:14px;margin-top:16px;">
            @foreach($silentTrips as $item)
            <div style="background:#fff;border:1.5px solid #fca5a5;border-radius:12px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:14px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="background:#0f172a;color:#fff;padding:6px 12px;border-radius:8px;font-family:monospace;font-weight:800;font-size:13px;letter-spacing:1px;box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                        {{ $item['license_plate'] }}
                    </div>
                    <div>
                        <div style="font-weight:700;color:#1e293b;font-size:14px;">{{ $item['driver_name'] }}</div>
                        <div style="font-size:12px;color:#dc2626;font-weight:700;margin-top:2px;">
                            ⚠️ Diam: {{ $item['silence_mins'] }} Menit <span style="color:#64748b;font-weight:500;">(Terakhir: {{ $item['last_ping'] }})</span>
                        </div>
                        <div style="font-size:11.5px;color:#64748b;margin-top:1px;">
                            Tujuan: <strong>{{ $item['destination'] }}</strong>
                        </div>
                    </div>
                </div>

                <div>
                    @if(!empty($item['driver_phone']))
                    @php
                        $cleanPhone = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $item['driver_phone']));
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($item['driver_name']) }},%20sistem%20SIMVENTRA%20mendeteksi%20tracking%20GPS%20Anda%20terputus%20sudah%20{{ $item['silence_mins'] }}%20menit.%20Mohon%20segera%20buka%20kembali%20aplikasi%20SIMVENTRA%20Driver." 
                       target="_blank" 
                       class="btn" 
                       style="background:#22c55e;color:#fff;padding:8px 14px;border-radius:8px;font-size:12.5px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;box-shadow:0 3px 8px rgba(34,197,94,0.3);" 
                       title="Hubungi Sopir via WhatsApp">
                        <span>💬 WA Sopir</span>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Critical Alert Dokumen -->
    @if($stats['expiring_soon_7'] > 0)
    <div class="alert alert-danger mb-6">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <strong>Peringatan Kritis!</strong> Terdapat <strong>{{ $stats['expiring_soon_7'] }} dokumen</strong> yang kadaluarsa dalam 7 hari ke depan.
            <a href="{{ route('reminders.index') }}" style="font-weight:700;text-decoration:underline;margin-left:8px;">Tinjau Sekarang →</a>
        </div>
    </div>
    @endif

    <!-- ========= STAT CARDS ROW 1 ========= -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:20px;" class="stats-grid">

        <!-- Total Karyawan -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-label" style="margin-top:0;margin-bottom:8px;">Total Karyawan Aktif</div>
                    <div class="stat-value">{{ number_format($stats['total_employees']) }}</div>
                    <div style="display:flex;align-items:center;gap:6px;margin-top:8px;">
                        <span class="badge badge-blue">{{ $stats['total_drivers'] }} Sopir</span>
                    </div>
                </div>
                <div class="stat-icon stat-icon-blue">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <!-- mini progress bar -->
            <div style="height:3px;background:#eff2f7;border-radius:3px;margin-top:12px;">
                <div style="height:3px;background:linear-gradient(90deg,#556ee6,#6f42c1);border-radius:3px;width:70%;"></div>
            </div>
        </div>

        <!-- Total Kendaraan -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-label" style="margin-top:0;margin-bottom:8px;">Kendaraan Aktif</div>
                    <div class="stat-value">{{ number_format($stats['total_vehicles']) }}</div>
                    <div style="margin-top:8px;">
                        @if(($stats['silent_trips_count'] ?? 0) > 0)
                        <span class="badge badge-red" style="background:#fee2e2;color:#dc2626;font-weight:700;">
                            ⚠️ {{ $stats['silent_trips_count'] }} Sinyal Putus
                        </span>
                        @else
                        <span class="badge badge-green">Beroperasi Normal</span>
                        @endif
                    </div>
                </div>
                <div class="stat-icon stat-icon-green">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
            </div>
            <div style="height:3px;background:#eff2f7;border-radius:3px;margin-top:12px;">
                <div style="height:3px;background:linear-gradient(90deg,#34c38f,#20a373);border-radius:3px;width:80%;"></div>
            </div>
        </div>

        <!-- Dokumen Kritis -->
        <div class="stat-card" style="{{ $stats['expiring_soon_7'] > 0 ? 'border:1px solid rgba(244,106,106,.3);' : '' }}">
            <div class="stat-card-top">
                <div>
                    <div class="stat-label" style="margin-top:0;margin-bottom:8px;">Dokumen Kritis (H-7)</div>
                    <div class="stat-value" style="{{ $stats['expiring_soon_7'] > 0 ? 'color:#f46a6a;' : '' }}">{{ $stats['expiring_soon_7'] }}</div>
                    <div style="margin-top:8px;">
                        <span class="badge badge-{{ $stats['expiring_soon_7'] > 0 ? 'red' : 'gray' }}">{{ $stats['expiring_soon_7'] > 0 ? 'Butuh Perhatian' : 'Aman' }}</span>
                    </div>
                </div>
                <div class="stat-icon stat-icon-{{ $stats['expiring_soon_7'] > 0 ? 'red' : 'teal' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div style="height:3px;background:#eff2f7;border-radius:3px;margin-top:12px;">
                <div style="height:3px;background:{{ $stats['expiring_soon_7'] > 0 ? 'linear-gradient(90deg,#f46a6a,#c73e3e)' : '#eff2f7' }};border-radius:3px;width:{{ $stats['expiring_soon_7'] > 0 ? '85%' : '0%' }};"></div>
            </div>
        </div>

        <!-- Dokumen H-30 -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-label" style="margin-top:0;margin-bottom:8px;">Akan Habis (H-30)</div>
                    <div class="stat-value" style="{{ $stats['expiring_soon_30'] > 0 ? 'color:#f1b44c;' : '' }}">{{ $stats['expiring_soon_30'] }}</div>
                    <div style="margin-top:8px;">
                        <span class="badge badge-yellow">Perlu Diperpanjang</span>
                    </div>
                </div>
                <div class="stat-icon stat-icon-yellow">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div style="height:3px;background:#eff2f7;border-radius:3px;margin-top:12px;">
                <div style="height:3px;background:linear-gradient(90deg,#f1b44c,#d48c1e);border-radius:3px;width:{{ $stats['expiring_soon_30'] > 0 ? '60%' : '0%' }};"></div>
            </div>
        </div>
    </div>

    <!-- ========= MINI STATS ROW 2 ========= -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:24px;">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(111,66,193,.1);">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#6f42c1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#343a40;">{{ $stats['total_drivers'] }}</div>
                <div style="font-size:12.5px;color:#74788d;margin-top:2px;">Sopir Aktif</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(244,106,106,.1);">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#f46a6a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#f46a6a;">{{ $stats['expired_vehicle_docs'] }}</div>
                <div style="font-size:12.5px;color:#74788d;margin-top:2px;">Dok. Kendaraan Expired</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(244,106,106,.1);">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#f46a6a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#f46a6a;">{{ $stats['expired_employee_docs'] }}</div>
                <div style="font-size:12.5px;color:#74788d;margin-top:2px;">Dok. Karyawan Expired</div>
            </div>
        </div>
    </div>

    <!-- ========= TABLES ROW ========= -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <!-- Dokumen Kendaraan Akan Habis -->
        <div class="table-card">
            <div class="table-card-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="card-title-icon" style="background:rgba(85,110,230,.1);">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#556ee6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <div>
                        <div class="card-title">Dok. Kendaraan – Akan Habis</div>
                        <div style="font-size:11.5px;color:#adb5bd;">30 hari ke depan</div>
                    </div>
                </div>
                <a href="{{ route('documents.vehicles') }}" style="font-size:12.5px;color:#556ee6;font-weight:600;text-decoration:none;">Lihat Semua →</a>
            </div>
            @if($recentExpiringVehicleDocs->isEmpty())
            <div class="empty-state" style="padding:32px;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="empty-state-title">Semua Aman</div>
                <div class="empty-state-desc">Tidak ada dokumen yang akan habis dalam 30 hari</div>
            </div>
            @else
            <table class="data-table">
                <thead><tr><th>Kendaraan</th><th>Dokumen</th><th>Kadaluarsa</th><th>Sisa</th></tr></thead>
                <tbody>
                    @foreach($recentExpiringVehicleDocs as $doc)
                    @php $days = now()->diffInDays($doc->expiry_date, false); @endphp
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:13px;font-family:monospace;">{{ $doc->vehicle?->license_plate }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;">{{ $doc->vehicle?->brand }}</div>
                        </td>
                        <td style="font-size:13px;">{{ $doc->title }}</td>
                        <td style="font-size:13px;">{{ $doc->expiry_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge badge-{{ $days <= 7 ? 'red' : ($days <= 14 ? 'orange' : 'yellow') }}">
                                H-{{ $days }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <!-- Dokumen Karyawan Akan Habis -->
        <div class="table-card">
            <div class="table-card-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="card-title-icon" style="background:rgba(52,195,143,.1);">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#34c38f"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <div class="card-title">Dok. Karyawan – Akan Habis</div>
                        <div style="font-size:11.5px;color:#adb5bd;">30 hari ke depan</div>
                    </div>
                </div>
                <a href="{{ route('documents.employees') }}" style="font-size:12.5px;color:#556ee6;font-weight:600;text-decoration:none;">Lihat Semua →</a>
            </div>
            @if($recentExpiringEmployeeDocs->isEmpty())
            <div class="empty-state" style="padding:32px;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="empty-state-title">Semua Aman</div>
                <div class="empty-state-desc">Tidak ada dokumen yang akan habis dalam 30 hari</div>
            </div>
            @else
            <table class="data-table">
                <thead><tr><th>Karyawan</th><th>Dokumen</th><th>Kadaluarsa</th><th>Sisa</th></tr></thead>
                <tbody>
                    @foreach($recentExpiringEmployeeDocs as $doc)
                    @php $days = now()->diffInDays($doc->expiry_date, false); @endphp
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:13px;">{{ $doc->employee?->name }}</div>
                            <div style="font-size:11.5px;color:#adb5bd;">{{ $doc->employee?->employee_number }}</div>
                        </td>
                        <td style="font-size:13px;">{{ $doc->title }}</td>
                        <td style="font-size:13px;">{{ $doc->expiry_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge badge-{{ $days <= 7 ? 'red' : ($days <= 14 ? 'orange' : 'yellow') }}">
                                H-{{ $days }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    <style>
        @media (max-width: 1280px) {
            .stats-grid { grid-template-columns: repeat(2,1fr) !important; }
        }
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr !important; }
            div[style*="grid-template-columns:repeat(3,1fr)"],
            div[style*="grid-template-columns:1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</div>
