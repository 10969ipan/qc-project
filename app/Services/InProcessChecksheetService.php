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

        if (!empty($filters['hidden_item_ids']) && is_array($filters['hidden_item_ids'])) {
            $query->whereNotIn('in_process_checksheets.item_id', $filters['hidden_item_ids']);
        }

        if (!empty($filters['hide_ng_rows']) && $filters['hide_ng_rows'] == '1') {
            if (isset($filters['ng_dimensi_checksheet_ids']) && is_array($filters['ng_dimensi_checksheet_ids'])) {
                if (!empty($filters['ng_dimensi_checksheet_ids'])) {
                    $query->whereNotIn('in_process_checksheets.id', $filters['ng_dimensi_checksheet_ids']);
                }
            } else {
                $plantId = $filters['plant'] ?? null;
                $ngDimIds = $this->getDimensionNgChecksheetIds($plantId, $filters);
                if (!empty($ngDimIds)) {
                    $query->whereNotIn('in_process_checksheets.id', $ngDimIds);
                }
            }
        }

        if (!empty($filters['hide_no_dimension_rows']) && $filters['hide_no_dimension_rows'] == '1') {
            if (isset($filters['no_dimension_checksheet_ids']) && is_array($filters['no_dimension_checksheet_ids'])) {
                if (!empty($filters['no_dimension_checksheet_ids'])) {
                    $query->whereNotIn('in_process_checksheets.id', $filters['no_dimension_checksheet_ids']);
                }
            } else {
                $plantId = $filters['plant'] ?? null;
                $noDimIds = $this->getNoDimensionChecksheetIds($plantId, $filters);
                if (!empty($noDimIds)) {
                    $query->whereNotIn('in_process_checksheets.id', $noDimIds);
                }
            }
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

        // QR Raw filter (Prioritize B-Tree index lookup for exact QR/Unique/SAP code scans)
        if (!empty($filters['qr_raw'])) {
            $qr = trim($filters['qr_raw']);
            $query->where(function ($q) use ($qr) {
                $q->where('in_process_checksheets.qrcode', $qr)
                  ->orWhere('in_process_checksheets.unique_code_id', $qr)
                  ->orWhere('in_process_checksheets.sap_code', $qr)
                  ->orWhere('in_process_checksheets.qrcode', 'like', "%{$qr}%");
            });
        }

        // Entry Method / View Mode filter (Verification vs Regular)
        $viewMode = $filters['view_mode'] ?? null;
        $entryMethod = $filters['entry_method'] ?? null;

        if ($viewMode === 'verifikasi' || $entryMethod === 'verification' || $entryMethod === 'qr') {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('in_process_checksheets.qrcode')
                        ->where('in_process_checksheets.qrcode', '!=', '');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('in_process_checksheets.unique_code_id')
                        ->where('in_process_checksheets.unique_code_id', '!=', '');
                })->orWhereIn('in_process_checksheets.scan_method', ['hardware', 'camera']);
            });
        } else {
            // Default: strict regular manual input entries only (excludes verification scan data)
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('in_process_checksheets.qrcode')
                        ->orWhere('in_process_checksheets.qrcode', '');
                })->where(function ($sub) {
                    $sub->whereNull('in_process_checksheets.unique_code_id')
                        ->orWhere('in_process_checksheets.unique_code_id', '');
                })->where(function ($sub) {
                    $sub->whereNull('in_process_checksheets.scan_method')
                        ->orWhere('in_process_checksheets.scan_method', 'manual');
                });
            });
        }

        if (!empty($filters['shift'])) {
            $query->where('in_process_checksheets.shift', $filters['shift']);
        }

        if (!empty($filters['tujuan'])) {
            $query->where('in_process_checksheets.tujuan', $filters['tujuan']);
        }

        if (!empty($filters['code_machine'])) {
            $query->where('in_process_checksheets.code_machine', $filters['code_machine']);
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
        // Remove plus-minus signs
        $val = str_replace(["±", "\u{00B1}"], "", $val);
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
        $dimensionStandards = null;

        if ($item && !empty($item->dimension_standards)) {
            $itemStandards = [];
            foreach ($item->dimension_standards as $index => $std) {
                $hasSizeTol = isset($std['size']) && $std['size'] !== '' && isset($std['tolerance']) && $std['tolerance'] !== '';
                $hasMinMax = (isset($std['min']) && $std['min'] !== '') || (isset($std['max']) && $std['max'] !== '');

                if (is_array($std) && ($hasSizeTol || $hasMinMax)) {
                    $pointKey = (string) ($index + 1);
                    $processValue = function ($val) {
                        $val = $this->normalizeStandardValue($val);
                        if ($val === null || $val === '') return null;
                        if (preg_match('/^[+-]\d+(\.\d+)?(\/[+-]\d+(\.\d+)?)?$/u', $val)) {
                            return $val;
                        }
                        return is_numeric($val) ? (float) $val : $val;
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
                $dimensionStandards = $itemStandards;
            }
        }

        if (!$dimensionStandards && $item) {
            $allStandards = $this->getConsolidatedStandards();
            $partNum = $this->normalizePartNumber($item->part_number ?? "");
            $dimensionStandards = $allStandards[$partNum] ?? null;
        }

        $rawCheck = $data['dimension_check'] ?? $data['dimensions'] ?? null;
        if (is_string($rawCheck)) {
            $rawCheck = json_decode($rawCheck, true);
        }

        if ($item && !empty($dimensionStandards) && !empty($rawCheck) && is_array($rawCheck)) {
            $isAnyInvalid = false;
            $hasValidDimensions = false;
            $epsilon = 0.0001;

            foreach ($rawCheck as $cavity => $points) {
                if (!is_array($points)) continue;

                foreach ($points as $point => $valOrArr) {
                    $pointKey = (string)$point;
                    $std = $dimensionStandards[$pointKey] ?? null;

                    $valuesToCheck = is_array($valOrArr) ? array_values($valOrArr) : [$valOrArr];

                    foreach ($valuesToCheck as $val) {
                        if ($val === null || $val === '') continue;

                        $valStr = str_replace(',', '.', (string)$val);
                        if (!is_numeric($valStr)) continue;

                        $floatValue = (float)$valStr;
                        $hasValidDimensions = true;

                        if (!$std) continue;

                        $isPointNG = false;

                        if (isset($std['min']) || isset($std['max'])) {
                            if (isset($std['min']) && $std['min'] !== null && $floatValue < ((float)$std['min'] - $epsilon)) {
                                $isPointNG = true;
                            }
                            if (isset($std['max']) && $std['max'] !== null && $floatValue > ((float)$std['max'] + $epsilon)) {
                                $isPointNG = true;
                            }
                        } elseif (isset($std['size']) && $std['size'] !== null && isset($std['tolerance']) && $std['tolerance'] !== null) {
                            $size = (float)$std['size'];
                            $tol = (string)$std['tolerance'];

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
                                $lowerBound = $size + (float)$tol;
                            } else {
                                $tVal = (float)$tol;
                                $lowerBound = $size - $tVal;
                                $upperBound = $size + $tVal;
                            }

                            if ($floatValue < ($lowerBound - $epsilon) || $floatValue > ($upperBound + $epsilon)) {
                                $isPointNG = true;
                            }
                        }

                        if ($isPointNG) {
                            $isAnyInvalid = true;
                            break 3;
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
     * Determine if a checksheet row has NG Dimensi (Dimension check NG specifically)
     * 
     * @param InProcessChecksheet|array $checksheet
     * @param array|null $consolidatedStandards
     * @return bool
     */
    public function isDimensionNg($checksheet, ?array $consolidatedStandards = null): bool
    {
        if (!$checksheet) return false;

        $defects = is_object($checksheet) ? $checksheet->defects : ($checksheet['defects'] ?? []);
        if (is_string($defects)) {
            $defects = json_decode($defects, true);
        }
        if (is_array($defects)) {
            foreach ($defects as $d) {
                $rawType = is_array($d) ? ($d['type'] ?? '') : (is_string($d) ? $d : '');
                $key = strtolower(trim((string)$rawType));
                if (in_array($key, ['dimensi', 'dimension', 'ng dimensi'])) {
                    return true;
                }
            }
        }

        $rawCheck = is_object($checksheet) ? $checksheet->dimension_check : ($checksheet['dimension_check'] ?? []);
        if (is_string($rawCheck)) {
            $rawCheck = json_decode($rawCheck, true);
        }
        if (empty($rawCheck) || !is_array($rawCheck)) {
            return false;
        }

        $item = is_object($checksheet) ? $checksheet->item : null;
        $itemId = is_object($checksheet) ? $checksheet->item_id : ($checksheet['item_id'] ?? null);
        if (!$item && $itemId) {
            $item = Item::find($itemId);
        }

        $dimensionStandards = null;
        if ($item && !empty($item->dimension_standards) && is_array($item->dimension_standards)) {
            $itemStandards = [];
            foreach ($item->dimension_standards as $index => $std) {
                if (is_array($std)) {
                    $pointKey = (string)($std['point'] ?? ($index + 1));
                    $itemStandards[$pointKey] = [
                        'size' => $std['size'] ?? null,
                        'tolerance' => $std['tolerance'] ?? null,
                        'min' => $std['min'] ?? null,
                        'max' => $std['max'] ?? null,
                    ];
                }
            }
            if (!empty($itemStandards)) {
                $dimensionStandards = $itemStandards;
            }
        }

        if (!$dimensionStandards && $item) {
            if ($consolidatedStandards === null) {
                $consolidatedStandards = $this->getConsolidatedStandards();
            }
            $partNum = $this->normalizePartNumber($item->part_number ?? '');
            $dimensionStandards = $consolidatedStandards[$partNum] ?? null;
        }

        if (empty($dimensionStandards)) {
            return false;
        }

        $epsilon = 0.00001;

        $checkValueNG = function($val, $std) use ($epsilon) {
            if ($val === '-' || $val === '' || $val === null || !is_numeric($val) || empty($std)) {
                return false;
            }
            $fVal = (float)$val;
            $normStd = function($v) {
                if ($v === null || $v === '') return '';
                $s = str_replace(',', '.', (string)$v);
                $s = str_replace(["\u{2012}", "\u{2013}", "\u{2014}", "\u{2212}"], '-', $s);
                $s = str_replace(['Ø', '⌀', 'ø', '±', "\u{00B1}", "\u{00D8}", "\u{00F8}", "\u{2300}"], '', $s);
                return trim($s);
            };

            // 1. Check Min / Max
            if (($std['min'] ?? null) !== null && $std['min'] !== '') {
                $minBound = (float)$normStd($std['min']);
                if ($fVal < ($minBound - $epsilon)) return true;
            }
            if (($std['max'] ?? null) !== null && $std['max'] !== '') {
                $maxBound = (float)$normStd($std['max']);
                if ($fVal > ($maxBound + $epsilon)) return true;
            }

            // 2. Check Size +/- Tolerance
            if (($std['size'] ?? null) !== null && ($std['tolerance'] ?? null) !== null && $std['size'] !== '' && $std['tolerance'] !== '') {
                $szStr = $normStd($std['size']);
                if (!str_starts_with($szStr, '+') && !str_starts_with($szStr, '-')) {
                    $base = (float)$szStr;
                    $tol = $normStd($std['tolerance']);
                    $lb = $base; $ub = $base;
                    if (str_contains($tol, '/')) {
                        $parts = explode('/', $tol);
                        foreach ($parts as $p) {
                            $p = $normStd($p);
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
                    if ($fVal < ($lb - $epsilon) || $fVal > ($ub + $epsilon)) return true;
                }
            }

            // 3. Check Special Size (operator prefix)
            if (($std['size'] ?? null) !== null && $std['size'] !== '') {
                $szStr = $normStd($std['size']);
                if (str_starts_with($szStr, '+') || str_starts_with($szStr, '-')) {
                    $op = $szStr[0];
                    $bound = (float)substr($szStr, 1);
                    if ($op === '+' && $fVal < ($bound - $epsilon)) return true;
                    if ($op === '-' && $fVal > ($bound + $epsilon)) return true;
                }
            }

            return false;
        };

        foreach ($rawCheck as $cavKey => $points) {
            if (!is_array($points)) continue;
            foreach ($points as $pKey => $valOrArr) {
                $pointKey = (string)$pKey;
                $std = $dimensionStandards[$pointKey] ?? null;
                if (!$std) continue;

                $valuesToCheck = is_array($valOrArr) ? $valOrArr : [$valOrArr];
                foreach ($valuesToCheck as $v) {
                    if ($checkValueNG($v, $std)) {
                        return true;
                    }
                }
            }
        }

        return false;
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
            if (!is_array($points)) continue;
            $filteredPoints = [];
            foreach ($points as $point => $valOrArr) {
                if (is_array($valOrArr)) {
                    $cleanedArr = array_filter($valOrArr, fn($v) => $v !== null && $v !== '');
                    if (!empty($cleanedArr)) {
                        $filteredPoints[$point] = $cleanedArr;
                    }
                } else {
                    if ($valOrArr !== null && $valOrArr !== '') {
                        $filteredPoints[$point] = $valOrArr;
                    }
                }
            }
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
            \App\Models\MachineStatus::withoutGlobalScope('plant')->updateOrCreate(
                [
                    'plant_id' => $checksheet->plant_id,
                    'type' => 'machine',
                    'number' => (int) $checksheet->code_machine,
                ],
                [
                    'status' => 'normal',
                    'description' => 'Automatically cleared by checksheet input',
                    'created_by' => 'System'
                ]
            );

            DB::commit();

            // Clear filter cache for this plant so dropdowns refresh immediately
            \Illuminate\Support\Facades\Cache::forget("in_proc_filter_init_{$checksheet->plant_id}");
            \Illuminate\Support\Facades\Cache::forget("in_proc_filter_mach_{$checksheet->plant_id}");

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
            \App\Models\MachineStatus::withoutGlobalScope('plant')->updateOrCreate(
                [
                    'plant_id' => $checksheet->plant_id,
                    'type' => 'machine',
                    'number' => (int) $checksheet->code_machine,
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

    /**
     * Get IDs of checksheets that have NG Dimensi for a given plant/filter set
     * 
     * @param mixed $plantId
     * @param array $filters
     * @return array
     */
    public function getDimensionNgChecksheetIds($plantId, array $filters = []): array
    {
        $resolvedPlant = $this->resolvePlantId($plantId);
        $cacheKey = "in_proc_dim_ng_ids_" . ($resolvedPlant ?? 'global') . "_" . md5(json_encode([
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null,
            $filters['item_id'] ?? null,
            $filters['search'] ?? null,
        ]));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function() use ($resolvedPlant, $filters) {
            $standards = $this->getConsolidatedStandards();
            
            $query = InProcessChecksheet::where('plant_id', $resolvedPlant)
                ->where(function($q) {
                    $q->where('judgment', 'NG')
                      ->orWhere('total_ng', '>', 0)
                      ->orWhere(function($sub) {
                          $sub->whereNotNull('dimension_check')
                              ->where('dimension_check', '!=', '[]')
                              ->where('dimension_check', '!=', '{}');
                      });
                });

            if (!empty($filters['start_date'])) {
                $query->whereDate('date', '>=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $query->whereDate('date', '<=', $filters['end_date']);
            }
            if (!empty($filters['item_id'])) {
                $query->where('item_id', $filters['item_id']);
            }

            $candidates = $query->get(['id', 'item_id', 'dimension_check', 'defects']);
            $ngIds = [];

            foreach ($candidates as $c) {
                if ($this->isDimensionNg($c, $standards)) {
                    $ngIds[] = $c->id;
                }
            }

            return $ngIds;
        });
    }

    /**
     * Get distinct item IDs that have NG Dimensi history
     * 
     * @param mixed $plantId
     * @return array
     */
    public function getDimensionNgItemIds($plantId): array
    {
        $resolvedPlant = $this->resolvePlantId($plantId);
        $cacheKey = "in_proc_dim_ng_item_ids_" . ($resolvedPlant ?? 'global');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($resolvedPlant) {
            $standards = $this->getConsolidatedStandards();
            
            $candidates = InProcessChecksheet::where('plant_id', $resolvedPlant)
                ->whereNotNull('item_id')
                ->where(function($q) {
                    $q->where('judgment', 'NG')
                      ->orWhere('total_ng', '>', 0)
                      ->orWhere(function($sub) {
                          $sub->whereNotNull('dimension_check')
                              ->where('dimension_check', '!=', '[]')
                              ->where('dimension_check', '!=', '{}');
                      });
                })->get(['id', 'item_id', 'dimension_check', 'defects']);

            $itemIds = [];
            foreach ($candidates as $c) {
                if ($this->isDimensionNg($c, $standards)) {
                    $itemIds[$c->item_id] = true;
                }
            }

            return array_keys($itemIds);
        });
    }

    /**
     * Determine if a checksheet row has NO dimension measurement values (Visual Only row)
     * 
     * @param InProcessChecksheet|array $checksheet
     * @return bool
     */
    public function isNoDimensionRow($checksheet): bool
    {
        if (!$checksheet) return false;

        $rawCheck = is_object($checksheet) ? $checksheet->dimension_check : ($checksheet['dimension_check'] ?? []);
        if (is_string($rawCheck)) {
            $rawCheck = json_decode($rawCheck, true);
        }
        if (empty($rawCheck) || !is_array($rawCheck)) {
            return true;
        }

        foreach ($rawCheck as $cavPoints) {
            if (is_array($cavPoints)) {
                foreach ($cavPoints as $val) {
                    if (is_array($val)) {
                        foreach ($val as $subV) {
                            if ($subV !== null && $subV !== '' && $subV !== '-') {
                                return false;
                            }
                        }
                    } else {
                        if ($val !== null && $val !== '' && $val !== '-') {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get IDs of checksheets that have NO dimension check measurements for a given plant/filter set
     * 
     * @param mixed $plantId
     * @param array $filters
     * @return array
     */
    public function getNoDimensionChecksheetIds($plantId, array $filters = []): array
    {
        $resolvedPlant = $this->resolvePlantId($plantId);
        $cacheKey = "in_proc_no_dim_ids_" . ($resolvedPlant ?? 'global') . "_" . md5(json_encode([
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null,
            $filters['item_id'] ?? null,
            $filters['search'] ?? null,
        ]));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function() use ($resolvedPlant, $filters) {
            $query = InProcessChecksheet::where('plant_id', $resolvedPlant);

            if (!empty($filters['start_date'])) {
                $query->whereDate('date', '>=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $query->whereDate('date', '<=', $filters['end_date']);
            }
            if (!empty($filters['item_id'])) {
                $query->where('item_id', $filters['item_id']);
            }

            $candidates = $query->get(['id', 'dimension_check']);
            $noDimIds = [];

            foreach ($candidates as $c) {
                if ($this->isNoDimensionRow($c)) {
                    $noDimIds[] = $c->id;
                }
            }

            return $noDimIds;
        });
    }
}
