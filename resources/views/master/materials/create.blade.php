<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Tambah Material</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Tambah Material" subtitle="Isi data material baru" />

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

        <form method="POST" action="{{ route('master.materials.store') }}" x-data="materialForm()">
            @csrf

            {{-- ============ INFORMASI DASAR ============ --}}
            <x-atelier.card title="Informasi Dasar" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input name="kode" label="Kode Sistem" :value="$generatedKode" required hint="Auto-generate, bisa diubah" />

                    <x-atelier.input name="kode_bahan" label="Kode Internal" :value="old('kode_bahan')" placeholder="A8-18-0" hint="Kode internal perusahaan (opsional)" />

                    <div class="md:col-span-2">
                        <x-atelier.input name="nama" label="Nama Material" :value="old('nama')" placeholder="Kayu Pinus 164×120×18" required />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Spesifikasi</label>
                        <textarea name="spesifikasi" rows="2" placeholder="Deskripsi detail material..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('spesifikasi') }}</textarea>
                    </div>

                    <x-atelier.input name="category" label="Kategori" :value="old('category')" placeholder="Kayu, Cat, Besi, dll" />

                    <x-atelier.input name="unit" label="Satuan Beli" :value="old('unit')" placeholder="pcs, lembar, roll, karung" required hint="Satuan saat beli" />
                </div>
            </x-atelier.card>

            {{-- ============ COSTING METHOD ============ --}}
            <x-atelier.card title="Costing Method" subtitle="Pilih metode kalkulasi biaya" :brackets="true">
                <div>
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                        Metode Costing <span class="text-red-400">*</span>
                    </label>
                    <select name="costing_method" required x-model="costingMethod" @change="onMethodChange()"
                            class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="per_unit">Per Unit — Beli pcs, pakai pcs</option>
                        <option value="per_area">Per Area — Beli lembar, pakai luas (mm²)</option>
                        <option value="per_volume">Per Volume — Beli batang, pakai volume (mm³)</option>
                        <option value="per_length">Per Panjang — Beli roll, pakai panjang (mm)</option>
                        <option value="per_weight">Per Berat — Beli karung, pakai berat (gram)</option>
                    </select>
                </div>

                {{-- INFO --}}
                <div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-xs text-blue-300">
                            <p class="font-semibold mb-1" x-text="methodInfo.title"></p>
                            <p class="text-blue-400" x-text="methodInfo.description"></p>
                            <p class="text-blue-400 mt-1" x-text="methodInfo.example"></p>
                        </div>
                    </div>
                </div>
            </x-atelier.card>

            {{-- ============ DIMENSI STANDAR (DINAMIS) ============ --}}
            <x-atelier.card x-show="needsDimension" x-cloak title="Dimensi Standar" subtitle="Ukuran satuan beli" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                    {{-- PANJANG (untuk per_area, per_volume, per_length) --}}
                    <template x-if="costingMethod === 'per_area' || costingMethod === 'per_volume' || costingMethod === 'per_length'">
                        <div>
                            <x-atelier.input 
                                name="panjang_standar" 
                                label="Panjang (mm)" 
                                type="number" 
                                step="0.01" 
                                min="0"
                                :value="old('panjang_standar')" 
                                placeholder="2400"
                                hint="Panjang satuan beli"
                            />
                        </div>
                    </template>

                    {{-- LEBAR (untuk per_area, per_volume) --}}
                    <template x-if="costingMethod === 'per_area' || costingMethod === 'per_volume'">
                        <div>
                            <x-atelier.input 
                                name="lebar_standar" 
                                label="Lebar (mm)" 
                                type="number" 
                                step="0.01" 
                                min="0"
                                :value="old('lebar_standar')" 
                                placeholder="1200"
                                hint="Lebar satuan beli"
                            />
                        </div>
                    </template>

                    {{-- TINGGI (untuk per_volume) --}}
                    <template x-if="costingMethod === 'per_volume'">
                        <div>
                            <x-atelier.input 
                                name="tinggi_standar" 
                                label="Tinggi/Tebal (mm)" 
                                type="number" 
                                step="0.01" 
                                min="0"
                                :value="old('tinggi_standar')" 
                                placeholder="18"
                                hint="Tebal satuan beli"
                            />
                        </div>
                    </template>

                    {{-- BERAT (untuk per_weight) --}}
                    <template x-if="costingMethod === 'per_weight'">
                        <div>
                            <x-atelier.input 
                                name="berat_standar" 
                                label="Berat (gram)" 
                                type="number" 
                                step="0.01" 
                                min="0"
                                :value="old('berat_standar')" 
                                placeholder="25000"
                                hint="Contoh: 25 kg = 25.000 gram"
                            />
                        </div>
                    </template>

                </div>
            </x-atelier.card>

            {{-- ============ HARGA & YIELD ============ --}}
            <x-atelier.card title="Harga & Yield" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input 
                        name="price" 
                        label="Harga per Satuan Beli (Rp)" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        :value="old('price')" 
                        required 
                        placeholder="500000"
                        hint="Harga 1 satuan beli"
                    />

                    <x-atelier.input 
                        name="yield_percent" 
                        label="Yield / Efisiensi (%)" 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="100"
                        :value="old('yield_percent', 100)" 
                        placeholder="100"
                        hint="100 = tanpa waste, 85 = waste 15%"
                    />

                </div>
            </x-atelier.card>

            {{-- ============ STOK ============ --}}
            <x-atelier.card title="Stok & Supplier" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                    <x-atelier.input name="min_stock" label="Min Stock" type="number" step="0.01" min="0" :value="old('min_stock')" hint="Batas minimum" />
                    <x-atelier.input name="max_stock" label="Max Stock" type="number" step="0.01" min="0" :value="old('max_stock')" hint="Batas maksimum" />
                    <x-atelier.input name="location" label="Lokasi Default" :value="old('location')" placeholder="Rak A-01" />

                </div>
            </x-atelier.card>

            {{-- ============ STATUS & CATATAN ============ --}}
            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                    <label for="is_active" class="text-sm text-navy-300">Material Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan tambahan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('master.materials.index')" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary">Simpan Material</x-atelier.button>
            </div>

        </form>

    </div>

    @push('scripts')
    <script>
        function materialForm() {
            return {
                costingMethod: '{{ old('costing_method', 'per_unit') }}',

                get needsDimension() {
                    return ['per_area', 'per_volume', 'per_length', 'per_weight'].includes(this.costingMethod);
                },

                get methodInfo() {
                    const info = {
                        per_unit: {
                            title: 'Per Unit — Beli pcs, pakai pcs',
                            description: 'Cocok untuk sekrup, kancing, komponen kecil.',
                            example: 'Contoh: Sekrup Rp 500/pcs, BOM butuh 8 pcs → Cost Rp 4.000',
                        },
                        per_area: {
                            title: 'Per Area — Beli lembar, pakai luas',
                            description: 'Cocok untuk MDF, plywood, kaca, akrilik, kain.',
                            example: 'Contoh: MDF Rp 500K/lembar (2400×1200), BOM butuh 200×100 → Cost Rp 3.472',
                        },
                        per_volume: {
                            title: 'Per Volume — Beli batang, pakai volume',
                            description: 'Cocok untuk kayu balok, besi beton, resin.',
                            example: 'Contoh: Kayu Rp 200K/batang (100×100×4000), BOM butuh 100×100×500 → Cost Rp 25.000',
                        },
                        per_length: {
                            title: 'Per Panjang — Beli roll, pakai panjang',
                            description: 'Cocok untuk kabel, pipa, tali, dowel.',
                            example: 'Contoh: Kabel Rp 200K/roll (100 m), BOM butuh 500 mm → Cost Rp 1.000',
                        },
                        per_weight: {
                            title: 'Per Berat — Beli karung, pakai berat',
                            description: 'Cocok untuk tepung, gula, cat bubuk, resin pellet.',
                            example: 'Contoh: Tepung Rp 300K/karung (25 kg), BOM butuh 250 g → Cost Rp 3.000',
                        },
                    };
                    return info[this.costingMethod] || info.per_unit;
                },

                onMethodChange() {
                    // Reset info — biar reaktif
                }
            }
        }
    </script>
    @endpush

</x-app-layout>