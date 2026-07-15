@extends('layouts.admin')

@section('title', 'Data Kakotora')

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
    #dataTableKakotora {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    #dataTableKakotora td, #dataTableKakotora th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }
    #dataTableKakotora tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.68rem !important;
        padding: 4px 6px !important;
    }

    /* Global TH sticky setup - Forced override for admin.blade.php blue headers */
    #dataTableKakotora > thead > tr > th,
    #dataTableKakotora thead th,
    .table#dataTableKakotora thead th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px !important;
        padding: 6px 12px !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        text-align: center !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #e2e8f0 !important;
        top: 0 !important;
        z-index: 105 !important;
    }

    /* Forced overrides for DataTables elements */
    #dataTableKakotora.dataTable thead .sorting:before,
    #dataTableKakotora.dataTable thead .sorting:after,
    #dataTableKakotora.dataTable thead .sorting_asc:before,
    #dataTableKakotora.dataTable thead .sorting_asc:after,
    #dataTableKakotora.dataTable thead .sorting_desc:before,
    #dataTableKakotora.dataTable thead .sorting_desc:after {
        display: none !important;
    }
    #dataTableKakotora.dataTable thead th,
    #dataTableKakotora.dataTable thead .sorting,
    #dataTableKakotora.dataTable thead .sorting_asc,
    #dataTableKakotora.dataTable thead .sorting_desc {
        background-image: none !important;
        background-color: #f8fafc !important;
        color: #475569 !important;
    }
    #dataTableKakotora .btn {
        min-width: 0 !important;
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
    #dataTableKakotora .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
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

    td.details-control {
        cursor: pointer;
        text-align: center;
        vertical-align: middle !important;
    }

    /* Fix for footer positioning */
    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }

    /* Prevent global-loader from blocking clicks */
    #global-loader {
        pointer-events: none !important;
    }
</style>

@php
    $plantCode = strtolower($plant ?: 'jakarta');
    $docHeader = \App\Models\GeneralSetting::getDocHeader('kakotora', $plantCode, [
        'no_dokumen' => strtoupper($plantCode) === 'JAKARTA' ? 'ENG-JKT-F-037' : 'ENG-KRW-F-037',
        'tgl_terbit' => '17-06-2020',
        'revisi' => '1 / 06-04-2023',
        'halaman' => '1 / 1'
    ]);
@endphp

<div class="card shadow mb-2">
    <div class="card-body p-0">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                    <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                </td>
                <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                    <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                        DATABASE KAKOTORA - {{ strtoupper($plantCode) }}
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
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <!-- Action Bar Sleek with Filters -->
        <form action="{{ route('kakotora.index') }}" method="GET" class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm" style="gap: 8px; overflow-x: auto; white-space: nowrap;" id="filterFormKakotora">
            <input type="hidden" name="plant" value="{{ $plant }}">
            
            <!-- Unified Search: Part Name, Part No, Model -->
            <div class="d-flex align-items-center mr-2">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cari:</label>
                <input type="text" name="search" class="form-control form-control-sm shadow-sm border-0 no-autoupper" 
                    placeholder="Ketik untuk mencari..." value="{{ request('search') }}" style="width: 200px; font-size: 0.75rem;">
            </div>

            <!-- Filter Claim -->
            <div class="d-flex align-items-center mr-2">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Claim:</label>
                <select name="category_claim" class="form-control form-control-sm shadow-sm border-0" style="width: 130px; font-size: 0.75rem;">
                    <option value="">Semua Claim</option>
                    @foreach($claims as $c)
                        <option value="{{ $c }}" {{ request('category_claim') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div class="d-flex align-items-center mr-2">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Status:</label>
                <select name="status" class="form-control form-control-sm shadow-sm border-0" style="width: 120px; font-size: 0.75rem;">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" id="btnFilterSearch" title="Filter Data">
                    <i class="fas fa-search fa-sm"></i>
                </button>
                <button type="button" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" id="btnResetFilter" title="Reset Filter">
                    <i class="fas fa-undo fa-sm"></i>
                </button>
                <button type="submit" formaction="{{ route('kakotora.print') }}" formtarget="_blank" class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print Preview" style="background-color: #17a589; color: white;">
                    <i class="fas fa-print fa-sm"></i>
                </button>
                <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalTambahKakotora">
                    <i class="fas fa-plus fa-sm mr-1"></i> <span class="font-weight-bold small">Tambah Data</span>
                </button>
            </div>
        </form>

        <!-- Loading Spinner -->
        <div id="tableLoader" class="text-center py-5">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <h6 class="text-muted font-weight-bold">Memuat Data Kakotora...</h6>
        </div>

        <!-- Table Container (Hidden until initialized) -->
        <div id="tableContainer" style="display: none;">
            <table class="table table-hover" id="dataTableKakotora" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        @if(auth()->user()->role === 'admin')
                            <th rowspan="2" width="40" class="text-center align-middle" style="width: 50px;">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <span style="font-size: 10px; margin-bottom: 5px; white-space: nowrap;">Semua (<span id="checkedCountDisplay">0</span>)</span>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="checkAllRows">
                                        <label class="custom-control-label" for="checkAllRows" style="cursor:pointer;"></label>
                                    </div>
                                </div>
                            </th>
                        @endif
                        <th rowspan="2" width="40" class="align-middle">NO</th>
                        <th rowspan="2" width="50" class="text-center align-middle">DETAIL</th>
                        <th rowspan="2" class="align-middle">TANGGAL ENTRY</th>
                        <th rowspan="2" class="align-middle">NO REGISTRASI</th>
                        <th rowspan="2" class="align-middle">ISSUE DATE</th>
                        <th rowspan="2" class="align-middle">NO. REVMODEL</th>
                        <th rowspan="2" class="align-middle">FAMILY</th>
                        <th rowspan="2" class="align-middle">CATEGORY</th>
                        <th rowspan="2" class="align-middle">CLAIM</th>
                        <th rowspan="2" class="align-middle">MODEL</th>
                        <th rowspan="2" class="align-middle">PART NAME</th>
                        <th rowspan="2" class="align-middle">PART NO.</th>
                        <th rowspan="2" class="align-middle">MOULD</th>
                        <th rowspan="2" class="align-middle">OWNER</th>
                        <th rowspan="2" class="d-none align-middle">SIMILAR PART</th>
                        <th rowspan="2" class="align-middle">SECTION</th>
                        <th rowspan="2" class="d-none align-middle">PROBLEM</th>
                        <th rowspan="2" class="align-middle">PROSES</th>
                        <th rowspan="2" class="d-none align-middle">CAUSE</th>
                        <th rowspan="2" class="d-none align-middle">COUNTERMEASURE</th>
                        <th rowspan="2" class="align-middle">PIC</th>
                        <th rowspan="2" class="align-middle">SUPPLIER</th>
                        <th rowspan="2" class="align-middle">DEFECT</th>
                        <th rowspan="2" class="align-middle">STATUS</th>
                        <th colspan="2" class="text-center align-middle">EVIDENCE</th>
                        <th rowspan="2" width="100" class="align-middle">ACTION</th>
                    </tr>
                    <tr>
                        <th class="text-center align-middle">FOTO</th>
                        <th class="text-center align-middle">REPORT</th>
                    </tr>
                </thead>
                <tbody>
                            @foreach ($kakotoras as $item)
                                <tr>
                                    @if(auth()->user()->role === 'admin')
                                        <td class="align-middle text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input row-checkbox" id="checkRow{{ $item->id }}" value="{{ $item->id }}">
                                                <label class="custom-control-label" for="checkRow{{ $item->id }}" style="cursor:pointer;"></label>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="details-control"><i class="fas fa-caret-right text-primary fa-lg"></i></td>
                                    <td>{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->no_reg ?? '-' }}</td>
                                    <td>
                                        @if($item->issue_date)
                                            @foreach(explode(',', $item->issue_date) as $d)
                                                <div style="white-space:nowrap;">{{ \Carbon\Carbon::parse(trim($d))->format('d/m/Y') }}</div>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->rev_model ?? '-' }}</td>
                                    <td>{{ $item->family ?? '-' }}</td>
                                    <td>{{ $item->category_nm_mp ?? '-' }}</td>
                                    <td>{{ $item->category_claim ?? '-' }}</td>
                                    <td>{{ $item->model ?? '-' }}</td>
                                    <td>{{ $item->part_name ?? '-' }}</td>
                                    <td>{{ $item->part_number ?? '-' }}</td>
                                    <td>{{ $item->mould ?? '-' }}</td>
                                    <td>{{ $item->owner_mould ?? '-' }}</td>
                                    <td>{{ $item->similar_part ?? '-' }}</td>
                                    <td>{{ $item->section ?? '-' }}</td>
                                    <td>{{ $item->problem ?? '-' }}</td>
                                    <td>{{ $item->process ?? '-' }}</td>
                                    <td>{{ $item->cause ?? '-' }}</td>
                                    <td>{{ $item->countermeasure ?? '-' }}</td>
                                    <td>{{ $item->pic ?? '-' }}</td>
                                    <td>{{ $item->supplier ?? '-' }}</td>
                                    <td>{{ $item->defect_category ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $item->status == 'Closed' ? 'success' : 'warning' }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if ($item->foto_path)
                                            <button type="button" class="btn btn-outline-info btn-sm shadow-sm rounded view-foto-btn-kakotora"
                                                data-src="{{ $item->foto_url }}"
                                                title="Lihat Foto">
                                                <i class="fas fa-image"></i>
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if ($item->form_analysis_path)
                                            <button type="button" class="btn btn-outline-info btn-sm shadow-sm rounded view-pdf-btn-kakotora"
                                                data-src="{{ $item->form_analysis_url }}"
                                                title="Lihat Form Analysis">
                                                <i class="fas fa-file-pdf"></i>
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center" style="gap: 5px;">
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-edit-kakotora shadow-sm rounded"
                                                data-id="{{ $item->id }}" data-date="{{ $item->date }}"
                                                data-no_reg="{{ $item->no_reg }}" data-issue_date="{{ $item->issue_date }}"
                                                data-rev_model="{{ $item->rev_model }}" data-family="{{ $item->family }}"
                                                data-category_nm_mp="{{ $item->category_nm_mp }}"
                                                data-category_claim="{{ $item->category_claim }}"
                                                data-model="{{ $item->model }}" data-part_number="{{ $item->part_number }}"
                                                data-part_name="{{ $item->part_name }}" data-mould="{{ $item->mould }}"
                                                data-owner_mould="{{ $item->owner_mould }}"
                                                data-similar_part="{{ $item->similar_part }}"
                                                data-section="{{ $item->section }}" data-process="{{ $item->process }}"
                                                data-problem="{{ $item->problem }}" data-cause="{{ $item->cause }}"
                                                data-countermeasure="{{ $item->countermeasure }}" data-pic="{{ $item->pic }}"
                                                data-supplier="{{ $item->supplier }}"
                                                data-defect_category="{{ $item->defect_category }}"
                                                data-status="{{ $item->status }}" data-remarks="{{ $item->remarks }}"
                                                data-file_url="{{ $item->form_analysis_path ? $item->form_analysis_url : '' }}"
                                                data-foto_url="{{ $item->foto_path ? $item->foto_url : '' }}"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm shadow-sm rounded"
                                                onclick="kakotoraDeleteRow('{{ route('kakotora.destroy', $item->id) }}', '{{ csrf_token() }}')"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div> <!-- End Table Container -->
        </div>
    </div>

    <!-- Modal Tambah Kakotora -->
    <div class="modal fade" id="modalTambahKakotora" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                        <i class="fas fa-plus-circle text-primary mr-2"></i> Tambah Data Kakotora
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAddKakotora" action="{{ route('kakotora.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plant }}">
                    <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                        <!-- Section 1 -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">INFORMASI UMUM</div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Tanggal Entry</label>
                                    <div class="col-sm-9">
                                        <input type="date" name="date" class="form-control form-control-sm border-0 shadow-sm" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">No Registrasi</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="no_reg" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Issue Date</label>
                                    <div class="col-sm-9">
                                        <div class="d-flex w-100 mb-1">
                                            <input type="date" id="add_issue_date_input" class="form-control form-control-sm border-0 shadow-sm">
                                            <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1" onclick="appendIssueDate('add')" title="Tambahkan ke list"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <div id="add_issue_date_container" class="w-100 mt-2"></div>
                                        <input type="hidden" name="issue_date" id="add_issue_date_hidden">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">No. Revmodel</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="rev_model" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2 -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">DETAIL PART & KATEGORI</div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Family</label>
                                    <div class="col-sm-9">
                                        <select name="family" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="">- Pilih Family -</option>
                                            <option value="M">M</option>
                                            <option value="C">C</option>
                                            <option value="S">S</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Category</label>
                                    <div class="col-sm-9">
                                        <select name="category_nm_mp" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="">- Pilih Kategori -</option>
                                            <option value="NM">NM</option>
                                            <option value="MP">MP</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Claim</label>
                                    <div class="col-sm-9">
                                        <select name="category_claim" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="">- Pilih Claim -</option>
                                            <option value="External">External</option>
                                            <option value="Internal">Internal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Model</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="model" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Part No.</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="part_number" id="add_part_number" list="partNumberList" class="form-control form-control-sm border-0 shadow-sm no-autoupper" placeholder="Cari / ketik Part No." autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Part Name</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="part_name" id="add_part_name" list="partNameList" class="form-control form-control-sm border-0 shadow-sm no-autoupper" placeholder="Cari / ketik Part Name" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Similar Part</label>
                                    <div class="col-sm-9">
                                        <div class="d-flex w-100 mb-1">
                                            <input type="text" id="add_similar_part_search" class="form-control form-control-sm border-0 shadow-sm no-autoupper" list="similarPartsList" placeholder="Cari / ketik part..." autocomplete="off">
                                            <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1" onclick="appendSimilarPart('add_similar_part_search', 'add')" title="Tambahkan ke list"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <div id="add_similar_part_container" class="w-100 mt-2"></div>
                                        <input type="hidden" name="similar_part" id="add_similar_part_hidden">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Mould</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="mould" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Owner of Mould</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="owner_mould" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3 -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">ANALISIS PROBLEM & TINDAKAN</div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Section</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="section" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Proses</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="process" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Problem</label>
                                    <div class="col-sm-9">
                                        <div class="d-flex w-100">
                                            <select class="form-control form-control-sm border-0 shadow-sm" name="problem" id="add_problem_select">
                                                <option value="">- Pilih Problem -</option>
                                                @foreach($problems as $p)
                                                    <option value="{{ $p }}">{{ $p }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1" onclick="deleteProblem('add_problem_select')" title="Hapus Problem Terpilih dari Master"><i class="fas fa-times"></i></button>
                                            <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1" onclick="addNewProblem('add_problem_select')" title="Tambah Problem Baru"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Cause</label>
                                    <div class="col-sm-9">
                                        <select class="custom-select custom-select-sm border-0 shadow-sm font-weight-bold text-dark mb-1" id="add_cause_4m" style="width: 120px;" onchange="updateHiddenCause('add')">
                                            <option value="">- 4M -</option>
                                            <option value="Man">Man</option>
                                            <option value="Material">Material</option>
                                            <option value="Method">Method</option>
                                            <option value="Machine">Machine</option>
                                        </select>
                                        <textarea id="add_cause_text" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="4" placeholder="Ketik deskripsi cause..." oninput="updateHiddenCause('add')"></textarea>
                                        <input type="hidden" name="cause" id="add_cause_hidden">
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Countermeasure</label>
                                    <div class="col-sm-9">
                                        <div class="d-flex flex-wrap align-items-start w-100 mb-1" style="gap: 5px;">
                                            <select class="custom-select custom-select-sm border-0 shadow-sm font-weight-bold text-dark" id="add_cm_4m" style="width: 110px;">
                                                <option value="">- 4M -</option>
                                                <option value="Man">Man</option>
                                                <option value="Material">Material</option>
                                                <option value="Method">Method</option>
                                                <option value="Machine">Machine</option>
                                            </select>
                                            <textarea id="add_cm_corrective" class="form-control form-control-sm border-0 shadow-sm no-autoupper flex-fill" rows="3" style="min-width: 120px; resize: none;" placeholder="Corrective..."></textarea>
                                            <textarea id="add_cm_preventive" class="form-control form-control-sm border-0 shadow-sm no-autoupper flex-fill" rows="3" style="min-width: 120px; resize: none;" placeholder="Preventive..."></textarea>
                                            <button type="button" class="btn btn-sm btn-primary shadow-sm" onclick="appendCountermeasure('add')" title="Tambahkan"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <div id="add_cm_container" class="w-100 mt-2"></div>
                                        <input type="hidden" name="countermeasure" id="add_cm_hidden">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">PIC</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="pic" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Supplier</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="supplier" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Kategori Defect</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="defect_category" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Status</label>
                                    <div class="col-sm-9">
                                        <select name="status" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="Open">Open</option>
                                            <option value="Closed">Closed</option>
                                            <option value="On Progress">On Progress</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Remarks</label>
                                    <div class="col-sm-9">
                                        <textarea name="remarks" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Form Analysis <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="file" name="form_analysis" id="add_form_analysis" class="form-control-file border-0 p-1 shadow-sm rounded" style="background:#fff;">
                                        <small class="text-muted">Max 10MB (pptx, xlsx, doc, pdf)</small>
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Foto <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="file" name="foto" id="add_foto" class="form-control-file border-0 p-1 shadow-sm rounded" style="background:#fff;" accept="image/*">
                                        <small class="text-muted">Max 5MB (jpeg, png, jpg, gif)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-light border px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm"><i class="fas fa-save mr-1"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Datalist Similar Part -->
    <datalist id="similarPartsList">
        @foreach($similarParts as $sp)
            <option value="{{ $sp->name }} ({{ $sp->part_number }})"></option>
        @endforeach
    </datalist>
    <datalist id="partNameList">
        @foreach($similarParts as $sp)
            <option value="{{ $sp->name }}">{{ $sp->part_number }}</option>
        @endforeach
    </datalist>
    <datalist id="partNumberList">
        @foreach($similarParts as $sp)
            <option value="{{ $sp->part_number }}">{{ $sp->name }}</option>
        @endforeach
    </datalist>

    <!-- Modal Edit Kakotora -->
    <div class="modal fade" id="modalEditKakotora" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                        <i class="fas fa-edit text-info mr-2"></i> Edit Data Kakotora
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditKakotora" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ $plant }}">
                    <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                        <!-- Section 1 -->
                        <div class="font-weight-bold text-info mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">INFORMASI UMUM</div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Tanggal Entry</label>
                                    <div class="col-sm-9">
                                        <input type="date" name="date" id="edit_date" class="form-control form-control-sm border-0 shadow-sm">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">No Registrasi</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="no_reg" id="edit_no_reg" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Issue Date</label>
                                    <div class="col-sm-9">
                                        <div class="d-flex w-100 mb-1">
                                            <input type="date" id="edit_issue_date_input" class="form-control form-control-sm border-0 shadow-sm">
                                            <button type="button" class="btn btn-sm btn-info shadow-sm ml-1" onclick="appendIssueDate('edit')" title="Tambahkan ke list"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <div id="edit_issue_date_container" class="w-100 mt-2"></div>
                                        <input type="hidden" name="issue_date" id="edit_issue_date_hidden">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">No. Revmodel</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="rev_model" id="edit_rev_model" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2 -->
                        <div class="font-weight-bold text-info mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">DETAIL PART & KATEGORI</div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Family</label>
                                    <div class="col-sm-9">
                                        <select name="family" id="edit_family" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="">- Pilih Family -</option>
                                            <option value="M">M (Matic)</option>
                                            <option value="C">C (Cube)</option>
                                            <option value="S">S (Sport)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Category</label>
                                    <div class="col-sm-9">
                                        <select name="category_nm_mp" id="edit_category_nm_mp" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="">- Pilih Kategori -</option>
                                            <option value="NM">NM (New Model)</option>
                                            <option value="MP">MP (Mass Pro)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Claim</label>
                                    <div class="col-sm-9">
                                        <select name="category_claim" id="edit_category_claim" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="">- Pilih Claim -</option>
                                            <option value="External">External</option>
                                            <option value="Internal">Internal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Model</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="model" id="edit_model" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Part No.</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="part_number" id="edit_part_number" list="partNumberList" class="form-control form-control-sm border-0 shadow-sm no-autoupper" autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Part Name</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="part_name" id="edit_part_name" list="partNameList" class="form-control form-control-sm border-0 shadow-sm no-autoupper" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Similar Part</label>
                                    <div class="col-sm-9">
                                        <div class="d-flex w-100 mb-1">
                                            <input type="text" id="edit_similar_part_search" class="form-control form-control-sm border-0 shadow-sm no-autoupper" list="similarPartsList" placeholder="Cari / ketik part..." autocomplete="off">
                                            <button type="button" class="btn btn-sm btn-info shadow-sm ml-1" onclick="appendSimilarPart('edit_similar_part_search', 'edit')" title="Tambahkan ke list"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <div id="edit_similar_part_container" class="w-100 mt-2"></div>
                                        <input type="hidden" name="similar_part" id="edit_similar_part_hidden">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Mould</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="mould" id="edit_mould" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Owner of Mould</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="owner_mould" id="edit_owner_mould" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3 -->
                        <div class="font-weight-bold text-info mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">ANALISIS PROBLEM & TINDAKAN</div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Section</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="section" id="edit_section" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Proses</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="process" id="edit_process" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Problem</label>
                                    <div class="col-sm-9">
                                        <div class="d-flex w-100">
                                            <select class="form-control form-control-sm border-0 shadow-sm" name="problem" id="edit_problem_select">
                                                <option value="">- Pilih Problem -</option>
                                                @foreach($problems as $p)
                                                    <option value="{{ $p }}">{{ $p }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1" onclick="deleteProblem('edit_problem_select')" title="Hapus Problem Terpilih dari Master"><i class="fas fa-times"></i></button>
                                            <button type="button" class="btn btn-sm btn-info shadow-sm ml-1" onclick="addNewProblem('edit_problem_select')" title="Tambah Problem Baru"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Cause</label>
                                    <div class="col-sm-9">
                                        <select class="custom-select custom-select-sm border-0 shadow-sm font-weight-bold text-dark mb-1" id="edit_cause_4m" style="width: 120px;" onchange="updateHiddenCause('edit')">
                                            <option value="">- 4M -</option>
                                            <option value="Man">Man</option>
                                            <option value="Material">Material</option>
                                            <option value="Method">Method</option>
                                            <option value="Machine">Machine</option>
                                        </select>
                                        <textarea id="edit_cause_text" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="4" placeholder="Ketik deskripsi cause..." oninput="updateHiddenCause('edit')"></textarea>
                                        <input type="hidden" name="cause" id="edit_cause_hidden">
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Countermeasure</label>
                                    <div class="col-sm-9">
                                        <div class="d-flex flex-wrap align-items-start w-100 mb-1" style="gap: 5px;">
                                            <select class="custom-select custom-select-sm border-0 shadow-sm font-weight-bold text-dark" id="edit_cm_4m" style="width: 110px;">
                                                <option value="">- 4M -</option>
                                                <option value="Man">Man</option>
                                                <option value="Material">Material</option>
                                                <option value="Method">Method</option>
                                                <option value="Machine">Machine</option>
                                            </select>
                                            <textarea id="edit_cm_corrective" class="form-control form-control-sm border-0 shadow-sm no-autoupper flex-fill" rows="3" style="min-width: 120px; resize: none;" placeholder="Corrective..."></textarea>
                                            <textarea id="edit_cm_preventive" class="form-control form-control-sm border-0 shadow-sm no-autoupper flex-fill" rows="3" style="min-width: 120px; resize: none;" placeholder="Preventive..."></textarea>
                                            <button type="button" class="btn btn-sm btn-info shadow-sm" onclick="appendCountermeasure('edit')" title="Tambahkan"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <div id="edit_cm_container" class="w-100 mt-2"></div>
                                        <input type="hidden" name="countermeasure" id="edit_cm_hidden">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">PIC</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="pic" id="edit_pic" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Supplier</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="supplier" id="edit_supplier" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Kategori Defect</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="defect_category" id="edit_defect_category" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700">Status</label>
                                    <div class="col-sm-9">
                                        <select name="status" id="edit_status" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="Open">Open</option>
                                            <option value="Closed">Closed</option>
                                            <option value="On Progress">On Progress</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row align-items-start mb-2">
                                    <label class="col-sm-3 col-form-label small font-weight-bold text-gray-700 pt-2">Remarks</label>
                                    <div class="col-sm-9">
                                        <textarea name="remarks" id="edit_remarks" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="mb-3" style="border: 1px solid #e9ecef; border-radius: 6px;">
                                    <div class="p-3">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> Form Analysis <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="file" name="form_analysis" id="edit_form_analysis" class="form-control-file form-control-sm border-0 shadow-sm" style="background:#fff;" accept=".pptx,.xlsx,.doc,.docx,.pdf">
                                            <button type="button" class="btn btn-sm btn-light text-danger ml-2 d-none" id="clear_edit_file" title="Hapus pilihan file" style="border: 1px solid #e3e6f0; border-radius:4px; padding:2px 8px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">Upload file form analysis. Max 10MB (pptx, xlsx, doc, pdf).</small>
                                        <div id="edit_file_preview" class="mt-2"></div>
                                    </div>
                                </div>
                                <div class="mb-3" style="border: 1px solid #e9ecef; border-radius: 6px;">
                                    <div class="p-3">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-image text-info mr-1"></i> Foto <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="file" name="foto" id="edit_foto" class="form-control-file form-control-sm border-0 shadow-sm" style="background:#fff;" accept="image/*">
                                            <button type="button" class="btn btn-sm btn-light text-danger ml-2 d-none" id="clear_edit_foto" title="Hapus pilihan file" style="border: 1px solid #e3e6f0; border-radius:4px; padding:2px 8px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted text-xs d-block mt-1">Upload foto. Max 5MB (jpeg, png, jpg, gif).</small>
                                        <div id="edit_foto_preview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-light border px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info px-4 font-weight-bold shadow-sm"><i class="fas fa-save mr-1"></i> Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>

    <!-- Float Menu untuk Bulk Delete -->
    @if(auth()->user()->role === 'admin')
    <div id="bulkActionMenu" class="position-fixed shadow-lg rounded" style="bottom: 80px; left: 50%; transform: translateX(-50%); display: none; z-index: 1050; background: white; padding: 15px; border: 1px solid #e3e6f0;">
        <div class="d-flex align-items-center">
            <span class="mr-3 font-weight-bold text-gray-800"><span id="bulkSelectedCount">0</span> Data Terpilih</span>
            <button class="btn btn-danger btn-sm shadow-sm" id="btnBulkDelete">
                <i class="fas fa-trash-alt mr-1"></i> Hapus Data
            </button>
        </div>
    </div>
    @endif

    <!-- Modal View PDF Kakotora -->
    <div class="modal fade" id="modalViewPdfKakotora" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;"><i class="fas fa-file-pdf mr-2 text-danger"></i> Lihat File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 d-flex flex-column" style="background-color: #f8fafc; height: 85vh; overflow: hidden;">
                    <iframe id="kakotoraPdfIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal View Foto Kakotora -->
    <div class="modal fade" id="modalViewFotoKakotora" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;"><i class="fas fa-image mr-2 text-info"></i> Lihat Foto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center" style="background-color: #f8fafc; border-radius: 0 0 12px 12px;">
                    <img id="kakotoraFotoImg" src="" alt="Foto Kakotora" style="max-width: 100%; max-height: 75vh; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                </div>
            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        function compressImage(file, maxWidth = 1280, maxHeight = 1280, quality = 0.7) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = () => {
                        let width = img.width;
                        let height = img.height;
                        if (width > height) {
                            if (width > maxWidth) {
                                height = Math.round((height * maxWidth) / width);
                                width = maxWidth;
                            }
                        } else {
                            if (height > maxHeight) {
                                width = Math.round((width * maxHeight) / height);
                                height = maxHeight;
                            }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        canvas.toBlob((blob) => {
                            if (blob) {
                                const newFile = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(newFile);
                            } else {
                                reject(new Error('Canvas to Blob failed'));
                            }
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = reject;
                };
                reader.onerror = reject;
            });
        }

        // Global error logger to capture any JS errors immediately
        window.onerror = function(msg, url, line, col, error) {
            alert("JS Error: " + msg + "\nURL: " + url + "\nLine: " + line);
        };

        // ============================================================
        // GLOBAL FUNCTIONS - defined outside document.ready
        // so they are always accessible from onclick attributes
        // ============================================================

        function kakotoraDeleteRow(url, token) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data kakotora ini akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = token;
                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Old function kept for safety
        function deleteKakotora(id, token, actionUrl) {
            kakotoraDeleteRow(actionUrl, token);
        }

        function addNewProblem(selectId) {
            Swal.fire({
                title: 'Tambah Problem Baru',
                input: 'text',
                customClass: {
                    input: 'no-autoupper'
                },
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'Masukkan problem baru...',
                    style: 'text-transform: none;'
                },
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: (name) => {
                    if(!name) {
                        Swal.showValidationMessage('Nama problem tidak boleh kosong!');
                        return false;
                    }
                    return $.ajax({
                        url: '{{ route("kakotora.add_problem") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            plant: '{{ $plant }}',
                            name: name
                        }
                    }).then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Gagal menyimpan data');
                        }
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.message || error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    var newProblem = result.value.problem;
                    $('#add_problem_select').append(new Option(newProblem, newProblem, false, false));
                    $('#edit_problem_select').append(new Option(newProblem, newProblem, false, false));
                    
                    $('#' + selectId).val(newProblem);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Problem baru telah ditambahkan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        function deleteProblem(selectId) {
            var select = document.getElementById(selectId);
            var name = select.value;
            if (!name) {
                Swal.fire('Peringatan', 'Silakan pilih problem yang akan dihapus terlebih dahulu.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Hapus Problem?',
                text: 'Problem "' + name + '" akan dihapus permanen dari daftar master opsi. (Data kakotora yang sudah terlanjur menggunakan problem ini tidak akan terhapus, namun opsinya hilang dari dropdown)',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: '{{ route("kakotora.delete_problem") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            plant: '{{ $plant }}',
                            name: name
                        }
                    }).then(response => {
                        if (!response.success) {
                            throw new Error('Gagal menghapus data');
                        }
                        return response;
                    }).catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.message || error}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove from both selects
                    $("#add_problem_select option[value='" + name + "']").remove();
                    $("#edit_problem_select option[value='" + name + "']").remove();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Problem telah dihapus dari daftar.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        $(document).ready(function () {
            // Prevent Bootstrap modal from stealing focus from SweetAlert
            $.fn.modal.Constructor.prototype._enforceFocus = function() {};

            window.updateHiddenSimilarPart = function(prefix) {
                var container = document.getElementById(prefix + '_similar_part_container');
                var hidden = document.getElementById(prefix + '_similar_part_hidden');
                var inputs = container.querySelectorAll('input[type="text"]');
                var vals = [];
                var count = inputs.length;
                inputs.forEach(function(inp, index) {
                    var val = inp.value.trim();
                    // Bersihkan nomor (1., 2.) jika sudah ada dari load data lama
                    val = val.replace(/^\d+\.\s*/, '');
                    
                    if (count > 1) {
                        vals.push((index + 1) + '. ' + val);
                    } else {
                        vals.push(val);
                    }
                });
                hidden.value = vals.join('\n');
            };

            window.removeSimilarPart = function(btn, prefix) {
                btn.parentElement.remove();
                window.updateHiddenSimilarPart(prefix);
            };

            window.addSimilarPartElement = function(prefix, val) {
                var container = document.getElementById(prefix + '_similar_part_container');
                var div = document.createElement('div');
                div.className = 'd-flex align-items-center mb-1';
                div.innerHTML = '<input type="text" class="form-control form-control-sm border-0 shadow-sm bg-light font-weight-bold" value="' + val + '" readonly>' +
                                '<button type="button" class="btn btn-sm btn-danger shadow-sm ml-1" onclick="window.removeSimilarPart(this, \'' + prefix + '\')" title="Hapus Part"><i class="fas fa-times"></i></button>';
                container.appendChild(div);
                window.updateHiddenSimilarPart(prefix);
            };

            window.appendSimilarPart = function(inputId, prefix) {
                var input = document.getElementById(inputId);
                var val = input.value.trim();
                
                if (val !== '') {
                    // Cek apakah value ada di datalist
                    var listId = input.getAttribute('list');
                    var datalist = document.getElementById(listId);
                    var exists = false;
                    
                    if (datalist) {
                        var options = datalist.options;
                        for (var i = 0; i < options.length; i++) {
                            if (options[i].value === val) {
                                exists = true;
                                break;
                            }
                        }
                    }
                    
                    if (!exists) {
                        Swal.fire('Peringatan', 'Part tidak terdaftar! Harap pilih part dari daftar yang tersedia.', 'warning');
                        return;
                    }

                    window.addSimilarPartElement(prefix, val);
                    input.value = '';
                    input.focus();
                }
            };
            window.updateHiddenCause = function(prefix) {
                var m = document.getElementById(prefix + '_cause_4m').value;
                var txt = document.getElementById(prefix + '_cause_text').value.trim();
                var hidden = document.getElementById(prefix + '_cause_hidden');
                if (m && txt) {
                    hidden.value = '[' + m + '] ' + txt;
                } else if (txt) {
                    hidden.value = txt;
                } else if (m) {
                    hidden.value = '[' + m + '] ';
                } else {
                    hidden.value = '';
                }
            };

            window.addCmElement = function(prefix, m, corr, prev) {
                var container = document.getElementById(prefix + '_cm_container');
                var div = document.createElement('div');
                div.className = 'd-flex align-items-start mb-2 bg-light p-2 rounded shadow-sm position-relative';
                
                var compiledText = '[' + m + '] Corrective: ' + corr + ' | Preventive: ' + prev;
                
                div.innerHTML = '<div class="flex-grow-1 small">' +
                                '<strong>[' + m + ']</strong><br>' +
                                '<span class="text-dark font-weight-bold">Corrective:</span> ' + corr + '<br>' +
                                '<span class="text-dark font-weight-bold">Preventive:</span> ' + prev + 
                                '</div>' +
                                '<input type="hidden" class="cm-raw-value" value="' + compiledText.replace(/"/g, '&quot;') + '">' +
                                '<button type="button" class="btn btn-sm btn-danger ml-2" onclick="window.removeCm(this, \'' + prefix + '\')" title="Hapus"><i class="fas fa-times"></i></button>';
                container.appendChild(div);
                window.updateHiddenCm(prefix);
            };

            window.removeCm = function(btn, prefix) {
                btn.parentElement.remove();
                window.updateHiddenCm(prefix);
            };

            window.updateHiddenCm = function(prefix) {
                var container = document.getElementById(prefix + '_cm_container');
                var hidden = document.getElementById(prefix + '_cm_hidden');
                var raws = container.querySelectorAll('.cm-raw-value');
                var vals = [];
                var count = raws.length;
                raws.forEach(function(inp, index) {
                    var val = inp.value;
                    val = val.replace(/^\d+\.\s*/, '');
                    if (count > 1) {
                        vals.push((index + 1) + '. ' + val);
                    } else {
                        vals.push(val);
                    }
                });
                hidden.value = vals.join('\n');
            };

            window.appendCountermeasure = function(prefix) {
                var m = document.getElementById(prefix + '_cm_4m');
                var corr = document.getElementById(prefix + '_cm_corrective');
                var prev = document.getElementById(prefix + '_cm_preventive');
                
                if (!m.value) { Swal.fire('Peringatan', 'Pilih 4M terlebih dahulu!', 'warning'); return; }
                if (!corr.value.trim() && !prev.value.trim()) { Swal.fire('Peringatan', 'Isi Corrective atau Preventive minimal satu!', 'warning'); return; }
                
                window.addCmElement(prefix, m.value, corr.value.trim(), prev.value.trim());
                
                m.value = '';
                corr.value = '';
                prev.value = '';
                m.focus();
            };

            window.updateHiddenIssueDate = function(prefix) {
                var container = document.getElementById(prefix + '_issue_date_container');
                var hidden = document.getElementById(prefix + '_issue_date_hidden');
                var inputs = container.querySelectorAll('input[type="hidden"]');
                var vals = [];
                inputs.forEach(function(inp) {
                    var val = inp.value.trim();
                    if(val) vals.push(val);
                });
                hidden.value = vals.join(',');
            };

            window.removeIssueDate = function(btn, prefix) {
                btn.parentElement.remove();
                window.updateHiddenIssueDate(prefix);
            };

            window.addIssueDateElement = function(prefix, val) {
                var container = document.getElementById(prefix + '_issue_date_container');
                var div = document.createElement('div');
                div.className = 'd-flex align-items-center mb-1';
                
                var parts = val.split('-');
                var displayVal = parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : val;

                div.innerHTML = '<input type="text" class="form-control form-control-sm border-0 shadow-sm bg-light font-weight-bold" value="' + displayVal + '" readonly>' +
                                '<input type="hidden" value="' + val + '">' +
                                '<button type="button" class="btn btn-sm btn-danger shadow-sm ml-1" onclick="window.removeIssueDate(this, \'' + prefix + '\')" title="Hapus"><i class="fas fa-times"></i></button>';
                container.appendChild(div);
                window.updateHiddenIssueDate(prefix);
            };

            window.appendIssueDate = function(prefix) {
                var input = document.getElementById(prefix + '_issue_date_input');
                var val = input.value.trim();
                
                if (val !== '') {
                    window.addIssueDateElement(prefix, val);
                    input.value = '';
                } else {
                    Swal.fire('Peringatan', 'Pilih tanggal terlebih dahulu!', 'warning');
                }
            };

            var isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
            var colOffset = isAdmin ? 1 : 0;

            var formatChildRow = function (d) {
                var cmRaw = d[19 + colOffset] || '-';
                var cmFormatted = cmRaw;
                
                if (cmRaw !== '-') {
                    var lines = cmRaw.split('\n');
                    var fLines = [];
                    lines.forEach(function(line) {
                        var mMatch = line.match(/^(?:(\d+\.)\s*)?(\[(?:Man|Material|Method|Machine)\])\s*Corrective:\s*(.*?)\s*\|\s*Preventive:\s*(.*)$/si);
                        if (mMatch) {
                            var num = mMatch[1] ? mMatch[1] + ' ' : '';
                            var f = '<div class="mb-2 text-dark">' + num + '<strong>' + mMatch[2] + '</strong><br>' +
                                    '<div style="padding-left: 1.5rem;"><span class="font-weight-bold">&bull; Corrective:</span> ' + mMatch[3] + '<br>' +
                                    '<span class="font-weight-bold">&bull; Preventive:</span> ' + mMatch[4] + '</div></div>';
                            fLines.push(f);
                        } else {
                            fLines.push('<div>' + line + '</div>');
                        }
                    });
                    cmFormatted = fLines.join('');
                }

                return '<div class="p-3" style="background-color: #f8f9fc;">' +
                    '<table class="table table-sm table-borderless mb-0">' +
                    '<tr>' +
                    '<td style="width: 15%; font-weight: bold; padding: 0.5rem;">Similar Part</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[14 + colOffset] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Problem</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[16 + colOffset] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Cause</td>' +
                    '<td style="white-space: pre-wrap; padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + (d[18 + colOffset] || '-') + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="font-weight: bold; padding: 0.5rem;">Countermeasure</td>' +
                    '<td style="padding: 0.5rem; border-left: 1px solid #e3e6f0;">' + cmFormatted + '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>';
            };

            var table = $('#dataTableKakotora').DataTable({
                dom: "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                     "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                "order": [],
                "autoWidth": false,
                "columnDefs": [
                    { "orderable": false, "targets": isAdmin ? [0, 1 + colOffset] : [1] },
                    { "visible": false, "targets": [14 + colOffset, 16 + colOffset, 18 + colOffset, 19 + colOffset] }
                ],
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total records)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                initComplete: function(settings, json) {
                    // ponytail: Prevent FOUC (Flash of Unstyled Content) by showing table only after fully initialized
                    $('#tableLoader').hide();
                    $('#tableContainer').fadeIn('fast', function() {
                        table.columns.adjust(); // fix squished headers
                    });
                },
                drawCallback: function(settings) {
                    // ponytail: Highlight search keywords safely using TreeWalker (supports multiple words & overlapping)
                    var api = this.api();
                    var tbody = api.table().body();
                    
                    // 1. Unmark previous highlights and merge split text nodes
                    $(tbody).find('mark.hlt').each(function() {
                        $(this).replaceWith(this.childNodes);
                    });
                    tbody.normalize();

                    var searchStr = api.search();
                    if (!searchStr) return;

                    var keywords = searchStr.split(' ').filter(w => w.trim().length > 1);
                    if (keywords.length === 0) return;

                    // Escape regex chars and sort by length descending to match longer words first
                    keywords = keywords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).sort((a, b) => b.length - a.length);
                    var regex = new RegExp("(" + keywords.join('|') + ")", "gi");

                    api.rows({ page: 'current' }).nodes().each(function(row) {
                        $(row).find('td:not(:last-child)').each(function() {
                            var walker = document.createTreeWalker(this, NodeFilter.SHOW_TEXT, null, false);
                            var nodes = [];
                            while (walker.nextNode()) {
                                nodes.push(walker.currentNode);
                            }
                            nodes.forEach(function(node) {
                                var text = node.nodeValue;
                                if (text.trim() && regex.test(text)) {
                                    var span = document.createElement('span');
                                    span.innerHTML = text.replace(regex, "<mark class='hlt' style='background-color: #fffa90; color: #000000; padding: 0 2px; border-radius: 2px;'>$1</mark>");
                                    var frag = document.createDocumentFragment();
                                    while (span.firstChild) {
                                        frag.appendChild(span.firstChild);
                                    }
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

            // Prevent normal form submission unless printing
            $('#filterFormKakotora').on('submit', function (e) {
                if (document.activeElement && document.activeElement.hasAttribute('formaction')) {
                    return;
                }
                e.preventDefault();
            });

            // Instant smart search
            $('input[name="search"]').on('keyup input', function () {
                // ponytail: Smart NLP Search - Remove Indonesian stop words so conversational queries like 
                // "tolong keluarkan problem bintik di proses plating" become "bintik plating".
                let input = $(this).val().toLowerCase();
                let stops = ['tolong', 'keluarkan', 'semua', 'di', 'pada', 'proses', 'nah', 'langsung', 'nya', 'tampilkan', 'cari', 'carikan', 'yang', 'ada', 'dan', 'atau', 'buatkan', 'buat', 'data', 'problem', 'masalah', 'part', 'kakotora', 'database', 'dari', 'ke', 'untuk'];
                let keywords = input.split(/[\s,.]+/).filter(w => w && !stops.includes(w));
                table.search(keywords.length ? keywords.join(' ') : input).draw();
            });

            // Instant claim filter (Column index 8)
            $('select[name="category_claim"]').on('change', function () {
                var val = $(this).val();
                table.column(8 + colOffset).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
            });

            // Instant status filter (Column index 23)
            $('select[name="status"]').on('change', function () {
                var val = $(this).val();
                table.column(23 + colOffset).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
            });

            // Reset filters client-side
            $('#btnResetFilter').on('click', function () {
                $('input[name="search"]').val('');
                $('select[name="category_claim"]').val('');
                $('select[name="status"]').val('');
                
                table.search('').columns().search('').draw();
            });

            // Run initial filters if preset
            var initialSearch = $('input[name="search"]').val();
            if (initialSearch) {
                table.search(initialSearch);
            }
            var initialClaim = $('select[name="category_claim"]').val();
            if (initialClaim) {
                table.column(8 + colOffset).search('^' + $.fn.dataTable.util.escapeRegex(initialClaim) + '$', true, false);
            }
            var initialStatus = $('select[name="status"]').val();
            if (initialStatus) {
                table.column(23 + colOffset).search('^' + $.fn.dataTable.util.escapeRegex(initialStatus) + '$', true, false);
            }
            if (initialSearch || initialClaim || initialStatus) {
                table.draw();
            }

            // Add event listener for opening and closing details
            $('#dataTableKakotora tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var icon = $(this).find('i');

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    icon.removeClass('fa-caret-down').addClass('fa-caret-right');
                }
                else {
                    row.child(formatChildRow(row.data())).show();
                    tr.addClass('shown');
                    icon.removeClass('fa-caret-right').addClass('fa-caret-down');
                    
                    // Highlight details if search is active
                    var searchStr = table.search();
                    if (searchStr) {
                        var keywords = searchStr.split(' ').filter(w => w.trim().length > 1);
                        if (keywords.length > 0) {
                            keywords = keywords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).sort((a, b) => b.length - a.length);
                            var regex = new RegExp("(" + keywords.join('|') + ")", "gi");
                            var walker = document.createTreeWalker(row.child()[0], NodeFilter.SHOW_TEXT, null, false);
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
                        }
                    }
                }
            });

            $('#dataTableKakotora tbody').on('click', '.btn-edit-kakotora', function () {
                var id = $(this).data('id');
                var date = $(this).data('date');
                var no_reg = $(this).data('no_reg');
                var issue_date = $(this).data('issue_date');
                var rev_model = $(this).data('rev_model');
                var family = $(this).data('family');
                var category_nm_mp = $(this).data('category_nm_mp');
                var category_claim = $(this).data('category_claim');
                var model = $(this).data('model');
                var part_number = $(this).data('part_number');
                var part_name = $(this).data('part_name');
                var mould = $(this).data('mould');
                var owner_mould = $(this).data('owner_mould');
                var similar_part = $(this).data('similar_part');
                var section = $(this).data('section');
                var process = $(this).data('process');
                var problem = $(this).data('problem');
                var cause = $(this).data('cause');
                var countermeasure = $(this).data('countermeasure');
                var pic = $(this).data('pic');
                var supplier = $(this).data('supplier');
                var defect_category = $(this).data('defect_category');
                var status = $(this).data('status');
                var remarks = $(this).data('remarks');
                var file_url = $(this).data('file_url');
                var foto_url = $(this).data('foto_url');

                // Set values to Edit Modal
                $('#edit_date').val(date);
                $('#edit_no_reg').val(no_reg);

                // Populate Issue Dates
                $('#edit_issue_date_container').empty();
                $('#edit_issue_date_hidden').val('');
                if (issue_date) {
                    let dates = issue_date.split(',');
                    dates.forEach(function(d) {
                        let trimmed = d.trim();
                        if (trimmed) {
                            addIssueDateElement('edit', trimmed);
                        }
                    });
                }
                updateHiddenIssueDate('edit');

                $('#edit_rev_model').val(rev_model);
                $('#edit_family').val(family);
                $('#edit_category_nm_mp').val(category_nm_mp);
                $('#edit_category_claim').val(category_claim);
                $('#edit_model').val(model);
                $('#edit_part_number').val(part_number);
                $('#edit_part_name').val(part_name);
                $('#edit_mould').val(mould);
                $('#edit_owner_mould').val(owner_mould);
                
                var container = $('#edit_similar_part_container');
                container.empty();
                if(similar_part) {
                    var parts = similar_part.split('\n');
                    parts.forEach(function(p) {
                        if(p.trim() !== '') {
                            window.addSimilarPartElement('edit', p.trim());
                        }
                    });
                }
                window.updateHiddenSimilarPart('edit');

                $('#edit_section').val(section);
                $('#edit_process').val(process);
                
                // Set problem field properly
                var editProbSel = $('#edit_problem_select');
                var exists = false;
                editProbSel.find('option').each(function(){
                    if($(this).val() == problem && problem != '') {
                        exists = true;
                    }
                });
                if(!exists && problem) {
                    editProbSel.append(new Option(problem, problem, false, false));
                }
                editProbSel.val(problem);

                // Parse Cause
                var mMatchCause = (cause || '').match(/^\[(Man|Material|Method|Machine)\]\s*(.*)$/si);
                if (mMatchCause) {
                    $('#edit_cause_4m').val(mMatchCause[1]);
                    $('#edit_cause_text').val(mMatchCause[2]);
                } else {
                    $('#edit_cause_4m').val('');
                    $('#edit_cause_text').val(cause || '');
                }
                $('#edit_cause_hidden').val(cause || '');

                // Parse Countermeasure
                var cmContainer = $('#edit_cm_container');
                cmContainer.empty();
                if(countermeasure) {
                    var cmParts = countermeasure.split('\n');
                    cmParts.forEach(function(p) {
                        var cleanP = p.replace(/^\d+\.\s*/, '').trim();
                        if(cleanP !== '') {
                            var mMatchCm = cleanP.match(/^\[(Man|Material|Method|Machine)\]\s*Corrective:\s*(.*?)\s*\|\s*Preventive:\s*(.*)$/si);
                            if (mMatchCm) {
                                window.addCmElement('edit', mMatchCm[1], mMatchCm[2], mMatchCm[3]);
                            } else {
                                // Legacy data
                                window.addCmElement('edit', 'Method', cleanP, '-');
                            }
                        }
                    });
                }
                window.updateHiddenCm('edit');
                $('#edit_pic').val(pic);
                $('#edit_supplier').val(supplier);
                $('#edit_defect_category').val(defect_category);
                $('#edit_status').val(status);
                $('#edit_remarks').val(remarks);

                if (file_url) {
                    let fileName = file_url.split('/').pop();
                    $('#edit_file_preview').html(`
                        <label class="small font-weight-bold mb-1 d-block text-muted">File tersimpan:</label>
                        <div id="form-analysis-file-row" class="d-flex align-items-center mb-1 p-1 border rounded bg-light" style="overflow:hidden; font-size: 0.75rem;">
                            <i class="fas fa-file-pdf text-danger mr-1 flex-shrink-0" style="font-size: 1.1rem;"></i>
                            <span class="text-truncate mr-2 flex-grow-1" style="min-width:0;" title="${fileName}">${fileName}</span>
                            <button type="button" class="btn btn-info btn-sm mr-1 flex-shrink-0 view-pdf-btn-kakotora" data-src="${file_url}" style="font-size:0.65rem; padding:2px 6px;">View</button>
                            <button type="button" class="btn btn-danger btn-sm flex-shrink-0 btn-delete-pdf-ajax" data-id="${id}" style="font-size:0.65rem; padding:2px 6px;">Hapus</button>
                        </div>
                    `);
                } else {
                    $('#edit_file_preview').html('');
                }

                if (foto_url) {
                    let fotoName = foto_url.split('/').pop();
                    $('#edit_foto_preview').html(`
                        <label class="small font-weight-bold mb-1 d-block text-muted">Foto tersimpan:</label>
                        <div id="foto-file-row" class="d-flex align-items-center mb-1 p-1 border rounded bg-light" style="overflow:hidden; font-size: 0.75rem;">
                            <i class="fas fa-image text-info mr-1 flex-shrink-0" style="font-size: 1.1rem;"></i>
                            <span class="text-truncate mr-2 flex-grow-1" style="min-width:0;" title="${fotoName}">${fotoName}</span>
                            <button type="button" class="btn btn-info btn-sm mr-1 flex-shrink-0 view-foto-btn-kakotora" data-src="${foto_url}" style="font-size:0.65rem; padding:2px 6px;">View</button>
                            <button type="button" class="btn btn-danger btn-sm flex-shrink-0 btn-delete-foto-ajax" data-id="${id}" style="font-size:0.65rem; padding:2px 6px;">Hapus</button>
                        </div>
                    `);
                } else {
                    $('#edit_foto_preview').html('');
                }

                // Set Action URL
                $('#formEditKakotora').attr('action', '{{ url("kakotora") }}/' + id);

                // Show Modal
                $('#modalEditKakotora').modal('show');
            });

            // Bulk Delete Logic
            const checkAllBtn = $('#checkAllRows');
            const bulkMenu = $('#bulkActionMenu');
            const bulkSelectedCount = $('#bulkSelectedCount');
            const btnBulkDelete = $('#btnBulkDelete');
            const countDisplay = $('#checkedCountDisplay');

            function updateCount() {
                const checkedCount = $('.row-checkbox:checked').length;
                const totalCheckboxes = $('.row-checkbox').length;
                countDisplay.text(checkedCount);
                if (bulkSelectedCount.length > 0) {
                    bulkSelectedCount.text(checkedCount);
                }
                
                if(totalCheckboxes > 0) {
                    checkAllBtn.prop('checked', checkedCount === totalCheckboxes);
                }

                if (checkedCount > 0) {
                    bulkMenu.fadeIn(200);
                } else {
                    bulkMenu.fadeOut(200);
                }

                $('.row-checkbox').each(function() {
                    const row = $(this).closest('tr');
                    if ($(this).is(':checked')) {
                        row.css('background-color', 'rgba(78, 115, 223, 0.05)');
                    } else {
                        row.css('background-color', '');
                    }
                });
            }

            checkAllBtn.on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.row-checkbox').prop('checked', isChecked);
                updateCount();
            });

            $('#dataTableKakotora tbody').on('change', '.row-checkbox', function(e) {
                e.stopPropagation();
                updateCount();
            });

            table.on('draw', function() {
                updateCount();
            });

            if (btnBulkDelete.length > 0) {
                btnBulkDelete.on('click', function() {
                    const selectedIds = $('.row-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (selectedIds.length === 0) return;

                    Swal.fire({
                        title: 'Konfirmasi Hapus',
                        text: "Apakah Anda yakin ingin menghapus " + selectedIds.length + " data yang dipilih? Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74a3b',
                        cancelButtonColor: '#858796',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Menghapus Data...',
                                html: 'Mohon tunggu sebentar',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: '{{ route("kakotora.bulk_destroy") }}',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: selectedIds
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil!',
                                            text: response.message,
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            if (response.redirect) {
                                                window.location.href = response.redirect;
                                            } else {
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        Swal.fire('Gagal!', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                                }
                            });
                        }
                    });
                });
            }
            // Single Delete Logic - handled by global deleteKakotora() function

            // Delete Form Analysis logic via AJAX
            $(document).on('click', '.btn-delete-pdf-ajax', function() {
                var btn = $(this);
                var id = btn.data('id');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'File PDF akan dihapus secara langsung dan permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Hapus File!',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus File...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '{{ url("kakotora/delete-pdf") }}/' + id,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#edit_file_preview').empty();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Update local DOM so the PDF button disappears from the table too
                                        var editBtn = $('.btn-edit-kakotora[data-id="'+id+'"]');
                                        editBtn.attr('data-file_url', '');
                                        editBtn.data('file_url', '');
                                        editBtn.closest('div').find('.view-pdf-btn-kakotora').remove();
                                    });
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Gagal menghapus file.', 'error');
                            }
                        });
                    }
                });
            });

            // Delete Foto logic via AJAX
            $(document).on('click', '.btn-delete-foto-ajax', function() {
                var btn = $(this);
                var id = btn.data('id');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Foto akan dihapus secara langsung dan permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Hapus Foto!',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus Foto...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '{{ url("kakotora/delete-foto") }}/' + id,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#edit_foto_preview').empty();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        var editBtn = $('.btn-edit-kakotora[data-id="'+id+'"]');
                                        editBtn.attr('data-foto_url', '');
                                        editBtn.data('foto_url', '');
                                        // Optional: update table cell to remove icon
                                        location.reload(); // Reload for safety so the table is updated
                                    });
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Gagal menghapus foto.', 'error');
                            }
                        });
                    }
                });
            });

            // View PDF logic
            $(document).on('click', '.view-pdf-btn-kakotora', function(e) {
                e.preventDefault();
                const url = $(this).data('src');
                $('#kakotoraPdfIframe').attr('src', url);
                $('#modalViewPdfKakotora').modal('show');
            });

            // Clear iframe on hide
            $('#modalViewPdfKakotora').on('hidden.bs.modal', function () {
                $('#kakotoraPdfIframe').attr('src', '');
            });

            // View Foto logic
            $(document).on('click', '.view-foto-btn-kakotora', function(e) {
                e.preventDefault();
                const url = $(this).data('src');
                $('#kakotoraFotoImg').attr('src', url);
                $('#modalViewFotoKakotora').modal('show');
            });

            // Clear image on hide
            $('#modalViewFotoKakotora').on('hidden.bs.modal', function () {
                $('#kakotoraFotoImg').attr('src', '');
            });

            $('#modalTambahKakotora').on('hidden.bs.modal', function () {
                $(this).find('form')[0].reset();
                $('#add_similar_part_container').empty();
                $('#add_similar_part_hidden').val('');
                $('#add_cause_hidden').val('');
                $('#add_cm_container').empty();
                $('#add_cm_hidden').val('');
                $('#add_issue_date_container').empty();
                $('#add_issue_date_hidden').val('');
            });

            // Handle clear input file
            $('#edit_form_analysis').on('change', function() {
                if ($(this)[0].files.length > 0) {
                    $('#clear_edit_file').removeClass('d-none');
                } else {
                    $('#clear_edit_file').addClass('d-none');
                }
            });

            $('#clear_edit_file').on('click', function() {
                $('#edit_form_analysis').val('');
                $(this).addClass('d-none');
            });

            $('#edit_foto').on('change', async function(e) {
                const file = e.target.files[0];
                if (file && file.type.match(/image.*/)) {
                    try {
                        const compressedFile = await compressImage(file);
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        e.target.files = dataTransfer.files;
                    } catch (err) {
                        console.error("Gagal kompres foto:", err);
                    }
                }

                if ($(this)[0].files.length > 0) {
                    $('#clear_edit_foto').removeClass('d-none');
                } else {
                    $('#clear_edit_foto').addClass('d-none');
                }
            });

            $('#add_foto').on('change', async function(e) {
                const file = e.target.files[0];
                if (file && file.type.match(/image.*/)) {
                    try {
                        const compressedFile = await compressImage(file);
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        e.target.files = dataTransfer.files;
                    } catch (err) {
                        console.error("Gagal kompres foto:", err);
                    }
                }
            });

            $('#clear_edit_foto').on('click', function() {
                $('#edit_foto').val('');
                $(this).addClass('d-none');
            });

            // Autofill Part Name & Part No
            window.partsData = @json($similarParts);
            
            function autofillPart(prefix, source) {
                var nameInput = $('#' + prefix + '_part_name');
                var noInput = $('#' + prefix + '_part_number');
                
                if (source === 'name') {
                    var val = nameInput.val().trim();
                    var match = window.partsData.find(p => p.name === val);
                    if (match) noInput.val(match.part_number);
                } else if (source === 'number') {
                    var val = noInput.val().trim();
                    var match = window.partsData.find(p => p.part_number === val);
                    if (match) nameInput.val(match.name);
                }
            }
            
            $('#add_part_name').on('change input', function() { autofillPart('add', 'name'); });
            $('#add_part_number').on('change input', function() { autofillPart('add', 'number'); });
            $('#edit_part_name').on('change input', function() { autofillPart('edit', 'name'); });
            $('#edit_part_number').on('change input', function() { autofillPart('edit', 'number'); });

        });
    function validateKakotoraForm(e, prefix) {
        // Cek Similar Part
        const similarPartHidden = document.getElementById(prefix + '_similar_part_hidden');
        if (!similarPartHidden || !similarPartHidden.value.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field Similar Part tidak boleh kosong. Silakan isi dan tambahkan (+) part yang mirip.',
                icon: 'warning'
            });
            return false;
        }

        // Cek Problem
        const problemSelect = document.getElementById(prefix + '_problem_select');
        if (!problemSelect || !problemSelect.value.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field Problem tidak boleh kosong. Silakan pilih atau tambahkan problem.',
                icon: 'warning'
            });
            return false;
        }

        // Cek Cause
        const causeHidden = document.getElementById(prefix + '_cause_hidden');
        if (!causeHidden || !causeHidden.value.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field Cause tidak boleh kosong. Silakan isi 4M dan deskripsinya.',
                icon: 'warning'
            });
            return false;
        }

        // Cek Countermeasure
        const cmHidden = document.getElementById(prefix + '_cm_hidden');
        if (!cmHidden || !cmHidden.value.trim()) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Field Countermeasure tidak boleh kosong. Silakan isi setidaknya satu countermeasure dan klik (+).',
                icon: 'warning'
            });
            return false;
        }

        // Cek Jika ada ketikan di Countermeasure tapi belum di klik +
        const cm4m = document.getElementById(prefix + '_cm_4m');
        const cmCorrective = document.getElementById(prefix + '_cm_corrective');
        const cmPreventive = document.getElementById(prefix + '_cm_preventive');
        
        if (cmCorrective && cmPreventive && cm4m) {
            if (cmCorrective.value.trim() !== '' || cmPreventive.value.trim() !== '') {
                e.preventDefault();
                Swal.fire({
                    title: 'Belum Ditambahkan!',
                    text: 'Anda sudah mengetik Countermeasure, tetapi belum klik tombol (+). Silakan klik tanda (+) terlebih dahulu agar data masuk ke daftar.',
                    icon: 'warning'
                });
                return false;
            }
        }

        // Cek Form Analysis
        var fileInput = document.getElementById(prefix + '_form_analysis');
        var filePreview = document.getElementById(prefix + '_file_preview');
        var hasFile = (filePreview && filePreview.innerHTML.trim() !== '') || (fileInput && fileInput.value !== '');
        
        if (!hasFile) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Form Analysis wajib diupload!',
                icon: 'warning'
            });
            return false;
        }

        // Cek Foto
        var fotoInput = document.getElementById(prefix + '_foto');
        var fotoPreview = document.getElementById(prefix + '_foto_preview');
        var hasFoto = (fotoPreview && fotoPreview.innerHTML.trim() !== '') || (fotoInput && fotoInput.value !== '');

        if (!hasFoto) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap!',
                text: 'Foto wajib diupload!',
                icon: 'warning'
            });
            return false;
        }

        return true;
    }

    document.getElementById('formAddKakotora').addEventListener('submit', function(e) {
        validateKakotoraForm(e, 'add');
    });

    document.getElementById('formEditKakotora').addEventListener('submit', function(e) {
        validateKakotoraForm(e, 'edit');
    });
    </script>
@endpush




