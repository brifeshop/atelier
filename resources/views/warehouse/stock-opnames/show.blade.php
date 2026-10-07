<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Detail Opname</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M4C"
            title="{{ $opname->opname_number }}"
            subtitle="{{ $opname->location->nama ?? '-' }} — {{ format_tanggal($opname->opname_date) }}"
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
        <x-atelier.card title="Informasi Opname" :brackets="true">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">No. Opname</p>
                    <p class="text-sm text-gold-500 font-mono">{{ $opname->opname_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Status</p>
                    <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border {{ $opname->status_color }}">
                        {{ $opname->status_label }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Lokasi</p>
                    <p class="text-sm text-white font-medium">{{ $opname->location->nama ?? '-' }}</p>
                    <p class="text-xs text-navy-500">{{ $opname->location->warehouse->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Tanggal</p>
                    <p class="text-sm text-navy-300">{{ format_tanggal($opname->opname_date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Dibuat Oleh</p>
                    <p class="text-sm text-navy-300">{{ $opname->creator->name ?? '-' }}</p>
                </div>
                @if($opname->approver)
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Di-approve Oleh</p>
                        <p class="text-sm text-navy-300">{{ $opname->approver->name ?? '-' }}</p>
                        <p class="text-xs text-navy-500">{{ $opname->approved_at?->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
                @if($opname->notes)
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Catatan</p>
                        <p class="text-sm text-navy-300">{{ $opname->notes }}</p>
                    </div>
                @endif
            </div>
        </x-atelier.card>

        {{-- SUMMARY --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">Total Item</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $opname->items->count() }}</p>
            </div>
            <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">Sudah Dihitung</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $opname->items->count() - $opname->unfilled_count }}</p>
            </div>
            <div class="bg-navy-900/50 border {{ $opname->unfilled_count > 0 ? 'border-yellow-500/50' : 'border-navy-800' }} rounded-xl p-5">
                <p class="text-[10px] font-mono {{ $opname->unfilled_count > 0 ? 'text-yellow-400' : 'text-navy-400' }} uppercase tracking-widest mb-1">Belum Dihitung</p>
                <p class="font-serif text-3xl font-bold {{ $opname->unfilled_count > 0 ? 'text-yellow-400' : 'text-white' }}">{{ $opname->unfilled_count }}</p>
            </div>
            <div class="bg-navy-900/50 border {{ $opname->variance_count > 0 ? 'border-red-500/50' : 'border-navy-800' }} rounded-xl p-5">
                <p class="text-[10px] font-mono {{ $opname->variance_count > 0 ? 'text-red-400' : 'text-navy-400' }} uppercase tracking-widest mb-1">Variance</p>
                <p class="font-serif text-3xl font-bold {{ $opname->variance_count > 0 ? 'text-red-400' : 'text-white' }}">{{ $opname->variance_count }}</p>
            </div>
        </div>

        {{-- ITEM LIST --}}
        <x-atelier.card title="Item Opname" :brackets="true" padding="p-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-navy-950/50 border-b border-navy-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Item</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Stok Sistem</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Stok Fisik</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Variance</th>
                            <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Nilai</th>
                            @if($opname->canEdit())
                                <th class="px-5 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-800">
                        @forelse($opname->items as $item)
                            <tr class="hover:bg-navy-800/30 transition">
                                <td class="px-5 py-3 text-sm">
                                    <span class="text-white font-medium">{{ $item->item->nama ?? '-' }}</span>
                                    <br><span class="text-xs font-mono text-navy-500">{{ $item->item->kode ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-sm text-navy-300">
                                    {{ format_angka($item->system_qty, 2) }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-sm">
                                    @if($item->physical_qty !== null)
                                        <span class="text-white">{{ format_angka($item->physical_qty, 2) }}</span>
                                    @else
                                        <span class="text-navy-500 italic">Belum diinput</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-sm">
                                    @if($item->physical_qty !== null)
                                        <span class="{{ $item->variance_color }}">
                                            {{ $item->variance_qty > 0 ? '+' : '' }}{{ format_angka($item->variance_qty, 2) }}
                                        </span>
                                    @else
                                        <span class="text-navy-500">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-sm">
                                    @if($item->physical_qty !== null && $item->variance_value != 0)
                                        <span class="{{ $item->variance_color }}">
                                            {{ format_rupiah($item->variance_value) }}
                                        </span>
                                    @else
                                        <span class="text-navy-500">-</span>
                                    @endif
                                </td>
                                @if($opname->canEdit())
                                    <td class="px-5 py-3 text-right">
                                        <button
                                            type="button"
                                            onclick="openInputModal({{ $item->id }}, '{{ addslashes($item->item->nama ?? '-') }}', {{ $item->system_qty }}, {{ $item->physical_qty ?? 'null' }})"
                                            class="text-xs text-gold-500 hover:text-gold-400 transition"
                                        >
                                            Input
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-sm text-navy-500">
                                    Tidak ada item di lokasi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-atelier.card>

        {{-- ACTIONS --}}
        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('warehouse.stock-opnames.index')" variant="ghost">Kembali</x-atelier.button>

            <div class="flex gap-3">
                @if($opname->canEdit())
                    <form method="POST" action="{{ route('warehouse.stock-opnames.cancel', $opname) }}" onsubmit="return confirm('Yakin batalkan opname ini?')">
                        @csrf
                        @method('PATCH')
                        <x-atelier.button type="submit" variant="danger">Batalkan</x-atelier.button>
                    </form>

                    @if($opname->canApprove())
                        <form method="POST" action="{{ route('warehouse.stock-opnames.approve', $opname) }}" onsubmit="return confirm('Approve opname ini? Stok akan disesuaikan.')">
                            @csrf
                            @method('PATCH')
                            <x-atelier.button type="submit" variant="primary">Approve & Sesuaikan Stok</x-atelier.button>
                        </form>
                    @else
                        <button disabled class="inline-flex items-center gap-2 px-4 py-2 bg-navy-800 text-navy-500 text-sm font-semibold rounded-lg cursor-not-allowed">
                            Approve & Sesuaikan Stok
                        </button>
                    @endif
                @endif
            </div>
        </div>

    </div>

    {{-- MODAL INPUT FISIK --}}
    <div id="inputModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-navy-950/80 backdrop-blur-sm">
        <div class="bg-navy-900 border border-navy-800 rounded-xl p-6 w-full max-w-md mx-4">
            <h3 class="font-serif text-lg font-bold text-white mb-4">Input Stok Fisik</h3>

            <form id="inputForm" method="POST">
                @csrf
                @method('PATCH')

                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Item</p>
                        <p id="modalItemName" class="text-sm text-white font-medium"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-mono text-navy-500 uppercase tracking-widest mb-1">Stok Sistem</p>
                        <p id="modalSystemQty" class="text-lg font-mono text-navy-300"></p>
                    </div>

                    <x-atelier.input name="physical_qty" label="Stok Fisik" type="number" step="0.01" min="0" required hint="Hasil hitung manual" />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan (opsional)..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <button type="button" onclick="closeInputModal()" class="px-4 py-2 text-sm text-navy-300 hover:text-white transition">
                        Batal
                    </button>
                    <x-atelier.button type="submit" variant="primary">Simpan</x-atelier.button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openInputModal(itemId, itemName, systemQty, physicalQty) {
            const modal = document.getElementById('inputModal');
            const form = document.getElementById('inputForm');

            form.action = `{{ route('warehouse.stock-opnames.items.update', [$opname, ':item']) }}`.replace(':item', itemId);

            document.getElementById('modalItemName').textContent = itemName;
            document.getElementById('modalSystemQty').textContent = systemQty;
            form.querySelector('[name="physical_qty"]').value = physicalQty ?? '';

            modal.classList.remove('hidden');
        }

        function closeInputModal() {
            document.getElementById('inputModal').classList.add('hidden');
        }

        // Close on backdrop click
        document.getElementById('inputModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeInputModal();
        });
    </script>
    @endpush

</x-app-layout>