<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\InProcessChecksheet;
use App\Models\CrossCutChecksheet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalysisController extends Controller
{
    protected $analysisService;

    public function __construct(\App\Services\AnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    public function monthlyNgSubAssy(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date']);

        if (auth()->user()->role === 'inspector') {
            $filters['plant'] = auth()->user()->plant;
            $request->merge(['plant' => $filters['plant']]);
        }

        $analysisData = $this->analysisService->getMonthlyAnalysis('sub_assy', $filters);
        $plant = $filters['plant'] ?? null;

        return view('analysis.monthly_ng', array_merge($analysisData, compact('plant')));
    }

    public function monthlyNgInProcess(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date']);

        if (auth()->user()->role === 'inspector') {
            $filters['plant'] = auth()->user()->plant;
            $request->merge(['plant' => $filters['plant']]);
        }

        $analysisData = $this->analysisService->getMonthlyAnalysis('in_process', $filters);
        $plant = $filters['plant'] ?? null;

        return view('analysis.monthly_ng_in_process', array_merge($analysisData, compact('plant')));
    }

    public function monthlyNgCrossCut(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date']);

        if (auth()->user()->role === 'inspector') {
            $filters['plant'] = auth()->user()->plant;
            $request->merge(['plant' => $filters['plant']]);
        }

        $analysisData = $this->analysisService->getMonthlyAnalysis('cross_cut', $filters);
        $plant = $filters['plant'] ?? null;

        return view('analysis.monthly_ng_cross_cut', array_merge($analysisData, compact('plant')));
    }
}
