<?php

namespace App\Services;

use App\Models\PlatingChecksheet;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatingChecksheetService extends BaseService
{
    use \App\Traits\ChecksheetServiceTrait;
    protected $googleSheetService;
    protected $notificationService;

    public function __construct(GoogleSheetService $googleSheetService, NotificationService $notificationService)
    {
        $this->googleSheetService = $googleSheetService;
        $this->notificationService = $notificationService;
    }

    public function getQuery(array $filters)
    {
        return $this->buildFilteredQuery($filters);
    }

    public function buildFilteredQuery(array $filters)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = PlatingChecksheet::with(['item', 'platingCabutSplit.cabutRecord.pasangRecord'])->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('plating_checksheets.date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('plating_checksheets.date', '<=', $filters['end_date']);
        }

        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['operator_initials'])) {
            $query->where('plating_checksheets.operator_initials', $filters['operator_initials']);
        }

        if (!empty($filters['customer'])) {
            $customer = $filters['customer'];
            $query->whereHas('item', function ($q) use ($customer) {
                $q->where('customer', $customer);
            });
        }

        if (!empty($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('item', function ($itemQuery) use ($searchTerm) {
                    $itemQuery->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('customer', 'like', "%{$searchTerm}%")
                        ->orWhere('part_number', 'like', "%{$searchTerm}%");
                })->orWhere('operator_initials', 'like', "%{$searchTerm}%");
            });
        }

        if (!empty($filters['qr_raw'])) {
            $qr = trim($filters['qr_raw']);
            $query->where(function ($q) use ($qr) {
                $q->where('plating_checksheets.qrcode', $qr)
                  ->orWhere('plating_checksheets.unique_code_id', $qr)
                  ->orWhere('plating_checksheets.sap_code', $qr)
                  ->orWhere('plating_checksheets.qrcode', 'like', "%{$qr}%");
            });
        }

        if (!empty($filters['entry_method'])) {
            if ($filters['entry_method'] === 'verification') {
                $query->whereNotNull('plating_checksheets.qrcode');
            } elseif ($filters['entry_method'] === 'regular') {
                $query->whereNull('plating_checksheets.qrcode');
            }
        }

        if (!empty($filters['id'])) {
            $query->where($query->getModel()->getTable() . '.id', $filters['id']);
        }

        return $query;
    }

    public function getFilteredChecksheets(array $filters)
    {
        return $this->buildFilteredQuery($filters)->paginate(10)->withQueryString();
    }

    public function createChecksheet(array $data, callable $mapExportRow): array
    {
        DB::beginTransaction();
        try {
            $defects = $this->processDefects($data);

            $checksheet = PlatingChecksheet::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id),
                'item_id' => $data['item_id'],
                'qrcode' => $data['qrcode'] ?? null,
                'part_code' => $data['part_code'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'unique_code_id' => $data['unique_code_id'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'qrcode_verifikasi' => $data['qrcode_verifikasi'] ?? null,
                'date' => $data['date'],
                'shift' => $data['shift'],
                'injection_date' => $data['injection_date'] ?? null,
                'injection_shift' => $data['injection_shift'] ?? null,
                'injection_initials' => $data['injection_initials'] ?? null,
                'plating_date' => $data['plating_date'] ?? null,
                'plating_shift' => $data['plating_shift'] ?? null,
                'line' => $data['line'],
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'] ?? $data['total_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'next_proses' => $data['next_proses'] ?? null,
                'cycle_time' => $data['cycle_time'] ?? null,
                'no_lot' => $data['no_lot'] ?? null,
                'defects' => $defects,
                'standard_cycle_time' => $data['standard_cycle_time'] ?? Item::find($data['item_id'])->standard_cycle_time,
            ]);

            DB::commit();

            $this->clearFilterCache($checksheet->plant_id);

            Log::info('Checksheet Plating berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id
            ]);

            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Plating');
            }

            return [
                'checksheet' => $checksheet,
                'google_sheets_success' => false,
                'error' => null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Plating', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function updateChecksheet(int $id, array $data): PlatingChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = PlatingChecksheet::findOrFail($id);

            $defects = $this->processDefects($data);
            
            $updateData = [
                'item_id' => $data['item_id'],
                'qrcode' => $data['qrcode'] ?? $checksheet->qrcode,
                'part_code' => $data['part_code'] ?? $checksheet->part_code,
                'supplier_id' => $data['supplier_id'] ?? $checksheet->supplier_id,
                'quantity' => $data['quantity'] ?? $checksheet->quantity,
                'unique_code_id' => $data['unique_code_id'] ?? $checksheet->unique_code_id,
                'sap_code' => $data['sap_code'] ?? $checksheet->sap_code,
                'qrcode_verifikasi' => $data['qrcode_verifikasi'] ?? $checksheet->qrcode_verifikasi,
                'date' => $data['date'],
                'shift' => $data['shift'],
                'injection_date' => $data['injection_date'],
                'injection_shift' => $data['injection_shift'],
                'injection_initials' => $data['injection_initials'] ?? $checksheet->injection_initials,
                'plating_date' => $data['plating_date'],
                'plating_shift' => $data['plating_shift'],
                'line' => $data['line'],
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'] ?? $data['total_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'next_proses' => $data['next_proses'] ?? null,
                'cycle_time' => $data['cycle_time'] ?? $checksheet->cycle_time,
                'no_lot' => $data['no_lot'] ?? $checksheet->no_lot,
                'defects' => $defects,
            ];
            
            $checksheet->update($updateData);

            DB::commit();
            $this->clearFilterCache($checksheet->plant_id);
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteChecksheet(int $id): bool
    {
        try {
            $checksheet = PlatingChecksheet::findOrFail($id);
            $plantId = $checksheet->plant_id;
            $res = $checksheet->delete();
            if ($res) {
                $this->clearFilterCache($plantId);
            }
            return $res;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function clearFilterCache($plantId)
    {
        if ($plantId) {
            \Illuminate\Support\Facades\Cache::forget("plating_filter_init_{$plantId}");
            \Illuminate\Support\Facades\Cache::forget("plating_filter_items_{$plantId}");
            \Illuminate\Support\Facades\Cache::forget("plating_filter_cust_{$plantId}");
        }
    }

    public function updateApprovalStatus(int $id, array $data): PlatingChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = PlatingChecksheet::findOrFail($id);

            $this->processFullApprovalUpdate($checksheet, $data);

            $checksheet->save();
            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get daily recap for verification data
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDailyRecap(array $filters)
    {
        $query = PlatingChecksheet::with('item')
            ->whereNotNull('qrcode') // Verification only
            ->select(
                'item_id',
                'shift',
                DB::raw('MIN(total_qty) as packing_size'),
                DB::raw('COUNT(*) as total_packing'),
                DB::raw('SUM(total_qty) as total_qty_sum'),
                DB::raw('SUM(total_qty - total_ng) as total_ok_sum'),
                DB::raw('SUM(total_ng) as total_ng_sum')
            )
            ->groupBy('item_id', 'shift');

        if (isset($filters['plant'])) {
            $query->where('plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        if (empty($filters['start_date']) && empty($filters['end_date'])) {
            if (!empty($filters['date'])) {
                $query->whereDate('date', $filters['date']);
            } else {
                $query->whereDate('date', now()->toDateString());
            }
        }

        if (!empty($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        return $query->get();
    }

    /**
     * Get daily recap per inspector for performance tracking
     */
    public function getInspectorDailyRecap(array $filters)
    {
        $query = PlatingChecksheet::query()
            ->join('items', 'plating_checksheets.item_id', '=', 'items.id')
            ->where(function($q) {
                $q->whereNull('plating_checksheets.qrcode')
                  ->orWhere('plating_checksheets.qrcode', '');
            })
            ->select(
                'plating_checksheets.operator_initials',
                'plating_checksheets.item_id',
                DB::raw('SUM(plating_checksheets.total_qty) as total_qty_sum'),
                DB::raw('SUM(plating_checksheets.cycle_time) as total_act'),
                DB::raw('COALESCE(MAX(plating_checksheets.standard_cycle_time), MAX(items.standard_cycle_time)) as sct'),
                DB::raw('COUNT(*) as total_entries')
            )
            ->groupBy('plating_checksheets.operator_initials', 'plating_checksheets.item_id');

        if (isset($filters['plant'])) {
            $query->where('plating_checksheets.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('plating_checksheets.date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('plating_checksheets.date', '<=', $filters['end_date']);
        }

        if (!empty($filters['shift'])) {
            $query->where('plating_checksheets.shift', $filters['shift']);
        }
        
        $query->with('item')->orderBy('plating_checksheets.operator_initials');

        return $query->get()->map(function($row) {
            $act_min = $row->total_act / 60;
            $sct_min = $row->sct; // In minutes

            // Target = Actual Duration (Min) / Standard Cycle Time (Min)
            $target = $sct_min > 0 ? ($act_min / $sct_min) : 0;
            $row->target = round($target);

            // Plus/Minus = Total Actual Qty - Target
            $row->plus_minus = $row->total_qty_sum - $row->target;
            
            return $row;
        });
    }

    /**
     * Get NG daily recap per item for NG tracking and percentage calculation
     */
    public function getNgDailyRecap(array $filters)
    {
        $baseQuery = PlatingChecksheet::with('item')
            ->where('plant_id', $this->resolvePlantId($filters['plant'] ?? 'karawang'));

        if (!empty($filters['start_date'])) {
            $baseQuery->whereDate('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $baseQuery->whereDate('date', '<=', $filters['end_date']);
        }

        if (empty($filters['start_date']) && empty($filters['end_date'])) {
            if (!empty($filters['date'])) {
                $baseQuery->whereDate('date', $filters['date']);
            } else {
                $baseQuery->whereDate('date', now()->toDateString());
            }
        }

        if (!empty($filters['shift'])) {
            $baseQuery->where('shift', $filters['shift']);
        }

        if (!empty($filters['operator_initials'])) {
            $baseQuery->where('operator_initials', $filters['operator_initials']);
        }

        $allChecksheets = $baseQuery->get();
        $grouped = $allChecksheets->groupBy('item_id');

        $result = collect();

        foreach ($grouped as $itemId => $items) {
            $totalQtySum = $items->sum('total_qty');
            $totalNgSum = $items->sum('total_ng');

            if ($totalNgSum <= 0 || $totalQtySum <= 0) {
                continue;
            }

            $first = $items->first();

            $defectsSummary = [];
            foreach ($items as $c) {
                if ($c->total_ng <= 0) continue;
                $defectsData = is_array($c->defects) ? $c->defects : json_decode($c->defects, true);
                if (is_array($defectsData)) {
                    foreach ($defectsData as $d) {
                        if (is_array($d) && isset($d['type'])) {
                            $type = $d['type'];
                            $qty = (int)($d['qty'] ?? 1);
                            $defectsSummary[$type] = ($defectsSummary[$type] ?? 0) + $qty;
                        } elseif (is_string($d)) {
                            $defectsSummary[$d] = ($defectsSummary[$d] ?? 0) + 1;
                        }
                    }
                }
            }

            $defectsList = collect();
            $maxPct = 0;
            foreach ($defectsSummary as $defectType => $defectQty) {
                $percentage = ($defectQty / $totalQtySum) * 100;
                if ($percentage > $maxPct) {
                    $maxPct = $percentage;
                }
                $defectsList->push((object)[
                    'defect_type' => $defectType,
                    'defect_qty' => $defectQty,
                    'percentage' => round($percentage, 2)
                ]);
            }

            $defectsList = $defectsList->sortByDesc('percentage')->values();

            $result->push((object)[
                'item_id' => $itemId,
                'item' => $first->item,
                'total_qty_sum' => $totalQtySum,
                'total_ng_sum' => $totalNgSum,
                'max_percentage' => round($maxPct, 2),
                'defects' => $defectsList
            ]);
        }

        return $result;
    }
}
