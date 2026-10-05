<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Warehouse</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4A"
            title="{{ $item->nama }}"
            subtitle="Kode: {{ $item->kode }}"
            action="Edit"
            :actionUrl="route('warehouse.warehouses.edit', $item)"
        />

        <x-atelier.card title="Informasi Warehouse" :brackets="true">
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
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Type</p>
                    <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $item->type_color }}">
                        {{ $item->type }}
                    </span>
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
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">PIC</p>
                    <p class="text-sm text-navy-300">{{ $item->pic_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Telepon</p>
                    <p class="text-sm text-navy-300">{{ $item->phone ?? '-' }}</p>
                </div>
                @if($item->address)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Alamat</p>
                        <p class="text-sm text-navy-300">{{ $item->address }}</p>
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

        {{-- Placeholder untuk Location --}}
        <x-atelier.card title="Lokasi / Rak" subtitle="Akan tersedia setelah modul Location selesai" :brackets="true">
            <div class="p-8 text-center border border-dashed border-navy-700 rounded-lg">
                <p class="text-sm text-navy-400">Belum ada lokasi. Akan dibuat di M4A bagian berikutnya.</p>
            </div>
        </x-atelier.card>

        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('warehouse.warehouses.index')" variant="ghost">Kembali</x-atelier.button>
            <form method="POST" action="{{ route('warehouse.warehouses.destroy', $item) }}" onsubmit="return confirm('Yakin hapus warehouse ini?')">
                @csrf
                @method('DELETE')
                <x-atelier.button type="submit" variant="danger">Hapus Warehouse</x-atelier.button>
            </form>
        </div>

    </div>
</x-app-layout>