<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppMenu;
use App\Models\RolePermission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find existing DURABILITY menus to place DURABILITY TRIAL beside them
        $durabilityMenus = AppMenu::where('name', 'DURABILITY')->get();

        foreach ($durabilityMenus as $durability) {
            $alreadyExists = AppMenu::where('parent_id', $durability->parent_id)
                ->where('name', 'DURABILITY TEST')
                ->exists();

            if (!$alreadyExists) {
                $maxOrder = AppMenu::where('parent_id', $durability->parent_id)->max('order') ?? 0;

                $trialMenu = AppMenu::create([
                    'name' => 'DURABILITY TEST',
                    'parent_id' => $durability->parent_id,
                    'route' => 'standard-performance-tests-trial.index',
                    'order' => $maxOrder + 1,
                    'is_active' => true
                ]);

                // Grant default permissions to roles
                $roles = [
                    'admin', 'manager', 'asst_manager', 'supervisor',
                    'kashift', 'inspector', 'karu_qc', 'kashift_plating',
                    'supervisor_plating', 'manager_plating'
                ];

                foreach ($roles as $role) {
                    RolePermission::create([
                        'role' => $role,
                        'menu_id' => $trialMenu->id,
                        'can_view' => true,
                        'can_input' => in_array($role, ['admin', 'supervisor', 'inspector', 'kashift']),
                        'can_edit' => in_array($role, ['admin', 'supervisor']),
                        'can_delete' => $role === 'admin',
                        'can_approve' => in_array($role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift']),
                        'can_export' => true,
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
        $trialMenus = AppMenu::where('name', 'DURABILITY TRIAL')->get();
        foreach ($trialMenus as $menu) {
            RolePermission::where('menu_id', $menu->id)->delete();
            $menu::delete();
        }
    }
};
