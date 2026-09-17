<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dokumen Karyawan</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola SIM, SKCK, kontrak, dan dokumen karyawan lainnya</p>
        </div>
        @can('upload employee documents')
        <button wire:click="openForm" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Upload Dokumen
        </button>
        @endcan
    </div>

    <!-- Filter -->
    <div class="card mb-5">
        <div class="card-body flex flex-wrap gap-3">
            <div class="flex-1 min-w-48 relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama karyawan, judul, nomor dokumen..." class="form-input pl-9">
            </div>
            <select wire:model.live="filterStatus" class="form-select w-44">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="expired">Expired</option>
                <option value="revoked">Dicabut</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Dokumen</th>
                        <th>No. Dokumen</th>
                        <th>Berlaku S/D</th>
                        <th>Status</th>
                        <th>File</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    @php $daysLeft = $doc->days_until_expiry; @endphp
                    <tr>
                        <td>
                            <div class="font-medium">{{ $doc->employee?->name }}</div>
                            <div class="text-xs text-slate-400">{{ $doc->employee?->employee_number }}</div>
                        </td>
                        <td>
                            <div class="font-medium">{{ $doc->title }}</div>
                            <div class="text-xs text-slate-400">{{ $doc->document_type }}</div>
                        </td>
                        <td class="font-mono text-xs">{{ $doc->document_number ?? '–' }}</td>
                        <td>
                            @if($doc->expiry_date)
                            <div class="text-sm">{{ $doc->expiry_date->format('d/m/Y') }}</div>
                            @if($daysLeft !== null && $daysLeft <= 30)
                            <span class="badge badge-{{ $doc->expiry_status_color }} text-xs mt-1">
                                {{ $daysLeft < 0 ? 'EXPIRED' : 'H-'.$daysLeft }}
                            </span>
                            @endif
                            @else
                            <span class="text-slate-400">–</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $doc->status === 'active' ? 'green' : ($doc->status === 'expired' ? 'red' : 'gray') }}">
                                {{ ucfirst($doc->status) }}
                            </span>
                        </td>
                        <td>
                            @if($doc->file_path)
                            <a href="{{ Storage::disk(config('filesystems.default_public_disk'))->url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z"/></svg>
                                Unduh
                            </a>
                            @else
                            <span class="text-slate-400 text-sm">–</span>
                            @endif
                        </td>
                        <td>
                            @can('delete employee documents')
                            <button wire:click="confirmDelete({{ $doc->id }})" class="btn btn-danger btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-slate-400 text-sm">Belum ada dokumen karyawan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $documents->links() }}</div>
        @endif
    </div>

    <!-- Upload Form Modal -->
    @if($showForm)
    <div class="modal-overlay" wire:click.self="closeForm">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Upload Dokumen Karyawan</h3>
                <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="save">
                <div class="modal-body grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label">Karyawan *</label>
                        <select wire:model="employee_id" class="form-select @error('employee_id') form-input-error @enderror">
                            <option value="">Pilih karyawan...</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_number }})</option>
                            @endforeach
                        </select>
                        @error('employee_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Tipe Dokumen *</label>
                        <input wire:model="document_type" type="text" class="form-input" placeholder="SIM, SKCK, Kontrak, dll">
                        @error('document_type') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Judul Dokumen *</label>
                        <input wire:model="title" type="text" class="form-input" placeholder="Nama/label dokumen">
                        @error('title') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Nomor Dokumen</label>
                        <input wire:model="document_number" type="text" class="form-input" placeholder="Nomor seri/registrasi">
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select wire:model="status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="expired">Expired</option>
                            <option value="revoked">Dicabut</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tanggal Terbit</label>
                        <input wire:model="issued_date" type="date" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Kadaluarsa</label>
                        <input wire:model="expiry_date" type="date" class="form-input @error('expiry_date') form-input-error @enderror">
                        @error('expiry_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Upload File (PDF/Gambar, max 10MB)</label>
                        <input wire:model="file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="form-input">
                        @error('file') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="form-input" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="closeForm" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan Dokumen</span>
                        <span wire:loading>Mengupload...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
    <div class="modal-overlay" wire:click.self="$set('showDeleteModal', false)">
        <div class="modal-box max-w-md">
            <div class="modal-header">
                <h3 class="modal-title text-red-600">Konfirmasi Hapus</h3>
                <button wire:click="$set('showDeleteModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="text-slate-700 text-sm">Yakin ingin menghapus dokumen ini?</p>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showDeleteModal', false)" class="btn-secondary">Batal</button>
                <button wire:click="delete" class="btn-danger">Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
