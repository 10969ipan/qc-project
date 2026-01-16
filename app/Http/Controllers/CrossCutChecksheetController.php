<?php

namespace App\Http\Controllers;

use App\Models\CrossCutChecksheet;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CrossCutChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;

    protected function getModelClass()
    {
        return CrossCutChecksheet::class;
    }

    protected function getApprovalMapping($type)
    {
        $mappings = [
            'karu_qc' => ['field' => 'karu_qc', 'time' => 'karu_qc_approved_at', 'label' => 'Karu QC'],
            'kashift_plating' => ['field' => 'kashift_plating', 'time' => 'kashift_plating_approved_at', 'label' => 'Kashift Plating'],
            'supervisor_plating' => ['field' => 'supervisor_plating', 'time' => 'supervisor_plating_approved_at', 'label' => 'Supervisor Plating'],
            'supervisor' => ['field' => 'supervisor_qc', 'time' => 'supervisor_approved_at', 'label' => 'SPV Quality'], // Mapped 'supervisor' to 'supervisor_qc'
            'manager_plating' => ['field' => 'manager_plating', 'time' => 'manager_plating_approved_at', 'label' => 'Manager Plating'],
            'manager' => ['field' => 'manager_qc', 'time' => 'manager_approved_at', 'label' => 'Manager QC'],
        ];
        return $mappings[$type] ?? null;
    }
    /**
     * Menampilkan daftar data (resource).
     */
    public function index(Request $request)
    {
        $items = Item::orderBy('name')->get();
        $query = CrossCutChecksheet::with('item')->orderBy('qc_datetime', 'desc')->orderBy('created_at', 'desc');

        // Admin can switch plants via query parameter, others are locked via HasPlantFilter
        if (auth()->user()->role === 'admin' && $request->has('plant')) {
            $query->withoutGlobalScope('plant')->where('plant', $request->get('plant'));
        }

        // For inspector, we explicitly override the request plant to their own plant for UI consistency
        if (auth()->user()->role === 'inspector') {
            $request->merge(['plant' => auth()->user()->plant]);
        }

        $this->applyFilters($query, $request);

        $checksheets = $query->paginate(10)->withQueryString();

        return view('cross_cut.index', compact('checksheets', 'items'));
    }

    /**
     * Menampilkan form untuk membuat data baru.
     */
    public function create(Request $request)
    {
        $query = Item::byCategory(['Cross Cut Plating', 'Cross Cut Painting'])->orderBy('name');

        // Filter items based on plant context
        if (auth()->user()->role === 'admin' && $request->has('plant')) {
            $query->where('plant', $request->query('plant'));
        }

        $items = $query->get();
        $now = now();
        $defaultDateTime = ($now->hour < 7) ? $now->copy()->subDay()->format('Y-m-d\TH:i') : $now->format('Y-m-d\TH:i');

        return view('cross_cut.create', compact('items', 'defaultDateTime'));
    }

    /**
     * Menyimpan data baru ke penyimpanan (database).
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
            'next_proses' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cross_cut_images', 'public');
        }

        CrossCutChecksheet::create([
            'plant' => auth()->user()->plant,
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
            'next_proses' => $validated['next_proses'] ?? null,
            'cycle_time' => $validated['cycle_time'],
            'operator_initials' => $validated['operator_initials'],
        ]);

        return redirect()->route('cross_cut.create')->with('success', 'Cross Cut Checksheet created successfully.');
    }

    /**
     * Menampilkan resource spesifik (detail).
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

    // Menyajikan gambar dari storage privat/public agar aman
    public function serveImage($id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);

        if (!Storage::disk('public')->exists($checksheet->image_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($checksheet->image_path));
    }

    // Get checksheet data for image modal
    public function getData($id)
    {
        $query = CrossCutChecksheet::with('item');

        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }

        $checksheet = $query->findOrFail($id);

        return response()->json([
            'image_path' => $checksheet->image_path,
            'item_name' => $checksheet->item->name ?? null,
            'customer' => $checksheet->item->customer ?? null,
            'part_number' => $checksheet->item->part_number ?? null,
            'sap_code' => $checksheet->item->sap_code ?? null,
            'production_date' => $checksheet->production_datetime ? \Carbon\Carbon::parse($checksheet->production_datetime)->format('d-m-Y H:i') : null,
            'qc_date' => $checksheet->qc_datetime ? \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d-m-Y H:i') : null,
            'production_shift' => $checksheet->production_shift,
            'qc_shift' => $checksheet->qc_shift,
            'chemical_copper' => $checksheet->chemical_copper,
            'chemical_nikel' => $checksheet->chemical_nikel,
            'chemical_eching' => $checksheet->chemical_eching,
            'chemical_abu' => $checksheet->chemical_abu,
            'position_remark_judgment' => $checksheet->position_remark_judgment,
            'position_remark_no_lot' => $checksheet->position_remark_no_lot,
            'result_remark' => $checksheet->result_remark,
            'operator_initials' => $checksheet->operator_initials,
        ]);
    }

    public function edit($id)
    {
        $query = CrossCutChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

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
            'next_proses' => 'nullable|string',
        ]);

        $imagePath = $checksheet->image_path;
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            // Simpan gambar baru
            $imagePath = $request->file('image')->store('cross_cut_images', 'public');
        }

        $checksheet->update(array_merge($validated, ['image_path' => $imagePath]));

        return redirect()->route('cross_cut.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $query = CrossCutChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        if ($checksheet->image_path) {
            Storage::disk('public')->delete($checksheet->image_path);
        }
        $checksheet->delete();

        return redirect()->route('cross_cut.index')->with('success', 'Data Cross Cut berhasil dihapus.');
    }

    public function approve(Request $request, $id, $type)
    {
        try {
            $query = CrossCutChecksheet::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $checksheet = $query->findOrFail($id);
            $user = auth()->user();

            // Role validation (admin can approve any level)
            if ($user->role !== 'admin') {
                if ($type == 'karu_qc' && $user->role !== 'karu_qc')
                    abort(403);
                if ($type == 'kashift_plating' && $user->role !== 'kashift_plating')
                    abort(403);
                if ($type == 'supervisor_plating' && $user->role !== 'supervisor_plating')
                    abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor')
                    abort(403);
                if ($type == 'manager_plating' && $user->role !== 'manager_plating')
                    abort(403);
                if ($type == 'manager' && $user->role !== 'manager')
                    abort(403);
            }

            // Check if this level was previously rejected, if so clear rejection remarks
            $wasRejected = false;
            if ($type == 'karu_qc' && $checksheet->karu_qc === 'REJECTED') {
                $wasRejected = true;
            } elseif ($type == 'kashift_plating' && $checksheet->kashift_plating === 'REJECTED') {
                $wasRejected = true;
            } elseif ($type == 'supervisor_plating' && $checksheet->supervisor_plating === 'REJECTED') {
                $wasRejected = true;
            } elseif ($type == 'supervisor' && $checksheet->supervisor_qc === 'REJECTED') {
                $wasRejected = true;
            } elseif ($type == 'manager_plating' && $checksheet->manager_plating === 'REJECTED') {
                $wasRejected = true;
            } elseif ($type == 'manager' && $checksheet->manager_qc === 'REJECTED') {
                $wasRejected = true;
            }

            // If was rejected and now being approved, clear rejection remarks
            if ($wasRejected) {
                $checksheet->rejection_remarks = null;
            }

            // Modified workflow - allow approval at any level without waiting for previous levels
            // Level 1: Karu QC
            if ($type == 'karu_qc') {
                if ($checksheet->karu_qc && $checksheet->karu_qc !== 'REJECTED') {
                    return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('error', 'Checksheet sudah disetujui oleh Karu QC.');
                }
                $checksheet->karu_qc = $user->name;
                $checksheet->karu_qc_approved_at = now();
            }
            // Level 2: Kashift Plating
            elseif ($type == 'kashift_plating') {
                if ($checksheet->kashift_plating && $checksheet->kashift_plating !== 'REJECTED') {
                    return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('error', 'Checksheet sudah disetujui oleh Kashift Plating.');
                }

                // Validate manual approver name input
                $request->validate([
                    'approver_name' => 'required|string|min:3|max:100',
                ], [
                    'approver_name.required' => 'Nama approver wajib diisi.',
                    'approver_name.min' => 'Nama approver minimal 3 karakter.',
                    'approver_name.max' => 'Nama approver maksimal 100 karakter.',
                ]);

                $checksheet->kashift_plating = $request->approver_name;
                $checksheet->kashift_plating_approved_at = now();
            }
            // Level 3: SPV Plating
            elseif ($type == 'supervisor_plating') {
                if ($checksheet->supervisor_plating && $checksheet->supervisor_plating !== 'REJECTED') {
                    return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('error', 'Checksheet sudah disetujui oleh SPV Plating.');
                }
                $checksheet->supervisor_plating = $user->name;
                $checksheet->supervisor_plating_approved_at = now();
            }
            // Level 4: SPV Quality (supervisor)
            elseif ($type == 'supervisor') {
                if ($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED') {
                    return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('error', 'Checksheet sudah disetujui oleh SPV Quality.');
                }
                $checksheet->supervisor_qc = $user->name;
                $checksheet->supervisor_approved_at = now();
            }
            // Level 5: Manager Plating
            elseif ($type == 'manager_plating') {
                if ($checksheet->manager_plating && $checksheet->manager_plating !== 'REJECTED') {
                    return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('error', 'Checksheet sudah disetujui oleh Manager Plating.');
                }
                $checksheet->manager_plating = $user->name;
                $checksheet->manager_plating_approved_at = now();
            }
            // Level 6: Manager QC (final approval)
            elseif ($type == 'manager') {
                if ($checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED') {
                    return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('error', 'Checksheet sudah disetujui oleh Manager QC.');
                }
                $checksheet->manager_qc = $user->name;
                $checksheet->manager_approved_at = now();
                // Set approval status to Approved when final level approves
                $checksheet->approval_status = 'Approved';
            }

            $checksheet->save();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }

        return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('success', 'Cross Cut Checksheet approved successfully.');
    }



    public function exportPdf(Request $request)
    {
        $query = CrossCutChecksheet::with('item')->orderBy('qc_datetime', 'desc')->orderBy('created_at', 'desc');

        // Apply all the same filters as the index page
        $this->applyFilters($query, $request);

        $checksheets = $query->get(); // Get all results, not paginated

        $itemName = null;
        if ($request->filled('item_id')) {
            $item = Item::find($request->item_id);
            $itemName = $item ? $item->name : 'Item tidak diketahui';
        }

        // Pass all request data to the view for filter display
        $viewData = [
            'checksheets' => $checksheets,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'item_id' => $request->item_id,
            'itemName' => $itemName,
            'approval_status' => $request->approval_status,
        ];

        $pdf = Pdf::loadView('cross_cut.pdf', $viewData);
        return $pdf->setPaper('a4', 'landscape')->stream('laporan-cross-cut.pdf');
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
                // Approved means Manager QC (final level) has approved
                $query->whereNotNull('manager_qc')->where('manager_qc', '!=', 'REJECTED');
            } elseif ($status == 'rejected') {
                $query->where(function ($q) {
                    $q->where('karu_qc', 'REJECTED')
                        ->orWhere('kashift_plating', 'REJECTED')
                        ->orWhere('supervisor_plating', 'REJECTED')
                        ->orWhere('supervisor_qc', 'REJECTED')
                        ->orWhere('manager_plating', 'REJECTED')
                        ->orWhere('manager_qc', 'REJECTED');
                });
            } elseif ($status == 'pending') {
                // Pending means Karu QC (first level) hasn't approved yet
                $query->whereNull('karu_qc');
            }
        }
        // Live search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('item', function ($itemQuery) use ($searchTerm) {
                    $itemQuery->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('customer', 'like', "%{$searchTerm}%")
                        ->orWhere('part_number', 'like', "%{$searchTerm}%");
                })->orWhere('operator_initials', 'like', "%{$searchTerm}%");
            });
        }
    }

    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);
        return view('cross_cut.edit_approval', compact('checksheet'));
    }

    // Update status approval oleh admin
    public function updateApproval(Request $request, $id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);
        $user = auth()->user(); // User Admin

        $validated = $request->validate([
            'karu_qc' => 'required|in:Pending,Approved,Rejected',
            'kashift_plating' => 'required|in:Pending,Approved,Rejected',
            'supervisor_plating' => 'required|in:Pending,Approved,Rejected',
            'supervisor_qc' => 'required|in:Pending,Approved,Rejected',
            'manager_plating' => 'required|in:Pending,Approved,Rejected',
            'manager_qc' => 'required|in:Pending,Approved,Rejected',
        ]);

        // Fungsi helper untuk update approval level
        $updateLevel = function ($field, $dateField, $status) use ($checksheet, $user) {
            if ($status === 'Approved') {
                if (is_null($checksheet->$field) || $checksheet->$field === 'REJECTED') {
                    $checksheet->$field = $user->name;
                    $checksheet->$dateField = now();
                }
            } elseif ($status === 'Rejected') {
                if ($checksheet->$field !== 'REJECTED') {
                    $checksheet->$field = 'REJECTED';
                    $checksheet->$dateField = now();
                }
            } else { // Pending
                $checksheet->$field = null;
                $checksheet->$dateField = null;
            }
        };

        // Update each level
        $updateLevel('karu_qc', 'karu_qc_approved_at', $validated['karu_qc']);
        $updateLevel('kashift_plating', 'kashift_plating_approved_at', $validated['kashift_plating']);
        $updateLevel('supervisor_plating', 'supervisor_plating_approved_at', $validated['supervisor_plating']);
        $updateLevel('supervisor_qc', 'supervisor_approved_at', $validated['supervisor_qc']);
        $updateLevel('manager_plating', 'manager_plating_approved_at', $validated['manager_plating']);
        $updateLevel('manager_qc', 'manager_approved_at', $validated['manager_qc']);

        // Update the main approval status based on the final level (Manager QC)
        if (
            $checksheet->manager_qc === 'REJECTED' ||
            $checksheet->manager_plating === 'REJECTED' ||
            $checksheet->supervisor_qc === 'REJECTED' ||
            $checksheet->supervisor_plating === 'REJECTED' ||
            $checksheet->kashift_plating === 'REJECTED' ||
            $checksheet->karu_qc === 'REJECTED'
        ) {
            $checksheet->approval_status = 'Rejected';
        } elseif ($checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED') {
            $checksheet->approval_status = 'Approved';
        } else {
            $checksheet->approval_status = 'Pending';
        }

        $checksheet->save();

        return redirect()->route('cross_cut.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('success', 'Status approval berhasil diperbarui oleh Admin.');
    }
}
