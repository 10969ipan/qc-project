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

    protected function normalizeApprovalType($type)
    {
        $map = [
            'karu_qc' => 'kashift',
            'kashift_qc' => 'kashift',
            'supervisor_qc' => 'supervisor',
            'asst_manager_qc' => 'asst_manager',
            'manager_qc' => 'manager',
        ];
        return $map[$type] ?? $type;
    }

    protected function validateSequentialApproval($checksheet, $type)
    {
        $sequence = $this->getApprovalSequence();
        $normalizedType = $this->normalizeApprovalType($type);
        $idx = array_search($normalizedType, $sequence);
        if ($idx === false) {
            $idx = array_search($type, $sequence);
        }
        
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
            $entryMethod = $checksheet->getAttribute('entry_method');
            if ($entryMethod !== null && !in_array($entryMethod, ['regular', ''])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Data verifikasi tidak dapat di-approve.'], 403);
                }
                return redirect()->back()->with('error', 'Data verifikasi tidak dapat di-approve.');
            }

            $scanMethod = $checksheet->getAttribute('scan_method');
            if ($scanMethod !== null && !in_array($scanMethod, ['manual', ''])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Data verifikasi tidak dapat di-approve.'], 403);
                }
                return redirect()->back()->with('error', 'Data verifikasi tidak dapat di-approve.');
            }

            $qrVerif = $checksheet->getAttribute('qrcode_verifikasi');
            if (!empty($qrVerif)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Data verifikasi tidak dapat di-approve.'], 403);
                }
                return redirect()->back()->with('error', 'Data verifikasi tidak dapat di-approve.');
            }

            $qrVal = $checksheet->getAttribute('qrcode');
            if (!empty($qrVal)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Data verifikasi tidak dapat di-approve.'], 403);
                }
                return redirect()->back()->with('error', 'Data verifikasi tidak dapat di-approve.');
            }

            $uniqueCodeVal = $checksheet->getAttribute('unique_code_id');
            if (!empty($uniqueCodeVal)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Data verifikasi tidak dapat di-approve.'], 403);
                }
                return redirect()->back()->with('error', 'Data verifikasi tidak dapat di-approve.');
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
                if (array_key_exists('approval_status', $checksheet->getAttributes()) || \App\Helpers\ApprovalHelper::tableHasColumn($checksheet->getTable(), 'approval_status')) {
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
                } elseif (!empty($quotaStatus['unfulfilled_categories'])) {
                    $catDetails = [];
                    foreach ($quotaStatus['unfulfilled_categories'] as $uc) {
                        $catDetails[] = "{$uc['category']} ({$uc['current']}/{$uc['required']})";
                    }
                    $catsStr = implode(', ', $catDetails);
                    $msg = "Strict Blocking: Kategori berikut belum memiliki approval sampling ({$catsStr}). Harap lakukan approval sampling minimal {$req} data per kategori terlebih dahulu sebelum dapat Approve Semua.";
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
                $resolvedPlantId = \App\Models\Plant::resolveId($plantValue);
                $query->where(function ($q) use ($plantValue, $resolvedPlantId) {
                    if ($resolvedPlantId) {
                        $q->where('plant_id', $resolvedPlantId);
                    } else {
                        $q->where('plant_id', $plantValue)
                            ->orWhereHas('plant', function ($pq) use ($plantValue) {
                                $pq->where('code', $plantValue);
                            });
                    }
                });
            } elseif ($user->role !== 'admin' && !empty($user->plant_id)) {
                $query->where('plant_id', $user->plant_id);
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

                if ($request->filled('tujuan')) {
                    if (Schema::hasColumn($table, 'tujuan')) {
                        $query->where("{$table}.tujuan", $request->input('tujuan'));
                    }
                }

                if ($request->filled('code_machine')) {
                    if (Schema::hasColumn($table, 'code_machine')) {
                        $query->where("{$table}.code_machine", $request->input('code_machine'));
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
                }
                if (Schema::hasColumn($table, 'scan_method')) {
                    $query->where(function ($q) use ($table) {
                        $q->whereNull("{$table}.scan_method")
                          ->orWhere("{$table}.scan_method", 'manual');
                    });
                }
                if (Schema::hasColumn($table, 'qrcode_verifikasi')) {
                    $query->whereNull("{$table}.qrcode_verifikasi");
                }
                if (Schema::hasColumn($table, 'qrcode')) {
                    $query->where(function ($q) use ($table) {
                        $q->whereNull("{$table}.qrcode")
                          ->orWhere("{$table}.qrcode", '');
                    });
                }
                if (Schema::hasColumn($table, 'unique_code_id')) {
                    $query->where(function ($q) use ($table) {
                        $q->whereNull("{$table}.unique_code_id")
                          ->orWhere("{$table}.unique_code_id", '');
                    });
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
                $samplingIds = [];
                $bulkIds = [];

                if ($user->role === 'admin' && $approvedCount > 0) {
                    $quotaConfig = \App\Helpers\ApprovalHelper::getSamplingQuotaRequirement($type);
                    $quota = (int) ($quotaConfig['quota'] ?? 1);
                    $scope = $quotaConfig['scope'] ?? 'shift';

                    $shiftCol = Schema::hasColumn($table, 'qc_shift') ? 'qc_shift' : (Schema::hasColumn($table, 'shift') ? 'shift' : null);

                    $dateColName = $dateColumn ?? 'date';
                    if (!Schema::hasColumn($table, $dateColName)) {
                        if (Schema::hasColumn($table, 'qc_datetime')) {
                            $dateColName = 'qc_datetime';
                        } elseif (Schema::hasColumn($table, 'tanggal')) {
                            $dateColName = 'tanggal';
                        } elseif (Schema::hasColumn($table, 'tanggal_cek')) {
                            $dateColName = 'tanggal_cek';
                        }
                    }

                    $selectCols = ["{$table}.id"];
                    if (Schema::hasColumn($table, $dateColName)) {
                        $selectCols[] = "{$table}.{$dateColName} as group_date";
                    }
                    if ($shiftCol && Schema::hasColumn($table, $shiftCol)) {
                        $selectCols[] = "{$table}.{$shiftCol} as group_shift";
                    }

                    $candidateRecords = (clone $query)->select($selectCols)->get();

                    $grouped = [];
                    foreach ($candidateRecords as $rec) {
                        $gDate = !empty($rec->group_date) ? substr((string)$rec->group_date, 0, 10) : 'unknown';
                        $gShift = ($scope === 'shift' && isset($rec->group_shift) && $rec->group_shift !== '') ? (string)$rec->group_shift : 'all';
                        $groupKey = ($scope === 'shift') ? "{$gDate}_{$gShift}" : $gDate;

                        $grouped[$groupKey][] = [
                            'id' => $rec->id,
                            'date' => $gDate,
                            'shift' => $gShift,
                        ];
                    }

                    // Alias role keys for checking existing sampling
                    $roleKeys = [$type];
                    if (in_array($type, ['karu_qc', 'kashift', 'kashift_qc', 'kashift_plating'])) {
                        $roleKeys = array_unique(array_merge($roleKeys, ['karu_qc', 'kashift', 'kashift_qc', 'kashift_plating']));
                    } elseif (in_array($type, ['supervisor', 'supervisor_qc', 'supervisor_plating'])) {
                        $roleKeys = array_unique(array_merge($roleKeys, ['supervisor', 'supervisor_qc', 'supervisor_plating']));
                    } elseif (in_array($type, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating'])) {
                        $roleKeys = array_unique(array_merge($roleKeys, ['asst_manager', 'asst_manager_qc', 'asst_manager_plating']));
                    } elseif (in_array($type, ['manager', 'manager_qc', 'manager_plating'])) {
                        $roleKeys = array_unique(array_merge($roleKeys, ['manager', 'manager_qc', 'manager_plating']));
                    }

                    foreach ($grouped as $groupKey => $groupItems) {
                        $itemIds = array_column($groupItems, 'id');
                        $firstItem = $groupItems[0];
                        $targetDate = $firstItem['date'];
                        $targetShift = $firstItem['shift'];

                        $existingCount = 0;
                        if (Schema::hasColumn($table, 'approval_methods') && $targetDate !== 'unknown') {
                            $existQ = $modelClass::withoutGlobalScopes();
                            if ($request->filled('plant') && Schema::hasColumn($table, 'plant_id')) {
                                $resolvedP = \App\Models\Plant::resolveId($request->input('plant'));
                                if ($resolvedP) $existQ->where('plant_id', $resolvedP);
                            }
                            $existQ->whereDate("{$table}.{$dateColName}", $targetDate);
                            if ($scope === 'shift' && $targetShift !== 'all' && $shiftCol) {
                                $existQ->where("{$table}.{$shiftCol}", $targetShift);
                            }
                            $existQ->where(function ($eq) use ($roleKeys, $table) {
                                foreach ($roleKeys as $rk) {
                                    $eq->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT({$table}.approval_methods, '$.\"{$rk}\"')) = 'sampling'");
                                }
                            });
                            $existingCount = $existQ->count();
                        }

                        $needed = max(0, $quota - $existingCount);
                        if ($needed > 0 && !empty($itemIds)) {
                            $shuffled = collect($itemIds)->shuffle();
                            $picked = $shuffled->take($needed)->all();
                            $leftovers = $shuffled->slice($needed)->all();

                            $samplingIds = array_merge($samplingIds, $picked);
                            $bulkIds = array_merge($bulkIds, $leftovers);
                        } else {
                            $bulkIds = array_merge($bulkIds, $itemIds);
                        }
                    }
                } else {
                    $bulkIds = $checksheetIds;
                }

                DB::rollBack();
                return response()->json([
                    'success' => true,
                    'ids' => $checksheetIds,
                    'sampling_ids' => array_values($samplingIds),
                    'bulk_ids' => array_values($bulkIds),
                    'sampling_count' => count($samplingIds),
                    'bulk_count' => count($bulkIds),
                    'total' => $approvedCount,
                ]);
            }

            $lastTimeStr = null;

            if ($approvedCount > 0) {
                $now = now();
                $lastTimeStr = $now->toDateTimeString();

                $shiftCol = Schema::hasColumn($table, 'qc_shift') ? 'qc_shift' : (Schema::hasColumn($table, 'shift') ? 'shift' : null);

                // Determine Approver Name
                $approverName = $user->name;
                if ($user->role === 'admin' && $request->filled('selected_user_id')) {
                    $selUser = \App\Models\User::find($request->input('selected_user_id'));
                    if ($selUser) {
                        $approverName = $selUser->name;
                    }
                }

                $userByShift = $request->input('user_by_shift');
                $isShiftMode = ($user->role === 'admin' && !empty($userByShift) && is_array($userByShift) && $shiftCol && Schema::hasColumn($table, $shiftCol));

                $shiftApproverMap = [];
                if ($isShiftMode) {
                    $uids = array_unique(array_filter($userByShift));
                    $foundUsers = \App\Models\User::whereIn('id', $uids)->pluck('name', 'id')->toArray();
                    foreach ($userByShift as $sNum => $uId) {
                        if (isset($foundUsers[$uId])) {
                            $shiftApproverMap[(string)$sNum] = $foundUsers[$uId];
                        }
                    }
                }

                $reqSamplingIds = (array) $request->input('sampling_ids', []);
                $batchSampling = array_intersect($checksheetIds, $reqSamplingIds);
                $batchBulk = array_diff($checksheetIds, $batchSampling);

                if ($isShiftMode) {
                    $itemsShift = $modelClass::whereIn('id', $checksheetIds)->pluck($shiftCol, 'id')->toArray();
                    foreach ($checksheetIds as $cid) {
                        $cShift = (string)($itemsShift[$cid] ?? '');
                        $rowApprover = $shiftApproverMap[$cShift] ?? $approverName;
                        $methodType = in_array($cid, $batchSampling) ? 'sampling' : 'bulk';

                        $upData = [
                            $field => $rowApprover,
                            $timeField => $now,
                            'approval_methods' => DB::raw("JSON_SET(COALESCE({$table}.approval_methods, '{}'), '$.\"{$type}\"', '{$methodType}')"),
                        ];
                        if ($type === 'supervisor' || $type === 'supervisor_plating') {
                            if (Schema::hasColumn($table, 'approval_status')) {
                                $upData['approval_status'] = 'Approved';
                            }
                        }
                        if (Schema::hasColumn($table, 'rejection_remarks')) {
                            $upData['rejection_remarks'] = null;
                        }
                        $modelClass::where('id', $cid)->update($upData);
                    }
                } else {
                    if (!empty($batchSampling)) {
                        $samplingUpdate = [
                            $field => $approverName,
                            $timeField => $now,
                            'approval_methods' => DB::raw("JSON_SET(COALESCE({$table}.approval_methods, '{}'), '$.\"{$type}\"', 'sampling')"),
                        ];
                        if ($type === 'supervisor' || $type === 'supervisor_plating') {
                            if (Schema::hasColumn($table, 'approval_status')) {
                                $samplingUpdate['approval_status'] = 'Approved';
                            }
                        }
                        if (Schema::hasColumn($table, 'rejection_remarks')) {
                            $samplingUpdate['rejection_remarks'] = null;
                        }
                        $modelClass::whereIn('id', $batchSampling)->update($samplingUpdate);
                    }

                    if (!empty($batchBulk)) {
                        $bulkUpdate = [
                            $field => $approverName,
                            $timeField => $now,
                            'approval_methods' => DB::raw("JSON_SET(COALESCE({$table}.approval_methods, '{}'), '$.\"{$type}\"', 'bulk')"),
                        ];
                        if ($type === 'supervisor' || $type === 'supervisor_plating') {
                            if (Schema::hasColumn($table, 'approval_status')) {
                                $bulkUpdate['approval_status'] = 'Approved';
                            }
                        }
                        if (Schema::hasColumn($table, 'rejection_remarks')) {
                            $bulkUpdate['rejection_remarks'] = null;
                        }
                        $modelClass::whereIn('id', $batchBulk)->update($bulkUpdate);
                    }
                }
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
        $normalizedType = $this->normalizeApprovalType($type);
        $idx = array_search($normalizedType, $sequence);
        if ($idx === false) {
            $idx = array_search($type, $sequence);
        }
        
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
