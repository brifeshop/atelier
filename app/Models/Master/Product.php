<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'kode',
        'nama',
        'category',
        'unit',
        'description',
        'selling_price',
        'notes',
        'photo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'selling_price' => 'decimal:2',
    ];

    /**
     * Generate kode product otomatis: PRD-0001
     */
    public static function generateKode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $lastNumber = $last ? (int) substr($last->kode, -4) : 0;
        return 'PRD-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) return null;
        return asset('storage/' . $this->photo_path);
    }

    public function workOrders()
    {
        return $this->hasMany(\App\Models\Production\WorkOrder::class);
    }

    /**
     * Scope: hanya product aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}