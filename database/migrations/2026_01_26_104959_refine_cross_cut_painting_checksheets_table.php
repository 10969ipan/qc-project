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
            $table->dropColumn(['position_remark_no_lot', 'result_remark']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->string('position_remark_no_lot')->nullable();
            $table->string('result_remark')->nullable();
        });
    }
};
