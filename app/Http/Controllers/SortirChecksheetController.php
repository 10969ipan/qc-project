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

    public function create(Request $request)
    {
        // Get already processed source IDs to exclude them from dropdown
        $processedSourceIds = SortirChecksheet::select('source_type', 'source_id')
            ->get()
            ->groupBy('source_type')
            ->map(fn($items) => $items->pluck('source_id')->toArray())
            ->toArray();

        $plant = $request->query('plant');
        $isAdminAndHasPlant = auth()->user()->role === 'admin' && $request->has('plant');

        // Helper to apply plant filter
        $applyPlantFilter = function ($query) use ($isAdminAndHasPlant, $plant) {
            if ($isAdminAndHasPlant) {
                // If the model uses HasPlantFilter, we might need withoutGlobalScope if we were using it,
                // but for Admin HasPlantFilter usually bypasses itself or we need to explicitly filter.
                // Assuming Admin usually sees all, so we restrict it here.
                // The models likely have 'plant' column.
                $query->where('plant', $plant);
            }
        };

        // Get NG items from Sub Assy
        $querySubAssy = Checksheet::where('judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['sub_assy'] ?? [])
            ->with('item');
        $applyPlantFilter($querySubAssy);

        $ngItemsSubAssy = $querySubAssy->get()
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
        $queryInProcess = InProcessChecksheet::where('judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['in_process'] ?? [])
            ->with('item');
        $applyPlantFilter($queryInProcess);

        $ngItemsInProcess = $queryInProcess->get()
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
        $queryCrossCut = CrossCutChecksheet::where('position_remark_judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['cross_cut'] ?? [])
            ->with('item');
        $applyPlantFilter($queryCrossCut);

        $ngItemsCrossCut = $queryCrossCut->get()
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
    protected function applyFilters($query, Request $request)
    {
        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('approval_status') && $request->approval_status != '') {
            if ($request->approval_status === 'Pending') {
                $query->where(function ($q) {
                    $q->where('approval_status', 'Pending')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('approval_status')
                                ->whereNull('supervisor_qc')
                                ->where(function ($rej) {
                                    $rej->where('kashift_qc', '!=', 'REJECTED')
                                        ->orWhereNull('kashift_qc');
                                });
                        });
                });
            } elseif ($request->approval_status === 'Approved') {
                $query->where(function ($q) {
                    $q->where('approval_status', 'Approved')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('approval_status')
                                ->whereNotNull('supervisor_qc')
                                ->where('supervisor_qc', '!=', 'REJECTED');
                        });
                });
            } elseif ($request->approval_status === 'Rejected') {
                $query->where(function ($q) {
                    $q->where('approval_status', 'Rejected')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('approval_status')
                                ->where(function ($rej) {
                                    $rej->where('kashift_qc', 'REJECTED')
                                        ->orWhere('supervisor_qc', 'REJECTED')
                                        ->orWhere('asst_manager_qc', 'REJECTED');
                                });
                        });
                });
            }
        }

        if ($request->has('item_id') && $request->item_id != '') {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('item', function ($itemQuery) use ($searchTerm) {
                    $itemQuery->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('part_number', 'like', "%{$searchTerm}%");
                })->orWhere('operator_initials', 'like', "%{$searchTerm}%");
            });
        }
    }

    protected function getFilterParams(Request $request, $ignorePage = false)
    {
        $params = $request->only(['plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'search']);
        if (!$ignorePage && $request->has('page')) {
            $params['page'] = $request->page;
        }
        return $params;
    }
}
