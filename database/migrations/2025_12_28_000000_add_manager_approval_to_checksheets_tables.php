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
        // Add manager approval columns to the 'checksheets' table
        Schema::table('checksheets', function (Blueprint $table) {
            if (!Schema::hasColumn('checksheets', 'manager_qc')) {
                $table->string('manager_qc')->nullable()->after('asst_manager_qc');
            }
            if (!Schema::hasColumn('checksheets', 'manager_approved_at')) {
                $table->timestamp('manager_approved_at')->nullable()->after('manager_qc');
            }
        });

        // Add manager approval columns to the 'in_process_checksheets' table
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            if (!Schema::hasColumn('in_process_checksheets', 'manager_qc')) {
                $table->string('manager_qc')->nullable()->after('asst_manager_qc');
            }
            if (!Schema::hasColumn('in_process_checksheets', 'manager_approved_at')) {
                $table->timestamp('manager_approved_at')->nullable()->after('manager_qc');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop manager approval columns from the 'checksheets' table
        Schema::table('checksheets', function (Blueprint $table) {
            if (Schema::hasColumn('checksheets', 'manager_qc')) {
                $table->dropColumn('manager_qc');
            }
            if (Schema::hasColumn('checksheets', 'manager_approved_at')) {
                $table->dropColumn('manager_approved_at');
            }
        });

        // Drop manager approval columns from the 'in_process_checksheets' table
        Schema::table('in_process_checksheets', function (Blueprint $table) {
            if (Schema::hasColumn('in_process_checksheets', 'manager_qc')) {
                $table->dropColumn('manager_qc');
            }
            if (Schema::hasColumn('in_process_checksheets', 'manager_approved_at')) {
                $table->dropColumn('manager_approved_at');
            }
        });
    }
};
