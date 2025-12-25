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
        // Define tables to modify
        $tables = ['checksheets', 'in_process_checksheets', 'cross_cut_checksheets'];

        foreach ($tables as $tableName) {
            // Ensure the table exists before attempting to modify it
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'kashift_approved_at')) {
                    // Place after the QC name column if it exists, otherwise just add it
                    $afterColumn = Schema::hasColumn($tableName, 'kashift_qc') ? 'kashift_qc' : 'remarks';
                    if (!Schema::hasColumn($tableName, $afterColumn)) { // Fallback if remarks don't exist
                        $table->timestamp('kashift_approved_at')->nullable();
                    } else {
                        $table->timestamp('kashift_approved_at')->nullable()->after($afterColumn);
                    }
                }
                if (!Schema::hasColumn($tableName, 'supervisor_approved_at')) {
                    $afterColumn = Schema::hasColumn($tableName, 'supervisor_qc') ? 'supervisor_qc' : 'kashift_approved_at';
                     if (!Schema::hasColumn($tableName, $afterColumn)) {
                        $table->timestamp('supervisor_approved_at')->nullable();
                    } else {
                        $table->timestamp('supervisor_approved_at')->nullable()->after($afterColumn);
                    }
                }
                if (!Schema::hasColumn($tableName, 'asst_manager_approved_at')) {
                    $afterColumn = Schema::hasColumn($tableName, 'asst_manager_qc') ? 'asst_manager_qc' : 'supervisor_approved_at';
                    if (!Schema::hasColumn($tableName, $afterColumn)) {
                        $table->timestamp('asst_manager_approved_at')->nullable();
                    } else {
                        $table->timestamp('asst_manager_approved_at')->nullable()->after($afterColumn);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['checksheets', 'in_process_checksheets', 'cross_cut_checksheets'];
        $columns = ['kashift_approved_at', 'supervisor_approved_at', 'asst_manager_approved_at'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                // Drop only the columns that actually exist in the table
                $existingColumns = array_filter($columns, function($column) use ($tableName) {
                    return Schema::hasColumn($tableName, $column);
                });

                if (!empty($existingColumns)) {
                    Schema::table($tableName, function (Blueprint $table) use ($existingColumns) {
                        $table->dropColumn($existingColumns);
                    });
                }
            }
        }
    }
};
