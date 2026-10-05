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
                // Adjustment bisa positif atau negatif
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
}