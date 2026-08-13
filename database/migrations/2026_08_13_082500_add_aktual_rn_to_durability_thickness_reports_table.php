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
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('durability_thickness_reports', 'aktual_rn')) {
                $table->string('aktual_rn')->nullable()->after('standar_jam_cass');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            if (Schema::hasColumn('durability_thickness_reports', 'aktual_rn')) {
                $table->dropColumn('aktual_rn');
            }
        });
    }
};
