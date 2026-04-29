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

        // Find the menu by route
        $menu = AppMenuModel::where('route', $route)->first();

        if (!$menu) {
            // If menu not found, we might want to check by slug or allow it
            // For now, if it's not registered in AppMenu, we fallback to false for safety
            return false;
        }

        return $user->hasPermission($menu->id, $action);
    }
}
