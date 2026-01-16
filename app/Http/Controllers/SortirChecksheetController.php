<?php

namespace App\Http\Controllers;

use App\Models\SortirChecksheet;
use App\Models\Checksheet;
use App\Models\InProcessChecksheet;
use App\Models\CrossCutChecksheet;
use App\Models\Item;
use Illuminate\Http\Request;

class SortirChecksheetController extends Controller
{
    use \App\Traits\HasChecksheetApproval;
    use \App\Traits\HasChecksheetExport;

    protected function getModelClass()
    {
        return \App\Models\SortirChecksheet::class;
    }

    protected function getExportHeaders()
    {
        return ['Date', 'Shift', 'Line', 'Item', 'Part Number', 'Source', 'Total Qty', 'Sampling', 'OK', 'NG', 'Judgment', 'Operator', 'Kashift QC', 'Supervisor QC', 'Asst. Manager QC', 'Manager QC'];
    }

    protected function mapExportRow($c)
    {
        return [
            $c->date instanceof \Carbon\Carbon ? $c->date->format('Y-m-d') : $c->date,
            $c->shift,
            $c->line ?? '-',
            $c->item->name ?? '-',
            $c->item->part_number ?? '-',
            strtoupper(str_replace('_', ' ', $c->source_type)),
            $c->total_qty,
            $c->sampling_qty,
            $c->total_ok,
            $c->total_ng,
            $c->judgment,
            $c->operator_initials ?? '-',
            $c->kashift_qc ?? 'PENDING',
            $c->supervisor_qc ?? 'PENDING',
            $c->asst_manager_qc ?? 'PENDING',
            $c->manager_qc ?? 'PENDING',
        ];
    }

    /**
     * Override approval mapping for Sortir specific columns
     */
    protected function getApprovalMapping($type)
    {
        $mappings = [
            'kashift' => ['field' => 'kashift_qc', 'time' => 'kashift_qc_time', 'label' => 'Kashift QC'],
            'supervisor' => ['field' => 'supervisor_qc', 'time' => 'supervisor_qc_time', 'label' => 'Supervisor QC'],
        ];
        return $mappings[$type] ?? null;
    }
    public function index(Request $request)
    {
        $query = SortirChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        // Admin can switch plants via query parameter, others are locked via HasPlantFilter
        if (auth()->user()->role === 'admin' && $request->has('plant')) {
            $query->withoutGlobalScope('plant')->where('plant', $request->get('plant'));
        }

        // For inspector, we explicitly override the request plant to their own plant for UI consistency
        if (auth()->user()->role === 'inspector') {
            $request->merge(['plant' => auth()->user()->plant]);
        }

        $this->applyFilters($query, $request);

        $checksheets = $query->paginate(10)->withQueryString();
        $items = Item::orderBy('name')->get();

        return view('sortir.index', compact('checksheets', 'items'));
    }

    public function create()
    {
        // Get already processed source IDs to exclude them from dropdown
        $processedSourceIds = SortirChecksheet::select('source_type', 'source_id')
            ->get()
            ->groupBy('source_type')
            ->map(fn($items) => $items->pluck('source_id')->toArray())
            ->toArray();

        // Get NG items from Sub Assy
        $ngItemsSubAssy = Checksheet::where('judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['sub_assy'] ?? [])
            ->with('item')
            ->get()
            ->map(fn($c) => [
                'item_id' => $c->item_id,
                'item_name' => $c->item->name,
                'part_number' => $c->item->part_number ?? '-',
                'sap_code' => $c->item->sap_code ?? '',
                'source_type' => 'sub_assy',
                'source_id' => $c->id,
                'date' => $c->date instanceof \Carbon\Carbon ? $c->date->format('Y-m-d') : $c->date,
                'shift' => $c->shift,
            ])
            ->toArray();

        // Get NG items from In-Process
        $ngItemsInProcess = InProcessChecksheet::where('judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['in_process'] ?? [])
            ->with('item')
            ->get()
            ->map(fn($c) => [
                'item_id' => $c->item_id,
                'item_name' => $c->item->name,
                'part_number' => $c->item->part_number ?? '-',
                'sap_code' => $c->item->sap_code ?? '',
                'source_type' => 'in_process',
                'source_id' => $c->id,
                'date' => $c->date instanceof \Carbon\Carbon ? $c->date->format('Y-m-d') : $c->date,
                'shift' => $c->shift,
            ])
            ->toArray();

        // Get NG items from Cross Cut
        $ngItemsCrossCut = CrossCutChecksheet::where('position_remark_judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['cross_cut'] ?? [])
            ->with('item')
            ->get()
            ->map(fn($c) => [
                'item_id' => $c->item_id,
                'item_name' => $c->item->name ?? '-',
                'part_number' => $c->item->part_number ?? '-',
                'sap_code' => $c->item->sap_code ?? '',
                'source_type' => 'cross_cut',
                'source_id' => $c->id,
                'date' => \Carbon\Carbon::parse($c->qc_datetime)->format('Y-m-d'),
                'shift' => $c->qc_shift,
            ])
            ->toArray();

        // Merge arrays
        $ngItems = collect(array_merge($ngItemsSubAssy, $ngItemsInProcess, $ngItemsCrossCut));

        $now = now();
        $defaultDate = ($now->hour < 7) ? $now->copy()->subDay()->format('Y-m-d') : $now->format('Y-m-d');

        return view('sortir.create', compact('ngItems', 'defaultDate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'source_type' => 'required|in:sub_assy,in_process,cross_cut',
            'source_id' => 'required|integer',
            'date' => 'required|date',
            'shift' => 'required|string',
            'line' => 'nullable|string',
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
            'next_proses' => 'nullable|string',
        ]);

        $defects = [];
        if ($request->filled('defect_types')) {
            foreach ($request->defect_types as $index => $type) {
                if ($type) {
                    $qty = $request->defect_quantities[$index] ?? 1;
                    $defects[] = ['type' => $type, 'qty' => (int) $qty];
                }
            }
        }

        SortirChecksheet::create(array_merge($validated, [
            'plant' => auth()->user()->plant,
            'defects' => json_encode($defects)
        ]));

        // Update Source Checksheet Remarks
        if ($validated['source_type'] === 'sub_assy') {
            $source = Checksheet::find($validated['source_id']);
            if ($source) {
                $statusMsg = '[SORTIR_CLOSED]';
                if (!str_contains($source->remarks ?? '', $statusMsg)) {
                    $source->remarks = trim(($source->remarks ?? '') . ' ' . $statusMsg);
                    $source->save();
                }
                // Stop clearing next_proses as requested
                // $source->next_proses = null; 
            }
        } elseif ($validated['source_type'] === 'in_process') {
            $source = InProcessChecksheet::find($validated['source_id']);
            if ($source) {
                $statusMsg = '[SORTIR_CLOSED]';
                if (!str_contains($source->remarks ?? '', $statusMsg)) {
                    $source->remarks = trim(($source->remarks ?? '') . ' ' . $statusMsg);
                    $source->save();
                }
                // $source->next_proses = null;
            }
        } elseif ($validated['source_type'] === 'cross_cut') {
            $source = CrossCutChecksheet::find($validated['source_id']);
            if ($source) {
                $statusMsg = '[SORTIR_CLOSED]';
                if (!str_contains($source->keterangan ?? '', $statusMsg)) {
                    $source->keterangan = trim(($source->keterangan ?? '') . ' ' . $statusMsg);
                    $source->save();
                }
                // $source->next_proses = null;
            }
        }

        return redirect()->back()->with('success', 'Data Sortir berhasil disimpan.');
    }

    public function edit($id)
    {
        $query = SortirChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $items = Item::orderBy('name')->get();
        return view('sortir.edit', compact('checksheet', 'items'));
    }

    public function update(Request $request, $id)
    {
        $checksheet = SortirChecksheet::findOrFail($id);

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'date' => 'required|date',
            'shift' => 'required|string',
            'line' => 'nullable|string',
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'next_proses' => 'nullable|string',
        ]);

        $checksheet->update($validated);

        return redirect()->route('sortir.index', $this->getFilterParams($request))->with('success', 'Data Sortir berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $query = SortirChecksheet::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        $checksheet = $query->findOrFail($id);

        $checksheet->delete();

        return redirect()->route('sortir.index', $this->getFilterParams($request, true))
            ->with('success', 'Data Sortir berhasil dihapus.');
    }
}
