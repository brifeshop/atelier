<?php

namespace App\Models\Production;

use App\Models\User;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialIssue extends Model
{
    use HasFactory;

    protected $table = 'material_issues';

    protected $fillable = [
        'issue_number', 'work_order_id', 'warehouse_id', 'date',
        'status', 'notes', 'issued_by', 'issued_at',
    ];

    protected $casts = [
        'date'      => 'date',
        'issued_at' => 'datetime',
    ];

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items() { return $this->hasMany(MaterialIssueItem::class); }
    public function issuer() { return $this->belongsTo(User::class, 'issued_by'); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'  => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
            'issued' => 'bg-green-500/10 border-green-500/30 text-green-400',
            default  => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'  => 'Draft',
            'issued' => 'Sudah Dikeluarkan',
            default  => 'Unknown',
        };
    }

    public static function generateNumber(): string
    {
        $prefix = 'MI-' . date('Y-m') . '-';
        $last = self::where('issue_number', 'like', "{$prefix}%")->orderByDesc('issue_number')->first();
        $lastNumber = $last ? (int) substr($last->issue_number, -4) : 0;
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function canIssue(): bool
    {
        return $this->status === 'draft' && $this->items()->count() > 0;
    }
}