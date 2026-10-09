<?php

namespace App\Services;

use App\Models\Production\MaterialIssue;
use App\Models\Production\MaterialIssueItem;
use App\Models\Production\WorkOrder;
use App\Models\Production\WorkOrderMaterial;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use Illuminate\Support\Facades\DB;

class MaterialIssueService
{
    /**
     * Generate nomor issue
     */
    public static function generateNumber(): string
    {
        $prefix = 'MI-' . date('Y-m') . '-';
        $last = MaterialIssue::where('issue_number', 'like', "{$prefix}%")
            ->orderByDesc('issue_number')
            ->first();

        $lastNumber = $last ? (int) substr($last->issue_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Issue material — potong stok & update WO
     * 
     * @param  MaterialIssue $issue
     * @return void
     */
    public static function issue(MaterialIssue $issue): void
    {
        $issue->load(['items.workOrderMaterial.material', 'workOrder']);

        DB::beginTransaction();
        try {
            foreach ($issue->items as $item) {
                $material = $item->material;
                if (!$material) continue;

                // Ambil inventory
                $inventory = Inventory::getOrCreate('material', $material->id, $item->location_id);

                if ($inventory->qty < $item->qty) {
                    throw new \Exception("Stok tidak cukup untuk {$material->nama}. Tersedia: {$inventory->qty}, diminta: {$item->qty}");
                }

                $qtyBefore = (float) $inventory->qty;
                $qtyAfter = $qtyBefore - $item->qty;

                // Update inventory
                $inventory->update([
                    'qty' => $qtyAfter,
                    'last_movement_at' => now(),
                ]);

                // Catat stock movement (OUT)
                StockMovement::create([
                    'movement_number'   => StockMovement::generateNumber(),
                    'item_type'         => 'material',
                    'item_id'           => $material->id,
                    'location_id'       => $item->location_id,
                    'type'              => 'out',
                    'qty'               => -$item->qty,
                    'qty_before'        => $qtyBefore,
                    'qty_after'         => $qtyAfter,
                    'unit_price'        => $item->unit_cost,
                    'total_value'       => $item->total_cost,
                    'reference_type'    => MaterialIssue::class,
                    'reference_id'      => $issue->id,
                    'reference_number'  => $issue->issue_number,
                    'date'              => $issue->date,
                    'notes'             => "Material Issue untuk WO {$issue->workOrder->wo_number}",
                    'created_by'        => auth()->id(),
                ]);

                // Update Work Order Material
                if ($item->workOrderMaterial) {
                    $wom = $item->workOrderMaterial;
                    $newIssuedQty = (float) $wom->issued_qty + (float) $item->qty;

                    $status = 'partial';
                    if ($newIssuedQty >= (float) $wom->required_qty) {
                        $status = 'issued';
                    } elseif ($newIssuedQty == 0) {
                        $status = 'pending';
                    }

                    $wom->update([
                        'issued_qty' => $newIssuedQty,
                        'status'     => $status,
                    ]);
                }
            }

            // Update status issue
            $issue->update([
                'status'     => 'issued',
                'issued_by'  => auth()->id(),
                'issued_at'  => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}