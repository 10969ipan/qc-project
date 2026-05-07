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
        // Remove 'Problem Log' from app_menus
        \DB::table('app_menus')->where('name', 'Problem Log')
            ->where('route', 'calibration.tools.problem-logs')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add if rolled back (Optional, but good practice)
        $parentId = \DB::table('app_menus')->where('name', 'KALIBRASI')->value('id');
        
        if ($parentId) {
            \DB::table('app_menus')->insert([
                'name' => 'Problem Log',
                'route' => 'calibration.tools.problem-logs',
                'parent_id' => $parentId,
                'order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
