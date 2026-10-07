<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('adjustment_reason')->nullable()->after('notes');
            // 'damaged', 'lost', 'expired', 'correction', 'found', 'opname', 'other'
            $table->foreignId('stock_opname_id')->nullable()->after('reference_id')
                ->constrained('stock_opnames')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['stock_opname_id']);
            $table->dropColumn(['adjustment_reason', 'stock_opname_id']);
        });
    }
};