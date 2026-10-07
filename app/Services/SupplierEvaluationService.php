<?php

namespace App\Services;

use App\Models\Purchasing\GoodsReceipt;
use App\Models\Purchasing\GoodsReceiptItem;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\SupplierEvaluation;
use App\Models\Master\Supplier;
use Carbon\Carbon;

class SupplierEvaluationService
{
    /**
     * Hitung evaluasi supplier untuk periode tertentu.
     */
    public static function evaluate(Supplier $supplier, Carbon $start, Carbon $end): SupplierEvaluation
    {
        // 1. Ambil semua GR dalam periode
        $receipts = GoodsReceipt::where('supplier_id', $supplier->id)
            ->whereBetween('date', [$start, $end])
            ->where('status', 'received')
            ->with('items')
            ->get();

        $totalReceived = $receipts->flatMap->items->sum('qty_received');
        $totalRejected = $receipts->flatMap->items->sum('qty_rejected');
        $totalQty = $totalReceived + $totalRejected;

        // 2. Reject rate
        $rejectRate = $totalQty > 0 ? ($totalRejected / $totalQty) * 100 : 0;

        // 3. Quality score (100 - reject_rate × 10)
        $qualityScore = max(0, 100 - ($rejectRate * 10));

        // 4. Delivery score
        $totalOrders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', ['received', 'partial', 'closed'])
            ->count();

        $onTime = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', ['received', 'partial', 'closed'])
            ->whereNotNull('received_at')
            ->whereNotNull('delivery_date')
            ->whereColumn('received_at', '<=', 'delivery_date')
            ->count();

        $late = $totalOrders - $onTime;
        $deliveryScore = $totalOrders > 0 ? ($onTime / $totalOrders) * 100 : 0;

        // 5. Price score (bandingkan harga supplier vs rata-rata)
        $priceScore = self::calculatePriceScore($supplier, $start, $end);

        // 6. Total score (weighted)
        $totalScore = ($qualityScore * 0.4) + ($deliveryScore * 0.3) + ($priceScore * 0.3);

        // 7. Rating
        $rating = match(true) {
            $totalScore >= 90 => 'A',
            $totalScore >= 75 => 'B',
            $totalScore >= 60 => 'C',
            default => 'D',
        };

        // 8. Simpan
        $period = $start->format('Y-m');

        return SupplierEvaluation::updateOrCreate(
            [
                'supplier_id' => $supplier->id,
                'period' => $period,
            ],
            [
                'period_start' => $start,
                'period_end' => $end,
                'quality_score' => round($qualityScore, 2),
                'delivery_score' => round($deliveryScore, 2),
                'price_score' => round($priceScore, 2),
                'total_score' => round($totalScore, 2),
                'rating' => $rating,
                'total_orders' => $totalOrders,
                'total_received' => $totalReceived,
                'total_rejected' => $totalRejected,
                'reject_rate' => round($rejectRate, 2),
                'on_time_deliveries' => $onTime,
                'late_deliveries' => $late,
                'evaluated_by' => auth()->id(),
            ]
        );
    }

    /**
     * Hitung price score: seberapa kompetitif harga supplier.
     */
    protected static function calculatePriceScore(Supplier $supplier, Carbon $start, Carbon $end): float
    {
        $prices = \App\Models\Purchasing\SupplierPrice::where('supplier_id', $supplier->id)
            ->active()
            ->get();

        if ($prices->isEmpty()) {
            return 50; // netral kalau tidak ada data
        }

        $score = 0;
        foreach ($prices as $price) {
            $avgPrice = \App\Models\Purchasing\SupplierPrice::where('material_id', $price->material_id)
                ->active()
                ->avg('price');

            if ($avgPrice && $avgPrice > 0) {
                // Kalau harga supplier ≤ rata-rata → score tinggi
                $ratio = $price->price / $avgPrice;
                $score += max(0, min(100, 100 - (($ratio - 1) * 100)));
            } else {
                $score += 50;
            }
        }

        return $score / $prices->count();
    }
}