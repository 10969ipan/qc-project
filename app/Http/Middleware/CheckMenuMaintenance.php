<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AppMenu;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't check for Admins to allow them to fix things
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        $maintenanceMenus = Cache::remember('maintenance_menus_list', 300, function () {
            return AppMenu::where('is_active', true)
                ->where('is_maintenance', true)
                ->get(['route', 'maintenance_message']);
        });

        if ($maintenanceMenus->isEmpty()) {
            return $next($request);
        }

        $currentRoute = $request->route() ? $request->route()->getName() : null;
        $currentPath = $request->path();

        $matchingMenu = $maintenanceMenus->first(function ($menu) use ($currentRoute, $currentPath) {
            if ($currentRoute && $menu->route === $currentRoute) return true;
            if ($menu->route === $currentPath || $menu->route === '/' . $currentPath) return true;
            return false;
        });

        if ($matchingMenu) {
            $message = $matchingMenu->maintenance_message ?: 'Modul ini sedang dalam pemeliharaan.';
            
            if ($request->ajax()) {
                return response()->json(['status' => 'maintenance', 'message' => $message], 403);
            }

            return redirect()->route('dashboard')->with('maintenance_alert', $message);
        }

        return $next($request);
    }
}

