{{-- Bulk Approve Button --}}
@php
    $hasFilter = request('start_date') || request('end_date') || request('result_judgment') || request('search') || request('customer_name') || request('category');
@endphp
@if(\App\Helpers\AppMenu::checkPermission(Route::currentRouteName(), 'approve_all') && $hasFilter)
    <button type="button" id="btnBulkApprove" class="btn btn-success btn-sm ml-2"
        title="Approve semua data sesuai filter yang aktif">
        <i class="fas fa-check-double"></i> Approve Semua
    </button>
@endif
