<?php

namespace App\Http\Controllers;

use App\Models\CustomerClaim;
use App\Models\Plant;
use Illuminate\Http\Request;

class CustomerClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Managers and Asst Managers can only view
        $isRestricted = in_array(auth()->user()->role, ['manager', 'asst_manager']);

        $query = CustomerClaim::with(['plant', 'creator'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        // Default to current year if not set
        if (!$request->filled('year')) {
            $request->merge(['year' => date('Y')]);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter by plant
        if ($request->filled('plant')) {
            $plantId = Plant::resolveId($request->plant);
            if ($plantId) {
                $query->where('plant_id', $plantId);
            }
        }

        $claims = $query->get();

        // Get available years for filter
        $years = CustomerClaim::selectRaw('DISTINCT year')->orderBy('year', 'desc')->pluck('year');
        $plants = Plant::orderBy('name')->get();
        $currentYear = (int) date('Y');
        $plantId = Plant::resolveId($request->plant) ?: (auth()->check() ? auth()->user()->plant_id : null);

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return view('customer_claims.index', compact('claims', 'years', 'plants', 'currentYear', 'plantId', 'months'));
    }



    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage (Bulk / Yearly Input).
     */
    public function store(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return redirect()->route('admin.customer-claims.index', ['plant' => $request->plant])
                ->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'year' => 'required|integer|min:2020|max:2100',
            'data.*.ppm_value' => 'nullable|numeric|min:0',
            'data.*.target_value' => 'nullable|numeric|min:0',
            'data.*.total_claims' => 'nullable|numeric|min:0',
            'data.*.total_claim_pcs' => 'nullable|numeric|min:0',
            'data.*.total_delivery' => 'nullable|numeric|min:0',
            // Validation for annual summary (Month 0)
            'ppm_value' => 'nullable|numeric|min:0',
            'target_value' => 'nullable|numeric|min:0',
            'total_claims' => 'nullable|numeric|min:0',
            'total_claim_pcs' => 'nullable|numeric|min:0',
            'total_delivery' => 'nullable|numeric|min:0',
        ]);

        $plantId = $request->plant_id;
        $year = $request->year;

        // Process annual summary (month = 0)
        $ppmValue = $request->input('ppm_value');
        $targetValue = $request->input('target_value');
        $totalClaims = $request->input('total_claims');
        $totalClaimPcs = $request->input('total_claim_pcs');
        $totalDelivery = $request->input('total_delivery');

        if (
            ($ppmValue !== null && $ppmValue !== '') ||
            ($targetValue !== null && $targetValue !== '') ||
            ($totalClaims !== null && $totalClaims !== '') ||
            ($totalClaimPcs !== null && $totalClaimPcs !== '') ||
            ($totalDelivery !== null && $totalDelivery !== '')
        ) {
            CustomerClaim::withoutGlobalScope('plant')->updateOrCreate(
                [
                    'plant_id' => $plantId,
                    'year' => $year,
                    'month' => 0,
                ],
                [
                    'ppm_value' => $ppmValue,
                    'target_value' => $targetValue,
                    'total_claims' => $totalClaims,
                    'total_claim_pcs' => $totalClaimPcs,
                    'total_delivery' => $totalDelivery,
                    'created_by' => auth()->id(),
                ]
            );
        }

        // Process monthly data (1-12)
        $bulkData = $request->data;
        if ($bulkData) {
            foreach ($bulkData as $month => $values) {
                if (
                    ($values['ppm_value'] !== null && $values['ppm_value'] !== '') ||
                    ($values['target_value'] !== null && $values['target_value'] !== '') ||
                    ($values['total_claims'] !== null && $values['total_claims'] !== '') ||
                    ($values['total_claim_pcs'] !== null && $values['total_claim_pcs'] !== '') ||
                    ($values['total_delivery'] !== null && $values['total_delivery'] !== '')
                ) {
                    CustomerClaim::withoutGlobalScope('plant')->updateOrCreate(
                        [
                            'plant_id' => $plantId,
                            'year' => $year,
                            'month' => $month,
                        ],
                        [
                            'ppm_value' => $values['ppm_value'],
                            'target_value' => $values['target_value'],
                            'total_claims' => $values['total_claims'],
                            'total_claim_pcs' => $values['total_claim_pcs'],
                            'total_delivery' => $values['total_delivery'],
                            'created_by' => auth()->id(),
                        ]
                    );
                }
            }
        }

        $queryParams = [
            'plant' => $request->plant,
            'year' => $year,
        ];
        $queryParams = array_filter($queryParams);

        return redirect()->route('admin.customer-claims.index', $queryParams)
            ->with('success', "Data claim customer tahun $year berhasil disimpan.");
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerClaim $customerClaim)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return redirect()->route('admin.customer-claims.index', ['plant' => $customerClaim->plant->code])
                ->with('error', 'Anda tidak memiliki akses untuk mengupdate data.');
        }

        $isTotalPlant = false;
        if ($request->filled('plant_id')) {
            $isTotalPlant = Plant::where('id', $request->plant_id)->where('code', 'total')->exists();
        }

        $validated = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:0|max:12', // Allowed 0 for yearly
            'ppm_value' => 'nullable|numeric|min:0',
            'target_value' => 'nullable|numeric|min:0',
            'total_claims' => 'nullable|numeric|min:0',
            'total_claim_pcs' => 'nullable|numeric|min:0',
            'total_delivery' => 'nullable|numeric|min:0',
        ]);



        try {
            $customerClaim->update($validated);

            $queryParams = [
                'plant' => $request->input('plant') ?: $customerClaim->plant->code, // Fallback to plant code
                'year' => $request->input('filter_year'),
                'month' => $request->input('filter_month'),
            ];

            $queryParams = array_filter($queryParams, function ($value) {
                return !is_null($value) && $value !== '';
            });

            return redirect()->route('admin.customer-claims.index', $queryParams)
                ->with('success', 'Data claim customer berhasil diperbarui.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Data untuk plant, tahun, dan bulan ini sudah ada.');
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CustomerClaim $customerClaim)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return redirect()->route('admin.customer-claims.index', ['plant' => $customerClaim->plant->code])
                ->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $customerClaim->delete();

        $queryParams = [
            'plant' => $request->input('plant'),
            'year' => $request->input('year'),
            'month' => $request->input('month'),
        ];

        $queryParams = array_filter($queryParams, function ($value) {
            return !is_null($value) && $value !== '';
        });

        return redirect()->route('admin.customer-claims.index', $queryParams)
            ->with('success', 'Data claim customer berhasil dihapus.');
    }
    /**
     * Show the form for bulk yearly input.
     */

}
