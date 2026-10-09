<?php

namespace App\Services;

use App\Models\Engineering\Bom;
use App\Models\Engineering\BomItem;
use App\Models\Master\Material;
use App\Models\Master\Product;
use App\Models\Engineering\Routing;
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

        foreach ($bom->items as $item) {
            // Skip header group — tidak dihitung
            if ($item->is_header) {
                continue;
            }

            // Kalau item adalah material → hitung langsung
            if ($item->item_type === 'material') {
                $material = Material::find($item->item_id);
                if (!$material) continue;

                $cost = self::calculateItemCost($item, $material);
                $item->update([
                    'unit_cost'  => $cost['unit_cost'],
                    'total_cost' => $cost['total_cost'],
                ]);

                $totalMaterial += $cost['total_cost'];
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
                    $scrapMultiplier = 1 + ((float) $item->scrap_percent / 100);
                    $totalCost = (float) $item->qty * $unitCost * $scrapMultiplier;

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
                $scrapMultiplier = 1 + ((float) $item->scrap_percent / 100);
                $totalCost = (float) $item->qty * $unitCost * $scrapMultiplier;

                $item->update([
                    'unit_cost'  => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                $totalMaterial += $totalCost;
            }
        }

        // Ambil dari routing (kalau ada)
        $totalLabor = 0;
        $totalOverhead = 0;

        $routing = Routing::where('product_id', $bom->product_id)
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
     * ⭐ INTI: Hitung cost per item berdasarkan costing_method material
     * 
     * Support 5 metode:
     * - per_unit   : beli pcs, pakai pcs
     * - per_area   : beli lembar, pakai luas (mm²)
     * - per_volume : beli batang, pakai volume (mm³)
     * - per_length : beli roll, pakai panjang (mm)
     * - per_weight : beli karung, pakai berat (gram)
     * 
     * @param  BomItem  $bomItem
     * @param  Material $material
     * @return array ['unit_cost' => float, 'total_cost' => float, 'method' => string]
     */
    public static function calculateItemCost(BomItem $bomItem, Material $material): array
    {
        $method = $material->costing_method ?: 'per_unit';
        $yield = ((float) ($material->yield_percent ?: 100)) / 100;
        $scrapMultiplier = 1 + ((float) $bomItem->scrap_percent / 100);
        $qty = (float) $bomItem->qty;
        $price = (float) $material->price;

        $unitCost = 0;

        switch ($method) {
            case 'per_unit':
                // Beli pcs, pakai pcs
                // unit_cost = price
                $unitCost = $price;
                break;

            case 'per_area':
                // Beli lembar, pakai luas
                // price_per_mm2 = price / (panjang × lebar)
                // unit_cost = (panjang_pakai × lebar_pakai) × price_per_mm2
                $areaStandar = (float) $material->panjang_standar * (float) $material->lebar_standar;

                if ($areaStandar > 0) {
                    $pricePerMm2 = $price / $areaStandar;
                    $areaPakai = (float) $bomItem->panjang_pakai * (float) $bomItem->lebar_pakai;
                    $unitCost = $areaPakai * $pricePerMm2;
                }
                break;

            case 'per_volume':
            // Cek jenis volume
            if ($material->volume_type === 'cair') {
                // Volume Cair (ml)
                $volumeStandar = (float) $material->volume_standar;
                $volumePakai = (float) $bomItem->volume_pakai;

                if ($volumeStandar > 0 && $volumePakai > 0) {
                    $pricePerMl = $price / $volumeStandar;
                    $unitCost = $volumePakai * $pricePerMl;
                }
            } else {
                // Volume Kotak (P × L × T)
                $volumeStandar = (float) $material->panjang_standar
                    * (float) $material->lebar_standar
                    * (float) $material->tinggi_standar;

                if ($volumeStandar > 0) {
                    $pricePerMm3 = $price / $volumeStandar;
                    $volumePakai = (float) $bomItem->panjang_pakai
                        * (float) $bomItem->lebar_pakai
                        * (float) $bomItem->tinggi_pakai;
                    $unitCost = $volumePakai * $pricePerMm3;
                }
            }
            break;

            case 'per_length':
                // Beli roll, pakai panjang
                // price_per_mm = price / panjang_standar
                // unit_cost = panjang_pakai × price_per_mm
                $panjangStandar = (float) $material->panjang_standar;

                if ($panjangStandar > 0) {
                    $pricePerMm = $price / $panjangStandar;
                    $unitCost = (float) $bomItem->panjang_pakai * $pricePerMm;
                }
                break;

            case 'per_weight':
                // Beli karung, pakai berat
                // price_per_gram = price / berat_standar
                // unit_cost = berat_pakai × price_per_gram
                $beratStandar = (float) $material->berat_standar;

                if ($beratStandar > 0) {
                    $pricePerGram = $price / $beratStandar;
                    $unitCost = (float) $bomItem->berat_pakai * $pricePerGram;
                }
                break;

            default:
                $unitCost = $price;
        }

        // Apply yield (waste factor)
        if ($yield > 0) {
            $unitCost = $unitCost / $yield;
        }

        // Total = qty × unit_cost × (1 + scrap%)
        $totalCost = $qty * $unitCost * $scrapMultiplier;

        return [
            'unit_cost'  => round($unitCost, 2),
            'total_cost' => round($totalCost, 2),
            'method'     => $method,
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
                'level'         => $level,
                'sequence'      => $item->sequence,
                'item_type'     => $item->item_type,
                'item_id'       => $item->item_id,
                'kode'          => $item->item->kode ?? '-',
                'kode_bahan'    => $item->item->kode_bahan ?? null,
                'nama'          => $item->item->nama ?? '-',
                'spesifikasi'   => $item->spesifikasi,
                'divisi'        => $item->divisi,
                'level_label'   => $item->level,
                'qty'           => (float) $item->qty,
                'unit'          => $item->unit,
                'dimension'     => $item->dimension_label,
                'scrap_percent' => (float) $item->scrap_percent,
                'unit_cost'     => (float) $item->unit_cost,
                'total_cost'    => (float) $item->total_cost,
                'children'      => [],
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