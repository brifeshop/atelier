<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Work Center</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="{{ $item->nama }}"
            subtitle="Kode: {{ $item->kode }}"
            action="Edit"
            :actionUrl="route('master.work-centers.edit', $item)"
        />

        <x-atelier.card title="Informasi Work Center" :brackets="true">
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
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Lokasi</p>
                    <p class="text-sm text-navy-300">{{ $item->location ?? '-' }}</p>
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
            </div>
        </x-atelier.card>

        <x-atelier.card title="Tarif & Kapasitas" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Tarif per Jam</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->hourly_rate) }}</p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kapasitas per Jam</p>
                    <p class="font-serif text-2xl font-bold text-white">
                        {{ $item->capacity_per_hour ? format_angka($item->capacity_per_hour, 1) : '-' }}
                    </p>
                </div>
            </div>
        </x-atelier.card>

        @if($item->notes)
            <x-atelier.card title="Catatan" :brackets="true">
                <p class="text-sm text-navy-300">{{ $item->notes }}</p>
            </x-atelier.card>
        @endif

        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('master.work-centers.index')" variant="ghost">Kembali</x-atelier.button>
            <form method="POST" action="{{ route('master.work-centers.destroy', $item) }}" onsubmit="return confirm('Yakin hapus work center ini?')">
                @csrf
                @method('DELETE')
                <x-atelier.button type="submit" variant="danger">Hapus Work Center</x-atelier.button>
            </form>
        </div>

    </div>
</x-app-layout>