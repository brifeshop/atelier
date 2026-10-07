<?php

namespace App\Models\Engineering;

use App\Models\User;
use App\Models\Master\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routing extends Model
{
    use HasFactory;

    protected $table = 'routings';

    protected $fillable = [
        'kode',
        'product_id',
        'version',
        'effective_date',
        'status',
        'notes',
        'total_labor_cost',
        'total_overhead_cost',
        'total_cost',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'effective_date'       => 'date',
        'approved_at'          => 'datetime',
        'total_labor_cost'     => 'decimal:2',
        'total_overhead_cost'  => 'decimal:2',
        'total_cost'           => 'decimal:2',
    ];

    // ============ RELASI ============
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function steps()
    {
        return $this->hasMany(RoutingStep::class)->orderBy('sequence');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ============ SCOPE ============
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ============ ACCESSOR ============
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
            'active'   => 'bg-green-500/10 border-green-500/30 text-green-400',
            'obsolete' => 'bg-red-500/10 border-red-500/30 text-red-400',
            default    => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'active'   => 'Aktif',
            'obsolete' => 'Obsolete',
            default    => 'Unknown',
        };
    }

    // Total waktu produksi (menit)
    public function getTotalTimeMinutesAttribute(): float
    {
        return $this->steps->sum(function ($step) {
            return (float) $step->setup_time_minutes + (float) $step->run_time_per_unit_minutes;
        });
    }

    // ============ HELPER ============
    public static function generateKode(): string
    {
        $prefix = 'RTG-' . date('Y') . '-';

        $last = self::where('kode', 'like', "{$prefix}%")
            ->orderByDesc('kode')
            ->first();

        $lastNumber = $last ? (int) substr($last->kode, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    public function canActivate(): bool
    {
        return $this->status === 'draft' && $this->steps()->count() > 0;
    }

    public function canObsolete(): bool
    {
        return $this->status === 'active';
    }
}