<?php

namespace App\Http\Controllers;

use App\Models\SortirChecksheet;
use App\Models\Item;
use App\Helpers\ShiftHelper;
use App\Services\SortirChecksheetService;
use App\Http\Requests\StoreSortirChecksheetRequest;
use App\Http\Requests\UpdateSortirChecksheetRequest;
use Illuminate\Http\Request;
use App\Models\Plant;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ActivityLogger;

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
        $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'source_type', 'shift']);
        $checksheets = $this->sortirService->getFilteredChecksheets($filters);

        $plantId = \App\Models\Plant::resolveId($filters['plant'] ?? null);
        $items = \Illuminate\Support\Facades\Cache::remember("sortir_filter_items_{$plantId}", 1800, function () use ($plantId) {
            return Item::where('plant_id', $plantId)->orderBy('name')->get();
        });

        return view('sortir.index', compact('checksheets', 'items'));
    }

    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
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

        $plant = \App\Models\Plant::resolveId($request->query('plant') ?? $user->plant_id);
        $nextProcesses = \App\Models\NextProcess::where('plant_id', $plant)
            ->where('module', 'sortir')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('sortir.create', compact('ngItems', 'defaultDate', 'defaultShift', 'plant', 'nextProcesses'));
    }

    public function store(StoreSortirChecksheetRequest $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $checksheet = $this->sortirService->createSortirChecksheet($request->validated());
            ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Sortir baru: {$checksheet->item->name}");
            $message = 'Data Sortir berhasil disimpan.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('sortir.index', ['plant' => $request->get('plant')])
                ]);
            }
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data Sortir: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data Sortir: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager']) && auth()->user()->name !== 'Marsiah') {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $query = SortirChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::byCategory(['Sub Assy', 'INPROSES', 'Cross Cut Plating', 'Cross Cut Painting'])
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        $nextProcesses = \App\Models\NextProcess::where('plant_id', $checksheet->plant_id)
            ->where('module', 'sortir')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        if (request()->ajax()) {
            return view('sortir.partials.edit_form', compact('checksheet', 'items', 'nextProcesses'));
        }

        return view('sortir.edit', compact('checksheet', 'items', 'nextProcesses'));
    }

    public function update(UpdateSortirChecksheetRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager']) && auth()->user()->name !== 'Marsiah') {
            abort(403, 'Unauthorized action.');
        }
        try {
            $this->sortirService->updateChecksheet($id, $request->validated());
            $checksheet = \App\Models\SortirChecksheet::find($id);
            ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Sortir: {$checksheet->item->name}");
            $message = 'Data Sortir berhasil diperbarui.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('sortir.index', $this->getFilterParams($request))
                ]);
            }

            return redirect()->route('sortir.index', $this->getFilterParams($request))->with('success', $message);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data Sortir: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui data Sortir: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager']) && auth()->user()->name !== 'Marsiah') {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        try {
            $checksheet = SortirChecksheet::find($id);
            $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
            $this->sortirService->deleteChecksheet($id);
            \App\Helpers\ActivityLogger::log('deleted', null, "Menghapus checksheet Sortir: {$itemName}");
            return redirect()->route('sortir.index', $this->getFilterParams($request, true))
                ->with('success', 'Data Sortir berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data Sortir: ' . $e->getMessage());
        }
    }
    public function bulkDestroy(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager']) && auth()->user()->name !== 'Marsiah') {
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

            $checksheets = SortirChecksheet::whereIn('id', $ids)->get();

            if ($checksheets->isEmpty()) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            foreach ($checksheets as $checksheet) {
                $itemName = $checksheet->item ? $checksheet->item->name : 'Unknown';
                $this->sortirService->deleteChecksheet($checksheet->id);
                ActivityLogger::log('deleted', null, "Menghapus checksheet Sortir: {$itemName} secara massal");
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' data berhasil dihapus.',
                'redirect' => route('sortir.index', $this->getFilterParams($request, true))
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function applyFilters($query, Request $request)
    {
        // Redundant as we use service for filtering.
    }

    protected function getFilterParams(Request $request, $ignorePage = false)
    {
        $params = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'source_type', 'shift']);
        if (!$ignorePage && $request->has('page')) {
            $params['page'] = $request->page;
        }
        return $params;
    }

    public function exportPdf(Request $request)
    {
        // Copy filter logic from index
        $user = auth()->user();

        // Apply same restricted roles logic
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];
        if (in_array($user->role, $restrictedRoles)) {
            $request->merge(['plant' => $user->plant_id]);
        }

        // Filter parameters
        $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'source_type', 'shift']);

        // Get data (using service logic but without pagination)
        $query = $this->sortirService->getQuery($filters)->latest();

        if ($request->has('page')) {
            $checksheets = $query->paginate(10)->getCollection();
        } else {
            $checksheets = $query->limit(10)->get();
        }

        // Plant info for header
        $plantCode = 'karawang'; // default
        $plantName = 'Karawang';

        if ($request->plant) {
            $plant = Plant::where('code', $request->plant)->orWhere('id', $request->plant)->first();
            if ($plant) {
                $plantCode = strtolower($plant->code);
                $plantName = $plant->name;
            }
        } elseif ($user->plant) {
            $plantCode = strtolower($user->plant->code);
            $plantName = $user->plant->name;
        }

        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';

        $pdf = Pdf::loadView('sortir.pdf', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Sortir_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function printView(Request $request)
    {
        $user = auth()->user();
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];
        if (in_array($user->role, $restrictedRoles)) {
            $request->merge(['plant' => $user->plant_id]);
        }

        $filters = [
            'plant' => $request->get('plant'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'approval_status' => $request->approval_status,
            'item_id' => $request->item_id,
            'operator_initials' => $request->operator_initials,
            'customer' => $request->customer,
            'id' => $request->id,
            'shift' => $request->shift,
            'source_type' => $request->source_type,
            'search' => $request->search,
        ];

        if (empty($filters['start_date']) && empty($filters['end_date']) && 
            empty($filters['item_id']) && empty($filters['operator_initials']) && 
            empty($filters['customer']) && empty($filters['search'])) {
            $filters['start_date'] = now()->toDateString();
            $filters['end_date'] = now()->toDateString();
        }

        $checksheets = $this->sortirService->getQuery($filters)->latest()->get();

        $plantCode = 'karawang';
        $plantName = 'Karawang';

        if ($request->plant) {
            $plant = Plant::where('code', $request->plant)->orWhere('id', $request->plant)->first();
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

        return view('sortir.print', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }
}
