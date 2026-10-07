<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Purchasing Dashboard</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4D"
            title="Purchasing Dashboard"
            subtitle="Ringkasan aktivitas purchasing"
        />

        {{-- STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <a href="{{ route('purchasing.purchase-requisitions.index', ['status' => 'draft']) }}"
               class="bg-navy-900/50 border border-navy-800 rounded-xl p-5 hover:border-gold-500/50 transition">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">PR Pending</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['pr_pending'] }}</p>
            </a>
            <a href="{{ route('purchasing.purchase-orders.index', ['status' => 'draft']) }}"
               class="bg-navy-900/50 border border-navy-800 rounded-xl p-5 hover:border-gold-500/50 transition">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">PO Draft</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['po_pending'] }}</p>
            </a>
            <a href="{{ route('purchasing.purchase-orders.index', ['status' => 'sent']) }}"
               class="bg-navy-900/50 border border-blue-500/30 rounded-xl p-5 hover:border-blue-500/50 transition">
                <p class="text-[10px] font-mono text-blue-400 uppercase tracking-widest mb-1">PO Sent</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['po_sent'] }}</p>
            </a>
            <a href="{{ route('purchasing.goods-receipts.index', ['status' => 'draft']) }}"
               class="bg-navy-900/50 border border-navy-800 rounded-xl p-5 hover:border-gold-500/50 transition">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">GR Draft</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['gr_draft'] }}</p>
            </a>
            <div class="bg-navy-900/50 border {{ $stats['total_rejected_7days'] > 0 ? 'border-red-500/50' : 'border-navy-800' }} rounded-xl p-5">
                <p class="text-[10px] font-mono {{ $stats['total_rejected_7days'] > 0 ? 'text-red-400' : 'text-navy-400' }} uppercase tracking-widest mb-1">Reject 7 Hari</p>
                <p class="font-serif text-3xl font-bold {{ $stats['total_rejected_7days'] > 0 ? 'text-red-400' : 'text-white' }}">{{ $stats['total_rejected_7days'] }}</p>
            </div>
        </div>

        {{-- ALERT REJECT --}}
        @if($recentRejects->count() > 0)
            <x-atelier.card title="⚠️ Barang Reject (7 Hari Terakhir)" :brackets="true" padding="p-0">
                <div class="divide-y divide-navy-800">
                    @foreach($recentRejects as $reject)
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-navy-800/30 transition">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-white font-medium">
                                        {{ $reject->material->nama ?? '-' }}
                                        <span class="text-xs text-navy-500 font-mono">{{ $reject->material->kode ?? '-' }}</span>
                                    </p>
                                    <p class="text-xs text-navy-400">
                                        Supplier: {{ $reject->goodsReceipt->supplier->nama ?? '-' }}
                                        · GR: <a href="{{ route('purchasing.goods-receipts.show', $reject->goodsReceipt) }}" class="text-gold-500 hover:text-gold-400">{{ $reject->goodsReceipt->gr_number ?? '-' }}</a>
                                        · {{ $reject->goodsReceipt->date ? format_tanggal($reject->goodsReceipt->date) : '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-mono text-red-400 font-semibold">
                                    -{{ format_angka($reject->qty_rejected, 2) }}
                                </p>
                                @if($reject->notes)
                                    <p class="text-xs text-navy-500">{{ Str::limit($reject->notes, 40) }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-atelier.card>
        @endif

    </div>
</x-app-layout>