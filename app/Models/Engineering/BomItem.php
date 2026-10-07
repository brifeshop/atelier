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