<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('sales_order_item_id')->nullable()->constrained('sales_order_items')->nullOnDelete();
            $table->foreignId('bom_id')->nullable()->constrained('boms')->nullOnDelete();
            $table->foreignId('routing_id')->nullable()->constrained('routings')->nullOnDelete();
            
            $table->decimal('planned_qty', 15, 2);
            $table->decimal('actual_qty', 15, 2)->default(0);
            $table->decimal('rejected_qty', 15, 2)->default(0);
            
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            
            $table->string('status')->default('draft');
            $table->string('priority')->default('normal');
            $table->text('notes')->nullable();
            
            // Costing (cache)
            $table->decimal('total_material_cost', 15, 2)->default(0);
            $table->decimal('total_labor_cost', 15, 2)->default(0);
            $table->decimal('total_overhead_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('cost_per_unit', 15, 2)->default(0);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['status', 'start_date']);
            $table->index('sales_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};