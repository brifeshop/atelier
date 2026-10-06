<?php

namespace App\Models\Purchasing;

use App\Models\Master\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'purchase_requisition_id',
        'date',
        'delivery_date',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'sent_at',
        'received_at',
        'closed_at',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate nomor PO otomatis: PO-2025-0001
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "PO-{$year}-";

        $last = self::where('po_number', 'like', "{$prefix}%")
            ->orderBy('po_number', 'desc')
            ->first();

        $lastNumber = $last ? (int) substr($last->po_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate totals dari items
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $discountAmount = $this->items->sum('discount_amount');

        $this->subtotal = $subtotal + $discountAmount;
        $this->discount_amount = $discountAmount;
        $this->total_amount = $subtotal + $this->tax_amount;

        $this->save();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-navy-800 text-navy-300 border-navy-700',
            'sent' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            'partial' => 'bg-gold-500/10 text-gold-500 border-gold-500/30',
            'received' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'closed' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/30',
            default => 'bg-navy-800 text-navy-300 border-navy-700',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'sent' => 'Sent',
            'partial' => 'Partial',
            'received' => 'Received',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft']);
    }

    public function canSend(): bool
    {
        return $this->status === 'draft' && $this->items->count() > 0;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    public function canCreateGR(): bool
    {
        return in_array($this->status, ['sent', 'partial']);
    }
}