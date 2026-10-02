<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('type')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->integer('useful_life_years')->default(5);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->decimal('power_kw', 10, 2)->default(0);
            $table->decimal('capacity_per_hour', 15, 2)->nullable();
            $table->decimal('maintenance_cost_per_month', 15, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('Active'); // Active, Maintenance, Broken
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};