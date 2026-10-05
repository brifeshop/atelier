<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Product</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Edit Product" subtitle="{{ $item->kode }} — {{ $item->nama }}" />

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

        <form method="POST" action="{{ route('master.products.update', $item) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- FOTO --}}
            <x-atelier.card title="Foto Product" subtitle="Opsional, max 2MB" :brackets="true">
                <div class="flex items-start gap-6">
                    <div class="flex-shrink-0">
                        <img id="photo-preview"
                             src="{{ $item->photo_url ?? 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22128%22 height=%22128%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23475569%22 stroke-width=%221.5%22%3E%3Crect x=%223%22 y=%223%22 width=%2218%22 height=%2218%22 rx=%222%22/%3E%3Ccircle cx=%228.5%22 cy=%228.5%22 r=%221.5%22/%3E%3Cpath d=%22M21 15l-5-5L5 21%22/%3E%3C/svg%3E' }}"
                             class="w-32 h-32 object-cover rounded-lg border border-navy-700 bg-navy-950">
                    </div>

                    <div class="flex-1">
                        <input type="file" name="photo" accept="image/*"
                               onchange="previewPhoto(this)"
                               class="w-full px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gold-500 file:text-navy-900 hover:file:bg-gold-400 cursor-pointer">
                        <p class="text-xs text-navy-500 mt-2">Format: JPG, PNG, WEBP. Maksimal 2MB.</p>

                        @if($item->photo_path)
                            <p class="text-xs text-green-400 mt-1">✓ Foto sudah ada. Upload baru untuk mengganti.</p>
                        @endif

                        <button type="button" onclick="resetPhoto()"
                                class="mt-3 text-xs text-red-400 hover:text-red-300 transition">
                            Reset preview
                        </button>
                    </div>
                </div>
            </x-atelier.card>

            {{-- INFORMASI PRODUK --}}
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

            {{-- CATATAN + STATUS + SIMPAN --}}
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

    @push('scripts')
    <script>
        const defaultPhotoSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='128' height='128' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='1.5'%3E%3Crect x='3' y='3' width='18' height='18' rx='2'/%3E%3Ccircle cx='8.5' cy='8.5' r='1.5'/%3E%3Cpath d='M21 15l-5-5L5 21'/%3E%3C/svg%3E";
        const originalPhotoSrc = "{{ $item->photo_url ?? '' }}";

        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photo-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function resetPhoto() {
            document.querySelector('input[name="photo"]').value = '';
            document.getElementById('photo-preview').src = originalPhotoSrc || defaultPhotoSvg;
        }
    </script>
    @endpush

</x-app-layout>