<?php

namespace App\Helpers {

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ApprovalHelper
{
    protected static array $columnsCache = [];

    public static function tableHasColumn(string $table, string $column): bool
    {
        if (!isset(static::$columnsCache[$table])) {
            static::$columnsCache[$table] = Schema::getColumnListing($table);
        }
        return in_array($column, static::$columnsCache[$table], true);
    }

    /**
     * Get approval label based on plant and level
     */
    public static function getApprovalLabel(string $level, $plant = null): string
    {
        if (!$plant) {
            $plant = request('plant') ?? (auth()->check() ? auth()->user()->plant : null) ?? 'karawang';
        }
        if (is_object($plant)) {
            $plant = $plant->code ?? $plant->name ?? 'karawang';
        }
        $labels = [
            'karawang' => [
                'karu_qc' => 'Kepala Regu QC',
                'kashift_plating' => 'Kashift Plating',
                'supervisor_plating' => 'Supervisor Plating',
                'asst_manager_plating' => 'Asst Manager Plating',
                'manager_plating' => 'Manager Plating',
                'kashift' => 'Kashift QC',
                'supervisor' => 'Supervisor QC',
                'asst_manager' => 'Asst Manager QC',
                'manager' => 'Manager QC',
            ],
            'jakarta' => [
                'karu_qc' => 'Kepala Regu',
                'kashift_plating' => 'Kashift Plating',
                'supervisor_plating' => 'Supervisor Plating',
                'asst_manager_plating' => 'Asst Manager Plating',
                'manager_plating' => 'Manager Plating',
                'kashift' => 'Kepala Regu',
                'supervisor' => 'Supervisor QC',
                'asst_manager' => 'Asst Manager QC',
                'manager' => 'Manager QC',
            ],
        ];
        return $labels[strtolower((string) $plant)][$level] ?? $labels['karawang'][$level] ?? ucfirst(str_replace('_', ' ', $level));
    }

    /**
     * Get short approval label based on plant and level
     */
    public static function getApprovalLabelShort(string $level, $plant = null): string
    {
        if (!$plant) {
            $plant = request('plant') ?? (auth()->check() ? auth()->user()->plant : null) ?? 'karawang';
        }
        if (is_object($plant)) {
            $plant = $plant->code ?? $plant->name ?? 'karawang';
        }
        $labels = [
            'karawang' => [
                'karu_qc' => 'Karu QC',
                'kashift_plating' => 'Kashift Ptg',
                'supervisor_plating' => 'SPV Ptg',
                'asst_manager_plating' => 'Asst Mgr Ptg',
                'manager_plating' => 'Mgr Ptg',
                'kashift' => 'Kashift',
                'supervisor' => 'Supervisor',
                'asst_manager' => 'Asst Manager',
                'manager' => 'Manager',
            ],
            'jakarta' => [
                'karu_qc' => 'Karu',
                'kashift_plating' => 'Kashift Ptg',
                'supervisor_plating' => 'SPV Ptg',
                'asst_manager_plating' => 'Asst Mgr Ptg',
                'manager_plating' => 'Mgr Ptg',
                'kashift' => 'Kepala Regu',
                'supervisor' => 'Supervisor',
                'asst_manager' => 'Asst Manager',
                'manager' => 'Manager',
            ],
        ];
        return $labels[strtolower((string) $plant)][$level] ?? $labels['karawang'][$level] ?? ucfirst(str_replace('_', ' ', $level));
    }

    /**
     * Get display name for a role
     */
    public static function getRoleDisplayName(string $role): string
    {
        $roles = [
            'admin' => 'Administrator',
            'inspector' => 'Inspector QC',
            'karu_qc' => 'Kepala Regu',
            'supervisor' => 'Supervisor QC',
            'asst_manager' => 'Asst Manager QC',
            'manager' => 'Manager QC',
            'kashift_plating' => 'Kashift Plating',
            'supervisor_plating' => 'Supervisor Plating',
            'asst_manager_plating' => 'Asst Manager Plating',
            'manager_plating' => 'Manager Plating',
            'kashift' => 'Kashift QC',
        ];
        return $roles[strtolower($role)] ?? ucfirst(str_replace('_', ' ', $role));
    }

    /**
     * Parse rejector name from rejection remarks
     */
    public static function getRejectorName(?string $remarks): ?string
    {
        if (!$remarks) return null;
        if (preg_match('/ - (.*?) \(\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}\)$/', $remarks, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get approval field name for a role
     */
    public static function getApprovalField(string $type): string
    {
        $fields = [
            'karu_qc' => 'karu_qc',
            'kashift_plating' => 'kashift_plating',
            'supervisor_plating' => 'supervisor_plating',
            'asst_manager_plating' => 'asst_manager_plating',
            'supervisor' => 'supervisor_qc',
            'manager_plating' => 'manager_plating',
            'manager' => 'manager_qc',
            'kashift' => 'kashift_qc',
            'asst_manager' => 'asst_manager_qc',
            'inspector' => 'inspector',
        ];
        return $fields[$type] ?? $type;
    }

    /**
     * Get approval date field name for a role
     */
    public static function getApprovalDateField(string $type): string
    {
        $fields = [
            'karu_qc' => 'karu_qc_approved_at',
            'kashift_plating' => 'kashift_plating_approved_at',
            'supervisor_plating' => 'supervisor_plating_approved_at',
            'asst_manager_plating' => 'asst_manager_plating_approved_at',
            'supervisor' => 'supervisor_approved_at',
            'manager_plating' => 'manager_plating_approved_at',
            'manager' => 'manager_approved_at',
            'kashift' => 'kashift_approved_at',
            'asst_manager' => 'asst_manager_approved_at',
        ];
        return $fields[$type] ?? "{$type}_approved_at";
    }

    /**
     * Check if a checksheet record has an unclosed Next Process (Sortir/Label Merah).
     * Guards: InProcessChecksheet, SubAssyChecksheet, FirstPieceApproval.
     */
    public static function isNextProcessOpen($checksheet): bool
    {
        if (!$checksheet) return false;

        $class = is_string($checksheet) ? $checksheet : get_class($checksheet);
        $guarded = [
            'App\Models\InProcessChecksheet',
            'App\Models\SubAssyChecksheet',
            'App\Models\FirstPieceApproval',
        ];

        $isGuarded = false;
        foreach ($guarded as $g) {
            if ($checksheet instanceof $g || $class === $g) {
                $isGuarded = true;
                break;
            }
        }
        if (!$isGuarded) return false;

        if (empty($checksheet->next_proses)) return false;

        // Special exception for InProcess: Dimension-only defects are ignored for sortir
        if ($checksheet instanceof \App\Models\InProcessChecksheet || str_contains($class, 'InProcessChecksheet')) {
            $rawDefects = $checksheet->defects;
            if (is_string($rawDefects)) {
                $rawDefects = json_decode($rawDefects, true);
            }
            if (is_array($rawDefects) && !empty($rawDefects)) {
                $hasAnyQty = false;
                $hasNonDim = false;
                foreach ($rawDefects as $k => $d) {
                    $dType = is_array($d) ? ($d['type'] ?? $d['name'] ?? $k) : (is_string($k) ? $k : $d);
                    $dQty = is_array($d) ? (int) ($d['qty'] ?? 1) : 1;
                    if ($dQty > 0 && !empty($dType)) {
                        $hasAnyQty = true;
                        if (!preg_match('/dimensi|dimension/i', trim($dType))) {
                            $hasNonDim = true;
                        }
                    }
                }
                if ($hasAnyQty && !$hasNonDim) {
                    return false;
                }
            }
        }

        $remarks = (string) ($checksheet->remarks ?? '');
        return !str_contains($remarks, '[SORTIR_CLOSED]');
    }

    /**
     * Get sampling quota requirement per role.
     */
    public static function getSamplingQuotaRequirement(string $role): array
    {
        $normalized = strtolower(trim($role));
        if (in_array($normalized, ['karu_qc', 'kashift', 'kashift_qc', 'kashift_plating'])) {
            return ['quota' => 2, 'scope' => 'shift'];
        }
        if (in_array($normalized, ['supervisor', 'supervisor_qc', 'supervisor_plating'])) {
            return ['quota' => 1, 'scope' => 'shift'];
        }
        if (in_array($normalized, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_qc', 'manager_plating'])) {
            return ['quota' => 1, 'scope' => 'day'];
        }
        return ['quota' => 1, 'scope' => 'shift'];
    }

    /**
     * Determine whether this approval should be marked as 'sampling' or 'bulk'
     * based on the role quota:
     * - Karu / Kashift: 2 per shift
     * - SPV: 1 per shift
     * - Asst Manager & Manager: 1 per day
     * Any approvals exceeding the quota automatically become 'bulk' (rendered in green).
     */
    public static function determineApprovalMethod($checksheet, string $role): string
    {
        if (!$checksheet) return 'sampling';

        $roleNorm = strtolower(trim($role));
        $config = self::getSamplingQuotaRequirement($roleNorm);
        $quota = (int) ($config['quota'] ?? 1);
        $scope = $config['scope'] ?? 'shift';

        $table = $checksheet->getTable();
        $modelClass = get_class($checksheet);

        // Determine date column
        $dateCol = 'date';
        if (static::tableHasColumn($table, 'qc_datetime')) {
            $dateCol = 'qc_datetime';
        } elseif (static::tableHasColumn($table, 'tanggal')) {
            $dateCol = 'tanggal';
        }

        // Determine shift column
        $shiftCol = null;
        if (static::tableHasColumn($table, 'shift')) {
            $shiftCol = 'shift';
        } elseif (static::tableHasColumn($table, 'qc_shift')) {
            $shiftCol = 'qc_shift';
        }

        // Get target date from current checksheet
        $targetDate = null;
        $dateVal = $checksheet->$dateCol ?? null;
        if ($dateVal instanceof \DateTimeInterface) {
            $targetDate = $dateVal->format('Y-m-d');
        } elseif (is_string($dateVal) && strlen($dateVal) >= 10) {
            $targetDate = substr($dateVal, 0, 10);
        }

        if (!$targetDate) {
            return 'sampling'; // Fallback if date is not identifiable
        }

        $query = $modelClass::withoutGlobalScopes();
        if ($checksheet->id) {
            $query->where($table . '.id', '!=', $checksheet->id);
        }

        if (!empty($checksheet->plant_id) && static::tableHasColumn($table, 'plant_id')) {
            $query->where($table . '.plant_id', $checksheet->plant_id);
        }

        if ($dateCol === 'qc_datetime' || $dateCol === 'production_datetime') {
            $query->whereBetween($table . '.' . $dateCol, [$targetDate . ' 00:00:00', $targetDate . ' 23:59:59']);
        } else {
            $query->where($table . '.' . $dateCol, $targetDate);
        }

        // Filter shift if scope is shift and column exists
        if ($scope === 'shift' && $shiftCol) {
            $shiftVal = $checksheet->$shiftCol ?? null;
            if (!empty($shiftVal)) {
                $query->where($table . '.' . $shiftCol, $shiftVal);
            }
        }

        // Filter sampling method for this role or related role keys
        $roleKeys = [$roleNorm];
        if (in_array($roleNorm, ['karu_qc', 'kashift', 'kashift_qc', 'kashift_plating'])) {
            $roleKeys = array_unique(array_merge($roleKeys, ['karu_qc', 'kashift', 'kashift_qc', 'kashift_plating']));
        } elseif (in_array($roleNorm, ['supervisor', 'supervisor_qc', 'supervisor_plating'])) {
            $roleKeys = array_unique(array_merge($roleKeys, ['supervisor', 'supervisor_qc', 'supervisor_plating']));
        } elseif (in_array($roleNorm, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating'])) {
            $roleKeys = array_unique(array_merge($roleKeys, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating']));
        } elseif (in_array($roleNorm, ['manager', 'manager_qc', 'manager_plating'])) {
            $roleKeys = array_unique(array_merge($roleKeys, ['manager', 'manager_qc', 'manager_plating']));
        }

        if (static::tableHasColumn($table, 'approval_methods')) {
            $query->whereRaw("{$table}.approval_methods LIKE '%\"sampling\"%'");
            $query->where(function ($q) use ($roleKeys, $table) {
                foreach ($roleKeys as $rk) {
                    $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT({$table}.approval_methods, '$.\"{$rk}\"')) = 'sampling'");
                }
            });
            $currentSamplingCount = $query->count();
        } else {
            $currentSamplingCount = 0;
        }

        // If existing sampling count already meets or exceeds quota, automatically mark as bulk
        return ($currentSamplingCount >= $quota) ? 'bulk' : 'sampling';
    }

    /**
     * Determine if a given role approved the checksheet via sampling.
     */
    public static function isSamplingApproved($checksheet, string $roleKey): bool
    {
        if (!$checksheet) return false;
        $methods = $checksheet->approval_methods ?? null;
        if (is_string($methods)) {
            $methods = json_decode($methods, true);
        }
        if (!is_array($methods)) return false;

        $targetMethod = $methods[$roleKey] ?? null;
        if ($targetMethod === 'sampling') return true;

        // Alias matching for karu_qc / kashift
        if (in_array($roleKey, ['karu_qc', 'kashift', 'kashift_qc'])) {
            if (($methods['karu_qc'] ?? null) === 'sampling') return true;
            if (($methods['kashift'] ?? null) === 'sampling') return true;
            if (($methods['kashift_qc'] ?? null) === 'sampling') return true;
        }

        // Alias matching for x <-> x_qc (supervisor, asst_manager, manager)
        $base = preg_replace('/_qc$/', '', $roleKey);
        if (in_array($base, ['supervisor', 'asst_manager', 'manager'])) {
            if (($methods[$base] ?? null) === 'sampling') return true;
            if (($methods[$base . '_qc'] ?? null) === 'sampling') return true;
        }

        return false;
    }

    /**
     * Check if a checksheet row is eligible for approval by a specific role.
     * Takes sequential approval hierarchy into account.
     */
    public static function isEligibleForApproval($checksheet, string $role): bool
    {
        if (!$checksheet) return false;

        $role = strtolower(trim($role));
        $attrs = method_exists($checksheet, 'getAttributes') ? $checksheet->getAttributes() : (array)$checksheet;

        if ($role === 'admin') {
            $fields = [
                'karu_qc', 'kashift_qc', 'kashift', 'kashift_plating',
                'supervisor_qc', 'supervisor', 'supervisor_plating',
                'asst_manager_qc', 'asst_manager', 'asst_manager_plating',
                'manager_qc', 'manager', 'manager_plating'
            ];
            foreach ($fields as $f) {
                if (array_key_exists($f, $attrs) || isset($checksheet->$f)) {
                    $val = $checksheet->$f ?? null;
                    if (empty($val) || $val === 'REJECTED') {
                        return true;
                    }
                }
            }
            return false;
        }

        $targetField = null;
        if (in_array($role, ['manager', 'manager_qc'])) {
            $targetField = array_key_exists('manager_qc', $attrs) ? 'manager_qc' : (array_key_exists('manager', $attrs) ? 'manager' : 'manager_qc');
        } elseif ($role === 'manager_plating') {
            $targetField = 'manager_plating';
        } elseif (in_array($role, ['asst_manager', 'asst_manager_qc'])) {
            $targetField = array_key_exists('asst_manager_qc', $attrs) ? 'asst_manager_qc' : (array_key_exists('asst_manager', $attrs) ? 'asst_manager' : 'asst_manager_qc');
        } elseif ($role === 'asst_manager_plating') {
            $targetField = 'asst_manager_plating';
        } elseif (in_array($role, ['supervisor', 'supervisor_qc'])) {
            $targetField = array_key_exists('supervisor_qc', $attrs) ? 'supervisor_qc' : (array_key_exists('supervisor', $attrs) ? 'supervisor' : 'supervisor_qc');
        } elseif ($role === 'supervisor_plating') {
            $targetField = 'supervisor_plating';
        } elseif (in_array($role, ['karu_qc', 'kashift', 'kashift_qc'])) {
            $targetField = array_key_exists('karu_qc', $attrs) ? 'karu_qc' : (array_key_exists('kashift_qc', $attrs) ? 'kashift_qc' : (array_key_exists('kashift', $attrs) ? 'kashift' : 'kashift_qc'));
        } elseif ($role === 'kashift_plating') {
            $targetField = 'kashift_plating';
        }

        if (!$targetField) return false;

        // Check if already approved by target role
        $currentVal = $checksheet->$targetField ?? null;
        if (!empty($currentVal) && $currentVal !== 'REJECTED') {
            return false; // Already approved!
        }

        // Helper to check if a prior approval level is satisfied
        $isPriorStepSatisfied = function($candidates) use ($checksheet, $attrs) {
            $foundAny = false;
            foreach ((array)$candidates as $col) {
                if (array_key_exists($col, $attrs) || isset($checksheet->$col)) {
                    $foundAny = true;
                    $val = $checksheet->$col ?? null;
                    if (!empty($val) && $val !== 'REJECTED') {
                        return true; // Found an approved prerequisite field
                    }
                }
            }
            return !$foundAny;
        };

        // Sequential checks for prior approval steps
        if (in_array($role, ['manager', 'manager_qc'])) {
            if (!$isPriorStepSatisfied(['asst_manager_qc', 'asst_manager'])) return false;
            if (!$isPriorStepSatisfied(['supervisor_qc', 'supervisor'])) return false;
            if (!$isPriorStepSatisfied(['karu_qc', 'kashift_qc', 'kashift'])) return false;
        } elseif ($role === 'manager_plating') {
            if (!$isPriorStepSatisfied('asst_manager_plating')) return false;
            if (!$isPriorStepSatisfied('supervisor_plating')) return false;
            if (!$isPriorStepSatisfied('kashift_plating')) return false;
        } elseif (in_array($role, ['asst_manager', 'asst_manager_qc'])) {
            if (!$isPriorStepSatisfied(['supervisor_qc', 'supervisor'])) return false;
            if (!$isPriorStepSatisfied(['karu_qc', 'kashift_qc', 'kashift'])) return false;
        } elseif ($role === 'asst_manager_plating') {
            if (!$isPriorStepSatisfied('supervisor_plating')) return false;
            if (!$isPriorStepSatisfied('kashift_plating')) return false;
        } elseif (in_array($role, ['supervisor', 'supervisor_qc'])) {
            if (!$isPriorStepSatisfied(['karu_qc', 'kashift_qc', 'kashift'])) return false;
        } elseif ($role === 'supervisor_plating') {
            if (!$isPriorStepSatisfied('kashift_plating')) return false;
        }

        return true;
    }

    /**
     * Calculate current sampling count vs quota requirement for a model & role.
     */
    public static function getSamplingQuotaStatus($modelClass, $user, array $filters = []): array
    {
        $role = is_object($user) ? ($user->role ?? '') : (string) $user;
        if ($role === 'admin') {
            return [
                'fulfilled' => true,
                'current' => 999,
                'required' => 0,
                'scope' => 'all',
                'target_date' => now()->toDateString(),
                'target_shift' => 1,
            ];
        }

        // ponytail: no cache — quota must reflect approvals instantly; query is ~12ms with targetDate set.
        return self::calculateSamplingQuotaStatus($modelClass, $user, $role, $filters);
    }

    /**
     * Internal calculation of sampling quota status
     */
    protected static function calculateSamplingQuotaStatus($modelClass, $user, string $role, array $filters = []): array
    {
        $config = self::getSamplingQuotaRequirement($role);
        $required = $config['quota'];
        $scope = $config['scope'];

        if (!class_exists($modelClass)) {
            return [
                'fulfilled' => true,
                'current' => 0,
                'required' => $required,
                'scope' => $scope,
                'target_date' => now()->toDateString(),
                'target_shift' => 1,
            ];
        }

        $model = new $modelClass();
        $table = $model->getTable();

        // Determine date column
        $dateCol = null;
        if (self::tableHasColumn($table, 'date')) {
            $dateCol = 'date';
        } elseif (self::tableHasColumn($table, 'qc_datetime')) {
            $dateCol = 'qc_datetime';
        } elseif (self::tableHasColumn($table, 'tanggal')) {
            $dateCol = 'tanggal';
        } elseif (self::tableHasColumn($table, 'tanggal_cek')) {
            $dateCol = 'tanggal_cek';
        } elseif (self::tableHasColumn($table, 'created_at')) {
            $dateCol = 'created_at';
        }

        // Determine shift column
        $shiftCol = null;
        if (self::tableHasColumn($table, 'shift')) {
            $shiftCol = 'shift';
        } elseif (self::tableHasColumn($table, 'qc_shift')) {
            $shiftCol = 'qc_shift';
        }

        // Durability: Rule khusus minimal 1 data sampling per kategori
        $isDurability = ($table === 'durability_thickness_reports' || is_a($modelClass, \App\Models\DurabilityThicknessReport::class, true));
        if ($isDurability) {
            $required = 1;
            $scope = 'category';

            $testType = $filters['test_type'] ?? 'thickness';
            $dateColName = ($testType === 'porecount') ? 'tanggal_cek_porecount' : 'tanggal_cek';
            $effectiveDateCol = self::tableHasColumn($table, $dateColName) ? $dateColName : ($dateCol ?? 'tanggal_cek');

            $hasExplicitDate = !empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filters['date']) || !empty($filters['target_date']);
            $hasOtherFilter = !empty($filters['customer_name']) || !empty($filters['category']) || !empty($filters['item_id']) || !empty($filters['search']);

            $targetDate = null;
            if ($hasExplicitDate) {
                $targetDate = $filters['start_date'] ?? ($filters['end_date'] ?? ($filters['date'] ?? ($filters['target_date'] ?? null)));
            } elseif (!$hasOtherFilter) {
                $targetDate = $filters['item_date'] ?? null;
                if (!$targetDate && $effectiveDateCol) {
                    try {
                        $targetDate = $modelClass::query()->whereNotNull($effectiveDateCol)->max($effectiveDateCol);
                        if ($targetDate instanceof \DateTimeInterface) {
                            $targetDate = $targetDate->format('Y-m-d');
                        } elseif (is_string($targetDate) && strlen($targetDate) >= 10) {
                            $targetDate = substr($targetDate, 0, 10);
                        }
                    } catch (\Throwable $e) {}
                }
                if (!$targetDate) {
                    $targetDate = \App\Helpers\ShiftHelper::getProductionDate();
                }
            }

            $baseDateQuery = $modelClass::query()
                ->join('standard_performance_tests', "{$table}.standard_performance_test_id", '=', 'standard_performance_tests.id')
                ->whereNotNull('standard_performance_tests.category')
                ->where('standard_performance_tests.category', '!=', '');

            if (isset($filters['is_trial'])) {
                $baseDateQuery->where("{$table}.is_trial", (bool)$filters['is_trial']);
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date']) && $filters['start_date'] !== $filters['end_date']) {
                $baseDateQuery->whereBetween("{$table}.{$effectiveDateCol}", [$filters['start_date'], $filters['end_date']]);
            } elseif ($targetDate) {
                $baseDateQuery->whereDate("{$table}.{$effectiveDateCol}", $targetDate);
            }

            if (!empty($filters['customer_name'])) {
                $baseDateQuery->where('standard_performance_tests.customer_name', $filters['customer_name']);
            }
            if (!empty($filters['category'])) {
                $baseDateQuery->where('standard_performance_tests.category', $filters['category']);
            }
            if (!empty($filters['item_id'])) {
                $baseDateQuery->where("{$table}.standard_performance_test_id", $filters['item_id']);
            }
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $baseDateQuery->where(function($q) use ($search) {
                    $q->where('standard_performance_tests.part_name', 'like', "%$search%")
                      ->orWhere('standard_performance_tests.part_number', 'like', "%$search%")
                      ->orWhere('standard_performance_tests.customer_name', 'like', "%$search%")
                      ->orWhere('standard_performance_tests.customer_standard', 'like', "%$search%");
                });
            }

            // Only check rows that have actual data for this test type
            $baseDateQuery->where(function ($q) use ($testType, $table) {
                if ($testType === 'thickness') {
                    $q->where(function($sub) use ($table) {
                        $sub->whereNotNull("{$table}.actual_cu")->where("{$table}.actual_cu", '!=', '')->where("{$table}.actual_cu", '!=', '-')
                            ->orWhereNotNull("{$table}.actual_ni")->where("{$table}.actual_ni", '!=', '')->where("{$table}.actual_ni", '!=', '-')
                            ->orWhereNotNull("{$table}.actual_cr")->where("{$table}.actual_cr", '!=', '')->where("{$table}.actual_cr", '!=', '-');
                    });
                } elseif ($testType === 'corrodkote') {
                    $q->whereNotNull("{$table}.standar_jam_corrodkote")->where("{$table}.standar_jam_corrodkote", '!=', '')->where("{$table}.standar_jam_corrodkote", '!=', '-');
                } elseif ($testType === 'cass') {
                    $q->whereNotNull("{$table}.standar_jam_cass")->where("{$table}.standar_jam_cass", '!=', '')->where("{$table}.standar_jam_cass", '!=', '-');
                } elseif ($testType === 'salt_spray') {
                    $q->whereNotNull("{$table}.standar_jam_salt_spray")->where("{$table}.standar_jam_salt_spray", '!=', '')->where("{$table}.standar_jam_salt_spray", '!=', '-');
                } elseif ($testType === 'porecount') {
                    $q->whereNotNull("{$table}.actual_porecount")->where("{$table}.actual_porecount", '!=', '')->where("{$table}.actual_porecount", '!=', '-');
                }
            });

            $presentCategories = (clone $baseDateQuery)
                ->distinct()
                ->pluck('standard_performance_tests.category')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $roleKeys = [$role];
            if (in_array($role, ['supervisor', 'supervisor_qc', 'supervisor_plating'])) {
                $roleKeys = array_unique(array_merge($roleKeys, ['supervisor', 'supervisor_qc', 'supervisor_plating']));
            } elseif (in_array($role, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating'])) {
                $roleKeys = array_unique(array_merge($roleKeys, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating']));
            } elseif (in_array($role, ['manager', 'manager_qc', 'manager_plating'])) {
                $roleKeys = array_unique(array_merge($roleKeys, ['manager', 'manager_qc', 'manager_plating']));
            }

            $samplingQuery = (clone $baseDateQuery);
            if (self::tableHasColumn($table, 'approval_methods')) {
                $samplingQuery->where(function ($q) use ($roleKeys, $table) {
                    foreach ($roleKeys as $rk) {
                        $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT({$table}.approval_methods, '$.\"{$rk}\"')) = 'sampling'");
                    }
                });
            } else {
                $samplingQuery->whereRaw('1 = 0');
            }

            $samplingCounts = $samplingQuery
                ->groupBy('standard_performance_tests.category')
                ->select('standard_performance_tests.category', \DB::raw('COUNT(*) as total'))
                ->pluck('total', 'category')
                ->all();

            $unfulfilledCategories = [];
            if (empty($presentCategories)) {
                $current = 0;
                $isFulfilled = true;
            } else {
                $minCurrent = 999999;
                foreach ($presentCategories as $cat) {
                    $c = (int)($samplingCounts[$cat] ?? 0);
                    if ($c < $minCurrent) {
                        $minCurrent = $c;
                    }
                    if ($c < $required) {
                        $unfulfilledCategories[$cat] = [
                            'category' => $cat,
                            'current' => $c,
                            'required' => $required,
                        ];
                    }
                }
                $current = ($minCurrent === 999999) ? 0 : $minCurrent;
                $isFulfilled = empty($unfulfilledCategories);
            }

            return [
                'fulfilled' => $isFulfilled,
                'current' => $current,
                'required' => $required,
                'scope' => 'category',
                'target_date' => $targetDate,
                'target_shift' => null,
                'unfulfilled_categories' => $unfulfilledCategories,
                'unfulfilled_customers' => [],
                'unfulfilled_shifts' => [],
            ];
        }

        $targetDate = $filters['start_date'] ?? ($filters['end_date'] ?? ($filters['date'] ?? ($filters['target_date'] ?? ($filters['item_date'] ?? null))));
        if (!$targetDate) {
            $targetDate = \App\Helpers\ShiftHelper::getProductionDate();
        } elseif ($targetDate instanceof \DateTimeInterface) {
            $targetDate = $targetDate->format('Y-m-d');
        } elseif (is_string($targetDate) && strlen($targetDate) >= 10) {
            $targetDate = substr($targetDate, 0, 10);
        }

        $targetShift = !empty($filters['shift']) ? (string)$filters['shift'] : (!empty($filters['target_shift']) ? (string)$filters['target_shift'] : null);

        $query = $modelClass::query();
        if (is_object($user) && $user->role !== 'admin' && method_exists($model, 'scopePlant')) {
            // let global plant scope apply
        }

        // Apply plant filter if set and table supports plant_id
        $plantInput = $filters['plant'] ?? (is_object($user) ? ($user->plant_id ?? null) : null);
        if (!empty($plantInput) && self::tableHasColumn($table, 'plant_id')) {
            $resolvedPlantId = \App\Models\Plant::resolveId($plantInput);
            if ($resolvedPlantId) {
                $query->where($table . '.plant_id', $resolvedPlantId);
            }
        }

        // Filter date if date column exists (use exact equality for date columns to preserve B-Tree index)
        if ($dateCol) {
            if ($dateCol === 'created_at' || $dateCol === 'qc_datetime') {
                $query->whereBetween($table . '.' . $dateCol, ["{$targetDate} 00:00:00", "{$targetDate} 23:59:59"]);
            } else {
                $query->where($table . '.' . $dateCol, $targetDate);
            }
        }

        // Filter sampling method for this role or related role keys
        $roleKeys = [$role];
        if (in_array($role, ['karu_qc', 'kashift', 'kashift_qc'])) {
            $roleKeys = array_unique(array_merge($roleKeys, ['karu_qc', 'kashift', 'kashift_qc']));
        } elseif (in_array($role, ['supervisor', 'supervisor_qc', 'supervisor_plating'])) {
            $roleKeys = array_unique(array_merge($roleKeys, ['supervisor', 'supervisor_qc', 'supervisor_plating']));
        } elseif (in_array($role, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating'])) {
            $roleKeys = array_unique(array_merge($roleKeys, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating']));
        } elseif (in_array($role, ['manager', 'manager_qc', 'manager_plating'])) {
            $roleKeys = array_unique(array_merge($roleKeys, ['manager', 'manager_qc', 'manager_plating']));
        }

        if (self::tableHasColumn($table, 'approval_methods')) {
            $query->where(function ($q) use ($roleKeys, $table) {
                foreach ($roleKeys as $rk) {
                    $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT({$table}.approval_methods, '$.\"{$rk}\"')) = 'sampling'");
                }
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        $unfulfilledShifts = [];
        $isFulfilled = false;

        // Filter shift if scope is shift and column exists
        if ($scope === 'shift' && $shiftCol) {
            if (!empty($targetShift)) {
                $query->where($table . '.' . $shiftCol, $targetShift);
                $current = $query->count();
                $isFulfilled = ($current >= $required);
                if (!$isFulfilled) {
                    $unfulfilledShifts[$targetShift] = [
                        'shift' => $targetShift,
                        'current' => $current,
                        'required' => $required,
                    ];
                }
            } else {
                // STRICT BLOCKING: Check ALL shifts that have data on this date
                $baseDateQuery = $modelClass::query();
                if (!empty($plantInput) && self::tableHasColumn($table, 'plant_id')) {
                    $resolvedPlantId = \App\Models\Plant::resolveId($plantInput);
                    if ($resolvedPlantId) {
                        $baseDateQuery->where($table . '.plant_id', $resolvedPlantId);
                    }
                }
                if ($dateCol === 'created_at' || $dateCol === 'qc_datetime') {
                    $baseDateQuery->whereBetween($table . '.' . $dateCol, ["{$targetDate} 00:00:00", "{$targetDate} 23:59:59"]);
                } else {
                    $baseDateQuery->where($table . '.' . $dateCol, $targetDate);
                }
                $presentShifts = $baseDateQuery->whereNotNull($table . '.' . $shiftCol)
                    ->distinct()
                    ->pluck($table . '.' . $shiftCol)
                    ->map(fn($s) => (string)$s)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                $samplingCounts = (clone $query)->select($table . '.' . $shiftCol, \DB::raw('COUNT(*) as total'))
                    ->groupBy($table . '.' . $shiftCol)
                    ->pluck('total', $shiftCol)
                    ->all();

                if (empty($presentShifts)) {
                    $current = 0;
                    $isFulfilled = true;
                } else {
                    $minCurrent = 999999;
                    foreach ($presentShifts as $ps) {
                        $c = (int)($samplingCounts[$ps] ?? 0);
                        if ($c < $minCurrent) {
                            $minCurrent = $c;
                        }
                        if ($c < $required) {
                            $unfulfilledShifts[$ps] = [
                                'shift' => $ps,
                                'current' => $c,
                                'required' => $required,
                            ];
                        }
                    }
                    $current = ($minCurrent === 999999) ? 0 : $minCurrent;
                    $isFulfilled = empty($unfulfilledShifts);
                }
            }
        } else {
            $current = $query->count();
            $isFulfilled = ($current >= $required);
        }

        return [
            'fulfilled' => $isFulfilled,
            'current' => $current,
            'required' => $required,
            'scope' => $scope,
            'target_date' => $targetDate,
            'target_shift' => $targetShift,
            'unfulfilled_shifts' => $unfulfilledShifts,
        ];
    }

    /**
     * Filter query by approval method (sampling vs bulk)
     */
    public static function applyApprovalMethodFilter($query, string $method): void
    {
        $table = $query->getModel()->getTable();
        if ($method === 'sampling') {
            $query->whereRaw("JSON_SEARCH({$table}.approval_methods, 'one', 'sampling') IS NOT NULL");
        } elseif ($method === 'bulk') {
            $query->whereRaw("JSON_SEARCH({$table}.approval_methods, 'one', 'bulk') IS NOT NULL");
        }
    }

    /**
     * Standardized HTML generator for approval status badges.
     * Blue for Sampling, Green for Bulk, Yellow for Pending, Red for Rejected.
     */
    public static function renderApprovalBadgeHtml($checksheet, string $roleKey, ?string $approver, $approvedAt = null, ?string $rejectionRemarks = null): string
    {
        if ($approver === 'REJECTED') {
            $rejector = self::getRejectorName($rejectionRemarks);
            $html = '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> REJECTED</span>';
            if ($rejector) {
                $html .= '<br><small class="text-muted">oleh ' . e($rejector) . '</small>';
            }
            return $html;
        }

        if (!empty($approver)) {
            $isSampling = self::isSamplingApproved($checksheet, $roleKey);
            $badgeClass = $isSampling ? 'badge-primary' : 'badge-success';
            $icon = $isSampling ? 'fa-check' : 'fa-check-double';

            $html = '<span class="badge ' . $badgeClass . ' px-2 py-1 shadow-sm"><i class="fas ' . $icon . ' mr-1"></i> APPROVED</span>';
            $html .= '<br><small class="text-muted">oleh ' . e($approver) . '</small>';

            if ($approvedAt) {
                $dt = Carbon::parse($approvedAt)->format('d/m/Y H:i');
                $html .= '<br><small class="text-muted">' . $dt . '</small>';
            }
            return $html;
        }

        return '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> PENDING</span>';
    }

    /**
     * Get active users matching an approval role/level.
     */
    public static function getUsersByRole(string $role, $plantId = null): array
    {
        $roleNorm = strtolower(trim($role));

        $roleMap = [
            'karu_qc' => ['karu_qc', 'kashift', 'kashift_qc'],
            'kashift' => ['kashift', 'karu_qc', 'kashift_qc'],
            'kashift_qc' => ['kashift_qc', 'karu_qc', 'kashift'],
            'kashift_plating' => ['kashift_plating', 'karu_qc', 'kashift'],
            'supervisor' => ['supervisor', 'supervisor_qc'],
            'supervisor_qc' => ['supervisor_qc', 'supervisor'],
            'supervisor_plating' => ['supervisor_plating', 'supervisor'],
            'asst_manager' => ['asst_manager', 'asst_manager_qc'],
            'asst_manager_qc' => ['asst_manager_qc', 'asst_manager'],
            'asst_manager_plating' => ['asst_manager_plating', 'asst_manager'],
            'manager' => ['manager', 'manager_qc'],
            'manager_qc' => ['manager_qc', 'manager'],
            'manager_plating' => ['manager_plating', 'manager'],
        ];

        $targetRoles = $roleMap[$roleNorm] ?? [$roleNorm];

        $query = \App\Models\User::query()
            ->whereIn('role', $targetRoles)
            ->where('is_active', 1);

        if (!empty($plantId)) {
            $query->where(function ($q) use ($plantId) {
                $q->where('plant_id', $plantId)->orWhereNull('plant_id');
            });
        }

        return $query->select('id', 'name', 'role', 'plant_id', 'initials')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }
}
}

namespace {
    // Global functions for backward compatibility with existing Blade templates
    if (!function_exists('getApprovalLabel')) {
        function getApprovalLabel(string $level, $plant = null): string {
            return \App\Helpers\ApprovalHelper::getApprovalLabel($level, $plant);
        }
    }
    if (!function_exists('getApprovalLabelShort')) {
        function getApprovalLabelShort(string $level, $plant = null): string {
            return \App\Helpers\ApprovalHelper::getApprovalLabelShort($level, $plant);
        }
    }
    if (!function_exists('getRoleDisplayName')) {
        function getRoleDisplayName(string $role): string {
            return \App\Helpers\ApprovalHelper::getRoleDisplayName($role);
        }
    }
    if (!function_exists('getRejectorName')) {
        function getRejectorName(?string $remarks): ?string {
            return \App\Helpers\ApprovalHelper::getRejectorName($remarks);
        }
    }
    if (!function_exists('getApprovalField')) {
        function getApprovalField(string $type): string {
            return \App\Helpers\ApprovalHelper::getApprovalField($type);
        }
    }
    if (!function_exists('getApprovalDateField')) {
        function getApprovalDateField(string $type): string {
            return \App\Helpers\ApprovalHelper::getApprovalDateField($type);
        }
    }
    if (!function_exists('isNextProcessOpen')) {
        function isNextProcessOpen($checksheet): bool {
            return \App\Helpers\ApprovalHelper::isNextProcessOpen($checksheet);
        }
    }
    if (!function_exists('getSamplingQuotaRequirement')) {
        function getSamplingQuotaRequirement(string $role): array {
            return \App\Helpers\ApprovalHelper::getSamplingQuotaRequirement($role);
        }
    }
    if (!function_exists('isSamplingApproved')) {
        function isSamplingApproved($checksheet, string $roleKey): bool {
            return \App\Helpers\ApprovalHelper::isSamplingApproved($checksheet, $roleKey);
        }
    }
    if (!function_exists('isEligibleForApproval')) {
        function isEligibleForApproval($checksheet, string $role): bool {
            return \App\Helpers\ApprovalHelper::isEligibleForApproval($checksheet, $role);
        }
    }
    if (!function_exists('getSamplingQuotaStatus')) {
        function getSamplingQuotaStatus(string $modelClass, $user, array $filters = []): array {
            return \App\Helpers\ApprovalHelper::getSamplingQuotaStatus($modelClass, $user, $filters);
        }
    }
    if (!function_exists('determineApprovalMethod')) {
        function determineApprovalMethod($checksheet, string $role): string {
            return \App\Helpers\ApprovalHelper::determineApprovalMethod($checksheet, $role);
        }
    }
    if (!function_exists('applyApprovalMethodFilter')) {
        function applyApprovalMethodFilter($query, string $method): void {
            \App\Helpers\ApprovalHelper::applyApprovalMethodFilter($query, $method);
        }
    }
    if (!function_exists('renderApprovalBadgeHtml')) {
        function renderApprovalBadgeHtml($checksheet, string $roleKey, ?string $approver, $approvedAt = null, ?string $rejectionRemarks = null): string {
            return \App\Helpers\ApprovalHelper::renderApprovalBadgeHtml($checksheet, $roleKey, $approver, $approvedAt, $rejectionRemarks);
        }
    }
}