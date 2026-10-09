<?php

namespace App\Models\Production;

use App\Models\User;
use App\Models\Master\Product;
use App\Models\Warehouse\Warehouse;
use App\Models\Warehouse\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGoodsReceipt extends Model
{
    use HasFactory;

    protected $table = 'finished_goods_receipts';

    protected $fillable = [
        'fgr_number',
        'work_order_id',
        'product_id',
        'warehouse_id',
        'location_id',
        'date',
        'status',
        'qty_good',
        'qty_rejected',
        'unit_cost',
        'total_value',
        'notes',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'date'         => 'date',
        'received_at'  => 'datetime',
        'qty_good'     => 'decimal:2',
        'qty_rejected' => 'decimal:2',
        'unit_cost'    => 'decimal:2',
        'total_value'  => 'decimal:2',
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(FinishedGoodsReceiptItem::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // ============ ACCESSOR ============
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
            'received' => 'bg-green-500/10 border-green-500/30 text-green-400',
            default    => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'received' => 'Diterima',
            default    => 'Unknown',
        };
    }

    // ============ HELPER ============
    public static function generateNumber(): string
    {
        $prefix = 'FGR-' . date('Y-m') . '-';
        $last = self::where('fgr_number', 'like', "{$prefix}%")
            ->orderByDesc('fgr_number')
            ->first();

        $lastNumber = $last ? (int) substr($last->fgr_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function canReceive(): bool
    {
        return $this->status === 'draft' && $this->qty_good > 0;
    }
}