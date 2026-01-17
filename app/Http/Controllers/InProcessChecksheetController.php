<?php

namespace App\Http\Controllers;

use App\Models\InProcessChecksheet;
use App\Models\Item;
use App\Services\InProcessChecksheetService;
use App\Http\Requests\StoreInProcessChecksheetRequest;
use App\Http\Requests\UpdateInProcessChecksheetRequest;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;
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

    private $hardcodedStandards = [
        '53102-K0L -D002' => [ // Corresponds to "COVER HNDL END K3VA"
            '1' => ['size' => 5, 'tolerance' => 0.2],
            '2' => ['size' => 10, 'tolerance' => 0.2],
            '3' => ['size' => 10, 'tolerance' => 0.5],
            '4' => ['size' => 20.5, 'tolerance' => 0.2],
            '5' => ['size' => 20, 'tolerance' => 0.2],
        ],
        '1PA - F836B - 00' => [ // Corresponds to "EMBLEM 3D"
            '1' => ['size' => 25, 'tolerance' => 0.2],
            '2' => ['size' => 21, 'tolerance' => 0.4],
            '3' => ['size' => 3.2, 'tolerance' => 0.2],
            '4' => ['size' => 24, 'tolerance' => 0.4],
        ],
        '53209-K3V-N100' => [ // Corresponds to "COVER HEAD LIGHT (NATURAL)"
            '1' => ['size' => 10, 'tolerance' => 0.2],
            '2' => ['size' => 10, 'tolerance' => 0.2],
            '3' => ['size' => 10, 'tolerance' => 0.2],
            '4' => ['size' => 10, 'tolerance' => 0.2],
        ],
    ];



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
            $request->merge(['plant' => auth()->user()->plant]);
        }

        $filters = [
            'plant' => $request->get('plant'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'approval_status' => $request->approval_status,
            'item_id' => $request->item_id,
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
        $query = Item::byCategory('Inprosess')->orderBy('name');

        // Filter items based on plant context
        // Filter items based on plant context
        $user = auth()->user();
        $isSpvJakarta = ($user->role === 'supervisor' || $user->role === 'supervisor_plating') && $user->plant === 'jakarta';

        if (($user->role === 'admin' || $isSpvJakarta) && $request->has('plant')) {
            $query->where('plant', $request->query('plant'));
        }

        $items = $query->get();
        $now = now();
        $defaultDate = ($now->hour < 7) ? $now->copy()->subDay()->format('Y-m-d') : $now->format('Y-m-d');

        return view('in_process.create', [
            'items' => $items,
            'defaultDate' => $defaultDate,
            'partDimensionStandards' => json_encode($this->getConsolidatedStandards())
        ]);
    }

    // Simpan data (submission)
    public function store(StoreInProcessChecksheetRequest $request)
    {
        try {
            $result = $this->inProcessService->createChecksheet(
                $request->validated(),
                [$this, 'mapExportRow']
            );

            $message = 'Data Checksheet Inprocess berhasil disimpan & terkirim ke Google Sheets.';
            if (!$result['google_sheets_success']) {
                $message = 'Data Checksheet Inprocess berhasil disimpan lokal, namun GAGAL kirim ke Google Sheets. Error: ' . $result['error'];
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    // Edit Checksheet
    public function edit($id)
    {
        $query = InProcessChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::orderBy('name')->get();
        return view('in_process.edit', [
            'checksheet' => $checksheet,
            'items' => $items,
            'partDimensionStandards' => json_encode($this->getConsolidatedStandards())
        ]);
    }

    // Update Checksheet
    public function update(UpdateInProcessChecksheetRequest $request, $id)
    {
        try {
            $this->inProcessService->updateChecksheet($id, $request->validated());
            return redirect()->route('in_process.index')->with('success', 'Data Checksheet Inprocess berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    // Delete Checksheet
    public function destroy($id)
    {
        try {
            $this->inProcessService->deleteChecksheet($id);
            return redirect()->route('in_process.index')->with('success', 'Data Checksheet Inprocess berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }



    // Export Checksheets to PDF
    public function exportPdf(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'item_id', 'plant']);

        $checksheets = $this->inProcessService->buildFilteredQuery($filters)->get();
        $items = Item::orderBy('name')->get();

        $pdf = Pdf::loadView('in_process.pdf', compact('checksheets', 'items', 'request'));
        return $pdf->setPaper('a4', 'landscape')->stream('laporan-checksheet-inprocess.pdf');
    }

    // Tampilkan form untuk admin mengedit status approval
    public function editApproval($id)
    {
        $checksheet = InProcessChecksheet::findOrFail($id);
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
            return redirect()->route('in_process.index', $request->only(['page', 'part_number', 'customer', 'approval_status', 'date_from', 'date_to']))->with('success', 'Status approval berhasil diperbarui oleh Admin.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status approval: ' . $e->getMessage());
        }
    }
}
