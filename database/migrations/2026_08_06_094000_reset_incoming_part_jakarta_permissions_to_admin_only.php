<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppMenu;
use App\Models\RolePermission;
use App\Models\UserPermission;

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
            // Remove role permissions for non-admin roles so menu is NOT active by default for all users
            RolePermission::whereIn('menu_id', $ids)->where('role', '!=', 'admin')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed
    }
};
