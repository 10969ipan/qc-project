<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AppMenu;
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

        $currentRoute = $request->route() ? $request->route()->getName() : null;
        $currentPath = $request->path();

        // Find the menu entry that matches current route or path
        $menu = AppMenu::where('is_active', true)
            ->where('is_maintenance', true)
            ->where(function($q) use ($currentRoute, $currentPath) {
                if ($currentRoute) {
                    $q->where('route', $currentRoute);
                }
                $q->orWhere('route', $currentPath)
                  ->orWhere('route', '/' . $currentPath);
            })
            ->first();

        if ($menu) {
            $message = $menu->maintenance_message ?: 'Modul ini sedang dalam pemeliharaan.';
            
            if ($request->ajax()) {
                return response()->json(['status' => 'maintenance', 'message' => $message], 403);
            }

            return redirect()->route('dashboard')->with('maintenance_alert', $message);
        }

        return $next($request);
    }
}
