<?php

namespace App\Http\Controllers;

use App\Models\CrossCutPaintingChecksheet;
use App\Models\Item;
use App\Services\CrossCutPaintingChecksheetService;
use App\Http\Requests\StoreCrossCutPaintingChecksheetRequest;
use App\Http\Requests\UpdateCrossCutPaintingChecksheetRequest;
use Illuminate\Http\Request;
use App\Helpers\ShiftHelper;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ActivityLogger;

class CrossCutPaintingChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;

    protected $paintingService;

    public function __construct(CrossCutPaintingChecksheetService $paintingService)
    {
        $this->paintingService = $paintingService;
    }

    protected function getModelClass()
    {
        return CrossCutPaintingChecksheet::class;
    }

    protected function getApprovalMapping($type)
    {
        $mappings = [
            'karu_qc' => ['field' => 'karu_qc', 'time' => 'karu_qc_approved_at', 'label' => 'Karu QC'],
            'kashift_plating' => ['field' => 'kashift_plating', 'time' => 'kashift_plating_approved_at', 'label' => 'Kashift Painting'],
            'supervisor_plating' => ['field' => 'supervisor_plating', 'time' => 'supervisor_plating_approved_at', 'label' => 'Supervisor Painting'],
            'supervisor' => ['field' => 'supervisor_qc', 'time' => 'supervisor_approved_at', 'label' => 'SPV Quality'],
            'manager_plating' => ['field' => 'manager_plating', 'time' => 'manager_plating_approved_at', 'label' => 'Manager Painting'],
            'manager' => ['field' => 'manager_qc', 'time' => 'manager_approved_at', 'label' => 'Manager QC'],
        ];
        return $mappings[$type] ?? null;
    }

    /**
     * Menampilkan daftar data (resource).
     */
    public function index(Request $request)
    {
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
        $checksheets = $this->paintingService->getFilteredChecksheets($filters);
        $items = Item::byCategory('Cross Cut Painting')->orderBy('name')->get();

        $approvalOrder = ['karu_qc', 'kashift_plating', 'supervisor', 'supervisor_plating', 'manager', 'manager_plating'];

        return view('cross_cut_painting.index', compact('checksheets', 'items', 'approvalOrder'));
    }

    /**
     * Menampilkan form untuk membuat data baru.
     */
    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        $query = Item::byCategory('Cross Cut Painting')->orderBy('name');

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

        return view('cross_cut_painting.create', compact('items', 'defaultDate', 'defaultShift'));
    }

    /**
     * Menyimpan data baru.
     */
    public function store(StoreCrossCutPaintingChecksheetRequest $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $checksheet = $this->paintingService->createChecksheet($request->validated());
            ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Cross Cut Painting baru: {$checksheet->item->name}");
            $message = 'Cross Cut Painting Checksheet created successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('cross_cut_painting.index', ['plant' => $request->get('plant')])
                ]);
            }

            return redirect()->route('cross_cut_painting.create')->with('success', $message);
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

    public function show($id)
    {
        $checksheet = CrossCutPaintingChecksheet::findOrFail($id);
        return response()->json([
            'image_url' => route('cross_cut_painting.image', $checksheet->id),
            'item_name' => $checksheet->item->name,
            'qc_datetime' => $checksheet->qc_datetime,
        ]);
    }

    public function serveImage($id)
    {
        $checksheet = CrossCutPaintingChecksheet::findOrFail($id);
        if (!Storage::disk('public')->exists($checksheet->image_path)) {
            abort(404);
        }
        return response()->file(Storage::disk('public')->path($checksheet->image_path));
    }

    public function getData($id)
    {
        $query = CrossCutPaintingChecksheet::with('item');
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
            'chemical_copper' => $checksheet->chemical_copper,
            'chemical_nikel' => $checksheet->chemical_nikel,
            'chemical_eching' => $checksheet->chemical_eching,
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
            abort(403, 'Unauthorized action.');
        }
        $query = CrossCutPaintingChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);
        $items = Item::byCategory('Cross Cut Painting')
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        if (request()->ajax()) {
            return view('cross_cut_painting.partials.edit_form', compact('checksheet', 'items'));
        }

        return view('cross_cut_painting.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateCrossCutPaintingChecksheetRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $this->paintingService->updateChecksheet($id, $request->validated());
            $checksheet = \App\Models\CrossCutPaintingChecksheet::find($id);
            ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Cross Cut Painting: {$checksheet->item->name}");
            return redirect()->route('cross_cut_painting.index')->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $this->paintingService->deleteChecksheet($id);
            $redirectParams = [];
            if ($request->has('plant')) {
                $redirectParams['plant'] = $request->input('plant');
            }
            return redirect()->route('cross_cut_painting.index', $redirectParams)->with('success', 'Data Cross Cut Painting berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id, $type)
    {
        try {
            $data = [];
            if ($type === 'kashift_plating') {
                $request->validate(['approver_name' => 'required|string|min:3|max:100']);
                $data['approver_name'] = $request->approver_name;
            }
            $this->paintingService->singleApprove($id, $type, $data);
            $checksheet = \App\Models\CrossCutPaintingChecksheet::find($id);
            $mapping = $this->getApprovalMapping($type);
            $label = $mapping['label'] ?? $type;
            ActivityLogger::log('approved', $checksheet, "Melakukan approval ({$label}) pada checksheet Cross Cut Painting: {$checksheet->item->name}");

            return redirect()->route('cross_cut_painting.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('success', 'Cross Cut Painting Checksheet approved successfully.');
        } catch (\Exception $e) {
            if ($e->getCode() == 403)
                abort(403);
            return redirect()->route('cross_cut_painting.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id, $type)
    {
        $request->validate([
            'rejection_remarks' => 'required|string|min:10|max:500',
        ]);

        try {
            $this->paintingService->rejectChecksheet($id, $type, $request->rejection_remarks);
            return redirect()->route('cross_cut_painting.index', $request->only(['page', 'start_date', 'end_date', 'item_id', 'approval_status']))
                ->with('warning', 'Checksheet telah ditolak.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menolak checksheet: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'item_id', 'approval_status']);
        $query = $this->paintingService->buildFilteredQuery($filters)->latest();

        if ($request->has('page')) {
            $checksheets = $query->paginate(10)->getCollection();
        } else {
            $checksheets = $query->limit(10)->get();
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

        $pdf = Pdf::loadView('cross_cut_painting.pdf', $viewData);
        return $pdf->setPaper('a4', 'landscape')->stream('laporan-cross-cut-painting.pdf');
    }

    public function editApproval($id)
    {
        $checksheet = CrossCutPaintingChecksheet::findOrFail($id);
        if (request()->ajax()) {
            return view('cross_cut_painting.partials.edit_approval_form', compact('checksheet'));
        }
        return view('cross_cut_painting.edit_approval', compact('checksheet'));
    }

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
            $this->paintingService->updateApprovalStatus($id, $validated);
            return redirect()->route('cross_cut_painting.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('success', 'Status approval berhasil diperbarui oleh Admin.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status approval: ' . $e->getMessage());
        }
    }
}
