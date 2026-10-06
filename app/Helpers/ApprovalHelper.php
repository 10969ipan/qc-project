<?php

namespace App\Helpers {

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ApprovalHelper
{
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
        if (Schema::hasColumn($table, 'qc_datetime')) {
            $dateCol = 'qc_datetime';
        } elseif (Schema::hasColumn($table, 'tanggal')) {
            $dateCol = 'tanggal';
        }

        // Determine shift column
        $shiftCol = null;
        if (Schema::hasColumn($table, 'shift')) {
            $shiftCol = 'shift';
        } elseif (Schema::hasColumn($table, 'qc_shift')) {
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

        if (!empty($checksheet->plant_id) && Schema::hasColumn($table, 'plant_id')) {
            $query->where($table . '.plant_id', $checksheet->plant_id);
        }

        $query->whereDate($table . '.' . $dateCol, $targetDate);

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

        if (Schema::hasColumn($table, 'approval_methods')) {
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
        if (Schema::hasColumn($table, 'date')) {
            $dateCol = 'date';
        } elseif (Schema::hasColumn($table, 'qc_datetime')) {
            $dateCol = 'qc_datetime';
        } elseif (Schema::hasColumn($table, 'tanggal')) {
            $dateCol = 'tanggal';
        } elseif (Schema::hasColumn($table, 'created_at')) {
            $dateCol = 'created_at';
        }

        // Determine shift column
        $shiftCol = null;
        if (Schema::hasColumn($table, 'shift')) {
            $shiftCol = 'shift';
        } elseif (Schema::hasColumn($table, 'qc_shift')) {
            $shiftCol = 'qc_shift';
        }

        $targetDate = $filters['start_date'] ?? ($filters['end_date'] ?? ($filters['date'] ?? ($filters['target_date'] ?? ($filters['item_date'] ?? null))));
        if (!$targetDate && $dateCol) {
            try {
                $targetDate = $modelClass::query()->whereNotNull($dateCol)->max($dateCol);
                if ($targetDate instanceof \DateTimeInterface) {
                    $targetDate = $targetDate->format('Y-m-d');
                } elseif (is_string($targetDate) && strlen($targetDate) >= 10) {
                    $targetDate = substr($targetDate, 0, 10);
                }
            } catch (\Throwable $e) {
                // Ignore and use fallback
            }
        }
        if (!$targetDate) {
            $targetDate = \App\Helpers\ShiftHelper::getProductionDate();
        }

        $targetShift = !empty($filters['shift']) ? (string)$filters['shift'] : (!empty($filters['target_shift']) ? (string)$filters['target_shift'] : null);

        $query = $modelClass::query();
        if (is_object($user) && $user->role !== 'admin' && method_exists($model, 'scopePlant')) {
            // let global plant scope apply
        }

        // Apply plant filter if set and table supports plant_id
        if (!empty($filters['plant']) && Schema::hasColumn($table, 'plant_id')) {
            $resolvedPlantId = \App\Models\Plant::resolveId($filters['plant']);
            if ($resolvedPlantId) {
                $query->where($table . '.plant_id', $resolvedPlantId);
            }
        }

        // Filter date if date column exists
        if ($dateCol) {
            $query->whereDate($table . '.' . $dateCol, $targetDate);
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

        if (Schema::hasColumn($table, 'approval_methods')) {
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
                if (!empty($filters['plant']) && Schema::hasColumn($table, 'plant_id')) {
                    $resolvedPlantId = \App\Models\Plant::resolveId($filters['plant']);
                    if ($resolvedPlantId) {
                        $baseDateQuery->where($table . '.plant_id', $resolvedPlantId);
                    }
                }
                $presentShifts = $baseDateQuery->whereDate($table . '.' . $dateCol, $targetDate)
                    ->whereNotNull($table . '.' . $shiftCol)
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