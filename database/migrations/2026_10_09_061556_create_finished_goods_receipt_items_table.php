<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finished_goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_goods_receipt_id')->constrained('finished_goods_receipts')->cascadeOnDelete();
            
            // Bisa multi lokasi dalam 1 receipt
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            
            $table->decimal('qty', 15, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_goods_receipt_items');
    }
};