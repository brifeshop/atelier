<?php

namespace App\Models\Production;

use App\Models\Engineering\RoutingStep;
use App\Models\Master\WorkCenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderProgress extends Model
{
    use HasFactory;

    protected $table = 'work_order_progress';

    protected $fillable = [
        'work_order_id', 'routing_step_id', 'sequence', 'operation_name',
        'work_center_id', 'status', 'qty_completed', 'qty_rejected',
        'started_at', 'completed_at', 'actual_minutes',
        'labor_cost', 'overhead_cost', 'total_cost', 'notes',
    ];

    protected $casts = [
        'qty_completed'  => 'decimal:2',
        'qty_rejected'   => 'decimal:2',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'actual_minutes' => 'decimal:2',
        'labor_cost'     => 'decimal:2',
        'overhead_cost'  => 'decimal:2',
        'total_cost'     => 'decimal:2',
    ];

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function routingStep() { return $this->belongsTo(RoutingStep::class); }
    public function workCenter() { return $this->belongsTo(WorkCenter::class); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
            'in_progress' => 'bg-gold-500/10 border-gold-500/30 text-gold-400',
            'completed'   => 'bg-green-500/10 border-green-500/30 text-green-400',
            'skipped'     => 'bg-navy-500/10 border-navy-500/30 text-navy-500',
            default       => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'Menunggu',
            'in_progress' => 'Sedang Dikerjakan',
            'completed'   => 'Selesai',
            'skipped'     => 'Dilewati',
            default       => 'Unknown',
        };
    }

    public function calculateCost(): array
        {
            $wc = $this->workCenter;
            if (!$wc) {
                return ['labor_cost' => 0, 'overhead_cost' => 0, 'total_cost' => 0];
            }

            $hours = (float) $this->actual_minutes / 60;
            $laborCost = $hours * (float) $wc->hourly_rate;
            $overheadCost = $hours * (float) $wc->overhead_rate;

            return [
                'labor_cost'    => round($laborCost, 2),
                'overhead_cost' => round($overheadCost, 2),
                'total_cost'    => round($laborCost + $overheadCost, 2),
            ];
        }
}