<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number')->unique();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->date('opname_date');
            $table->string('status')->default('draft'); // draft, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['location_id', 'opname_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};