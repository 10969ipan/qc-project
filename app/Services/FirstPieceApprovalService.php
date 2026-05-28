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

    public function __construct(
        GoogleSheetService $googleSheetService,
        NotificationService $notificationService,
    ) {
        $this->googleSheetService = $googleSheetService;
        $this->notificationService = $notificationService;

        // Hardcoded dimension standards -- ... rest of standards ...
        $this->hardcodedStandards = [
            "53102-K0L -D002" => [
                "1" => ["size" => 5, "tolerance" => 0.2],
                "2" => ["size" => 10, "tolerance" => 0.2],
                "3" => ["size" => 10, "tolerance" => 0.5],
                "4" => ["size" => 20.5, "tolerance" => 0.2],
                "5" => ["size" => 20, "tolerance" => 0.2],
            ],
            "1PA - F836B - 00" => [
                "1" => ["size" => 25, "tolerance" => 0.2],
                "2" => ["size" => 21, "tolerance" => 0.4],
                "3" => ["size" => 3.2, "tolerance" => 0.2],
                "4" => ["size" => 24, "tolerance" => 0.4],
            ],
            "53209-K3V-N100" => [
                "1" => ["size" => 10, "tolerance" => 0.2],
                "2" => ["size" => 10, "tolerance" => 0.2],
                "3" => ["size" => 10, "tolerance" => 0.2],
                "4" => ["size" => 10, "tolerance" => 0.2],
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

        $dbItems = Item::whereNotNull("dimension_standards")->get();
        foreach ($dbItems as $item) {
            $partNum = $this->normalizePartNumber($item->part_number ?? "");
            if ($partNum !== "" && !empty($item->dimension_standards)) {
                $itemStandards = [];
                foreach ($item->dimension_standards as $index => $std) {
                    $hasSizeTol =
                        isset($std["size"]) &&
                        $std["size"] !== "" &&
                        isset($std["tolerance"]) &&
                        $std["tolerance"] !== "";
                    $hasMinMax =
                        (isset($std["min"]) && $std["min"] !== "") ||
                        (isset($std["max"]) && $std["max"] !== "");

                    if (is_array($std) && ($hasSizeTol || $hasMinMax)) {
                        $pointKey = (string) ($index + 1);

                        // Robust float conversion helper
                        $toFloat = function ($val) {
                            if ($val === null || $val === "") {
                                return null;
                            }
                            $val = (string) $val;
                            // Remove ±, +, and leading spaces
                            $val = str_replace(["±", "+"], "", $val);
                            $val = str_replace(",", ".", $val);
                            $val = trim($val);
                            return is_numeric($val) ? (float) $val : null;
                        };

                        $itemStandards[$pointKey] = [
                            "size" => $toFloat($std["size"] ?? null),
                            "tolerance" => $toFloat($std["tolerance"] ?? null),
                            "min" => $toFloat($std["min"] ?? null),
                            "max" => $toFloat($std["max"] ?? null),
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
        return $this->buildFilteredQuery($filters)
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Build the filtered query
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(
        array $filters,
    ): \Illuminate\Database\Eloquent\Builder {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = FirstPieceApproval::with("item")
            ->orderBy("date", "desc")
            ->orderBy("created_at", "desc");

        // Apply plant filter if present
        if (isset($filters["plant"])) {
            $query->where(
                $query->getModel()->getTable() . ".plant_id",
                $this->resolvePlantId($filters["plant"]),
            );
        }

        if (!empty($filters["start_date"])) {
            $query->whereDate(
                "first_piece_approvals.date",
                ">=",
                $filters["start_date"],
            );
        }

        if (!empty($filters["end_date"])) {
            $query->whereDate(
                "first_piece_approvals.date",
                "<=",
                $filters["end_date"],
            );
        }

        if (!empty($filters["approval_status"])) {
            $this->applyApprovalStatusFilter(
                $query,
                $filters["approval_status"],
            );
        }

        if (!empty($filters["item_id"])) {
            $query->where("first_piece_approvals.item_id", $filters["item_id"]);
        }

        if (!empty($filters["operator_initials"])) {
            $query->where(
                "first_piece_approvals.operator_initials",
                $filters["operator_initials"],
            );
        }

        if (!empty($filters["customer"])) {
            $query->whereHas("item", function ($q) use ($filters) {
                $q->where("customer", $filters["customer"]);
            });
        }

        if (!empty($filters["part_no"])) {
            $query->whereHas("item", function ($q) use ($filters) {
                $q->where("part_number", $filters["part_no"]);
            });
        }

        if (!empty($filters["next_proses"])) {
            $query->where("next_proses", $filters["next_proses"]);
        }

        if (!empty($filters["search"])) {
            $searchTerm = $filters["search"];
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas("item", function ($itemQuery) use ($searchTerm) {
                    $itemQuery
                        ->where("name", "like", "%{$searchTerm}%")
                        ->orWhere("customer", "like", "%{$searchTerm}%")
                        ->orWhere("part_number", "like", "%{$searchTerm}%");
                })->orWhere("operator_initials", "like", "%{$searchTerm}%");
            });
        }

        // ID filter (for direct links from Sortir)
        if (!empty($filters["id"])) {
            $query->where("id", $filters["id"]);
        }

        if (!empty($filters["shift"])) {
            $query->where("first_piece_approvals.shift", $filters["shift"]);
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
        if ($val === null || $val === "") {
            return null;
        }
        $val = (string) $val;
        // Replace commas with dots
        $val = str_replace(",", ".", $val);
        // Replace non-standard dashes with standard hyphen-minus
        $val = str_replace(
            ["\u{2012}", "\u{2013}", "\u{2014}", "\u{2212}"],
            "-",
            $val,
        );
        // Trim whitespace
        return trim($val);
    }

    /**
     * Validate dimensions and auto-set judgment
     *
     * @param array $data
     * @param int $itemId
     * @return array Modified data with auto-set judgment
     */
    public function validateDimensions(array $data, $itemId): array
    {
        $item = Item::find($itemId);
        $allStandards = $this->getConsolidatedStandards();
        $partNum = $item
            ? $this->normalizePartNumber($item->part_number ?? "")
            : "";

        \Log::info("Validating FPA Dimensions", [
            "item_id" => $itemId,
            "part_number" => $partNum,
            "has_standards" => isset($allStandards[$partNum]),
            "dimensions_count" => count($data["dimensions"] ?? []),
        ]);

        if (
            $item &&
            isset($allStandards[$partNum]) &&
            !empty($data["dimensions"])
        ) {
            $dimensionStandards = $allStandards[$partNum];
            $isAnyInvalid = false;
            $hasValidDimensions = false;
            $okPointsCount = 0;
            $ngPointsCount = 0;

            foreach ($data["dimensions"] as $cavity => $points) {
                if (!is_array($points)) {
                    continue;
                }

                foreach ($points as $point => $value) {
                    if (
                        isset($dimensionStandards[$point]) &&
                        $value !== null &&
                        $value !== "" &&
                        is_numeric($value)
                    ) {
                        $hasValidDimensions = true;
                        $std = $dimensionStandards[$point];
                        $floatValue = (float) $value;
                        $isPointNG = false;
                        $epsilon = 0.00001;

                        // Helper for prefix-aware comparison (Aligned with In-Process logic)
                        $checkInvalid = function ($val, $stdVal, $mode) use (
                            $epsilon,
                        ) {
                            if ($stdVal === null || $stdVal === "") {
                                return false;
                            }
                            $stdStr = $this->normalizeStandardValue($stdVal);

                            if (
                                strlen($stdStr) > 1 &&
                                (str_starts_with($stdStr, "+") ||
                                    str_starts_with($stdStr, "-"))
                            ) {
                                $operator = substr($stdStr, 0, 1);
                                $limit = (float) substr($stdStr, 1);
                                if ($operator === "+") {
                                    // Must be greater than
                                    return $val <= $limit + $epsilon;
                                } elseif ($operator === "-") {
                                    // Must be less than
                                    return $val >= $limit - $epsilon;
                                }
                            }

                            $stdFloat = (float) $stdStr;
                            if ($mode === "min") {
                                return $val < $stdFloat - $epsilon;
                            }
                            if ($mode === "max") {
                                return $val > $stdFloat + $epsilon;
                            }
                            return false;
                        };

                        if (
                            $std["min"] !== null &&
                            $checkInvalid($floatValue, $std["min"], "min")
                        ) {
                            $isPointNG = true;
                        }
                        if (
                            !$isPointNG &&
                            $std["max"] !== null &&
                            $checkInvalid($floatValue, $std["max"], "max")
                        ) {
                            $isPointNG = true;
                        }

                        // Special case: if Size is a prefix operator (+ or -)
                        if (!$isPointNG && ($std["size"] ?? null) !== null) {
                            $sizeStr = $this->normalizeStandardValue(
                                $std["size"],
                            );
                            if (
                                strlen($sizeStr) > 1 &&
                                (str_starts_with($sizeStr, "+") ||
                                    str_starts_with($sizeStr, "-"))
                            ) {
                                if (
                                    $checkInvalid($floatValue, $sizeStr, "size")
                                ) {
                                    $isPointNG = true;
                                }
                            }
                        }

                        // Fallback to Size +/- Tolerance
                        if (
                            !$isPointNG &&
                            ($std["min"] ?? null) === null &&
                            ($std["max"] ?? null) === null &&
                            ($std["size"] ?? null) !== null &&
                            ($std["tolerance"] ?? null) !== null
                        ) {
                            $sizeStr = $this->normalizeStandardValue(
                                $std["size"],
                            );
                            if (
                                !str_starts_with($sizeStr, "+") &&
                                !str_starts_with($sizeStr, "-")
                            ) {
                                $size = (float) $sizeStr;
                                $tol = $this->normalizeStandardValue(
                                    $std["tolerance"],
                                );
                                $lowerBound = $size;
                                $upperBound = $size;

                                if (str_contains($tol, "/")) {
                                    $parts = explode("/", $tol);
                                    foreach ($parts as $p) {
                                        $p = $this->normalizeStandardValue($p);
                                        $fVal = (float) $p;
                                        if (
                                            str_starts_with($p, "+") ||
                                            $fVal > 0
                                        ) {
                                            $upperBound = $size + abs($fVal);
                                        } elseif (
                                            str_starts_with($p, "-") ||
                                            $fVal < 0
                                        ) {
                                            $lowerBound = $size - abs($fVal);
                                        }
                                    }
                                } elseif (str_starts_with($tol, "+")) {
                                    $upperBound =
                                        $size + (float) substr($tol, 1);
                                } elseif (str_starts_with($tol, "-")) {
                                    $lowerBound = $size + (float) $tol;
                                } else {
                                    $tVal = (float) $tol;
                                    $lowerBound = $size - $tVal;
                                    $upperBound = $size + $tVal;
                                }

                                if (
                                    $floatValue < $lowerBound - $epsilon ||
                                    $floatValue > $upperBound + $epsilon
                                ) {
                                    $isPointNG = true;
                                }
                            }
                        }

                        if ($isPointNG) {
                            $isAnyInvalid = true;
                            $ngPointsCount++;
                        } else {
                            $okPointsCount++;
                        }
                    }
                }
            }

            if ($hasValidDimensions) {
                $data["ok_points_count"] = $okPointsCount;
                $data["ng_points_count"] = $ngPointsCount;

                if ($isAnyInvalid) {
                    $data["judgment"] = "NG";
                } else {
                    // Jika dimensi OK, cek apakah ada defect lain (total_ng > 0)
                    if (
                        isset($data["total_ng"]) &&
                        (int) $data["total_ng"] > 0
                    ) {
                        $data["judgment"] = "NG";
                    } else {
                        $data["judgment"] = "OK";
                    }
                }
            } else {
                // Jika tidak ada data dimensi yang valid tetapi standar ada,
                // pastikan judgment tetap mengikuti total_ng jika ada
                if (isset($data["total_ng"]) && (int) $data["total_ng"] > 0) {
                    $data["judgment"] = "NG";
                }
            }
        } else {
            // Jika standar tidak ditemukan, jangan biarkan judgment kosong
            if (!isset($data["judgment"])) {
                $data["judgment"] =
                    isset($data["total_ng"]) && (int) $data["total_ng"] > 0
                        ? "NG"
                        : "OK";
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
    private function processDimensions(?array $dimensions): array
    {
        if (empty($dimensions)) {
            return [];
        }

        $filteredDimensions = [];
        foreach ($dimensions as $cavity => $points) {
            $filteredPoints = array_filter(
                $points,
                fn($value) => $value !== null && $value !== "",
            );
            if (!empty($filteredPoints)) {
                $filteredDimensions[$cavity] = $filteredPoints;
            }
        }

        return $filteredDimensions;
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
            $data = $this->validateDimensions($data, $data["item_id"]);

            // Process defects
            $defects = $this->processDefects($data);

            // Process dimensions
            $dimensionCheck = $this->processDimensions(
                $data["dimensions"] ?? null,
            );

            $checksheet = FirstPieceApproval::create([
                "plant_id" => $this->resolvePlantId(
                    $data["plant_id"] ??
                        ($data["plant"] ?? auth()->user()->plant_id),
                ),
                "user_id" => auth()->id(),
                "item_id" => $data["item_id"],
                "date" => $data["date"],
                "shift" => $data["shift"],
                "code_machine" => $data["code_machine"],
                "total_qty" => $data["total_qty"],
                "sampling_qty" => $data["sampling_qty"],
                "total_ok" => $data["total_ok"],
                "total_ng" => $data["total_ng"],
                "judgment" => $data["judgment"],
                "operator_initials" => $data["operator_initials"] ?? null,
                "part_weight" => $data["part_weight"] ?? null,
                "remarks" => $data["remarks"] ?? null,
                "dimension_check" => $dimensionCheck,
                "cycle_time" => $data["cycle_time"] ?? null,
                "defects" => $defects,
                "next_proses" =>
                    $data["judgment"] === "NG"
                        ? ($data["next_proses"] ?:
                        "SORTIR")
                        : null,
                "sap_code" => $data["sap_code"] ?? null,
            ]);

            // Clear manual machine status override
            \App\Models\MachineStatus::updateOrCreate(
                [
                    "plant_id" => $checksheet->plant_id,
                    "type" => "machine",
                    "number" => $checksheet->code_machine,
                ],
                [
                    "status" => "normal",
                    "description" =>
                        "Automatically cleared by First Piece Approval input",
                    "created_by" => "System",
                ],
            );

            DB::commit();

            Log::info("First Piece Approval berhasil dibuat", [
                "user_id" => auth()->id(),
                "checksheet_id" => $checksheet->id,
                "plant_id" => $checksheet->plant_id,
            ]);

            // Notifications
            if ($checksheet->total_ng > 0) {
                $this->notificationService->notifyNGFinding(
                    $checksheet,
                    "First Piece Approval",
                );
            }
            $this->notificationService->notifyApprovalRequest(
                $checksheet,
                "First Piece Approval",
            );

            return [
                "checksheet" => $checksheet,
                "google_sheets_success" => false,
                "error" => null,
                "ok_points_count" => $data["ok_points_count"] ?? null,
                "ng_points_count" => $data["ng_points_count"] ?? null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal membuat First Piece Approval", [
                "user_id" => auth()->id(),
                "error" => $e->getMessage(),
                "data" => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update First Piece Approval
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateChecksheet(int $id, array $data): array
    {
        DB::beginTransaction();
        try {
            $checksheet = FirstPieceApproval::findOrFail($id);

            // Validate dimensions and auto-set judgment
            $data = $this->validateDimensions($data, $data["item_id"]);

            // Process dimensions
            $dimensionCheck = $this->processDimensions(
                $data["dimensions"] ?? null,
            );

            // Process defects
            $defects = $this->processDefects($data);

            $updateData = [
                "item_id" => $data["item_id"],
                "date" => $data["date"],
                "shift" => $data["shift"],
                "code_machine" => $data["code_machine"],
                "total_qty" => $data["total_qty"],
                "sampling_qty" => $data["sampling_qty"],
                "total_ok" => $data["total_ok"],
                "total_ng" => $data["total_ng"],
                "judgment" => $data["judgment"],
                "operator_initials" => $data["operator_initials"] ?? null,
                "part_weight" => $data["part_weight"] ?? null,
                "remarks" => $data["remarks"] ?? null,
                "dimension_check" => $dimensionCheck,
                "defects" => $defects,
                "next_proses" =>
                    $data["judgment"] === "NG"
                        ? ($data["next_proses"] ?:
                        "SORTIR")
                        : null,
                "sap_code" => $data["sap_code"] ?? null,
                "user_id" => $data["user_id"] ?? auth()->id(),
            ];

            // Update created_at and cycle_time if user has authority
            if (auth()->user()->role !== "inspector") {
                $currentDate = $checksheet->created_at->format("Y-m-d");

                if (!empty($data["jam_after"])) {
                    $updateData["created_at"] = \Carbon\Carbon::parse(
                        $currentDate . " " . $data["jam_after"],
                    );
                }

                if (!empty($data["jam_before"]) && !empty($data["jam_after"])) {
                    $before = \Carbon\Carbon::parse(
                        $currentDate . " " . $data["jam_before"],
                    );
                    $after = \Carbon\Carbon::parse(
                        $currentDate . " " . $data["jam_after"],
                    );

                    if ($after->lessThan($before)) {
                        $after->addDay();
                    }

                    $updateData["cycle_time"] = $before->diffInSeconds($after);
                } else {
                    $updateData["cycle_time"] = $data["cycle_time"] ?? null;
                }
            } else {
                $updateData["cycle_time"] = $data["cycle_time"] ?? null;
            }

            $checksheet->update($updateData);

            // Clear manual machine status override
            \App\Models\MachineStatus::updateOrCreate(
                [
                    "plant_id" => $checksheet->plant_id,
                    "type" => "machine",
                    "number" => $checksheet->code_machine,
                ],
                [
                    "status" => "normal",
                    "description" =>
                        "Automatically cleared by First Piece Approval update",
                    "created_by" => "System",
                ],
            );

            DB::commit();

            Log::info("First Piece Approval berhasil diperbarui", [
                "user_id" => auth()->id(),
                "checksheet_id" => $checksheet->id,
                "plant_id" => $checksheet->plant_id,
            ]);

            return [
                "checksheet" => $checksheet,
                "ok_points_count" => $data["ok_points_count"] ?? null,
                "ng_points_count" => $data["ng_points_count"] ?? null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal memperbarui First Piece Approval", [
                "user_id" => auth()->id(),
                "checksheet_id" => $id,
                "error" => $e->getMessage(),
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
            if (auth()->user()->role === "admin") {
                $query->withoutGlobalScope("plant");
            }
            $checksheet = $query->findOrFail($id);
            $checksheet->delete();

            DB::commit();

            Log::info("First Piece Approval berhasil dihapus", [
                "user_id" => auth()->id(),
                "checksheet_id" => $id,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menghapus First Piece Approval", [
                "user_id" => auth()->id(),
                "checksheet_id" => $id,
                "error" => $e->getMessage(),
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
    public function updateApprovalStatus(
        int $id,
        array $data,
    ): FirstPieceApproval {
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
     * Get hourly distribution of FPA inputs for a given date
     * Returns an array of 24 slots (hour 0–23), each with:
     * - hour (int)
     * - count (int)
     * - percentage (float)
     * - avg_cycle_time_seconds (float|null)
     *
     * @param array $filters  Expects: 'date' (Y-m-d), optionally 'plant'
     * @return array
     */
    public function getHourlyDistribution(array $filters): array
    {
        $date = $filters['date'] ?? now()->toDateString();

        $query = DB::table('first_piece_approvals')
            ->whereDate('created_at', $date);

        if (!empty($filters['plant'])) {
            $query->where('plant_id', $this->resolvePlantId($filters['plant']));
        }

        // Total FPA on that day
        $total = (clone $query)->count();

        // Group by hour: count, avg cycle time
        $rows = (clone $query)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(NULLIF(cycle_time, 0)) as avg_cycle_time_seconds')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // Group by hour: inspector initials (distinct, sorted)
        $inspectorRows = (clone $query)
            ->whereNotNull('operator_initials')
            ->where('operator_initials', '!=', '')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('GROUP_CONCAT(DISTINCT operator_initials ORDER BY operator_initials SEPARATOR \', \') as inspectors')
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->get()
            ->keyBy('hour');

        // Build full 24-hour array
        $distribution = [];
        $maxCount = 0;
        for ($h = 0; $h < 24; $h++) {
            $row = $rows->get($h);
            $count = $row ? (int) $row->count : 0;
            if ($count > $maxCount) {
                $maxCount = $count;
            }
            $inspRow = $inspectorRows->get($h);
            $distribution[$h] = [
                'hour'                   => $h,
                'count'                  => $count,
                'percentage'             => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                'avg_cycle_time_seconds' => $row ? round((float) $row->avg_cycle_time_seconds, 1) : null,
                'inspectors'             => $inspRow ? $inspRow->inspectors : null,
            ];
        }

        // Mark peak hours (count == maxCount and count > 0)
        foreach ($distribution as $h => &$slot) {
            $slot['is_peak'] = ($maxCount > 0 && $slot['count'] === $maxCount);
        }
        unset($slot);

        return [
            'distribution' => $distribution,
            'total'        => $total,
            'max_count'    => $maxCount,
            'date'         => $date,
        ];
    }

    /**
     * Normalize part number for consistent internal matching
     */
    private function normalizePartNumber(?string $pn): string
    {
        if (is_null($pn) || $pn === "") {
            return "";
        }

        // Unify various dashes
        $dashes = ["\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"];
        $pn = str_replace($dashes, "-", $pn);

        // Remove ALL spaces
        $pn = str_replace([" ", "\xc2\xa0", "\t", "\n", "\r"], "", $pn);

        return strtoupper($pn);
    }
}
