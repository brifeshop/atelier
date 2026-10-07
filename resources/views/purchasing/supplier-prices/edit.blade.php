<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Harga Supplier</h2>
    </x-slot>

    @php
        $supplierOptions = $suppliers->map(fn($s) => [
            'id' => (string) $s->id,
            'label' => $s->kode . ' — ' . $s->nama,
        ])->values()->all();

        $materialOptions = $materials->map(fn($m) => [
            'id' => (string) $m->id,
            'label' => $m->kode . ' — ' . $m->nama,
        ])->values()->all();
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M4D" title="Edit Harga" subtitle="{{ $item->supplier->nama ?? '-' }} — {{ $item->material->nama ?? '-' }}" />

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
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

        <form method="POST" action="{{ route('purchasing.supplier-prices.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Harga" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

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

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Material <span class="text-red-400">*</span>
                        </label>
                        <div x-data="searchable({
                            items: @js($materialOptions),
                            selected: '{{ old('material_id', $item->material_id) }}',
                            name: 'material_id'
                        })">
                            <input type="hidden" name="material_id" :value="selected" required>
                            <div x-ref="trigger" @click="toggle()"
                                 class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                 :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Material --</span>
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

                    <x-atelier.input name="price" label="Harga per Unit (Rp)" type="number" step="0.01" :value="old('price', $item->price)" required />
                    <x-atelier.input name="min_order_qty" label="Minimum Order Qty" type="number" step="0.01" :value="old('min_order_qty', $item->min_order_qty)" />
                    <x-atelier.input name="lead_time_days" label="Lead Time (hari)" type="number" :value="old('lead_time_days', $item->lead_time_days)" />
                    <x-atelier.input name="valid_from" label="Berlaku Dari" type="date" :value="old('valid_from', $item->valid_from?->format('Y-m-d'))" />
                    <x-atelier.input name="valid_to" label="Berlaku Sampai" type="date" :value="old('valid_to', $item->valid_to?->format('Y-m-d'))" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                        <label for="is_active" class="text-sm text-navy-300">Harga Aktif</label>
                    </div>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('purchasing.supplier-prices.index')" variant="ghost">Batal</x-atelier.button>
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
    </script>
    @endpush

</x-app-layout>