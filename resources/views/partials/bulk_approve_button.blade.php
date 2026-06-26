{{-- Bulk Approve Button --}}
@if(\App\Helpers\AppMenu::checkPermission(Route::currentRouteName(), 'approve_all') && request('start_date'))
    <button type="button" id="btnBulkApprove" class="btn btn-success btn-sm ml-2"
        title="Approve semua data sesuai filter tanggal">
        <i class="fas fa-check-double"></i> Approve Semua
    </button>
@endif
