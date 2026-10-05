<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Stock Movement</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4A"
            title="Stock Movement"
            subtitle="Riwayat pergerakan stok"
            action="Catat Movement"
            :actionUrl="route('warehouse.stock-movements.create')"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">Total Movement</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['total_movements'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-green-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">IN</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['in_count'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-red-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-red-400 uppercase tracking-widest mb-1">OUT</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['out_count'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Adjustment</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['adjustment_count'] }}</p>
            </div>
        </div>

        <x-atelier.card :brackets="true" padding="p-0">
            <div class="px-5 py-4 border-b border-navy-800 flex items-center justify-between flex-wrap gap-3">
                <form method="GET" action="{{ route('warehouse.stock-movements.index') }}" class="flex gap-3 flex-wrap flex-1">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nomor, item, referensi..."
                        class="flex-1 min-w-[200px] px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                    >
                    <select name="type" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="">Semua Type</option>
                        <option value="in" @selected(request('type') == 'in')>IN</option>
                        <option value="out" @selected(request('type') == 'out')>OUT</option>
                        <option value="transfer" @selected(request('type') == 'transfer')>Transfer</option>
                        <option value="adjustment" @selected(request('type') == 'adjustment')>Adjustment</option>
                    </select>
                    <x-atelier.button type="submit" variant="secondary">Filter</x-atelier.button>
                    @if(request()->hasAny(['search', 'type', 'warehouse_id']))
                        <x-atelier.button :href="route('warehouse.stock-movements.index')" variant="ghost">Reset</x-atelier.button>
                    @endif
                </form>
                <x-atelier.button :href="route('warehouse.stock-movements.transfer.create')" variant="secondary" size="sm">
                    Transfer Stok
                </x-atelier.button>
            </div>

            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">No. Movement</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Item</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Lokasi</th>
                                <th class="px-5 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Type</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Stok Akhir</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $mov)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm font-mono text-gold-500">{{ $mov->movement_number }}</td>
                                    <td class="px-5 py-3 text-sm text-navy-300">{{ format_tanggal($mov->date) }}</td>
                                    <td class="px-5 py-3 text-sm">
                                        @if($mov->item)
                                            <span class="text-white font-medium">{{ $mov->item->nama }}</span>
                                            <br><span class="text-xs font-mono text-navy-500">{{ $mov->item->kode }}</span>
                                        @else
                                            <span class="text-navy-500 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-navy-300">
                                        {{ $mov->location->nama ?? '-' }}
                                        <br><span class="text-xs text-navy-500">{{ $mov->location->warehouse->nama ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $mov->type_color }}">
                                            {{ $mov->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm {{ $mov->qty > 0 ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $mov->qty > 0 ? '+' : '' }}{{ format_angka($mov->qty, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white">
                                        {{ format_angka($mov->qty_after, 2) }}
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
                <x-atelier.empty-state
                    title="Belum ada stock movement"
                    subtitle="Mulai dengan mencatat pergerakan stok pertama."
                    action="Catat Movement"
                    :actionUrl="route('warehouse.stock-movements.create')"
                />
            @endif
        </x-atelier.card>

    </div>
</x-app-layout>