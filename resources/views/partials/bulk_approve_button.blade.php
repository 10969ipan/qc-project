{{-- Bulk Approve Button --}}
@php
    $hasFilter = request('start_date') || request('end_date') || request('result_judgment') || request('search') || request('customer_name') || request('customer') || request('category') || request('shift') || request('operator_initials') || request('item_id');

    $itemsToCheck = isset($checksheets) ? $checksheets : (isset($reports) ? $reports : null);
    $hasPendingApproval = false;

    if ($hasFilter && !empty($itemsToCheck) && count($itemsToCheck) > 0) {
        $user = auth()->user();
        $userRole = $user->role ?? '';

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
            } elseif (in_array($userRole, ['kashift', 'kashift_qc', 'karu_qc', 'kashift_plating'])) {
                $field = property_exists($cs, 'kashift_qc') || isset($cs->kashift_qc) ? 'kashift_qc' : (isset($cs->karu_qc) ? 'karu_qc' : 'kashift_qc');
                if (empty($cs->$field) || $cs->$field === 'REJECTED') {
                    $hasPendingApproval = true;
                    break;
                }
            } elseif (in_array($userRole, ['supervisor', 'supervisor_plating', 'supervisor_qc'])) {
                $field = isset($cs->supervisor_qc) ? 'supervisor_qc' : (isset($cs->supervisor_plating) ? 'supervisor_plating' : 'supervisor_qc');
                if (empty($cs->$field) || $cs->$field === 'REJECTED') {
                    $hasPendingApproval = true;
                    break;
                }
            } elseif (in_array($userRole, ['asst_manager', 'asst_manager_plating', 'asst_manager_qc'])) {
                $field = isset($cs->asst_manager_qc) ? 'asst_manager_qc' : (isset($cs->asst_manager_plating) ? 'asst_manager_plating' : 'asst_manager_qc');
                if (empty($cs->$field) || $cs->$field === 'REJECTED') {
                    $hasPendingApproval = true;
                    break;
                }
            } elseif (in_array($userRole, ['manager', 'manager_plating'])) {
                $field = isset($cs->manager_qc) ? 'manager_qc' : (isset($cs->manager_plating) ? 'manager_plating' : 'manager_qc');
                if (empty($cs->$field) || $cs->$field === 'REJECTED') {
                    $hasPendingApproval = true;
                    break;
                }
            }
        }
    }
@endphp
@if(\App\Helpers\AppMenu::checkPermission(Route::currentRouteName(), 'approve_all') && $hasFilter && $hasPendingApproval)
    <button type="button" id="btnBulkApprove" class="btn btn-success btn-sm shadow-sm" style="font-size: 0.72rem; padding: 3px 8px;"
        title="Approve semua data sesuai filter yang aktif">
        <i class="fas fa-check-double mr-1"></i> Approve Semua
    </button>
@endif
