<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->date('tgl_masuk')->nullable()->after('lot_no');
            $table->time('jam_masuk')->nullable()->after('tgl_masuk');
            $table->date('tgl_keluar')->nullable()->after('jam_masuk');
            $table->time('jam_keluar')->nullable()->after('tgl_keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn(['tgl_masuk', 'jam_masuk', 'tgl_keluar', 'jam_keluar']);
        });
    }
};
