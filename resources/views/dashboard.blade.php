<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">
            Dashboard
        </h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-10 grid-bg">

        <!-- ============================================ -->
        <!-- SEGMENT 01: KEY METRICS -->
        <!-- ============================================ -->
        <section class="relative">
            
            <!-- Segment Header -->
            <div class="relative mb-6">
                <!-- Laser Line -->
                <div class="laser-line laser-line-pulse mb-4">
                    <div class="laser-dot laser-dot-left"></div>
                    <div class="laser-dot laser-dot-right"></div>
                </div>

                <!-- Header Content -->
                <div class="flex items-end justify-between">
                    <div class="flex items-baseline gap-4">
                        <span class="font-mono text-xs text-gold-500 tracking-widest glow-gold">01</span>
                        <div>
                            <h3 class="font-serif text-xl font-bold text-white leading-none">Key Metrics</h3>
                            <p class="text-xs text-navy-400 mt-1.5 tracking-wide uppercase">Ringkasan performa bulan ini</p>
                        </div>
                    </div>
                    <div class="text-xs text-navy-500 font-mono">{{ now()->format('d.m.Y') }}</div>
                </div>
            </div>

            <!-- Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- SO Aktif -->
                <a href="#" class="group relative bg-navy-900/50 border border-navy-800 rounded-xl p-5 hover:border-gold-500/50 transition-all duration-300 overflow-hidden">
                    <div class="corner-bracket corner-bracket-tl"></div>
                    <div class="corner-bracket corner-bracket-tr"></div>
                    <div class="flex items-start justify-between mb-3 relative">
                        <div class="w-9 h-9 rounded-lg bg-navy-800 flex items-center justify-center group-hover:bg-gold-500/20 transition">
                            <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold text-green-400 bg-green-500/10 px-2 py-0.5 rounded-full font-mono">+2d</span>
                    </div>
                    <p class="font-serif text-3xl font-bold text-white leading-none relative">12</p>
                    <p class="text-xs text-navy-400 uppercase tracking-wider mt-2 relative">Sales Order Aktif</p>
                </a>

                <!-- WO Aktif -->
                <a href="#" class="group relative bg-navy-900/50 border border-navy-800 rounded-xl p-5 hover:border-gold-500/50 transition-all duration-300 overflow-hidden">
                    <div class="corner-bracket corner-bracket-tl"></div>
                    <div class="corner-bracket corner-bracket-tr"></div>
                    <div class="flex items-start justify-between mb-3 relative">
                        <div class="w-9 h-9 rounded-lg bg-navy-800 flex items-center justify-center group-hover:bg-gold-500/20 transition">
                            <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full font-mono">3 late</span>
                    </div>
                    <p class="font-serif text-3xl font-bold text-white leading-none relative">8</p>
                    <p class="text-xs text-navy-400 uppercase tracking-wider mt-2 relative">Work Order Aktif</p>
                </a>

                <!-- HPP -->
                <a href="#" class="group relative bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-xl p-5 hover:border-gold-500 transition-all duration-300 overflow-hidden">
                    <div class="corner-bracket corner-bracket-tl" style="border-color: rgba(201, 169, 97, 0.8);"></div>
                    <div class="corner-bracket corner-bracket-tr" style="border-color: rgba(201, 169, 97, 0.8);"></div>
                    <div class="flex items-start justify-between mb-3 relative">
                        <div class="w-9 h-9 rounded-lg bg-gold-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full font-mono">+12%</span>
                    </div>
                    <p class="font-serif text-2xl font-bold text-white leading-none relative">Rp 145jt</p>
                    <p class="text-xs text-gold-500/70 uppercase tracking-wider mt-2 relative">HPP Bulan Ini</p>
                </a>

                <!-- Issue -->
                <a href="#" class="group relative bg-navy-900/50 border border-navy-800 rounded-xl p-5 hover:border-red-500/50 transition-all duration-300 overflow-hidden">
                    <div class="corner-bracket corner-bracket-tl" style="border-color: rgba(239, 68, 68, 0.5);"></div>
                    <div class="corner-bracket corner-bracket-tr" style="border-color: rgba(239, 68, 68, 0.5);"></div>
                    <div class="flex items-start justify-between mb-3 relative">
                        <div class="w-9 h-9 rounded-lg bg-navy-800 flex items-center justify-center group-hover:bg-red-500/20 transition">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-semibold text-red-400 bg-red-500/10 px-2 py-0.5 rounded-full font-mono">2 crit</span>
                    </div>
                    <p class="font-serif text-3xl font-bold text-white leading-none relative">5</p>
                    <p class="text-xs text-navy-400 uppercase tracking-wider mt-2 relative">Issue Terbuka</p>
                </a>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- SEGMENT 02: PROJECT TRACKING + ALERT -->
        <!-- ============================================ -->
        <section class="relative">
            
            <!-- Segment Header -->
            <div class="relative mb-6">
                <div class="laser-line laser-line-pulse mb-4">
                    <div class="laser-dot laser-dot-left"></div>
                    <div class="laser-dot laser-dot-right"></div>
                </div>
                <div class="flex items-end justify-between">
                    <div class="flex items-baseline gap-4">
                        <span class="font-mono text-xs text-gold-500 tracking-widest glow-gold">02</span>
                        <div>
                            <h3 class="font-serif text-xl font-bold text-white leading-none">Project Tracking</h3>
                            <p class="text-xs text-navy-400 mt-1.5 tracking-wide uppercase">Alur dokumen & perhatian</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Project List -->
                <div class="lg:col-span-2 bg-navy-900/50 border border-navy-800 rounded-xl overflow-hidden relative">
                    <div class="corner-bracket corner-bracket-tl"></div>
                    <div class="corner-bracket corner-bracket-tr"></div>
                    <div class="corner-bracket corner-bracket-bl"></div>
                    <div class="corner-bracket corner-bracket-br"></div>

                    <div class="px-5 py-4 border-b border-navy-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-gold-500" style="box-shadow: 0 0 8px #C9A961;"></div>
                            <span class="text-xs font-mono text-navy-400 uppercase tracking-widest">Active Projects</span>
                        </div>
                        <a href="#" class="text-xs font-semibold text-gold-500 hover:text-gold-400 uppercase tracking-wider">
                            Semua →
                        </a>
                    </div>

                    <div class="divide-y divide-navy-800">
                        
                        <!-- Project 1 -->
                        <div class="p-4 hover:bg-navy-800/30 transition cursor-pointer">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-mono font-semibold text-gold-500">SO-001</span>
                                    <span class="text-sm font-medium text-white">PT ABC</span>
                                    <span class="text-xs text-navy-400">Wind Turbin A · 100 pcs</span>
                                </div>
                                <span class="text-xs font-semibold text-white font-mono">65%</span>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex-1 h-1.5 bg-navy-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-gold-500 to-gold-400 rounded-full" style="width: 65%; box-shadow: 0 0 8px rgba(201,169,97,0.5);"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-4 text-navy-400 font-mono">
                                    <span>15 Nov</span>
                                    <span>Rp 6.15jt</span>
                                    <span class="text-green-400">38.5%</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-green-400" style="box-shadow: 0 0 6px #4ade80;"></div>
                                    <span class="text-green-400 font-mono text-[10px] uppercase tracking-wider">On Track</span>
                                </div>
                            </div>
                        </div>

                        <!-- Project 2 -->
                        <div class="p-4 hover:bg-navy-800/30 transition cursor-pointer">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-mono font-semibold text-gold-500">SO-002</span>
                                    <span class="text-sm font-medium text-white">PT XYZ</span>
                                    <span class="text-xs text-navy-400">Wind Turbin B · 50 pcs</span>
                                </div>
                                <span class="text-xs font-semibold text-white font-mono">25%</span>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex-1 h-1.5 bg-navy-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-amber-500 to-amber-400 rounded-full" style="width: 25%; box-shadow: 0 0 8px rgba(245,158,11,0.5);"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-4 text-navy-400 font-mono">
                                    <span>20 Nov</span>
                                    <span>—</span>
                                    <span>—</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-400" style="box-shadow: 0 0 6px #fbbf24;"></div>
                                    <span class="text-amber-400 font-mono text-[10px] uppercase tracking-wider">At Risk</span>
                                </div>
                            </div>
                        </div>

                        <!-- Project 3 -->
                        <div class="p-4 hover:bg-navy-800/30 transition cursor-pointer">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-mono font-semibold text-gold-500">SO-003</span>
                                    <span class="text-sm font-medium text-white">CV Maju</span>
                                    <span class="text-xs text-navy-400">Komponen C · 200 pcs</span>
                                </div>
                                <span class="text-xs font-semibold text-white font-mono">10%</span>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex-1 h-1.5 bg-navy-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-navy-600 to-navy-500 rounded-full" style="width: 10%"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-4 text-navy-400 font-mono">
                                    <span>25 Nov</span>
                                    <span>—</span>
                                    <span>—</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-navy-500"></div>
                                    <span class="text-navy-400 font-mono text-[10px] uppercase tracking-wider">Starting</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Alert -->
                <div class="bg-navy-900/50 border border-navy-800 rounded-xl overflow-hidden relative">
                    <div class="corner-bracket corner-bracket-tl"></div>
                    <div class="corner-bracket corner-bracket-tr"></div>
                    <div class="corner-bracket corner-bracket-bl"></div>
                    <div class="corner-bracket corner-bracket-br"></div>

                    <div class="px-5 py-4 border-b border-navy-800 flex items-center gap-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-500" style="box-shadow: 0 0 8px #ef4444;"></div>
                        <span class="text-xs font-mono text-navy-400 uppercase tracking-widest">Alerts</span>
                    </div>

                    <div class="p-4 space-y-3">
                        
                        <a href="#" class="flex items-start gap-3 p-3 rounded-lg bg-red-500/5 border border-red-500/20 hover:border-red-500/40 transition">
                            <div class="w-6 h-6 rounded-md bg-red-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-mono font-semibold text-red-400 mb-1 tracking-widest">CRITICAL</p>
                                <p class="text-sm text-white leading-tight">3 WO melewati deadline</p>
                                <p class="text-xs text-navy-400 mt-1 font-mono">SO-002 · SO-004 · SO-007</p>
                            </div>
                        </a>

                        <a href="#" class="flex items-start gap-3 p-3 rounded-lg bg-amber-500/5 border border-amber-500/20 hover:border-amber-500/40 transition">
                            <div class="w-6 h-6 rounded-md bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-mono font-semibold text-amber-400 mb-1 tracking-widest">WARNING</p>
                                <p class="text-sm text-white leading-tight">2 project over budget</p>
                                <p class="text-xs text-navy-400 mt-1 font-mono">Rp 8.5jt</p>
                            </div>
                        </a>

                        <a href="#" class="flex items-start gap-3 p-3 rounded-lg bg-amber-500/5 border border-amber-500/20 hover:border-amber-500/40 transition">
                            <div class="w-6 h-6 rounded-md bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-mono font-semibold text-amber-400 mb-1 tracking-widest">WARNING</p>
                                <p class="text-sm text-white leading-tight">Stok bahan di bawah minimum</p>
                                <p class="text-xs text-navy-400 mt-1 font-mono">5 material</p>
                            </div>
                        </a>

                        <a href="#" class="flex items-start gap-3 p-3 rounded-lg bg-navy-800/50 border border-navy-700 hover:border-navy-600 transition">
                            <div class="w-6 h-6 rounded-md bg-navy-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-navy-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-mono font-semibold text-navy-300 mb-1 tracking-widest">INFO</p>
                                <p class="text-sm text-white leading-tight">5 PO menunggu konfirmasi</p>
                                <p class="text-xs text-navy-400 mt-1 font-mono">Rp 42jt</p>
                            </div>
                        </a>

                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- SEGMENT 03: COSTING + PRODUCTION -->
        <!-- ============================================ -->
        <section class="relative">
            
            <!-- Segment Header -->
            <div class="relative mb-6">
                <div class="laser-line laser-line-pulse mb-4">
                    <div class="laser-dot laser-dot-left"></div>
                    <div class="laser-dot laser-dot-right"></div>
                </div>
                <div class="flex items-end justify-between">
                    <div class="flex items-baseline gap-4">
                        <span class="font-mono text-xs text-gold-500 tracking-widest glow-gold">03</span>
                        <div>
                            <h3 class="font-serif text-xl font-bold text-white leading-none">Costing & Production</h3>
                            <p class="text-xs text-navy-400 mt-1.5 tracking-wide uppercase">Analisis biaya & aktivitas hari ini</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- HPP Breakdown -->
                <div class="bg-navy-900/50 border border-navy-800 rounded-xl overflow-hidden relative">
                    <div class="corner-bracket corner-bracket-tl"></div>
                    <div class="corner-bracket corner-bracket-tr"></div>
                    <div class="corner-bracket corner-bracket-bl"></div>
                    <div class="corner-bracket corner-bracket-br"></div>

                    <div class="px-5 py-4 border-b border-navy-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-gold-500" style="box-shadow: 0 0 8px #C9A961;"></div>
                            <span class="text-xs font-mono text-navy-400 uppercase tracking-widest">HPP Composition</span>
                        </div>
                        <span class="text-xs font-mono text-white">Rp 145.000.000</span>
                    </div>

                    <div class="p-5 space-y-4">
                        @php
                            $hppItems = [
                                ['label' => 'Material', 'value' => 65000000, 'pct' => 45, 'color' => 'gold'],
                                ['label' => 'Labor', 'value' => 43500000, 'pct' => 30, 'color' => 'gold'],
                                ['label' => 'Machine', 'value' => 21750000, 'pct' => 15, 'color' => 'navy'],
                                ['label' => 'Waste', 'value' => 7250000, 'pct' => 5, 'color' => 'red'],
                                ['label' => 'Rework', 'value' => 7250000, 'pct' => 5, 'color' => 'red'],
                            ];
                        @endphp

                        @foreach($hppItems as $item)
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-medium text-navy-300 font-mono">{{ $item['label'] }}</span>
                                    <div class="flex items-center gap-2 font-mono">
                                        <span class="text-xs text-navy-400">Rp {{ number_format($item['value']/1000000, 1) }}jt</span>
                                        <span class="text-xs font-semibold text-white w-8 text-right">{{ $item['pct'] }}%</span>
                                    </div>
                                </div>
                                <div class="h-1.5 bg-navy-800 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full 
                                        {{ $item['color'] === 'gold' ? 'bg-gradient-to-r from-gold-500 to-gold-400' : '' }}
                                        {{ $item['color'] === 'navy' ? 'bg-gradient-to-r from-navy-500 to-navy-400' : '' }}
                                        {{ $item['color'] === 'red' ? 'bg-gradient-to-r from-red-500 to-red-400' : '' }}
                                    " style="width: {{ $item['pct'] }}%; 
                                        {{ $item['color'] === 'gold' ? 'box-shadow: 0 0 8px rgba(201,169,97,0.5);' : '' }}
                                        {{ $item['color'] === 'red' ? 'box-shadow: 0 0 8px rgba(239,68,68,0.5);' : '' }}
                                    "></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Production Today -->
                <div class="bg-navy-900/50 border border-navy-800 rounded-xl overflow-hidden relative">
                    <div class="corner-bracket corner-bracket-tl"></div>
                    <div class="corner-bracket corner-bracket-tr"></div>
                    <div class="corner-bracket corner-bracket-bl"></div>
                    <div class="corner-bracket corner-bracket-br"></div>

                    <div class="px-5 py-4 border-b border-navy-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-500" style="box-shadow: 0 0 8px #22c55e;"></div>
                            <span class="text-xs font-mono text-navy-400 uppercase tracking-widest">Live Production</span>
                        </div>
                        <span class="text-xs font-mono text-white">{{ now()->format('H:i') }} WIB</span>
                    </div>

                    <div class="p-5 grid grid-cols-2 gap-4">
                        
                        <div class="bg-navy-800/50 rounded-lg p-4 border border-navy-700/50">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-gold-500"></div>
                                <p class="text-[10px] text-navy-400 uppercase tracking-widest font-mono">WO Running</p>
                            </div>
                            <p class="font-serif text-2xl font-bold text-white">5</p>
                        </div>

                        <div class="bg-navy-800/50 rounded-lg p-4 border border-navy-700/50">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-gold-500"></div>
                                <p class="text-[10px] text-navy-400 uppercase tracking-widest font-mono">Operators</p>
                            </div>
                            <p class="font-serif text-2xl font-bold text-white">12</p>
                        </div>

                        <div class="bg-navy-800/50 rounded-lg p-4 border border-navy-700/50">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400"></div>
                                <p class="text-[10px] text-navy-400 uppercase tracking-widest font-mono">Machines</p>
                            </div>
                            <p class="font-serif text-2xl font-bold text-white">3<span class="text-sm text-navy-400">/4</span></p>
                        </div>

                        <div class="bg-navy-800/50 rounded-lg p-4 border border-navy-700/50">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <p class="text-[10px] text-navy-400 uppercase tracking-widest font-mono">Waste Rate</p>
                            </div>
                            <p class="font-serif text-2xl font-bold text-white">3.2<span class="text-sm text-navy-400">%</span></p>
                        </div>

                    </div>
                </div>
            </div>
        </section>

    </div>
</x-app-layout>