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
        $query = PlatingChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

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
            $query->where('plating_checksheets.qrcode', 'like', "%{$filters['qr_raw']}%");
        }

        if (!empty($filters['entry_method'])) {
            if ($filters['entry_method'] === 'verification') {
                $query->whereNotNull('plating_checksheets.qrcode');
            } elseif ($filters['entry_method'] === 'regular') {
                $query->whereNull('plating_checksheets.qrcode');
            }
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
                'date' => $data['date'],
                'shift' => $data['shift'],
                'injection_date' => $data['injection_date'],
                'injection_shift' => $data['injection_shift'],
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
                'cycle_time' => $data['cycle_time'] ?? null,
                'no_lot' => $data['no_lot'] ?? null,
                'defects' => $defects,
            ]);

            DB::commit();

            Log::info('Checksheet Plating berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id
            ]);

            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Plating');
            }
            $this->notificationService->notifyApprovalRequest($checksheet, 'Plating');

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
                'date' => $data['date'],
                'shift' => $data['shift'],
                'injection_date' => $data['injection_date'],
                'injection_shift' => $data['injection_shift'],
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
            $checksheet = PlatingChecksheet::findOrFail($id);
            return $checksheet->delete();
        } catch (\Exception $e) {
            return false;
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

}
