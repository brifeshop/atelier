<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // Kode bahan (untuk referensi internal)
            $table->string('kode_bahan')->nullable()->after('kode')
                ->comment('Kode internal: A8-18-0, J1-9-0, dll');
            
            // Spesifikasi teknis (free text)
            $table->text('spesifikasi')->nullable()->after('nama')
                ->comment('Deskripsi detail: MDF 8mm single side melamin');

            // Dimensi standar (satuan beli)
            $table->decimal('panjang_standar', 15, 4)->nullable()->after('unit')
                ->comment('Panjang satuan beli (mm)');
            $table->decimal('lebar_standar', 15, 4)->nullable()->after('panjang_standar')
                ->comment('Lebar satuan beli (mm)');
            $table->decimal('tinggi_standar', 15, 4)->nullable()->after('lebar_standar')
                ->comment('Tinggi/tebal satuan beli (mm)');
            $table->decimal('berat_standar', 15, 4)->nullable()->after('tinggi_standar')
                ->comment('Berat satuan beli (gram)');
            $table->decimal('volume_standar', 15, 4)->nullable()->after('berat_standar')
                ->comment('Volume satuan beli (ml)');

            // Konfigurasi costing
            $table->string('costing_method')->default('per_unit')->after('volume_standar')
                ->comment('per_unit, per_area, per_volume, per_length, per_weight');
            $table->string('base_unit')->nullable()->after('costing_method')
                ->comment('Satuan dasar kalkulasi: pcs, mm2, mm3, mm, gram, ml');
            $table->decimal('yield_percent', 5, 2)->default(100)->after('base_unit')
                ->comment('Efisiensi/waste % (default 100 = tanpa waste)');

            // Index untuk filter
            $table->index('costing_method');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex(['costing_method']);
            $table->dropColumn([
                'kode_bahan',
                'spesifikasi',
                'panjang_standar',
                'lebar_standar',
                'tinggi_standar',
                'berat_standar',
                'volume_standar',
                'costing_method',
                'base_unit',
                'yield_percent',
            ]);
        });
    }
};