@extends('layouts.admin')

@section('title', 'Report Durability Plating')

@section('content')
<style>
    .table-responsive {
        max-height: calc(100vh - 220px) !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }

    @media (max-width: 992px) {
        .table-responsive {
            max-height: 60vh !important;
        }
    }
    #dataTable, table.dataTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        border-top: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    }
    #dataTable td, #dataTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }
    }
    #dataTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.75rem !important;
        padding: 6px 8px !important;
    }
    }
    #dataTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.7rem !important;
        letter-spacing: 0.2px;
        padding: 8px 12px !important;
        border-top: 1px solid #e2e8f0 !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #e2e8f0;
        top: 0 !important;
        z-index: 105 !important;
    }
    }
    #dataTable .btn {
        min-width: 0 !important;
        padding: 0.2rem 0.4rem !important;
        font-size: 0.7rem !important;
        margin: 1px !important;
    }
</style>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Report Durability Plating</h6>
        <div>
            <a href="{{ route('durability_plating.create') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
                <i class="fas fa-plus fa-sm text-white-50"></i> Input Data
            </a>
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager' || auth()->user()->role === 'asst_manager')
                <button type="button" class="btn btn-sm btn-success shadow-sm font-weight-bold" onclick="bulkApprove()">
                    <i class="fas fa-check-double fa-sm text-white-50"></i> Bulk Approve
                </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <form id="bulkApproveForm" method="POST" action="{{ route('durability_plating.bulk_approve') }}">
                @csrf
                <table class="table table-hover text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager' || auth()->user()->role === 'asst_manager')
                            <th><input type="checkbox" id="selectAll"></th>
                            @endif
                            <th>NO.</th>
                            <th>TGL TEST</th>
                            <th>SHIFT</th>
                            <th>NO LOT PRODUKSI</th>
                            <th>PART NAME</th>
                            <th>CUSTOMER</th>
                            <th>STANDARD</th>
                            <th>ACT. Cu</th>
                            <th>ACT. Ni</th>
                            <th>ACT. Cr</th>
                            <th>RESULT</th>
                            <th>ANALIS</th>
                            <th>APPROVAL STATUS</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checksheets as $index => $c)
                            <tr>
                                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager' || auth()->user()->role === 'asst_manager')
                                <td>
                                    @if($c->approval_status !== 'completed' && $c->approval_status !== 'rejected')
                                        <input type="checkbox" name="checksheet_ids[]" value="{{ $c->id }}" class="bulk-check">
                                    @endif
                                </td>
                                @endif
                                <td>{{ $index + 1 + ($checksheets->currentPage() - 1) * $checksheets->perPage() }}</td>
                                <td>{{ $c->date->format('d/m/Y') }}</td>
                                <td>{{ $c->shift }}</td>
                                <td>{{ $c->no_lot_produksi ?? '-' }}</td>
                                <td class="text-left font-weight-bold">{{ $c->standard->part_name ?? '-' }}</td>
                                <td>{{ $c->standard->customer ?? '-' }}</td>
                                <td>{{ $c->standard->standard_name ?? '-' }}</td>
                                <td>{{ $c->thickness_cu ?? '-' }}</td>
                                <td>{{ $c->thickness_ni ?? '-' }}</td>
                                <td>{{ $c->thickness_cr ?? '-' }}</td>
                                <td>
                                    @if($c->result === 'OK')
                                        <span class="badge badge-success">OK</span>
                                    @else
                                        <span class="badge badge-danger">NG</span>
                                    @endif
                                </td>
                                <td>{{ $c->analis }}</td>
                                <td>
                                    @if($c->approval_status === 'completed')
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Completed</span>
                                    @elseif($c->approval_status === 'rejected')
                                        <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                                    @else
                                        <span class="badge badge-warning text-dark"><i class="fas fa-clock"></i> In Progress</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center flex-wrap" style="gap: 2px;">
                                        <button type="button" class="btn btn-warning btn-sm btn-approval shadow-sm" data-id="{{ $c->id }}" title="Approval Detail">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        @if(auth()->user()->role === 'admin')
                                        <form action="{{ route('durability_plating.destroy', $c->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm shadow-sm btn-delete" title="Hapus" onclick="return confirm('Hapus data ini?');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="mt-3">
            {{ $checksheets->links() }}
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-check-circle mr-2"></i> Approval Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light" id="approvalModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#selectAll').change(function() {
            $('.bulk-check').prop('checked', $(this).prop('checked'));
        });

        $('.btn-approval').click(function() {
            var id = $(this).data('id');
            $('#approvalModalBody').html('<div class="text-center py-4"><div class="spinner-border text-warning" role="status"><span class="sr-only">Loading...</span></div></div>');
            $('#approvalModal').modal('show');
            
            var url = '{{ route("durability_plating.edit_approval", ":id") }}'.replace(':id', id);
            $.get(url, function(data) {
                $('#approvalModalBody').html(data);
            }).fail(function() {
                $('#approvalModalBody').html('<div class="alert alert-danger">Gagal memuat data approval.</div>');
            });
        });
    });

    function bulkApprove() {
        if ($('.bulk-check:checked').length === 0) {
            alert('Pilih setidaknya satu data untuk di-approve.');
            return;
        }
        if (confirm('Approve semua data yang dipilih?')) {
            $('#bulkApproveForm').submit();
        }
    }
</script>
@endpush
@endsection



