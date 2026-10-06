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
}