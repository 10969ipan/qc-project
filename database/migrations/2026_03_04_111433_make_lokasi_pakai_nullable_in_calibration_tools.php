<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Make lokasi_pakai nullable in calibration_tools.
     * The column is no longer submitted from the form / controller,
     * but the DB still requires a value. Making it nullable fixes the error.
     */
    public function up(): void
    {
        Schema::table('calibration_tools', function (Blueprint $table) {
            $table->string('lokasi_pakai')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('calibration_tools', function (Blueprint $table) {
            $table->string('lokasi_pakai')->nullable(false)->change();
        });
    }
};
