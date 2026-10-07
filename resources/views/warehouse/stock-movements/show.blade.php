<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Movement</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4A"
            title="{{ $item->movement_number }}"
            subtitle="{{ $item->type_label }} — {{ format_tanggal($item->date) }}"
        />

        <x-atelier.card title="Informasi Movement" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">No. Movement</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->movement_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tipe</p>
                    <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $item->type_color }}">
                        {{ $item->type_label }}
                    </span>
                </div>
                @if($item->type === 'adjustment' && $item->adjustment_reason)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Alasan</p>
                        <p class="text-sm text-navy-300">{{ $item->adjustment_reason_label }}</p>
                    </div>
                @endif

                @if($item->stock_opname_id)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Referensi Opname</p>
                        <a href="{{ route('warehouse.stock-opnames.show', $item->stock_opname_id) }}" class="text-sm text-gold-500 hover:text-gold-400">
                            {{ $item->stockOpname->opname_number ?? '-' }}
                        </a>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Item</p>
                    <p class="text-sm text-white font-medium">{{ $item->item->nama ?? '-' }}</p>
                    <p class="text-xs text-gold-500 font-mono">{{ $item->item->kode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Lokasi</p>
                    <p class="text-sm text-navy-300">{{ $item->location->nama ?? '-' }}</p>
                    <p class="text-xs text-navy-500">{{ $item->location->warehouse->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dibuat Oleh</p>
                    <p class="text-sm text-navy-300">{{ $item->createdBy->name ?? '-' }}</p>
                </div>
                @if($item->reference_number)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Referensi</p>
                        <p class="text-sm text-navy-300">{{ $item->reference_number }}</p>
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

        <x-atelier.card title="Perubahan Stok" :brackets="true">
            <div class="grid grid-cols-3 gap-6">
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Sebelum</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_angka($item->qty_before, 2) }}</p>
                </div>
                <div class="bg-gradient-to-br {{ $item->qty > 0 ? 'from-green-500/10 border-green-500/30' : 'from-red-500/10 border-red-500/30' }} to-navy-900/50 border rounded-lg p-5">
                    <p class="text-[10px] font-mono {{ $item->qty > 0 ? 'text-green-400' : 'text-red-400' }} uppercase tracking-widest mb-1">Perubahan</p>
                    <p class="font-serif text-2xl font-bold {{ $item->qty > 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $item->qty > 0 ? '+' : '' }}{{ format_angka($item->qty, 2) }}
                    </p>
                </div>
                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Sesudah</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_angka($item->qty_after, 2) }}</p>
                </div>
            </div>

            @if($item->unit_price)
                <div class="mt-6 pt-6 border-t border-navy-800">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-navy-400">Harga per Unit</span>
                        <span class="text-white font-mono">{{ format_rupiah($item->unit_price) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-navy-400">Total Nilai</span>
                        <span class="text-gold-500 font-mono">{{ format_rupiah($item->total_value) }}</span>
                    </div>
                </div>
            @endif
        </x-atelier.card>

        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('warehouse.stock-movements.index')" variant="ghost">Kembali</x-atelier.button>
        </div>

    </div>
</x-app-layout>