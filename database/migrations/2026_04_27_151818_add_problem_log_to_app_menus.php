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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('app_menus')->where('name', 'Problem Log')
            ->where('route', 'calibration.tools.problem-logs')
            ->delete();
    }
};
