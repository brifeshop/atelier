<?php

namespace App\Services;

use App\Models\Production\WorkOrder;
use App\Models\Production\CostSnapshot;
use App\Models\Engineering\Bom;
use App\Models\Engineering\Routing;
use Illuminate\Support\Facades\DB;

class CostingService
{
    /**
     * Hitung standard cost dari BOM & Routing
     * 
     * @param  WorkOrder $wo
     * @return array
     */
    public static function calculateStandardCost(WorkOrder $wo): array
    {
        $standardMaterial = 0;
        $standardLabor = 0;
        $standardOverhead = 0;

        // Material dari BOM
        if ($wo->bom_id) {
            $bom = Bom::find($wo->bom_id);
            if ($bom) {
                // Sum dari BOM items (sudah di-cache di boms.total_material_cost)
                $standardMaterial = (float) $bom->total_material_cost * $wo->planned_qty;
            }
        }

        // Labor & Overhead dari Routing
        if ($wo->routing_id) {
            $routing = Routing::find($wo->routing_id);
            if ($routing) {
                $standardLabor = (float) $routing->total_labor_cost * $wo->planned_qty;
                $standardOverhead = (float) $routing->total_overhead_cost * $wo->planned_qty;
            }
        }

        $totalStandard = $standardMaterial + $standardLabor + $standardOverhead;
        $costPerUnit = $wo->planned_qty > 0 ? $totalStandard / $wo->planned_qty : 0;

        return [
            'standard_material_cost'    => round($standardMaterial, 2),
            'standard_labor_cost'       => round($standardLabor, 2),
            'standard_overhead_cost'    => round($standardOverhead, 2),
            'standard_total_cost'       => round($totalStandard, 2),
            'standard_cost_per_unit'    => round($costPerUnit, 2),
        ];
    }

    /**
     * Hitung actual cost dari Work Order
     * 
     * @param  WorkOrder $wo
     * @return array
     */
    public static function calculateActualCost(WorkOrder $wo): array
    {
        $wo->load(['materials', 'progress']);

        // Material: sum dari work_order_materials
        $actualMaterial = $wo->materials->sum('total_cost');

        // Labor & Overhead: sum dari progress
        $actualLabor = $wo->progress->sum('labor_cost');
        $actualOverhead = $wo->progress->sum('overhead_cost');

        // Kalau belum ada progress, pakai dari routing estimate
        if ($actualLabor == 0 && $actualOverhead == 0 && $wo->routing_id) {
            $routing = Routing::find($wo->routing_id);
            if ($routing) {
                $actualLabor = (float) $routing->total_labor_cost * $wo->planned_qty;
                $actualOverhead = (float) $routing->total_overhead_cost * $wo->planned_qty;
            }
        }

        $totalActual = $actualMaterial + $actualLabor + $actualOverhead;
        $costPerUnit = $wo->planned_qty > 0 ? $totalActual / $wo->planned_qty : 0;

        return [
            'actual_material_cost'    => round($actualMaterial, 2),
            'actual_labor_cost'       => round($actualLabor, 2),
            'actual_overhead_cost'    => round($actualOverhead, 2),
            'actual_total_cost'       => round($totalActual, 2),
            'actual_cost_per_unit'    => round($costPerUnit, 2),
        ];
    }

    /**
     * Hitung variance antara standard vs actual
     * 
     * @param  array $standard
     * @param  array $actual
     * @return array
     */
    public static function calculateVariance(array $standard, array $actual): array
    {
        $materialVar = $actual['actual_material_cost'] - $standard['standard_material_cost'];
        $laborVar    = $actual['actual_labor_cost'] - $standard['standard_labor_cost'];
        $overheadVar = $actual['actual_overhead_cost'] - $standard['standard_overhead_cost'];
        $totalVar    = $materialVar + $laborVar + $overheadVar;

        $variancePercent = $standard['standard_total_cost'] > 0
            ? ($totalVar / $standard['standard_total_cost']) * 100
            : 0;

        return [
            'material_variance' => round($materialVar, 2),
            'labor_variance'    => round($laborVar, 2),
            'overhead_variance' => round($overheadVar, 2),
            'total_variance'    => round($totalVar, 2),
            'variance_percent'  => round($variancePercent, 2),
        ];
    }

    /**
     * Buat snapshot cost untuk 1 Work Order
     * 
     * @param  WorkOrder $wo
     * @return CostSnapshot
     */
    public static function createSnapshot(WorkOrder $wo): CostSnapshot
    {
        DB::beginTransaction();
        try {
            $standard = self::calculateStandardCost($wo);
            $actual = self::calculateActualCost($wo);
            $variance = self::calculateVariance($standard, $actual);

            $snapshot = CostSnapshot::create([
                'work_order_id' => $wo->id,
                'product_id'    => $wo->product_id,
                'snapshot_date' => now(),
                'planned_qty'   => $wo->planned_qty,
                'actual_qty'    => $wo->actual_qty,
                'rejected_qty'  => $wo->rejected_qty,
                ...$standard,
                ...$actual,
                ...$variance,
                'created_by'    => auth()->id(),
            ]);

            // Update Work Order dengan actual cost
            $wo->update([
                'total_material_cost' => $actual['actual_material_cost'],
                'total_labor_cost'    => $actual['actual_labor_cost'],
                'total_overhead_cost' => $actual['actual_overhead_cost'],
                'total_cost'          => $actual['actual_total_cost'],
                'cost_per_unit'       => $actual['actual_cost_per_unit'],
            ]);

            DB::commit();
            return $snapshot;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hitung HPP rata-rata per produk
     * 
     * @param  int  $productId
     * @param  int  $monthsBack
     * @return array
     */
    public static function averageCostPerProduct(int $productId, int $monthsBack = 3): array
    {
        $since = now()->subMonths($monthsBack);

        $snapshots = CostSnapshot::where('product_id', $productId)
            ->where('snapshot_date', '>=', $since)
            ->where('actual_qty', '>', 0)
            ->get();

        if ($snapshots->isEmpty()) {
            return [
                'avg_cost_per_unit' => 0,
                'avg_material_cost' => 0,
                'avg_labor_cost'    => 0,
                'avg_overhead_cost' => 0,
                'total_produced'    => 0,
                'total_cost'        => 0,
                'snapshot_count'    => 0,
            ];
        }

        $totalProduced = $snapshots->sum('actual_qty');
        $totalCost = $snapshots->sum('actual_total_cost');
        $avgCostPerUnit = $totalProduced > 0 ? $totalCost / $totalProduced : 0;

        // Breakdown per komponen
        $avgMaterial = $snapshots->avg('actual_material_cost');
        $avgLabor = $snapshots->avg('actual_labor_cost');
        $avgOverhead = $snapshots->avg('actual_overhead_cost');

        return [
            'avg_cost_per_unit' => round($avgCostPerUnit, 2),
            'avg_material_cost' => round($avgMaterial, 2),
            'avg_labor_cost'    => round($avgLabor, 2),
            'avg_overhead_cost' => round($avgOverhead, 2),
            'total_produced'    => round($totalProduced, 2),
            'total_cost'        => round($totalCost, 2),
            'snapshot_count'    => $snapshots->count(),
        ];
    }

    /**
     * Hitung margin per produk
     * 
     * @param  int   $productId
     * @return array
     */
    public static function marginAnalysis(int $productId): array
    {
        $product = \App\Models\Master\Product::find($productId);
        if (!$product) {
            return ['error' => 'Product not found'];
        }

        $avg = self::averageCostPerProduct($productId);
        $sellingPrice = (float) $product->selling_price;
        $avgCost = $avg['avg_cost_per_unit'];

        $grossMargin = $sellingPrice - $avgCost;
        $marginPercent = $sellingPrice > 0 ? ($grossMargin / $sellingPrice) * 100 : 0;

        return [
            'product'         => $product,
            'selling_price'   => $sellingPrice,
            'avg_cost'        => $avgCost,
            'gross_margin'    => round($grossMargin, 2),
            'margin_percent'  => round($marginPercent, 2),
            'avg_detail'      => $avg,
            'is_profitable'   => $grossMargin > 0,
        ];
    }
}