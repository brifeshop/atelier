<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">HPP & Costing</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5C"
            title="Dashboard Costing"
            subtitle="Analisis HPP & biaya produksi"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- FILTER PERIODE --}}
        <x-atelier.card :brackets="true">
            <form method="GET" action="{{ route('production.costing.index') }}" class="flex items-end gap-3 flex-wrap">
                <div>
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Periode</label>
                    <select name="period" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        @foreach([7, 30, 60, 90, 180, 365] as $p)
                            <option value="{{ $p }}" @selected($period == $p)>
                                {{ $p }} hari terakhir
                            </option>
                        @endforeach
                    </select>
                </div>
                <x-atelier.button type="submit" variant="secondary">Terapkan</x-atelier.button>
                <div class="ml-auto flex gap-2">
                    <x-atelier.button :href="route('production.costing.variance')" variant="ghost" size="sm">Variance Analysis</x-atelier.button>
                    <x-atelier.button :href="route('production.costing.margin')" variant="ghost" size="sm">Margin Analysis</x-atelier.button>
                </div>
            </form>
        </x-atelier.card>

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">Snapshot</p>
                <p class="font-serif text-2xl font-bold text-white">{{ $stats['total_snapshots'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-blue-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-blue-400 uppercase tracking-widest mb-1">Total Produksi</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_angka($stats['total_produced'], 0) }}</p>
            </div>
            <div class="bg-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Total Cost</p>
                <p class="font-serif text-lg font-bold text-white">{{ format_rupiah($stats['total_cost']) }}</p>
            </div>
            <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Avg Cost/Unit</p>
                <p class="font-serif text-lg font-bold text-white">{{ format_rupiah($stats['avg_cost_per_unit']) }}</p>
            </div>
            <div class="bg-navy-900/50 border border-red-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-red-400 uppercase tracking-widest mb-1">Unfavorable</p>
                <p class="font-serif text-2xl font-bold text-white">{{ $stats['unfavorable_count'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-green-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Favorable</p>
                <p class="font-serif text-2xl font-bold text-white">{{ $stats['favorable_count'] }}</p>
            </div>
        </div>

        {{-- RECENT SNAPSHOTS --}}
        <x-atelier.card title="Snapshot Terbaru" :brackets="true" padding="p-0">
            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">No. WO</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Produk</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Standard</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Actual</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Variance</th>
                                <th class="px-5 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Status</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $snap)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm text-navy-300">{{ format_tanggal($snap->snapshot_date) }}</td>
                                    <td class="px-5 py-3 text-sm font-mono text-gold-500">{{ $snap->workOrder->wo_number ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm">
                                        <span class="text-white font-medium">{{ $snap->product->nama ?? '-' }}</span>
                                        <br><span class="text-xs font-mono text-navy-500">{{ $snap->product->kode ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white">
                                        {{ format_angka($snap->actual_qty, 0) }} / {{ format_angka($snap->planned_qty, 0) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                        {{ format_rupiah($snap->standard_cost_per_unit) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white font-semibold">
                                        {{ format_rupiah($snap->actual_cost_per_unit) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm {{ $snap->variance_color }}">
                                        {{ $snap->total_variance > 0 ? '+' : '' }}{{ format_rupiah($snap->total_variance) }}
                                        <br><span class="text-xs">{{ $snap->variance_percent > 0 ? '+' : '' }}{{ number_format($snap->variance_percent, 1) }}%</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $snap->variance_badge_color }}">
                                            {{ $snap->variance_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <x-atelier.button :href="route('production.costing.show', $snap)" variant="ghost" size="sm">Detail</x-atelier.button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                    <div class="px-5 py-4 border-t border-navy-800">
                        {{ $items->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <x-atelier.empty-state
                    title="Belum ada snapshot costing"
                    subtitle="Snapshot otomatis dibuat saat Work Order diselesaikan."
                />
            @endif
        </x-atelier.card>

    </div>
</x-app-layout>