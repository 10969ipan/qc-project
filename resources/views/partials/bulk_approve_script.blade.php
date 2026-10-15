{{-- Bulk Approve Configuration & Script Loader --}}
{{-- Requires: $bulkApproveRoute variable to be set before including --}}
@php
    $userRole = auth()->user()->role ?? '';
    $approvalRoles = ['admin', 'kashift', 'kashift_qc', 'karu_qc', 'kashift_plating', 'supervisor', 'supervisor_qc', 'supervisor_plating', 'asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_plating'];
    $canBulkApproveScript = in_array($userRole, $approvalRoles) || \App\Helpers\AppMenu::checkPermission(Route::currentRouteName(), 'approve_all');
    $preloadedApprovers = [];
    $plantList = [];
    if ($userRole === 'admin') {
        $approverQuery = \App\Models\User::query()
            ->whereIn('role', [
                'karu_qc', 'kashift', 'kashift_qc', 'kashift_plating',
                'supervisor', 'supervisor_qc', 'supervisor_plating',
                'asst_manager', 'asst_manager_qc', 'asst_manager_plating',
                'manager', 'manager_qc', 'manager_plating'
            ]);
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
            $approverQuery->where(function ($q) {
                $q->where('is_active', 1)->orWhereNull('is_active');
            });
        }
        $preloadedApprovers = $approverQuery->select('id', 'name', 'role', 'plant_id', 'initials')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        $plantList = \App\Models\Plant::select('id', 'code', 'name')->get()->toArray();
    }
@endphp
@if($canBulkApproveScript)
    <script>
        window.bulkApproveConfig = {
            bulkApproveRoute: '{{ $bulkApproveRoute ?? "" }}',
            usersByRoleRoute: '{{ \Illuminate\Support\Facades\Route::has("approvals.users_by_role") ? route("approvals.users_by_role", [], false) : url("/approvals/users-by-role") }}',
            approvers: @json($preloadedApprovers),
            plantList: @json($plantList),
            csrfToken: '{{ csrf_token() }}',
            userRole: '{{ $userRole }}',
            testType: '{{ $testType ?? "thickness" }}',
            isTrial: {{ isset($isTrial) && $isTrial ? 1 : 0 }},
            startDate: '{{ request("start_date", "") }}',
            endDate: '{{ request("end_date", "") }}',
            shift: '{{ request("shift", "") }}',
            operatorInitials: '{{ request("operator_initials", "") }}',
            tujuan: '{{ request("tujuan", "") }}',
            codeMachine: '{{ request("code_machine", "") }}',
            approvalMethod: '{{ request("approval_method", "") }}',
            startTglDatang: '{{ request("start_tgl_datang", "") }}',
            endTglDatang: '{{ request("end_tgl_datang", "") }}',
            plant: '{{ request("plant", "") }}',
            resultJudgment: '{{ request("result_judgment", request("judgment", "")) }}',
            search: '{{ request("search", "") }}',
            customerName: '{{ request("customer_name", request("customer", "")) }}',
            supplier: '{{ request("supplier", "") }}',
            approvalStatus: '{{ request("approval_status", "") }}',
            category: '{{ request("category", "") }}',
            itemId: '{{ request("item_id", "") }}',
            viewMode: '{{ request("view_mode", "") }}'
        };
    </script>
    <script src="{{ asset('js/checksheet/bulk-approve.js') }}"></script>
@endif
