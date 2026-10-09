<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Import BOM</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5A"
            title="Import BOM"
            subtitle="{{ $bom->kode }} — {{ $bom->product->nama ?? '-' }}"
        />

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- STEP 1: DOWNLOAD TEMPLATE --}}
        <x-atelier.card title="Langkah 1: Download Template" :brackets="true">
            <p class="text-sm text-navy-300 mb-4">
                Download template CSV, isi dengan data BOM Anda, lalu upload di langkah 2.
            </p>

            <x-atelier.button :href="route('engineering.boms.import.template', $bom)" variant="secondary">
                ⬇️ Download Template CSV
            </x-atelier.button>

            <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <p class="text-xs font-semibold text-blue-400 mb-2">📝 Petunjuk Pengisian:</p>
                <ul class="text-xs text-blue-300 space-y-1 list-disc list-inside">
                    <li><strong>header_group</strong> — <span class="text-gold-500">isi</span> kalau baris ini adalah <strong>header group</strong> (contoh: "Meja Pukul Palu"). <span class="text-navy-400">Kosongkan</span> kalau baris ini item biasa.</li>
                    <li><strong>nama_item</strong> — <span class="text-red-400">wajib</span> untuk item biasa (contoh: "Kayu Pinus")</li>
                    <li><strong>kode_bahan</strong> — kode internal material (contoh: A8-18-0) — <strong>prioritas match</strong></li>
                    <li><strong>kode</strong> — kode sistem material (contoh: MAT-0009) — fallback</li>
                    <li><strong>qty</strong> — jumlah pemakaian (wajib untuk item)</li>
                    <li><strong>unit</strong> — satuan pakai. Kalau kosong, auto dari material.</li>
                    <li><strong>level</strong> — L.1, L.2, ..., L.6</li>
                    <li><strong>divisi</strong> — Kayu, Offset Printing, Finishing, dll</li>
                    <li><strong>panjang_pakai, lebar_pakai, tinggi_pakai, berat_pakai</strong> — dimensi pakai (untuk costing advanced)</li>
                    <li><strong>scrap_percent</strong> — waste proses (0-100)</li>
                </ul>
            </div>

            <div class="mt-4 p-3 bg-gold-500/10 border border-gold-500/30 rounded-lg">
                <p class="text-xs font-semibold text-gold-500 mb-1">💡 Tentang Header Group:</p>
                <p class="text-xs text-gold-300">
                    Header group = <strong>baris tanpa item</strong>, hanya label untuk grouping. Cost tidak dihitung. Cocok untuk grouping visual tanpa sub-assy.
                </p>
            </div>
        </x-atelier.card>

        {{-- STEP 2: UPLOAD CSV --}}
        <x-atelier.card title="Langkah 2: Upload CSV" :brackets="true">
            <form method="POST" action="{{ route('engineering.boms.import.preview', $bom) }}" enctype="multipart/form-data">
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
                        <x-atelier.button :href="route('engineering.boms.show', $bom)" variant="ghost">Batal</x-atelier.button>
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
                        <li>Separator bisa <strong>koma (,) atau titik koma (;)</strong> — auto-detect</li>
                        <li>Material di-match by <strong>kode_bahan</strong> dulu, lalu <strong>kode</strong>, lalu <strong>nama</strong></li>
                        <li>BOM di-preview dulu sebelum import final</li>
                        <li>Material yang tidak ditemukan → baris error (bisa di-skip)</li>
                    </ul>
                </div>
            </div>
        </x-atelier.card>

    </div>
</x-app-layout>