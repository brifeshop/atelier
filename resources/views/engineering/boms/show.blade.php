<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail BOM</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5A"
            title="{{ $item->kode }}"
            subtitle="{{ $item->product->nama ?? '-' }} — v{{ $item->version }}"
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

        {{-- INFO --}}
        <x-atelier.card title="Informasi BOM" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kode</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->kode }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status</p>
                    <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $item->status_color }}">
                        {{ $item->status_label }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Produk</p>
                    <p class="text-sm text-white font-medium">{{ $item->product->nama ?? '-' }}</p>
                    <p class="text-xs text-navy-500">{{ $item->product->kode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Versi</p>
                    <p class="text-sm text-navy-300">v{{ $item->version }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Berlaku Mulai</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->effective_date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dibuat Oleh</p>
                    <p class="text-sm text-navy-300">{{ $item->creator->name ?? '-' }}</p>
                </div>
                @if($item->notes)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                        <p class="text-sm text-navy-300">{{ $item->notes }}</p>
                    </div>
                @endif
            </div>
        </x-atelier.card>

        {{-- COST SUMMARY --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-navy-900/50 border border-blue-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-blue-400 uppercase tracking-widest mb-1">Material Cost</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->total_material_cost) }}</p>
            </div>
            <div class="bg-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Labor Cost</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->total_labor_cost) }}</p>
            </div>
            <div class="bg-navy-900/50 border border-orange-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-orange-400 uppercase tracking-widest mb-1">Overhead Cost</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->total_overhead_cost) }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500/10 to-navy-900/50 border border-green-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Total Cost</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->total_cost) }}</p>
            </div>
        </div>

        {{-- ITEMS --}}
        <x-atelier.card title="Komponen BOM" :brackets="true" padding="p-0">
            @if($item->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Seq</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Item</th>
                                <th class="px-5 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tipe</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Scrap %</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Unit Cost</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total</th>
                                @if($item->canEdit())
                                    <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($item->items as $bomItem)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm font-mono text-navy-500">{{ $bomItem->sequence }}</td>
                                    <td class="px-5 py-3 text-sm">
                                        <span class="text-white font-medium">{{ $bomItem->item->nama ?? '-' }}</span>
                                        <br><span class="text-xs font-mono text-navy-500">{{ $bomItem->item->kode ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $bomItem->item_type_color }}">
                                            {{ $bomItem->item_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white">
                                        {{ format_angka($bomItem->qty, 4) }}
                                        <span class="text-xs text-navy-500">{{ $bomItem->unit }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                        {{ $bomItem->scrap_percent > 0 ? format_angka($bomItem->scrap_percent, 2) . '%' : '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                        {{ format_rupiah($bomItem->unit_cost) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white font-semibold">
                                        {{ format_rupiah($bomItem->total_cost) }}
                                    </td>
                                    @if($item->canEdit())
                                        <td class="px-5 py-3 text-right">
                                            <form method="POST" action="{{ route('engineering.boms.items.destroy', [$item, $bomItem]) }}" onsubmit="return confirm('Hapus item ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-400 hover:text-red-300">Hapus</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-atelier.empty-state
                    title="Belum ada item"
                    subtitle="Tambahkan material atau sub-assembly di form bawah."
                />
            @endif
        </x-atelier.card>

        {{-- FORM ADD ITEM --}}
        @if($item->canEdit())
            <x-atelier.card title="Tambah Item" :brackets="true">
                <form method="POST" action="{{ route('engineering.boms.items.store', $item) }}" x-data="bomItemForm()">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- ITEM TYPE --}}
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                                Tipe Item <span class="text-red-400">*</span>
                            </label>
                            <select name="item_type" required x-model="itemType" @change="resetItem()"
                                    class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                <option value="material">Material</option>
                                <option value="product">Sub-Assembly (Product)</option>
                            </select>
                        </div>

                        {{-- ITEM SELECTOR --}}
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                                Item <span class="text-red-400">*</span>
                            </label>
                            <select name="item_id" required x-model="itemId"
                                    class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                <option value="">-- Pilih Item --</option>
                                <template x-for="opt in currentItems" :key="opt.id">
                                    <option :value="opt.id" x-text="opt.label"></option>
                                </template>
                            </select>
                        </div>

                        <x-atelier.input name="qty" label="Qty" type="number" step="0.0001" min="0.0001" required />

                        <x-atelier.input name="unit" label="Satuan" placeholder="pcs, kg, m" required />

                        <x-atelier.input name="scrap_percent" label="Scrap %" type="number" step="0.01" min="0" max="100" hint="Opsional, % waste" />

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-6 border-t border-navy-800">
                        <x-atelier.button type="submit" variant="primary">Tambah Item</x-atelier.button>
                    </div>
                </form>
            </x-atelier.card>
        @endif

        {{-- ACTIONS --}}
        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('engineering.boms.index')" variant="ghost">Kembali</x-atelier.button>

            <div class="flex gap-3">
                @if($item->canEdit())
                    <x-atelier.button :href="route('engineering.boms.edit', $item)" variant="secondary">Edit</x-atelier.button>

                    <form method="POST" action="{{ route('engineering.boms.recalculate', $item) }}">
                        @csrf
                        <x-atelier.button type="submit" variant="secondary">Hitung Ulang Cost</x-atelier.button>
                    </form>

                    @if($item->canActivate())
                        <form method="POST" action="{{ route('engineering.boms.activate', $item) }}" onsubmit="return confirm('Aktifkan BOM ini?')">
                            @csrf
                            @method('PATCH')
                            <x-atelier.button type="submit" variant="primary">Aktifkan BOM</x-atelier.button>
                        </form>
                    @endif
                @endif

                @if($item->canObsolete())
                    <form method="POST" action="{{ route('engineering.boms.obsolete', $item) }}" onsubmit="return confirm('Obsolete BOM ini?')">
                        @csrf
                        @method('PATCH')
                        <x-atelier.button type="submit" variant="danger">Obsolete</x-atelier.button>
                    </form>
                @endif
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        const materials = @js($materials ?? []);
        const products = @js($products ?? []);

        function bomItemForm() {
            return {
                itemType: 'material',
                itemId: '',

                get currentItems() {
                    if (this.itemType === 'material') {
                        return materials.map(m => ({ id: m.id, label: `[MAT] ${m.kode} — ${m.nama}` }));
                    }
                    return products.map(p => ({ id: p.id, label: `[PRD] ${p.kode} — ${p.nama}` }));
                },

                resetItem() {
                    this.itemId = '';
                },
            }
        }
    </script>
    @endpush

</x-app-layout>