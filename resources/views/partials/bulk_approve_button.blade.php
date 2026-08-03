{{-- Bulk Approve Button --}}
@php
    $hasFilter = request('start_date') || request('end_date') || request('result_judgment') || request('search') || request('customer_name') || request('category');
@endphp
@if(\App\Helpers\AppMenu::checkPermission(Route::currentRouteName(), 'approve_all') && $hasFilter)
    <button type="button" id="btnBulkApprove" class="btn btn-success btn-sm shadow-sm" style="font-size: 0.72rem; padding: 3px 8px;"
        title="Approve semua data sesuai filter yang aktif">
        <i class="fas fa-check-double mr-1"></i> Approve Semua
    </button>
@endif
