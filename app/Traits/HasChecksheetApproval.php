<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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

            // Mark related notifications as read after successful approval
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

                $notificationService->markChecksheetNotificationsAsRead($checksheet, $typeLabel);
            } catch (\Exception $ne) {
                Log::error('Gagal mark notifications as read: ' . $ne->getMessage());
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();
        $modelClass = $this->getModelClass();

        // Determine approval type based on user role
        $type = null;
        $allowedRoles = ['supervisor', 'asst_manager', 'manager', 'admin'];

        if ($user->role === 'admin') {
            // Admin must specify which type to approve
            $type = $request->input('approval_type');
            if (!$type) {
                return response()->json(['success' => false, 'message' => 'Admin harus memilih level approval.'], 422);
            }
        } elseif (in_array($user->role, $allowedRoles)) {
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

            // Filter by date range
            $query->whereDate('date', '>=', $request->start_date)
                ->whereDate('date', '<=', $request->end_date);

            // Only records that are pending approval (field is NULL or REJECTED)
            $query->where(function ($q) use ($field) {
                $q->whereNull($field)->orWhere($field, 'REJECTED');
            });

            $checksheets = $query->get();
            $approvedCount = 0;

            foreach ($checksheets as $checksheet) {
                // Clear rejection if it was rejected
                if ($checksheet->$field === 'REJECTED') {
                    $checksheet->rejection_remarks = null;
                }

                $checksheet->$field = $user->name;
                $checksheet->$timeField = now();

                // Set global approval status if Supervisor approves
                if ($type === 'supervisor' || $type === 'supervisor_plating') {
                    if (Schema::hasColumn($checksheet->getTable(), 'approval_status')) {
                        $checksheet->approval_status = 'Approved';
                    }
                }

                $checksheet->save();
                $approvedCount++;
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
