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
            'next_proses' => $request->next_proses,
            'id' => $request->id,
            'search' => $request->search,
        ];

        $checksheets = $this->inProcessService->getFilteredChecksheets($filters);
        $items = Item::orderBy('name')->get();
        $partDimensionStandards = $this->getConsolidatedStandards();

        return view('in_process.index', compact('checksheets', 'items', 'partDimensionStandards'));
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
        // Pre-fill date with the most recent checksheet entry's date for better UX
        $lastChecksheet = InProcessChecksheet::latest('created_at')->first();
        $defaultDate = $lastChecksheet ? \Carbon\Carbon::parse($lastChecksheet->date)->format('Y-m-d') : ShiftHelper::getProductionDate(now());
        $defaultShift = ShiftHelper::getShift(now());

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

            $message = 'Data Checksheet Inprocess berhasil disimpan (Local Only).';
            // if (!$result['google_sheets_success']) {
            //    $message = 'Data Checksheet Inprocess berhasil disimpan lokal, namun GAGAL kirim ke Google Sheets. Error: ' . $result['error'];
            // }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
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

        $items = Item::orderBy('name')->get();
        $partDimensionStandards = json_encode($this->getConsolidatedStandards());

        if (request()->ajax()) {
            return view('in_process.partials.edit_form', [
                'checksheet' => $checksheet,
                'items' => $items,
                'partDimensionStandards' => $partDimensionStandards
            ]);
        }

        return view('in_process.edit', [
            'checksheet' => $checksheet,
            'items' => $items,
            'partDimensionStandards' => $partDimensionStandards
        ]);
    }

    // Update Checksheet
    public function update(UpdateInProcessChecksheetRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $this->inProcessService->updateChecksheet($id, $request->validated());

            // Only preserve specific navigation and filter parameters
            // Exclude fields that might collide with form data (item_id, next_proses)
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
            $redirectParams = $request->only($preservationKeys);

            return redirect()->route('in_process.index', $redirectParams)->with('success', 'Data Checksheet Inprocess berhasil diperbarui.');
        } catch (\Exception $e) {
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
            $this->inProcessService->deleteChecksheet($id);

            // Only preserve specific navigation and filter parameters
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
            $redirectParams = $request->only($preservationKeys);

            return redirect()->route('in_process.index', $redirectParams)
                ->with('success', 'Data Checksheet Inprocess berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }



    // Export Checksheets to PDF
    public function exportPdf(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'plant']);

        // Limit to 10 records as requested
        $checksheets = $this->inProcessService->buildFilteredQuery($filters)->limit(10)->get();
        $items = Item::orderBy('name')->get();

        $partDimensionStandards = $this->getConsolidatedStandards();
        $pdf = Pdf::loadView('in_process.pdf', compact('checksheets', 'items', 'request', 'partDimensionStandards'));
        return $pdf->setPaper('a4', 'landscape')->stream('laporan-checksheet-inprocess.pdf');
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

            // Only preserve specific navigation and filter parameters
            $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search'];
            $redirectParams = $request->only($preservationKeys);

            return redirect()->route('in_process.index', $redirectParams)->with('success', 'Status approval berhasil diperbarui oleh Admin.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status approval: ' . $e->getMessage());
        }
    }
}
