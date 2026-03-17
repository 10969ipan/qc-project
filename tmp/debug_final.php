<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppMenu;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;

echo "--- USERS ---\n";
foreach(User::all() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Role: {$u->role} | Email: {$u->email}\n";
}

$settingsMenu = AppMenu::where('name', 'Pengaturan')->first();
if (!$settingsMenu) {
    echo "\n!!! ERROR: 'Pengaturan' menu NOT FOUND in app_menus table !!!\n";
    exit;
}

echo "\n--- MENU INFO ---\n";
echo "ID: {$settingsMenu->id} | Name: {$settingsMenu->name} | Active: " . ($settingsMenu->is_active ? 'YES' : 'NO') . " | Route: {$settingsMenu->route}\n";

echo "\n--- ROLE PERMISSIONS FOR 'Pengaturan' ---\n";
foreach(RolePermission::where('menu_id', $settingsMenu->id)->get() as $p) {
    echo "Role: {$p->role} | View: " . ($p->can_view ? 'YES' : 'NO') . "\n";
}

echo "\n--- USER OVERRIDES FOR 'Pengaturan' ---\n";
foreach(UserPermission::where('menu_id', $settingsMenu->id)->get() as $p) {
    $user = User::find($p->user_id);
    $email = $user ? $user->email : 'UNKNOWN';
    echo "User: {$email} (ID: {$p->user_id}) | View: " . ($p->can_view ? 'YES' : 'NO') . "\n";
}

echo "\n--- SIMULATING AppServiceProvider QUERY ---\n";
// Simplified version of the query in AppServiceProvider
$role = 'admin'; // Testing for admin
$userId = 1;     // Testing for admin ID 1

$permissionCheck = function($q) use ($role, $userId) {
    $q->where(function($query) use ($role, $userId) {
        $query->whereHas('userPermissions', function($up) use ($userId) {
            $up->where('user_id', $userId)->where('can_view', true);
        })
        ->orWhere(function($sub) use ($role, $userId) {
            $sub->whereHas('permissions', function($p) use ($role) {
                $p->where('role', $role)->where('can_view', true);
            })->whereDoesntHave('userPermissions', function($up) use ($userId) {
                $up->where('user_id', $userId);
            });
        })
        ->orWhere('route', '/'); 
    });
};

$menus = AppMenu::whereNull('parent_id')
    ->where('is_active', true)
    ->where(function($q) use ($permissionCheck) {
        $permissionCheck($q);
    })
    ->get();

echo "Simulated Root Menus for Role 'admin' (User ID 1):\n";
foreach($menus as $m) {
    echo "- {$m->name} (ID: {$m->id})\n";
}
