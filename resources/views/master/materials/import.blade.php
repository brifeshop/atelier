<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Import Material</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="Import Material"
            subtitle="Upload CSV untuk input masal"
        />

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- STEP 1: DOWNLOAD TEMPLATE --}}
        <x-atelier.card title="Langkah 1: Download Template" :brackets="true">
            <p class="text-sm text-navy-300 mb-4">
                Download template CSV, isi dengan data material Anda, lalu upload di langkah 2.
            </p>

            <x-atelier.button :href="route('master.materials.import.template')" variant="secondary">
                ⬇️ Download Template CSV
            </x-atelier.button>

            <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <p class="text-xs font-semibold text-blue-400 mb-2">📝 Petunjuk Pengisian:</p>
                <ul class="text-xs text-blue-300 space-y-1 list-disc list-inside">
                    <li><strong>kode</strong> — <span class="text-green-400">opsional</span>. Kosongkan untuk auto-generate (MAT-0001, MAT-0002, dst.), atau isi manual untuk kode custom.</li>
                    <li><strong>nama</strong> — <span class="text-red-400">wajib</span> (contoh: Kayu Pinus)</li>
                    <li><strong>kode_bahan</strong> — opsional (contoh: A8-18-0)</li>
                    <li><strong>unit</strong> — <span class="text-red-400">wajib</span> (pcs, lembar, roll, karung, kg, dll)</li>
                    <li><strong>costing_method</strong> — per_unit, per_area, per_volume, per_length, per_weight</li>
                    <li><strong>volume_type</strong> — <strong>kotak</strong> (untuk balok) atau <strong>cair</strong> (untuk cat, varnish, tinta). Hanya dipakai kalau costing_method = <code>per_volume</code>.</li>
                    <li><strong>panjang_standar, lebar_standar, tinggi_standar, berat_standar</strong> — wajib sesuai costing method</li>
                    <li><strong>volume_standar</strong> — wajib untuk <strong>per_volume + cair</strong> (dalam ml). Contoh: 5 liter = 5000</li>
                    <li><strong>price</strong> — angka tanpa titik/koma (contoh: 500000)</li>
                    <li><strong>yield_percent</strong> — 0-100 (default 100)</li>
                </ul>
            </div>

            <div class="mt-4 p-3 bg-green-500/10 border border-green-500/30 rounded-lg">
                <p class="text-xs font-semibold text-green-400 mb-1">✨ Auto-Generate Kode:</p>
                <p class="text-xs text-green-300">
                    Kalau kolom <code class="bg-navy-900 px-1 rounded text-gold-500">kode</code> dikosongkan, sistem akan otomatis generate kode format <strong>MAT-XXXX</strong> saat import. Sangat berguna untuk migrasi data dari Excel yang tidak punya kode sistem.
                </p>
            </div>
        </x-atelier.card>

        {{-- STEP 2: UPLOAD CSV --}}
        <x-atelier.card title="Langkah 2: Upload CSV" :brackets="true">
            <form method="POST" action="{{ route('master.materials.import.preview') }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            File CSV <span class="text-red-400">*</span>
                        </label>
                        <input type="file" 
                               name="file" 
                               accept=".csv,.txt"
                               required
                               class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:bg-gold-500 file:text-navy-900 file:font-semibold file:cursor-pointer hover:file:bg-gold-400 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <p class="text-xs text-navy-500 mt-2">Format: .csv atau .txt. Ukuran maksimal: 5 MB.</p>
                        @error('file')
                            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-navy-800">
                        <x-atelier.button :href="route('master.materials.index')" variant="ghost">Batal</x-atelier.button>
                        <x-atelier.button type="submit" variant="primary">Preview</x-atelier.button>
                    </div>
                </div>
            </form>
        </x-atelier.card>

        {{-- INFO --}}
        <x-atelier.card :brackets="true">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-gold-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-xs text-navy-300">
                    <p class="font-semibold text-white mb-1">💡 Tips:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-navy-400">
                        <li>Isi CSV dari Excel/Google Sheets → Save As → <strong>CSV UTF-8</strong></li>
                        <li>Baris pertama = header (jangan diubah)</li>
                        <li>Kode yang sudah ada akan di-skip (tidak overwrite)</li>
                        <li>Material di-preview dulu sebelum import final</li>
                        <li>Kalau ada baris error, tetap bisa import baris yang valid</li>
                    </ul>
                </div>
            </div>
        </x-atelier.card>

    </div>
</x-app-layout>