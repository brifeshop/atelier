<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Machine</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Edit Machine" subtitle="{{ $item->kode }} — {{ $item->nama }}" />

        <form method="POST" action="{{ route('master.machines.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Mesin" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Machine" :value="$item->kode" required />
                    <x-atelier.input name="nama" label="Nama Mesin" :value="$item->nama" required />
                    <x-atelier.input name="type" label="Type" :value="$item->type" />
                    <x-atelier.input name="brand" label="Brand" :value="$item->brand" />
                    <x-atelier.input name="model" label="Model" :value="$item->model" />
                    <x-atelier.input name="serial_number" label="Serial Number" :value="$item->serial_number" />
                    <x-atelier.input name="location" label="Lokasi" :value="$item->location" />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            @foreach(['Active', 'Maintenance', 'Broken'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $item->status) == $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card title="Kapasitas & Daya" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="power_kw" label="Daya Listrik (kW)" type="number" step="0.01" :value="$item->power_kw" />
                    <x-atelier.input name="capacity_per_hour" label="Kapasitas per Jam" type="number" step="0.01" :value="$item->capacity_per_hour" />
                </div>
            </x-atelier.card>

            <x-atelier.card title="Investasi & Depresiasi" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="purchase_date" label="Tanggal Beli" type="date" :value="$item->purchase_date?->format('Y-m-d')" />
                    <x-atelier.input name="purchase_price" label="Harga Beli (Rp)" type="number" :value="$item->purchase_price" />
                    <x-atelier.input name="useful_life_years" label="Umur Ekonomis (tahun)" type="number" :value="$item->useful_life_years" />
                    <x-atelier.input name="salvage_value" label="Nilai Sisa (Rp)" type="number" :value="$item->salvage_value" />
                    <x-atelier.input name="maintenance_cost_per_month" label="Biaya Maintenance / Bulan (Rp)" type="number" :value="$item->maintenance_cost_per_month" />
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                    <label for="is_active" class="text-sm text-navy-300">Machine Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.machines.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Perubahan</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>