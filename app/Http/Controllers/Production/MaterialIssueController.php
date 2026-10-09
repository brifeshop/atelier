<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\MaterialIssue;
use App\Models\Production\MaterialIssueItem;
use App\Models\Production\WorkOrder;
use App\Models\Production\WorkOrderMaterial;
use App\Models\Warehouse\Inventory;
use App\Models\Warehouse\Location;
use App\Models\Warehouse\Warehouse;
use App\Services\MaterialIssueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialIssueController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialIssue::with(['workOrder', 'warehouse', 'issuer']);

        if ($request->filled('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('issue_number', 'like', "%{$search}%")
                  ->orWhereHas('workOrder', function ($q2) use ($search) {
                      $q2->where('wo_number', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest('date')->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'total'  => MaterialIssue::count(),
            'draft'  => MaterialIssue::where('status', 'draft')->count(),
            'issued' => MaterialIssue::where('status', 'issued')->count(),
        ];

        return view('production.material-issues.index', [
            'items' => $items,
            'stats' => $stats,
        ]);
    }

    /**
     * Form create — dari Work Order
     */
    public function create(Request $request)
    {
        $workOrder = null;
        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::with(['product', 'materials.material'])
                ->find($request->work_order_id);
        }

        // Ambil WO yang bisa di-issue (status in_progress, released)
        $workOrders = WorkOrder::whereIn('status', ['released', 'in_progress'])
            ->with('product')
            ->orderByDesc('id')
            ->get();

        return view('production.material-issues.create', [
            'generatedNumber' => MaterialIssueService::generateNumber(),
            'workOrders'      => $workOrders,
            'workOrder'       => $workOrder,
            'warehouses'      => Warehouse::active()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'issue_number'  => 'required|string|max:50|unique:material_issues,issue_number',
            'work_order_id' => 'required|exists:work_orders,id',
            'warehouse_id'  => 'required|exists:warehouses,id',
            'date'          => 'required|date',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.work_order_material_id' => 'required|exists:work_order_materials,id',
            'items.*.material_id'    => 'required|exists:materials,id',
            'items.*.location_id'    => 'required|exists:locations,id',
            'items.*.qty'            => 'required|numeric|min:0.0001',
            'items.*.unit_cost'      => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $issue = MaterialIssue::create([
                'issue_number'  => $validated['issue_number'],
                'work_order_id' => $validated['work_order_id'],
                'warehouse_id'  => $validated['warehouse_id'],
                'date'          => $validated['date'],
                'status'        => 'draft',
                'notes'         => $validated['notes'] ?? null,
                'issued_by'     => auth()->id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                MaterialIssueItem::create([
                    'material_issue_id'      => $issue->id,
                    'work_order_material_id' => $itemData['work_order_material_id'],
                    'material_id'            => $itemData['material_id'],
                    'location_id'            => $itemData['location_id'],
                    'qty'                    => $itemData['qty'],
                    'unit_cost'              => $itemData['unit_cost'],
                    'total_cost'             => $itemData['qty'] * $itemData['unit_cost'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('production.material-issues.show', $issue)
                ->with('success', "Material Issue {$issue->issue_number} berhasil dibuat. Klik 'Issue Material' untuk potong stok.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal buat Material Issue: ' . $e->getMessage());
        }
    }

    public function show(MaterialIssue $materialIssue)
    {
        $materialIssue->load([
            'workOrder.product',
            'warehouse',
            'items.material',
            'items.location',
            'items.workOrderMaterial',
            'issuer',
        ]);

        return view('production.material-issues.show', [
            'item' => $materialIssue,
        ]);
    }

    /**
     * Issue material — potong stok
     */
    public function issue(MaterialIssue $materialIssue)
    {
        if ($materialIssue->status === 'issued') {
            return back()->with('error', 'Material Issue ini sudah di-issue.');
        }

        try {
            MaterialIssueService::issue($materialIssue);

            return redirect()
                ->route('production.material-issues.show', $materialIssue)
                ->with('success', 'Material berhasil di-issue. Stok sudah berkurang.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus Material Issue (hanya draft)
     */
    public function destroy(MaterialIssue $materialIssue)
    {
        if ($materialIssue->status !== 'draft') {
            return back()->with('error', 'Hanya Material Issue berstatus draft yang bisa dihapus.');
        }

        $number = $materialIssue->issue_number;
        $materialIssue->delete();

        return redirect()
            ->route('production.material-issues.index')
            ->with('success', "Material Issue {$number} berhasil dihapus.");
    }

    /**
     * API: get work order materials untuk form
     */
    public function getWorkOrderMaterials(WorkOrder $workOrder)
    {
        $workOrder->load(['materials.material', 'materials.issueItems']);

        $items = $workOrder->materials->map(function ($wom) {
            $remainingQty = (float) $wom->required_qty - (float) $wom->issued_qty;

            return [
                'id'            => $wom->id,
                'material_id'   => $wom->material_id,
                'material_kode' => $wom->material->kode ?? '-',
                'material_nama' => $wom->material->nama ?? '-',
                'required_qty'  => (float) $wom->required_qty,
                'issued_qty'    => (float) $wom->issued_qty,
                'remaining_qty' => max(0, $remainingQty),
                'unit'          => $wom->unit,
                'unit_cost'     => (float) $wom->unit_cost,
                'status'        => $wom->status,
            ];
        });

        return response()->json([
            'success' => true,
            'items'   => $items,
        ]);
    }

    /**
     * API: get locations yang punya stok material
     */
    public function getMaterialLocations(Request $request)
    {
        $request->validate([
            'material_id' => 'required|integer',
            'min_qty'     => 'nullable|numeric|min:0',
        ]);

        $minQty = $request->input('min_qty', 0.0001);

        $inventories = Inventory::with(['location.warehouse'])
            ->where('item_type', 'material')
            ->where('item_id', $request->material_id)
            ->where('qty', '>=', $minQty)
            ->orderByDesc('qty')
            ->get();

        $locations = $inventories->map(fn($inv) => [
            'id'            => (string) $inv->location_id,
            'location_id'   => (string) $inv->location_id,
            'label'         => ($inv->location->kode ?? '-') . ' — ' . ($inv->location->nama ?? '-') . ' (' . ($inv->location->warehouse->nama ?? '-') . ')',
            'qty_available' => (float) $inv->qty,
        ]);

        return response()->json([
            'success'   => true,
            'locations' => $locations,
        ]);
    }
}