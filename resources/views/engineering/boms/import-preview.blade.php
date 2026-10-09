<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Preview Import BOM</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M5A"
            title="Preview Import"
            subtitle="{{ $bom->kode }} — {{ $bom->product->nama ?? '-' }}"
        />

        {{-- SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-navy-900/50 border border-navy-800 rounded-xl p-5">
                <p class="text-[10px] font-mono text-navy-400 uppercase tracking-widest mb-1">Total Baris</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $totalCount }}</p>
            </div>
            <div class="bg-navy-900/50 border border-green-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-green-400 uppercase tracking-widest mb-1">✓ Valid</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $validCount }}</p>
            </div>
            <div class="bg-navy-900/50 border border-red-500/30 rounded-xl p-5">
                <p class="text-[10px] font-mono text-red-400 uppercase tracking-widest mb-1">✗ Error</p>
                <p class="font-serif text-3xl font-bold text-white">{{ $errorCount }}</p>
            </div>
        </div>

        @if($errorCount > 0)
            <div class="bg-orange-500/10 border border-orange-500/30 text-orange-400 px-4 py-3 rounded-lg text-sm">
                ⚠️ <strong>{{ $errorCount }} baris</strong> akan di-skip karena error.
            </div>
        @endif

        {{-- MODE --}}
        <x-atelier.card :brackets="true">
            <div class="flex items-center gap-4">
                <span class="text-sm text-navy-300">Mode Import:</span>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="mode_preview" value="append" checked class="text-gold-500 focus:ring-gold-500">
                    <span class="text-sm text-white">Tambah ke BOM yang ada</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="mode_preview" value="replace" class="text-gold-500 focus:ring-gold-500">
                    <span class="text-sm text-red-400">Ganti semua item BOM</span>
                </label>
            </div>
        </x-atelier.card>

        {{-- PREVIEW TABLE --}}
        <x-atelier.card :brackets="true" padding="p-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-navy-950/50 border-b border-navy-800">
                        <tr>
                            <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">#</th>
                            <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Tipe</th>
                            <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Level</th>
                            <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Group / Item</th>
                            <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Divisi</th>
                            <th class="px-3 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Qty</th>
                            <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Match</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy-800">
                        @foreach($rows as $i => $row)
                            @php 
                                $hasError = !empty($row['_errors']); 
                                $isHeader = !empty($row['header_group']);
                            @endphp
                            <tr class="{{ $hasError ? 'bg-red-500/5' : ($isHeader ? 'bg-navy-800/50' : 'hover:bg-navy-800/30') }} transition">
                                <td class="px-3 py-2 text-xs font-mono text-navy-500">{{ $i + 1 }}</td>
                                <td class="px-3 py-2">
                                    @if($hasError)
                                        <span class="inline-flex items-center gap-1 text-xs text-red-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Error
                                        </span>
                                    @elseif($isHeader)
                                        <span class="inline-flex items-center gap-1 text-xs text-gold-500">
                                            📦 Header
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-green-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Item
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs font-mono text-navy-500">{{ $row['level'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-xs">
                                    @if($isHeader)
                                        <span class="font-semibold text-gold-500 uppercase tracking-wider">{{ $row['header_group'] }}</span>
                                    @else
                                        <span class="text-white">{{ $row['nama_item'] ?: '-' }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs text-navy-400">{{ $row['divisi'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs text-white">
                                    @if(!$isHeader)
                                        {{ $row['qty'] ?: '-' }}
                                        <span class="text-navy-500">{{ $row['unit'] ?? '' }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    @if($isHeader)
                                        <span class="text-navy-500 italic">—</span>
                                    @elseif($hasError)
                                        <span class="text-red-400">{{ $row['_errors'][0] ?? 'Error' }}</span>
                                    @elseif(!empty($row['_matched_material']))
                                        <span class="text-green-400">✓ {{ $row['_matched_material']['kode_bahan'] ?? $row['_matched_material']['kode'] }}</span>
                                        <br><span class="text-[10px] text-navy-500">{{ $row['_matched_material']['nama'] }}</span>
                                    @else
                                        <span class="text-navy-500">—</span>
                                    @endif
                                </td>
                            </tr>
                            @if($hasError)
                                <tr class="bg-red-500/10">
                                    <td colspan="7" class="px-3 py-2">
                                        <div class="flex items-start gap-2 text-xs text-red-400">
                                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <div>
                                                @foreach($row['_errors'] as $err)
                                                    <div>• {{ $err }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-atelier.card>

        {{-- ACTIONS --}}
        <div class="flex items-center justify-between gap-3">
            <x-atelier.button :href="route('engineering.boms.import', $bom)" variant="ghost">← Upload Ulang</x-atelier.button>

            <form method="POST" action="{{ route('engineering.boms.import.store', $bom) }}" id="importForm">
                @csrf
                <input type="hidden" name="rows" value="{{ json_encode($rows) }}">
                <input type="hidden" name="mode" id="modeInput" value="append">
                
                <x-atelier.button type="submit" variant="primary"
                                  onclick="return confirm('Import {{ $validCount }} baris ke BOM?')">
                    ✓ Import {{ $validCount }} Baris
                </x-atelier.button>
            </form>
        </div>

    </div>

    @push('scripts')
    <script>
        // Sync mode radio ke hidden input
        document.querySelectorAll('input[name="mode_preview"]').forEach(el => {
            el.addEventListener('change', function() {
                document.getElementById('modeInput').value = this.value;
            });
        });
    </script>
    @endpush
</x-app-layout>