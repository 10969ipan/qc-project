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

        $claims = $query->paginate(15)->withQueryString();

        // Get available years for filter
        $years = CustomerClaim::selectRaw('DISTINCT year')->orderBy('year', 'desc')->pluck('year');

        return view('customer_claims.index', compact('claims', 'years'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return redirect()->route('admin.customer-claims.index', ['plant' => $request->plant])
                ->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        // Determine plant context
        $plantIdentifier = $request->get('plant');
        $plantId = null;

        if ($plantIdentifier) {
            $plantId = Plant::resolveId($plantIdentifier);
        } elseif (auth()->user()->plant_id) {
            $plantId = auth()->user()->plant_id;
        }

        $plants = Plant::orderBy('name')->get();
        $currentYear = date('Y');
        $currentPlant = $request->get('plant', auth()->user()->plant);

        return view('customer_claims.create', compact('plants', 'currentYear', 'currentPlant', 'plantId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return redirect()->route('admin.customer-claims.index', ['plant' => $request->plant])
                ->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        $validated = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:0|max:12', // Allowed 0 for yearly
            'ppm_value' => 'required|numeric|min:0',
            'target_value' => 'required|numeric|min:0',
        ], [
            'plant_id.required' => 'Plant harus dipilih',
            'year.required' => 'Tahun harus diisi',
            'month.required' => 'Bulan harus dipilih',
            'ppm_value.required' => 'Nilai PPM harus diisi',
            'target_value.required' => 'Target harus diisi',
        ]);

        $validated['created_by'] = auth()->id();

        try {
            CustomerClaim::create($validated);
            $redirectPlant = $request->plant ?: Plant::where('id', $validated['plant_id'])->value('code');
            return redirect()->route('admin.customer-claims.index', ['plant' => $redirectPlant])
                ->with('success', 'Data claim customer berhasil ditambahkan.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Data untuk plant, tahun, dan bulan ini sudah ada. Silakan edit data yang sudah ada.');
            }
            throw $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerClaim $customerClaim)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return redirect()->route('admin.customer-claims.index', ['plant' => $customerClaim->plant->code])
                ->with('error', 'Anda tidak memiliki akses untuk mengedit data.');
        }

        // For admin to edit claims from any plant
        if (auth()->user()->role === 'admin') {
            $customerClaim = CustomerClaim::withoutGlobalScope('plant')->findOrFail($customerClaim->id);
        }

        $plants = Plant::orderBy('name')->get();

        return view('customer_claims.edit', compact('customerClaim', 'plants'));
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

        $validated = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:0|max:12', // Allowed 0 for yearly
            'ppm_value' => 'required|numeric|min:0',
            'target_value' => 'required|numeric|min:0',
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
    public function yearly(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return redirect()->route('admin.customer-claims.index', ['plant' => $request->plant])
                ->with('error', 'Anda tidak memiliki akses untuk input data tahunan.');
        }

        $year = $request->get('year', date('Y'));
        $plantIdentifier = $request->get('plant');
        $plantId = null;

        if ($plantIdentifier) {
            $plantId = Plant::resolveId($plantIdentifier);
        } elseif (auth()->user()->plant_id) {
            $plantId = auth()->user()->plant_id;
        }

        $plants = Plant::orderBy('name')->get();
        $currentPlant = Plant::find($plantId);
        $currentYear = (int) date('Y');

        if ($year < $currentYear) {
            // Historical year: fetch month = 0 data
            $existingData = CustomerClaim::withoutGlobalScope('plant')
                ->where('plant_id', $plantId)
                ->where('year', $year)
                ->where('month', 0)
                ->first();

            return view('customer_claims.yearly', compact('plants', 'year', 'plantId', 'currentPlant', 'existingData', 'currentYear'));
        }

        // Current/Future year: fetch existing data for the year to pre-fill
        $existingData = CustomerClaim::withoutGlobalScope('plant')
            ->where('plant_id', $plantId)
            ->where('year', $year)
            ->get()
            ->keyBy('month');

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

        return view('customer_claims.yearly', compact('plants', 'year', 'plantId', 'currentPlant', 'existingData', 'months', 'currentYear'));
    }

    /**
     * Store bulk yearly data.
     */
    public function storeYearly(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return redirect()->route('admin.customer-claims.index', ['plant' => $request->plant])
                ->with('error', 'Anda tidak memiliki akses untuk menyimpan data tahunan.');
        }

        $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'year' => 'required|integer|min:2020|max:2100',
            'data.*.ppm_value' => 'nullable|numeric|min:0',
            'data.*.target_value' => 'nullable|numeric|min:0',
            'ppm_value' => 'nullable|numeric|min:0',
            'target_value' => 'nullable|numeric|min:0',
        ]);

        $plantId = $request->plant_id;
        $year = $request->year;
        // process annual summary (month = 0) if provided
        $ppmValue = $request->input('ppm_value');
        $targetValue = $request->input('target_value');

        if (($ppmValue !== null && $ppmValue !== '') || ($targetValue !== null && $targetValue !== '')) {
            CustomerClaim::withoutGlobalScope('plant')->updateOrCreate(
                [
                    'plant_id' => $plantId,
                    'year' => $year,
                    'month' => 0,
                ],
                [
                    'ppm_value' => $ppmValue ?? 0,
                    'target_value' => $targetValue ?? 0,
                    'created_by' => auth()->id(),
                ]
            );
        }

        // process monthly data if provided
        $bulkData = $request->data;
        if ($bulkData) {
            foreach ($bulkData as $month => $values) {
                if (
                    ($values['ppm_value'] !== null && $values['ppm_value'] !== '') ||
                    ($values['target_value'] !== null && $values['target_value'] !== '')
                ) {
                    CustomerClaim::withoutGlobalScope('plant')->updateOrCreate(
                        [
                            'plant_id' => $plantId,
                            'year' => $year,
                            'month' => $month,
                        ],
                        [
                            'ppm_value' => $values['ppm_value'] ?? 0,
                            'target_value' => $values['target_value'] ?? 0,
                            'created_by' => auth()->id(),
                        ]
                    );
                }
            }
        }

        $redirectPlant = $request->plant ?: Plant::where('id', $plantId)->value('code');

        return redirect()->route('admin.customer-claims.index', ['plant' => $redirectPlant, 'year' => $year])
            ->with('success', "Data claim customer tahun $year berhasil diperbarui.");
    }
}
