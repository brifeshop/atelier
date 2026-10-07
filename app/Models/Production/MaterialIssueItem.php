<?php

namespace App\Models\Production;

use App\Models\Master\Material;
use App\Models\Warehouse\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialIssueItem extends Model
{
    use HasFactory;

    protected $table = 'material_issue_items';

    protected $fillable = [
        'material_issue_id', 'work_order_material_id', 'material_id',
        'location_id', 'qty', 'unit_cost', 'total_cost', 'notes',
    ];

    protected $casts = [
        'qty'        => 'decimal:4',
        'unit_cost'  => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function materialIssue() { return $this->belongsTo(MaterialIssue::class); }
    public function workOrderMaterial() { return $this->belongsTo(WorkOrderMaterial::class); }
    public function material() { return $this->belongsTo(Material::class); }
    public function location() { return $this->belongsTo(Location::class); }
}