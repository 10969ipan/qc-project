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
            $table->string('actual_corrodkote')->nullable()->after('actual_cr');
            $table->string('actual_cass')->nullable()->after('actual_corrodkote');
            $table->string('actual_salt_spray')->nullable()->after('actual_cass');
            $table->string('actual_porecount')->nullable()->after('actual_salt_spray');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('durability_thickness_reports', function (Blueprint $table) {
            $table->dropColumn(['actual_corrodkote', 'actual_cass', 'actual_salt_spray', 'actual_porecount']);
        });
    }
};
