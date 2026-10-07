<?php

namespace App\Models\Engineering;

use App\Models\Master\WorkCenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutingStep extends Model
{
    use HasFactory;

    protected $table = 'routing_steps';

    protected $fillable = [
        'routing_id',
        'sequence',
        'work_center_id',
        'operation_name',
        'setup_time_minutes',
        'run_time_per_unit_minutes',
        'labor_rate',
        'overhead_rate',
        'labor_cost',
        'overhead_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'setup_time_minutes'          => 'decimal:2',
        'run_time_per_unit_minutes'   => 'decimal:2',
        'labor_rate'                  => 'decimal:2',
        'overhead_rate'               => 'decimal:2',
        'labor_cost'                  => 'decimal:2',
        'overhead_cost'               => 'decimal:2',
        'total_cost'                  => 'decimal:2',
    ];

    // ============ RELASI ============
    public function routing()
    {
        return $this->belongsTo(Routing::class);
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    // ============ HELPER ============
    // Total waktu (menit) = setup + run
    public function getTotalTimeMinutesAttribute(): float
    {
        return (float) $this->setup_time_minutes + (float) $this->run_time_per_unit_minutes;
    }

    // Total waktu (jam)
    public function getTotalTimeHoursAttribute(): float
    {
        return $this->total_time_minutes / 60;
    }

    // Hitung labor cost: (setup + run) / 60 × labor_rate
    public function calculateLaborCost(): float
    {
        return ($this->total_time_minutes / 60) * (float) $this->labor_rate;
    }

    // Hitung overhead cost: (setup + run) / 60 × overhead_rate
    public function calculateOverheadCost(): float
    {
        return ($this->total_time_minutes / 60) * (float) $this->overhead_rate;
    }

    // Hitung total cost
    public function calculateTotalCost(): float
    {
        return $this->calculateLaborCost() + $this->calculateOverheadCost();
    }
}