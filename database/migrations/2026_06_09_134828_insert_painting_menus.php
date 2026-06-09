<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AppMenu;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temukan root menu untuk Karawang
        $karawang = AppMenu::where('plant_code', 'karawang')->first();
        if (!$karawang) return;

        // Temukan sub-menu CHECKSHEET
        $checksheetMenu = AppMenu::where('parent_id', $karawang->id)->where('name', 'CHECKSHEET')->first();
        if ($checksheetMenu) {
            AppMenu::firstOrCreate(
                ['route' => 'painting.create', 'parent_id' => $checksheetMenu->id],
                ['name' => 'Painting', 'order' => 8, 'is_active' => true]
            );
        }

        // Temukan sub-menu LAPORAN
        $laporanMenu = AppMenu::where('parent_id', $karawang->id)->where('name', 'LAPORAN')->first();
        if ($laporanMenu) {
            AppMenu::firstOrCreate(
                ['route' => 'painting.index', 'parent_id' => $laporanMenu->id],
                ['name' => 'Painting', 'order' => 8, 'is_active' => true]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        AppMenu::whereIn('route', ['painting.create', 'painting.index'])->delete();
    }
};
