<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseRequisition;
use App\Models\Purchasing\PurchaseRequisitionItem;
use App\Models\Master\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequisitionController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequisition::with(['requestedBy', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $items = $query->latest('date')->latest('id')->paginate(15);

        $stats = [
            'total' => PurchaseRequisition::count(),
            'draft' => PurchaseRequisition::where('status', 'draft')->count(),
            'approved' => PurchaseRequisition::where('status', 'approved')->count(),
            'rejected' => PurchaseRequisition::where('status', 'rejected')->count(),
        ];

        return view('purchasing.purchase-requisitions.index', compact('items', 'stats'));
    }

    public function create()
    {
        return view('purchasing.purchase-requisitions.create', [
            'generatedNumber' => PurchaseRequisition::generateNumber(),
            'materials' => Material::active()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_number' => 'required|string|max:50|unique:purchase_requisitions,pr_number',
            'department' => 'nullable|string|max:100',
            'date' => 'required|date',
            'needed_date' => 'nullable|date|after_or_equal:date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ], [
            'items.required' => 'Minimal harus ada 1 item.',
            'items.min' => 'Minimal harus ada 1 item.',
        ]);

        DB::beginTransaction();
        try {
            $pr = PurchaseRequisition::create([
                'pr_number' => $validated['pr_number'],
                'requested_by' => auth()->id(),
                'department' => $validated['department'] ?? null,
                'date' => $validated['date'],
                'needed_date' => $validated['needed_date'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                PurchaseRequisitionItem::create([
                    'purchase_requisition_id' => $pr->id,
                    'material_id' => $itemData['material_id'],
                    'qty' => $itemData['qty'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchasing.purchase-requisitions.show', $pr)
                ->with('success', "PR {$pr->pr_number} berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan PR: ' . $e->getMessage());
        }
    }

    public function show(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->load(['requestedBy', 'approvedBy', 'items.material', 'items.purchaseOrderItems.purchaseOrder', 'purchaseOrders']);
        return view('purchasing.purchase-requisitions.show', ['item' => $purchaseRequisition]);
    }

    public function edit(PurchaseRequisition $purchaseRequisition)
    {
        if (!$purchaseRequisition->canEdit()) {
            return redirect()
                ->route('purchasing.purchase-requisitions.show', $purchaseRequisition)
                ->with('error', 'PR tidak bisa diedit karena sudah diproses.');
        }

        $purchaseRequisition->load('items');

        return view('purchasing.purchase-requisitions.edit', [
            'item' => $purchaseRequisition,
            'materials' => Material::active()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if (!$purchaseRequisition->canEdit()) {
            return redirect()
                ->route('purchasing.purchase-requisitions.show', $purchaseRequisition)
                ->with('error', 'PR tidak bisa diedit.');
        }

        $validated = $request->validate([
            'pr_number' => 'required|string|max:50|unique:purchase_requisitions,pr_number,' . $purchaseRequisition->id,
            'department' => 'nullable|string|max:100',
            'date' => 'required|date',
            'needed_date' => 'nullable|date|after_or_equal:date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchaseRequisition->update([
                'pr_number' => $validated['pr_number'],
                'department' => $validated['department'] ?? null,
                'date' => $validated['date'],
                'needed_date' => $validated['needed_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Hapus item lama, buat baru
            $purchaseRequisition->items()->delete();

            foreach ($validated['items'] as $itemData) {
                PurchaseRequisitionItem::create([
                    'purchase_requisition_id' => $purchaseRequisition->id,
                    'material_id' => $itemData['material_id'],
                    'qty' => $itemData['qty'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchasing.purchase-requisitions.show', $purchaseRequisition)
                ->with('success', 'PR berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update PR: ' . $e->getMessage());
        }
    }

    public function approve(PurchaseRequisition $purchaseRequisition)
    {
        if (!$purchaseRequisition->canApprove()) {
            return back()->with('error', 'PR tidak bisa di-approve.');
        }

        $purchaseRequisition->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "PR {$purchaseRequisition->pr_number} berhasil di-approve.");
    }

    public function reject(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $purchaseRequisition->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', "PR {$purchaseRequisition->pr_number} ditolak.");
    }

    public function destroy(PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'draft') {
            return back()->with('error', 'Hanya PR draft yang bisa dihapus.');
        }

        $prNumber = $purchaseRequisition->pr_number;
        $purchaseRequisition->delete();

        return redirect()
            ->route('purchasing.purchase-requisitions.index')
            ->with('success', "PR {$prNumber} berhasil dihapus.");
    }
}