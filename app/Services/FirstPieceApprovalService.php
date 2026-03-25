<?php

namespace App\Services;

use App\Models\FirstPieceApproval;
use App\Models\Item;
use App\Services\GoogleSheetService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FirstPieceApprovalService extends BaseService
{
    use \App\Traits\ChecksheetServiceTrait;
    protected $googleSheetService;
    protected $notificationService;
    protected $hardcodedStandards;

    public function __construct(GoogleSheetService $googleSheetService, NotificationService $notificationService)
    {
        $this->googleSheetService = $googleSheetService;
        $this->notificationService = $notificationService;

        // Hardcoded dimension standards -- ... rest of standards ...
        $this->hardcodedStandards = [
            '53102-K0L -D002' => [
                '1' => ['size' => 5, 'tolerance' => 0.2],
                '2' => ['size' => 10, 'tolerance' => 0.2],
                '3' => ['size' => 10, 'tolerance' => 0.5],
                '4' => ['size' => 20.5, 'tolerance' => 0.2],
                '5' => ['size' => 20, 'tolerance' => 0.2],
            ],
            '1PA - F836B - 00' => [
                '1' => ['size' => 25, 'tolerance' => 0.2],
                '2' => ['size' => 21, 'tolerance' => 0.4],
                '3' => ['size' => 3.2, 'tolerance' => 0.2],
                '4' => ['size' => 24, 'tolerance' => 0.4],
            ],
            '53209-K3V-N100' => [
                '1' => ['size' => 10, 'tolerance' => 0.2],
                '2' => ['size' => 10, 'tolerance' => 0.2],
                '3' => ['size' => 10, 'tolerance' => 0.2],
                '4' => ['size' => 10, 'tolerance' => 0.2],
            ],
        ];
    }

    /**
     * Get consolidated dimension standards (hardcoded + database)
     * 
     * @return array
     */
    public function getConsolidatedStandards(): array
    {
        $standards = [];
        foreach ($this->hardcodedStandards as $pn => $stds) {
            $standards[$this->normalizePartNumber($pn)] = $stds;
        }

        $dbItems = Item::whereNotNull('dimension_standards')->get();
        foreach ($dbItems as $item) {
            $partNum = $this->normalizePartNumber($item->part_number ?? '');
            if ($partNum !== '' && !empty($item->dimension_standards)) {
                $itemStandards = [];
                foreach ($item->dimension_standards as $index => $std) {
                    $hasSizeTol = isset($std['size']) && $std['size'] !== '' && isset($std['tolerance']) && $std['tolerance'] !== '';
                    $hasMinMax = (isset($std['min']) && $std['min'] !== '') || (isset($std['max']) && $std['max'] !== '');

                    if (is_array($std) && ($hasSizeTol || $hasMinMax)) {
                        $pointKey = (string) ($index + 1);

                        // Robust float conversion helper
                        $toFloat = function ($val) {
                            if ($val === null || $val === '')
                                return null;
                            $val = str_replace(',', '.', (string) $val);
                            return is_numeric($val) ? (float) $val : null;
                        };

                        $itemStandards[$pointKey] = [
                            'size' => $toFloat($std['size'] ?? null),
                            'tolerance' => $toFloat($std['tolerance'] ?? null),
                            'min' => $toFloat($std['min'] ?? null),
                            'max' => $toFloat($std['max'] ?? null),
                        ];
                    }
                }

                if (!empty($itemStandards)) {
                    $standards[$partNum] = $itemStandards;
                }
            }
        }

        return $standards;
    }

    /**
     * Get filtered checksheets with pagination
     * 
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredChecksheets(array $filters)
    {
        return $this->buildFilteredQuery($filters)->paginate(10)->withQueryString();
    }

    /**
     * Build the filtered query
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = FirstPieceApproval::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        // Apply plant filter if present
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
            $query->where('items.id', $filters['item_id'])
                ->leftJoin('items', 'first_piece_approvals.item_id', '=', 'items.id')
                ->select('first_piece_approvals.*');
        }

        if (!empty($filters['customer'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('customer', $filters['customer']);
            });
        }

        if (!empty($filters['part_no'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('part_number', $filters['part_no']);
            });
        }

        if (!empty($filters['next_proses'])) {
            $query->where('next_proses', $filters['next_proses']);
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

        // ID filter (for direct links from Sortir)
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        return $query;
    }

    /**
     * Validate dimensions and auto-set judgment
     * 
     * @param array $data
     * @param int $itemId
     * @return array Modified data with auto-set judgment
     */
    public function validateDimensions(array $data, int $itemId): array
    {
        $item = Item::find($itemId);
        $allStandards = $this->getConsolidatedStandards();
        $partNum = $item ? $this->normalizePartNumber($item->part_number ?? '') : '';

        if ($item && isset($allStandards[$partNum]) && !empty($data['dimensions'])) {
            $dimensionStandards = $allStandards[$partNum];
            $isAnyInvalid = false;
            $hasValidDimensions = false;

            foreach ($data['dimensions'] as $cavity => $points) {
                if (!is_array($points))
                    continue;

                foreach ($points as $point => $value) {
                    if (isset($dimensionStandards[$point]) && $value !== null && $value !== '' && is_numeric($value)) {
                        $hasValidDimensions = true;
                        $standard = $dimensionStandards[$point];
                        $floatValue = (float) $value;

                        // Use min/max if both are set, otherwise fallback to size +/- tolerance
                        $isPointInvalid = false;

                        if ($standard['min'] !== null && $floatValue < $standard['min']) {
                            $isPointInvalid = true;
                        }
                        if ($standard['max'] !== null && $floatValue > $standard['max']) {
                            $isPointInvalid = true;
                        }

                        // Fallback to size +/- tolerance if no Min/Max is set
                        if ($standard['min'] === null && $standard['max'] === null) {
                            if ($standard['size'] !== null && $standard['tolerance'] !== null) {
                                $lowerBound = $standard['size'] - $standard['tolerance'];
                                $upperBound = $standard['size'] + $standard['tolerance'];
                                if ($floatValue < $lowerBound || $floatValue > $upperBound) {
                                    $isPointInvalid = true;
                                }
                            }
                        }

                        if ($isPointInvalid) {
                            $isAnyInvalid = true;
                            break 2;
                        }
                    }
                }
            }

            if ($hasValidDimensions) {
                if ($isAnyInvalid) {
                    $data['judgment'] = 'NG';
                } else {
                    if (isset($data['total_ng']) && $data['total_ng'] > 0) {
                        $data['judgment'] = 'NG';
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Process dimensions into JSON
     * 
     * @param array|null $dimensions
     * @return string
     */
    private function processDimensions(?array $dimensions): string
    {
        if (empty($dimensions)) {
            return json_encode([]);
        }

        $filteredDimensions = [];
        foreach ($dimensions as $cavity => $points) {
            $filteredPoints = array_filter($points, fn($value) => $value !== null && $value !== '');
            if (!empty($filteredPoints)) {
                $filteredDimensions[$cavity] = $filteredPoints;
            }
        }

        return json_encode($filteredDimensions);
    }

    /**
     * Create new First Piece Approval
     * 
     * @param array $data
     * @param callable $mapExportRow
     * @return array
     */
    public function createChecksheet(array $data, callable $mapExportRow): array
    {
        DB::beginTransaction();
        try {
            // Validate dimensions and auto-set judgment
            $data = $this->validateDimensions($data, (int) $data['item_id']);

            // Process defects
            $defects = $this->processDefects($data);

            // Process dimensions
            $dimensionCheck = $this->processDimensions($data['dimensions'] ?? null);

            $checksheet = FirstPieceApproval::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id),
                'user_id' => auth()->id(),
                'item_id' => $data['item_id'],
                'date' => $data['date'],
                'shift' => $data['shift'],
                'code_machine' => $data['code_machine'],
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'part_weight' => $data['part_weight'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'dimension_check' => $dimensionCheck,
                'cycle_time' => $data['cycle_time'] ?? null,
                'defects' => json_encode($defects),
                'next_proses' => $data['next_proses'] ?? ($data['judgment'] === 'NG' ? 'SORTIR' : null),
                'sap_code' => $data['sap_code'] ?? null,
            ]);

            // Clear manual machine status override
            \App\Models\MachineStatus::updateOrCreate(
                [
                    'plant_id' => $checksheet->plant_id,
                    'type' => 'machine',
                    'number' => $checksheet->code_machine,
                ],
                [
                    'status' => 'normal',
                    'description' => 'Automatically cleared by First Piece Approval input',
                    'created_by' => 'System'
                ]
            );

            DB::commit();

            Log::info('First Piece Approval berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id,
                'plant_id' => $checksheet->plant_id
            ]);

            // Notifications
            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'First Piece Approval');
            }
            $this->notificationService->notifyApprovalRequest($checksheet, 'First Piece Approval');

            return [
                'checksheet' => $checksheet,
                'google_sheets_success' => false,
                'error' => null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat First Piece Approval', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update First Piece Approval
     * 
     * @param int $id
     * @param array $data
     * @return FirstPieceApproval
     */
    public function updateChecksheet(int $id, array $data): FirstPieceApproval
    {
        DB::beginTransaction();
        try {
            $checksheet = FirstPieceApproval::findOrFail($id);

            // Validate dimensions and auto-set judgment
            $data = $this->validateDimensions($data, (int) $data['item_id']);

            // Process dimensions
            $dimensionCheck = $this->processDimensions($data['dimensions'] ?? null);

            // Process defects
            $defects = $this->processDefects($data);

            $updateData = [
                'item_id' => $data['item_id'],
                'date' => $data['date'],
                'shift' => $data['shift'],
                'code_machine' => $data['code_machine'],
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'part_weight' => $data['part_weight'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'dimension_check' => $dimensionCheck,
                'defects' => json_encode($defects),
                'next_proses' => $data['next_proses'] ?? ($data['judgment'] === 'NG' ? 'SORTIR' : null),
                'sap_code' => $data['sap_code'] ?? null,
                'user_id' => $data['user_id'] ?? auth()->id(),
            ];

            // Update created_at and cycle_time if user has authority
            if (auth()->user()->role !== 'inspector') {
                $currentDate = $checksheet->created_at->format('Y-m-d');

                if (!empty($data['jam_after'])) {
                    $updateData['created_at'] = \Carbon\Carbon::parse($currentDate . ' ' . $data['jam_after']);
                }

                if (!empty($data['jam_before']) && !empty($data['jam_after'])) {
                    $before = \Carbon\Carbon::parse($currentDate . ' ' . $data['jam_before']);
                    $after = \Carbon\Carbon::parse($currentDate . ' ' . $data['jam_after']);

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

            // Clear manual machine status override
            \App\Models\MachineStatus::updateOrCreate(
                [
                    'plant_id' => $checksheet->plant_id,
                    'type' => 'machine',
                    'number' => $checksheet->code_machine,
                ],
                [
                    'status' => 'normal',
                    'description' => 'Automatically cleared by First Piece Approval update',
                    'created_by' => 'System'
                ]
            );

            DB::commit();

            Log::info('First Piece Approval berhasil diperbarui', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id,
                'plant_id' => $checksheet->plant_id
            ]);

            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui First Piece Approval', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete First Piece Approval
     * 
     * @param int $id
     * @return bool
     */
    public function deleteChecksheet(int $id): bool
    {
        DB::beginTransaction();
        try {
            $query = FirstPieceApproval::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $checksheet = $query->findOrFail($id);
            $checksheet->delete();

            DB::commit();

            Log::info('First Piece Approval berhasil dihapus', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus First Piece Approval', [
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
     * @return FirstPieceApproval
     */
    public function updateApprovalStatus(int $id, array $data): FirstPieceApproval
    {
        DB::beginTransaction();
        try {
            $checksheet = FirstPieceApproval::findOrFail($id);

            $this->processFullApprovalUpdate($checksheet, $data);

            $checksheet->save();

            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Normalize part number for consistent internal matching
     */
    private function normalizePartNumber(?string $pn): string
    {
        if (is_null($pn) || $pn === '')
            return '';

        // Unify various dashes
        $dashes = ["\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"];
        $pn = str_replace($dashes, '-', $pn);

        // Remove ALL spaces
        $pn = str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', $pn);

        return strtoupper($pn);
    }
}
