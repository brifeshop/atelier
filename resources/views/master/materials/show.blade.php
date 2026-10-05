<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Material</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="{{ $item->nama }}"
            subtitle="Kode: {{ $item->kode }}"
            action="Edit"
            :actionUrl="route('master.materials.edit', $item)"
        />

        {{-- FOTO --}}
        @if($item->photo_url)
            <x-atelier.card title="Foto Material" :brackets="true">
                <div class="flex justify-center">
                    <img src="{{ $item->photo_url }}"
                         alt="{{ $item->nama }}"
                         class="max-w-md w-full rounded-lg border border-navy-700">
                </div>
            </x-atelier.card>
        @endif

        {{-- INFORMASI --}}
        <x-atelier.card title="Informasi Material" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kode</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->kode }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Nama</p>
                    <p class="text-sm text-white font-medium">{{ $item->nama }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kategori</p>
                    <p class="text-sm text-navy-300">{{ $item->category ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Satuan</p>
                    <p class="text-sm text-navy-300">{{ $item->unit }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Harga per Unit</p>
                    <p class="text-sm text-white font-mono">{{ format_rupiah($item->price) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Lokasi</p>
                    <p class="text-sm text-navy-300">{{ $item->location ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Supplier</p>
                    <p class="text-sm text-navy-300">{{ $item->supplier->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status</p>
                    @if($item->is_active)
                        <span class="inline-flex items-center gap-1.5 text-xs text-green-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs text-navy-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-navy-500"></span>Nonaktif
                        </span>
                    @endif
                </div>
            </div>
        </x-atelier.card>

        {{-- STOK --}}
        <x-atelier.card title="Stok" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php $isLow = $item->current_stock <= $item->min_stock; @endphp
                <div class="bg-gradient-to-br {{ $isLow ? 'from-red-500/10 border-red-500/30' : 'from-gold-500/10 border-gold-500/30' }} to-navy-900/50 border rounded-lg p-5">
                    <p class="text-[10px] font-mono {{ $isLow ? 'text-red-400' : 'text-gold-500' }} uppercase tracking-widest mb-1">Stok Saat Ini</p>
                    <p class="font-serif text-2xl font-bold text-white">
                        {{ format_angka($item->current_stock, 1) }}
                        <span class="text-sm text-navy-400">{{ $item->unit }}</span>
                    </p>
                    @if($isLow)
                        <p class="text-xs text-red-400 mt-1">⚠ Di bawah minimum!</p>
                    @endif
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Stok Minimum</p>
                    <p class="font-serif text-2xl font-bold text-white">
                        {{ format_angka($item->min_stock, 1) }}
                        <span class="text-sm text-navy-400">{{ $item->unit }}</span>
                    </p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Stok Maksimum</p>
                    <p class="font-serif text-2xl font-bold text-white">
                        {{ $item->max_stock ? format_angka($item->max_stock, 1) : '-' }}
                        <span class="text-sm text-navy-400">{{ $item->unit }}</span>
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-navy-800">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-navy-400">Nilai Stok</span>
                    <span class="text-white font-mono">{{ format_rupiah($item->current_stock * $item->price) }}</span>
                </div>
            </div>
        </x-atelier.card>

        {{-- CATATAN --}}
        @if($item->notes)
            <x-atelier.card title="Catatan" :brackets="true">
                <p class="text-sm text-navy-300">{{ $item->notes }}</p>
            </x-atelier.card>
        @endif

        {{-- ACTIONS --}}
        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('master.materials.index')" variant="ghost">Kembali</x-atelier.button>
            <form method="POST" action="{{ route('master.materials.destroy', $item) }}" onsubmit="return confirm('Yakin hapus material ini?')">
                @csrf
                @method('DELETE')
                <x-atelier.button type="submit" variant="danger">Hapus Material</x-atelier.button>
            </form>
        </div>

    </div>
</x-app-layout>