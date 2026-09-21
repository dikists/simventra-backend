<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/assets/logo_rhl.png">
    <title>{{ isset($title) ? $title . ' | RHL SIMVENTRA' : 'RHL SIMVENTRA – Sistem Manajemen Vendor Transportasi' }}</title>
    <meta name="description" content="Rajawali Handal Logistik – Sistem Manajemen Vendor Transportasi Terpusat">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script>
        // Restore desktop collapsed sidebar early to prevent flicker
        if (localStorage.getItem('simventra_sidebar_collapsed') === 'true' && window.innerWidth > 768) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
</head>
<body>

<!-- Mobile Sidebar Backdrop -->
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

<!-- ======================================================
     SIDEBAR
====================================================== -->
<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <img src="{{ \App\Models\CompanySetting::getLogoUrl() }}" alt="{{ \App\Models\CompanySetting::getName() }}" style="width:34px;height:34px;object-fit:contain;border-radius:8px;">
        </div>
        <div>
            <div class="sidebar-brand-text">{{ \App\Models\CompanySetting::getShortName() }}</div>
            <div class="sidebar-brand-sub">{{ \App\Models\CompanySetting::getTagline() }}</div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav">

        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-2a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/>
            </svg>
            <span>Dashboard</span>
        </a>

        @canany(['view employees', 'create employees'])
        <div class="sidebar-section">Personalia</div>
        <a href="{{ route('employees.index') }}"
           class="sidebar-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Karyawan & Sopir</span>
        </a>
        @endcanany

        @canany(['view vehicles', 'create vehicles'])
        <div class="sidebar-section">Armada</div>
        <a href="{{ route('vehicles.index') }}"
           class="sidebar-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <span>Kendaraan</span>
        </a>
        <a href="{{ route('tracking.index') }}"
           class="sidebar-link {{ request()->routeIs('tracking.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Live GPS Armada</span>
            @php
                $silentGpsCount = \App\Models\VehicleAssignment::where('status', 'on_trip')
                    ->where(function($q) {
                        $q->whereNull('last_ping_at')->where('departure_time', '<', now()->subMinutes(5))
                          ->orWhere('last_ping_at', '<', now()->subMinutes(5));
                    })->count();
            @endphp
            @if($silentGpsCount > 0)
            <span class="sidebar-badge" style="background:#ef4444;color:#fff;font-weight:700;">{{ $silentGpsCount }}</span>
            @endif
        </a>
        @endcanany

        @canany(['view employee documents', 'view vehicle documents'])
        <div class="sidebar-section">Dokumen</div>
        @can('view employee documents')
        <a href="{{ route('documents.employees') }}"
           class="sidebar-link {{ request()->routeIs('documents.employees*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Dok. Karyawan</span>
        </a>
        @endcan
        @can('view vehicle documents')
        <a href="{{ route('documents.vehicles') }}"
           class="sidebar-link {{ request()->routeIs('documents.vehicles*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span>Dok. Kendaraan</span>
        </a>
        @endcan
        @endcanany

        @can('view reminders')
        <div class="sidebar-section">Monitoring</div>
        <a href="{{ route('reminders.index') }}"
           class="sidebar-link {{ request()->routeIs('reminders.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span>Reminder Dokumen</span>
            @php
                $expCount = \App\Models\VehicleDocument::expiringSoon(30)->count()
                          + \App\Models\EmployeeDocument::expiringSoon(30)->count();
            @endphp
            @if($expCount > 0)
            <span class="sidebar-badge">{{ $expCount }}</span>
            @endif
        </a>
        @endcan

        @can('view reports')
        <a href="{{ route('reports.index') }}"
           class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Laporan & Export</span>
        </a>
        @endcan

        @canany(['view users', 'view activity logs'])
        <div class="sidebar-section">Administrasi</div>
        @can('view users')
        <a href="{{ route('users.index') }}"
           class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Pengguna</span>
        </a>
        <a href="{{ route('settings.company') }}"
           class="sidebar-link {{ request()->routeIs('settings.company') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>Profil & Logo Perusahaan</span>
        </a>
        <a href="{{ route('settings.warehouse') }}"
           class="sidebar-link {{ request()->routeIs('settings.warehouse') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Pengaturan Gudang</span>
        </a>
        @endcan
        @can('view activity logs')
        <a href="{{ route('activity-logs.index') }}"
           class="sidebar-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Log Aktivitas</span>
        </a>
        @endcan
        @endcanany
    </nav>

    <!-- Sidebar Footer: User info mini -->
    <div class="sidebar-footer">
        <div style="display:flex;align-items:center;gap:10px;padding:8px 4px;">
            <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#556ee6,#6f42c1);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="sm-hide-collapsed" style="flex:1;min-width:0;">
                <div style="font-size:12.5px;font-weight:600;color:rgba(255,255,255,.85);truncate;">{{ Str::limit(auth()->user()->name, 18) }}</div>
                <div style="font-size:10.5px;color:rgba(255,255,255,.35);">{{ auth()->user()->roles->first()?->name ?? 'User' }}</div>
            </div>
        </div>
    </div>
</aside>

<!-- ======================================================
     MAIN WRAPPER
====================================================== -->
<div class="main-wrapper">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-left">
            <button id="sidebar-toggle"
                style="width:36px;height:36px;border-radius:8px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#74788d;"
                title="Toggle Sidebar">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <!-- Breadcrumb -->
            <nav style="display:flex;align-items:center;gap:6px;font-size:13px;color:#adb5bd;">
                <a href="{{ route('dashboard') }}" style="color:#adb5bd;text-decoration:none;">Beranda</a>
                @if(isset($title) && $title !== 'Dashboard')
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span style="color:#495057;font-weight:600;">{{ $title }}</span>
                @endif
            </nav>
        </div>

        <div class="topbar-right">
            <!-- Notifications -->
            @php
                $alertCount = \App\Models\VehicleDocument::expiringSoon(7)->count()
                            + \App\Models\EmployeeDocument::expiringSoon(7)->count()
                            + ($silentGpsCount ?? 0);
            @endphp
            <a href="{{ route('reminders.index') }}" class="topbar-icon-btn" title="Notifikasi Dokumen">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($alertCount > 0)
                <span class="badge-dot"></span>
                @endif
            </a>

            <!-- User Dropdown -->
            <div style="position:relative;" x-data="{ open: false }">
                <div @click="open = !open" class="topbar-user">
                    <div class="topbar-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div style="display:none;" class="sm-show">
                        <div class="topbar-user-name">{{ Str::limit(auth()->user()->name, 16) }}</div>
                        <div class="topbar-user-role">{{ auth()->user()->roles->first()?->name ?? '-' }}</div>
                    </div>
                    <svg width="14" height="14" fill="none" stroke="#adb5bd" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <div x-show="open" @click.outside="open = false" x-transition class="user-dropdown">
                    <div class="user-dropdown-header">
                        <div style="font-size:13px;font-weight:700;color:#343a40;">{{ auth()->user()->name }}</div>
                        <div style="font-size:11.5px;color:#adb5bd;margin-top:2px;">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="user-dropdown-item">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil Saya
                    </a>
                    <div class="user-dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="user-dropdown-item user-dropdown-item-danger" style="width:100%;border:none;background:none;cursor:pointer;text-align:left;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success') || session('error'))
    <div class="flash-container">
        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:0;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom:0;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif
    </div>
    @endif

    <!-- PAGE CONTENT -->
    <main class="page-content">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer style="text-align:center;padding:16px 24px;font-size:12px;color:#adb5bd;border-top:1px solid #eff2f7;background:#fff;margin-top:auto;">
        © {{ date('Y') }} {{ \App\Models\CompanySetting::getName() }} – Sistem Manajemen Vendor Transportasi
    </footer>
</div>

@livewireScripts
<script>
    (function() {
        const toggleBtn = document.getElementById('sidebar-toggle');
        const backdrop = document.getElementById('sidebar-backdrop');
        const isMobile = () => window.innerWidth <= 768;

        // Sinkronisasi state dari localStorage
        if (localStorage.getItem('simventra_sidebar_collapsed') === 'true' && !isMobile()) {
            document.documentElement.classList.add('sidebar-collapsed');
            document.body.classList.add('sidebar-collapsed');
        }

        toggleBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            if (isMobile()) {
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
                document.documentElement.classList.toggle('sidebar-collapsed', isCollapsed);
                localStorage.setItem('simventra_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            }
        });

        // Tutup sidebar di mobile saat backdrop diklik
        backdrop?.addEventListener('click', () => {
            document.body.classList.remove('sidebar-mobile-open');
        });

        // Tutup drawer di mobile saat tautan diklik
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    document.body.classList.remove('sidebar-mobile-open');
                }
            });
        });
    })();
</script>
<style>
    /* Transisi Halus Sidebar & Content Wrapper */
    :root {
        --sidebar-width: 270px;
        --sidebar-collapsed-width: 72px;
        --topbar-height: 68px;
    }

    .sidebar {
        transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        overflow-x: hidden !important;
    }

    .main-wrapper {
        transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    @media (min-width: 640px) {
        .sm-show { display: block !important; }
    }

    /* Desktop: Collapsed / Mini Sidebar Mode */
    @media (min-width: 769px) {
        .sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width) !important;
        }

        .sidebar-collapsed .main-wrapper {
            margin-left: var(--sidebar-collapsed-width) !important;
        }

        .sidebar-collapsed .sidebar .sidebar-brand {
            padding: 0 17px;
            justify-content: center;
        }

        .sidebar-collapsed .sidebar .sidebar-brand-text,
        .sidebar-collapsed .sidebar .sidebar-brand-sub {
            display: none !important;
        }

        .sidebar-collapsed .sidebar .sidebar-section {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 12px 14px;
            padding: 0;
            font-size: 0;
            color: transparent;
            overflow: hidden;
        }

        .sidebar-collapsed .sidebar .sidebar-link {
            padding: 11px 0;
            justify-content: center;
            gap: 0;
        }

        .sidebar-collapsed .sidebar .sidebar-link span {
            display: none !important;
        }

        .sidebar-collapsed .sidebar .sidebar-link svg {
            width: 20px;
            height: 20px;
            margin: 0;
        }

        .sidebar-collapsed .sidebar .sidebar-badge {
            position: absolute;
            top: 6px;
            right: 14px;
            width: 8px;
            height: 8px;
            padding: 0;
            border-radius: 50%;
            font-size: 0;
        }

        .sidebar-collapsed .sidebar .sidebar-footer {
            padding: 12px 17px;
            justify-content: center;
        }

        .sidebar-collapsed .sidebar .sidebar-footer .sm-hide-collapsed {
            display: none !important;
        }

        /* Hover to Expand (Snacked / Metronic style) */
        .sidebar-collapsed .sidebar:hover {
            width: var(--sidebar-width) !important;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.35) !important;
            z-index: 1050;
        }

        .sidebar-collapsed .sidebar:hover .sidebar-brand {
            padding: 0 20px;
            justify-content: flex-start;
        }

        .sidebar-collapsed .sidebar:hover .sidebar-brand-text,
        .sidebar-collapsed .sidebar:hover .sidebar-brand-sub {
            display: block !important;
        }

        .sidebar-collapsed .sidebar:hover .sidebar-section {
            height: auto;
            background: transparent;
            margin: 4px 0 0;
            padding: 16px 12px 6px;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.3);
        }

        .sidebar-collapsed .sidebar:hover .sidebar-link {
            padding: 10px 14px;
            justify-content: flex-start;
            gap: 10px;
        }

        .sidebar-collapsed .sidebar:hover .sidebar-link span {
            display: inline !important;
        }

        .sidebar-collapsed .sidebar:hover .sidebar-badge {
            position: static;
            width: auto;
            height: auto;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 10px;
        }

        .sidebar-collapsed .sidebar:hover .sidebar-footer .sm-hide-collapsed {
            display: block !important;
        }
    }

    /* Mobile Screens (< 768px) */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%) !important;
            width: var(--sidebar-width) !important;
            z-index: 1050 !important;
        }

        .main-wrapper {
            margin-left: 0 !important;
        }

        body.sidebar-mobile-open .sidebar {
            transform: translateX(0) !important;
        }

        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 1040;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        body.sidebar-mobile-open .sidebar-backdrop {
            opacity: 1;
            pointer-events: auto;
        }
    }
</style>
</body>
</html>
