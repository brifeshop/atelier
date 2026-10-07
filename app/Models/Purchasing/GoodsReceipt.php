<?php

namespace App\Models\Purchasing;

use App\Models\Master\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $table = 'goods_receipts';

    protected $fillable = [
        'gr_number',
        'purchase_order_id',
        'supplier_id',
        'date',
        'status',
        'notes',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'date' => 'date',
        'received_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Generate nomor GR otomatis: GR-2025-0001
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "GR-{$year}-";

        $last = self::where('gr_number', 'like', "{$prefix}%")
            ->orderBy('gr_number', 'desc')
            ->first();

        $lastNumber = $last ? (int) substr($last->gr_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-navy-800 text-navy-300 border-navy-700',
            'received' => 'bg-green-500/10 text-green-400 border-green-500/30',
            default => 'bg-navy-800 text-navy-300 border-navy-700',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'received' => 'Received',
            default => ucfirst($this->status),
        };
    }

    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    public function canReceive(): bool
    {
        return $this->status === 'draft' && $this->items->count() > 0;
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}