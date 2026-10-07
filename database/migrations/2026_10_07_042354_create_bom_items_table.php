<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(1);

            // Polymorphic: material atau product (untuk multi-level)
            $table->string('item_type'); // 'material' atau 'product'
            $table->unsignedBigInteger('item_id');

            $table->decimal('qty', 15, 4);                    // presisi 4 desimal
            $table->string('unit');                            // pcs, kg, m, dll
            $table->decimal('scrap_percent', 5, 2)->default(0);

            // Snapshot harga saat BOM dibuat
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
            $table->index(['bom_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};