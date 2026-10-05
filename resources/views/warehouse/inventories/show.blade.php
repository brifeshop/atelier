<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Inventory</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4A"
            title="{{ $item->item->nama ?? 'Item' }}"
            subtitle="Kode: {{ $item->item->kode ?? '-' }}"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <x-atelier.card title="Informasi Item" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Item</p>
                    <p class="text-sm text-white font-medium">{{ $item->item->nama ?? '-' }}</p>
                    <p class="text-xs font-mono text-gold-500">{{ $item->item->kode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Type</p>
                    <p class="text-sm text-navy-300">{{ ucfirst($item->item_type) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Lokasi</p>
                    <p class="text-sm text-white">{{ $item->location->nama ?? '-' }}</p>
                    <p class="text-xs text-navy-500">{{ $item->location->warehouse->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Last Movement</p>
                    <p class="text-sm text-navy-300">{{ $item->last_movement_at ? format_tanggal_waktu($item->last_movement_at) : '-' }}</p>
                </div>
            </div>
        </x-atelier.card>

        {{-- STOK --}}
        <x-atelier.card title="Stok" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br {{ $item->is_low_stock ? 'from-red-500/10 border-red-500/30' : 'from-gold-500/10 border-gold-500/30' }} to-navy-900/50 border rounded-lg p-5">
                    <p class="text-[10px] font-mono {{ $item->is_low_stock ? 'text-red-400' : 'text-gold-500' }} uppercase tracking-widest mb-1">Stok Saat Ini</p>
                    <p class="font-serif text-3xl font-bold text-white">
                        {{ format_angka($item->qty, 2) }}
                    </p>
                    @if($item->is_low_stock)
                        <p class="text-xs text-red-400 mt-1">⚠ Di bawah minimum!</p>
                    @endif
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Stok Minimum</p>
                    <p class="font-serif text-3xl font-bold text-white">
                        {{ $item->min_stock ? format_angka($item->min_stock, 2) : '-' }}
                    </p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Stok Maksimum</p>
                    <p class="font-serif text-3xl font-bold text-white">
                        {{ $item->max_stock ? format_angka($item->max_stock, 2) : '-' }}
                    </p>
                </div>
            </div>
        </x-atelier.card>

        {{-- FORM UPDATE MIN/MAX --}}
        <x-atelier.card title="Setting Min/Max Stock" subtitle="Atur batas stok minimum dan maksimum" :brackets="true">
            <form method="POST" action="{{ route('warehouse.inventories.update', $item) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-atelier.input name="min_stock" label="Stok Minimum" type="number" step="0.01" :value="$item->min_stock" hint="Alert kalau stok ≤ ini" />
                    <x-atelier.input name="max_stock" label="Stok Maksimum" type="number" step="0.01" :value="$item->max_stock" />
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button type="submit" variant="primary">Simpan Setting</x-atelier.button>
                </div>
            </form>
        </x-atelier.card>

        {{-- Placeholder Stock Movement --}}
        <x-atelier.card title="Riwayat Pergerakan" subtitle="Akan tersedia setelah modul Stock Movement selesai" :brackets="true">
            <div class="p-8 text-center border border-dashed border-navy-700 rounded-lg">
                <p class="text-sm text-navy-400">Riwayat pergerakan akan muncul di sini.</p>
            </div>
        </x-atelier.card>

        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('warehouse.inventories.index')" variant="ghost">Kembali</x-atelier.button>
        </div>

    </div>
</x-app-layout>