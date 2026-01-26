<?php

namespace App\Http\Controllers;

use App\Models\CustomerClaimRecord;
use App\Models\Plant;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CustomerClaimRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CustomerClaimRecord::with(['plant', 'creator'])
            ->orderBy('tanggal_claim', 'desc');

        // Filter by plant
        if ($request->filled('plant')) {
            $plantId = Plant::resolveId($request->plant);
            if ($plantId) {
                $query->where('plant_id', $plantId);
            }
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('tanggal_claim', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal_claim', '<=', $request->end_date);
        }

        // Filter by customer
        if ($request->filled('customer')) {
            $query->where('customer', 'LIKE', '%' . $request->customer . '%');
        }

        $records = $query->paginate(15)->withQueryString();

        $plants = Plant::orderBy('name')->get();
        $plantId = Plant::resolveId($request->plant) ?: (auth()->check() ? auth()->user()->plant_id : null);

        return view('customer_claim_records.index', compact('records', 'plants', 'plantId'));
    }

    /**
     * Export records to PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = CustomerClaimRecord::with(['plant', 'creator'])
            ->orderBy('tanggal_claim', 'desc');

        // Reuse filtering logic from index
        if ($request->filled('plant')) {
            $plantId = Plant::resolveId($request->plant);
            if ($plantId) {
                $query->where('plant_id', $plantId);
            }
        }

        if ($request->filled('start_date')) {
            $query->where('tanggal_claim', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal_claim', '<=', $request->end_date);
        }

        if ($request->filled('customer')) {
            $query->where('customer', 'LIKE', '%' . $request->customer . '%');
        }

        $records = $query->get();
        $plant = $request->filled('plant') ? Plant::where('code', $request->plant)->first() : null;
        $plantName = $plant ? strtoupper($plant->name) : 'ALL PLANTS';

        $pdf = Pdf::loadView('customer_claim_records.pdf', compact('records', 'plantName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('List_Claim_Customer_' . ($plantName ? str_replace(' ', '_', $plantName) : 'All') . '_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_claim' => 'required|date',
            'customer' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',
            'nama_part' => 'required|string|max:255',
            'problem' => 'required|string',
            'qty' => 'required|integer|min:0',
            // Add other fields as optional or required based on business needs
        ]);

        $allData = $request->all();
        $allData['created_by'] = auth()->id();

        CustomerClaimRecord::create($allData);

        return redirect()->back()->with('success', 'Data claim customer berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerClaimRecord $customerClaimRecord)
    {
        $validated = $request->validate([
            'tanggal_claim' => 'required|date',
            'customer' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',
            'nama_part' => 'required|string|max:255',
            'problem' => 'required|string',
            'qty' => 'required|integer|min:0',
        ]);

        $customerClaimRecord->update($request->all());

        return redirect()->back()->with('success', 'Data claim customer berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerClaimRecord $customerClaimRecord)
    {
        $customerClaimRecord->delete();

        return redirect()->back()->with('success', 'Data claim customer berhasil dihapus.');
    }
}
