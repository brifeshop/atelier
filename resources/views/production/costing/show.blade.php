<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Cost Snapshot</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5C"
            title="{{ $item->workOrder->wo_number ?? 'Snapshot' }}"
            subtitle="{{ $item->product->nama ?? '-' }} — {{ format_tanggal($item->snapshot_date) }}"
        />

        {{-- VARIANCE SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-500/10 to-navy-900/50 border border-blue-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-blue-400 uppercase tracking-widest mb-1">Standard Cost</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->standard_cost_per_unit) }}</p>
                <p class="text-xs text-navy-400 mt-1">per unit</p>
            </div>
            <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Actual Cost</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->actual_cost_per_unit) }}</p>
                <p class="text-xs text-navy-400 mt-1">per unit</p>
            </div>
            <div class="bg-gradient-to-br {{ $item->total_variance > 0 ? 'from-red-500/10 border-red-500/30' : ($item->total_variance < 0 ? 'from-green-500/10 border-green-500/30' : 'from-navy-500/10 border-navy-500/30') }} to-navy-900/50 border rounded-xl p-5">
                <p class="text-[10px] font-mono {{ $item->variance_color }} uppercase tracking-widest mb-1">Variance</p>
                <p class="font-serif text-2xl font-bold {{ $item->variance_color }}">
                    {{ $item->total_variance > 0 ? '+' : '' }}{{ format_rupiah($item->total_variance) }}
                </p>
                <p class="text-xs {{ $item->variance_color }} mt-1">
                    {{ $item->variance_percent > 0 ? '+' : '' }}{{ number_format($item->variance_percent, 1) }}% — {{ $item->variance_label }}
                </p>
            </div>
        </div>

        {{-- INFO --}}
        <x-atelier.card title="Informasi" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">No. WO</p>
                    <a href="{{ route('production.work-orders.show', $item->work_order_id) }}" class="text-sm text-gold-500 font-mono hover:text-gold-400">
                        {{ $item->workOrder->wo_number ?? '-' }}
                    </a>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Produk</p>
                    <p class="text-sm text-white font-medium">{{ $item->product->nama ?? '-' }}</p>
                    <p class="text-xs text-navy-500">{{ $item->product->kode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal Snapshot</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->snapshot_date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Qty Rencana</p>
                    <p class="text-sm text-navy-300">{{ format_angka($item->planned_qty, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Qty Selesai</p>
                    <p class="text-sm text-green-400 font-semibold">{{ format_angka($item->actual_qty, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Qty Reject</p>
                    <p class="text-sm text-red-400">{{ format_angka($item->rejected_qty, 2) }}</p>
                </div>
            </div>
        </x-atelier.card>

        {{-- COST BREAKDOWN --}}
        <x-atelier.card title="Cost Breakdown" :brackets="true" padding="p-0">
            <table class="w-full">
                <thead class="bg-navy-950/50 border-b border-navy-800">
                    <tr>
                        <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Komponen</th>
                        <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Standard</th>
                        <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Actual</th>
                        <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Variance</th>
                        <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-navy-800">
                    @php
                        $items = [
                            ['label' => 'Material', 'std' => $item->standard_material_cost, 'act' => $item->actual_material_cost, 'var' => $item->material_variance, 'color' => 'text-blue-400'],
                            ['label' => 'Labor', 'std' => $item->standard_labor_cost, 'act' => $item->actual_labor_cost, 'var' => $item->labor_variance, 'color' => 'text-gold-500'],
                            ['label' => 'Overhead', 'std' => $item->standard_overhead_cost, 'act' => $item->actual_overhead_cost, 'var' => $item->overhead_variance, 'color' => 'text-orange-400'],
                        ];
                    @endphp

                    @foreach($items as $row)
                        <tr>
                            <td class="px-5 py-3 text-sm {{ $row['color'] }} font-medium">{{ $row['label'] }}</td>
                            <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">{{ format_rupiah($row['std']) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-sm text-white">{{ format_rupiah($row['act']) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-sm font-semibold {{ $row['var'] > 0 ? 'text-red-400' : ($row['var'] < 0 ? 'text-green-400' : 'text-navy-400') }}">
                                {{ $row['var'] > 0 ? '+' : '' }}{{ format_rupiah($row['var']) }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-sm {{ $row['var'] > 0 ? 'text-red-400' : ($row['var'] < 0 ? 'text-green-400' : 'text-navy-400') }}">
                                @php
                                    $pct = $row['std'] > 0 ? ($row['var'] / $row['std']) * 100 : 0;
                                @endphp
                                {{ $pct > 0 ? '+' : '' }}{{ number_format($pct, 1) }}%
                            </td>
                        </tr>
                    @endforeach

                    <tr class="bg-gold-500/5 border-t-2 border-gold-500/30">
                        <td class="px-5 py-4 text-sm text-gold-500 font-bold">TOTAL</td>
                        <td class="px-5 py-4 text-right font-mono text-base text-navy-300 font-semibold">{{ format_rupiah($item->standard_total_cost) }}</td>
                        <td class="px-5 py-4 text-right font-mono text-base text-white font-bold">{{ format_rupiah($item->actual_total_cost) }}</td>
                        <td class="px-5 py-4 text-right font-mono text-base font-bold {{ $item->variance_color }}">
                            {{ $item->total_variance > 0 ? '+' : '' }}{{ format_rupiah($item->total_variance) }}
                        </td>
                        <td class="px-5 py-4 text-right font-mono text-base font-bold {{ $item->variance_color }}">
                            {{ $item->variance_percent > 0 ? '+' : '' }}{{ number_format($item->variance_percent, 1) }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </x-atelier.card>

        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('production.costing.index')" variant="ghost">Kembali</x-atelier.button>
            <x-atelier.button :href="route('production.work-orders.show', $item->work_order_id)" variant="secondary">Lihat Work Order</x-atelier.button>
        </div>

    </div>
</x-app-layout>