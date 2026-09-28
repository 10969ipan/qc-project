<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppMenu;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Jalankan migrasi buat nambahin menu Kepatuhan Operator di sidebar (Jakarta & Karawang).
     */
    public function up(): void
    {
        // Daftar role yang dapet izin akses menu
        $roles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc', 'inspector'];

        // 1. Menu CHECKSHEET Plant Jakarta (Parent ID 10)
        $jktParent = AppMenu::find(10);
        if ($jktParent) {
            $maxOrderJkt = AppMenu::where('parent_id', 10)->max('order') ?? 0;
            // Gunakan firstOrCreate agar jika menu sudah ada, pengaturan custom Admin tidak tertimpa
            $jktMenu = AppMenu::firstOrCreate(
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

            // Gunakan firstOrCreate: Hanya set nilai default jika izin role belum pernah disetting oleh Admin
            foreach ($roles as $role) {
                $canAccess = ($role !== 'inspector');
                RolePermission::firstOrCreate(
                    ['role' => $role, 'menu_id' => $jktMenu->id],
                    [
                        'can_view'   => $canAccess,
                        'can_create' => $canAccess,
                        'can_edit'   => $canAccess,
                        'can_delete' => $canAccess,
                    ]
                );
            }
        }

        // 2. Menu CHECKSHEET Plant Karawang (Parent ID 28)
        $krwParent = AppMenu::find(28);
        if ($krwParent) {
            $maxOrderKrw = AppMenu::where('parent_id', 28)->max('order') ?? 0;
            // Gunakan firstOrCreate agar jika menu sudah ada, pengaturan custom Admin tidak tertimpa
            $krwMenu = AppMenu::firstOrCreate(
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

            // Gunakan firstOrCreate: Hanya set nilai default jika izin role belum pernah disetting oleh Admin
            foreach ($roles as $role) {
                $canAccess = ($role !== 'inspector');
                RolePermission::firstOrCreate(
                    ['role' => $role, 'menu_id' => $krwMenu->id],
                    [
                        'can_view'   => $canAccess,
                        'can_create' => $canAccess,
                        'can_edit'   => $canAccess,
                        'can_delete' => $canAccess,
                    ]
                );
            }
        }

        // Bersihin cache aplikasi biar menu langsung muncul
        Cache::flush();
    }

    /**
     * Batalkan migrasi (hapus menu & permission terkait).
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
