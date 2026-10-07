<?php

namespace App\Models\Production;

use App\Models\Master\Material;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderMaterial extends Model
{
    use HasFactory;

    protected $table = 'work_order_materials';

    protected $fillable = [
        'work_order_id', 'material_id', 'required_qty', 'issued_qty',
        'unit', 'scrap_percent', 'unit_cost', 'total_cost', 'status', 'notes',
    ];

    protected $casts = [
        'required_qty'   => 'decimal:4',
        'issued_qty'     => 'decimal:4',
        'scrap_percent'  => 'decimal:2',
        'unit_cost'      => 'decimal:2',
        'total_cost'     => 'decimal:2',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function issueItems()
    {
        return $this->hasMany(MaterialIssueItem::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
            'partial' => 'bg-gold-500/10 border-gold-500/30 text-gold-400',
            'issued'  => 'bg-green-500/10 border-green-500/30 text-green-400',
            default   => 'bg-navy-500/10 border-navy-500/30 text-navy-400',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Belum',
            'partial' => 'Sebagian',
            'issued'  => 'Sudah Diambil',
            default   => 'Unknown',
        };
    }

    public function getRemainingQtyAttribute(): float
    {
        return max(0, $this->required_qty - $this->issued_qty);
    }
}