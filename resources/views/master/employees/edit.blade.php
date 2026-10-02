<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-white">Edit Employee</h2>
    </x-slot>

    <div class="p-6 lg:p-8 space-y-6 grid-bg">

        <x-atelier.page-header segment="M2" title="Edit Employee" subtitle="{{ $item->kode }} — {{ $item->nama }}" />

        <form method="POST" action="{{ route('master.employees.update', $item) }}">
            @csrf
            @method('PUT')

            <x-atelier.card title="Informasi Karyawan" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <x-atelier.input name="kode" label="Kode Employee" :value="$item->kode" required />
                    <x-atelier.input name="nama" label="Nama Lengkap" :value="$item->nama" required />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Departemen</label>
                        <select name="department" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach(['Produksi', 'QC', 'Maintenance', 'Warehouse', 'Purchasing', 'Sales', 'Finance', 'Admin'] as $dept)
                                <option value="{{ $dept }}" @selected(old('department', $item->department) == $dept)>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-atelier.input name="position" label="Posisi" :value="$item->position" />

                    <div>
                        <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Skill Level</label>
                        <select name="skill_level" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">
                            <option value="">-- Pilih Level --</option>
                            @foreach(['Junior', 'Intermediate', 'Senior', 'Expert'] as $level)
                                <option value="{{ $level }}" @selected(old('skill_level', $item->skill_level) == $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-atelier.input name="phone" label="Telepon" :value="$item->phone" />
                    <x-atelier.input name="email" label="Email" type="email" :value="$item->email" />
                    <x-atelier.input name="join_date" label="Tanggal Masuk" type="date" :value="$item->join_date?->format('Y-m-d')" />

                </div>
            </x-atelier.card>

            <x-atelier.card title="Informasi Gaji" :brackets="true">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <x-atelier.input name="monthly_salary" label="Gaji Bulanan (Rp)" type="number" :value="$item->monthly_salary" />
                    <x-atelier.input name="daily_rate" label="Tarif Harian (Rp)" type="number" :value="$item->daily_rate" />
                    <x-atelier.input name="hourly_rate" label="Tarif per Jam (Rp)" type="number" :value="$item->hourly_rate" hint="Kosongkan untuk auto-hitung" />
                </div>
            </x-atelier.card>

            <x-atelier.card :brackets="true">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} class="rounded border-navy-600 bg-navy-950 text-gold-500 focus:ring-gold-500">
                    <label for="is_active" class="text-sm text-navy-300">Employee Aktif</label>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-semibold text-navy-300 uppercase tracking-wider mb-2">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2.5 bg-navy-950 border border-navy-700 rounded-lg text-sm text-white placeholder-navy-500 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none transition">{{ old('notes', $item->notes) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-navy-800">
                    <x-atelier.button :href="route('master.employees.index')" variant="ghost">Batal</x-atelier.button>
                    <x-atelier.button type="submit" variant="primary">Simpan Perubahan</x-atelier.button>
                </div>
            </x-atelier.card>

        </form>

    </div>
</x-app-layout>