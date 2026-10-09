<?php

namespace App\Models\Production;

use App\Models\User;
use App\Models\Master\Product;
use App\Models\Engineering\Bom;
use App\Models\Engineering\Routing;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $table = 'work_orders';

    protected $fillable = [
        'wo_number', 'product_id', 'sales_order_id', 'sales_order_item_id',
        'bom_id', 'routing_id', 'planned_qty', 'actual_qty', 'rejected_qty',
        'start_date', 'end_date', 'actual_start_date', 'actual_end_date',
        'status', 'priority', 'notes',
        'total_material_cost', 'total_labor_cost', 'total_overhead_cost',
        'total_cost', 'cost_per_unit',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'planned_qty'           => 'decimal:2',
        'actual_qty'            => 'decimal:2',
        'rejected_qty'          => 'decimal:2',
        'start_date'            => 'date',
        'end_date'              => 'date',
        'actual_start_date'     => 'date',
        'actual_end_date'       => 'date',
        'approved_at'           => 'datetime',
        'total_material_cost'   => 'decimal:2',
        'total_labor_cost'      => 'decimal:2',
        'total_overhead_cost'   => 'decimal:2',
        'total_cost'            => 'decimal:2',
        'cost_per_unit'         => 'decimal:2',
    ];

    // ============ RELASI ============
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }

    public function routing()
    {
        return $this->belongsTo(Routing::class);
    }

    public function materials()
    {
        return $this->hasMany(WorkOrderMaterial::class);
    }

    public function materialIssues()
    {
        return $this->hasMany(MaterialIssue::class);
    }

    public function progress()
    {
        return $this->hasMany(WorkOrderProgress::class)->orderBy('sequence');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ============ SCOPE ============
    public function scopeDraft($query) { return $query->where('status', 'draft'); }
    public function scopeReleased($query) { return $query->where('status', 'released'); }
    public function scopeInProgress($query) { return $query->where('status', 'in_progress'); }
    public function scopeCompleted($query) { return $query->where('status', 'completed'); }

    // ============ ACCESSOR ============
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'       => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
            'released'    => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
            'in_progress' => 'bg-gold-500/10 border-gold-500/30 text-gold-400',
            'completed'   => 'bg-green-500/10 border-green-500/30 text-green-400',
            'cancelled'   => 'bg-red-500/10 border-red-500/30 text-red-400',
            default       => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'       => 'Draft',
            'released'    => 'Released',
            'in_progress' => 'In Progress',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
            default       => 'Unknown',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
            'normal' => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
            'high'   => 'bg-orange-500/10 border-orange-500/30 text-orange-400',
            'urgent' => 'bg-red-500/10 border-red-500/30 text-red-400',
            default  => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'Rendah',
            'normal' => 'Normal',
            'high'   => 'Tinggi',
            'urgent' => 'Urgent',
            default  => 'Normal',
        };
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->planned_qty <= 0) return 0;
        return min(100, ($this->actual_qty / $this->planned_qty) * 100);
    }

    public function getRemainingQtyAttribute(): float
    {
        return max(0, $this->planned_qty - $this->actual_qty);
    }

    public function finishedGoodsReceipts()
    {
        return $this->hasMany(FinishedGoodsReceipt::class);
    }

    /**
     * Total qty good dari semua FG Receipt
     */
    public function getTotalFgrQtyAttribute(): float
    {
        return (float) $this->finishedGoodsReceipts()
            ->where('status', 'received')
            ->sum('qty_good');
    }

    // ============ HELPER ============
    public static function generateNumber(): string
    {
        $prefix = 'WO-' . date('Y-m') . '-';

        $last = self::where('wo_number', 'like', "{$prefix}%")
            ->orderByDesc('wo_number')
            ->first();

        $lastNumber = $last ? (int) substr($last->wo_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function canEdit(): bool { return $this->status === 'draft'; }
    public function canRelease(): bool{    return $this->status === 'draft' && $this->bom_id !== null;}
    public function canStart(): bool { return $this->status === 'released'; }
    public function canComplete(): bool { return $this->status === 'in_progress'; }
    public function canCancel(): bool { return in_array($this->status, ['draft', 'released']); }
}