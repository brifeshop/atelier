<?php

namespace App\Models\Purchasing;

use App\Models\Master\Material;
use App\Models\Master\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPrice extends Model
{
    use HasFactory;

    protected $table = 'supplier_prices';

    protected $fillable = [
        'supplier_id',
        'material_id',
        'price',
        'min_order_qty',
        'lead_time_days',
        'valid_from',
        'valid_to',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_order_qty' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Scope: hanya harga aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cek apakah harga masih berlaku
     */
    public function getIsValidAttribute(): bool
    {
        $today = now()->toDateString();

        if ($this->valid_from && $this->valid_from->toDateString() > $today) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->toDateString() < $today) {
            return false;
        }

        return true;
    }
}