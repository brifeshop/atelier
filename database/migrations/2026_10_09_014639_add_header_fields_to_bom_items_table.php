<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->boolean('is_header')->default(false)->after('level')
                ->comment('Header group (bukan item)');
            $table->string('header_label')->nullable()->after('is_header')
                ->comment('Nama group (untuk header)');
        });
    }

    public function down(): void
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->dropColumn(['is_header', 'header_label']);
        });
    }
};