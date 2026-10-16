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
        if (Schema::hasTable('in_process_checksheets') && Schema::hasColumn('in_process_checksheets', 'alat_ukur')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE in_process_checksheets MODIFY COLUMN alat_ukur TEXT NULL');
        }

        if (Schema::hasTable('first_piece_approvals') && Schema::hasColumn('first_piece_approvals', 'alat_ukur')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE first_piece_approvals MODIFY COLUMN alat_ukur TEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('in_process_checksheets') && Schema::hasColumn('in_process_checksheets', 'alat_ukur')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE in_process_checksheets MODIFY COLUMN alat_ukur VARCHAR(255) NULL');
        }

        if (Schema::hasTable('first_piece_approvals') && Schema::hasColumn('first_piece_approvals', 'alat_ukur')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE first_piece_approvals MODIFY COLUMN alat_ukur VARCHAR(255) NULL');
        }
    }
};
