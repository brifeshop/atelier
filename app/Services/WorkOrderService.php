<?php

namespace App\Services;

use App\Models\Production\WorkOrder;
use App\Models\Production\WorkOrderMaterial;
use App\Models\Production\WorkOrderProgress;
use App\Models\Engineering\Bom;
use App\Models\Engineering\Routing;
use App\Models\Master\Material;
use App\Models\Master\Product;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    /**
     * Generate material requirement dari BOM
     * 
     * @param  WorkOrder $wo
     * @return int  Jumlah material yang ditambahkan
     */
    public static function generateMaterials(WorkOrder $wo): int
    {
        if (!$wo->bom_id) {
            throw new \Exception('Work Order belum punya BOM. Silakan pilih BOM dulu.');
        }

        $bom = Bom::with('items.item')->find($wo->bom_id);
        if (!$bom) {
            throw new \Exception('BOM tidak ditemukan.');
        }

        // Hapus material lama (kalau re-generate)
        $wo->materials()->delete();

        $count = 0;
        $totalMaterialCost = 0;

        DB::beginTransaction();
        try {
            foreach ($bom->items as $bomItem) {
                // Hanya ambil material (skip sub-assembly untuk sekarang)
                // Kalau item_type = 'product', kita perlu explode sub-BOM juga
                $materials = self::explodeMaterials($bomItem, $wo->planned_qty);

                foreach ($materials as $mat) {
                    $requiredQty = $mat['qty'];
                    $unitCost = $mat['unit_cost'];
                    $totalCost = $requiredQty * $unitCost;

                    WorkOrderMaterial::create([
                        'work_order_id'  => $wo->id,
                        'material_id'    => $mat['material_id'],
                        'required_qty'   => $requiredQty,
                        'issued_qty'     => 0,
                        'unit'           => $mat['unit'],
                        'scrap_percent'  => $mat['scrap_percent'],
                        'unit_cost'      => $unitCost,
                        'total_cost'     => $totalCost,
                        'status'         => 'pending',
                    ]);

                    $totalMaterialCost += $totalCost;
                    $count++;
                }
            }

            $wo->update([
                'total_material_cost' => round($totalMaterialCost, 2),
            ]);

            DB::commit();
            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Explode BOM item → material list (rekursif untuk multi-level)
     */
    private static function explodeMaterials($bomItem, float $parentQty, array $visited = []): array
    {
        $result = [];

        // Kalau material → langsung
        if ($bomItem->item_type === 'material') {
            $material = Material::find($bomItem->item_id);
            if (!$material) return [];

            // Qty = qty BOM × parent qty × (1 + scrap%)
            $scrapMultiplier = 1 + ($bomItem->scrap_percent / 100);
            $requiredQty = (float) $bomItem->qty * $parentQty * $scrapMultiplier;

            $result[] = [
                'material_id'    => $material->id,
                'qty'            => round($requiredQty, 4),
                'unit'           => $bomItem->unit,
                'scrap_percent'  => $bomItem->scrap_percent,
                'unit_cost'      => (float) $bomItem->unit_cost,
            ];

            return $result;
        }

        // Kalau product (sub-assembly) → rekursif cari BOM-nya
        if ($bomItem->item_type === 'product') {
            $subProductId = $bomItem->item_id;

            // Cegah circular
            if (in_array($subProductId, $visited)) {
                return [];
            }

            $subBom = Bom::where('product_id', $subProductId)
                ->where('status', 'active')
                ->latest('effective_date')
                ->first();

            if (!$subBom) {
                // Tidak ada BOM → anggap sebagai material langsung (dengan selling_price)
                $product = Product::find($subProductId);
                if (!$product) return [];

                $scrapMultiplier = 1 + ($bomItem->scrap_percent / 100);
                $requiredQty = (float) $bomItem->qty * $parentQty * $scrapMultiplier;

                // Skip — product tanpa BOM tidak bisa di-explode jadi material
                return [];
            }

            $subBom->load('items.item');
            $subQty = (float) $bomItem->qty * $parentQty;

            foreach ($subBom->items as $subItem) {
                $subResults = self::explodeMaterials($subItem, $subQty, array_merge($visited, [$subProductId]));
                $result = array_merge($result, $subResults);
            }
        }

        return $result;
    }

    /**
     * Generate progress steps dari Routing
     */
    public static function generateProgress(WorkOrder $wo): int
    {
        if (!$wo->routing_id) {
            return 0;
        }

        $routing = Routing::with('steps')->find($wo->routing_id);
        if (!$routing) return 0;

        $wo->progress()->delete();

        $count = 0;
        foreach ($routing->steps as $step) {
            WorkOrderProgress::create([
                'work_order_id'   => $wo->id,
                'routing_step_id' => $step->id,
                'sequence'        => $step->sequence,
                'operation_name'  => $step->operation_name,
                'work_center_id'  => $step->work_center_id,
                'status'          => 'pending',
                'qty_completed'   => 0,
                'qty_rejected'    => 0,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Release Work Order:
     * - Generate materials dari BOM
     * - Generate progress dari Routing
     * - Hitung total cost (material + labor + overhead)
     * - Set status = released
     */
    public static function release(WorkOrder $wo): WorkOrder
    {
        DB::beginTransaction();
        try {
            $materialCount = self::generateMaterials($wo);
            $progressCount = self::generateProgress($wo);

            // Hitung labor & overhead dari routing
            $totalLabor = 0;
            $totalOverhead = 0;

            if ($wo->routing_id) {
                $routing = Routing::find($wo->routing_id);
                if ($routing) {
                    $totalLabor = (float) $routing->total_labor_cost * $wo->planned_qty;
                    $totalOverhead = (float) $routing->total_overhead_cost * $wo->planned_qty;
                }
            }

            $totalMaterial = (float) $wo->total_material_cost;
            $totalCost = $totalMaterial + $totalLabor + $totalOverhead;
            $costPerUnit = $wo->planned_qty > 0 ? $totalCost / $wo->planned_qty : 0;

            $wo->update([
                'status'                => 'released',
                'total_labor_cost'      => round($totalLabor, 2),
                'total_overhead_cost'   => round($totalOverhead, 2),
                'total_cost'            => round($totalCost, 2),
                'cost_per_unit'         => round($costPerUnit, 2),
                'approved_by'           => auth()->id(),
                'approved_at'           => now(),
            ]);

            DB::commit();
            return $wo->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Start Work Order (mulai produksi)
     */
    public static function start(WorkOrder $wo): WorkOrder
    {
        $wo->update([
            'status'            => 'in_progress',
            'actual_start_date' => now(),
        ]);

        return $wo->fresh();
    }

    /**
     * Complete Work Order
     */
    public static function complete(WorkOrder $wo): WorkOrder
    {
        DB::beginTransaction();
        try {
            $wo->update([
                'status'          => 'completed',
                'actual_end_date' => now(),
            ]);

            DB::commit();
            return $wo->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel Work Order
     */
    public static function cancel(WorkOrder $wo): WorkOrder
    {
        $wo->update(['status' => 'cancelled']);
        return $wo->fresh();
    }

    /**
     * Recalculate cost
     */
    public static function recalculateCost(WorkOrder $wo): WorkOrder
    {
        DB::beginTransaction();
        try {
            // Sum material dari wo_materials
            $totalMaterial = $wo->materials()->sum('total_cost');

            // Sum labor & overhead dari progress
            $totalLabor = $wo->progress()->sum('labor_cost');
            $totalOverhead = $wo->progress()->sum('overhead_cost');

            // Kalau belum ada progress, ambil dari routing
            if ($totalLabor == 0 && $totalOverhead == 0 && $wo->routing_id) {
                $routing = Routing::find($wo->routing_id);
                if ($routing) {
                    $totalLabor = (float) $routing->total_labor_cost * $wo->planned_qty;
                    $totalOverhead = (float) $routing->total_overhead_cost * $wo->planned_qty;
                }
            }

            $totalCost = $totalMaterial + $totalLabor + $totalOverhead;
            $costPerUnit = $wo->planned_qty > 0 ? $totalCost / $wo->planned_qty : 0;

            $wo->update([
                'total_material_cost' => round($totalMaterial, 2),
                'total_labor_cost'    => round($totalLabor, 2),
                'total_overhead_cost' => round($totalOverhead, 2),
                'total_cost'          => round($totalCost, 2),
                'cost_per_unit'       => round($costPerUnit, 2),
            ]);

            DB::commit();
            return $wo->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}