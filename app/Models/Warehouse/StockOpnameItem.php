<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_items';

    protected $fillable = [
        'stock_opname_id',
        'item_type',
        'item_id',
        'system_qty',
        'physical_qty',
        'variance_qty',
        'unit_price',
        'variance_value',
        'notes',
    ];

    protected $casts = [
        'system_qty'     => 'decimal:2',
        'physical_qty'   => 'decimal:2',
        'variance_qty'   => 'decimal:2',
        'unit_price'     => 'decimal:2',
        'variance_value' => 'decimal:2',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function item()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    // Warna untuk variance
    public function getVarianceColorAttribute(): string
    {
        if ($this->physical_qty === null) return 'text-navy-400';
        if ($this->variance_qty > 0) return 'text-green-400';
        if ($this->variance_qty < 0) return 'text-red-400';
        return 'text-navy-300';
    }
}