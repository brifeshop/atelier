<?php

namespace App\Services;

use App\Models\Engineering\Bom;
use App\Models\Engineering\BomItem;
use App\Models\Master\Material;
use App\Models\Master\Product;
use Illuminate\Support\Facades\DB;

class BomService
{
    /**
     * Hitung total cost BOM (rekursif untuk multi-level)
     * 
     * @param  Bom   $bom
     * @param  array $visited  Daftar product_id yang sudah dikunjungi (cegah circular reference)
     * @return array
     */
    public static function calculateCost(Bom $bom, array $visited = []): array
    {
        $bom->load('items.item');

        $totalMaterial = 0;
        $totalLabor = 0;
        $totalOverhead = 0;

        foreach ($bom->items as $item) {
            // Kalau item adalah material → hitung langsung
            if ($item->item_type === 'material') {
                $material = Material::find($item->item_id);
                if (!$material) continue;

                $unitCost = (float) $material->price;
                $totalCost = $item->qty * $unitCost * (1 + ($item->scrap_percent / 100));

                $item->update([
                    'unit_cost'  => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                $totalMaterial += $totalCost;
                continue;
            }

            // Kalau item adalah product (sub-assembly) → REKURSIF
            if ($item->item_type === 'product') {
                $subProductId = $item->item_id;

                // Cegah circular reference
                if (in_array($subProductId, $visited)) {
                    throw new \Exception("Circular reference terdeteksi pada product ID: {$subProductId}");
                }

                $subProduct = Product::find($subProductId);
                if (!$subProduct) continue;

                // Cari BOM aktif untuk sub-product
                $subBom = Bom::where('product_id', $subProductId)
                    ->where('status', 'active')
                    ->where('effective_date', '<=', now())
                    ->latest('effective_date')
                    ->first();

                // Kalau sub-product tidak punya BOM, anggap material biasa
                if (!$subBom) {
                    $unitCost = (float) ($subProduct->selling_price ?? 0);
                    $totalCost = $item->qty * $unitCost * (1 + ($item->scrap_percent / 100));

                    $item->update([
                        'unit_cost'  => $unitCost,
                        'total_cost' => $totalCost,
                    ]);

                    $totalMaterial += $totalCost;
                    continue;
                }

                // Hitung rekursif sub-BOM
                $subCost = self::calculateCost($subBom, array_merge($visited, [$subProductId]));

                $unitCost = $subCost['total_cost'];
                $totalCost = $item->qty * $unitCost * (1 + ($item->scrap_percent / 100));

                $item->update([
                    'unit_cost'  => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                // Total dari sub-BOM langsung ditambahkan
                $totalMaterial += $totalCost;
            }
        }

        // Ambil dari routing (kalau ada)
        $routing = \App\Models\Engineering\Routing::where('product_id', $bom->product_id)
            ->where('status', 'active')
            ->latest('effective_date')
            ->first();

        if ($routing) {
            $totalLabor = (float) $routing->total_labor_cost;
            $totalOverhead = (float) $routing->total_overhead_cost;
        }

        $totalCost = $totalMaterial + $totalLabor + $totalOverhead;

        return [
            'total_material_cost' => round($totalMaterial, 2),
            'total_labor_cost'    => round($totalLabor, 2),
            'total_overhead_cost' => round($totalOverhead, 2),
            'total_cost'          => round($totalCost, 2),
        ];
    }

    /**
     * Update BOM total cost di database
     */
    public static function updateCost(Bom $bom): Bom
    {
        DB::beginTransaction();
        try {
            $cost = self::calculateCost($bom);

            $bom->update($cost);

            DB::commit();
            return $bom->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Recalculate semua BOM yang aktif
     */
    public static function recalculateAll(): int
    {
        $boms = Bom::where('status', 'active')->get();
        $count = 0;

        foreach ($boms as $bom) {
            try {
                self::updateCost($bom);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Failed to recalculate BOM {$bom->kode}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Ambil struktur BOM sebagai tree (untuk tampilan)
     */
    public static function getTree(Bom $bom, array $visited = [], int $level = 0): array
    {
        $bom->load('items.item');
        $tree = [];

        foreach ($bom->items as $item) {
            $node = [
                'level'      => $level,
                'sequence'   => $item->sequence,
                'item_type'  => $item->item_type,
                'item_id'    => $item->item_id,
                'kode'       => $item->item->kode ?? '-',
                'nama'       => $item->item->nama ?? '-',
                'qty'        => (float) $item->qty,
                'unit'       => $item->unit,
                'scrap_percent' => (float) $item->scrap_percent,
                'unit_cost'  => (float) $item->unit_cost,
                'total_cost' => (float) $item->total_cost,
                'children'   => [],
            ];

            // Kalau product → cari sub-BOM
            if ($item->item_type === 'product' && !in_array($item->item_id, $visited)) {
                $subBom = Bom::where('product_id', $item->item_id)
                    ->where('status', 'active')
                    ->latest('effective_date')
                    ->first();

                if ($subBom) {
                    $node['children'] = self::getTree($subBom, array_merge($visited, [$item->item_id]), $level + 1);
                }
            }

            $tree[] = $node;
        }

        return $tree;
    }
}