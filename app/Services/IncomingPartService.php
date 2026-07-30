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

        if (!empty($filters['operator_initials'])) {
            $query->where('operator_initials', $filters['operator_initials']);
        }

        if (!empty($filters['customer'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('customer', $filters['customer']);
            });
        }

        if (!empty($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        if (!empty($filters['qr_raw'])) {
            $qrRaw = trim($filters['qr_raw']);
            $query->where(function ($q) use ($qrRaw) {
                $q->where('qrcode', 'like', "%{$qrRaw}%")
                  ->orWhere('unique_code_id', 'like', "%{$qrRaw}%")
                  ->orWhere('part_code', 'like', "%{$qrRaw}%")
                  ->orWhere('sap_code', 'like', "%{$qrRaw}%");
            });
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
            // Default saat view_mode bukan verifikasi: hanya tampilkan data manual (regular)
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

    public function getOutstandingArrivals($itemId)
    {
        // Clean up orphan arrival records that have 0 checksheets attached
        IncomingPartArrival::where('item_id', $itemId)
            ->whereDoesntHave('checksheets')
            ->delete();

        return IncomingPartArrival::where('item_id', $itemId)
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
            // Check for duplicate QR Code / Unique Code ID
            $uniqueCode = !empty($data['unique_code_id']) ? trim($data['unique_code_id']) : null;
            $qrCode = !empty($data['qrcode']) ? trim($data['qrcode']) : null;

            if ($uniqueCode) {
                $query = IncomingPart::where('unique_code_id', $uniqueCode);
                if (!empty($data['sap_code'])) {
                    $query->where('sap_code', trim($data['sap_code']));
                }
                $duplicate = $query->first();
                if ($duplicate) {
                    $tglScan = $duplicate->created_at ? $duplicate->created_at->format('d/m/Y H:i') : $duplicate->date;
                    throw new \Exception("Gagal Menyimpan! QR Code / Unique Code ID ({$uniqueCode}) ini sudah pernah dipindai dan disimpan sebelumnya pada {$tglScan}.");
                }
            }

            if ($qrCode) {
                $duplicateQr = IncomingPart::where('qrcode', $qrCode)->first();
                if ($duplicateQr) {
                    $tglScan = $duplicateQr->created_at ? $duplicateQr->created_at->format('d/m/Y H:i') : $duplicateQr->date;
                    throw new \Exception("Gagal Menyimpan! QR Code ini sudah pernah dipindai dan disimpan sebelumnya pada {$tglScan}.");
                }
            }

            $defects = $this->processDefects($data);
            $plantId = $this->resolvePlantId($data['plant_id'] ?? auth()->user()->plant_id);
            $arrival = null;

            // Handle Arrival record (prioritize user submitted form date & shift)
            $shiftDatang = $data['shift_datang'] ?? '1';
            $tglDatang = !empty($data['tanggal_datang']) ? date('Y-m-d', strtotime($data['tanggal_datang'])) : null;

            if (!empty($data['arrival_id'])) {
                $candidate = \App\Models\IncomingPartArrival::find($data['arrival_id']);
                if ($candidate && $candidate->status === 'OPEN') {
                    $arrival = $candidate;
                    // Preserve user submitted date if provided, otherwise fallback to arrival record date
                    if (!empty($tglDatang)) {
                        $arrival->tanggal_datang = $tglDatang;
                    } else {
                        $tglDatang = $arrival->tanggal_datang ? $arrival->tanggal_datang->format('Y-m-d') : null;
                    }

                    if (!empty($data['shift_datang'])) {
                        $arrival->shift_datang = $data['shift_datang'];
                    } else {
                        $shiftDatang = (string)($arrival->shift_datang ?? $shiftDatang);
                    }
                    $arrival->save();
                }
            }

            if (!$arrival && !empty($tglDatang)) {
                // Find existing OPEN arrival for this exact item, plant, date & shift
                $existingArrival = \App\Models\IncomingPartArrival::where('plant_id', $plantId)
                    ->where('item_id', $data['item_id'])
                    ->where('tanggal_datang', $tglDatang)
                    ->where('shift_datang', $shiftDatang)
                    ->where('status', 'OPEN')
                    ->first();

                if ($existingArrival) {
                    $arrival = $existingArrival;
                } elseif (!empty($data['qty_datang']) && (int)$data['qty_datang'] > 0) {
                    $arrival = \App\Models\IncomingPartArrival::create([
                        'plant_id'       => $plantId,
                        'item_id'        => $data['item_id'],
                        'tanggal_datang' => $tglDatang,
                        'shift_datang'   => $shiftDatang,
                        'qty_datang'     => (int)$data['qty_datang'],
                        'qty_sisa'       => (int)$data['qty_datang'],
                        'status'         => 'OPEN',
                    ]);
                }
            }

            // Calculate Historical Snapshot of Qty Balance Sisa
            $checkQty = (int)($data['total_check'] ?? 0);
            $historicalSisaSnapshot = 0;

            if ($arrival) {
                $historicalSisaSnapshot = max(0, $arrival->qty_sisa - $checkQty);
                $arrival->qty_sisa = $historicalSisaSnapshot;
                if ($historicalSisaSnapshot <= 0) {
                    $arrival->status = 'COMPLETED';
                }
                $arrival->save();
            } else {
                $initialStock = (int)($data['qty_balance'] ?? $data['lot_qty'] ?? 0);
                $historicalSisaSnapshot = max(0, $initialStock - $checkQty);
            }

            $checksheet = IncomingPart::create([
                'plant_id'          => $plantId,
                'item_id'           => $data['item_id'],
                'arrival_id'        => $arrival ? $arrival->id : null,
                'date'              => $data['date'],
                'shift'             => $data['shift'],
                'lot_qty'           => $data['lot_qty'] ?? ($arrival ? $arrival->qty_datang : 0),
                'total_check'       => $data['total_check'],
                'sampling_qty'      => $data['sampling_qty'] ?? null,
                'qty_balance_sisa'  => isset($data['qty_balance_sisa']) && $data['qty_balance_sisa'] !== '' ? (int)$data['qty_balance_sisa'] : $historicalSisaSnapshot,
                'tanggal_datang'    => $tglDatang ?? ($arrival ? $arrival->tanggal_datang : $data['date']),
                'judgment'          => $data['judgment'],
                'total_ng'          => $data['total_ng'] ?? 0,
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks'           => $data['remarks'] ?? null,
                'defects'           => json_encode($defects),
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

            $newSnapshotSisa = isset($data['qty_balance_sisa']) && $data['qty_balance_sisa'] !== ''
                ? (int)$data['qty_balance_sisa']
                : max(0, (int)($checksheet->qty_balance_sisa ?? 0) - $diffCheck);

            $checksheet->update([
                'item_id' => $data['item_id'],
                'date' => $data['date'],
                'shift' => $data['shift'],
                'lot_qty' => $data['lot_qty'] ?? $checksheet->lot_qty,
                'total_check' => $newTotalCheck,
                'sampling_qty' => $data['sampling_qty'] ?? $checksheet->sampling_qty,
                'qty_balance_sisa' => $newSnapshotSisa,
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
            $arrivalId = $checksheet->arrival_id;

            $deleted = $checksheet->delete();

            // Sync or cleanup Arrival Qty Balance if arrival_id was present
            if ($arrivalId) {
                $arrival = IncomingPartArrival::find($arrivalId);
                if ($arrival) {
                    if ($arrival->checksheets()->count() === 0) {
                        $arrival->delete();
                    } else {
                        $newQtySisa = $arrival->qty_sisa + (int) $checksheet->total_check;
                        $arrival->qty_sisa = min($arrival->qty_datang, $newQtySisa);
                        if ($arrival->qty_sisa > 0) {
                            $arrival->status = 'OPEN';
                        }
                        $arrival->save();
                    }
                }
            }

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus checksheet Incoming Part', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function bulkDeleteChecksheets(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        DB::beginTransaction();
        try {
            $checksheets = IncomingPart::whereIn('id', $ids)->get();

            // Group & batch sync Arrival Qty Balances
            $arrivalAdjustments = [];
            $affectedArrivalIds = [];
            foreach ($checksheets as $cs) {
                if ($cs->arrival_id) {
                    $arrivalAdjustments[$cs->arrival_id] = ($arrivalAdjustments[$cs->arrival_id] ?? 0) + (int) $cs->total_check;
                    $affectedArrivalIds[] = $cs->arrival_id;
                }
            }

            // High-speed deletion without per-row JSON table scan bottlenecks
            $deletedCount = IncomingPart::withoutEvents(function () use ($ids) {
                return IncomingPart::whereIn('id', $ids)->delete();
            });

            // Cleanup or adjust arrival balances
            $affectedArrivalIds = array_unique($affectedArrivalIds);
            foreach ($affectedArrivalIds as $arrivalId) {
                $arrival = IncomingPartArrival::find($arrivalId);
                if ($arrival) {
                    if ($arrival->checksheets()->count() === 0) {
                        $arrival->delete();
                    } else {
                        $addQty = $arrivalAdjustments[$arrivalId] ?? 0;
                        $newQtySisa = $arrival->qty_sisa + $addQty;
                        $arrival->qty_sisa = min($arrival->qty_datang, $newQtySisa);
                        if ($arrival->qty_sisa > 0) {
                            $arrival->status = 'OPEN';
                        }
                        $arrival->save();
                    }
                }
            }

            DB::commit();
            return $deletedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus massal checksheet Incoming Part', ['error' => $e->getMessage()]);
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
