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

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $filters['plant'] = 'karawang'; // Enforce Karawang for this specific checksheet

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::whereHas('plant', function ($q) {
            $q->where('code', 'karawang');
        })->orderBy('name')->get();

        return view('plating.index', compact('checksheets', 'items'));
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

        $result = $this->checksheetService->createChecksheet(
            $request->validated(),
            fn($checksheet) => $this->mapExportRow($checksheet)
        );

        if ($result['checksheet']) {
            $message = 'Data Checksheet Plating berhasil disimpan.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('plating.index')
                ]);
            }
            return redirect()->route('plating.index')
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

        $checksheet = PlatingChecksheet::findOrFail($id);
        $items = Item::byCategory('Plating')
            ->where('plant_id', $checksheet->plant_id)
            ->orderBy('name')
            ->get();

        if (request()->ajax()) {
            return view('plating.partials.edit_form', compact('checksheet', 'items'));
        }

        return view('plating.edit', compact('checksheet', 'items'));
    }

    public function update(UpdatePlatingChecksheetRequest $request, $id)
    {
        $this->restrictToKarawang();

        $this->checksheetService->updateChecksheet($id, $request->validated());
        return redirect()->route('plating.index')->with('success', 'Checksheet Plating berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->restrictToKarawang();

        $this->checksheetService->deleteChecksheet($id);
        return redirect()->route('plating.index')->with('success', 'Data Checksheet Plating berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $this->restrictToKarawang();

        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'search']);
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

        return redirect()->route('plating.index')->with('success', 'Status approval Plating berhasil diperbarui.');
    }
}
