<?php

namespace App\Models\Warehouse;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $table = 'stock_opnames';

    protected $fillable = [
        'opname_number',
        'location_id',
        'opname_date',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // ============ RELASI ============
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ============ ACCESSOR ============
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'       => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
            'in_progress' => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
            'completed'   => 'bg-green-500/10 border-green-500/30 text-green-400',
            'cancelled'   => 'bg-red-500/10 border-red-500/30 text-red-400',
            default       => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'       => 'Draft',
            'in_progress' => 'Sedang Dihitung',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
            default       => 'Unknown',
        };
    }

    // Total variance value
    public function getTotalVarianceValueAttribute(): float
    {
        return (float) $this->items()->sum('variance_value');
    }

    // Jumlah item yang belum diinput
    public function getUnfilledCountAttribute(): int
    {
        return $this->items()->whereNull('physical_qty')->count();
    }

    // Jumlah item dengan variance (selisih)
    public function getVarianceCountAttribute(): int
    {
        return $this->items()->where('variance_qty', '!=', 0)->count();
    }

    // ============ HELPER ============
    public static function generateNumber(): string
    {
        $prefix = 'OPN-' . date('Y-m') . '-';

        $last = self::where('opname_number', 'like', "{$prefix}%")
            ->orderByDesc('opname_number')
            ->first();

        $lastNumber = $last ? (int) substr($last->opname_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    // Cek apakah bisa diedit
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    // Cek apakah bisa di-approve
    public function canApprove(): bool
    {
        return $this->canEdit() && $this->unfilled_count === 0 && $this->items()->count() > 0;
    }
}