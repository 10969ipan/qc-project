@extends('layouts.admin')

@section('title', 'Cross Cut')

@section('content')
<style>
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #checksheetTable, #sortirTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    
    #checksheetTable td, #checksheetTable th,
    #sortirTable td, #sortirTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }

    #checksheetTable tbody td,
    #sortirTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.68rem !important;
        padding: 4px 6px !important;
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th,
    #sortirTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 6px 12px !important; /* Wider padding so it's not cramped sideways */
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 2px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important; /* Force all headers to be side-by-side */
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export,
    #sortirTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    #checksheetTable .btn,
    #sortirTable .btn {
        min-width: 0 !important; /* Overrides 110px inline style */
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
    #checksheetTable .badge,
    #sortirTable .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    /* Exact sticky heights since headers no longer wrap */
    #checksheetTable > thead > tr:nth-child(1) > th,
    #sortirTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 35px !important; 
    }
    #checksheetTable > thead > tr:nth-child(2) > th,
    #sortirTable > thead > tr:nth-child(2) > th {
        top: 35px !important; 
        z-index: 104 !important;
        height: 30px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"],
    #sortirTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        height: 65px !important; 
    }
</style>
    @php
        // Resolve menu ID for permission checks
        $currentMenu = \App\Models\AppMenu::where('route', 'cross_cut.index')->first();
        $menuId = $currentMenu ? $currentMenu->id : null;
        $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
        $canEdit = $menuId ? auth()->user()->hasPermission($menuId, 'edit') : true;
        $canDelete = $menuId ? auth()->user()->hasPermission($menuId, 'delete') : true;
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
                            LAPORAN DATA CHECKSHEET CROSS CUT PLATING
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">QC-KRW-F-0214</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">25/03/2015</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">3 / 22/12/2025</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">1 / 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <!-- Logo Tersembunyi untuk Ekspor PDF -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('cross_cut.index') }}" method="GET" class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm" id="filterFormCrossCut" style="gap: 10px;">
                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Part:</label>
                    <div style="width: 240px;" class="custom-filter-wrapper">
                        <select name="item_id" id="filterItem" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Item / Part No.</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-name="{{ $item->name }}" data-part-number="{{ $item->part_number }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} {{ $item->part_number ? '- '.$item->part_number : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Tanggal:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                            style="width: 130px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                        <span class="px-2 text-gray-500 small">-</span>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                            style="width: 130px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Inisial:</label>
                    <div style="width: 120px;" class="custom-filter-wrapper">
                        <select name="operator_initials" id="filterInisial" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Inisial</option>
                            @foreach($initials as $initial)
                                <option value="{{ $initial }}" {{ request('operator_initials') == $initial ? 'selected' : '' }}>{{ $initial }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Customer:</label>
                    <div style="width: 130px;" class="custom-filter-wrapper">
                        <select name="customer" id="filterCustomer" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer }}" {{ request('customer') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Shift:</label>
                    <div style="width: 100px;" class="custom-filter-wrapper">
                        <select name="shift" id="filterShift" class="form-control form-control-sm border-0 shadow-sm">
                            <option value="">Semua Shift</option>
                            <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>

                <div class="ml-auto d-flex" style="gap: 5px;">
                    <style>
                        .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                        .custom-filter-wrapper .ips-input { padding: 4px 20px 4px 8px; font-size: 0.75rem; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: calc(1.5em + 0.5rem + 2px); }
                        .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
                        .custom-filter-wrapper { position: relative; top: -1px; }
                    </style>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Cari Data">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                    <a href="{{ route('cross_cut.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if($canExport)
                    <a href="{{ route('cross_cut.export_pdf') }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('cross_cut.print') }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
                    </a>
                    @endif
                </div>
            </form>

            <hr>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="checksheetTable">
                    <thead>
                        <tr class="text-center">
                            @if(!in_array(auth()->user()->role, ['inspector']) && auth()->user()->role === 'admin')
                                <th rowspan="2" class="align-middle" style="width: 50px;">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <span style="font-size: 10px; margin-bottom: 5px; white-space: nowrap;">Semua (<span id="checkedCountDisplay">0</span>)</span>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="checkAllRows">
                                            <label class="custom-control-label" for="checkAllRows" style="cursor:pointer;"></label>
                                        </div>
                                    </div>
                                </th>
                            @endif
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal Produksi</th>
                            <th rowspan="2" class="align-middle">Shift Produksi</th>
                            <th rowspan="2" class="align-middle">Tanggal QC</th>
                            <th rowspan="2" class="align-middle">Shift QC</th>
                            <th rowspan="2" class="align-middle">Jam Before</th>
                            <th rowspan="2" class="align-middle">Jam After</th>
                            <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            <th rowspan="2" class="align-middle d-none">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Part No</th>
                            <th rowspan="2" class="align-middle no-export">Hasil Cross Cut</th>
                            <th rowspan="2" class="align-middle">Bak No</th>
                            <th rowspan="2" class="align-middle">Posisi Remark</th>
                            <th rowspan="2" class="align-middle">Visual OK</th>
                            <th rowspan="2" class="align-middle">Result Remark</th>
                            <th rowspan="2" class="align-middle">Inisial</th>
                            <th colspan="5" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            @if(!in_array(auth()->user()->role, ['inspector']))
                                <th rowspan="2" class="align-middle no-export">Actions</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="font-size: 10px;">Kepala Regu QC</th>
                            <th style="font-size: 10px;">Supervisor Quality</th>
                            <th style="font-size: 10px;">Supervisor Plating</th>
                            <th style="font-size: 10px;">Asst Manager QC</th>
                            <th style="font-size: 10px;">Asst Manager Plating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checksheets as $checksheet)
                            <tr class="text-center">
                                @if(!in_array(auth()->user()->role, ['inspector']) && auth()->user()->role === 'admin')
                                    <td class="align-middle text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input row-checkbox" id="checkRow{{ $checksheet->id }}" value="{{ $checksheet->id }}">
                                            <label class="custom-control-label" for="checkRow{{ $checksheet->id }}" style="cursor:pointer;"></label>
                                        </div>
                                    </td>
                                @endif
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->production_shift }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->qc_shift }}</td>
                                <td class="align-middle">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                <td class="align-middle text-nowrap d-none">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->name }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
                                <td class="align-middle no-export">
                                    <button class="btn btn-outline-primary btn-xs view-image-btn" data-id="{{ $checksheet->id }}"
                                        data-image="{{ route('cross_cut.image', $checksheet->id) }}"
                                        data-toggle="modal" data-target="#imageModal"
                                        style="padding: 0.2rem 0.4rem; font-size: 0.75rem; white-space: nowrap;" title="Lihat Foto">
                                        <i class="fas fa-image"></i> Lihat Foto
                                    </button>
                                </td>
                                <td class="align-middle p-0 kimia-col">
                                    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
                                        <tbody>
                                            <tr>
                                                <th class="p-1">Catalyst</th>
                                                <td class="p-1">{{ $checksheet->chemical_catalyst ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="p-1">Abu</th>
                                                <td class="p-1">{{ $checksheet->chemical_abu ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="align-middle">{{ $checksheet->position_remark_judgment }} -
                                    {{ $checksheet->position_remark_no_lot }}
                                </td>
                                <td class="align-middle text-center">
                                    @if($checksheet->visual_ok)
                                        <span class="badge badge-success"><i class="fas fa-check"></i> OK</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle">{{ $checksheet->result_remark }}</td>
                                <td class="align-middle text-uppercase">{{ $checksheet->operator_initials }}</td>

                                {{-- Kolom Status Approval --}}
                                {{-- Level 1: Karu QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->karu_qc)
                                        @if($checksheet->karu_qc === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->karu_qc }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->karu_qc_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->karu_qc_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>



                                {{-- Level 3: SPV Quality --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->supervisor_qc)
                                        @if($checksheet->supervisor_qc === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->supervisor_qc }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->supervisor_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->supervisor_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Level 4: SPV Plating --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->supervisor_plating)
                                        @if($checksheet->supervisor_plating === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->supervisor_plating }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->supervisor_plating_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->supervisor_plating_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Level 5: Asst Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->manager_qc)
                                        @if($checksheet->manager_qc === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->manager_qc }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->manager_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Level 6: Asst Manager Plating --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->manager_plating)
                                        @if($checksheet->manager_plating === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->manager_plating }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->manager_plating_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_plating_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    @if($checksheet->rejection_remarks)
                                        <div class="text-danger font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i> REJECTED
                                        </div>
                                        <small class="text-muted">{{ $checksheet->rejection_remarks }}</small>
                                    @else
                                        @if($checksheet->next_proses)
                                            <div class="mb-1">
                                                <span class="badge badge-danger px-2 py-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    LABEL MERAH: {{ $checksheet->next_proses }}
                                                </span>
                                                <br>
                                                @if(!str_contains($checksheet->keterangan ?? '', '[SORTIR_CLOSED]'))
                                                    <span class="text-danger small font-weight-bold ml-1">
                                                        <i class="fas fa-clock"></i> STATUS: OPEN
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($checksheet->keterangan)) !!}
                                    @endif
                                </td>

                                @if(!in_array(auth()->user()->role, ['inspector']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 350px;">
                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif
                                        @php
                                            // Dimodifikasi: Mengizinkan approval pada level apa pun tanpa menunggu level sebelumnya
                                            $canApproveKaruQc = (auth()->user()->role === 'karu_qc' || auth()->user()->role === 'admin') && (!$checksheet->karu_qc || $checksheet->karu_qc === 'REJECTED');

                                            $canApproveSupervisorPlating = (auth()->user()->role === 'supervisor_plating' || auth()->user()->role === 'admin') && (!$checksheet->supervisor_plating || $checksheet->supervisor_plating === 'REJECTED');
                                            $canApproveSupervisor = (auth()->user()->role === 'supervisor' || auth()->user()->role === 'admin') && (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED');
                                            $canApproveManagerPlating = (auth()->user()->role === 'manager_plating' || auth()->user()->role === 'admin') && (!$checksheet->manager_plating || $checksheet->manager_plating === 'REJECTED');
                                            $canApproveManager = (auth()->user()->role === 'manager' || auth()->user()->role === 'admin') && (!$checksheet->manager_qc || $checksheet->manager_qc === 'REJECTED');
                                        @endphp

                                        {{-- Level 1: Karu QC --}}
                                        @if($canApproveKaruQc)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'karu_qc', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="operator_initials" value="{{ request('operator_initials') }}">
                                                <input type="hidden" name="customer" value="{{ request('customer') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="check_type" value="{{ request('check_type') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kepala Regu)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' KR' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kepala Regu)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}karu_qc"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif



                                        {{-- Level 3: SPV Quality --}}
                                        @if($canApproveSupervisor)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'supervisor', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="operator_initials" value="{{ request('operator_initials') }}">
                                                <input type="hidden" name="customer" value="{{ request('customer') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="check_type" value="{{ request('check_type') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV Quality)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' SPV Q' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV Quality)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        {{-- Level 4: SPV Plating --}}
                                        @if($canApproveSupervisorPlating)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'supervisor_plating', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="operator_initials" value="{{ request('operator_initials') }}">
                                                <input type="hidden" name="customer" value="{{ request('customer') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="check_type" value="{{ request('check_type') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV Plating)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' SPV P' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV Plating)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor_plating"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        {{-- Level 5: Asst Manager QC --}}
                                        @if($canApproveManager)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'manager', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="operator_initials" value="{{ request('operator_initials') }}">
                                                <input type="hidden" name="customer" value="{{ request('customer') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="check_type" value="{{ request('check_type') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Asst Manager QC)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' MGR Q' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Asst Manager QC)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        {{-- Level 6: Asst Manager Plating --}}
                                        @if($canApproveManagerPlating)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'manager_plating', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="operator_initials" value="{{ request('operator_initials') }}">
                                                <input type="hidden" name="customer" value="{{ request('customer') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="check_type" value="{{ request('check_type') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1"
                                                    title="Approve (Asst Manager Plating)" style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' MGR P' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Asst Manager Plating)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager_plating"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('admin.cross_cut.edit_approval', ['id' => $checksheet->id]) }}"
                                                class="btn btn-info btn-sm m-1 btn-status-modal no-loader" title="Edit Approval Status"
                                                style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                            @if($canEdit)
                                                <a href="{{ route('cross_cut.edit', ['id' => $checksheet->id]) }}"
                                                    class="btn btn-warning btn-sm m-1 btn-edit-modal no-loader" title="Edit"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            @endif
                                            @if($canDelete)
                                                <form
                                                    action="{{ route('cross_cut.destroy', ['id' => $checksheet->id, 'plant' => request('plant')]) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm m-1 btn-delete" title="Delete"
                                                        style="min-width: 110px;">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role !== 'inspector' ? 23 : 22 }}" class="text-center">No data
                                    available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $checksheets->withQueryString()->links() }}
            </div>
        </div>
    </div>


    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Data Checksheet
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3" id="editModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Status -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="statusModalLabel">
                        <i class="fas fa-user-check mr-2"></i> Edit Status Approval
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="statusModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Penolakan untuk setiap checksheet dan tipe -->
    @foreach($checksheets as $cs)
        @foreach(['karu_qc', 'supervisor_plating', 'supervisor', 'manager_plating', 'manager'] as $rejectType)
            @php
                $canReject = false;
                // Dimodifikasi: Mengizinkan penolakan pada level apa pun tanpa menunggu level sebelumnya
                if ($rejectType == 'karu_qc' && ((auth()->user()->role === 'karu_qc' || auth()->user()->role === 'admin') && (!$cs->karu_qc || $cs->karu_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor_plating' && ((auth()->user()->role === 'supervisor_plating' || auth()->user()->role === 'admin') && (!$cs->supervisor_plating || $cs->supervisor_plating === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor' && ((auth()->user()->role === 'supervisor' || auth()->user()->role === 'admin') && (!$cs->supervisor_qc || $cs->supervisor_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'manager_plating' && ((auth()->user()->role === 'manager_plating' || auth()->user()->role === 'admin') && (!$cs->manager_plating || $cs->manager_plating === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'manager' && ((auth()->user()->role === 'manager' || auth()->user()->role === 'admin') && (!$cs->manager_qc || $cs->manager_qc === 'REJECTED'))) {
                    $canReject = true;
                }
            @endphp
            @if($canReject)
                <div class="modal fade" id="rejectModal{{ $cs->id }}{{ $rejectType }}" tabindex="-1" role="dialog"
                    aria-labelledby="rejectModalLabel{{ $cs->id }}{{ $rejectType }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="rejectModalLabel{{ $cs->id }}{{ $rejectType }}">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Rejection
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form
                                action="{{ route('cross_cut.reject', ['id' => $cs->id, 'type' => $rejectType, 'plant' => request('plant')]) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="page" value="{{ request('page') }}">
                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                <input type="hidden" name="operator_initials" value="{{ request('operator_initials') }}">
                                <input type="hidden" name="customer" value="{{ request('customer') }}">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="check_type" value="{{ request('check_type') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle"></i> Anda akan menolak checksheet ini sebagai
                                        <strong>{{ ucfirst(str_replace('_', ' ', $rejectType)) }}</strong>
                                    </div>
                                    <div class="form-group">
                                        <label for="rejection_remarks{{ $cs->id }}{{ $rejectType }}" class="font-weight-bold">
                                            Alasan Rejection <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control rejection-textarea @error('rejection_remarks') is-invalid @enderror"
                                            id="rejection_remarks{{ $cs->id }}{{ $rejectType }}" 
                                            name="rejection_remarks" rows="4"
                                            placeholder="Masukkan alasan rejection (minimal 10 karakter)" 
                                            required minlength="10"
                                            maxlength="500"
                                            data-count-id="charCount{{ $cs->id }}{{ $rejectType }}">{{ old('rejection_remarks') }}</textarea>
                                        <small class="form-text text-muted">
                                            <span id="charCount{{ $cs->id }}{{ $rejectType }}">0</span>/500 karakter
                                        </small>
                                        @error('rejection_remarks')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                    <button type="submit" class="btn btn-danger btn-confirm-reject">
                                        <i class="fas fa-ban"></i> Tolak Checksheet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endforeach


    {{-- View Image Modal --}}
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-image mr-2"></i>Bukti Foto Hasil Cross Cut</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body overflow-auto">
                    <img id="modalViewImage" src="" class="img-fluid border shadow-sm" style="cursor: zoom-in; transition: transform 0.25s ease;" onclick="this.style.transform = this.style.transform === 'scale(2)' ? 'scale(1)' : 'scale(2)'; this.style.cursor = this.style.transform === 'scale(2)' ? 'zoom-out' : 'zoom-in';">
                </div>
                <div class="modal-footer justify-content-center">
                    <a href="#" id="downloadImageBtn" class="btn btn-primary" download>
                        <i class="fas fa-download mr-1"></i>Download Gambar
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/jspdf.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/jspdf.plugin.autotable.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Event delegation for rejection remarks character counter
            document.addEventListener('input', function (e) {
                if (e.target.classList.contains('rejection-textarea')) {
                    const countId = e.target.getAttribute('data-count-id');
                    const countSpan = document.getElementById(countId);
                    if (countSpan) {
                        countSpan.textContent = e.target.value.length;
                    }
                }
            });

            // Image Modal Handler
            $('#imageModal').on('show.bs.modal', function (e) {
                var btn = $(e.relatedTarget);
                var imagePath = btn.data('image');
                $('#modalViewImage').attr('src', imagePath);
                $('#downloadImageBtn').attr('href', imagePath);
            });

            // Link Synchronization (Sync Print/Export links with current filter selections)
            var form = document.getElementById('filterFormCrossCut');
            if (form) {
                function syncExportLinks() {
                    var baseUrlPrint = "{{ route('cross_cut.print') }}";
                    var baseUrlPdf = "{{ route('cross_cut.export_pdf') }}";
                    
                    var params = new URLSearchParams();
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        if (pair[1]) params.append(pair[0], pair[1]);
                    }
                    
                    var queryString = params.toString();
                    
                    var printBtn = form.querySelector('a[title="Print"]');
                    var pdfBtn = form.querySelector('a[title="Export to PDF"]');
                    
                    if (printBtn) printBtn.href = baseUrlPrint + '?' + queryString;
                    if (pdfBtn) pdfBtn.href = baseUrlPdf + '?' + queryString;
                }

                $(form).find('input, select').on('change', syncExportLinks);
                syncExportLinks();

                $(form).on('submit', function(e) {
                    var startDate = document.getElementById('start_date').value;
                    var endDate = document.getElementById('end_date').value;

                    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Rentang Tanggal Tidak Valid',
                                text: 'Tanggal Akhir tidak boleh lebih kecil dari Tanggal Mulai!'
                            });
                        } else {
                            alert('Tanggal Akhir tidak boleh lebih kecil dari Tanggal Mulai!');
                        }
                    }
                });
            }
            // Edit Modal Handler
            $('.btn-edit-modal').on('click', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                $('#editModal').modal('show');
                $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

                $.ajax({
                    url: url,
                    success: function (response) {
                        $('#editModalBody').html(response);
                    },
                    error: function (xhr) {
                        var message = 'Gagal memuat data checksheet.';
                        if (xhr.status === 404) {
                            message = 'Data checksheet tidak ditemukan.';
                        } else if (xhr.status === 403) {
                            message = 'Anda tidak memiliki akses untuk mengedit checksheet ini.';
                        } else if (xhr.status === 500) {
                            message = 'Terjadi kesalahan pada server.';
                        }
                        $('#editModalBody').html('<div class="alert alert-danger">' + message + '</div>');
                    }
                });
            });

            // Handle AJAX Submit for Edit Form to catch validation errors
            $(document).on('submit', '#formEditCrossCut', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);
                var btn = form.find('button[type="submit"]');
                var originalHtml = btn.html();

                btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
                form.find('.invalid-feedback').remove();
                form.find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: form.attr('action'),
                    type: "POST", // Method SPOOFING handles PUT
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#global-loader').fadeOut(); // Prevent loader from hiding the alert
                        $('#editModal').modal('hide');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || 'Data berhasil diperbarui.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            alert(response.message || 'Data berhasil diperbarui.');
                            window.location.reload();
                        }
                    },
                    error: function(xhr) {
                        $('#global-loader').fadeOut(); // Hide loader so error is visible
                        btn.html(originalHtml).prop('disabled', false);
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorList = '<ul class="mb-0">';
                            for (var key in errors) {
                                errorList += '<li>' + errors[key][0] + '</li>';
                                var input = form.find('[name="' + key + '"]');
                                if (input.length) {
                                    input.addClass('is-invalid');
                                }
                            }
                            errorList += '</ul>';
                            
                            // Tampilkan alert error di dalam modal
                            if ($('#editModalError').length === 0) {
                                form.prepend('<div id="editModalError" class="alert alert-danger"></div>');
                            }
                            $('#editModalError').html(errorList);
                            
                            // Scroll ke atas
                            $('#editModal').find('.modal-body').scrollTop(0);
                        } else {
                            alert('Terjadi kesalahan sistem: ' + (xhr.responseJSON?.message || xhr.statusText));
                        }
                    }
                });
            });
        });
    </script>
    @php $bulkApproveRoute = route('cross_cut.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')
    
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

    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof initItemSearch === 'function') {
                initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
                initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
                initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
            }
        });

        $(document).ready(function() {
            const checkAllBtn = $('#checkAllRows');
            const rowCheckboxes = $('.row-checkbox');
            const countDisplay = $('#checkedCountDisplay');
            const bulkMenu = $('#bulkActionMenu');
            const bulkSelectedCount = $('#bulkSelectedCount');
            const btnBulkDelete = $('#btnBulkDelete');

            function updateCount() {
                const checkedCount = $('.row-checkbox:checked').length;
                countDisplay.text(checkedCount);
                if (bulkSelectedCount.length > 0) {
                    bulkSelectedCount.text(checkedCount);
                }
                
                if(rowCheckboxes.length > 0) {
                    checkAllBtn.prop('checked', checkedCount === rowCheckboxes.length);
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
                rowCheckboxes.prop('checked', isChecked);
                updateCount();
            });

            rowCheckboxes.on('change', function() {
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
                                url: '{{ route("cross_cut.bulk_destroy") }}',
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
        });
    </script>
@endpush
