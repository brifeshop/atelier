<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_type');  // 'material' / 'product'
            $table->unsignedBigInteger('item_id');
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('min_stock', 15, 2)->nullable();
            $table->decimal('max_stock', 15, 2)->nullable();
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            // Satu item hanya boleh ada 1 record per lokasi
            $table->unique(['item_type', 'item_id', 'location_id']);
            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};