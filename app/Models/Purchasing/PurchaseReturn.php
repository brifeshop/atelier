<?php

namespace App\Models\Purchasing;

use App\Models\Master\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $table = 'purchase_returns';

    protected $fillable = [
        'return_number',
        'supplier_id',
        'goods_receipt_id',
        'purchase_order_id',
        'date',
        'status',
        'reason',
        'notes',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate nomor return otomatis: PRT-2025-0001
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "PRT-{$year}-";

        $last = self::where('return_number', 'like', "{$prefix}%")
            ->orderBy('return_number', 'desc')
            ->first();

        $lastNumber = $last ? (int) substr($last->return_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-navy-800 text-navy-300 border-navy-700',
            'completed' => 'bg-green-500/10 text-green-400 border-green-500/30',
            default => 'bg-navy-800 text-navy-300 border-navy-700',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }

    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    public function canComplete(): bool
    {
        return $this->status === 'draft' && $this->items->count() > 0;
    }

    /**
     * Total nilai return
     */
    public function getTotalValueAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->qty * $i->unit_price);
    }
}