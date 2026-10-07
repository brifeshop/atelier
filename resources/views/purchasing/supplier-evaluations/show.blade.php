<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Evaluasi Supplier') }}
            </h2>
            <a href="{{ route('purchasing.supplier-evaluations.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Header Info --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Supplier</p>
                            <p class="text-lg font-semibold">{{ $evaluation->supplier->name ?? '-' }}</p>
                            <p class="text-sm text-gray-600">{{ $evaluation->supplier->code ?? '' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Periode Evaluasi</p>
                            <p class="text-lg font-semibold">
                                {{ $evaluation->period_start->format('d M Y') }} - {{ $evaluation->period_end->format('d M Y') }}
                            </p>
                            <p class="text-sm text-gray-600">
                                ({{ $evaluation->period_start->diffInDays($evaluation->period_end) }} hari)
                            </p>
                        </div>
                        <div class="text-center md:text-right">
                            <p class="text-sm text-gray-500">Grade</p>
                            @php
                                $gradeColor = match($evaluation->grade) {
                                    'A' => 'bg-green-100 text-green-800 border-green-300',
                                    'B' => 'bg-blue-100 text-blue-800 border-blue-300',
                                    'C' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                    'D' => 'bg-orange-100 text-orange-800 border-orange-300',
                                    default => 'bg-red-100 text-red-800 border-red-300',
                                };
                            @endphp
                            <span class="inline-block px-6 py-3 text-4xl font-bold rounded-full border-2 {{ $gradeColor }}">
                                {{ $evaluation->grade }}
                            </span>
                            <p class="text-sm text-gray-600 mt-1">Overall Score: <strong>{{ number_format($evaluation->overall_score, 2) }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Score Breakdown --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @php
                    $scores = [
                        ['label' => 'Quality', 'value' => $evaluation->quality_score, 'weight' => '40%', 'color' => 'blue'],
                        ['label' => 'Delivery', 'value' => $evaluation->delivery_score, 'weight' => '30%', 'color' => 'green'],
                        ['label' => 'Price', 'value' => $evaluation->price_score, 'weight' => '20%', 'color' => 'yellow'],
                        ['label' => 'Response', 'value' => $evaluation->response_score, 'weight' => '10%', 'color' => 'purple'],
                    ];
                @endphp

                @foreach ($scores as $score)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-2">
                                <p class="text-sm font-medium text-gray-500">{{ $score['label'] }}</p>
                                <span class="text-xs text-gray-400">Bobot: {{ $score['weight'] }}</span>
                            </div>
                            <p class="text-3xl font-bold text-{{ $score['color'] }}-600">
                                {{ number_format($score['value'], 2) }}%
                            </p>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-{{ $score['color'] }}-600 h-2 rounded-full"
                                     style="width: {{ min(100, max(0, $score['value'])) }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Detail Data Transaksi --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-lg mb-4">📊 Detail Data Transaksi</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Data Quality --}}
                        <div class="border rounded p-4">
                            <h4 class="font-semibold text-sm text-gray-700 mb-3">Quality (dari Goods Receipt)</h4>
                            <table class="min-w-full text-sm">
                                <tr>
                                    <td class="py-1 text-gray-600">Total Diterima</td>
                                    <td class="py-1 text-right font-medium">{{ number_format($evaluation->total_received) }} unit</td>
                                </tr>
                                <tr>
                                    <td class="py-1 text-gray-600">Total Reject</td>
                                    <td class="py-1 text-right font-medium text-red-600">{{ number_format($evaluation->total_rejected) }} unit</td>
                                </tr>
                                <tr class="border-t">
                                    <td class="py-1 text-gray-600 font-semibold">Reject Rate</td>
                                    <td class="py-1 text-right font-bold">
                                        {{ $evaluation->total_received + $evaluation->total_rejected > 0
                                            ? number_format(($evaluation->total_rejected / ($evaluation->total_received + $evaluation->total_rejected)) * 100, 2)
                                            : 0 }}%
                                    </td>
                                </tr>
                            </table>
                        </div>

                        {{-- Data Delivery --}}
                        <div class="border rounded p-4">
                            <h4 class="font-semibold text-sm text-gray-700 mb-3">Delivery (dari Purchase Order)</h4>
                            <table class="min-w-full text-sm">
                                <tr>
                                    <td class="py-1 text-gray-600">Total PO</td>
                                    <td class="py-1 text-right font-medium">{{ number_format($evaluation->total_po ?? 0) }} PO</td>
                                </tr>
                                <tr>
                                    <td class="py-1 text-gray-600">PO Tepat Waktu</td>
                                    <td class="py-1 text-right font-medium text-green-600">{{ number_format($evaluation->on_time_po ?? 0) }} PO</td>
                                </tr>
                                <tr class="border-t">
                                    <td class="py-1 text-gray-600 font-semibold">On-Time Rate</td>
                                    <td class="py-1 text-right font-bold">{{ number_format($evaluation->delivery_score, 2) }}%</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            @if ($evaluation->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-semibold text-lg mb-2">📝 Catatan</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ $evaluation->notes }}</p>
                    </div>
                </div>
            @endif

            {{-- Rekomendasi --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-lg mb-3">💡 Rekomendasi</h3>
                    @php
                        $recommendation = match($evaluation->grade) {
                            'A' => ['text' => 'Supplier sangat baik. Pertahankan hubungan dan pertimbangkan untuk menambah volume order.', 'color' => 'green'],
                            'B' => ['text' => 'Supplier baik. Lanjutkan kerja sama, pantau area yang perlu ditingkatkan.', 'color' => 'blue'],
                            'C' => ['text' => 'Supplier cukup. Lakukan evaluasi lanjutan dan komunikasikan area perbaikan.', 'color' => 'yellow'],
                            'D' => ['text' => 'Supplier kurang baik. Berikan peringatan dan minta rencana perbaikan.', 'color' => 'orange'],
                            default => ['text' => 'Supplier buruk. Pertimbangkan untuk mencari supplier alternatif.', 'color' => 'red'],
                        };
                    @endphp
                    <div class="p-4 bg-{{ $recommendation['color'] }}-50 border border-{{ $recommendation['color'] }}-200 rounded">
                        <p class="text-{{ $recommendation['color'] }}-800">{{ $recommendation['text'] }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>