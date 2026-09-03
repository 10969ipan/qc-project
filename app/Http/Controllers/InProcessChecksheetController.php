<?php

namespace App\Http\Controllers;

use App\Models\InProcessChecksheet;
use App\Models\Item;
use App\Services\InProcessChecksheetService;
use App\Http\Requests\StoreInProcessChecksheetRequest;
use App\Http\Requests\UpdateInProcessChecksheetRequest;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;
use App\Helpers\ShiftHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ActivityLogger;

class InProcessChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $inProcessService;

    public function __construct(InProcessChecksheetService $inProcessService)
    {
        $this->inProcessService = $inProcessService;
    }

    protected function getModelClass()
    {
        return InProcessChecksheet::class;
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


    // Get consolidated standards from service
    private function getConsolidatedStandards()
    {
        return $this->inProcessService->getConsolidatedStandards();
    }

    public function index(Request $request)
    {
        // For restricted roles (inspector, plating), override request plant to their own plant
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
            'qr_raw' => $request->qr_raw,
            'entry_method' => $request->entry_method,
            'shift' => $request->shift,
            'tujuan' => $request->tujuan,
            'view_mode' => $request->get('view_mode'),
            'code_machine' => $request->code_machine,
        ];

        // Default: hanya tampilkan data regular, kecuali mode verifikasi aktif
        if ($request->get('view_mode') !== 'verifikasi') {
            $filters['entry_method'] = 'regular';
        }

        $checksheets = $this->inProcessService->getFilteredChecksheets($filters);

        $partDimensionStandards = \Illuminate\Support\Facades\Cache::remember("in_proc_standards", 43200, function () {
            return $this->getConsolidatedStandards();
        });

        // Data for filters (Cached per plant to avoid 4x subquery scans over 28,000+ rows on every page load)
        $plantId = \App\Models\Plant::resolveId($filters['plant']);
        
        $items = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_items_{$plantId}", 1800, function () use ($plantId) {
            return Item::where('plant_id', $plantId)->orderBy('name')->get();
        });

        $customers = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_cust_{$plantId}", 1800, function () use ($plantId) {
            return Item::where('plant_id', $plantId)
                ->whereNotNull('customer')
                ->where('customer', '!=', '')
                ->distinct()
                ->pluck('customer')
                ->sort();
        });

        $initials = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_init_{$plantId}", 1800, function () use ($plantId) {
            return InProcessChecksheet::where('plant_id', $plantId)
                ->where('date', '>=', now()->subDays(90))
                ->whereNotNull('operator_initials')
                ->distinct()
                ->pluck('operator_initials')
                ->sort();
        });

        $machines = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_mach_{$plantId}", 3600, function () use ($plantId) {
            return InProcessChecksheet::where('plant_id', $plantId)
                ->whereNotNull('code_machine')
                ->distinct()
                ->pluck('code_machine')
                ->sort(SORT_NUMERIC)
                ->values();
        });

        return view('in_process.index', compact('checksheets', 'partDimensionStandards', 'items', 'customers', 'initials', 'machines'));
    }

    // Show form (updated to pass items)
    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = Item::byCategory('INPROSES')->orderBy('name');

        // Filter items based on plant context
        $user = auth()->user();

        // Roles that can switch between plants via request parameter
        $canSwitchPlants = ['admin', 'supervisor', 'supervisor_plating', 'manager', 'manager_qc', 'manager_plating', 'kashift', 'asst_manager'];

        if (in_array($user->role, $canSwitchPlants)) {
            // These roles can filter by request plant parameter
            if ($request->has('plant')) {
                $query->where('plant_id', \App\Models\Plant::resolveId($request->query('plant')));
            }
        } else {
            // Inspector and other restricted roles: always filter by their own plant
            $query->where('plant_id', $user->plant_id);
        }

        $items = $query->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        $plant = \App\Models\Plant::resolveId($request->query('plant') ?? $user->plant_id);
        $nextProcesses = \App\Models\NextProcess::where('plant_id', $plant)
            ->where('module', 'in_process')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('in_process.create', [
            'items' => $items,
            'defaultDate' => $defaultDate,
            'defaultShift' => $defaultShift,
            'plant' => $plant,
            'nextProcesses' => $nextProcesses,
            'partDimensionStandards' => json_encode($this->getConsolidatedStandards())
        ]);
    }

    // Simpan data (submission)
    public function store(StoreInProcessChecksheetRequest $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $result = $this->inProcessService->createChecksheet(
                $request->validated(),
                fn($c) => $this->mapExportRow($c)
            );
            if ($result['checksheet']) {
                $checksheet = $result['checksheet'];
                ActivityLogger::log('created', $checksheet, "Menambahkan checksheet In Process baru: {$checksheet->item->name}");
            }

            $message = 'Data Checksheet Inprocess berhasil disimpan.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('in_process.index', ['plant' => $request->input('plant')])
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

    // Edit Checksheet
    public function edit($id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = InProcessChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::byCategory('INPROSES')
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();
        $partDimensionStandards = json_encode($this->getConsolidatedStandards());

        $users = \App\Models\User::where('is_active', true)
            ->whereIn('role', ['admin', 'inspector', 'supervisor', 'kashift'])
            ->orderBy('name')
            ->get();

        $nextProcesses = \App\Models\NextProcess::where('plant_id', $checksheet->plant_id)
            ->where('module', 'in_process')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        if (request()->ajax()) {
            return view('in_process.partials.edit_form', [
                'checksheet' => $checksheet,
                'items' => $items,
                'partDimensionStandards' => $partDimensionStandards,
                'users' => $users,
                'nextProcesses' => $nextProcesses
            ]);
        }

        return view('in_process.edit', [
            'checksheet' => $checksheet,
            'items' => $items,
            'partDimensionStandards' => $partDimensionStandards,
            'users' => $users,
            'nextProcesses' => $nextProcesses
        ]);
    }

    // Update Checksheet
    public function update(UpdateInProcessChecksheetRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $validatedData = $request->validated();

            // Log data yang diterima untuk debugging
            \Log::info('Update Checksheet In Process', [
                'checksheet_id' => $id,
                'user_id' => auth()->id(),
                'validated_data' => $validatedData,
                'has_dimensions' => isset($validatedData['dimensions']),
                'has_defects' => isset($validatedData['defect_types']),
                'dimension_count' => isset($validatedData['dimensions']) ? count($validatedData['dimensions']) : 0,
                'defect_count' => isset($validatedData['defect_types']) ? count($validatedData['defect_types']) : 0,
            ]);

            $this->inProcessService->updateChecksheet($id, $validatedData);
            $checksheet = \App\Models\InProcessChecksheet::find($id);

            ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet In Process: {$checksheet->item->name}");

            // Only preserve specific navigation and filter parameters
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'shift', 'view_mode'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Checksheet Inprocess berhasil diperbarui.',
                    'redirect' => route('in_process.index', $redirectParams)
                ]);
            }

            return redirect()->route('in_process.index', $request->query())->with('success', 'Data Checksheet Inprocess berhasil diperbarui.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Gagal update Checksheet In Process', [
                'checksheet_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    // Delete Checksheet
    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        try {
            $checksheet = InProcessChecksheet::find($id);
            $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
            $this->inProcessService->deleteChecksheet($id);
            ActivityLogger::log('deleted', null, "Menghapus checksheet In Process: {$itemName}");

            // Only preserve specific navigation and filter parameters
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'shift', 'view_mode'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Checksheet Inprocess berhasil dihapus.',
                    'redirect' => route('in_process.index', $redirectParams)
                ]);
            }

            return redirect()->route('in_process.index', $request->query())
                ->with('success', 'Data Checksheet Inprocess berhasil dihapus.');
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

    public function bulkDestroy(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);
        }

        try {
            \DB::beginTransaction();
            foreach ($ids as $id) {
                $this->inProcessService->deleteChecksheet($id);
            }
            \DB::commit();

            ActivityLogger::log('deleted', null, 'Menghapus multiple checksheet In Process');

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' data berhasil dihapus.',
                'redirect' => route('in_process.index', $request->query())
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }



    // Export Checksheets to PDF
    public function exportPdf(Request $request)
    {
        // For restricted roles (inspector, plating), override request plant to their own plant
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];
        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'operator_initials', 'customer', 'part_no', 'search', 'plant', 'entry_method', 'shift']);

        if (empty($filters['start_date']) && empty($filters['end_date']) && 
            empty($filters['item_id']) && empty($filters['operator_initials']) && 
            empty($filters['customer']) && empty($filters['part_no']) && 
            empty($filters['search']) && empty($filters['entry_method'])) {
            $filters['start_date'] = now()->toDateString();
            $filters['end_date'] = now()->toDateString();
        }

        // Fetch filtered records
        $query = $this->inProcessService->buildFilteredQuery($filters)->latest();

        if ($request->has('page')) {
            // Get records for the specific page
            $checksheets = $query->paginate(10)->getCollection();
        } else {
            // Default to all matches for export
            $checksheets = $query->get();
        }
        $items = Item::orderBy('name')->get();

        $partDimensionStandards = $this->getConsolidatedStandards();

        // Plant info for header
        $user = auth()->user();
        $plantCode = 'karawang'; // default
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

        $startDate = !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') : 'Semua';
        $endDate = !empty($filters['end_date']) ? \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') : 'Semua';

        $pdf = Pdf::loadView('in_process.pdf', compact('checksheets', 'items', 'request', 'partDimensionStandards', 'startDate', 'endDate', 'plantName', 'plantCode'));
        return $pdf->setPaper('a4', 'landscape')->download('Laporan_Inprocess_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);
        $nextProcesses = \App\Models\NextProcess::where('plant_id', $checksheet->plant_id)
            ->where('module', 'in_process')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        if (request()->ajax()) {
            return view('in_process.partials.edit_approval_form', compact('checksheet', 'nextProcesses'));
        }
        return view('in_process.edit_approval', compact('checksheet', 'nextProcesses'));
    }

    // Update status approval oleh admin
    public function updateApproval(Request $request, $id)
    {
        $validated = $request->validate([
            'kashift_qc' => 'required|in:Pending,Approved,Rejected',
            'supervisor_qc' => 'required|in:Pending,Approved,Rejected',
            'asst_manager_qc' => 'required|in:Pending,Approved,Rejected',
            'manager_qc' => 'required|in:Pending,Approved,Rejected',
        ]);

        try {
            $this->inProcessService->updateApprovalStatus($id, $validated);
            $checksheet = \App\Models\InProcessChecksheet::find($id);

            // Jika status dirubah menjadi Rejected melalui modal admin, kirim notifikasi dan berikan remarks
            if ($checksheet->approval_status === 'Rejected' && empty($checksheet->rejection_remarks)) {
                $checksheet->rejection_remarks = "[Admin] Status dirubah menjadi Rejected via Edit Status - " . auth()->user()->name . " (" . now()->format('d/m/Y H:i') . ")";
                $checksheet->save();

                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->notifyRejection($checksheet, 'In Process', auth()->user()->name);
                } catch (\Exception $ne) {
                    \Illuminate\Support\Facades\Log::error('Gagal kirim notifikasi rejection: ' . $ne->getMessage());
                }
            }

            ActivityLogger::log('updated', $checksheet, "Memperbarui status approval (Admin) pada checksheet In Process: {$checksheet->item->name}");

            // Only preserve specific navigation and filter parameters
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'shift', 'view_mode'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status approval berhasil diperbarui oleh Admin.',
                    'redirect' => route('in_process.index', $redirectParams)
                ]);
            }

            return redirect()->route('in_process.index', $request->query())->with('success', 'Status approval berhasil diperbarui oleh Admin.');
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
            'qr_raw' => $request->qr_raw,
            'entry_method' => $request->entry_method,
            'shift' => $request->shift,
            'tujuan' => $request->tujuan,
            'view_mode' => $request->get('view_mode'),
            'code_machine' => $request->code_machine,
            'search' => $request->search,
        ];

        // Default: hanya tampilkan data regular, kecuali mode verifikasi aktif
        if ($request->get('view_mode') !== 'verifikasi') {
            $filters['entry_method'] = 'regular';
        }

        if (empty($filters['start_date']) && empty($filters['end_date']) && 
            empty($filters['item_id']) && empty($filters['operator_initials']) && 
            empty($filters['customer']) && empty($filters['part_no']) && 
            empty($filters['search']) && empty($filters['entry_method'])) {
            $filters['start_date'] = now()->toDateString();
            $filters['end_date'] = now()->toDateString();
        }

        $checksheets = $this->inProcessService->buildFilteredQuery($filters)->latest()->get();

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

        return view('in_process.print', compact('checksheets', 'partDimensionStandards', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }

    /**
     * Report Harian: Rekap data Verification per Item & Shift
     */
    public function dailyRecap(Request $request)
    {
        $date = $request->get('start_date') ?: ($request->get('date') ?: now()->toDateString());
        $plant = $request->get('plant') ?: auth()->user()->plant_id;
        $shift = $request->get('shift');

        $filters = [
            'date' => $date,
            'plant' => $plant,
            'shift' => $shift
        ];

        $recap = $this->inProcessService->getDailyRecap($filters);
        
        $plantModel = \App\Models\Plant::where('code', $plant)->orWhere('id', $plant)->first();
        $plantCode = $plantModel ? strtolower($plantModel->code) : 'karawang';
        $plantName = $plantModel ? $plantModel->name : 'Karawang';

        return view('in_process.daily_recap', compact('recap', 'date', 'plantName', 'plantCode'));
    }

    /**
     * Ekspor Data Pengukuran (Actual) ke XLSX berdasarkan filter
     */
    public function exportMeasureData(Request $request)
    {
        $plantId = \App\Models\Plant::resolveId($request->get('plant') ?: auth()->user()->plant_id);

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'operator_initials', 'customer', 'part_no', 'plant']);
        $filters['plant'] = $plantId;

        $query = $this->inProcessService->buildFilteredQuery($filters)->latest();
        $checksheets = $query->get();

        $maxPoints = 20;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pengukuran');

        // --- Header row ---
        $headers = ['Checksheet ID', 'Tanggal', 'Part Name', 'Part Number', 'Cavity'];
        for ($i = 1; $i <= $maxPoints; $i++) {
            $headers[] = "P$i";
        }
        $sheet->fromArray($headers, null, 'A1');

        // Style header: bold + background kuning muda
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF1E293B']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF9C3']],
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

        // --- Auto-width untuk kolom pertama sampai kelima ---
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Kolom P1-P20 lebar pas
        for ($i = 6; $i <= count($headers); $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(9);
        }

        // Freeze baris header
        $sheet->freezePane('A2');

        $filename = "data_pengukuran_inprocess_" . date('Y-m-d_H-i-s') . ".xlsx";

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
        // Longgarkan validasi mime karena sering bermasalah dengan pendeteksian server
        $request->validate(['file' => 'required']);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        // --- Jalur XLSX ---
        if ($extension === 'xlsx' || $extension === 'xls') {
            return $this->importMeasureDataFromXlsx($file);
        }

        // --- Jalur CSV (existing logic, inlined below) ---

        $raw = file_get_contents($file->getRealPath());
        
        // 1. BRUTE FORCE REPAIR: Hapus semua NULL bytes (memperbaiki file UTF-16 secara paksa) 
        // dan karakter kontrol aneh lainnya
        $clean = str_replace("\0", "", $raw);
        
        // 2. Hapus BOM (Byte Order Mark) yang mungkin tersisa
        $clean = preg_replace('/^(\xEF\xBB\xBF|\xFF\xFE|\xFE\xFF)/', '', $clean);
        
        // 3. Normalisasi semua variasi baris baru ke \n standar
        $clean = str_replace(["\r\n", "\r"], "\n", $clean);
        
        $lines = explode("\n", $clean);
        
        $dataById = [];
        $totalRowsParsed = 0;
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // ELEKSI DELIMITER: Cari hasil parsing yang memberikan kolom terbanyak
            $bestRow = [];
            foreach ([',', ';', "\t"] as $delim) {
                $row = str_getcsv($line, $delim);
                
                // RECURSIVE UNBOXING: Menangani kasus di mana seluruh baris terbungkus kutip ganda
                // (Sering terjadi jika file diproses oleh tools tertentu)
                if (count($row) === 1 && !empty($row[0]) && (substr_count($row[0], $delim) > 3)) {
                    $row = str_getcsv($row[0], $delim);
                }
                
                if (count($row) > count($bestRow)) {
                    $bestRow = $row;
                }
            }
            
            $row = $bestRow;
            
            // Minimal 5 kolom awal (ID sampai Cavity)
            if (count($row) < 5) continue;
            
            // Validasi ID: Ambil hanya angka dari kolom pertama
            $idRaw = trim($row[0]);
            $id = preg_replace('/[^0-9]/', '', $idRaw);
            
            if (empty($id) || !is_numeric($id)) {
                // Baris header atau data ID kosong, lewati
                continue;
            }
            
            $cavity = trim($row[4]);
            if (!isset($dataById[$id])) $dataById[$id] = [];
            
            $points = [];
            // Titik pengukuran mulai dari kolom ke-5 (P1)
            for ($i = 5; $i < count($row); $i++) {
                $pointIndex = $i - 4; 
                $val = trim($row[$i], " \t\n\r\0\x0B\""); // Bersihkan kutip/spasi
                $val = str_replace(',', '.', $val);   // Normalisasi desimal
                
                if ($val !== '' && $val !== '-') {
                    $points[$pointIndex] = $val;
                }
            }
            
            $dataById[$id][$cavity] = $points;
            $totalRowsParsed++;
        }

        if (empty($dataById)) {
            \Log::error("Import InProcess Diagnosis: Gagal baca. Ukuran file: " . strlen($raw) . " bytes. Baris ditemukan: " . count($lines));
            return redirect()->back()->with('warning', "Format file tidak dapat dikenali (Data terbaca: 0). Mohon pastikan file Anda mengandung kolom ID Laporan di awal.");
        }

        $updatedCount = 0;
        $notFoundIds = [];
        
        \DB::beginTransaction();
        try {
            foreach ($dataById as $id => $measurements) {
                // pastikan menggunakan withoutGlobalScope
                $checksheet = InProcessChecksheet::withoutGlobalScope('plant')->find($id);
                if ($checksheet) {
                    $oldJudgment = $checksheet->judgment;
                    $checksheet->dimension_check = $measurements;
                    
                    // Hitung base total_ng (tanpa menghitung defect "Dimensi" yang mungkin sudah ada sebelumnya)
                    $currentDefects = $checksheet->defects;
                    if (is_string($currentDefects)) $currentDefects = json_decode($currentDefects, true) ?? [];
                    $baseTotalNg = 0;
                    if (is_array($currentDefects)) {
                        foreach ($currentDefects as $d) {
                            $type = $d['type'] ?? '';
                            if ($type !== 'Dimensi' && $type !== 'NG Dimensi') {
                                $baseTotalNg += (int)($d['qty'] ?? 0);
                            }
                        }
                    }

                    // Hitung otomatis status judgment menggunakan base total_ng (hanya defect non-dimensi)
                    $dataToValidate = ['dimensions' => $measurements, 'total_ng' => $baseTotalNg];
                    $validated = $this->inProcessService->validateDimensions($dataToValidate, $checksheet->item_id);
                    // Fallback ke judgment lama jika item tidak punya standar dimensi
                    $newJudgment = $validated['judgment'] ?? $oldJudgment;
                    $checksheet->judgment = $newJudgment;

                    // --- Otomatis kelola defect "Dimensi" ---
                    $checksheet = $this->syncNgDimensiDefect($checksheet, $oldJudgment, $newJudgment);

                    $checksheet->save();
                    $updatedCount++;
                    
                    ActivityLogger::log('updated', $checksheet, "Import BruteForce: Sukses update ID $id (Judgment: {$oldJudgment} → {$newJudgment})");
                } else {
                    $notFoundIds[] = $id;
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Import InProcess Critical Failure: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        if ($updatedCount === 0) {
            $msg = "Data terbaca, namun ID laporan tidak ditemukan di database.";
            if (!empty($notFoundIds)) {
                $msg .= " ID: " . implode(', ', array_slice($notFoundIds, 0, 3));
            }
            return redirect()->back()->with('warning', $msg);
        }

        return redirect()->back()->with('success', "Berhasil! $updatedCount data telah diperbarui secara massal.");
    }

    /**
     * Helper: Sinkronisasi defect "Dimensi" berdasarkan perubahan judgment dari import.
     * - Jika judgment berubah MENJADI NG: tambahkan/update defect "Dimensi"
     * - Jika judgment berubah KEMBALI ke OK: hapus defect "Dimensi" dari list
     * - Selalu sinkronkan total_ng dan total_ok agar konsisten
     */
    private function syncNgDimensiDefect($checksheet, string $oldJudgment, string $newJudgment)
    {
        // Ambil defects yang ada (decode jika masih JSON string)
        $defects = $checksheet->defects;
        if (is_string($defects)) {
            $defects = json_decode($defects, true) ?? [];
        }
        if (!is_array($defects)) {
            $defects = [];
        }

        $isDimensiType = function($t) {
            $key = strtolower(trim((string)$t));
            return in_array($key, ['dimensi', 'dimension', 'ng dimensi']);
        };

        if ($newJudgment === 'NG') {
            // Cari apakah sudah ada entry "Dimensi" atau "NG Dimensi"
            $found = false;
            foreach ($defects as &$defect) {
                if (is_array($defect) && isset($defect['type']) && $isDimensiType($defect['type'])) {
                    // Normalisasi nama ke "Dimensi" jika masih menggunakan nama lama
                    $defect['type'] = 'Dimensi';
                    $found = true;
                    break;
                }
            }
            unset($defect);

            // Jika belum ada, tambahkan
            if (!$found) {
                $defects[] = ['type' => 'Dimensi', 'qty' => 1];

                // Update total_ng jika sebelumnya OK (judgment berubah)
                if ($oldJudgment !== 'NG') {
                    $checksheet->total_ng = ((int) $checksheet->total_ng) + 1;
                    $checksheet->total_ok = max(0, ((int) $checksheet->total_ok) - 1);
                }
            }
        } else {
            // Judgment adalah OK — hapus entry "Dimensi" dan "NG Dimensi" jika ada
            $hadNgDimensi = false;
            $defects = array_values(array_filter($defects, function ($defect) use (&$hadNgDimensi, $isDimensiType) {
                if (is_array($defect) && $isDimensiType($defect['type'] ?? '')) {
                    $hadNgDimensi = true;
                    return false; // hapus dari list
                }
                return true;
            }));

            // Kembalikan hitungan jika sebelumnya NG karena dimensi
            if ($hadNgDimensi && $oldJudgment === 'NG') {
                $checksheet->total_ng = max(0, ((int) $checksheet->total_ng) - 1);
                $checksheet->total_ok = ((int) $checksheet->total_ok) + 1;
            }
        }

        $checksheet->defects = json_encode($defects);
        return $checksheet;
    }

    /**
     * Helper: Impor dari file XLSX menggunakan PhpSpreadsheet
     */
    private function importMeasureDataFromXlsx($file)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file XLSX: ' . $e->getMessage());
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false); // indexed array, format values

        $dataById      = [];
        $totalRowsParsed = 0;

        foreach ($rows as $index => $row) {
            // Lewati baris header (baris pertama)
            if ($index === 0) continue;

            // Minimal 5 kolom: ID, Tanggal, Part Name, Part Number, Cavity
            if (count($row) < 5) continue;

            $idRaw = trim((string)($row[0] ?? ''));
            $id    = preg_replace('/[^0-9]/', '', $idRaw);

            if (empty($id) || !is_numeric($id)) continue;

            $cavity = trim((string)($row[4] ?? '1'));
            if (!isset($dataById[$id])) $dataById[$id] = [];

            $points = [];
            // Kolom P1 mulai dari index ke-5 (index 5 = kolom ke-6)
            for ($i = 5; $i < count($row); $i++) {
                $pointIndex = $i - 4;
                $val = trim(str_replace(',', '.', (string)($row[$i] ?? '')));
                if ($val !== '' && $val !== '-') {
                    $points[$pointIndex] = $val;
                }
            }

            $dataById[$id][$cavity] = $points;
            $totalRowsParsed++;
        }

        if (empty($dataById)) {
            return redirect()->back()->with('warning', 'Format file XLSX tidak dapat dikenali. Pastikan kolom pertama berisi Checksheet ID yang valid.');
        }

        $updatedCount = 0;
        $notFoundIds  = [];

        \DB::beginTransaction();
        try {
            foreach ($dataById as $id => $measurements) {
                $checksheet = InProcessChecksheet::withoutGlobalScope('plant')->find($id);
                if ($checksheet) {
                    $oldJudgment = $checksheet->judgment;
                    $checksheet->dimension_check = $measurements;

                    // Hitung base total_ng (tanpa menghitung defect "Dimensi" yang mungkin sudah ada sebelumnya)
                    $currentDefects = $checksheet->defects;
                    if (is_string($currentDefects)) $currentDefects = json_decode($currentDefects, true) ?? [];
                    $baseTotalNg = 0;
                    if (is_array($currentDefects)) {
                        foreach ($currentDefects as $d) {
                            $type = $d['type'] ?? '';
                            if ($type !== 'Dimensi' && $type !== 'NG Dimensi') {
                                $baseTotalNg += (int)($d['qty'] ?? 0);
                            }
                        }
                    }

                    $dataToValidate = ['dimensions' => $measurements, 'total_ng' => $baseTotalNg];
                    $validated = $this->inProcessService->validateDimensions($dataToValidate, $checksheet->item_id);
                    // Fallback ke judgment lama jika item tidak punya standar dimensi
                    $newJudgment = $validated['judgment'] ?? $oldJudgment;
                    $checksheet->judgment = $newJudgment;

                    // --- Otomatis kelola defect "Dimensi" ---
                    $checksheet = $this->syncNgDimensiDefect($checksheet, $oldJudgment, $newJudgment);

                    $checksheet->save();
                    $updatedCount++;
                    ActivityLogger::log('updated', $checksheet, "Import XLSX: Sukses update ID $id (Judgment: {$oldJudgment} → {$newJudgment})");
                } else {
                    $notFoundIds[] = $id;
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        if ($updatedCount === 0) {
            $msg = 'Data terbaca, namun ID laporan tidak ditemukan di database.';
            if (!empty($notFoundIds)) {
                $msg .= ' ID: ' . implode(', ', array_slice($notFoundIds, 0, 3));
            }
            return redirect()->back()->with('warning', $msg);
        }

        return redirect()->back()->with('success', "Berhasil! $updatedCount data telah diperbarui secara massal (dari XLSX).");
    }
}
