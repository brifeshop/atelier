<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Supplier</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header
            segment="M2"
            title="Edit Supplier"
            subtitle="{{ $item->kode }} — {{ $item->nama }}"
        />

        <form method="POST" action="{{ route('master.suppliers.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Supplier" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input name="kode" label="Kode Supplier" :value="$item->kode" required />
                    <x-atelier.input name="nama" label="Nama Supplier" :value="$item->nama" required />
                    <x-atelier.input name="pic_name" label="PIC (Contact Person)" :value="$item->pic_name" />
                    <x-atelier.input name="phone" label="Telepon" :value="$item->phone" />
                    <x-atelier.input name="email" label="Email" type="email" :value="$item->email" />
                    <x-atelier.input name="city" label="Kota" :value="$item->city" />

                    <div class="md:col-span-2">
                        <x-atelier.input name="address" label="Alamat" :value="$item->address" />
                    </div>

                    <x-atelier.input name="bank_account" label="Rekening Bank" :value="$item->bank_account" />
                    <x-atelier.input name="payment_terms" label="Term Pembayaran" :value="$item->payment_terms" />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Rating (1-5)</label>
                        <select name="rating" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Rating --</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected(old('rating', $item->rating) == $i)>
                                    {{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} ({{ $i }})
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex items-center gap-3 pt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                        <label for="is_active" class="text-sm text-navy-300">Supplier Aktif</label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.suppliers.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Perubahan</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>