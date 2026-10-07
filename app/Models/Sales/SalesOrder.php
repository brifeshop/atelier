<?php

namespace App\Models\Sales;

use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $table = 'sales_orders';

    protected $fillable = [
        'so_number',
        'customer_id',
        'sales_person_id',
        'order_date',
        'delivery_date',
        'status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'commission_rate',
        'commission_amount',
        'notes',
        'confirmed_at',
        'delivered_at',
        'paid_at',
        'closed_at',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'paid_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Relasi ke Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relasi ke Sales Person (Employee)
     */
    public function salesPerson()
    {
        return $this->belongsTo(Employee::class, 'sales_person_id');
    }

    /**
     * Relasi ke Items
     */
    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Relasi ke User (pembuat)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate SO number otomatis: SO-2025-0001
     */
    public static function generateSoNumber(): string
    {
        $year = date('Y');
        $prefix = "SO-{$year}-";

        $last = self::where('so_number', 'like', "{$prefix}%")
            ->orderBy('so_number', 'desc')
            ->first();

        $lastNumber = $last ? (int) substr($last->so_number, -4) : 0;
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate totals dari items
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $discountAmount = $this->items->sum('discount_amount');

        $this->subtotal = $subtotal + $discountAmount; // subtotal sebelum diskon
        $this->discount_amount = $discountAmount;
        $this->total_amount = $subtotal;
        $this->commission_amount = round($subtotal * $this->commission_rate / 100, 2);

        $this->save();
    }

    /**
     * Scope per status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: SO yang belum selesai (aktif)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['closed', 'cancelled']);
    }

    /**
     * Label status (untuk tampilan)
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'confirmed' => 'Confirmed',
            'in_production' => 'In Production',
            'delivered' => 'Delivered',
            'paid' => 'Paid',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Bisa diedit?
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'confirmed']);
    }

    /**
     * Bisa dikonfirmasi?
     */
    public function canConfirm(): bool
    {
        return $this->status === 'draft' && $this->items->count() > 0;
    }

        /**
     * Relasi ke status logs
     */
    public function statusLogs()
    {
        return $this->hasMany(SalesOrderStatusLog::class)->orderBy('changed_at', 'desc');
    }

    public function workOrders()
    {
        return $this->hasMany(\App\Models\Production\WorkOrder::class);
    }

    /**
     * Bisa dibatalkan?
     */
    public function canCancel(): bool
    {
        return in_array($this->status, ['draft', 'confirmed']);
    }
}