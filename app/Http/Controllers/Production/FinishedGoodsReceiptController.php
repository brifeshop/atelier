<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\FinishedGoodsReceipt;
use App\Models\Production\FinishedGoodsReceiptItem;
use App\Models\Production\WorkOrder;
use App\Models\Master\Product;
use App\Models\Warehouse\Location;
use App\Models\Warehouse\Warehouse;
use App\Services\FinishedGoodsReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinishedGoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = FinishedGoodsReceipt::with(['workOrder', 'product', 'warehouse', 'receiver']);

        if ($request->filled('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fgr_number', 'like', "%{$search}%")
                  ->orWhereHas('workOrder', function ($q2) use ($search) {
                      $q2->where('wo_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest('date')->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'total'    => FinishedGoodsReceipt::count(),
            'draft'    => FinishedGoodsReceipt::where('status', 'draft')->count(),
            'received' => FinishedGoodsReceipt::where('status', 'received')->count(),
        ];

        return view('production.finished-goods.index', [
            'items' => $items,
            'stats' => $stats,
        ]);
    }

    /**
     * Form create
     */
    public function create(Request $request)
    {
        $workOrder = null;
        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::with(['product', 'materials'])->find($request->work_order_id);
        }

        // Ambil WO yang bisa diterima produknya (in_progress)
        $workOrders = WorkOrder::where('status', 'in_progress')
            ->with('product')
            ->orderByDesc('id')
            ->get();

        return view('production.finished-goods.create', [
            'generatedNumber' => FinishedGoodsReceiptService::generateNumber(),
            'workOrders'      => $workOrders,
            'workOrder'       => $workOrder,
            'warehouses'      => Warehouse::active()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fgr_number'    => 'required|string|max:50|unique:finished_goods_receipts,fgr_number',
            'work_order_id' => 'required|exists:work_orders,id',
            'warehouse_id'  => 'required|exists:warehouses,id',
            'date'          => 'required|date',
            'qty_good'      => 'required|numeric|min:0.01',
            'qty_rejected'  => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.location_id' => 'required|exists:locations,id',
            'items.*.qty'         => 'required|numeric|min:0',
            'items.*.notes'       => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $wo = WorkOrder::find($validated['work_order_id']);

            $fgr = FinishedGoodsReceipt::create([
                'fgr_number'   => $validated['fgr_number'],
                'work_order_id' => $validated['work_order_id'],
                'product_id'   => $wo->product_id,
                'warehouse_id' => $validated['warehouse_id'],
                'location_id'  => $validated['items'][0]['location_id'], // Lokasi pertama sebagai default
                'date'         => $validated['date'],
                'status'       => 'draft',
                'qty_good'     => $validated['qty_good'],
                'qty_rejected' => $validated['qty_rejected'] ?? 0,
                'unit_cost'    => $wo->cost_per_unit,
                'total_value'  => $validated['qty_good'] * $wo->cost_per_unit,
                'notes'        => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                FinishedGoodsReceiptItem::create([
                    'finished_goods_receipt_id' => $fgr->id,
                    'location_id'               => $itemData['location_id'],
                    'qty'                       => $itemData['qty'],
                    'unit_cost'                 => $wo->cost_per_unit,
                    'total_value'               => $itemData['qty'] * $wo->cost_per_unit,
                    'notes'                     => $itemData['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('production.finished-goods.show', $fgr)
                ->with('success', "FG Receipt {$fgr->fgr_number} berhasil dibuat. Klik 'Terima Produk' untuk tambah stok.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal buat FG Receipt: ' . $e->getMessage());
        }
    }

    public function show(FinishedGoodsReceipt $finishedGood)
    {
        $finishedGood->load([
            'workOrder.product',
            'product',
            'warehouse',
            'location',
            'items.location',
            'receiver',
        ]);

        return view('production.finished-goods.show', [
            'item' => $finishedGood,
        ]);
    }

    /**
     * Receive product — tambah stok
     */
    public function receive(FinishedGoodsReceipt $finishedGood)
    {
        try {
            FinishedGoodsReceiptService::receive($finishedGood);

            return redirect()
                ->route('production.finished-goods.show', $finishedGood)
                ->with('success', 'Produk berhasil diterima. Stok sudah ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(FinishedGoodsReceipt $finishedGood)
    {
        if ($finishedGood->status === 'received') {
            return back()->with('error', 'FG Receipt yang sudah diterima tidak bisa dihapus.');
        }

        $number = $finishedGood->fgr_number;
        $finishedGood->delete();

        return redirect()
            ->route('production.finished-goods.index')
            ->with('success', "FG Receipt {$number} berhasil dihapus.");
    }

    /**
     * API: get locations untuk warehouse
     */
    public function getLocations(Warehouse $warehouse)
    {
        $locations = Location::where('warehouse_id', $warehouse->id)
            ->where('is_active', true)
            ->orderBy('kode')
            ->get()
            ->map(fn($loc) => [
                'id'    => (string) $loc->id,
                'label' => $loc->kode . ' — ' . $loc->nama,
            ]);

        return response()->json([
            'success'   => true,
            'locations' => $locations,
        ]);
    }
}