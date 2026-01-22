<?php

namespace App\Http\Controllers;

use App\Models\SubAssyChecksheet;
use App\Models\Item;
use App\Services\SubAssyChecksheetService;
use App\Http\Requests\StoreSubAssyChecksheetRequest;
use App\Http\Requests\UpdateSubAssyChecksheetRequest;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;

class SubAssyChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(SubAssyChecksheetService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return SubAssyChecksheet::class;
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
            // Approvals
            $c->kashift_qc === 'REJECTED' ? 'REJECTED' : ($c->kashift_qc ?? ''),
            $c->supervisor_qc === 'REJECTED' ? 'REJECTED' : ($c->supervisor_qc ?? ''),
            $c->asst_manager_qc === 'REJECTED' ? 'REJECTED' : ($c->asst_manager_qc ?? ''),
            $c->manager_qc === 'REJECTED' ? 'REJECTED' : ($c->manager_qc ?? '')
        ];
    }

    public function index(Request $request)
    {
        // For restricted roles (inspector, plating), override request plant to their own plant
        // Admin, Manager, Asst Manager, Supervisor, Kashift are trusted to switch plants via menu
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];

        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $plantFilter = $request->get('plant');

        $filters = [
            'plant' => $plantFilter,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'approval_status' => $request->approval_status,
            'item_id' => $request->item_id,
            'next_proses' => $request->next_proses,
            'id' => $request->id,
            'search' => $request->search,
        ];

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::orderBy('name')->get();

        return view('sub_assy.index', compact('checksheets', 'items'));
    }

    // Tampilkan form (diupdate untuk mengirim data items)
    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $user = auth()->user();

        // Roles that can switch between plants via request parameter
        $canSwitchPlants = ['admin', 'supervisor', 'supervisor_plating', 'manager', 'manager_qc', 'manager_plating', 'kashift', 'asst_manager'];

        // IMPORTANT: Inspector role is NOT in exemptRoles of HasPlantFilter trait,
        // so global scope already filters by plant. We need to remove it to prevent double filtering.
        $query = Item::byCategory('Sub Assy')->orderBy('name');

        if (in_array($user->role, $canSwitchPlants)) {
            // Admin/Manager/Supervisor: use normal query, filter by request plant_id if provided
            if ($request->has('plant')) {
                $query->where('plant_id', Plant::resolveId($request->query('plant')));
            }
        } else {
            // Inspector: strictly follow user's assigned plant_id
            $query->where('plant_id', $user->plant_id);
        }

        $items = $query->get();

        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('sub_assy.create', compact('items', 'defaultDate', 'defaultShift'));
    }

    // Simpan data (submission)
    public function store(StoreSubAssyChecksheetRequest $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        $result = $this->checksheetService->createChecksheet(
            $request->validated(),
            fn($checksheet) => $this->mapExportRow($checksheet)
        );

        if ($result['checksheet']) {
            // Preserve plant parameter in redirect
            $plantParam = $request->input('plant') ?? auth()->user()->plant_id;
            return redirect()->route('checksheet.sub_assy', ['plant' => $plantParam])
                ->with('success', 'Data Checksheet berhasil disimpan (Local Only).');
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan data.');
        }
    }

    // Edit Checksheet
    public function edit($id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = SubAssyChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::orderBy('name')->get();
        return view('sub_assy.edit', compact('checksheet', 'items'));
    }

    // Update Checksheet
    public function update(UpdateSubAssyChecksheetRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        $this->checksheetService->updateChecksheet($id, $request->validated());
        return redirect()->route('admin.checksheets.index', $request->query())->with('success', 'Checksheet berhasil diperbarui.');
    }

    // Delete Checksheet
    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $this->checksheetService->deleteChecksheet($id);

        // Preserve plant parameter when redirecting back
        $redirectParams = [];
        if ($request->has('plant')) {
            $redirectParams['plant'] = $request->input('plant');
        }

        return redirect()->route('admin.checksheets.index', $redirectParams)
            ->with('success', 'Data Checksheet berhasil dihapus.');
    }

    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = SubAssyChecksheet::findOrFail($id);
        return view('sub_assy.edit_approval', compact('checksheet'));
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

        $this->checksheetService->updateApprovalStatus($id, $validated);

        return redirect()->route('admin.checksheets.index', $request->query())->with('success', 'Status approval berhasil diperbarui oleh Admin.');
    }
}
