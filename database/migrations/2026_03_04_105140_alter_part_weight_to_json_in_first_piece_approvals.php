<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Convert part_weight from a single string value to a JSON array
     * to support per-cavity weight input (max 8 cavities).
     */
    public function up(): void
    {
        // Step 1: Migrate existing data — wrap old single string values into a JSON array
        DB::table('first_piece_approvals')
            ->whereNotNull('part_weight')
            ->where('part_weight', '!=', '')
            ->get(['id', 'part_weight'])
            ->each(function ($row) {
                // Only wrap if it's NOT already a JSON array
                $decoded = json_decode($row->part_weight, true);
                if (!is_array($decoded)) {
                    DB::table('first_piece_approvals')
                        ->where('id', $row->id)
                        ->update(['part_weight' => json_encode([$row->part_weight])]);
                }
            });

        // Step 2: Change column type to text (to hold JSON)
        Schema::table('first_piece_approvals', function (Blueprint $table) {
            $table->text('part_weight')->nullable()->change();
        });
    }

    /**
     * Reverse: convert back to string (take first cavity value).
     */
    public function down(): void
    {
        DB::table('first_piece_approvals')
            ->whereNotNull('part_weight')
            ->get(['id', 'part_weight'])
            ->each(function ($row) {
                $decoded = json_decode($row->part_weight, true);
                if (is_array($decoded)) {
                    DB::table('first_piece_approvals')
                        ->where('id', $row->id)
                        ->update(['part_weight' => $decoded[0] ?? null]);
                }
            });

        Schema::table('first_piece_approvals', function (Blueprint $table) {
            $table->string('part_weight')->nullable()->change();
        });
    }
};
