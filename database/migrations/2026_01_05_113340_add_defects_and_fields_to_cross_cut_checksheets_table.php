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
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            // Add fields to support defect tracking like other checksheets
            $table->json('defects')->nullable()->after('position_remark_judgment');
            $table->integer('total_ng')->default(0)->after('defects');
            $table->integer('sampling_qty')->default(0)->after('total_ng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->dropColumn(['defects', 'total_ng', 'sampling_qty']);
        });
    }
};
