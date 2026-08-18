@extends('layouts.admin')

@section('title', 'Cross Cut')

@section('content')
<style>
    .table-responsive {
        max-height: 68vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #checksheetTable, #sortirTable {
        border-collapse: separate !important;
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
        font-size: 0.60rem !important;
        padding: 2px 4px !important;
        line-height: 1.1 !important;
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th,
    #sortirTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.58rem !important;
        letter-spacing: 0.1px;
        padding: 3px 5px !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.1 !important;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #cbd5e1;
    }

    #checksheetTable tbody tr:hover,
    #sortirTable tbody tr:hover {
        background-color: #f1f5f9 !important;
        transition: background-color 0.2s ease;
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export,
    #sortirTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    #checksheetTable .btn,
    #sortirTable .btn {
        min-width: 0 !important;
        padding: 0.1rem 0.3rem !important;
        font-size: 0.58rem !important;
        margin: 0px !important;
    }
    #checksheetTable .badge,
    #sortirTable .badge {
        font-size: 0.58rem !important;
        padding: 0.1rem 0.3rem !important;
    }

    /* Exact sticky heights */
    #checksheetTable > thead > tr:nth-child(1) > th,
    #sortirTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 24px !important; 
    }
    #checksheetTable > thead > tr:nth-child(2) > th,
    #sortirTable > thead > tr:nth-child(2) > th {
        top: 24px !important; 
        z-index: 104 !important;
        height: 20px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"],
    #sortirTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        top: 0 !important;
        height: 44px !important; /* 24 + 20 */
        z-index: 106 !important;
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
    <!-- Logo Tersembunyi untuk Ekspor PDF -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-2">
        <div class="card-header py-2 px-3">
            <h6 class="m-0 font-weight-bold text-dark text-uppercase" style="font-size: 0.80rem;">DATA MASUK CROSS CUT PLATING</h6>
        </div>
        <div class="card-body p-2">
            <form action="{{ route('cross_cut.index') }}" method="GET"
                class="d-flex flex-wrap align-items-end bg-light p-2 rounded mb-2 shadow-sm"
                style="gap: 8px; overflow-x: auto;" id="filterFormCrossCut">

                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <!-- 1. Field: Part Name -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Part Name</label>
                    <div style="width: 180px;" class="custom-filter-wrapper">
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

                <!-- 2. Field: Customer -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Customer</label>
                    <div style="width: 120px;" class="custom-filter-wrapper">
                        <select name="customer" id="filterCustomer" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer }}" {{ request('customer') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 3. Field: Tanggal -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Tanggal</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden" style="border: 1px solid #e2e8f0;">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                            style="width: 125px; font-size: 0.70rem; height: 26px;" value="{{ request('start_date') }}" title="Dari Tanggal">
                        <span class="px-2 text-gray-500 font-weight-bold small">s/d</span>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                            style="width: 125px; font-size: 0.70rem; height: 26px;" value="{{ request('end_date') }}" title="Sampai Tanggal">
                    </div>
                </div>

                <!-- 4. Field: Shift -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Shift</label>
                    <div style="width: 95px;" class="custom-filter-wrapper">
                        <select name="shift" id="filterShift" class="form-control form-control-sm border-0 shadow-sm" style="font-size: 0.70rem; height: 26px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>

                <!-- Tombol Filter & Reset -->
                <div class="d-flex align-items-center" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important; margin-left: 20px;">
                    <style>
                        .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                        .custom-filter-wrapper .ips-input { padding: 2px 18px 2px 6px !important; font-size: 0.68rem !important; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: 26px !important; }
                        .custom-filter-wrapper .ips-clear { right: 5px; font-size: 10px; }
                        .custom-filter-wrapper { position: relative; top: 0px; }
                    </style>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Cari Data">
                        <i class="fas fa-search fa-sm mr-1"></i> Filter
                    </button>
                    <a href="{{ route('cross_cut.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Reset Filter">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </a>
                </div>

                <!-- Tombol Ekspor (Paling Kanan) -->
                <div class="d-flex align-items-center ml-auto" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important;">
                    @if($canExport)
                    <a href="{{ route('cross_cut.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-download d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm mr-1"></i> PDF
                    </a>
                    <a href="{{ route('cross_cut.print', request()->query()) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-print-direct d-flex align-items-center" style="background-color: #17a589; color: white; font-size: 0.68rem; height: 26px;" title="Cetak Direct">
                        <i class="fas fa-print fa-sm mr-1"></i> Cetak
                    </a>
                    @endif
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="checksheetTable">
                    <thead>
                        @php
                            $requestPlant = request('plant');
                            $userPlantCode = optional(auth()->user()->plant)->code;
                            if (!empty($requestPlant)) {
                                $plant = \App\Models\Plant::where('code', $requestPlant)->orWhere('id', $requestPlant)->first();
                                $plantContext = strtolower($plant?->code ?? $requestPlant);
                            } else {
                                $plantContext = strtolower(!empty($userPlantCode) ? $userPlantCode : 'karawang');
                            }
                        @endphp
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
                            <th rowspan="2" class="align-middle">Item Part / Part No</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle no-export">Hasil Cross Cut</th>
                            <th rowspan="2" class="align-middle">Bak No</th>
                            <th rowspan="2" class="align-middle">Judgment & Posisi Remark</th>
                            <th rowspan="2" class="align-middle">Result Remark</th>
                            <th rowspan="2" class="align-middle">Inisial</th>
                            <th colspan="4" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle" style="min-width: 400px;">DESCRIPTION</th>
                            @if(!in_array(auth()->user()->role, ['inspector']))
                                <th rowspan="2" class="align-middle no-export">Actions</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="font-size: 10px; min-width: 120px;">{{ $plantContext === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                            <th style="font-size: 10px; min-width: 120px;">Kashift Plating</th>
                            <th style="font-size: 10px; min-width: 120px;">Supervisor Quality</th>
                            <th style="font-size: 10px; min-width: 120px;">Supervisor Plating</th>
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
                                <td class="align-middle text-left text-nowrap">
                                    <span class="font-weight-bold text-gray-800">{{ $checksheet->item->name }}</span><br>
                                    <small class="text-muted"><i class="fas fa-tag mr-1"></i>{{ $checksheet->item->part_number ?? '-' }}</small>
                                </td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
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
                                                <th class="p-1">Copper</th>
                                                <td class="p-1">{{ $checksheet->chemical_abu ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="align-middle">{{ $checksheet->position_remark_judgment }} -
                                    {{ $checksheet->position_remark_no_lot }}
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

                                {{-- Level 2: Kashift Plating --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->kashift_plating)
                                        @if($checksheet->kashift_plating === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->kashift_plating }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->kashift_plating_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->kashift_plating_approved_at)->format('d/m/Y H:i') }}</small>
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
                                {{-- DESCRIPTION Column --}}
                                <td class="align-middle text-left" style="min-width: 400px; word-wrap: break-word;">
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
                                        {!! nl2br(str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($checksheet->keterangan ?? ''))) !!}
                                    @endif
                                </td>

                                 @if(!in_array(auth()->user()->role, ['inspector']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 50px;">
                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif
                                        @php
                                            $isAdmin = auth()->user()->role === 'admin';
                                            $currentRole = auth()->user()->role;

                                            // Map role → [field_to_check, admin_label]
                                            $approvalLevels = [
                                                'karu_qc'              => ['field' => 'karu_qc',              'label' => 'KR'],
                                                'kashift_plating'      => ['field' => 'kashift_plating',      'label' => 'Kashift P'],
                                                'supervisor'           => ['field' => 'supervisor_qc',         'label' => 'SPV Q'],
                                                'supervisor_plating'   => ['field' => 'supervisor_plating',    'label' => 'SPV P'],
                                            ];
                                            $approvalKeys = array_keys($approvalLevels);
                                        @endphp

                                        @foreach($approvalLevels as $approvalRole => $config)
                                            @php
                                                $f = $config['field'];
                                                $lbl = $config['label'];

                                                $idx = array_search($approvalRole, $approvalKeys);
                                                $prevApproved = true;
                                                if ($idx > 0) {
                                                    for ($i = $idx - 1; $i >= 0; $i--) {
                                                        $prevF = $approvalLevels[$approvalKeys[$i]]['field'];
                                                        if (empty($checksheet->$prevF) || $checksheet->$prevF === 'REJECTED') {
                                                            $prevApproved = false;
                                                            break;
                                                        }
                                                    }
                                                }

                                                $canApprove = ($isAdmin || $currentRole === $approvalRole)
                                                              && (empty($checksheet->$f) || $checksheet->$f === 'REJECTED')
                                                              && $prevApproved;
                                            @endphp
                                            @if($canApprove)
                                                <form
                                                    action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => $approvalRole, 'plant' => request('plant')]) }}"
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
                                                        title="Approve ({{ $lbl }})" style="min-width: 110px;">
                                                        <i class="fas fa-check"></i>
                                                        Approve{{ $isAdmin ? ' ' . $lbl : '' }}
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1"
                                                    data-toggle="modal"
                                                    data-target="#rejectModal{{ $checksheet->id }}{{ $approvalRole }}"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                        @endforeach

                                        @include('partials.action_dropdown', [
                                            'canEdit'      => $canEdit,
                                            'canDelete'    => $canDelete,
                                            'editUrl'      => route('cross_cut.edit', ['id' => $checksheet->id]),
                                            'deleteRoute'  => route('cross_cut.destroy', ['id' => $checksheet->id, 'plant' => request('plant')]),
                                            'deleteParams' => [],
                                            'statusUrl'    => $isAdmin ? route('cross_cut.edit_approval', array_merge(['id' => $checksheet->id], request()->all())) : null,
                                        ])
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role !== 'inspector' ? 24 : 23 }}" class="text-center">No data
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
        @foreach(['karu_qc', 'kashift_plating', 'supervisor_plating', 'supervisor', 'asst_manager_plating', 'asst_manager'] as $rejectType)
            @php
                $canReject = false;
                // Dimodifikasi: Mengizinkan penolakan pada level apa pun tanpa menunggu level sebelumnya
                if ($rejectType == 'karu_qc' && ((auth()->user()->role === 'karu_qc' || auth()->user()->role === 'admin') && (!$cs->karu_qc || $cs->karu_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'kashift_plating' && ((auth()->user()->role === 'kashift_plating' || auth()->user()->role === 'admin') && (!$cs->kashift_plating || $cs->kashift_plating === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor_plating' && ((auth()->user()->role === 'supervisor_plating' || auth()->user()->role === 'admin') && (!$cs->supervisor_plating || $cs->supervisor_plating === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor' && ((auth()->user()->role === 'supervisor' || auth()->user()->role === 'admin') && (!$cs->supervisor_qc || $cs->supervisor_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'asst_manager_plating' && ((auth()->user()->role === 'asst_manager_plating' || auth()->user()->role === 'admin') && (!$cs->asst_manager_plating || $cs->asst_manager_plating === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'asst_manager' && ((auth()->user()->role === 'asst_manager' || auth()->user()->role === 'admin') && (!$cs->asst_manager_qc || $cs->asst_manager_qc === 'REJECTED'))) {
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
    <!-- Script Cetak Langsung (Direct Silent Print) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-print-direct').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var printUrl = this.getAttribute('href');
                    var iframe = document.createElement('iframe');
                    iframe.style.position = 'fixed';
                    iframe.style.right = '0';
                    iframe.style.bottom = '0';
                    iframe.style.width = '0';
                    iframe.style.height = '0';
                    iframe.style.border = '0';
                    iframe.src = printUrl;
                    document.body.appendChild(iframe);
                    iframe.onload = function() {
                        try {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                        } catch (err) {
                            console.error('Print iframe error:', err);
                            window.open(printUrl, '_blank');
                        }
                        setTimeout(function() {
                            document.body.removeChild(iframe);
                        }, 60000);
                    };
                });
            });
        });
    </script>
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
