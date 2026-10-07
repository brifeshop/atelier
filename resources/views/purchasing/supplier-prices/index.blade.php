<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Supplier Price List</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4D"
            title="Supplier Price List"
            subtitle="Daftar harga material per supplier"
            action="Tambah Harga"
            :actionUrl="route('purchasing.supplier-prices.create')"
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

        <x-atelier.card :brackets="true" padding="p-0">
            <div class="px-5 py-4 border-b border-navy-800 flex items-center justify-between flex-wrap gap-3">
                <form method="GET" action="{{ route('purchasing.supplier-prices.index') }}" class="flex gap-3 flex-wrap flex-1">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari supplier atau material..."
                        class="flex-1 min-w-[200px] px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                    >
                    <select name="supplier_id" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->nama }}</option>
                        @endforeach
                    </select>
                    <select name="material_id" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="">Semua Material</option>
                        @foreach($materials as $m)
                            <option value="{{ $m->id }}" @selected(request('material_id') == $m->id)>{{ $m->kode }} — {{ $m->nama }}</option>
                        @endforeach
                    </select>
                    <x-atelier.button type="submit" variant="secondary">Filter</x-atelier.button>
                    @if(request()->hasAny(['search', 'supplier_id', 'material_id']))
                        <x-atelier.button :href="route('purchasing.supplier-prices.index')" variant="ghost">Reset</x-atelier.button>
                    @endif
                </form>

                <x-atelier.button :href="route('purchasing.supplier-prices.compare')" variant="secondary" size="sm">
                    Bandingkan Harga
                </x-atelier.button>
            </div>

            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Supplier</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Harga</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Min Order</th>
                                <th class="px-5 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Lead Time</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Valid</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Status</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $price)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm text-white font-medium">
                                        {{ $price->supplier->nama ?? '-' }}
                                        @if($price->supplier)
                                            <br><span class="text-xs text-navy-500 font-mono">{{ $price->supplier->kode }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-navy-300">
                                        {{ $price->material->nama ?? '-' }}
                                        @if($price->material)
                                            <br><span class="text-xs text-navy-500 font-mono">{{ $price->material->kode }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-gold-500 font-semibold">
                                        {{ format_rupiah($price->price) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-navy-300 text-sm">
                                        {{ $price->min_order_qty ? format_angka($price->min_order_qty, 2) : '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-center text-sm text-navy-300">
                                        {{ $price->lead_time_days ? $price->lead_time_days . ' hari' : '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-navy-400">
                                        @if($price->valid_from && $price->valid_to)
                                            {{ format_tanggal($price->valid_from) }} — {{ format_tanggal($price->valid_to) }}
                                        @elseif($price->valid_from)
                                            Dari {{ format_tanggal($price->valid_from) }}
                                        @elseif($price->valid_to)
                                            Sampai {{ format_tanggal($price->valid_to) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($price->is_active && $price->is_valid)
                                            <span class="inline-flex items-center gap-1.5 text-xs text-green-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Aktif
                                            </span>
                                        @elseif(!$price->is_active)
                                            <span class="inline-flex items-center gap-1.5 text-xs text-navy-500">
                                                <span class="w-1.5 h-1.5 rounded-full bg-navy-500"></span>Nonaktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs text-red-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Expired
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-atelier.button :href="route('purchasing.supplier-prices.edit', $price)" variant="secondary" size="sm">Edit</x-atelier.button>
                                            <form method="POST" action="{{ route('purchasing.supplier-prices.destroy', $price) }}" onsubmit="return confirm('Yakin hapus harga ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <x-atelier.button type="submit" variant="danger" size="sm">Hapus</x-atelier.button>
                                            </form>
                                        </div>
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
                    title="Belum ada harga supplier"
                    subtitle="Mulai dengan menambahkan harga material dari supplier."
                    action="Tambah Harga"
                    :actionUrl="route('purchasing.supplier-prices.create')"
                />
            @endif
        </x-atelier.card>

    </div>
</x-app-layout>