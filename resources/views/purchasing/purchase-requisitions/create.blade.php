<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Buat Purchase Requisition</h2>
    </x-slot>

    @php
        $materialOptions = $materials->map(fn($m) => [
            'id' => (string) $m->id,
            'label' => $m->kode . ' — ' . $m->nama,
        ])->values()->all();
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M4B" title="Buat PR" subtitle="Isi permintaan pembelian" />

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

        <form method="POST" action="{{ route('purchasing.purchase-requisitions.store') }}" x-data="prForm()">
            @csrf

            <x-atelier.card title="Informasi PR" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="pr_number" label="No. PR" :value="$generatedNumber" required />
                    <x-atelier.input name="department" label="Department" placeholder="Produksi, QC, dll" />
                    <x-atelier.input name="date" label="Tanggal PR" type="date" :value="old('date', date('Y-m-d'))" required />
                    <x-atelier.input name="needed_date" label="Dibutuhkan Tanggal" type="date" :value="old('needed_date')" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card title="Item Permintaan" :brackets="true">
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
                                <div class="md:col-span-7">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Material</label>
                                    <div x-data="searchable({
                                        items: @js($materialOptions),
                                        selected: '',
                                        name: `items[${index}][material_id]`
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
                                                    <template x-if="filteredItems.length === 0">
                                                        <div class="px-3 py-4 text-center text-sm text-navy-500">Tidak ada hasil</div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Qty</label>
                                    <input type="number" :name="`items[${index}][qty]`" x-model.number="item.qty"
                                           step="0.01" min="0.01" required
                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-semibold text-navy-400 uppercase tracking-wider mb-1">Catatan</label>
                                    <input type="text" :name="`items[${index}][notes]`" x-model="item.notes"
                                           placeholder="Opsional"
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
                <x-atelier.button :href="route('purchasing.purchase-requisitions.index')" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary">Simpan PR</x-atelier.button>
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
                    this.dropdownStyle = `position: fixed; top: ${top}px; left: ${rect.left}px; width: ${rect.width}px; z-index: 9999;`;
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

        function prForm() {
            return {
                items: [
                    { qty: 1, notes: '' }
                ],
                addItem() {
                    this.items.push({ qty: 1, notes: '' });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
            }
        }
    </script>
    @endpush

</x-app-layout>