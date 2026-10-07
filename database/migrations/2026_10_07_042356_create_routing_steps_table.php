<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routing_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routing_id')->constrained('routings')->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->foreignId('work_center_id')->constrained('work_centers')->restrictOnDelete();
            $table->string('operation_name'); // "Potong", "Rakit", dst

            // Waktu
            $table->decimal('setup_time_minutes', 8, 2)->default(0);        // sekali per batch
            $table->decimal('run_time_per_unit_minutes', 8, 2)->default(0); // per unit

            // Snapshot rate saat routing dibuat
            $table->decimal('labor_rate', 15, 2)->default(0);
            $table->decimal('overhead_rate', 15, 2)->default(0);

            // Cost
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('overhead_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['routing_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_steps');
    }
};