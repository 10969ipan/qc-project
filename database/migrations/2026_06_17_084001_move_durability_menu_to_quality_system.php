<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AppMenu;

return new class extends Migration
{
    public function up(): void
    {
        $targetParent = AppMenu::find(69);
        $durability = AppMenu::where('name', 'DURABILITY')->first();

        if ($targetParent && $durability) {
            $maxOrder = AppMenu::where('parent_id', $targetParent->id)->max('order') ?? 0;
            $durability->parent_id = $targetParent->id;
            $durability->order = $maxOrder + 1;
            $durability->save();
            
            $painting = AppMenu::where('parent_id', $durability->id)->where('name', 'Painting')->first();
            if ($painting && $painting->children()->count() == 0) {
                AppMenu::create(['name' => 'Master Data', 'parent_id' => $painting->id, 'order' => 1, 'route' => '#', 'is_maintenance' => true]);
                AppMenu::create(['name' => 'Input Data', 'parent_id' => $painting->id, 'order' => 2, 'route' => '#', 'is_maintenance' => true]);
                AppMenu::create(['name' => 'Laporan', 'parent_id' => $painting->id, 'order' => 3, 'route' => '#', 'is_maintenance' => true]);
            }
        }
    }

    public function down(): void
    {
        $originalParent = AppMenu::find(20);
        $durability = AppMenu::where('name', 'DURABILITY')->first();

        if ($originalParent && $durability) {
            $maxOrder = AppMenu::where('parent_id', $originalParent->id)->max('order') ?? 0;
            $durability->parent_id = $originalParent->id;
            $durability->order = $maxOrder + 1;
            $durability->save();
            
            $painting = AppMenu::where('parent_id', $durability->id)->where('name', 'Painting')->first();
            if ($painting) {
                AppMenu::where('parent_id', $painting->id)->delete();
            }
        }
    }
};
