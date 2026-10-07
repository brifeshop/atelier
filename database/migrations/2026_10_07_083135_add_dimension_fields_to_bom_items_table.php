<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_items', function (Blueprint $table) {
            // Spesifikasi & hierarki
            $table->text('spesifikasi')->nullable()->after('unit')
                ->comment('164 x 120 x 18 mm');
            $table->string('divisi')->nullable()->after('spesifikasi')
                ->comment('Kayu, Offset Printing, Set Up, dll');
            $table->string('level')->nullable()->after('divisi')
                ->comment('L.1, L.2, L.3 untuk hierarki');

            // Dimensi pakai (satuan pakai)
            $table->decimal('panjang_pakai', 15, 4)->nullable()->after('level')
                ->comment('Panjang pakai (mm)');
            $table->decimal('lebar_pakai', 15, 4)->nullable()->after('panjang_pakai')
                ->comment('Lebar pakai (mm)');
            $table->decimal('tinggi_pakai', 15, 4)->nullable()->after('lebar_pakai')
                ->comment('Tinggi/tebal pakai (mm)');
            $table->decimal('berat_pakai', 15, 4)->nullable()->after('tinggi_pakai')
                ->comment('Berat pakai (gram)');
            $table->decimal('volume_pakai', 15, 4)->nullable()->after('berat_pakai')
                ->comment('Volume pakai (ml)');

            // Index
            $table->index('divisi');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->dropIndex(['divisi']);
            $table->dropIndex(['level']);
            $table->dropColumn([
                'spesifikasi',
                'divisi',
                'level',
                'panjang_pakai',
                'lebar_pakai',
                'tinggi_pakai',
                'berat_pakai',
                'volume_pakai',
            ]);
        });
    }
};