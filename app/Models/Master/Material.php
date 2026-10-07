<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materials';

    protected $fillable = [
        'kode',
        'kode_bahan',           // ← TAMBAH
        'nama',
        'spesifikasi',          // ← TAMBAH
        'category',
        'unit',
        'panjang_standar',      // ← TAMBAH
        'lebar_standar',        // ← TAMBAH
        'tinggi_standar',       // ← TAMBAH
        'berat_standar',        // ← TAMBAH
        'volume_standar',       // ← TAMBAH
        'costing_method',       // ← TAMBAH
        'base_unit',            // ← TAMBAH
        'yield_percent',        // ← TAMBAH
        'price',
        'min_stock',
        'max_stock',
        'current_stock',
        'supplier_id',
        'location',
        'notes',
        'photo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'price'             => 'decimal:2',
        'min_stock'         => 'decimal:2',
        'max_stock'         => 'decimal:2',
        'current_stock'     => 'decimal:2',
        'panjang_standar'   => 'decimal:4',     // ← TAMBAH
        'lebar_standar'     => 'decimal:4',     // ← TAMBAH
        'tinggi_standar'    => 'decimal:4',     // ← TAMBAH
        'berat_standar'     => 'decimal:4',     // ← TAMBAH
        'volume_standar'    => 'decimal:4',     // ← TAMBAH
        'yield_percent'     => 'decimal:2',     // ← TAMBAH
    ];

    /**
     * Relasi ke Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Generate kode material otomatis: MAT-0001
     */
    public static function generateKode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $lastNumber = $last ? (int) substr($last->kode, -4) : 0;
        return 'MAT-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: hanya material aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * URL foto material
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) return null;
        return asset('storage/' . $this->photo_path);
    }

    public function supplierPrices()
    {
        return $this->hasMany(\App\Models\Purchasing\SupplierPrice::class);
    }

    public function workOrderMaterials()
    {
        return $this->hasMany(\App\Models\Production\WorkOrderMaterial::class);
    }

    // ============ COSTING ACCESSOR ============

    /**
     * Label costing method
     */
    public function getCostingMethodLabelAttribute(): string
    {
        return match($this->costing_method) {
            'per_unit'   => 'Per Unit',
            'per_area'   => 'Per Area (Luas)',
            'per_volume' => 'Per Volume',
            'per_length' => 'Per Panjang',
            'per_weight' => 'Per Berat',
            default      => 'Per Unit',
        };
    }

    /**
     * Deskripsi costing method
     */
    public function getCostingMethodDescriptionAttribute(): string
    {
        return match($this->costing_method) {
            'per_unit'   => 'Beli pcs, pakai pcs',
            'per_area'   => 'Beli lembar, pakai luas (mm²)',
            'per_volume' => 'Beli batang, pakai volume (mm³)',
            'per_length' => 'Beli roll, pakai panjang (mm)',
            'per_weight' => 'Beli karung, pakai berat (gram)',
            default      => 'Beli pcs, pakai pcs',
        };
    }

    /**
     * Warna badge costing method
     */
    public function getCostingMethodColorAttribute(): string
    {
        return match($this->costing_method) {
            'per_unit'   => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
            'per_area'   => 'bg-green-500/10 border-green-500/30 text-green-400',
            'per_volume' => 'bg-gold-500/10 border-gold-500/30 text-gold-400',
            'per_length' => 'bg-orange-500/10 border-orange-500/30 text-orange-400',
            'per_weight' => 'bg-purple-500/10 border-purple-500/30 text-purple-400',
            default      => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    /**
     * Cek apakah material butuh dimensi
     */
    public function getNeedsDimensionAttribute(): bool
    {
        return in_array($this->costing_method, ['per_area', 'per_volume', 'per_length']);
    }

    /**
     * Cek apakah material butuh berat
     */
    public function getNeedsWeightAttribute(): bool
    {
        return $this->costing_method === 'per_weight';
    }

    /**
     * Cek apakah material butuh volume (ml)
     */
    public function getNeedsVolumeAttribute(): bool
    {
        return $this->costing_method === 'per_volume';
    }

    /**
     * Hitung harga per satuan dasar
     */
    public function getPricePerBaseUnitAttribute(): float
    {
        $price = (float) $this->price;
        $yield = ($this->yield_percent ?: 100) / 100;

        switch ($this->costing_method) {
            case 'per_area':
                $standar = (float) $this->panjang_standar * (float) $this->lebar_standar;
                return $standar > 0 ? ($price / $standar) / $yield : 0;

            case 'per_volume':
                $standar = (float) $this->panjang_standar * (float) $this->lebar_standar * (float) $this->tinggi_standar;
                return $standar > 0 ? ($price / $standar) / $yield : 0;

            case 'per_length':
                $standar = (float) $this->panjang_standar;
                return $standar > 0 ? ($price / $standar) / $yield : 0;

            case 'per_weight':
                $standar = (float) $this->berat_standar;
                return $standar > 0 ? ($price / $standar) / $yield : 0;

            case 'per_unit':
            default:
                return $price / $yield;
        }
    }

    /**
     * Deskripsi satuan standar
     */
    public function getStandardSizeLabelAttribute(): string
    {
        switch ($this->costing_method) {
            case 'per_area':
                return "{$this->panjang_standar} × {$this->lebar_standar} mm";

            case 'per_volume':
                return "{$this->panjang_standar} × {$this->lebar_standar} × {$this->tinggi_standar} mm";

            case 'per_length':
                return "{$this->panjang_standar} mm";

            case 'per_weight':
                return "{$this->berat_standar} gram";

            case 'per_unit':
            default:
                return "1 {$this->unit}";
        }
    }

    /**
     * Scope: material di bawah stok minimum
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock');
    }
}