<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('app_menus')
            ->where('name', 'Incoming Export')
            ->update(['name' => 'Outgoing Export']);

        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('app_menus')
            ->where('name', 'Outgoing Export')
            ->update(['name' => 'Incoming Export']);

        Cache::flush();
    }
};
