<div>
    <div class="page-header" style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary" style="padding:8px 14px;border-radius:8px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <div>
                <h1 class="page-title">{{ $isEdit ? 'Edit Karyawan' : 'Tambah Karyawan' }}</h1>
                <p class="page-subtitle">{{ $isEdit ? 'Perbarui data karyawan atau sopir armada' : 'Daftarkan karyawan atau sopir baru ke sistem' }}</p>
            </div>
        </div>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="xl:col-span-2 flex flex-col gap-6">
                <!-- Data Dasar -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Data Dasar</h3></div>
                    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Nomor Karyawan *</label>
                            <input wire:model="employee_number" type="text" class="form-input @error('employee_number') form-input-error @enderror" placeholder="EMP-0001">
                            @error('employee_number') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Nama Lengkap *</label>
                            <input wire:model="name" type="text" class="form-input @error('name') form-input-error @enderror" placeholder="Nama lengkap sesuai KTP">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Tipe *</label>
                            <select wire:model="type" class="form-select @error('type') form-input-error @enderror">
                                <option value="karyawan">Karyawan</option>
                                <option value="sopir">Sopir</option>
                                <option value="mitra">Mitra</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Status Kepegawaian *</label>
                            <select wire:model="employment_status" class="form-select">
                                <option value="tetap">Tetap</option>
                                <option value="kontrak">Kontrak</option>
                                <option value="magang">Magang</option>
                                <option value="mitra">Mitra</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Jabatan</label>
                            <input wire:model="position" type="text" class="form-input" placeholder="Contoh: Sopir Senior">
                        </div>
                        <div>
                            <label class="form-label">Departemen</label>
                            <input wire:model="department" type="text" class="form-input" placeholder="Contoh: Operasional">
                        </div>
                        <div>
                            <label class="form-label">No. Telepon</label>
                            <input wire:model="phone" type="text" class="form-input" placeholder="08xxxxxxxx">
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input wire:model="email" type="email" class="form-input @error('email') form-input-error @enderror" placeholder="email@perusahaan.com">
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Alamat</label>
                            <textarea wire:model="address" rows="2" class="form-input" placeholder="Alamat lengkap"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Data Pribadi -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Data Pribadi</h3></div>
                    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Tanggal Lahir</label>
                            <input wire:model="date_of_birth" type="date" class="form-input @error('date_of_birth') form-input-error @enderror">
                            @error('date_of_birth') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Tempat Lahir</label>
                            <input wire:model="place_of_birth" type="text" class="form-input" placeholder="Kota">
                        </div>
                        <div>
                            <label class="form-label">Jenis Kelamin</label>
                            <select wire:model="gender" class="form-select">
                                <option value="">Pilih...</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">NIK (KTP)</label>
                            <input wire:model="nik" type="text" class="form-input @error('nik') form-input-error @enderror" placeholder="16 digit" maxlength="16">
                            @error('nik') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Tanggal Bergabung</label>
                            <input wire:model="join_date" type="date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Akhir Kontrak</label>
                            <input wire:model="contract_end_date" type="date" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Data Sopir (conditional) -->
                @if($type === 'sopir')
                <div class="card border-l-4 border-l-blue-500">
                    <div class="card-header">
                        <h3 class="card-title">Data Sopir</h3>
                        <span class="badge badge-blue">Sopir</span>
                    </div>
                    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">No. SIM</label>
                            <input wire:model="sim_number" type="text" class="form-input" placeholder="Nomor SIM">
                        </div>
                        <div>
                            <label class="form-label">Tipe SIM</label>
                            <select wire:model="sim_type" class="form-select">
                                <option value="">Pilih...</option>
                                <option value="A">A</option>
                                <option value="B1">B1</option>
                                <option value="B2">B2</option>
                                <option value="BI">BI</option>
                                <option value="BII">BII</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Kadaluarsa SIM</label>
                            <input wire:model="sim_expiry" type="date" class="form-input @error('sim_expiry') form-input-error @enderror">
                            @error('sim_expiry') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-3 pt-6">
                            <input wire:model="has_skck" type="checkbox" id="has_skck" class="w-4 h-4 rounded border-slate-300 text-blue-600">
                            <label for="has_skck" class="text-sm font-medium text-slate-700">Memiliki SKCK</label>
                        </div>
                        @if($has_skck)
                        <div>
                            <label class="form-label">Kadaluarsa SKCK</label>
                            <input wire:model="skck_expiry" type="date" class="form-input">
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Catatan -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Catatan</h3></div>
                    <div class="card-body">
                        <textarea wire:model="notes" rows="3" class="form-input" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Right sidebar -->
            <div class="flex flex-col gap-6">
                <!-- Foto -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Foto</h3></div>
                    <div class="card-body text-center">
                        @if($photo && method_exists($photo, 'temporaryUrl'))
                            <img src="{{ $photo->temporaryUrl() }}" class="w-32 h-32 rounded-full mx-auto object-cover mb-3 border-4 border-blue-100">
                        @elseif($isEdit && ($existingPhoto ?? $employee?->photo))
                            <img src="{{ Storage::url($existingPhoto ?? $employee->photo) }}" class="w-32 h-32 rounded-full mx-auto object-cover mb-3 border-4 border-blue-100">
                        @else
                            <div class="w-32 h-32 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 border-4 border-slate-200">
                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                        @endif
                        <label class="btn btn-secondary" style="cursor:pointer;display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Pilih Foto
                            <input wire:model="photo" type="file" accept="image/*" class="hidden">
                        </label>
                        @error('photo') <p class="form-error mt-2">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-400 mt-2">Max 2MB, JPG/PNG</p>
                    </div>
                </div>

                <!-- Status -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Status Karyawan</h3></div>
                    <div class="card-body">
                        <select wire:model="status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="terminated">Dihentikan</option>
                        </select>
                    </div>
                </div>

                <!-- ID Card -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">ID Card</h3></div>
                    <div class="card-body flex flex-col gap-3">
                        <div>
                            <label class="form-label">No. ID Card</label>
                            <input wire:model="id_card_number" type="text" class="form-input" placeholder="Nomor ID Card">
                        </div>
                        <div>
                            <label class="form-label">Kadaluarsa</label>
                            <input wire:model="id_card_expiry" type="date" class="form-input">
                        </div>
                        <div class="flex items-center gap-2">
                            <input wire:model="id_card_active" type="checkbox" id="id_card_active" class="w-4 h-4 rounded border-slate-300 text-blue-600">
                            <label for="id_card_active" class="text-sm text-slate-700">ID Card Aktif</label>
                        </div>
                    </div>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div class="card" style="padding:16px;">
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <button type="submit" class="btn btn-primary" style="width:100%;padding:11px 20px;font-size:14px;" wire:loading.attr="disabled">
                            <span wire:loading.remove style="display:inline-flex;align-items:center;justify-content:center;gap:8px;">
                                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Karyawan' }}
                            </span>
                            <span wire:loading style="display:none;align-items:center;justify-content:center;gap:8px;">
                                Menyimpan Data...
                            </span>
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary" style="width:100%;text-align:center;padding:10px 20px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
