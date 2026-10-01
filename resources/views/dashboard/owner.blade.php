<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-[10px] font-semibold text-gold-500 uppercase tracking-widest mb-0.5">Executive Summary</p>
            <h2 class="font-serif text-2xl font-bold text-white">Owner Dashboard</h2>
        </div>
    </x-slot>

    <div class="p-8 grid-bg">

        {{-- Segment 01: Business Health --}}
        <x-atelier.page-header 
            segment="01"
            title="Business Health"
            subtitle="Kesehatan bisnis bulan ini"
        />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            <x-atelier.stat-card
                label="Revenue"
                value="Rp 500jt"
                trend="+12%"
                trend-type="up"
                accent
            />
            <x-atelier.stat-card
                label="Gross Margin"
                value="35%"
                trend="-2%"
                trend-type="down"
            />
            <x-atelier.stat-card
                label="Net Profit"
                value="Rp 85jt"
                trend="+5%"
                trend-type="up"
            />
            <x-atelier.stat-card
                label="Cash Position"
                value="Rp 120jt"
                trend="+8%"
                trend-type="up"
            />
        </div>

        {{-- Segment 02: Performance & Alert --}}
        <x-atelier.page-header 
            segment="02"
            title="Performance & Alert"
            subtitle="Top customer dan peringatan"
        />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <x-atelier.card title="Top 5 Customer">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-navy-800">
                            <span class="text-sm text-white">PT ABC</span>
                            <span class="text-sm font-mono text-gold-500">Rp 85jt (17%)</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-navy-800">
                            <span class="text-sm text-white">PT XYZ</span>
                            <span class="text-sm font-mono text-gold-500">Rp 72jt (14%)</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-white">CV Maju</span>
                            <span class="text-sm font-mono text-gold-500">Rp 65jt (13%)</span>
                        </div>
                    </div>
                </x-atelier.card>
            </div>

            <x-atelier.card title="Alert">
                <div class="space-y-3">
                    <div class="p-3 rounded-lg bg-red-500/5 border border-red-500/20">
                        <p class="text-[10px] font-mono font-semibold text-red-400 tracking-widest mb-1">CRITICAL</p>
                        <p class="text-sm text-white">2 project over budget</p>
                    </div>
                    <div class="p-3 rounded-lg bg-amber-500/5 border border-amber-500/20">
                        <p class="text-[10px] font-mono font-semibold text-amber-400 tracking-widest mb-1">WARNING</p>
                        <p class="text-sm text-white">5 invoice jatuh tempo</p>
                    </div>
                </div>
            </x-atelier.card>
        </div>

    </div>
</x-app-layout>