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
        $tables = ['in_process_checksheets', 'first_piece_approvals'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'leader_dimension_check')) {
                        $table->longText('leader_dimension_check')->nullable()->after('dimension_check');
                    }
                    if (!Schema::hasColumn($tableName, 'leader_dimension_user_id')) {
                        $table->unsignedBigInteger('leader_dimension_user_id')->nullable()->after('leader_dimension_check');
                    }
                    if (!Schema::hasColumn($tableName, 'leader_dimension_at')) {
                        $table->timestamp('leader_dimension_at')->nullable()->after('leader_dimension_user_id');
                    }
                    if (!Schema::hasColumn($tableName, 'leader_alat_ukur')) {
                        $table->text('leader_alat_ukur')->nullable()->after('leader_dimension_at');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['in_process_checksheets', 'first_piece_approvals'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $columnsToDrop = [];
                    foreach (['leader_alat_ukur', 'leader_dimension_at', 'leader_dimension_user_id', 'leader_dimension_check'] as $col) {
                        if (Schema::hasColumn($tableName, $col)) {
                            $columnsToDrop[] = $col;
                        }
                    }
                    if (!empty($columnsToDrop)) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }
        }
    }
};
