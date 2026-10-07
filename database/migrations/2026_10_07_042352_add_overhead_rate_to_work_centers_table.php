<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            $table->decimal('overhead_rate', 15, 2)->default(0)->after('hourly_rate');
            $table->decimal('setup_time_default', 8, 2)->default(0)->after('capacity_per_hour')
                ->comment('Waktu setup default dalam menit');
        });
    }

    public function down(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            $table->dropColumn(['overhead_rate', 'setup_time_default']);
        });
    }
};