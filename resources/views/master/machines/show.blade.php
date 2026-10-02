<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Machine</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="{{ $item->nama }}"
            subtitle="Kode: {{ $item->kode }}"
            action="Edit"
            :actionUrl="route('master.machines.edit', $item)"
        />

        <x-atelier.card title="Informasi Mesin" :brackets="true">
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
                    <p class="text-sm text-navy-300">{{ $item->type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Brand / Model</p>
                    <p class="text-sm text-navy-300">{{ $item->brand ?? '-' }} / {{ $item->model ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Serial Number</p>
                    <p class="text-sm text-navy-300">{{ $item->serial_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Lokasi</p>
                    <p class="text-sm text-navy-300">{{ $item->location ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status</p>
                    @php
                        $statusColors = [
                            'Active' => 'text-green-400',
                            'Maintenance' => 'text-amber-400',
                            'Broken' => 'text-red-400',
                        ];
                    @endphp
                    <span class="text-sm {{ $statusColors[$item->status] ?? 'text-navy-300' }}">{{ $item->status }}</span>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status Aktif</p>
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

        <x-atelier.card title="Kapasitas & Daya" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Daya Listrik</p>
                    <p class="font-serif text-xl font-bold text-white">
                        {{ $item->power_kw ? format_angka($item->power_kw, 1) . ' kW' : '-' }}
                    </p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kapasitas per Jam</p>
                    <p class="font-serif text-xl font-bold text-white">
                        {{ $item->capacity_per_hour ? format_angka($item->capacity_per_hour, 1) : '-' }}
                    </p>
                </div>
            </div>
        </x-atelier.card>

        <x-atelier.card title="Investasi & Depresiasi" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Harga Beli</p>
                    <p class="font-serif text-lg font-bold text-white">{{ format_rupiah($item->purchase_price) }}</p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Nilai Sisa</p>
                    <p class="font-serif text-lg font-bold text-white">{{ format_rupiah($item->salvage_value) }}</p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Umur Ekonomis</p>
                    <p class="font-serif text-lg font-bold text-white">{{ $item->useful_life_years }} tahun</p>
                </div>
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Depresiasi per Jam</p>
                    <p class="font-serif text-lg font-bold text-white">{{ format_rupiah($item->depreciation_per_hour) }}</p>
                </div>
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Maintenance per Jam</p>
                    <p class="font-serif text-lg font-bold text-white">{{ format_rupiah($item->maintenance_per_hour) }}</p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Maintenance / Bulan</p>
                    <p class="font-serif text-lg font-bold text-white">{{ format_rupiah($item->maintenance_cost_per_month ?? 0) }}</p>
                </div>
            </div>

            @if($item->notes)
                <div class="mt-6 pt-6 border-t border-navy-800">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                    <p class="text-sm text-navy-300">{{ $item->notes }}</p>
                </div>
            @endif

            <div class="flex items-center justify-between gap-3 mt-6 pt-6 border-t border-navy-800">
                <x-atelier.button :href="route('master.machines.index')" variant="ghost">Kembali</x-atelier.button>
                <form method="POST" action="{{ route('master.machines.destroy', $item) }}" onsubmit="return confirm('Yakin hapus machine ini?')">
                    @csrf
                    @method('DELETE')
                    <x-atelier.button type="submit" variant="danger">Hapus Machine</x-atelier.button>
                </form>
            </div>
        </x-atelier.card>

    </div>
</x-app-layout>