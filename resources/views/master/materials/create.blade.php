<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Tambah Material</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Tambah Material" subtitle="Isi data bahan baku baru" />

        <form method="POST" action="{{ route('master.materials.store') }}">
            @csrf

            <x-atelier.card title="Informasi Material" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Material" :value="$generatedKode" required hint="Kode otomatis, bisa diubah" />
                    <x-atelier.input name="nama" label="Nama Material" placeholder="Fiber Glass" required />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Kategori</label>
                        <select name="category" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach(['Raw Material', 'Consumable', 'Packaging', 'Spare Part', 'Lainnya'] as $cat)
                                <option value="{{ $cat }}" @selected(old('category') == $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Satuan <span class="text-red-400">*</span></label>
                        <select name="unit" required class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Satuan --</option>
                            @foreach(['pcs', 'kg', 'gram', 'meter', 'cm', 'liter', 'ml', 'roll', 'box', 'set'] as $unit)
                                <option value="{{ $unit }}" @selected(old('unit', 'pcs') == $unit)>{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-atelier.input name="price" label="Harga per Unit (Rp)" type="number" step="0.01" placeholder="50000" />
                    <x-atelier.input name="location" label="Lokasi Penyimpanan" placeholder="Rak A-01" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Supplier</label>
                        <select name="supplier_id" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Supplier (Opsional) --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                    {{ $supplier->kode }} — {{ $supplier->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card title="Stok" subtitle="Untuk tracking inventory" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <x-atelier.input name="current_stock" label="Stok Saat Ini" type="number" step="0.01" value="0" />
                    <x-atelier.input name="min_stock" label="Stok Minimum" type="number" step="0.01" value="0" hint="Alert kalau stok ≤ ini" />
                    <x-atelier.input name="max_stock" label="Stok Maksimum" type="number" step="0.01" hint="Opsional" />
                </div>
            </x-atelier.card>

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

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.materials.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Material</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>