<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'incoming_parts',
            'in_process_checksheets',
            'sub_assy_checksheets',
            'plating_checksheets',
            'double_tape_checksheets',
            'painting_checksheets',
            'incoming_exports',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'unique_code_id')) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        $table->index(['unique_code_id', 'sap_code'], $tableName . '_unique_sap_idx');
                    });
                } catch (\Throwable $e) {
                    // Ignore if index already exists
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'incoming_parts',
            'in_process_checksheets',
            'sub_assy_checksheets',
            'plating_checksheets',
            'double_tape_checksheets',
            'painting_checksheets',
            'incoming_exports',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'unique_code_id')) {
                try {
                    Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                        $table->dropIndex($tableName . '_unique_sap_idx');
                    });
                } catch (\Throwable $e) {
                    // Ignore if index not present
                }
            }
        }
    }
};
