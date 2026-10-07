<?php

namespace App\Services;

use App\Models\Engineering\Routing;
use App\Models\Master\WorkCenter;
use Illuminate\Support\Facades\DB;

class RoutingService
{
    /**
     * Hitung total cost routing
     */
    public static function calculateCost(Routing $routing): array
    {
        $routing->load('steps.workCenter');

        $totalLabor = 0;
        $totalOverhead = 0;

        foreach ($routing->steps as $step) {
            $workCenter = $step->workCenter;
            if (!$workCenter) continue;

            // Total waktu dalam jam = (setup + run) / 60
            $totalTimeMinutes = (float) $step->setup_time_minutes + (float) $step->run_time_per_unit_minutes;
            $totalTimeHours = $totalTimeMinutes / 60;

            $laborRate = (float) $workCenter->hourly_rate;
            $overheadRate = (float) $workCenter->overhead_rate;

            $laborCost = $totalTimeHours * $laborRate;
            $overheadCost = $totalTimeHours * $overheadRate;
            $totalCost = $laborCost + $overheadCost;

            $step->update([
                'labor_rate'    => $laborRate,
                'overhead_rate' => $overheadRate,
                'labor_cost'    => round($laborCost, 2),
                'overhead_cost' => round($overheadCost, 2),
                'total_cost'    => round($totalCost, 2),
            ]);

            $totalLabor += $laborCost;
            $totalOverhead += $overheadCost;
        }

        return [
            'total_labor_cost'    => round($totalLabor, 2),
            'total_overhead_cost' => round($totalOverhead, 2),
            'total_cost'          => round($totalLabor + $totalOverhead, 2),
        ];
    }

    /**
     * Update routing total cost
     */
    public static function updateCost(Routing $routing): Routing
    {
        DB::beginTransaction();
        try {
            $cost = self::calculateCost($routing);
            $routing->update($cost);

            DB::commit();
            return $routing->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Recalculate semua routing aktif
     */
    public static function recalculateAll(): int
    {
        $routings = Routing::where('status', 'active')->get();
        $count = 0;

        foreach ($routings as $routing) {
            try {
                self::updateCost($routing);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Failed to recalculate Routing {$routing->kode}: " . $e->getMessage());
            }
        }

        return $count;
    }
}