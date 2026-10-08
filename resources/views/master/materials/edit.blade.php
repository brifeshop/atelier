<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Material</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Edit Material" subtitle="{{ $item->kode }} — {{ $item->nama }}" />

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

        <form method="POST" action="{{ route('master.materials.update', $item) }}" x-data="materialForm()">
            @csrf
            @method('PUT')

            {{-- INFO DASAR --}}
            <x-atelier.card title="Informasi Dasar" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Sistem" :value="$item->kode" required />
                    <x-atelier.input name="kode_bahan" label="Kode Internal" :value="$item->kode_bahan" placeholder="A8-18-0" hint="Kode internal perusahaan (opsional)" />
                    <div class="md:col-span-2">
                        <x-atelier.input name="nama" label="Nama Material" :value="$item->nama" required />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Spesifikasi</label>
                        <textarea name="spesifikasi" rows="2" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('spesifikasi', $item->spesifikasi) }}</textarea>
                    </div>
                    <x-atelier.input name="category" label="Kategori" :value="$item->category" />
                    <x-atelier.input name="unit" label="Satuan Beli" :value="$item->unit" required />
                </div>
            </x-atelier.card>

            {{-- COSTING METHOD --}}
            <x-atelier.card title="Costing Method" :brackets="true">
                <div>
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                        Metode Costing <span class="text-red-400">*</span>
                    </label>
                    <select name="costing_method" required x-model="costingMethod" @change="onMethodChange()"
                            class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="per_unit">Per Unit — Beli pcs, pakai pcs</option>
                        <option value="per_area">Per Area — Beli lembar, pakai luas (mm²)</option>
                        <option value="per_volume">Per Volume — Beli batang/cair, pakai volume</option>
                        <option value="per_length">Per Panjang — Beli roll, pakai panjang (mm)</option>
                        <option value="per_weight">Per Berat — Beli karung, pakai berat (gram)</option>
                    </select>
                </div>

                {{-- VOLUME TYPE --}}
                <div x-show="costingMethod === 'per_volume'" x-cloak class="mt-4">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                        Jenis Volume <span class="text-red-400">*</span>
                    </label>
                    <select name="volume_type" x-model="volumeType" :required="costingMethod === 'per_volume'"
                            class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="kotak">Volume Kotak (P × L × T) — untuk balok, batang</option>
                        <option value="cair">Volume Cair (ml) — untuk cat, varnish, tinta, oli</option>
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

            {{-- DIMENSI STANDAR --}}
            <x-atelier.card x-show="needsDimension" x-cloak title="Dimensi Standar" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                    <template x-if="costingMethod === 'per_area' || costingMethod === 'per_length' || (costingMethod === 'per_volume' && volumeType === 'kotak')">
                        <div>
                            <x-atelier.input name="panjang_standar" label="Panjang (mm)" type="number" step="0.01" min="0" :value="$item->panjang_standar" />
                        </div>
                    </template>

                    <template x-if="costingMethod === 'per_area' || (costingMethod === 'per_volume' && volumeType === 'kotak')">
                        <div>
                            <x-atelier.input name="lebar_standar" label="Lebar (mm)" type="number" step="0.01" min="0" :value="$item->lebar_standar" />
                        </div>
                    </template>

                    <template x-if="costingMethod === 'per_volume' && volumeType === 'kotak'">
                        <div>
                            <x-atelier.input name="tinggi_standar" label="Tinggi/Tebal (mm)" type="number" step="0.01" min="0" :value="$item->tinggi_standar" />
                        </div>
                    </template>

                    <template x-if="costingMethod === 'per_weight'">
                        <div>
                            <x-atelier.input name="berat_standar" label="Berat (gram)" type="number" step="0.01" min="0" :value="$item->berat_standar" />
                        </div>
                    </template>

                    <template x-if="costingMethod === 'per_volume' && volumeType === 'cair'">
                        <div class="md:col-span-3">
                            <x-atelier.input name="volume_standar" label="Volume Standar (ml)" type="number" step="0.01" min="0" :value="$item->volume_standar" hint="Contoh: 5 liter = 5.000 ml" />
                        </div>
                    </template>
                </div>
            </x-atelier.card>

            {{-- HARGA & YIELD --}}
            <x-atelier.card title="Harga & Yield" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="price" label="Harga per Satuan Beli (Rp)" type="number" step="0.01" min="0" :value="$item->price" required />
                    <x-atelier.input name="yield_percent" label="Yield (%)" type="number" step="0.01" min="0" max="100" :value="$item->yield_percent" hint="100 = tanpa waste" />
                </div>
            </x-atelier.card>

            {{-- STOK --}}
            <x-atelier.card title="Stok & Supplier" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <x-atelier.input name="min_stock" label="Min Stock" type="number" step="0.01" min="0" :value="$item->min_stock" />
                    <x-atelier.input name="max_stock" label="Max Stock" type="number" step="0.01" min="0" :value="$item->max_stock" />
                    <x-atelier.input name="location" label="Lokasi Default" :value="$item->location" />
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                    <label for="is_active" class="text-sm text-navy-300">Material Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.materials.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Perubahan</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>

    @push('scripts')
    <script>
        function materialForm() {
            return {
                costingMethod: '{{ old('costing_method', $item->costing_method ?? 'per_unit') }}',
                volumeType: '{{ old('volume_type', $item->volume_type ?? 'kotak') }}',

                get needsDimension() {
                    return ['per_area', 'per_volume', 'per_length', 'per_weight'].includes(this.costingMethod);
                },

                get methodInfo() {
                    const info = {
                        per_unit: { title: 'Per Unit', description: 'Beli pcs, pakai pcs', example: 'Contoh: Sekrup Rp 500/pcs' },
                        per_area: { title: 'Per Area', description: 'Beli lembar, pakai luas', example: 'Contoh: MDF Rp 500K/lembar (2400×1200)' },
                        per_volume: { title: 'Per Volume', description: 'Beli batang/cair, pakai volume', example: 'Cair: Varnish Rp 500K/5 liter' },
                        per_length: { title: 'Per Panjang', description: 'Beli roll, pakai panjang', example: 'Contoh: Kabel Rp 200K/roll (100 m)' },
                        per_weight: { title: 'Per Berat', description: 'Beli karung, pakai berat', example: 'Contoh: Tepung Rp 300K/karung (25 kg)' },
                    };
                    return info[this.costingMethod] || info.per_unit;
                },

                onMethodChange() {
                    if (this.costingMethod !== 'per_volume') {
                        this.volumeType = 'kotak';
                    }
                },
            }
        }
    </script>
    @endpush

</x-app-layout>