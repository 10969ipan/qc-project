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

class CrossCutChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;

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
            'manager_plating' => ['field' => 'manager_plating', 'time' => 'manager_plating_approved_at', 'label' => 'Manager Plating'],
            'manager' => ['field' => 'manager_qc', 'time' => 'manager_approved_at', 'label' => 'Manager QC'],
        ];
        return $mappings[$type] ?? null;
    }
    /**
     * Menampilkan daftar data (resource).
     */
    public function index(Request $request)
    {
        // For restricted roles (inspector, plating), override request plant to their own plant
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];

        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $filters = [
            'plant' => $request->get('plant'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'item_id' => $request->get('item_id'),
            'approval_status' => $request->get('approval_status'),
            'id' => $request->get('id'),
            'search' => $request->get('search'),
        ];
        $checksheets = $this->crossCutService->getFilteredChecksheets($filters);
        $items = Item::orderBy('name')->get();

        return view('cross_cut.index', compact('checksheets', 'items'));
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

        return view('cross_cut.create', compact('items', 'defaultDate', 'defaultShift'));
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
            $this->crossCutService->createChecksheet($request->validated());
            return redirect()->route('cross_cut.create')->with('success', 'Cross Cut Checksheet created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
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
        $checksheet = CrossCutChecksheet::findOrFail($id);

        if (!Storage::disk('public')->exists($checksheet->image_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($checksheet->image_path));
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

        $items = Item::orderBy('name')->get();

        if (request()->ajax()) {
            return view('cross_cut.partials.edit_form', compact('checksheet', 'items'));
        }

        return view('cross_cut.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateCrossCutChecksheetRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $this->crossCutService->updateChecksheet($id, $request->validated());
            return redirect()->route('cross_cut.index')->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        try {
            $this->crossCutService->deleteChecksheet($id);

            // Preserve plant parameter when redirecting back
            $redirectParams = [];
            if ($request->has('plant')) {
                $redirectParams['plant'] = $request->input('plant');
            }

            return redirect()->route('cross_cut.index', $redirectParams)
                ->with('success', 'Data Cross Cut berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
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
            return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('success', 'Cross Cut Checksheet approved successfully.');
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code == 403)
                abort(403);
            return redirect()->route('cross_cut.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('error', $e->getMessage());
        }
    }



    public function exportPdf(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'item_id', 'approval_status']);

        $checksheets = $this->crossCutService->buildFilteredQuery($filters)->get();

        $itemName = null;
        if ($request->filled('item_id')) {
            $item = Item::find($request->item_id);
            $itemName = $item ? $item->name : 'Item tidak diketahui';
        }

        $viewData = [
            'checksheets' => $checksheets,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'item_id' => $request->item_id,
            'itemName' => $itemName,
            'approval_status' => $request->approval_status,
        ];

        $pdf = Pdf::loadView('cross_cut.pdf', $viewData);
        return $pdf->setPaper('a4', 'landscape')->stream('laporan-cross-cut.pdf');
    }

    // Unified filtering delegating to service
    private function applyFilters($query, Request $request)
    {
        // This is now redundant as we use service for filtering.
        // Keeping it for now if any legacy code still calls it, but redirecting to service logic would be better.
        $filters = $request->only(['start_date', 'end_date', 'item_id', 'approval_status', 'search']);
        // Service already handles this.
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
            'manager_plating' => 'required|in:Pending,Approved,Rejected',
            'manager_qc' => 'required|in:Pending,Approved,Rejected',
        ]);

        try {
            $this->crossCutService->updateApprovalStatus($id, $validated);
            return redirect()->route('cross_cut.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('success', 'Status approval berhasil diperbarui oleh Admin.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status approval: ' . $e->getMessage());
        }
    }
}
