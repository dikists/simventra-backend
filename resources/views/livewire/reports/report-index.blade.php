<div>
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Laporan & Ekspor Data</h1>
            <p class="page-subtitle">Pusat pelaporan dan unduh rekapan data operasional SIMVENTRA</p>
        </div>
        <button wire:click="exportCsv" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Unduh CSV / Excel
        </button>
    </div>

    <!-- Stats Summary Row -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:24px;" class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-label">Total Karyawan & Sopir</div>
                    <div class="stat-value">{{ number_format($stats['total_employees']) }}</div>
                </div>
                <div class="stat-icon stat-icon-blue">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-label">Total Armada Kendaraan</div>
                    <div class="stat-value">{{ number_format($stats['total_vehicles']) }}</div>
                </div>
                <div class="stat-icon stat-icon-green">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-label">Total Arsip Dokumen</div>
                    <div class="stat-value">{{ number_format($stats['total_docs']) }}</div>
                </div>
                <div class="stat-icon stat-icon-purple">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-label">Dokumen Kritis (H-7)</div>
                    <div class="stat-value" style="{{ $stats['critical_docs'] > 0 ? 'color:#f46a6a;' : '' }}">{{ number_format($stats['critical_docs']) }}</div>
                </div>
                <div class="stat-icon stat-icon-red">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Bar -->
    <div class="card mb-5">
        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
            <div style="display:flex;gap:8px;">
                <button wire:click="$set('activeTab', 'documents')"
                        class="btn {{ $activeTab === 'documents' ? 'btn-primary' : 'btn-secondary' }}"
                        style="border-radius:20px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Laporan Dokumen & Masa Berlaku
                </button>
                <button wire:click="$set('activeTab', 'employees')"
                        class="btn {{ $activeTab === 'employees' ? 'btn-primary' : 'btn-secondary' }}"
                        style="border-radius:20px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Laporan Data Personalia
                </button>
                <button wire:click="$set('activeTab', 'vehicles')"
                        class="btn {{ $activeTab === 'vehicles' ? 'btn-primary' : 'btn-secondary' }}"
                        style="border-radius:20px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Laporan Data Armada Kendaraan
                </button>
            </div>

            @if($activeTab === 'documents')
            <div style="display:flex;gap:10px;align-items:center;">
                <select wire:model.live="docTypeFilter" class="form-select" style="min-width:140px;">
                    <option value="all">Semua Kategori</option>
                    <option value="vehicle">Kendaraan Saja</option>
                    <option value="employee">Karyawan Saja</option>
                </select>
                <select wire:model.live="expiryStatusFilter" class="form-select" style="min-width:160px;">
                    <option value="all">Semua Status</option>
                    <option value="critical_7">Kritis (H-7)</option>
                    <option value="warning_30">Mendekati Habis (H-30)</option>
                    <option value="expired">Sudah Expired</option>
                    <option value="valid">Masa Berlaku Aman</option>
                </select>
            </div>
            @endif
        </div>
    </div>

    <!-- Export Action Box -->
    <div class="card" style="padding:28px;text-align:center;">
        <div style="width:64px;height:64px;border-radius:16px;background:rgba(85,110,230,.1);color:#556ee6;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
        </div>
        <h3 style="font-size:17px;font-weight:700;color:#343a40;margin-bottom:6px;">
            Ekspor {{ $activeTab === 'documents' ? 'Laporan Dokumen & Masa Berlaku' : ($activeTab === 'employees' ? 'Laporan Data Personalia & Sopir' : 'Laporan Data Armada Kendaraan') }}
        </h3>
        <p style="font-size:13.5px;color:#74788d;max-width:540px;margin:0 auto 20px;">
            File akan di-generate dalam format CSV terstruktur (UTF-8) yang dapat dibuka secara langsung dan rapi menggunakan Microsoft Excel, LibreOffice, atau Google Sheets.
        </p>
        <button wire:click="exportCsv" class="btn btn-primary" style="padding:10px 24px;font-size:14px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download Laporan Sekarang (.CSV)
        </button>
    </div>
</div>
