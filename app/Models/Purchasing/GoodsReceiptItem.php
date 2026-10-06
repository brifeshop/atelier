<?php

namespace App\Models\Purchasing;

use App\Models\Master\Material;
use App\Models\Warehouse\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $table = 'goods_receipt_items';

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'material_id',
        'location_id',
        'qty_ordered',
        'qty_received',
        'qty_rejected',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'qty_ordered' => 'decimal:2',
        'qty_received' => 'decimal:2',
        'qty_rejected' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Total nilai
     */
    public function getTotalValueAttribute(): float
    {
        return $this->qty_received * $this->unit_price;
    }
}