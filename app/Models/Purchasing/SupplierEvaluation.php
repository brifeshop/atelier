<?php

namespace App\Models\Purchasing;

use App\Models\Master\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierEvaluation extends Model
{
    use HasFactory;

    protected $table = 'supplier_evaluations';

    protected $fillable = [
        'supplier_id',
        'period',
        'period_start',
        'period_end',
        'quality_score',
        'delivery_score',
        'price_score',
        'total_score',
        'rating',
        'total_orders',
        'total_received',
        'total_rejected',
        'reject_rate',
        'on_time_deliveries',
        'late_deliveries',
        'notes',
        'evaluated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'quality_score' => 'decimal:2',
        'delivery_score' => 'decimal:2',
        'price_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'reject_rate' => 'decimal:2',
        'total_received' => 'decimal:2',
        'total_rejected' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function evaluatedBy()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function getRatingColorAttribute(): string
    {
        return match($this->rating) {
            'A' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'B' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            'C' => 'bg-gold-500/10 text-gold-500 border-gold-500/30',
            'D' => 'bg-red-500/10 text-red-400 border-red-500/30',
            default => 'bg-navy-800 text-navy-300 border-navy-700',
        };
    }
}