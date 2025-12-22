<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns to checksheets table
        Schema::table('checksheets', function (Blueprint $table) {
            $table->string('manager_qc')->nullable()->after('asst_manager_approved_at');
            $table->timestamp('manager_approved_at')->nullable()->after('manager_qc');
        });

        // Add columns to in_process_checksheets table
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->string('manager_qc')->nullable()->after('asst_manager_approved_at');
            $table->timestamp('manager_approved_at')->nullable()->after('manager_qc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheets', function (Blueprint $table) {
            $table->dropColumn(['manager_qc', 'manager_approved_at']);
        });

        Schema::table('in_process_checksheets', function (Blueprint $table) {
            $table->dropColumn(['manager_qc', 'manager_approved_at']);
        });
    }
};
