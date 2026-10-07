<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\Location;
use App\Models\Master\Material;
use App\Models\Master\Product;
use Illuminate\Http\Request;

class StockCardController extends Controller
{
    /**
     * List kartu stok (dengan filter)
     */
    public function index(Request $request)
    {
        $movements = collect();
        $openingBalance = 0;
        $summary = [
            'total_in' => 0,
            'total_out' => 0,
            'closing_balance' => 0,
        ];

        // Hanya query kalau item & lokasi sudah dipilih
        $hasFilter = $request->filled('item_type')
                  && $request->filled('item_id')
                  && $request->filled('location_id');

        if ($hasFilter) {
            $query = StockMovement::with(['location.warehouse', 'createdBy'])
                ->where('item_type', $request->item_type)
                ->where('item_id', $request->item_id)
                ->where('location_id', $request->location_id);

            // Hitung saldo awal (dari movement terakhir sebelum date_from)
            if ($request->filled('date_from')) {
                $openingBalance = $this->calculateOpeningBalance(
                    $request->item_type,
                    (int) $request->item_id,
                    (int) $request->location_id,
                    $request->date_from
                );
                $query->whereDate('date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            $movements = $query->orderBy('date')->orderBy('id')->get();

            // Hitung summary
            $summary['total_in'] = $movements->where('qty', '>', 0)->sum('qty');
            $summary['total_out'] = abs($movements->where('qty', '<', 0)->sum('qty'));
            $summary['closing_balance'] = $movements->last()->qty_after ?? $openingBalance;
        }

        return view('warehouse.stock-cards.index', [
            'movements'      => $movements,
            'openingBalance' => $openingBalance,
            'summary'        => $summary,
            'locations'      => Location::active()->with('warehouse')->orderBy('kode')->get(),
            'materials'      => Material::active()->orderBy('nama')->get(),
            'products'       => Product::active()->orderBy('nama')->get(),
            'hasFilter'      => $hasFilter,
        ]);
    }

    /**
     * Hitung saldo awal sebelum periode
     */
    private function calculateOpeningBalance($itemType, int $itemId, int $locationId, string $dateFrom): float
    {
        $lastMovement = StockMovement::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->whereDate('date', '<', $dateFrom)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        return $lastMovement ? (float) $lastMovement->qty_after : 0;
    }
}