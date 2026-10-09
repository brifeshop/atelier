<?php

namespace App\Models\Production;

use App\Models\Warehouse\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedGoodsReceiptItem extends Model
{
    use HasFactory;

    protected $table = 'finished_goods_receipt_items';

    protected $fillable = [
        'finished_goods_receipt_id',
        'location_id',
        'qty',
        'unit_cost',
        'total_value',
        'notes',
    ];

    protected $casts = [
        'qty'         => 'decimal:2',
        'unit_cost'   => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function finishedGoodsReceipt()
    {
        return $this->belongsTo(FinishedGoodsReceipt::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}