<?php

namespace App\Http\Controllers;

use App\Models\Kakotora;
use App\Models\KakotoraProblem;
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
        $dbClaims = Kakotora::where('plant', $plant)->whereNotNull('category_claim')->where('category_claim', '!=', '')->distinct()->pluck('category_claim');
        $claims = collect(['External', 'Internal'])->merge($dbClaims)->unique()->values();
        $dbStatuses = Kakotora::where('plant', $plant)->whereNotNull('status')->where('status', '!=', '')->distinct()->pluck('status');
        $statuses = collect(['Open', 'Closed', 'On Progress'])->merge($dbStatuses)->unique()->values();
        $problems = KakotoraProblem::where('plant', $plant)->orderBy('name')->pluck('name');
        
        $similarParts = \App\Models\Item::withoutGlobalScope('plant')->select('name', 'part_number')->distinct()->orderBy('name')->get();

        return view('kakotora.index', compact('kakotoras', 'plant', 'claims', 'statuses', 'problems', 'similarParts'));
    }

    public function print(Request $request)
    {
        $plant = $request->query('plant') ?: 'jakarta';
        $query = Kakotora::query();
        $query->where('plant', $plant);

        if ($request->filled('search')) {
            $rawSearch = strtolower(trim((string) $request->search));
            $stops = ['tolong', 'keluarkan', 'semua', 'di', 'pada', 'proses', 'nah', 'langsung', 'nya', 'tampilkan', 'cari', 'carikan', 'yang', 'ada', 'dan', 'atau', 'buatkan', 'buat', 'data', 'problem', 'masalah', 'part', 'kakotora', 'database', 'dari', 'ke', 'untuk'];
            $keywords = array_values(array_filter(preg_split('/[\s,.]+/', $rawSearch), fn($w) => $w !== '' && !in_array($w, $stops)));
            if (empty($keywords)) {
                $keywords = [$rawSearch];
            }

            foreach ($keywords as $kw) {
                $query->where(function ($q) use ($kw) {
                    $q->where('part_name', 'like', "%{$kw}%")
                      ->orWhere('part_number', 'like', "%{$kw}%")
                      ->orWhere('model', 'like', "%{$kw}%")
                      ->orWhere('no_reg', 'like', "%{$kw}%")
                      ->orWhere('problem', 'like', "%{$kw}%")
                      ->orWhere('cause', 'like', "%{$kw}%")
                      ->orWhere('countermeasure', 'like', "%{$kw}%")
                      ->orWhere('similar_part', 'like', "%{$kw}%")
                      ->orWhere('mould', 'like', "%{$kw}%")
                      ->orWhere('owner_mould', 'like', "%{$kw}%")
                      ->orWhere('supplier', 'like', "%{$kw}%")
                      ->orWhere('defect_category', 'like', "%{$kw}%")
                      ->orWhere('pic', 'like', "%{$kw}%")
                      ->orWhere('section', 'like', "%{$kw}%")
                      ->orWhere('process', 'like', "%{$kw}%")
                      ->orWhere('family', 'like', "%{$kw}%")
                      ->orWhere('category_nm_mp', 'like', "%{$kw}%")
                      ->orWhere('category_claim', 'like', "%{$kw}%")
                      ->orWhere('status', 'like', "%{$kw}%")
                      ->orWhere('remarks', 'like', "%{$kw}%");
                });
            }
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
            'issue_date' => 'nullable|string',
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
            'form_analysis' => 'required|file|mimes:pptx,xlsx,doc,docx,pdf|max:10240',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $validated;
        $data['plant'] = $request->plant;

        if ($request->hasFile('form_analysis')) {
            $file = $request->file('form_analysis');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('kakotora', $filename, 'public');
            $data['form_analysis_path'] = $path;
        }

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_foto_' . $file->getClientOriginalName();
            $path = $file->storeAs('kakotora', $filename, 'public');
            $data['foto_path'] = $path;
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
            'issue_date' => 'nullable|string',
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
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($request->has('delete_file') && $request->delete_file == '1') {
            if ($kakotora->form_analysis_path) {
                Storage::disk('public')->delete($kakotora->form_analysis_path);
                $validated['form_analysis_path'] = null;
            }
        }
        
        if ($request->hasFile('form_analysis')) {
            // Delete old file if exists
            if ($kakotora->form_analysis_path) {
                Storage::disk('public')->delete($kakotora->form_analysis_path);
            }
            $file = $request->file('form_analysis');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('kakotora', $filename, 'public');
            $validated['form_analysis_path'] = $path;
        }

        if ($request->has('delete_foto') && $request->delete_foto == '1') {
            if ($kakotora->foto_path) {
                Storage::disk('public')->delete($kakotora->foto_path);
                $validated['foto_path'] = null;
            }
        }
        
        if ($request->hasFile('foto')) {
            // Delete old file if exists
            if ($kakotora->foto_path) {
                Storage::disk('public')->delete($kakotora->foto_path);
            }
            $file = $request->file('foto');
            $filename = time() . '_foto_' . $file->getClientOriginalName();
            $path = $file->storeAs('kakotora', $filename, 'public');
            $validated['foto_path'] = $path;
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
        if ($kakotora->foto_path) {
            Storage::disk('public')->delete($kakotora->foto_path);
        }
        $noReg = $kakotora->no_reg;
        $kakotora->delete();
        ActivityLogger::log('deleted', null, "Menghapus data KAKOTORA: {$noReg}");

        return redirect()->route('kakotora.index', ['plant' => $plant])->with('success', 'Data KAKOTORA berhasil dihapus.');
    }

    public function deletePdf($id)
    {
        $kakotora = Kakotora::findOrFail($id);

        if ($kakotora->form_analysis_path) {
            Storage::disk('public')->delete($kakotora->form_analysis_path);
            $kakotora->update(['form_analysis_path' => null]);
            ActivityLogger::log('updated', $kakotora, "Menghapus file form analysis KAKOTORA: {$kakotora->no_reg}");
            
            return response()->json(['success' => true, 'message' => 'File berhasil dihapus.']);
        }

        return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 404);
    }

    public function deleteFoto($id)
    {
        $kakotora = Kakotora::findOrFail($id);

        if ($kakotora->foto_path) {
            Storage::disk('public')->delete($kakotora->foto_path);
            $kakotora->update(['foto_path' => null]);
            ActivityLogger::log('updated', $kakotora, "Menghapus file foto KAKOTORA: {$kakotora->no_reg}");
            
            return response()->json(['success' => true, 'message' => 'Foto berhasil dihapus.']);
        }

        return response()->json(['success' => false, 'message' => 'Foto tidak ditemukan.'], 404);
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
                if ($record->foto_path) {
                    Storage::disk('public')->delete($record->foto_path);
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

    public function addProblem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plant' => 'required|string|max:255',
        ]);

        $exists = KakotoraProblem::where('plant', $request->plant)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Problem sudah ada!']);
        }

        $problem = KakotoraProblem::create([
            'plant' => $request->plant,
            'name' => $request->name,
        ]);

        return response()->json(['success' => true, 'problem' => $problem->name]);
    }

    public function deleteProblem(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'plant' => 'required|string',
        ]);

        KakotoraProblem::where('plant', $request->plant)
            ->where('name', $request->name)
            ->delete();

        return response()->json(['success' => true]);
    }
}
