@extends('layouts.admin')

@section('title', 'Record Claim')

@section('content')
    @php
        $currentPlant = $plants->firstWhere('id', $plantId);
        
        // Resolve menu ID for permission checks
        $currentMenu = \App\Models\AppMenu::where('route', 'admin.customer-claim-records.index')->first();
        $menuId = $currentMenu ? $currentMenu->id : null;
        $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
        $canEdit = $menuId ? auth()->user()->hasPermission($menuId, 'edit') : true;
        $canDelete = $menuId ? auth()->user()->hasPermission($menuId, 'delete') : true;
    @endphp

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:1.15rem; letter-spacing:0.3px;">
                            LIST CLAIM CUSTOMER - PLANT {{ strtoupper($currentPlant->name ?? 'ALL') }}
                        </h1>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-2 d-flex justify-content-between align-items-center bg-white border-0">
            <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem; letter-spacing: 0.3px;">
                LIST CLAIM CUSTOMER
            </h6>
        </div>
        <div class="card-body py-3">
            <form action="{{ route('admin.customer-claim-records.index') }}" method="GET"
                class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 10px;" id="filterFormClaim">

                @if(request('plant'))
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                @endif

                <!-- Field: Tanggal -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700 text-nowrap">Tanggal:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                            style="width: 130px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                        <span class="px-2 text-gray-500 small">-</span>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                            style="width: 130px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                    </div>
                </div>

                @php
                    $selectedId = request('smart_filter');
                @endphp

                <!-- Unified Smart Search -->
                <div class="d-flex align-items-center flex-grow-1 mx-2">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700 text-nowrap"><i class="fas fa-search mr-1"></i> Cari:</label>
                    <div style="flex-grow: 1; max-width: 600px;" class="custom-filter-wrapper">
                        <select name="smart_filter" id="smartFilter" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Cari Part, Customer, Problem, atau Status...</option>
                            @foreach($allRecords as $record)
                                <option value="{{ $record->id }}" 
                                    data-name="{{ $record->nama_part }}" 
                                    data-detail="{{ $record->customer }} · {{ $record->problem }} · {{ $record->monitoring_status }}"
                                    {{ $selectedId == $record->id ? 'selected' : '' }}>
                                    {{ $record->nama_part }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="ml-auto d-flex" style="gap: 5px;">
                    <style>
                        .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                        .custom-filter-wrapper .ips-input { padding: 4px 20px 4px 8px; font-size: 0.72rem; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: calc(1.5em + 0.5rem + 2px); }
                        .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
                        .custom-filter-wrapper { position: relative; top: -1px; }
                        .uppercase-input { text-transform: uppercase; }
                    </style>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Cari Data">
                        <i class="fas fa-search fa-sm mr-1"></i>
                    </button>
                    <a href="{{ route('admin.customer-claim-records.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if($canExport)
                    <a href="{{ route('admin.customer-claim-records.export', request()->only(['plant', 'start_date', 'end_date', 'smart_filter'])) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('admin.customer-claim-records.print', request()->only(['plant', 'start_date', 'end_date', 'smart_filter'])) }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader btn-print" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
                    </a>
                    @endif
                    @if (!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                        <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-toggle="modal"
                            data-target="#modalTambahRecord">
                            <i class="fas fa-plus fa-sm mr-1"></i> Tambah Data
                        </button>
                    @endif
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

@if(isset($errors) && (is_object($errors) ? $errors->any() : (is_array($errors) && count($errors) > 0)))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <style>
                .table-responsive {
                    max-height: 75vh !important;
                    overflow: auto !important;
                    border: none !important;
                    box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
                }
                #dataTable {
                    border-collapse: collapse !important;
                    border-spacing: 0 !important;
                    border: none !important;
                    width: 100% !important;
                }
                #dataTable td, #dataTable th {
                    border-left: none !important;
                    border-right: none !important;
                }
                #dataTable tbody td {
                    border-bottom: 1px solid #f1f5f9 !important;
                    border-top: none !important;
                    vertical-align: middle !important;
                    color: #334155 !important;
                    padding: 6px 8px !important;
                }
                #dataTable thead th {
                    position: -webkit-sticky !important;
                    position: sticky !important;
                    top: 0 !important;
                    z-index: 100 !important;
                    background-color: #f8fafc !important; /* Industrial Slate */
                    color: #475569 !important;
                    font-weight: 600 !important;
                    text-transform: uppercase;
                    font-size: 0.65rem !important;
                    letter-spacing: 0.2px;
                    padding: 8px 12px !important;
                    border: none !important;
                    border-bottom: 2px solid #e2e8f0 !important;
                    vertical-align: middle !important;
                    white-space: nowrap !important;
                }
                .badge {
                    font-size: 0.65rem !important;
                    padding: 0.25rem 0.45rem !important;
                    font-weight: 600 !important;
                    letter-spacing: 0.2px;
                }
                .btn-xs { 
                    padding: 0.2rem 0.4rem !important; 
                    font-size: 0.65rem !important;
                }
            </style>

            <div class="table-responsive">
                <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Claim</th>
                            <th>Customer</th>
                            <th>Plant / UP (Customer)</th>
                            <th>Officially / Non Officially / Suspect</th>
                            <th>No. Dokumen (Report)</th>
                            <th>Project (NM/MP)</th>
                            <th class="col-part">Nama Part</th>
                            <th class="col-problem">Problem</th>
                            <th>Qty (pcs)</th>
                            <th>Kategori Problem</th>
                            <th>Kategori Penyimpangan (4M/IPQ/OTHER)</th>
                            <th>Initial Operator</th>
                            <th>Initial Inspektor</th>
                            <th style="min-width: 150px;">Temporary Action</th>
                            <th>Cost Akomodasi (Rp)</th>
                            <th>Cost Overtime (Rp)</th>
                            <th style="min-width: 150px;">Feedback</th>
                            <th>Status Feedback</th>
                            <th>Status (C/M)</th>
                            <th>Dokumen Evidential</th>
                            <th>Evaluasi Problem</th>
                            <th>Monitoring Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="text-center align-middle">{{ $records->firstItem() + $loop->index }}</td>
                                <td class="text-nowrap align-middle text-center">
                                    {{ $record->tanggal_claim ? $record->tanggal_claim->format('d/m/Y') : '-' }}
                                </td>
                                <td class="align-middle font-weight-bold">{{ $record->customer }}</td>
                                <td class="align-middle">{{ Str::title($record->plant_up_customer) }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-{{ $record->claim_type == 'OFFICIAL' ? 'danger' : 'warning' }}">
                                        {{ $record->claim_type }}
                                    </span>
                                </td>
                                <td class="align-middle">{{ $record->no_report }}</td>
                                <td class="text-center align-middle">{{ $record->project }}</td>
                                <td class="align-middle text-nowrap font-weight-bold">{{ $record->nama_part }}</td>
                                <td class="align-middle small">{{ Str::title($record->problem) }}</td>
                                <td class="text-center align-middle font-weight-bold text-primary">{{ $record->qty }}</td>
                                <td class="align-middle text-center">{{ Str::title($record->kategori_defect) }}</td>
                                <td class="align-middle text-center text-uppercase small">{{ $record->kategori_penyimpangan }}
                                </td>
                                <td class="text-center align-middle">{{ $record->initial_operator }}</td>
                                <td class="text-center align-middle">{{ $record->initial_inspektor }}</td>
                                <td class="align-middle small text-center">{{ Str::title($record->action_taken) }}</td>
                                <td class="text-right align-middle">{{ number_format($record->total_akomodasi, 0, ',', '.') }}
                                </td>
                                <td class="text-right align-middle">{{ number_format($record->total_overtime, 0, ',', '.') }}
                                </td>
                                <td class="align-middle small">{{ Str::title($record->feedback) }}</td>
                                <td class="text-center align-middle">{{ $record->status_feedback }}</td>
                                <td class="text-center align-middle">{{ Str::title($record->status_cm) }}</td>
                                <td class="text-left align-middle">
                                    @if($record->attachments && is_array($record->attachments))
                                        @foreach($record->attachments as $index => $path)
                                            @php 
                                                $fullFilename = basename($path);
                                                $displayFilename = preg_replace('/^\d+_/', '', $fullFilename);
                                            @endphp
                                            <div class="d-flex align-items-center mb-1 bg-light p-1 rounded border shadow-sm"
                                                style="font-size: 0.7rem;">
                                                <a href="javascript:void(0)" class="text-dark text-truncate mr-auto btn-preview-file"
                                                    style="max-width: 100px; text-decoration: none;"
                                                    data-url="{{ asset('storage/' . $path) }}" data-name="{{ $displayFilename }}"
                                                    title="Preview: {{ $displayFilename }}">
                                                    {{ $displayFilename }}
                                                </a>
                                                <a href="{{ asset('storage/' . $path) }}" download class="text-primary ml-1"
                                                    title="Download">
                                                    <i class="fas fa-download" style="font-size: 0.65rem;"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle small">{{ $record->evaluasi }}</td>
                                <td class="text-center align-middle">
                                    <span
                                        class="badge badge-{{ strtolower($record->monitoring_status) == 'open' ? 'primary' : 'success' }}">
                                        {{ $record->monitoring_status }}
                                    </span>
                                </td>
                                <td class="text-center align-middle text-nowrap">
                                    @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                        @if($canEdit)
                                            <button type="button" class="btn btn-warning btn-xs py-0 btn-edit-record"
                                                data-toggle="modal" data-target="#modalEditRecord" data-id="{{ $record->id }}"
                                                data-json="{{ json_encode($record) }}">
                                                <i class="fas fa-edit fa-xs"></i>
                                            </button>
                                        @endif
                                        @if($canDelete)
                                            <form action="{{ route('admin.customer-claim-records.destroy', $record->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-xs py-0 btn-delete-record">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="26" class="text-center py-4 text-muted">Belum ada data claim</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $records->links() }}
            </div>
        </div>
    </div>

    @include('customer_claim_records.modals')

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0">
                <div class="modal-header bg-info text-white shadow-sm">
                    <h5 class="modal-title font-weight-bold" id="previewModalLabel">Preview File</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-dark" id="file_preview_body"
                    style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                </div>
                <div class="modal-footer bg-light">
                    <a href="#" id="btn-download-full" download class="btn btn-primary btn-sm mr-auto px-4 shadow-sm">
                        <i class="fas fa-download mr-1"></i> Download File
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .col-part { width: 30% !important; }
        .col-problem { width: 10% !important; }
        .text-xs { font-size: 0.75rem; }
        .btn-xs { padding: 0.1rem 0.3rem; font-size: 0.7rem; }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script>
        window.__CUSTOMER_CLAIM_RECORDS__ = {
            baseUrl: "{{ url('admin/customer-claim-records') }}",
            deleteAttachmentUrl: "{{ route('admin.customer-claim-records.attachment.destroy', [':id', ':index']) }}",
            plant: "{{ request('plant') }}"
        };

        $(document).ready(function() {
            // Initialize Unified Smart Search
            if (typeof initItemSearch === 'function') {
                initItemSearch('smartFilter', { 
                    placeholder: 'Cari Part, Customer, Problem, atau Status...', 
                    maxResults: 60 
                });
            }

            var form = document.getElementById('filterFormClaim');
            if (form) {
                function syncExportLinks() {
                    var baseUrlPdf = "{{ route('admin.customer-claim-records.export') }}";
                    var baseUrlPrint = "{{ route('admin.customer-claim-records.print') }}";
                    var params = new URLSearchParams();
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        if (pair[1]) params.append(pair[0], pair[1]);
                    }
                    var queryString = params.toString();
                    
                    var pdfBtn = form.querySelector('.btn-download');
                    if (pdfBtn) pdfBtn.href = baseUrlPdf + '?' + queryString;
                    
                    var printBtn = form.querySelector('.btn-print');
                    if (printBtn) printBtn.href = baseUrlPrint + '?' + queryString;
                }

                $(form).find('input, select').on('change', syncExportLinks);
                syncExportLinks();
            }
        });
    </script>
    <script src="{{ asset('js/customer-claim-records/customer-claim-records.js') }}"></script>
@endpush
