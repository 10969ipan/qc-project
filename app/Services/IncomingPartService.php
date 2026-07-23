<?php

namespace App\Services;

use App\Models\IncomingPart;
use App\Models\IncomingPartArrival;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncomingPartService extends BaseService
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
        $query = IncomingPart::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

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
                })->orWhere('operator_initials', 'like', "%{$searchTerm}%");
            });
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

    public function getOutstandingArrivals($itemId)
    {
        return \App\Models\IncomingPartArrival::where('item_id', $itemId)
            ->where('status', 'OPEN')
            ->where('qty_sisa', '>', 0)
            ->orderBy('tanggal_datang', 'asc')
            ->orderBy('shift_datang', 'asc')
            ->get();
    }

    public function isFirstTimeArrival($itemId)
    {
        $count = \App\Models\IncomingPartArrival::where('item_id', $itemId)->count();
        if ($count > 0) {
            return false;
        }
        return \App\Models\IncomingPart::where('item_id', $itemId)->whereNotNull('tanggal_datang')->count() === 0;
    }

    public function createChecksheet(array $data): array
    {
        DB::beginTransaction();
        try {
            $defects = $this->processDefects($data);
            $plantId = $this->resolvePlantId($data['plant_id'] ?? auth()->user()->plant_id);
            $arrival = null;

            // Handle Arrival record
            if (!empty($data['arrival_id'])) {
                $arrival = \App\Models\IncomingPartArrival::find($data['arrival_id']);
            } elseif (!empty($data['tanggal_datang']) && !empty($data['qty_datang']) && (int)$data['qty_datang'] > 0) {
                $arrival = \App\Models\IncomingPartArrival::create([
                    'plant_id'       => $plantId,
                    'item_id'        => $data['item_id'],
                    'tanggal_datang' => $data['tanggal_datang'],
                    'shift_datang'   => $data['shift_datang'] ?? '1',
                    'qty_datang'     => (int)$data['qty_datang'],
                    'qty_sisa'       => (int)$data['qty_datang'],
                    'status'         => 'OPEN',
                ]);
            }

            // Deduct sisa qty on arrival if associated
            if ($arrival) {
                $checkQty = (int)($data['total_check'] ?? 0);
                $newSisa = max(0, $arrival->qty_sisa - $checkQty);
                $arrival->qty_sisa = $newSisa;
                if ($newSisa <= 0) {
                    $arrival->status = 'COMPLETED';
                }
                $arrival->save();
            }

            $checksheet = IncomingPart::create([
                'plant_id'          => $plantId,
                'item_id'           => $data['item_id'],
                'arrival_id'        => $arrival ? $arrival->id : null,
                'date'              => $data['date'],
                'shift'             => $data['shift'],
                'lot_qty'           => $data['lot_qty'] ?? ($arrival ? $arrival->qty_datang : 0),
                'total_check'       => $data['total_check'],
                'tanggal_datang'    => $data['tanggal_datang'] ?? ($arrival ? $arrival->tanggal_datang : $data['date']),
                'judgment'          => $data['judgment'],
                'total_ng'          => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks'           => $data['remarks'] ?? null,
                'defects'           => json_encode($defects),
            ]);

            DB::commit();

            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Incoming Part');
            }

            return ['checksheet' => $checksheet];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Incoming Part', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateChecksheet(int $id, array $data): IncomingPart
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingPart::findOrFail($id);
            $oldTotalCheck = (int) $checksheet->total_check;
            $newTotalCheck = (int) $data['total_check'];
            $diffCheck = $newTotalCheck - $oldTotalCheck;

            $defects = $this->processDefects($data);

            $checksheet->update([
                'item_id' => $data['item_id'],
                'date' => $data['date'],
                'shift' => $data['shift'],
                'lot_qty' => $data['lot_qty'] ?? $checksheet->lot_qty,
                'total_check' => $newTotalCheck,
                'tanggal_datang' => $data['tanggal_datang'] ?? $checksheet->tanggal_datang,
                'judgment' => $data['judgment'],
                'total_ng' => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'defects' => json_encode($defects),
            ]);

            // Sync Arrival Qty Balance if arrival_id is present and check qty changed
            if ($checksheet->arrival_id && $diffCheck !== 0) {
                $arrival = IncomingPartArrival::find($checksheet->arrival_id);
                if ($arrival) {
                    $newQtySisa = $arrival->qty_sisa - $diffCheck;
                    $arrival->qty_sisa = max(0, min($arrival->qty_datang, $newQtySisa));
                    $arrival->status = ($arrival->qty_sisa === 0) ? 'COMPLETED' : 'OPEN';
                    $arrival->save();
                }
            }

            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui checksheet Incoming Part', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteChecksheet(int $id): bool
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingPart::findOrFail($id);

            // Sync Arrival Qty Balance if arrival_id is present
            if ($checksheet->arrival_id) {
                $arrival = IncomingPartArrival::find($checksheet->arrival_id);
                if ($arrival) {
                    $newQtySisa = $arrival->qty_sisa + (int) $checksheet->total_check;
                    $arrival->qty_sisa = min($arrival->qty_datang, $newQtySisa);
                    if ($arrival->qty_sisa > 0) {
                        $arrival->status = 'OPEN';
                    }
                    $arrival->save();
                }
            }

            $deleted = $checksheet->delete();
            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus checksheet Incoming Part', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateApprovalStatus(int $id, array $data): IncomingPart
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingPart::findOrFail($id);
            $user = auth()->user();

            $this->updateApprovalLevel($checksheet, 'kashift', $data['kashift_qc'], $user);
            $this->updateApprovalLevel($checksheet, 'supervisor', $data['supervisor_qc'], $user);
            $this->updateApprovalLevel($checksheet, 'asst_manager', $data['asst_manager_qc'], $user);
            $this->updateApprovalLevel($checksheet, 'manager', $data['manager_qc'], $user);

            if (in_array('REJECTED', [$checksheet->manager_qc, $checksheet->asst_manager_qc, $checksheet->supervisor_qc, $checksheet->kashift_qc])) {
                $checksheet->approval_status = 'Rejected';
            } elseif ($checksheet->manager_qc && $checksheet->manager_qc !== 'Pending') {
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
