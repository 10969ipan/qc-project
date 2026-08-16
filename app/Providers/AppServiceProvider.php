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
            $isInputRoute = request()->routeIs(
                'checksheet.sub_assy', 'plating.create', 'double_tape.create',
                'in_process.create', 'first_piece_approval.create',
                'cross_cut.create', 'cross_cut_painting.create',
                'sortir.create', 'incoming.*.create'
            );

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
                $role   = auth()->user()->role;
                $userId = auth()->id();

                // Cache key per user – invalidate when permissions change (10 menit)
                $cacheKey = "topbar_menus_{$userId}_{$role}";

                $menus = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () use ($role, $userId) {
                    // Admin bypasses all permission checks
                    if ($role === 'admin') {
                        return \App\Models\AppMenu::whereNull('parent_id')
                            ->where('is_active', true)
                            ->with(['children' => function($q) {
                                $q->where('is_active', true)
                                  ->with(['children' => function($sq) {
                                      $sq->where('is_active', true)
                                         ->with(['children' => function($ssq) {
                                             $ssq->where('is_active', true)->orderBy('order');
                                         }])->orderBy('order');
                                  }])->orderBy('order');
                            }])
                            ->orderBy('order')
                            ->get();
                    }

                    // ── Permission filter closure ─────────────────────────────────
                    $permissionCheck = function($q) use ($role, $userId) {
                        $q->where(function($query) use ($role, $userId) {
                            $query->whereHas('userPermissions', function($up) use ($userId) {
                                $up->where('user_id', $userId)->where('can_view', true);
                            })
                            ->orWhere(function($sub) use ($role, $userId) {
                                $sub->whereHas('permissions', function($p) use ($role) {
                                    $p->where('role', $role)->where('can_view', true);
                                })->whereDoesntHave('userPermissions', function($up) use ($userId) {
                                    $up->where('user_id', $userId);
                                });
                            });
                        });
                    };

                    $visibleMenus = \App\Models\AppMenu::whereNull('parent_id')
                        ->where('is_active', true)
                        ->with(['children' => function($q) use ($permissionCheck) {
                            $q->where('is_active', true)
                              ->where(function($sq) use ($permissionCheck) {
                                  $permissionCheck($sq);
                              })
                              ->with(['children' => function($sq) use ($permissionCheck) {
                                  $sq->where('is_active', true)
                                     ->where(function($ssq) use ($permissionCheck) {
                                         $permissionCheck($ssq);
                                     })
                                     ->with(['children' => function($ssq) use ($permissionCheck) {
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

                    return $visibleMenus->filter(function($menu) use ($permissionCheck, $role, $userId) {
                        if ($menu->children->isNotEmpty()) return true;

                        $hasUserPerm = \App\Models\UserPermission::where('user_id', $userId)
                            ->where('menu_id', $menu->id)->where('can_view', true)->exists();
                        if ($hasUserPerm) return true;

                        $hasRolePerm = \App\Models\RolePermission::where('role', $role)
                            ->where('menu_id', $menu->id)->where('can_view', true)->exists();
                        $hasUserOverride = \App\Models\UserPermission::where('user_id', $userId)
                            ->where('menu_id', $menu->id)->exists();
                        return $hasRolePerm && !$hasUserOverride;
                    })->values();
                });

                $view->with('dynamicMenus', $menus);
            }
        });

        // Share dynamic Next Process options with all views (cached 30 menit)
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('nextProcessesGlobal', \Illuminate\Support\Facades\Cache::remember(
                'next_processes_active',
                now()->addMinutes(30),
                fn() => \App\Models\NextProcess::where('is_active', true)->orderBy('plant_id')->orderBy('order')->get()
            ));
        });

        // Register Global Observer for Activity Log Diff
        \Illuminate\Support\Facades\Event::listen('eloquent.updating: *', function($eventName, array $data) {
            $model = $data[0] ?? null;
            if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                $class = get_class($model);
                $id = $model->getKey();
                if ($id) {
                    $dirty = $model->getDirty();
                    $changes = [];
                    foreach ($dirty as $key => $newValue) {
                        if (in_array($key, ['created_at', 'updated_at', 'cycle_time', 'password', 'remember_token'])) continue;
                        
                        $oldValue = $model->getOriginal($key);
                        
                        // Ignore date format equivalence
                        if (is_string($oldValue) && is_string($newValue)) {
                            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $oldValue) && preg_match('/^\d{4}-\d{2}-\d{2}/', $newValue)) {
                                try {
                                    if (\Carbon\Carbon::parse($oldValue)->eq(\Carbon\Carbon::parse($newValue))) {
                                        continue;
                                    }
                                } catch (\Exception $e) {}
                            }
                        }

                        // Ignore JSON empty array/object equivalents
                        if (($oldValue === '{}' && $newValue === '[]') || ($oldValue === '[]' && $newValue === '{}')) {
                            continue;
                        }

                        $changes[$key] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                    }
                    if (!empty($changes)) {
                        \App\Helpers\ActivityLogger::setOriginalData($class, $id, $changes);
                    }
                }
            }
        });
    }
}
