<?php

namespace App\Services;

use App\Models\SubAssyChecksheet;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubAssyChecksheetService extends BaseService
{
    use \App\Traits\ChecksheetServiceTrait;
    protected $googleSheetService;
    protected $notificationService;

    public function __construct(GoogleSheetService $googleSheetService, NotificationService $notificationService)
    {
        $this->googleSheetService = $googleSheetService;
        $this->notificationService = $notificationService;
    }

    /**
     * Get filtered checksheets with pagination
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery(array $filters)
    {
        return $this->buildFilteredQuery($filters);
    }

    /**
     * Build the filtered query
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(array $filters)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = SubAssyChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        // Apply plant filter if present (Global scope handles restrictions for non-exempt roles)
        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        // Date range filter
        if (!empty($filters['start_date'])) {
            $query->whereDate('sub_assy_checksheets.date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('sub_assy_checksheets.date', '<=', $filters['end_date']);
        }

        // Approval status filter
        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        // Item filter
        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        // Next Process filter
        if (!empty($filters['next_proses'])) {
            $query->where('next_proses', $filters['next_proses']);
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

        // ID filter (for direct links from Sortir)
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        return $query;
    }

    public function getFilteredChecksheets(array $filters)
    {
        return $this->buildFilteredQuery($filters)->paginate(10)->withQueryString();
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
                'user_id' => auth()->id(),
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
                'defects' => $defects,
            ]);

            // Clear manual line status override
            \App\Models\MachineStatus::updateOrCreate(
                [
                    'plant_id' => $checksheet->plant_id,
                    'type' => 'line',
                    'number' => $checksheet->line,
                ],
                [
                    'status' => 'normal',
                    'description' => 'Automatically cleared by checksheet input',
                    'created_by' => 'System'
                ]
            );

            DB::commit();

            Log::info('Checksheet Sub Assy berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id,
                'plant_id' => $checksheet->plant_id
            ]);

            // Notifications
            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'Sub Assy');
            }
            $this->notificationService->notifyApprovalRequest($checksheet, 'Sub Assy');

            // Try to send to Google Sheets
            $googleSheetsSuccess = false;
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
            Log::error('Gagal membuat checksheet Sub Assy', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
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

            // Allow manual correction of inspector if provided
            if (isset($data['user_id'])) {
                $updateData['user_id'] = $data['user_id'];
            }

            // Update plant_id if provided (primarily for admin)
            if (isset($data['plant'])) {
                $updateData['plant_id'] = $this->resolvePlantId($data['plant']);
            }

            // Process defects update
            $defects = $this->processDefects($data);
            $updateData['defects'] = $defects;

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

            // Clear manual line status override
            \App\Models\MachineStatus::updateOrCreate(
                [
                    'plant_id' => $checksheet->plant_id,
                    'type' => 'line',
                    'number' => $checksheet->line,
                ],
                [
                    'status' => 'normal',
                    'description' => 'Automatically cleared by checksheet update',
                    'created_by' => 'System'
                ]
            );

            DB::commit();

            Log::info('Checksheet Sub Assy berhasil diperbarui', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id,
                'plant_id' => $checksheet->plant_id
            ]);

            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui checksheet Sub Assy', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id,
                'error' => $e->getMessage()
            ]);
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

            Log::info('Checksheet Sub Assy berhasil dihapus', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus checksheet Sub Assy', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id,
                'error' => $e->getMessage()
            ]);
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
