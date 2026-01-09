<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;

class ChecksheetController extends Controller
{
    // Untuk Admin melihat daftar checksheet
    public function index(Request $request)
    {
        $query = Checksheet::with('item')->latest();

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

        $checksheets = $query->paginate(10);
        // Sort items by name to make filter dropdown cleaner
        $items = Item::orderBy('name')->get();

        return view('sub_assy.index', compact('checksheets', 'items'));
    }

    // Tampilkan form (diupdate untuk mengirim data items)
    public function create()
    {
        $items = Item::byCategory('Sub Assy')->orderBy('name')->get();
        return view('sub_assy.create', compact('items'));
    }

    // Simpan data (submission)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'date' => 'required|date',
            'shift' => 'required|string',
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
            'item_id' => $validated['item_id'],
            'date' => $validated['date'],
            'shift' => $validated['shift'],
            'total_qty' => $validated['total_qty'],
            'sampling_qty' => $validated['sampling_qty'],
            'total_ok' => $validated['total_ok'],
            'total_ng' => $validated['total_ng'],
            'judgment' => $validated['judgment'],
            'operator_initials' => $validated['operator_initials'],
            'remarks' => $validated['remarks'],
            'cycle_time' => $validated['cycle_time'] ?? null,
            'defects' => json_encode($defects),
        ]);

        // Kirim ke Google Sheets
        try {
            $googleService = new GoogleSheetService();
            $item = Item::find($validated['item_id']);

            $sheetData = [
                $checksheet->id, // Kolom A: No (ID)
                $validated['date'], // Kolom B: Tanggal
                $checksheet->created_at->copy()->subSeconds($validated['cycle_time'] ?? 0)->format('H:i:s'), // Kolom C: Jam Before
                $checksheet->created_at->format('H:i:s'), // Kolom D: Jam After
                $validated['cycle_time'] ?? '-', // Kolom E: Cycle Time
                $validated['shift'], // Kolom F: Shift
                $item ? $item->name : '-', // Kolom G: Barang
                $item ? $item->part_number : '-', // Kolom G: Part No
                $item ? $item->customer : '-', // Kolom H: Customer
                $validated['total_qty'], // Kolom I: Total Qty
                $validated['sampling_qty'], // Kolom J: Sampling Qty
                $validated['total_ok'], // Kolom K: Total OK
                $validated['total_ng'], // Kolom L: Total NG
                $validated['judgment'], // Kolom M: Judgment
                $validated['operator_initials'], // Kolom N: Operator
                $validated['remarks'] ?? '-', // Kolom O: Remarks
            ];

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
        // Izinkan akses edit berdasarkan logika role sidebar atau diizinkan secara umum
        $checksheet = Checksheet::findOrFail($id);
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
        ]);

        $updateData = [
            'item_id' => $validated['item_id'],
            'date' => $validated['date'],
            'shift' => $validated['shift'],
            'total_qty' => $validated['total_qty'],
            'sampling_qty' => $validated['sampling_qty'],
            'total_ok' => $validated['total_ok'],
            'total_ng' => $validated['total_ng'],
            'judgment' => $validated['judgment'],
            'operator_initials' => $validated['operator_initials'],
            'remarks' => $validated['remarks'],
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

        return redirect()->route('admin.checksheets.index')->with('success', 'Data Checksheet berhasil diperbarui.');
    }

    // Delete Checksheet
    public function destroy($id)
    {
        $checksheet = Checksheet::findOrFail($id);
        $checksheet->delete();

        return redirect()->route('admin.checksheets.index')->with('success', 'Data Checksheet berhasil dihapus.');
    }

    // Approve Checksheet
    public function approve(Request $request, $id, $type)
    {
        try {
            $checksheet = Checksheet::findOrFail($id);
            $user = auth()->user();

            // Validasi bahwa user diizinkan untuk approve tipe ini
            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift')
                    abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor')
                    abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager')
                    abort(403);
                if ($type == 'manager' && $user->role !== 'manager')
                    abort(403);
            }

            // Modified workflow - allow approval at any level without waiting for previous levels
            if ($type == 'kashift') {
                if ($checksheet->kashift_qc && $checksheet->kashift_qc !== 'REJECTED') {
                    return redirect()->route('admin.checksheets.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('error', 'Checksheet sudah disetujui oleh Kashift.');
                }
                $checksheet->kashift_qc = $user->name;
                $checksheet->kashift_approved_at = now();
            } elseif ($type == 'supervisor') {
                if ($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED') {
                    return redirect()->route('admin.checksheets.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('error', 'Checksheet sudah disetujui oleh Supervisor.');
                }
                $checksheet->supervisor_qc = $user->name;
                $checksheet->supervisor_approved_at = now();
                $checksheet->approval_status = 'Approved';
            } elseif ($type == 'asst_manager') {
                if ($checksheet->asst_manager_qc && $checksheet->asst_manager_qc !== 'REJECTED') {
                    return redirect()->route('admin.checksheets.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('error', 'Checksheet sudah disetujui oleh Asst Manager.');
                }
                $checksheet->asst_manager_qc = $user->name;
                $checksheet->asst_manager_approved_at = now();
            } elseif ($type == 'manager') {
                if ($checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED') {
                    return redirect()->route('admin.checksheets.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('error', 'Checksheet sudah disetujui oleh Manager.');
                }
                $checksheet->manager_qc = $user->name;
                $checksheet->manager_approved_at = now();
            }

            $checksheet->save();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage() . ' <br><a href="/run-migration">Klik disini untuk jalankan migrasi database jika error terkait kolom hilang</a>', 500);
        }

        return redirect()->route('admin.checksheets.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('success', 'Data Checksheet berhasil disetujui.');
    }

    // Reject Checksheet
    public function reject(Request $request, $id, $type)
    {
        try {
            $checksheet = Checksheet::findOrFail($id);
            $user = auth()->user();

            // Validasi bahwa user diizinkan untuk menolak (reject) tipe ini
            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift')
                    abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor')
                    abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager')
                    abort(403);
                if ($type == 'manager' && $user->role !== 'manager')
                    abort(403);
            }

            // Validate rejection remarks
            $request->validate([
                'rejection_remarks' => 'required|string|min:10|max:500',
            ], [
                'rejection_remarks.required' => 'Keterangan rejection wajib diisi.',
                'rejection_remarks.min' => 'Keterangan rejection minimal 10 karakter.',
                'rejection_remarks.max' => 'Keterangan rejection maksimal 500 karakter.',
            ]);

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

            // Simpan keterangan penolakan dengan prefix role
            $roleLabel = ucfirst(str_replace('_', ' ', $type));
            $checksheet->rejection_remarks = "[{$roleLabel}] " . $request->rejection_remarks . " - " . $user->name . " (" . now()->format('d/m/Y H:i') . ")";

            $checksheet->save();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage() . ' <br><a href="/run-migration">Klik disini untuk jalankan migrasi database jika error terkait kolom hilang</a>', 500);
        }

        return redirect()->route('admin.checksheets.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('success', 'Data Checksheet berhasil ditolak.');
    }

    // Sinkronisasi Semua Data ke Google Sheets
    public function syncToGoogleSheets(Request $request)
    {
        try {
            $service = new GoogleSheetService();

            // 1. Bersihkan Sheet (Clear Sheet)
            $service->clearSheet();

            // 2. Siapkan Data dan Tambahkan dalam Potongan (Chunks)

            // Kirim Baris Header dulu
            $headerRow = [
                [
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
                ]
            ];
            $service->appendRows($headerRow);

            $count = 0;
            // Chunk di level database untuk menghemat memori
            Checksheet::with('item')
                ->orderBy('date')
                ->orderBy('created_at')
                ->chunk(500, function ($checksheets) use ($service, &$count) {
                    $rows = [];
                    foreach ($checksheets as $c) {
                        $rows[] = [
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
                    $service->appendRows($rows);
                    $count += count($rows);
                });

            return redirect()->back()->with('success', 'Sinkronisasi ke Google Sheets berhasil (' . $count . ' data).');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Sinkronisasi: ' . $e->getMessage());
        }
    }

    // Ekspor Checksheet ke CSV
    public function export(Request $request)
    {
        $query = Checksheet::with('item')->latest();

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $checksheets = $query->get();
        $filename = "checksheets_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = array('Tanggal', 'Jam Input', 'Cycle Time', 'Shift', 'Barang', 'Part No', 'Customer', 'Total Qty', 'Sampling Qty', 'Total OK', 'Total NG', 'Judgment', 'Inisial Operator', 'Remarks', 'Ka Shift', 'Supervisor', 'Asst Manager', 'Manager');

        $callback = function () use ($checksheets, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($checksheets as $checksheet) {
                $row['Tanggal'] = $checksheet->date;
                $row['Jam Input'] = $checksheet->created_at->format('H:i');
                $row['Cycle Time'] = $checksheet->cycle_time ?? '-';
                $row['Shift'] = $checksheet->shift;
                $row['Barang'] = $checksheet->item->name ?? '-';
                $row['Part No'] = $checksheet->item->part_number ?? '-';
                $row['Customer'] = $checksheet->item->customer ?? '-';
                $row['Total Qty'] = $checksheet->total_qty;
                $row['Sampling Qty'] = $checksheet->sampling_qty;
                $row['Total OK'] = $checksheet->total_ok;
                $row['Total NG'] = $checksheet->total_ng;
                $row['Judgment'] = $checksheet->judgment;
                $row['Inisial Operator'] = $checksheet->operator_initials;
                $row['Remarks'] = $checksheet->remarks;

                // Ekspor nama approver aktual jika tersedia
                $row['Ka Shift'] = $checksheet->kashift_qc ?? '';
                $row['Supervisor'] = $checksheet->supervisor_qc ?? '';
                $row['Asst Manager'] = $checksheet->asst_manager_qc ?? '';
                $row['Manager'] = $checksheet->manager_qc ?? '';

                fputcsv($file, array(
                    $row['Tanggal'],
                    $row['Jam Input'],
                    $row['Cycle Time'],
                    $row['Shift'],
                    $row['Barang'],
                    $row['Part No'],
                    $row['Customer'],
                    $row['Total Qty'],
                    $row['Sampling Qty'],
                    $row['Total OK'],
                    $row['Total NG'],
                    $row['Judgment'],
                    $row['Inisial Operator'],
                    $row['Remarks'],
                    $row['Ka Shift'],
                    $row['Supervisor'],
                    $row['Asst Manager'],
                    $row['Manager']
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

        return redirect()->route('admin.checksheets.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('success', 'Status approval berhasil diperbarui oleh Admin.');
    }
}
