<?php

namespace App\Services;

use App\Models\IncomingChemical;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncomingChemicalService extends BaseService
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
        $query = IncomingChemical::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

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

        if (!empty($filters['id'])) {
            $query->where($query->getModel()->getTable() . '.id', $filters['id']);
        }

        if (!empty($filters['supplier'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('customer', $filters['supplier']);
            });
        }

        if (!empty($filters['start_tgl_datang']) && !empty($filters['end_tgl_datang'])) {
            $query->whereBetween('tanggal_datang', [$filters['start_tgl_datang'], $filters['end_tgl_datang']]);
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

            $checksheet = IncomingChemical::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? auth()->user()->plant_id),
                'item_id' => $data['item_id'],
                'standard' => $data['standard'] ?? null,
                'tanggal_datang' => $data['tanggal_datang'],
                'date' => $data['date'],
                'lot_batch_number' => $data['lot_batch_number'],
                'quantity_kg' => $data['quantity_kg'],
                'komper_jirigen_kg' => $data['komper_jirigen_kg'],
                'sampling_size_jirigen_kg' => $data['sampling_size_jirigen_kg'],
                'expired_date' => $data['expired_date'],
                'judgment' => $data['judgment'],
                'total_ng' => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'defects' => json_encode($defects),
            ]);

            DB::commit();
            \Illuminate\Support\Facades\Cache::forget("incoming_chemicals_filters_" . md5(json_encode([$checksheet->plant_id])));

            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Incoming Chemical');
            }

            return ['checksheet' => $checksheet];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Incoming Chemical', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateChecksheet(int $id, array $data): IncomingChemical
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingChemical::findOrFail($id);
            $defects = $this->processDefects($data);

            $checksheet->update([
                'item_id' => $data['item_id'],
                'standard' => $data['standard'] ?? null,
                'tanggal_datang' => $data['tanggal_datang'],
                'date' => $data['date'],
                'lot_batch_number' => $data['lot_batch_number'],
                'quantity_kg' => $data['quantity_kg'],
                'komper_jirigen_kg' => $data['komper_jirigen_kg'],
                'sampling_size_jirigen_kg' => $data['sampling_size_jirigen_kg'],
                'expired_date' => $data['expired_date'],
                'judgment' => $data['judgment'],
                'total_ng' => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'defects' => json_encode($defects),
            ]);

            DB::commit();
            \Illuminate\Support\Facades\Cache::forget("incoming_chemicals_filters_" . md5(json_encode([$checksheet->plant_id])));
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui checksheet Incoming Chemical', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteChecksheet(int $id): bool
    {
        $checksheet = IncomingChemical::findOrFail($id);
        return $checksheet->delete();
    }

    public function updateApprovalStatus(int $id, array $data): IncomingChemical
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingChemical::findOrFail($id);
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
