<?php

namespace App\Http\Controllers;

use App\Models\PaintingChecksheet;
use App\Models\Item;
use App\Services\PaintingChecksheetService;
use App\Http\Requests\StorePaintingChecksheetRequest;
use App\Http\Requests\UpdatePaintingChecksheetRequest;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PaintingChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(PaintingChecksheetService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return PaintingChecksheet::class;
    }

    protected function getPlantCode(Request $request)
    {
        $plant = $request->get('plant') ?? optional(auth()->user()->plant)->code ?? 'karawang';
        return strtolower($plant);
    }

    protected function getExportHeaders()
    {
        return [
            'No',
            'Tgl Lot ID',
            'Shift Lot ID',
            'Inisial Lot ID',
            'Tgl Painting',
            'Shift Painting',
            'No Lot',
            'Tgl Quality',
            'Shift Quality',
            'Jam Before',
            'Barang',
            'Part No',
            'Customer',
            'Total Qty',
            // 'Check Qty',
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
            $c->injection_date ? $c->injection_date->format('d/m/Y') : '-',
            $c->injection_shift ?? '-',
            $c->injection_initials ?? '-',
            $c->painting_date ? $c->painting_date->format('d/m/Y') : '-',
            $c->painting_shift ?? '-',
            $c->no_lot ?? '-',
            $c->date->format('d/m/Y'),
            $c->shift,
            $c->created_at->copy()->subSeconds($c->cycle_time ?? 0)->format('H:i:s'),
            $c->created_at->format('H:i:s'),
            $c->cycle_time ?? '-',
            $c->item->name ?? '-',
            $c->item->part_number ?? '-',
            $c->item->customer ?? '-',
            $c->total_qty,
            // $c->sampling_qty, // In Painting this is "Check Qty"
            $c->total_ok,
            $c->total_ng,
            $c->judgment,
            $c->operator_initials,
            $c->remarks ?? '-',
            $c->kashift_qc ?? '',
            $c->supervisor_qc ?? '',
            $c->asst_manager_qc ?? '',
            $c->manager_qc ?? ''
        ];
    }

    public function index(Request $request)
    {
        $plantCode = $this->getPlantCode($request);
        $filters = $request->only(['id', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw', 'entry_method', 'shift', 'operator_initials', 'customer']);
        $filters['plant'] = $plantCode;

        // Default: hanya tampilkan data regular, kecuali mode verifikasi aktif
        if ($request->get('view_mode') !== 'verifikasi') {
            $filters['entry_method'] = 'regular';
        }

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        
        $plantId = \App\Models\Plant::resolveId($plantCode);
        
        $items = Item::whereIn('id', function($query) use ($plantId) {
            $query->select('item_id')->from('painting_checksheets')->where('plant_id', $plantId);
        })->orderBy('name')->get();

        $customers = Item::whereIn('id', function($query) use ($plantId) {
            $query->select('item_id')->from('painting_checksheets')->where('plant_id', $plantId);
        })->whereNotNull('customer')->distinct()->pluck('customer')->sort();

        $initials = PaintingChecksheet::where('plant_id', $plantId)
            ->whereNotNull('operator_initials')
            ->distinct()
            ->pluck('operator_initials')
            ->sort();

        $canExport = \App\Helpers\AppMenu::checkPermission('painting.index', 'export');
        $canEdit = \App\Helpers\AppMenu::checkPermission('painting.index', 'edit');
        $canDelete = \App\Helpers\AppMenu::checkPermission('painting.index', 'delete');

        return view('painting.index', compact('checksheets', 'items', 'customers', 'initials', 'canExport', 'canEdit', 'canDelete'));
    }

    public function printView(Request $request)
    {
        $plantCode = $this->getPlantCode($request);
        $filters = $request->only(['id', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw', 'entry_method', 'shift', 'operator_initials', 'customer']);
        $filters['plant'] = $plantCode;

        if (empty($filters['start_date'])) {
            $filters['start_date'] = now()->toDateString();
        }
        if (empty($filters['end_date'])) {
            $filters['end_date'] = now()->toDateString();
        }

        $checksheets = $this->checksheetService->buildFilteredQuery($filters)->latest()->get();

        $plantModel = \App\Models\Plant::find(\App\Models\Plant::resolveId($plantCode));
        $plantName = $plantModel ? $plantModel->name : ucfirst($plantCode);

        $dispStart = $filters['start_date'];
        $dispEnd = $filters['end_date'];

        $startDate = \Carbon\Carbon::parse($dispStart)->format('d/m/Y');
        $endDate   = \Carbon\Carbon::parse($dispEnd)->format('d/m/Y');

        return view('painting.print', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }

    public function create(Request $request)
    {
        $plantCode = $this->getPlantCode($request);
        $user = auth()->user();
        $items = Item::whereHas('category', function ($q) {
            $q->where('name', 'Painting');
        })->whereHas('plant', function ($q) use ($plantCode) {
            $q->where('code', $plantCode);
        })->orderBy('name')->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        $plant = \App\Models\Plant::resolveId($plantCode);
        $nextProcesses = \App\Models\NextProcess::where('plant_id', $plant)
            ->where('module', 'Painting')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('painting.create', compact('items', 'defaultDate', 'defaultShift', 'plant', 'nextProcesses'));
    }

    public function store(StorePaintingChecksheetRequest $request)
    {
        $plantCode = $this->getPlantCode($request);

        try {
            $result = $this->checksheetService->createChecksheet(
                $request->validated(),
                fn($checksheet) => $this->mapExportRow($checksheet)
            );

            if ($result['checksheet']) {
                $checksheet = $result['checksheet'];
                \App\Helpers\ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Painting baru: {$checksheet->item->name}");
                
                $message = 'Data Checksheet Painting berhasil disimpan.';
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'index_url' => route('painting.index', ['plant' => $plantCode])
                    ]);
                }

                return redirect()->route('painting.index', array_merge($request->query(), ['plant' => $plantCode]))
                    ->with('success', $message);
            } else {
                throw new \Exception('Gagal menyimpan data checksheet.');
            }
        } catch (\Exception $e) {
            \Log::error('Painting Store Error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        $plantCode = $this->getPlantCode($request);

        $checksheet = PaintingChecksheet::findOrFail($id);
        $items = Item::byCategory('Painting')
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        $users = \App\Models\User::where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        $nextProcesses = \App\Models\NextProcess::where('plant_id', $checksheet->plant_id)
            ->where('module', 'Painting')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        if (request()->ajax()) {
            return view('painting.partials.edit_form', compact('checksheet', 'items', 'users', 'nextProcesses'));
        }

        return view('painting.edit', compact('checksheet', 'items', 'users', 'nextProcesses'));
    }

    public function update(UpdatePaintingChecksheetRequest $request, $id)
    {
        $plantCode = $this->getPlantCode($request);

        try {
            $this->checksheetService->updateChecksheet($id, $request->validated());
            $checksheet = \App\Models\PaintingChecksheet::find($id);
            \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Painting: {$checksheet->item->name}");

            $message = 'Checksheet Painting berhasil diperbarui.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('painting.index')
                ]);
            }

            return redirect()->route('painting.index', $request->query())->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Painting Update Error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        $plantCode = $this->getPlantCode($request);

        $checksheet = \App\Models\PaintingChecksheet::find($id);
        $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
        $this->checksheetService->deleteChecksheet($id);
        \App\Helpers\ActivityLogger::log('deleted', null, "Menghapus checksheet Painting: {$itemName}");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Checksheet Painting berhasil dihapus.'
            ]);
        }

        return redirect()->route('painting.index', $request->query())->with('success', 'Data Checksheet Painting berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $plantCode = $this->getPlantCode($request);

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

            \App\Helpers\ActivityLogger::log('deleted', null, 'Menghapus multiple checksheet Painting');

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' data berhasil dihapus.',
                'redirect' => route('painting.index', $request->query())
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 422);
        }
    }

    public function exportPdf(Request $request)
    {
        $plantCode = $this->getPlantCode($request);
        $filters = $request->only(['id', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw', 'shift', 'operator_initials', 'customer']);
        $filters['plant'] = $plantCode;

        $checksheets = $this->checksheetService->getQuery($filters)->latest()->get();

        $plantModel = \App\Models\Plant::find(\App\Models\Plant::resolveId($plantCode));
        $plantName = $plantModel ? $plantModel->name : ucfirst($plantCode);
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';

        $pdf = Pdf::loadView('painting.pdf', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Painting_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function editApproval($id)
    {
        $checksheet = PaintingChecksheet::findOrFail($id);
        if (request()->ajax()) {
            return view('painting.partials.edit_approval_form', compact('checksheet'));
        }
        return view('painting.edit_approval', compact('checksheet'));
    }

    public function updateApproval(Request $request, $id)
    {
        $validated = $request->validate([
            'kashift_qc' => 'required|in:Pending,Approved,Rejected',
            'supervisor_qc' => 'required|in:Pending,Approved,Rejected',
            'asst_manager_qc' => 'required|in:Pending,Approved,Rejected',
            'manager_qc' => 'required|in:Pending,Approved,Rejected',
        ]);

        $this->checksheetService->updateApprovalStatus($id, $validated);
        $checksheet = \App\Models\PaintingChecksheet::find($id);
        \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui status approval (Admin) pada checksheet Painting: {$checksheet->item->name}");
        return redirect()->route('painting.index', $request->query())->with('success', 'Status approval Painting berhasil diperbarui.');
    }

    /**
     * API: Get auto-generated No Lot based on format A07AE26A.
     */
    public function getAutoNoLot(Request $request)
    {
        $itemId = $request->input('item_id');
        $PaintingDate = $request->input('painting_date');
        $PaintingShift = (int) $request->input('painting_shift', 1);
        $qcShift = (int) $request->input('shift', 1);
        $operatorInitials = strtoupper(trim($request->input('operator_initials', '')));

        if (!$itemId || !$PaintingDate || !$operatorInitials) {
            return response()->json(['no_lot' => null, 'count' => 0]);
        }

        try {
            $dateObj = Carbon::parse($PaintingDate);
            $month = $dateObj->month; // 1-12
            $day = $dateObj->format('d'); // 01-31
            $year2 = $dateObj->format('y'); // 26
            
            $monthChar = chr(64 + $month); // 1->A, 2->B, etc.

            $count = PaintingChecksheet::withoutGlobalScope('plant')
                ->where('item_id', $itemId)
                ->whereDate('painting_date', $dateObj->toDateString())
                ->where('painting_shift', $PaintingShift)
                ->count();

            $seqChar = chr(65 + ($count % 26));
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
     * API: Get last data (injection date/shift and line) for a given item + operator.
     */
    public function getLastData(Request $request)
    {
        $itemId = $request->input('item_id');
        $operatorInitials = strtoupper(trim($request->input('operator_initials', '')));

        if (!$operatorInitials) {
            return response()->json(['success' => false]);
        }

        $lastUserActivity = PaintingChecksheet::withoutGlobalScope('plant')
            ->where('operator_initials', $operatorInitials)
            ->latest('id')
            ->first();

        $line = $lastUserActivity ? $lastUserActivity->line : null;

        $lastItemActivity = null;
        if ($itemId) {
            $lastItemActivity = PaintingChecksheet::withoutGlobalScope('plant')
                ->where('item_id', $itemId)
                ->where('operator_initials', $operatorInitials)
                ->latest('id')
                ->first();
        }

        return response()->json([
            'success' => true,
            'injection_date' => $lastItemActivity && $lastItemActivity->injection_date ? $lastItemActivity->injection_date->toDateString() : null,
            'injection_shift' => $lastItemActivity ? $lastItemActivity->injection_shift : null,
            'injection_initials' => $lastItemActivity ? $lastItemActivity->injection_initials : null,
            'line' => $line
        ]);
    }

    /**
     * Report Harian: Rekap data Verification per Item & Shift
     */
    public function dailyRecap(Request $request)
    {
        $plantCode = $this->getPlantCode($request);
        $startDate = $request->get('start_date') ?: ($request->get('date') ?: now()->toDateString());
        $endDate = $request->get('end_date') ?: $startDate;
        $plant = $plantCode;
        $shift = $request->get('shift');
        $date = $startDate; // For backward compatibility if needed

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'plant' => $plant,
            'shift' => $shift
        ];

        $recap = $this->checksheetService->getDailyRecap($filters);
        $inspectorRecap = $this->checksheetService->getInspectorDailyRecap($filters);
        $ngRecap = $this->checksheetService->getNgDailyRecap($filters);
        
        $plantModel = \App\Models\Plant::where('code', $plant)->orWhere('id', $plant)->first();
        $plantCode = $plantModel ? strtolower($plantModel->code) : 'karawang';
        $plantName = $plantModel ? $plantModel->name : 'Karawang';

        return view('painting.daily_recap', compact('recap', 'inspectorRecap', 'ngRecap', 'startDate', 'endDate', 'plantName', 'plantCode'));
    }
}
