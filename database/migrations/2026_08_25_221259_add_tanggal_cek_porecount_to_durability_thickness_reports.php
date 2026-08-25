<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->date('tanggal_cek_porecount')->nullable()->after('tanggal_cek')
                ->comment('Tanggal aktual pelaksanaan Porecount Test (terpisah dari tanggal_cek Thickness)');
        });
    }

    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn('tanggal_cek_porecount');
        });
    }
};
