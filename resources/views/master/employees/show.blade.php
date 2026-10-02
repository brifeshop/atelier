<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Employee</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="{{ $item->nama }}"
            subtitle="Kode: {{ $item->kode }}"
            action="Edit"
            :actionUrl="route('master.employees.edit', $item)"
        />

        <x-atelier.card title="Informasi Karyawan" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Kode</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $item->kode }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Nama</p>
                    <p class="text-sm text-white font-medium">{{ $item->nama }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Departemen</p>
                    <p class="text-sm text-navy-300">{{ $item->department ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Posisi</p>
                    <p class="text-sm text-navy-300">{{ $item->position ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Skill Level</p>
                    <p class="text-sm text-navy-300">{{ $item->skill_level ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Telepon</p>
                    <p class="text-sm text-navy-300">{{ $item->phone ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Email</p>
                    <p class="text-sm text-navy-300">{{ $item->email ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal Masuk</p>
                    <p class="text-sm text-navy-300">{{ $item->join_date ? format_tanggal($item->join_date) : '-' }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status</p>
                    @if($item->is_active)
                        <span class="inline-flex items-center gap-1.5 text-xs text-green-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs text-navy-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-navy-500"></span>Nonaktif
                        </span>
                    @endif
                </div>

            </div>
        </x-atelier.card>

        <x-atelier.card title="Informasi Gaji" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Gaji Bulanan</p>
                    <p class="font-serif text-xl font-bold text-white">
                        {{ $item->monthly_salary ? format_rupiah($item->monthly_salary) : '-' }}
                    </p>
                </div>

                <div class="bg-navy-800/30 border border-navy-700 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tarif Harian</p>
                    <p class="font-serif text-xl font-bold text-white">
                        {{ $item->daily_rate ? format_rupiah($item->daily_rate) : '-' }}
                    </p>
                </div>

                <div class="bg-gradient-to-br from-gold-500/10 to-navy-900/50 border border-gold-500/30 rounded-lg p-5">
                    <p class="text-[10px] font-mono text-gold-500 uppercase tracking-widest mb-1">Tarif per Jam</p>
                    <p class="font-serif text-xl font-bold text-white">
                        {{ $item->hourly_rate > 0 ? format_rupiah($item->hourly_rate) : '-' }}
                    </p>
                </div>

            </div>

            @if($item->notes)
                <div class="mt-6 pt-6 border-t border-navy-800">
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                    <p class="text-sm text-navy-300">{{ $item->notes }}</p>
                </div>
            @endif

            <div class="flex items-center justify-between gap-3 mt-6 pt-6 border-t border-navy-800">
                <x-atelier.button :href="route('master.employees.index')" variant="ghost">Kembali</x-atelier.button>
                <form method="POST" action="{{ route('master.employees.destroy', $item) }}" onsubmit="return confirm('Yakin hapus employee ini?')">
                    @csrf
                    @method('DELETE')
                    <x-atelier.button type="submit" variant="danger">Hapus Employee</x-atelier.button>
                </form>
            </div>
        </x-atelier.card>

    </div>
</x-app-layout>