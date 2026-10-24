{{-- Modal Notifikasi Pending Approval Master Item Saat Baru Login --}}
@php
    $currentUserRole = auth()->user()->role ?? '';
    $isLeadershipRole = in_array($currentUserRole, [
        'supervisor', 'supervisor_qc',
        'asst_manager', 'asst_manager_qc'
    ]);
@endphp

@if($isLeadershipRole)
<div class="modal fade" id="modalPendingApprovalLogin" tabindex="-1" role="dialog" aria-labelledby="modalPendingApprovalLoginTitle" aria-hidden="true" data-backdrop="static" data-auto-show="{{ session('show_pending_items_modal') ? '1' : '0' }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title font-weight-bold" id="modalPendingApprovalLoginTitle">
                    <i class="fas fa-info-circle mr-2"></i> Perhatian: Ada Master Data Item Menunggu Approval Anda
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light p-3" style="overflow-x: hidden;">
                <p class="small text-muted mb-2 font-weight-bold">
                    Halo <strong>{{ auth()->user()->name }}</strong>, berikut adalah daftar master data item baru / pembaruan data yang memerlukan pengecekan dan persetujuan dari Anda:
                </p>
                <style>
                    #tablePendingLoginItems {
                        border-collapse: separate !important;
                        border-spacing: 0 !important;
                        border: 1px solid #e2e8f0 !important;
                        width: 100% !important;
                        table-layout: fixed !important;
                    }
                    #tablePendingLoginItems td, #tablePendingLoginItems th {
                        border-left: none !important;
                        border-right: 1px solid #f1f5f9 !important;
                    }
                    #tablePendingLoginItems thead th {
                        position: sticky !important;
                        top: 0 !important;
                        z-index: 2 !important;
                        background-color: #f8fafc !important;
                        color: #475569 !important;
                        font-weight: 700 !important;
                        text-transform: uppercase;
                        font-size: 0.65rem !important;
                        letter-spacing: 0.2px;
                        padding: 6px 8px !important;
                        border-bottom: 1px solid #cbd5e1 !important;
                        vertical-align: middle !important;
                    }
                    #tablePendingLoginItems tbody td {
                        border-bottom: 1px solid #f1f5f9 !important;
                        vertical-align: middle !important;
                        color: #334155 !important;
                        font-size: 0.70rem !important;
                        padding: 6px 8px !important;
                        line-height: 1.25 !important;
                        word-break: break-word !important;
                        overflow-wrap: break-word !important;
                        white-space: normal !important;
                    }
                    #tablePendingLoginItems tbody tr:hover {
                        background-color: #f1f5f9 !important;
                    }
                </style>
                <div class="table-responsive bg-white rounded shadow-sm" style="max-height: 50vh; overflow-y: auto; overflow-x: hidden !important;">
                    <table class="table table-hover mb-0" id="tablePendingLoginItems">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 4%;">No</th>
                                <th style="width: 17%;" class="text-left">Nama Item</th>
                                <th style="width: 13%;">No Part</th>
                                <th style="width: 15%;">Customer</th>
                                <th style="width: 11%;">Kategori</th>
                                <th style="width: 12%;">Diminta Oleh</th>
                                <th style="width: 22%;" class="text-left">Perubahan (Before &rarr; After)</th>
                                <th style="width: 6%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPendingLoginItems">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data pending approval...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white py-2 px-4 justify-content-between" style="border-radius: 0 0 12px 12px;">
                <span class="small text-muted font-italic font-weight-bold" id="pendingLoginCountText"></span>
                <div>
                    <a href="{{ route('admin.items.index', ['approval_status' => 'pending']) }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                        <i class="fas fa-external-link-alt mr-1"></i> Buka Master Item
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm shadow-sm" data-dismiss="modal">Nanti Saja</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.pendingApprovalLoginConfig = {
        fetchUrl: "{{ route('admin.items.pending-approval') }}",
        approveUrl: "{{ route('admin.items.approve', ':id') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/items/pending-login-modal.js') }}?v={{ filemtime(public_path('js/items/pending-login-modal.js')) ?? time() }}" defer></script>
@endif
