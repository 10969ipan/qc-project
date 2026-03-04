<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Drop the deprecated lokasi_penyimpanan column that causes validation errors on update.
     */
    public function up(): void
    {
        Schema::table('calibration_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('calibration_verifications', 'lokasi_penyimpanan')) {
                $table->dropColumn('lokasi_penyimpanan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calibration_verifications', function (Blueprint $table) {
            $table->string('lokasi_penyimpanan')->nullable()->after('acuan_toleransi');
        });
    }
};
