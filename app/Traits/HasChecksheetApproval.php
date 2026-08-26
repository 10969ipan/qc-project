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
            'kashift' => ['field' => 'kashift_qc', 'time' => 'kashift_approved_at', 'label' => 'Kashift'],
            'supervisor' => ['field' => 'supervisor_qc', 'time' => 'supervisor_approved_at', 'label' => 'Supervisor'],
            'asst_manager' => ['field' => 'asst_manager_qc', 'time' => 'asst_manager_approved_at', 'label' => 'Asst Manager'],
            'manager' => ['field' => 'manager_qc', 'time' => 'manager_approved_at', 'label' => 'Manager'],
        ];

        return $mapping[$type] ?? null;
    }

    protected function getApprovalDateColumn()
    {
        return 'date';
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

            // validation: check if user owns the role or is admin
            if ($user->role !== 'admin') {
                $modelClass = $this->getModelClass();

                // 1. Block Jakarta users from approving Cross Cut (Karawang only)
                if (strpos($modelClass, 'CrossCutChecksheet') !== false && strtolower(optional($user->plant)->code) === 'jakarta') {
                    abort(403, 'User Jakarta tidak memiliki akses approval untuk Cross Cut.');
                }

                $isAllowed = false;

                // Standard role match (e.g. kashift role for kashift type)
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

            // Execute Approval
            $checksheet->$field = $user->name;
            $checksheet->$timeField = now();

            // Set global approval status if Supervisor approves (Standard logic)
            // Or usually Supervisor approval triggers 'Approved' status in simplified flow
            if ($type === 'supervisor' || $type === 'supervisor_plating') {
                if (Schema::hasColumn($checksheet->getTable(), 'approval_status')) {
                    $checksheet->approval_status = 'Approved';
                }
            }

            // Logic khusus Sortir / Lainnya jika semua verified bisa ditaruh di sini atau override
            // Untuk saat ini kita ikuti logic eksisting: Supervisor approve -> Status Approved.

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
            session()->flash('success', 'Data Checksheet berhasil disetujui.');
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
            session()->flash('success', 'Data Checksheet berhasil ditolak.');
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
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'ids' => 'nullable|array',
        ]);

        $user = auth()->user();
        $modelClass = $this->getModelClass();

        // Determine approval type based on user role
        $type = null;
        if ($user->role === 'admin') {
            // Admin must specify which type to approve
            $type = $request->input('approval_type');
            if (!$type) {
                return response()->json(['success' => false, 'message' => 'Admin harus memilih level approval.'], 422);
            }
        } elseif (\App\Helpers\AppMenu::checkPermission(\Route::currentRouteName(), 'approve_all')) {
            $type = $user->role;
        }

        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Role Anda tidak diizinkan untuk bulk approve.'], 403);
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
                if ($request->filled('start_date')) {
                    $query->whereDate($dateColumn, '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $query->whereDate($dateColumn, '<=', $request->end_date);
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

                $viewMode = $request->input('view_mode', $request->input('entry_method'));
                if ($viewMode === 'verifikasi' || $viewMode === 'verification' || $viewMode === 'qr') {
                    if (Schema::hasColumn($table, 'entry_method')) {
                        $query->where("{$table}.entry_method", 'verifikasi');
                    } else {
                        $query->where(function ($q) use ($table) {
                            $hasCol = false;
                            if (Schema::hasColumn($table, 'qrcode')) {
                                $q->whereNotNull("{$table}.qrcode")->where("{$table}.qrcode", '!=', '');
                                $hasCol = true;
                            }
                            if (Schema::hasColumn($table, 'unique_code_id')) {
                                if ($hasCol) {
                                    $q->orWhere(function ($sub) use ($table) {
                                        $sub->whereNotNull("{$table}.unique_code_id")->where("{$table}.unique_code_id", '!=', '');
                                    });
                                } else {
                                    $q->whereNotNull("{$table}.unique_code_id")->where("{$table}.unique_code_id", '!=', '');
                                }
                            }
                        });
                    }
                } else {
                    // Default on manual input page: only approve regular (manual input) entries!
                    if (Schema::hasColumn($table, 'entry_method')) {
                        $query->where(function($q) use ($table) {
                            $q->where("{$table}.entry_method", 'regular')
                              ->orWhereNull("{$table}.entry_method");
                        });
                    } else {
                        if (Schema::hasColumn($table, 'qrcode')) {
                            $query->where(function ($sub) use ($table) {
                                $sub->whereNull("{$table}.qrcode")->orWhere("{$table}.qrcode", '');
                            });
                        }
                        if (Schema::hasColumn($table, 'unique_code_id')) {
                            $query->where(function ($sub) use ($table) {
                                $sub->whereNull("{$table}.unique_code_id")->orWhere("{$table}.unique_code_id", '');
                            });
                        }
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

            // Get IDs before updating to know the count
            $checksheetIds = $query->pluck('id')->toArray();
            $approvedCount = count($checksheetIds);

            if ($approvedCount > 0) {
                $updateData = [
                    $field => $user->name,
                    $timeField => now(),
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

                // Execute mass update (very fast, O(1) query)
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

    /**
     * Must be implemented by Controller to return the Model class name
     * e.g. return \App\Models\SubAssyChecksheet::class;
     */
    abstract protected function getModelClass();
}
