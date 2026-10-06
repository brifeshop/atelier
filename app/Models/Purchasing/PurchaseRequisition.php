<?php

namespace App\Models\Purchasing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    use HasFactory;

    protected $table = 'purchase_requisitions';

    protected $fillable = [
        'pr_number',
        'requested_by',
        'department',
        'date',
        'needed_date',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'needed_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Generate nomor PR otomatis: PR-2025-0001
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "PR-{$year}-";

        $last = self::where('pr_number', 'like', "{$prefix}%")
            ->orderBy('pr_number', 'desc')
            ->first();

        $lastNumber = $last ? (int) substr($last->pr_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-navy-800 text-navy-300 border-navy-700',
            'approved' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'rejected' => 'bg-red-500/10 text-red-400 border-red-500/30',
            default => 'bg-navy-800 text-navy-300 border-navy-700',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    public function canApprove(): bool
    {
        return $this->status === 'draft' && $this->items->count() > 0;
    }
}