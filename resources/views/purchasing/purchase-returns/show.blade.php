<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Purchase Return</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4D"
            title="{{ $item->return_number }}"
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
                        <x-atelier.button :href="route('purchasing.purchase-returns.edit', $item)" variant="secondary" size="sm">
                            Edit Return
                        </x-atelier.button>
                    @endif

                    @if($item->canComplete())
                        <form method="POST" action="{{ route('purchasing.purchase-returns.complete', $item) }}" class="inline"
                              onsubmit="return confirm('Konfirmasi return? Stok akan otomatis berkurang.')">
                            @csrf
                            @method('PATCH')
                            <x-atelier.button type="submit" variant="primary" size="sm">
                                Konfirmasi Return
                            </x-atelier.button>
                        </form>
                    @endif

                    @if($item->status === 'draft')
                        <form method="POST" action="{{ route('purchasing.purchase-returns.destroy', $item) }}" class="inline"
                              onsubmit="return confirm('Yakin hapus return ini?')">
                            @csrf
                            @method('DELETE')
                            <x-atelier.button type="submit" variant="danger" size="sm">
                                Hapus
                            </x-atelier.button>
                        </form>
                    @endif

                    <x-atelier.button :href="route('purchasing.purchase-returns.index')" variant="ghost" size="sm">
                        Kembali
                    </x-atelier.button>
                </div>
            </div>
        </x-atelier.card>

        <x-atelier.card title="Informasi Return" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">No. Return</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->return_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Supplier</p>
                    <p class="text-sm text-white font-medium">{{ $item->supplier->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal Return</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->date) }}</p>
                </div>
                @if($item->goodsReceipt)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Ref GR</p>
                        <a href="{{ route('purchasing.goods-receipts.show', $item->goodsReceipt) }}"
                           class="text-sm text-gold-500 hover:text-gold-400 font-mono">
                            {{ $item->goodsReceipt->gr_number }} →
                        </a>
                    </div>
                @endif
                @if($item->purchaseOrder)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Ref PO</p>
                        <a href="{{ route('purchasing.purchase-orders.show', $item->purchaseOrder) }}"
                           class="text-sm text-gold-500 hover:text-gold-400 font-mono">
                            {{ $item->purchaseOrder->po_number }} →
                        </a>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dibuat Oleh</p>
                    <p class="text-sm text-navy-300">{{ $item->createdBy->name ?? '-' }}</p>
                </div>
                @if($item->completed_at)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dikonfirmasi</p>
                        <p class="text-sm text-navy-300">{{ format_tanggal_waktu($item->completed_at) }}</p>
                    </div>
                @endif
                @if($item->reason)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Alasan Return</p>
                        <p class="text-sm text-navy-300">{{ $item->reason }}</p>
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

        <x-atelier.card title="Item Return" :brackets="true" padding="p-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-navy-950/50 border-b border-navy-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material</th>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Lokasi</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Harga</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total</th>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-800">
                        @foreach($item->items as $returnItem)
                            <tr>
                                <td class="px-5 py-3 text-sm text-white font-medium">
                                    {{ $returnItem->material->nama ?? '-' }}
                                    <br><span class="text-xs text-navy-500 font-mono">{{ $returnItem->material->kode ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-navy-300">
                                    {{ $returnItem->location->nama ?? '-' }}
                                    <br><span class="text-xs text-navy-500">{{ $returnItem->location->warehouse->nama ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-red-400">
                                    {{ format_angka($returnItem->qty, 2) }}
                                    <span class="text-xs text-navy-500">{{ $returnItem->material->unit ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-navy-300">
                                    {{ format_rupiah($returnItem->unit_price) }}
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-white font-semibold">
                                    {{ format_rupiah($returnItem->total_value) }}
                                </td>
                                <td class="px-5 py-3 text-sm text-navy-300">
                                    {{ $returnItem->reason ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-atelier.card>

        <x-atelier.card title="Ringkasan" :brackets="true">
            <div class="max-w-md ml-auto">
                <div class="flex justify-between text-lg pt-3">
                    <span class="text-white font-semibold">Total Return Value</span>
                    <span class="text-red-400 font-mono font-bold">{{ format_rupiah($item->total_value) }}</span>
                </div>
            </div>
        </x-atelier.card>

        @if($item->status === 'completed')
            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3 text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium">Return sudah dikonfirmasi</p>
                        <p class="text-xs text-navy-400">Stok sudah otomatis berkurang di Inventory</p>
                    </div>
                </div>
            </x-atelier.card>
        @endif

    </div>
</x-app-layout>