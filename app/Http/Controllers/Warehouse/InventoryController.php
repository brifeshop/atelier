<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\Location;
use App\Models\Warehouse\Warehouse;
use App\Models\Master\Material;
use App\Models\Master\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * List semua inventory
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['location.warehouse', 'item']);

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        // Filter by type
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter low stock
        if ($request->filled('low_stock')) {
            $query->lowStock();
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', function ($q2) use ($search) {
                    $q2->where('nama', 'like', "%{$search}%")
                       ->orWhere('kode', 'like', "%{$search}%");
                });
            });
        }

        $items = $query->latest('last_movement_at')->paginate(15);

        // Stats
        $stats = [
            'total_items' => Inventory::where('qty', '>', 0)->count(),
            'total_locations' => Location::active()->count(),
            'low_stock_count' => Inventory::lowStock()->count(),
            'total_value' => 0, // nanti dihitung
        ];

        return view('warehouse.inventories.index', [
            'items' => $items,
            'stats' => $stats,
            'warehouses' => Warehouse::active()->orderBy('nama')->get(),
        ]);
    }

    /**
     * Show detail inventory
     */
    public function show(Inventory $inventory)
    {
        $inventory->load(['location.warehouse', 'item']);
        return view('warehouse.inventories.show', ['item' => $inventory]);
    }

    /**
     * Update min/max stock
     */
    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
        ]);

        $inventory->update($validated);

        return back()->with('success', 'Setting stok berhasil diperbarui.');
    }
}