<?php

namespace App\Services;

use App\Models\DoubleTapeChecksheet;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoubleTapeChecksheetService extends BaseService
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
        $query = DoubleTapeChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('double_tape_checksheets.date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('double_tape_checksheets.date', '<=', $filters['end_date']);
        }

        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['operator_initials'])) {
            $query->where('double_tape_checksheets.operator_initials', $filters['operator_initials']);
        }

        if (!empty($filters['customer'])) {
            $customer = $filters['customer'];
            $query->whereHas('item', function ($q) use ($customer) {
                $q->where('customer', $customer);
            });
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

        if (!empty($filters['check_type']) && is_array($filters['check_type'])) {
            $query->whereIn('check_type', $filters['check_type']);
        }

        if (!empty($filters['qr_raw'])) {
            $query->where('double_tape_checksheets.qrcode', 'like', "%{$filters['qr_raw']}%");
        }

        // Entry Method filter (Verification vs Regular)
        if (!empty($filters['entry_method'])) {
            if ($filters['entry_method'] === 'verification' || $filters['entry_method'] === 'qr') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNotNull('double_tape_checksheets.qrcode')
                            ->where('double_tape_checksheets.qrcode', '!=', '');
                    })->orWhere(function ($sub) {
                        $sub->whereNotNull('double_tape_checksheets.unique_code_id')
                            ->where('double_tape_checksheets.unique_code_id', '!=', '');
                    });
                });
            } elseif ($filters['entry_method'] === 'regular' || $filters['entry_method'] === 'manual') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('double_tape_checksheets.qrcode')
                            ->orWhere('double_tape_checksheets.qrcode', '');
                    })->where(function ($sub) {
                        $sub->whereNull('double_tape_checksheets.unique_code_id')
                            ->orWhere('double_tape_checksheets.unique_code_id', '');
                    });
                });
            }
        }

        if (!empty($filters['shift'])) {
            $query->where('double_tape_checksheets.shift', $filters['shift']);
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

            $checksheet = DoubleTapeChecksheet::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id),
                'item_id' => $data['item_id'],
                'qrcode' => $data['qrcode'] ?? null,
                'part_code' => $data['part_code'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'unique_code_id' => $data['unique_code_id'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'date' => $data['date'],
                'shift' => $data['shift'],
                'injection_date' => $data['injection_date'] ?? null,
                'injection_shift' => $data['injection_shift'] ?? null,
                'injection_initials' => $data['injection_initials'] ?? null,
                'plating_date' => $data['plating_date'] ?? null,
                'plating_shift' => $data['plating_shift'] ?? null,
                'plating_initials' => $data['plating_initials'] ?? null,
                'check_type' => $data['check_type'] ?? 'sampling',
                // No 'line' field
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'next_proses' => $data['next_proses'] ?? null,
                'cycle_time' => $data['cycle_time'] ?? null,
                'defects' => $defects,
            ]);

            DB::commit();

            Log::info('Checksheet Double Tape berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id
            ]);

            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Double Tape');
            }

            return [
                'checksheet' => $checksheet,
                'google_sheets_success' => false,
                'error' => null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Double Tape', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function updateChecksheet(int $id, array $data): DoubleTapeChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = DoubleTapeChecksheet::findOrFail($id);

            $updateData = [
                'item_id' => $data['item_id'],
                'qrcode' => $data['qrcode'] ?? $checksheet->qrcode,
                'part_code' => $data['part_code'] ?? $checksheet->part_code,
                'supplier_id' => $data['supplier_id'] ?? $checksheet->supplier_id,
                'quantity' => $data['quantity'] ?? $checksheet->quantity,
                'unique_code_id' => $data['unique_code_id'] ?? $checksheet->unique_code_id,
                'sap_code' => $data['sap_code'] ?? $checksheet->sap_code,
                'date' => $data['date'],
                'shift' => $data['shift'],
                'injection_date' => array_key_exists('injection_date', $data) ? $data['injection_date'] : $checksheet->injection_date,
                'injection_shift' => array_key_exists('injection_shift', $data) ? $data['injection_shift'] : $checksheet->injection_shift,
                'injection_initials' => array_key_exists('injection_initials', $data) ? $data['injection_initials'] : $checksheet->injection_initials,
                'plating_date' => array_key_exists('plating_date', $data) ? $data['plating_date'] : $checksheet->plating_date,
                'plating_shift' => array_key_exists('plating_shift', $data) ? $data['plating_shift'] : $checksheet->plating_shift,
                'plating_initials' => array_key_exists('plating_initials', $data) ? $data['plating_initials'] : $checksheet->plating_initials,
                'check_type' => $data['check_type'] ?? 'sampling',
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'next_proses' => $data['next_proses'] ?? null,
                'cycle_time' => $data['cycle_time'] ?? $checksheet->cycle_time,
            ];

            $checksheet->update($updateData);

            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteChecksheet(int $id): bool
    {
        try {
            $checksheet = DoubleTapeChecksheet::findOrFail($id);
            return $checksheet->delete();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateApprovalStatus(int $id, array $data): DoubleTapeChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = DoubleTapeChecksheet::findOrFail($id);

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
        $query = DoubleTapeChecksheet::with('item')
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

        // Filter HANYA data verifikasi scan QR
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('double_tape_checksheets.qrcode')
                    ->where('double_tape_checksheets.qrcode', '!=', '');
            })->orWhere(function ($sub) {
                $sub->whereNotNull('double_tape_checksheets.unique_code_id')
                    ->where('double_tape_checksheets.unique_code_id', '!=', '');
            });
        });

        return $query->get();
    }

    /**
     * Get daily recap per inspector for performance tracking
     */
    public function getInspectorDailyRecap(array $filters)
    {
        $query = DoubleTapeChecksheet::query()
            ->join('items', 'double_tape_checksheets.item_id', '=', 'items.id')
            ->select(
                'double_tape_checksheets.operator_initials',
                'double_tape_checksheets.item_id',
                DB::raw('SUM(double_tape_checksheets.total_qty) as total_qty_sum'),
                DB::raw('SUM(double_tape_checksheets.total_ng) as total_ng_sum'),
                DB::raw('SUM(double_tape_checksheets.cycle_time) as total_act'),
                DB::raw('MAX(items.standard_cycle_time) as sct'),
                DB::raw('COUNT(*) as total_entries')
            )
            ->groupBy('double_tape_checksheets.operator_initials', 'double_tape_checksheets.item_id');

        // Filter HANYA data input manual
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNull('double_tape_checksheets.qrcode')
                    ->orWhere('double_tape_checksheets.qrcode', '');
            })->where(function ($sub) {
                $sub->whereNull('double_tape_checksheets.unique_code_id')
                    ->orWhere('double_tape_checksheets.unique_code_id', '');
            });
        });

        if (isset($filters['plant'])) {
            $query->where('double_tape_checksheets.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('double_tape_checksheets.date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('double_tape_checksheets.date', '<=', $filters['end_date']);
        }

        if (!empty($filters['shift'])) {
            $query->where('double_tape_checksheets.shift', $filters['shift']);
        }

        if (!empty($filters['operator_initials'])) {
            $query->where('double_tape_checksheets.operator_initials', $filters['operator_initials']);
        }
        
        $query->with('item')->orderBy('double_tape_checksheets.operator_initials');

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
        $baseQuery = DoubleTapeChecksheet::with('item')
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

        return $result->sortByDesc('max_percentage')->values();
    }
}
