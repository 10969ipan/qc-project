<?php

namespace App\Services;

use App\Models\SortirChecksheet;
use App\Models\SubAssyChecksheet;
use App\Models\InProcessChecksheet;
use App\Models\CrossCutChecksheet;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class SortirChecksheetService extends BaseService
{
    /**
     * Get filtered sortir checksheets with pagination
     * 
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredChecksheets(array $filters)
    {
        return $this->buildFilteredQuery($filters)->paginate(10)->withQueryString();
    }

    /**
     * Build the filtered query
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(array $filters)
    {
        $query = SortirChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        $user = auth()->user();
        $isSpvJakarta = ($user->role === 'supervisor' || $user->role === 'supervisor_plating') && $user->plant === 'jakarta';

        if (($user->role === 'admin' || $isSpvJakarta) && isset($filters['plant'])) {
            $query->withoutGlobalScope('plant')->where('plant', $filters['plant']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('item', function ($itemQuery) use ($searchTerm) {
                    $itemQuery->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('part_number', 'like', "%{$searchTerm}%");
                })->orWhere('operator_initials', 'like', "%{$searchTerm}%");
            });
        }

        return $query;
    }

    /**
     * Get NG items available for sortir
     * 
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableNgItems(array $filters = [])
    {
        $processedSourceIds = SortirChecksheet::select('source_type', 'source_id')
            ->get()
            ->groupBy('source_type')
            ->map(fn($items) => $items->pluck('source_id')->toArray())
            ->toArray();

        $plant = $filters['plant'] ?? null;
        $shouldFilterByPlant = !empty($plant);

        // Sub Assy
        $querySubAssy = SubAssyChecksheet::where('judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['sub_assy'] ?? [])
            ->with('item');
        if ($shouldFilterByPlant)
            $querySubAssy->withoutGlobalScope('plant')->where('plant', $plant);

        $ngSubAssy = $querySubAssy->get()->map(fn($c) => $this->mapNgItem($c, 'sub_assy'));

        // In Process
        $queryInProcess = InProcessChecksheet::where('judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['in_process'] ?? [])
            ->with('item');
        if ($shouldFilterByPlant)
            $queryInProcess->withoutGlobalScope('plant')->where('plant', $plant);

        $ngInProcess = $queryInProcess->get()->map(fn($c) => $this->mapNgItem($c, 'in_process'));

        // Cross Cut
        $queryCrossCut = CrossCutChecksheet::where('position_remark_judgment', 'NG')
            ->whereNotIn('id', $processedSourceIds['cross_cut'] ?? [])
            ->with('item');
        if ($shouldFilterByPlant)
            $queryCrossCut->withoutGlobalScope('plant')->where('plant', $plant);

        $ngCrossCut = $queryCrossCut->get()->map(fn($c) => $this->mapNgItem($c, 'cross_cut'));

        return collect(array_merge($ngSubAssy->toArray(), $ngInProcess->toArray(), $ngCrossCut->toArray()));
    }

    /**
     * Create sortir checksheet
     * 
     * @param array $data
     * @return SortirChecksheet
     */
    public function createSortirChecksheet(array $data): SortirChecksheet
    {
        DB::beginTransaction();
        try {
            $defects = [];
            if (!empty($data['defect_types'])) {
                foreach ($data['defect_types'] as $index => $type) {
                    if ($type) {
                        $qty = $data['defect_quantities'][$index] ?? 1;
                        $defects[] = ['type' => $type, 'qty' => (int) $qty];
                    }
                }
            }

            $sortir = SortirChecksheet::create(array_merge($data, [
                'plant' => auth()->user()->plant,
                'defects' => json_encode($defects)
            ]));

            $this->closeSource($data['source_type'], $data['source_id']);

            DB::commit();
            return $sortir;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update sortir checksheet
     * 
     * @param int $id
     * @param array $data
     * @return SortirChecksheet
     */
    public function updateChecksheet(int $id, array $data): SortirChecksheet
    {
        $checksheet = SortirChecksheet::findOrFail($id);
        $checksheet->update($data);
        return $checksheet;
    }

    /**
     * Delete sortir checksheet
     * 
     * @param int $id
     * @return bool
     */
    public function deleteChecksheet(int $id): bool
    {
        return SortirChecksheet::findOrFail($id)->delete();
    }

    /**
     * Mark source as closed for sortir
     * 
     * @param string $sourceType
     * @param int $sourceId
     * @return void
     */
    private function closeSource(string $sourceType, int $sourceId): void
    {
        $statusMsg = '[SORTIR_CLOSED]';
        $source = null;

        if ($sourceType === 'sub_assy') {
            $source = SubAssyChecksheet::find($sourceId);
        } elseif ($sourceType === 'in_process') {
            $source = InProcessChecksheet::find($sourceId);
        } elseif ($sourceType === 'cross_cut') {
            $source = CrossCutChecksheet::find($sourceId);
        }

        if ($source) {
            $remarksField = ($sourceType === 'cross_cut') ? 'keterangan' : 'remarks';
            if (!str_contains($source->$remarksField ?? '', $statusMsg)) {
                $source->$remarksField = trim(($source->$remarksField ?? '') . ' ' . $statusMsg);
                $source->save();
            }
        }
    }

    /**
     * Map NG item to uniform structure
     * 
     * @param mixed $c
     * @param string $type
     * @return array
     */
    private function mapNgItem($c, string $type): array
    {
        $date = $c->date ?? $c->qc_datetime;
        $shift = $c->shift ?? $c->qc_shift;

        return [
            'item_id' => $c->item_id,
            'item_name' => $c->item->name ?? '-',
            'part_number' => $c->item->part_number ?? '-',
            'sap_code' => $c->item->sap_code ?? '',
            'source_type' => $type,
            'source_id' => $c->id,
            'date' => ($date instanceof \Carbon\Carbon) ? $date->format('Y-m-d') : \Carbon\Carbon::parse($date)->format('Y-m-d'),
            'shift' => $shift,
        ];
    }

    /**
     * Apply approval status filter
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return void
     */
    private function applyApprovalStatusFilter($query, string $status): void
    {
        if ($status === 'Pending') {
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
        } elseif ($status === 'Approved') {
            $query->where(function ($q) {
                $q->where('approval_status', 'Approved')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('approval_status')
                            ->whereNotNull('supervisor_qc')
                            ->where('supervisor_qc', '!=', 'REJECTED');
                    });
            });
        } elseif ($status === 'Rejected') {
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
}
