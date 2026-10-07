<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('period'); // 2025-10, 2025-Q4, dll
            $table->date('period_start');
            $table->date('period_end');
            
            // Metrics
            $table->decimal('quality_score', 5, 2)->default(0);      // 0-100
            $table->decimal('delivery_score', 5, 2)->default(0);
            $table->decimal('price_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->string('rating')->nullable();                      // A, B, C, D
            
            // Detail
            $table->integer('total_orders')->default(0);
            $table->decimal('total_received', 15, 2)->default(0);
            $table->decimal('total_rejected', 15, 2)->default(0);
            $table->decimal('reject_rate', 5, 2)->default(0);
            $table->integer('on_time_deliveries')->default(0);
            $table->integer('late_deliveries')->default(0);
            
            $table->text('notes')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['supplier_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_evaluations');
    }
};