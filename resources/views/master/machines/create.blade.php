<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Tambah Machine</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Tambah Machine" subtitle="Isi data mesin baru" />

        <form method="POST" action="{{ route('master.machines.store') }}">
            @csrf

            <x-atelier.card title="Informasi Mesin" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Machine" :value="$generatedKode" required hint="Kode otomatis, bisa diubah" />
                    <x-atelier.input name="nama" label="Nama Mesin" placeholder="Mesin Cutting" required />
                    <x-atelier.input name="type" label="Type" placeholder="Cutting, Welding, Assembly" />
                    <x-atelier.input name="brand" label="Brand" placeholder="Merek mesin" />
                    <x-atelier.input name="model" label="Model" placeholder="Model mesin" />
                    <x-atelier.input name="serial_number" label="Serial Number" placeholder="No. seri" />
                    <x-atelier.input name="location" label="Lokasi" placeholder="Workshop A" />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            @foreach(['Active', 'Maintenance', 'Broken'] as $status)
                                <option value="{{ $status }}" @selected(old('status') == $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card title="Kapasitas & Daya" subtitle="Data untuk perhitungan HPP rill" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="power_kw" label="Daya Listrik (kW)" type="number" step="0.01" placeholder="5.5" hint="Untuk hitung biaya listrik per jam" />
                    <x-atelier.input name="capacity_per_hour" label="Kapasitas per Jam" type="number" step="0.01" placeholder="100" hint="Produk per jam" />
                </div>
            </x-atelier.card>

            <x-atelier.card title="Investasi & Depresiasi" subtitle="Data untuk hitung biaya mesin per jam" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="purchase_date" label="Tanggal Beli" type="date" />
                    <x-atelier.input name="purchase_price" label="Harga Beli (Rp)" type="number" placeholder="100000000" />
                    <x-atelier.input name="useful_life_years" label="Umur Ekonomis (tahun)" type="number" placeholder="5" value="5" />
                    <x-atelier.input name="salvage_value" label="Nilai Sisa (Rp)" type="number" placeholder="10000000" />
                    <x-atelier.input name="maintenance_cost_per_month" label="Biaya Maintenance / Bulan (Rp)" type="number" placeholder="500000" />
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                    <label for="is_active" class="text-sm text-navy-300">Machine Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan tambahan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.machines.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Machine</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>