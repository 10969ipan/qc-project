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
        ];

        $checksheets = $this->inProcessService->getFilteredChecksheets($filters);

        $partDimensionStandards = $this->getConsolidatedStandards();

        // Data for filters (Standardized with Cross-Cut)
        // Adjust to fetch only from available data in current table (excluding those filtered out by plant if possible)
        $plantId = \App\Models\Plant::resolveId($filters['plant']);
        
        $items = Item::whereIn('id', function($query) use ($plantId) {
            $query->select('item_id')->from('in_process_checksheets')->where('plant_id', $plantId);
        })->orderBy('name')->get();

        $customers = Item::whereIn('id', function($query) use ($plantId) {
            $query->select('item_id')->from('in_process_checksheets')->where('plant_id', $plantId);
        })->whereNotNull('customer')->distinct()->pluck('customer')->sort();

        $initials = InProcessChecksheet::where('plant_id', $plantId)
            ->whereNotNull('operator_initials')
            ->distinct()
            ->pluck('operator_initials')
            ->sort();

        return view('in_process.index', compact('checksheets', 'partDimensionStandards', 'items', 'customers', 'initials'));
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
        $canSwitchPlants = ['admin', 'supervisor', 'supervisor_plating', 'manager', 'manager_qc', 'manager_plating', 'kashift', 'asst_manager', 'oshef'];

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

        return view('in_process.create', [
            'items' => $items,
            'defaultDate' => $defaultDate,
            'defaultShift' => $defaultShift,
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
                session()->flash('success', $message);
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

        if (request()->ajax()) {
            return view('in_process.partials.edit_form', [
                'checksheet' => $checksheet,
                'items' => $items,
                'partDimensionStandards' => $partDimensionStandards,
                'users' => $users
            ]);
        }

        return view('in_process.edit', [
            'checksheet' => $checksheet,
            'items' => $items,
            'partDimensionStandards' => $partDimensionStandards,
            'users' => $users
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
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('success', 'Data Checksheet Inprocess berhasil diperbarui.');
                return response()->json([
                    'success' => true,
                    'message' => 'Data Checksheet Inprocess berhasil diperbarui.',
                    'redirect' => route('in_process.index', $redirectParams)
                ]);
            }

            return redirect()->route('in_process.index', $redirectParams)->with('success', 'Data Checksheet Inprocess berhasil diperbarui.');
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
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('success', 'Data Checksheet Inprocess berhasil dihapus.');
                return response()->json([
                    'success' => true,
                    'message' => 'Data Checksheet Inprocess berhasil dihapus.',
                    'redirect' => route('in_process.index', $redirectParams)
                ]);
            }

            return redirect()->route('in_process.index', $redirectParams)
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



    // Export Checksheets to PDF
    public function exportPdf(Request $request)
    {
        // For restricted roles (inspector, plating), override request plant to their own plant
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];
        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'operator_initials', 'customer', 'part_no', 'search', 'plant']);

        if (empty($filters['start_date'])) {
            $filters['start_date'] = now()->toDateString();
        }
        if (empty($filters['end_date'])) {
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

        $startDate = \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y');
        $endDate = \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y');

        $pdf = Pdf::loadView('in_process.pdf', compact('checksheets', 'items', 'request', 'partDimensionStandards', 'startDate', 'endDate', 'plantName', 'plantCode'));
        return $pdf->setPaper('a4', 'landscape')->download('Laporan_Inprocess_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);
        if (request()->ajax()) {
            return view('in_process.partials.edit_approval_form', compact('checksheet'));
        }
        return view('in_process.edit_approval', compact('checksheet'));
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
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
            $redirectParams = $request->only($preservationKeys);

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('success', 'Status approval berhasil diperbarui oleh Admin.');
                return response()->json([
                    'success' => true,
                    'message' => 'Status approval berhasil diperbarui oleh Admin.',
                    'redirect' => route('in_process.index', $redirectParams)
                ]);
            }

            return redirect()->route('in_process.index', $redirectParams)->with('success', 'Status approval berhasil diperbarui oleh Admin.');
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

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'operator_initials', 'customer', 'part_no', 'search', 'plant']);

        if (empty($filters['start_date'])) {
            $filters['start_date'] = now()->toDateString();
        }
        if (empty($filters['end_date'])) {
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

        // For display labels: use provided dates or default to 'Today' for the label only
        $dispStart = ($filters['start_date'] ?? null) ?: now()->toDateString();
        $dispEnd = ($filters['end_date'] ?? null) ?: now()->toDateString();

        $startDate = \Carbon\Carbon::parse($dispStart)->format('d/m/Y');
        $endDate   = \Carbon\Carbon::parse($dispEnd)->format('d/m/Y');

        return view('in_process.print', compact('checksheets', 'partDimensionStandards', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }
}
