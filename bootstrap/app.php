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
            'check_approval_rate' => \App\Http\Middleware\CheckDailyApprovalRate::class,
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
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($e instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
                $maxSize = ini_get('post_max_size');
                $message = "Ukuran file/foto yang Anda unggah terlalu besar (Melebihi batas server {$maxSize}). Mohon kurangi ukuran atau kompres foto sebelum mengunggah.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => $message
                    ], 413);
                }
                return redirect()->back()->with('error', $message);
            }

            if ($e instanceof \Illuminate\Session\TokenMismatchException || ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 419)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                        'redirect' => route('login')
                    ], 419);
                }
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }
        });
    })->create();