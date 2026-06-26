<?php

namespace App\Http\Controllers;

use App\Models\CrossCutChecksheet;
use App\Models\Item;
use App\Services\CrossCutChecksheetService;
use App\Http\Requests\StoreCrossCutChecksheetRequest;
use App\Http\Requests\UpdateCrossCutChecksheetRequest;
use Illuminate\Http\Request;
use App\Helpers\ShiftHelper;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ActivityLogger;

class CrossCutChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval {
        reject as traitReject;
    }

    protected $crossCutService;

    public function __construct(CrossCutChecksheetService $crossCutService)
    {
        $this->crossCutService = $crossCutService;
    }

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
            'asst_manager_plating' => ['field' => 'asst_manager_plating', 'time' => 'asst_manager_plating_approved_at', 'label' => 'Asst Manager Plating'],
            'asst_manager' => ['field' => 'asst_manager_qc', 'time' => 'asst_manager_approved_at', 'label' => 'Asst Manager QC'],
        ];
        return $mappings[$type] ?? null;
    }
    protected function getApprovalDateColumn()
    {
        return 'qc_datetime';
    }

    /**
     * Menampilkan daftar data (resource).
     */
    public function index(Request $request)
    {
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'asst_manager_plating', 'manager_plating'];

        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $filters = [
            'plant' => $request->input('plant'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'item_id' => $request->input('item_id'),
            'approval_status' => $request->input('approval_status'),
            'id' => $request->input('id'),
            'search' => $request->input('search'),
            'check_type' => $request->input('check_type'),
            'operator_initials' => $request->input('operator_initials'),
            'customer' => $request->input('customer'),
            'shift' => $request->input('shift'),
        ];
        $checksheets = $this->crossCutService->getFilteredChecksheets($filters);
        // Fetch unique item IDs that have checksheets
        $existingItemIds = CrossCutChecksheet::withoutGlobalScope('plant')->distinct()->pluck('item_id');

        $items = Item::whereIn('id', $existingItemIds)->orderBy('name')->get();

        $customers = Item::whereIn('id', $existingItemIds)
            ->whereNotNull('customer')
            ->where('customer', '!=', '')
            ->distinct()
            ->orderBy('customer')
            ->pluck('customer');
            
        $initials = CrossCutChecksheet::withoutGlobalScope('plant')->whereNotNull('operator_initials')->where('operator_initials', '!=', '')->distinct()->orderBy('operator_initials')->pluck('operator_initials');

        $canExport = \App\Helpers\AppMenu::checkPermission('cross_cut.index', 'export');
        $canEdit = \App\Helpers\AppMenu::checkPermission('cross_cut.index', 'edit');
        $canDelete = \App\Helpers\AppMenu::checkPermission('cross_cut.index', 'delete');

        return view('cross_cut.index', compact('checksheets', 'items', 'customers', 'initials', 'canExport', 'canEdit', 'canDelete'));
    }

    /**
     * Menampilkan form untuk membuat data baru.
     */
    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = Item::byCategory(['Cross Cut Plating', 'Cross Cut Painting'])->orderBy('name');

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
            // Inspector: strictly follow user's assigned plant_id
            $query->where('plant_id', $user->plant_id);
        }

        $items = $query->get();

        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        $plant = \App\Models\Plant::resolveId($request->query('plant') ?? $user->plant_id);
        $nextProcesses = \App\Models\NextProcess::where('plant_id', $plant)
            ->where('module', 'cross_cut')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('cross_cut.create', compact('items', 'defaultDate', 'defaultShift', 'plant', 'nextProcesses'));
    }

    /**
     * Menyimpan data baru ke penyimpanan (database).
     */
    public function store(StoreCrossCutChecksheetRequest $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $checksheet = $this->crossCutService->createChecksheet($request->validated());
            ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Cross Cut baru: {$checksheet->item->name}");
            $message = 'Cross Cut Checksheet created successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('cross_cut.index', ['plant' => $request->input('plant')])
                ]);
            }

            return redirect()->route('cross_cut.create')->with('success', $message);
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

    /**
     * API: Get next auto-generated Result Remark for a given item + current user.
     * Response: { remark: "IA3", count: 2, initials: "IA" }
     */
    public function getNextResultRemark(Request $request)
    {
        $itemId   = $request->input('item_id');
        $operatorInitials = $request->input('operator_initials');
        $initials = strtoupper(trim($operatorInitials ?: auth()->user()->initials ?? ''));

        if (!$itemId) {
            return response()->json(['remark' => null, 'count' => 0, 'initials' => $initials]);
        }

        $latestChecksheet = CrossCutChecksheet::withoutGlobalScope('plant')
            ->where('item_id', $itemId)
            ->where('operator_initials', $initials)
            ->whereNotNull('result_remark')
            ->where('result_remark', '!=', '')
            ->latest('id')
            ->first();

        $count = CrossCutChecksheet::withoutGlobalScope('plant')
            ->where('item_id', $itemId)
            ->count();

        if ($latestChecksheet) {
            $latestRemark = trim($latestChecksheet->result_remark);
            if (preg_match('/^(.*?)(\d+)$/', $latestRemark, $matches)) {
                $prefix = $matches[1];
                $numberStr = $matches[2];
                $nextNumber = (int)$numberStr + 1;
                $paddedNumber = str_pad((string)$nextNumber, strlen($numberStr), '0', STR_PAD_LEFT);
                $next = $prefix . $paddedNumber;

                return response()->json([
                    'remark'   => $next,
                    'count'    => $count,
                    'initials' => $initials,
                ]);
            }
        }

        $next = $initials ? $initials . '01' : null;

        return response()->json([
            'remark'   => $next,
            'count'    => $count,
            'initials' => $initials,
        ]);
    }

    /**
     * API: Get auto-generated No Lot QC based on format A07AE26A.
     * Month(1 char) + Date(2 chars) + Initials + Year(2 chars) + SequenceChar(repeated by shift count)
     */
    public function getAutoNoLot(Request $request)
    {
        $itemId = $request->input('item_id');
        $productionDate = $request->input('production_date');
        $prodShift = (int) $request->input('production_shift', 1);
        $operatorInitials = strtoupper(trim($request->input('operator_initials', '')));
        $qcShift = (int) $request->input('qc_shift', 1);

        if (!$itemId || !$productionDate || !$operatorInitials) {
            return response()->json(['no_lot' => null, 'count' => 0]);
        }

        try {
            $dateObj = \Carbon\Carbon::parse($productionDate);
            $month = $dateObj->month; // 1-12
            $day = $dateObj->format('d'); // 01-31
            $year2 = $dateObj->format('y'); // 26
            
            $monthChar = chr(64 + $month); // 1->A, 2->B, etc.
            
            $count = CrossCutChecksheet::withoutGlobalScope('plant')
                ->where('item_id', $itemId)
                ->whereDate('production_datetime', $dateObj->toDateString())
                ->where('production_shift', $prodShift)
                ->count();
                
            $seqCount = $count % 26; 
            $seqChar = chr(65 + $seqCount); 
            
            $suffix = str_repeat($seqChar, max(1, $qcShift));
            
            $noLot = "{$monthChar}{$day}{$operatorInitials}{$year2}{$suffix}";
            
            return response()->json([
                'no_lot' => $noLot,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json(['no_lot' => null, 'error' => $e->getMessage()]);
        }
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
        try {
            $checksheet = CrossCutChecksheet::findOrFail($id);

            // Guard: prevent Path cannot be empty error
            if (empty($checksheet->image_path)) {
                return abort(404, 'Image path is empty.');
            }

            if (!Storage::disk('public')->exists($checksheet->image_path)) {
                return abort(404, 'Image file does not exist on disk.');
            }

            return response()->file(Storage::disk('public')->path($checksheet->image_path));
        } catch (\Exception $e) {
            \Log::error("Path cannot be empty or related error in serveImage (Plating) ID {$id}: " . $e->getMessage());
            return abort(404, 'Error processing image: ' . $e->getMessage());
        }
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
            'chemical_catalyst' => $checksheet->chemical_catalyst,
            'chemical_abu' => $checksheet->chemical_abu,
            'position_remark_judgment' => $checksheet->position_remark_judgment,
            'position_remark_no_lot' => $checksheet->position_remark_no_lot,
            'result_remark' => $checksheet->result_remark,
            'operator_initials' => $checksheet->operator_initials,
        ]);
    }

    public function edit($id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = CrossCutChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::byCategory(['Cross Cut Plating', 'Cross Cut Painting'])
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        $nextProcesses = \App\Models\NextProcess::where('plant_id', $checksheet->plant_id)
            ->where('module', 'cross_cut')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        if (request()->ajax()) {
            return view('cross_cut.partials.edit_form', compact('checksheet', 'items', 'nextProcesses'));
        }

        return view('cross_cut.edit', compact('checksheet', 'items', 'nextProcesses'));
    }

    public function update(UpdateCrossCutChecksheetRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $this->crossCutService->updateChecksheet($id, $request->validated());
            $checksheet = CrossCutChecksheet::find($id);
            ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Cross Cut: {$checksheet->item->name}");
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui.']);
            }
            
            return redirect()->route('cross_cut.index', $request->only(['page', 'plant', 'start_date', 'end_date', 'item_id', 'approval_status', 'operator_initials', 'customer', 'search', 'check_type', 'shift']))
                ->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memperbarui data: ' . $e->getMessage()], 500);
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
            $checksheet = CrossCutChecksheet::find($id);
            $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
            $this->crossCutService->deleteChecksheet($id);
            ActivityLogger::log('deleted', null, "Menghapus checksheet Cross Cut: {$itemName}");

            // Preserve all filters when redirecting back
            $redirectParams = $request->only(['page', 'plant', 'start_date', 'end_date', 'item_id', 'approval_status', 'operator_initials', 'customer', 'search', 'check_type', 'shift']);

            return redirect()->route('cross_cut.index', $redirectParams)
                ->with('success', 'Data Cross Cut berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. Managers can only perform approvals.'
            ], 403);
        }

        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang dipilih untuk dihapus.'
            ], 400);
        }

        try {
            \DB::beginTransaction();

            $checksheets = CrossCutChecksheet::whereIn('id', $ids)->get();

            if ($checksheets->isEmpty()) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            foreach ($checksheets as $checksheet) {
                $itemName = $checksheet->item ? $checksheet->item->name : 'Unknown';
                $this->crossCutService->deleteChecksheet($checksheet->id);
                ActivityLogger::log('deleted', null, "Menghapus checksheet Cross Cut: {$itemName} secara massal");
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' data berhasil dihapus.',
                'redirect' => route('cross_cut.index', $request->except(['ids', '_token']))
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request, $id, $type)
    {
        try {
            $data = [];
            if ($type === 'kashift_plating') {
                $request->validate([
                    'approver_name' => 'required|string|min:3|max:100',
                ], [
                    'approver_name.required' => 'Nama approver wajib diisi.',
                    'approver_name.min' => 'Nama approver minimal 3 karakter.',
                    'approver_name.max' => 'Nama approver maksimal 100 karakter.',
                ]);
                $data['approver_name'] = $request->approver_name;
            }

            $this->crossCutService->singleApprove($id, $type, $data);
            $checksheet = CrossCutChecksheet::find($id);
            $mapping = $this->getApprovalMapping($type);
            $label = $mapping['label'] ?? $type;
            ActivityLogger::log('approved', $checksheet, "Melakukan approval ({$label}) pada checksheet Cross Cut: {$checksheet->item->name}");

            return redirect()->route('cross_cut.index', $request->only(['page', 'plant', 'start_date', 'end_date', 'item_id', 'approval_status', 'operator_initials', 'customer', 'search', 'check_type', 'shift']))
                ->with('success', 'Cross Cut Checksheet approved successfully.');
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code == 403)
                abort(403);
            return redirect()->route('cross_cut.index', $request->only(['page', 'plant', 'start_date', 'end_date', 'item_id', 'approval_status', 'operator_initials', 'customer', 'search', 'check_type', 'shift']))
                ->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id, $type)
    {
        try {
            $this->traitReject($request, $id, $type);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cross Cut Checksheet rejected successfully.'
                ]);
            }

            return redirect()->route('cross_cut.index', $request->only(['page', 'plant', 'start_date', 'end_date', 'item_id', 'approval_status', 'operator_initials', 'customer', 'search', 'check_type', 'shift']))
                ->with('success', 'Cross Cut Checksheet rejected successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->route('cross_cut.index', $request->only(['page', 'plant', 'start_date', 'end_date', 'item_id', 'approval_status', 'operator_initials', 'customer', 'search', 'check_type', 'shift']))
                ->with('error', $e->getMessage());
        }
    }



    public function exportPdf(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'item_id', 'approval_status', 'check_type', 'operator_initials', 'customer', 'shift']);

        // Default to today if no date range is provided
        if (empty($filters['start_date']) && empty($filters['end_date'])) {
            $filters['start_date'] = now()->toDateString();
            $filters['end_date'] = now()->toDateString();
        }

        $query = $this->crossCutService->buildFilteredQuery($filters)->latest();

        if ($request->has('page')) {
            $checksheets = $query->paginate(10)->getCollection();
        } else {
            $checksheets = $query->get();
        }

        $itemName = null;
        if ($request->filled('item_id')) {
            $item = Item::find($request->item_id);
            $itemName = $item ? $item->name : 'Item tidak diketahui';
        }

        $viewData = [
            'checksheets' => $checksheets,
            'startDate' => $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua',
            'endDate' => $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua',
            'item_id' => $request->item_id,
            'itemName' => $itemName,
            'approval_status' => $request->approval_status,
            'plantName' => \App\Models\Plant::resolveName($request->plant ?? auth()->user()->plant_id),
        ];

        $pdf = Pdf::loadView('cross_cut.pdf', $viewData);
        return $pdf->setPaper('a4', 'landscape')->stream('laporan-cross-cut.pdf');
    }

    public function printView(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'item_id', 'approval_status', 'check_type', 'operator_initials', 'customer', 'shift']);

        // Default to today if no date range is provided
        if (empty($filters['start_date']) && empty($filters['end_date'])) {
            $filters['start_date'] = now()->toDateString();
            $filters['end_date'] = now()->toDateString();
        }

        $query = $this->crossCutService->buildFilteredQuery($filters)->latest();
        $checksheets = $query->get();

        $itemName = null;
        if ($request->filled('item_id')) {
            $item = Item::find($request->item_id);
            $itemName = $item ? $item->name : null;
        }

        $plantName = \App\Models\Plant::resolveName($request->plant ?? auth()->user()->plant_id);

        return view('cross_cut.print', compact('checksheets', 'filters', 'itemName', 'plantName'));
    }



    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);
        if (request()->ajax()) {
            return view('cross_cut.partials.edit_approval_form', compact('checksheet'));
        }
        return view('cross_cut.edit_approval', compact('checksheet'));
    }

    // Update status approval oleh admin
    public function updateApproval(Request $request, $id)
    {
        $validated = $request->validate([
            'karu_qc' => 'required|in:Pending,Approved,Rejected',
            'kashift_plating' => 'required|in:Pending,Approved,Rejected',
            'supervisor_plating' => 'required|in:Pending,Approved,Rejected',
            'supervisor_qc' => 'required|in:Pending,Approved,Rejected',
            'asst_manager_qc' => 'required|in:Pending,Approved,Rejected',
        ]);

        try {
            $this->crossCutService->updateApprovalStatus($id, $validated);
            $checksheet = \App\Models\CrossCutChecksheet::find($id);

            // Jika status dirubah menjadi Rejected melalui modal admin, kirim notifikasi dan berikan remarks
            if ($checksheet->approval_status === 'Rejected' && empty($checksheet->rejection_remarks)) {
                $checksheet->rejection_remarks = "[Admin] Status dirubah menjadi Rejected via Edit Status - " . auth()->user()->name . " (" . now()->format('d/m/Y H:i') . ")";
                $checksheet->save();

                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->notifyRejection($checksheet, 'Cross Cut', auth()->user()->name);
                } catch (\Exception $ne) {
                    \Illuminate\Support\Facades\Log::error('Gagal kirim notifikasi rejection: ' . $ne->getMessage());
                }
            }

            ActivityLogger::log('updated', $checksheet, "Memperbarui status approval (Admin) pada checksheet Cross Cut: {$checksheet->item->name}");
            return redirect()->route('cross_cut.index', $request->only(['page', 'plant', 'start_date', 'end_date', 'item_id', 'approval_status', 'operator_initials', 'customer', 'search', 'check_type', 'shift']))
                ->with('success', 'Status approval berhasil diperbarui oleh Admin.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status approval: ' . $e->getMessage());
        }
    }
}
