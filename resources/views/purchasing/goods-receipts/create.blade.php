<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Buat Goods Receipt</h2>
    </x-slot>

    @php
        $locationOptions = $locations->map(fn($l) => [
            'id' => (string) $l->id,
            'label' => $l->kode . ' — ' . $l->nama . ' (' . ($l->warehouse->nama ?? '-') . ')',
        ])->values()->all();

        $initialItems = [];
        if ($po) {
            foreach ($po->items as $poItem) {
                $remaining = $poItem->qty - $poItem->received_qty;
                if ($remaining <= 0) continue;

                $initialItems[] = [
                    'purchase_order_item_id' => (string) $poItem->id,
                    'material_id' => (string) $poItem->material_id,
                    'material_nama' => $poItem->material->nama ?? '-',
                    'material_kode' => $poItem->material->kode ?? '-',
                    'unit' => $poItem->material->unit ?? '',
                    'qty_ordered' => (float) $remaining,
                    'qty_received' => (float) $remaining,
                    'qty_rejected' => 0,
                    'unit_price' => (float) $poItem->unit_price,
                    'location_id' => '',
                    'notes' => '',
                ];
            }
        }
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4B"
            title="Buat GR"
            :subtitle="$po ? 'Dari PO: ' . $po->po_number : 'Isi data penerimaan barang'"
        />

        @if(!$po)
            <div class="bg-gold-500/10 border border-gold-500/30 rounded-lg px-4 py-4">
                <p class="text-sm text-gold-500 font-semibold mb-1">⚠️ Belum pilih PO</p>
                <p class="text-xs text-navy-300">
                    Goods Receipt harus dibuat dari Purchase Order yang sudah dikirim.
                    Buka <a href="{{ route('purchasing.purchase-orders.index') }}" class="text-gold-500 underline">Purchase Order</a>,
                    pilih PO, lalu klik "Buat Goods Receipt".
                </p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                <p class="font-semibold mb-1">Ada kesalahan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($po)
            <form method="POST" action="{{ route('purchasing.goods-receipts.store') }}" x-data="grForm()">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">

                <x-atelier.card title="Informasi GR" :brackets="true">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-atelier.input name="gr_number" label="No. GR" :value="$generatedNumber" required />
                        <x-atelier.input name="date" label="Tanggal Terima" type="date" :value="old('date', date('Y-m-d'))" required />

                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">PO</label>
                            <div class="px-3 py-2.5 bg-navy-900 border border-navy-700 rounded-lg text-sm">
                                <span class="text-gold-500 font-mono">{{ $po->po_number }}</span>
                                <span class="text-navy-300"> — {{ $po->supplier->nama ?? '-' }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Supplier</label>
                            <div class="px-3 py-2.5 bg-navy-900 border border-navy-700 rounded-lg text-sm text-white">
                                {{ $po->supplier->nama ?? '-' }}
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </x-atelier.card>

                <x-atelier.card title="Item Penerimaan" subtitle="Pilih lokasi penyimpanan & isi qty diterima" :brackets="true">
                    @if(count($initialItems) > 0)
                        <div class="space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-4">
                                    <input type="hidden" :name="`items[${index}][purchase_order_item_id]`" :value="item.purchase_order_item_id">

                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <p class="text-sm text-white font-medium" x-text="item.material_nama"></p>
                                            <p class="text-xs font-mono text-navy-500" x-text="item.material_kode"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-navy-400">Sisa PO</p>
                                            <p class="text-sm font-mono text-gold-500">
                                                <span x-text="formatNumber(item.qty_ordered)"></span>
                                                <span x-text="item.unit" class="text-xs text-navy-500"></span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                        <div class="md:col-span-5">
                                            <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Lokasi Penyimpanan *</label>
                                            <div x-data="searchable({
                                                items: @js($locationOptions),
                                                selected: String(item.location_id || ''),
                                                name: `items[${index}][location_id]`
                                            })">
                                                <input type="hidden" :name="`items[${index}][location_id]`" :value="selected" required>
                                                <div x-ref="trigger" @click="toggle()"
                                                     class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                                     :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                                    <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                                    <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Lokasi --</span>
                                                    <svg class="w-4 h-4 text-navy-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </div>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-cloak :style="dropdownStyle"
                                                         class="bg-navy-800 border border-navy-700 rounded-lg shadow-xl overflow-hidden"
                                                         @click.away="open = false">
                                                        <div class="p-2 border-b border-navy-700">
                                                            <input type="text" x-model="search" :data-search-input="name"
                                                                   @keydown.arrow-down.prevent="highlightNext()"
                                                                   @keydown.arrow-up.prevent="highlightPrev()"
                                                                   @keydown.enter.prevent="selectHighlighted()"
                                                                   @keydown.escape="open = false"
                                                                   placeholder="Cari lokasi..."
                                                                   class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:outline-none">
                                                        </div>
                                                        <div class="max-h-64 overflow-y-auto">
                                                            <template x-for="(opt, idx) in filteredItems" :key="opt.id">
                                                                <div @click="select(opt)" @mouseenter="highlighted = idx"
                                                                     :data-option-index="idx"
                                                                     class="px-3 py-2 text-sm cursor-pointer"
                                                                     :class="highlighted === idx ? 'bg-gold-500 text-navy-900' : 'text-white hover:bg-navy-700'"
                                                                     x-text="opt.label"></div>
                                                            </template>
                                                            <template x-if="filteredItems.length === 0">
                                                                <div class="px-3 py-4 text-center text-sm text-navy-500">Tidak ada hasil</div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Qty Diterima</label>
                                            <input type="number" :name="`items[${index}][qty_received]`" x-model.number="item.qty_received" @input="calculateItem(index)"
                                                   step="0.01" min="0" required
                                                   class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Qty Reject</label>
                                            <input type="number" :name="`items[${index}][qty_rejected]`" x-model.number="item.qty_rejected" @input="calculateItem(index)"
                                                   step="0.01" min="0"
                                                   class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                        </div>

                                        <div class="md:col-span-3">
                                            <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Total Nilai</label>
                                            <div class="px-3 py-2 bg-navy-900 border border-navy-700 rounded-lg text-sm text-gold-500 font-mono">
                                                Rp <span x-text="formatNumber(item.qty_received * item.unit_price)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @else
                        <div class="p-8 text-center border border-dashed border-navy-700 rounded-lg">
                            <p class="text-sm text-navy-400">Tidak ada item yang perlu diterima. Semua item PO sudah diterima.</p>
                        </div>
                    @endif
                </x-atelier.card>

                <div class="flex items-center justify-end gap-3">
                    <x-atelier.button :href="route('purchasing.goods-receipts.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan GR</x-atelier.button>
                </div>
            </form>
        @else
            <x-atelier.card :brackets="true">
                <div class="p-8 text-center">
                    <p class="text-sm text-navy-400 mb-4">Pilih PO terlebih dahulu untuk membuat GR.</p>
                    <x-atelier.button :href="route('purchasing.purchase-orders.index')" variant="primary">
                        Lihat Daftar PO
                    </x-atelier.button>
                </div>
            </x-atelier.card>
        @endif

    </div>

    @push('scripts')
    <script>
        function searchable({ items, selected, name, onChange = null }) {
            return {
                items: items, selected: selected || '', search: '', open: false, highlighted: 0,
                name: name, onChange: onChange, dropdownStyle: '',
                get filteredItems() {
                    if (!this.search) return this.items;
                    const q = this.search.toLowerCase();
                    return this.items.filter(i => i.label.toLowerCase().includes(q));
                },
                get selectedLabel() {
                    const item = this.items.find(i => i.id === this.selected);
                    return item ? item.label : '';
                },
                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.search = ''; this.highlighted = 0;
                        this.$nextTick(() => {
                            this.updatePosition();
                            setTimeout(() => {
                                const input = document.querySelector(`[data-search-input="${this.name}"]`);
                                if (input) input.focus();
                            }, 10);
                        });
                    }
                },
                updatePosition() {
                    const trigger = this.$refs.trigger;
                    if (!trigger) return;
                    const rect = trigger.getBoundingClientRect();
                    const dropdownHeight = 320;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;
                    let top, maxHeight;
                    if (spaceBelow >= dropdownHeight || spaceBelow >= spaceAbove) {
                        top = rect.bottom + 4;
                        maxHeight = Math.min(dropdownHeight, spaceBelow - 20);
                    } else {
                        maxHeight = Math.min(dropdownHeight, spaceAbove - 20);
                        top = rect.top - maxHeight - 4;
                    }
                    this.dropdownStyle = `position: fixed; top: ${top}px; left: ${rect.left}px; width: ${rect.width}px; z-index: 9999;`;
                },
                select(opt) {
                    this.selected = opt.id;
                    this.open = false;
                    if (this.onChange) this.onChange(opt.id, opt.data || {});
                },
                highlightNext() { if (this.highlighted < this.filteredItems.length - 1) this.highlighted++; this.scrollToHighlighted(); },
                highlightPrev() { if (this.highlighted > 0) this.highlighted--; this.scrollToHighlighted(); },
                scrollToHighlighted() {
                    this.$nextTick(() => {
                        const el = document.querySelector(`[data-option-index="${this.highlighted}"]`);
                        if (el) el.scrollIntoView({ block: 'nearest' });
                    });
                },
                selectHighlighted() {
                    if (this.filteredItems[this.highlighted]) this.select(this.filteredItems[this.highlighted]);
                },
            }
        }

        function grForm() {
            return {
                items: @js($initialItems),
                calculateItem(index) {
                    // bisa ditambahkan validasi kalau perlu
                },
                formatNumber(value) {
                    return new Intl.NumberFormat('id-ID').format(Math.round(value || 0));
                },
            }
        }
    </script>
    @endpush

</x-app-layout>