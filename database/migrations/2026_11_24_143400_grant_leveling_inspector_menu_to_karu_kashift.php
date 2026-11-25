<?php

use App\Models\AppMenu;
use App\Models\RolePermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        $rolesToGrant = ['leader', 'karu_qc', 'karu_prod', 'kashift', 'kashift_plating'];
        $rolesDenied  = ['staff', 'inspector', 'operator'];

        $menus = AppMenu::where('route', 'checksheet.leveling_inspector.index')->get();

        foreach ($menus as $menu) {
            foreach ($rolesToGrant as $role) {
                RolePermission::updateOrCreate(
                    ['role' => $role, 'menu_id' => $menu->id],
                    [
                        'can_view'    => true,
                        'can_input'   => false,
                        'can_edit'    => false,
                        'can_delete'  => false,
                        'can_approve' => true,
                    ]
                );
            }

            foreach ($rolesDenied as $role) {
                RolePermission::firstOrCreate(
                    ['role' => $role, 'menu_id' => $menu->id],
                    [
                        'can_view'    => false,
                        'can_input'   => false,
                        'can_edit'    => false,
                        'can_delete'  => false,
                        'can_approve' => false,
                    ]
                );
            }
        }

        Cache::flush();
    }

    public function down(): void
    {
        $rolesToRevoke = ['leader', 'karu_qc', 'karu_prod', 'kashift', 'kashift_plating'];
        $menuIds = AppMenu::where('route', 'checksheet.leveling_inspector.index')->pluck('id');

        RolePermission::whereIn('menu_id', $menuIds)
            ->whereIn('role', $rolesToRevoke)
            ->update([
                'can_view'    => false,
                'can_input'   => false,
                'can_edit'    => false,
                'can_delete'  => false,
                'can_approve' => false,
            ]);

        Cache::flush();
    }
};
