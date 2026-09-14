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
        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->index(['plant_id', 'production_datetime'], 'idx_crosscut_pnt_plant_prod_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->dropIndex('idx_crosscut_pnt_plant_prod_dt');
        });
    }
};
