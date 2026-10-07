<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Bandingkan Harga</h2>
    </x-slot>

    @php
        $materialOptions = $materials->map(fn($m) => [
            'id' => (string) $m->id,
            'label' => $m->kode . ' — ' . $m->nama,
        ])->values()->all();
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4D"
            title="Bandingkan Harga"
            subtitle="Pilih material untuk lihat harga di semua supplier"
        />

        <x-atelier.card :brackets="true">
            <form method="GET" action="{{ route('purchasing.supplier-prices.compare') }}">
                <div>
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                        Pilih Material
                    </label>
                    <div x-data="searchable({
                        items: @js($materialOptions),
                        selected: '{{ request('material_id') }}',
                        name: 'material_id'
                    })" class="relative">
                        <input type="hidden" name="material_id" :value="selected">
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
                <div class="mt-4">
                    <x-atelier.button type="submit" variant="primary">Bandingkan</x-atelier.button>
                </div>
            </form>
        </x-atelier.card>

        @if($material)
            <x-atelier.card title="Hasil Perbandingan: {{ $material->nama }}" :brackets="true">
                @if($prices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-navy-950/50 border-b border-navy-800">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Peringkat</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Supplier</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Harga</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Min Order</th>
                                    <th class="px-5 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Lead Time</th>
                                    <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-navy-800">
                                @foreach($prices as $index => $price)
                                    <tr class="{{ $index === 0 ? 'bg-green-500/5' : '' }}">
                                        <td class="px-5 py-3">
                                            @if($index === 0)
                                                <span class="inline-flex items-center gap-1.5 text-xs text-green-400 font-semibold">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                    Termurah
                                                </span>
                                            @else
                                                <span class="text-xs text-navy-500 font-mono">#{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-sm text-white font-medium">
                                            {{ $price->supplier->nama ?? '-' }}
                                            @if($price->supplier)
                                                <br><span class="text-xs text-navy-500 font-mono">{{ $price->supplier->kode }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-right font-mono {{ $index === 0 ? 'text-green-400 font-bold' : 'text-white' }}">
                                            {{ format_rupiah($price->price) }}
                                        </td>
                                        <td class="px-5 py-3 text-right font-mono text-navy-300 text-sm">
                                            {{ $price->min_order_qty ? format_angka($price->min_order_qty, 2) : '-' }}
                                        </td>
                                        <td class="px-5 py-3 text-center text-sm text-navy-300">
                                            {{ $price->lead_time_days ? $price->lead_time_days . ' hari' : '-' }}
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            @if($index === 0)
                                                <span class="text-xs text-green-400 font-semibold">Rekomendasi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center border border-dashed border-navy-700 rounded-lg">
                        <p class="text-sm text-navy-400">Belum ada harga untuk material ini di supplier manapun.</p>
                        <a href="{{ route('purchasing.supplier-prices.create', ['material_id' => $material->id]) }}"
                           class="inline-block mt-4 px-4 py-2 bg-gold-500 text-navy-900 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-gold-400 transition">
                            + Tambah Harga
                        </a>
                    </div>
                @endif
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
    </script>
    @endpush

</x-app-layout>