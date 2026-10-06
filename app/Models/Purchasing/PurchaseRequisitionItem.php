<?php

namespace App\Models\Purchasing;

use App\Models\Master\Material;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_requisition_items';

    protected $fillable = [
        'purchase_requisition_id',
        'material_id',
        'qty',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_requisition_item_id');
    }

    /**
 * Total qty yang sudah di-PO
 */
public function getOrderedQtyAttribute(): float
{
    return $this->purchaseOrderItems->sum('qty');
}

/**
 * Sisa qty yang belum di-PO
 */
public function getRemainingQtyAttribute(): float
{
    return max(0, $this->qty - $this->ordered_qty);
}

/**
 * Sudah di-PO semua?
 */
public function getIsFullyOrderedAttribute(): bool
{
    return $this->ordered_qty >= $this->qty;
}

/**
 * Belum di-PO?
 */
public function getIsPendingAttribute(): bool
{
    return $this->ordered_qty < $this->qty;
}
}