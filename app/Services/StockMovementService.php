<?php

namespace App\Services;

use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    /**
     * CATAT STOK MASUK (IN)
     * 
     * Contoh pemakaian:
     * - Goods Receipt (M4B)
     * - Produk jadi dari produksi (M6)
     * - Stock opname surplus (M4C)
     */
    public static function recordIn(
        string $itemType,
        int $itemId,
        int $locationId,
        float $qty,
        array $options = []
    ): StockMovement {
        if ($qty <= 0) {
            throw new \Exception('Qty untuk IN harus lebih dari 0.');
        }

        return self::recordMovement($itemType, $itemId, $locationId, 'in', $qty, $options);
    }

    /**
     * CATAT STOK KELUAR (OUT)
     * 
     * Contoh pemakaian:
     * - Material Issue (M6)
     * - Delivery Order (M9)
     * - Stock opname minus (M4C)
     */
    public static function recordOut(
        string $itemType,
        int $itemId,
        int $locationId,
        float $qty,
        array $options = []
    ): StockMovement {
        if ($qty <= 0) {
            throw new \Exception('Qty untuk OUT harus lebih dari 0.');
        }

        // Validasi stok cukup
        $inventory = Inventory::getOrCreate($itemType, $itemId, $locationId);
        if ($inventory->qty < $qty) {
            throw new \Exception("Stok tidak cukup. Tersedia: {$inventory->qty}, diminta: {$qty}.");
        }

        return self::recordMovement($itemType, $itemId, $locationId, 'out', -$qty, $options);
    }

    /**
     * CATAT TRANSFER antar lokasi
     */
    public static function recordTransfer(
        string $itemType,
        int $itemId,
        int $fromLocationId,
        int $toLocationId,
        float $qty,
        array $options = []
    ): array {
        if ($qty <= 0) {
            throw new \Exception('Qty untuk transfer harus lebih dari 0.');
        }

        if ($fromLocationId === $toLocationId) {
            throw new \Exception('Lokasi asal dan tujuan tidak boleh sama.');
        }

        DB::beginTransaction();
        try {
            // OUT dari lokasi asal
            $out = self::recordOut($itemType, $itemId, $fromLocationId, $qty, array_merge($options, [
                'notes' => 'Transfer keluar ke lokasi lain',
                'reference_number' => $options['reference_number'] ?? null,
            ]));

            // IN ke lokasi tujuan
            $in = self::recordIn($itemType, $itemId, $toLocationId, $qty, array_merge($options, [
                'notes' => 'Transfer masuk dari lokasi lain',
                'reference_number' => $options['reference_number'] ?? null,
            ]));

            DB::commit();
            return ['out' => $out, 'in' => $in];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * CATAT ADJUSTMENT (koreksi stok)
     * 
     * qty bisa positif (surplus) atau negatif (minus)
     */
    public static function recordAdjustment(
        string $itemType,
        int $itemId,
        int $locationId,
        float $qty,
        array $options = []
    ): StockMovement {
        if ($qty == 0) {
            throw new \Exception('Qty adjustment tidak boleh 0.');
        }

        return self::recordMovement($itemType, $itemId, $locationId, 'adjustment', $qty, $options);
    }

    /**
     * CORE: Catat movement & update inventory
     */
    protected static function recordMovement(
        string $itemType,
        int $itemId,
        int $locationId,
        string $type,
        float $qty,
        array $options = []
    ): StockMovement {
        DB::beginTransaction();
        try {
            // Get atau create inventory record
            $inventory = Inventory::getOrCreate($itemType, $itemId, $locationId);

            $qtyBefore = (float) $inventory->qty;
            $qtyAfter = $qtyBefore + $qty;

            // Validasi: stok tidak boleh negatif (kecuali adjustment)
            if ($qtyAfter < 0 && $type !== 'adjustment') {
                DB::rollBack();
                throw new \Exception("Stok akan negatif. Qty before: {$qtyBefore}, qty: {$qty}.");
            }

            // Hitung unit_price & total_value
            $unitPrice = $options['unit_price'] ?? null;
            $totalValue = $unitPrice ? abs($qty) * $unitPrice : null;

            // Buat movement record
            $movement = StockMovement::create([
                'movement_number' => StockMovement::generateNumber(),
                'item_type' => $itemType,
                'item_id' => $itemId,
                'location_id' => $locationId,
                'type' => $type,
                'qty' => $qty,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'unit_price' => $unitPrice,
                'total_value' => $totalValue,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'reference_number' => $options['reference_number'] ?? null,
                'date' => $options['date'] ?? now()->toDateString(),
                'notes' => $options['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update inventory
            $inventory->update([
                'qty' => $qtyAfter,
                'last_movement_at' => now(),
            ]);

            DB::commit();
            return $movement;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}