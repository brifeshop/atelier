<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\Warehouse;
use Illuminate\Http\Request;

class LowStockController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['item', 'location.warehouse'])
            ->lowStock()
            ->whereNotNull('min_stock');

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        // Filter by item type
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Urutkan: yang paling kritis dulu (gap terbesar)
        $items = $query->orderByRaw('(min_stock - qty) DESC')
            ->paginate(20)
            ->withQueryString();

        // Hitung stats
        $allLowStocks = Inventory::lowStock()->whereNotNull('min_stock')->get();

        $stats = [
            'total_low'      => $allLowStocks->count(),
            'total_material' => $allLowStocks->where('item_type', 'material')->count(),
            'total_product'  => $allLowStocks->where('item_type', 'product')->count(),
            'total_value'    => $allLowStocks->sum(function ($inv) {
                return ($inv->min_stock - $inv->qty) * $inv->unit_price;
            }),
        ];

        return view('warehouse.low-stocks.index', [
            'items'      => $items,
            'stats'      => $stats,
            'warehouses' => Warehouse::active()->orderBy('nama')->get(),
        ]);
    }
}