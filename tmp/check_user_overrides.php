<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppMenu;
use App\Models\User;
use App\Models\UserPermission;

$adminUsers = User::where('role', 'admin')->get();
echo "Admin Users Count: " . $adminUsers->count() . "\n\n";

foreach ($adminUsers as $user) {
    echo "Checking User: {$user->email} (ID: {$user->id})\n";
    
    $settingsMenu = AppMenu::where('name', 'Pengaturan')->first();
    if ($settingsMenu) {
        $up = UserPermission::where('user_id', $user->id)->where('menu_id', $settingsMenu->id)->first();
        if ($up) {
            echo "  - User Specific Override FOUND for 'Pengaturan'\n";
            echo "  - can_view: " . ($up->can_view ? 'YES' : 'NO') . "\n";
        } else {
            echo "  - No user specific override for 'Pengaturan'. Falling back to role.\n";
        }
    }
}
echo "\nTotal UserPermissions for all users: " . UserPermission::count() . "\n";
