<?php

namespace App\Models\Sales;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderStatusLog extends Model
{
    use HasFactory;

    protected $table = 'sales_order_status_logs';

    protected $fillable = [
        'sales_order_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
        'reference_type',
        'reference_id',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Label status yang mudah dibaca
     */
    public function getFromStatusLabelAttribute(): string
    {
        return $this->from_status ? ucfirst(str_replace('_', ' ', $this->from_status)) : '—';
    }

    public function getToStatusLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->to_status));
    }
}