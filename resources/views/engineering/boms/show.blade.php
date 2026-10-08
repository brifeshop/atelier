<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail BOM</h2>
    </x-slot>

    @php
        // Data untuk searchable dropdown item
        $itemOptions = collect()
            ->merge($materials->map(fn($m) => [
                'id' => 'material_' . $m->id,
                'label' => '[MAT] ' . ($m->kode_bahan ?? $m->kode) . ' — ' . $m->nama,
                'data' => [
                    'type' => 'material',
                    'id' => (string) $m->id,
                    'nama' => $m->nama,
                    'kode' => $m->kode,
                    'kode_bahan' => $m->kode_bahan,
                    'unit' => $m->unit,
                    'costing_method' => $m->costing_method,
                    'volume_type' => $m->volume_type,
                    'spesifikasi' => $m->spesifikasi,
                ],
            ]))
            ->merge($products->map(fn($p) => [
                'id' => 'product_' . $p->id,
                'label' => '[SUB] ' . $p->kode . ' — ' . $p->nama,
                'data' => [
                    'type' => 'product',
                    'id' => (string) $p->id,
                    'nama' => $p->nama,
                    'kode' => $p->kode,
                    'unit' => 'set',
                    'costing_method' => 'per_unit',
                ],
            ]))
            ->values()
            ->all();

        // Konfigurasi Divisi
        $divisiOptions = [
            'Set Up',
            'Kayu',
            'Offset Printing',
            'Finishing',
            'Perakitan',
            'Packing',
            'QC',
            'Lainnya',
        ];
    @endphp

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

        {{-- INFO BOM --}}
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

        {{-- ITEMS TABLE --}}
        <x-atelier.card title="Komponen BOM" :brackets="true" padding="p-0">
            @if($item->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Seq</th>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Level</th>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Item</th>
                                <th class="px-4 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tipe</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Dimensi</th>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Divisi</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Unit Cost</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total</th>
                                @if($item->canEdit())
                                    <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($item->items as $bomItem)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-4 py-3 text-sm font-mono text-navy-500">{{ $bomItem->sequence }}</td>
                                    <td class="px-4 py-3 text-xs font-mono text-navy-500">{{ $bomItem->level ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="text-white font-medium">{{ $bomItem->item->nama ?? '-' }}</span>
                                        <br>
                                        <span class="text-xs font-mono text-navy-500">
                                            {{ $bomItem->item->kode_bahan ?? $bomItem->item->kode ?? '-' }}
                                        </span>
                                        @if($bomItem->spesifikasi)
                                            <br><span class="text-xs text-navy-400 italic">{{ $bomItem->spesifikasi }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $bomItem->item_type_color }}">
                                            {{ $bomItem->item_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-white">
                                        {{ format_angka($bomItem->qty, 4) }}
                                        <span class="text-xs text-navy-500">{{ $bomItem->unit }}</span>
                                        @if($bomItem->scrap_percent > 0)
                                            <br><span class="text-[10px] text-orange-400">scrap {{ $bomItem->scrap_percent }}%</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-navy-300">
                                        {{ $bomItem->dimension_label }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-navy-400">{{ $bomItem->divisi ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-navy-300">
                                        {{ format_rupiah($bomItem->unit_cost) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-white font-semibold">
                                        {{ format_rupiah($bomItem->total_cost) }}
                                    </td>
                                    @if($item->canEdit())
                                        <td class="px-4 py-3 text-right">
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
                <form method="POST" action="{{ route('engineering.boms.items.store', $item) }}" 
                      x-data="bomItemForm()">
                    @csrf

                    {{-- Hidden inputs --}}
                    <input type="hidden" name="item_type" x-model="selectedItemType" required>
                    <input type="hidden" name="item_id" x-model="selectedItemId" required>
                    <input type="hidden" name="level" :value="selectedLevel" required>
                    <input type="hidden" name="divisi" :value="selectedDivisi">

                    <div class="space-y-5">

                        {{-- LEVEL (Tombol) --}}
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                                Level <span class="text-red-400">*</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @for($i = 1; $i <= 6; $i++)
                                    <button type="button"
                                            @click="selectedLevel = 'L.{{ $i }}'"
                                            :class="selectedLevel === 'L.{{ $i }}' 
                                                ? 'bg-gold-500 text-navy-900 border-gold-500 shadow-lg shadow-gold-500/20' 
                                                : 'bg-navy-950 text-navy-300 border-navy-700 hover:border-gold-500/50 hover:text-white'"
                                            class="px-4 py-2 rounded-lg border text-sm font-mono font-semibold transition">
                                        L.{{ $i }}
                                    </button>
                                @endfor
                            </div>
                            <p class="text-xs text-navy-500 mt-2">
                                <span x-show="selectedLevel === 'L.1'">L.1 = Komponen langsung dari produk jadi</span>
                                <span x-show="selectedLevel === 'L.2'">L.2 = Sub-komponen dari L.1</span>
                                <span x-show="selectedLevel === 'L.3'">L.3 = Sub-komponen dari L.2</span>
                                <span x-show="selectedLevel === 'L.4'">L.4 = Sub-komponen dari L.3</span>
                                <span x-show="selectedLevel === 'L.5'">L.5 = Sub-komponen dari L.4</span>
                                <span x-show="selectedLevel === 'L.6'">L.6 = Sub-komponen dari L.5</span>
                            </p>
                        </div>

                        {{-- DIVISI (Tombol) --}}
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                                Divisi Pengerjaan
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($divisiOptions as $div)
                                    <button type="button"
                                            @click="selectedDivisi = selectedDivisi === '{{ $div }}' ? '' : '{{ $div }}'"
                                            :class="selectedDivisi === '{{ $div }}' 
                                                ? 'bg-gold-500 text-navy-900 border-gold-500 shadow-lg shadow-gold-500/20' 
                                                : 'bg-navy-950 text-navy-300 border-navy-700 hover:border-gold-500/50 hover:text-white'"
                                            class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition">
                                        {{ $div }}
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-xs text-navy-500 mt-2">Klik untuk pilih, klik lagi untuk batal.</p>
                        </div>

                        {{-- ITEM SEARCHABLE + TOMBOL --}}
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                                Item <span class="text-red-400">*</span>
                            </label>
                            <div class="flex gap-2 items-stretch">
                                <div class="flex-1">
                                    <div x-data="searchable({
                                        items: @js($itemOptions),
                                        selected: '',
                                        name: 'item_selector',
                                        onChange: (id, data) => {
                                            selectedItemType = data.type || '';
                                            selectedItemId = data.id || '';
                                            onItemChange(data);
                                        }
                                    })" class="relative">
                                        <div x-ref="trigger" @click="toggle()"
                                             class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                             :class="open && 'border-gold-500 ring-2 ring-gold-500/20'">
                                            <span x-show="selectedLabel" x-text="selectedLabel" class="truncate"></span>
                                            <span x-show="!selectedLabel" class="text-navy-500">-- Pilih Material / Sub-Assembly --</span>
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
                                                           placeholder="Cari material / sub-assembly..."
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

                                {{-- Tombol + Material Baru --}}
                                <button type="button" 
                                        @click="openQuickMaterialModal = true"
                                        class="px-3 py-2.5 bg-gold-500/20 text-gold-400 border border-gold-500/30 rounded-lg text-xs font-semibold hover:bg-gold-500/30 transition whitespace-nowrap flex items-center gap-1"
                                        title="Tambah material baru tanpa keluar halaman">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Material
                                </button>

                                {{-- Tombol + Sub-Assembly (tab baru) --}}
                                <a href="{{ route('master.products.create') }}" 
                                   target="_blank"
                                   class="px-3 py-2.5 bg-navy-800 text-navy-300 border border-navy-700 rounded-lg text-xs font-semibold hover:text-gold-500 hover:border-gold-500/50 transition whitespace-nowrap flex items-center gap-1"
                                   title="Buat sub-assembly baru di tab baru">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Sub-Assembly
                                </a>
                            </div>

                            {{-- INFO ITEM TERPILIH --}}
                            <div x-show="selectedItem" x-cloak class="mt-2 p-2 bg-navy-900/50 border border-navy-800 rounded text-xs">
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-navy-400">
                                    <span>Tipe: <span class="text-navy-200" x-text="selectedItemType === 'material' ? 'Material' : 'Sub-Assembly'"></span></span>
                                    <span>Satuan beli: <span class="text-navy-200" x-text="selectedItem?.unit || '-'"></span></span>
                                    <span>Costing: <span class="text-gold-500" x-text="selectedItem?.costing_method || '-'"></span></span>
                                </div>
                                <div x-show="selectedItem?.spesifikasi" class="mt-1 pt-1 border-t border-navy-800 text-navy-400">
                                    <span class="text-[10px] uppercase tracking-wider">Spesifikasi Material:</span>
                                    <p class="text-navy-300 mt-0.5" x-text="selectedItem?.spesifikasi"></p>
                                </div>
                            </div>
                        </div>

                        {{-- QTY & SATUAN --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-atelier.input 
                                name="qty" 
                                label="Qty" 
                                type="number" 
                                step="0.0001" 
                                min="0.0001" 
                                :value="old('qty')" 
                                required 
                                hint="Jumlah pemakaian"
                            />

                            {{-- SATUAN (DROPDOWN) --}}
                            <div>
                                <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                                    Satuan <span class="text-red-400">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <select name="unit" x-model="unitValue" required
                                            class="flex-1 px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                        <option value="">-- Pilih Satuan --</option>
                                        <optgroup label="Hitung">
                                            <option value="pcs">pcs</option>
                                            <option value="unit">unit</option>
                                            <option value="set">set</option>
                                            <option value="lusin">lusin</option>
                                            <option value="rim">rim</option>
                                        </optgroup>
                                        <optgroup label="Kemasan">
                                            <option value="lembar">lembar</option>
                                            <option value="batang">batang</option>
                                            <option value="roll">roll</option>
                                            <option value="kaleng">kaleng</option>
                                            <option value="botol">botol</option>
                                            <option value="karung">karung</option>
                                            <option value="drum">drum</option>
                                        </optgroup>
                                        <optgroup label="Panjang">
                                            <option value="mm">mm</option>
                                            <option value="cm">cm</option>
                                            <option value="m">m</option>
                                        </optgroup>
                                        <optgroup label="Berat">
                                            <option value="gram">gram</option>
                                            <option value="kg">kg</option>
                                            <option value="ton">ton</option>
                                        </optgroup>
                                        <optgroup label="Volume">
                                            <option value="ml">ml</option>
                                            <option value="liter">liter</option>
                                        </optgroup>
                                    </select>
                                    <button type="button"
                                            x-show="autoFilledUnit && unitValue !== selectedItem?.unit"
                                            @click="unitValue = selectedItem?.unit"
                                            class="px-3 py-2 bg-navy-800 border border-navy-700 rounded-lg text-xs text-navy-300 hover:text-gold-500 hover:border-gold-500/50 transition"
                                            title="Reset ke satuan material">
                                        ↺
                                    </button>
                                </div>
                                <p class="text-xs text-navy-500 mt-1">
                                    <span x-show="autoFilledUnit && unitValue === selectedItem?.unit" class="text-green-400">✓ Auto-fill dari material</span>
                                    <span x-show="autoFilledUnit && unitValue !== selectedItem?.unit" class="text-orange-400">✎ Diubah manual</span>
                                    <span x-show="!autoFilledUnit">Pilih satuan pakai di BOM</span>
                                </p>
                            </div>
                        </div>

                        {{-- SPESIFIKASI --}}
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Spesifikasi</label>
                            <textarea name="spesifikasi" rows="1" placeholder="164 x 120 x 18 mm; Cokelat Single Corrugated"
                                      class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('spesifikasi') }}</textarea>
                        </div>

                        {{-- INFO DIMENSI --}}
                        <div x-show="needsDimension" x-cloak class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg text-xs text-blue-300">
                            <p class="font-semibold mb-1">
                                ℹ️ Material ini pakai costing method: 
                                <span class="text-gold-500" x-text="selectedItem?.costing_method"></span>
                            </p>
                            <p class="text-blue-400" x-text="dimensionHint"></p>
                        </div>

                        {{-- DIMENSI PAKAI --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <template x-if="needsArea || needsLength || needsVolumeKotak">
                                <div>
                                    <x-atelier.input 
                                        name="panjang_pakai" 
                                        label="Panjang Pakai (mm)" 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        :value="old('panjang_pakai')" 
                                        placeholder="200"
                                    />
                                </div>
                            </template>

                            <template x-if="needsArea || needsVolumeKotak">
                                <div>
                                    <x-atelier.input 
                                        name="lebar_pakai" 
                                        label="Lebar Pakai (mm)" 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        :value="old('lebar_pakai')" 
                                        placeholder="100"
                                    />
                                </div>
                            </template>

                            <template x-if="needsVolumeKotak">
                                <div>
                                    <x-atelier.input 
                                        name="tinggi_pakai" 
                                        label="Tinggi/Tebal Pakai (mm)" 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        :value="old('tinggi_pakai')" 
                                        placeholder="8"
                                    />
                                </div>
                            </template>

                            <template x-if="needsVolumeCair">
                                <div class="md:col-span-2">
                                    <x-atelier.input 
                                        name="volume_pakai" 
                                        label="Volume Pakai (ml)" 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        :value="old('volume_pakai')" 
                                        placeholder="50"
                                        hint="Volume pakai dalam ml. Contoh: 50 ml cat"
                                    />
                                </div>
                            </template>

                            <template x-if="needsWeight">
                                <div>
                                    <x-atelier.input 
                                        name="berat_pakai" 
                                        label="Berat Pakai (gram)" 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        :value="old('berat_pakai')" 
                                        placeholder="250"
                                    />
                                </div>
                            </template>
                        </div>

                        {{-- SCRAP & NOTES --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-atelier.input 
                                name="scrap_percent" 
                                label="Scrap %" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                max="100"
                                :value="old('scrap_percent', 0)" 
                                hint="Waste proses produksi (opsional)"
                            />

                            <div>
                                <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                                <textarea name="notes" rows="2" placeholder="Catatan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                            </div>
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
        // ============ SEARCHABLE DROPDOWN ============
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

        // ============ BOM ITEM FORM ============
        function bomItemForm() {
            return {
                // State
                selectedLevel: 'L.1',
                selectedDivisi: '',
                selectedItemType: '',
                selectedItemId: '',
                selectedItem: null,
                unitValue: '',
                autoFilledUnit: false,

                // Quick Material Modal State
                openQuickMaterialModal: false,
                savingQuickMaterial: false,
                quickMaterialError: '',
                quickMaterial: {
                    nama: '',
                    kode_bahan: '',
                    category: '',
                    unit: '',
                    costing_method: 'per_unit',
                    volume_type: 'kotak',
                    panjang_standar: '',
                    lebar_standar: '',
                    tinggi_standar: '',
                    berat_standar: '',
                    volume_standar: '',
                    price: '',
                    yield_percent: 100,
                    location: '',
                },

                // ============ EVENTS ============
                onItemChange(data) {
                    this.selectedItem = data;

                    // Auto-fill satuan
                    if (data.unit) {
                        this.unitValue = data.unit;
                        this.autoFilledUnit = true;
                    } else {
                        this.autoFilledUnit = false;
                    }

                    // Auto-fill spesifikasi
                    if (data.spesifikasi) {
                        const specInput = document.querySelector('textarea[name="spesifikasi"]');
                        if (specInput && !specInput.value) {
                            specInput.value = data.spesifikasi;
                        }
                    }
                },

                // ============ QUICK MATERIAL ============
                async saveQuickMaterial() {
                    this.quickMaterialError = '';
                    this.savingQuickMaterial = true;

                    try {
                        const response = await fetch('{{ route('master.materials.quick-store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(this.quickMaterial),
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Auto-select material baru
                            this.selectedItemType = 'material';
                            this.selectedItemId = data.material.id;
                            this.selectedItem = data.material;

                            // Auto-fill satuan
                            this.unitValue = data.material.unit || '';
                            this.autoFilledUnit = !!data.material.unit;

                            // Tutup modal
                            this.openQuickMaterialModal = false;

                            // Reset form
                            this.quickMaterial = {
                                nama: '', kode_bahan: '', category: '', unit: '',
                                costing_method: 'per_unit', volume_type: 'kotak',
                                panjang_standar: '', lebar_standar: '', tinggi_standar: '',
                                berat_standar: '', volume_standar: '',
                                price: '', yield_percent: 100, location: '',
                            };

                        } else {
                            this.quickMaterialError = data.message || 'Gagal simpan material.';
                        }
                    } catch (error) {
                        this.quickMaterialError = 'Gagal koneksi ke server: ' + error.message;
                    } finally {
                        this.savingQuickMaterial = false;
                    }
                },

                // ============ COMPUTED ============
                get costingMethod() {
                    if (this.selectedItemType !== 'material') return 'per_unit';
                    return this.selectedItem?.costing_method || 'per_unit';
                },

                get volumeType() {
                    if (this.selectedItemType !== 'material') return null;
                    return this.selectedItem?.volume_type || null;
                },

                get needsDimension() {
                    return ['per_area', 'per_volume', 'per_length', 'per_weight'].includes(this.costingMethod);
                },

                get needsArea() {
                    return ['per_area'].includes(this.costingMethod);
                },

                get needsVolumeCair() {
                    return this.costingMethod === 'per_volume' && this.volumeType === 'cair';
                },

                get needsVolumeKotak() {
                    return this.costingMethod === 'per_volume' && this.volumeType !== 'cair';
                },

                get needsLength() {
                    return this.costingMethod === 'per_length';
                },

                get needsWeight() {
                    return this.costingMethod === 'per_weight';
                },

                get dimensionHint() {
                    if (this.costingMethod === 'per_area') return 'Isi Panjang & Lebar potongan (mm).';
                    if (this.costingMethod === 'per_volume' && this.volumeType === 'cair') return 'Isi Volume pakai (ml).';
                    if (this.costingMethod === 'per_volume') return 'Isi Panjang, Lebar & Tinggi potongan (mm).';
                    if (this.costingMethod === 'per_length') return 'Isi Panjang potongan (mm).';
                    if (this.costingMethod === 'per_weight') return 'Isi Berat pakai (gram).';
                    return '';
                },
            }
        }
    </script>
    @endpush

    {{-- MODAL: TAMBAH MATERIAL BARU --}}
    <div x-data x-show="$data.openQuickMaterialModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-navy-950/80 backdrop-blur-sm p-4"
         @click.self="$data.openQuickMaterialModal = false">
        <div class="bg-navy-900 border border-navy-800 rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="p-6">

                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-serif text-xl font-bold text-white">Tambah Material Baru</h3>
                        <p class="text-xs text-navy-500 mt-1">Material akan otomatis dipilih setelah disimpan.</p>
                    </div>
                    <button type="button" @click="$data.openQuickMaterialModal = false" class="text-navy-500 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="$data.quickMaterialError" x-cloak
                     class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-sm text-red-400"
                     x-text="$data.quickMaterialError"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Nama Material <span class="text-red-400">*</span>
                        </label>
                        <input type="text" x-model="$data.quickMaterial.nama"
                               placeholder="Contoh: Manual Book - Palu PAUD"
                               class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Kode Internal</label>
                        <input type="text" x-model="$data.quickMaterial.kode_bahan" placeholder="A8-18-0"
                               class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Kategori</label>
                        <input type="text" x-model="$data.quickMaterial.category" placeholder="Kayu, Cat, Besi"
                               class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Satuan Beli <span class="text-red-400">*</span>
                        </label>
                        <select x-model="$data.quickMaterial.unit"
                                class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih --</option>
                            <optgroup label="Hitung">
                                <option value="pcs">pcs</option>
                                <option value="unit">unit</option>
                                <option value="set">set</option>
                                <option value="lusin">lusin</option>
                            </optgroup>
                            <optgroup label="Kemasan">
                                <option value="lembar">lembar</option>
                                <option value="batang">batang</option>
                                <option value="roll">roll</option>
                                <option value="kaleng">kaleng</option>
                                <option value="botol">botol</option>
                                <option value="karung">karung</option>
                                <option value="drum">drum</option>
                            </optgroup>
                            <optgroup label="Ukur">
                                <option value="mm">mm</option>
                                <option value="cm">cm</option>
                                <option value="m">m</option>
                                <option value="gram">gram</option>
                                <option value="kg">kg</option>
                                <option value="ml">ml</option>
                                <option value="liter">liter</option>
                            </optgroup>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Costing Method <span class="text-red-400">*</span>
                        </label>
                        <select x-model="$data.quickMaterial.costing_method"
                                class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="per_unit">Per Unit</option>
                            <option value="per_area">Per Area</option>
                            <option value="per_volume">Per Volume</option>
                            <option value="per_length">Per Length</option>
                            <option value="per_weight">Per Weight</option>
                        </select>
                    </div>

                    <div x-show="$data.quickMaterial.costing_method === 'per_volume'" class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Jenis Volume <span class="text-red-400">*</span>
                        </label>
                        <select x-model="$data.quickMaterial.volume_type"
                                class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="kotak">Volume Kotak (P × L × T)</option>
                            <option value="cair">Volume Cair (ml)</option>
                        </select>
                    </div>

                    <template x-if="['per_area', 'per_length'].includes($data.quickMaterial.costing_method) || ($data.quickMaterial.costing_method === 'per_volume' && $data.quickMaterial.volume_type === 'kotak')">
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Panjang (mm)</label>
                            <input type="number" x-model="$data.quickMaterial.panjang_standar" step="0.01" min="0" placeholder="2400"
                                   class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        </div>
                    </template>

                    <template x-if="$data.quickMaterial.costing_method === 'per_area' || ($data.quickMaterial.costing_method === 'per_volume' && $data.quickMaterial.volume_type === 'kotak')">
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Lebar (mm)</label>
                            <input type="number" x-model="$data.quickMaterial.lebar_standar" step="0.01" min="0" placeholder="1200"
                                   class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        </div>
                    </template>

                    <template x-if="$data.quickMaterial.costing_method === 'per_volume' && $data.quickMaterial.volume_type === 'kotak'">
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Tinggi/Tebal (mm)</label>
                            <input type="number" x-model="$data.quickMaterial.tinggi_standar" step="0.01" min="0" placeholder="18"
                                   class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        </div>
                    </template>

                    <template x-if="$data.quickMaterial.costing_method === 'per_weight'">
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Berat (gram)</label>
                            <input type="number" x-model="$data.quickMaterial.berat_standar" step="0.01" min="0" placeholder="1000"
                                   class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        </div>
                    </template>

                    <template x-if="$data.quickMaterial.costing_method === 'per_volume' && $data.quickMaterial.volume_type === 'cair'">
                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Volume (ml)</label>
                            <input type="number" x-model="$data.quickMaterial.volume_standar" step="0.01" min="0" placeholder="5000"
                                   class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        </div>
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Harga per Satuan (Rp) <span class="text-red-400">*</span>
                        </label>
                        <input type="number" x-model="$data.quickMaterial.price" step="0.01" min="0" placeholder="500000"
                               class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Yield (%)</label>
                        <input type="number" x-model="$data.quickMaterial.yield_percent" step="0.01" min="0" max="100" placeholder="100"
                               class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Lokasi Default</label>
                        <input type="text" x-model="$data.quickMaterial.location" placeholder="Rak A-01"
                               class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                    </div>

                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <button type="button" @click="$data.openQuickMaterialModal = false"
                            class="px-4 py-2 bg-navy-800 text-navy-300 border border-navy-700 rounded-lg text-sm font-semibold hover:text-white transition">
                        Batal
                    </button>
                    <button type="button" @click="$data.saveQuickMaterial()"
                            :disabled="$data.savingQuickMaterial"
                            class="px-4 py-2 bg-gold-500 text-navy-900 rounded-lg text-sm font-bold hover:bg-gold-400 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!$data.savingQuickMaterial">Simpan & Pilih</span>
                        <span x-show="$data.savingQuickMaterial">Menyimpan...</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

</x-app-layout>