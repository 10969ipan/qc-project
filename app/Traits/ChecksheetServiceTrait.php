<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trait for shared checksheet service logic
 */
trait ChecksheetServiceTrait
{
    /**
     * Process defects from request data
     */
    protected function processDefects(array $data): array
    {
        $defectsMap = [];
        if (isset($data['defect_types'])) {
            foreach ($data['defect_types'] as $index => $type) {
                if ($type !== null && trim((string)$type) !== '') {
                    $qty = (int) ($data['defect_quantities'][$index] ?? 0);
                    if ($qty > 0) {
                        $trimmedType = trim((string)$type);
                        $key = strtolower($trimmedType);
                        
                        // Standardize dimension defect names to 'Dimensi'
                        if (in_array($key, ['dimension', 'dimensi', 'ng dimensi'])) {
                            $key = 'dimensi';
                            $trimmedType = 'Dimensi';
                        }

                        if (isset($defectsMap[$key])) {
                            $defectsMap[$key]['qty'] += $qty;
                        } else {
                            $defectsMap[$key] = [
                                'type' => $trimmedType,
                                'qty'  => $qty,
                            ];
                        }
                    }
                }
            }
        }
        return array_values($defectsMap);
    }

    /**
     * Apply approval status filter to query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     */
    protected function applyApprovalStatusFilter($query, string $status): void
    {
        if ($status === 'Pending') {
            $query->where(function ($q) {
                $q->where('approval_status', 'Pending')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('approval_status')
                            ->whereNull('supervisor_qc')
                            ->where(function ($rej) {
                                $rej->where('kashift_qc', '!=', 'REJECTED')
                                    ->orWhereNull('kashift_qc');
                            });
                    });
            });
        } elseif ($status === 'Approved') {
            $query->where(function ($q) {
                $q->where('approval_status', 'Approved')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('approval_status')
                            ->whereNotNull('supervisor_qc')
                            ->where('supervisor_qc', '!=', 'REJECTED');
                    });
            });
        } elseif ($status === 'Rejected') {
            $query->where(function ($q) {
                $q->where('approval_status', 'Rejected')
                    ->orWhere(function ($sub) {
                        $sub->whereNull('approval_status')
                            ->where(function ($rej) {
                                $rej->where('kashift_qc', 'REJECTED')
                                    ->orWhere('supervisor_qc', 'REJECTED')
                                    ->orWhere('asst_manager_qc', 'REJECTED')
                                    ->orWhere('manager_qc', 'REJECTED');
                            });
                    });
            });
        }
    }

    /**
     * Update single approval level
     */
    protected function updateApprovalLevel($checksheet, string $level, string $status, $user): void
    {
        $nameField = "{$level}_qc";
        $dateField = "{$level}_approved_at";

        if ($status === 'Approved') {
            if (is_null($checksheet->$nameField) || $checksheet->$nameField === 'REJECTED') {
                $checksheet->$nameField = $user->name;
                $checksheet->$dateField = now();
            }
        } elseif ($status === 'Rejected') {
            if ($checksheet->$nameField !== 'REJECTED') {
                $checksheet->$nameField = 'REJECTED';
                $checksheet->$dateField = now();
            }
        } else {
            $checksheet->$nameField = null;
            $checksheet->$dateField = null;
        }
    }

    /**
     * Common logic for updating approval status across all 4 levels
     */
    protected function processFullApprovalUpdate($checksheet, array $data): void
    {
        $user = auth()->user();

        $this->updateApprovalLevel($checksheet, 'kashift', $data['kashift_qc'], $user);
        $this->updateApprovalLevel($checksheet, 'supervisor', $data['supervisor_qc'], $user);
        $this->updateApprovalLevel($checksheet, 'asst_manager', $data['asst_manager_qc'], $user);
        $this->updateApprovalLevel($checksheet, 'manager', $data['manager_qc'], $user);

        if (
            $checksheet->manager_qc === 'REJECTED' ||
            $checksheet->asst_manager_qc === 'REJECTED' ||
            $checksheet->supervisor_qc === 'REJECTED' ||
            $checksheet->kashift_qc === 'REJECTED'
        ) {
            $checksheet->approval_status = 'Rejected';
        } elseif ($checksheet->manager_qc) {
            $checksheet->approval_status = 'Approved';
        } else {
            $checksheet->approval_status = 'Pending';
        }
    }
}
