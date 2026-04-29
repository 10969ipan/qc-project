<?php

namespace App\Http\Controllers;

use App\Models\PlatingChecksheet;
use App\Models\Item;
use App\Services\PlatingChecksheetService;
use App\Http\Requests\StorePlatingChecksheetRequest;
use App\Http\Requests\UpdatePlatingChecksheetRequest;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PlatingChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(PlatingChecksheetService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return PlatingChecksheet::class;
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
            'Tgl Plating',
            'Shift Plating',
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
            $c->plating_date ? $c->plating_date->format('d/m/Y') : '-',
            $c->plating_shift ?? '-',
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
            // $c->sampling_qty, // In Plating this is "Check Qty"
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

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw']);
        $filters['plant'] = 'karawang'; 

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        
        $plantId = \App\Models\Plant::resolveId('karawang');
        
        $items = Item::whereIn('id', function($query) use ($plantId) {
            $query->select('item_id')->from('plating_checksheets')->where('plant_id', $plantId);
        })->orderBy('name')->get();

        $customers = Item::whereIn('id', function($query) use ($plantId) {
            $query->select('item_id')->from('plating_checksheets')->where('plant_id', $plantId);
        })->whereNotNull('customer')->distinct()->pluck('customer')->sort();

        $initials = PlatingChecksheet::where('plant_id', $plantId)
            ->whereNotNull('operator_initials')
            ->distinct()
            ->pluck('operator_initials')
            ->sort();

        $canExport = \App\Helpers\AppMenu::checkPermission('plating.index', 'export');
        $canEdit = \App\Helpers\AppMenu::checkPermission('plating.index', 'edit');
        $canDelete = \App\Helpers\AppMenu::checkPermission('plating.index', 'delete');

        return view('plating.index', compact('checksheets', 'items', 'customers', 'initials', 'canExport', 'canEdit', 'canDelete'));
    }

    public function printView(Request $request)
    {
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw']);
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

        return view('plating.print', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }

    public function create(Request $request)
    {
        $this->restrictToKarawang();

        $user = auth()->user();
        $items = Item::whereHas('category', function ($q) {
            $q->where('name', 'Plating');
        })->whereHas('plant', function ($q) {
            $q->where('code', 'karawang');
        })->orderBy('name')->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('plating.create', compact('items', 'defaultDate', 'defaultShift'));
    }

    public function store(StorePlatingChecksheetRequest $request)
    {
        $this->restrictToKarawang();

        try {
            $result = $this->checksheetService->createChecksheet(
                $request->validated(),
                fn($checksheet) => $this->mapExportRow($checksheet)
            );

            if ($result['checksheet']) {
                $checksheet = $result['checksheet'];
                \App\Helpers\ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Plating baru: {$checksheet->item->name}");
                
                $message = 'Data Checksheet Plating berhasil disimpan.';
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'index_url' => route('plating.index', ['plant' => 'karawang'])
                    ]);
                }

                return redirect()->route('plating.index', ['plant' => 'karawang'])
                    ->with('success', $message);
            } else {
                throw new \Exception('Gagal menyimpan data checksheet.');
            }
        } catch (\Exception $e) {
            \Log::error('Plating Store Error: ' . $e->getMessage());
            
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

        $checksheet = PlatingChecksheet::findOrFail($id);
        $items = Item::byCategory('Plating')
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        $users = \App\Models\User::where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        if (request()->ajax()) {
            return view('plating.partials.edit_form', compact('checksheet', 'items', 'users'));
        }

        return view('plating.edit', compact('checksheet', 'items', 'users'));
    }

    public function update(UpdatePlatingChecksheetRequest $request, $id)
    {
        $this->restrictToKarawang();

        try {
            $this->checksheetService->updateChecksheet($id, $request->validated());
            $checksheet = \App\Models\PlatingChecksheet::find($id);
            \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Plating: {$checksheet->item->name}");

            $message = 'Checksheet Plating berhasil diperbarui.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('plating.index')
                ]);
            }

            return redirect()->route('plating.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Plating Update Error: ' . $e->getMessage());

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

    public function destroy($id)
    {
        $this->restrictToKarawang();

        $checksheet = \App\Models\PlatingChecksheet::find($id);
        $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
        $this->checksheetService->deleteChecksheet($id);
        \App\Helpers\ActivityLogger::log('deleted', null, "Menghapus checksheet Plating: {$itemName}");
        return redirect()->route('plating.index')->with('success', 'Data Checksheet Plating berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'qr_raw']);
        $filters['plant'] = 'karawang';

        $checksheets = $this->checksheetService->getQuery($filters)->latest()->get();

        $plantName = 'Karawang';
        $plantCode = 'karawang';
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';

        $pdf = Pdf::loadView('plating.pdf', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Plating_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function editApproval($id)
    {
        $checksheet = PlatingChecksheet::findOrFail($id);
        if (request()->ajax()) {
            return view('plating.partials.edit_approval_form', compact('checksheet'));
        }
        return view('plating.edit_approval', compact('checksheet'));
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
        $checksheet = \App\Models\PlatingChecksheet::find($id);
        \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui status approval (Admin) pada checksheet Plating: {$checksheet->item->name}");
        return redirect()->route('plating.index')->with('success', 'Status approval Plating berhasil diperbarui.');
    }
}
