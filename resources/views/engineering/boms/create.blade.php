<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Buat BOM</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M5A" title="Buat BOM" subtitle="Definisikan resep produk" />

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

        <form method="POST" action="{{ route('engineering.boms.store') }}">
            @csrf

            <x-atelier.card title="Informasi BOM" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input name="kode" label="Kode BOM" :value="$generatedKode" required hint="Auto-generate, bisa diubah" />

                    <x-atelier.input name="version" label="Versi" value="1.0" required hint="Contoh: 1.0, 1.1, 2.0" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Produk <span class="text-red-400">*</span>
                        </label>
                        <select name="product_id" required class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>
                                    {{ $p->kode }} — {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-atelier.input name="effective_date" label="Berlaku Mulai" type="date" :value="old('effective_date', date('Y-m-d'))" required />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan tambahan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex gap-3 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-300">
                        <p class="font-semibold mb-1">Langkah Berikutnya:</p>
                        <ol class="list-decimal list-inside space-y-0.5 text-blue-400">
                            <li>Setelah BOM dibuat, Anda akan diarahkan ke halaman detail</li>
                            <li>Tambahkan material / sub-assembly di sana</li>
                            <li>Sistem akan otomatis menghitung total cost</li>
                            <li>Aktifkan BOM jika sudah siap</li>
                        </ol>
                    </div>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('engineering.boms.index')" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary">Buat BOM</x-atelier.button>
            </div>

        </form>

    </div>
</x-app-layout>