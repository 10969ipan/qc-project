{{-- Bulk Approve Button - Only shown for supervisor, asst_manager, manager, admin --}}
@if(in_array(auth()->user()->role, ['supervisor', 'asst_manager', 'manager', 'admin']) && request('start_date'))
    <button type="button" id="btnBulkApprove" class="btn btn-success btn-sm ml-2"
        title="Approve semua data sesuai filter tanggal">
        <i class="fas fa-check-double"></i> Approve Semua
    </button>
@endif
