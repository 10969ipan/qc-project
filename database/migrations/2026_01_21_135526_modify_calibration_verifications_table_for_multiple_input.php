<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        Schema::table('calibration_verifications', function (Blueprint $table) {
            $table->text('nilai_alat')->change();
            $table->text('nilai_koreksi')->change();
            $table->text('nilai_ketidakpastian')->change();
            $table->text('hasil_verifikasi')->change();
        });
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calibration_verifications', function (Blueprint $table) {
            $table->decimal('nilai_alat', 12, 4)->change();
            $table->decimal('nilai_koreksi', 12, 4)->change();
            $table->decimal('nilai_ketidakpastian', 12, 4)->change();
            $table->string('hasil_verifikasi')->change();
        });
    }
};
