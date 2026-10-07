<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Buat Purchase Return</h2>
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

        $locationOptions = $locations->map(fn($l) => [
            'id' => (string) $l->id,
            'label' => $l->kode . ' — ' . $l->nama . ' (' . ($l->warehouse->nama ?? '-') . ')',
        ])->values()->all();

        $initialItems = [[
            'material_id' => '',
            'location_id' => '',
            'qty' => 1,
            'unit_price' => 0,
            'reason' => '',
            'notes' => '',
        ]];
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4D"
            title="Buat Return"
            :subtitle="$gr ? 'Dari GR: ' . $gr->gr_number : 'Isi data retur barang'"
        />

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

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('purchasing.purchase-returns.store') }}" x-data="returnForm()">
            @csrf

            @if($gr)
                <input type="hidden" name="goods_receipt_id" value="{{ $gr->id }}">
                <input type="hidden" name="purchase_order_id" value="{{ $gr->purchase_order_id }}">
            @endif

            <x-atelier.card title="Informasi Return" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="return_number" label="No. Return" :value="$generatedNumber" required />
                    <x-atelier.input name="date" label="Tanggal Return" type="date" :value="old('date', date('Y-m-d'))" required />

                    {{-- Supplier --}}
                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Supplier <span class="text-red-400">*</span>
                        </label>
                        <div x-data="searchable({
                            items: @js($supplierOptions),
                            selected: '{{ old('supplier_id', $gr->supplier_id ?? '') }}',
                            name: 'supplier_id'
                        })">
                            <input type="hidden" name="supplier_id" :value="selected" required>
                            <div x-ref="trigger" @click="toggle()"
                                 class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                 :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Supplier --</span>
                                <svg class="w-4 h-4 text-navy-500 flex-shrink-0" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Alasan Return</label>
                        <textarea name="reason" rows="2" placeholder="Contoh: barang rusak, tidak sesuai pesanan" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('reason') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card title="Item Return" :brackets="true">
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
                                {{-- Material --}}
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
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Lokasi --}}
                                <div class="md:col-span-4">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Lokasi</label>
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
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Qty</label>
                                    <input type="number" :name="`items[${index}][qty]`" x-model.number="item.qty"
                                           step="0.01" min="0.01" required
                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Harga</label>
                                    <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price"
                                           step="0.01" min="0" required
                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Alasan Item</label>
                                    <input type="text" :name="`items[${index}][reason]`" x-model="item.reason"
                                           placeholder="Contoh: rusak, tidak sesuai"
                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
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

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('purchasing.purchase-returns.index')" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary">Simpan Return</x-atelier.button>
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

        function returnForm() {
            return {
                items: @js($initialItems),
                addItem() {
                    this.items.push({
                        material_id: '',
                        location_id: '',
                        qty: 1,
                        unit_price: 0,
                        reason: '',
                        notes: ''
                    });
                },
                removeItem(index) { this.items.splice(index, 1); },
                setMaterial(index, materialId, data) {
                    this.items[index].material_id = materialId;
                    if (data && data.price) this.items[index].unit_price = data.price;
                },
            }
        }
    </script>
    @endpush

</x-app-layout>