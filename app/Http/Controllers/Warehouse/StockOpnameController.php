<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\StockOpname;
use App\Models\Warehouse\StockOpnameItem;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\Location;
use App\Models\Warehouse\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    /**
     * List semua opname
     */
    public function index(Request $request)
    {
        $query = StockOpname::with(['location.warehouse', 'creator']);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('opname_number', 'like', "%{$search}%")
                  ->orWhereHas('location', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%")
                         ->orWhere('kode', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest('opname_date')->latest('id')->paginate(20);

        $stats = [
            'total'       => StockOpname::count(),
            'draft'       => StockOpname::where('status', 'draft')->count(),
            'in_progress' => StockOpname::where('status', 'in_progress')->count(),
            'completed'   => StockOpname::where('status', 'completed')->count(),
        ];

        return view('warehouse.stock-opnames.index', [
            'items'     => $items,
            'locations' => Location::active()->with('warehouse')->orderBy('kode')->get(),
            'stats'     => $stats,
        ]);
    }

    /**
     * Form create
     */
    public function create()
    {
        return view('warehouse.stock-opnames.create', [
            'generatedNumber' => StockOpname::generateNumber(),
            'locations'       => Location::active()->with('warehouse')->orderBy('kode')->get(),
        ]);
    }

    /**
     * Store opname baru (snapshot inventory)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'opname_date' => 'required|date',
            'notes'       => 'nullable|string|max:1000',
        ]);

        // Cek apakah sudah ada opname draft/in_progress di lokasi ini
        $existing = StockOpname::where('location_id', $validated['location_id'])
            ->whereIn('status', ['draft', 'in_progress'])
            ->first();

        if ($existing) {
            return back()->withInput()->with('error',
                "Masih ada opname aktif ({$existing->opname_number}) di lokasi ini. Selesaikan atau batalkan dulu.");
        }

        DB::beginTransaction();
        try {
            $opname = StockOpname::create([
                'opname_number' => StockOpname::generateNumber(),
                'location_id'   => $validated['location_id'],
                'opname_date'   => $validated['opname_date'],
                'status'        => 'draft',
                'notes'         => $validated['notes'] ?? null,
                'created_by'    => auth()->id(),
            ]);

            // Snapshot inventory di lokasi ini
            $inventories = Inventory::where('location_id', $validated['location_id'])->get();

            foreach ($inventories as $inv) {
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'item_type'       => $inv->item_type,
                    'item_id'         => $inv->item_id,
                    'system_qty'      => $inv->qty,
                    'unit_price'      => $inv->unit_price,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('warehouse.stock-opnames.show', $opname)
                ->with('success', "Stock Opname {$opname->opname_number} berhasil dibuat dengan {$inventories->count()} item.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat opname: ' . $e->getMessage());
        }
    }

    /**
     * Show detail
     */
    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load([
            'location.warehouse',
            'items.item',
            'creator',
            'approver',
        ]);

        return view('warehouse.stock-opnames.show', [
            'opname' => $stockOpname,
        ]);
    }

    /**
     * Update 1 item opname (input fisik)
     */
    public function updateItem(Request $request, StockOpname $stockOpname, StockOpnameItem $item)
    {
        abort_if($item->stock_opname_id !== $stockOpname->id, 404);

        if (!$stockOpname->canEdit()) {
            return back()->with('error', 'Opname sudah selesai, tidak bisa diubah.');
        }

        $validated = $request->validate([
            'physical_qty' => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:500',
        ]);

        $varianceQty = $validated['physical_qty'] - $item->system_qty;
        $varianceValue = $varianceQty * $item->unit_price;

        $item->update([
            'physical_qty'   => $validated['physical_qty'],
            'variance_qty'   => $varianceQty,
            'variance_value' => $varianceValue,
            'notes'          => $validated['notes'] ?? null,
        ]);

        // Auto-update status ke in_progress kalau ada input
        if ($stockOpname->status === 'draft') {
            $stockOpname->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Item berhasil diupdate.');
    }

    /**
     * Approve opname → update stok
     */
    public function approve(StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'completed') {
            return back()->with('error', 'Opname sudah selesai.');
        }

        if ($stockOpname->unfilled_count > 0) {
            return back()->with('error',
                "Masih ada {$stockOpname->unfilled_count} item yang belum diinput stok fisiknya.");
        }

        if ($stockOpname->items()->count() === 0) {
            return back()->with('error', 'Opname tidak punya item.');
        }

        DB::beginTransaction();
        try {
            $stockOpname->load('items');
            $adjustedCount = 0;

            foreach ($stockOpname->items as $item) {
                if ($item->variance_qty == 0) continue;

                $inventory = Inventory::where('location_id', $stockOpname->location_id)
                    ->where('item_type', $item->item_type)
                    ->where('item_id', $item->item_id)
                    ->first();

                if (!$inventory) continue;

                $qtyBefore = (float) $inventory->qty;

                // Update inventory
                $inventory->update([
                    'qty'              => $item->physical_qty,
                    'last_movement_at' => now(),
                ]);

                // Catat stock movement untuk audit trail
                StockMovement::create([
                    'movement_number'   => StockMovement::generateNumber(),
                    'item_type'         => $item->item_type,
                    'item_id'           => $item->item_id,
                    'location_id'       => $stockOpname->location_id,
                    'type'              => 'adjustment',
                    'qty'               => $item->variance_qty,
                    'qty_before'        => $qtyBefore,
                    'qty_after'         => $item->physical_qty,
                    'unit_price'        => $item->unit_price,
                    'total_value'       => $item->variance_value,
                    'reference_type'    => StockOpname::class,
                    'reference_id'      => $stockOpname->id,
                    'reference_number'  => $stockOpname->opname_number,
                    'date'              => now(),
                    'notes'             => "Penyesuaian dari opname {$stockOpname->opname_number}",
                    'adjustment_reason' => 'opname',
                    'stock_opname_id'   => $stockOpname->id,
                    'created_by'        => auth()->id(),
                ]);

                $adjustedCount++;
            }

            $stockOpname->update([
                'status'      => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('warehouse.stock-opnames.show', $stockOpname)
                ->with('success', "Opname selesai. {$adjustedCount} item disesuaikan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    /**
     * Cancel opname
     */
    public function cancel(StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'completed') {
            return back()->with('error', 'Opname yang sudah selesai tidak bisa dibatalkan.');
        }

        $stockOpname->update(['status' => 'cancelled']);

        return redirect()
            ->route('warehouse.stock-opnames.index')
            ->with('success', 'Opname berhasil dibatalkan.');
    }

    /**
     * Delete opname
     */
    public function destroy(StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'completed') {
            return back()->with('error', 'Opname yang sudah selesai tidak bisa dihapus.');
        }

        $number = $stockOpname->opname_number;
        $stockOpname->delete();

        return redirect()
            ->route('warehouse.stock-opnames.index')
            ->with('success', "Opname {$number} berhasil dihapus.");
    }
}