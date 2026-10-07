<?php

namespace App\Models\Purchasing;

use App\Models\Master\Material;
use App\Models\Warehouse\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_return_items';

    protected $fillable = [
        'purchase_return_id',
        'material_id',
        'location_id',
        'qty',
        'unit_price',
        'reason',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function getTotalValueAttribute(): float
    {
        return $this->qty * $this->unit_price;
    }
}