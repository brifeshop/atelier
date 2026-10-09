<?php

namespace App\Models\Engineering;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    use HasFactory;

    protected $table = 'bom_items';

    protected $fillable = [
        'bom_id',
        'sequence',
        'item_type',
        'item_id',
        'qty',
        'unit',
        'spesifikasi',          // ← TAMBAH
        'divisi',               // ← TAMBAH
        'level',                // ← TAMBAH
        'is_header',          // ← TAMBAH
        'header_label',       // ← TAMBAH
        'panjang_pakai',        // ← TAMBAH
        'lebar_pakai',          // ← TAMBAH
        'tinggi_pakai',         // ← TAMBAH
        'berat_pakai',          // ← TAMBAH
        'volume_pakai',         // ← TAMBAH
        'scrap_percent',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'qty'             => 'decimal:4',
        'scrap_percent'   => 'decimal:2',
        'unit_cost'       => 'decimal:2',
        'total_cost'      => 'decimal:2',
        'panjang_pakai'   => 'decimal:4',       // ← TAMBAH
        'lebar_pakai'     => 'decimal:4',       // ← TAMBAH
        'tinggi_pakai'    => 'decimal:4',       // ← TAMBAH
        'berat_pakai'     => 'decimal:4',       // ← TAMBAH
        'volume_pakai'    => 'decimal:4',    
        'is_header'       => 'boolean',        // ← TAMBAH
    ];

    // ============ RELASI ============
    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }

    public function item()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    // Relasi ke sub-BOM (kalau item_type = 'product')
    public function subBom()
    {
        if ($this->item_type !== 'product') return null;

        return Bom::where('product_id', $this->item_id)
            ->where('status', 'active')
            ->latest('effective_date')
            ->first();
    }

    // ============ ACCESSOR ============
    public function getItemTypeLabelAttribute(): string
    {
        return match($this->item_type) {
            'material' => 'Material',
            'product'  => 'Sub-Assembly',
            default    => ucfirst($this->item_type),
        };
    }

    public function getItemTypeColorAttribute(): string
    {
        return match($this->item_type) {
            'material' => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
            'product'  => 'bg-gold-500/10 border-gold-500/30 text-gold-400',
            default    => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    /**
     * Deskripsi dimensi pakai
     */
    public function getDimensionLabelAttribute(): string
    {
        // Cek material — kalau volume cair
        if ($this->item_type === 'material' && $this->item) {
            $material = $this->item;
            if ($material->costing_method === 'per_volume' && $material->volume_type === 'cair') {
                return $this->volume_pakai ? "{$this->volume_pakai} ml" : '-';
            }
        }

        $parts = [];

        if ($this->panjang_pakai) $parts[] = $this->panjang_pakai;
        if ($this->lebar_pakai) $parts[] = $this->lebar_pakai;
        if ($this->tinggi_pakai) $parts[] = $this->tinggi_pakai;

        if (count($parts) > 0) {
            return implode(' × ', $parts) . ' mm';
        }

        if ($this->berat_pakai) {
            return $this->berat_pakai . ' gram';
        }

        if ($this->volume_pakai) {
            return $this->volume_pakai . ' ml';
        }

        return '-';
    }

    /**
     * Cek apakah item punya dimensi
     */
    public function getHasDimensionAttribute(): bool
    {
        return $this->panjang_pakai || $this->lebar_pakai || $this->tinggi_pakai
            || $this->berat_pakai || $this->volume_pakai;
    }

    /**
     * Cek apakah header group
     */
    public function getIsHeaderGroupAttribute(): bool
    {
        return $this->is_header === true;
    }

    /**
     * Label untuk tampilan
     */
    public function getDisplayLabelAttribute(): string
    {
        if ($this->is_header) {
            return $this->header_label ?? 'Group';
        }
        return $this->item->nama ?? '-';
    }

    // ============ HELPER ============
    // Hitung total cost: qty × unit_cost × (1 + scrap%)
    public function calculateTotalCost(): float
    {
        $scrapMultiplier = 1 + ($this->scrap_percent / 100);
        return (float) $this->qty * (float) $this->unit_cost * $scrapMultiplier;
    }

    // Qty + scrap (qty efektif yang dibutuhkan)
    public function getEffectiveQtyAttribute(): float
    {
        $scrapMultiplier = 1 + ($this->scrap_percent / 100);
        return (float) $this->qty * $scrapMultiplier;
    }
}