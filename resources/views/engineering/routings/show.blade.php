<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Routing</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5A"
            title="{{ $item->kode }}"
            subtitle="{{ $item->product->nama ?? '-' }} — v{{ $item->version }}"
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

        {{-- INFO --}}
        <x-atelier.card title="Informasi Routing" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kode</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->kode }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status</p>
                    <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $item->status_color }}">
                        {{ $item->status_label }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Produk</p>
                    <p class="text-sm text-white font-medium">{{ $item->product->nama ?? '-' }}</p>
                    <p class="text-xs text-navy-500">{{ $item->product->kode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Versi</p>
                    <p class="text-sm text-navy-300">v{{ $item->version }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Berlaku Mulai</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($item->effective_date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dibuat Oleh</p>
                    <p class="text-sm text-navy-300">{{ $item->creator->name ?? '-' }}</p>
                </div>
                @if($item->notes)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                        <p class="text-sm text-navy-300">{{ $item->notes }}</p>
                    </div>
                @endif
            </div>
        </x-atelier.card>

        {{-- COST SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-navy-900/50 border border-gold-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Labor Cost</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->total_labor_cost) }}</p>
            </div>
            <div class="bg-navy-900/50 border border-orange-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-orange-400 uppercase tracking-widest mb-1">Overhead Cost</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->total_overhead_cost) }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500/10 to-navy-900/50 border border-green-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">Total Cost / Unit</p>
                <p class="font-serif text-2xl font-bold text-white">{{ format_rupiah($item->total_cost) }}</p>
            </div>
        </div>

        {{-- STEPS --}}
        <x-atelier.card title="Urutan Proses (Steps)" :brackets="true" padding="p-0">
            @if($item->steps->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Seq</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Operasi</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Work Center</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Setup (m)</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Run/Unit (m)</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Labor</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Overhead</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Total</th>
                                @if($item->canEdit())
                                    <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($item->steps as $step)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm font-mono text-navy-500">{{ $step->sequence }}</td>
                                    <td class="px-5 py-3 text-sm text-white font-medium">{{ $step->operation_name }}</td>
                                    <td class="px-5 py-3 text-sm">
                                        <span class="text-navy-300">{{ $step->workCenter->nama ?? '-' }}</span>
                                        <br><span class="text-xs font-mono text-navy-500">{{ $step->workCenter->kode ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                        {{ format_angka($step->setup_time_minutes, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                        {{ format_angka($step->run_time_per_unit_minutes, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-gold-500">
                                        {{ format_rupiah($step->labor_cost) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-orange-400">
                                        {{ format_rupiah($step->overhead_cost) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-sm text-white font-semibold">
                                        {{ format_rupiah($step->total_cost) }}
                                    </td>
                                    @if($item->canEdit())
                                        <td class="px-5 py-3 text-right">
                                            <form method="POST" action="{{ route('engineering.routings.steps.destroy', [$item, $step]) }}" onsubmit="return confirm('Hapus step ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-400 hover:text-red-300">Hapus</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-atelier.empty-state
                    title="Belum ada step"
                    subtitle="Tambahkan step proses di form bawah."
                />
            @endif
        </x-atelier.card>

        {{-- FORM ADD STEP --}}
        @if($item->canEdit())
            <x-atelier.card title="Tambah Step" :brackets="true">
                <form method="POST" action="{{ route('engineering.routings.steps.store', $item) }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <x-atelier.input name="operation_name" label="Nama Operasi" placeholder="Potong, Rakit, Finishing" required />

                        <div>
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                                Work Center <span class="text-red-400">*</span>
                            </label>
                            <select name="work_center_id" required class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                                <option value="">-- Pilih Work Center --</option>
                                @foreach($workCenters as $wc)
                                    <option value="{{ $wc->id }}">
                                        {{ $wc->kode }} — {{ $wc->nama }} (Rp {{ number_format($wc->hourly_rate, 0, ',', '.') }}/jam)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <x-atelier.input name="setup_time_minutes" label="Setup Time (menit)" type="number" step="0.01" min="0" hint="Waktu persiapan (sekali per batch)" />

                        <x-atelier.input name="run_time_per_unit_minutes" label="Run Time per Unit (menit)" type="number" step="0.01" min="0" hint="Waktu proses per 1 unit" />

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-6 border-t border-navy-800">
                        <x-atelier.button type="submit" variant="primary">Tambah Step</x-atelier.button>
                    </div>
                </form>
            </x-atelier.card>
        @endif

        {{-- ACTIONS --}}
        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('engineering.routings.index')" variant="ghost">Kembali</x-atelier.button>

            <div class="flex gap-3">
                @if($item->canEdit())
                    <x-atelier.button :href="route('engineering.routings.edit', $item)" variant="secondary">Edit</x-atelier.button>

                    <form method="POST" action="{{ route('engineering.routings.recalculate', $item) }}">
                        @csrf
                        <x-atelier.button type="submit" variant="secondary">Hitung Ulang Cost</x-atelier.button>
                    </form>

                    @if($item->canActivate())
                        <form method="POST" action="{{ route('engineering.routings.activate', $item) }}" onsubmit="return confirm('Aktifkan Routing ini?')">
                            @csrf
                            @method('PATCH')
                            <x-atelier.button type="submit" variant="primary">Aktifkan Routing</x-atelier.button>
                        </form>
                    @endif
                @endif

                @if($item->canObsolete())
                    <form method="POST" action="{{ route('engineering.routings.obsolete', $item) }}" onsubmit="return confirm('Obsolete Routing ini?')">
                        @csrf
                        @method('PATCH')
                        <x-atelier.button type="submit" variant="danger">Obsolete</x-atelier.button>
                    </form>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>