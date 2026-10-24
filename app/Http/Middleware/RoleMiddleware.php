<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user's role is in the allowed roles list (case-insensitive)
        $userRole = strtolower(trim(Auth::user()->role ?? ''));
        $allowedRoles = array_map('strtolower', array_map('trim', $roles));

        if (!in_array($userRole, $allowedRoles)) {
            // Dynamic fallback: Jika role tidak ada di daftar kaku, periksa apakah user/role diberi izin di menu Pengaturan
            $routeName = $request->route() ? $request->route()->getName() : null;
            if ($routeName && \App\Helpers\AppMenu::checkPermission($routeName, 'view')) {
                return $next($request);
            }

            // Jika di pengaturan juga tidak diberi hak akses, lempar 403 Forbidden
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}