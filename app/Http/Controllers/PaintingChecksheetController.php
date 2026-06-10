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

    protected function restrictToKarawang()
    {
        $user = auth()->user();
        if ($user->role === 'admin')
            return;

        $plant = $user->plant;
        if (!$plant || strtolower($plant->code) !== 'karawang') {
            abort(403, 'Akses terbatas untuk Plant Karawang saja.');
        }
    }

    protected function getExportHeaders()
    {
        return [
            'No',
            'Tgl Injection',
            'Shift Injection',
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
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw', 'entry_method', 'shift', 'operator_initials', 'customer']);
        $filters['plant'] = 'karawang';

        // Default: hanya tampilkan data regular, kecuali mode verifikasi aktif
        if ($request->get('view_mode') !== 'verifikasi') {
            $filters['entry_method'] = 'regular';
        }

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        
        $plantId = \App\Models\Plant::resolveId('karawang');
        
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
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw', 'entry_method', 'shift', 'operator_initials', 'customer']);
        $filters['plant'] = 'karawang';

        if (empty($filters['start_date'])) {
            $filters['start_date'] = now()->toDateString();
        }
        if (empty($filters['end_date'])) {
            $filters['end_date'] = now()->toDateString();
        }

        $checksheets = $this->checksheetService->buildFilteredQuery($filters)->latest()->get();

        $plantName = 'Karawang';
        $plantCode = 'karawang';

        $dispStart = $filters['start_date'];
        $dispEnd = $filters['end_date'];

        $startDate = \Carbon\Carbon::parse($dispStart)->format('d/m/Y');
        $endDate   = \Carbon\Carbon::parse($dispEnd)->format('d/m/Y');

        return view('painting.print', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }

    public function create(Request $request)
    {
        $this->restrictToKarawang();

        $user = auth()->user();
        $items = Item::whereHas('category', function ($q) {
            $q->where('name', 'Painting');
        })->whereHas('plant', function ($q) {
            $q->where('code', 'karawang');
        })->orderBy('name')->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        $plant = \App\Models\Plant::resolveId('karawang');
        $nextProcesses = \App\Models\NextProcess::where('plant_id', $plant)
            ->where('module', 'Painting')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('painting.create', compact('items', 'defaultDate', 'defaultShift', 'plant', 'nextProcesses'));
    }

    public function store(StorePaintingChecksheetRequest $request)
    {
        $this->restrictToKarawang();

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
                        'index_url' => route('painting.index', ['plant' => 'karawang'])
                    ]);
                }

                return redirect()->route('painting.index', ['plant' => 'karawang'])
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

    public function edit($id)
    {
        $this->restrictToKarawang();

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
        $this->restrictToKarawang();

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

            return redirect()->route('painting.index')->with('success', $message);
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
        $this->restrictToKarawang();

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

        return redirect()->route('painting.index')->with('success', 'Data Checksheet Painting berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw', 'shift', 'operator_initials', 'customer']);
        $filters['plant'] = 'karawang';

        $checksheets = $this->checksheetService->getQuery($filters)->latest()->get();

        $plantName = 'Karawang';
        $plantCode = 'karawang';
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
        return redirect()->route('painting.index', $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw', 'shift']))->with('success', 'Status approval Painting berhasil diperbarui.');
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

        if (!$itemId || !$operatorInitials) {
            return response()->json(['success' => false]);
        }

        $last = PaintingChecksheet::withoutGlobalScope('plant')
            ->where('item_id', $itemId)
            ->where('operator_initials', $operatorInitials)
            ->latest('id')
            ->first();

        if ($last) {
            return response()->json([
                'success' => true,
                'injection_date' => $last->injection_date ? $last->injection_date->toDateString() : null,
                'injection_shift' => $last->injection_shift,
                'line' => $last->line
            ]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Report Harian: Rekap data Verification per Item & Shift
     */
    public function dailyRecap(Request $request)
    {
        $startDate = $request->get('start_date') ?: ($request->get('date') ?: now()->toDateString());
        $endDate = $request->get('end_date') ?: $startDate;
        $plant = 'karawang';
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
        
        $plantModel = \App\Models\Plant::where('code', $plant)->orWhere('id', $plant)->first();
        $plantCode = $plantModel ? strtolower($plantModel->code) : 'karawang';
        $plantName = $plantModel ? $plantModel->name : 'Karawang';

        return view('painting.daily_recap', compact('recap', 'inspectorRecap', 'startDate', 'endDate', 'plantName', 'plantCode'));
    }
}
