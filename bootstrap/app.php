<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Load helper functions
require_once __DIR__ . '/../app/Helpers/ApprovalHelper.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        // Register alias untuk middleware role
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'check_status' => \App\Http\Middleware\CheckUserStatus::class,
            'check_maintenance' => \App\Http\Middleware\CheckMenuMaintenance::class,
        ]);

        // Append to web group to ensure it runs for all web requests
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\CheckMenuMaintenance::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('app:check-calibration-schedules')->dailyAt('07:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
    })->create();