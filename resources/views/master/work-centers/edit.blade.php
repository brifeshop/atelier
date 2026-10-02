<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Work Center</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Edit Work Center" subtitle="{{ $item->kode }} — {{ $item->nama }}" />

        <form method="POST" action="{{ route('master.work-centers.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Work Center" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Work Center" :value="$item->kode" required />
                    <x-atelier.input name="nama" label="Nama Work Center" :value="$item->nama" required />
                    <x-atelier.input name="location" label="Lokasi" :value="$item->location" />
                    <x-atelier.input name="hourly_rate" label="Tarif per Jam (Rp)" type="number" step="0.01" :value="$item->hourly_rate" />
                    <x-atelier.input name="capacity_per_hour" label="Kapasitas per Jam" type="number" step="0.01" :value="$item->capacity_per_hour" />

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
                    <label for="is_active" class="text-sm text-navy-300">Work Center Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.work-centers.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Perubahan</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>