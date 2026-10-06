<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Purchase Order</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4B"
            title="{{ $item->po_number }}"
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
                        <x-atelier.button :href="route('purchasing.purchase-orders.edit', $item)" variant="secondary" size="sm">
                            Edit PO
                        </x-atelier.button>
                    @endif

                    @if($item->canSend())
                        <form method="POST" action="{{ route('purchasing.purchase-orders.send', $item) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <x-atelier.button type="submit" variant="primary" size="sm">
                                Kirim ke Supplier
                            </x-atelier.button>
                        </form>
                    @endif

                    @if($item->canCreateGR())
                        <x-atelier.button :href="route('purchasing.goods-receipts.create', ['from_po' => $item->id])" variant="primary" size="sm">
                            Buat Goods Receipt
                        </x-atelier.button>
                    @endif

                    @if($item->canCancel())
                        <form method="POST" action="{{ route('purchasing.purchase-orders.cancel', $item) }}" class="inline"
                              onsubmit="return confirm('Yakin batalkan PO ini?')">
                            @csrf
                            @method('PATCH')
                            <x-atelier.button type="submit" variant="danger" size="sm">Batalkan</x-atelier.button>
                        </form>
                    @endif

                    <x-atelier.button :href="route('purchasing.purchase-orders.index')" variant="ghost" size="sm">
                        Kembali
                    </x-atelier.button>
                </div>
            </div>
        </x-atelier.card>

        <x-atelier.card title="Informasi PO" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">No. PO</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->po_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Supplier</p>
                    <p class="text-sm text-white font-medium">{{ $item->supplier->nama ?? '-' }}</p>
                    @if($item->supplier)
                        <p class="text-xs text-navy-500">{{ $item->supplier->kode }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal PO</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Target Kirim</p>
                    <p class="text-sm text-navy-300">{{ $item->delivery_date ? format_tanggal($item->delivery_date) : '-' }}</p>
                </div>
                @if($item->purchaseRequisition)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dari PR</p>
                        <a href="{{ route('purchasing.purchase-requisitions.show', $item->purchaseRequisition) }}"
                           class="text-sm text-gold-500 hover:text-gold-400 font-mono">
                            {{ $item->purchaseRequisition->pr_number }} →
                        </a>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dibuat Oleh</p>
                    <p class="text-sm text-navy-300">{{ $item->createdBy->name ?? '-' }}</p>
                </div>
                @if($item->sent_at)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dikirim</p>
                        <p class="text-sm text-navy-300">{{ format_tanggal_waktu($item->sent_at) }}</p>
                    </div>
                @endif
                @if($item->received_at)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Diterima</p>
                        <p class="text-sm text-navy-300">{{ format_tanggal_waktu($item->received_at) }}</p>
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

        {{-- ITEM PO --}}
        <x-atelier.card title="Item Pesanan" :brackets="true" padding="p-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-navy-950/50 border-b border-navy-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty Pesan</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty Diterima</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Harga</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Diskon</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-800">
                        @foreach($item->items as $poItem)
                            <tr>
                                <td class="px-5 py-3 text-sm text-white font-medium">
                                    {{ $poItem->material->nama ?? '-' }}
                                    @if($poItem->material)
                                        <br><span class="text-xs text-navy-500 font-mono">{{ $poItem->material->kode }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-white">
                                    {{ format_angka($poItem->qty, 2) }}
                                    <span class="text-xs text-navy-500">{{ $poItem->material->unit ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono {{ $poItem->is_fully_received ? 'text-green-400' : 'text-gold-500' }}">
                                    {{ format_angka($poItem->received_qty, 2) }}
                                    @if(!$poItem->is_fully_received)
                                        <br><span class="text-xs text-navy-500">Sisa: {{ format_angka($poItem->remaining_qty, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-navy-300">
                                    {{ format_rupiah($poItem->unit_price) }}
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-red-400">
                                    @if($poItem->discount_percent > 0)
                                        {{ $poItem->discount_percent }}%
                                        <br><span class="text-xs">- {{ format_rupiah($poItem->discount_amount) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-white font-semibold">
                                    {{ format_rupiah($poItem->subtotal) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-atelier.card>

        {{-- RINGKASAN --}}
        <x-atelier.card title="Ringkasan" :brackets="true">
            <div class="max-w-md ml-auto space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-navy-400">Subtotal</span>
                    <span class="text-white font-mono">{{ format_rupiah($item->subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-navy-400">Total Diskon</span>
                    <span class="text-red-400 font-mono">- {{ format_rupiah($item->discount_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-navy-400">Pajak</span>
                    <span class="text-white font-mono">{{ format_rupiah($item->tax_amount) }}</span>
                </div>
                <div class="flex justify-between text-lg pt-3 border-t border-navy-800">
                    <span class="text-white font-semibold">Total</span>
                    <span class="text-gold-500 font-mono font-bold">{{ format_rupiah($item->total_amount) }}</span>
                </div>
            </div>
        </x-atelier.card>

        {{-- GOODS RECEIPTS TERKAIT --}}
        @if($item->goodsReceipts && $item->goodsReceipts->count() > 0)
            <x-atelier.card title="Goods Receipt Terkait" :brackets="true" padding="p-0">
                <div class="divide-y divide-navy-800">
                    @foreach($item->goodsReceipts as $gr)
                        <a href="{{ route('purchasing.goods-receipts.show', $gr) }}"
                           class="flex items-center justify-between px-5 py-3 hover:bg-navy-800/30 transition">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-mono text-gold-500">{{ $gr->gr_number }}</span>
                                <span class="text-xs text-navy-500">{{ format_tanggal($gr->date) }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs px-2 py-0.5 rounded-full border {{ $gr->status_color }}">
                                    {{ $gr->status_label }}
                                </span>
                                <span class="text-xs text-navy-400">Lihat →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </x-atelier.card>
        @endif

    </div>
</x-app-layout>