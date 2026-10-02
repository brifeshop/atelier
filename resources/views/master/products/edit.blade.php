<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Product</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Edit Product" subtitle="{{ $item->kode }} — {{ $item->nama }}" />

        <form method="POST" action="{{ route('master.products.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Produk" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Product" :value="$item->kode" required />
                    <x-atelier.input name="nama" label="Nama Produk" :value="$item->nama" required />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Kategori</label>
                        <select name="category" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach(['Produk Jadi', 'Sub-Assembly', 'Komponen', 'Lainnya'] as $cat)
                                <option value="{{ $cat }}" @selected(old('category', $item->category) == $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Satuan <span class="text-red-400">*</span></label>
                        <select name="unit" required class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            @foreach(['pcs', 'set', 'unit', 'box'] as $unit)
                                <option value="{{ $unit }}" @selected(old('unit', $item->unit) == $unit)>{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-atelier.input name="selling_price" label="Harga Jual (Rp)" type="number" step="0.01" :value="$item->selling_price" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Deskripsi</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('description', $item->description) }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                    <label for="is_active" class="text-sm text-navy-300">Product Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.products.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Perubahan</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>