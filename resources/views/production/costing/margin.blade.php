<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Margin Analysis</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5C"
            title="Margin Analysis"
            subtitle="Analisis profitabilitas per produk"
        />

        <x-atelier.card :brackets="true" padding="p-0">
            @if($margins->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Produk</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Harga Jual</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Avg HPP</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Labor</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Overhead</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Margin</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Margin %</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Produksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($margins as $m)
                                @php
                                    $product = $m['product'];
                                    $marginColor = $m['gross_margin'] > 0 ? 'text-green-400' : 'text-red-400';
                                    $marginPct = $m['margin_percent'];
                                    $badgeColor = $marginPct >= 30 
                                        ? 'bg-green-500/10 border-green-500/30 text-green-400'
                                        : ($marginPct >= 15 
                                            ? 'bg-gold-500/10 border-gold-500/30 text-gold-400'
                                            : ($marginPct > 0
                                                ? 'bg-orange-500/10 border-orange-500/30 text-orange-400'
                                                : 'bg-red-500/10 border-red-500/30 text-red-400'));
                                @endphp
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm">
                                        <span class="text-white font-medium">{{ $product->nama }}</span>
                                        <br><span class="text-xs font-mono text-navy-500">{{ $product->kode }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                        {{ format_rupiah($m['selling_price']) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white font-semibold">
                                        {{ format_rupiah($m['avg_cost']) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-xs text-blue-400">
                                        {{ format_rupiah($m['avg_detail']['avg_material_cost']) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-xs text-gold-500">
                                        {{ format_rupiah($m['avg_detail']['avg_labor_cost']) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-xs text-orange-400">
                                        {{ format_rupiah($m['avg_detail']['avg_overhead_cost']) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm font-bold {{ $marginColor }}">
                                        {{ $m['gross_margin'] > 0 ? '+' : '' }}{{ format_rupiah($m['gross_margin']) }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border font-bold {{ $badgeColor }}">
                                            {{ number_format($marginPct, 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-xs text-navy-400">
                                        {{ format_angka($m['avg_detail']['total_produced'], 0) }}
                                        <br><span class="text-[10px]">{{ $m['avg_detail']['snapshot_count'] }} snapshot</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-atelier.empty-state
                    title="Belum ada data margin"
                    subtitle="Data margin muncul setelah ada snapshot costing."
                />
            @endif
        </x-atelier.card>

        {{-- LEGENDA --}}
        <x-atelier.card :brackets="true">
            <div class="flex flex-wrap gap-6 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="text-navy-300"><strong>≥ 30%</strong> — Margin sehat</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gold-500"></span>
                    <span class="text-navy-300"><strong>15-30%</strong> — Margin cukup</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                    <span class="text-navy-300"><strong>0-15%</strong> — Margin tipis</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="text-navy-300"><strong>&lt; 0%</strong> — Rugi</span>
                </div>
            </div>
        </x-atelier.card>

    </div>
</x-app-layout>