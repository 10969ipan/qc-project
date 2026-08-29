<?php

namespace App\Services;

use App\Models\IncomingSubPart;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncomingSubPartService extends BaseService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getQuery(array $filters)
    {
        return $this->buildFilteredQuery($filters);
    }

    public function buildFilteredQuery(array $filters)
    {
        $query = IncomingSubPart::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
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
                })->orWhere('lot_batch_number', 'like', "%{$searchTerm}%")
                    ->orWhere('operator_initials', 'like', "%{$searchTerm}%");
            });
        }

        $table = $query->getModel()->getTable();
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'qrcode')) {
            if (!empty($filters['view_mode']) && $filters['view_mode'] === 'verifikasi') {
                $query->where(function ($q) {
                    $q->whereNotNull('qrcode')
                      ->orWhereNotNull('unique_code_id')
                      ->orWhereIn('scan_method', ['hardware', 'camera']);
                });
            } elseif (!empty($filters['entry_method'])) {
                if (in_array($filters['entry_method'], ['qr', 'verification'])) {
                    $query->where(function ($q) {
                        $q->whereNotNull('qrcode')
                          ->orWhereNotNull('unique_code_id')
                          ->orWhereIn('scan_method', ['hardware', 'camera']);
                    });
                } elseif (in_array($filters['entry_method'], ['manual', 'regular'])) {
                    $query->where(function ($q) {
                        $q->whereNull('qrcode')
                          ->whereNull('unique_code_id')
                          ->where(function ($sub) {
                              $sub->whereNull('scan_method')
                                  ->orWhere('scan_method', 'manual');
                          });
                    });
                }
            } else {
                $query->where(function ($q) {
                    $q->whereNull('qrcode')
                      ->whereNull('unique_code_id')
                      ->where(function ($sub) {
                          $sub->whereNull('scan_method')
                              ->orWhere('scan_method', 'manual');
                      });
                });
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

    public function createChecksheet(array $data): array
    {
        DB::beginTransaction();
        try {
            $defects = $this->processDefects($data);
            $checkDimensi = $this->resolveCheckDimensi($data);

            $checksheet = IncomingSubPart::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? auth()->user()->plant_id),
                'item_id' => $data['item_id'],
                'standard' => $data['standard'] ?? null,
                'tanggal_datang' => $data['tanggal_datang'],
                'date' => $data['date'],
                'lot_batch_number' => $data['lot_batch_number'],
                'quantity' => $data['quantity'],
                'sampling_size_pcs' => $data['sampling_size_pcs'],
                'check_dimensi' => $checkDimensi,
                'judgment' => $data['judgment'],
                'total_ng' => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'cycle_time' => $data['cycle_time'] ?? null,
                'defects' => json_encode($defects),
            ]);

            DB::commit();

            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Incoming Sub-Part');
            }

            return ['checksheet' => $checksheet];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Incoming Sub-Part', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateChecksheet(int $id, array $data): IncomingSubPart
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingSubPart::findOrFail($id);
            $defects = $this->processDefects($data);
            $checkDimensi = $this->resolveCheckDimensi($data);

            $checksheet->update([
                'item_id' => $data['item_id'],
                'standard' => $data['standard'] ?? null,
                'tanggal_datang' => $data['tanggal_datang'],
                'date' => $data['date'],
                'lot_batch_number' => $data['lot_batch_number'],
                'quantity' => $data['quantity'],
                'sampling_size_pcs' => $data['sampling_size_pcs'],
                'check_dimensi' => $checkDimensi,
                'judgment' => $data['judgment'],
                'total_ng' => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'cycle_time' => $data['cycle_time'] ?? $checksheet->cycle_time,
                'defects' => json_encode($defects),
            ]);

            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui checksheet Incoming Sub-Part', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function resolveCheckDimensi(array $data): string
    {
        if (!empty($data['dimensions']) && is_array($data['dimensions'])) {
            $hasData = false;
            foreach ($data['dimensions'] as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $sub) {
                        if ($sub !== null && trim((string)$sub) !== '' && trim((string)$sub) !== '-') {
                            $hasData = true;
                            break 2;
                        }
                    }
                } elseif ($v !== null && trim((string)$v) !== '' && trim((string)$v) !== '-') {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                return json_encode($data['dimensions']);
            }
        }
        if (!empty($data['check_dimensi'])) {
            return $data['check_dimensi'];
        }
        return 'OK';
    }

    public function deleteChecksheet(int $id): bool
    {
        $checksheet = IncomingSubPart::findOrFail($id);
        return $checksheet->delete();
    }

    public function updateApprovalStatus(int $id, array $data): IncomingSubPart
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingSubPart::findOrFail($id);
            $user = auth()->user();

            $this->updateApprovalLevel($checksheet, 'kashift', $data['kashift_qc'], $user);
            $this->updateApprovalLevel($checksheet, 'supervisor', $data['supervisor_qc'], $user);

            if (in_array('REJECTED', [$checksheet->supervisor_qc, $checksheet->kashift_qc])) {
                $checksheet->approval_status = 'Rejected';
            } elseif ($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'Pending') {
                $checksheet->approval_status = 'Approved';
            } else {
                $checksheet->approval_status = 'Pending';
            }

            $checksheet->save();
            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processDefects(array $data): array
    {
        $defects = [];
        if (isset($data['defect_types'])) {
            foreach ($data['defect_types'] as $index => $type) {
                if ($type) {
                    $qty = $data['defect_quantities'][$index] ?? 0;
                    if ((int)$qty > 0) {
                        $defects[] = ['type' => $type, 'qty' => (int) $qty];
                    }
                }
            }
        }
        return $defects;
    }

    private function applyApprovalStatusFilter($query, string $status): void
    {
        if ($status === 'Pending') {
            $query->where('approval_status', 'Pending')->orWhereNull('approval_status');
        } else {
            $query->where('approval_status', $status);
        }
    }

    private function updateApprovalLevel($checksheet, string $level, string $status, $user): void
    {
        $nameField = "{$level}_qc";
        $dateField = "{$level}_approved_at";

        if ($status === 'Approved') {
            $checksheet->$nameField = $user->name;
            $checksheet->$dateField = now();
        } elseif ($status === 'Rejected') {
            $checksheet->$nameField = 'REJECTED';
            $checksheet->$dateField = now();
        } else {
            $checksheet->$nameField = null;
            $checksheet->$dateField = null;
        }
    }
}
