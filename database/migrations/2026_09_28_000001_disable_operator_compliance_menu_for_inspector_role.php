<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppMenu;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Nonaktifkan izin menu Kepatuhan Operator secara eksplisit untuk role inspector.
     */
    public function up(): void
    {
        $menus = AppMenu::where('name', 'Kepatuhan Operator')->get();
        foreach ($menus as $menu) {
            RolePermission::updateOrCreate(
                ['role' => 'inspector', 'menu_id' => $menu->id],
                [
                    'can_view'   => false,
                    'can_create' => false,
                    'can_edit'   => false,
                    'can_delete' => false,
                ]
            );
        }

        // Bersihkan cache aplikasi agar topbar menu di-refresh seketika
        Cache::flush();
    }

    /**
     * Kembalikan izin menu untuk inspector jika rollback.
     */
    public function down(): void
    {
        $menus = AppMenu::where('name', 'Kepatuhan Operator')->get();
        foreach ($menus as $menu) {
            RolePermission::updateOrCreate(
                ['role' => 'inspector', 'menu_id' => $menu->id],
                [
                    'can_view'   => true,
                    'can_create' => true,
                    'can_edit'   => true,
                    'can_delete' => true,
                ]
            );
        }

        Cache::flush();
    }
};
