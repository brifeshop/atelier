<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Transfer Stok</h2>
    </x-slot>

    @php
        $itemOptions = collect()
            ->merge($materials->map(fn($m) => [
                'id' => 'material_' . $m->id,
                'label' => '[MAT] ' . $m->kode . ' — ' . $m->nama,
                'data' => ['type' => 'material', 'id' => (string) $m->id],
            ]))
            ->merge($products->map(fn($p) => [
                'id' => 'product_' . $p->id,
                'label' => '[PRD] ' . $p->kode . ' — ' . $p->nama,
                'data' => ['type' => 'product', 'id' => (string) $p->id],
            ]))
            ->values()
            ->all();

        $locationOptions = $locations->map(fn($loc) => [
            'id' => (string) $loc->id,
            'label' => $loc->kode . ' — ' . $loc->nama . ' (' . ($loc->warehouse->nama ?? '-') . ')',
        ])->values()->all();
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M4A" title="Transfer Stok" subtitle="Pindahkan stok antar lokasi" />

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

        <form method="POST" action="{{ route('warehouse.stock-movements.transfer.store') }}" x-data="transferForm()">
            @csrf

            <x-atelier.card title="Informasi Transfer" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <input type="hidden" name="item_type" x-model="selectedItemType" required>
                    <input type="hidden" name="item_id" x-model="selectedItemId" required>

                    <x-atelier.input name="date" label="Tanggal Transfer" type="date" :value="old('date', date('Y-m-d'))" required />

                    {{-- ITEM --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Item <span class="text-red-400">*</span>
                        </label>
                        <div x-data="searchable({
                            items: @js($itemOptions),
                            selected: '',
                            name: 'item_selector',
                            onChange: (id, data) => {
                                selectedItemType = data.type || '';
                                selectedItemId = data.id || '';
                            }
                        })" class="relative">
                            <div x-ref="trigger" @click="toggle()"
                                 class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                 :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Item --</span>
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
                                               placeholder="Cari item..."
                                               class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-0 focus:outline-none">
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

                    {{-- DARI LOKASI --}}
                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Dari Lokasi <span class="text-red-400">*</span>
                        </label>
                        <div x-data="searchable({
                            items: @js($locationOptions),
                            selected: '{{ old('from_location_id') }}',
                            name: 'from_location_id'
                        })" class="relative">
                            <input type="hidden" name="from_location_id" :value="selected" required>

                            <div x-ref="trigger" @click="toggle()"
                                 class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                 :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Lokasi Asal --</span>
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
                                               placeholder="Cari lokasi..."
                                               class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-0 focus:outline-none">
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

                    {{-- KE LOKASI --}}
                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Ke Lokasi <span class="text-red-400">*</span>
                        </label>
                        <div x-data="searchable({
                            items: @js($locationOptions),
                            selected: '{{ old('to_location_id') }}',
                            name: 'to_location_id'
                        })" class="relative">
                            <input type="hidden" name="to_location_id" :value="selected" required>

                            <div x-ref="trigger" @click="toggle()"
                                 class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                 :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Lokasi Tujuan --</span>
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
                                               placeholder="Cari lokasi..."
                                               class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-0 focus:outline-none">
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

                    <x-atelier.input name="qty" label="Qty" type="number" step="0.01" :value="old('qty')" required />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('warehouse.stock-movements.index')" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary">Proses Transfer</x-atelier.button>
            </div>

        </form>

    </div>

    @push('scripts')
    <script>
        function searchable({ items, selected, name, onChange = null }) {
            return {
                items: items,
                selected: selected || '',
                search: '',
                open: false,
                highlighted: 0,
                name: name,
                onChange: onChange,
                dropdownStyle: '',

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
                        this.search = '';
                        this.highlighted = 0;
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

                    this.dropdownStyle = `
                        position: fixed;
                        top: ${top}px;
                        left: ${rect.left}px;
                        width: ${rect.width}px;
                        z-index: 9999;
                    `;
                },

                select(opt) {
                    this.selected = opt.id;
                    this.open = false;
                    if (this.onChange) this.onChange(opt.id, opt.data || {});
                },

                highlightNext() {
                    if (this.highlighted < this.filteredItems.length - 1) this.highlighted++;
                    this.scrollToHighlighted();
                },
                highlightPrev() {
                    if (this.highlighted > 0) this.highlighted--;
                    this.scrollToHighlighted();
                },
                scrollToHighlighted() {
                    this.$nextTick(() => {
                        const el = document.querySelector(`[data-option-index="${this.highlighted}"]`);
                        if (el) el.scrollIntoView({ block: 'nearest' });
                    });
                },
                selectHighlighted() {
                    if (this.filteredItems[this.highlighted]) {
                        this.select(this.filteredItems[this.highlighted]);
                    }
                },
            }
        }

        function transferForm() {
            return {
                selectedItemType: '{{ old('item_type', 'material') }}',
                selectedItemId: '{{ old('item_id') }}',
            }
        }
    </script>
    @endpush

</x-app-layout>