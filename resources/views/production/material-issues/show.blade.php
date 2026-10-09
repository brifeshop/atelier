<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Material Issue</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5B"
            title="{{ $item->issue_number }}"
            subtitle="{{ $item->workOrder->wo_number ?? '-' }} — {{ format_tanggal($item->date) }}"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                {!! session('error') !!}
            </div>
        @endif

        {{-- INFO --}}
        <x-atelier.card title="Informasi Material Issue" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">No. MI</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->issue_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status</p>
                    <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $item->status_color }}">
                        {{ $item->status_label }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Gudang</p>
                    <p class="text-sm text-navy-300">{{ $item->warehouse->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Work Order</p>
                    <a href="{{ route('production.work-orders.show', $item->work_order_id) }}" 
                       class="text-sm text-gold-500 hover:text-gold-400 font-mono">
                        {{ $item->workOrder->wo_number ?? '-' }}
                    </a>
                    <p class="text-xs text-navy-500 mt-0.5">{{ $item->workOrder->product->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dibuat Oleh</p>
                    <p class="text-sm text-navy-300">{{ $item->issuer->name ?? '-' }}</p>
                </div>
                @if($item->notes)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                        <p class="text-sm text-navy-300">{{ $item->notes }}</p>
                    </div>
                @endif
            </div>
        </x-atelier.card>

        {{-- ITEMS --}}
        <x-atelier.card title="Material yang Diambil" :brackets="true" padding="p-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-navy-950/50 border-b border-navy-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Lokasi</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Unit Cost</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-800">
                        @foreach($item->items as $miItem)
                            <tr class="hover:bg-navy-800/30 transition">
                                <td class="px-5 py-3 text-sm">
                                    <span class="text-white font-medium">{{ $miItem->material->nama ?? '-' }}</span>
                                    <br><span class="text-xs font-mono text-navy-500">{{ $miItem->material->kode_bahan ?? $miItem->material->kode ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-sm text-white">
                                    {{ format_angka($miItem->qty, 4) }}
                                    <span class="text-xs text-navy-500">{{ $miItem->material->unit ?? '' }}</span>
                                </td>
                                <td class="px-5 py-3 text-sm text-navy-300">
                                    {{ $miItem->location->nama ?? '-' }}
                                    <br><span class="text-xs text-navy-500">{{ $miItem->location->warehouse->nama ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                    {{ format_rupiah($miItem->unit_cost) }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-sm text-white font-semibold">
                                    {{ format_rupiah($miItem->total_cost) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gold-500/10 border-t-2 border-gold-500/30">
                        <tr>
                            <td colspan="4" class="px-5 py-3 text-right text-sm text-gold-500 font-semibold uppercase tracking-wider">
                                Total
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-base text-white font-bold">
                                {{ format_rupiah($item->items->sum('total_cost')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-atelier.card>

        {{-- ACTIONS --}}
        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('production.material-issues.index')" variant="ghost">Kembali</x-atelier.button>

            <div class="flex gap-3">
                @if($item->status === 'draft')
                    <form method="POST" action="{{ route('production.material-issues.destroy', $item) }}" 
                          onsubmit="return confirm('Hapus Material Issue ini?')">
                        @csrf
                        @method('DELETE')
                        <x-atelier.button type="submit" variant="danger">Hapus</x-atelier.button>
                    </form>

                    <form method="POST" action="{{ route('production.material-issues.issue', $item) }}"
                          onsubmit="return confirm('Issue material ini? Stok akan berkurang.')">
                        @csrf
                        @method('PATCH')
                        <x-atelier.button type="submit" variant="primary">Issue Material</x-atelier.button>
                    </form>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>