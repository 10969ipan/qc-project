<?php

namespace App\Services;

use App\Models\SubAssyChecksheet;
use App\Models\Item;
use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubAssyChecksheetService extends BaseService
{
    protected $googleSheetService;

    public function __construct(GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

    /**
     * Get filtered checksheets with pagination
     * 
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredChecksheets(array $filters)
    {
        $query = SubAssyChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        // Apply plant filter if present (Global scope handles restrictions for non-exempt roles)
        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        // Date range filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        // Approval status filter
        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        // Item filter
        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        // Live search filter
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

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Create new checksheet
     * 
     * @param array $data
     * @param callable $mapExportRow
     * @return array ['checksheet' => SubAssyChecksheet, 'google_sheets_success' => bool, 'error' => string|null]
     */
    public function createChecksheet(array $data, callable $mapExportRow): array
    {
        DB::beginTransaction();
        try {
            // Process defects
            $defects = $this->processDefects($data);

            // Create checksheet
            $checksheet = SubAssyChecksheet::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id),
                'item_id' => $data['item_id'],
                'date' => $data['date'],
                'shift' => $data['shift'],
                'line' => $data['line'],
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'next_proses' => $data['next_proses'] ?? null,
                'cycle_time' => $data['cycle_time'] ?? null,
                'defects' => json_encode($defects),
            ]);

            DB::commit();

            // Try to send to Google Sheets
            $googleSheetsSuccess = false; // Disabled by user request
            $error = null;

            /*
            try {
                $sheetData = $mapExportRow($checksheet);
                $this->googleSheetService->appendRow($sheetData);
                $googleSheetsSuccess = true;
            } catch (\Exception $e) {
                Log::error('Gagal kirim ke Google Sheets: ' . $e->getMessage());
                $error = $e->getMessage();
            }
            */

            return [
                'checksheet' => $checksheet,
                'google_sheets_success' => $googleSheetsSuccess,
                'error' => $error
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update checksheet
     * 
     * @param int $id
     * @param array $data
     * @return SubAssyChecksheet
     */
    public function updateChecksheet(int $id, array $data): SubAssyChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = SubAssyChecksheet::findOrFail($id);

            $updateData = [
                'item_id' => $data['item_id'],
                'date' => $data['date'],
                'shift' => $data['shift'],
                'line' => $data['line'],
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'next_proses' => $data['next_proses'] ?? null,
            ];

            // Update created_at and cycle_time if user has authority (not inspector)
            if (auth()->user()->role !== 'inspector') {
                $currentDate = $checksheet->created_at->format('Y-m-d');

                if (!empty($data['jam_after'])) {
                    $updateData['created_at'] = \Carbon\Carbon::parse($currentDate . ' ' . $data['jam_after']);
                }

                if (!empty($data['jam_before']) && !empty($data['jam_after'])) {
                    $before = \Carbon\Carbon::parse($currentDate . ' ' . $data['jam_before']);
                    $after = \Carbon\Carbon::parse($currentDate . ' ' . $data['jam_after']);

                    // Handle day transition (crossing midnight)
                    if ($after->lessThan($before)) {
                        $after->addDay();
                    }

                    $updateData['cycle_time'] = $before->diffInSeconds($after);
                } else {
                    $updateData['cycle_time'] = $data['cycle_time'] ?? null;
                }
            } else {
                $updateData['cycle_time'] = $data['cycle_time'] ?? null;
            }

            $checksheet->update($updateData);

            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete checksheet
     * 
     * @param int $id
     * @return bool
     */
    public function deleteChecksheet(int $id): bool
    {
        DB::beginTransaction();
        try {
            $query = SubAssyChecksheet::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $checksheet = $query->findOrFail($id);
            $checksheet->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update approval status (admin only)
     * 
     * @param int $id
     * @param array $data
     * @return SubAssyChecksheet
     */
    public function updateApprovalStatus(int $id, array $data): SubAssyChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = SubAssyChecksheet::findOrFail($id);
            $user = auth()->user();

            // Update each approval level
            $this->updateApprovalLevel($checksheet, 'kashift', $data['kashift_qc'], $user);
            $this->updateApprovalLevel($checksheet, 'supervisor', $data['supervisor_qc'], $user);
            $this->updateApprovalLevel($checksheet, 'asst_manager', $data['asst_manager_qc'], $user);
            $this->updateApprovalLevel($checksheet, 'manager', $data['manager_qc'], $user);

            // Update overall approval status
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

            $checksheet->save();

            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process defects from request
     * 
     * @param array $data
     * @return array
     */
    private function processDefects(array $data): array
    {
        $defects = [];
        if (isset($data['defect_types'])) {
            foreach ($data['defect_types'] as $index => $type) {
                if ($type) {
                    $qty = $data['defect_quantities'][$index] ?? 1;
                    $defects[] = ['type' => $type, 'qty' => (int) $qty];
                }
            }
        }
        return $defects;
    }

    /**
     * Apply approval status filter to query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return void
     */
    private function applyApprovalStatusFilter($query, string $status): void
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
                                    ->orWhere('asst_manager_qc', 'REJECTED');
                            });
                    });
            });
        }
    }

    /**
     * Update single approval level
     * 
     * @param SubAssyChecksheet $checksheet
     * @param string $level
     * @param string $status
     * @param \App\Models\User $user
     * @return void
     */
    private function updateApprovalLevel(SubAssyChecksheet $checksheet, string $level, string $status, $user): void
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
        } else { // Pending
            $checksheet->$nameField = null;
            $checksheet->$dateField = null;
        }
    }
}
