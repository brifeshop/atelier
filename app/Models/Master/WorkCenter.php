<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkCenter extends Model
{
    use HasFactory;

    protected $table = 'work_centers';

    protected $fillable = [
        'kode',
        'nama',
        'description',
        'hourly_rate',
        'capacity_per_hour',
        'location',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'capacity_per_hour' => 'decimal:2',
    ];

    /**
     * Generate kode work center otomatis: WC-0001
     */
    public static function generateKode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $lastNumber = $last ? (int) substr($last->kode, -4) : 0;
        return 'WC-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: hanya work center aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}