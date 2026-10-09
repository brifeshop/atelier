<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Buat Material Issue</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M5B" title="Buat Material Issue" subtitle="Ambil material dari gudang untuk produksi" />

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm">
                <p class="font-semibold mb-1">Ada kesalahan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('production.material-issues.store') }}" 
              x-data="materialIssueForm()"
              x-init="init()">
            @csrf

            {{-- INFO DASAR --}}
            <x-atelier.card title="Informasi Material Issue" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input name="issue_number" label="No. Material Issue" :value="$generatedNumber" required readonly hint="Auto-generate" />

                    <x-atelier.input name="date" label="Tanggal" type="date" :value="old('date', date('Y-m-d'))" required />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Work Order <span class="text-red-400">*</span>
                        </label>
                        <select name="work_order_id" required x-model="workOrderId" @change="loadWorkOrderMaterials()"
                                class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Work Order --</option>
                            @foreach($workOrders as $wo)
                                <option value="{{ $wo->id }}" @selected(old('work_order_id', $workOrder->id ?? '') == $wo->id)>
                                    {{ $wo->wo_number }} — {{ $wo->product->nama ?? '-' }} ({{ $wo->planned_qty }} unit)
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-navy-500 mt-1.5">Hanya WO dengan status Released/In Progress yang muncul.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Gudang <span class="text-red-400">*</span>
                        </label>
                        <select name="warehouse_id" required
                                class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Gudang --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" @selected(old('warehouse_id') == $wh->id)>
                                    {{ $wh->kode }} — {{ $wh->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            {{-- ITEMS --}}
            <x-atelier.card title="Material yang Diambil" :brackets="true" padding="p-0">

                {{-- Loading --}}
                <div x-show="isLoading" class="p-8 text-center">
                    <svg class="w-8 h-8 text-gold-500 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm text-navy-500 mt-2">Memuat material requirement...</p>
                </div>

                {{-- Empty State --}}
                <div x-show="!isLoading && !workOrderId" class="p-12 text-center">
                    <p class="text-sm text-navy-400">Pilih Work Order dulu untuk melihat material requirement.</p>
                </div>

                <div x-show="!isLoading && workOrderId && items.length === 0" x-cloak class="p-12 text-center">
                    <p class="text-sm text-navy-400">Work Order ini tidak punya material requirement. Pastikan WO sudah di-release.</p>
                </div>

                {{-- Table --}}
                <div x-show="!isLoading && items.length > 0" x-cloak class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-navy-950/50 border-b border-navy-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Material</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Dibutuhkan</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Sudah Diambil</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest">Sisa</th>
                                <th class="px-4 py-3 text-right text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[120px]">Qty Ambil</th>
                                <th class="px-4 py-3 text-left text-[10px] font-mono font-semibold text-navy-400 uppercase tracking-widest min-w-[180px]">Lokasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-800">
                            <template x-for="(item, idx) in items" :key="item.id">
                                <tr class="hover:bg-navy-800/30 transition">
                                    <td class="px-4 py-3 text-sm">
                                        <span class="text-white font-medium" x-text="item.material_nama"></span>
                                        <br><span class="text-xs font-mono text-navy-500" x-text="item.material_kode"></span>

                                        {{-- Hidden inputs --}}
                                        <input type="hidden" :name="`items[${idx}][work_order_material_id]`" :value="item.id">
                                        <input type="hidden" :name="`items[${idx}][material_id]`" :value="item.material_id">
                                        <input type="hidden" :name="`items[${idx}][unit_cost]`" :value="item.unit_cost">
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-navy-300">
                                        <span x-text="formatNumber(item.required_qty)"></span>
                                        <span class="text-xs text-navy-500" x-text="item.unit"></span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-green-400">
                                        <span x-text="formatNumber(item.issued_qty)"></span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-sm text-gold-500">
                                        <span x-text="formatNumber(item.remaining_qty)"></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" 
                                               :name="`items[${idx}][qty]`"
                                               x-model.number="item.qty_to_issue"
                                               step="0.0001"
                                               min="0"
                                               :max="item.remaining_qty"
                                               class="w-full px-2 py-1.5 bg-navy-950 border border-navy-700 rounded text-sm text-white text-right focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition"
                                               placeholder="0">
                                    </td>
                                    <td class="px-4 py-3">
                                        <select :name="`items[${idx}][location_id]`"
                                                x-model="item.location_id"
                                                @change="updateLocationQty(item)"
                                                class="w-full px-2 py-1.5 bg-navy-950 border border-navy-700 rounded text-sm text-white focus:border-gold-500 focus:ring-1 focus:ring-gold-500/20 focus:outline-none transition">
                                            <option value="">-- Pilih Lokasi --</option>
                                            <template x-for="loc in item.locations" :key="loc.id">
                                                <option :value="loc.id" x-text="loc.label"></option>
                                            </template>
                                        </select>
                                        <p x-show="item.qty_to_issue > item.max_qty_available" class="text-xs text-red-400 mt-1">
                                            ⚠ Stok tidak cukup! Maks: <span x-text="formatNumber(item.max_qty_available)"></span>
                                        </p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Info --}}
                <div x-show="!isLoading && items.length > 0" x-cloak class="px-5 py-4 bg-blue-500/10 border-t border-blue-500/30">
                    <p class="text-xs text-blue-300">
                        💡 <strong>Tips:</strong> Kosongkan qty kalau tidak mau ambil material tersebut. Bisa ambil sebagian (partial issue).
                    </p>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('production.material-issues.index')" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary" ::disabled="isLoading || items.length === 0">
                    Buat Material Issue
                </x-atelier.button>
            </div>

        </form>
    </div>

    @push('scripts')
    <script>
        function materialIssueForm() {
            return {
                workOrderId: '{{ old('work_order_id', $workOrder->id ?? '') }}',
                isLoading: false,
                items: [],

                init() {
                    if (this.workOrderId) {
                        this.loadWorkOrderMaterials();
                    }
                },

                async loadWorkOrderMaterials() {
                    if (!this.workOrderId) {
                        this.items = [];
                        return;
                    }

                    this.isLoading = true;
                    this.items = [];

                    try {
                        const url = `{{ url('production/work-orders') }}/${this.workOrderId}/materials`;
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.items = data.items.map(item => ({
                                ...item,
                                qty_to_issue: item.remaining_qty, // default: ambil sisa
                                location_id: '',
                                locations: [],
                                max_qty_available: 0,
                            }));

                            // Load locations untuk setiap item
                            for (let i = 0; i < this.items.length; i++) {
                                await this.loadLocations(this.items[i]);
                            }
                        }
                    } catch (error) {
                        console.error('Failed to load materials:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                async loadLocations(item) {
                    try {
                        const url = `{{ route('production.material-issues.get-locations') }}?material_id=${item.material_id}`;
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            item.locations = data.locations;

                            // Auto-pilih lokasi dengan stok terbanyak
                            if (item.locations.length > 0) {
                                item.location_id = item.locations[0].id;
                                item.max_qty_available = item.locations[0].qty_available;
                            }
                        }
                    } catch (error) {
                        console.error('Failed to load locations:', error);
                    }
                },

                updateLocationQty(item) {
                    const loc = item.locations.find(l => l.id === item.location_id);
                    item.max_qty_available = loc ? loc.qty_available : 0;
                },

                formatNumber(value) {
                    if (value === null || value === undefined) return '0';
                    return new Intl.NumberFormat('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 4,
                    }).format(value);
                },
            }
        }
    </script>
    @endpush

</x-app-layout>