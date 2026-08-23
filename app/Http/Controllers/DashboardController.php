<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        // Define restricted roles (if any) that should be locked to their plant on the dashboard
        $user = auth()->user();
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];

        if (in_array($user->role, $restrictedRoles)) {
            $request->merge(['plant' => $user->plant_id]);
        }

        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        // Dashboard Layout Config
        $setting = \App\Models\GeneralSetting::where('category', 'dashboard_layout')
            ->where('key', $user->role)
            ->first();

        $dashboardLayout = [];
        if ($setting && is_string($setting->value)) {
            $decoded = json_decode($setting->value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $dashboardLayout = $decoded;
            }
        }

        $data = $this->dashboardService->getDashboardData($month, $year, $dashboardLayout);

        // Customer Claim Data (Ter-cache 24 jam)
        $claimYear = $request->get('year', 'combined');
        $claimData = \Illuminate\Support\Facades\Cache::remember("dashboard_customer_claim_{$claimYear}", 86400, function () use ($claimYear) {
            return $this->dashboardService->getCustomerClaimData($claimYear);
        });
        $data['claimData'] = $claimData;
        $data['selectedMonth'] = (int) $month;
        $data['selectedYear'] = $year;
        $data['dashboardLayout'] = $dashboardLayout;

        return view('layouts.dashboard', $data);
    }

    /**
     * AJAX endpoint for Customer Claim data
     */
    public function getCustomerClaimData(Request $request)
    {
        $data = $this->dashboardService->getCustomerClaimData($request->year);
        return response()->json($data);
    }

    /**
     * TV Signage Dashboard (Menonaktifkan sementara)
     */
    public function tvIndex(Request $request)
    {
        abort(404, 'Dashboard TV sedang dinonaktifkan.');
    }

    /**
     * TV Dashboard: Lightweight real-time JSON polling endpoint (Disabled)
     */
    public function tvLiveData(Request $request)
    {
        return response()->json(['status' => 'disabled', 'message' => 'Dashboard TV sedang dinonaktifkan.'], 404);
    }

    /**
     * TV Signage: NG Defect aggregation for Slide 3 panel (Disabled)
     */
    public function tvDefects()
    {
        return response()->json(['status' => 'disabled', 'message' => 'Dashboard TV sedang dinonaktifkan.'], 404);
    }
}
