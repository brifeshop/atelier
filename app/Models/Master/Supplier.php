<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'kode',
        'nama',
        'pic_name',
        'phone',
        'email',
        'address',
        'city',
        'bank_account',
        'payment_terms',
        'rating',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
    ];

    /**
     * Generate kode supplier otomatis: SUP-0001
     */
    public static function generateKode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $lastNumber = $last ? (int) substr($last->kode, -4) : 0;
        return 'SUP-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: hanya supplier aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}