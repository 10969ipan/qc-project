<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\Item;
use App\Services\IncomingMaterialService;
use App\Http\Requests\StoreIncomingMaterialRequest;
use App\Http\Requests\UpdateIncomingMaterialRequest;
use App\Models\Plant;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomingMaterialController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(IncomingMaterialService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return IncomingMaterial::class;
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
            'Qty KG',
            'Komper KG',
            'Sampling KG',
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
            $c->quantity_kg,
            $c->komper_karung_kg,
            $c->sampling_size_karung_kg,
            $c->expired_date->format('d/m/Y'),
            $c->judgment,
            $c->operator_initials
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::byCategory('Incoming Material')->orderBy('name')->get();

        return view('incoming.materials.index', compact('checksheets', 'items'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $query = Item::byCategory('Incoming Material')->orderBy('name');

        if ($request->has('plant')) {
            $query->where('plant_id', Plant::resolveId($request->query('plant')));
        } else {
            $query->where('plant_id', $user->plant_id);
        }

        $items = $query->get();
        return view('incoming.materials.create', compact('items'));
    }

    public function store(StoreIncomingMaterialRequest $request)
    {
        $this->checksheetService->createChecksheet($request->validated());
        return redirect()->route('incoming.materials.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
            ->with('success', 'Data Incoming Material berhasil disimpan.');
    }

    public function edit($id)
    {
        $checksheet = IncomingMaterial::findOrFail($id);
        $items = Item::byCategory('Incoming Material')->orderBy('name')->get();

        if (request()->ajax()) {
            return view('incoming.materials.partials.edit_form', compact('checksheet', 'items'));
        }
        return view('incoming.materials.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateIncomingMaterialRequest $request, $id)
    {
        $this->checksheetService->updateChecksheet($id, $request->validated());
        return redirect()->route('incoming.materials.index', $request->query())->with('success', 'Incoming Material berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->checksheetService->deleteChecksheet($id);
        return redirect()->route('incoming.materials.index', $request->query())->with('success', 'Incoming Material berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $checksheets = $this->checksheetService->getQuery($filters)->get();
        $plantName = Plant::resolveName($request->plant ?? auth()->user()->plant_id);

        $pdf = Pdf::loadView('incoming.materials.pdf', compact('checksheets', 'plantName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Incoming_Material_' . date('Ymd_His') . '.pdf');
    }
}
