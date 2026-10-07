<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Catat Stock Movement</h2>
    </x-slot>

    @php
        // Siapkan data untuk searchable dropdown ITEM
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
    @endphp

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M4A" title="Catat Movement" subtitle="Input pergerakan stok manual" />

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

        <form method="POST" action="{{ route('warehouse.stock-movements.store') }}"
              x-data="stockMovementForm()"
              x-init="init()"
              @submit="validateBeforeSubmit($event)">
            @csrf

            <x-atelier.card title="Informasi Movement" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- TIPE MOVEMENT --}}
                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Tipe Movement <span class="text-red-400">*</span>
                        </label>
                        <select name="type" required x-model="selectedType" @change="onTypeChange()"
                                class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="in">IN (Stok Masuk)</option>
                            <option value="out">OUT (Stok Keluar)</option>
                            <option value="adjustment">ADJUSTMENT (Koreksi)</option>
                        </select>
                    </div>

                    {{-- TANGGAL --}}
                    <x-atelier.input name="date" label="Tanggal" type="date" :value="old('date', date('Y-m-d'))" required />

                    {{-- DROPDOWN ALASAN — muncul kalau type = adjustment --}}
                    <div x-show="selectedType === 'adjustment'" x-cloak class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Alasan Adjustment <span class="text-red-400">*</span>
                        </label>
                        <select name="adjustment_reason" :required="selectedType === 'adjustment'"
                                class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Alasan --</option>
                            <option value="damaged" @selected(old('adjustment_reason') == 'damaged')>Barang Rusak</option>
                            <option value="lost" @selected(old('adjustment_reason') == 'lost')>Barang Hilang</option>
                            <option value="expired" @selected(old('adjustment_reason') == 'expired')>Kadaluarsa</option>
                            <option value="correction" @selected(old('adjustment_reason') == 'correction')>Koreksi Pencatatan</option>
                            <option value="found" @selected(old('adjustment_reason') == 'found')>Barang Ditemukan</option>
                            <option value="opname" @selected(old('adjustment_reason') == 'opname')>Hasil Opname</option>
                            <option value="other" @selected(old('adjustment_reason') == 'other')>Lainnya</option>
                        </select>
                        @error('adjustment_reason')
                            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hidden inputs --}}
                    <input type="hidden" name="item_type" x-model="selectedItemType" required>
                    <input type="hidden" name="item_id" x-model="selectedItemId" required>

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
                                onItemChange();
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
                                               placeholder="Cari material / product..."
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
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Lokasi <span class="text-red-400">*</span>
                            <span x-show="selectedType === 'out'" class="text-xs text-navy-500 font-normal normal-case ml-2">
                                (hanya lokasi dengan stok)
                            </span>
                        </label>

                        {{-- Info: pilih item dulu --}}
                        <div x-show="!selectedItemId"
                             class="p-3 bg-navy-800/30 border border-navy-700 rounded-lg text-sm text-navy-400 text-center">
                            Pilih item terlebih dahulu untuk melihat daftar lokasi
                        </div>

                        {{-- Dropdown lokasi --}}
                        <div x-show="selectedItemId" x-cloak x-data="{ openLoc: false, searchLoc: '' }" class="relative">
                            <input type="hidden" name="location_id" :value="selectedLocationId" required>

                            {{-- Trigger --}}
                            <div @click="openLoc = !openLoc"
                                 class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white cursor-pointer flex items-center justify-between"
                                 :class="openLoc && 'border-gold-500 ring-2 ring-gold-500/20'">
                                <span x-show="selectedLocationId && locationOptions.length > 0"
                                      x-text="locationOptions.find(l => l.id === selectedLocationId)?.label || '-- Pilih Lokasi --'"
                                      class="truncate"></span>
                                <span x-show="!selectedLocationId" class="text-navy-500">
                                    <span x-show="isLoadingLocations">Memuat lokasi...</span>
                                    <span x-show="!isLoadingLocations">-- Pilih Lokasi --</span>
                                </span>
                                <svg class="w-4 h-4 text-navy-500 flex-shrink-0" :class="openLoc && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>

                            {{-- Dropdown List --}}
                            <div x-show="openLoc" x-cloak @click.away="openLoc = false"
                                 class="absolute z-50 mt-1 w-full bg-navy-800 border border-navy-700 rounded-lg shadow-xl overflow-hidden">
                                <div class="p-2 border-b border-navy-700">
                                    <input type="text" x-model="searchLoc"
                                           placeholder="Cari lokasi..."
                                           class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-0 focus:outline-none">
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <template x-for="loc in locationOptions.filter(l => l.label.toLowerCase().includes(searchLoc.toLowerCase()))" :key="loc.id">
                                        <div @click="selectedLocationId = loc.id; openLoc = false; onLocationChange();"
                                             class="px-3 py-2 text-sm cursor-pointer"
                                             :class="selectedLocationId === loc.id ? 'bg-gold-500/20 text-gold-400' : 'text-white hover:bg-navy-700'"
                                             x-text="loc.label"></div>
                                    </template>
                                    <div x-show="locationOptions.filter(l => l.label.toLowerCase().includes(searchLoc.toLowerCase())).length === 0"
                                         class="px-3 py-4 text-center text-sm text-navy-500">
                                        Tidak ada lokasi.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- STOCK HINT --}}
                    <div x-show="showStockHint" x-cloak class="md:col-span-2">
                        <div class="p-4 rounded-lg border"
                             :class="stockInfo.is_low_stock
                                 ? 'bg-red-500/10 border-red-500/30'
                                 : 'bg-blue-500/10 border-blue-500/30'">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5"
                                     :class="stockInfo.is_low_stock ? 'text-red-400' : 'text-blue-400'"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold"
                                       :class="stockInfo.is_low_stock ? 'text-red-400' : 'text-blue-400'">
                                        Stok Saat Ini di <span x-text="stockInfo.location_name"></span>
                                    </p>
                                    <p class="text-2xl font-serif font-bold text-white mt-1">
                                        <span x-text="formatNumber(stockInfo.qty)"></span>
                                    </p>
                                    <div class="text-xs text-navy-400 mt-2 space-y-0.5">
                                        <p x-show="stockInfo.min_stock > 0">
                                            Minimum: <span x-text="formatNumber(stockInfo.min_stock)"></span>
                                        </p>
                                        <p x-show="stockInfo.max_stock > 0">
                                            Maksimum: <span x-text="formatNumber(stockInfo.max_stock)"></span>
                                        </p>
                                        <p x-show="stockInfo.is_low_stock" class="text-red-400 font-semibold">
                                            ⚠️ Stok di bawah minimum!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- QTY --}}
                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Qty <span class="text-red-400">*</span>
                        </label>
                        <input type="number" name="qty" step="0.01" min="0.01" required
                               x-model.number="qty"
                               @input="validateQty()"
                               :class="qtyError ? 'border-red-500 ring-2 ring-red-500/20' : 'border-navy-700'"
                               class="w-full px-3 py-2.5 bg-navy-950 border rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                               placeholder="0">
                        <p x-show="qtyError" x-cloak class="text-xs text-red-400 mt-1.5" x-text="qtyError"></p>
                        <p x-show="!qtyError" class="text-xs text-navy-500 mt-1.5">
                            <span x-show="selectedType === 'adjustment'">Bisa positif (surplus) atau negatif (minus)</span>
                            <span x-show="selectedType === 'out'">Maksimal: <span x-text="formatNumber(stockInfo.qty)"></span></span>
                            <span x-show="selectedType === 'in'">Jumlah yang masuk</span>
                        </p>
                    </div>

                    {{-- UNIT PRICE --}}
                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Harga per Unit (Rp)
                        </label>
                        <input type="number" name="unit_price" step="0.01" min="0"
                               x-model.number="unitPrice"
                               @input="userEditedPrice = true"
                               class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                               placeholder="0">
                        <p class="text-xs text-navy-500 mt-1.5">
                            <span x-show="unitPriceFromInventory && !userEditedPrice">✓ Auto-fill dari inventory</span>
                            <span x-show="userEditedPrice">✎ Diedit manual</span>
                            <span x-show="!unitPriceFromInventory && !userEditedPrice">Opsional</span>
                        </p>
                    </div>

                    {{-- NOTES --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan..."
                                  class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('warehouse.stock-movements.index')" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary" x-bind:disabled="!canSubmit">
                    <span x-show="!isSubmitting">Simpan Movement</span>
                    <span x-show="isSubmitting">Menyimpan...</span>
                </x-atelier.button>
            </div>

        </form>

    </div>

    @push('scripts')
    <script>
        // ============ SEARCHABLE DROPDOWN (untuk ITEM saja) ============
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

        // ============ STOCK MOVEMENT FORM ============
        function stockMovementForm() {
            return {
                // Form state
                selectedType: '{{ old('type', $type) }}',
                selectedItemType: '{{ old('item_type', 'material') }}',
                selectedItemId: '{{ old('item_id') }}',
                selectedLocationId: '{{ old('location_id') }}',
                qty: {{ old('qty', 0) }},
                unitPrice: {{ old('unit_price', 0) }},
                unitPriceFromInventory: false,
                userEditedPrice: false,
                isSubmitting: false,

                // Stock info
                showStockHint: false,
                stockInfo: {
                    qty: 0,
                    min_stock: 0,
                    max_stock: 0,
                    is_low_stock: false,
                    location_name: '',
                },

                // Location options (dynamic)
                locationOptions: [],
                isLoadingLocations: false,

                // Validation
                qtyError: '',

                // ============ INIT ============
                init() {
                    // Kalau ada old value (setelah validation error), fetch lokasi
                    if (this.selectedItemId) {
                        this.fetchLocations().then(() => {
                            if (this.selectedLocationId) {
                                this.fetchStock();
                            }
                        });
                    }
                },

                // ============ EVENTS ============
                onTypeChange() {
                    this.validateQty();
                    // Re-fetch locations karena filter berbeda untuk IN vs OUT
                    if (this.selectedItemId) {
                        this.fetchLocations();
                    }
                },

                onItemChange() {
                    this.showStockHint = false;
                    this.selectedLocationId = '';
                    this.unitPrice = 0;                  // RESET harga
                    this.unitPriceFromInventory = false; // RESET flag auto-fill
                    this.userEditedPrice = false;        // RESET flag manual edit
                    this.qtyError = '';

                    if (this.selectedItemId) {
                        this.fetchLocations();
                    }
                },

                onLocationChange() {
                    if (this.selectedItemId && this.selectedLocationId) {
                        this.fetchStock();
                    }
                },

                // ============ FETCH LOCATIONS ============
                async fetchLocations() {
                    if (!this.selectedItemId) return;

                    this.isLoadingLocations = true;
                    this.locationOptions = [];

                    try {
                        // Untuk OUT, filter lokasi yang stoknya > 0
                        // Untuk IN/ADJUSTMENT, tampilkan semua lokasi aktif
                        const minQty = this.selectedType === 'out' ? 0.01 : 0;

                        const url = `{{ route('warehouse.stock-movements.get-locations') }}?item_type=${this.selectedItemType}&item_id=${this.selectedItemId}&min_qty=${minQty}`;

                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.locationOptions = data.locations.map(loc => ({
                                id: loc.location_id,
                                label: loc.label,
                                qty: loc.qty,
                                unit_price: loc.unit_price,
                            }));
                        }
                    } catch (error) {
                        console.error('Failed to fetch locations:', error);
                    } finally {
                        this.isLoadingLocations = false;
                    }
                },

                // ============ FETCH STOCK ============
                async fetchStock() {
                    if (!this.selectedItemId || !this.selectedLocationId) return;

                    try {
                        const url = `{{ route('warehouse.stock-movements.get-stock') }}?item_type=${this.selectedItemType}&item_id=${this.selectedItemId}&location_id=${this.selectedLocationId}`;
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.stockInfo = {
                                qty: data.qty,
                                min_stock: data.min_stock,
                                max_stock: data.max_stock,
                                is_low_stock: data.is_low_stock,
                                location_name: data.location_name,
                            };
                            this.showStockHint = true;

                            // Auto-fill harga:
                            // - Kalau user BELUM edit manual, OVERWRITE harga
                            // - Kalau user SUDAH edit manual, JANGAN overwrite
                            if (!this.userEditedPrice && data.unit_price > 0) {
                                this.unitPrice = data.unit_price;
                                this.unitPriceFromInventory = true;
                            }

                            this.validateQty();
                        }
                    } catch (error) {
                        console.error('Failed to fetch stock:', error);
                    }
                },

                // ============ VALIDASI ============
                validateQty() {
                    this.qtyError = '';

                    if (!this.qty || this.qty <= 0) {
                        if (this.selectedType === 'adjustment' && this.qty === 0) {
                            this.qtyError = 'Qty tidak boleh 0.';
                        }
                        return;
                    }

                    if (this.selectedType === 'out' && this.showStockHint) {
                        if (this.qty > this.stockInfo.qty) {
                            this.qtyError = `Stok tidak cukup. Tersedia: ${this.formatNumber(this.stockInfo.qty)}, diminta: ${this.formatNumber(this.qty)}.`;
                        }
                    }
                },

                validateBeforeSubmit(event) {
                    this.validateQty();

                    if (this.qtyError) {
                        event.preventDefault();
                        return false;
                    }

                    this.isSubmitting = true;
                    return true;
                },

                // ============ HELPERS ============
                get canSubmit() {
                    return !this.qtyError && this.qty > 0 && this.selectedItemId && this.selectedLocationId;
                },

                formatNumber(value) {
                    if (value === null || value === undefined) return '0';
                    return new Intl.NumberFormat('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2,
                    }).format(value);
                },
            }
        }
    </script>
    @endpush

</x-app-layout>