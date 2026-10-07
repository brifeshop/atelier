<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Evaluasi Supplier</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4C"
            title="Evaluasi Supplier"
            subtitle="Penilaian performa supplier berdasarkan data transaksi"
            action="Buat Evaluasi"
            :actionUrl="route('purchasing.supplier-evaluations.create')"
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

        {{-- Statistik Rating --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">Total</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-green-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Rating A</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['rating_a'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-blue-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-blue-400 uppercase tracking-widest mb-1">Rating B</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['rating_b'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-yellow-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-yellow-400 uppercase tracking-widest mb-1">Rating C</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['rating_c'] }}</p>
            </div>
            <div class="bg-navy-900/50 border border-red-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-red-400 uppercase tracking-widest mb-1">Rating D</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $stats['rating_d'] }}</p>
            </div>
        </div>

        <x-atelier.card :brackets="true" padding="p-0">
            <div class="px-5 py-4 border-b border-navy-800">
                <form method="GET" action="{{ route('purchasing.supplier-evaluations.index') }}" class="flex gap-3 flex-wrap">
                    <select name="supplier_id" class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->nama }}</option>
                        @endforeach
                    </select>
                    <input
                        type="month"
                        name="period"
                        value="{{ request('period') }}"
                        class="px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                    >
                    <x-atelier.button type="submit" variant="secondary">Filter</x-atelier.button>
                    @if(request()->hasAny(['supplier_id', 'period']))
                        <x-atelier.button :href="route('purchasing.supplier-evaluations.index')" variant="ghost">Reset</x-atelier.button>
                    @endif
                </form>
            </div>

            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Supplier</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Periode</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Quality</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Delivery</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Price</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Reject</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total</th>
                                <th class="px-5 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Rating</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $item)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm text-white font-medium">
                                        {{ $item->supplier->nama ?? '-' }}
                                        @if($item->supplier)
                                            <br><span class="text-xs text-navy-500">{{ $item->supplier->kode }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-navy-300">
                                        {{ $item->period ?? '-' }}
                                        <br><span class="text-xs text-navy-500">
                                            {{ $item->period_start?->format('d/m/Y') }} - {{ $item->period_end?->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-right font-mono text-white">
                                        {{ number_format($item->quality_score, 1) }}%
                                    </td>
                                    <td class="px-5 py-3 text-sm text-right font-mono text-white">
                                        {{ number_format($item->delivery_score, 1) }}%
                                    </td>
                                    <td class="px-5 py-3 text-sm text-right font-mono text-white">
                                        {{ number_format($item->price_score, 1) }}%
                                    </td>
                                    <td class="px-5 py-3 text-sm text-right font-mono">
                                        <span class="{{ $item->reject_rate > 5 ? 'text-red-400 font-semibold' : 'text-navy-300' }}">
                                            {{ number_format($item->reject_rate, 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-right font-mono font-bold text-gold-500">
                                        {{ number_format($item->total_score, 1) }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php
                                            $ratingColor = match($item->rating) {
                                                'A' => 'bg-green-500/10 border-green-500/30 text-green-400',
                                                'B' => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
                                                'C' => 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
                                                'D' => 'bg-orange-500/10 border-orange-500/30 text-orange-400',
                                                default => 'bg-red-500/10 border-red-500/30 text-red-400',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center text-xs px-3 py-1 rounded-full border font-bold {{ $ratingColor }}">
                                            {{ $item->rating ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-atelier.button :href="route('purchasing.supplier-evaluations.show', $item)" variant="ghost" size="sm">Lihat</x-atelier.button>
                                            <form action="{{ route('purchasing.supplier-evaluations.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Hapus evaluasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-atelier.button type="submit" variant="ghost" size="sm" class="text-red-400 hover:text-red-300">Hapus</x-atelier.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                    <div class="px-5 py-4 border-t border-navy-800">
                        {{ $items->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <x-atelier.empty-state
                    title="Belum ada evaluasi supplier"
                    subtitle="Mulai dengan membuat evaluasi pertama."
                    action="Buat Evaluasi"
                    :actionUrl="route('purchasing.supplier-evaluations.create')"
                />
            @endif
        </x-atelier.card>

    </div>
</x-app-layout>