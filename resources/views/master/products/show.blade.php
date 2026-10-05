<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Product</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="{{ $item->nama }}"
            subtitle="Kode: {{ $item->kode }}"
            action="Edit"
            :actionUrl="route('master.products.edit', $item)"
        />

        {{-- FOTO --}}
        @if($item->photo_url)
            <x-atelier.card title="Foto Product" :brackets="true">
                <div class="flex justify-center">
                    <img src="{{ $item->photo_url }}"
                         alt="{{ $item->nama }}"
                         class="max-w-md w-full rounded-lg border border-navy-700">
                </div>
            </x-atelier.card>
        @endif

        {{-- INFORMASI --}}
        <x-atelier.card title="Informasi Produk" :brackets="true">
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
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Harga Jual</p>
                    <p class="text-sm text-white font-mono">{{ format_rupiah($item->selling_price) }}</p>
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
                @if($item->description)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Deskripsi</p>
                        <p class="text-sm text-navy-300">{{ $item->description }}</p>
                    </div>
                @endif
                @if($item->notes)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                        <p class="text-sm text-navy-300">{{ $item->notes }}</p>
                    </div>
                @endif
            </div>
        </x-atelier.card>

        {{-- Placeholder untuk BOM nanti --}}
        <x-atelier.card title="Bill of Materials" subtitle="Akan tersedia setelah modul BOM selesai" :brackets="true">
            <div class="p-8 text-center border border-dashed border-navy-700 rounded-lg">
                <p class="text-sm text-navy-400">BOM belum tersedia. Akan dibuat di M5.</p>
            </div>
        </x-atelier.card>

        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('master.products.index')" variant="ghost">Kembali</x-atelier.button>
            <form method="POST" action="{{ route('master.products.destroy', $item) }}" onsubmit="return confirm('Yakin hapus product ini?')">
                @csrf
                @method('DELETE')
                <x-atelier.button type="submit" variant="danger">Hapus Product</x-atelier.button>
            </form>
        </div>

    </div>
</x-app-layout>