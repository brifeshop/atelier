<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Tambah Warehouse</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M4A" title="Tambah Warehouse" subtitle="Isi data gudang baru" />

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

        <form method="POST" action="{{ route('warehouse.warehouses.store') }}">
            @csrf

            <x-atelier.card title="Informasi Warehouse" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Warehouse" :value="$generatedKode" required hint="Kode otomatis, bisa diubah" />
                    <x-atelier.input name="nama" label="Nama Warehouse" placeholder="Gudang Utama" required />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Type <span class="text-red-400">*</span>
                        </label>
                        <select name="type" required class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Type --</option>
                            <option value="Raw Material" @selected(old('type') == 'Raw Material')>Raw Material</option>
                            <option value="WIP" @selected(old('type') == 'WIP')>WIP (Work In Progress)</option>
                            <option value="Finished Goods" @selected(old('type') == 'Finished Goods')>Finished Goods</option>
                        </select>
                    </div>

                    <x-atelier.input name="pic_name" label="PIC (Penanggung Jawab)" placeholder="Budi Santoso" />
                    <x-atelier.input name="phone" label="Telepon" placeholder="0812-xxxx-xxxx" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Alamat</label>
                        <textarea name="address" rows="2" placeholder="Alamat gudang..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('address') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                    <label for="is_active" class="text-sm text-navy-300">Warehouse Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('warehouse.warehouses.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Warehouse</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>