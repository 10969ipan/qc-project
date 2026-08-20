<?php

namespace App\Services;

use App\Models\IncomingExport;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncomingExportService extends BaseService
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
        $query = IncomingExport::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

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
                })->orWhere('operator_initials', 'like', "%{$searchTerm}%")
                  ->orWhere('qrcode', 'like', "%{$searchTerm}%")
                  ->orWhere('unique_code_id', 'like', "%{$searchTerm}%");
            });
        }

        if (!empty($filters['qr_raw'])) {
            $qrRaw = $filters['qr_raw'];
            $query->where(function ($q) use ($qrRaw) {
                $q->where('qrcode', 'like', "%{$qrRaw}%")
                  ->orWhere('unique_code_id', 'like', "%{$qrRaw}%")
                  ->orWhere('part_code', 'like', "%{$qrRaw}%")
                  ->orWhere('sap_code', 'like', "%{$qrRaw}%");
            });
        }

        if (!empty($filters['view_mode']) && $filters['view_mode'] === 'verifikasi') {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('qrcode')->where('qrcode', '!=', '');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('unique_code_id')->where('unique_code_id', '!=', '');
                })->orWhereIn('scan_method', ['hardware', 'camera']);
            });
        } elseif (!empty($filters['entry_method'])) {
            if (in_array($filters['entry_method'], ['qr', 'verification'])) {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNotNull('qrcode')->where('qrcode', '!=', '');
                    })->orWhere(function ($sub) {
                        $sub->whereNotNull('unique_code_id')->where('unique_code_id', '!=', '');
                    })->orWhereIn('scan_method', ['hardware', 'camera']);
                });
            } elseif (in_array($filters['entry_method'], ['manual', 'regular'])) {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('qrcode')->orWhere('qrcode', '');
                    })->where(function ($sub) {
                        $sub->whereNull('unique_code_id')->orWhere('unique_code_id', '');
                    })->where(function ($sub) {
                        $sub->whereNull('scan_method')
                            ->orWhere('scan_method', 'manual');
                    });
                });
            }
        } else {
            // Default when view_mode is not verifikasi: only show manual (regular) data
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('qrcode')->orWhere('qrcode', '');
                })->where(function ($sub) {
                    $sub->whereNull('unique_code_id')->orWhere('unique_code_id', '');
                })->where(function ($sub) {
                    $sub->whereNull('scan_method')
                        ->orWhere('scan_method', 'manual');
                });
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

    public function createChecksheet(array $data): array
    {
        DB::beginTransaction();
        try {
            $defects = $this->processDefects($data);

            $checksheet = IncomingExport::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? auth()->user()->plant_id),
                'item_id' => $data['item_id'],
                'standard' => $data['standard'] ?? null,
                'date' => $data['date'],
                'tanggal_delivery' => $data['tanggal_delivery'],
                'lot_qty' => $data['lot_qty'],
                'total_check' => $data['total_check'],
                'judgment' => $data['judgment'],
                'total_ng' => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'defects' => json_encode($defects),
                'part_code'         => !empty($data['part_code']) ? $data['part_code'] : null,
                'supplier_id'       => !empty($data['supplier_id']) ? $data['supplier_id'] : null,
                'quantity'          => !empty($data['quantity']) ? $data['quantity'] : null,
                'unique_code_id'    => !empty($data['unique_code_id']) ? $data['unique_code_id'] : null,
                'sap_code'          => !empty($data['sap_code']) ? $data['sap_code'] : null,
                'scan_method'       => !empty($data['scan_method']) ? $data['scan_method'] : 'manual',
                'qrcode'            => !empty($data['qrcode']) ? $data['qrcode'] : null,
                'cycle_time'        => $data['cycle_time'] ?? null,
            ]);

            DB::commit();

            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Incoming Export');
            }

            return ['checksheet' => $checksheet];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Incoming Export', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateChecksheet(int $id, array $data): IncomingExport
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingExport::findOrFail($id);
            $defects = $this->processDefects($data);

            $checksheet->update([
                'item_id' => $data['item_id'],
                'standard' => $data['standard'] ?? null,
                'date' => $data['date'],
                'tanggal_delivery' => $data['tanggal_delivery'],
                'lot_qty' => $data['lot_qty'],
                'total_check' => $data['total_check'],
                'judgment' => $data['judgment'],
                'total_ng' => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'defects' => json_encode($defects),
                'part_code'         => !empty($data['part_code']) ? $data['part_code'] : $checksheet->part_code,
                'supplier_id'       => !empty($data['supplier_id']) ? $data['supplier_id'] : $checksheet->supplier_id,
                'quantity'          => !empty($data['quantity']) ? $data['quantity'] : $checksheet->quantity,
                'unique_code_id'    => !empty($data['unique_code_id']) ? $data['unique_code_id'] : $checksheet->unique_code_id,
                'sap_code'          => !empty($data['sap_code']) ? $data['sap_code'] : $checksheet->sap_code,
                'scan_method'       => !empty($data['scan_method']) ? $data['scan_method'] : $checksheet->scan_method,
                'qrcode'            => !empty($data['qrcode']) ? $data['qrcode'] : $checksheet->qrcode,
                'cycle_time'        => $data['cycle_time'] ?? $checksheet->cycle_time,
            ]);

            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui checksheet Incoming Export', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteChecksheet(int $id): bool
    {
        $checksheet = IncomingExport::findOrFail($id);
        return $checksheet->delete();
    }

    public function updateApprovalStatus(int $id, array $data): IncomingExport
    {
        DB::beginTransaction();
        try {
            $checksheet = IncomingExport::findOrFail($id);
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
