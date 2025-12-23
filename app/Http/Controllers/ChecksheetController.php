<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;

class ChecksheetController extends Controller
{
    // For Admin to view list
    public function index(Request $request)
    {
        $query = Checksheet::with('item')->latest();

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('approval_status') && $request->approval_status != '') {
            if ($request->approval_status === 'Pending') {
                // Pending: Explicit 'Pending' OR (Null Status AND No Supervisor Approval AND No Rejections)
                $query->where(function($q) {
                    $q->where('approval_status', 'Pending')
                      ->orWhere(function($sub) {
                          $sub->whereNull('approval_status')
                              ->whereNull('supervisor_qc')
                              ->where(function($rej) {
                                  $rej->where('kashift_qc', '!=', 'REJECTED')
                                      ->orWhereNull('kashift_qc');
                              });
                      });
                });
            } elseif ($request->approval_status === 'Approved') {
                // Approved: Explicit 'Approved' OR (Null Status AND Supervisor Approved)
                $query->where(function($q) {
                    $q->where('approval_status', 'Approved')
                      ->orWhere(function($sub) {
                          $sub->whereNull('approval_status')
                              ->whereNotNull('supervisor_qc')
                              ->where('supervisor_qc', '!=', 'REJECTED');
                      });
                });
            } elseif ($request->approval_status === 'Rejected') {
                // Rejected: Explicit 'Rejected' OR (Null Status AND Any Rejected)
                $query->where(function($q) {
                    $q->where('approval_status', 'Rejected')
                      ->orWhere(function($sub) {
                          $sub->whereNull('approval_status')
                              ->where(function($rej) {
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

        $checksheets = $query->paginate(10);
        // Sort items by name to make filter dropdown cleaner
        $items = Item::orderBy('name')->get(); 
        
        return view('admin.checksheets.index', compact('checksheets', 'items'));
    }

    // Show form (updated to pass items)
    public function create()
    {
        $items = Item::orderBy('name')->get();
        return view('checksheet.sub_assy', compact('items'));
    }

    // Store submission
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

        // Process Defects into Structured JSON
        $defects = [];
        if ($request->has('defect_types')) {
            foreach ($request->defect_types as $index => $type) {
                if ($type) {
                    $qty = $request->defect_quantities[$index] ?? 1; // Default to 1 if missing
                    $defects[] = ['type' => $type, 'qty' => (int)$qty];
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
        // Allow access to edit based on sidebar role logic or generally allowed
        $checksheet = Checksheet::findOrFail($id);
        $items = Item::orderBy('name')->get();
        return view('admin.checksheets.edit', compact('checksheet', 'items'));
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
            'cycle_time' => $validated['cycle_time'] ?? null,
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

        // Update created_at and cycle_time if time is provided and user is authorized (not inspector)
        if (auth()->user()->role !== 'inspector') {
            $currentDate = $checksheet->created_at->format('Y-m-d');

            if (!empty($validated['jam_after'])) {
                $updateData['created_at'] = \Carbon\Carbon::parse($currentDate . ' ' . $validated['jam_after']);
            }

            if (!empty($validated['jam_before']) && !empty($validated['jam_after'])) {
                $before = \Carbon\Carbon::parse($currentDate . ' ' . $validated['jam_before']);
                $after = \Carbon\Carbon::parse($currentDate . ' ' . $validated['jam_after']);

                // Handle midnight crossing (e.g., 23:55 to 00:05)
                if ($after->lessThan($before)) {
                    $after->addDay();
                }

                $updateData['cycle_time'] = $after->diffInSeconds($before);
            }
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
            
            // Validate that the user is allowed to approve this type
            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift') abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor') abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager') abort(403);
                if ($type == 'manager' && $user->role !== 'manager') abort(403);
            }

            // Assign approval based on type
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
            return response('Error: ' . $e->getMessage() . ' <br><a href="/run-migration">Klik disini untuk jalankan migrasi database jika error terkait kolom hilang</a>', 500);
        }

        return redirect()->route('admin.checksheets.index')->with('success', 'Data Checksheet berhasil disetujui.');
    }

    // Reject Checksheet
    public function reject(Request $request, $id, $type)
    {
        try {
            $checksheet = Checksheet::findOrFail($id);
            $user = auth()->user();

            // Validate that the user is allowed to reject this type
            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift') abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor') abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager') abort(403);
                if ($type == 'manager' && $user->role !== 'manager') abort(403);
            }
            
            if ($type == 'kashift') {
                $checksheet->kashift_qc = 'REJECTED';
                $checksheet->kashift_approved_at = now(); // Mark time of rejection too
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
            return response('Error: ' . $e->getMessage() . ' <br><a href="/run-migration">Klik disini untuk jalankan migrasi database jika error terkait kolom hilang</a>', 500);
        }

        return redirect()->route('admin.checksheets.index')->with('success', 'Data Checksheet berhasil ditolak.');
    }

    // Sync All Data to Google Sheets
    public function syncToGoogleSheets(Request $request)
    {
        try {
            $service = new GoogleSheetService();
            
            // 1. Clear Sheet
            $service->clearSheet();

            // 2. Prepare Data and Append in Chunks
            
            // Send Header Row first
            $headerRow = [[
                'No', 'Tanggal', 'Jam Before', 'Jam After', 'Cycle Time', 'Shift', 'Barang', 'Part No', 'Customer', 
                'Total Qty', 'Sampling Qty', 'Total OK', 'Total NG', 'Judgment', 
                'Inisial Operator', 'Remarks', 'Ka Shift', 'Supervisor', 'Asst Manager', 'Manager'
            ]];
            $service->appendRows($headerRow);

            $count = 0;
            // Chunk at the database level to save memory
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

    // Export Checksheets to CSV
    public function export(Request $request)
    {
        $query = Checksheet::with('item')->latest();

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $checksheets = $query->get();
        $filename = "checksheets_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = array('Tanggal', 'Jam Input', 'Cycle Time', 'Shift', 'Barang', 'Part No', 'Customer', 'Total Qty', 'Sampling Qty', 'Total OK', 'Total NG', 'Judgment', 'Inisial Operator', 'Remarks', 'Ka Shift', 'Supervisor', 'Asst Manager', 'Manager');

        $callback = function() use($checksheets, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($checksheets as $checksheet) {
                $row['Tanggal']      = $checksheet->date;
                $row['Jam Input']    = $checksheet->created_at->format('H:i');
                $row['Cycle Time']   = $checksheet->cycle_time ?? '-';
                $row['Shift']        = $checksheet->shift;
                $row['Barang']       = $checksheet->item->name ?? '-';
                $row['Part No']      = $checksheet->item->part_number ?? '-';
                $row['Customer']     = $checksheet->item->customer ?? '-';
                $row['Total Qty']    = $checksheet->total_qty;
                $row['Sampling Qty'] = $checksheet->sampling_qty;
                $row['Total OK']     = $checksheet->total_ok;
                $row['Total NG']     = $checksheet->total_ng;
                $row['Judgment']     = $checksheet->judgment;
                $row['Inisial Operator'] = $checksheet->operator_initials;
                $row['Remarks']      = $checksheet->remarks;
                
                // Export the actual approver name if available
                $row['Ka Shift']     = $checksheet->kashift_qc ?? ''; 
                $row['Supervisor']   = $checksheet->supervisor_qc ?? '';
                $row['Asst Manager'] = $checksheet->asst_manager_qc ?? '';
                $row['Manager']      = $checksheet->manager_qc ?? '';

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
}
