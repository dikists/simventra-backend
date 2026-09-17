<div>
    <div class="page-header" style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <a href="{{ route('vehicles.index') }}" class="btn btn-secondary" style="padding:8px 14px;border-radius:8px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <div>
                <h1 class="page-title">{{ $isEdit ? 'Edit Kendaraan' : 'Tambah Kendaraan' }}</h1>
                <p class="page-subtitle">{{ $isEdit ? 'Perbarui data armada kendaraan operasional' : 'Daftarkan unit kendaraan baru ke armada operasional' }}</p>
            </div>
        </div>
    </div>

    <!-- Error Alert Box -->
    @if ($errors->any())
        <div class="alert alert-danger mb-4" style="border-radius:12px;display:flex;align-items:flex-start;gap:12px;">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <div style="font-weight:700;margin-bottom:4px;">Gagal menyimpan kendaraan. Mohon periksa isian berikut:</div>
                <ul style="margin:0;padding-left:18px;font-size:12.5px;list-style-type:disc;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Main -->
            <div class="xl:col-span-2 flex flex-col gap-6">
                <!-- Identitas Kendaraan -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Identitas Kendaraan</h3></div>
                    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Kode Kendaraan *</label>
                            <input wire:model="vehicle_code" type="text" class="form-input @error('vehicle_code') form-input-error @enderror" placeholder="KND-0001">
                            @error('vehicle_code') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Nomor Polisi *</label>
                            <input wire:model="license_plate" type="text" class="form-input @error('license_plate') form-input-error @enderror" placeholder="B 1234 ABC">
                            @error('license_plate') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Merek *</label>
                            <input wire:model="brand" type="text" class="form-input @error('brand') form-input-error @enderror" placeholder="Toyota, Daihatsu, Mitsubishi, dll">
                            @error('brand') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Tipe/Model</label>
                            <input wire:model="model" type="text" class="form-input @error('model') form-input-error @enderror" placeholder="Grandmax, Canter, Blindvan, dll">
                            @error('model') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Jenis Kendaraan *</label>
                            <input wire:model="vehicle_type" type="text" class="form-input @error('vehicle_type') form-input-error @enderror" placeholder="Blindvan, Box, Truk, Pickup, dll">
                            @error('vehicle_type') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Tahun</label>
                            <input wire:model="year" type="number" class="form-input @error('year') form-input-error @enderror" placeholder="{{ date('Y') }}" min="1990">
                            @error('year') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Warna</label>
                            <input wire:model="color" type="text" class="form-input @error('color') form-input-error @enderror" placeholder="Putih, Hitam, dll">
                            @error('color') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Bahan Bakar *</label>
                            <select wire:model="fuel_type" class="form-select @error('fuel_type') form-input-error @enderror">
                                <option value="solar">Solar</option>
                                <option value="bensin">Bensin</option>
                                <option value="listrik">Listrik</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                            @error('fuel_type') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">No. Rangka</label>
                            <input wire:model="chassis_number" type="text" class="form-input @error('chassis_number') form-input-error @enderror" placeholder="Nomor rangka">
                            @error('chassis_number') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">No. Mesin</label>
                            <input wire:model="engine_number" type="text" class="form-input @error('engine_number') form-input-error @enderror" placeholder="Nomor mesin">
                            @error('engine_number') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Kapasitas Muatan (kg)</label>
                            <input wire:model="max_capacity_kg" type="number" step="0.01" class="form-input @error('max_capacity_kg') form-input-error @enderror" placeholder="Contoh: 2000">
                            @error('max_capacity_kg') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Odometer Saat Ini (km)</label>
                            <input wire:model="current_odometer_km" type="number" step="0.01" class="form-input @error('current_odometer_km') form-input-error @enderror" placeholder="Contoh: 15000">
                            @error('current_odometer_km') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">ID GPS Tracker</label>
                            <input wire:model="gps_device_id" type="text" class="form-input @error('gps_device_id') form-input-error @enderror" placeholder="Device ID dari GPS tracker">
                            @error('gps_device_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Sopir yang Ditugaskan</label>
                            <select wire:model="assigned_driver_id" class="form-select @error('assigned_driver_id') form-input-error @enderror">
                                <option value="">– Belum Ditugaskan –</option>
                                @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->employee_number }})</option>
                                @endforeach
                            </select>
                            @error('assigned_driver_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Catatan</h3></div>
                    <div class="card-body">
                        <textarea wire:model="notes" rows="3" class="form-input @error('notes') form-input-error @enderror" placeholder="Catatan kondisi, riwayat, atau informasi tambahan..."></textarea>
                        @error('notes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="flex flex-col gap-6">
                <!-- Foto -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Foto Kendaraan</h3></div>
                    <div class="card-body text-center">
                        @if($photo && method_exists($photo, 'temporaryUrl'))
                            <div class="relative mb-3">
                                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-40 rounded-xl mx-auto object-cover border border-slate-200">
                                <button type="button" wire:click="removePhoto" class="btn btn-soft-danger btn-sm mt-2" style="font-size:11px;">
                                    ✕ Hapus / Ganti Foto
                                </button>
                            </div>
                        @elseif($isEdit && ($existingPhoto ?? $vehicle?->photo))
                            <div class="relative mb-3">
                                <img src="{{ Storage::disk(config('filesystems.default_public_disk'))->url($existingPhoto ?? $vehicle->photo) }}" class="w-full h-40 rounded-xl mx-auto object-cover border border-slate-200">
                                <button type="button" wire:click="removePhoto" class="btn btn-soft-danger btn-sm mt-2" style="font-size:11px;">
                                    ✕ Hapus Foto
                                </button>
                            </div>
                        @else
                            <div class="w-full h-36 rounded-xl bg-slate-100 flex flex-col items-center justify-center mb-3 border-2 border-dashed border-slate-200">
                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <p class="text-xs text-slate-400 mt-2">Belum ada foto</p>
                            </div>
                        @endif

                        <label for="vehicle_photo_input" class="btn btn-secondary cursor-pointer w-full justify-center" style="gap:8px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $photo ? 'Ganti Foto' : 'Pilih Foto' }}</span>
                        </label>
                        <input id="vehicle_photo_input" wire:model="photo" type="file" accept="image/*" style="display:none;">

                        <div wire:loading wire:target="photo" style="font-size:12px;color:#556ee6;margin-top:6px;">
                            Mengunggah pratinjau foto...
                        </div>

                        @error('photo') 
                            <p class="form-error mt-2">{{ $message }}</p> 
                        @enderror

                        <p class="text-[11px] text-slate-400 mt-2">Format: JPG, PNG, WEBP (Maks. 10MB)</p>
                    </div>
                </div>

                <!-- Status & Halal -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Status & Kategori</h3></div>
                    <div class="card-body flex flex-col gap-4">
                        <div>
                            <label class="form-label">Status Kendaraan</label>
                            <select wire:model="status" class="form-select @error('status') form-input-error @enderror">
                                <option value="active">Aktif</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="inactive">Tidak Aktif</option>
                                <option value="scrapped">Scrap</option>
                            </select>
                            @error('status') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-green-50 border border-green-100">
                            <input wire:model="is_halal_dedicated" type="checkbox" id="halal" class="w-4 h-4 mt-0.5 rounded border-slate-300 text-green-600">
                            <div>
                                <label for="halal" class="text-sm font-semibold text-green-800 cursor-pointer">Kendaraan Halal Dedicated</label>
                                <p class="text-xs text-green-600 mt-0.5">Kendaraan ini khusus untuk pengiriman produk halal</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div class="card" style="padding:16px;">
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <button type="submit" class="btn btn-primary" style="width:100%;padding:11px 20px;font-size:14px;" wire:loading.attr="disabled">
                            <span wire:loading.remove style="display:inline-flex;align-items:center;justify-content:center;gap:8px;">
                                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Kendaraan' }}
                            </span>
                            <span wire:loading style="display:inline-flex;align-items:center;justify-content:center;gap:8px;">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Menyimpan Data...
                            </span>
                        </button>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary" style="width:100%;text-align:center;padding:10px 20px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
