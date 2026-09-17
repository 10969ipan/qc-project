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
        $roles = [
            'admin', 
            'manager', 
            'asst_manager', 
            'supervisor', 
            'kashift', 
            'inspector', 
            'karu_qc', 
            'kashift_plating', 
            'supervisor_plating', 
            'manager_plating'
        ];

        // Find Quality Control main menu
        $qcMenu = DB::table('app_menus')
            ->whereNull('parent_id')
            ->where('name', 'Quality Control')
            ->first();

        if (!$qcMenu) return;

        $plantParents = DB::table('app_menus')
            ->where('parent_id', $qcMenu->id)
            ->whereIn('plant_code', ['jakarta', 'karawang'])
            ->get();

        foreach ($plantParents as $parent) {
            // Clean up any old duplicate/legacy category menus
            $oldIds = DB::table('app_menus')
                ->where('parent_id', $parent->id)
                ->whereIn('name', ['VERIFIKASI ALAT', 'VERIFIKASI JIG', 'VERIFIKASI (JIG, MP & C/F)', 'Verifikasi (JIG, MP & C/F)'])
                ->pluck('id');
            if ($oldIds->isNotEmpty()) {
                DB::table('role_permissions')->whereIn('menu_id', $oldIds)->delete();
                DB::table('app_menus')->whereIn('id', $oldIds)->delete();
            }

            // Check if VERIFIKASI category menu exists under this plant parent
            $categoryMenu = DB::table('app_menus')
                ->where('parent_id', $parent->id)
                ->where('name', 'VERIFIKASI')
                ->first();

            if (!$categoryMenu) {
                $maxOrder = DB::table('app_menus')->where('parent_id', $parent->id)->max('order') ?? 0;
                $categoryId = DB::table('app_menus')->insertGetId([
                    'name' => 'VERIFIKASI',
                    'icon' => 'fas fa-tools',
                    'route' => '#',
                    'parent_id' => $parent->id,
                    'order' => $maxOrder + 1,
                    'is_active' => true,
                    'plant_code' => $parent->plant_code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $categoryId = $categoryMenu->id;
            }

            // Assign/Update permissions for Category Menu to all roles (default: only admin is active)
            foreach ($roles as $role) {
                $isAdmin = ($role === 'admin');
                DB::table('role_permissions')->updateOrInsert(
                    ['menu_id' => $categoryId, 'role' => $role],
                    [
                        'can_view' => $isAdmin,
                        'can_input' => $isAdmin,
                        'can_edit' => $isAdmin,
                        'can_delete' => $isAdmin,
                        'can_approve' => $isAdmin,
                        'can_export' => $isAdmin,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Submenus to insert
            $submenus = [
                ['name' => 'Jadwal Verifikasi', 'route' => 'verifications.schedule.index', 'icon' => 'fas fa-calendar-alt', 'order' => 1],
                ['name' => 'Hasil Verif', 'route' => 'verifications.verifications.index', 'icon' => 'fas fa-list-check', 'order' => 2],
                ['name' => 'Daftar Alat', 'route' => 'verifications.tools.index', 'icon' => 'fas fa-database', 'order' => 3],
            ];

            foreach ($submenus as $sub) {
                $subItem = DB::table('app_menus')
                    ->where('parent_id', $categoryId)
                    ->where('route', $sub['route'])
                    ->first();

                if (!$subItem) {
                    $subId = DB::table('app_menus')->insertGetId([
                        'name' => $sub['name'],
                        'icon' => $sub['icon'],
                        'route' => $sub['route'],
                        'parent_id' => $categoryId,
                        'order' => $sub['order'],
                        'is_active' => true,
                        'plant_code' => $parent->plant_code,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $subId = $subItem->id;
                }

                // Assign/Update permissions for Submenu to all roles (default: only admin is active)
                foreach ($roles as $role) {
                    $isAdmin = ($role === 'admin');
                    DB::table('role_permissions')->updateOrInsert(
                        ['menu_id' => $subId, 'role' => $role],
                        [
                            'can_view' => $isAdmin,
                            'can_input' => $isAdmin,
                            'can_edit' => $isAdmin,
                            'can_delete' => $isAdmin,
                            'can_approve' => $isAdmin,
                            'can_export' => $isAdmin,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $routes = [
            'verifications.schedule.index',
            'verifications.verifications.index',
            'verifications.tools.index',
        ];

        $subItemIds = DB::table('app_menus')->whereIn('route', $routes)->pluck('id');
        DB::table('role_permissions')->whereIn('menu_id', $subItemIds)->delete();
        DB::table('app_menus')->whereIn('id', $subItemIds)->delete();

        $categoryIds = DB::table('app_menus')->whereIn('name', ['VERIFIKASI', 'VERIFIKASI ALAT', 'VERIFIKASI JIG'])->pluck('id');
        DB::table('role_permissions')->whereIn('menu_id', $categoryIds)->delete();
        DB::table('app_menus')->whereIn('id', $categoryIds)->delete();

        Cache::flush();
    }
};
