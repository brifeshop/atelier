<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Input Progress Produksi</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5B"
            title="{{ $workOrder->wo_number }}"
            subtitle="Input qty per step routing — {{ $workOrder->product->nama ?? '-' }}"
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

        {{-- SUMMARY --}}
        <x-atelier.card title="Ringkasan Produksi" :brackets="true">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-4">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Rencana</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_angka($workOrder->planned_qty, 2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-500/10 to-navy-900/50 border border-green-500/30 rounded-lg p-4">
                    <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Selesai</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_angka($workOrder->actual_qty, 2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-500/10 to-navy-900/50 border border-red-500/30 rounded-lg p-4">
                    <p class="text-[10px] font-mono text-red-400 uppercase tracking-widest mb-1">Reject</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ format_angka($workOrder->rejected_qty, 2) }}</p>
                </div>
                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-4">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Progress</p>
                    <p class="font-serif text-2xl font-bold text-white">{{ number_format($workOrder->progress_percent, 1) }}%</p>
                </div>
            </div>
        </x-atelier.card>

        {{-- STEPS --}}
        @if($workOrder->progress->count() > 0)
            <form method="POST" action="{{ route('production.work-orders.progress.bulk', $workOrder) }}">
                @csrf
                @method('PATCH')

                <x-atelier.card title="Progress Step" :brackets="true" padding="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-navy-950/50 border-b border-navy-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Seq</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Operasi</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Work Center</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[100px]">Qty OK</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[100px]">Qty Reject</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[100px]">Waktu (menit)</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[130px]">Status</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-navy-800">
                                @foreach($workOrder->progress as $i => $step)
                                    <tr class="hover:bg-navy-800/30 transition">
                                        <td class="px-4 py-3 text-sm font-mono text-navy-500">{{ $step->sequence }}</td>
                                        <td class="px-4 py-3 text-sm text-white font-medium">{{ $step->operation_name }}</td>
                                        <td class="px-4 py-3 text-xs text-navy-300">
                                            {{ $step->workCenter->nama ?? '-' }}
                                            @if($step->workCenter)
                                                <br><span class="text-[10px] text-navy-500">
                                                    Rp {{ number_format($step->workCenter->hourly_rate, 0, ',', '.') }}/jam + 
                                                    Rp {{ number_format($step->workCenter->overhead_rate, 0, ',', '.') }}/jam
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" 
                                                   name="steps[{{ $step->id }}][qty_completed]"
                                                   value="{{ old("steps.{$step->id}.qty_completed", $step->qty_completed) }}"
                                                   step="0.01"
                                                   min="0"
                                                   class="w-full px-2 py-1.5 bg-navy-950 border border-navy-700 rounded text-sm text-white text-right focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition"
                                                   placeholder="0">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" 
                                                   name="steps[{{ $step->id }}][qty_rejected]"
                                                   value="{{ old("steps.{$step->id}.qty_rejected", $step->qty_rejected) }}"
                                                   step="0.01"
                                                   min="0"
                                                   class="w-full px-2 py-1.5 bg-navy-950 border border-navy-700 rounded text-sm text-white text-right focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition"
                                                   placeholder="0">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" 
                                                   name="steps[{{ $step->id }}][actual_minutes]"
                                                   value="{{ old("steps.{$step->id}.actual_minutes", $step->actual_minutes) }}"
                                                   step="0.01"
                                                   min="0"
                                                   class="w-full px-2 py-1.5 bg-navy-950 border border-navy-700 rounded text-sm text-white text-right focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition"
                                                   placeholder="0">
                                        </td>
                                        <td class="px-4 py-3">
                                            <select name="steps[{{ $step->id }}][status]"
                                                    class="w-full px-2 py-1.5 bg-navy-950 border border-navy-700 rounded text-xs text-white focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition">
                                                <option value="pending" @selected($step->status == 'pending')>Menunggu</option>
                                                <option value="in_progress" @selected($step->status == 'in_progress')>Dikerjakan</option>
                                                <option value="completed" @selected($step->status == 'completed')>Selesai</option>
                                                <option value="skipped" @selected($step->status == 'skipped')>Dilewati</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-sm text-white">
                                            {{ format_rupiah($step->total_cost) }}
                                            <br><span class="text-[10px] text-navy-500">
                                                L: {{ format_rupiah($step->labor_cost) }} | O: {{ format_rupiah($step->overhead_cost) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <input type="hidden" name="steps[{{ $step->id }}][notes]" value="{{ $step->notes }}">
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gold-500/10 border-t-2 border-gold-500/30">
                                <tr>
                                    <td colspan="7" class="px-4 py-3 text-right text-sm text-gold-500 font-semibold uppercase tracking-wider">
                                        Total Cost Step
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-base text-white font-bold">
                                        {{ format_rupiah($workOrder->progress->sum('total_cost')) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </x-atelier.card>

                <div class="flex items-center justify-between gap-3">
                    <x-atelier.button :href="route('production.work-orders.show', $workOrder)" variant="ghost">Kembali</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Progress</x-atelier.button>
                </div>
            </form>
        @else
            <x-atelier.card :brackets="true">
                <x-atelier.empty-state
                    title="Belum ada step progress"
                    subtitle="Progress step akan di-generate otomatis saat Release Work Order."
                />
            </x-atelier.card>
        @endif

    </div>
</x-app-layout>