<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('skill_level')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('join_date')->nullable();
            $table->decimal('hourly_rate', 15, 2)->default(0);
            $table->decimal('daily_rate', 15, 2)->nullable();
            $table->decimal('monthly_salary', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};