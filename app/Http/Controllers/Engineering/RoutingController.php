<?php

namespace App\Http\Controllers\Engineering;

use App\Http\Controllers\Controller;
use App\Models\Engineering\Routing;
use App\Models\Engineering\RoutingStep;
use App\Models\Master\Product;
use App\Models\Master\WorkCenter;
use App\Services\RoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutingController extends Controller
{
    public function index(Request $request)
    {
        $query = Routing::with(['product', 'creator']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->latest('effective_date')->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'total'    => Routing::count(),
            'draft'    => Routing::where('status', 'draft')->count(),
            'active'   => Routing::where('status', 'active')->count(),
            'obsolete' => Routing::where('status', 'obsolete')->count(),
        ];

        return view('engineering.routings.index', [
            'items'    => $items,
            'stats'    => $stats,
            'products' => Product::active()->orderBy('nama')->get(),
        ]);
    }

    public function create()
    {
        return view('engineering.routings.create', [
            'generatedKode' => Routing::generateKode(),
            'products'      => Product::active()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'           => 'required|string|max:50|unique:routings,kode',
            'product_id'     => 'required|exists:products,id',
            'version'        => 'required|string|max:20',
            'effective_date' => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $routing = Routing::create([
                'kode'           => $validated['kode'],
                'product_id'     => $validated['product_id'],
                'version'        => $validated['version'],
                'effective_date' => $validated['effective_date'],
                'status'         => 'draft',
                'notes'          => $validated['notes'] ?? null,
                'created_by'     => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('engineering.routings.show', $routing)
                ->with('success', "Routing {$routing->kode} berhasil dibuat. Tambahkan step di bawah.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat Routing: ' . $e->getMessage());
        }
    }

    public function show(Routing $routing)
    {
        $routing->load(['product', 'steps.workCenter', 'creator', 'approver']);

        return view('engineering.routings.show', [
            'item'        => $routing,
            'workCenters' => WorkCenter::active()->orderBy('nama')->get(),
        ]);
    }

    public function edit(Routing $routing)
    {
        if (!$routing->canEdit()) {
            return redirect()
                ->route('engineering.routings.show', $routing)
                ->with('error', 'Routing yang sudah aktif tidak bisa diedit.');
        }

        return view('engineering.routings.edit', [
            'item'     => $routing,
            'products' => Product::active()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Routing $routing)
    {
        if (!$routing->canEdit()) {
            return back()->with('error', 'Routing yang sudah aktif tidak bisa diedit.');
        }

        $validated = $request->validate([
            'kode'           => 'required|string|max:50|unique:routings,kode,' . $routing->id,
            'product_id'     => 'required|exists:products,id',
            'version'        => 'required|string|max:20',
            'effective_date' => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $routing->update($validated);

        return redirect()
            ->route('engineering.routings.show', $routing)
            ->with('success', 'Routing berhasil diperbarui.');
    }

    public function destroy(Routing $routing)
    {
        if ($routing->status === 'active') {
            return back()->with('error', 'Routing yang sudah aktif tidak bisa dihapus.');
        }

        $kode = $routing->kode;
        $routing->delete();

        return redirect()
            ->route('engineering.routings.index')
            ->with('success', "Routing {$kode} berhasil dihapus.");
    }

    public function activate(Routing $routing)
    {
        if (!$routing->canActivate()) {
            return back()->with('error', 'Routing belum bisa diaktifkan. Pastikan sudah ada step.');
        }

        RoutingService::updateCost($routing);

        $routing->update([
            'status'      => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('engineering.routings.show', $routing)
            ->with('success', 'Routing berhasil diaktifkan.');
    }

    public function obsolete(Routing $routing)
    {
        if (!$routing->canObsolete()) {
            return back()->with('error', 'Hanya Routing aktif yang bisa di-obsolete.');
        }

        $routing->update(['status' => 'obsolete']);

        return redirect()
            ->route('engineering.routings.show', $routing)
            ->with('success', 'Routing berhasil di-obsolete.');
    }

    public function recalculate(Routing $routing)
    {
        try {
            RoutingService::updateCost($routing);
            return back()->with('success', 'Cost Routing berhasil dihitung ulang.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal recalculate: ' . $e->getMessage());
        }
    }

    // ============ ROUTING STEPS ============

    public function addStep(Request $request, Routing $routing)
    {
        if (!$routing->canEdit()) {
            return back()->with('error', 'Routing yang sudah aktif tidak bisa diubah.');
        }

        $validated = $request->validate([
            'work_center_id'              => 'required|exists:work_centers,id',
            'operation_name'              => 'required|string|max:100',
            'setup_time_minutes'          => 'nullable|numeric|min:0',
            'run_time_per_unit_minutes'   => 'nullable|numeric|min:0',
            'notes'                       => 'nullable|string',
        ]);

        $maxSeq = $routing->steps()->max('sequence') ?? 0;

        $workCenter = WorkCenter::find($validated['work_center_id']);

        $setup = $validated['setup_time_minutes'] ?? 0;
        $run = $validated['run_time_per_unit_minutes'] ?? 0;
        $totalMinutes = $setup + $run;
        $totalHours = $totalMinutes / 60;

        $laborRate = (float) $workCenter->hourly_rate;
        $overheadRate = (float) $workCenter->overhead_rate;

        $laborCost = $totalHours * $laborRate;
        $overheadCost = $totalHours * $overheadRate;

        RoutingStep::create([
            'routing_id'                  => $routing->id,
            'sequence'                    => $maxSeq + 1,
            'work_center_id'              => $validated['work_center_id'],
            'operation_name'              => $validated['operation_name'],
            'setup_time_minutes'          => $setup,
            'run_time_per_unit_minutes'   => $run,
            'labor_rate'                  => $laborRate,
            'overhead_rate'               => $overheadRate,
            'labor_cost'                  => round($laborCost, 2),
            'overhead_cost'               => round($overheadCost, 2),
            'total_cost'                  => round($laborCost + $overheadCost, 2),
            'notes'                       => $validated['notes'] ?? null,
        ]);

        RoutingService::updateCost($routing);

        return back()->with('success', 'Step berhasil ditambahkan.');
    }

    public function updateStep(Request $request, Routing $routing, RoutingStep $step)
    {
        abort_if($step->routing_id !== $routing->id, 404);

        if (!$routing->canEdit()) {
            return back()->with('error', 'Routing yang sudah aktif tidak bisa diubah.');
        }

        $validated = $request->validate([
            'work_center_id'              => 'required|exists:work_centers,id',
            'operation_name'              => 'required|string|max:100',
            'setup_time_minutes'          => 'nullable|numeric|min:0',
            'run_time_per_unit_minutes'   => 'nullable|numeric|min:0',
            'notes'                       => 'nullable|string',
        ]);

        $workCenter = WorkCenter::find($validated['work_center_id']);

        $setup = $validated['setup_time_minutes'] ?? 0;
        $run = $validated['run_time_per_unit_minutes'] ?? 0;
        $totalMinutes = $setup + $run;
        $totalHours = $totalMinutes / 60;

        $laborRate = (float) $workCenter->hourly_rate;
        $overheadRate = (float) $workCenter->overhead_rate;

        $laborCost = $totalHours * $laborRate;
        $overheadCost = $totalHours * $overheadRate;

        $step->update([
            'work_center_id'              => $validated['work_center_id'],
            'operation_name'              => $validated['operation_name'],
            'setup_time_minutes'          => $setup,
            'run_time_per_unit_minutes'   => $run,
            'labor_rate'                  => $laborRate,
            'overhead_rate'               => $overheadRate,
            'labor_cost'                  => round($laborCost, 2),
            'overhead_cost'               => round($overheadCost, 2),
            'total_cost'                  => round($laborCost + $overheadCost, 2),
            'notes'                       => $validated['notes'] ?? null,
        ]);

        RoutingService::updateCost($routing);

        return back()->with('success', 'Step berhasil diperbarui.');
    }

    public function removeStep(Routing $routing, RoutingStep $step)
    {
        abort_if($step->routing_id !== $routing->id, 404);

        if (!$routing->canEdit()) {
            return back()->with('error', 'Routing yang sudah aktif tidak bisa diubah.');
        }

        $step->delete();
        RoutingService::updateCost($routing);

        return back()->with('success', 'Step berhasil dihapus.');
    }
}