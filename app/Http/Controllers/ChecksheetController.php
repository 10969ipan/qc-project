<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;

class ChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected function getModelClass()
    {
        return \App\Models\Checksheet::class;
    }

    protected function getExportHeaders()
    {
        return [
            'No',
            'Tanggal',
            'Jam Before',
            'Jam After',
            'Cycle Time',
            'Shift',
            'Barang',
            'Part No',
            'Customer',
            'Total Qty',
            'Sampling Qty',
            'Total OK',
            'Total NG',
            'Judgment',
            'Inisial Operator',
            'Remarks',
            'Ka Shift',
            'Supervisor',
            'Asst Manager',
            'Manager'
        ];
    }

    protected function mapExportRow($c)
    {
        return [
            $c->id,
            $c->date,
            $c->created_at->copy()->subSeconds($c->cycle_time ?? 0)->format('H:i:s'),
            $c->created_at->format('H:i:s'),
            $c->cycle_time ?? '-',
            $c->shift,
            $c->item->name ?? '-',
            $c->item->part_number ?? '-',
            $c->item->customer ?? '-',
            $c->total_qty,
            $c->sampling_qty,
            $c->total_ok,
            $c->total_ng,
            $c->judgment,
            $c->operator_initials,
            $c->remarks ?? '-',
            // Approvals
            $c->kashift_qc === 'REJECTED' ? 'REJECTED' : ($c->kashift_qc ?? ''),
            $c->supervisor_qc === 'REJECTED' ? 'REJECTED' : ($c->supervisor_qc ?? ''),
            $c->asst_manager_qc === 'REJECTED' ? 'REJECTED' : ($c->asst_manager_qc ?? ''),
            $c->manager_qc === 'REJECTED' ? 'REJECTED' : ($c->manager_qc ?? '')
        ];
    }

    public function index(Request $request)
    {
        $query = Checksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        // Admin can switch plants via query parameter, others are locked via HasPlantFilter
        if (auth()->user()->role === 'admin' && $request->has('plant')) {
            $query->withoutGlobalScope('plant')->where('plant', $request->get('plant'));
        }

        // For inspector, we explicitly override the request plant to their own plant for UI consistency
        if (auth()->user()->role === 'inspector') {
            $request->merge(['plant' => auth()->user()->plant]);
        }

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('approval_status') && $request->approval_status != '') {
            if ($request->approval_status === 'Pending') {
                // Pending: Eksplisit 'Pending' ATAU (Status Null DAN Tidak Ada Approval Konfirmasi Supervisor DAN Tidak Ada Rejection)
                $query->where(function ($q) {
                    $q->where('approval_status', 'Pending')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('approval_status')
                                ->whereNull('supervisor_qc')
                                ->where(function ($rej) {
                                    $rej->where('kashift_qc', '!=', 'REJECTED')
                                        ->orWhereNull('kashift_qc');
                                });
                        });
                });
            } elseif ($request->approval_status === 'Approved') {
                // Approved: Eksplisit 'Approved' ATAU (Status Null DAN Supervisor sudah Approve)
                $query->where(function ($q) {
                    $q->where('approval_status', 'Approved')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('approval_status')
                                ->whereNotNull('supervisor_qc')
                                ->where('supervisor_qc', '!=', 'REJECTED');
                        });
                });
            } elseif ($request->approval_status === 'Rejected') {
                // Rejected: Eksplisit 'Rejected' ATAU (Status Null DAN Ada yang Reject)
                $query->where(function ($q) {
                    $q->where('approval_status', 'Rejected')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('approval_status')
                                ->where(function ($rej) {
                                    $rej->where('kashift_qc', 'REJECTED')
                                        ->orWhere('supervisor_qc', 'REJECTED')
                                        ->orWhere('asst_manager_qc', 'REJECTED');
                                });
                        });
                });
            }
        }

        if ($request->has('item_id') && $request->item_id != '') {
            $query->where('item_id', $request->item_id);
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

        $checksheets = $query->paginate(10)->withQueryString();
        // Sort items by name to make filter dropdown cleaner
        $items = Item::orderBy('name')->get();

        return view('sub_assy.index', compact('checksheets', 'items'));
    }

    // Tampilkan form (diupdate untuk mengirim data items)
    public function create(Request $request)
    {
        $query = Item::byCategory('Sub Assy')->orderBy('name');

        // Filter items based on plant context
        // If user is Admin and has selected a plant via query param
        if (auth()->user()->role === 'admin' && $request->has('plant')) {
            $query->where('plant', $request->query('plant'));
        }

        $items = $query->get();
        $now = now();
        $defaultDate = ($now->hour < 7) ? $now->copy()->subDay()->format('Y-m-d') : $now->format('Y-m-d');

        return view('sub_assy.create', compact('items', 'defaultDate'));
    }

    // Simpan data (submission)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'date' => 'required|date',
            'shift' => 'required|string',
            'line' => 'required|string', // Validasi Line
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
            'next_proses' => 'nullable|string',
        ]);

        // Proses Defect menjadi JSON terstruktur
        $defects = [];
        if ($request->has('defect_types')) {
            foreach ($request->defect_types as $index => $type) {
                if ($type) {
                    $qty = $request->defect_quantities[$index] ?? 1; // Default to 1 if missing
                    $defects[] = ['type' => $type, 'qty' => (int) $qty];
                }
            }
        }

        $checksheet = Checksheet::create([
            'plant' => auth()->user()->plant,
            'item_id' => $validated['item_id'],
            'date' => $validated['date'],
            'shift' => $validated['shift'],
            'line' => $validated['line'], // Add line
            'total_qty' => $validated['total_qty'],
            'sampling_qty' => $validated['sampling_qty'],
            'total_ok' => $validated['total_ok'],
            'total_ng' => $validated['total_ng'],
            'judgment' => $validated['judgment'],
            'operator_initials' => $validated['operator_initials'],
            'remarks' => $validated['remarks'],
            'next_proses' => $validated['next_proses'] ?? null,
            'cycle_time' => $validated['cycle_time'] ?? null,
            'defects' => json_encode($defects),
        ]);

        // Kirim ke Google Sheets
        try {
            $googleService = new GoogleSheetService();
            $item = Item::find($validated['item_id']);

            $sheetData = $this->mapExportRow($checksheet);

            // Kirim ke Google Sheets
            $googleService->appendRow($sheetData);
        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan simpan database
            \Illuminate\Support\Facades\Log::error('Gagal kirim ke Google Sheets: ' . $e->getMessage());
            return redirect()->back()->with('success', 'Data tersimpan lokal, namun GAGAL kirim ke Google Sheets. Error: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Data Checksheet berhasil disimpan & terkirim ke Google Sheets.');
    }

    // Edit Checksheet
    public function edit($id)
    {
        $query = Checksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::orderBy('name')->get();
        return view('sub_assy.edit', compact('checksheet', 'items'));
    }

    // Update Checksheet
    public function update(Request $request, $id)
    {
        $checksheet = Checksheet::findOrFail($id);

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'date' => 'required|date',
            'shift' => 'required|string',
            'line' => 'required|string', // Validasi Line
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'jam_before' => 'nullable|date_format:H:i',
            'jam_after' => 'nullable|date_format:H:i',
            'next_proses' => 'nullable|string',
        ]);

        $updateData = [
            'item_id' => $validated['item_id'],
            'date' => $validated['date'],
            'shift' => $validated['shift'],
            'line' => $validated['line'], // Add line
            'total_qty' => $validated['total_qty'],
            'sampling_qty' => $validated['sampling_qty'],
            'total_ok' => $validated['total_ok'],
            'total_ng' => $validated['total_ng'],
            'judgment' => $validated['judgment'],
            'operator_initials' => $validated['operator_initials'],
            'remarks' => $validated['remarks'],
            'next_proses' => $validated['next_proses'] ?? null,
        ];

        // Update created_at dan cycle_time jika waktu disediakan dan user punya otoritas (bukan inspector)
        if (auth()->user()->role !== 'inspector') {
            $currentDate = $checksheet->created_at->format('Y-m-d');

            if (!empty($validated['jam_after'])) {
                $updateData['created_at'] = \Carbon\Carbon::parse($currentDate . ' ' . $validated['jam_after']);
            }

            if (!empty($validated['jam_before']) && !empty($validated['jam_after'])) {
                $before = \Carbon\Carbon::parse($currentDate . ' ' . $validated['jam_before']);
                $after = \Carbon\Carbon::parse($currentDate . ' ' . $validated['jam_after']);

                // Menangani pergantian hari (melewati tengah malam, misal 23:55 ke 00:05)
                if ($after->lessThan($before)) {
                    $after->addDay();
                }

                $updateData['cycle_time'] = $before->diffInSeconds($after);
            } else {
                // Jika jam_before atau jam_after tidak dilampirkan, gunakan nilai dari form
                $updateData['cycle_time'] = $validated['cycle_time'] ?? null;
            }
        } else {
            // Inspector tidak bisa mengubah waktu, gunakan nilai dari form
            $updateData['cycle_time'] = $validated['cycle_time'] ?? null;
        }

        $checksheet->update($updateData);

        return redirect()->route('admin.checksheets.index', $request->query())->with('success', 'Status approval berhasil diperbarui oleh Admin.');
    }

    // Delete Checksheet
    public function destroy($id)
    {
        $query = Checksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);
        $checksheet->delete();

        return redirect()->route('admin.checksheets.index')->with('success', 'Data Checksheet berhasil dihapus.');
    }

    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = Checksheet::findOrFail($id);
        return view('sub_assy.edit_approval', compact('checksheet'));
    }

    // Update status approval oleh admin
    public function updateApproval(Request $request, $id)
    {
        $checksheet = Checksheet::findOrFail($id);
        $user = auth()->user(); // User Admin

        $validated = $request->validate([
            'kashift_qc' => 'required|in:Pending,Approved,Rejected',
            'supervisor_qc' => 'required|in:Pending,Approved,Rejected',
            'asst_manager_qc' => 'required|in:Pending,Approved,Rejected',
            'manager_qc' => 'required|in:Pending,Approved,Rejected',
        ]);

        // Fungsi helper untuk update satu level approval
        $updateLevel = function ($level, $status) use ($checksheet, $user) {
            $nameField = "{$level}_qc";
            $dateField = "{$level}_approved_at";

            if ($status === 'Approved') {
                // Set ke approved hanya jika belum diset
                if (is_null($checksheet->$nameField) || $checksheet->$nameField === 'REJECTED') {
                    $checksheet->$nameField = $user->name; // Gunakan nama admin
                    $checksheet->$dateField = now();
                }
            } elseif ($status === 'Rejected') {
                // Set ke rejected hanya jika belum diset
                if ($checksheet->$nameField !== 'REJECTED') {
                    $checksheet->$nameField = 'REJECTED';
                    $checksheet->$dateField = now();
                }
            } else { // Pending
                // Hapus approval (reset)
                $checksheet->$nameField = null;
                $checksheet->$dateField = null;
            }
        };

        $updateLevel('kashift', $validated['kashift_qc']);
        $updateLevel('supervisor', $validated['supervisor_qc']);
        $updateLevel('asst_manager', $validated['asst_manager_qc']);
        $updateLevel('manager', $validated['manager_qc']);

        // Update status approval utama berdasarkan level akhir
        if ($checksheet->manager_qc === 'REJECTED' || $checksheet->asst_manager_qc === 'REJECTED' || $checksheet->supervisor_qc === 'REJECTED' || $checksheet->kashift_qc === 'REJECTED') {
            $checksheet->approval_status = 'Rejected';
        } elseif ($checksheet->manager_qc) {
            $checksheet->approval_status = 'Approved';
        } else {
            $checksheet->approval_status = 'Pending';
        }

        $checksheet->save();

        return redirect()->route('admin.checksheets.index', $request->query())->with('success', 'Status approval berhasil diperbarui oleh Admin.');
    }
}
