<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users' => 'plant',
            'sub_assy_checksheets' => 'plant',
            'in_process_checksheets' => 'plant',
            'cross_cut_checksheets' => 'plant',
            'sortir_checksheets' => 'plant',
        ];

        $plants = DB::table('plants')->get()->pluck('id', 'code');

        foreach ($tables as $table => $oldColumn) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignUuid('plant_id_new')->nullable()->after('plant')->constrained('plants')->restrictOnDelete();
            });

            $data = DB::table($table)->get();
            foreach ($data as $row) {
                $plantCode = strtolower($row->$oldColumn);
                $plantId = $plants[$plantCode] ?? null;

                if ($plantId) {
                    DB::table($table)->where('id', $row->id)->update(['plant_id_new' => $plantId]);
                }
            }

            // Optional: make it non-nullable if needed later, but some might be null (like admin)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'sub_assy_checksheets', 'in_process_checksheets', 'cross_cut_checksheets', 'sortir_checksheets'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['plant_id_new']);
                $table->dropColumn('plant_id_new');
            });
        }
    }
};
