<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Kartu Stok</h2>
    </x-slot>

    @php
        // Siapkan data untuk searchable dropdown
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

        // Ambil request query untuk pre-select
        $reqItemType = request('item_type');
        $reqItemId   = request('item_id');
        $reqLocation = request('location_id');
        $reqDateFrom = request('date_from');
        $reqDateTo   = request('date_to');
        $selectedItemKey = ($reqItemType && $reqItemId) ? $reqItemType . '_' . $reqItemId : '';
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4C"
            title="Kartu Stok"
            subtitle="Riwayat pergerakan stok per item & lokasi"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-500/10 border border-blue-500/30 text-blue-400 px-4 py-3 rounded-lg text-sm">
                {{ session('info') }}
            </div>
        @endif

        {{-- FILTER --}}
        <x-atelier.card title="Filter" :brackets="true">
            <form method="GET" action="{{ route('warehouse.stock-cards.index') }}" x-data="stockCardFilter()">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Hidden --}}
                    <input type="hidden" name="item_type" x-model="selectedItemType">
                    <input type="hidden" name="item_id" x-model="selectedItemId">

                    {{-- ITEM --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Item <span class="text-red-400">*</span>
                        </label>
                        <div x-data="searchable({
                            items: @js($itemOptions),
                            selected: '{{ $selectedItemKey }}',
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

                    {{-- LOKASI --}}
                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Lokasi <span class="text-red-400">*</span>
                        </label>
                        <div x-data="searchable({
                            items: @js($locationOptions),
                            selected: '{{ $reqLocation }}',
                            name: 'location_id'
                        })" class="relative">
                            <input type="hidden" name="location_id" :value="selected">
                            <div x-ref="trigger" @click="toggle()"
                                 class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                 :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Lokasi --</span>
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

                    {{-- DATE RANGE --}}
                    <x-atelier.input name="date_from" label="Dari Tanggal" type="date" :value="$reqDateFrom" />
                    <x-atelier.input name="date_to" label="Sampai Tanggal" type="date" :value="$reqDateTo" />
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    @if($hasFilter)
                        <x-atelier.button :href="route('warehouse.stock-cards.index')" variant="ghost">Reset</x-atelier.button>
                    @endif
                    <x-atelier.button type="submit" variant="primary">Tampilkan</x-atelier.button>
                </div>
            </form>
        </x-atelier.card>

        {{-- HASIL --}}
        @if($hasFilter)
            {{-- SUMMARY --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                    <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">Saldo Awal</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_angka($openingBalance, 2) }}</p>
                </div>
                <div class="bg-navy-900/50 border border-green-500/30 rounded-xl p-5">
                    <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Total Masuk</p>
                    <p class="font-serif text-2xl font-bold text-green-400">+{{ format_angka($summary['total_in'], 2) }}</p>
                </div>
                <div class="bg-navy-900/50 border border-red-500/30 rounded-xl p-5">
                    <p class="text-[10px] font-mono text-red-400 uppercase tracking-widest mb-1">Total Keluar</p>
                    <p class="font-serif text-2xl font-bold text-red-400">-{{ format_angka($summary['total_out'], 2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Saldo Akhir</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_angka($summary['closing_balance'], 2) }}</p>
                </div>
            </div>

            {{-- TABEL --}}
            <x-atelier.card :brackets="true" padding="p-0">
                @if($movements->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-navy-950/50 border-b border-navy-800">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tanggal</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">No. Movement</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tipe</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Referensi</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Masuk</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Keluar</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-navy-800">
                                {{-- Baris saldo awal --}}
                                <tr class="bg-navy-800/30">
                                    <td class="px-5 py-3 text-sm text-navy-400" colspan="4">
                                        <span class="italic">Saldo Awal per {{ $reqDateFrom ? format_tanggal($reqDateFrom) : 'awal' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-navy-400">-</td>
                                    <td class="px-5 py-3 text-right font-mono text-navy-400">-</td>
                                    <td class="px-5 py-3 text-right font-mono text-white font-semibold">{{ format_angka($openingBalance, 2) }}</td>
                                </tr>

                                @foreach($movements as $mov)
                                    <tr class="hover:bg-navy-800/30 transition">
                                        <td class="px-5 py-3 text-sm text-navy-300">{{ format_tanggal($mov->date) }}</td>
                                        <td class="px-5 py-3 text-sm font-mono text-gold-500">{{ $mov->movement_number }}</td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $mov->type_color }}">
                                                {{ $mov->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-sm text-navy-400">
                                            {{ $mov->reference_number ?? $mov->notes ?? '-' }}
                                        </td>
                                        <td class="px-5 py-3 text-right font-mono text-sm text-green-400">
                                            {{ $mov->qty > 0 ? format_angka($mov->qty, 2) : '-' }}
                                        </td>
                                        <td class="px-5 py-3 text-right font-mono text-sm text-red-400">
                                            {{ $mov->qty < 0 ? format_angka(abs($mov->qty), 2) : '-' }}
                                        </td>
                                        <td class="px-5 py-3 text-right font-mono text-sm text-white font-semibold">
                                            {{ format_angka($mov->qty_after, 2) }}
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Baris saldo akhir --}}
                                <tr class="bg-gold-500/10 border-t-2 border-gold-500/30">
                                    <td class="px-5 py-3 text-sm text-gold-500 font-semibold" colspan="4">
                                        Saldo Akhir
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-green-400 font-semibold">
                                        {{ format_angka($summary['total_in'], 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-red-400 font-semibold">
                                        {{ format_angka($summary['total_out'], 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-white font-bold text-lg">
                                        {{ format_angka($summary['closing_balance'], 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-atelier.empty-state
                        title="Tidak ada pergerakan"
                        subtitle="Tidak ada pergerakan stok untuk item ini di periode yang dipilih."
                    />
                @endif
            </x-atelier.card>
        @else
            {{-- BELUM FILTER --}}
            <x-atelier.card :brackets="true">
                <div class="p-12 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-navy-800/50 flex items-center justify-center border border-navy-700">
                        <svg class="w-10 h-10 text-navy-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <p class="font-serif text-lg text-white mb-2">Pilih Item & Lokasi</p>
                    <p class="text-sm text-navy-400">Pilih item dan lokasi di filter di atas untuk menampilkan kartu stok.</p>
                </div>
            </x-atelier.card>
        @endif

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

        function stockCardFilter() {
            return {
                selectedItemType: '{{ $reqItemType }}',
                selectedItemId: '{{ $reqItemId }}',
            }
        }
    </script>
    @endpush

</x-app-layout>