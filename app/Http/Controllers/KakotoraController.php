<?php

namespace App\Http\Controllers;

use App\Models\Kakotora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ActivityLogger;

class KakotoraController extends Controller
{
    public function index(Request $request)
    {
        $plant = $request->query('plant') ?: 'jakarta';
        $query = Kakotora::query();

        if ($plant) {
            $query->where('plant', $plant);
        }

        // ponytail: Load all records client-side because the database is small (currently <500 rows).
        // If data size grows and performance is affected, upgrade to server-side DataTables.
        $kakotoras = $query->orderBy('date', 'desc')->get();
        
        // Get unique claims and statuses for dropdown filters
        $claims = Kakotora::where('plant', $plant)->whereNotNull('category_claim')->where('category_claim', '!=', '')->distinct()->pluck('category_claim');
        $statuses = Kakotora::where('plant', $plant)->whereNotNull('status')->where('status', '!=', '')->distinct()->pluck('status');

        return view('kakotora.index', compact('kakotoras', 'plant', 'claims', 'statuses'));
    }

    public function print(Request $request)
    {
        $plant = $request->query('plant') ?: 'jakarta';
        $query = Kakotora::query();
        $query->where('plant', $plant);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('no_reg', 'like', "%{$search}%")
                  ->orWhere('problem', 'like', "%{$search}%")
                  ->orWhere('cause', 'like', "%{$search}%")
                  ->orWhere('countermeasure', 'like', "%{$search}%")
                  ->orWhere('pic', 'like', "%{$search}%")
                  ->orWhere('section', 'like', "%{$search}%")
                  ->orWhere('process', 'like', "%{$search}%")
                  ->orWhere('family', 'like', "%{$search}%")
                  ->orWhere('category_claim', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_claim')) {
            $query->where('category_claim', $request->category_claim);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $kakotoras = $query->orderBy('date', 'desc')->get();
        
        return view('kakotora.print', compact('kakotoras', 'plant'));
    }

    public function create(Request $request)
    {
        $plant = $request->query('plant');
        return view('kakotora.create', compact('plant'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'no_reg' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'rev_model' => 'nullable|string|max:255',
            'family' => 'nullable|string|max:255',
            'category_nm_mp' => 'nullable|string|max:255',
            'category_claim' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'mould' => 'nullable|string|max:255',
            'owner_mould' => 'nullable|string|max:255',
            'similar_part' => 'nullable|string',
            'section' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'problem' => 'nullable|string',
            'cause' => 'nullable|string',
            'countermeasure' => 'nullable|string',
            'pic' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'defect_category' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'form_analysis' => 'nullable|file|mimes:pptx,xlsx,doc,docx,pdf|max:10240',
        ]);

        $data = $validated;
        $data['plant'] = $request->plant;

        if ($request->hasFile('form_analysis')) {
            $path = $request->file('form_analysis')->store('kakotora', 'public');
            $data['form_analysis_path'] = $path;
        }

        $kakotora = Kakotora::create($data);
        ActivityLogger::log('created', $kakotora, "Menambahkan data KAKOTORA baru: {$kakotora->no_reg}");

        return redirect()->route('kakotora.index', ['plant' => $request->plant])->with('success', 'Data KAKOTORA berhasil ditambahkan.');
    }

    public function show(Kakotora $kakotora)
    {
        return view('kakotora.show', compact('kakotora'));
    }

    public function edit(Kakotora $kakotora)
    {
        $plant = $kakotora->plant;
        return view('kakotora.edit', compact('kakotora', 'plant'));
    }

    public function update(Request $request, Kakotora $kakotora)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'no_reg' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'rev_model' => 'nullable|string|max:255',
            'family' => 'nullable|string|max:255',
            'category_nm_mp' => 'nullable|string|max:255',
            'category_claim' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'mould' => 'nullable|string|max:255',
            'owner_mould' => 'nullable|string|max:255',
            'similar_part' => 'nullable|string',
            'section' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'problem' => 'nullable|string',
            'cause' => 'nullable|string',
            'countermeasure' => 'nullable|string',
            'pic' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'defect_category' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'form_analysis' => 'nullable|file|mimes:pptx,xlsx,doc,docx,pdf|max:10240',
        ]);

        if ($request->hasFile('form_analysis')) {
            // Delete old file if exists
            if ($kakotora->form_analysis_path) {
                Storage::disk('public')->delete($kakotora->form_analysis_path);
            }
            $path = $request->file('form_analysis')->store('kakotora', 'public');
            $validated['form_analysis_path'] = $path;
        }

        $kakotora->update($validated);
        ActivityLogger::log('updated', $kakotora, "Memperbarui data KAKOTORA: {$kakotora->no_reg}");

        return redirect()->route('kakotora.index', ['plant' => $kakotora->plant])->with('success', 'Data KAKOTORA berhasil diperbarui.');
    }

    public function destroy(Kakotora $kakotora)
    {
        $plant = $kakotora->plant;
        if ($kakotora->form_analysis_path) {
            Storage::disk('public')->delete($kakotora->form_analysis_path);
        }
        $noReg = $kakotora->no_reg;
        $kakotora->delete();
        ActivityLogger::log('deleted', null, "Menghapus data KAKOTORA: {$noReg}");

        return redirect()->route('kakotora.index', ['plant' => $plant])->with('success', 'Data KAKOTORA berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $ids = $request->ids;
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.'], 400);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $records = Kakotora::whereIn('id', $ids)->get();
            $count = $records->count();

            foreach ($records as $record) {
                if ($record->form_analysis_path) {
                    Storage::disk('public')->delete($record->form_analysis_path);
                }
                ActivityLogger::log('deleted', null, "Menghapus massal data KAKOTORA: {$record->no_reg}");
                $record->delete();
            }

            \Illuminate\Support\Facades\DB::commit();

            session()->flash('success', "Berhasil menghapus {$count} data KAKOTORA.");

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data.",
                'redirect' => route('kakotora.index', ['plant' => request('plant')])
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}
