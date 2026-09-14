<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardService;
use App\Models\User;
use App\Models\Plant;
use App\Models\AppMenu;
use App\Models\NextProcess;
use Illuminate\Support\Facades\Cache;

class WarmCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qc:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-warm essential application caches (Dashboard, Topbar Menus, Next Processes, Maintenance Routes) for fast boot performance';

    /**
     * Execute the console command.
     */
    public function handle(DashboardService $dashboardService): int
    {
        $this->info('Starting QC Project cache warming...');

        // 1. Warm Maintenance Menus List
        Cache::remember('maintenance_menus_list', 300, function () {
            return AppMenu::where('is_active', true)
                ->where('is_maintenance', true)
                ->get(['route', 'maintenance_message']);
        });
        $this->info('  [✓] Maintenance routes cached.');

        // 2. Warm Next Processes Active List
        Cache::remember('next_processes_active', 1800, function () {
            return NextProcess::where('is_active', true)->orderBy('plant_id')->orderBy('order')->get();
        });
        $this->info('  [✓] Next processes cached.');

        // 3. Warm Dashboard Stats & Monitoring for Admin / Management Dual-View
        $adminUser = User::whereIn('role', ['admin', 'manager', 'asst_manager', 'manager_qc'])->first();
        if ($adminUser) {
            auth()->login($adminUser);
            foreach (['karawang', 'jakarta'] as $plantCode) {
                request()->merge(['plant' => $plantCode]);
                $dashboardService->getDashboardData();
            }
            $this->info('  [✓] Dashboard dual-view statistics cached.');
        }

        // 4. Warm Customer Claim Data
        $claimYear = date('Y');
        Cache::remember("dashboard_customer_claim_{$claimYear}", 86400, function () use ($dashboardService, $claimYear) {
            return $dashboardService->getCustomerClaimData($claimYear);
        });
        $this->info('  [✓] Customer claim data cached.');

        $this->info('QC Project cache warming complete! Application is pre-warmed for instant response.');
        return Command::SUCCESS;
    }
}
