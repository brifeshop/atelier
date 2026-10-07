<?php

namespace App\Models\Production;

use App\Models\User;
use App\Models\Master\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostSnapshot extends Model
{
    use HasFactory;

    protected $table = 'cost_snapshots';

    protected $fillable = [
        'work_order_id', 'product_id', 'snapshot_date',
        'planned_qty', 'actual_qty', 'rejected_qty',
        'standard_material_cost', 'standard_labor_cost', 'standard_overhead_cost',
        'standard_total_cost', 'standard_cost_per_unit',
        'actual_material_cost', 'actual_labor_cost', 'actual_overhead_cost',
        'actual_total_cost', 'actual_cost_per_unit',
        'material_variance', 'labor_variance', 'overhead_variance',
        'total_variance', 'variance_percent',
        'notes', 'created_by',
    ];

    protected $casts = [
        'snapshot_date'             => 'date',
        'planned_qty'               => 'decimal:2',
        'actual_qty'                => 'decimal:2',
        'rejected_qty'              => 'decimal:2',
        'standard_material_cost'    => 'decimal:2',
        'standard_labor_cost'       => 'decimal:2',
        'standard_overhead_cost'    => 'decimal:2',
        'standard_total_cost'       => 'decimal:2',
        'standard_cost_per_unit'    => 'decimal:2',
        'actual_material_cost'      => 'decimal:2',
        'actual_labor_cost'         => 'decimal:2',
        'actual_overhead_cost'      => 'decimal:2',
        'actual_total_cost'         => 'decimal:2',
        'actual_cost_per_unit'      => 'decimal:2',
        'material_variance'         => 'decimal:2',
        'labor_variance'            => 'decimal:2',
        'overhead_variance'         => 'decimal:2',
        'total_variance'            => 'decimal:2',
        'variance_percent'          => 'decimal:2',
    ];

    // ============ RELASI ============
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============ ACCESSOR ============
    public function getVarianceColorAttribute(): string
    {
        if ($this->total_variance == 0) {
            return 'text-navy-400';
        }
        return $this->total_variance > 0 ? 'text-red-400' : 'text-green-400';
    }

    public function getVarianceLabelAttribute(): string
    {
        if ($this->total_variance == 0) {
            return 'Sesuai Standar';
        }
        return $this->total_variance > 0 ? 'Unfavorable' : 'Favorable';
    }

    public function getVarianceBadgeColorAttribute(): string
    {
        if ($this->total_variance == 0) {
            return 'bg-navy-500/10 border-navy-500/30 text-navy-400';
        }
        return $this->total_variance > 0
            ? 'bg-red-500/10 border-red-500/30 text-red-400'
            : 'bg-green-500/10 border-green-500/30 text-green-400';
    }
}