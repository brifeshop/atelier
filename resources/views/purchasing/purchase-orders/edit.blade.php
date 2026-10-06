<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Purchase Order</h2>
    </x-slot>

    @php
        $supplierOptions = $suppliers->map(fn($s) => [
            'id' => (string) $s->id,
            'label' => $s->kode . ' — ' . $s->nama,
        ])->values()->all();

        $materialOptions = $materials->map(fn($m) => [
            'id' => (string) $m->id,
            'label' => $m->kode . ' — ' . $m->nama,
            'data' => ['price' => (float) $m->price],
        ])->values()->all();

        $initialItems = $item->items->map(fn($i) => [
            'material_id' => (string) $i->material_id,
            'qty' => (float) $i->qty,
            'unit_price' => (float) $i->unit_price,
            'discount_percent' => (float) $i->discount_percent,
            'subtotal' => (float) $i->subtotal,
            'notes' => $i->notes,
        ])->values()->all();
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M4B" title="Edit PO" subtitle="{{ $item->po_number }}" />

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('purchasing.purchase-orders.update', $item) }}" x-data="poFormEdit()">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi PO" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="po_number" label="No. PO" :value="$item->po_number" required />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Supplier <span class="text-red-400">*</span>
                        </label>
                        <div x-data="searchable({
                            items: @js($supplierOptions),
                            selected: '{{ old('supplier_id', $item->supplier_id) }}',
                            name: 'supplier_id'
                        })">
                            <input type="hidden" name="supplier_id" :value="selected" required>
                            <div x-ref="trigger" @click="toggle()"
                                 class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                 :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Supplier --</span>
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
                                               placeholder="Cari supplier..."
                                               class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded text-sm text-white focus:border-gold-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-64 overflow-y-auto">
                                        <template x-for="(opt, idx) in filteredItems" :key="opt.id">
                                            <div @click="select(opt)" @mouseenter="highlighted = idx"
                                                 :data-option-index="idx"
                                                 class="px-3 py-2 text-sm cursor-pointer"
                                                 :class="highlighted === idx ? 'bg-gold-500 text-navy-900' : 'text-white hover:bg-navy-700'"
                                                 x-text="opt.label"></div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <x-atelier.input name="date" label="Tanggal PO" type="date" :value="$item->date->format('Y-m-d')" required />
                    <x-atelier.input name="delivery_date" label="Target Kirim" type="date" :value="$item->delivery_date?->format('Y-m-d')" />
                    <x-atelier.input name="tax_amount" label="Pajak (Rp)" type="number" step="0.01" :value="$item->tax_amount" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card title="Item Pesanan" :brackets="true">
                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-mono text-gold-500 uppercase tracking-widest">
                                    Item #<span x-text="index + 1"></span>
                                </p>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                        class="text-red-400 hover:text-red-300 text-xs font-medium">
                                    Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-4">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Material</label>
                                    <div x-data="searchable({
                                        items: @js($materialOptions),
                                        selected: String(item.material_id || ''),
                                        name: `items[${index}][material_id]`,
                                        onChange: (id, data) => setMaterial(index, id, data)
                                    })">
                                        <input type="hidden" :name="`items[${index}][material_id]`" :value="selected" required>
                                        <div x-ref="trigger" @click="toggle()"
                                             class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                             :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                            <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                            <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Material --</span>
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
                                                           placeholder="Cari material..."
                                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded text-sm text-white focus:border-gold-500 focus:outline-none">
                                                </div>
                                                <div class="max-h-64 overflow-y-auto">
                                                    <template x-for="(opt, idx) in filteredItems" :key="opt.id">
                                                        <div @click="select(opt)" @mouseenter="highlighted = idx"
                                                             :data-option-index="idx"
                                                             class="px-3 py-2 text-sm cursor-pointer"
                                                             :class="highlighted === idx ? 'bg-gold-500 text-navy-900' : 'text-white hover:bg-navy-700'"
                                                             x-text="opt.label"></div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Qty</label>
                                    <input type="number" :name="`items[${index}][qty]`" x-model.number="item.qty" @input="calculateItem(index)"
                                           step="0.01" min="0.01" required
                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Harga</label>
                                    <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" @input="calculateItem(index)"
                                           step="0.01" min="0" required
                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Disk %</label>
                                    <input type="number" :name="`items[${index}][discount_percent]`" x-model.number="item.discount_percent" @input="calculateItem(index)"
                                           step="0.01" min="0" max="100"
                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Subtotal</label>
                                    <div class="px-3 py-2 bg-navy-900 border border-navy-700 rounded-lg text-sm text-gold-500 font-mono">
                                        Rp <span x-text="formatNumber(item.subtotal)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addItem()"
                            class="w-full py-3 border-2 border-dashed border-navy-700 rounded-lg text-sm text-navy-400 hover:text-gold-500 hover:border-gold-500/50 transition">
                        + Tambah Item
                    </button>
                </div>
            </x-atelier.card>

            <x-atelier.card title="Ringkasan" :brackets="true">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-navy-400">Subtotal</span>
                        <span class="text-white font-mono">Rp <span x-text="formatNumber(grandSubtotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-navy-400">Total Diskon</span>
                        <span class="text-red-400 font-mono">- Rp <span x-text="formatNumber(grandDiscount)"></span></span>
                    </div>
                    <div class="flex justify-between text-lg pt-3 border-t border-navy-800">
                        <span class="text-white font-semibold">Total</span>
                        <span class="text-gold-500 font-mono font-bold">Rp <span x-text="formatNumber(grandTotal)"></span></span>
                    </div>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('purchasing.purchase-orders.show', $item)" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary">Simpan Perubahan</x-atelier.button>
            </div>

        </form>

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

        function poFormEdit() {
            return {
                items: @js($initialItems),
                get grandSubtotal() {
                    return this.items.reduce((sum, i) => sum + (i.qty * i.unit_price || 0), 0);
                },
                get grandDiscount() {
                    return this.items.reduce((sum, i) => sum + (i.subtotal ? (i.qty * i.unit_price - i.subtotal) : 0), 0);
                },
                get grandTotal() {
                    return this.items.reduce((sum, i) => sum + (i.subtotal || 0), 0);
                },
                addItem() {
                    this.items.push({ material_id: '', qty: 1, unit_price: 0, discount_percent: 0, subtotal: 0, notes: '' });
                },
                removeItem(index) { this.items.splice(index, 1); },
                setMaterial(index, materialId, data) {
                    this.items[index].material_id = materialId;
                    if (data && data.price) this.items[index].unit_price = data.price;
                    this.calculateItem(index);
                },
                calculateItem(index) {
                    const item = this.items[index];
                    const gross = (item.qty || 0) * (item.unit_price || 0);
                    const discount = gross * (item.discount_percent || 0) / 100;
                    item.subtotal = gross - discount;
                },
                formatNumber(value) {
                    return new Intl.NumberFormat('id-ID').format(Math.round(value || 0));
                },
            }
        }
    </script>
    @endpush

</x-app-layout>