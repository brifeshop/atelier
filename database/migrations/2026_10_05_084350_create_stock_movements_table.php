<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number')->unique();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('type'); // in, out, transfer, adjustment
            $table->decimal('qty', 15, 2); // positif/negatif
            $table->decimal('qty_before', 15, 2);
            $table->decimal('qty_after', 15, 2);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('total_value', 15, 2)->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
            $table->index(['location_id', 'date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};