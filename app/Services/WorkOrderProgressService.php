<?php

namespace App\Services;

use App\Models\Production\WorkOrder;
use App\Models\Production\WorkOrderProgress;
use Illuminate\Support\Facades\DB;

class WorkOrderProgressService
{
    /**
     * Update progress step
     * 
     * @param  WorkOrderProgress $progress
     * @param  array $data  ['qty_completed', 'qty_rejected', 'actual_minutes', 'status', 'notes']
     * @return WorkOrderProgress
     */
    public static function updateProgress(WorkOrderProgress $progress, array $data): WorkOrderProgress
    {
        DB::beginTransaction();
        try {
            $wo = $progress->workOrder;

            // Validasi: WO harus in_progress
            if ($wo->status !== 'in_progress') {
                throw new \Exception('Work Order harus dalam status In Progress untuk input progress.');
            }

            // Hitung cost
            $actualMinutes = (float) ($data['actual_minutes'] ?? 0);
            $cost = $progress->calculateCost();

            // Update progress
            $progress->update([
                'qty_completed'  => $data['qty_completed'] ?? 0,
                'qty_rejected'   => $data['qty_rejected'] ?? 0,
                'actual_minutes' => $actualMinutes,
                'labor_cost'     => $cost['labor_cost'],
                'overhead_cost'  => $cost['overhead_cost'],
                'total_cost'     => $cost['total_cost'],
                'status'         => $data['status'] ?? $progress->status,
                'notes'          => $data['notes'] ?? $progress->notes,
                'started_at'     => $progress->started_at ?? now(),
                'completed_at'   => ($data['status'] ?? '') === 'completed' ? now() : $progress->completed_at,
            ]);

            // Update actual_qty WO = qty_completed dari step TERAKHIR
            self::updateWorkOrderActualQty($wo);

            DB::commit();
            return $progress->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update actual_qty WO dari step terakhir
     */
    protected static function updateWorkOrderActualQty(WorkOrder $wo): void
    {
        $wo->load('progress');

        // Ambil step terakhir (paling tinggi sequence)
        $lastStep = $wo->progress->sortByDesc('sequence')->first();

        if ($lastStep) {
            $wo->update([
                'actual_qty'   => $lastStep->qty_completed,
                'rejected_qty' => $lastStep->qty_rejected,
            ]);

            // Update total labor & overhead dari semua progress
            $totalLabor = $wo->progress->sum('labor_cost');
            $totalOverhead = $wo->progress->sum('overhead_cost');

            // Kalau progress sudah ada labor cost, pakai actual
            if ($totalLabor > 0 || $totalOverhead > 0) {
                $totalCost = (float) $wo->total_material_cost + $totalLabor + $totalOverhead;
                $costPerUnit = $wo->planned_qty > 0 ? $totalCost / $wo->planned_qty : 0;

                $wo->update([
                    'total_labor_cost'    => round($totalLabor, 2),
                    'total_overhead_cost' => round($totalOverhead, 2),
                    'total_cost'          => round($totalCost, 2),
                    'cost_per_unit'       => round($costPerUnit, 2),
                ]);
            }
        }
    }

    /**
     * Bulk update progress (semua step sekaligus)
     */
    public static function bulkUpdate(WorkOrder $wo, array $stepsData): int
    {
        DB::beginTransaction();
        try {
            if ($wo->status !== 'in_progress') {
                throw new \Exception('Work Order harus dalam status In Progress.');
            }

            $count = 0;
            foreach ($stepsData as $stepId => $data) {
                $progress = WorkOrderProgress::where('work_order_id', $wo->id)
                    ->where('id', $stepId)
                    ->first();

                if (!$progress) continue;

                $actualMinutes = (float) ($data['actual_minutes'] ?? 0);

                // Set actual_minutes dulu untuk calculateCost()
                $progress->actual_minutes = $actualMinutes;
                $cost = $progress->calculateCost();

                $progress->update([
                    'qty_completed'  => $data['qty_completed'] ?? 0,
                    'qty_rejected'   => $data['qty_rejected'] ?? 0,
                    'actual_minutes' => $actualMinutes,
                    'labor_cost'     => $cost['labor_cost'],
                    'overhead_cost'  => $cost['overhead_cost'],
                    'total_cost'     => $cost['total_cost'],
                    'status'         => $data['status'] ?? 'pending',
                    'notes'          => $data['notes'] ?? null,
                    'started_at'     => ($data['status'] ?? '') !== 'pending' ? ($progress->started_at ?? now()) : null,
                    'completed_at'   => ($data['status'] ?? '') === 'completed' ? now() : null,
                ]);

                $count++;
            }

            self::updateWorkOrderActualQty($wo);

            DB::commit();
            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}