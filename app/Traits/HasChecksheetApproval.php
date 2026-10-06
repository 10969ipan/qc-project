<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Helpers\ActivityLogger;

trait HasChecksheetApproval
{
    /**
     * Define the approval roles and their corresponding database fields.
     * Controllers can override this to add specific roles (e.g. Plating).
     */
    protected function getApprovalMapping($type)
    {
        $mapping = [
            'karu_qc' => ['field' => 'kashift_qc', 'time' => 'kashift_approved_at', 'label' => 'Karu QC'],
            'kashift' => ['field' => 'kashift_qc', 'time' => 'kashift_approved_at', 'label' => 'Kashift'],
            'kashift_qc' => ['field' => 'kashift_qc', 'time' => 'kashift_approved_at', 'label' => 'Kashift'],
            'supervisor' => ['field' => 'supervisor_qc', 'time' => 'supervisor_approved_at', 'label' => 'Supervisor'],
            'supervisor_qc' => ['field' => 'supervisor_qc', 'time' => 'supervisor_approved_at', 'label' => 'Supervisor'],
            'asst_manager' => ['field' => 'asst_manager_qc', 'time' => 'asst_manager_approved_at', 'label' => 'Asst Manager'],
            'asst_manager_qc' => ['field' => 'asst_manager_qc', 'time' => 'asst_manager_approved_at', 'label' => 'Asst Manager'],
            'manager' => ['field' => 'manager_qc', 'time' => 'manager_approved_at', 'label' => 'Manager'],
            'manager_qc' => ['field' => 'manager_qc', 'time' => 'manager_approved_at', 'label' => 'Manager'],
        ];

        return $mapping[$type] ?? null;
    }

    protected function getApprovalDateColumn()
    {
        return 'date';
    }

    /**
     * Get the ordered sequence of approval types.
     * Controllers can override this if they have a custom sequence.
     */
    protected function getApprovalSequence()
    {
        return ['kashift', 'supervisor', 'asst_manager', 'manager'];
    }

    protected function validateSequentialApproval($checksheet, $type)
    {
        $sequence = $this->getApprovalSequence();
        $idx = array_search($type, $sequence);
        
        if ($idx === false || $idx === 0) return true; // Not in sequence or is the first one

        for ($i = $idx - 1; $i >= 0; $i--) {
            $prevType = $sequence[$i];
            $prevMap = $this->getApprovalMapping($prevType);
            if ($prevMap) {
                $prevField = $prevMap['field'];
                if (empty($checksheet->$prevField) || $checksheet->$prevField === 'REJECTED') {
                    return false;
                }
            }
        }
        return true;
    }

    public function approve(Request $request, $id, $type)
    {
        $map = $this->getApprovalMapping($type);
        if (!$map)
            abort(404);

        $modelClass = $this->getModelClass();
        $query = $modelClass::query();

        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }

        try {
            $checksheet = $query->findOrFail($id);
            $user = auth()->user();

            // validation: check if role is disallowed on this module
            if (method_exists($this, 'getDisallowedApprovalRoles')) {
                $disallowed = $this->getDisallowedApprovalRoles();
                if (in_array($user->role, $disallowed) && $user->role !== 'admin') {
                    abort(403, 'Role Anda tidak memiliki akses approval untuk modul ini.');
                }
            }

            // validation: check if user owns the role or is admin
            if ($user->role !== 'admin') {
                $modelClass = $this->getModelClass();

                // 1. Block Jakarta users from approving Cross Cut (Karawang only)
                if (strpos($modelClass, 'CrossCutChecksheet') !== false && strtolower(optional($user->plant)->code) === 'jakarta') {
                    abort(403, 'User Jakarta tidak memiliki akses approval untuk Cross Cut.');
                }

                $isAllowed = false;

                // Standard role match (e.g. kashift role for kashift type, karu_qc for karu_qc type)
                if ($type === $user->role || ($user->role !== '' && strpos($type, $user->role) !== false)) {
                    $isAllowed = true;
                }

                // 2. Special case: Jakarta 'karu_qc' or 'supervisor' can approve 'kashift' level
                // acting as 'Kepala Regu' for Sub Assy and In Process
                if (strtolower(optional($user->plant)->code) === 'jakarta' && $type === 'kashift') {
                    if ($user->role === 'karu_qc' || $user->role === 'supervisor') {
                        $isAllowed = true;
                    }
                }

                if (!$isAllowed) {
                    abort(403);
                }
            }

            // Guard: Check if Next Process is still OPEN
            if (function_exists('isNextProcessOpen') && isNextProcessOpen($checksheet)) {
                $nextProcessName = $checksheet->next_proses ?? 'Sortir';
                $openMsg = "Data tidak dapat di-approve karena status Next Proses ({$nextProcessName}) masih OPEN. Status harus CLOSE terlebih dahulu.";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $openMsg], 422);
                }
                return redirect()->back()->with('error', $openMsg);
            }

            $field = $map['field'];
            $timeField = $map['time'];

            // Clear Rejection if it was rejected
            if ($checksheet->$field === 'REJECTED') {
                $checksheet->rejection_remarks = null;
            }

            // Check if already approved
            if ($checksheet->$field && $checksheet->$field !== 'REJECTED') {
                return redirect()->back()->with('error', "Checksheet sudah disetujui oleh {$map['label']}.");
            }

            // Block approval of verification/QR data — only manual input can be approved
            $table = $checksheet->getTable();
            if (Schema::hasColumn($table, 'entry_method')) {
                if (!in_array($checksheet->entry_method, ['regular', null, ''])) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Data verifikasi tidak dapat di-approve.'], 403);
                    }
                    return redirect()->back()->with('error', 'Data verifikasi tidak dapat di-approve.');
                }
            } elseif (Schema::hasColumn($table, 'qrcode_verifikasi')) {
                if (!empty($checksheet->qrcode_verifikasi)) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Data verifikasi tidak dapat di-approve.'], 403);
                    }
                    return redirect()->back()->with('error', 'Data verifikasi tidak dapat di-approve.');
                }
            }

            if (!$this->validateSequentialApproval($checksheet, $type)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Menunggu approval dari level sebelumnya.'], 403);
                }
                return redirect()->back()->with('error', 'Menunggu approval dari level sebelumnya.');
            }

            // Execute Approval (Mark as Sampling)
            $checksheet->$field = $user->name;
            $checksheet->$timeField = now();

            // Record approval method (auto determine sampling vs bulk based on quota)
            $methods = $checksheet->approval_methods ?? [];
            if (is_string($methods)) {
                $methods = json_decode($methods, true) ?: [];
            }
            if (!is_array($methods)) {
                $methods = [];
            }
            $determinedMethod = \App\Helpers\ApprovalHelper::determineApprovalMethod($checksheet, $type);
            $methods[$type] = $determinedMethod;
            $checksheet->approval_methods = $methods;

            // Set global approval status if Supervisor approves (Standard logic)
            // Or usually Supervisor approval triggers 'Approved' status in simplified flow
            if ($type === 'supervisor' || $type === 'supervisor_plating') {
                if (Schema::hasColumn($checksheet->getTable(), 'approval_status')) {
                    $checksheet->approval_status = 'Approved';
                }
            }

            $checksheet->save();

            // Log activity
            $modelName = str_replace(['App\\Models\\', 'Checksheet'], ['', ''], $modelClass);
            ActivityLogger::log('approved', $checksheet, "Melakukan approval ({$map['label']}) pada checksheet {$modelName}: ID #{$checksheet->id}");



        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Checksheet berhasil disetujui.',
                'redirect' => url()->previous()
            ]);
        }

        return redirect()->back()->with('success', 'Data Checksheet berhasil disetujui.');
    }

    public function reject(Request $request, $id, $type)
    {
        $map = $this->getApprovalMapping($type);
        if (!$map)
            abort(404);

        $request->validate([
            'rejection_remarks' => 'required|string|min:5|max:500',
        ], [
            'rejection_remarks.required' => 'Keterangan rejection wajib diisi.',
            'rejection_remarks.min' => 'Keterangan rejection minimal 5 karakter.',
        ]);

        $modelClass = $this->getModelClass();
        $query = $modelClass::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }

        try {
            $checksheet = $query->findOrFail($id);
            $user = auth()->user();
            $user = auth()->user();

            // validation
            if ($user->role !== 'admin') {
                $modelClass = $this->getModelClass();

                // Block Jakarta users from rejecting Cross Cut
                if (strpos($modelClass, 'CrossCutChecksheet') !== false && strtolower(optional($user->plant)->code) === 'jakarta') {
                    abort(403, 'User Jakarta tidak memiliki akses rejection untuk Cross Cut.');
                }

                $isAllowed = false;
                if ($type === $user->role || ($user->role !== '' && strpos($type, $user->role) !== false)) {
                    $isAllowed = true;
                }

                if (strtolower(optional($user->plant)->code) === 'jakarta' && $type === 'kashift') {
                    if ($user->role === 'karu_qc' || $user->role === 'supervisor') {
                        $isAllowed = true;
                    }
                }

                if (!$isAllowed) {
                    abort(403);
                }
            }

            $field = $map['field'];
            $timeField = $map['time'];

            if (!$this->validateSequentialApproval($checksheet, $type)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Menunggu approval dari level sebelumnya.'], 403);
                }
                return redirect()->back()->with('error', 'Menunggu approval dari level sebelumnya.');
            }

            $checksheet->$field = 'REJECTED';
            $checksheet->$timeField = now();
            if (Schema::hasColumn($checksheet->getTable(), 'approval_status')) {
                $checksheet->approval_status = 'Rejected';
            }

            // Save remarks
            $roleLabel = $map['label'];
            $checksheet->rejection_remarks = "[{$roleLabel}] " . $request->rejection_remarks . " - " . $user->name . " (" . now()->format('d/m/Y H:i') . ")";

            $checksheet->save();

            // Log activity
            $modelName = str_replace(['App\\Models\\', 'Checksheet'], ['', ''], $modelClass);
            ActivityLogger::log('rejected', $checksheet, "Melakukan rejection pada checksheet {$modelName}: ID #{$checksheet->id}");

            // Trigger notification to inspectors
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $typeLabel = 'Checksheet';
                if (strpos($modelClass, 'SubAssy') !== false)
                    $typeLabel = 'Sub Assy';
                if (strpos($modelClass, 'InProcess') !== false)
                    $typeLabel = 'In Process';
                if (strpos($modelClass, 'CrossCut') !== false)
                    $typeLabel = 'Cross Cut';
                if (strpos($modelClass, 'Sortir') !== false)
                    $typeLabel = 'Sortir';

                $notificationService->notifyRejection($checksheet, $typeLabel, $user->name);
            } catch (\Exception $ne) {
                Log::error('Gagal kirim notifikasi rejection: ' . $ne->getMessage());
            }

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Checksheet berhasil ditolak.',
                'redirect' => url()->previous()
            ]);
        }

        return redirect()->back()->with('success', 'Data Checksheet berhasil ditolak.');
    }

    /**
     * Bulk approve all records matching the date filter for the user's approval level.
     * Only accessible by supervisor, asst_manager, manager, and admin roles.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'ids' => 'nullable|array',
        ]);

        $user = auth()->user();
        $modelClass = $this->getModelClass();

        // Check disallowed roles
        if (method_exists($this, 'getDisallowedApprovalRoles')) {
            $disallowed = $this->getDisallowedApprovalRoles();
            if (in_array($user->role, $disallowed) && $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Role Anda tidak diizinkan untuk approval pada modul ini.'], 403);
            }
        }

        // Determine approval type based on user role
        $type = null;
        if ($user->role === 'admin') {
            // Admin must specify which type to approve
            $type = $request->input('approval_type');
            if (!$type) {
                return response()->json(['success' => false, 'message' => 'Admin harus memilih level approval.'], 422);
            }
        } elseif (in_array($user->role, ['kashift', 'kashift_qc', 'karu_qc', 'kashift_plating', 'supervisor', 'supervisor_qc', 'supervisor_plating', 'asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_qc', 'manager_plating']) || \App\Helpers\AppMenu::checkPermission(\Route::currentRouteName(), 'approve_all')) {
            $type = $user->role;
        }

        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Role Anda tidak diizinkan untuk bulk approve.'], 403);
        }

        // Validate Sampling Quota Requirement Before Bulk Approval
        if ($user->role !== 'admin') {
            $quotaFilterParams = $request->all();
            $effectiveDate = $request->input('start_date') ?: ($request->input('end_date') ?: $request->input('item_date'));
            if ($effectiveDate) {
                $quotaFilterParams['start_date'] = $effectiveDate;
                $quotaFilterParams['end_date'] = $effectiveDate;
            }
            $quotaStatus = function_exists('getSamplingQuotaStatus') ? getSamplingQuotaStatus($modelClass, $user, $quotaFilterParams) : ['fulfilled' => true];
            if (!$quotaStatus['fulfilled']) {
                $req = $quotaStatus['required'];
                $cur = $quotaStatus['current'];
                $dateFormatted = \Carbon\Carbon::parse($quotaStatus['target_date'])->format('d/m/Y');

                if (!empty($quotaStatus['unfulfilled_shifts'])) {
                    $shiftDetails = [];
                    foreach ($quotaStatus['unfulfilled_shifts'] as $us) {
                        $shiftDetails[] = "Shift {$us['shift']} ({$us['current']}/{$us['required']})";
                    }
                    $shiftsStr = implode(', ', $shiftDetails);
                    $msg = "Strict Blocking: Tidak dapat Approve Semua karena {$shiftsStr} pada tanggal {$dateFormatted} belum memenuhi minimal sampling ({$req}x per shift). Silakan lengkapi sampling pada shift tersebut atau pilih filter Shift yang spesifik.";
                } else {
                    $scopeText = $quotaStatus['scope'] === 'shift'
                        ? "Shift {$quotaStatus['target_shift']} pada tanggal {$dateFormatted}"
                        : "tanggal {$dateFormatted}";
                    $msg = "Wajib melakukan approval sampling minimal {$req} data untuk {$scopeText} terlebih dahulu sebelum dapat Approve Semua. (Sampling saat ini: {$cur}/{$req})";
                }

                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'quota_status' => $quotaStatus,
                ], 422);
            }
        }

        $map = $this->getApprovalMapping($type);
        if (!$map) {
            return response()->json(['success' => false, 'message' => 'Level approval tidak valid untuk modul ini.'], 422);
        }

        // Block Jakarta users from approving Cross Cut
        if (strpos($modelClass, 'CrossCutChecksheet') !== false && strtolower(optional($user->plant)->code) === 'jakarta') {
            return response()->json(['success' => false, 'message' => 'User Jakarta tidak memiliki akses approval untuk Cross Cut.'], 403);
        }

        $field = $map['field'];
        $timeField = $map['time'];

        DB::beginTransaction();
        try {
            $query = $modelClass::query();

            // Admin can bypass plant scope
            if ($user->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }

            // Apply plant filter if provided
            if ($request->filled('plant')) {
                $plantValue = $request->input('plant');
                $query->where(function ($q) use ($plantValue) {
                    $q->where('plant_id', $plantValue)
                        ->orWhereHas('plant', function ($pq) use ($plantValue) {
                            $pq->where('code', $plantValue);
                        });
                });
            }

            // Filter by IDs or date range & active filters
            if ($request->filled('ids') && is_array($request->input('ids'))) {
                $query->whereIn($query->getModel()->getTable() . '.id', $request->input('ids'));
            } else {
                $dateColumn = $this->getApprovalDateColumn();
                $table = (new $modelClass)->getTable();
                $prefixedDateCol = $table . '.' . $dateColumn;

                $reqStart = $request->input('start_date');
                $reqEnd = $request->input('end_date');
                $itemDate = $request->input('item_date');

                // Strictly bound date range: single dates or active item_date bound to exact date
                $startDate = $reqStart ?: ($reqEnd ?: $itemDate);
                $endDate = $reqEnd ?: ($reqStart ?: $itemDate);

                if (!empty($startDate) && !empty($endDate)) {
                    $query->whereDate($prefixedDateCol, '>=', $startDate)
                          ->whereDate($prefixedDateCol, '<=', $endDate);
                } elseif (!empty($startDate)) {
                    $query->whereDate($prefixedDateCol, $startDate);
                } elseif (!empty($endDate)) {
                    $query->whereDate($prefixedDateCol, $endDate);
                }

                if ($request->filled('start_tgl_datang')) {
                    $query->where($table . '.tanggal_datang', '>=', $request->input('start_tgl_datang'));
                }
                if ($request->filled('end_tgl_datang')) {
                    $query->where($table . '.tanggal_datang', '<=', $request->input('end_tgl_datang'));
                }

                $table = (new $modelClass)->getTable();

                if ($request->filled('shift')) {
                    $shiftVal = $request->input('shift');
                    if (Schema::hasColumn($table, 'qc_shift')) {
                        $query->where("{$table}.qc_shift", $shiftVal);
                    } elseif (Schema::hasColumn($table, 'shift')) {
                        $query->where("{$table}.shift", $shiftVal);
                    }
                }

                if ($request->filled('operator_initials')) {
                    if (Schema::hasColumn($table, 'operator_initials')) {
                        $query->where("{$table}.operator_initials", $request->input('operator_initials'));
                    }
                }

                if ($request->filled('item_id')) {
                    if (Schema::hasColumn($table, 'item_id')) {
                        $query->where("{$table}.item_id", $request->input('item_id'));
                    }
                }

                if ($request->filled('supplier')) {
                    $supplierVal = $request->input('supplier');
                    if (Schema::hasColumn($table, 'supplier')) {
                        $query->where("{$table}.supplier", $supplierVal);
                    } elseif (method_exists($modelClass, 'item')) {
                        $query->whereHas('item', function ($q) use ($supplierVal) {
                            $q->where('customer', $supplierVal)->orWhere('name', 'like', "%{$supplierVal}%");
                        });
                    }
                }

                if ($request->filled('judgment') || $request->filled('result_judgment')) {
                    $jVal = $request->input('judgment', $request->input('result_judgment'));
                    if (Schema::hasColumn($table, 'judgment')) {
                        $query->where("{$table}.judgment", $jVal);
                    } elseif (Schema::hasColumn($table, 'result_judgment')) {
                        $query->where("{$table}.result_judgment", $jVal);
                    }
                }

                // Bulk approval is ONLY for manual input data. Verification data is never approved via bulk approve.
                if (Schema::hasColumn($table, 'entry_method')) {
                    $query->where(function ($q) use ($table) {
                        $q->where("{$table}.entry_method", 'regular')
                          ->orWhereNull("{$table}.entry_method");
                    });
                } elseif (Schema::hasColumn($table, 'qrcode_verifikasi')) {
                    $query->whereNull("{$table}.qrcode_verifikasi");
                }

                // Filter by Approval Method (Sampling vs Bulk) if requested
                if ($request->filled('approval_method')) {
                    $methodVal = $request->input('approval_method');
                    if ($methodVal === 'sampling') {
                        $query->whereRaw("JSON_SEARCH({$table}.approval_methods, 'one', 'sampling') IS NOT NULL");
                    } elseif ($methodVal === 'bulk') {
                        $query->whereRaw("JSON_SEARCH({$table}.approval_methods, 'one', 'bulk') IS NOT NULL");
                    }
                }

                // Guard: Exclude records with OPEN Next Process (only applicable to In-Process, Sub Assy, and FPA)
                $guardedNextProcessModels = [
                    'App\Models\InProcessChecksheet',
                    'App\Models\SubAssyChecksheet',
                    'App\Models\FirstPieceApproval',
                ];
                if (in_array($modelClass, $guardedNextProcessModels) && Schema::hasColumn($table, 'next_proses')) {
                    $query->where(function ($q) use ($table) {
                        $q->whereNull("{$table}.next_proses")
                          ->orWhere("{$table}.next_proses", '')
                          ->orWhere("{$table}.remarks", 'like', '%[SORTIR_CLOSED]%');
                    });
                }

                // Apply hiding filters for non-admins if this is InProcessChecksheet
                if ($user->role !== 'admin' && strpos($modelClass, 'InProcessChecksheet') !== false) {
                    $plantId = \App\Models\Plant::resolveId($request->input('plant')) ?? optional($user->plant)->id;

                    $setting = \App\Models\GeneralSetting::where('key', 'hidden_items_inprocess')
                        ->where('plant_code', $plantId ?? 'global')
                        ->first();
                    if ($setting && !empty($setting->value)) {
                        $decoded = json_decode($setting->value, true);
                        if (is_array($decoded) && !empty($decoded)) {
                            $query->whereNotIn("{$table}.item_id", array_values(array_filter($decoded)));
                        }
                    }

                    $settingNg = \App\Models\GeneralSetting::where('key', 'hide_ng_rows_inprocess')
                        ->where('plant_code', $plantId ?? 'global')
                        ->first();
                    if ($settingNg && $settingNg->value == '1') {
                        $query->where(function($q) use ($table) {
                            $q->where(function($sub) use ($table) {
                                $sub->where("{$table}.judgment", '!=', 'NG')
                                    ->orWhereNull("{$table}.judgment");
                            })
                            ->where(function($sub) use ($table) {
                                $sub->whereNull("{$table}.total_ng")
                                    ->orWhere("{$table}.total_ng", '<=', 0);
                            });
                        });
                    }

                    $settingNoDim = \App\Models\GeneralSetting::where('key', 'hide_no_dimension_rows_inprocess')
                        ->where('plant_code', $plantId ?? 'global')
                        ->first();
                    if ($settingNoDim && $settingNoDim->value == '1') {
                        $query->where(function($q) use ($table) {
                            $q->where(function($sub) use ($table) {
                                $sub->whereNotNull("{$table}.qrcode")
                                    ->where("{$table}.qrcode", '!=', '');
                            })
                            ->orWhere(function($sub) use ($table) {
                                $sub->whereNotNull("{$table}.unique_code_id")
                                    ->where("{$table}.unique_code_id", '!=', '');
                            })
                            ->orWhereIn("{$table}.scan_method", ['hardware', 'camera'])
                            ->orWhere(function($sub) use ($table) {
                                $sub->whereNotNull("{$table}.dimension_check")
                                    ->where("{$table}.dimension_check", '!=', '')
                                    ->where("{$table}.dimension_check", '!=', '[]')
                                    ->where("{$table}.dimension_check", '!=', '{}')
                                    ->where("{$table}.dimension_check", '!=', 'null')
                                    ->where("{$table}.dimension_check", '!=', '""')
                                    ->whereRaw("CAST({$table}.dimension_check AS CHAR) REGEXP '[0-9]'");
                            });
                        });
                    }
                }

                $cust = $request->input('customer', $request->input('customer_name'));
                if (!empty($cust)) {
                    if (Schema::hasColumn($table, 'customer')) {
                        $query->where("{$table}.customer", $cust);
                    } elseif (method_exists($modelClass, 'item')) {
                        $query->whereHas('item', function ($q) use ($cust) {
                            $q->where('customer', $cust);
                        });
                    }
                }

                if ($request->filled('search')) {
                    $searchTerm = $request->input('search');
                    $query->where(function ($q) use ($searchTerm, $modelClass, $table) {
                        if (method_exists($modelClass, 'item')) {
                            $q->whereHas('item', function ($itemQuery) use ($searchTerm) {
                                $itemQuery->where('name', 'like', "%{$searchTerm}%")
                                    ->orWhere('customer', 'like', "%{$searchTerm}%")
                                    ->orWhere('part_number', 'like', "%{$searchTerm}%");
                            });
                        }
                        if (Schema::hasColumn($table, 'operator_initials')) {
                            $q->orWhere("{$table}.operator_initials", 'like', "%{$searchTerm}%");
                        }
                    });
                }
            }

            // Only records that are pending approval (field is NULL or REJECTED)
            $query->where(function ($q) use ($field) {
                $q->whereNull($field)->orWhere($field, 'REJECTED');
            });

            // Hook for sequential approval enforcement (if implemented in controller)
            if (method_exists($this, 'applySequentialApprovalFilter')) {
                $this->applySequentialApprovalFilter($query, $type);
            }

            $dummyModel = new $modelClass();
            $table = $dummyModel->getTable();

            // Get IDs before updating to know the count
            // Sort by ID DESC so the newest checksheet gets processed first,
            // receiving the time closest to "now()" when we calculate backwards.
            $checksheetIds = $query->orderBy("{$table}.id", 'desc')->pluck("{$table}.id")->toArray();
            $approvedCount = count($checksheetIds);

            if ($request->boolean('get_ids')) {
                DB::rollBack();
                return response()->json([
                    'success' => true,
                    'ids' => $checksheetIds,
                    'total' => $approvedCount,
                ]);
            }

            $lastTimeStr = null;

            if ($approvedCount > 0) {
                $now = now();
                $lastTimeStr = $now->toDateTimeString();

                $updateData = [
                    $field => $user->name,
                    $timeField => $now,
                ];
                
                $dummyModel = new $modelClass();
                $table = $dummyModel->getTable();

                // Set global approval status if Supervisor approves
                if ($type === 'supervisor' || $type === 'supervisor_plating') {
                    if (Schema::hasColumn($table, 'approval_status')) {
                        $updateData['approval_status'] = 'Approved';
                    }
                }

                // Clear rejection if it was rejected
                if (Schema::hasColumn($table, 'rejection_remarks')) {
                    $updateData['rejection_remarks'] = null;
                }

                // Generic Actual Time & Mark as Bulk Approval
                $updateData['approval_methods'] = DB::raw("JSON_SET(COALESCE({$table}.approval_methods, '{}'), '$.\"{$type}\"', 'bulk')");

                $modelClass::whereIn('id', $checksheetIds)->update($updateData);
            }

            if ($approvedCount > 0) {
                $modelName = str_replace(['App\\Models\\', 'Checksheet'], ['', ''], $modelClass);
                ActivityLogger::log('approved', null, "Melakukan bulk approval ({$approvedCount} data) pada modul {$modelName}");
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil approve {$approvedCount} data checksheet.",
                'count' => $approvedCount,
                'last_time' => $lastTimeStr,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk Approve Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error saat bulk approve: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function applySequentialApprovalFilter($query, $type)
    {
        $sequence = $this->getApprovalSequence();
        $idx = array_search($type, $sequence);
        
        if ($idx === false || $idx === 0) return;

        for ($i = $idx - 1; $i >= 0; $i--) {
            $prevType = $sequence[$i];
            $prevMap = $this->getApprovalMapping($prevType);
            if ($prevMap) {
                $prevField = $prevMap['field'];
                $query->whereNotNull($prevField)->where($prevField, '!=', 'REJECTED');
            }
        }
    }

    /**
     * Must be implemented by Controller to return the Model class name
     * e.g. return \App\Models\SubAssyChecksheet::class;
     */
    abstract protected function getModelClass();
}
