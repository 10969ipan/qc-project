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
        ];

        $checksheets = $this->firstPieceService->getFilteredChecksheets($filters);
        $partDimensionStandards = $this->getConsolidatedStandards();

        // Data for filters (Standardized with Cross-Cut)
        $plantId = \App\Models\Plant::resolveId($filters['plant']);
        
        $items = Item::whereIn('id', function($query) use ($plantId) {
            $query->select('item_id')->from('first_piece_approvals')->where('plant_id', $plantId);
        })->orderBy('name')->get();

        $customers = Item::whereIn('id', function($query) use ($plantId) {
            $query->select('item_id')->from('first_piece_approvals')->where('plant_id', $plantId);
        })->whereNotNull('customer')->distinct()->pluck('customer')->sort();

        $initials = \App\Models\FirstPieceApproval::where('plant_id', $plantId)
            ->whereNotNull('operator_initials')
            ->distinct()
            ->pluck('operator_initials')
            ->sort();

        return view('first_piece_approval.index', compact('checksheets', 'partDimensionStandards', 'items', 'customers', 'initials'));
    }

    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = Item::byCategory('INPROSES')->orderBy('name');

        $user = auth()->user();
        $canSwitchPlants = ['admin', 'supervisor', 'supervisor_plating', 'manager', 'manager_qc', 'manager_plating', 'kashift', 'asst_manager', 'oshef'];

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

        return view('first_piece_approval.create', [
            'items' => $items,
            'defaultDate' => $defaultDate,
            'defaultShift' => $defaultShift,
            'partDimensionStandards' => $this->getConsolidatedStandards()
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

        if (request()->ajax()) {
            return view('first_piece_approval.partials.edit_form', [
                'checksheet' => $checksheet,
                'items' => $items,
                'partDimensionStandards' => $partDimensionStandards,
                'users' => $users
            ]);
        }

        return view('first_piece_approval.edit', [
            'checksheet' => $checksheet,
            'items' => $items,
            'partDimensionStandards' => $partDimensionStandards,
            'users' => $users
        ]);
    }

    public function update(UpdateFirstPieceApprovalRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $validatedData = $request->validated();

            $this->firstPieceService->updateChecksheet($id, $validatedData);
            $checksheet = FirstPieceApproval::find($id);
            ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet First Piece Approval: {$checksheet->item->name}");

            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
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

            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
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

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'operator_initials', 'customer', 'part_no', 'search', 'plant']);

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

            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
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

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'operator_initials', 'customer', 'part_no', 'search', 'plant']);

        if (empty($filters['start_date'])) {
            $filters['start_date'] = now()->toDateString();
        }
        if (empty($filters['end_date'])) {
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

        // For display labels: use provided dates or default to 'Today' for the label only
        $dispStart = ($filters['start_date'] ?? null) ?: now()->toDateString();
        $dispEnd = ($filters['end_date'] ?? null) ?: now()->toDateString();

        $startDate = \Carbon\Carbon::parse($dispStart)->format('d/m/Y');
        $endDate   = \Carbon\Carbon::parse($dispEnd)->format('d/m/Y');

        return view('first_piece_approval.print', compact('checksheets', 'partDimensionStandards', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }
}
