<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\AppMenu;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

header('Content-Type: application/json');

$debug = [
    'request' => [
        'method' => $request->getMethod(),
        'uri' => $request->getRequestUri(),
        'path' => $request->path(),
        'base_url' => $request->getBaseUrl(),
        'full_url' => $request->fullUrl(),
    ],
    'server' => [
        'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'Unknown',
    ],
    'auth' => [
        'check' => Auth::check(),
        'user' => Auth::user() ? [
            'id' => Auth::id(),
            'role' => Auth::user()->role,
            'name' => Auth::user()->name
        ] : null,
    ],
    'menus' => [
        'count' => AppMenu::count(),
        'root_menus' => AppMenu::whereNull('parent_id')->get(['id', 'title', 'route', 'is_active'])->toArray(),
    ],
    'route' => [
        'exists' => Route::has('home'),
        'current_name' => Route::currentRouteName(),
    ]
];

echo json_encode($debug, JSON_PRETTY_PRINT);
