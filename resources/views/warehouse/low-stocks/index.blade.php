<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Low Stock Alert</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4C"
            title="Low Stock Alert"
            subtitle="Item yang stoknya di bawah minimum"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-navy-900/50 border {{ $stats['total_low'] > 0 ? 'border-red-500/50' : 'border-navy-800' }} rounded-xl p-5">
                <p class="text-[10px] font-mono {{ $stats['total_low'] > 0 ? 'text-red-400' : 'text-navy-400' }} uppercase tracking-widest mb-1">Total Low Stock</p>
                <p class="font-serif text-3xl font-bold {{ $stats['total_low'] > 0 ? 'text-red-400' : 'text-white' }}">{{ $stats['total_low'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-blue-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-blue-400 uppercase tracking-widest mb-1">Material</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['total_material'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-green-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Product</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['total_product'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Estimasi Nilai Kekurangan</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($stats['total_value']) }}</p>
            </div>
        </div>

        {{-- FILTER --}}
        <x-atelier.card :brackets="true" padding="p-0">
            <div class="px-5 py-4 border-b border-navy-800">
                <form method="GET" action="{{ route('warehouse.low-stocks.index') }}" class="flex gap-3 flex-wrap">
                    <select name="warehouse_id" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="">Semua Warehouse</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->nama }}</option>
                        @endforeach
                    </select>
                    <select name="item_type" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="">Semua Tipe</option>
                        <option value="material" @selected(request('item_type') == 'material')>Material</option>
                        <option value="product" @selected(request('item_type') == 'product')>Product</option>
                    </select>
                    <x-atelier.button type="submit" variant="secondary">Filter</x-atelier.button>
                    @if(request()->hasAny(['warehouse_id', 'item_type']))
                        <x-atelier.button :href="route('warehouse.low-stocks.index')" variant="ghost">Reset</x-atelier.button>
                    @endif
                </form>
            </div>

            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Item</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tipe</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Lokasi</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Stok</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Min</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Kekurangan</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $inv)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm">
                                        @if($inv->item)
                                            <span class="text-white font-medium">{{ $inv->item->nama }}</span>
                                            <br><span class="text-xs font-mono text-gold-500">{{ $inv->item->kode }}</span>
                                        @else
                                            <span class="text-navy-500 italic">Item tidak ditemukan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @php
                                            $typeColor = $inv->item_type === 'material'
                                                ? 'bg-blue-500/10 text-blue-400 border-blue-500/30'
                                                : 'bg-green-500/10 text-green-400 border-green-500/30';
                                        @endphp
                                        <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $typeColor }}">
                                            {{ ucfirst($inv->item_type) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-navy-300">
                                        {{ $inv->location->nama ?? '-' }}
                                        <br><span class="text-xs text-navy-500">{{ $inv->location->warehouse->nama ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="text-sm font-mono text-red-400 font-semibold">
                                            {{ format_angka($inv->qty, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right text-sm font-mono text-navy-300">
                                        {{ format_angka($inv->min_stock, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="text-sm font-mono text-red-400 font-bold">
                                            -{{ format_angka($inv->min_stock - $inv->qty, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <x-atelier.button :href="route('warehouse.inventories.show', $inv)" variant="ghost" size="sm">Detail</x-atelier.button>
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
                    title="✅ Semua stok aman"
                    subtitle="Tidak ada item yang stoknya di bawah minimum."
                />
            @endif
        </x-atelier.card>

    </div>
</x-app-layout>