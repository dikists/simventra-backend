<div>
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary" style="padding:8px 14px;border-radius:8px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <h1 class="page-title" style="margin:0;">{{ $employee->name }}</h1>
                    <span class="badge badge-{{ $employee->type === 'sopir' ? 'purple' : ($employee->type === 'mitra' ? 'teal' : 'blue') }}">
                        {{ ucfirst($employee->type) }}
                    </span>
                    <span class="badge badge-{{ $employee->status === 'active' ? 'green' : ($employee->status === 'inactive' ? 'gray' : 'red') }}">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
                <p class="page-subtitle" style="margin-top:4px;">No. Karyawan: <strong>{{ $employee->employee_number }}</strong> &bull; Terdaftar sejak {{ $employee->join_date ? $employee->join_date->format('d M Y') : '–' }}</p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
            @can('edit employees')
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Karyawan
            </a>
            @endcan
        </div>
    </div>

    <!-- Main Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Left Column: Profile Card & Quick Info -->
        <div class="flex flex-col gap-6">
            <div class="card" style="text-align:center;padding:24px;">
                <div style="display:flex;justify-content:center;margin-bottom:16px;">
                    @if($employee->photo)
                        <img src="{{ Storage::url($employee->photo) }}" alt="{{ $employee->name }}" style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:4px solid #f1f3f7;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                    @else
                        <div style="width:110px;height:110px;border-radius:50%;background:linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);color:#fff;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;border:4px solid #f1f3f7;box-shadow:0 4px 12px rgba(13,110,253,0.25);">
                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                        </div>
                    @endif
                </div>

                <h3 style="font-size:18px;font-weight:700;color:#2a3042;margin:0;">{{ $employee->name }}</h3>
                <p style="font-size:13px;color:#74788d;margin:4px 0 12px 0;">{{ $employee->position ?? '–' }} {{ $employee->department ? '('.$employee->department.')' : '' }}</p>

                <div style="display:inline-block;padding:4px 14px;background:#f8f9fa;border-radius:20px;font-size:12px;font-weight:600;color:#495057;border:1px solid #e9ecef;">
                    Status: {{ ucfirst($employee->employment_status) }}
                </div>

                <div style="border-top:1px solid #eff2f7;margin-top:20px;padding-top:16px;text-align:left;display:flex;flex-col;gap:12px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;">
                        <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <div style="font-size:11.5px;color:#adb5bd;text-transform:uppercase;font-weight:600;">Telepon / WA</div>
                            <div style="font-size:13px;font-weight:600;color:#2a3042;">
                                @if($employee->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $employee->phone) }}" target="_blank" style="color:#0d6efd;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                        {{ $employee->phone }}
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    –
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;">
                        <div style="width:32px;height:32px;border-radius:8px;background:#eef9ff;color:#0ea5e9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div style="font-size:11.5px;color:#adb5bd;text-transform:uppercase;font-weight:600;">Email</div>
                            <div style="font-size:13px;font-weight:600;color:#2a3042;">
                                @if($employee->email)
                                    <a href="mailto:{{ $employee->email }}" style="color:#0d6efd;text-decoration:none;">{{ $employee->email }}</a>
                                @else
                                    –
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:8px;background:#fef2f2;color:#ef4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <div style="font-size:11.5px;color:#adb5bd;text-transform:uppercase;font-weight:600;">Alamat Tempat Tinggal</div>
                            <div style="font-size:13px;color:#495057;line-height:1.4;">{{ $employee->address ?? '–' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Vehicle Card -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 class="card-title">Armada Ditugaskan</h3>
                    @if($employee->vehicle)
                        <span class="badge badge-green">Aktif Membawa Unit</span>
                    @else
                        <span class="badge badge-gray">Tidak Ada Unit</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($employee->vehicle)
                        <div style="display:flex;align-items:center;gap:14px;padding:12px;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
                            <div style="width:48px;height:48px;border-radius:8px;background:#e9ecef;display:flex;align-items:center;justify-content:center;color:#495057;flex-shrink:0;">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:14px;font-weight:700;color:#2a3042;text-transform:uppercase;">
                                    {{ $employee->vehicle->license_plate }}
                                </div>
                                <div style="font-size:12px;color:#74788d;">
                                    {{ $employee->vehicle->brand }} {{ $employee->vehicle->model }}
                                </div>
                                <div style="font-size:11.5px;color:#0d6efd;margin-top:2px;">
                                    Odometer: {{ number_format($employee->vehicle->current_odometer_km, 0, ',', '.') }} KM
                                </div>
                            </div>
                            <a href="{{ route('vehicles.show', $employee->vehicle) }}" class="btn btn-soft-primary btn-sm" title="Lihat Kendaraan">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    @else
                        <div style="text-align:center;padding:16px 8px;color:#adb5bd;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:36px;height:36px;margin:0 auto 8px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <div style="font-size:13px;color:#495057;font-weight:500;">Belum Ditugaskan</div>
                            <div style="font-size:12px;color:#adb5bd;">Sopir saat ini standby dan siap ditugaskan pada armada.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Detailed Information Tabs/Cards -->
        <div class="xl:col-span-2 flex flex-col gap-6">

            <!-- Data Pribadi & Kepegawaian -->
            <div class="card">
                <div class="card-header"><h3 class="card-title">Data Pribadi & Kepegawaian</h3></div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Nomor KTP (NIK)</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;">{{ $employee->nik ?? '–' }}</div>
                        </div>
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">NPWP</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;">{{ $employee->npwp ?? '–' }}</div>
                        </div>
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Tempat, Tanggal Lahir</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;">
                                {{ $employee->place_of_birth ?? '–' }}, {{ $employee->date_of_birth ? $employee->date_of_birth->format('d M Y') : '–' }}
                            </div>
                        </div>
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Jenis Kelamin</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;">
                                {{ $employee->gender === 'L' ? 'Laki-laki' : ($employee->gender === 'P' ? 'Perempuan' : '–') }}
                            </div>
                        </div>
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Tanggal Masuk Kerja</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;">
                                {{ $employee->join_date ? $employee->join_date->format('d M Y') : '–' }}
                            </div>
                        </div>
                        <div style="padding:10px 14px;background:#f8f9fa;border-radius:8px;">
                            <div style="font-size:11.5px;color:#74788d;text-transform:uppercase;font-weight:600;">Tanggal Akhir Kontrak</div>
                            <div style="font-size:14px;font-weight:600;color:#2a3042;margin-top:2px;">
                                {{ $employee->contract_end_date ? $employee->contract_end_date->format('d M Y') : '– (Karyawan Tetap)' }}
                            </div>
                        </div>
                    </div>

                    @if($employee->notes)
                    <div style="margin-top:16px;padding:12px;background:#fff9db;border-left:4px solid #f59e0b;border-radius:4px;font-size:13px;color:#78350f;">
                        <strong>Catatan Khusus:</strong> {{ $employee->notes }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Legalitas & Kelayakan Mengemudi -->
            <div class="card">
                <div class="card-header"><h3 class="card-title">Legalitas & Kualifikasi Mengemudi</h3></div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <!-- SIM Card -->
                        <div style="padding:14px;border-radius:10px;border:1px solid #e9ecef;background:#fff;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:12px;font-weight:700;color:#495057;text-transform:uppercase;">Surat Izin Mengemudi</span>
                                <span class="badge badge-purple">SIM {{ $employee->sim_type ?? '–' }}</span>
                            </div>
                            <div style="font-size:15px;font-weight:700;color:#2a3042;margin-bottom:4px;">
                                {{ $employee->sim_number ?? 'Belum ada SIM' }}
                            </div>
                            <div style="font-size:12px;color:#74788d;">Masa Berlaku:</div>
                            <div style="font-size:13px;font-weight:600;color:#2a3042;margin-top:2px;">
                                @if($employee->sim_expiry)
                                    @php $daysLeft = now()->diffInDays($employee->sim_expiry, false); @endphp
                                    {{ $employee->sim_expiry->format('d M Y') }}
                                    @if($daysLeft <= 30)
                                        <span class="badge badge-{{ $daysLeft <= 7 ? 'red' : ($daysLeft <= 14 ? 'orange' : 'yellow') }}" style="margin-left:4px;">
                                            {{ $daysLeft <= 0 ? 'Expired' : 'H-'.$daysLeft }}
                                        </span>
                                    @else
                                        <span class="badge badge-green" style="margin-left:4px;">Aktif</span>
                                    @endif
                                @else
                                    –
                                @endif
                            </div>
                        </div>

                        <!-- SKCK Card -->
                        <div style="padding:14px;border-radius:10px;border:1px solid #e9ecef;background:#fff;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:12px;font-weight:700;color:#495057;text-transform:uppercase;">SKCK Kepolisian</span>
                                <span class="badge badge-{{ $employee->has_skck ? 'green' : 'gray' }}">
                                    {{ $employee->has_skck ? 'Ada' : 'Tidak Ada' }}
                                </span>
                            </div>
                            <div style="font-size:12px;color:#74788d;margin-top:8px;">Masa Berlaku:</div>
                            <div style="font-size:13px;font-weight:600;color:#2a3042;margin-top:2px;">
                                {{ $employee->skck_expiry ? $employee->skck_expiry->format('d M Y') : '–' }}
                            </div>
                            @if($employee->criminal_record_notes)
                            <div style="font-size:11.5px;color:#ef4444;margin-top:6px;">
                                Catatan: {{ $employee->criminal_record_notes }}
                            </div>
                            @endif
                        </div>

                        <!-- ID Card Perusahaan -->
                        <div style="padding:14px;border-radius:10px;border:1px solid #e9ecef;background:#fff;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:12px;font-weight:700;color:#495057;text-transform:uppercase;">ID Card Perusahaan</span>
                                <span class="badge badge-{{ $employee->id_card_active ? 'green' : 'gray' }}">
                                    {{ $employee->id_card_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </div>
                            <div style="font-size:15px;font-weight:700;color:#2a3042;margin-bottom:4px;">
                                {{ $employee->id_card_number ?? '–' }}
                            </div>
                            <div style="font-size:12px;color:#74788d;">Masa Berlaku:</div>
                            <div style="font-size:13px;font-weight:600;color:#2a3042;margin-top:2px;">
                                {{ $employee->id_card_expiry ? $employee->id_card_expiry->format('d M Y') : '–' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Berkas & Dokumen Terlampir -->
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 class="card-title">Berkas & Dokumen ({{ $employee->documents->count() }})</h3>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tipe Dokumen</th>
                                <th>Nomor / Judul</th>
                                <th>Masa Berlaku</th>
                                <th>Status</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->documents as $doc)
                            <tr>
                                <td>
                                    <span class="badge badge-blue">{{ strtoupper($doc->document_type) }}</span>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:#2a3042;">{{ $doc->title ?? $doc->document_number }}</div>
                                    @if($doc->document_number && $doc->title)
                                    <div style="font-size:11.5px;color:#adb5bd;">{{ $doc->document_number }}</div>
                                    @endif
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
                                        <span style="color:#adb5bd;font-size:13px;">Seumur Hidup</span>
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
                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-soft-primary btn-sm" title="Lihat Berkas">
                                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:24px;color:#adb5bd;">
                                    Belum ada berkas dokumen digital yang diunggah untuk karyawan ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
