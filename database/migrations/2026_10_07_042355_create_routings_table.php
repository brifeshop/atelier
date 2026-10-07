<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routings', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('version')->default('1.0');
            $table->date('effective_date');
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();

            // Total cost per unit produksi
            $table->decimal('total_labor_cost', 15, 2)->default(0);
            $table->decimal('total_overhead_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'version']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routings');
    }
};