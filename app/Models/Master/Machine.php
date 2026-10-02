<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $table = 'machines';

    protected $fillable = [
        'kode',
        'nama',
        'type',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'useful_life_years',
        'salvage_value',
        'power_kw',
        'capacity_per_hour',
        'maintenance_cost_per_month',
        'location',
        'status',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'power_kw' => 'decimal:2',
        'capacity_per_hour' => 'decimal:2',
        'maintenance_cost_per_month' => 'decimal:2',
        'useful_life_years' => 'integer',
    ];

    /**
     * Generate kode machine otomatis: MCH-0001
     */
    public static function generateKode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $lastNumber = $last ? (int) substr($last->kode, -4) : 0;
        return 'MCH-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: hanya machine aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Hitung depresiasi per jam.
     * Asumsi: 250 hari kerja/tahun, 8 jam/hari = 2000 jam/tahun.
     */
    public function getDepreciationPerHourAttribute(): float
    {
        $totalHours = $this->useful_life_years * 2000;
        if ($totalHours <= 0) return 0;

        $depreciableValue = $this->purchase_price - $this->salvage_value;
        return round($depreciableValue / $totalHours, 2);
    }

    /**
     * Hitung maintenance per jam.
     * Asumsi: 22 hari kerja/bulan, 8 jam/hari = 176 jam/bulan.
     */
    public function getMaintenancePerHourAttribute(): float
    {
        if (!$this->maintenance_cost_per_month) return 0;
        return round($this->maintenance_cost_per_month / 176, 2);
    }
}