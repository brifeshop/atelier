<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Tambah Customer</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="Tambah Customer"
            subtitle="Isi data customer baru"
        />

        <form method="POST" action="{{ route('master.customers.store') }}">
            @csrf

            <x-atelier.card title="Informasi Customer" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input
                        name="kode"
                        label="Kode Customer"
                        :value="$generatedKode"
                        required
                        hint="Kode otomatis, bisa diubah"
                    />

                    <x-atelier.input
                        name="nama"
                        label="Nama Customer"
                        placeholder="PT Contoh Jaya"
                        required
                    />

                    <x-atelier.input
                        name="contact_person"
                        label="Contact Person"
                        placeholder="Budi Santoso"
                    />

                    <x-atelier.input
                        name="phone"
                        label="Telepon"
                        placeholder="0812-xxxx-xxxx"
                    />

                    <x-atelier.input
                        name="email"
                        label="Email"
                        type="email"
                        placeholder="email@contoh.com"
                    />

                    <x-atelier.input
                        name="city"
                        label="Kota"
                        placeholder="Jakarta"
                    />

                    <div class="md:col-span-2">
                        <x-atelier.input
                            name="address"
                            label="Alamat"
                            placeholder="Jl. Industri No. 5"
                        />
                    </div>

                    <x-atelier.input
                        name="payment_terms"
                        label="Term Pembayaran"
                        placeholder="NET 30"
                    />

                    <div class="flex items-center gap-3 pt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            checked
                            class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500"
                        >
                        <label for="is_active" class="text-sm text-navy-300">Customer Aktif</label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea
                            name="notes"
                            rows="3"
                            placeholder="Catatan tambahan..."
                            class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition"
                        >{{ old('notes') }}</textarea>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.customers.index')" variant="ghost">
                        Batal
                    </x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">
                        Simpan Customer
                    </x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>