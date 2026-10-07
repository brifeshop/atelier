<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\Location;
use App\Models\Warehouse\Warehouse;
use App\Models\Master\Material;
use App\Models\Master\Product;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use App\Models\Warehouse\Inventory;

class StockMovementController extends Controller
{
    /**
     * List semua movement
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['location.warehouse', 'item', 'createdBy']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('adjustment_reason')) {
            $query->where('adjustment_reason', $request->adjustment_reason);
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('movement_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('item', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%")
                         ->orWhere('kode', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest('date')->latest('id')->paginate(20);

        // Stats
        $stats = [
            'total_movements' => StockMovement::count(),
            'in_count' => StockMovement::where('type', 'in')->count(),
            'out_count' => StockMovement::where('type', 'out')->count(),
            'adjustment_count' => StockMovement::where('type', 'adjustment')->count(),
        ];

        return view('warehouse.stock-movements.index', [
            'items' => $items,
            'stats' => $stats,
            'warehouses' => Warehouse::active()->orderBy('nama')->get(),
        ]);
    }


    /**
     * Laporan khusus adjustment
     */
    public function adjustments(Request $request)
    {
        $query = StockMovement::with(['location.warehouse', 'item', 'createdBy'])
            ->where('type', 'adjustment');

        if ($request->filled('adjustment_reason')) {
            $query->where('adjustment_reason', $request->adjustment_reason);
        }
        if ($request->filled('warehouse_id')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $items = $query->latest('date')->latest('id')->paginate(20)->withQueryString();

        // Stats per reason
        $stats = [
            'total'      => StockMovement::where('type', 'adjustment')->count(),
            'damaged'    => StockMovement::where('type', 'adjustment')->where('adjustment_reason', 'damaged')->count(),
            'lost'       => StockMovement::where('type', 'adjustment')->where('adjustment_reason', 'lost')->count(),
            'correction' => StockMovement::where('type', 'adjustment')->where('adjustment_reason', 'correction')->count(),
            'opname'     => StockMovement::where('type', 'adjustment')->where('adjustment_reason', 'opname')->count(),
        ];

        return view('warehouse.stock-movements.adjustments', [
            'items'      => $items,
            'stats'      => $stats,
            'warehouses' => Warehouse::active()->orderBy('nama')->get(),
        ]);
    }
    /**
     * Form create movement (manual)
     */
    public function create(Request $request)
    {
        $type = $request->input('type', 'in');

        return view('warehouse.stock-movements.create', [
            'type' => $type,
            'warehouses' => Warehouse::active()->orderBy('nama')->get(),
            'locations' => Location::active()->with('warehouse')->orderBy('kode')->get(),
            'materials' => Material::active()->orderBy('nama')->get(),
            'products' => Product::active()->orderBy('nama')->get(),
            'generatedNumber' => StockMovement::generateNumber(),
        ]);
    }

    /**
     * Store movement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'item_type' => 'required|in:material,product',
            'item_id' => 'required|integer',
            'location_id' => 'required|exists:locations,id',
            'qty' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'adjustment_reason' => 'required_if:type,adjustment|nullable|string|in:damaged,lost,expired,correction,found,opname,other',
        ]);

        try {
            $options = [
                'unit_price' => $validated['unit_price'] ?? null,
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
                'reference_number' => 'Manual',
            ];

            if ($validated['type'] === 'in') {
                StockMovementService::recordIn(
                    $validated['item_type'],
                    (int) $validated['item_id'],
                    (int) $validated['location_id'],
                    (float) $validated['qty'],
                    $options
                );
            } elseif ($validated['type'] === 'out') {
                StockMovementService::recordOut(
                    $validated['item_type'],
                    (int) $validated['item_id'],
                    (int) $validated['location_id'],
                    (float) $validated['qty'],
                    $options
                );
            } elseif ($validated['type'] === 'adjustment') {
                $options['adjustment_reason'] = $validated['adjustment_reason'];
                
                StockMovementService::recordAdjustment(
                    $validated['item_type'],
                    (int) $validated['item_id'],
                    (int) $validated['location_id'],
                    (float) $validated['qty'],
                    $options
                );
            }

            return redirect()
                ->route('warehouse.stock-movements.index')
                ->with('success', 'Stock movement berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show detail
     */
    public function show(StockMovement $stockMovement)
    {
        $stockMovement->load(['location.warehouse', 'item', 'createdBy']);
        return view('warehouse.stock-movements.show', ['item' => $stockMovement]);
    }

    /**
     * Form transfer
     */
    public function createTransfer()
    {
        return view('warehouse.stock-movements.transfer', [
            'locations' => Location::active()->with('warehouse')->orderBy('kode')->get(),
            'materials' => Material::active()->orderBy('nama')->get(),
            'products' => Product::active()->orderBy('nama')->get(),
        ]);
    }

    /**
     * Store transfer
     */
    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:material,product',
            'item_id' => 'required|integer',
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'qty' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ], [
            'to_location_id.different' => 'Lokasi tujuan harus berbeda dengan lokasi asal.',
        ]);

        try {
            StockMovementService::recordTransfer(
                $validated['item_type'],
                (int) $validated['item_id'],
                (int) $validated['from_location_id'],
                (int) $validated['to_location_id'],
                (float) $validated['qty'],
                [
                    'date' => $validated['date'],
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            return redirect()
                ->route('warehouse.stock-movements.index')
                ->with('success', 'Transfer berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * API: Get stock info for item at location
     * 
     * GET /warehouse/stock-movements/get-stock?item_type=material&item_id=5&location_id=2
     */
    public function getStock(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:material,product',
            'item_id' => 'required|integer',
            'location_id' => 'required|integer',
        ]);

        $inventory = Inventory::where('item_type', $request->item_type)
            ->where('item_id', $request->item_id)
            ->where('location_id', $request->location_id)
            ->first();

        return response()->json([
            'success' => true,
            'qty' => $inventory ? (float) $inventory->qty : 0,
            'min_stock' => $inventory ? (float) ($inventory->min_stock ?? 0) : 0,
            'max_stock' => $inventory ? (float) ($inventory->max_stock ?? 0) : 0,
            'unit_price' => $inventory ? (float) $inventory->unit_price : 0,
            'is_low_stock' => $inventory ? $inventory->is_low_stock : false,
            'location_name' => $inventory?->location->nama ?? null,
        ]);
    }

    /**
     * API: Get list of locations that have stock for a given item
     * 
     * GET /warehouse/stock-movements/get-locations?item_type=material&item_id=5&min_qty=1
     */
    public function getLocations(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:material,product',
            'item_id' => 'required|integer',
        ]);

        $minQty = $request->input('min_qty', 0);

        $inventories = Inventory::with(['location.warehouse'])
            ->where('item_type', $request->item_type)
            ->where('item_id', $request->item_id)
            ->where('qty', '>=', $minQty)
            ->orderBy('qty', 'desc')
            ->get();

        $locations = $inventories->map(fn($inv) => [
            'id' => (string) $inv->id,
            'location_id' => (string) $inv->location_id,
            'label' => ($inv->location->kode ?? '-') . ' — ' . ($inv->location->nama ?? '-') 
                    . ' (' . ($inv->location->warehouse->nama ?? '-') . ')'
                    . ' — Stok: ' . number_format($inv->qty, 2),
            'qty' => (float) $inv->qty,
            'unit_price' => (float) $inv->unit_price,
        ]);

        return response()->json([
            'success' => true,
            'locations' => $locations,
        ]);
    }
}