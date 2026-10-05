<?php

namespace App\Models\Warehouse;

use App\Models\Master\Material;
use App\Models\Master\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'item_type',
        'item_id',
        'location_id',
        'qty',
        'min_stock',
        'max_stock',
        'last_movement_at',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'last_movement_at' => 'datetime',
    ];

    /**
     * Relasi ke Location
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Relasi polymorphic ke item (Material / Product)
     */
    public function item()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    /**
     * Scope: filter by type
     */
    public function scopeMaterials($query)
    {
        return $query->where('item_type', 'material');
    }

    public function scopeProducts($query)
    {
        return $query->where('item_type', 'product');
    }

    /**
     * Scope: stok di bawah minimum
     */
    public function scopeLowStock($query)
    {
        return $query->whereNotNull('min_stock')
                     ->whereColumn('qty', '<=', 'min_stock');
    }

    /**
     * Cek apakah stok rendah
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->min_stock !== null && $this->qty <= $this->min_stock;
    }

    /**
     * Get atau buat inventory record.
     */
    public static function getOrCreate(string $itemType, int $itemId, int $locationId): self
    {
        return self::firstOrCreate(
            [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'location_id' => $locationId,
            ],
            [
                'qty' => 0,
            ]
        );
    }
}