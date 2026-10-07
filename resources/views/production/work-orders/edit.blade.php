<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Work Order</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M5B" title="Edit Work Order" subtitle="{{ $item->wo_number }}" />

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

        <form method="POST" action="{{ route('production.work-orders.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Work Order" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input name="wo_number" label="No. Work Order" :value="$item->wo_number" required />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Prioritas <span class="text-red-400">*</span>
                        </label>
                        <select name="priority" required class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            @foreach(['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi', 'urgent' => 'Urgent'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('priority', $item->priority) == $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">
                            Produk <span class="text-red-400">*</span>
                        </label>
                        <select name="product_id" required class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(old('product_id', $item->product_id) == $p->id)>
                                    {{ $p->kode }} — {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">BOM</label>
                        <select name="bom_id" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Tanpa BOM --</option>
                            @foreach($boms as $bom)
                                <option value="{{ $bom->id }}" @selected(old('bom_id', $item->bom_id) == $bom->id)>
                                    {{ $bom->kode }} — {{ $bom->product->nama }} v{{ $bom->version }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Routing</label>
                        <select name="routing_id" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Tanpa Routing --</option>
                            @foreach($routings as $routing)
                                <option value="{{ $routing->id }}" @selected(old('routing_id', $item->routing_id) == $routing->id)>
                                    {{ $routing->kode }} — {{ $routing->product->nama }} v{{ $routing->version }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-atelier.input name="planned_qty" label="Qty Rencana Produksi" type="number" step="0.01" :value="$item->planned_qty" required />
                    <x-atelier.input name="start_date" label="Tanggal Mulai" type="date" :value="$item->start_date?->format('Y-m-d')" required />
                    <x-atelier.input name="end_date" label="Target Selesai" type="date" :value="$item->end_date?->format('Y-m-d')" />

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                    </div>
                </div>
            </x-atelier.card>

            <div class="flex items-center justify-end gap-3">
                <x-atelier.button :href="route('production.work-orders.show', $item)" variant="ghost">Batal</x-atelier.button>
                <x-atelier.button type="submit" variant="primary">Simpan Perubahan</x-atelier.button>
            </div>

        </form>

    </div>
</x-app-layout>