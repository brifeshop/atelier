<?php

namespace App\Models\Warehouse;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'movement_number',
        'item_type',
        'item_id',
        'location_id',
        'type',
        'qty',
        'qty_before',
        'qty_after',
        'unit_price',
        'total_value',
        'reference_type',
        'reference_id',
        'reference_number',
        'date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'qty_before' => 'decimal:2',
        'qty_after' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_value' => 'decimal:2',
        'date' => 'date',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function item()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate nomor movement otomatis: SM-2025-0001
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "SM-{$year}-";

        $last = self::where('movement_number', 'like', "{$prefix}%")
            ->orderBy('movement_number', 'desc')
            ->first();

        $lastNumber = $last ? (int) substr($last->movement_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Label type dengan warna
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'in' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'out' => 'bg-red-500/10 text-red-400 border-red-500/30',
            'transfer' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            'adjustment' => 'bg-gold-500/10 text-gold-500 border-gold-500/30',
            default => 'bg-navy-800 text-navy-300 border-navy-700',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in' => 'Masuk',
            'out' => 'Keluar',
            'transfer' => 'Transfer',
            'adjustment' => 'Penyesuaian',
            default => ucfirst($this->type),
        };
    }
}