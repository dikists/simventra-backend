<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="/assets/simventra-logo.png">

        <title>{{ config('app.name', 'SIMVENTRA') }} - Fleet Control Tower & Logistics Intelligence</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            .bg-mesh-dots {
                background-image: radial-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px);
                background-size: 28px 28px;
            }
            .glass-panel {
                background: rgba(15, 23, 42, 0.65);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.96);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.6);
            }
            .glow-orb-1 {
                position: absolute;
                top: -15%;
                left: -10%;
                width: 600px;
                height: 600px;
                background: radial-gradient(circle, rgba(59, 130, 246, 0.22) 0%, rgba(30, 58, 138, 0.05) 50%, transparent 70%);
                filter: blur(50px);
                pointer-events: none;
                z-index: 0;
            }
            .glow-orb-2 {
                position: absolute;
                bottom: -15%;
                right: -10%;
                width: 650px;
                height: 650px;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.20) 0%, rgba(147, 51, 234, 0.06) 50%, transparent 70%);
                filter: blur(60px);
                pointer-events: none;
                z-index: 0;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-800 bg-[#090d1a] relative min-h-screen selection:bg-blue-500 selection:text-white overflow-x-hidden">
        <!-- Ambient Glowing Orbs -->
        <div class="glow-orb-1"></div>
        <div class="glow-orb-2"></div>

        <!-- Dot Matrix Overlay -->
        <div class="absolute inset-0 bg-mesh-dots pointer-events-none z-0"></div>

        <!-- Main Wrapper -->
        <div class="relative z-10 min-h-screen flex flex-col justify-between p-4 sm:p-6 lg:p-10">
            
            <!-- Top Minimal Navbar -->
            <header class="w-full max-w-7xl mx-auto flex items-center justify-between py-2 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500/20 to-indigo-500/10 p-1.5 backdrop-blur-md border border-blue-400/30 flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <img src="/assets/simventra-logo.png" alt="SIMVENTRA Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="text-white font-extrabold tracking-wider text-base flex items-center gap-2">
                            <span>SIMVENTRA</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5"></span>
                                CLOUD ACTIVE
                            </span>
                        </div>
                        <div class="text-slate-400 text-xs font-medium tracking-wide">
                            FLEET CONTROL TOWER &bull; ENTERPRISE LOGISTICS
                        </div>
                    </div>
                </div>

                <div class="hidden sm:flex items-center gap-3 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5 bg-slate-800/60 px-3 py-1.5 rounded-lg border border-slate-700/50">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        SSL 256-Bit Encrypted
                    </span>
                    <span class="flex items-center gap-1.5 bg-slate-800/60 px-3 py-1.5 rounded-lg border border-slate-700/50">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        GPS Real-Time v2.4
                    </span>
                </div>
            </header>

            <!-- Main Content: Split Grid -->
            <main class="w-full max-w-7xl mx-auto flex-1 flex items-center justify-center my-4">
                <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left Hero: Control Tower Showcase (visible on large screen) -->
                    <div class="hidden lg:flex lg:col-span-7 flex-col justify-center pr-4">
                        
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/25 text-blue-300 text-xs font-semibold w-fit mb-6 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
                            <span>INTELLIGENT FLEET LOGISTICS & TELEMETRY</span>
                        </div>

                        <!-- Big Headline -->
                        <h1 class="text-3xl xl:text-5xl font-extrabold text-white leading-tight tracking-tight mb-4">
                            Pusat Kendali Operasional <br/>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-300 to-teal-300">
                                Armada & Ekspedisi Logistik
                            </span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-slate-300 text-base xl:text-lg leading-relaxed mb-8 max-w-2xl">
                            Solusi cerdas manajemen transportasi dan pengawasan armada terpusat. Pantau pergerakan armada secara live, koordinasi penugasan sopir seketika, dan pastikan kepatuhan pengiriman tiba tepat waktu.
                        </p>

                        <!-- Highlights Feature Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl mb-8">
                            
                            <!-- Card 1 -->
                            <div class="glass-panel p-4 rounded-xl border border-white/10 hover:border-blue-500/40 transition-all duration-300 group">
                                <div class="w-10 h-10 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <h4 class="text-white font-semibold text-sm mb-1">Live GPS Telemetry</h4>
                                <p class="text-slate-400 text-xs leading-relaxed">
                                    Streaming koordinat pergerakan, status odometer, dan pantauan batas kecepatan secara akurat.
                                </p>
                            </div>

                            <!-- Card 2 -->
                            <div class="glass-panel p-4 rounded-xl border border-white/10 hover:border-indigo-500/40 transition-all duration-300 group">
                                <div class="w-10 h-10 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h4 class="text-white font-semibold text-sm mb-1">Driver Mobile Sync</h4>
                                <p class="text-slate-400 text-xs leading-relaxed">
                                    Notifikasi suara dering otomatis dan konfirmasi trip instan langsung ke HP Android sopir.
                                </p>
                            </div>

                            <!-- Card 3 -->
                            <div class="glass-panel p-4 rounded-xl border border-white/10 hover:border-emerald-500/40 transition-all duration-300 group">
                                <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h4 class="text-white font-semibold text-sm mb-1">Multi-Tenant Corporate</h4>
                                <p class="text-slate-400 text-xs leading-relaxed">
                                    Pengaturan identitas perusahaan, kustomisasi logo klien, dan isolasi data operasional yang aman.
                                </p>
                            </div>

                            <!-- Card 4 -->
                            <div class="glass-panel p-4 rounded-xl border border-white/10 hover:border-amber-500/40 transition-all duration-300 group">
                                <div class="w-10 h-10 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <h4 class="text-white font-semibold text-sm mb-1">Laporan & Audit Trip</h4>
                                <p class="text-slate-400 text-xs leading-relaxed">
                                    Kalkulasi otomatis kilometer, durasi perjalanan, dan riwayat kondisi unit saat check-in gudang.
                                </p>
                            </div>

                        </div>

                        <!-- System Status Telemetry Banner -->
                        <div class="flex items-center gap-6 text-xs text-slate-400 pt-4 border-t border-white/10 max-w-2xl">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>High-Performance Cloud</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                                <span>OpenStreetMap Engine</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                <span>Zero Google API Billing</span>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Authentication Card -->
                    <div class="w-full lg:col-span-5 max-w-md mx-auto">
                        <div class="glass-card rounded-3xl shadow-2xl p-7 sm:p-9 border border-white/80 transition-all duration-300 hover:shadow-blue-500/10 relative overflow-hidden">
                            
                            <!-- Subtle Top Gradient Accent -->
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-500"></div>

                            {{ $slot }}

                        </div>

                        <!-- Mobile-only footer info -->
                        <div class="text-center mt-6 lg:hidden">
                            <p class="text-xs text-slate-400">
                                &copy; {{ date('Y') }} SIMVENTRA Enterprise. All rights reserved.
                            </p>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Fleet Control Tower &bull; v2.4 Enterprise Edition
                            </p>
                        </div>
                    </div>

                </div>
            </main>

            <!-- Bottom Footer (Desktop) -->
            <footer class="w-full max-w-7xl mx-auto hidden lg:flex items-center justify-between text-xs text-slate-500 pt-4 border-t border-white/5">
                <div>
                    &copy; {{ date('Y') }} <strong>SIMVENTRA Enterprise</strong>. Sistem Informasi Manajemen Ventilasi & Armada Terpusat.
                </div>
                <div class="flex items-center gap-4">
                    <span>Versi 2.4.0 (Enterprise Hub)</span>
                    <span>&bull;</span>
                    <a href="/panduan-sop" class="hover:text-slate-300 transition-colors">SOP Operasional</a>
                    <span>&bull;</span>
                    <span class="text-slate-400">Pusat Kendali 24/7</span>
                </div>
            </footer>

        </div>
    </body>
</html>
