<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Share unread rejection alerts with all views for inspectors
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $inputRoutes = [
                'checksheet.sub_assy',
                'plating.create',
                'double_tape.create',
                'in_process.create',
                'first_piece_approval.create',
                'cross_cut.create',
                'cross_cut_painting.create',
                'sortir.create',
            ];

            $isInputRoute = false;
            foreach ($inputRoutes as $route) {
                if (request()->routeIs($route)) {
                    $isInputRoute = true;
                    break;
                }
            }

            // Also check for incoming wildcard routes
            if (!$isInputRoute && request()->routeIs('incoming.*.create')) {
                $isInputRoute = true;
            }

            if ($isInputRoute && auth()->check() && auth()->user()->role === 'inspector') {
                $rejectionAlerts = \App\Models\Notification::where('user_id', auth()->id())
                    ->where('type', 'rejection_alert')
                    ->where('is_read', false)
                    ->get()
                    ->map(function ($n) {
                        return [
                            'id' => $n->id,
                            'title' => $n->title,
                            'message' => $n->message,
                            'url' => $n->data['url'] ?? '#',
                        ];
                    });
                $view->with('unreadRejections', $rejectionAlerts);
            }
        });

        // Share dynamic menus with topbar based on permissions
        \Illuminate\Support\Facades\View::composer('layouts.topbar', function ($view) {
            if (auth()->check()) {
                $role = auth()->user()->role;
                $userId = auth()->id();
                
                $permissionCheck = function($q) use ($role, $userId) {
                    $q->where(function($query) use ($role, $userId) {
                        // User specific override (Allows)
                        $query->whereHas('userPermissions', function($up) use ($userId) {
                            $up->where('user_id', $userId)->where('can_view', true);
                        })
                        // OR Role permission (only if NO user override exists for this menu)
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

                // Fetch menus including children up to 4 levels, filtered by user/role permissions
                $menus = \App\Models\AppMenu::whereNull('parent_id')
                    ->where('is_active', true)
                    ->where(function($q) use ($permissionCheck) {
                        $permissionCheck($q);
                    })
                    ->with(['children' => function($q) use ($role, $userId, $permissionCheck) {
                        $q->where('is_active', true)
                          ->where(function($sq) use ($permissionCheck) {
                              $permissionCheck($sq);
                          })
                          ->with(['children' => function($sq) use ($role, $userId, $permissionCheck) {
                              $sq->where('is_active', true)
                                 ->where(function($ssq) use ($permissionCheck) {
                                     $permissionCheck($ssq);
                                 })
                                 ->with(['children' => function($ssq) use ($role, $userId, $permissionCheck) {
                                     $ssq->where('is_active', true)
                                         ->where(function($sssq) use ($permissionCheck) {
                                             $permissionCheck($sssq);
                                         })
                                         ->orderBy('order');
                                 }])
                                 ->orderBy('order');
                          }])
                          ->orderBy('order');
                    }])
                    ->orderBy('order')
                    ->get();
                    
                $view->with('dynamicMenus', $menus);
            }
        });
    }
}
