<?php

namespace App\Http\Controllers;

use App\Models\SortirChecksheet;
use App\Models\Item;
use App\Helpers\ShiftHelper;
use App\Services\SortirChecksheetService;
use App\Http\Requests\StoreSortirChecksheetRequest;
use App\Http\Requests\UpdateSortirChecksheetRequest;
use Illuminate\Http\Request;

class SortirChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $sortirService;

    public function __construct(SortirChecksheetService $sortirService)
    {
        $this->sortirService = $sortirService;
    }

    protected function getModelClass()
    {
        return SortirChecksheet::class;
    }

    protected function getExportHeaders()
    {
        return ['Date', 'Shift', 'Line', 'Item', 'Part Number', 'Source', 'Total Qty', 'Sampling', 'OK', 'NG', 'Judgment', 'Operator', 'Kashift QC', 'Supervisor QC', 'Asst. Manager QC', 'Manager QC'];
    }

    protected function mapExportRow($c)
    {
        return [
            $c->date instanceof \Carbon\Carbon ? $c->date->format('Y-m-d') : $c->date,
            $c->shift,
            $c->line ?? '-',
            $c->item->name ?? '-',
            $c->item->part_number ?? '-',
            strtoupper(str_replace('_', ' ', $c->source_type)),
            $c->total_qty,
            $c->sampling_qty,
            $c->total_ok,
            $c->total_ng,
            $c->judgment,
            $c->operator_initials ?? '-',
            $c->kashift_qc ?? 'PENDING',
            $c->supervisor_qc ?? 'PENDING',
            $c->asst_manager_qc ?? 'PENDING',
            $c->manager_qc ?? 'PENDING',
        ];
    }

    /**
     * Override approval mapping for Sortir specific columns
     */
    protected function getApprovalMapping($type)
    {
        $mappings = [
            'kashift' => ['field' => 'kashift_qc', 'time' => 'kashift_qc_time', 'label' => 'Kashift QC'],
            'supervisor' => ['field' => 'supervisor_qc', 'time' => 'supervisor_qc_time', 'label' => 'Supervisor QC'],
        ];
        return $mappings[$type] ?? null;
    }
    public function index(Request $request)
    {
        // For restricted roles (inspector, plating), override request plant to their own plant
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];

        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $plantFilter = $request->get('plant');
        $filters = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'source_type']);
        $checksheets = $this->sortirService->getFilteredChecksheets($filters);
        $items = Item::orderBy('name')->get();

        return view('sortir.index', compact('checksheets', 'items'));
    }

    public function create(Request $request)
    {
        $filters = $request->only(['plant']);

        // Filter based on plant context
        $user = auth()->user();

        // Roles that can switch between plants via request parameter
        $canSwitchPlants = ['admin', 'supervisor', 'supervisor_plating', 'manager', 'manager_qc', 'manager_plating', 'kashift', 'asst_manager'];

        if (!in_array($user->role, $canSwitchPlants)) {
            // Inspector and other restricted roles: always filter by their own plant
            $filters['plant'] = $user->plant_id;
        }

        $ngItems = $this->sortirService->getAvailableNgItems($filters);

        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('sortir.create', compact('ngItems', 'defaultDate', 'defaultShift'));
    }

    public function store(StoreSortirChecksheetRequest $request)
    {
        try {
            $this->sortirService->createSortirChecksheet($request->validated());
            return redirect()->back()->with('success', 'Data Sortir berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data Sortir: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $query = SortirChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::orderBy('name')->get();
        return view('sortir.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateSortirChecksheetRequest $request, $id)
    {
        try {
            $this->sortirService->updateChecksheet($id, $request->validated());
            return redirect()->route('sortir.index', $this->getFilterParams($request))->with('success', 'Data Sortir berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data Sortir: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->sortirService->deleteChecksheet($id);
            return redirect()->route('sortir.index', $this->getFilterParams($request, true))
                ->with('success', 'Data Sortir berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data Sortir: ' . $e->getMessage());
        }
    }
    protected function applyFilters($query, Request $request)
    {
        // Redundant as we use service for filtering.
    }

    protected function getFilterParams(Request $request, $ignorePage = false)
    {
        $params = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'source_type']);
        if (!$ignorePage && $request->has('page')) {
            $params['page'] = $request->page;
        }
        return $params;
    }
}
