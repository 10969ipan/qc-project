<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DashboardService;
use App\Models\GeneralSetting;
use Symfony\Component\HttpFoundation\Response;

class CheckDailyApprovalRate
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // 1. Only applies to Inspectors
        if ($user && $user->role === 'inspector') {
            
            // Check if the security gate is enabled in general settings
            $isGateEnabled = GeneralSetting::getValue('daily_approval_gate_enabled', '1');
            if ($isGateEnabled === '0' || $isGateEnabled === false) {
                return $next($request);
            }

            $now = now();
            $hour = $now->hour;
            $plantId = $user->plant_id;

            // Jakarta ID: 36d54522-c4f4-48b7-acb1-b7eb2dbc44ae
            // Jakarta end hour: 17 (5 PM), Others: 15 (3 PM)
            $endHour = ($plantId === '36d54522-c4f4-48b7-acb1-b7eb2dbc44ae') ? 17 : 15;

            // Time Window Check
            if ($hour >= 12 && $hour < $endHour) {
                
                $plantId = $user->plant_id;
                $rate = $this->dashboardService->getDailyApprovalRate($plantId);

                // 3. If rate is below 90%
                if ($rate < 90) {
                    $message = "Maaf, akses modul input data saat ini dikunci. " . 
                              "Batas maksimal approval harian harus mencapai 90% sebelum jam 12:00 siang. " .
                              "Persentase saat ini baru {$rate}%. Silakan hubungi Karu/Kashift Anda untuk melakukan approval.";

                    if ($request->ajax()) {
                        return response()->json([
                            'status' => 'locked', 
                            'message' => $message,
                            'rate' => $rate
                        ], 403);
                    }

                    return redirect()->route('dashboard')->with('error', $message);
                }
            }
        }

        return $next($request);
    }
}
