<?php

namespace App\Services;

use App\Models\PaintingChecksheet;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaintingChecksheetService extends BaseService
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
        $query = PaintingChecksheet::with(['item'])->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('painting_checksheets.date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('painting_checksheets.date', '<=', $filters['end_date']);
        }

        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['operator_initials'])) {
            $query->where('painting_checksheets.operator_initials', $filters['operator_initials']);
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
            $query->where('painting_checksheets.qrcode', 'like', "%{$filters['qr_raw']}%");
        }

        if (!empty($filters['entry_method'])) {
            if ($filters['entry_method'] === 'verification') {
                $query->whereNotNull('painting_checksheets.qrcode');
            } elseif ($filters['entry_method'] === 'regular') {
                $query->whereNull('painting_checksheets.qrcode');
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

            $checksheet = PaintingChecksheet::create([
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
                'injection_date' => $data['injection_date'],
                'injection_shift' => $data['injection_shift'],
                'painting_date' => $data['painting_date'],
                'painting_shift' => $data['painting_shift'],
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

            Log::info('Checksheet Painting berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id
            ]);

            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Painting');
            }
            $this->notificationService->notifyApprovalRequest($checksheet, 'Painting');

            return [
                'checksheet' => $checksheet,
                'google_sheets_success' => false,
                'error' => null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Painting', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function updateChecksheet(int $id, array $data): PaintingChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = PaintingChecksheet::findOrFail($id);

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
                'painting_date' => $data['painting_date'],
                'painting_shift' => $data['painting_shift'],
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
                'no_lot' => $data['no_lot'] ?? $checksheet->no_lot,
                'defects' => $defects,
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
            $checksheet = PaintingChecksheet::findOrFail($id);
            return $checksheet->delete();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateApprovalStatus(int $id, array $data): PaintingChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = PaintingChecksheet::findOrFail($id);

            $this->processFullApprovalUpdate($checksheet, $data);

            $checksheet->save();
            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getDailyRecap(array $filters)
    {
        $query = PaintingChecksheet::with('item')
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

    public function getInspectorDailyRecap(array $filters)
    {
        $query = PaintingChecksheet::query()
            ->join('items', 'painting_checksheets.item_id', '=', 'items.id')
            ->whereNotNull('painting_checksheets.qrcode')
            ->select(
                'painting_checksheets.operator_initials',
                'painting_checksheets.item_id',
                DB::raw('SUM(painting_checksheets.total_qty) as total_qty_sum'),
                DB::raw('SUM(painting_checksheets.cycle_time) as total_act'),
                DB::raw('COALESCE(MAX(painting_checksheets.standard_cycle_time), MAX(items.standard_cycle_time)) as sct'),
                DB::raw('COUNT(*) as total_entries')
            )
            ->groupBy('painting_checksheets.operator_initials', 'painting_checksheets.item_id');

        if (isset($filters['plant'])) {
            $query->where('painting_checksheets.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('painting_checksheets.date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('painting_checksheets.date', '<=', $filters['end_date']);
        }

        if (!empty($filters['shift'])) {
            $query->where('painting_checksheets.shift', $filters['shift']);
        }
        
        $query->with('item')->orderBy('painting_checksheets.operator_initials');

        return $query->get()->map(function($row) {
            $act_min = $row->total_act / 60;
            $sct_min = $row->sct;

            $target = $sct_min > 0 ? ($act_min / $sct_min) : 0;
            $row->target = round($target);
            $row->plus_minus = $row->total_qty_sum - $row->target;
            
            return $row;
        });
    }
}
