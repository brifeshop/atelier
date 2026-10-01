<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Atelier') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-navy-950 text-navy-100">
        
        <div class="min-h-screen flex" x-data="{ 
            sidebarOpen: JSON.parse(localStorage.getItem('sidebarOpen') ?? 'true'),
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                localStorage.setItem('sidebarOpen', this.sidebarOpen);
            }
        }" x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))">

            <!-- ============================================ -->
            <!-- SIDEBAR -->
            <!-- ============================================ -->
            <aside 
                :class="sidebarOpen ? 'w-64' : 'w-20'"
                class="bg-navy-950 border-r border-navy-800 flex flex-col flex-shrink-0 transition-all duration-300 ease-in-out"
            >
                
                <!-- LOGO + TOGGLE -->
                <div class="h-20 flex items-center border-b border-navy-800 px-4"
                     :class="sidebarOpen ? 'justify-between' : 'justify-center'">
                    
                    <!-- Logo -->
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gold-500 to-gold-600 flex items-center justify-center shadow-lg shadow-gold-500/20 flex-shrink-0">
                            <span class="font-serif text-xl font-bold text-navy-900">A</span>
                        </div>
                        <div x-show="sidebarOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-x-2"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             class="overflow-hidden">
                            <p class="font-serif text-lg font-bold text-white leading-none">Atelier</p>
                            <p class="text-[10px] text-navy-500 italic leading-none mt-1">L'atelier de votre production</p>
                        </div>
                    </div>

                    <!-- Toggle Button -->
                    <button @click="toggleSidebar()" 
                            x-show="sidebarOpen"
                            class="text-navy-500 hover:text-gold-500 transition flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>

                <!-- EXPAND BUTTON (saat collapsed) -->
                <button @click="toggleSidebar()" 
                        x-show="!sidebarOpen"
                        class="mx-auto mt-4 w-10 h-10 rounded-lg text-navy-500 hover:text-gold-500 hover:bg-navy-800 transition flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- NAVIGATION -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                    @php
                        $menuItems = [
                            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'group' => null],
                            ['route' => null, 'label' => 'Sales Order', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'group' => 'Sales'],
                            ['route' => 'master.customers.index', 'label' => 'Customer', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'group' => 'Sales'],
                            ['route' => null, 'label' => 'Purchase Order', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', 'group' => 'Purchasing'],
                            ['route' => null, 'label' => 'Supplier', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'group' => 'Purchasing'],
                            ['route' => null, 'label' => 'Inventory', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'group' => 'Warehouse'],
                            ['route' => null, 'label' => 'Stock Movement', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'group' => 'Warehouse'],
                            ['route' => null, 'label' => 'Work Order', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'group' => 'Produksi'],
                            ['route' => null, 'label' => 'HPP & Costing', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'group' => 'Produksi'],
                            ['route' => null, 'label' => 'Quality Control', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'group' => 'Produksi'],
                            ['route' => null, 'label' => 'Surat Jalan', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0', 'group' => 'Pengiriman'],
                            ['route' => null, 'label' => 'Invoice', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'group' => 'Pengiriman'],
                            ['route' => null, 'label' => 'Laporan', 'icon' => 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'group' => 'Lainnya'],
                            ['route' => null, 'label' => 'Pengaturan', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'group' => 'Lainnya'],
                        ];
                    @endphp

                    @foreach($menuItems as $item)
                        
                        {{-- Group Label --}}
                        @if($item['group'] && ($loop->first || $menuItems[$loop->index - 1]['group'] !== $item['group']))
                            <div x-show="sidebarOpen" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 class="pt-4 pb-2 px-3">
                                <p class="text-[10px] font-semibold text-navy-500 uppercase tracking-widest">{{ $item['group'] }}</p>
                            </div>
                            <div x-show="!sidebarOpen" class="pt-3"></div>
                        @endif

                        @php
                            // ✅ FIXED: pakai wildcard * agar sub-route juga ter-highlight
                            $isActive = $item['route'] && request()->routeIs($item['route'] . '*');
                        @endphp

                        <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                           class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200
                                  {{ $isActive 
                                      ? 'bg-gold-500 text-navy-900 shadow-lg shadow-gold-500/20' 
                                      : 'text-navy-300 hover:text-white hover:bg-navy-800' }}"
                           :class="!sidebarOpen && 'justify-center'">
                            
                            <!-- Active Indicator Bar -->
                            @if($isActive)
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-navy-900 rounded-r-full -ml-3"></div>
                            @endif

                            <!-- Icon -->
                            <svg class="w-5 h-5 flex-shrink-0 {{ $isActive ? 'stroke-[2.5]' : '' }}" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>

                            <!-- Label -->
                            <span x-show="sidebarOpen"
                                  x-transition:enter="transition ease-out duration-200"
                                  x-transition:enter-start="opacity-0 -translate-x-2"
                                  x-transition:enter-end="opacity-100 translate-x-0"
                                  class="text-sm font-medium whitespace-nowrap overflow-hidden">
                                {{ $item['label'] }}
                            </span>

                            <!-- Tooltip (saat collapsed) -->
                            <div x-show="!sidebarOpen"
                                 class="absolute left-full ml-3 px-3 py-1.5 bg-navy-800 text-white text-xs font-medium rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap transition-opacity z-50 shadow-xl border border-navy-700">
                                {{ $item['label'] }}
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1 w-2 h-2 bg-navy-800 rotate-45 border-l border-b border-navy-700"></div>
                            </div>
                        </a>
                    @endforeach
                </nav>

                <!-- USER PROFILE (Bawah) -->
                <div class="border-t border-navy-800 p-3" x-data="{ userMenuOpen: false }">
                    <div class="relative">
                        <!-- User Button -->
                        <button @click="userMenuOpen = !userMenuOpen" 
                                class="w-full flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-navy-800 transition"
                                :class="!sidebarOpen && 'justify-center'">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gold-500 to-gold-600 text-navy-900 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div x-show="sidebarOpen" class="overflow-hidden flex-1 text-left">
                                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-navy-500 truncate">{{ Auth::user()->getRoleNames()->first() ?? 'User' }}</p>
                            </div>
                            <svg x-show="sidebarOpen" class="w-4 h-4 text-navy-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="userMenuOpen" 
                            @click.away="userMenuOpen = false"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute bottom-full left-0 mb-2 w-full bg-navy-800 border border-navy-700 rounded-lg shadow-xl overflow-hidden z-50">
                            
                            <a href="{{ route('profile.edit') }}" 
                            class="flex items-center gap-3 px-3 py-2.5 text-sm text-navy-200 hover:bg-navy-700 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span x-show="sidebarOpen">Profile</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span x-show="sidebarOpen">Log Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- ============================================ -->
            <!-- MAIN CONTENT -->
            <!-- ============================================ -->
            <div class="flex-1 flex flex-col min-w-0">
                
                <!-- TOP NAVBAR -->
                <header class="h-20 bg-navy-950/80 backdrop-blur border-b border-navy-800 px-8 flex items-center justify-between flex-shrink-0">
                    <div>
                        <p class="text-[10px] font-semibold text-gold-500 uppercase tracking-widest mb-0.5">Atelier</p>
                        <h2 class="font-serif text-2xl font-bold text-white">
                            @yield('title', 'Dashboard')
                        </h2>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Date -->
                        <div class="text-right hidden md:block">
                            <p class="text-xs text-navy-400">{{ now()->translatedFormat('l, d F Y') }}</p>
                            <p class="text-sm font-medium text-gold-500">{{ now()->format('H:i') }} WIB</p>
                        </div>
                        
                        <!-- Notification -->
                        <button class="relative w-10 h-10 rounded-lg bg-navy-900 border border-navy-800 text-navy-400 hover:text-gold-500 hover:border-gold-500/50 transition flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </button>
                    </div>
                </header>

                <!-- PAGE CONTENT -->
                <main class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>

                <!-- FOOTER -->
                <footer class="bg-navy-950 border-t border-navy-800 px-8 py-3 text-center text-xs text-navy-500 flex-shrink-0">
                    &copy; {{ date('Y') }} Atelier — L'atelier de votre production.
                </footer>
            </div>
        </div>
    </body>
</html>