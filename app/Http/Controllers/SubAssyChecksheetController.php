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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Log;

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
            'No. Meja',
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
            $c->line ?? '-',
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
            'operator_initials' => $request->operator_initials,
            'customer' => $request->customer,
            'next_proses' => $request->next_proses,
            'id' => $request->id,
            'search' => $request->search,
            'qr_raw' => $request->qr_raw,
            'entry_method' => $request->entry_method,
            'shift' => $request->shift,
            'line' => $request->line,
        ];

        // Default: hanya tampilkan data regular, kecuali mode verifikasi aktif
        if ($request->get('view_mode') !== 'verifikasi') {
            $filters['entry_method'] = 'regular';
        }

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);

        // Data for filters (Cached per plant to avoid 4x subquery scans over 44,000+ rows on every page load)
        $plantId = \App\Models\Plant::resolveId($filters['plant']);
        
        $items = \Illuminate\Support\Facades\Cache::remember("sub_assy_filter_items_{$plantId}", 1800, function () use ($plantId) {
            return Item::where('plant_id', $plantId)->orderBy('name')->get();
        });

        $customers = \Illuminate\Support\Facades\Cache::remember("sub_assy_filter_cust_{$plantId}", 1800, function () use ($plantId) {
            return Item::where('plant_id', $plantId)->whereNotNull('customer')->where('customer', '!=', '')->distinct()->pluck('customer')->sort();
        });

        $initials = \Illuminate\Support\Facades\Cache::remember("sub_assy_filter_init_{$plantId}", 1800, function () use ($plantId) {
            return SubAssyChecksheet::where('plant_id', $plantId)
                ->where('date', '>=', now()->subDays(90))
                ->whereNotNull('operator_initials')
                ->distinct()
                ->pluck('operator_initials')
                ->sort();
        });

        $lines = \Illuminate\Support\Facades\Cache::remember("sub_assy_filter_lines_{$plantId}", 3600, function () use ($plantId) {
            return SubAssyChecksheet::where('plant_id', $plantId)
                ->whereNotNull('line')
                ->distinct()
                ->pluck('line')
                ->sort(SORT_NUMERIC)
                ->values();
        });

        return view('sub_assy.index', compact('checksheets', 'items', 'customers', 'initials', 'lines'));
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

        $plant = \App\Models\Plant::resolveId($request->query('plant') ?? $user->plant_id);
        $nextProcesses = \App\Models\NextProcess::where('plant_id', $plant)
            ->where('module', 'sub_assy')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('sub_assy.create', compact('items', 'defaultDate', 'defaultShift', 'plant', 'nextProcesses'));
    }

    /**
     * Cek meja terakhir yang digunakan untuk item tertentu hari ini.
     * Digunakan untuk auto-fill field "meja" saat item dipilih.
     */
    public function getLastLine(Request $request)
    {
        $itemId  = $request->get('item_id');
        $plantId = \App\Models\Plant::resolveId($request->get('plant') ?? auth()->user()->plant_id);
        $date    = $request->get('date', now()->toDateString());

        if (!$itemId) {
            return response()->json(['found' => false, 'line' => null]);
        }

        $record = \App\Models\SubAssyChecksheet::where('item_id', $itemId)
            ->where('plant_id', $plantId)
            ->whereDate('date', $date)
            ->orderBy('created_at', 'desc')
            ->first(['line']);

        if ($record && $record->line) {
            return response()->json([
                'found' => true,
                'line'  => $record->line,
            ]);
        }

        return response()->json(['found' => false, 'line' => null]);
    }

    // Simpan data (submission)
    public function store(StoreSubAssyChecksheetRequest $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $result = $this->checksheetService->createChecksheet(
                $request->validated(),
                fn($checksheet) => $this->mapExportRow($checksheet)
            );

            if ($result['checksheet']) {
                $checksheet = $result['checksheet'];
                ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Sub Assy baru: {$checksheet->item->name}");
            }

            $message = 'Data Checksheet Sub Assy berhasil disimpan.';
            $plantParam = $request->input('plant') ?? auth()->user()->plant_id;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('admin.checksheets.index', ['plant' => $plantParam])
                ]);
            }

            return redirect()->route('admin.checksheets.index', ['plant' => $plantParam])->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error saving Sub Assy checksheet', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

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
        $query = SubAssyChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::byCategory('Sub Assy')
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        $users = \App\Models\User::where('is_active', true)
            ->whereIn('role', ['admin', 'inspector', 'supervisor', 'kashift'])
            ->orderBy('name')
            ->get();

        $nextProcesses = \App\Models\NextProcess::where('plant_id', $checksheet->plant_id)
            ->where('module', 'sub_assy')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        if (request()->ajax()) {
            return view('sub_assy.partials.edit_form', compact('checksheet', 'items', 'nextProcesses', 'users'));
        }

        return view('sub_assy.edit', compact('checksheet', 'items', 'nextProcesses', 'users'));
    }

    // Update Checksheet
    public function update(UpdateSubAssyChecksheetRequest $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $this->checksheetService->updateChecksheet($id, $request->validated());
        $checksheet = \App\Models\SubAssyChecksheet::find($id);

        ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Sub Assy: {$checksheet->item->name}");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Checksheet berhasil diperbarui.',
                'index_url' => route('admin.checksheets.index', $request->query())
            ]);
        }

        return redirect()->route('admin.checksheets.index', $request->query())->with('success', 'Checksheet berhasil diperbarui.');
    }

    // Delete Checksheet
    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $checksheet = SubAssyChecksheet::find($id);
        $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
        $this->checksheetService->deleteChecksheet($id);
        \App\Helpers\ActivityLogger::log('deleted', null, "Menghapus checksheet Sub Assy: {$itemName}");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Checksheet berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.checksheets.index', $request->query())
            ->with('success', 'Data Checksheet berhasil dihapus.');
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
                $this->checksheetService->deleteChecksheet($id);
            }
            \DB::commit();

            \App\Helpers\ActivityLogger::log('deleted', null, 'Menghapus multiple checksheet Sub Assy');

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' data berhasil dihapus.',
                'redirect' => route('admin.checksheets.index', $request->query())
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 422);
        }
    }

    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = SubAssyChecksheet::findOrFail($id);
        if (request()->ajax()) {
            return view('sub_assy.partials.edit_approval_form', compact('checksheet'));
        }
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
        $checksheet = SubAssyChecksheet::find($id);
        \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui status approval (Admin) pada checksheet Sub Assy: {$checksheet->item->name}");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status approval berhasil diperbarui.',
                'index_url' => route('admin.checksheets.index', $request->query())
            ]);
        }
        return redirect()->route('admin.checksheets.index', $request->query())->with('success', 'Status approval berhasil diperbarui oleh Admin.');
    }

    public function exportPdf(Request $request)
    {
        // Copy filter logic from index
        $user = auth()->user();
        $requestPlant = $request->get('plant');

        // Apply same restricted roles logic
        $restrictedRoles = ['inspector', 'kashift_plating', 'supervisor_plating', 'manager_plating'];
        if (in_array($user->role, $restrictedRoles)) {
            $request->merge(['plant' => $user->plant_id]);
        }

        // Apply filters via service
        $filters = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'shift', 'entry_method', 'operator_initials', 'customer']);

        // Ensure we get all records, not paginated
        // The service's getFilteredChecksheets returns a Paginator if not careful. 
        // We might need to adjust the query manually or add a 'no_pagination' option to the service.
        // For now, let's manually build the query similar to index but get() instead of paginate()

        // Ensure we get records matching current view (paginated or latest 10)
        $query = $this->checksheetService->getQuery($filters)->latest();

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

        $pdf = Pdf::loadView('sub_assy.pdf', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Sub_Assy_' . date('Y-m-d_H-i-s') . '.pdf');
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
            'search' => $request->search,
            'shift' => $request->shift,
            'entry_method' => $request->entry_method,
            'view_mode' => $request->get('view_mode'),
        ];

        // Default: hanya tampilkan data regular, kecuali mode verifikasi aktif
        if ($request->get('view_mode') !== 'verifikasi') {
            $filters['entry_method'] = 'regular';
        }

        if (empty($filters['start_date'])) {
            $filters['start_date'] = now()->toDateString();
        }
        if (empty($filters['end_date'])) {
            $filters['end_date'] = now()->toDateString();
        }

        $checksheets = $this->checksheetService->getQuery($filters)->latest()->get();

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

        $startDate = \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y');
        $endDate   = \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y');

        return view('sub_assy.print', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }
}
