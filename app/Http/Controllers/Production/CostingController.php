<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\CostSnapshot;
use App\Models\Production\WorkOrder;
use App\Models\Master\Product;
use App\Services\CostingService;
use Illuminate\Http\Request;

class CostingController extends Controller
{
    /**
     * Dashboard Costing
     */
    public function index(Request $request)
    {
        // Filter periode
        $period = $request->get('period', 30);
        $since = now()->subDays((int) $period);

        // Stats
        $snapshots = CostSnapshot::where('snapshot_date', '>=', $since);

        $stats = [
            'total_snapshots'   => (clone $snapshots)->count(),
            'total_produced'    => (clone $snapshots)->sum('actual_qty'),
            'total_cost'        => (clone $snapshots)->sum('actual_total_cost'),
            'avg_cost_per_unit' => (clone $snapshots)->avg('actual_cost_per_unit') ?? 0,
            'unfavorable_count' => (clone $snapshots)->where('total_variance', '>', 0)->count(),
            'favorable_count'   => (clone $snapshots)->where('total_variance', '<', 0)->count(),
        ];

        // Recent snapshots
        $items = CostSnapshot::with(['workOrder', 'product'])
            ->orderByDesc('snapshot_date')
            ->paginate(20);

        return view('production.costing.index', [
            'items'  => $items,
            'stats'  => $stats,
            'period' => $period,
        ]);
    }

    /**
     * Variance Analysis (all snapshots)
     */
    public function variance(Request $request)
    {
        $query = CostSnapshot::with(['workOrder', 'product']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('variance_type')) {
            if ($request->variance_type === 'unfavorable') {
                $query->where('total_variance', '>', 0);
            } elseif ($request->variance_type === 'favorable') {
                $query->where('total_variance', '<', 0);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('snapshot_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('snapshot_date', '<=', $request->date_to);
        }

        $items = $query->orderByDesc('snapshot_date')->paginate(20)->withQueryString();

        return view('production.costing.variance', [
            'items'    => $items,
            'products' => Product::active()->orderBy('nama')->get(),
        ]);
    }

    /**
     * Margin Analysis per produk
     */
    public function margin()
    {
        $products = Product::active()->orderBy('nama')->get();

        $margins = $products->map(function ($product) {
            return CostingService::marginAnalysis($product->id);
        })->filter(fn($m) => !isset($m['error']));

        // Sort by margin percent descending
        $margins = $margins->sortByDesc('margin_percent')->values();

        return view('production.costing.margin', [
            'margins' => $margins,
        ]);
    }

    /**
     * Detail snapshot
     */
    public function show(CostSnapshot $costSnapshot)
    {
        $costSnapshot->load(['workOrder.product', 'product', 'creator', 'workOrder.materials.material', 'workOrder.progress.workCenter']);

        return view('production.costing.show', [
            'item' => $costSnapshot,
        ]);
    }

    /**
     * Export costing report (CSV)
     */
    public function export(Request $request)
    {
        // Placeholder — nanti bisa implement
        return back()->with('info', 'Export akan tersedia di update berikutnya.');
    }
}