<?php

namespace App\Services;

use App\Models\InProcessChecksheet;
use App\Models\Item;
use App\Services\GoogleSheetService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InProcessChecksheetService extends BaseService
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

                        // Flexible conversion that preserves +/- operators and asymmetric tolerances
                        $processValue = function ($val) {
                            $val = $this->normalizeStandardValue($val);
                            if ($val === null || $val === '') return null;
                            
                            // Check if it's an operator-prefixed value (e.g., +1, -0.5) OR asymmetric tolerance (e.g., -0.2/+0.1)
                            if (preg_match('/^[+-]\d+(\.\d+)?(\/[+-]\d+(\.\d+)?)?$/u', $val)) {
                                return $val; // Store as string to preserve operators/slashes
                            }
                            
                            return is_numeric($val) ? (float) $val : $val; // Keep as string if not strictly numeric but might be valid special format
                        };

                        $itemStandards[$pointKey] = [
                            'size' => $processValue($std['size'] ?? null),
                            'tolerance' => $processValue($std['tolerance'] ?? null),
                            'min' => $processValue($std['min'] ?? null),
                            'max' => $processValue($std['max'] ?? null),
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
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = InProcessChecksheet::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        // Apply plant filter if present
        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('in_process_checksheets.date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('in_process_checksheets.date', '<=', $filters['end_date']);
        }

        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('in_process_checksheets.item_id', $filters['item_id']);
        }

        if (!empty($filters['operator_initials'])) {
            $query->where('in_process_checksheets.operator_initials', $filters['operator_initials']);
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

        // QR Raw filter
        if (!empty($filters['qr_raw'])) {
            $query->where('in_process_checksheets.qrcode', 'like', "%{$filters['qr_raw']}%");
        }

        if (!empty($filters['view_mode']) && $filters['view_mode'] === 'verifikasi') {
            $query->where(function ($q) {
                $q->whereNotNull('in_process_checksheets.qrcode')
                  ->orWhereNotNull('in_process_checksheets.unique_code_id')
                  ->orWhereIn('in_process_checksheets.scan_method', ['hardware', 'camera']);
            });
        } elseif (!empty($filters['entry_method'])) {
            if ($filters['entry_method'] === 'verification' || $filters['entry_method'] === 'qr') {
                $query->where(function ($q) {
                    $q->whereNotNull('in_process_checksheets.qrcode')
                      ->orWhereNotNull('in_process_checksheets.unique_code_id')
                      ->orWhereIn('in_process_checksheets.scan_method', ['hardware', 'camera']);
                });
            } elseif ($filters['entry_method'] === 'regular' || $filters['entry_method'] === 'manual') {
                $query->where(function ($q) {
                    $q->whereNull('in_process_checksheets.qrcode')
                      ->whereNull('in_process_checksheets.unique_code_id')
                      ->where(function ($sub) {
                          $sub->whereNull('in_process_checksheets.scan_method')
                              ->orWhere('in_process_checksheets.scan_method', 'manual');
                      });
                });
            }
        }

        if (!empty($filters['shift'])) {
            $query->where('in_process_checksheets.shift', $filters['shift']);
        }

        if (!empty($filters['tujuan'])) {
            $query->where('in_process_checksheets.tujuan', $filters['tujuan']);
        }

        return $query;
    }

    /**
     * Normalize standard value string (e.g., replace comma with dot, normalize dashes)
     * 
     * @param mixed $val
     * @return string|null
     */
    private function normalizeStandardValue($val)
    {
        if ($val === null || $val === '') return null;
        $val = (string)$val;
        // Replace commas with dots
        $val = str_replace(',', '.', $val);
        // Replace non-standard dashes with standard hyphen-minus
        $val = str_replace(["\u{2012}", "\u{2013}", "\u{2014}", "\u{2212}"], '-', $val);
        // Trim whitespace
        return trim($val);
    }

    /**
     * Validate dimensions and auto-set judgment
     * 
     * @param array $data
     * @param mixed $itemId
     * @return array Modified data with auto-set judgment
     */
    public function validateDimensions(array $data, $itemId): array
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
                        $std = $dimensionStandards[$point];
                        $floatValue = (float) $value;
                        $isPointNG = false;
                        $epsilon = 0.00001;

                        // NEW: Resolve baseline size for offset calculation
                        $baseSizeStr = $this->normalizeStandardValue($std['size'] ?? null);
                        $baseSize = ($baseSizeStr !== null && !str_starts_with($baseSizeStr, '+') && !str_starts_with($baseSizeStr, '-')) ? (float)$baseSizeStr : null;

                        // 1. Check Absolute Min/Max
                        if (($std['min'] ?? null) !== null && $std['min'] !== '') {
                            $minBound = (float)$std['min'];
                            if ($floatValue < ($minBound - $epsilon)) $isPointNG = true;
                        }
                        if (!$isPointNG && ($std['max'] ?? null) !== null && $std['max'] !== '') {
                            $maxBound = (float)$std['max'];
                            if ($floatValue > ($maxBound + $epsilon)) $isPointNG = true;
                        }

                        // 2. Check Size +/- Tolerance
                        if (!$isPointNG && ($std['size'] ?? null) !== null && ($std['tolerance'] ?? null) !== null && $std['size'] !== '' && $std['tolerance'] !== '') {
                            $szStr = $this->normalizeStandardValue($std['size']);
                            if (!str_starts_with($szStr, '+') && !str_starts_with($szStr, '-')) {
                                $base = (float)$szStr;
                                $tol = $this->normalizeStandardValue($std['tolerance']);
                                $lb = $base; $ub = $base;
                                
                                if (str_contains($tol, '/')) {
                                    $parts = explode('/', $tol);
                                    foreach ($parts as $p) {
                                        $p = $this->normalizeStandardValue($p);
                                        $fv = (float)$p;
                                        if (str_starts_with($p, '+') || $fv > 0) $ub = $base + abs($fv);
                                        elseif (str_starts_with($p, '-') || $fv < 0) $lb = $base - abs($fv);
                                    }
                                } elseif (str_starts_with($tol, '+')) {
                                    $ub = $base + (float)substr($tol, 1);
                                } elseif (str_starts_with($tol, '-')) {
                                    $lb = $base + (float)$tol;
                                } else {
                                    $tv = (float)$tol;
                                    $lb = $base - $tv; $ub = $base + $tv;
                                }
                                
                                if ($floatValue < ($lb - $epsilon) || $floatValue > ($ub + $epsilon)) $isPointNG = true;
                            }
                        }

                        // 3. Check Special Size (prefix)
                        if (!$isPointNG && ($std['size'] ?? null) !== null && $std['size'] !== '') {
                            $szStr = $this->normalizeStandardValue($std['size']);
                            if (str_starts_with($szStr, '+') || str_starts_with($szStr, '-')) {
                                $op = $szStr[0];
                                $bound = (float)substr($szStr, 1);
                                if ($op === '+' && $floatValue < ($bound - $epsilon)) $isPointNG = true;
                                elseif ($op === '-' && $floatValue > ($bound + $epsilon)) $isPointNG = true;
                            }
                        }

                        // Fallback to Size +/- Tolerance
                        if (!$isPointNG && ($std['min'] ?? null) === null && ($std['max'] ?? null) === null && ($std['size'] ?? null) !== null && ($std['tolerance'] ?? null) !== null) {
                            $sizeStr = $this->normalizeStandardValue($std['size']);
                            if (!str_starts_with($sizeStr, '+') && !str_starts_with($sizeStr, '-')) {
                                $size = (float)$sizeStr;
                                $tol = $this->normalizeStandardValue($std['tolerance']);
                                $lowerBound = $size;
                                $upperBound = $size;

                                if (str_contains($tol, '/')) {
                                    $parts = explode('/', $tol);
                                    foreach ($parts as $p) {
                                        $p = $this->normalizeStandardValue($p);
                                        $fVal = (float)$p;
                                        if (str_starts_with($p, '+') || $fVal > 0) {
                                            $upperBound = $size + abs($fVal);
                                        } elseif (str_starts_with($p, '-') || $fVal < 0) {
                                            $lowerBound = $size - abs($fVal);
                                        }
                                    }
                                } elseif (str_starts_with($tol, '+')) {
                                    $upperBound = $size + (float)substr($tol, 1);
                                } elseif (str_starts_with($tol, '-')) {
                                    $lowerBound = $size + (float)$tol; // Negative value handled by parseFloat equivalent
                                } else {
                                    $tVal = (float)$tol;
                                    $lowerBound = $size - $tVal;
                                    $upperBound = $size + $tVal;
                                }

                                if ($floatValue < ($lowerBound - $epsilon) || $floatValue > ($upperBound + $epsilon)) {
                                    $isPointNG = true;
                                }
                            }
                        }

                        if ($isPointNG) {
                            $isAnyInvalid = true;
                            // We don't break here because we want all matching inputs to be flagged (thought it's simpler to break for backend judgment)
                            // But for backend judgment calculation, one NG is enough.
                            break 2;
                        }
                    }
                }
            }

            if ($hasValidDimensions) {
                if ($isAnyInvalid) {
                    $data['judgment'] = 'NG';
                } else {
                    if (isset($data['total_ng']) && (int)$data['total_ng'] > 0) {
                        $data['judgment'] = 'NG';
                    } else {
                        $data['judgment'] = 'OK';
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
     * Create new in-process checksheet
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

            $checksheet = InProcessChecksheet::create([
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
                'remarks' => $data['remarks'] ?? null,
                'dimension_check' => $dimensionCheck,
                'cycle_time' => $data['cycle_time'] ?? null,
                'defects' => json_encode($defects),
                'part_weight' => $data['part_weight'] ?? null,
                'qrcode' => $data['qrcode'] ?? null,
                'part_code' => $data['part_code'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'unique_code_id' => $data['unique_code_id'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'scan_method' => $data['scan_method'] ?? 'manual',
                'next_proses' => ($data['judgment'] === 'NG') 
                    ? ($data['next_proses'] ?: 'SORTIR') 
                    : null,
                'tujuan' => $data['tujuan'] ?? null,
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
                    'description' => 'Automatically cleared by checksheet input',
                    'created_by' => 'System'
                ]
            );

            DB::commit();

            Log::info('Checksheet In Process berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id,
                'plant_id' => $checksheet->plant_id
            ]);

            // Notifications
            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding($checksheet, 'In Process');
            }

            // Try to send to Google Sheets
            $googleSheetsSuccess = false;
            $error = null;

            /*
            try {
                $this->googleSheetService->setSheetName('Sheet2');
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
            Log::error('Gagal membuat checksheet In Process', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update in-process checksheet
     * 
     * @param int $id
     * @param array $data
     * @return InProcessChecksheet
     */
    public function updateChecksheet(int $id, array $data): InProcessChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = InProcessChecksheet::findOrFail($id);

            // Validate dimensions and auto-set judgment
            $data = $this->validateDimensions($data, (int) $data['item_id']);

            // Process dimensions
            $dimensionCheck = $this->processDimensions($data['dimensions'] ?? null);

            // Process defects (Fix: Enable defect updates)
            $defects = $this->processDefects($data);

            $updateData = [
                'item_id' => $data['item_id'],
                'date' => $data['date'],
                'shift' => $data['shift'],
                'code_machine' => $data['code_machine'] ?? null,
                'total_qty' => $data['total_qty'],
                'sampling_qty' => $data['sampling_qty'],
                'total_ok' => $data['total_ok'],
                'total_ng' => $data['total_ng'],
                'judgment' => $data['judgment'],
                'operator_initials' => $data['operator_initials'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'dimension_check' => $dimensionCheck,
                'defects' => $defects,
                'part_weight' => $data['part_weight'] ?? null,
                'next_proses' => ($data['judgment'] === 'NG') 
                    ? ($data['next_proses'] ?: 'SORTIR') 
                    : null,
                'qrcode' => $data['qrcode'] ?? null,
                'part_code' => $data['part_code'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'unique_code_id' => $data['unique_code_id'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'scan_method' => $data['scan_method'] ?? $checksheet->scan_method,
            ];

            // Allow manual correction of inspector if provided
            if (isset($data['user_id'])) {
                $updateData['user_id'] = $data['user_id'];
            }

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
                    $updateData['cycle_time'] = $data['cycle_time'] ?? $checksheet->cycle_time;
                }
            } else {
                $updateData['cycle_time'] = $data['cycle_time'] ?? $checksheet->cycle_time;
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
                    'description' => 'Automatically cleared by checksheet update',
                    'created_by' => 'System'
                ]
            );

            DB::commit();

            Log::info('Checksheet In Process berhasil diperbarui', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id,
                'plant_id' => $checksheet->plant_id
            ]);

            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui checksheet In Process', [
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
            $query = InProcessChecksheet::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $checksheet = $query->findOrFail($id);
            $checksheet->delete();

            DB::commit();

            Log::info('Checksheet In Process berhasil dihapus', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus checksheet In Process', [
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
     * @return InProcessChecksheet
     */
    public function updateApprovalStatus(int $id, array $data): InProcessChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = InProcessChecksheet::findOrFail($id);

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
     * Get daily recap for verification data
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDailyRecap(array $filters)
    {
        $query = InProcessChecksheet::with('item')
            ->whereNotNull('qrcode') // Verification only
            ->select(
                'item_id',
                'shift',
                DB::raw('MIN(total_qty) as packing_size'),
                DB::raw('COUNT(*) as total_packing'),
                DB::raw('SUM(total_qty) as total_qty_sum'),
                DB::raw('SUM(total_qty - total_ng) as total_ok_sum'),
                DB::raw('SUM(total_ng) as total_ng_sum')
            )
            ->groupBy('item_id', 'shift');

        if (isset($filters['plant'])) {
            $query->where('plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        } else {
            $query->whereDate('date', now()->toDateString());
        }

        if (!empty($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        return $query->get();
    }

    /**
     * Normalize part number for consistent internal matching
     * Removes ALL spaces and unifies EN/EM dashes to hyphen
     */
    private function normalizePartNumber(?string $pn): string
    {
        if (is_null($pn) || $pn === '')
            return '';

        // Unify various dashes: EN DASH (e2 80 93), EM DASH (e2 80 94), MINUS SIGN (e2 88 92)
        $dashes = ["\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"];
        $pn = str_replace($dashes, '-', $pn);

        // Remove ALL spaces (regular, non-breaking, etc.)
        $pn = str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', $pn);

        return strtoupper($pn);
    }
}
