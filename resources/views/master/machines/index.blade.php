<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Machine</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="Master Machine"
            subtitle="Daftar semua mesin produksi"
            action="Tambah Machine"
            :actionUrl="route('master.machines.create')"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <x-atelier.card :brackets="true" padding="p-0">
            <div class="px-5 py-4 border-b border-navy-800">
                <form method="GET" action="{{ route('master.machines.index') }}" class="flex gap-3">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode, nama, type, brand..."
                        class="flex-1 px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                    >
                    <x-atelier.button type="submit" variant="secondary">Cari</x-atelier.button>
                    @if(request('search'))
                        <x-atelier.button :href="route('master.machines.index')" variant="ghost">Reset</x-atelier.button>
                    @endif
                </form>
            </div>

            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Kode</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Nama / Type</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Brand / Model</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Power (kW)</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Status</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $machine)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm font-mono text-gold-500">{{ $machine->kode }}</td>
                                    <td class="px-5 py-3 text-sm text-white font-medium">
                                        {{ $machine->nama }}
                                        @if($machine->type)
                                            <br><span class="text-xs text-navy-500">{{ $machine->type }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-navy-300">
                                        {{ $machine->brand ?? '-' }}
                                        @if($machine->model)
                                            <br><span class="text-xs text-navy-500">{{ $machine->model }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-right font-mono text-white">
                                        {{ $machine->power_kw ? format_angka($machine->power_kw, 1) . ' kW' : '-' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @php
                                            $statusColors = [
                                                'Active' => 'text-green-400 bg-green-500/10',
                                                'Maintenance' => 'text-amber-400 bg-amber-500/10',
                                                'Broken' => 'text-red-400 bg-red-500/10',
                                            ];
                                            $color = $statusColors[$machine->status] ?? 'text-navy-400 bg-navy-500/10';
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full {{ $color }}">
                                            {{ $machine->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-atelier.button :href="route('master.machines.show', $machine)" variant="ghost" size="sm">Lihat</x-atelier.button>
                                            <x-atelier.button :href="route('master.machines.edit', $machine)" variant="secondary" size="sm">Edit</x-atelier.button>
                                            <form method="POST" action="{{ route('master.machines.destroy', $machine) }}" onsubmit="return confirm('Yakin hapus machine ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <x-atelier.button type="submit" variant="danger" size="sm">Hapus</x-atelier.button>
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
                        {{ $items->links() }}
                    </div>
                @endif
            @else
                <x-atelier.empty-state
                    title="Belum ada machine"
                    subtitle="Mulai dengan menambahkan machine pertama."
                    action="Tambah Machine"
                    :actionUrl="route('master.machines.create')"
                />
            @endif
        </x-atelier.card>

    </div>
</x-app-layout>