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
            if ($userRole === 'admin') {
                $approvalFields = ['kashift_qc', 'supervisor_qc', 'supervisor_plating', 'asst_manager_qc', 'asst_manager_plating', 'manager_qc', 'manager_plating'];
                foreach ($approvalFields as $af) {
                    if (isset($cs->$af) && (empty($cs->$af) || $cs->$af === 'REJECTED')) {
                        $hasPendingApproval = true;
                        break 2;
                    }
                }
                if (empty($cs->supervisor_qc) || empty($cs->asst_manager_qc) || empty($cs->supervisor_plating) || empty($cs->asst_manager_plating)) {
                    $hasPendingApproval = true;
                    break;
                }
            } else {
                $field = null;
                if ($userRole === 'asst_manager_plating') {
                    $field = 'asst_manager_plating';
                } elseif (in_array($userRole, ['asst_manager', 'asst_manager_qc'])) {
                    $field = (isset($cs->asst_manager_qc) || property_exists($cs, 'asst_manager_qc')) ? 'asst_manager_qc' : (isset($cs->asst_manager) ? 'asst_manager' : 'asst_manager_qc');
                } elseif ($userRole === 'supervisor_plating') {
                    $field = 'supervisor_plating';
                } elseif (in_array($userRole, ['supervisor', 'supervisor_qc'])) {
                    $field = (isset($cs->supervisor_qc) || property_exists($cs, 'supervisor_qc')) ? 'supervisor_qc' : (isset($cs->supervisor) ? 'supervisor' : 'supervisor_qc');
                } elseif ($userRole === 'manager_plating') {
                    $field = 'manager_plating';
                } elseif (in_array($userRole, ['manager', 'manager_qc'])) {
                    $field = (isset($cs->manager_qc) || property_exists($cs, 'manager_qc')) ? 'manager_qc' : (isset($cs->manager) ? 'manager' : 'manager_qc');
                } elseif ($userRole === 'kashift_plating') {
                    $field = 'kashift_plating';
                } elseif (in_array($userRole, ['kashift', 'kashift_qc', 'karu_qc'])) {
                    $field = (isset($cs->kashift_qc) || property_exists($cs, 'kashift_qc')) ? 'kashift_qc' : (isset($cs->karu_qc) ? 'karu_qc' : 'kashift_qc');
                }

                if ($field) {
                    $val = $cs->$field ?? null;
                    if (empty($val) || $val === 'REJECTED') {
                        $hasPendingApproval = true;
                        break;
                    }
                }
            }
        }
    }
@endphp
@if($canBulkApprove && $hasPendingApproval)
    <button type="button" id="btnBulkApprove" class="btn btn-success btn-sm shadow-sm" style="font-size: 0.72rem; padding: 3px 8px;"
        title="Approve semua data yang tampil / sesuai filter">
        <i class="fas fa-check-double mr-1"></i> Approve Semua
    </button>
@endif
