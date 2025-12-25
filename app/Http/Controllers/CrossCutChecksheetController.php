<?php

namespace App\Http\Controllers;

use App\Models\CrossCutChecksheet;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CrossCutChecksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = Item::orderBy('name')->get();
        $query = CrossCutChecksheet::with('item')->latest();

        $this->applyFilters($query, $request);

        $checksheets = $query->paginate(10)->withQueryString();

        return view('cross_cut.index', compact('checksheets', 'items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::orderBy('name')->get();
        return view('cross_cut.create', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'production_shift' => 'required|string|max:255',
            'qc_shift' => 'required|string|max:255',
            'production_datetime' => 'required|date',
            'qc_datetime' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'chemical_copper' => 'nullable|string|max:255',
            'chemical_nikel' => 'nullable|string|max:255',
            'chemical_eching' => 'nullable|string|max:255',
            'chemical_abu' => 'nullable|string|max:255',
            'position_remark_judgment' => 'required|in:OK,NG',
            'position_remark_no_lot' => 'required|string|max:255',
            'result_remark' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'operator_initials' => 'nullable|string|max:255',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cross_cut_images', 'public');
        }

        CrossCutChecksheet::create([
            'item_id' => $validated['item_id'],
            'production_shift' => $validated['production_shift'],
            'qc_shift' => $validated['qc_shift'],
            'production_datetime' => $validated['production_datetime'],
            'qc_datetime' => $validated['qc_datetime'],
            'image_path' => $imagePath,
            'chemical_copper' => $validated['chemical_copper'],
            'chemical_nikel' => $validated['chemical_nikel'],
            'chemical_eching' => $validated['chemical_eching'],
            'chemical_abu' => $validated['chemical_abu'],
            'position_remark_judgment' => $validated['position_remark_judgment'],
            'position_remark_no_lot' => $validated['position_remark_no_lot'],
            'result_remark' => $validated['result_remark'],
            'keterangan' => $validated['keterangan'],
            'cycle_time' => $validated['cycle_time'],
            'operator_initials' => $validated['operator_initials'],
        ]);

        return redirect()->route('cross_cut.create')->with('success', 'Cross Cut Checksheet created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);
        return response()->json([
            'image_url' => route('cross_cut.image', $checksheet->id),
            'item_name' => $checksheet->item->name,
            'qc_datetime' => $checksheet->qc_datetime,
        ]);
    }

    public function serveImage($id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);

        if (!Storage::disk('public')->exists($checksheet->image_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($checksheet->image_path);
    }

    public function edit($id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);
        $items = Item::orderBy('name')->get();
        return view('cross_cut.edit', compact('checksheet', 'items'));
    }

    public function update(Request $request, $id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'production_shift' => 'required|string|max:255',
            'qc_shift' => 'required|string|max:255',
            'production_datetime' => 'required|date',
            'qc_datetime' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'chemical_copper' => 'nullable|string|max:255',
            'chemical_nikel' => 'nullable|string|max:255',
            'chemical_eching' => 'nullable|string|max:255',
            'chemical_abu' => 'nullable|string|max:255',
            'position_remark_judgment' => 'required|in:OK,NG',
            'position_remark_no_lot' => 'required|string|max:255',
            'result_remark' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'operator_initials' => 'nullable|string|max:255',
        ]);

        $imagePath = $checksheet->image_path;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            // Store new image
            $imagePath = $request->file('image')->store('cross_cut_images', 'public');
        }

        $checksheet->update(array_merge($validated, ['image_path' => $imagePath]));

        return redirect()->route('cross_cut.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);

        // Delete the image from storage
        if ($checksheet->image_path && Storage::disk('public')->exists($checksheet->image_path)) {
            Storage::disk('public')->delete($checksheet->image_path);
        }

        $checksheet->delete();

        return redirect()->route('cross_cut.index')->with('success', 'Data berhasil dihapus.');
    }

    public function approve(Request $request, $id, $type)
    {
        try {
            $checksheet = CrossCutChecksheet::findOrFail($id);
            $user = auth()->user();
            
            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift') abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor') abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager') abort(403);
                if ($type == 'manager' && $user->role !== 'manager') abort(403);
            }

            if ($type == 'kashift') {
                $checksheet->kashift_qc = $user->name;
                $checksheet->kashift_approved_at = now();
            } elseif ($type == 'supervisor') {
                $checksheet->supervisor_qc = $user->name;
                $checksheet->supervisor_approved_at = now();
                $checksheet->approval_status = 'Approved'; 
            } elseif ($type == 'asst_manager') {
                $checksheet->asst_manager_qc = $user->name;
                $checksheet->asst_manager_approved_at = now();
            } elseif ($type == 'manager') {
                $checksheet->manager_qc = $user->name;
                $checksheet->manager_approved_at = now();
            }

            $checksheet->save();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }

        return redirect()->route('cross_cut.index')->with('success', 'Cross Cut Checksheet approved successfully.');
    }

    public function reject(Request $request, $id, $type)
    {
        try {
            $checksheet = CrossCutChecksheet::findOrFail($id);
            $user = auth()->user();

            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift') abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor') abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager') abort(403);
                if ($type == 'manager' && $user->role !== 'manager') abort(403);
            }
            
            if ($type == 'kashift') {
                $checksheet->kashift_qc = 'REJECTED';
                $checksheet->kashift_approved_at = now();
                $checksheet->approval_status = 'Rejected';
            } elseif ($type == 'supervisor') {
                $checksheet->supervisor_qc = 'REJECTED';
                $checksheet->supervisor_approved_at = now();
                $checksheet->approval_status = 'Rejected';
            } elseif ($type == 'asst_manager') {
                $checksheet->asst_manager_qc = 'REJECTED';
                $checksheet->asst_manager_approved_at = now();
                $checksheet->approval_status = 'Rejected';
            } elseif ($type == 'manager') {
                $checksheet->manager_qc = 'REJECTED';
                $checksheet->manager_approved_at = now();
                $checksheet->approval_status = 'Rejected';
            }

            $checksheet->save();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }

        return redirect()->route('cross_cut.index')->with('success', 'Cross Cut Checksheet rejected successfully.');
    }

    public function exportPdf(Request $request)
    {
        $query = CrossCutChecksheet::with('item')->latest();

        $this->applyFilters($query, $request);

        $checksheets = $query->get();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $pdf = \Pdf::loadView('cross_cut.pdf', compact('checksheets', 'startDate', 'endDate'));
        return $pdf->stream('laporan-cross-cut.pdf');
    }

    public function exportCsv(Request $request)
    {
        $query = CrossCutChecksheet::with('item')->latest();
        $this->applyFilters($query, $request);
        $checksheets = $query->get();

        $filename = "cross_cut_checksheets_" . date('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($checksheets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Tanggal Prod.', 'Tanggal QC', 'Shift Prod.', 'Shift QC', 'Item Part', 
                'Copper', 'Nikel', 'Eching', 'Abu', 'Posisi Judgment', 'Posisi No. Lot', 
                'Result Remark', 'Inisial', 'Keterangan', 'Approval Status'
            ]);

            foreach ($checksheets as $checksheet) {
                $status = 'Pending';
                if ($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED') {
                    $status = 'Approved';
                } elseif ($checksheet->kashift_qc === 'REJECTED' || $checksheet->supervisor_qc === 'REJECTED' || $checksheet->asst_manager_qc === 'REJECTED' || $checksheet->manager_qc === 'REJECTED') {
                    $status = 'Rejected';
                }

                fputcsv($file, [
                    $checksheet->id,
                    \Carbon\Carbon::parse($checksheet->production_datetime)->format('Y-m-d'),
                    \Carbon\Carbon::parse($checksheet->qc_datetime)->format('Y-m-d'),
                    $checksheet->production_shift,
                    $checksheet->qc_shift,
                    $checksheet->item->name,
                    $checksheet->chemical_copper ?? '-',
                    $checksheet->chemical_nikel ?? '-',
                    $checksheet->chemical_eching ?? '-',
                    $checksheet->chemical_abu ?? '-',
                    $checksheet->position_remark_judgment,
                    $checksheet->position_remark_no_lot,
                    $checksheet->result_remark ?? '-',
                    $checksheet->operator_initials ?? '-',
                    $checksheet->keterangan ?? '-',
                    $status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('start_date')) {
            $query->whereDate('qc_datetime', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('qc_datetime', '<=', $request->end_date);
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('approval_status')) {
            $status = $request->approval_status;
            if ($status == 'approved') {
                $query->whereNotNull('supervisor_qc')->where('supervisor_qc', '!=', 'REJECTED');
            } elseif ($status == 'rejected') {
                $query->where(function($q) {
                    $q->where('kashift_qc', 'REJECTED')
                      ->orWhere('supervisor_qc', 'REJECTED')
                      ->orWhere('asst_manager_qc', 'REJECTED')
                      ->orWhere('manager_qc', 'REJECTED');
                });
            } elseif ($status == 'pending') {
                $query->whereNull('kashift_qc');
            }
        }
    }
}
