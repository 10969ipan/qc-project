<?php

namespace App\Http\Controllers;

use App\Models\DoubleTapeChecksheet;
use App\Models\Item;
use App\Services\DoubleTapeChecksheetService;
use App\Http\Requests\StoreDoubleTapeChecksheetRequest;
use App\Http\Requests\UpdateDoubleTapeChecksheetRequest;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DoubleTapeChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(DoubleTapeChecksheetService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return DoubleTapeChecksheet::class;
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
            $c->date->format('d/m/Y'),
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
            $c->kashift_qc ?? '',
            $c->supervisor_qc ?? '',
            $c->asst_manager_qc ?? '',
            $c->manager_qc ?? ''
        ];
    }

    public function index(Request $request)
    {
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'check_type']);
        $filters['plant'] = 'karawang';

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::whereHas('plant', function ($q) {
            $q->where('code', 'karawang');
        })->orderBy('name')->get();

        return view('double_tape.index', compact('checksheets', 'items'));
    }

    public function create(Request $request)
    {
        $this->restrictToKarawang();

        $user = auth()->user();
        $items = Item::whereHas('category', function ($q) {
            $q->where('name', 'Double Tape');
        })->whereHas('plant', function ($q) {
            $q->where('code', 'karawang');
        })->orderBy('name')->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('double_tape.create', compact('items', 'defaultDate', 'defaultShift'));
    }

    public function store(StoreDoubleTapeChecksheetRequest $request)
    {
        $this->restrictToKarawang();

        $result = $this->checksheetService->createChecksheet(
            $request->validated(),
            fn($checksheet) => $this->mapExportRow($checksheet)
        );

        if ($result['checksheet']) {
            $checksheet = $result['checksheet'];
            \App\Helpers\ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Double Tape baru: {$checksheet->item->name}");
            $message = 'Data Checksheet Double Tape berhasil disimpan.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('double_tape.index')
                ]);
            }
            return redirect()->route('double_tape.index')
                ->with('success', $message);
        } else {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data.');
        }
    }

    public function edit($id)
    {
        $this->restrictToKarawang();

        $checksheet = DoubleTapeChecksheet::findOrFail($id);
        $items = Item::byCategory('Double Tape')
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        if (request()->ajax()) {
            return view('double_tape.partials.edit_form', compact('checksheet', 'items'));
        }

        return view('double_tape.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateDoubleTapeChecksheetRequest $request, $id)
    {
        $this->restrictToKarawang();

        $this->checksheetService->updateChecksheet($id, $request->validated());
        $checksheet = DoubleTapeChecksheet::find($id);
        \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Double Tape: {$checksheet->item->name}");
        return redirect()->back()->with('success', 'Checksheet Double Tape berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->restrictToKarawang();

        $checksheet = DoubleTapeChecksheet::find($id);
        $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
        $this->checksheetService->deleteChecksheet($id);
        \App\Helpers\ActivityLogger::log('deleted', null, "Menghapus checksheet Double Tape: {$itemName}");
        return redirect()->back()->with('success', 'Data Checksheet Double Tape berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'check_type']);
        $filters['plant'] = 'karawang';

        $checksheets = $this->checksheetService->getQuery($filters)->latest()->get();

        $plantName = 'Karawang';
        $plantCode = 'karawang';
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';

        $pdf = Pdf::loadView('double_tape.pdf', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Double_Tape_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function printView(Request $request)
    {
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search', 'check_type']);
        $filters['plant'] = 'karawang';

        // Default ke hari ini jika tidak ada filter tanggal
        if (empty($filters['start_date'])) {
            $filters['start_date'] = now()->toDateString();
        }
        if (empty($filters['end_date'])) {
            $filters['end_date'] = now()->toDateString();
        }

        $checksheets = $this->checksheetService->getQuery($filters)->latest()->get();

        $plantName = 'Karawang';
        $plantCode = 'karawang';
        $startDate = \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y');
        $endDate   = \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y');

        return view('double_tape.print', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }

    public function editApproval($id)
    {
        $checksheet = DoubleTapeChecksheet::findOrFail($id);
        if (request()->ajax()) {
            return view('double_tape.partials.edit_approval_form', compact('checksheet'));
        }
        return view('double_tape.edit_approval', compact('checksheet'));
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
        $checksheet = DoubleTapeChecksheet::find($id);
        \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui status approval (Admin) pada checksheet Double Tape: {$checksheet->item->name}");
        return redirect()->back()->with('success', 'Status approval Double Tape berhasil diperbarui.');
    }
}
