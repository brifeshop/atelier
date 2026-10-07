<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Master\Supplier;
use App\Models\Purchasing\SupplierEvaluation;
use App\Services\SupplierEvaluationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SupplierEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierEvaluation::with('supplier');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $items = $query->latest('period')->latest('id')->paginate(20);

        // Stats
        $stats = [
            'total' => SupplierEvaluation::count(),
            'rating_a' => SupplierEvaluation::where('rating', 'A')->count(),
            'rating_b' => SupplierEvaluation::where('rating', 'B')->count(),
            'rating_c' => SupplierEvaluation::where('rating', 'C')->count(),
            'rating_d' => SupplierEvaluation::where('rating', 'D')->count(),
        ];

        return view('purchasing.supplier-evaluations.index', [
            'items' => $items,
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        return view('purchasing.supplier-evaluations.create', [
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);
        $start = Carbon::parse($validated['period_start']);
        $end = Carbon::parse($validated['period_end']);

        SupplierEvaluationService::evaluate($supplier, $start, $end);

        return redirect()
            ->route('purchasing.supplier-evaluations.index')
            ->with('success', "Evaluasi supplier {$supplier->nama} berhasil dihitung.");
    }

    public function show(SupplierEvaluation $supplierEvaluation)
    {
        $supplierEvaluation->load(['supplier', 'evaluatedBy']);

        // Detail GR dalam periode
        $receipts = \App\Models\Purchasing\GoodsReceipt::where('supplier_id', $supplierEvaluation->supplier_id)
            ->whereBetween('date', [$supplierEvaluation->period_start, $supplierEvaluation->period_end])
            ->where('status', 'received')
            ->with(['items.material'])
            ->get();

        return view('purchasing.supplier-evaluations.show', [
            'item' => $supplierEvaluation,
            'receipts' => $receipts,
        ]);
    }

    public function destroy(SupplierEvaluation $supplierEvaluation)
    {
        $supplierEvaluation->delete();

        return redirect()
            ->route('purchasing.supplier-evaluations.index')
            ->with('success', 'Evaluasi supplier berhasil dihapus.');
    }
}