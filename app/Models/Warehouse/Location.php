<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';

    protected $fillable = [
        'warehouse_id',
        'kode',
        'nama',
        'capacity',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'decimal:2',
    ];

    /**
     * Relasi ke Warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Generate kode location otomatis: WH-0001-A-01
     * Format: {warehouse_kode}-{huruf}-{angka}
     */
    public static function generateKode(int $warehouseId): string
    {
        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse) {
            return 'LOC-0001';
        }

        // Hitung lokasi ke berapa di warehouse ini
        $count = self::where('warehouse_id', $warehouseId)->count();
        $number = $count + 1;

        // Format: WH-0001-A-01
        // Huruf A untuk 1-10, B untuk 11-20, dst.
        $letterIndex = (int) floor(($number - 1) / 10);
        $letter = chr(65 + $letterIndex); // 65 = 'A'
        $numberInGroup = (($number - 1) % 10) + 1;

        return $warehouse->kode . '-' . $letter . '-' . str_pad($numberInGroup, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: hanya location aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}