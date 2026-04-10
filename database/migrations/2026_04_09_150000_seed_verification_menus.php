<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parents = [
            ['name' => 'PLANT JAKARTA', 'plant_code' => 'jakarta'],
            ['name' => 'PLANT KARAWANG', 'plant_code' => 'karawang'],
        ];

        $roles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc', 'inspector', 'oshef'];

        foreach ($parents as $parentInfo) {
            $parentMenu = DB::table('app_menus')
                ->where('parent_id', 2) // Quality Control
                ->where('name', $parentInfo['name'])
                ->where('plant_code', $parentInfo['plant_code'])
                ->first();

            if (!$parentMenu) continue;

            // Get current max order to append
            $maxOrder = DB::table('app_menus')->where('parent_id', $parentMenu->id)->max('order') ?? 0;

            // 1. Insert Category: VERIFIKASI ALAT
            $verifyId = DB::table('app_menus')->insertGetId([
                'name' => 'VERIFIKASI ALAT',
                'icon' => 'fas fa-tools',
                'route' => '#',
                'parent_id' => $parentMenu->id,
                'order' => $maxOrder + 1,
                'is_active' => true,
                'plant_code' => $parentInfo['plant_code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add permissions for Category
            foreach ($roles as $role) {
                DB::table('role_permissions')->insert([
                    'menu_id' => $verifyId,
                    'role' => $role,
                    'can_view' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. Insert Submenus
            $submenus = [
                ['name' => 'Jadwal Verifikasi', 'route' => 'verifications.schedule.index', 'icon' => 'fas fa-calendar-alt'],
                ['name' => 'Master Data Alat', 'route' => 'verifications.tools.index', 'icon' => 'fas fa-database'],
                ['name' => 'Hasil Verifikasi', 'route' => 'verifications.verifications.index', 'icon' => 'fas fa-list-check'],
            ];

            foreach ($submenus as $index => $sub) {
                $subId = DB::table('app_menus')->insertGetId([
                    'name' => $sub['name'],
                    'icon' => $sub['icon'],
                    'route' => $sub['route'],
                    'parent_id' => $verifyId,
                    'order' => $index + 1,
                    'is_active' => true,
                    'plant_code' => $parentInfo['plant_code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Add permissions for Submenu
                foreach ($roles as $role) {
                    DB::table('role_permissions')->insert([
                        'menu_id' => $subId,
                        'role' => $role,
                        'can_view' => true,
                        'can_input' => true,
                        'can_edit' => true,
                        'can_delete' => $role === 'admin',
                        'can_export' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuNames = ['VERIFIKASI ALAT', 'Jadwal Verifikasi', 'Master Data Alat', 'Hasil Verifikasi'];
        
        $menuIds = DB::table('app_menus')
            ->whereIn('name', $menuNames)
            ->whereIn('plant_code', ['jakarta', 'karawang'])
            ->pluck('id');

        DB::table('role_permissions')->whereIn('menu_id', $menuIds)->delete();
        DB::table('app_menus')->whereIn('id', $menuIds)->delete();
    }
};
