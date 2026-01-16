<?php

namespace App\Http\Controllers;

use App\Models\InProcessChecksheet;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;
use Barryvdh\DomPDF\Facade\Pdf;

class InProcessChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected function getModelClass()
    {
        return \App\Models\InProcessChecksheet::class;
    }

    protected function getGoogleSheetName()
    {
        return 'Sheet2';
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
            'Check Dimensi',
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
            $c->dimension_check ?? '-',
            // Approvals
            $c->kashift_qc === 'REJECTED' ? 'REJECTED' : ($c->kashift_qc ?? ''),
            $c->supervisor_qc === 'REJECTED' ? 'REJECTED' : ($c->supervisor_qc ?? ''),
            $c->asst_manager_qc === 'REJECTED' ? 'REJECTED' : ($c->asst_manager_qc ?? ''),
            $c->manager_qc === 'REJECTED' ? 'REJECTED' : ($c->manager_qc ?? '')
        ];
    }

    private $hardcodedStandards = [
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



    // Menggabungkan standar hardcoded dengan standar dari database
    private function getConsolidatedStandards()
    {
        $standards = $this->hardcodedStandards;

        // Ambil standar dari database dan konversi ke format yang sama
        $dbItems = Item::whereNotNull('dimension_standards')->get();
        foreach ($dbItems as $item) {
            if ($item->part_number && !empty($item->dimension_standards)) {
                $itemStandards = [];
                foreach ($item->dimension_standards as $index => $std) {
                    // Check if $std is an array and has size/tolerance
                    if (is_array($std) && isset($std['size']) && isset($std['tolerance'])) {
                        // Point indices often start at 1 in the UI, but database might be 0-based
                        // We'll use string keys '1', '2', etc. to match the controller array format
                        $pointKey = (string) ($index + 1);
                        $itemStandards[$pointKey] = [
                            'size' => (float) $std['size'],
                            'tolerance' => (float) $std['tolerance']
                        ];
                    }
                }

                if (!empty($itemStandards)) {
                    // Standar dari database menimpa standar hardcoded jika ada konflik
                    $standards[$item->part_number] = $itemStandards;
                }
            }
        }

        return $standards;
    }

    public function index(Request $request)
    {
        $query = InProcessChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

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
                // Pending: Explicit 'Pending' OR (Null Status AND No Supervisor Approval AND No Rejections)
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
                // Approved: Explicit 'Approved' OR (Null Status AND Supervisor Approved)
                $query->where(function ($q) {
                    $q->where('approval_status', 'Approved')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('approval_status')
                                ->whereNotNull('supervisor_qc')
                                ->where('supervisor_qc', '!=', 'REJECTED');
                        });
                });
            } elseif ($request->approval_status === 'Rejected') {
                // Rejected: Explicit 'Rejected' OR (Null Status AND Any Rejected)
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

        $partDimensionStandards = $this->getConsolidatedStandards();

        return view('in_process.index', compact('checksheets', 'items', 'partDimensionStandards'));
    }

    // Show form (updated to pass items)
    public function create()
    {
        $items = Item::byCategory('Inprosess')->orderBy('name')->get();
        $now = now();
        $defaultDate = ($now->hour < 7) ? $now->copy()->subDay()->format('Y-m-d') : $now->format('Y-m-d');

        return view('in_process.create', [
            'items' => $items,
            'defaultDate' => $defaultDate,
            'partDimensionStandards' => json_encode($this->getConsolidatedStandards())
        ]);
    }

    // Simpan data (submission)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'date' => 'required|date',
            'shift' => 'required|string',
            'code_machine' => 'required|string', // Validasi Mesin
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
            'next_proses' => 'nullable|string',
        ]);

        // --- Validasi Dimensi Terpusat di Server-Side ---
        $item = Item::find($validated['item_id']);
        $allStandards = $this->getConsolidatedStandards();
        if ($item && isset($allStandards[$item->part_number]) && !empty($request->dimensions)) {
            $dimensionStandards = $allStandards[$item->part_number];
            $isAnyInvalid = false;
            $hasValidDimensions = false; // Track if there are any filled dimensions

            foreach ($request->dimensions as $cavity => $points) {
                if (!is_array($points))
                    continue;
                foreach ($points as $point => $value) {
                    if (isset($dimensionStandards[$point]) && $value !== null && $value !== '' && is_numeric($value)) {
                        $hasValidDimensions = true; // At least one dimension is filled
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
                if ($isAnyInvalid)
                    break;
            }

            // Auto-set judgment based on dimension validation
            if ($hasValidDimensions) {
                if ($isAnyInvalid) {
                    $validated['judgment'] = 'NG';
                } else {
                    $validated['judgment'] = 'OK';
                }
            }
        }
        // --- End Validation ---

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

        // Proses Dimensi menjadi JSON, filter nilai yang kosong
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
            'plant' => auth()->user()->plant,
            'item_id' => $validated['item_id'],
            'date' => $validated['date'],
            'shift' => $validated['shift'],
            'code_machine' => $validated['code_machine'],
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
            'next_proses' => $validated['next_proses'] ?? null,
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
        $query = InProcessChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::orderBy('name')->get();
        return view('in_process.edit', [
            'checksheet' => $checksheet,
            'items' => $items,
            'partDimensionStandards' => json_encode($this->getConsolidatedStandards())
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
            'code_machine' => 'required|string', // Validasi Mesin
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
            'next_proses' => 'nullable|string',
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

        // --- Centralized Server-Side Dimension Validation (same as store) ---
        $item = Item::find($validated['item_id']);
        $allStandards = $this->getConsolidatedStandards();
        if ($item && isset($allStandards[$item->part_number]) && !empty($request->dimensions)) {
            $dimensionStandards = $allStandards[$item->part_number];
            $isAnyInvalid = false;
            $hasValidDimensions = false;

            foreach ($request->dimensions as $cavity => $points) {
                if (!is_array($points))
                    continue;
                foreach ($points as $point => $value) {
                    if (isset($dimensionStandards[$point]) && $value !== null && $value !== '' && is_numeric($value)) {
                        $hasValidDimensions = true;
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
                if ($isAnyInvalid)
                    break;
            }

            // Auto-set judgment based on dimension validation
            if ($hasValidDimensions) {
                if ($isAnyInvalid) {
                    $validated['judgment'] = 'NG';
                } else {
                    $validated['judgment'] = 'OK';
                }
            }
        }
        // --- End Validation ---

        $updateData = [
            'item_id' => $validated['item_id'],
            'date' => $validated['date'],
            'shift' => $validated['shift'],
            'code_machine' => $validated['code_machine'], // Add code_machine
            'total_qty' => $validated['total_qty'],
            'sampling_qty' => $validated['sampling_qty'],
            'total_ok' => $validated['total_ok'],
            'total_ng' => $validated['total_ng'],
            'judgment' => $validated['judgment'],
            'operator_initials' => $validated['operator_initials'],
            'remarks' => $validated['remarks'],
            'dimension_check' => $dimensionCheck,
            'next_proses' => $validated['next_proses'] ?? null,
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

                $updateData['cycle_time'] = $before->diffInSeconds($after);
            } else {
                // If jam_before or jam_after not provided, use the form value
                $updateData['cycle_time'] = $validated['cycle_time'] ?? null;
            }
        } else {
            // Inspector can't change time, use the form value
            $updateData['cycle_time'] = $validated['cycle_time'] ?? null;
        }

        $checksheet->update($updateData);

        return redirect()->route('in_process.index')->with('success', 'Data Checksheet Inprocess berhasil diperbarui.');
    }

    // Delete Checksheet
    public function destroy($id)
    {
        $query = InProcessChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);
        $checksheet->delete();

        return redirect()->route('in_process.index')->with('success', 'Data Checksheet Inprocess berhasil dihapus.');
    }





    // Export Checksheets to PDF
    public function exportPdf(Request $request)
    {
        // Reuse the query logic from the index method
        $query = InProcessChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('approval_status') && $request->approval_status != '') {
            if ($request->approval_status === 'Pending') {
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
                $query->where(function ($q) {
                    $q->where('approval_status', 'Approved')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('approval_status')
                                ->whereNotNull('supervisor_qc')
                                ->where('supervisor_qc', '!=', 'REJECTED');
                        });
                });
            } elseif ($request->approval_status === 'Rejected') {
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

        $checksheets = $query->get(); // Get all results, not paginated
        $items = Item::orderBy('name')->get();

        $pdf = Pdf::loadView('in_process.pdf', compact('checksheets', 'items', 'request'));
        return $pdf->setPaper('a4', 'landscape')->stream('laporan-checksheet-inprocess.pdf');
    }

    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);
        return view('in_process.edit_approval', compact('checksheet'));
    }

    // Update status approval oleh admin
    public function updateApproval(Request $request, $id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);
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

        return redirect()->route('in_process.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('success', 'Status approval berhasil diperbarui oleh Admin.');
    }
}
