<?php

namespace App\Http\Controllers;

use App\Models\IncomingPart;
use App\Models\Item;
use App\Services\IncomingPartService;
use App\Http\Requests\StoreIncomingPartRequest;
use App\Http\Requests\UpdateIncomingPartRequest;
use App\Models\Plant;
use App\Helpers\ShiftHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\ActivityLogger;

class IncomingPartController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected $checksheetService;

    public function __construct(IncomingPartService $checksheetService)
    {
        $this->checksheetService = $checksheetService;
    }

    protected function getModelClass()
    {
        return IncomingPart::class;
    }

    protected function getExportHeaders()
    {
        return [
            'No',
            'Tanggal',
            'Shift',
            'Barang',
            'Part No',
            'Total Check',
            'Tanggal Datang',
            'Judgment',
            'Inisial QC',
            'Remarks'
        ];
    }

    protected function mapExportRow($c)
    {
        return [
            $c->id,
            $c->date->format('d/m/Y'),
            $c->shift,
            $c->item->name ?? '-',
            $c->item->part_number ?? '-',
            $c->total_check,
            $c->tanggal_datang->format('d/m/Y'),
            $c->judgment,
            $c->operator_initials,
            $c->remarks ?? '-'
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'entry_method', 'view_mode']);
        if ($request->get('view_mode') !== 'verifikasi' && empty($filters['entry_method'])) {
            $filters['entry_method'] = 'manual';
        }
        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::byCategory('Incoming Part')->orderBy('name')->get();

        return view('incoming.parts.index', compact('checksheets', 'items'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $query = Item::byCategory('Incoming Part')->orderBy('name');

        if ($request->has('plant')) {
            $query->where('plant_id', Plant::resolveId($request->query('plant')));
        } else {
            $query->where('plant_id', $user->plant_id);
        }

        $items = $query->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);

        return view('incoming.parts.create', compact('items', 'defaultDate', 'defaultShift'));
    }

    public function store(StoreIncomingPartRequest $request)
    {
        try {
            $result = $this->checksheetService->createChecksheet($request->validated());
            $checksheet = $result['checksheet'] ?? null;
            if ($checksheet) {
                ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Incoming Part baru: {$checksheet->item->name}");
            }
            $message = 'Data Incoming Part berhasil disimpan.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('incoming.parts.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
                ]);
            }

            return redirect()->route('incoming.parts.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
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
        $checksheet = IncomingPart::with(['item', 'arrival'])->findOrFail($id);
        $items = Item::byCategory('Incoming Part')->orderBy('name')->get();

        if (request()->ajax()) {
            return view('incoming.parts.partials.edit_form', compact('checksheet', 'items'));
        }
        return view('incoming.parts.edit', compact('checksheet', 'items'));
    }

    public function update(UpdateIncomingPartRequest $request, $id)
    {
        $this->checksheetService->updateChecksheet($id, $request->validated());
        $checksheet = IncomingPart::find($id);
        ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Incoming Part: {$checksheet->item->name}");
        return redirect()->route('incoming.parts.index', $request->query())->with('success', 'Incoming Part berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $checksheet = IncomingPart::find($id);
        $itemName = $checksheet ? $checksheet->item->name : 'Unknown';
        $this->checksheetService->deleteChecksheet($id);
        ActivityLogger::log('deleted', null, "Menghapus checksheet Incoming Part: {$itemName}");
        return redirect()->route('incoming.parts.index', $request->query())->with('success', 'Incoming Part berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.'], 422);
        }

        try {
            foreach ($ids as $id) {
                $this->checksheetService->deleteChecksheet($id);
            }
            ActivityLogger::log('deleted', null, "Menghapus massal " . count($ids) . " data Incoming Part");

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus ' . count($ids) . ' data Incoming Part.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getArrivals(Request $request)
    {
        $itemId = $request->query('item_id');
        if (!$itemId) {
            return response()->json([]);
        }

        $arrivals = $this->checksheetService->getOutstandingArrivals($itemId);
        return response()->json($arrivals);
    }

    public function checkFirstTimeArrival(Request $request)
    {
        $itemId = $request->query('item_id');
        if (!$itemId) {
            return response()->json(['is_first_time' => true]);
        }

        $isFirstTime = $this->checksheetService->isFirstTimeArrival($itemId);
        return response()->json(['is_first_time' => $isFirstTime]);
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

        $pdf = Pdf::loadView('incoming.parts.pdf', compact('checksheets', 'plantName', 'startDate', 'endDate', 'plantCode'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Incoming_Part_' . date('Ymd_His') . '.pdf');
    }

    public function printView(Request $request)
    {
        $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'entry_method', 'view_mode']);
        if ($request->get('view_mode') !== 'verifikasi' && empty($filters['entry_method'])) {
            $filters['entry_method'] = 'manual';
        }
        $query = $this->checksheetService->buildFilteredQuery($filters)->latest();

        if ($request->has('page')) {
            $checksheets = $query->paginate(10)->getCollection();
        } else {
            $checksheets = $query->limit(50)->get();
        }

        $plantCode = strtolower($request->plant ?? auth()->user()->plant->code ?? 'karawang');
        $plantName = Plant::resolveName($request->plant ?? auth()->user()->plant_id);
        $startDate = !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') : 'Semua';
        $endDate   = !empty($filters['end_date'])   ? \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y')   : 'Semua';

        return view('incoming.parts.print', compact('checksheets', 'plantName', 'plantCode', 'startDate', 'endDate'));
    }
}
