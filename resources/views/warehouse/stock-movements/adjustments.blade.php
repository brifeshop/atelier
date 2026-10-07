<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Laporan Adjustment</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4C"
            title="Laporan Adjustment"
            subtitle="Riwayat penyesuaian stok"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">Total</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-red-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-red-400 uppercase tracking-widest mb-1">Rusak</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['damaged'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-orange-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-orange-400 uppercase tracking-widest mb-1">Hilang</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['lost'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-blue-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-blue-400 uppercase tracking-widest mb-1">Koreksi</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['correction'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Opname</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['opname'] }}</p>
            </div>
        </div>

        <x-atelier.card :brackets="true" padding="p-0">
            <div class="px-5 py-4 border-b border-navy-800">
                <form method="GET" action="{{ route('warehouse.stock-movements.adjustments') }}" class="flex gap-3 flex-wrap">
                    <select name="adjustment_reason" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="">Semua Alasan</option>
                        <option value="damaged" @selected(request('adjustment_reason') == 'damaged')>Barang Rusak</option>
                        <option value="lost" @selected(request('adjustment_reason') == 'lost')>Barang Hilang</option>
                        <option value="expired" @selected(request('adjustment_reason') == 'expired')>Kadaluarsa</option>
                        <option value="correction" @selected(request('adjustment_reason') == 'correction')>Koreksi Pencatatan</option>
                        <option value="found" @selected(request('adjustment_reason') == 'found')>Barang Ditemukan</option>
                        <option value="opname" @selected(request('adjustment_reason') == 'opname')>Hasil Opname</option>
                        <option value="other" @selected(request('adjustment_reason') == 'other')>Lainnya</option>
                    </select>
                    <select name="warehouse_id" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="">Semua Warehouse</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->nama }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    <x-atelier.button type="submit" variant="secondary">Filter</x-atelier.button>
                    @if(request()->hasAny(['adjustment_reason', 'warehouse_id', 'date_from', 'date_to']))
                        <x-atelier.button :href="route('warehouse.stock-movements.adjustments')" variant="ghost">Reset</x-atelier.button>
                    @endif
                </form>
            </div>

            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">No. Movement</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Item</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Alasan</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Nilai</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $mov)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm text-navy-300">{{ format_tanggal($mov->date) }}</td>
                                    <td class="px-5 py-3 text-sm font-mono text-gold-500">{{ $mov->movement_number }}</td>
                                    <td class="px-5 py-3 text-sm">
                                        @if($mov->item)
                                            <span class="text-white font-medium">{{ $mov->item->nama }}</span>
                                            <br><span class="text-xs font-mono text-navy-500">{{ $mov->item->kode }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gold-500/10 border border-gold-500/30 text-gold-500">
                                            {{ $mov->adjustment_reason_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm {{ $mov->qty > 0 ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $mov->qty > 0 ? '+' : '' }}{{ format_angka($mov->qty, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                        {{ $mov->total_value ? format_rupiah(abs($mov->total_value)) : '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <x-atelier.button :href="route('warehouse.stock-movements.show', $mov)" variant="ghost" size="sm">Detail</x-atelier.button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                    <div class="px-5 py-4 border-t border-navy-800">
                        {{ $items->links() }}
                    </div>
                @endif
            @else
                <x-atelier-empty-state
                    title="Belum ada adjustment"
                    subtitle="Belum ada penyesuaian stok yang tercatat."
                />
            @endif
        </x-atelier.card>

    </div>
</x-app-layout>