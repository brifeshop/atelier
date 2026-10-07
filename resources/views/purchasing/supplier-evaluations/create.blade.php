<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Evaluasi Supplier Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Error validasi --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('purchasing.supplier-evaluations.store') }}" method="POST">
                        @csrf

                        {{-- Pilih Supplier --}}
                        <div class="mb-4">
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">
                                Supplier <span class="text-red-500">*</span>
                            </label>
                            <select name="supplier_id" id="supplier_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('supplier_id') border-red-500 @enderror">
                                <option value="">-- Pilih Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} ({{ $supplier->code ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Periode --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="period_start" class="block text-sm font-medium text-gray-700">
                                    Tanggal Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="period_start" id="period_start"
                                       value="{{ old('period_start', now()->subDays(30)->format('Y-m-d')) }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('period_start') border-red-500 @enderror">
                                @error('period_start')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="period_end" class="block text-sm font-medium text-gray-700">
                                    Tanggal Akhir <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="period_end" id="period_end"
                                       value="{{ old('period_end', now()->format('Y-m-d')) }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('period_end') border-red-500 @enderror">
                                @error('period_end')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Info Bobot --}}
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
                            <h4 class="font-semibold text-sm text-blue-800 mb-2">ℹ️ Bobot Penilaian</h4>
                            <ul class="text-xs text-blue-700 space-y-1">
                                <li>• <strong>Quality (40%)</strong>: Berdasarkan reject rate dari Goods Receipt</li>
                                <li>• <strong>Delivery (30%)</strong>: Berdasarkan ketepatan waktu pengiriman PO</li>
                                <li>• <strong>Price (20%)</strong>: Berdasarkan perbandingan harga dengan rata-rata supplier lain</li>
                                <li>• <strong>Response (10%)</strong>: Berdasarkan kecepatan konfirmasi PO</li>
                            </ul>
                            <p class="text-xs text-blue-700 mt-2">
                                Sistem akan otomatis menghitung skor berdasarkan data transaksi yang ada.
                            </p>
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Catatan (opsional)
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-500 @enderror"
                                      placeholder="Catatan tambahan tentang evaluasi ini...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('purchasing.supplier-evaluations.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                Batal
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Hitung & Simpan Evaluasi
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>