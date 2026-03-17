<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppMenu;
use App\Models\RolePermission;

echo "All Menus in app_menus table:\n";
$allMenus = AppMenu::all();
foreach ($allMenus as $m) {
    echo "ID: {$m->id} | Name: {$m->name} | Route: {$m->route} | Parent: {$m->parent_id} | Order: {$m->order}\n";
}

echo "\nRole Permissions for role 'admin':\n";
$perms = RolePermission::where('role', 'admin')->get();
foreach ($perms as $p) {
    $menu = AppMenu::find($p->menu_id);
    $menuName = $menu ? $menu->name : 'UNKNOWN';
    echo "Menu ID: {$p->menu_id} ({$menuName}) | View: " . ($p->can_view ? 'YES' : 'NO') . "\n";
}
