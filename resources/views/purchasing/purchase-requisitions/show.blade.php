<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Purchase Requisition</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4B"
            title="{{ $item->pr_number }}"
            subtitle="PR dari {{ $item->requestedBy->name ?? '-' }}"
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
                        <x-atelier.button :href="route('purchasing.purchase-requisitions.edit', $item)" variant="secondary" size="sm">
                            Edit PR
                        </x-atelier.button>
                    @endif

                    @if($item->canApprove())
                        <form method="POST" action="{{ route('purchasing.purchase-requisitions.approve', $item) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <x-atelier.button type="submit" variant="primary" size="sm">
                                Approve PR
                            </x-atelier.button>
                        </form>
                    @endif

                    @if($item->canCreatePO())
                        <x-atelier.button :href="route('purchasing.purchase-orders.create', ['from_pr' => $item->id])" variant="primary" size="sm">
                            Buat PO ({{ $item->pending_items_count }} item)
                        </x-atelier.button>
                    @endif

                    @if($item->status === 'draft')
                        <button onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition">
                            Reject
                        </button>
                    @endif

                    <x-atelier.button :href="route('purchasing.purchase-requisitions.index')" variant="ghost" size="sm">
                        Kembali
                    </x-atelier.button>
                </div>
            </div>
        </x-atelier.card>

        <x-atelier.card title="Informasi PR" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">No. PR</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->pr_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Requested By</p>
                    <p class="text-sm text-white font-medium">{{ $item->requestedBy->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Department</p>
                    <p class="text-sm text-navy-300">{{ $item->department ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal PR</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dibutuhkan Tanggal</p>
                    <p class="text-sm text-navy-300">{{ $item->needed_date ? format_tanggal($item->needed_date) : '-' }}</p>
                </div>
                @if($item->approved_by)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Approved By</p>
                        <p class="text-sm text-navy-300">{{ $item->approvedBy->name ?? '-' }}</p>
                        <p class="text-xs text-navy-500">{{ format_tanggal_waktu($item->approved_at) }}</p>
                    </div>
                @endif
                @if($item->rejection_reason)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-red-500 uppercase tracking-widest mb-1">Alasan Reject</p>
                        <p class="text-sm text-red-400">{{ $item->rejection_reason }}</p>
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

        {{-- RINGKASAN PROGRESS PO --}}
        @if($item->status === 'approved')
            <x-atelier.card title="Progress Purchase Order" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Total Item</p>
                        <p class="font-serif text-3xl font-bold text-white">{{ $item->items->count() }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500/10 to-navy-900/50 border border-green-500/30 rounded-lg p-5">
                        <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Sudah di-PO</p>
                        <p class="font-serif text-3xl font-bold text-white">{{ $item->ordered_items_count }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                        <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Belum di-PO</p>
                        <p class="font-serif text-3xl font-bold text-white">{{ $item->pending_items_count }}</p>
                    </div>
                </div>

                @if($item->is_fully_ordered)
                    <div class="mt-6 pt-6 border-t border-navy-800">
                        <div class="flex items-center gap-2 text-sm text-green-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium">Semua item sudah dibuat PO</span>
                        </div>
                    </div>
                @endif
            </x-atelier.card>
        @endif

        {{-- ITEM PERMINTAAN --}}
        <x-atelier.card title="Item Permintaan" :brackets="true" padding="p-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-navy-950/50 border-b border-navy-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty Minta</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty di-PO</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Sisa</th>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Status</th>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">PO</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-800">
                        @foreach($item->items as $prItem)
                            <tr>
                                <td class="px-5 py-3 text-sm text-white font-medium">
                                    {{ $prItem->material->nama ?? '-' }}
                                    <br><span class="text-xs text-navy-500 font-mono">{{ $prItem->material->kode ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono text-white">
                                    {{ format_angka($prItem->qty, 2) }}
                                    <span class="text-xs text-navy-500">{{ $prItem->material->unit ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono {{ $prItem->ordered_qty > 0 ? 'text-green-400' : 'text-navy-500' }}">
                                    {{ format_angka($prItem->ordered_qty, 2) }}
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-mono {{ $prItem->remaining_qty > 0 ? 'text-gold-500' : 'text-navy-500' }}">
                                    {{ format_angka($prItem->remaining_qty, 2) }}
                                </td>
                                <td class="px-5 py-3">
                                    @if($prItem->is_fully_ordered)
                                        <span class="inline-flex items-center gap-1.5 text-xs text-green-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Selesai
                                        </span>
                                    @elseif($prItem->ordered_qty > 0)
                                        <span class="inline-flex items-center gap-1.5 text-xs text-gold-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gold-500"></span>Partial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs text-navy-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-navy-500"></span>Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs">
                                    @forelse($prItem->purchaseOrderItems as $poItem)
                                        @if($poItem->purchaseOrder)
                                            <a href="{{ route('purchasing.purchase-orders.show', $poItem->purchaseOrder) }}"
                                               class="block text-gold-500 hover:text-gold-400 font-mono">
                                                {{ $poItem->purchaseOrder->po_number }}
                                            </a>
                                        @endif
                                    @empty
                                        <span class="text-navy-500">-</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-atelier.card>

        {{-- PO TERKAIT --}}
        @if($item->purchaseOrders && $item->purchaseOrders->count() > 0)
            <x-atelier.card title="Purchase Order Terkait" :brackets="true" padding="p-0">
                <div class="divide-y divide-navy-800">
                    @foreach($item->purchaseOrders as $po)
                        <a href="{{ route('purchasing.purchase-orders.show', $po) }}"
                           class="flex items-center justify-between px-5 py-3 hover:bg-navy-800/30 transition">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-mono text-gold-500">{{ $po->po_number }}</span>
                                <span class="text-sm text-white">{{ $po->supplier->nama ?? '-' }}</span>
                                <span class="text-xs text-navy-500">{{ format_tanggal($po->date) }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs px-2 py-0.5 rounded-full border {{ $po->status_color }}">
                                    {{ $po->status_label }}
                                </span>
                                <span class="text-xs text-navy-400">Lihat →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </x-atelier.card>
        @endif

    </div>

    {{-- REJECT MODAL --}}
    <div id="reject-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-navy-900 border border-navy-700 rounded-xl max-w-md w-full p-6">
            <h3 class="font-serif text-xl font-bold text-white mb-4">Reject PR</h3>
            <form method="POST" action="{{ route('purchasing.purchase-requisitions.reject', $item) }}">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                        Alasan Reject <span class="text-red-400">*</span>
                    </label>
                    <textarea name="rejection_reason" rows="3" required
                              class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-navy-300 hover:text-white transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-lg hover:bg-red-600 transition">
                        Reject PR
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>