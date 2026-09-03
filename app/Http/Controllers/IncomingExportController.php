<?php

namespace App\Http\Controllers;

use App\Models\IncomingExport;
use App\Models\Item;
use App\Services\IncomingExportService;
use App\Http\Requests\StoreIncomingExportRequest;
use App\Http\Requests\UpdateIncomingExportRequest;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ActivityLogger;

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
        $plantInput = $request->get('plant', auth()->user()->plant_id);
        $plantId = Plant::resolveId($plantInput);

        $filters = [
            'plant'             => $plantInput,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'approval_status'   => $request->approval_status,
            'item_id'           => $request->item_id,
            'operator_initials' => $request->operator_initials,
            'search'            => $request->search,
            'qr_raw'            => $request->qr_raw,
            'entry_method'      => $request->entry_method,
            'view_mode'         => $request->view_mode,
            'id'                => $request->id,
        ];

        if ($request->get('view_mode') !== 'verifikasi' && empty($filters['entry_method'])) {
            $filters['entry_method'] = 'manual';
        }

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);

        $cacheKey = "incoming_exports_filters_" . md5(json_encode([$plantId]));
        $items = \Illuminate\Support\Facades\Cache::remember($cacheKey, 1800, function() use ($plantId) {
            $categories = ['Incoming Export', 'Incoming Part', 'INPROSES', 'Inprosess', 'Inprocess', 'SUB ASSY', 'Sub Assy', 'Plating', 'PLATING'];
            $jakartaPlantId = Plant::resolveId('jakarta');
            $karawangPlantId = Plant::resolveId('karawang');
            $plantIds = array_unique(array_filter([$plantId, $jakartaPlantId, $karawangPlantId]));
            return Item::byCategory($categories)->whereIn('plant_id', $plantIds)->orderBy('name')->get();
        });

        $approvalOrder = ['kashift', 'supervisor', 'asst_manager', 'manager'];
        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_exports', $plantInput, [
            'no_dokumen' => 'QC-KRW-F-0213',
            'tgl_terbit' => '01/01/2026',
            'revisi'     => '-',
            'halaman'    => '- / -'
        ]);

        return view('incoming.exports.index', compact('checksheets', 'items', 'approvalOrder', 'docHeader'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $categories = ['Incoming Export', 'Incoming Part', 'INPROSES', 'Inprosess', 'Inprocess', 'SUB ASSY', 'Sub Assy', 'Plating', 'PLATING'];
        $query = Item::byCategory($categories)->orderBy('name');

        $jakartaPlantId = Plant::resolveId('jakarta');
        $karawangPlantId = Plant::resolveId('karawang');
        $currentPlantId = $request->has('plant') ? Plant::resolveId($request->query('plant')) : $user->plant_id;

        $plantIds = array_unique(array_filter([$currentPlantId, $jakartaPlantId, $karawangPlantId]));
        $query->whereIn('plant_id', $plantIds);

        $items = $query->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('incoming.exports.create', compact('items', 'defaultDate', 'defaultShift'));
    }

    public function store(StoreIncomingExportRequest $request)
    {
        try {
            $result = $this->checksheetService->createChecksheet($request->validated());
            $checksheet = $result['checksheet'] ?? null;
            if ($checksheet) {
                ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Incoming Export baru: {$checksheet->item->name}");
            }
            $message = 'Data Incoming Export berhasil disimpan.';
            $plantInput = $request->get('plant') ?? $request->get('plant_id') ?? auth()->user()->plant_id;
            $plantCode = (is_string($plantInput) && strlen($plantInput) > 30) ? \App\Models\Plant::where('id', $plantInput)->value('code') : (string) $plantInput;
            $plantCode = strtolower($plantCode ?: 'karawang');
            $indexUrl = route('incoming.exports.index', ['plant' => $plantCode]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => $indexUrl
                ]);
            }

            return redirect()->to($indexUrl)->with('success', $message);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $checksheet = IncomingExport::findOrFail($id);
        $categories = ['Incoming Export', 'Incoming Part', 'INPROSES', 'Inprosess', 'Inprocess', 'SUB ASSY', 'Sub Assy', 'Plating', 'PLATING'];
        $jakartaPlantId = Plant::resolveId('jakarta');
        $karawangPlantId = Plant::resolveId('karawang');
        $plantIds = array_unique(array_filter([$checksheet->plant_id, $jakartaPlantId, $karawangPlantId]));

        $items = Item::byCategory($categories)->whereIn('plant_id', $plantIds)->orderBy('name')->get();

        if (request()->ajax()) {
            return view('incoming.exports.partials.edit_form', compact('checksheet', 'items'));
        }
        return view('incoming.exports.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateIncomingExportRequest $request, $id)
    {
        $this->checksheetService->updateChecksheet($id, $request->validated());
        $checksheet = IncomingExport::find($id);
        ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Incoming Export: {$checksheet->item->name}");
        return redirect()->route('incoming.exports.index', $request->query())->with('success', 'Incoming Export berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $checksheet = IncomingExport::find($id);
        $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
        $this->checksheetService->deleteChecksheet($id);
        ActivityLogger::log('deleted', null, "Menghapus checksheet Incoming Export: {$itemName}");
        return redirect()->route('incoming.exports.index', $request->query())->with('success', 'Incoming Export berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $query = $this->checksheetService->getQuery($filters)->latest();

        if ($request->has('page')) {
            $checksheets = $query->paginate(10)->getCollection();
        } else {
            $checksheets = $query->limit(10)->get();
        }
        $plantCode = strtolower($request->plant ?? auth()->user()->plant->code ?? 'karawang');
        $plantName = Plant::resolveName($request->plant ?? auth()->user()->plant_id);
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';

        $pdf = Pdf::loadView('incoming.exports.pdf', compact('checksheets', 'plantName', 'startDate', 'endDate', 'plantCode'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Incoming_Export_' . date('Ymd_His') . '.pdf');
    }

    public function printView(Request $request)
    {
        $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'entry_method', 'view_mode']);
        $query = $this->checksheetService->getQuery($filters)->latest();
        $checksheets = $query->get();

        $plantFilter = $request->get('plant', auth()->user()->plant_id);
        $plantCode = strtolower($request->plant ?? auth()->user()->plant->code ?? 'karawang');
        $plantName = Plant::resolveName($plantFilter);
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua';
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua';

        $approvalOrder = ['kashift', 'supervisor', 'asst_manager', 'manager'];
        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_exports', $plantFilter, [
            'no_dokumen' => 'QC-KRW-F-0213',
            'tgl_terbit' => '01/01/2026',
            'revisi'     => '-',
            'halaman'    => '- / -'
        ]);

        $selectedItem = null;
        if (!empty($filters['item_id'])) {
            $selectedItem = Item::find($filters['item_id']);
        }

        return view('incoming.exports.print', compact('checksheets', 'plantName', 'startDate', 'endDate', 'plantCode', 'approvalOrder', 'docHeader', 'selectedItem'));
    }
}
