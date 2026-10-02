<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'kode',
        'nama',
        'department',
        'position',
        'skill_level',
        'phone',
        'email',
        'join_date',
        'hourly_rate',
        'daily_rate',
        'monthly_salary',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'join_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
    ];

    /**
     * Generate kode employee otomatis: EMP-0001
     */
    public static function generateKode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $lastNumber = $last ? (int) substr($last->kode, -4) : 0;
        return 'EMP-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: hanya employee aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Hitung ulang hourly_rate dari monthly_salary.
     * Asumsi: 22 hari kerja, 8 jam per hari = 176 jam per bulan.
     */
    public static function hitungHourlyRate(float $monthlySalary): float
    {
        return round($monthlySalary / 176, 2);
    }
}