<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold text-gold-500 uppercase tracking-widest mb-0.5">Executive Summary</p>
                <h2 class="font-serif text-2xl font-bold text-white">Owner Dashboard</h2>
            </div>
        </div>
    </x-slot>

    <div class="p-8 space-y-8 grid-bg">

        <!-- Business Health -->
        <section>
            <div class="laser-line laser-line-pulse mb-4">
                <div class="laser-dot laser-dot-left"></div>
                <div class="laser-dot laser-dot-right"></div>
            </div>
            <div class="flex items-baseline gap-4 mb-6">
                <span class="font-mono text-xs text-gold-500 tracking-widest glow-gold">01</span>
                <div>
                    <h3 class="font-serif text-xl font-bold text-white">Business Health</h3>
                    <p class="text-xs text-navy-400 mt-1 uppercase tracking-wide">Kesehatan bisnis bulan ini</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                    <p class="text-xs text-gold-500 uppercase tracking-widest font-mono mb-2">Revenue</p>
                    <p class="font-serif text-3xl font-bold text-white">Rp 500jt</p>
                    <p class="text-xs text-green-400 mt-2 font-mono">▲ +12% vs bulan lalu</p>
                </div>

                <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                    <p class="text-xs text-navy-400 uppercase tracking-widest font-mono mb-2">Gross Margin</p>
                    <p class="font-serif text-3xl font-bold text-white">35%</p>
                    <p class="text-xs text-red-400 mt-2 font-mono">▼ -2% vs bulan lalu</p>
                </div>

                <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                    <p class="text-xs text-navy-400 uppercase tracking-widest font-mono mb-2">Net Profit</p>
                    <p class="font-serif text-3xl font-bold text-white">Rp 85jt</p>
                    <p class="text-xs text-green-400 mt-2 font-mono">▲ +5% vs bulan lalu</p>
                </div>

                <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                    <p class="text-xs text-navy-400 uppercase tracking-widest font-mono mb-2">Cash Position</p>
                    <p class="font-serif text-3xl font-bold text-white">Rp 120jt</p>
                    <p class="text-xs text-green-400 mt-2 font-mono">▲ +8% vs bulan lalu</p>
                </div>
            </div>
        </section>

        <!-- Top 5 & Alert -->
        <section>
            <div class="laser-line laser-line-pulse mb-4">
                <div class="laser-dot laser-dot-left"></div>
                <div class="laser-dot laser-dot-right"></div>
            </div>
            <div class="flex items-baseline gap-4 mb-6">
                <span class="font-mono text-xs text-gold-500 tracking-widest glow-gold">02</span>
                <div>
                    <h3 class="font-serif text-xl font-bold text-white">Performance & Alert</h3>
                    <p class="text-xs text-navy-400 mt-1 uppercase tracking-wide">Top customer, produk, dan peringatan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                    <h4 class="font-serif text-lg font-bold text-white mb-4">Top 5 Customer</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-navy-800">
                            <span class="text-sm text-white">PT ABC</span>
                            <span class="text-sm font-mono text-gold-500">Rp 85jt (17%)</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-navy-800">
                            <span class="text-sm text-white">PT XYZ</span>
                            <span class="text-sm font-mono text-gold-500">Rp 72jt (14%)</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-navy-800">
                            <span class="text-sm text-white">CV Maju</span>
                            <span class="text-sm font-mono text-gold-500">Rp 65jt (13%)</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-navy-800">
                            <span class="text-sm text-white">PT DEF</span>
                            <span class="text-sm font-mono text-gold-500">Rp 48jt (10%)</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-white">CV Sejahtera</span>
                            <span class="text-sm font-mono text-gold-500">Rp 35jt (7%)</span>
                        </div>
                    </div>
                </div>

                <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                    <h4 class="font-serif text-lg font-bold text-white mb-4">Alert</h4>
                    <div class="space-y-3">
                        <div class="p-3 rounded-lg bg-red-500/5 border border-red-500/20">
                            <p class="text-[10px] font-mono font-semibold text-red-400 tracking-widest mb-1">CRITICAL</p>
                            <p class="text-sm text-white">2 project over budget</p>
                        </div>
                        <div class="p-3 rounded-lg bg-amber-500/5 border border-amber-500/20">
                            <p class="text-[10px] font-mono font-semibold text-amber-400 tracking-widest mb-1">WARNING</p>
                            <p class="text-sm text-white">5 invoice jatuh tempo</p>
                        </div>
                        <div class="p-3 rounded-lg bg-amber-500/5 border border-amber-500/20">
                            <p class="text-[10px] font-mono font-semibold text-amber-400 tracking-widest mb-1">WARNING</p>
                            <p class="text-sm text-white">3 customer telat bayar</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-app-layout>