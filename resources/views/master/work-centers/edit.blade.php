<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Work Center</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Edit Work Center" subtitle="{{ $item->kode }} — {{ $item->nama }}" />

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

        <form method="POST" action="{{ route('master.work-centers.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Work Center" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Work Center" :value="$item->kode" required />
                    <x-atelier.input name="nama" label="Nama Work Center" :value="$item->nama" required />
                    <x-atelier.input name="location" label="Lokasi" :value="$item->location" />
                    <x-atelier.input name="capacity_per_hour" label="Kapasitas per Jam" type="number" step="0.01" :value="$item->capacity_per_hour" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Deskripsi</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('description', $item->description) }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card title="Costing" subtitle="Digunakan untuk hitung HPP produksi" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <x-atelier.input 
                        name="hourly_rate" 
                        label="Tarif Labor per Jam (Rp)" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        :value="$item->hourly_rate" 
                        required
                    />
                    <x-atelier.input 
                        name="overhead_rate" 
                        label="Tarif Overhead per Jam (Rp)" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        :value="$item->overhead_rate" 
                    />
                    <x-atelier.input 
                        name="setup_time_default" 
                        label="Setup Time Default (menit)" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        :value="$item->setup_time_default" 
                    />
                </div>

                <div class="mt-5 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-xs text-blue-300">
                            <p class="font-semibold mb-1">Total Rate per Jam:</p>
                            <p class="text-lg font-bold text-white">
                                Rp {{ number_format(($item->hourly_rate ?? 0) + ($item->overhead_rate ?? 0), 0, ',', '.') }}
                            </p>
                            <p class="text-blue-400 mt-1">
                                (Labor: Rp {{ number_format($item->hourly_rate ?? 0, 0, ',', '.') }} + 
                                Overhead: Rp {{ number_format($item->overhead_rate ?? 0, 0, ',', '.') }})
                            </p>
                        </div>
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