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
        $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::byCategory(['Incoming Export', 'INPROSES', 'SUB ASSY', 'Plating', 'PLATING'])->orderBy('name')->get();

        return view('incoming.exports.index', compact('checksheets', 'items'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $query = Item::byCategory(['Incoming Export', 'INPROSES', 'SUB ASSY', 'Plating', 'PLATING'])->orderBy('name');

        if ($request->has('plant')) {
            $query->where('plant_id', Plant::resolveId($request->query('plant')));
        } else {
            $query->where('plant_id', $user->plant_id);
        }

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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('incoming.exports.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
                ]);
            }

            return redirect()->route('incoming.exports.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
                ->with('success', $message);
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
        $items = Item::byCategory(['Incoming Export', 'INPROSES', 'SUB ASSY'])->orderBy('name')->get();

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
}
