<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi buat nambahin kolom verifikasi harian Leader & mingguan SPV.
     */
    public function up(): void
    {
        Schema::table('operator_compliance_checksheets', function (Blueprint $table) {
            $table->json('leader_checks')->nullable()->after('year'); // Ceklis harian Leader/Kashift {day: {checked, user_id, user_name, time}}
            $table->json('spv_checks')->nullable()->after('leader_checks'); // Ceklis mingguan SPV/Karu {week: {checked, user_id, user_name, time}}
        });
    }

    /**
     * Batalkan migrasi (hapus kolom JSON verifikasi).
     */
    public function down(): void
    {
        Schema::table('operator_compliance_checksheets', function (Blueprint $table) {
            $table->dropColumn(['leader_checks', 'spv_checks']);
        });
    }
};
