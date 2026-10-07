<?php

namespace App\Models\Purchasing;

use App\Models\Master\Material;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'purchase_requisition_item_id',
        'material_id',
        'qty',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'subtotal',
        'received_qty',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'received_qty' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Hitung subtotal otomatis
     */
    public function calculateTotals(): void
    {
        $gross = $this->qty * $this->unit_price;
        $this->discount_amount = round($gross * $this->discount_percent / 100, 2);
        $this->subtotal = $gross - $this->discount_amount;
    }

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->calculateTotals();
        });
    }

    /**
     * Sisa qty yang belum diterima
     */
    public function getRemainingQtyAttribute(): float
    {
        return max(0, $this->qty - $this->received_qty);
    }

    public function purchaseRequisitionItem()
    {
        return $this->belongsTo(PurchaseRequisitionItem::class, 'purchase_requisition_item_id');
    }

    /**
     * Apakah sudah diterima semua?
     */
    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_qty >= $this->qty;
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Total qty rejected dari semua GR
     */
    public function getTotalRejectedAttribute(): float
    {
        return $this->goodsReceiptItems->sum('qty_rejected');
    }
}