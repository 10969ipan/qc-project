<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppMenu;
use App\Models\OperatorComplianceItem;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        $allowedRoles = OperatorComplianceItem::MASTER_MANAGER_ROLES;
        $deniedRoles  = ['kashift', 'karu_qc', 'leader', 'inspector', 'operator'];
        $allRoles     = array_unique(array_merge($allowedRoles, $deniedRoles));

        $qualitySystem = AppMenu::whereNull('parent_id')
            ->whereRaw('LOWER(name) = ?', ['quality system'])
            ->first();

        $parents = [];
        if ($qualitySystem) {
            foreach (['jakarta', 'karawang'] as $pCode) {
                $plantParent = AppMenu::where('parent_id', $qualitySystem->id)
                    ->whereRaw('LOWER(plant_code) = ?', [$pCode])
                    ->first();
                if ($plantParent) {
                    $parents[$plantParent->id] = $pCode;
                }
            }
        }

        // Fallback ID baku (Quality System -> Plant Jakarta 65, Plant Karawang 72)
        if (empty($parents)) {
            $parents = [
                65 => 'jakarta',
                72 => 'karawang',
            ];
        }

        foreach ($parents as $parentId => $plantCode) {
            $parent = AppMenu::find($parentId);
            if (!$parent) {
                continue;
            }

            $durabilityTest = AppMenu::where('parent_id', $parentId)
                ->whereRaw('LOWER(name) = ?', ['durability test'])
                ->first();

            $targetOrder = $durabilityTest
                ? ((int) $durabilityTest->order + 1)
                : ((int) (AppMenu::where('parent_id', $parentId)->max('order') ?? 0) + 1);

            // Geser menu lain di bawah Durability Test jika urutan bentrok
            AppMenu::where('parent_id', $parentId)
                ->where('order', '>=', $targetOrder)
                ->where('route', '!=', 'checksheet.leveling_inspector.index')
                ->increment('order');

            $menu = AppMenu::updateOrCreate(
                [
                    'route'      => 'checksheet.leveling_inspector.index',
                    'plant_code' => $plantCode,
                ],
                [
                    'parent_id' => $parentId,
                    'name'      => 'LEVELING INSPECTOR',
                    'order'     => $targetOrder,
                    'is_active' => true,
                    'icon'      => 'fas fa-layer-group',
                ]
            );

            foreach ($allRoles as $role) {
                $canAccess = in_array($role, $allowedRoles, true);
                RolePermission::firstOrCreate(
                    ['role' => $role, 'menu_id' => $menu->id],
                    [
                        'can_view'   => $canAccess,
                        'can_create' => $canAccess,
                        'can_edit'   => $canAccess,
                        'can_delete' => $canAccess,
                    ]
                );
            }
        }

        Cache::flush();
    }

    public function down(): void
    {
        $menus = AppMenu::where('route', 'checksheet.leveling_inspector.index')->get();
        foreach ($menus as $m) {
            RolePermission::where('menu_id', $m->id)->delete();
            $m->delete();
        }
        Cache::flush();
    }
};
