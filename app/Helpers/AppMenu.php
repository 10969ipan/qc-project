<?php

namespace App\Helpers;

use App\Models\AppMenu as AppMenuModel;
use Illuminate\Support\Facades\Auth;

class AppMenu
{
    /**
     * Check if the authenticated user has permission for a specific route and action.
     *
     * @param string $route
     * @param string $action
     * @return bool
     */
    public static function checkPermission($route, $action = 'view')
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Admin has all permissions
        if ($user->role === 'admin') {
            return true;
        }

        // Find ALL menus by route
        $menus = AppMenuModel::where('route', $route)->get();

        // If not found, try to find the base module index route
        if ($menus->isEmpty()) {
            // Strip suffixes like .bulk_approve, .store, .update, etc., and append .index
            $baseRoute = preg_replace('/\.(bulk_approve|bulk_destroy|store|update|destroy|edit|create|approve|reject|export_pdf)$/', '.index', $route);
            if ($baseRoute !== $route) {
                $menus = AppMenuModel::where('route', $baseRoute)->get();
            }
        }

        if ($menus->isEmpty()) {
            // If menu not found, we fallback to false for safety
            return false;
        }

        // If user has permission in ANY of the matched menus, grant it.
        // This fixes the issue where a route exists in both Jakarta and Karawang, 
        // and first() was checking the wrong one.
        foreach ($menus as $m) {
            if ($user->hasPermission($m->id, $action)) {
                return true;
            }
        }

        return false;
    }
}
