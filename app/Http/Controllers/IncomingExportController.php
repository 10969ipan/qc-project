<?php

namespace App\Http\Controllers;

use App\Models\IncomingExport;
use App\Models\Item;
use App\Services\IncomingExportService;
use App\Http\Requests\StoreIncomingExportRequest;
use App\Http\Requests\UpdateIncomingExportRequest;
use App\Models\Plant;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomingExportController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(IncomingExportService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return IncomingExport::class;
    }

    protected function getExportHeaders()
    {
        return [
            'No',
            'Standard',
            'Barang',
            'Part No',
            'Tgl Check',
            'Tgl Delivery',
            'Judgment',
            'Inisial QC',
            'Remarks'
        ];
    }

    protected function mapExportRow($c)
    {
        return [
            $c->id,
            $c->standard ?? '-',
            $c->item->name ?? '-',
            $c->item->part_number ?? '-',
            $c->date->format('d/m/Y'),
            $c->tanggal_delivery->format('d/m/Y'),
            $c->judgment,
            $c->operator_initials,
            $c->remarks ?? '-'
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::byCategory('Incoming Export')->orderBy('name')->get();

        return view('incoming.exports.index', compact('checksheets', 'items'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $query = Item::byCategory('Incoming Export')->orderBy('name');

        if ($request->has('plant')) {
            $query->where('plant_id', Plant::resolveId($request->query('plant')));
        } else {
            $query->where('plant_id', $user->plant_id);
        }

        $items = $query->get();
        return view('incoming.exports.create', compact('items'));
    }

    public function store(StoreIncomingExportRequest $request)
    {
        $this->checksheetService->createChecksheet($request->validated());
        return redirect()->route('incoming.exports.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
            ->with('success', 'Data Incoming Export berhasil disimpan.');
    }

    public function edit($id)
    {
        $checksheet = IncomingExport::findOrFail($id);
        $items = Item::byCategory('Incoming Export')->orderBy('name')->get();

        if (request()->ajax()) {
            return view('incoming.exports.partials.edit_form', compact('checksheet', 'items'));
        }
        return view('incoming.exports.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateIncomingExportRequest $request, $id)
    {
        $this->checksheetService->updateChecksheet($id, $request->validated());
        return redirect()->route('incoming.exports.index', $request->query())->with('success', 'Incoming Export berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->checksheetService->deleteChecksheet($id);
        return redirect()->route('incoming.exports.index', $request->query())->with('success', 'Incoming Export berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $checksheets = $this->checksheetService->getQuery($filters)->get();
        $plantName = Plant::resolveName($request->plant ?? auth()->user()->plant_id);

        $pdf = Pdf::loadView('incoming.exports.pdf', compact('checksheets', 'plantName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Incoming_Export_' . date('Ymd_His') . '.pdf');
    }
}
