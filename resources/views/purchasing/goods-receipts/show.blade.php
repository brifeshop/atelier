<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Goods Receipt</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4B"
            title="{{ $item->gr_number }}"
            subtitle="{{ $item->supplier->nama ?? '-' }}"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <x-atelier.card :brackets="true">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status</p>
                    <span class="inline-flex items-center text-sm px-3 py-1.5 rounded-full border {{ $item->status_color }}">
                        {{ $item->status_label }}
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if($item->canEdit())
                        <x-atelier.button :href="route('purchasing.goods-receipts.edit', $item)" variant="secondary" size="sm">
                            Edit GR
                        </x-atelier.button>
                    @endif

                    @if($item->canReceive())
                        <form method="POST" action="{{ route('purchasing.goods-receipts.receive', $item) }}" class="inline"
                              onsubmit="return confirm('Konfirmasi penerimaan? Stok akan otomatis bertambah.')">
                            @csrf
                            @method('PATCH')
                            <x-atelier.button type="submit" variant="primary" size="sm">
                                Konfirmasi Penerimaan
                            </x-atelier.button>
                        </form>
                    @endif

                    <x-atelier.button :href="route('purchasing.goods-receipts.index')" variant="ghost" size="sm">
                        Kembali
                    </x-atelier.button>
                </div>
            </div>
        </x-atelier.card>

        <x-atelier.card title="Informasi GR" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">No. GR</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->gr_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Supplier</p>
                    <p class="text-sm text-white font-medium">{{ $item->supplier->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">PO</p>
                    @if($item->purchaseOrder)
                        <a href="{{ route('purchasing.purchase-orders.show', $item->purchaseOrder) }}"
                           class="text-sm text-gold-500 hover:text-gold-400 font-mono">
                            {{ $item->purchaseOrder->po_number }} →
                        </a>
                    @else
                        <p class="text-sm text-navy-300">-</p>
                    @endif
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal Terima</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->date) }}</p>
                </div>
                @if($item->received_at)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dikonfirmasi</p>
                        <p class="text-sm text-navy-300">{{ format_tanggal_waktu($item->received_at) }}</p>
                        <p class="text-xs text-navy-500">{{ $item->receivedBy->name ?? '-' }}</p>
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

        <x-atelier.card title="Item Penerimaan" :brackets="true" padding="p-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-navy-950/50 border-b border-navy-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material</th>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Lokasi</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty Diterima</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty Reject</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Harga</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-800">
                        @foreach($item->items as $grItem)
                            <tr>
                                <td class="px-5 py-3 text-sm text-white font-medium">
                                    {{ $grItem->material->nama ?? '-' }}
                                    <br><span class="text-xs text-navy-500 font-mono">{{ $grItem->material->kode ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-navy-300">
                                    {{ $grItem->location->nama ?? '-' }}
                                    <br><span class="text-xs text-navy-500">{{ $grItem->location->warehouse->nama ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-green-400">
                                    {{ format_angka($grItem->qty_received, 2) }}
                                    <span class="text-xs text-navy-500">{{ $grItem->material->unit ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono {{ $grItem->qty_rejected > 0 ? 'text-red-400' : 'text-navy-500' }}">
                                    {{ format_angka($grItem->qty_rejected, 2) }}
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-navy-300">
                                    {{ format_rupiah($grItem->unit_price) }}
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-white font-semibold">
                                    {{ format_rupiah($grItem->total_value) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-atelier.card>

        @if($item->status === 'received')
            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3 text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium">Penerimaan sudah dikonfirmasi</p>
                        <p class="text-xs text-navy-400">Stok sudah otomatis bertambah di Inventory</p>
                    </div>
                </div>
            </x-atelier.card>
        @endif

    </div>
</x-app-layout>