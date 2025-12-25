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
        $tables = ['checksheets', 'in_process_checksheets', 'cross_cut_checksheets'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'manager_qc')) {
                    $afterColumn = Schema::hasColumn($tableName, 'asst_manager_qc') ? 'asst_manager_qc' : 'id';
                    $table->string('manager_qc')->nullable()->after($afterColumn);
                }
                if (!Schema::hasColumn($tableName, 'manager_approved_at')) {
                    $table->timestamp('manager_approved_at')->nullable()->after('manager_qc');
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

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columnsToDrop = [];
                if (Schema::hasColumn($tableName, 'manager_qc')) {
                    $columnsToDrop[] = 'manager_qc';
                }
                if (Schema::hasColumn($tableName, 'manager_approved_at')) {
                    $columnsToDrop[] = 'manager_approved_at';
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
