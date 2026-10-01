<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-[10px] font-semibold text-gold-500 uppercase tracking-widest mb-0.5">Purchasing Dashboard</p>
            <h2 class="font-serif text-2xl font-bold text-white">Purchasing</h2>
        </div>
    </x-slot>

    <div class="p-8 grid-bg">
        <div class="laser-line laser-line-pulse mb-6">
            <div class="laser-dot laser-dot-left"></div>
            <div class="laser-dot laser-dot-right"></div>
        </div>

        <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-navy-800/50 flex items-center justify-center border border-navy-700">
                <svg class="w-10 h-10 text-navy-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <p class="font-serif text-lg text-white mb-2">Dashboard Purchasing</p>
            <p class="text-sm text-navy-400">Akan diisi setelah modul Master Data & Produksi selesai.</p>
        </div>
    </div>
</x-app-layout>