<?php

namespace App\Http\Controllers;

use App\Models\CustomerClaimRecord;
use App\Models\Plant;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;
use Carbon\Carbon;

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

        // Filter by customer
        if ($request->filled('customer')) {
            $query->where('customer', $request->customer);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('tanggal_claim', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal_claim', '<=', $request->end_date);
        }

        // Smart Search Filter
        if ($request->filled('smart_filter')) {
            $query->where('id', $request->smart_filter);
        }

        // Filter by claim type
        if ($request->filled('claim_type')) {
            $query->where('claim_type', $request->claim_type);
        }

        // Global Search
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('customer', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('plant_up_customer', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('no_report', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('nama_part', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('problem', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('kategori_defect', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('kategori_penyimpangan', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('action_taken', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('feedback', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('status_feedback', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('status_cm', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('monitoring', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('evaluasi', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('initial_operator', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('initial_inspektor', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $records = $query->paginate(15)->withQueryString();

        // Data for Smart Search
        $allRecords = CustomerClaimRecord::select('id', 'nama_part', 'customer', 'problem', 'monitoring_status')
            ->orderBy('nama_part')
            ->get();

        $plants = Plant::orderBy('name')->get();
        $customers = CustomerClaimRecord::distinct()->orderBy('customer')->pluck('customer');
        $plantId = Plant::resolveId($request->plant) ?: (auth()->check() ? auth()->user()->plant_id : null);

        return view('customer_claim_records.index', compact('records', 'plants', 'plantId', 'allRecords', 'customers'));
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

        if ($request->filled('customer')) {
            $query->where('customer', $request->customer);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_claim', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_claim', '<=', $request->end_date);
        }

        // Smart Search Filter
        if ($request->filled('smart_filter')) {
            $query->where('id', $request->smart_filter);
        }

        // Filter by claim type
        if ($request->filled('claim_type')) {
            $query->where('claim_type', $request->claim_type);
        }

        // Global Search

        if ($request->has('page')) {
            $records = $query->paginate(15)->getCollection();
        } else {
            $records = $query->limit(10)->get();
        }

        // Resolve plant name for display
        $plantName = 'ALL PLANTS';
        if ($request->filled('plant')) {
            $plantId = Plant::resolveId($request->plant);
            if ($plantId) {
                $plant = Plant::find($plantId);
                if ($plant) {
                    $plantName = strtoupper($plant->name);
                }
            }
        }

        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';

        $pdf = Pdf::loadView('customer_claim_records.pdf', compact('records', 'plantName', 'request', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        $filename = 'List_Claim_Customer_' . str_replace(' ', '_', $plantName) . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Display a minimalist view for browser printing.
     */
    public function printView(Request $request)
    {
        $query = CustomerClaimRecord::with(['plant', 'creator'])
            ->orderBy('tanggal_claim', 'desc');

        // Reuse filtering logic
        if ($request->filled('plant')) {
            $plantId = Plant::resolveId($request->plant);
            if ($plantId) {
                $query->where('plant_id', $plantId);
            }
        }

        if ($request->filled('customer')) {
            $query->where('customer', $request->customer);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_claim', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_claim', '<=', $request->end_date);
        }

        if ($request->filled('smart_filter')) {
            $query->where('id', $request->smart_filter);
        }

        // Filter by claim type
        if ($request->filled('claim_type')) {
            $query->where('claim_type', $request->claim_type);
        }

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('customer', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('no_report', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('nama_part', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('problem', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $records = $query->get();

        // Plant info for header
        $plantName = 'ALL PLANTS';
        $plantCode = 'karawang';
        if ($request->filled('plant')) {
            $plantId = Plant::resolveId($request->plant);
            $plant = Plant::find($plantId);
            if ($plant) {
                $plantName = $plant->name;
                $plantCode = strtolower($plant->code);
            }
        }

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('d/m/Y') : '-';
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->format('d/m/Y') : '-';

        return view('customer_claim_records.print', compact('records', 'plantName', 'plantCode', 'startDate', 'endDate'));
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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,xlsx,xls,pptx,docx|max:10240', // 10MB max per file
        ]);

        $data = $request->except(['attachments']);
        $data['attachments'] = []; // Default empty array
        $data['created_by'] = auth()->id();

        if ($request->hasFile('attachments')) {
            $paths = [];
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs('attachments/claim_customer', $filename, 'public');
                $paths[] = $path;
            }
            $data['attachments'] = $paths;
        }

        // Backend fallback for evaluasi date if not provided
        if (empty($data['evaluasi']) && !empty($data['tanggal_claim'])) {
            $data['evaluasi'] = date('d-m-Y', strtotime($data['tanggal_claim'] . ' +6 months'));
        }

        $record = CustomerClaimRecord::create($data);
        ActivityLogger::log('created', $record, "Menambahkan record claim customer: {$record->customer} - {$record->nama_part}");

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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,xlsx,xls,pptx,docx|max:10240',
        ]);

        $data = $request->except(['attachments']);

        if ($request->hasFile('attachments')) {
            $paths = is_array($customerClaimRecord->attachments) ? $customerClaimRecord->attachments : [];
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs('attachments/claim_customer', $filename, 'public');
                $paths[] = $path;
            }
            $data['attachments'] = $paths;
        }

        $customerClaimRecord->update($data);
        ActivityLogger::log('updated', $customerClaimRecord, "Memperbarui record claim customer: {$customerClaimRecord->customer} - {$customerClaimRecord->nama_part}");

        return redirect()->back()->with('success', 'Data claim customer berhasil diperbarui.');
    }

    /**
     * Delete a specific attachment.
     */
    public function deleteAttachment($id, $index)
    {
        $record = CustomerClaimRecord::findOrFail($id);
        $attachments = $record->attachments;

        if (isset($attachments[$index])) {
            $path = $attachments[$index];
            if (\Storage::disk('public')->exists($path)) {
                \Storage::disk('public')->delete($path);
            }
            unset($attachments[$index]);
            $record->attachments = array_values($attachments); // Re-index
            $record->save();
            ActivityLogger::log('deleted', $record, "Menghapus lampiran index {$index} pada record claim customer: {$record->customer}");

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'File not found.'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerClaimRecord $customerClaimRecord)
    {
        // Delete all associated files
        if (is_array($customerClaimRecord->attachments)) {
            foreach ($customerClaimRecord->attachments as $path) {
                if (\Storage::disk('public')->exists($path)) {
                    \Storage::disk('public')->delete($path);
                }
            }
        }

        $recordInfo = "{$customerClaimRecord->customer} - {$customerClaimRecord->nama_part}";
        $customerClaimRecord->delete();
        ActivityLogger::log('deleted', null, "Menghapus record claim customer: {$recordInfo}");

        return redirect()->back()->with('success', 'Data claim customer berhasil dihapus.');
    }
}
