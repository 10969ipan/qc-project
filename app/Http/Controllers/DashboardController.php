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

        $data = $this->dashboardService->getDashboardData($month, $year);

        // Customer Claim Data (Ter-cache 24 jam)
        $claimYear = $request->get('year', 'combined');
        $claimData = \Illuminate\Support\Facades\Cache::remember("dashboard_customer_claim_{$claimYear}", 86400, function () use ($claimYear) {
            return $this->dashboardService->getCustomerClaimData($claimYear);
        });
        $data['claimData'] = $claimData;
        $data['selectedMonth'] = (int) $month;
        $data['selectedYear'] = $year;

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
     * TV Signage Dashboard
     */
    public function tvIndex(Request $request)
    {
        // Force Karawang for TV Dashboard unless explicitly overridden by URL
        if (!$request->has('plant')) {
            $request->merge(['plant' => 'karawang']);
        }

        $data = $this->dashboardService->getDashboardData();

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('dashboard.tv', $data);
    }

    /**
     * TV Dashboard: Lightweight real-time JSON polling endpoint
     * Returns only the minimal data needed to update station cards (no full page render)
     */
    public function tvLiveData(Request $request)
    {
        if (!$request->has('plant')) {
            $request->merge(['plant' => 'karawang']);
        }

        $data = $this->dashboardService->getLiveDashboardData();

        return response()->json($data)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * TV Signage: NG Defect aggregation for Slide 3 panel
     */
    public function tvDefects()
    {
        return cache()->remember('tv_defects_karawang', 300, function () {
            $karawangId = \App\Models\Plant::resolveId('karawang');
            $startDate  = now()->subDays(30)->toDateString();

            $tables = [
                'sub_assy'   => 'sub_assy_checksheets',
                'in_process' => 'in_process_checksheets',
            ];

            $result = [];

            foreach ($tables as $key => $table) {
                $rows = \Illuminate\Support\Facades\DB::table($table)
                    ->where('plant_id', $karawangId)
                    ->where('date', '>=', $startDate)
                    ->whereNotNull('defects')
                    ->whereRaw("defects != '[]' AND defects != 'null' AND defects != '\"[]\"'")
                    ->select('defects')
                    ->get();

                $totals = [];
                $grandTotal = 0;

                foreach ($rows as $row) {
                    $raw = $row->defects;
                    $defects = is_string($raw) ? json_decode($raw, true) : $raw;
                    if (!is_array($defects)) continue;
                    foreach ($defects as $d) {
                        $type = strtoupper(trim($d['type'] ?? 'UNKNOWN'));
                        $qty  = (int) ($d['qty'] ?? 0);
                        if ($qty <= 0) continue;
                        $totals[$type] = ($totals[$type] ?? 0) + $qty;
                        $grandTotal += $qty;
                    }
                }

                arsort($totals);
                $top = array_slice($totals, 0, 8, true);

                $result[$key] = [
                    'total' => $grandTotal,
                    'items' => array_map(fn($type, $qty) => [
                        'type' => $type,
                        'qty'  => $qty,
                        'pct'  => $grandTotal > 0 ? round($qty / $grandTotal * 100, 1) : 0,
                    ], array_keys($top), array_values($top)),
                ];
            }

            return response()->json($result);
        });
    }
}
