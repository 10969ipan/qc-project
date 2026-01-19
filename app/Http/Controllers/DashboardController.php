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

        $data = $this->dashboardService->getDashboardData();

        // Customer Claim Data
        $claimYear = $request->get('year', 'combined');
        $claimData = $this->dashboardService->getCustomerClaimData($claimYear);
        $data['claimData'] = $claimData;

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
}
