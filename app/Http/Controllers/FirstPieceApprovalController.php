<?php

namespace App\Http\Controllers;

use App\Models\FirstPieceApproval;
use App\Models\Item;
use App\Services\FirstPieceApprovalService;
use App\Http\Requests\StoreFirstPieceApprovalRequest;
use App\Http\Requests\UpdateFirstPieceApprovalRequest;
use Illuminate\Http\Request;
use App\Helpers\ShiftHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ActivityLogger;

class FirstPieceApprovalController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $firstPieceService;

    public function __construct(FirstPieceApprovalService $firstPieceService)
    {
        $this->firstPieceService = $firstPieceService;
    }

    protected function getModelClass()
    {
        return FirstPieceApproval::class;
    }

    protected function getGoogleSheetName()
    {
        return 'Sheet_FPA'; // Suggested name
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


    private function getConsolidatedStandards()
    {
        return $this->firstPieceService->getConsolidatedStandards();
    }

    public function index(Request $request)
    {
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];

        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $filters = [
            'plant' => $request->get('plant'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'approval_status' => $request->approval_status,
            'item_id' => $request->item_id,
            'operator_initials' => $request->operator_initials,
            'customer' => $request->customer,
            'next_proses' => $request->next_proses,
            'id' => $request->id,
            'shift' => $request->shift,
            'code_machine' => $request->code_machine,
            'search' => $request->search,
        ];

        $checksheets = $this->firstPieceService->getFilteredChecksheets($filters);
        $partDimensionStandards = \Illuminate\Support\Facades\Cache::remember("fpa_standards", 43200, function () {
            return $this->getConsolidatedStandards();
        });

        // Data for filters (Cached per plant to avoid 4x subquery scans on every page load)
        $plantId = \App\Models\Plant::resolveId($filters['plant']);
        
        $items = \Illuminate\Support\Facades\Cache::remember("fpa_filter_items_v2_{$plantId}", 1800, function () use ($plantId) {
            $usedItemIds = \App\Models\FirstPieceApproval::where('plant_id', $plantId)
                ->whereNotNull('item_id')
                ->distinct()
                ->pluck('item_id');
            return Item::whereIn('id', $usedItemIds)->orderBy('name')->get();
        });

        $customers = \Illuminate\Support\Facades\Cache::remember("fpa_filter_cust_v2_{$plantId}", 1800, function () use ($plantId) {
            $usedItemIds = \App\Models\FirstPieceApproval::where('plant_id', $plantId)
                ->whereNotNull('item_id')
                ->distinct()
                ->pluck('item_id');
            return Item::whereIn('id', $usedItemIds)
                ->whereNotNull('customer')
                ->where('customer', '!=', '')
                ->distinct()
                ->pluck('customer')
                ->sort();
        });

        $initials = \Illuminate\Support\Facades\Cache::remember("fpa_filter_init_{$plantId}", 1800, function () use ($plantId) {
            return \App\Models\FirstPieceApproval::where('plant_id', $plantId)
                ->where('date', '>=', now()->subDays(90))
                ->whereNotNull('operator_initials')
                ->distinct()
                ->pluck('operator_initials')
                ->sort();
        });

        $machines = \Illuminate\Support\Facades\Cache::remember("fpa_filter_mach_{$plantId}", 3600, function () use ($plantId) {
            return \App\Models\FirstPieceApproval::where('plant_id', $plantId)
                ->whereNotNull('code_machine')
                ->distinct()
                ->pluck('code_machine')
                ->sort(SORT_NUMERIC)
                ->values();
        });

        return view('first_piece_approval.index', compact('checksheets', 'partDimensionStandards', 'items', 'customers', 'initials', 'machines'));
    }

    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = Item::byCategory('INPROSES')->orderBy('name');

        $user = auth()->user();
        $canSwitchPlants = ['admin', 'supervisor', 'supervisor_plating', 'manager', 'manager_qc', 'manager_plating', 'kashift', 'asst_manager'];

        if (in_array($user->role, $canSwitchPlants)) {
            if ($request->has('plant')) {
                $query->where('plant_id', \App\Models\Plant::resolveId($request->query('plant')));
            }
        } else {
            $query->where('plant_id', $user->plant_id);
        }

        $items = $query->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        $plant = \App\Models\Plant::resolveId($request->query('plant') ?? $user->plant_id);
        $nextProcesses = \App\Models\NextProcess::where('plant_id', $plant)
            ->where('module', 'first_piece_approval')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('first_piece_approval.create', [
            'items' => $items,
            'defaultDate' => $defaultDate,
            'defaultShift' => $defaultShift,
            'plant' => $plant,
            'nextProcesses' => $nextProcesses,
            'partDimensionStandards' => $this->getConsolidatedStandards(),
            'fpaCategories' => \App\Models\GeneralSetting::getFpaCategories()
        ]);
    }

    public function store(StoreFirstPieceApprovalRequest $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $result = $this->firstPieceService->createChecksheet(
                $request->validated(),
                fn($c) => $this->mapExportRow($c)
            );
            $checksheet = $result['checksheet'] ?? null;
            if ($checksheet) {
                // --- Otomatis kelola defect "Dimensi" (Inisialisasi) ---
                $checksheet = $this->syncNgDimensiDefect($checksheet, 'OK', $checksheet->judgment, $result['ok_points_count'] ?? null, $result['ng_points_count'] ?? null);
                $checksheet->save();

                ActivityLogger::log('created', $checksheet, "Menambahkan checksheet First Piece Approval baru: {$checksheet->item->name}");
            }

            $message = 'Data First Piece Approval berhasil disimpan.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('first_piece_approval.index', ['plant' => $request->input('plant')])
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = FirstPieceApproval::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::byCategory('INPROSES')
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();
        $partDimensionStandards = $this->getConsolidatedStandards();

        $users = \App\Models\User::where('is_active', true)
            ->whereIn('role', ['admin', 'inspector', 'supervisor', 'kashift'])
            ->orderBy('name')
            ->get();

        $nextProcesses = \App\Models\NextProcess::where('plant_id', $checksheet->plant_id)
            ->where('module', 'first_piece_approval')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $fpaCategories = \App\Models\GeneralSetting::getFpaCategories();

        if (request()->ajax()) {
            return view('first_piece_approval.partials.edit_form', [
                'checksheet' => $checksheet,
                'items' => $items,
                'partDimensionStandards' => $partDimensionStandards,
                'users' => $users,
                'nextProcesses' => $nextProcesses,
                'fpaCategories' => $fpaCategories
            ]);
        }

        return view('first_piece_approval.edit', [
            'checksheet' => $checksheet,
            'items' => $items,
            'partDimensionStandards' => $partDimensionStandards,
            'users' => $users,
            'nextProcesses' => $nextProcesses,
            'fpaCategories' => $fpaCategories
        ]);
    }

    public function update(UpdateFirstPieceApprovalRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $validatedData = $request->validated();
            $checksheet = FirstPieceApproval::findOrFail($id);
            $oldJudgment = $checksheet->judgment;
            
            $result = $this->firstPieceService->updateChecksheet($id, $validatedData);
            
            // Refresh data setelah update di service
            $checksheet->refresh();
            $newJudgment = $checksheet->judgment;

            // --- Otomatis kelola defect "Dimensi" (Update) ---
            $this->syncNgDimensiDefect($checksheet, $oldJudgment, $newJudgment, $result['ok_points_count'] ?? null, $result['ng_points_count'] ?? null);
            
            // PENTING: Lakukan save() manual karena syncNgDimensiDefect merubah model tanpa menyimpan
            $checksheet->save(); 

            ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet First Piece Approval: {$checksheet->item->name}");

            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'shift'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data First Piece Approval berhasil diperbarui.',
                    'redirect' => route('first_piece_approval.index', $redirectParams)
                ]);
            }

            return redirect()->route('first_piece_approval.index', $redirectParams)->with('success', 'Data First Piece Approval berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        try {
            $checksheet = FirstPieceApproval::find($id);
            $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
            $this->firstPieceService->deleteChecksheet($id);
            ActivityLogger::log('deleted', null, "Menghapus checksheet First Piece Approval: {$itemName}");

            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'shift'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data First Piece Approval berhasil dihapus.',
                    'redirect' => route('first_piece_approval.index', $redirectParams)
                ]);
            }

            return redirect()->route('first_piece_approval.index', $redirectParams)
                ->with('success', 'Data First Piece Approval berhasil dihapus.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];
        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $filters = $request->only(['id', 'start_date', 'end_date', 'approval_status', 'item_id', 'operator_initials', 'customer', 'part_no', 'search', 'plant', 'shift']);

        if (empty($filters['start_date'])) {
            $filters['start_date'] = now()->toDateString();
        }
        if (empty($filters['end_date'])) {
            $filters['end_date'] = now()->toDateString();
        }

        $query = $this->firstPieceService->buildFilteredQuery($filters)->latest();

        if ($request->has('page')) {
            $checksheets = $query->paginate(10)->getCollection();
        } else {
            $checksheets = $query->get();
        }
        $items = Item::orderBy('name')->get();
        $partDimensionStandards = $this->getConsolidatedStandards();

        $user = auth()->user();
        $plantCode = 'karawang';
        $plantName = 'Karawang';

        if ($request->plant) {
            $plant = \App\Models\Plant::where('code', $request->plant)->orWhere('id', $request->plant)->first();
            if ($plant) {
                $plantCode = strtolower($plant->code);
                $plantName = $plant->name;
            }
        } elseif ($user->plant) {
            $plantCode = strtolower($user->plant->code);
            $plantName = $user->plant->name;
        }

        $startDate = \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y');
        $endDate = \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y');

        $pdf = Pdf::loadView('first_piece_approval.pdf', compact('checksheets', 'items', 'request', 'partDimensionStandards', 'startDate', 'endDate', 'plantName', 'plantCode'));
        return $pdf->setPaper('a4', 'landscape')->download('Laporan_FirstPieceApproval_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function editApproval($id)
    {
        $checksheet = FirstPieceApproval::findOrFail($id);
        if (request()->ajax()) {
            return view('first_piece_approval.partials.edit_approval_form', compact('checksheet'));
        }
        return view('first_piece_approval.edit_approval', compact('checksheet'));
    }

    public function updateApproval(Request $request, $id)
    {
        $validated = $request->validate([
            'kashift_qc' => 'required|in:Pending,Approved,Rejected',
            'supervisor_qc' => 'required|in:Pending,Approved,Rejected',
            'asst_manager_qc' => 'required|in:Pending,Approved,Rejected',
            'manager_qc' => 'required|in:Pending,Approved,Rejected',
        ]);

        try {
            $this->firstPieceService->updateApprovalStatus($id, $validated);
            $checksheet = FirstPieceApproval::find($id);

            // Jika status dirubah menjadi Rejected melalui modal admin, kirim notifikasi dan berikan remarks
            if ($checksheet->approval_status === 'Rejected' && empty($checksheet->rejection_remarks)) {
                $checksheet->rejection_remarks = "[Admin] Status dirubah menjadi Rejected via Edit Status - " . auth()->user()->name . " (" . now()->format('d/m/Y H:i') . ")";
                $checksheet->save();

                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->notifyRejection($checksheet, 'First Piece Approval', auth()->user()->name);
                } catch (\Exception $ne) {
                    \Illuminate\Support\Facades\Log::error('Gagal kirim notifikasi rejection FPA: ' . $ne->getMessage());
                }
            }

            ActivityLogger::log('updated', $checksheet, "Memperbarui status approval (Admin) pada checksheet First Piece Approval: {$checksheet->item->name}");

            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'shift'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('success', 'Status approval berhasil diperbarui oleh Admin.');
                return response()->json([
                    'success' => true,
                    'message' => 'Status approval berhasil diperbarui oleh Admin.',
                    'redirect' => route('first_piece_approval.index', $redirectParams)
                ]);
            }

            return redirect()->route('first_piece_approval.index', $redirectParams)->with('success', 'Status approval berhasil diperbarui oleh Admin.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui status approval: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui status approval: ' . $e->getMessage());
        }
    }

    public function printView(Request $request)
    {
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];
        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $filters = [
            'plant' => $request->get('plant'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'approval_status' => $request->approval_status,
            'item_id' => $request->item_id,
            'operator_initials' => $request->operator_initials,
            'customer' => $request->customer,
            'next_proses' => $request->next_proses,
            'id' => $request->id,
            'shift' => $request->shift,
            'code_machine' => $request->code_machine,
            'search' => $request->search,
            'part_no' => $request->part_no,
        ];

        if (empty($filters['start_date']) && empty($filters['end_date']) && 
            empty($filters['item_id']) && empty($filters['operator_initials']) && 
            empty($filters['customer']) && empty($filters['part_no']) && 
            empty($filters['search'])) {
            $filters['start_date'] = now()->toDateString();
            $filters['end_date'] = now()->toDateString();
        }

        $checksheets = $this->firstPieceService->buildFilteredQuery($filters)->latest()->get();

        $partDimensionStandards = $this->getConsolidatedStandards();

        $user = auth()->user();
        $plantCode = 'karawang';
        $plantName = 'Karawang';

        if ($request->plant) {
            $plant = \App\Models\Plant::where('code', $request->plant)->orWhere('id', $request->plant)->first();
            if ($plant) {
                $plantCode = strtolower($plant->code);
                $plantName = $plant->name;
            }
        } elseif ($user->plant) {
            $plantCode = strtolower($user->plant->code);
            $plantName = $user->plant->name;
        }

        // For display labels: use provided dates or show 'Semua'
        $startDate = !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') : 'Semua';
        $endDate   = !empty($filters['end_date'])   ? \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y')   : 'Semua';

        return view('first_piece_approval.print', compact('checksheets', 'partDimensionStandards', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }

    /**
     * Rekap Harian FPA — Distribusi Beban Jam (24 Jam)
     */
    public function dailyRecap(Request $request)
    {
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];
        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $date = $request->get('date', now()->toDateString());
        $plant = $request->get('plant');

        $filters = [
            'date'  => $date,
            'plant' => $plant,
        ];

        $recap = $this->firstPieceService->getHourlyDistribution($filters);

        // Resolve plant name
        $user = auth()->user();
        $plantName = 'Karawang';
        if ($plant) {
            $plantModel = \App\Models\Plant::where('code', $plant)->orWhere('id', $plant)->first();
            if ($plantModel) {
                $plantName = $plantModel->name;
            }
        } elseif ($user->plant) {
            $plantName = $user->plant->name;
        }

        // Calculate overall avg cycle time (weighted)
        $totalSeconds = 0;
        $totalWithCt  = 0;
        foreach ($recap['distribution'] as $slot) {
            if ($slot['avg_cycle_time_seconds'] !== null && $slot['count'] > 0) {
                $totalSeconds += $slot['avg_cycle_time_seconds'] * $slot['count'];
                $totalWithCt  += $slot['count'];
            }
        }
        $overallAvgCt = $totalWithCt > 0 ? round($totalSeconds / $totalWithCt) : null;

        // Find peak hour label
        $peakHours = array_filter($recap['distribution'], fn($s) => $s['is_peak'] && $s['count'] > 0);
        $peakLabel = collect($peakHours)->map(fn($s) => sprintf('%02d:00', $s['hour']))->implode(', ');

        return view('first_piece_approval.daily_recap', compact(
            'recap',
            'date',
            'plantName',
            'overallAvgCt',
            'peakLabel'
        ));
    }


    public function exportMeasureData(Request $request)
    {
        $plantId = \App\Models\Plant::resolveId($request->get('plant') ?: auth()->user()->plant_id);

        $filters = $request->only(['id', 'start_date', 'end_date', 'approval_status', 'item_id', 'operator_initials', 'customer', 'part_no', 'plant', 'shift']);
        $filters['plant'] = $plantId;

        $query = $this->firstPieceService->buildFilteredQuery($filters)->latest();
        $checksheets = $query->get();

        $maxPoints = 20;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pengukuran FPA');

        // --- Header row ---
        $headers = ['Checksheet ID', 'Tanggal', 'Part Name', 'Part Number', 'Cavity'];
        for ($i = 1; $i <= $maxPoints; $i++) {
            $headers[] = "P$i";
        }
        $sheet->fromArray($headers, null, 'A1');

        // Style header: bold + background light blue (FPA style)
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4E73DF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // --- Data rows ---
        $rowIndex = 2;
        foreach ($checksheets as $c) {
            $dims = $c->dimension_check;
            if (is_string($dims)) {
                $dims = json_decode($dims, true);
                if (is_string($dims)) $dims = json_decode($dims, true);
            }
            $dims = is_array($dims) ? $dims : [];

            $dateStr = $c->date instanceof \Carbon\Carbon
                ? $c->date->format('Y-m-d')
                : ($c->date ? date('Y-m-d', strtotime($c->date)) : '');

            if (empty($dims)) {
                $row = [(int)$c->id, $dateStr, $c->item->name ?? '', $c->item->part_number ?? '', 1];
                for ($i = 1; $i <= $maxPoints; $i++) $row[] = '';
                $sheet->fromArray($row, null, "A{$rowIndex}");
                $rowIndex++;
            } else {
                foreach ($dims as $cavity => $points) {
                    if (!is_array($points)) continue;
                    $row = [(int)$c->id, $dateStr, $c->item->name ?? '', $c->item->part_number ?? '', (int)$cavity];
                    for ($i = 1; $i <= $maxPoints; $i++) {
                        $val = $points[$i] ?? $points["$i"] ?? '';
                        $row[] = is_numeric($val) ? (float)$val : $val;
                    }
                    $sheet->fromArray($row, null, "A{$rowIndex}");
                    $rowIndex++;
                }
            }
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        for ($i = 6; $i <= count($headers); $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(9);
        }

        $sheet->freezePane('A2');

        $filename = "data_pengukuran_fpa_" . date('Y-m-d_H-i-s') . ".xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Impor Data Pengukuran (Actual) dari file XLSX atau CSV
     */
    public function importMeasureData(Request $request)
    {
        $request->validate(['file' => 'required']);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'xlsx' || $extension === 'xls') {
            return $this->importMeasureDataFromXlsx($file);
        }

        // --- Path CSV (Fallback) ---
        $raw = file_get_contents($file->getRealPath());
        $clean = str_replace("\0", "", $raw);
        $clean = preg_replace('/^(\xEF\xBB\xBF|\xFF\xFE|\xFE\xFF)/', '', $clean);
        $clean = str_replace(["\r\n", "\r"], "\n", $clean);
        $lines = explode("\n", $clean);
        
        $dataById = [];
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $bestRow = [];
            foreach ([',', ';', "\t"] as $delim) {
                $row = str_getcsv($line, $delim);
                if (count($row) === 1 && !empty($row[0]) && (substr_count($row[0], $delim) > 3)) {
                    $row = str_getcsv($row[0], $delim);
                }
                if (count($row) > count($bestRow)) { $bestRow = $row; }
            }
            $row = $bestRow;
            if (count($row) < 5) continue;
            
            $idRaw = trim($row[0]);
            $id = preg_replace('/[^0-9]/', '', $idRaw);
            if (empty($id) || !is_numeric($id)) continue;
            
            $cavity = trim($row[4]);
            if (!isset($dataById[$id])) $dataById[$id] = [];
            
            $points = [];
            for ($i = 5; $i < count($row); $i++) {
                $pointIndex = $i - 4; 
                $val = trim($row[$i], " \t\n\r\0\x0B\"");
                $val = str_replace(',', '.', $val);
                if ($val !== '' && $val !== '-') { $points[$pointIndex] = $val; }
            }
            $dataById[$id][$cavity] = $points;
        }

        if (empty($dataById)) {
            return redirect()->back()->with('warning', "Format file tidak dapat dikenali. Pastikan file Anda mengandung kolom ID Laporan di awal.");
        }

        return $this->processImportedData($dataById);
    }

    private function importMeasureDataFromXlsx($file)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file XLSX: ' . $e->getMessage());
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false);

        $dataById = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
            if (count($row) < 5) continue;

            $idRaw = trim((string)($row[0] ?? ''));
            $id    = preg_replace('/[^0-9]/', '', $idRaw);
            if (empty($id) || !is_numeric($id)) continue;

            $cavity = trim((string)($row[4] ?? '1'));
            if (!isset($dataById[$id])) $dataById[$id] = [];

            $points = [];
            for ($i = 5; $i < count($row); $i++) {
                $pointIndex = $i - 4;
                $val = trim(str_replace(',', '.', (string)($row[$i] ?? '')));
                if ($val !== '' && $val !== '-') { $points[$pointIndex] = $val; }
            }
            $dataById[$id][$cavity] = $points;
        }

        if (empty($dataById)) {
            return redirect()->back()->with('warning', 'Format file XLSX tidak dapat dikenali.');
        }

        return $this->processImportedData($dataById);
    }

    private function processImportedData($dataById)
    {
        $updatedCount = 0;
        \DB::beginTransaction();
        try {
            foreach ($dataById as $id => $measurements) {
                $checksheet = FirstPieceApproval::withoutGlobalScope('plant')->find($id);
                if ($checksheet) {
                    $oldJudgment = $checksheet->judgment;
                    $checksheet->dimension_check = $measurements;
                    
                    $currentDefects = $checksheet->defects;
                    if (is_string($currentDefects)) $currentDefects = json_decode($currentDefects, true) ?? [];
                    $baseTotalNg = 0;
                    if (is_array($currentDefects)) {
                        foreach ($currentDefects as $d) {
                            $type = $d['type'] ?? '';
                            if ($type !== 'Dimensi' && $type !== 'NG Dimensi') { $baseTotalNg += (int)($d['qty'] ?? 0); }
                        }
                    }

                    $dataToValidate = ['dimensions' => $measurements, 'total_ng' => $baseTotalNg];
                    $validated = $this->firstPieceService->validateDimensions($dataToValidate, $checksheet->item_id);
                    $newJudgment = $validated['judgment'] ?? $oldJudgment;
                    $checksheet->judgment = $newJudgment;

                    $checksheet = $this->syncNgDimensiDefect($checksheet, $oldJudgment, $newJudgment, $validated['ok_points_count'] ?? null, $validated['ng_points_count'] ?? null);
                    $checksheet->save();
                    $updatedCount++;
                    ActivityLogger::log('updated', $checksheet, "Import FPA: Sukses update ID $id (Judgment: {$oldJudgment} → {$newJudgment})");
                }
            }
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil! {$updatedCount} data FPA telah diperbarui secara massal."
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    private function syncNgDimensiDefect($checksheet, string $oldJudgment, string $newJudgment, ?int $okPoints = null, ?int $ngPoints = null)
    {
        $defects = $checksheet->defects;
        if (is_string($defects)) { $defects = json_decode($defects, true) ?? []; }
        if (!is_array($defects)) { $defects = []; }

        $isDimensiType = function($t) {
            $key = strtolower(trim((string)$t));
            return in_array($key, ['dimensi', 'dimension', 'ng dimensi']);
        };

        // Hitung base total NG (tanpa Dimensi)
        $baseTotalNg = 0;
        $hasExistingDimensi = false;
        foreach ($defects as $d) {
            if (is_array($d)) {
                $type = $d['type'] ?? '';
                if ($isDimensiType($type)) {
                    $hasExistingDimensi = true;
                } else {
                    $baseTotalNg += (int)($d['qty'] ?? 0);
                }
            }
        }

        // Determine if Dimensi defect should exist
        $shouldHaveDimensi = false;
        if ($ngPoints !== null) {
            // If we have explicit validation results, trust ngPoints
            $shouldHaveDimensi = ($ngPoints > 0);
        } else {
            // Fallback for when there is no dimension validation result
            $shouldHaveDimensi = ($newJudgment === 'NG') ? $hasExistingDimensi : false;
        }

        if ($shouldHaveDimensi) {
            $found = false;
            foreach ($defects as &$defect) {
                if (is_array($defect) && isset($defect['type']) && $isDimensiType($defect['type'])) {
                    $defect['type'] = 'Dimensi';
                    $found = true;
                    break;
                }
            }
            unset($defect);

            $qty = 1;
            if (!$found) {
                $defects[] = ['type' => 'Dimensi', 'qty' => $qty];
            } else {
                foreach ($defects as &$d) {
                    if (is_array($d) && $isDimensiType($d['type'] ?? '')) {
                        $d['type'] = 'Dimensi';
                        $d['qty'] = $qty;
                    }
                }
            }
            $checksheet->total_ng = $baseTotalNg + $qty;
        } else {
            // Dimension measurements are OK - remove ALL dimension defect entries
            $defects = array_values(array_filter($defects, function ($defect) use ($isDimensiType) {
                if (is_array($defect) && $isDimensiType($defect['type'] ?? '')) {
                    return false; // hapus dari list
                }
                return true;
            }));

            $checksheet->total_ng = $baseTotalNg; // Reset ke base (tanpa dimensi)
        }

        // Sinkronisasi OK = Sampling Qty - Total NG
        $samplingQty = (int) ($checksheet->sampling_qty ?? 0);
        $totalNg = (int) ($checksheet->total_ng ?? 0);
        $checksheet->total_ok = max(0, $samplingQty - $totalNg);
        
        // Re-evaluate judgment: if total_ng > 0 then NG, otherwise OK
        $checksheet->judgment = ($checksheet->total_ng > 0) ? 'NG' : 'OK';

        $checksheet->defects = $defects; // Cast handled by model
        return $checksheet;
    }
}
