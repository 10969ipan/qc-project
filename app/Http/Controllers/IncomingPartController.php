<?php

namespace App\Http\Controllers;

use App\Models\IncomingPart;
use App\Models\IncomingPartArrival;
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

    protected function getApprovalMapping($type)
    {
        $mapping = [
            'kashift' => ['field' => 'kashift_qc', 'time' => 'kashift_approved_at', 'label' => 'Kashift QC / Kepala Regu'],
            'supervisor' => ['field' => 'supervisor_qc', 'time' => 'supervisor_approved_at', 'label' => 'Supervisor QC'],
        ];

        return $mapping[$type] ?? null;
    }

    protected function applySequentialApprovalFilter($query, $type)
    {
        $sequence = [
            'kashift' => 'kashift_qc',
            'supervisor' => 'supervisor_qc',
        ];

        $keys = array_keys($sequence);
        $currentIndex = array_search($type, $keys);

        if ($currentIndex > 0) {
            for ($i = $currentIndex - 1; $i >= 0; $i--) {
                $prevField = $sequence[$keys[$i]];
                $query->whereNotNull($prevField)->where($prevField, '!=', 'REJECTED');
            }
        }
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
        $plantFilter = $request->get('plant', auth()->user()->plant_id);
        $plantId = Plant::resolveId($plantFilter);

        $filters = [
            'plant'             => $plantFilter,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'approval_status'   => $request->approval_status,
            'item_id'           => $request->item_id,
            'operator_initials' => $request->operator_initials,
            'customer'          => $request->customer,
            'id'                => $request->id,
            'search'            => $request->search,
            'qr_raw'            => $request->qr_raw,
            'entry_method'      => $request->entry_method,
            'shift'             => $request->shift,
            'view_mode'         => $request->view_mode,
        ];

        if ($request->get('view_mode') !== 'verifikasi' && empty($filters['entry_method'])) {
            $filters['entry_method'] = 'manual';
        }

        $plantInput = $request->get('plant', auth()->user()->plant_id);
        $plantCodeVal = (is_string($plantInput) && strlen($plantInput) > 30) ? \App\Models\Plant::where('id', $plantInput)->value('code') : (string) $plantInput;
        $isJakarta = strtolower($plantCodeVal ?: '') === 'jakarta';
        $categories = $isJakarta ? ['Incoming Part', 'INPROSES', 'Inprosess', 'Inprocess'] : 'Incoming Part';

        $checksheets = $this->checksheetService->getFilteredChecksheets($filters);
        $items = Item::byCategory($categories)->orderBy('name')->get();

        $customers = Item::whereIn('id', function ($query) use ($plantId) {
            $query->select('item_id')->from('incoming_parts')->where('plant_id', $plantId);
        })->whereNotNull('customer')->distinct()->pluck('customer')->sort();

        $initialsQuery = IncomingPart::where('plant_id', $plantId)
            ->whereNotNull('operator_initials');

        if (!empty($filters['start_date'])) {
            $initialsQuery->whereDate('date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $initialsQuery->whereDate('date', '<=', $filters['end_date']);
        }

        $initials = $initialsQuery->distinct()->pluck('operator_initials')->sort();

        $openArrivals = \App\Models\IncomingPartArrival::with('item')
            ->where('plant_id', $plantId)
            ->where('status', 'OPEN')
            ->where('qty_sisa', '>', 0)
            ->where('created_at', '>=', '2026-08-21 00:43:00')
            ->orderBy('tanggal_datang', 'asc')
            ->orderBy('shift_datang', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('incoming.parts.index', compact('checksheets', 'items', 'customers', 'initials', 'openArrivals'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $plantInput = $request->get('plant', $user->plant_id);
        $plantId = Plant::resolveId($plantInput);
        $plantCodeVal = (is_string($plantInput) && strlen($plantInput) > 30) ? \App\Models\Plant::where('id', $plantInput)->value('code') : (string) $plantInput;
        $isJakarta = strtolower($plantCodeVal ?: '') === 'jakarta';
        $categories = $isJakarta ? ['Incoming Part', 'INPROSES', 'Inprosess', 'Inprocess'] : 'Incoming Part';

        $query = Item::byCategory($categories)->orderBy('name')->where('plant_id', $plantId);

        $items = $query->get();
        $now = now();
        $defaultDate = ShiftHelper::getProductionDate($now);
        $defaultShift = ShiftHelper::getShift($now);
        $recentArrivals = [];

        $openArrivals = \App\Models\IncomingPartArrival::with('item')
            ->where('plant_id', $plantId)
            ->where('status', 'OPEN')
            ->where('qty_sisa', '>', 0)
            ->where('created_at', '>=', '2026-08-21 00:43:00')
            ->orderBy('tanggal_datang', 'asc')
            ->orderBy('shift_datang', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('incoming.parts.create', compact('items', 'defaultDate', 'defaultShift', 'recentArrivals', 'openArrivals'));
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
            $plantInput = $request->get('plant') ?? $request->get('plant_id') ?? auth()->user()->plant_id;
            $plantCode = (is_string($plantInput) && strlen($plantInput) > 30) ? \App\Models\Plant::where('id', $plantInput)->value('code') : (string) $plantInput;
            $plantCode = strtolower($plantCode ?: 'karawang');
            $indexUrl = route('incoming.parts.index', ['plant' => $plantCode]);

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

    public function storeArrival(Request $request)
    {
        $validated = $request->validate([
            'plant_id'       => 'nullable|string',
            'item_id'        => 'required|exists:items,id',
            'tanggal_datang' => 'required|date',
            'shift_datang'   => 'required|string',
            'qty_datang'     => 'required|integer|min:1',
        ]);

        try {
            $arrival = $this->checksheetService->createArrival($validated);
            ActivityLogger::log('created', $arrival, "Menambahkan Stok Kedatangan Awal Incoming Part: " . ($arrival->item ? $arrival->item->name : '-'));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stok Kedatangan Awal berhasil disimpan.',
                    'arrival' => $arrival->load('item'),
                ]);
            }

            return redirect()->back()->with('success', 'Stok Kedatangan Awal berhasil disimpan.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan stok kedatangan: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan stok kedatangan: ' . $e->getMessage());
        }
    }

    public function updateArrival(Request $request, $id)
    {
        $arrival = IncomingPartArrival::findOrFail($id);

        $validated = $request->validate([
            'qty_datang'     => 'required|integer|min:1',
            'qty_sisa'       => 'required|integer|min:0|lte:qty_datang',
            'tanggal_datang' => 'nullable|date',
            'shift_datang'   => 'nullable|string',
        ], [
            'qty_sisa.lte' => 'Qty Sisa Stok tidak boleh melebihi Qty Datang Awal.',
        ]);

        try {
            $qtyBefore = $arrival->qty_sisa;

            $inputQtyDatang = (int)$validated['qty_datang'];
            $inputQtySisa   = (int)$validated['qty_sisa'];

            // Hitung akumulasi Total Check MANUAL yang mengurangi lot ini
            $totalChecked = (int)IncomingPart::where('arrival_id', $arrival->id)
                ->where(function ($q) {
                    $q->whereNull('scan_method')->orWhere('scan_method', 'manual');
                })
                ->sum('total_check');

            if ($inputQtySisa !== $qtyBefore) {
                // Jika user mengubah Qty Sisa Stok, sesuaikan Qty Datang Awal agar sinkron dengan Total Check
                $arrival->qty_sisa = $inputQtySisa;
                $arrival->qty_datang = $inputQtySisa + $totalChecked;
            } else {
                // Jika user mengubah Qty Datang Awal, sesuaikan Qty Sisa Stok agar sinkron dengan Total Check
                $arrival->qty_datang = $inputQtyDatang;
                $arrival->qty_sisa = max(0, $inputQtyDatang - $totalChecked);
            }

            if (!empty($validated['tanggal_datang'])) {
                $arrival->tanggal_datang = $validated['tanggal_datang'];
            }
            if (!empty($validated['shift_datang'])) {
                $arrival->shift_datang = $validated['shift_datang'];
            }
            $arrival->status = ($arrival->qty_sisa <= 0) ? 'COMPLETED' : 'OPEN';
            $arrival->save();

            $qtyAfter = $arrival->qty_sisa;
            $qtyChange = $qtyAfter - $qtyBefore;

            // Sync latest checksheet's qty_balance_sisa if linked
            $latestChecksheet = IncomingPart::where('arrival_id', $arrival->id)->latest('id')->first();
            if ($latestChecksheet) {
                $latestChecksheet->update(['qty_balance_sisa' => $arrival->qty_sisa]);
            }

            ActivityLogger::log('updated', $arrival, "Mengubah Data Kedatangan Awal Incoming Part: " . ($arrival->item ? $arrival->item->name : '-'));
            \App\Models\IncomingPartArrivalLog::record(
                $arrival->load('item'),
                'UPDATE',
                $qtyBefore,
                $qtyChange,
                $qtyAfter,
                "Update manual stok kedatangan (Qty Awal: {$arrival->qty_datang} pcs, Qty Sisa: {$arrival->qty_sisa} pcs)"
            );

            return response()->json([
                'success' => true,
                'message' => 'Data Kedatangan Awal berhasil diperbarui.',
                'arrival' => $arrival->load('item'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengedit stok kedatangan: ' . $e->getMessage()
            ], 422);
        }
    }

    public function destroyArrival($id)
    {
        try {
            $arrival = IncomingPartArrival::findOrFail($id);
            $itemName = $arrival->item ? $arrival->item->name : '-';

            // Cegah hapus jika sudah ada riwayat checksheet QC tersimpan
            $checksheetCount = IncomingPart::where('arrival_id', $arrival->id)->count();
            if ($checksheetCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok kedatangan tidak dapat dihapus karena sudah digunakan oleh {$checksheetCount} data checksheet QC yang tersimpan."
                ], 422);
            }

            $qtyBefore = $arrival->qty_sisa;
            \App\Models\IncomingPartArrivalLog::record(
                $arrival->load('item'),
                'DELETE',
                $qtyBefore,
                -$qtyBefore,
                0,
                "Menghapus Stok Kedatangan Awal (Qty Datang: {$arrival->qty_datang} pcs)"
            );

            $arrival->delete();
            ActivityLogger::log('deleted', null, "Menghapus Stok Kedatangan Awal Incoming Part: {$itemName}");

            return response()->json([
                'success' => true,
                'message' => 'Stok Kedatangan Awal berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus stok kedatangan: ' . $e->getMessage()
            ], 422);
        }
    }

    public function getArrivalLogs(Request $request)
    {
        $plantInput = $request->get('plant', auth()->user()->plant_id);
        $plantId = Plant::resolveId($plantInput);

        $query = \App\Models\IncomingPartArrivalLog::query()
            ->where(function ($q) use ($plantId) {
                $q->where('plant_id', $plantId)
                  ->orWhereNull('plant_id');
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('arrival_id')) {
            $query->where('arrival_id', $request->arrival_id);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', strtoupper($request->action_type));
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int)$request->get('per_page', 10);
        $paginated = $query->paginate($perPage);

        $logs = collect($paginated->items())->map(function ($log) {
            return [
                'id'             => $log->id,
                'created_at'     => $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-',
                'user_name'      => $log->user_name ?? 'System',
                'item_name'      => $log->item_name ?? '-',
                'part_number'    => $log->part_number ?? '-',
                'tanggal_datang' => $log->tanggal_datang ? \Carbon\Carbon::parse($log->tanggal_datang)->format('d/m/Y') : '-',
                'shift_datang'   => $log->shift_datang ?? '-',
                'action_type'    => $log->action_type,
                'qty_before'     => number_format($log->qty_before),
                'qty_change_raw' => $log->qty_change,
                'qty_change'     => ($log->qty_change > 0 ? '+' : '') . number_format($log->qty_change),
                'qty_after'      => number_format($log->qty_after),
                'description'    => $log->description ?? '-',
            ];
        });

        return response()->json([
            'success'      => true,
            'logs'         => $logs,
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'from'         => $paginated->firstItem() ?? 0,
            'to'           => $paginated->lastItem() ?? 0,
            'total'        => $paginated->total(),
            'per_page'     => $paginated->perPage(),
        ]);
    }

    public function edit($id)
    {
        $checksheet = IncomingPart::with(['item', 'arrival', 'plant'])->findOrFail($id);
        $plantCodeVal = optional($checksheet->plant)->code;
        $isJakarta = strtolower($plantCodeVal ?: '') === 'jakarta';
        $categories = $isJakarta ? ['Incoming Part', 'INPROSES', 'Inprosess', 'Inprocess'] : 'Incoming Part';
        $items = Item::byCategory($categories)->orderBy('name')->get();

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
            $count = $this->checksheetService->bulkDeleteChecksheets($ids);
            ActivityLogger::log('deleted', null, "Menghapus massal {$count} data Incoming Part");

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data Incoming Part."
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
