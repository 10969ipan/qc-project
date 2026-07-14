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
            $table->string('line')->nullable()->after('plant_id');
        });

        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->string('line')->nullable()->after('plant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->dropColumn('line');
        });

        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->dropColumn('line');
        });
    }
};
