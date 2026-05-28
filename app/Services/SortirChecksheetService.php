<?php

namespace App\Services;

use App\Models\SortirChecksheet;
use App\Models\SubAssyChecksheet;
use App\Models\InProcessChecksheet;
use App\Models\CrossCutChecksheet;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SortirChecksheetService extends BaseService
{
    use \App\Traits\ChecksheetServiceTrait;
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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
    public function getQuery(array $filters)
    {
        return $this->buildFilteredQuery($filters);
    }

    /**
     * Build the filtered query
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(array $filters)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = SortirChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        // Apply plant filter if present
        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('sortir_checksheets.date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('sortir_checksheets.date', '<=', $filters['end_date']);
        }

        if (!empty($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
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
        // Get sum of sorted qty per source (source_type + source_id)
        $sortedQtyBySource = SortirChecksheet::selectRaw('source_type, source_id, SUM(total_qty) as sorted_qty')
            ->groupBy('source_type', 'source_id')
            ->get()
            ->groupBy('source_type')
            ->map(fn($items) => $items->keyBy('source_id')->map(fn($item) => (int) $item->sorted_qty)->toArray())
            ->toArray();

        $plantId = $this->resolvePlantId($filters['plant'] ?? null);
        $shouldFilterByPlant = !empty($plantId);

        // Sub Assy - Include all NG items with any next_proses value
        $querySubAssy = SubAssyChecksheet::where('judgment', 'NG')
            ->whereNotNull('next_proses')
            ->with('item');
        if ($shouldFilterByPlant) {
            $querySubAssy->withoutGlobalScope('plant')->where('plant_id', $plantId);
        }
        $ngSubAssy = $querySubAssy->get()
            ->map(fn($c) => $this->mapNgItemWithQty($c, 'sub_assy', $sortedQtyBySource))
            ->filter(fn($item) => $item['remaining_qty'] > 0);

        // In Process - Include all NG items with any next_proses value
        $queryInProcess = InProcessChecksheet::where('judgment', 'NG')
            ->whereNotNull('next_proses')
            ->with('item');
        if ($shouldFilterByPlant) {
            $queryInProcess->withoutGlobalScope('plant')->where('plant_id', $plantId);
        }
        $ngInProcess = $queryInProcess->get()
            ->map(fn($c) => $this->mapNgItemWithQty($c, 'in_process', $sortedQtyBySource))
            ->filter(fn($item) => $item['remaining_qty'] > 0);

        // Cross Cut - Include all NG items with any next_proses value
        $queryCrossCut = CrossCutChecksheet::where('position_remark_judgment', 'NG')
            ->whereNotNull('next_proses')
            ->with('item');
        if ($shouldFilterByPlant) {
            $queryCrossCut->withoutGlobalScope('plant')->where('plant_id', $plantId);
        }
        $ngCrossCut = $queryCrossCut->get()
            ->map(fn($c) => $this->mapNgItemWithQty($c, 'cross_cut', $sortedQtyBySource))
            ->filter(fn($item) => $item['remaining_qty'] > 0);

        return collect(array_merge(
            $ngSubAssy->toArray(),
            $ngInProcess->toArray(),
            $ngCrossCut->toArray()
        ));
    }

    /**
     * Map NG item with qty tracking information
     * 
     * @param mixed $c
     * @param string $type
     * @param array $sortedQtyBySource
     * @return array
     */
    private function mapNgItemWithQty($c, string $type, array $sortedQtyBySource): array
    {
        $date = $c->date ?? $c->qc_datetime;
        $shift = $c->shift ?? $c->qc_shift;

        // Get total qty from source checksheet
        // For Double Tape, use total_ng as the sortable quantity (only defective pieces)
        $totalQty = ($type === 'double_tape')
            ? (int) ($c->total_ng ?? 0)
            : (int) ($c->total_qty ?? 0);

        // Get sorted qty from sortir checksheets for this source
        $sortedQty = (int) ($sortedQtyBySource[$type][$c->id] ?? 0);

        // Calculate remaining qty
        $remainingQty = max(0, $totalQty - $sortedQty);

        return [
            'item_id' => $c->item_id,
            'item_name' => $c->item->name ?? '-',
            'part_number' => $c->item->part_number ?? '-',
            'sap_code' => $c->item->sap_code ?? '',
            'source_type' => $type,
            'source_id' => $c->id,
            'date' => ($date instanceof \Carbon\Carbon) ? $date->format('d-m-Y') : \Carbon\Carbon::parse($date)->format('d-m-Y'),
            'shift' => $shift,
            'next_proses' => $c->next_proses ?? '',
            'total_qty' => $totalQty,
            'sorted_qty' => $sortedQty,
            'remaining_qty' => $remainingQty,
            'file_path' => $c->item->file_path ?? null,
            'file_paths' => $c->item->file_paths ?? ($c->item->file_path ? [$c->item->file_path] : []),
        ];
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
            $defects = $this->processDefects($data);

            $sortir = SortirChecksheet::create(array_merge($data, [
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id),
                'defects' => $defects
            ]));

            // Check if all qty has been sorted, then close source
            $this->checkAndCloseSource($data['source_type'], $data['source_id']);

            DB::commit();

            Log::info('Checksheet Sortir berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $sortir->id,
                'plant_id' => $sortir->plant_id
            ]);

            // Notifications
            $this->notificationService->notifyApprovalRequest($sortir, 'Sortir');

            return $sortir;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Sortir', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
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
        DB::beginTransaction();
        try {
            $checksheet = SortirChecksheet::findOrFail($id);

            // Process plant if provided (Admin bypass)
            if (isset($data['plant'])) {
                $data['plant_id'] = $this->resolvePlantId($data['plant']);
            }

            // Process defects update
            $defects = $this->processDefects($data);
            $data['defects'] = $defects;

            // Update record
            $checksheet->update($data);

            // Recalculate source closure status
            $this->checkAndCloseSource($checksheet->source_type, $checksheet->source_id);

            DB::commit();

            Log::info('Checksheet Sortir berhasil diperbarui', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id,
                'plant_id' => $checksheet->plant_id
            ]);

            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui checksheet Sortir', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete sortir checksheet
     * 
     * @param int $id
     * @return bool
     */
    public function deleteChecksheet(int $id): bool
    {
        DB::beginTransaction();
        try {
            $checksheet = SortirChecksheet::findOrFail($id);
            $res = $checksheet->delete();

            DB::commit();

            Log::info('Checksheet Sortir berhasil dihapus', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id
            ]);

            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus checksheet Sortir', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check if all qty has been sorted and close source if complete
     * 
     * @param string $sourceType
     * @param int $sourceId
     * @return void
     */
    private function checkAndCloseSource(string $sourceType, int $sourceId): void
    {
        $source = null;

        if ($sourceType === 'sub_assy') {
            $source = SubAssyChecksheet::find($sourceId);
        } elseif ($sourceType === 'in_process') {
            $source = InProcessChecksheet::find($sourceId);
        } elseif ($sourceType === 'cross_cut') {
            $source = CrossCutChecksheet::find($sourceId);
        } elseif ($sourceType === 'double_tape') {
            $source = DoubleTapeChecksheet::find($sourceId);
        }

        if (!$source) {
            return;
        }

        // Get total qty from source
        $totalQty = (int) ($source->total_qty ?? 0);

        // Get total sorted qty for this source
        $sortedQty = (int) SortirChecksheet::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->sum('total_qty');

        // Only close if all qty has been sorted
        if ($sortedQty >= $totalQty) {
            $statusMsg = '[SORTIR_CLOSED]';
            $remarksField = ($sourceType === 'cross_cut') ? 'keterangan' : 'remarks';

            if (!str_contains($source->$remarksField ?? '', $statusMsg)) {
                $source->$remarksField = trim(($source->$remarksField ?? '') . ' ' . $statusMsg);
            }

            // Clear next_proses field since sortir is now complete
            $source->next_proses = null;
            $source->save();
        }
    }



}
