<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            
            $table->decimal('required_qty', 15, 4);
            $table->decimal('issued_qty', 15, 4)->default(0);
            $table->string('unit');
            $table->decimal('scrap_percent', 5, 2)->default(0);
            
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'status']);
            $table->index('material_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_materials');
    }
};