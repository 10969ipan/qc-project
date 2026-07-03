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
        // Temukan root menu Quality Control
        $qcMenu = \App\Models\AppMenu::where('name', 'like', '%Quality Control%')->first();
        
        if (!$qcMenu) return;

        // Cari menu PLANT JAKARTA dan PLANT KARAWANG
        $jktMenu = \App\Models\AppMenu::where('name', 'like', '%PLANT JAKARTA%')->where('parent_id', $qcMenu->id)->first();
        $krwMenu = \App\Models\AppMenu::where('name', 'like', '%PLANT KARAWANG%')->where('parent_id', $qcMenu->id)->first();

        $menusToInsert = [];

        // Setup menu Jakarta
        if ($jktMenu) {
            $checksheetJkt = \App\Models\AppMenu::firstOrCreate(['name' => 'CHECKSHEET', 'parent_id' => $jktMenu->id], ['order' => 3, 'is_active' => true]);
            $laporanJkt = \App\Models\AppMenu::firstOrCreate(['name' => 'LAPORAN', 'parent_id' => $jktMenu->id], ['order' => 4, 'is_active' => true]);
            
            $menusToInsert = array_merge($menusToInsert, [
                ['name' => 'Painting', 'route' => 'painting.create', 'parent_id' => $checksheetJkt->id, 'order' => 10, 'plant_code' => 'jakarta', 'is_active' => true],
                ['name' => 'Painting', 'route' => 'painting.index', 'parent_id' => $laporanJkt->id, 'order' => 10, 'plant_code' => 'jakarta', 'is_active' => true],
                ['name' => 'Cross Cut Painting', 'route' => 'cross_cut_painting.create', 'parent_id' => $checksheetJkt->id, 'order' => 11, 'plant_code' => 'jakarta', 'is_active' => true],
                ['name' => 'Cross Cut Painting', 'route' => 'cross_cut_painting.index', 'parent_id' => $laporanJkt->id, 'order' => 11, 'plant_code' => 'jakarta', 'is_active' => true],
            ]);
        }

        // Setup menu Karawang
        if ($krwMenu) {
            $checksheetKrw = \App\Models\AppMenu::firstOrCreate(['name' => 'CHECKSHEET', 'parent_id' => $krwMenu->id], ['order' => 3, 'is_active' => true]);
            $laporanKrw = \App\Models\AppMenu::firstOrCreate(['name' => 'LAPORAN', 'parent_id' => $krwMenu->id], ['order' => 4, 'is_active' => true]);
            
            $menusToInsert = array_merge($menusToInsert, [
                ['name' => 'Painting', 'route' => 'painting.create', 'parent_id' => $checksheetKrw->id, 'order' => 10, 'plant_code' => 'karawang', 'is_active' => true],
                ['name' => 'Painting', 'route' => 'painting.index', 'parent_id' => $laporanKrw->id, 'order' => 10, 'plant_code' => 'karawang', 'is_active' => true],
                ['name' => 'Cross Cut Painting', 'route' => 'cross_cut_painting.create', 'parent_id' => $checksheetKrw->id, 'order' => 11, 'plant_code' => 'karawang', 'is_active' => true],
                ['name' => 'Cross Cut Painting', 'route' => 'cross_cut_painting.index', 'parent_id' => $laporanKrw->id, 'order' => 11, 'plant_code' => 'karawang', 'is_active' => true],
            ]);
        }

        foreach ($menusToInsert as $m) {
            $menu = \App\Models\AppMenu::firstOrCreate(
                ['route' => $m['route'], 'parent_id' => $m['parent_id']],
                $m
            );
            \App\Models\AppMenu::where('id', $menu->id)->update(['plant_code' => $m['plant_code']]);
            
            // Berikan permissions
            $roles = ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager', 'karu_qc'];
            foreach ($roles as $role) {
                \App\Models\RolePermission::updateOrCreate(
                    ['role' => $role, 'menu_id' => $menu->id],
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
        \App\Models\AppMenu::whereIn('route', [
            'painting.create', 'painting.index', 
            'cross_cut_painting.create', 'cross_cut_painting.index'
        ])->delete();
    }
};
