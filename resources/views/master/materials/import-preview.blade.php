<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Preview Import Material</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="Preview Import"
            subtitle="Edit data sebelum import"
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
                ⚠️ <strong>{{ $errorCount }} baris</strong> akan di-skip saat import karena error. Perbaiki CSV kalau perlu, atau lanjutkan.
            </div>
        @endif

        <div class="bg-blue-500/10 border border-blue-500/30 text-blue-400 px-4 py-3 rounded-lg text-sm">
            ℹ️ Kolom <strong>Unit</strong>, <strong>Costing Method</strong>, dan <strong>Location</strong> bisa di-edit langsung di bawah ini.
        </div>

        {{-- FORM --}}
        <form method="POST" action="{{ route('master.materials.import.store') }}" id="importForm">
            @csrf
            <input type="hidden" name="rows" id="rowsInput" value="{{ json_encode($rows) }}">

            <x-atelier.card :brackets="true" padding="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest sticky left-0 bg-navy-950/50 z-10">#</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Status</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Kode</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Kode Bahan</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Nama</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Kategori</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[120px]">Unit</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[160px]">Costing Method</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Volume Type</th>
                                <th class="px-3 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">P (mm)</th>
                                <th class="px-3 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">L (mm)</th>
                                <th class="px-3 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">T (mm)</th>
                                <th class="px-3 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Berat (g)</th>
                                <th class="px-3 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Vol (ml)</th>
                                <th class="px-3 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Price</th>
                                <th class="px-3 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Yield %</th>
                                <th class="px-3 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[120px]">Location</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            @foreach($rows as $i => $row)
                                @php $hasError = !empty($row['_errors']); @endphp
                                <tr class="{{ $hasError ? 'bg-red-500/5' : 'hover:bg-navy-800/30' }} transition">
                                    <td class="px-3 py-2 text-xs font-mono text-navy-500 sticky left-0 {{ $hasError ? 'bg-red-500/5' : 'bg-navy-950' }} z-10">
                                        {{ $i + 1 }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @if($hasError)
                                            <span class="inline-flex items-center gap-1 text-xs text-red-400" title="{{ implode(' | ', $row['_errors']) }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Error
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs text-green-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>OK
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        @php
                                            $previewKode = $row['_preview_kode'] ?? '-';
                                            $kodeSource = $row['_kode_source'] ?? 'error';
                                        @endphp
                                        @if($kodeSource === 'auto')
                                            <span class="font-mono text-green-400">{{ $previewKode }}</span>
                                            <br><span class="text-[10px] text-green-500/70">(auto)</span>
                                        @elseif($kodeSource === 'manual')
                                            <span class="font-mono text-gold-500">{{ $previewKode }}</span>
                                            <br><span class="text-[10px] text-gold-500/70">(manual)</span>
                                        @else
                                            <span class="font-mono text-navy-500">{{ $previewKode }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs text-navy-300 font-mono">{{ $row['kode_bahan'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-white">{{ $row['nama'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-navy-300">{{ $row['category'] ?: '-' }}</td>

                                    {{-- UNIT (EDITABLE) --}}
                                    <td class="px-3 py-2">
                                        <select data-row="{{ $i }}" data-field="unit" 
                                                class="unit-select w-full min-w-[100px] px-2 py-1 bg-navy-950 border border-navy-700 rounded text-xs text-white focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition">
                                            <option value="">-- Pilih --</option>
                                            @foreach(['pcs', 'unit', 'set', 'lusin', 'rim', 'lembar', 'batang', 'roll', 'kaleng', 'botol', 'karung', 'drum', 'mm', 'cm', 'm', 'gram', 'kg', 'ton', 'ml', 'liter'] as $unit)
                                                <option value="{{ $unit }}" @selected($row['unit'] == $unit)>{{ $unit }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    {{-- COSTING METHOD (EDITABLE) --}}
                                    <td class="px-3 py-2">
                                        <select data-row="{{ $i }}" data-field="costing_method"
                                                class="costing-select w-full min-w-[140px] px-2 py-1 bg-navy-950 border border-navy-700 rounded text-xs text-white focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition">
                                            <option value="per_unit" @selected($row['costing_method'] == 'per_unit')>Per Unit</option>
                                            <option value="per_area" @selected($row['costing_method'] == 'per_area')>Per Area</option>
                                            <option value="per_volume" @selected($row['costing_method'] == 'per_volume')>Per Volume</option>
                                            <option value="per_length" @selected($row['costing_method'] == 'per_length')>Per Length</option>
                                            <option value="per_weight" @selected($row['costing_method'] == 'per_weight')>Per Weight</option>
                                        </select>
                                    </td>

                                    <td class="px-3 py-2 text-xs text-navy-300">{{ $row['volume_type'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-mono text-navy-300">{{ $row['panjang_standar'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-mono text-navy-300">{{ $row['lebar_standar'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-mono text-navy-300">{{ $row['tinggi_standar'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-mono text-navy-300">{{ $row['berat_standar'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-mono text-navy-300">{{ $row['volume_standar'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-mono text-white">{{ $row['price'] ?: '0' }}</td>
                                    <td class="px-3 py-2 text-xs text-right font-mono text-navy-300">{{ $row['yield_percent'] ?: '100' }}</td>

                                    {{-- LOCATION (EDITABLE) --}}
                                    <td class="px-3 py-2">
                                        <input type="text" data-row="{{ $i }}" data-field="location"
                                               value="{{ $row['location'] ?? '' }}"
                                               list="location-suggestions"
                                               placeholder="Rak A-01"
                                               class="location-input w-full min-w-[100px] px-2 py-1 bg-navy-950 border border-navy-700 rounded text-xs text-white placeholder-navy-500 focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-atelier.card>

            {{-- ACTIONS --}}
            <div class="flex items-center justify-between gap-3 mt-6">
                <x-atelier.button :href="route('master.materials.import')" variant="ghost">← Upload Ulang</x-atelier.button>

                <div class="flex gap-3">
                    <button type="button" onclick="resetChanges()" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-navy-800 border border-navy-700 rounded-lg text-sm text-navy-300 hover:text-white hover:border-gold-500/50 transition">
                        ↺ Reset Perubahan
                    </button>

                    <x-atelier.button type="submit" variant="primary" 
                                      onclick="return confirm('Import {{ $validCount }} material?')">
                        ✓ Import {{ $validCount }} Material
                    </x-atelier.button>
                </div>
            </div>
        </form>

        {{-- Datalist untuk saran lokasi --}}
        <datalist id="location-suggestions">
            @php
                $locations = collect($rows)->pluck('location')->filter()->unique();
            @endphp
            @foreach($locations as $loc)
                <option value="{{ $loc }}">
            @endforeach
            <option value="Rak A-01">
            <option value="Rak A-02">
            <option value="Rak A-03">
            <option value="Rak B-01">
            <option value="Rak C-01">
            <option value="Gudang Bahan">
        </datalist>

    </div>

    @push('scripts')
    <script>
        // Data asli untuk reset
        const originalRows = {!! json_encode($rows) !!};
        let currentRows = JSON.parse(JSON.stringify(originalRows));

        // Bind edit events
        document.querySelectorAll('.unit-select, .costing-select, .location-input').forEach(el => {
            el.addEventListener('change', updateRow);
            el.addEventListener('input', updateRow);
        });

        function updateRow(e) {
            const rowIndex = e.target.dataset.row;
            const field = e.target.dataset.field;
            const value = e.target.value;

            currentRows[rowIndex][field] = value;

            // Update hidden input
            document.getElementById('rowsInput').value = JSON.stringify(currentRows);
        }

        function resetChanges() {
            if (!confirm('Reset semua perubahan ke data asli?')) return;
            
            currentRows = JSON.parse(JSON.stringify(originalRows));
            document.getElementById('rowsInput').value = JSON.stringify(currentRows);

            // Reset visual
            document.querySelectorAll('.unit-select').forEach(el => {
                el.value = originalRows[el.dataset.row]['unit'] || '';
            });
            document.querySelectorAll('.costing-select').forEach(el => {
                el.value = originalRows[el.dataset.row]['costing_method'] || 'per_unit';
            });
            document.querySelectorAll('.location-input').forEach(el => {
                el.value = originalRows[el.dataset.row]['location'] || '';
            });
        }

        // Submit — pastikan rows terbaru dikirim
        document.getElementById('importForm').addEventListener('submit', function(e) {
            document.getElementById('rowsInput').value = JSON.stringify(currentRows);
        });
    </script>
    @endpush

</x-app-layout>