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
            $table->string('actual_corrodkote_waktu')->nullable()->after('actual_cr');
            $table->string('actual_cass_waktu')->nullable()->after('actual_corrodkote');
            $table->string('actual_salt_spray_waktu')->nullable()->after('actual_cass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn(['actual_corrodkote_waktu', 'actual_cass_waktu', 'actual_salt_spray_waktu']);
        });
    }
};
