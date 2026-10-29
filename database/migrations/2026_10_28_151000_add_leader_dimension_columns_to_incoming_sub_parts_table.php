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
        if (Schema::hasTable('incoming_sub_parts')) {
            Schema::table('incoming_sub_parts', function (Blueprint $table) {
                if (!Schema::hasColumn('incoming_sub_parts', 'leader_dimension_check')) {
                    $table->longText('leader_dimension_check')->nullable()->after('check_dimensi');
                }
                if (!Schema::hasColumn('incoming_sub_parts', 'leader_dimension_user_id')) {
                    $table->unsignedBigInteger('leader_dimension_user_id')->nullable()->after('leader_dimension_check');
                }
                if (!Schema::hasColumn('incoming_sub_parts', 'leader_dimension_at')) {
                    $table->timestamp('leader_dimension_at')->nullable()->after('leader_dimension_user_id');
                }
                if (!Schema::hasColumn('incoming_sub_parts', 'leader_alat_ukur')) {
                    $table->text('leader_alat_ukur')->nullable()->after('leader_dimension_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incoming_sub_parts')) {
            Schema::table('incoming_sub_parts', function (Blueprint $table) {
                $columnsToDrop = [];
                foreach (['leader_alat_ukur', 'leader_dimension_at', 'leader_dimension_user_id', 'leader_dimension_check'] as $col) {
                    if (Schema::hasColumn('incoming_sub_parts', $col)) {
                        $columnsToDrop[] = $col;
                    }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
