<div>
    <!-- Page Header & Tab Navigasi Pengaturan -->
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title">Pengaturan Sistem Control Tower</h1>
            <p class="page-subtitle">Kelola profil tenant perusahaan, titik gudang pusat, serta toleransi GPS & notifikasi WhatsApp otomatis.</p>
        </div>
    </div>

    <!-- Sub-Navigasi Pengaturan (Tabs) -->
    <div style="display:flex;gap:8px;border-bottom:1px solid #e9ecef;margin-bottom:24px;padding-bottom:12px;overflow-x:auto;">
        <a href="{{ route('settings.company') }}"
           style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;font-size:13.5px;font-weight:500;text-decoration:none;transition:all .15s;color:#74788d;background:#f8f9fa;">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>Profil & Logo Perusahaan</span>
        </a>

        <a href="{{ route('settings.warehouse') }}"
           style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;font-size:13.5px;font-weight:500;text-decoration:none;transition:all .15s;color:#74788d;background:#f8f9fa;">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Pengaturan Gudang & Koordinat</span>
        </a>

        <a href="{{ route('settings.alerts') }}"
           style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;font-size:13.5px;font-weight:600;text-decoration:none;transition:all .15s;color:#fff;background:#556ee6;box-shadow:0 2px 6px rgba(85,110,230,.3);">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span>Pengaturan Alert & Notifikasi WA</span>
        </a>
    </div>

    <!-- Alert Flash -->
    @if (session()->has('success'))
        <div class="alert alert-success mb-4" style="border-radius:12px;display:flex;align-items:center;gap:12px;background:#e8f9f0;border:1px solid #c3f0d6;color:#1e7e48;padding:14px 18px;">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span style="font-weight:500;">{{ session('success') }}</span>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(340px, 1fr));gap:24px;">
        
        <!-- Left Column: Form Pengaturan Interval & Alert WA -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:#fef5ea;color:#f1b44c;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title" style="margin:0;font-size:15px;font-weight:600;">Jadwal Interval & Batas Toleransi GPS</h3>
                        <p style="font-size:12px;color:#74788d;margin:2px 0 0;">Atur frekuensi pengulangan alert WA (misal: 10 menit sekali) dan batas sinyal mati.</p>
                    </div>
                </div>

                <!-- Master Toggle WA -->
                <div style="display:flex;align-items:center;gap:8px;background:{{ $wa_alert_enabled ? '#edf9f2' : '#f8f9fa' }};padding:6px 14px;border-radius:20px;border:1px solid {{ $wa_alert_enabled ? '#a3e6be' : '#e2e5e8' }};">
                    <label style="cursor:pointer;display:flex;align-items:center;gap:8px;margin:0;font-size:12.5px;font-weight:600;color:{{ $wa_alert_enabled ? '#1e7e48' : '#74788d' }};">
                        <input type="checkbox" wire:model.live="wa_alert_enabled" style="accent-color:#34c38f;width:16px;height:16px;">
                        <span>Alert WA: {{ $wa_alert_enabled ? 'AKTIF' : 'NONAKTIF' }}</span>
                    </label>
                </div>
            </div>

            <div class="card-body" style="padding:22px;">
                <form wire:submit="save">

                    <!-- Info Box: Cara Kerja Alert -->
                    <div style="background:#f0f4fd;border:1px solid #dbe5fb;border-radius:12px;padding:16px;margin-bottom:24px;display:flex;gap:14px;">
                        <div style="color:#556ee6;flex-shrink:0;margin-top:2px;">
                            <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div style="font-size:12.5px;color:#384b7a;line-height:1.55;">
                            <strong>Bagaimana sistem bekerja?</strong><br>
                            Setiap 2 menit, server memeriksa armada yang sedang berjalan (<em>on_trip</em>). Jika sopir tidak mengirim update lokasi melewati batas toleransi, pesan peringatan WhatsApp akan dikirimkan otomatis. Jika kondisi masih berlanjut, pesan akan diulang kembali setiap <strong>{{ $heartbeat_cooldown_minutes }} menit sekali</strong> sesuai interval yang Anda tetapkan di bawah.
                        </div>
                    </div>

                    <!-- 1. PENGATURAN COOLDOWN / INTERVAL PENGULANGAN PESAN WA -->
                    <div style="background:#fff;border:1px solid #eaedf1;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.03);">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:12px;">
                            <div>
                                <label class="form-label" style="font-size:13.5px;font-weight:600;color:#2a3042;margin-bottom:4px;display:block;">
                                    ⏱️ Interval Pengulangan Alert WhatsApp (Cooldown Anti-Spam) <span style="color:#f46a6a;">*</span>
                                </label>
                                <p style="font-size:12px;color:#74788d;margin:0;">
                                    Berapa menit jeda antara pengiriman pesan WA jika GPS sopir tetap mati? Mencegah spam pesan terus-menerus.
                                </p>
                            </div>
                            
                            <!-- Preset Buttons -->
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <span style="font-size:11.5px;color:#74788d;margin-right:2px;">Pilihan Cepat:</span>
                                @foreach([5 => '5 Mnt', 10 => '10 Mnt (Standar)', 15 => '15 Mnt', 30 => '30 Mnt', 60 => '1 Jam'] as $mnt => $label)
                                    <button type="button" 
                                            wire:click="setCooldown({{ $mnt }})"
                                            style="border:1px solid {{ $heartbeat_cooldown_minutes == $mnt ? '#556ee6' : '#ced4da' }};background:{{ $heartbeat_cooldown_minutes == $mnt ? '#edf0fd' : '#fff' }};color:{{ $heartbeat_cooldown_minutes == $mnt ? '#556ee6' : '#495057' }};font-weight:{{ $heartbeat_cooldown_minutes == $mnt ? '600' : '400' }};font-size:11.5px;padding:4px 10px;border-radius:6px;cursor:pointer;transition:all .15s;">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;gap:12px;max-width:320px;">
                            <input type="number" 
                                   wire:model.live="heartbeat_cooldown_minutes" 
                                   min="1" 
                                   max="1440" 
                                   class="form-input @error('heartbeat_cooldown_minutes') form-input-error @enderror" 
                                   style="font-size:15px;font-weight:600;padding:8px 12px;">
                            <span style="font-size:13px;font-weight:500;color:#495057;white-space:nowrap;">Menit sekali</span>
                        </div>
                        @error('heartbeat_cooldown_minutes') 
                            <p class="form-error" style="color:#f46a6a;font-size:12px;margin-top:4px;">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- 2. PENGATURAN BATAS TOLERANSI GPS HILANG SINYAL (TIMEOUT) -->
                    <div style="background:#fff;border:1px solid #eaedf1;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.03);">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:12px;">
                            <div>
                                <label class="form-label" style="font-size:13.5px;font-weight:600;color:#2a3042;margin-bottom:4px;display:block;">
                                    📡 Batas Toleransi GPS Hilang Sinyal (Timeout) <span style="color:#f46a6a;">*</span>
                                </label>
                                <p style="font-size:12px;color:#74788d;margin:0;">
                                    Berapa menit sejak ping GPS terakhir sebelum sistem menyatakan armada <em>Silent / Hilang Kontak</em> dan memicu alert pertama.
                                </p>
                            </div>
                            
                            <!-- Preset Buttons -->
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <span style="font-size:11.5px;color:#74788d;margin-right:2px;">Pilihan Cepat:</span>
                                @foreach([3 => '3 Mnt', 5 => '5 Mnt (Standar)', 10 => '10 Mnt', 15 => '15 Mnt'] as $mnt => $label)
                                    <button type="button" 
                                            wire:click="setTimeout({{ $mnt }})"
                                            style="border:1px solid {{ $heartbeat_timeout_minutes == $mnt ? '#556ee6' : '#ced4da' }};background:{{ $heartbeat_timeout_minutes == $mnt ? '#edf0fd' : '#fff' }};color:{{ $heartbeat_timeout_minutes == $mnt ? '#556ee6' : '#495057' }};font-weight:{{ $heartbeat_timeout_minutes == $mnt ? '600' : '400' }};font-size:11.5px;padding:4px 10px;border-radius:6px;cursor:pointer;transition:all .15s;">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;gap:12px;max-width:320px;">
                            <input type="number" 
                                   wire:model.live="heartbeat_timeout_minutes" 
                                   min="1" 
                                   max="120" 
                                   class="form-input @error('heartbeat_timeout_minutes') form-input-error @enderror" 
                                   style="font-size:15px;font-weight:600;padding:8px 12px;">
                            <span style="font-size:13px;font-weight:500;color:#495057;white-space:nowrap;">Menit tidak update</span>
                        </div>
                        @error('heartbeat_timeout_minutes') 
                            <p class="form-error" style="color:#f46a6a;font-size:12px;margin-top:4px;">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- 3. TARGET PENERIMA NOTIFIKASI WHATSAPP -->
                    <div style="background:#fff;border:1px solid #eaedf1;border-radius:12px;padding:20px;margin-bottom:24px;box-shadow:0 1px 3px rgba(0,0,0,.03);">
                        <label class="form-label" style="font-size:13.5px;font-weight:600;color:#2a3042;margin-bottom:12px;display:block;">
                            👥 Sasaran Penerima Pesan WhatsApp
                        </label>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:14px;margin-bottom:16px;">
                            
                            <!-- Toggle Sopir -->
                            <label style="border:1px solid {{ $wa_notify_driver ? '#556ee6' : '#e2e5e8' }};background:{{ $wa_notify_driver ? '#f8f9ff' : '#fff' }};padding:12px 16px;border-radius:10px;cursor:pointer;display:flex;align-items:flex-start;gap:12px;transition:all .15s;">
                                <input type="checkbox" wire:model.live="wa_notify_driver" style="accent-color:#556ee6;margin-top:3px;width:16px;height:16px;">
                                <div>
                                    <span style="font-size:13px;font-weight:600;color:#2a3042;display:block;">Kirim Alert ke Sopir</span>
                                    <span style="font-size:11.5px;color:#74788d;">Memberitahu sopir lewat WA agar segera membuka kembali aplikasi.</span>
                                </div>
                            </label>

                            <!-- Toggle Dispatcher -->
                            <label style="border:1px solid {{ $wa_notify_dispatcher ? '#556ee6' : '#e2e5e8' }};background:{{ $wa_notify_dispatcher ? '#f8f9ff' : '#fff' }};padding:12px 16px;border-radius:10px;cursor:pointer;display:flex;align-items:flex-start;gap:12px;transition:all .15s;">
                                <input type="checkbox" wire:model.live="wa_notify_dispatcher" style="accent-color:#556ee6;margin-top:3px;width:16px;height:16px;">
                                <div>
                                    <span style="font-size:13px;font-weight:600;color:#2a3042;display:block;">Kirim Alert ke Dispatcher</span>
                                    <span style="font-size:11.5px;color:#74788d;">Kirim detail insiden armada ke seluruh tim dispatcher & fleet officer.</span>
                                </div>
                            </label>

                        </div>

                        <!-- Nomor Khusus Control Tower / Admin Darurat -->
                        <div style="border-top:1px dashed #e9ecef;padding-top:14px;margin-top:10px;">
                            <label class="form-label" style="font-size:12.5px;font-weight:600;color:#495057;margin-bottom:4px;display:block;">
                                🚨 Nomor WhatsApp Khusus Control Tower / Admin Pemantau (Opsional)
                            </label>
                            <div style="display:flex;gap:10px;align-items:center;">
                                <div style="position:relative;flex:1;max-width:360px;">
                                    <span style="position:absolute;left:12px;top:9px;color:#adb5bd;font-size:13px;">📞</span>
                                    <input type="text" 
                                           wire:model="control_tower_phone" 
                                           placeholder="Contoh: 08123456789 atau 628123456789" 
                                           class="form-input @error('control_tower_phone') form-input-error @enderror"
                                           style="padding-left:36px;">
                                </div>
                                <span style="font-size:11.5px;color:#74788d;">Broadcast darurat juga akan diteruskan ke nomor ini.</span>
                            </div>
                            @error('control_tower_phone') 
                                <p class="form-error" style="color:#f46a6a;font-size:12px;margin-top:4px;">{{ $message }}</p> 
                            @enderror
                        </div>

                    </div>

                    <!-- Tombol Simpan -->
                    <div style="display:flex;justify-content:flex-end;gap:12px;padding-top:16px;border-top:1px solid #eff2f7;">
                        <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:13.5px;font-weight:600;" wire:loading.attr="disabled">
                            <span wire:loading.remove>Simpan Pengaturan Alert</span>
                            <span wire:loading>Menyimpan Konfigurasi...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Right Column: Status Gateway & Simulasi Pesan WhatsApp -->
        <div style="display:flex;flex-direction:column;gap:20px;">
            
            <!-- 1. Gateway Status & Test Kirim WA -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;">
                    <h3 class="card-title" style="margin:0;font-size:14px;font-weight:600;">Status Gateway Wablas</h3>
                    @if($hasWablasToken)
                        <span style="background:#e8f9f0;color:#1e7e48;font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:12px;display:flex;align-items:center;gap:5px;">
                            <span style="width:6px;height:6px;background:#34c38f;border-radius:50%;display:inline-block;"></span>
                            Token Terpasang
                        </span>
                    @else
                        <span style="background:#fef2f2;color:#dc2626;font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:12px;display:flex;align-items:center;gap:5px;">
                            <span style="width:6px;height:6px;background:#ef4444;border-radius:50%;display:inline-block;"></span>
                            Token Belum Ada
                        </span>
                    @endif
                </div>

                <div class="card-body" style="padding:16px 18px;">
                    <div style="font-size:12px;color:#74788d;margin-bottom:14px;line-height:1.5;">
                        Gateway: <code style="background:#f1f3f5;padding:2px 6px;border-radius:4px;color:#2a3042;">{{ $wablasBaseUrl }}</code>
                    </div>

                    <!-- Form Uji Coba Kirim WA Instan -->
                    <div style="background:#f8f9fa;border:1px solid #eaedf1;border-radius:10px;padding:14px;">
                        <label style="font-size:12px;font-weight:600;color:#495057;margin-bottom:6px;display:block;">
                            🧪 Uji Coba Pengiriman WhatsApp
                        </label>
                        <div style="display:flex;gap:8px;margin-bottom:8px;">
                            <input type="text" 
                                   wire:model="test_phone" 
                                   placeholder="Nomor HP: 0812xxxx" 
                                   class="form-input" 
                                   style="font-size:12.5px;padding:6px 10px;">
                            <button type="button" 
                                    wire:click="sendTestWhatsApp" 
                                    class="btn btn-outline-primary btn-sm"
                                    style="white-space:nowrap;padding:6px 12px;font-size:12px;"
                                    wire:loading.attr="disabled"
                                    wire:target="sendTestWhatsApp">
                                <span wire:loading.remove wire:target="sendTestWhatsApp">Kirim Tes</span>
                                <span wire:loading wire:target="sendTestWhatsApp">Mengirim...</span>
                            </button>
                        </div>

                        @error('test_phone')
                            <p style="color:#f46a6a;font-size:11.5px;margin:4px 0 0;">{{ $message }}</p>
                        @enderror

                        @if ($test_result_type === 'success')
                            <div style="background:#e8f9f0;border:1px solid #c3f0d6;color:#1e7e48;padding:8px 10px;border-radius:6px;font-size:11.5px;margin-top:8px;">
                                {{ $test_result_message }}
                            </div>
                        @elseif ($test_result_type === 'error')
                            <div style="background:#fef2f2;border:1px solid #fed7d7;color:#991b1b;padding:8px 10px;border-radius:6px;font-size:11.5px;margin-top:8px;">
                                {{ $test_result_message }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Simulasi Tampilan Pesan WhatsApp (Mockup Bubble) -->
            <div class="card">
                <div class="card-header" style="padding:14px 18px;display:flex;align-items:center;justify-content:space-between;">
                    <h3 class="card-title" style="margin:0;font-size:14px;font-weight:600;">Simulasi Pesan WhatsApp</h3>
                    <span style="font-size:11px;color:#74788d;">Preview Format</span>
                </div>
                
                <div class="card-body" style="padding:16px 18px;background:#e5ddd5;border-bottom-left-radius:12px;border-bottom-right-radius:12px;">
                    <!-- Mock WhatsApp Chat Bubble -->
                    <div style="background:#fff;border-radius:10px;padding:12px 14px;max-width:100%;box-shadow:0 1px 2px rgba(0,0,0,.15);position:relative;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.45;color:#111b21;">
                        <div style="color:#d93025;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:4px;">
                            <span>🚨</span> PERINGATAN CONTROL TOWER {{ \App\Models\CompanySetting::getName() }}
                        </div>
                        <div style="font-weight:700;margin-bottom:8px;color:#111b21;">
                            ⚠️ Armada Kehilangan Kontak GPS
                        </div>
                        <div style="margin-bottom:8px;background:#f8f9fa;padding:8px;border-radius:6px;border-left:3px solid #f46a6a;">
                            <div>• Sopir : <strong>Budi Santoso</strong></div>
                            <div>• Kendaraan : <strong>B 9876 XYZ</strong></div>
                            <div>• Tujuan : Pelabuhan Tanjung Priok</div>
                            <div>• Terputus : <strong>{{ $heartbeat_timeout_minutes }} Menit</strong></div>
                            <div>• Pukul : {{ now()->timezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                        </div>
                        <div style="color:#54656f;font-size:11px;margin-bottom:6px;">
                            <em>Alert berulang dijadwalkan setiap {{ $heartbeat_cooldown_minutes }} menit sekali jika GPS belum menyala kembali.</em>
                        </div>
                        <div style="text-align:right;color:#667781;font-size:10px;margin-top:4px;">
                            {{ now()->timezone('Asia/Jakarta')->format('H:i') }} <span style="color:#53bdeb;">✓✓</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
