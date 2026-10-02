<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Material</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="Master Material"
            subtitle="Daftar semua bahan baku"
            action="Tambah Material"
            :actionUrl="route('master.materials.create')"
        />

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <x-atelier.card :brackets="true" padding="p-0">
            <div class="px-5 py-4 border-b border-navy-800">
                <form method="GET" action="{{ route('master.materials.index') }}" class="flex gap-3">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode, nama, kategori..."
                        class="flex-1 px-3 py-2 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                    >
                    <x-atelier.button type="submit" variant="secondary">Cari</x-atelier.button>
                    @if(request('search'))
                        <x-atelier.button :href="route('master.materials.index')" variant="ghost">Reset</x-atelier.button>
                    @endif
                </form>
            </div>

            @if($items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Kode</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Nama</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Kategori</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Harga</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Stok</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Supplier</th>
                                <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Status</th>
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($items as $material)
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-5 py-3 text-sm font-mono text-gold-500">{{ $material->kode }}</td>
                                    <td class="px-5 py-3 text-sm text-white font-medium">
                                        {{ $material->nama }}
                                        @if($material->location)
                                            <br><span class="text-xs text-navy-500">{{ $material->location }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-navy-300">{{ $material->category ?? '-' }}</td>
                                    <td class="px-5 py-3 text-sm text-right font-mono text-white">
                                        {{ format_rupiah($material->price) }}
                                        <span class="text-xs text-navy-500">/{{ $material->unit }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        @php
                                            $isLow = $material->current_stock <= $material->min_stock;
                                        @endphp
                                        <span class="text-sm font-mono {{ $isLow ? 'text-red-400' : 'text-white' }}">
                                            {{ format_angka($material->current_stock, 1) }}
                                            <span class="text-xs text-navy-500">{{ $material->unit }}</span>
                                        </span>
                                        @if($isLow)
                                            <br><span class="text-[10px] text-red-400 font-mono">⚠ LOW</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-navy-300">
                                        {{ $material->supplier->nama ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($material->is_active)
                                            <span class="inline-flex items-center gap-1.5 text-xs text-green-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs text-navy-500">
                                                <span class="w-1.5 h-1.5 rounded-full bg-navy-500"></span>Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-atelier.button :href="route('master.materials.show', $material)" variant="ghost" size="sm">Lihat</x-atelier.button>
                                            <x-atelier.button :href="route('master.materials.edit', $material)" variant="secondary" size="sm">Edit</x-atelier.button>
                                            <form method="POST" action="{{ route('master.materials.destroy', $material) }}" onsubmit="return confirm('Yakin hapus material ini?')" class="inline">
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
                    title="Belum ada material"
                    subtitle="Mulai dengan menambahkan material pertama."
                    action="Tambah Material"
                    :actionUrl="route('master.materials.create')"
                />
            @endif
        </x-atelier.card>

    </div>
</x-app-layout>