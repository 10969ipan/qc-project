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
        // 1. Get Dynamic Parent IDs for Jakarta
        $qcMenu = \App\Models\AppMenu::where('name', 'Quality Control')->first();
        $jktMenu = \App\Models\AppMenu::where('name', 'PLANT JAKARTA')->where('parent_id', $qcMenu?->id)->first();
        
        $checksheetJkt = \App\Models\AppMenu::where('name', 'CHECKSHEET')->where('parent_id', $jktMenu?->id)->first();
        $laporanJkt = \App\Models\AppMenu::where('name', 'LAPORAN')->where('parent_id', $jktMenu?->id)->first();

        $checksheetId = $checksheetJkt ? $checksheetJkt->id : 10;
        $laporanId = $laporanJkt ? $laporanJkt->id : 15;

        // 2. Add Painting Menus to Jakarta
        $menuCreate = \App\Models\AppMenu::firstOrCreate(
            ['route' => 'painting.create', 'parent_id' => $checksheetId],
            ['name' => 'Painting', 'icon' => 'fas fa-fw fa-paint-brush', 'is_active' => true, 'order' => 10, 'plant_code' => 'jakarta']
        );

        $menuIndex = \App\Models\AppMenu::firstOrCreate(
            ['route' => 'painting.index', 'parent_id' => $laporanId],
            ['name' => 'Painting', 'icon' => 'fas fa-fw fa-file-alt', 'is_active' => true, 'order' => 10, 'plant_code' => 'jakarta']
        );

        $crossCutCreate = \App\Models\AppMenu::firstOrCreate(
            ['route' => 'cross_cut_painting.create', 'parent_id' => $checksheetId],
            ['name' => 'Cross Cut Painting', 'icon' => 'fas fa-fw fa-paint-brush', 'is_active' => true, 'order' => 11, 'plant_code' => 'jakarta']
        );

        $crossCutIndex = \App\Models\AppMenu::firstOrCreate(
            ['route' => 'cross_cut_painting.index', 'parent_id' => $laporanId],
            ['name' => 'Cross Cut Painting', 'icon' => 'fas fa-fw fa-file-alt', 'is_active' => true, 'order' => 11, 'plant_code' => 'jakarta']
        );

        // 2. Give permissions for Jakarta Painting & Cross Cut
        $roles = ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager', 'karu_qc'];
        foreach ($roles as $role) {
            $menus = [$menuCreate->id, $menuIndex->id, $crossCutCreate->id, $crossCutIndex->id];
            foreach ($menus as $mid) {
                \App\Models\RolePermission::updateOrCreate(
                    ['role' => $role, 'menu_id' => $mid],
                    ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_export' => true]
                );
            }
        }

        // 4. Give Topbar Permissions to SPV Plating & Manager Plating (Dynamic IDs)
        $platingRoles = ['supervisor_plating', 'manager_plating'];
        
        $qcMenuId = $qcMenu?->id;
        $krwMenuId = \App\Models\AppMenu::where('name', 'PLANT KARAWANG')->where('parent_id', $qcMenuId)->value('id');
        $krwLaporanId = \App\Models\AppMenu::where('name', 'LAPORAN')->where('parent_id', $krwMenuId)->value('id');
        $ccPlatingId = \App\Models\AppMenu::where('route', 'cross_cut.index')->value('id');
        $ccPaintingId = \App\Models\AppMenu::where('route', 'cross_cut_painting.index')->where('plant_code', 'karawang')->value('id');

        $menuIds = array_filter([$qcMenuId, $krwMenuId, $krwLaporanId, $ccPlatingId, $ccPaintingId]); 

        foreach ($platingRoles as $role) {
            foreach ($menuIds as $menuId) {
                \App\Models\RolePermission::updateOrCreate(
                    ['role' => $role, 'menu_id' => $menuId],
                    ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_export' => true]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clean up is optional, but for safety:
        \App\Models\AppMenu::whereIn('route', ['painting.create', 'painting.index', 'cross_cut_painting.create', 'cross_cut_painting.index'])->where('plant_code', 'jakarta')->delete();
    }
};
