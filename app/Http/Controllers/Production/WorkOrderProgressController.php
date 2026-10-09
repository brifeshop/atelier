<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\WorkOrder;
use App\Models\Production\WorkOrderProgress;
use App\Services\WorkOrderProgressService;
use Illuminate\Http\Request;

class WorkOrderProgressController extends Controller
{
    /**
     * Form input progress per step
     */
    public function index(WorkOrder $workOrder)
    {
        if ($workOrder->status !== 'in_progress') {
            return redirect()
                ->route('production.work-orders.show', $workOrder)
                ->with('error', 'Work Order harus dalam status In Progress untuk input progress.');
        }

        $workOrder->load([
            'product',
            'progress.workCenter',
            'progress.routingStep',
        ]);

        return view('production.work-orders.progress', [
            'workOrder' => $workOrder,
        ]);
    }

    /**
     * Update 1 progress step
     */
    public function update(Request $request, WorkOrder $workOrder, WorkOrderProgress $progress)
    {
        abort_if($progress->work_order_id !== $workOrder->id, 404);

        $validated = $request->validate([
            'qty_completed'  => 'required|numeric|min:0',
            'qty_rejected'   => 'nullable|numeric|min:0',
            'actual_minutes' => 'nullable|numeric|min:0',
            'status'         => 'required|in:pending,in_progress,completed,skipped',
            'notes'          => 'nullable|string',
        ]);

        try {
            WorkOrderProgressService::updateProgress($progress, $validated);

            return redirect()
                ->route('production.work-orders.progress', $workOrder)
                ->with('success', "Progress step '{$progress->operation_name}' berhasil diupdate.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal update progress: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update — semua step sekaligus
     */
    public function bulkUpdate(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'steps' => 'required|array',
            'steps.*.qty_completed'  => 'nullable|numeric|min:0',
            'steps.*.qty_rejected'   => 'nullable|numeric|min:0',
            'steps.*.actual_minutes' => 'nullable|numeric|min:0',
            'steps.*.status'         => 'required|in:pending,in_progress,completed,skipped',
            'steps.*.notes'          => 'nullable|string',
        ]);

        try {
            $count = WorkOrderProgressService::bulkUpdate($workOrder, $validated['steps']);

            return redirect()
                ->route('production.work-orders.show', $workOrder)
                ->with('success', "{$count} progress step berhasil diupdate.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal update progress: ' . $e->getMessage());
        }
    }

    /**
     * Start step — set status in_progress
     */
    public function start(WorkOrder $workOrder, WorkOrderProgress $progress)
    {
        abort_if($progress->work_order_id !== $workOrder->id, 404);

        if ($workOrder->status !== 'in_progress') {
            return back()->with('error', 'Work Order harus In Progress.');
        }

        $progress->update([
            'status'     => 'in_progress',
            'started_at' => $progress->started_at ?? now(),
        ]);

        return back()->with('success', "Step '{$progress->operation_name}' dimulai.");
    }

    /**
     * Complete step — set status completed
     */
    public function complete(Request $request, WorkOrder $workOrder, WorkOrderProgress $progress)
    {
        abort_if($progress->work_order_id !== $workOrder->id, 404);

        $validated = $request->validate([
            'qty_completed'  => 'required|numeric|min:0',
            'qty_rejected'   => 'nullable|numeric|min:0',
            'actual_minutes' => 'required|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        try {
            WorkOrderProgressService::updateProgress($progress, array_merge($validated, [
                'status' => 'completed',
            ]));

            return back()->with('success', "Step '{$progress->operation_name}' selesai.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal complete step: ' . $e->getMessage());
        }
    }
}