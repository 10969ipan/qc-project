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
        $tables = [
            'checksheets',
            'in_process_checksheets',
            'cross_cut_checksheets',
            'cross_cut_painting_checksheets',
            'sortir_checksheets',
            'plating_checksheets',
            'painting_checksheets',
            'double_tape_checksheets',
            'first_piece_approvals',
            'incoming_parts',
            'incoming_materials',
            'incoming_chemicals',
            'incoming_sub_parts',
            'incoming_exports',
            'standard_performance_tests',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'approval_methods')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->json('approval_methods')->nullable()->after('updated_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'checksheets',
            'in_process_checksheets',
            'cross_cut_checksheets',
            'cross_cut_painting_checksheets',
            'sortir_checksheets',
            'plating_checksheets',
            'painting_checksheets',
            'double_tape_checksheets',
            'first_piece_approvals',
            'incoming_parts',
            'incoming_materials',
            'incoming_chemicals',
            'incoming_sub_parts',
            'incoming_exports',
            'standard_performance_tests',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'approval_methods')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('approval_methods');
                });
            }
        }
    }
};
