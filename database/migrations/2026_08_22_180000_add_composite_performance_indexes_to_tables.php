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
        // 1. sub_assy_checksheets
        if (Schema::hasTable('sub_assy_checksheets')) {
            try {
                Schema::table('sub_assy_checksheets', function (Blueprint $table) {
                    $table->index(['plant_id', 'date', 'shift'], 'idx_subassy_plant_date_shift');
                    $table->index(['plant_id', 'created_at'], 'idx_subassy_plant_created');
                });
            } catch (\Throwable $e) {}
        }

        // 2. in_process_checksheets
        if (Schema::hasTable('in_process_checksheets')) {
            try {
                Schema::table('in_process_checksheets', function (Blueprint $table) {
                    $table->index(['plant_id', 'date', 'shift'], 'idx_inproc_plant_date_shift');
                    $table->index(['plant_id', 'code_machine'], 'idx_inproc_plant_machine');
                });
            } catch (\Throwable $e) {}
        }

        // 3. first_piece_approvals
        if (Schema::hasTable('first_piece_approvals')) {
            try {
                Schema::table('first_piece_approvals', function (Blueprint $table) {
                    $table->index(['plant_id', 'date', 'shift'], 'idx_fpa_plant_date_shift');
                    $table->index(['plant_id', 'code_machine'], 'idx_fpa_plant_machine');
                });
            } catch (\Throwable $e) {}
        }

        // 4. plating_checksheets
        if (Schema::hasTable('plating_checksheets')) {
            try {
                Schema::table('plating_checksheets', function (Blueprint $table) {
                    $table->index(['plant_id', 'date', 'shift'], 'idx_plating_plant_date_shift');
                    $table->index(['plant_id', 'line'], 'idx_plating_plant_line');
                });
            } catch (\Throwable $e) {}
        }

        // 5. painting_checksheets
        if (Schema::hasTable('painting_checksheets')) {
            try {
                Schema::table('painting_checksheets', function (Blueprint $table) {
                    $table->index(['plant_id', 'date', 'shift'], 'idx_painting_plant_date_shift');
                    $table->index(['plant_id', 'line'], 'idx_painting_plant_line');
                });
            } catch (\Throwable $e) {}
        }

        // 6. cross_cut_checksheets
        if (Schema::hasTable('cross_cut_checksheets')) {
            try {
                Schema::table('cross_cut_checksheets', function (Blueprint $table) {
                    $table->index(['plant_id', 'qc_datetime', 'qc_shift'], 'idx_crosscut_plant_dt_shift');
                    $table->index(['plant_id', 'line'], 'idx_crosscut_plant_line');
                });
            } catch (\Throwable $e) {}
        }

        // 7. cross_cut_painting_checksheets
        if (Schema::hasTable('cross_cut_painting_checksheets')) {
            try {
                Schema::table('cross_cut_painting_checksheets', function (Blueprint $table) {
                    $table->index(['plant_id', 'qc_datetime', 'qc_shift'], 'idx_crosscut_pnt_plant_dt_shift');
                });
            } catch (\Throwable $e) {}
        }

        // 8. sortir_checksheets
        if (Schema::hasTable('sortir_checksheets')) {
            try {
                Schema::table('sortir_checksheets', function (Blueprint $table) {
                    $table->index(['plant_id', 'date', 'shift', 'source_type'], 'idx_sortir_plant_date_shift_src');
                });
            } catch (\Throwable $e) {}
        }

        // 9. double_tape_checksheets
        if (Schema::hasTable('double_tape_checksheets')) {
            try {
                Schema::table('double_tape_checksheets', function (Blueprint $table) {
                    $table->index(['plant_id', 'date', 'shift'], 'idx_doubletape_plant_date_shift');
                });
            } catch (\Throwable $e) {}
        }

        // 10. notifications
        if (Schema::hasTable('notifications')) {
            try {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->index(['user_id', 'is_read', 'type'], 'idx_notif_user_read_type');
                });
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'sub_assy_checksheets' => ['idx_subassy_plant_date_shift', 'idx_subassy_plant_created'],
            'in_process_checksheets' => ['idx_inproc_plant_date_shift', 'idx_inproc_plant_machine'],
            'first_piece_approvals' => ['idx_fpa_plant_date_shift', 'idx_fpa_plant_machine'],
            'plating_checksheets' => ['idx_plating_plant_date_shift', 'idx_plating_plant_line'],
            'painting_checksheets' => ['idx_painting_plant_date_shift', 'idx_painting_plant_line'],
            'cross_cut_checksheets' => ['idx_crosscut_plant_dt_shift', 'idx_crosscut_plant_line'],
            'cross_cut_painting_checksheets' => ['idx_crosscut_pnt_plant_dt_shift'],
            'sortir_checksheets' => ['idx_sortir_plant_date_shift_src'],
            'double_tape_checksheets' => ['idx_doubletape_plant_date_shift'],
            'notifications' => ['idx_notif_user_read_type'],
        ];

        foreach ($tables as $tableName => $indexes) {
            if (Schema::hasTable($tableName)) {
                foreach ($indexes as $indexName) {
                    try {
                        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                            $table->dropIndex($indexName);
                        });
                    } catch (\Throwable $e) {}
                }
            }
        }
    }
};
