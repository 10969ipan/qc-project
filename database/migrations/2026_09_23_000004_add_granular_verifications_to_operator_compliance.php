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
        Schema::table('operator_compliance_checksheets', function (Blueprint $table) {
            $table->json('leader_checks')->nullable()->after('year'); // Daily Leader/Kashift checks {day: {checked, user_id, user_name, time}}
            $table->json('spv_checks')->nullable()->after('leader_checks'); // Weekly SPV checks {week: {checked, user_id, user_name, time}}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_compliance_checksheets', function (Blueprint $table) {
            $table->dropColumn(['leader_checks', 'spv_checks']);
        });
    }
};
