<?php

namespace App\Http\Controllers;

use App\Models\InProcessChecksheet;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;
use Barryvdh\DomPDF\Facade\Pdf;

class InProcessChecksheetController extends Controller
{
    private $partDimensionStandards = [
        '53102-K0L -D002' => [ // Corresponds to "COVER HNDL END K3VA"
            '1' => ['size' => 5, 'tolerance' => 0.2],
            '2' => ['size' => 10, 'tolerance' => 0.2],
            '3' => ['size' => 10, 'tolerance' => 0.5],
            '4' => ['size' => 20.5, 'tolerance' => 0.2],
            '5' => ['size' => 20, 'tolerance' => 0.2],
        ],
        '1PA - F836B - 00' => [ // Corresponds to "EMBLEM 3D"
            '1' => ['size' => 25, 'tolerance' => 0.2],
            '2' => ['size' => 21, 'tolerance' => 0.4],
            '3' => ['size' => 3.2, 'tolerance' => 0.2],
            '4' => ['size' => 24, 'tolerance' => 0.4],
        ],
        '53209-K3V-N100' => [ // Corresponds to "COVER HEAD LIGHT (NATURAL)"
            '1' => ['size' => 10, 'tolerance' => 0.2],
            '2' => ['size' => 10, 'tolerance' => 0.2],
            '3' => ['size' => 10, 'tolerance' => 0.2],
            '4' => ['size' => 10, 'tolerance' => 0.2],
        ],
    ];

    // For Admin to view list
    public function index(Request $request)
    {
        $query = InProcessChecksheet::with('item')->latest();

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
        
        $partDimensionStandards = $this->partDimensionStandards;
        
        return view('in_process.index', compact('checksheets', 'items', 'partDimensionStandards'));
    }

    // Show form (updated to pass items)
    public function create()
    {
        $items = Item::orderBy('name')->get();
        return view('in_process.create', [
            'items' => $items,
            'partDimensionStandards' => json_encode($this->partDimensionStandards)
        ]);
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
            'dimensions' => 'nullable|array',
            'cycle_time' => 'nullable|integer',
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
        ]);

        // --- Centralized Server-Side Dimension Validation ---
        $item = Item::find($validated['item_id']);
        if ($item && isset($this->partDimensionStandards[$item->part_number]) && !empty($request->dimensions)) {
            $dimensionStandards = $this->partDimensionStandards[$item->part_number];
            $isAnyInvalid = false;
            foreach ($request->dimensions as $cavity => $points) {
                if (!is_array($points)) continue;
                foreach ($points as $point => $value) {
                    if (isset($dimensionStandards[$point]) && $value !== null && $value !== '' && is_numeric($value)) {
                        $standard = $dimensionStandards[$point];
                        $floatValue = (float) $value;
                        $lowerBound = $standard['size'] - $standard['tolerance'];
                        $upperBound = $standard['size'] + $standard['tolerance'];

                        if ($floatValue < $lowerBound || $floatValue > $upperBound) {
                            $isAnyInvalid = true;
                            break;
                        }
                    }
                }
                if ($isAnyInvalid) break;
            }

            if ($isAnyInvalid) {
                $validated['judgment'] = 'NG';
            }
        }
        // --- End Validation ---

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

        // Process Dimensions into JSON, filtering out empty values
        $dimensions = $request->dimensions ?? [];
        $filteredDimensions = [];
        foreach ($dimensions as $cavity => $points) {
            $filteredPoints = array_filter($points, fn($value) => $value !== null && $value !== '');
            if (!empty($filteredPoints)) {
                $filteredDimensions[$cavity] = $filteredPoints;
            }
        }
        $dimensionCheck = json_encode($filteredDimensions);

        $checksheet = InProcessChecksheet::create([
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
            'dimension_check' => $dimensionCheck,
            'cycle_time' => $validated['cycle_time'] ?? null,
            'defects' => json_encode($defects),
        ]);

        // Kirim ke Google Sheets
        try {
            $googleService = app(GoogleSheetService::class);
            $googleService->setSheetName('Sheet2');
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
                isset($validated['remarks']) ? $validated['remarks'] : '-', // Kolom O: Remarks
                $validated['dimension_check'] ?? '-', // Kolom P: Check Dimensi
            ];

            // Kirim ke Google Sheets
            // Note: Since the columns might differ from Sub Assy, appending might be tricky if they share the same sheet.
            // Assuming we use the same sheet service which appends to the default sheet.
            // If InProcess needs a separate sheet or tab, additional configuration in GoogleSheetService would be needed.
            // For now, I'll append, but be aware of column misalignment if mixed with Sub Assy.
            // However, the prompt implies a separate structure, but `GoogleSheetService` seems to use a single sheet ID.
            // I'll proceed with appending but adding the new column at the end.
            
            $googleService->appendRow($sheetData);
        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan simpan database
            \Illuminate\Support\Facades\Log::error('Gagal kirim ke Google Sheets: ' . $e->getMessage());
            return redirect()->back()->with('success', 'Data Checksheet Inprocess berhasil disimpan lokal, namun GAGAL kirim ke Google Sheets. Error: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Data Checksheet Inprocess berhasil disimpan & terkirim ke Google Sheets.');
    }

    // Edit Checksheet
    public function edit($id)
    {
        // Allow access to edit based on sidebar role logic or generally allowed
        $checksheet = InProcessChecksheet::findOrFail($id);
        $items = Item::orderBy('name')->get();
        return view('in_process.edit', [
            'checksheet' => $checksheet,
            'items' => $items,
            'partDimensionStandards' => json_encode($this->partDimensionStandards)
        ]);
    }

    // Update Checksheet
    public function update(Request $request, $id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);

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
            'dimensions' => 'nullable|array',
            'cycle_time' => 'nullable|integer',
            'jam_before' => 'nullable|date_format:H:i',
            'jam_after' => 'nullable|date_format:H:i',
        ]);

        // Process Dimensions into JSON, filtering out empty values
        $dimensions = $request->dimensions ?? [];
        $filteredDimensions = [];
        foreach ($dimensions as $cavity => $points) {
            $filteredPoints = array_filter($points, fn($value) => $value !== null && $value !== '');
            if (!empty($filteredPoints)) {
                $filteredDimensions[$cavity] = $filteredPoints;
            }
        }
        $dimensionCheck = json_encode($filteredDimensions);

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
            'dimension_check' => $dimensionCheck,
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

        return redirect()->route('in_process.index')->with('success', 'Data Checksheet Inprocess berhasil diperbarui.');
    }

    // Delete Checksheet
    public function destroy($id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);
        $checksheet->delete();

        return redirect()->route('in_process.index')->with('success', 'Data Checksheet Inprocess berhasil dihapus.');
    }

    // Approve Checksheet
    public function approve(Request $request, $id, $type)
    {
        try {
            $checksheet = InProcessChecksheet::findOrFail($id);
            $user = auth()->user();
            
            // Validate that the user is allowed to approve this type
            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift') abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor') abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager') abort(403);
                if ($type == 'manager' && $user->role !== 'manager') abort(403);
            }

            // Validate approval order/hierarchy
            if ($type == 'kashift') {
                // Kashift can always approve first (no prerequisite)
                if ($checksheet->kashift_qc) {
                    return redirect()->route('in_process.index')->with('error', 'Checksheet sudah disetujui oleh Kashift.');
                }
                $checksheet->kashift_qc = $user->name;
                $checksheet->kashift_approved_at = now();
            } elseif ($type == 'supervisor') {
                // Supervisor can only approve if Kashift has approved
                if (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED') {
                    return redirect()->route('in_process.index')->with('error', 'Checksheet harus disetujui oleh Kashift terlebih dahulu.');
                }
                if ($checksheet->supervisor_qc) {
                    return redirect()->route('in_process.index')->with('error', 'Checksheet sudah disetujui oleh Supervisor.');
                }
                $checksheet->supervisor_qc = $user->name;
                $checksheet->supervisor_approved_at = now();
                $checksheet->approval_status = 'Approved'; 
            } elseif ($type == 'asst_manager') {
                // Asst Manager can only approve if Supervisor has approved
                if (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED') {
                    return redirect()->route('in_process.index')->with('error', 'Checksheet harus disetujui oleh Supervisor terlebih dahulu.');
                }
                if ($checksheet->asst_manager_qc) {
                    return redirect()->route('in_process.index')->with('error', 'Checksheet sudah disetujui oleh Asst Manager.');
                }
                $checksheet->asst_manager_qc = $user->name;
                $checksheet->asst_manager_approved_at = now();
            } elseif ($type == 'manager') {
                // Manager can only approve if Asst Manager has approved
                if (!$checksheet->asst_manager_qc || $checksheet->asst_manager_qc === 'REJECTED') {
                    return redirect()->route('in_process.index')->with('error', 'Checksheet harus disetujui oleh Asst Manager terlebih dahulu.');
                }
                if ($checksheet->manager_qc) {
                    return redirect()->route('in_process.index')->with('error', 'Checksheet sudah disetujui oleh Manager.');
                }
                $checksheet->manager_qc = $user->name;
                $checksheet->manager_approved_at = now();
            }

            $checksheet->save();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }

        return redirect()->route('in_process.index')->with('success', 'Data Checksheet Inprocess berhasil disetujui.');
    }

    // Reject Checksheet
    public function reject(Request $request, $id, $type)
    {
        try {
            $checksheet = InProcessChecksheet::findOrFail($id);
            $user = auth()->user();

            // Validate that the user is allowed to reject this type
            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift') abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor') abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager') abort(403);
                if ($type == 'manager' && $user->role !== 'manager') abort(403);
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

            // Save rejection remarks with role prefix
            $roleLabel = ucfirst(str_replace('_', ' ', $type));
            $checksheet->rejection_remarks = "[{$roleLabel}] " . $request->rejection_remarks . " - " . $user->name . " (" . now()->format('d/m/Y H:i') . ")";

            $checksheet->save();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }

        return redirect()->route('in_process.index')->with('success', 'Data Checksheet Inprocess berhasil ditolak.');
    }

    // Sync All Data to Google Sheets
    public function syncToGoogleSheets(Request $request)
    {
        try {
            $service = app(GoogleSheetService::class);
            $service->setSheetName('Sheet2');
            
            // 1. Clear Sheet (Note: This clears the whole sheet! Be careful if sharing with Sub Assy)
            // Ideally InProcess should use a different Sheet/Tab ID.
            // For now, mirroring existing logic.
            $service->clearSheet();

            // 2. Prepare Data and Append in Chunks
            
            // Send Header Row first
            $headerRow = [[
                'No', 'Tanggal', 'Jam Before', 'Jam After', 'Cycle Time', 'Shift', 'Barang', 'Part No', 'Customer', 
                'Total Qty', 'Sampling Qty', 'Total OK', 'Total NG', 'Judgment', 
                'Inisial Operator', 'Remarks', 'Check Dimensi', 'Ka Shift', 'Supervisor', 'Asst Manager', 'Manager'
            ]];
            $service->appendRows($headerRow);

            $count = 0;
            // Chunk at the database level to save memory
            InProcessChecksheet::with('item')
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
                            $c->dimension_check ?? '-',
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
        $query = InProcessChecksheet::with('item')->latest();

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $checksheets = $query->get();
        $filename = "in_process_checksheets_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = array('Tanggal', 'Jam Input', 'Cycle Time', 'Shift', 'Barang', 'Part No', 'Customer', 'Total Qty', 'Sampling Qty', 'Total OK', 'Total NG', 'Judgment', 'Inisial Operator', 'Remarks', 'Check Dimensi', 'Ka Shift', 'Supervisor', 'Asst Manager', 'Manager');

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
                $row['Check Dimensi'] = $checksheet->dimension_check;
                
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
                    $row['Check Dimensi'],
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

    // Export Checksheets to PDF
    public function exportPdf(Request $request)
    {
        // Reuse the query logic from the index method
        $query = InProcessChecksheet::with('item')->latest();

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('approval_status') && $request->approval_status != '') {
            if ($request->approval_status === 'Pending') {
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
                $query->where(function($q) {
                    $q->where('approval_status', 'Approved')
                      ->orWhere(function($sub) {
                          $sub->whereNull('approval_status')
                              ->whereNotNull('supervisor_qc')
                              ->where('supervisor_qc', '!=', 'REJECTED');
                      });
                });
            } elseif ($request->approval_status === 'Rejected') {
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

        $checksheets = $query->get(); // Get all results, not paginated
        $items = Item::orderBy('name')->get();

        $pdf = Pdf::loadView('in_process.pdf', compact('checksheets', 'items', 'request'));
        return $pdf->setPaper('a4', 'landscape')->stream('laporan-checksheet-inprocess.pdf');
    }

    // Show the form for admins to edit approval status
    public function editApproval($id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);
        return view('in_process.edit_approval', compact('checksheet'));
    }

    // Update the approval status by admin
    public function updateApproval(Request $request, $id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);
        $user = auth()->user(); // Admin user

        $validated = $request->validate([
            'kashift_qc' => 'required|in:Pending,Approved,Rejected',
            'supervisor_qc' => 'required|in:Pending,Approved,Rejected',
            'asst_manager_qc' => 'required|in:Pending,Approved,Rejected',
            'manager_qc' => 'required|in:Pending,Approved,Rejected',
        ]);

        // Helper function to update a single approval level
        $updateLevel = function ($level, $status) use ($checksheet, $user) {
            $nameField = "{$level}_qc";
            $dateField = "{$level}_approved_at";

            if ($status === 'Approved') {
                if (is_null($checksheet->$nameField) || $checksheet->$nameField === 'REJECTED') {
                    $checksheet->$nameField = $user->name;
                    $checksheet->$dateField = now();
                }
            } elseif ($status === 'Rejected') {
                if ($checksheet->$nameField !== 'REJECTED') {
                    $checksheet->$nameField = 'REJECTED';
                    $checksheet->$dateField = now();
                }
            } else { // Pending
                $checksheet->$nameField = null;
                $checksheet->$dateField = null;
            }
        };

        $updateLevel('kashift', $validated['kashift_qc']);
        $updateLevel('supervisor', $validated['supervisor_qc']);
        $updateLevel('asst_manager', $validated['asst_manager_qc']);
        $updateLevel('manager', $validated['manager_qc']);
        
        // Update the main approval status based on the final level
        if ($checksheet->manager_qc === 'REJECTED' || $checksheet->asst_manager_qc === 'REJECTED' || $checksheet->supervisor_qc === 'REJECTED' || $checksheet->kashift_qc === 'REJECTED') {
            $checksheet->approval_status = 'Rejected';
        } elseif ($checksheet->manager_qc) {
            $checksheet->approval_status = 'Approved';
        } else {
            $checksheet->approval_status = 'Pending';
        }

        $checksheet->save();

        return redirect()->route('in_process.index')->with('success', 'Status approval berhasil diperbarui oleh Admin.');
    }
}
