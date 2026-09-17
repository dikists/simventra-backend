<div>
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Pengaturan Profil & Logo Perusahaan</h1>
            <p class="page-subtitle">Kelola nama identitas perusahaan, logo resmi, dan informasi kontak yang tampil pada dashboard Control Tower.</p>
        </div>
    </div>

    <!-- Alert Flash -->
    @if (session()->has('success'))
        <div class="alert alert-success mb-4" style="border-radius:12px;display:flex;align-items:center;gap:12px;">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:24px;">
        
        <!-- Left Column: Form & Identity -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="stat-icon stat-icon-blue" style="width:36px;height:36px;border-radius:8px;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">Identitas Perusahaan (Client Tenant)</h3>
                        <p style="font-size:11.5px;color:#74788d;margin:0;">Informasi ini tampil di sidebar kiri, header, dan laporan dokumen.</p>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form wire:submit="save">
                    
                    <!-- Logo Upload Section (Pola Form Standard SIMVENTRA) -->
                    <div style="background:#f8f9fa;border:1px solid #eff2f7;border-radius:12px;padding:20px;margin-bottom:24px;">
                        <div style="display:flex;flex-wrap:wrap;align-items:flex-start;gap:20px;">
                            
                            <!-- Preview Box -->
                            <div style="text-align:center;">
                                <div style="width:84px;height:84px;border-radius:12px;background:#fff;border:1px solid #ced4da;display:flex;align-items:center;justify-content:center;padding:6px;box-shadow:0 2px 8px rgba(0,0,0,.06);position:relative;overflow:hidden;">
                                    @if ($file)
                                        <img src="{{ $file->temporaryUrl() }}" alt="Preview Logo" style="width:100%;height:100%;object-fit:contain;">
                                    @elseif ($current_logo)
                                        <img src="{{ $current_logo }}" alt="Logo Aktif" style="width:100%;height:100%;object-fit:contain;">
                                    @else
                                        <span style="font-size:11px;color:#adb5bd;">Belum ada logo</span>
                                    @endif
                                </div>
                                <span style="display:inline-block;font-size:11px;color:#74788d;margin-top:4px;">
                                    {{ $file ? 'Pratinjau Baru' : 'Logo Aktif' }}
                                </span>
                            </div>

                            <!-- Input File Standard Sesuai Modul Dokumen -->
                            <div style="flex:1;min-width:240px;">
                                <label class="form-label">Upload File Logo (Gambar, max 2MB)</label>
                                <input wire:model="file" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="form-input">
                                
                                <div wire:loading wire:target="file" style="font-size:12px;color:#556ee6;margin-top:4px;">
                                    Mengupload...
                                </div>

                                @error('file') 
                                    <p class="form-error">{{ $message }}</p> 
                                @enderror

                                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;flex-wrap:wrap;gap:8px;">
                                    <span style="font-size:11.5px;color:#74788d;">
                                        Format: PNG, JPG, WEBP, SVG (Rekomendasi rasio 1:1 transparan).
                                    </span>

                                    @if ($current_logo && $current_logo !== '/assets/logo_rhl.png')
                                        <button type="button" wire:click="resetToDefaultLogo" class="btn btn-soft-danger btn-sm" onclick="return confirm('Kembalikan logo ke logo bawaan RHL?')">
                                            Reset ke Default RHL
                                        </button>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Company Form Fields Grid -->
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:16px;">
                        
                        <!-- Nama Lengkap Perusahaan -->
                        <div style="grid-column:span 2;">
                            <label class="form-label">Nama Lengkap Perusahaan <span style="color:#f46a6a;">*</span></label>
                            <input type="text" wire:model="name" class="form-input @error('name') form-input-error @enderror" placeholder="Contoh: PT Rajawali Handal Logistik">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nama Singkat di Sidebar -->
                        <div>
                            <label class="form-label">Nama Singkat (Baris 1 Sidebar) <span style="color:#f46a6a;">*</span></label>
                            <input type="text" wire:model="short_name" class="form-input @error('short_name') form-input-error @enderror" placeholder="Contoh: Rajawali Handal">
                            @error('short_name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tagline / Subtitle -->
                        <div>
                            <label class="form-label">Kategori / Tagline (Baris 2 Sidebar)</label>
                            <input type="text" wire:model="tagline" class="form-input @error('tagline') form-input-error @enderror" placeholder="Contoh: Logistik atau Transportasi">
                            @error('tagline') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <!-- No Telepon -->
                        <div>
                            <label class="form-label">Nomor Telepon Kantor</label>
                            <input type="text" wire:model="phone" class="form-input @error('phone') form-input-error @enderror" placeholder="Contoh: 021-5558899">
                            @error('phone') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="form-label">Email Resmi Perusahaan</label>
                            <input type="email" wire:model="email" class="form-input @error('email') form-input-error @enderror" placeholder="Contoh: ops@rajawalihandal.co.id">
                            @error('email') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <!-- Alamat -->
                        <div style="grid-column:span 2;">
                            <label class="form-label">Alamat Kantor Pusat</label>
                            <textarea wire:model="address" rows="2" class="form-input @error('address') form-input-error @enderror" placeholder="Alamat kantor pusat atau pool logistik utama..."></textarea>
                            @error('address') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div style="display:flex;justify-content:flex-end;gap:12px;padding-top:16px;border-top:1px solid #eff2f7;">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Simpan Perubahan Identitas</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Right Column: Sidebar Preview Simulation -->
        <div class="card" style="height:fit-content;">
            <div class="card-header">
                <h3 class="card-title">Simulasi Sidebar Dashboard</h3>
            </div>
            <div class="card-body">
                <p style="font-size:12px;color:#74788d;margin-bottom:14px;">
                    Begini tampilan logo dan nama perusahaan Anda saat dilihat oleh staf di menu navigasi utama:
                </p>

                <!-- Sidebar Box Mockup -->
                <div style="background:#2a3042;border-radius:12px;padding:16px;box-shadow:0 6px 18px rgba(0,0,0,.15);margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;padding:3px;box-shadow:0 2px 8px rgba(0,0,0,.2);overflow:hidden;flex-shrink:0;">
                            @if ($file)
                                <img src="{{ $file->temporaryUrl() }}" style="width:100%;height:100%;object-fit:contain;">
                            @elseif ($current_logo)
                                <img src="{{ $current_logo }}" style="width:100%;height:100%;object-fit:contain;">
                            @endif
                        </div>
                        <div style="min-width:0;flex:1;">
                            <div style="font-size:15px;font-weight:700;color:#fff;letter-spacing:.3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $short_name ?: 'Rajawali Handal' }}
                            </div>
                            <div style="font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $tagline ?: 'Logistik' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info" style="font-size:11.5px;line-height:1.5;">
                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>
                        Halaman depan / login tetap menampilkan identitas <strong>SIMVENTRA</strong> sebagai platform utama. Logo dan nama perusahaan yang Anda unggah di sini akan eksklusif tampil di dashboard operasional tim Anda.
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>
