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
        // Delete ANALYSIS menus from app_menus (cascades to children and role/user permissions)
        $analysisMenuIds = DB::table('app_menus')
            ->where('name', 'ANALYSIS')
            ->orWhere('name', 'like', '%Anls%')
            ->orWhere('route', 'like', 'analysis.%')
            ->pluck('id');

        if ($analysisMenuIds->isNotEmpty()) {
            DB::table('app_menus')->whereIn('id', $analysisMenuIds)->delete();
        }

        // Clear topbar and application menu caches
        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dead code removal - down migration not required
    }
};
