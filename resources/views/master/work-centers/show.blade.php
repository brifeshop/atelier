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

        {{-- COSTING --}}
        <x-atelier.card title="Costing" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Labor Rate</p>
                    <p class="font-serif text-xl font-bold text-white">{{ format_rupiah($item->hourly_rate) }}</p>
                    <p class="text-xs text-navy-400 mt-1">per jam</p>
                </div>
                <div class="bg-gradient-to-br from-orange-500/10 to-navy-900/50 border border-orange-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-orange-400 uppercase tracking-widest mb-1">Overhead Rate</p>
                    <p class="font-serif text-xl font-bold text-white">{{ format_rupiah($item->overhead_rate) }}</p>
                    <p class="text-xs text-navy-400 mt-1">per jam</p>
                </div>
                <div class="bg-gradient-to-br from-green-500/10 to-navy-900/50 border border-green-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Total Rate</p>
                    <p class="font-serif text-xl font-bold text-white">{{ format_rupiah($item->total_rate_per_hour) }}</p>
                    <p class="text-xs text-navy-400 mt-1">per jam</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mt-6 pt-6 border-t border-navy-800">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kapasitas / Jam</p>
                    <p class="text-sm text-navy-300">{{ $item->capacity_per_hour ? format_angka($item->capacity_per_hour, 2) . ' unit' : '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Setup Time Default</p>
                    <p class="text-sm text-navy-300">{{ $item->setup_time_default ? format_angka($item->setup_time_default, 2) . ' menit' : '-' }}</p>
                </div>
            </div>
        </x-atelier.card>

        {{-- CATATAN --}}
        @if($item->notes)
            <x-atelier.card title="Catatan" :brackets="true">
                <p class="text-sm text-navy-300 whitespace-pre-line">{{ $item->notes }}</p>
            </x-atelier.card>
        @endif

        {{-- PLACEHOLDER: ROUTING STEPS --}}
        <x-atelier.card title="Digunakan di Routing" subtitle="Routing step yang pakai work center ini" :brackets="true">
            @php
                $steps = \App\Models\Engineering\RoutingStep::with(['routing.product'])
                    ->where('work_center_id', $item->id)
                    ->take(10)
                    ->get();
            @endphp

            @if($steps->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Routing</th>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Produk</th>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Operasi</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($steps as $step)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-4 py-3 text-sm font-mono text-gold-500">{{ $step->routing->kode ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-navy-300">{{ $step->routing->product->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-white">{{ $step->operation_name }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-white">{{ format_rupiah($step->total_cost) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center border border-dashed border-navy-700 rounded-lg">
                    <p class="text-sm text-navy-400">Belum ada routing step yang pakai work center ini.</p>
                </div>
            @endif
        </x-atelier.card>

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