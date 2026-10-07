<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->date('snapshot_date');
            
            $table->decimal('planned_qty', 15, 2);
            $table->decimal('actual_qty', 15, 2)->default(0);
            $table->decimal('rejected_qty', 15, 2)->default(0);
            
            // Standard cost (dari BOM × planned)
            $table->decimal('standard_material_cost', 15, 2)->default(0);
            $table->decimal('standard_labor_cost', 15, 2)->default(0);
            $table->decimal('standard_overhead_cost', 15, 2)->default(0);
            $table->decimal('standard_total_cost', 15, 2)->default(0);
            $table->decimal('standard_cost_per_unit', 15, 2)->default(0);
            
            // Actual cost (dari real production)
            $table->decimal('actual_material_cost', 15, 2)->default(0);
            $table->decimal('actual_labor_cost', 15, 2)->default(0);
            $table->decimal('actual_overhead_cost', 15, 2)->default(0);
            $table->decimal('actual_total_cost', 15, 2)->default(0);
            $table->decimal('actual_cost_per_unit', 15, 2)->default(0);
            
            // Variance (actual - standard)
            $table->decimal('material_variance', 15, 2)->default(0);
            $table->decimal('labor_variance', 15, 2)->default(0);
            $table->decimal('overhead_variance', 15, 2)->default(0);
            $table->decimal('total_variance', 15, 2)->default(0);
            $table->decimal('variance_percent', 8, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['work_order_id', 'snapshot_date']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_snapshots');
    }
};