<?php

namespace App\Http\Controllers;

use App\Models\DurabilityPlatingChecksheet;
use App\Models\ThicknessStandard;
use App\Services\DurabilityPlatingChecksheetService;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DurabilityPlatingChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(DurabilityPlatingChecksheetService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return DurabilityPlatingChecksheet::class;
    }

    protected function getExportHeaders()
    {
        return [
            'No',
            'Tgl Test',
            'Shift',
            'Tgl Produksi',
            'No Lot Produksi',
            'Part Name',
            'Customer',
            'Standard',
            'Actual Cu',
            'Actual Ni',
            'Actual Cr',
            'Result',
            'Step Test SB',
            'Step Test MP',
            'Analis',
            'Keterangan',
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
            $c->shift,
            $c->tanggal_produksi ? $c->tanggal_produksi->format('d/m/Y') : '-',
            $c->no_lot_produksi,
            $c->standard->part_name ?? '-',
            $c->standard->customer ?? '-',
            $c->standard->standard_name ?? '-',
            $c->thickness_cu,
            $c->thickness_ni,
            $c->thickness_cr,
            $c->result,
            $c->step_test_sb,
            $c->step_test_mp,
            $c->analis,
            $c->keterangan ?? '-',
            $c->kashift_qc ?? '',
            $c->supervisor_qc ?? '',
            $c->asst_manager_qc ?? '',
            $c->manager_qc ?? ''
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->only(['id', 'start_date', 'end_date', 'approval_status', 'search', 'shift', 'plant']);
        if (empty($filters['plant'])) {
            $filters['plant'] = 'karawang'; // Default for durability plating
        }

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);

        return view('durability_plating.index', compact('checksheets'));
    }

    public function create(Request $request)
    {
        $plantCode = $request->query('plant', 'karawang');
        $plantId = Plant::resolveId($plantCode);
        
        $standards = ThicknessStandard::where('plant_id', $plantId)->orderBy('part_name')->get();
        
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('durability_plating.create', compact('standards', 'defaultDate', 'defaultShift', 'plantCode'));
    }

    public function store(Request $request)
    {
        $plantCode = $request->input('plant', 'karawang');
        $plantId = Plant::resolveId($plantCode);

        $validated = $request->validate([
            'date' => 'required|date',
            'tanggal_produksi' => 'required|date',
            'shift' => 'required|integer',
            'thickness_standard_id' => 'required|exists:thickness_standards,id',
            'no_lot_produksi' => 'nullable|string|max:255',
            'thickness_cr' => 'nullable|numeric',
            'thickness_ni' => 'nullable|numeric',
            'thickness_cu' => 'nullable|numeric',
            'step_test_sb' => 'nullable|string|max:255',
            'step_test_mp' => 'nullable|string|max:255',
            'result' => 'required|string|in:OK,NG',
            'analis' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $validated['plant_id'] = $plantId;

        try {
            $result = $this->checksheetService->createChecksheet($validated);

            if ($result['checksheet']) {
                $checksheet = $result['checksheet'];
                \App\Helpers\ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Durability Plating baru.");
                
                return redirect()->route('durability_plating.index', ['plant' => $plantCode])
                    ->with('success', 'Data Checksheet Durability Plating berhasil disimpan.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $checksheet = DurabilityPlatingChecksheet::findOrFail($id);
        $standards = ThicknessStandard::where('plant_id', $checksheet->plant_id)->orderBy('part_name')->get();
        
        return view('durability_plating.edit', compact('checksheet', 'standards'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'tanggal_produksi' => 'required|date',
            'shift' => 'required|integer',
            'thickness_standard_id' => 'required|exists:thickness_standards,id',
            'no_lot_produksi' => 'nullable|string|max:255',
            'thickness_cr' => 'nullable|numeric',
            'thickness_ni' => 'nullable|numeric',
            'thickness_cu' => 'nullable|numeric',
            'step_test_sb' => 'nullable|string|max:255',
            'step_test_mp' => 'nullable|string|max:255',
            'result' => 'required|string|in:OK,NG',
            'analis' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $this->checksheetService->updateChecksheet($id, $validated);
            $checksheet = DurabilityPlatingChecksheet::find($id);
            \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Durability Plating.");

            return redirect()->route('durability_plating.index')->with('success', 'Checksheet Durability Plating berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->checksheetService->deleteChecksheet($id);
        \App\Helpers\ActivityLogger::log('deleted', null, "Menghapus checksheet Durability Plating.");
        return redirect()->route('durability_plating.index')->with('success', 'Data Checksheet Durability Plating berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $filters = $request->only(['id', 'start_date', 'end_date', 'approval_status', 'search', 'shift', 'plant']);
        if (empty($filters['plant'])) {
            $filters['plant'] = 'karawang';
        }

        $checksheets = $this->checksheetService->getQuery($filters)->latest()->get();

        $plantName = 'Karawang';
        $plantCode = 'karawang';
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';

        $pdf = Pdf::loadView('durability_plating.pdf', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Durability_Plating_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function printView(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'approval_status', 'search', 'shift', 'plant']);
        if (empty($filters['plant'])) {
            $filters['plant'] = 'karawang';
        }

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

        return view('durability_plating.print', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }

    public function editApproval($id)
    {
        $checksheet = DurabilityPlatingChecksheet::findOrFail($id);
        if (request()->ajax()) {
            return view('durability_plating.partials.edit_approval_form', compact('checksheet'));
        }
        return view('durability_plating.edit_approval', compact('checksheet'));
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
        $checksheet = DurabilityPlatingChecksheet::find($id);
        \App\Helpers\ActivityLogger::log('updated', $checksheet, "Memperbarui status approval pada checksheet Durability Plating.");
        return redirect()->back()->with('success', 'Status approval Durability Plating berhasil diperbarui.');
    }
}
