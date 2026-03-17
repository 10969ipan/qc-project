<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppMenu;
use App\Models\RolePermission;
use App\Models\User;

$user = User::where('role', 'admin')->first();
if (!$user) {
    echo "No admin user found.\n";
    exit;
}

echo "Checking menus for user: {$user->email} (Role: {$user->role})\n\n";

$menus = AppMenu::whereNull('parent_id')->orderBy('order')->get();

foreach ($menus as $menu) {
    $rolePerm = RolePermission::where('role', $user->role)->where('menu_id', $menu->id)->first();
    $canView = $rolePerm ? ($rolePerm->can_view ? 'YES' : 'NO') : 'NULL';
    echo "Menu: {$menu->name} | Route: {$menu->route} | Role Can View: {$canView}\n";
    
    foreach ($menu->children as $child) {
        $childRolePerm = RolePermission::where('role', $user->role)->where('menu_id', $child->id)->first();
        $childCanView = $childRolePerm ? ($childRolePerm->can_view ? 'YES' : 'NO') : 'NULL';
        echo "  - Child: {$child->name} | Route: {$child->route} | Role Can View: {$childCanView}\n";
    }
}
