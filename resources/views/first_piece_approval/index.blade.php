@extends('layouts.admin')

@section('title', 'First Piece Approval')

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
    #checksheetTable, #sortirTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    }
    #checksheetTable td, #checksheetTable th,
    #sortirTable td, #sortirTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }
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
    }
    #checksheetTable .btn,
    #sortirTable .btn {
        min-width: 0 !important; /* Overrides 110px inline style */
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
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
    }
    #checksheetTable > thead > tr:nth-child(2) > th,
    #sortirTable > thead > tr:nth-child(2) > th {
        top: 35px !important; 
        z-index: 104 !important;
        height: 30px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    }
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"],
    #sortirTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        height: 65px !important; 
    }

    /* Minimalist Dimension Table Styles - Aggressive override for global !important */
    #checksheetTable .table-dimension-minimalist,
    #checksheetTable td .table-dimension-minimalist,
    #checksheetTable table.table-dimension-minimalist {
        border-collapse: collapse !important;
        width: 100% !important;
        margin: 0 !important;
        background: #ffffff !important;
        border: none !important;
    }
    }
    #checksheetTable .table-dimension-minimalist td,
    #checksheetTable .table-dimension-minimalist th {
        background-color: transparent !important;
        border: none !important;
        padding: 4px !important;
        text-align: center !important;
    }

    /* Target headers switched to td for avoiding global thead th blue style */
    #checksheetTable .table-dimension-minimalist .dim-header {
        background-color: #f8fafc !important; /* Industrial Slate */
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.55rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
        line-height: 1 !important;
    }
    }
    #checksheetTable .table-dimension-minimalist .dim-data {
        font-size: 0.65rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #1e293b !important;
        line-height: 1.2 !important;
    }
    }
    #checksheetTable .table-dimension-minimalist tr:last-child .dim-data {
        border-bottom: none !important;
    }
    }
    #checksheetTable .table-dimension-minimalist .text-std-header { 
        color: #64748b !important; 
        font-weight: 600 !important; 
        background-color: #f1f5f9 !important; 
    }
</style>
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        // Resolve menu IDs for permission checks (support for duplicate plant routes)
        $menuIds = \App\Models\AppMenu::where('route', 'first_piece_approval.index')->pluck('id');
        $canExport = true; $canEdit = true; $canDelete = true;
        if ($menuIds->isNotEmpty()) {
            $canExport = false; $canEdit = false; $canDelete = false;
            foreach ($menuIds as $mId) {
                if (auth()->user()->hasPermission($mId, 'export')) $canExport = true;
                if (auth()->user()->hasPermission($mId, 'edit')) $canEdit = true;
                if (auth()->user()->hasPermission($mId, 'delete')) $canDelete = true;
            }
        }
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
                            LAPORAN DATA CHECKSHEET FIRST PIECE APPROVAL
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? 'QC-JKT-F-032/0' : 'QC-KRW-F-0212' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? '21.02.2023' : '25/03/2015' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? '1 / 14.06.2023' : '3 / 22/12/2025' }}
                                </td>
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
    <!-- Hidden Logo for PDF Export -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('first_piece_approval.index') }}" method="GET"
                class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 10px;" id="filterFormFpa">

                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <!-- Field: Part -->
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

                <!-- Field: Tanggal -->
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

                <!-- Field: Inisial -->
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

                <!-- Field: Customer -->
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

                <!-- Field: Shift -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Shift:</label>
                    <div style="width: 90px;" class="custom-filter-wrapper">
                        <select name="shift" id="filterShift" class="form-control form-control-sm border-0 shadow-sm">
                            <option value="">Semua</option>
                            <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>

                <!-- Tombol Aksi -->
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
                    <a href="{{ route('first_piece_approval.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if($canExport)
                    <a href="{{ route('first_piece_approval.export_measurements', request()->query()) }}"
                        class="btn btn-info btn-sm shadow-sm rounded-pill px-3 no-loader" title="Export Metadata Pengukuran (Excel)">
                        <i class="fas fa-file-excel fa-sm"></i>
                    </a>
                    <button type="button" 
                        class="btn btn-warning btn-sm shadow-sm rounded-pill px-3 no-loader" 
                        title="Import Metadata Pengukuran"
                        data-toggle="modal" 
                        data-target="#importMetadataModal">
                        <i class="fas fa-file-import fa-sm"></i>
                    </button>
                    <a href="{{ route('first_piece_approval.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('first_piece_approval.print', request()->query()) }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
                    </a>
                    <a href="{{ route('first_piece_approval.daily_recap', ['plant' => request('plant'), 'date' => request('start_date') ?: now()->toDateString()]) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold"
                        title="Rekap Harian FPA — Distribusi Jam"
                        style="background-color: #7c3aed; color: white;">
                        <i class="fas fa-chart-bar fa-sm mr-1"></i> Rekap
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
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal</th>
                            <th rowspan="2" class="align-middle">Jam (Before)</th>
                            <th rowspan="2" class="align-middle">Jam (After)</th>
                            <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            <th rowspan="2" class="align-middle">Shift</th>
                            <th rowspan="2" class="align-middle d-none">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Part No</th>
                            <th rowspan="2" class="align-middle">Total Qty</th>
                            <th rowspan="2" class="align-middle">Sampling Qty</th>
                            <th rowspan="2" class="align-middle">Check Dimensi</th>
                            <th rowspan="2" class="align-middle">Berat Part</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial</th>

                            <th colspan="4" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            @if(!in_array(auth()->user()->role, ['inspector']))
                                <th rowspan="2" class="no-export align-middle">Action</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="width: 5%">Pcs</th>
                            <th>Jenis NG</th>
                            <th style="font-size: 10px;">{{ $plantContext === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}
                            </th>
                            <th style="font-size: 10px;">Supervisor QC</th>
                            <th style="font-size: 10px;">Asst. Manager QC</th>
                            <th style="font-size: 10px;">Manager QC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checksheets as $checksheet)
                            <tr class="text-center">
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">
                                    {{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->created_at->format('H:i') }}</td>
                                <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->shift }}</td>
                                <td class="align-middle text-nowrap d-none">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->name ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->total_qty }}</td>
                                <td class="align-middle">{{ $checksheet->sampling_qty }}</td>

                                {{-- Dimension Check Detail --}}
                                <td class="align-middle p-0">
                                    @php
                                        $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true);
                                        $dimensions = $dimensions ?: [];

                                        // Check if there is any actual user input
                                        $hasUserInputs = false;
                                        foreach ($dimensions as $cavPoints) {
                                            if (is_array($cavPoints)) {
                                                foreach ($cavPoints as $val) {
                                                    if ($val !== null && $val !== '' && $val !== '-') {
                                                        $hasUserInputs = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                        $itemPartNumber = str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', str_replace(["\xe2\x80\x92", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"], '-', $checksheet->item->part_number ?? ''));
                                        $itemPartNumber = strtoupper($itemPartNumber);
                                        $standards = $partDimensionStandards[$itemPartNumber] ?? [];

                                        // Find active points (columns that have data or are defined in standards)
                                        $activePoints = [];
                                        foreach ($dimensions as $cavKey => $points) {
                                            if (is_array($points)) {
                                                foreach ($points as $pKey => $pVal) {
                                                    if ($pVal !== null && $pVal !== '' && $pVal !== '-' && $pVal !== 0 && $pVal !== '0') {
                                                        $activePoints[$pKey] = true;
                                                    }
                                                }
                                            }
                                        }
                                        foreach ($standards as $pKey => $std) {
                                            $activePoints[$pKey] = true;
                                        }
                                        $activePoints = array_keys($activePoints);
                                        sort($activePoints);

                                        // Default points if none found
                                        if (empty($activePoints)) {
                                            $activePoints = range(1, 5);
                                        }

                                        // Find max cavity for rendering rows
                                        $actualMaxCavity = 0;
                                        foreach ($dimensions as $cavKey => $pointsData) {
                                            $cavNum = (int) filter_var($cavKey, FILTER_SANITIZE_NUMBER_INT);
                                            $actualMaxCavity = max($actualMaxCavity, $cavNum);
                                        }
                                        $displayMaxCavity = max(5, $actualMaxCavity);
                                        $anyNGInRow = false;
                                    @endphp
                                    @if($hasUserInputs)
                                        <div style="max-height: 200px; overflow-y: auto;">
                                            <table class="table-dimension-minimalist">
                                                <thead class="text-center" style="font-size: 0.6rem;">
                                                    {{-- Standard Row --}}
                                                    @php
                                                        $hasStdData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && ($standards[$j]['size'] !== null && $standards[$j]['size'] !== '' && $standards[$j]['size'] !== '-')) {
                                                                $hasStdData = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasStdData)
                                                        <tr>
                                                            <td class="dim-header text-std-header">Std</td>
                                                            @foreach ($activePoints as $j)
                                                                <td class="dim-header text-std-header">
                                                                    {{ isset($standards[$j]) ? $standards[$j]['size'] : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Min Row --}}
                                                    @php
                                                        $hasMinData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && $standards[$j]['min'] !== null && $standards[$j]['min'] !== '' && $standards[$j]['min'] !== '-') {
                                                                $hasMinData = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasMinData)
                                                        <tr>
                                                            <td class="dim-header text-std-header">Min</td>
                                                            @foreach ($activePoints as $j)
                                                                <td class="dim-header text-std-header">
                                                                    {{ (isset($standards[$j]) && $standards[$j]['min'] !== null) ? $standards[$j]['min'] : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Max Row --}}
                                                    @php
                                                        $hasMaxData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && $standards[$j]['max'] !== null && $standards[$j]['max'] !== '' && $standards[$j]['max'] !== '-') {
                                                                $hasMaxData = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasMaxData)
                                                        <tr>
                                                            <td class="dim-header text-std-header">Max</td>
                                                            @foreach ($activePoints as $j)
                                                                <td class="dim-header text-std-header">
                                                                    {{ (isset($standards[$j]) && $standards[$j]['max'] !== null) ? $standards[$j]['max'] : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Tolerance Row --}}
                                                    @php
                                                        $hasTolData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && $standards[$j]['tolerance'] !== null && $standards[$j]['tolerance'] !== '' && $standards[$j]['tolerance'] !== '-') {
                                                                $hasTolData = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasTolData)
                                                        <tr>
                                                            <td class="dim-header text-std-header">Tol</td>
                                                            @foreach ($activePoints as $j)
                                                                <td class="dim-header text-std-header">
                                                                    {{ isset($standards[$j]) ? '±' . $standards[$j]['tolerance'] : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Main Header Row --}}
                                                    <tr>
                                                        <td class="dim-header">Cav</td>
                                                        @foreach ($activePoints as $j)
                                                            <td class="dim-header">Ø{{ $j }}</td>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- Actual Measurements --}}
                                                    @for ($i = 1; $i <= $displayMaxCavity; $i++)
                                                        @php
                                                            $rowHasData = false;
                                                            foreach ($activePoints as $j) {
                                                                $val = $dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null);
                                                                if ($val !== null && $val !== '' && $val !== '-' && $val !== 0 && $val !== '0') {
                                                                    $rowHasData = true;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        @if($rowHasData)
                                                            <tr>
                                                                <td class="dim-data font-weight-bold">{{ $i }}</td>
                                                                @foreach ($activePoints as $j)
                                                                    @php
                                                                        $val = $dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? '-');
                                                                        $isNG = false;
                                                                        
                                                                        // Robust lookup for standard in PHP
                                                                        $std = null;
                                                                        if (!empty($standards)) {
                                                                            if (isset($standards[$j])) {
                                                                                $std = $standards[$j];
                                                                            } else {
                                                                                // Fallback for array structure if needed
                                                                                foreach ($standards as $itemStd) {
                                                                                    if (isset($itemStd['point']) && (string)$itemStd['point'] === (string)$j) {
                                                                                        $std = $itemStd;
                                                                                        break;
                                                                                    }
                                                                                }
                                                                            }
                                                                        }

                                                                        if ($std && is_numeric($val)) {
                                                                            $fVal = (float)$val;
                                                                            $epsilon = 0.00001;

                                                                            // 1. Check Absolute Min/Max
                                                                            if (($std['min'] ?? null) !== null && $std['min'] !== '') {
                                                                                $minBound = (float)$std['min'];
                                                                                if ($fVal < ($minBound - $epsilon)) $isNG = true;
                                                                            }
                                                                            if (!$isNG && ($std['max'] ?? null) !== null && $std['max'] !== '') {
                                                                                $maxBound = (float)$std['max'];
                                                                                if ($fVal > ($maxBound + $epsilon)) $isNG = true;
                                                                            }

                                                                            // 2. Check Size +/- Tolerance
                                                                            if (!$isNG && ($std['size'] ?? null) !== null && ($std['tolerance'] ?? null) !== null && $std['size'] !== '' && $std['tolerance'] !== '') {
                                                                                $szStr = (string)$std['size'];
                                                                                if (!str_starts_with($szStr, '+') && !str_starts_with($szStr, '-')) {
                                                                                    $base = (float)$szStr;
                                                                                    $tol = (string)$std['tolerance'];
                                                                                    $lb = $base; $ub = $base;
                                                                                    
                                                                                    if (str_contains($tol, '/')) {
                                                                                        $parts = explode('/', $tol);
                                                                                        foreach ($parts as $p) {
                                                                                            $p = trim(str_replace(',', '.', $p));
                                                                                            $fv = (float)$p;
                                                                                            if (str_starts_with($p, '+') || $fv > 0) $ub = $base + abs($fv);
                                                                                            elseif (str_starts_with($p, '-') || $fv < 0) $lb = $base - abs($fv);
                                                                                        }
                                                                                    } elseif (str_starts_with($tol, '+')) {
                                                                                        $ub = $base + (float)substr($tol, 1);
                                                                                    } elseif (str_starts_with($tol, '-')) {
                                                                                        $lb = $base + (float)$tol;
                                                                                    } else {
                                                                                        $tv = (float)$tol;
                                                                                        $lb = $base - $tv; $ub = $base + $tv;
                                                                                    }
                                                                                    
                                                                                    if ($fVal < ($lb - $epsilon) || $fVal > ($ub + $epsilon)) $isNG = true;
                                                                                }
                                                                            }

                                                                            // 3. Check Special Size (prefix)
                                                                            if (!$isNG && ($std['size'] ?? null) !== null && $std['size'] !== '') {
                                                                                $szStr = (string)$std['size'];
                                                                                if (str_starts_with($szStr, '+') || str_starts_with($szStr, '-')) {
                                                                                    $op = $szStr[0];
                                                                                    $bound = (float)substr($szStr, 1);
                                                                                    if ($op === '+' && $fVal < ($bound - $epsilon)) $isNG = true;
                                                                                    elseif ($op === '-' && $fVal > ($bound + $epsilon)) $isNG = true;
                                                                                }
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <td class="dim-data {{ $isNG ? 'text-danger font-weight-bold' : '' }}" @if($isNG)
                                                                    style="color: #dc3545 !important; font-weight: bold !important; background-color: #fef2f2 !important;" @endif>
                                                                        {{ $val }}
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endif
                                                    @endfor
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <span class="text-dark font-weight-bold" style="font-size: 0.8rem;">TIDAK ADA PENGUKURAN
                                            DIMENSI</span>
                                    @endif
                                </td>

                                <td class="align-middle" style="min-width:90px;">
                                    @php
                                        $weights = is_array($checksheet->part_weight)
                                            ? $checksheet->part_weight
                                            : (is_string($checksheet->part_weight) && str_starts_with($checksheet->part_weight, '[')
                                                ? json_decode($checksheet->part_weight, true)
                                                : ($checksheet->part_weight ? [$checksheet->part_weight] : []));
                                    @endphp
                                    @if(!empty(array_filter($weights, fn($w) => $w !== null && $w !== '')))
                                        @foreach($weights as $ci => $wv)
                                            @if($wv !== null && $wv !== '')
                                                <div class="text-nowrap" style="font-size:0.75rem;">
                                                    <span class="text-muted">CAV{{ $ci + 1 }}:</span>
                                                    <strong>{{ $wv }}</strong><small class="text-muted"> gr</small>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="align-middle text-success font-weight-bold">{{ $checksheet->total_ok }}</td>
                                <td class="align-middle text-danger font-weight-bold">{{ $checksheet->total_ng }}</td>

                                @php
                                    $defectsData = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
                                    $pcsLines = [];
                                    $nameLines = [];

                                    if (is_array($defectsData)) {
                                        foreach ($defectsData as $d) {
                                            if (is_array($d) && isset($d['type'])) {
                                                $qty = $d['qty'] ?? 1;
                                                $pcsLines[] = $qty;
                                                $typeStr = strtolower($d['type']) === 'dimension' ? 'Dimensi' : $d['type'];
                                                $nameLines[] = $typeStr;
                                            } elseif (is_string($d)) {
                                                $pcsLines[] = 1;
                                                $typeStr = strtolower($d) === 'dimension' ? 'Dimensi' : $d;
                                                $nameLines[] = $typeStr;
                                            }
                                        }
                                    }
                                    
                                    if ($anyNGInRow ?? false) {
                                        $hasDimensi = false;
                                        foreach ($nameLines as $name) {
                                            if (stripos($name, 'dimensi') !== false || stripos($name, 'dimension') !== false) {
                                                $hasDimensi = true;
                                                break;
                                            }
                                        }
                                        if (!$hasDimensi) {
                                            $pcsLines[] = '-';
                                            $nameLines[] = 'NG Dimensi';
                                        }
                                    }
                                @endphp

                                <td class="text-center align-middle p-0">
                                    @if(count($pcsLines) > 0)
                                        @foreach($pcsLines as $index => $qty)
                                            <div class="{{ $index < count($pcsLines) - 1 ? 'border-bottom' : '' }} py-1">
                                                <small class="text-danger font-weight-bold">{{ $qty }}</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="py-1">-</div>
                                    @endif
                                </td>
                                <td class="text-center align-middle p-0">
                                    @if(count($nameLines) > 0)
                                        @foreach($nameLines as $index => $name)
                                            <div class="{{ $index < count($nameLines) - 1 ? 'border-bottom' : '' }} py-1 px-2">
                                                <small class="text-danger font-weight-bold">{{ $name }}</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="py-1 px-2">-</div>
                                    @endif
                                </td>

                                <td class="align-middle">
                                     @php
                                         $effectiveJudgment = ($checksheet->judgment == 'NG' || ($anyNGInRow ?? false)) ? 'NG' : 'OK';
                                     @endphp
                                     <span class="badge badge-{{ $effectiveJudgment == 'OK' ? 'success' : 'danger' }}" 
                                           title="{{ $checksheet->judgment != $effectiveJudgment ? 'Warning: Database judgment differs from dimension check' : '' }}">
                                         {{ $effectiveJudgment }}
                                     </span>
                                </td>
                                <td class="align-middle text-uppercase">{{ $checksheet->operator_initials }}</td>

                                {{-- Kashift QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->kashift_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                    @elseif($checksheet->kashift_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->kashift_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->kashift_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->kashift_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Supervisor QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->supervisor_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                    @elseif($checksheet->supervisor_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->supervisor_qc }}</small>
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

                                {{-- Asst Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->asst_manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                    @elseif($checksheet->asst_manager_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->asst_manager_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->asst_manager_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->asst_manager_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                    @elseif($checksheet->manager_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->manager_qc }}</small>
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
                                                @if(!str_contains($checksheet->remarks ?? '', '[SORTIR_CLOSED]'))
                                                    <span class="text-danger small font-weight-bold ml-1">
                                                        <i class="fas fa-clock"></i> STATUS: OPEN
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($checksheet->remarks)) !!}
                                    @endif
                                </td>


                                @if(!in_array(auth()->user()->role, ['inspector']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 350px;">
                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif
                                        {{-- Action Buttons for Approvals --}}
                                        @php
                                            $user = auth()->user();
                                            $isAdmin = $user->role === 'admin';
                                            $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                                            $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                            $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                            $canApproveKashift = ($user->role === 'kashift' || $isAdmin || $isSpvJakarta ||
                                                $isKaruJakarta) && (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED');
                                            $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) &&
                                                (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED');
                                            $canApproveAsst = ($user->role === 'asst_manager' || $isAdmin) &&
                                                (!$checksheet->asst_manager_qc || $checksheet->asst_manager_qc === 'REJECTED');
                                            $canApproveManager = ($user->role === 'manager' || $isAdmin) && (!$checksheet->manager_qc ||
                                                $checksheet->manager_qc === 'REJECTED');
                                        @endphp

                                        @if($canApproveKashift)
                                            <form
                                                action="{{ route('first_piece_approval.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kashift)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ ($user->role === 'admin') ? ' KS' : (($isSpvJakarta || $isKaruJakarta) ? '' : '') }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kashift)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveSupervisor)
                                            <form
                                                action="{{ route('first_piece_approval.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' SPV' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveAsst)
                                            <form
                                                action="{{ route('first_piece_approval.approve', array_merge(['id' => $checksheet->id, 'type' => 'asst_manager'], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (AM)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' AM' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (AM)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}asst_manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveManager)
                                            <form
                                                action="{{ route('first_piece_approval.approve', array_merge(['id' => $checksheet->id, 'type' => 'manager'], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (MGR)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' MGR' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (MGR)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('admin.first_piece_approval.edit_approval', array_merge(['id' => $checksheet->id], request()->all())) }}"
                                                class="btn btn-info btn-sm m-1 btn-status-modal no-loader" title="Edit Approval Status"
                                                style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                            @if($canEdit)
                                                <a href="{{ route('first_piece_approval.edit', array_merge(['id' => $checksheet->id], request()->all())) }}"
                                                    class="btn btn-warning btn-sm m-1 btn-edit-modal no-loader" title="Edit"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            @endif
                                            @if($canDelete)
                                                <form
                                                    action="{{ route('first_piece_approval.destroy', array_merge(['id' => $checksheet->id], request()->all())) }}"
                                                    method="POST" class="d-inline ajax-form">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $checksheets->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Checksheet First Piece Approval
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Modal (Admin) -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="statusModalLabel">
                        <i class="fas fa-tasks mr-2"></i> Update Status Approval Admin
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="statusModalBody">
                    <!-- Loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal for each checksheet and type -->
    @foreach($checksheets as $cs)
        @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
            @php
                $user = auth()->user();
                $isAdmin = $user->role === 'admin';
                $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;
                $canReject = false;
                if (
                    $rejectType == 'kashift' && (($user->role === 'kashift' || $isAdmin || $isSpvJakarta || $isKaruJakarta) &&
                        (!$cs->kashift_qc || $cs->kashift_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'supervisor' && (($user->role === 'supervisor' || $isAdmin) && (!$cs->supervisor_qc ||
                        $cs->supervisor_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'asst_manager' && (($user->role === 'asst_manager' || $isAdmin) && (!$cs->asst_manager_qc ||
                        $cs->asst_manager_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'manager' && (($user->role === 'manager' || $isAdmin) && (!$cs->manager_qc || $cs->manager_qc
                        === 'REJECTED'))
                ) {
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
                                action="{{ route('first_piece_approval.reject', array_merge(['id' => $cs->id, 'type' => $rejectType], request()->all())) }}"
                                method="POST" class="ajax-form">
                                @csrf
                                <div id="modal-errors" class="mx-3 mt-3" style="display: none;"></div>
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle"></i> Anda akan menolak checksheet ini sebagai
                                        <strong>{{ ($isJakarta && $rejectType === 'kashift') ? 'Kepala Regu (KR)' : ucfirst(str_replace('_', ' ', $rejectType)) }}</strong>
                                    </div>
                                    <div class="form-group">
                                        <label for="rejection_remarks{{ $cs->id }}{{ $rejectType }}" class="font-weight-bold">
                                            Alasan Rejection <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('rejection_remarks') is-invalid @enderror"
                                            id="rejection_remarks{{ $cs->id }}{{ $rejectType }}" name="rejection_remarks" rows="4"
                                            placeholder="Masukkan alasan rejection (minimal 10 karakter)" required minlength="10"
                                            maxlength="500">{{ old('rejection_remarks') }}</textarea>
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

<!-- Modal Import Metadata -->
<div class="modal fade" id="importMetadataModal" tabindex="-1" role="dialog" aria-labelledby="importMetadataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title font-weight-bold" id="importMetadataModalLabel">
                    <i class="fas fa-file-import mr-2"></i> Import Data
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('first_piece_approval.import_measurements') }}" method="POST" enctype="multipart/form-data" class="no-loader" id="importMeasureForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 mb-4 rounded-lg" style="font-size: 0.85rem; border-left: 4px solid #3abaf4;">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Petunjuk:</strong><br>
                        1. Gunakan fitur <strong>Export Excel</strong> untuk mendapatkan file template <strong>.xlsx</strong>.<br>
                        2. Masukkan nilai hasil ukur pada kolom yang sesuai di Excel.<br>
                        3. Simpan file tetap dalam format <strong>.xlsx</strong> (tidak perlu ubah ke CSV).<br>
                        4. Unggah file di bawah ini. Sistem akan menghitung status OK/NG secara otomatis.
                    </div>
                    
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-gray-700 mb-2">Pilih File XLSX / CSV:</label>
                        <div class="custom-file shadow-sm">
                            <input type="file" name="file" class="custom-file-input" id="importFile" accept=".xlsx, .xls, .csv" required>
                            <label class="custom-file-label" for="importFile">Pilih file...</label>
                        </div>
                        <small class="text-muted mt-2 d-block px-1" style="font-size: 0.75rem;">
                            Format didukung: <strong>.xlsx</strong> (disarankan) atau .csv
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn px-4 rounded-pill font-weight-bold text-gray-600 border-0 shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill font-weight-bold shadow-sm no-loader">
                         <i class="fas fa-upload mr-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/fpa.js') }}"></script>
    <script>
        // Update label custom-file-input saat file dipilih
        $(document).on('change', '.custom-file-input', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
    <script id="fpa-index-data" type="application/json">
        {
            "routeIndex": "{{ route('first_piece_approval.index') }}",
            "plant": "{{ request('plant') }}",
            "checksheetIds": @json($checksheets->pluck('id'))
        }
    </script>
    <script>
        $(document).ready(function () {
            const dataEl = document.getElementById('fpa-index-data');
            if (dataEl) {
                const config = JSON.parse(dataEl.textContent);
                window.initFpaIndex({
                    routes: { index: config.routeIndex },
                    plant: config.plant,
                    checksheets: config.checksheetIds
                });
            }

            // Initialize Custom Search
            if (typeof initItemSearch === 'function') {
                initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
                initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
                initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
            }

            var form = document.getElementById('filterFormFpa');
            if (form) {
                // Link Synchronization (Sync Print/Export links with current filter selections)
                function syncExportLinks() {
                    var baseUrlPrint = "{{ route('first_piece_approval.print') }}";
                    var baseUrlPdf = "{{ route('first_piece_approval.export_pdf') }}";
                    var baseUrlExcel = "{{ route('first_piece_approval.export_measurements') }}";
                    
                    var params = new URLSearchParams();
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        if (pair[1]) params.append(pair[0], pair[1]);
                    }
                    
                    var queryString = params.toString();
                    
                    var printBtn = form.querySelector('a[title="Print"]');
                    var pdfBtn = form.querySelector('a[title="Export to PDF"]');
                    var excelBtn = form.querySelector('a[title="Export Metadata Pengukuran (Excel)"]');
                    
                    if (printBtn) printBtn.href = baseUrlPrint + '?' + queryString;
                    if (pdfBtn) pdfBtn.href = baseUrlPdf + '?' + queryString;
                    if (excelBtn) excelBtn.href = baseUrlExcel + '?' + queryString;
                }

                $(form).find('input, select').on('change', syncExportLinks);
                // Also sync on initial load
                syncExportLinks();

                $(form).on('submit', function(e) {
                    var startDate = document.getElementById('start_date').value;
                    var endDate = document.getElementById('end_date').value;

                    if (startDate && endDate && startDate > endDate) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Rentang Tanggal Tidak Valid',
                            text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
                            confirmButtonColor: '#4e73df'
                        });
                    }
                });
            }

            // AJAX Handler for Import Measure Form
            $('#importMeasureForm').on('submit', function(e) {
                e.preventDefault();
                
                let form = $(this);
                let formData = new FormData(this);
                
                // Close modal
                $('#importMetadataModal').modal('hide');
                
                // Show Loading Toast
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Sedang memproses data...',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                confirmButtonColor: '#4e73df'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message,
                                confirmButtonColor: '#4e73df'
                            });
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat mengunggah file.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg,
                            confirmButtonColor: '#4e73df'
                        });
                    }
                });
            });
        });
    </script>

    @php $bulkApproveRoute = route('first_piece_approval.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')
@endpush



