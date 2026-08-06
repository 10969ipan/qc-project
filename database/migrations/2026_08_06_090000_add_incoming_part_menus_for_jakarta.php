<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Models\AppMenu;
use App\Models\RolePermission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $qcMenu = AppMenu::where('name', 'Quality Control')->first();
        if (!$qcMenu) return;

        $jktMenu = AppMenu::where('name', 'PLANT JAKARTA')->where('parent_id', $qcMenu->id)->first();
        if (!$jktMenu) return;

        $checksheetJkt = AppMenu::where('name', 'CHECKSHEET')->where('parent_id', $jktMenu->id)->first();
        $laporanJkt = AppMenu::where('name', 'LAPORAN')->where('parent_id', $jktMenu->id)->first();

        if ($checksheetJkt) {
            $menuCreate = AppMenu::firstOrCreate(
                ['route' => 'incoming.parts.create', 'parent_id' => $checksheetJkt->id],
                ['name' => 'Incoming Part', 'order' => 5, 'is_active' => true, 'plant_code' => 'jakarta']
            );
        }

        if ($laporanJkt) {
            $menuIndex = AppMenu::firstOrCreate(
                ['route' => 'incoming.parts.index', 'parent_id' => $laporanJkt->id],
                ['name' => 'Incoming Part', 'order' => 5, 'is_active' => true, 'plant_code' => 'jakarta']
            );
        }

        $roles = ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager', 'karu_qc', 'admin_qc', 'spv_qc'];
        $menuIds = array_filter([$menuCreate->id ?? null, $menuIndex->id ?? null]);

        foreach ($roles as $role) {
            foreach ($menuIds as $mid) {
                RolePermission::updateOrCreate(
                    ['role' => $role, 'menu_id' => $mid],
                    [
                        'can_view' => true,
                        'can_create' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'can_export' => true,
                        'can_approve' => true
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $qcMenu = AppMenu::where('name', 'Quality Control')->first();
        $jktMenu = AppMenu::where('name', 'PLANT JAKARTA')->where('parent_id', $qcMenu?->id)->first();
        $checksheetJkt = AppMenu::where('name', 'CHECKSHEET')->where('parent_id', $jktMenu?->id)->first();
        $laporanJkt = AppMenu::where('name', 'LAPORAN')->where('parent_id', $jktMenu?->id)->first();

        $ids = [];
        if ($checksheetJkt) {
            $c = AppMenu::where('route', 'incoming.parts.create')->where('parent_id', $checksheetJkt->id)->value('id');
            if ($c) $ids[] = $c;
        }
        if ($laporanJkt) {
            $i = AppMenu::where('route', 'incoming.parts.index')->where('parent_id', $laporanJkt->id)->value('id');
            if ($i) $ids[] = $i;
        }

        if (!empty($ids)) {
            RolePermission::whereIn('menu_id', $ids)->delete();
            AppMenu::whereIn('id', $ids)->delete();
        }
    }
};
