<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppMenu;
use App\Models\RolePermission;

echo "Top-level Menus in app_menus table:\n";
$topMenus = AppMenu::whereNull('parent_id')->orderBy('order')->get();
foreach ($topMenus as $m) {
    echo "ID: {$m->id} | Name: {$m->name} | Active: " . ($m->is_active ? 'YES' : 'NO') . " | Route: {$m->route} | Order: {$m->order}\n";
}

echo "\nChecking Role Permissions for role 'admin' for ID 75 (if it exists):\n";
$m75 = AppMenu::where('name', 'Pengaturan')->first();
if ($m75) {
    $p = RolePermission::where('role', 'admin')->where('menu_id', $m75->id)->first();
    if ($p) {
        echo "Menu: {$m75->name} (ID: {$m75->id})\n";
        echo "  - can_view: " . ($p->can_view ? 'YES' : 'NO') . "\n";
    } else {
        echo "No RolePermission found for 'admin' on menu 'Pengaturan'\n";
    }
} else {
    echo "Menu 'Pengaturan' NOT FOUND in app_menus table\n";
}
