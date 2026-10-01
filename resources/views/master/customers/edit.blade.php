<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Customer</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="Edit Customer"
            subtitle="{{ $item->kode }} — {{ $item->nama }}"
        />

        <form method="POST" action="{{ route('master.customers.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Customer" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input
                        name="kode"
                        label="Kode Customer"
                        :value="$item->kode"
                        required
                    />

                    <x-atelier.input
                        name="nama"
                        label="Nama Customer"
                        :value="$item->nama"
                        required
                    />

                    <x-atelier.input
                        name="contact_person"
                        label="Contact Person"
                        :value="$item->contact_person"
                    />

                    <x-atelier.input
                        name="phone"
                        label="Telepon"
                        :value="$item->phone"
                    />

                    <x-atelier.input
                        name="email"
                        label="Email"
                        type="email"
                        :value="$item->email"
                    />

                    <x-atelier.input
                        name="city"
                        label="Kota"
                        :value="$item->city"
                    />

                    <div class="md:col-span-2">
                        <x-atelier.input
                            name="address"
                            label="Alamat"
                            :value="$item->address"
                        />
                    </div>

                    <x-atelier.input
                        name="payment_terms"
                        label="Term Pembayaran"
                        :value="$item->payment_terms"
                    />

                    <div class="flex items-center gap-3 pt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            {{ $item->is_active ? 'checked' : '' }}
                            class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500"
                        >
                        <label for="is_active" class="text-sm text-navy-300">Customer Aktif</label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea
                            name="notes"
                            rows="3"
                            class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                        >{{ old('notes', $item->notes) }}</textarea>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.customers.index')" variant="ghost">
                        Batal
                    </x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">
                        Simpan Perubahan
                    </x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>