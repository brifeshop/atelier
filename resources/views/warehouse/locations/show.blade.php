<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Location</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4A"
            title="{{ $item->nama }}"
            subtitle="Kode: {{ $item->kode }}"
            action="Edit"
            :actionUrl="route('warehouse.locations.edit', $item)"
        />

        <x-atelier.card title="Informasi Location" :brackets="true">
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
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Warehouse</p>
                    <p class="text-sm text-navy-300">{{ $item->warehouse->nama ?? '-' }}</p>
                    @if($item->warehouse)
                        <p class="text-xs text-navy-500 font-mono">{{ $item->warehouse->kode }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Capacity</p>
                    <p class="text-sm text-navy-300">{{ $item->capacity ? format_angka($item->capacity, 2) : '-' }}</p>
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
                @if($item->notes)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                        <p class="text-sm text-navy-300">{{ $item->notes }}</p>
                    </div>
                @endif
            </div>
        </x-atelier.card>

        {{-- Placeholder untuk Inventory --}}
        <x-atelier.card title="Stok di Lokasi Ini" subtitle="Akan tersedia setelah modul Inventory selesai" :brackets="true">
            <div class="p-8 text-center border border-dashed border-navy-700 rounded-lg">
                <p class="text-sm text-navy-400">Belum ada data stok. Akan dibuat di M4A bagian berikutnya.</p>
            </div>
        </x-atelier.card>

        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('warehouse.locations.index')" variant="ghost">Kembali</x-atelier.button>
            <form method="POST" action="{{ route('warehouse.locations.destroy', $item) }}" onsubmit="return confirm('Yakin hapus location ini?')">
                @csrf
                @method('DELETE')
                <x-atelier.button type="submit" variant="danger">Hapus Location</x-atelier.button>
            </form>
        </div>

    </div>
</x-app-layout>