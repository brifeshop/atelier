<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Buat Work Order</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M5B" title="Buat Work Order" subtitle="Perintah produksi baru" />

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

        <form method="POST" action="{{ route('production.work-orders.store') }}" x-data="woForm()">
            @csrf

            <x-atelier.card title="Informasi Work Order" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input name="wo_number" label="No. Work Order" :value="$generatedNumber" required hint="Auto-generate" />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Prioritas <span class="text-red-400">*</span>
                        </label>
                        <select name="priority" required class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="low">Rendah</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">Tinggi</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Produk <span class="text-red-400">*</span>
                        </label>
                        <select name="product_id" required x-model="productId" @change="filterBoms()"
                                class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>
                                    {{ $p->kode }} — {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-navy-500 mt-1.5">BOM & Routing akan difilter sesuai produk.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            BOM <span class="text-xs text-navy-500 font-normal ml-2">(untuk auto-generate material)</span>
                        </label>
                        <select name="bom_id" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Tanpa BOM --</option>
                            <template x-for="bom in filteredBoms" :key="bom.id">
                                <option :value="bom.id" x-text="bom.label"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Routing <span class="text-xs text-navy-500 font-normal ml-2">(untuk estimasi labor)</span>
                        </label>
                        <select name="routing_id" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Tanpa Routing --</option>
                            <template x-for="rtg in filteredRoutings" :key="rtg.id">
                                <option :value="rtg.id" x-text="rtg.label"></option>
                            </template>
                        </select>
                    </div>

                    <x-atelier.input name="planned_qty" label="Qty Rencana Produksi" type="number" step="0.01" min="0.01" :value="old('planned_qty')" required hint="Jumlah unit yang akan diproduksi" />

                    <x-atelier.input name="start_date" label="Tanggal Mulai" type="date" :value="old('start_date', date('Y-m-d'))" required />

                    <x-atelier.input name="end_date" label="Target Selesai" type="date" :value="old('end_date')" hint="Opsional" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan tambahan..." class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex gap-3 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-300">
                        <p class="font-semibold mb-1">Langkah Berikutnya:</p>
                        <ol class="list-decimal list-inside space-y-0.5 text-blue-400">
                            <li>WO akan dibuat dengan status <strong>Draft</strong></li>
                            <li>Klik <strong>Release</strong> → material requirement auto-generate dari BOM</li>
                            <li>Klik <strong>Start</strong> → mulai produksi</li>
                            <li>Input progress per step</li>
                            <li>Klik <strong>Complete</strong> → selesai</li>
                        </ol>
                    </div>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('production.work-orders.index')" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary">Buat Work Order</x-atelier.button>
            </div>

        </form>

    </div>

    @push('scripts')
    <script>
        const bomsData = @js($boms->map(fn($b) => ['id' => $b->id, 'product_id' => $b->product_id, 'label' => $b->kode . ' — ' . $b->product->nama . ' v' . $b->version]));
        const routingsData = @js($routings->map(fn($r) => ['id' => $r->id, 'product_id' => $r->product_id, 'label' => $r->kode . ' — ' . $r->product->nama . ' v' . $r->version]));

        function woForm() {
            return {
                productId: '{{ old('product_id') }}',
                filteredBoms: [],
                filteredRoutings: [],

                init() {
                    this.filterBoms();
                },

                filterBoms() {
                    if (!this.productId) {
                        this.filteredBoms = [];
                        this.filteredRoutings = [];
                        return;
                    }
                    this.filteredBoms = bomsData.filter(b => b.product_id == this.productId);
                    this.filteredRoutings = routingsData.filter(r => r.product_id == this.productId);
                }
            }
        }
    </script>
    @endpush

</x-app-layout>