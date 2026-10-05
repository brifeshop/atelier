<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';

    protected $fillable = [
        'kode',
        'nama',
        'type',
        'address',
        'pic_name',
        'phone',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Location
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Generate kode warehouse otomatis: WH-0001
     */
    public static function generateKode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $lastNumber = $last ? (int) substr($last->kode, -4) : 0;
        return 'WH-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: hanya warehouse aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Label type dengan warna
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'Raw Material' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            'Finished Goods' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'WIP' => 'bg-gold-500/10 text-gold-500 border-gold-500/30',
            default => 'bg-navy-800 text-navy-300 border-navy-700',
        };
    }
}