<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('routing_step_id')->nullable()->constrained('routing_steps')->nullOnDelete();
            
            $table->unsignedInteger('sequence');
            $table->string('operation_name');
            $table->foreignId('work_center_id')->nullable()->constrained('work_centers')->nullOnDelete();
            
            $table->string('status')->default('pending');
            $table->decimal('qty_completed', 15, 2)->default(0);
            $table->decimal('qty_rejected', 15, 2)->default(0);
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->decimal('actual_minutes', 8, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('overhead_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'sequence']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_progress');
    }
};