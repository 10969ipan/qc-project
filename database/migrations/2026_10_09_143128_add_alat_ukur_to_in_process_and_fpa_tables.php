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
        if (Schema::hasTable('in_process_checksheets') && !Schema::hasColumn('in_process_checksheets', 'alat_ukur')) {
            Schema::table('in_process_checksheets', function (Blueprint $table) {
                $table->string('alat_ukur')->nullable()->after('dimension_check');
            });
        }

        if (Schema::hasTable('first_piece_approvals') && !Schema::hasColumn('first_piece_approvals', 'alat_ukur')) {
            Schema::table('first_piece_approvals', function (Blueprint $table) {
                $table->string('alat_ukur')->nullable()->after('dimension_check');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('in_process_checksheets') && Schema::hasColumn('in_process_checksheets', 'alat_ukur')) {
            Schema::table('in_process_checksheets', function (Blueprint $table) {
                $table->dropColumn('alat_ukur');
            });
        }

        if (Schema::hasTable('first_piece_approvals') && Schema::hasColumn('first_piece_approvals', 'alat_ukur')) {
            Schema::table('first_piece_approvals', function (Blueprint $table) {
                $table->dropColumn('alat_ukur');
            });
        }
    }
};
