<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Variance Analysis</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5C"
            title="Variance Analysis"
            subtitle="Analisis selisih standard vs aktual"
        />

        <x-atelier.card :brackets="true">
            <form method="GET" action="{{ route('production.costing.variance') }}" class="flex gap-3 flex-wrap">
                <select name="product_id" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                            {{ $p->kode }} — {{ $p->nama }}
                        </option>
                    @endforeach
                </select>
                <select name="variance_type" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    <option value="">Semua Variance</option>
                    <option value="unfavorable" @selected(request('variance_type') == 'unfavorable')>Unfavorable (Merah)</option>
                    <option value="favorable" @selected(request('variance_type') == 'favorable')>Favorable (Hijau)</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                <x-atelier.button type="submit" variant="secondary">Filter</x-atelier.button>
                @if(request()->hasAny(['product_id', 'variance_type', 'date_from', 'date_to']))
                    <x-atelier.button :href="route('production.costing.variance')" variant="ghost">Reset</x-atelier.button>
                @endif
            </form>
        </x-atelier.card>

        <x-atelier.card :brackets="true" padding="p-0">
            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">No. WO</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Produk</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material Var</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Labor Var</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Overhead Var</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total Var</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">%</th>
                                <th class="px-4 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $snap)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm text-navy-300">{{ format_tanggal($snap->snapshot_date) }}</td>
                                    <td class="px-5 py-3 text-sm font-mono text-gold-500">{{ $snap->workOrder->wo_number ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm">
                                        <span class="text-white font-medium">{{ $snap->product->nama ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm {{ $snap->material_variance > 0 ? 'text-red-400' : ($snap->material_variance < 0 ? 'text-green-400' : 'text-navy-400') }}">
                                        {{ $snap->material_variance > 0 ? '+' : '' }}{{ format_rupiah($snap->material_variance) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm {{ $snap->labor_variance > 0 ? 'text-red-400' : ($snap->labor_variance < 0 ? 'text-green-400' : 'text-navy-400') }}">
                                        {{ $snap->labor_variance > 0 ? '+' : '' }}{{ format_rupiah($snap->labor_variance) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm {{ $snap->overhead_variance > 0 ? 'text-red-400' : ($snap->overhead_variance < 0 ? 'text-green-400' : 'text-navy-400') }}">
                                        {{ $snap->overhead_variance > 0 ? '+' : '' }}{{ format_rupiah($snap->overhead_variance) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm font-bold {{ $snap->variance_color }}">
                                        {{ $snap->total_variance > 0 ? '+' : '' }}{{ format_rupiah($snap->total_variance) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm {{ $snap->variance_color }}">
                                        {{ $snap->variance_percent > 0 ? '+' : '' }}{{ number_format($snap->variance_percent, 1) }}%
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $snap->variance_badge_color }}">
                                            {{ $snap->variance_label }}
                                        </span>
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
                    title="Belum ada data variance"
                    subtitle="Snapshot akan muncul setelah Work Order diselesaikan."
                />
            @endif
        </x-atelier.card>

        {{-- LEGENDA --}}
        <x-atelier.card :brackets="true">
            <div class="flex flex-wrap gap-6 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="text-navy-300"><strong class="text-red-400">Unfavorable</strong> — biaya aktual lebih tinggi dari standar</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="text-navy-300"><strong class="text-green-400">Favorable</strong> — biaya aktual lebih rendah dari standar</span>
                </div>
            </div>
        </x-atelier.card>

    </div>
</x-app-layout>