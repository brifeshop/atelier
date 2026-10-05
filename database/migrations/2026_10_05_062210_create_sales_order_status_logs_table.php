<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->string('reference_type')->nullable();  // Model class
            $table->unsignedBigInteger('reference_id')->nullable();  // Model ID
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['sales_order_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_status_logs');
    }
};