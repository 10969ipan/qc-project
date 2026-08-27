@extends('layouts.admin')

@section('title', 'Record Claim')

@section('content')
    @php
        $currentPlant = $plants->firstWhere('id', $plantId);
        $plantCode = strtolower($currentPlant->name ?? 'ALL');
        
        // Resolve menu IDs for permission checks
        $menuIds = \App\Models\AppMenu::where('route', 'admin.customer-claim-records.index')->pluck('id');
        $canExport = true; $canEdit = true; $canDelete = true;
        if ($menuIds->isNotEmpty()) {
            $canExport = false; $canEdit = false; $canDelete = false;
            foreach ($menuIds as $mId) {
                if (auth()->user()->role === 'admin' || auth()->user()->hasPermission($mId, 'export')) $canExport = true;
                if (auth()->user()->role === 'admin' || auth()->user()->hasPermission($mId, 'edit')) $canEdit = true;
                if (auth()->user()->role === 'admin' || auth()->user()->hasPermission($mId, 'delete')) $canDelete = true;
            }
        }

        $docHeader = \App\Models\GeneralSetting::getDocHeader('customer_claim', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0215',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '0',
            'halaman' => '- / -'
        ]);
    @endphp

    <div class="card shadow mb-4">
        <div class="card-body p-2">
            <div class="mb-2">
                <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                LIST CLAIM CUSTOMER - PLANT {{ strtoupper($currentPlant->name ?? 'ALL') }}
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                            <table style="border-collapse:collapse; font-size:0.68rem;">
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['no_dokumen'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <form action="{{ route('admin.customer-claim-records.index') }}" method="GET"
                class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 10px;" id="filterFormClaim">

                @if(request('plant'))
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                @endif

                <!-- Instant Smart Search -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cari:</label>
                    <input type="text" name="search" class="form-control form-control-sm shadow-sm border-0 no-autoupper" 
                        placeholder="Ketik untuk mencari..." value="{{ request('search') }}" style="width: 120px; font-size: 0.75rem;">
                </div>

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

                <!-- Field: Customer -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700 text-nowrap">Customer:</label>
                    <select name="customer" class="form-control form-control-sm border-0 shadow-sm" style="width: 140px; font-size: 0.75rem;">
                        <option value="">SEMUA</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust }}" {{ request('customer') == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Field: Claim Type -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700 text-nowrap">Type:</label>
                    <select name="claim_type" class="form-control form-control-sm border-0 shadow-sm" style="width: 130px; font-size: 0.75rem;">
                        <option value="">SEMUA</option>
                        <option value="OFFICIAL" {{ request('claim_type') == 'OFFICIAL' ? 'selected' : '' }}>Officially</option>
                        <option value="NON OFFICIAL" {{ request('claim_type') == 'NON OFFICIAL' ? 'selected' : '' }}>Non Officially</option>
                        <option value="SUSPECT" {{ request('claim_type') == 'SUSPECT' ? 'selected' : '' }}>Suspect</option>
                    </select>
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
                    <a href="{{ route('admin.customer-claim-records.export', request()->only(['plant', 'start_date', 'end_date', 'smart_filter', 'customer', 'claim_type'])) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('admin.customer-claim-records.print', request()->only(['plant', 'start_date', 'end_date', 'smart_filter', 'customer', 'claim_type'])) }}"
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
    #dataTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    #dataTable td, #dataTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }
    #dataTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        padding: 6px 8px !important;
    }
    
    /* Global TH sticky setup - Forced override for admin.blade.php blue headers */
    #dataTable > thead > tr > th,
    #dataTable thead th,
    .table#dataTable thead th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.65rem !important;
        letter-spacing: 0.2px !important;
        padding: 8px 12px !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #cbd5e1 !important;
        top: 0 !important;
        z-index: 105 !important;
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

                /* Pagination & Info bottom area */
                .dataTables_wrapper > .row:last-child {
                    background-color: #ffffff !important;
                    padding: 10px 0 !important;
                    margin: 0 !important;
                    border-top: 1px solid #e2e8f0 !important;
                    position: sticky;
                    bottom: 0;
                    z-index: 10;
                    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
                }
                
                .dataTables_info {
                    font-size: 0.7rem !important;
                    color: #475569 !important;
                    font-weight: 600 !important;
                    padding-top: 5px !important;
                }
                
                .dataTables_paginate .pagination {
                    margin: 0 !important;
                }
                
                .page-link {
                    padding: 0.3rem 0.6rem !important;
                    font-size: 0.7rem !important;
                }
            </style>

        <!-- Loading Spinner -->
        <div id="tableLoader" class="text-center py-5">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <h6 class="text-muted font-weight-bold">Memuat Data Claim...</h6>
        </div>

        <!-- Table Container (Hidden until initialized) -->
        <div id="tableContainer" style="display: none;">
            <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Claim</th>
                            <th>Customer</th>
                            <th>Plant / UP (Customer)</th>
                            <th>Officially / Non Officially / Suspect</th>
                            <th>Eksternal/Internal</th>
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
                            <th>Cost Irregular (Rp)</th>
                            <th>Total Cost (Rp)</th>
                            <th style="min-width: 150px;">Feedback</th>
                            <th>Status Feedback</th>
                            <th>Status (C/M)</th>
                            <th>Dokumen Evidential</th>
                            <th>Evaluasi Problem</th>
                            <th>Monitoring Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td class="text-nowrap align-middle text-center">
                                    {{ $record->tanggal_claim ? $record->tanggal_claim->format('d/m/Y') : '-' }}
                                </td>
                                <td class="align-middle font-weight-bold">{{ $record->customer }}</td>
                                <td class="align-middle">{{ Str::title($record->plant_up_customer) }}</td>
                                <td class="text-center align-middle">
                                    @php
                                        $claimBadge = match(strtoupper($record->claim_type ?? '')) {
                                            'OFFICIAL'    => 'danger',
                                            'NON OFFICIAL'=> 'warning',
                                            'SUSPECT'     => 'success',
                                            default       => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $claimBadge }}">
                                        {{ $record->claim_type }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">{{ $record->eksternal_internal }}</td>
                                <td class="align-middle">{{ $record->no_report }}</td>
                                <td class="text-center align-middle">{{ $record->project }}</td>
                                <td class="align-middle text-nowrap font-weight-bold">{{ $record->nama_part }}</td>
                                <td class="align-middle small">{{ Str::title($record->problem) }}</td>
                                <td class="text-center align-middle font-weight-bold text-primary">{{ $record->qty == 0 ? '-' : $record->qty }}</td>
                                <td class="align-middle text-center">{{ Str::title($record->kategori_defect) }}</td>
                                <td class="align-middle text-center text-uppercase small">{{ $record->kategori_penyimpangan }}
                                </td>
                                <td class="text-center align-middle">{{ $record->initial_operator }}</td>
                                <td class="text-center align-middle">{{ $record->initial_inspektor }}</td>
                                <td class="align-middle small text-center">{{ Str::title($record->action_taken) }}</td>
                                <td class="text-right align-middle">{{ $record->total_akomodasi == 0 ? '-' : number_format($record->total_akomodasi, 0, ',', '.') }}
                                </td>
                                <td class="text-right align-middle">{{ $record->total_overtime == 0 ? '-' : number_format($record->total_overtime, 0, ',', '.') }}
                                </td>
                                <td class="text-right align-middle">{{ $record->total_irregular == 0 ? '-' : number_format($record->total_irregular, 0, ',', '.') }}
                                </td>
                                <td class="text-right align-middle font-weight-bold text-danger">{{ $record->total_cost == 0 ? '-' : number_format($record->total_cost, 0, ',', '.') }}
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
                                <td class="align-middle small text-center">{{ $record->evaluasi_formatted }}</td>
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
                        @endforeach
                    </tbody>
                </table>
        </div>
    </div>

    @include('customer_claim_records.modals')

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;" id="previewModalLabel">
                        <i class="fas fa-file-alt text-info mr-2"></i>Preview File
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-dark" id="file_preview_body"
                    style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <a href="#" id="btn-download-full" download class="btn btn-info mr-auto px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-download mr-1"></i> Download File
                    </a>
                    <button type="button" class="btn btn-light border px-4 font-weight-bold" data-dismiss="modal">Tutup</button>
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
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        window.__CUSTOMER_CLAIM_RECORDS__ = {
            baseUrl: "{{ url('admin/customer-claim-records') }}",
            deleteAttachmentUrl: "{{ route('admin.customer-claim-records.attachment.destroy', [':id', ':index']) }}",
            plant: "{{ request('plant') }}"
        };

        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                dom: "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                     "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                "order": [[1, "desc"]],
                "autoWidth": false,
                "deferRender": true,
                "columnDefs": [
                    { "orderable": false, "targets": [20, 23] } // Evidential, Aksi
                ],
                language: {
                    emptyTable: "Belum ada data claim",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                initComplete: function(settings, json) {
                    $('#tableLoader').hide();
                    $('#tableContainer').fadeIn('fast', function() {
                        table.columns.adjust();
                    });
                },
                drawCallback: function(settings) {
                    // ponytail: Highlight search keywords safely using TreeWalker
                    var api = this.api();
                    var tbody = api.table().body();
                    
                    $(tbody).find('mark.hlt').each(function() {
                        $(this).replaceWith(this.childNodes);
                    });
                    tbody.normalize();

                    var searchStr = api.search();
                    if (!searchStr) return;

                    var keywords = searchStr.split(' ').filter(w => w.trim().length > 1);
                    if (keywords.length === 0) return;

                    keywords = keywords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).sort((a, b) => b.length - a.length);
                    var regex = new RegExp("(" + keywords.join('|') + ")", "gi");

                    api.rows({ page: 'current' }).nodes().each(function(row) {
                        $(row).find('td:not(:last-child)').each(function() {
                            var walker = document.createTreeWalker(this, NodeFilter.SHOW_TEXT, null, false);
                            var nodes = [];
                            while (walker.nextNode()) nodes.push(walker.currentNode);
                            nodes.forEach(function(node) {
                                var text = node.nodeValue;
                                if (text.trim() && regex.test(text)) {
                                    var span = document.createElement('span');
                                    span.innerHTML = text.replace(regex, "<mark class='hlt' style='background-color: #fffa90; color: #000000; padding: 0 2px; border-radius: 2px;'>$1</mark>");
                                    var frag = document.createDocumentFragment();
                                    while (span.firstChild) frag.appendChild(span.firstChild);
                                    node.parentNode.replaceChild(frag, node);
                                }
                            });
                        });
                    });
                }
            });

            // Prevent form submit on Enter key press on search input
            $('input[name="search"]').on('keypress', function (e) {
                if (e.which == 13) {
                    e.preventDefault();
                }
            });

            // Instant smart search
            $('input[name="search"]').on('keyup input', function () {
                let input = $(this).val().toLowerCase();
                let stops = ['tolong', 'keluarkan', 'semua', 'di', 'pada', 'proses', 'nah', 'langsung', 'nya', 'tampilkan', 'cari', 'carikan', 'yang', 'ada', 'dan', 'atau', 'buatkan', 'buat', 'data', 'problem', 'masalah', 'part', 'kakotora', 'database', 'dari', 'ke', 'untuk'];
                let keywords = input.split(/[\s,.]+/).filter(w => w && !stops.includes(w));
                table.search(keywords.length ? keywords.join(' ') : input).draw();
            });

            var initialSearch = $('input[name="search"]').val();
            if (initialSearch) {
                table.search(initialSearch).draw();
            }

            var form = document.getElementById('filterFormClaim');
            if (form) {
                window.syncExportLinks = function() {
                    var baseUrlPdf = "{{ route('admin.customer-claim-records.export') }}";
                    var baseUrlPrint = "{{ route('admin.customer-claim-records.print') }}";
                    var params = new URLSearchParams();
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        if (pair[1]) params.append(pair[0], pair[1]);
                    }
                    
                    // Add current search input to export params
                    var searchVal = $('input[name="search"]').val();
                    if (searchVal) params.append('search', searchVal);

                    var queryString = params.toString();
                    
                    var pdfBtn = form.querySelector('.btn-download');
                    if (pdfBtn) pdfBtn.href = baseUrlPdf + '?' + queryString;
                    
                    var printBtn = form.querySelector('.btn-print');
                    if (printBtn) printBtn.href = baseUrlPrint + '?' + queryString;
                };

                $(form).find('input, select').on('change keyup input', window.syncExportLinks);
                window.syncExportLinks();
            }
        });
    </script>
    <script src="{{ asset('js/customer-claim-records/customer-claim-records.js') }}?v={{ filemtime(public_path('js/customer-claim-records/customer-claim-records.js')) }}"></script>
@endpush



