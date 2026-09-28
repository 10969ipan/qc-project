<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppMenu;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc', 'inspector'];

        // 1. Jakarta CHECKSHEET (Parent ID 10)
        $jktParent = AppMenu::find(10);
        if ($jktParent) {
            $maxOrderJkt = AppMenu::where('parent_id', 10)->max('order') ?? 0;
            $jktMenu = AppMenu::updateOrCreate(
                [
                    'parent_id' => 10,
                    'name'      => 'Kepatuhan Operator',
                ],
                [
                    'route'      => 'checksheet.operator_compliance.index',
                    'plant_code' => 'jakarta',
                    'order'      => $maxOrderJkt + 1,
                    'is_active'  => true,
                    'icon'       => 'fas fa-user-check',
                ]
            );

            foreach ($roles as $role) {
                RolePermission::updateOrCreate(
                    ['role' => $role, 'menu_id' => $jktMenu->id],
                    ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true]
                );
            }
        }

        // 2. Karawang CHECKSHEET (Parent ID 28)
        $krwParent = AppMenu::find(28);
        if ($krwParent) {
            $maxOrderKrw = AppMenu::where('parent_id', 28)->max('order') ?? 0;
            $krwMenu = AppMenu::updateOrCreate(
                [
                    'parent_id' => 28,
                    'name'      => 'Kepatuhan Operator',
                ],
                [
                    'route'      => 'checksheet.operator_compliance.index',
                    'plant_code' => 'karawang',
                    'order'      => $maxOrderKrw + 1,
                    'is_active'  => true,
                    'icon'       => 'fas fa-user-check',
                ]
            );

            foreach ($roles as $role) {
                RolePermission::updateOrCreate(
                    ['role' => $role, 'menu_id' => $krwMenu->id],
                    ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true]
                );
            }
        }

        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menus = AppMenu::where('name', 'Kepatuhan Operator')->get();
        foreach ($menus as $m) {
            RolePermission::where('menu_id', $m->id)->delete();
            $m->delete();
        }
        Cache::flush();
    }
};
