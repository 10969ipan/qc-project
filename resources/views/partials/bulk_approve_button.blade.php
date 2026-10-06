{{-- Bulk Approve Button --}}
@php
    $hasFilter = request('start_date') || request('end_date') || request('start_tgl_datang') || request('end_tgl_datang') || request('supplier') || request('approval_status') || request('judgment') || request('result_judgment') || request('search') || request('customer_name') || request('customer') || request('category') || request('shift') || request('operator_initials') || request('item_id');

    $itemsToCheck = isset($checksheets) ? $checksheets : (isset($reports) ? $reports : null);
    $hasPendingApproval = false;
    $user = auth()->user();
    $userRole = $user->role ?? '';
    $approvalRoles = ['admin', 'kashift', 'kashift_qc', 'karu_qc', 'kashift_plating', 'supervisor', 'supervisor_qc', 'supervisor_plating', 'asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_qc', 'manager_plating'];
    $canBulkApprove = in_array($userRole, $approvalRoles) || \App\Helpers\AppMenu::checkPermission(Route::currentRouteName(), 'approve_all');

    if (!empty($itemsToCheck) && count($itemsToCheck) > 0) {
        foreach ($itemsToCheck as $cs) {
            if (\App\Helpers\ApprovalHelper::isEligibleForApproval($cs, $userRole)) {
                $hasPendingApproval = true;
                break;
            }
        }
    }

    $modelClass = null;
    $itemDate = null;
    $itemShift = null;
    if (!empty($itemsToCheck) && count($itemsToCheck) > 0) {
        $firstItem = is_array($itemsToCheck) ? ($itemsToCheck[0] ?? null) : $itemsToCheck->first();
        if (is_object($firstItem)) {
            $modelClass = get_class($firstItem);
            $itemDate = $firstItem->date ?? ($firstItem->tanggal ?? ($firstItem->qc_datetime ?? ($firstItem->tanggal_datang ?? ($firstItem->inspection_date ?? ($firstItem->tanggal_inspeksi ?? null)))));
            if ($itemDate instanceof \DateTimeInterface) {
                $itemDate = $itemDate->format('Y-m-d');
            } elseif (is_string($itemDate) && strlen($itemDate) >= 10) {
                $itemDate = substr($itemDate, 0, 10);
            }
            $itemShift = $firstItem->shift ?? ($firstItem->qc_shift ?? null);
        }
    }

    $isDisallowedRole = false;
    if ($modelClass) {
        $karuOnlyModels = [
            'App\Models\PlatingChecksheet',
            'App\Models\PaintingChecksheet',
            'App\Models\DoubleTapeChecksheet',
            'App\Models\IncomingPart',
        ];
        if (in_array($modelClass, $karuOnlyModels) && in_array($userRole, ['kashift', 'kashift_qc'])) {
            $isDisallowedRole = true;
        }
    }

    $quotaStatus = ['fulfilled' => true, 'current' => 0, 'required' => 0, 'scope' => 'shift'];
    if ($modelClass && $userRole !== 'admin' && function_exists('getSamplingQuotaStatus')) {
        $filters = array_merge([
            'item_date' => $itemDate,
        ], request()->all());
        $quotaStatus = getSamplingQuotaStatus($modelClass, $user, $filters);
    }
@endphp
@if($canBulkApprove && $hasPendingApproval && !$isDisallowedRole && ($userRole === 'admin' || $quotaStatus['fulfilled']))
    <button type="button" id="btnBulkApprove" class="btn btn-success btn-sm shadow-sm" style="font-size: 0.72rem; padding: 3px 8px;"
        data-item-date="{{ $itemDate }}"
        title="Approve semua data yang tampil / sesuai filter">
        <i class="fas fa-check-double mr-1"></i> Approve Semua
    </button>
@endif
