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
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->renameColumn('shift', 'production_shift');
            $table->string('qc_shift')->after('production_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->renameColumn('production_shift', 'shift');
            $table->dropColumn('qc_shift');
        });
    }
};
