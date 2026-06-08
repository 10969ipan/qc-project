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
        $qcMenu = DB::table('app_menus')->whereNull('parent_id')->where('name', 'Quality Control')->first();
        if (!$qcMenu) {
            return;
        }

        $karawangMenu = DB::table('app_menus')
            ->where('parent_id', $qcMenu->id)
            ->where('name', 'PLANT KARAWANG')
            ->first();

        if (!$karawangMenu) {
            return;
        }

        $checksheetMenu = DB::table('app_menus')
            ->where('parent_id', $karawangMenu->id)
            ->where('name', 'CHECKSHEET')
            ->first();

        $laporanMenu = DB::table('app_menus')
            ->where('parent_id', $karawangMenu->id)
            ->where('name', 'LAPORAN')
            ->first();

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
            'manager_plating', 
            'oshef'
        ];

        // 1. Seed QR Plating-Pasang and QR Plating-Cabut under CHECKSHEET
        if ($checksheetMenu) {
            $maxOrder = DB::table('app_menus')->where('parent_id', $checksheetMenu->id)->max('order') ?? 0;

            $menusToChecksheet = [
                [
                    'name' => 'QR Plating-Pasang',
                    'route' => 'plating_scan.pasang.create',
                    'order' => $maxOrder + 1
                ],
                [
                    'name' => 'QR Plating-Cabut',
                    'route' => 'plating_scan.cabut.create',
                    'order' => $maxOrder + 2
                ]
            ];

            foreach ($menusToChecksheet as $m) {
                $menuId = DB::table('app_menus')->insertGetId([
                    'name' => $m['name'],
                    'icon' => 'fas fa-qrcode',
                    'route' => $m['route'],
                    'parent_id' => $checksheetMenu->id,
                    'order' => $m['order'],
                    'is_active' => true,
                    'plant_code' => 'karawang',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($roles as $role) {
                    DB::table('role_permissions')->insert([
                        'menu_id' => $menuId,
                        'role' => $role,
                        'can_view' => true,
                        'can_input' => in_array($role, ['admin', 'supervisor', 'inspector', 'kashift', 'kashift_plating', 'supervisor_plating']),
                        'can_edit' => in_array($role, ['admin', 'supervisor', 'supervisor_plating']),
                        'can_delete' => $role === 'admin',
                        'can_approve' => in_array($role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'manager_plating']),
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
        $menuNames = ['QR Plating-Pasang', 'QR Plating-Cabut'];
        
        $menuIds = DB::table('app_menus')
            ->whereIn('name', $menuNames)
            ->where('plant_code', 'karawang')
            ->pluck('id');

        DB::table('role_permissions')->whereIn('menu_id', $menuIds)->delete();
        DB::table('app_menus')->whereIn('id', $menuIds)->delete();
    }
};
