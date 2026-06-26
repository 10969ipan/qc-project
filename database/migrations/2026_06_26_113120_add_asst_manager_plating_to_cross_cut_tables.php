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
            $table->string('asst_manager_plating')->nullable()->after('supervisor_plating_approved_at');
            $table->timestamp('asst_manager_plating_approved_at')->nullable()->after('asst_manager_plating');
        });

        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->string('asst_manager_plating')->nullable()->after('supervisor_plating_approved_at');
            $table->timestamp('asst_manager_plating_approved_at')->nullable()->after('asst_manager_plating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_cut_checksheets', function (Blueprint $table) {
            $table->dropColumn(['asst_manager_plating', 'asst_manager_plating_approved_at']);
        });

        Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
            $table->dropColumn(['asst_manager_plating', 'asst_manager_plating_approved_at']);
        });
    }
};
