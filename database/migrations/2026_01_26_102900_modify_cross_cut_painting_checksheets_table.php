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
        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->dropColumn(['chemical_copper', 'chemical_nikel', 'chemical_eching', 'chemical_abu']);
            $table->string('pencil_scratch')->nullable()->after('qc_datetime');
            $table->string('tap_test')->nullable()->after('pencil_scratch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->string('chemical_copper')->nullable();
            $table->string('chemical_nikel')->nullable();
            $table->string('chemical_eching')->nullable();
            $table->string('chemical_abu')->nullable();
            $table->dropColumn(['pencil_scratch', 'tap_test']);
        });
    }
};
