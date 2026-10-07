<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Tambah Work Center</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Tambah Work Center" subtitle="Isi data stasiun kerja baru" />

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

        <form method="POST" action="{{ route('master.work-centers.store') }}">
            @csrf

            <x-atelier.card title="Informasi Work Center" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="kode" label="Kode Work Center" :value="$generatedKode" required hint="Kode otomatis, bisa diubah" />
                    <x-atelier.input name="nama" label="Nama Work Center" placeholder="Assembly Station" required />
                    <x-atelier.input name="location" label="Lokasi" placeholder="Workshop A - Lantai 1" />
                    <x-atelier.input name="capacity_per_hour" label="Kapasitas per Jam" type="number" step="0.01" placeholder="10" hint="Produk per jam" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Deskripsi</label>
                        <textarea name="description" rows="3" placeholder="Deskripsi stasiun kerja..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('description') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            {{-- COSTING --}}
            <x-atelier.card title="Costing" subtitle="Digunakan untuk hitung HPP produksi" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <x-atelier.input 
                        name="hourly_rate" 
                        label="Tarif Labor per Jam (Rp)" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        placeholder="50000" 
                        hint="Biaya operator per jam" 
                        required
                    />
                    <x-atelier.input 
                        name="overhead_rate" 
                        label="Tarif Overhead per Jam (Rp)" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        placeholder="30000" 
                        hint="Listrik, depresiasi mesin, dll" 
                    />
                    <x-atelier.input 
                        name="setup_time_default" 
                        label="Setup Time Default (menit)" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        placeholder="10" 
                        hint="Waktu persiapan default" 
                    />
                </div>

                <div class="mt-5 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-xs text-blue-300">
                            <p class="font-semibold mb-1">Cara Hitung Cost:</p>
                            <ul class="list-disc list-inside space-y-0.5 text-blue-400">
                                <li><strong>Labor Cost</strong> = (Setup + Run time) / 60 × Tarif Labor</li>
                                <li><strong>Overhead Cost</strong> = (Setup + Run time) / 60 × Tarif Overhead</li>
                                <li><strong>Total</strong> = Labor + Overhead</li>
                            </ul>
                            <p class="mt-2 text-blue-400">Contoh: Setup 10m + Run 5m = 15m = 0.25 jam</p>
                            <p class="text-blue-400">→ Labor = 0.25 × Rp 50.000 = Rp 12.500</p>
                            <p class="text-blue-400">→ Overhead = 0.25 × Rp 30.000 = Rp 7.500</p>
                            <p class="text-blue-400">→ Total = Rp 20.000</p>
                        </div>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                    <label for="is_active" class="text-sm text-navy-300">Work Center Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan tambahan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.work-centers.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Work Center</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>