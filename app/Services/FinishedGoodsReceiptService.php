<?php

namespace App\Services;

use App\Models\Production\FinishedGoodsReceipt;
use App\Models\Production\FinishedGoodsReceiptItem;
use App\Models\Production\WorkOrder;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use Illuminate\Support\Facades\DB;

class FinishedGoodsReceiptService
{
    /**
     * Generate nomor FGR
     */
    public static function generateNumber(): string
    {
        $prefix = 'FGR-' . date('Y-m') . '-';
        $last = FinishedGoodsReceipt::where('fgr_number', 'like', "{$prefix}%")
            ->orderByDesc('fgr_number')
            ->first();

        $lastNumber = $last ? (int) substr($last->fgr_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Receive — tambah stok produk
     * 
     * @param  FinishedGoodsReceipt $fgr
     * @return void
     */
    public static function receive(FinishedGoodsReceipt $fgr): void
    {
        if ($fgr->status === 'received') {
            throw new \Exception('FG Receipt ini sudah diterima.');
        }

        if ($fgr->qty_good <= 0) {
            throw new \Exception('Qty good harus lebih dari 0.');
        }

        $fgr->load(['items', 'product', 'workOrder']);

        DB::beginTransaction();
        try {
            foreach ($fgr->items as $item) {
                $inventory = Inventory::getOrCreate('product', $fgr->product_id, $item->location_id);

                $qtyBefore = (float) $inventory->qty;
                $qtyAfter = $qtyBefore + (float) $item->qty;

                // Update inventory
                $inventory->update([
                    'qty' => $qtyAfter,
                    'last_movement_at' => now(),
                ]);

                // Catat stock movement (IN)
                StockMovement::create([
                    'movement_number'   => StockMovement::generateNumber(),
                    'item_type'         => 'product',
                    'item_id'           => $fgr->product_id,
                    'location_id'       => $item->location_id,
                    'type'              => 'in',
                    'qty'               => $item->qty,
                    'qty_before'        => $qtyBefore,
                    'qty_after'         => $qtyAfter,
                    'unit_price'        => $item->unit_cost,
                    'total_value'       => $item->total_value,
                    'reference_type'    => FinishedGoodsReceipt::class,
                    'reference_id'      => $fgr->id,
                    'reference_number'  => $fgr->fgr_number,
                    'date'              => $fgr->date,
                    'notes'             => "FG Receipt dari WO {$fgr->workOrder->wo_number}",
                    'created_by'        => auth()->id(),
                ]);
            }

            // Update status
            $fgr->update([
                'status'      => 'received',
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);

            // Update work order actual_qty (kalau belum di-set)
            self::updateWorkOrderQty($fgr);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update WO actual_qty & rejected_qty dari total FG Receipt
     */
    protected static function updateWorkOrderQty(FinishedGoodsReceipt $fgr): void
    {
        $wo = $fgr->workOrder;
        if (!$wo) return;

        // Sum semua FG Receipt
        $totalGood = $wo->finishedGoodsReceipts()
            ->where('status', 'received')
            ->sum('qty_good');

        $totalRejected = $wo->finishedGoodsReceipts()
            ->where('status', 'received')
            ->sum('qty_rejected');

        // Update WO
        $wo->update([
            'actual_qty'   => $totalGood,
            'rejected_qty' => $totalRejected,
        ]);

        // Recalculate cost per unit dari WO
        if ($wo->planned_qty > 0 && $totalGood > 0) {
            // Update unit_cost di FGR kalau belum di-set
            if ($fgr->unit_cost == 0) {
                $fgr->update(['unit_cost' => $wo->cost_per_unit]);
            }
        }
    }

    /**
     * Cancel FG Receipt (kalau draft)
     */
    public static function cancel(FinishedGoodsReceipt $fgr): void
    {
        if ($fgr->status === 'received') {
            throw new \Exception('FG Receipt yang sudah diterima tidak bisa dibatalkan.');
        }

        $fgr->update(['status' => 'cancelled']);
    }
}