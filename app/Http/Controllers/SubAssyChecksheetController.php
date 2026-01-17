<?php

namespace App\Http\Controllers;

use App\Models\SubAssyChecksheet;
use App\Models\Item;
use App\Services\SubAssyChecksheetService;
use App\Http\Requests\StoreSubAssyChecksheetRequest;
use App\Http\Requests\UpdateSubAssyChecksheetRequest;
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

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::orderBy('name')->get();

        return view('sub_assy.index', compact('checksheets', 'items'));
    }

    // Tampilkan form (diupdate untuk mengirim data items)
    public function create(Request $request)
    {
        $query = Item::byCategory('Sub Assy')->orderBy('name');

        // Filter items based on plant context
        // If user is Admin or SPV Jakarta and has selected a plant via query param
        $user = auth()->user();
        $isSpvJakarta = ($user->role === 'supervisor' || $user->role === 'supervisor_plating') && $user->plant === 'jakarta';

        if (($user->role === 'admin' || $isSpvJakarta) && $request->has('plant')) {
            $query->where('plant', $request->query('plant'));
        }

        $items = $query->get();
        $now = now();
        $defaultDate = ($now->hour < 7) ? $now->copy()->subDay()->format('Y-m-d') : $now->format('Y-m-d');

        return view('sub_assy.create', compact('items', 'defaultDate'));
    }

    // Simpan data (submission)
    public function store(StoreSubAssyChecksheetRequest $request)
    {
        $result = $this->checksheetService->createChecksheet(
            $request->validated(),
            fn($checksheet) => $this->mapExportRow($checksheet)
        );

        if ($result['google_sheets_success']) {
            return redirect()->back()->with('success', 'Data Checksheet berhasil disimpan & terkirim ke Google Sheets.');
        } else {
            return redirect()->back()->with('success', 'Data tersimpan lokal, namun GAGAL kirim ke Google Sheets. Error: ' . $result['error']);
        }
    }

    // Edit Checksheet
    public function edit($id)
    {
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
        $this->checksheetService->updateChecksheet($id, $request->validated());
        return redirect()->route('admin.checksheets.index', $request->query())->with('success', 'Checksheet berhasil diperbarui.');
    }

    // Delete Checksheet
    public function destroy($id)
    {
        $this->checksheetService->deleteChecksheet($id);
        return redirect()->route('admin.checksheets.index')->with('success', 'Data Checksheet berhasil dihapus.');
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
