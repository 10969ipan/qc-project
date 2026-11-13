<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update data checksheet yang jabatan-nya Performance Test
        DB::table('dandory_checksheets')
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(jabatan)) LIKE ?', ['%performance test%'])
                    ->orWhereRaw('LOWER(TRIM(jabatan)) LIKE ?', ['%perfoment test%']);
            })
            ->where('table_no', '!=', 'Ruangan Durability Test')
            ->update(['table_no' => 'Ruangan Durability Test']);

        // Update data checksheet lama yang jabatan-nya kosong namun operator dikonfigurasi sebagai Performance Test
        $perfConfigs = DB::table('dandory_operator_configs')
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(bagian)) LIKE ?', ['%performance test%'])
                    ->orWhereRaw('LOWER(TRIM(bagian)) LIKE ?', ['%perfoment test%']);
            })
            ->get(['plant_code', 'operator_id']);

        foreach ($perfConfigs as $cfg) {
            DB::table('dandory_checksheets')
                ->where('plant_code', $cfg->plant_code)
                ->where('operator_id', $cfg->operator_id)
                ->where(function ($query) {
                    $query->whereNull('jabatan')
                        ->orWhere('jabatan', '');
                })
                ->where('table_no', '!=', 'Ruangan Durability Test')
                ->update(['table_no' => 'Ruangan Durability Test']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data migration cannot be reliably reverted to arbitrary prior table names.
    }
};

