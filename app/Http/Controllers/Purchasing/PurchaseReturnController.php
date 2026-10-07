<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseReturn;
use App\Models\Purchasing\PurchaseReturnItem;
use App\Models\Purchasing\GoodsReceipt;
use App\Models\Master\Supplier;
use App\Models\Master\Material;
use App\Models\Warehouse\Location;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'goodsReceipt', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest('date')->latest('id')->paginate(15);

        $stats = [
            'total' => PurchaseReturn::count(),
            'draft' => PurchaseReturn::where('status', 'draft')->count(),
            'completed' => PurchaseReturn::where('status', 'completed')->count(),
        ];

        return view('purchasing.purchase-returns.index', compact('items', 'stats'));
    }

    public function create(Request $request)
    {
        $gr = null;
        $locations = Location::active()->with('warehouse')->orderBy('kode')->get();

        if ($request->filled('from_gr')) {
            $gr = GoodsReceipt::with(['items.material', 'supplier', 'purchaseOrder'])->find($request->from_gr);
        }

        return view('purchasing.purchase-returns.create', [
            'generatedNumber' => PurchaseReturn::generateNumber(),
            'gr' => $gr,
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
            'materials' => Material::active()->orderBy('nama')->get(),
            'locations' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_number' => 'required|string|max:50|unique:purchase_returns,return_number',
            'supplier_id' => 'required|exists:suppliers,id',
            'goods_receipt_id' => 'nullable|exists:goods_receipts,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'date' => 'required|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.location_id' => 'required|exists:locations,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $return = PurchaseReturn::create([
                'return_number' => $validated['return_number'],
                'supplier_id' => $validated['supplier_id'],
                'goods_receipt_id' => $validated['goods_receipt_id'] ?? null,
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'date' => $validated['date'],
                'status' => 'draft',
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'material_id' => $itemData['material_id'],
                    'location_id' => $itemData['location_id'],
                    'qty' => $itemData['qty'],
                    'unit_price' => $itemData['unit_price'],
                    'reason' => $itemData['reason'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchasing.purchase-returns.show', $return)
                ->with('success', "Purchase Return {$return->return_number} berhasil dibuat. Klik 'Konfirmasi Return' untuk update stok.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan Return: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['supplier', 'goodsReceipt', 'purchaseOrder', 'items.material', 'items.location', 'createdBy']);
        return view('purchasing.purchase-returns.show', ['item' => $purchaseReturn]);
    }

    public function edit(PurchaseReturn $purchaseReturn)
    {
        if (!$purchaseReturn->canEdit()) {
            return redirect()
                ->route('purchasing.purchase-returns.show', $purchaseReturn)
                ->with('error', 'Return tidak bisa diedit.');
        }

        $purchaseReturn->load('items');

        return view('purchasing.purchase-returns.edit', [
            'item' => $purchaseReturn,
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
            'materials' => Material::active()->orderBy('nama')->get(),
            'locations' => Location::active()->with('warehouse')->orderBy('kode')->get(),
        ]);
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        if (!$purchaseReturn->canEdit()) {
            return redirect()
                ->route('purchasing.purchase-returns.show', $purchaseReturn)
                ->with('error', 'Return tidak bisa diedit.');
        }

        $validated = $request->validate([
            'return_number' => 'required|string|max:50|unique:purchase_returns,return_number,' . $purchaseReturn->id,
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.location_id' => 'required|exists:locations,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchaseReturn->update([
                'return_number' => $validated['return_number'],
                'supplier_id' => $validated['supplier_id'],
                'date' => $validated['date'],
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $purchaseReturn->items()->delete();

            foreach ($validated['items'] as $itemData) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'material_id' => $itemData['material_id'],
                    'location_id' => $itemData['location_id'],
                    'qty' => $itemData['qty'],
                    'unit_price' => $itemData['unit_price'],
                    'reason' => $itemData['reason'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchasing.purchase-returns.show', $purchaseReturn)
                ->with('success', 'Purchase Return berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update Return: ' . $e->getMessage());
        }
    }

    /**
     * KONFIRMASI RETURN
     * - Stock Movement (OUT) → stok berkurang
     */
    public function complete(PurchaseReturn $purchaseReturn)
    {
        if (!$purchaseReturn->canComplete()) {
            return back()->with('error', 'Return tidak bisa dikonfirmasi.');
        }

        DB::beginTransaction();
        try {
            $purchaseReturn->load('items');

            foreach ($purchaseReturn->items as $item) {
                StockMovementService::recordOut(
                    'material',
                    $item->material_id,
                    $item->location_id,
                    (float) $item->qty,
                    [
                        'unit_price' => (float) $item->unit_price,
                        'reference_type' => PurchaseReturn::class,
                        'reference_id' => $purchaseReturn->id,
                        'reference_number' => $purchaseReturn->return_number,
                        'date' => $purchaseReturn->date->toDateString(),
                        'notes' => "Return ke supplier dari {$purchaseReturn->return_number}",
                    ]
                );
            }

            $purchaseReturn->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('purchasing.purchase-returns.show', $purchaseReturn)
                ->with('success', "Return {$purchaseReturn->return_number} berhasil dikonfirmasi. Stok sudah berkurang.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal konfirmasi Return: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->status !== 'draft') {
            return back()->with('error', 'Hanya Return draft yang bisa dihapus.');
        }

        $number = $purchaseReturn->return_number;
        $purchaseReturn->delete();

        return redirect()
            ->route('purchasing.purchase-returns.index')
            ->with('success', "Return {$number} berhasil dihapus.");
    }
}