<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Material</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="{{ $item->nama }}"
            subtitle="{{ $item->kode_bahan ?? $item->kode }}"
            action="Edit"
            :actionUrl="route('master.materials.edit', $item)"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- INFO UTAMA --}}
        <x-atelier.card title="Informasi Material" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kode Sistem</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->kode }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kode Internal</p>
                    <p class="text-sm text-navy-300 font-mono">{{ $item->kode_bahan ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Nama</p>
                    <p class="text-sm text-white font-medium">{{ $item->nama }}</p>
                </div>
                @if($item->spesifikasi)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Spesifikasi</p>
                        <p class="text-sm text-navy-300">{{ $item->spesifikasi }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kategori</p>
                    <p class="text-sm text-navy-300">{{ $item->category ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Lokasi Default</p>
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
                @if($item->notes)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                        <p class="text-sm text-navy-300">{{ $item->notes }}</p>
                    </div>
                @endif
            </div>
        </x-atelier.card>

        {{-- COSTING CONFIGURATION --}}
        <x-atelier.card title="Costing Configuration" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-2">Costing Method</p>
                    <span class="inline-flex items-center text-xs px-3 py-1 rounded-full border {{ $item->costing_method_color }}">
                        {{ $item->costing_method_label }}
                    </span>
                    <p class="text-xs text-navy-500 mt-2">{{ $item->costing_method_description }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Satuan Beli</p>
                    <p class="text-sm text-white font-medium">{{ $item->unit }}</p>
                    <p class="text-xs text-navy-500 mt-1">Dimensi: {{ $item->standard_size_label }}</p>
                </div>
            </div>

            {{-- COST CALCULATION --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-navy-800">
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Harga Beli</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->price) }}</p>
                    <p class="text-xs text-navy-400 mt-1">per {{ $item->unit }}</p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Yield</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ number_format($item->yield_percent ?? 100, 0) }}%</p>
                    <p class="text-xs text-navy-400 mt-1">
                        @if(($item->yield_percent ?? 100) >= 100)
                            Tanpa waste
                        @else
                            Waste {{ number_format(100 - $item->yield_percent, 0) }}%
                        @endif
                    </p>
                </div>
                <div class="bg-gradient-to-br from-blue-500/10 to-navy-900/50 border border-blue-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-blue-400 uppercase tracking-widest mb-1">Harga per Satuan Dasar</p>
                    <p class="font-serif text-xl font-bold text-white">
                        Rp {{ number_format($item->price_per_base_unit, 4, ',', '.') }}
                    </p>
                    <p class="text-xs text-navy-400 mt-1">{{ $item->base_unit ?? 'per unit' }}</p>
                </div>
            </div>

            {{-- DIMENSI STANDAR --}}
            @if($item->needs_dimension || $item->needs_weight)
                <div class="mt-6 pt-6 border-t border-navy-800">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-3">Dimensi Standar</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @if($item->panjang_standar)
                            <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-3">
                                <p class="text-[10px] text-navy-500 mb-1">Panjang</p>
                                <p class="text-sm text-white font-mono">{{ number_format($item->panjang_standar, 2) }} mm</p>
                            </div>
                        @endif
                        @if($item->lebar_standar)
                            <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-3">
                                <p class="text-[10px] text-navy-500 mb-1">Lebar</p>
                                <p class="text-sm text-white font-mono">{{ number_format($item->lebar_standar, 2) }} mm</p>
                            </div>
                        @endif
                        @if($item->tinggi_standar)
                            <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-3">
                                <p class="text-[10px] text-navy-500 mb-1">Tinggi/Tebal</p>
                                <p class="text-sm text-white font-mono">{{ number_format($item->tinggi_standar, 2) }} mm</p>
                            </div>
                        @endif
                        @if($item->berat_standar)
                            <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-3">
                                <p class="text-[10px] text-navy-500 mb-1">Berat</p>
                                <p class="text-sm text-white font-mono">{{ number_format($item->berat_standar, 2) }} gram</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </x-atelier.card>

        {{-- STOK --}}
        <x-atelier.card title="Informasi Stok" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Stok Saat Ini</p>
                    <p class="font-serif text-2xl font-bold text-white">
                        {{ format_angka($item->current_stock ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-navy-400 mt-1">{{ $item->unit }}</p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Min Stock</p>
                    <p class="font-serif text-xl font-bold text-white">
                        {{ $item->min_stock ? format_angka($item->min_stock, 2) : '-' }}
                    </p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Max Stock</p>
                    <p class="font-serif text-xl font-bold text-white">
                        {{ $item->max_stock ? format_angka($item->max_stock, 2) : '-' }}
                    </p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Nilai Stok</p>
                    <p class="font-serif text-xl font-bold text-white">
                        {{ format_rupiah(($item->current_stock ?? 0) * ($item->price ?? 0)) }}
                    </p>
                </div>
            </div>
        </x-atelier.card>

        {{-- DIMENSI PAKAI (kalau ada BOM items) --}}
        @php
            $bomUsage = \App\Models\Engineering\BomItem::with('bom.product')
                ->where('item_type', 'material')
                ->where('item_id', $item->id)
                ->take(10)
                ->get();
        @endphp

        @if($bomUsage->count() > 0)
            <x-atelier.card title="Digunakan di BOM" subtitle="Material ini dipakai di BOM berikut" :brackets="true" padding="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">BOM</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Produk</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Dimensi Pakai</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($bomUsage as $bomItem)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm font-mono text-gold-500">{{ $bomItem->bom->kode ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm text-navy-300">{{ $bomItem->bom->product->nama ?? '-' }}</td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white">
                                        {{ format_angka($bomItem->qty, 4) }} {{ $bomItem->unit }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-navy-300">
                                        {{ $bomItem->dimension_label }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white font-semibold">
                                        {{ format_rupiah($bomItem->total_cost) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-atelier.card>
        @endif

        {{-- PHOTO --}}
        @if($item->photo_url)
            <x-atelier.card title="Foto Material" :brackets="true">
                <div class="max-w-md">
                    <img src="{{ $item->photo_url }}" alt="{{ $item->nama }}" class="rounded-lg border border-navy-700">
                </div>
            </x-atelier.card>
        @endif

        {{-- ACTIONS --}}
        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('master.materials.index')" variant="ghost">Kembali</x-atelier.button>
            <div class="flex gap-3">
                <x-atelier.button :href="route('master.materials.edit', $item)" variant="secondary">Edit</x-atelier.button>
                <form method="POST" action="{{ route('master.materials.destroy', $item) }}" onsubmit="return confirm('Yakin hapus material ini?')">
                    @csrf
                    @method('DELETE')
                    <x-atelier.button type="submit" variant="danger">Hapus</x-atelier.button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>