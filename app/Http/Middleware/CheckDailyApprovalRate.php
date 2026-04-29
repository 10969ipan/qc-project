<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DashboardService;
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
            
            $now = now();
            $hour = $now->hour;

            // Time Window Check: Only enforce between 12:00 and 15:00 (End of Shift 1)
            // This ensures Shift 2 and 3 are not blocked by Shift 1's unapproved data.
            if ($hour >= 12 && $hour < 15) {
                
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
