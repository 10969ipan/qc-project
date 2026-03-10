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
    }
}
