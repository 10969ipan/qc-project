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
use App\Helpers\ActivityLogger;

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
            $c->tanggal_datang ? $c->tanggal_datang->format('d/m/Y') : '-',
            $c->date ? $c->date->format('d/m/Y') : '-',
            $c->lot_batch_number,
            $c->quantity,
            $c->sampling_size_pcs,
            $c->check_dimensi ?? '-',
            $c->judgment,
            $c->operator_initials
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search', 'entry_method', 'view_mode']);
        if ($request->get('view_mode') !== 'verifikasi' && empty($filters['entry_method'])) {
            $filters['entry_method'] = 'manual';
        }
        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::byCategory('Incoming Sub-Part')->orderBy('name')->get();
        $partDimensionStandards = $this->getSubPartDimensionStandards($items);

        return view('incoming.sub_parts.index', compact('checksheets', 'items', 'partDimensionStandards'));
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
        $partDimensionStandards = json_encode($this->getSubPartDimensionStandards($items));

        return view('incoming.sub_parts.create', compact('items', 'defaultDate', 'defaultShift', 'partDimensionStandards'));
    }

    private function getSubPartDimensionStandards($items): array
    {
        $partDimensionStandards = [];
        foreach ($items as $item) {
            $pNum = str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', str_replace(["\xe2\x80\x92", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"], '-', $item->part_number ?? ''));
            $pNum = strtoupper($pNum);
            if ($pNum !== '') {
                $stds = is_array($item->dimension_standards)
                    ? $item->dimension_standards
                    : (json_decode($item->dimension_standards, true) ?? []);
                
                $itemStds = [];
                foreach ($stds as $idx => $std) {
                    $ptKey = isset($std['point']) ? (string)$std['point'] : (string)($idx + 1);
                    $itemStds[$ptKey] = $std;
                }
                $partDimensionStandards[$pNum] = $itemStds;
            }
        }
        return $partDimensionStandards;
    }

    public function store(StoreIncomingSubPartRequest $request)
    {
        try {
            $result = $this->checksheetService->createChecksheet($request->validated());
            $checksheet = $result['checksheet'] ?? null;
            if ($checksheet) {
                ActivityLogger::log('created', $checksheet, "Menambahkan checksheet Incoming Sub-Part baru: {$checksheet->item->name}");
            }
            $message = 'Data Incoming Sub-Part berhasil disimpan.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'index_url' => route('incoming.sub_parts.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
                ]);
            }

            return redirect()->route('incoming.sub_parts.index', ['plant' => $request->get('plant', auth()->user()->plant_id)])
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
        $checksheet = IncomingSubPart::find($id);
        ActivityLogger::log('updated', $checksheet, "Memperbarui checksheet Incoming Sub-Part: {$checksheet->item->name}");
        return redirect()->route('incoming.sub_parts.index', $request->query())->with('success', 'Incoming Sub-Part berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $checksheet = IncomingSubPart::find($id);
        $itemName = $checksheet ? ($checksheet->item->name ?? 'Unknown') : 'Unknown';
        $this->checksheetService->deleteChecksheet($id);
        ActivityLogger::log('deleted', null, "Menghapus checksheet Incoming Sub-Part: {$itemName}");

        $preservationKeys = ['page', 'plant', 'start_date', 'end_date', 'approval_status', 'search', 'item_id', 'entry_method', 'view_mode'];
        $redirectParams = $request->only($preservationKeys);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Incoming Sub-Part berhasil dihapus.',
                'redirect' => route('incoming.sub_parts.index', $redirectParams)
            ]);
        }

        return redirect()->route('incoming.sub_parts.index', $redirectParams)->with('success', 'Incoming Sub-Part berhasil dihapus.');
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

        $pdf = Pdf::loadView('incoming.sub_parts.pdf', compact('checksheets', 'plantName', 'startDate', 'endDate', 'plantCode'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Incoming_SubPart_' . date('Ymd_His') . '.pdf');
    }

    public function printView(Request $request)
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

        return view('incoming.sub_parts.print', compact('checksheets', 'plantName', 'startDate', 'endDate', 'plantCode'));
    }
}
