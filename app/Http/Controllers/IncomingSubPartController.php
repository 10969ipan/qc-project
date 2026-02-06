<?php

namespace App\Http\Controllers;

use App\Models\IncomingSubPart;
use App\Models\Item;
use App\Services\IncomingSubPartService;
use App\Http\Requests\StoreIncomingSubPartRequest;
use App\Http\Requests\UpdateIncomingSubPartRequest;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomingSubPartController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(IncomingSubPartService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return IncomingSubPart::class;
    }

    protected function getExportHeaders()
    {
        return [
            'No',
            'Standard',
            'Barang',
            'Part No',
            'Tgl Datang',
            'Tgl Check',
            'Lot/Batch',
            'Qty',
            'Sampling',
            'Dimensi',
            'Expired',
            'Judgment',
            'Inisial QC'
        ];
    }

    protected function mapExportRow($c)
    {
        return [
            $c->id,
            $c->standard ?? '-',
            $c->item->name ?? '-',
            $c->item->part_number ?? '-',
            $c->tanggal_datang->format('d/m/Y'),
            $c->date->format('d/m/Y'),
            $c->lot_batch_number,
            $c->quantity,
            $c->sampling_size_pcs,
            $c->check_dimensi ?? '-',
            $c->expired_date->format('d/m/Y'),
            $c->judgment,
            $c->operator_initials
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::byCategory('Incoming Sub-Part')->orderBy('name')->get();

        return view('incoming.sub_parts.index', compact('checksheets', 'items'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $query = Item::byCategory('Incoming Sub-Part')->orderBy('name');

        if ($request->has('plant')) {
            $query->where('plant_id', Plant::resolveId($request->query('plant')));
        } else {
            $query->where('plant_id', $user->plant_id);
        }

        $items = $query->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('incoming.sub_parts.create', compact('items', 'defaultDate', 'defaultShift'));
    }

    public function store(StoreIncomingSubPartRequest $request)
    {
        $this->checksheetService->createChecksheet($request->validated());
        return redirect()->route('incoming.sub_parts.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
            ->with('success', 'Data Incoming Sub-Part berhasil disimpan.');
    }

    public function edit($id)
    {
        $checksheet = IncomingSubPart::findOrFail($id);
        $items = Item::byCategory('Incoming Sub-Part')->orderBy('name')->get();

        if (request()->ajax()) {
            return view('incoming.sub_parts.partials.edit_form', compact('checksheet', 'items'));
        }
        return view('incoming.sub_parts.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateIncomingSubPartRequest $request, $id)
    {
        $this->checksheetService->updateChecksheet($id, $request->validated());
        return redirect()->route('incoming.sub_parts.index', $request->query())->with('success', 'Incoming Sub-Part berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->checksheetService->deleteChecksheet($id);
        return redirect()->route('incoming.sub_parts.index', $request->query())->with('success', 'Incoming Sub-Part berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $checksheets = $this->checksheetService->getQuery($filters)->get();
        $plantName = Plant::resolveName($request->plant ?? auth()->user()->plant_id);

        $pdf = Pdf::loadView('incoming.sub_parts.pdf', compact('checksheets', 'plantName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Incoming_SubPart_' . date('Ymd_His') . '.pdf');
    }
}
