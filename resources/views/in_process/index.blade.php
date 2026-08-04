@extends('layouts.admin')

@section('title', 'Checksheet In-Process')

@section('content')
<style>
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #checksheetTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    
    #checksheetTable td, #checksheetTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }

    #checksheetTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.68rem !important;
        padding: 4px 6px !important;
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 6px 12px !important; /* Wider padding so it's not cramped sideways */
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important; /* Force all headers to be side-by-side */
        box-shadow: inset 0 -1px 0 #e2e8f0;
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    #checksheetTable .btn {
        min-width: 0 !important; /* Overrides 110px inline style */
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
    #checksheetTable .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    /* Exact sticky heights since headers no longer wrap */
    #checksheetTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 48px !important; 
    }
    #checksheetTable > thead > tr:nth-child(2) > th {
        top: 48px !important; 
        z-index: 104 !important;
        height: 38px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        top: 0 !important;
        height: 86px !important; /* 48 + 38 */
        z-index: 106 !important;
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

    #checksheetTable .table-dimension-minimalist .dim-data {
        font-size: 0.65rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #1e293b !important;
        line-height: 1.2 !important;
    }

    #checksheetTable .table-dimension-minimalist tr:last-child .dim-data {
        border-bottom: none !important;
    }

    #checksheetTable .table-dimension-minimalist .text-std-header { 
        color: #64748b !important; 
        font-weight: 600 !important; 
        background-color: #f1f5f9 !important; 
    }

    /* Sticky Pagination */
    .pagination-container {
        position: sticky !important;
        bottom: 0 !important;
        background-color: #ffffff !important;
        z-index: 106 !important;
        padding: 12px 20px !important;
        margin: 0 -20px -20px -20px !important;
        border-top: 1px solid #e2e8f0 !important;
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05) !important;
        border-bottom-left-radius: 0.35rem;
        border-bottom-right-radius: 0.35rem;
    }
    
    .pagination-container .pagination {
        margin-bottom: 0 !important;
    }
</style>
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');
        
        // Resolve menu IDs for permission checks (support for duplicate plant routes)
        $menuIds = \App\Models\AppMenu::where('route', 'in_process.index')->pluck('id');
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
                            LAPORAN DATA CHECKSHEET IN-PROCESS
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
    <!-- Logo Tersembunyi untuk Ekspor PDF -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            @if(request('view_mode') === 'verifikasi')
                <h6 class="m-0 font-weight-bold" style="color: #707070ff;">Data Hasil Verifikasi In-Process</h6>
            @else
                <h6 class="m-0 font-weight-bold text-primary">Data Masuk In-Process</h6>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('in_process.index') }}" method="GET"
                class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 8px; overflow-x: auto; white-space: nowrap;" id="filterFormInProcess">

                <input type="hidden" name="plant" value="{{ request('plant') }}">
                @if(request()->has('view_mode'))
                    <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
                @endif
                @if(request()->has('entry_method'))
                    <input type="hidden" name="entry_method" value="{{ request('entry_method') }}">
                @endif

                <!-- Field: Part -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Part:</label>
                    <div style="width: 200px;" class="custom-filter-wrapper">
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
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tgl:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                            style="width: 120px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                        <span class="px-1 text-gray-500 small">-</span>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                            style="width: 120px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                    </div>
                </div>

                <!-- Field: Inisial -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Inisial:</label>
                    <div style="width: 110px;" class="custom-filter-wrapper">
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
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cust:</label>
                    <div style="width: 110px;" class="custom-filter-wrapper">
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
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Shift:</label>
                    <div style="width: 90px;" class="custom-filter-wrapper">
                        <select name="shift" id="filterShift" class="form-control form-control-sm border-0 shadow-sm">
                            <option value="">Semua</option>
                            <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>

                <!-- Field: WIP/FG -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">WIP/FG:</label>
                    <div style="width: 100px;" class="custom-filter-wrapper">
                        <select name="tujuan" id="filterTujuan" class="form-control form-control-sm border-0 shadow-sm">
                            <option value="">Semua</option>
                            <option value="WIP" {{ request('tujuan') == 'WIP' ? 'selected' : '' }}>WIP</option>
                            <option value="FG" {{ request('tujuan') == 'FG' ? 'selected' : '' }}>FG</option>
                        </select>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->role === 'admin')
                <!-- Field: Mesin (Khusus Admin) -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-primary"><i class="fas fa-cogs mr-1"></i>Mesin:</label>
                    <div style="width: 115px;" class="custom-filter-wrapper">
                        <select name="code_machine" id="filterMachine" class="form-control form-control-sm border-0 shadow-sm font-weight-bold">
                            <option value="">Semua Mesin</option>
                            @foreach($machines as $m)
                                <option value="{{ $m }}" {{ request('code_machine') == $m ? 'selected' : '' }}>
                                    MESIN-{{ $m }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif



                <!-- Field: QR Raw -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">QR:</label>
                    <div class="input-group input-group-sm shadow-sm rounded" style="width: 200px;">
                        <input type="text" name="qr_raw" id="filterQrRaw" class="form-control border-0"
                            placeholder="Scan/Ketik QR..." value="{{ request('qr_raw') }}" style="font-size: 0.75rem;">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary border-0" id="btnScanQRIndex" title="Scan QR Code" style="min-width: 40px; touch-action: manipulation;">
                                <i class="fas fa-qrcode" style="pointer-events: none;"></i>
                            </button>
                        </div>
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
                    <a href="{{ route('in_process.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if(request('view_mode') !== 'verifikasi')
                        <a href="{{ route('in_process.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'verification', 'plant' => request('plant')])) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Data Hasil Verifikasi"
                            style="background-color: #6f42c1; color: white;">
                            <i class="fas fa-clipboard-check fa-sm"></i> Hasil Verifikasi
                        </a>
                    @else
                        <a href="{{ route('in_process.index', ['plant' => request('plant')]) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Kembali ke Data Regular"
                            style="background-color: #6c757d; color: white;">
                            <i class="fas fa-arrow-left fa-sm"></i> Kembali
                        </a>
                    @endif
                    @if($canExport)
                    <a href="{{ route('in_process.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('in_process.print', request()->query()) }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
                    </a>
                    @endif
                    <a href="{{ route('in_process.export_measurements', request()->query()) }}"
                        class="btn btn-warning btn-sm shadow-sm rounded-pill px-3 no-loader" title="Export Data Dimensi (XLSX)"
                        style="background-color: #d97706; color: white;">
                        <i class="fas fa-file-excel fa-sm"></i>
                    </a>
                    <button type="button" 
                        class="btn btn-info btn-sm shadow-sm rounded-pill px-3 no-loader" 
                        data-toggle="modal" data-target="#importMeasurementsModal" 
                        title="Import Data Dimensi (XLSX / CSV)">
                        <i class="fas fa-file-import fa-sm"></i>
                    </button>
                    <a href="{{ route('in_process.daily_recap', ['start_date' => request('start_date') ?: now()->toDateString(), 'plant' => request('plant')]) }}"
                        id="btnDailyRecap"
                        target="_blank"
                        class="btn btn-dark btn-sm shadow-sm rounded-pill px-3 no-loader" title="Daily Recap Verification">
                        <i class="fas fa-list-alt fa-sm"></i>
                    </a>
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-hover" width="100%" cellspacing="0" id="checksheetTable">
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
                            @if(auth()->user()->role === 'admin')
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
                            <th rowspan="2" class="align-middle">QR-Code</th>
                            <th rowspan="2" class="align-middle">Tanggal</th>
                            <th rowspan="2" class="align-middle">Jam (Before)</th>
                            <th rowspan="2" class="align-middle">Jam (After)</th>
                            <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'asst_manager', 'manager', 'supervisor_plating', 'manager_plating']))
                                <th rowspan="2" class="align-middle">No Mesin</th>
                            @endif
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
                            <th rowspan="2" class="align-middle">WIP/FG</th>
                            <th rowspan="2" class="align-middle">Inspector</th>

                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="4" class="align-middle">Approval Status</th>
                            @endif
                            <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            @if(request('view_mode') === 'verifikasi' ? auth()->user()->role !== 'inspector' : !in_array(auth()->user()->role, ['inspector']))
                                <th rowspan="2" class="no-export align-middle">Actions</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="width: 60px; min-width: 60px;">Pcs</th>
                            <th style="min-width: 150px;">Jenis NG</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th style="font-size: 10px;">{{ $plantContext === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}
                                </th>
                                <th style="font-size: 10px;">Supervisor QC</th>
                                <th style="font-size: 10px;">Asst. Manager QC</th>
                                <th style="font-size: 10px;">Manager QC</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checksheets as $checksheet)
                            <tr class="text-center">
                                @if(auth()->user()->role === 'admin')
                                    <td class="align-middle text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input row-checkbox" id="checkRow{{ $checksheet->id }}" value="{{ $checksheet->id }}">
                                            <label class="custom-control-label" for="checkRow{{ $checksheet->id }}" style="cursor:pointer;"></label>
                                        </div>
                                    </td>
                                @endif
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle">
                                    @if($canExport)
                                    <button type="button" class="btn btn-sm btn-primary btn-qr-detail" 
                                        data-qr="{{ $checksheet->qrcode }}"
                                        data-part="{{ $checksheet->part_code }}"
                                        data-supplier="{{ $checksheet->supplier_id }}"
                                        data-qty="{{ $checksheet->quantity }}"
                                        data-unique="{{ $checksheet->unique_code_id }}"
                                        data-sap="{{ $checksheet->sap_code ?? '-' }}">
                                        <i class="fas fa-qrcode"></i> View
                                    </button>
                                    @else
                                    <span class="badge badge-light text-muted small"><i class="fas fa-lock mr-1"></i> No Access</span>
                                    @endif
                                </td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">
                                    {{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->created_at->format('H:i') }}</td>
                                <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'asst_manager', 'manager', 'supervisor_plating', 'manager_plating', 'oshef']))
                                    <td class="align-middle">{{ $checksheet->code_machine ?? '-' }}</td>
                                @endif
                                <td class="align-middle">{{ $checksheet->shift }}</td>
                                <td class="align-middle text-nowrap d-none">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->name ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->total_qty }}</td>
                                <td class="align-middle">{{ $checksheet->sampling_qty }}</td>

                                {{-- Detail Cek Dimensi --}}
                                <td class="align-middle p-0">
                                    @php
                                        $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true);
                                        $dimensions = $dimensions ?: [];

                                        // Periksa apakah ada input pengguna yang sebenarnya
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

                                        // Temukan titik aktif (kolom yang memiliki data atau ditentukan dalam standar)
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

                                        // Titik default jika tidak ditemukan
                                        if (empty($activePoints)) {
                                            $activePoints = range(1, 5);
                                        }

                                        // Temukan cavity maksimal untuk merender baris
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
                                                <thead class="text-center">
                                                    {{-- Baris Standar --}}
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

                                                    {{-- Baris Min (Optional) --}}
                                                    @php
                                                        $hasMinData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && ($standards[$j]['min'] ?? null) !== null && ($standards[$j]['min'] ?? null) !== '' && ($standards[$j]['min'] ?? null) !== '-') {
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
                                                                    {{ (isset($standards[$j]) && ($standards[$j]['min'] ?? null) !== null) ? $standards[$j]['min'] : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Baris Max (Optional) --}}
                                                    @php
                                                        $hasMaxData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && ($standards[$j]['max'] ?? null) !== null && ($standards[$j]['max'] ?? null) !== '' && ($standards[$j]['max'] ?? null) !== '-') {
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
                                                                    {{ (isset($standards[$j]) && ($standards[$j]['max'] ?? null) !== null) ? $standards[$j]['max'] : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Baris Toleransi --}}
                                                    @php
                                                        $hasTolData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && ($standards[$j]['tolerance'] ?? null) !== null && ($standards[$j]['tolerance'] ?? null) !== '' && ($standards[$j]['tolerance'] ?? null) !== '-') {
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
                                                                    {{ isset($standards[$j]) ? '±' . ($standards[$j]['tolerance'] ?? '-') : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Baris Header Utama --}}
                                                    <tr>
                                                        <td class="dim-header">Cav</td>
                                                        @foreach ($activePoints as $j)
                                                            <td class="dim-header">Ø{{ $j }}</td>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- Pengukuran Aktual --}}
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
                                                                                // Fallback for array structure
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

                                                                            if ($isNG) $anyNGInRow = true;
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
                                        <span class="text-dark font-weight-bold" style="font-size: 0.8rem;">
                                            {{ $checksheet->scan_method === 'hardware' ? 'VERIFIKASI QUALITY IN PROCESS' : 'TIDAK ADA PENGUKURAN DIMENSI' }}
                                        </span>
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
                                                $nameLines[] = $d['type'];
                                            } elseif (is_string($d)) {
                                                $pcsLines[] = 1;
                                                $nameLines[] = $d;
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

                                <td colspan="2" class="align-middle" style="padding: 0px !important; vertical-align: middle !important;">
                                    @if(count($pcsLines) > 0)
                                        <table style="width: 100% !important; border-collapse: collapse !important; margin: 0px !important; padding: 0px !important; border: none !important; table-layout: fixed;">
                                            <tbody>
                                                @foreach($pcsLines as $index => $qty)
                                                    <tr style="border: none !important; border-bottom: {{ $index < count($pcsLines) - 1 ? '1.5px solid #dee2e6 !important' : 'none !important' }}; background: transparent !important;">
                                                        <td style="width: 60px; min-width: 60px; max-width: 60px; border: none !important; border-right: 1.5px solid #dee2e6 !important; padding: 4px 6px !important; vertical-align: middle !important; background: transparent !important;" class="text-center">
                                                            <small class="text-danger font-weight-bold">{{ $qty }}</small>
                                                        </td>
                                                        <td style="border: none !important; padding: 4px 6px !important; vertical-align: middle !important; background: transparent !important;" class="text-center">
                                                            <small class="text-danger font-weight-bold">{{ $nameLines[$index] ?? '-' }}</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="py-1 text-center" style="padding: 4px 6px !important;">-</div>
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
                                <td class="align-middle font-weight-bold text-nowrap" style="font-size: 0.75rem;">{{ in_array($checksheet->tujuan, ['WIP', 'FG']) ? $checksheet->tujuan : '-' }}</td>
                                <td class="align-middle text-uppercase">{{ $checksheet->user->initials ?? $checksheet->operator_initials ?? '-' }}</td>

                                @if(request('view_mode') !== 'verifikasi')
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
                                @endif {{-- end view_mode !== verifikasi --}}

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


                                @if(request('view_mode') === 'verifikasi' ? auth()->user()->role !== 'inspector' : !in_array(auth()->user()->role, ['inspector']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 350px;">
                                        @php
                                            $user = auth()->user();
                                            $isAdmin = $user->role === 'admin';
                                        @endphp
                                        @if(request('view_mode') !== 'verifikasi')
                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif
                                        {{-- Tombol Aksi untuk Persetujuan --}}
                                        @php
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
                                                action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())) }}"
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
                                                action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())) }}"
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
                                                action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'asst_manager'], request()->all())) }}"
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
                                                action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'manager'], request()->all())) }}"
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
                                        @endif

                                        @php $showEdit = (request('view_mode') === 'verifikasi' || $canEdit); $showDel = (request('view_mode') === 'verifikasi' || $canDelete); @endphp
                                        @include('partials.action_dropdown', [
                                            'canEdit'      => $showEdit,
                                            'canDelete'    => $showDel,
                                            'editUrl'      => route('in_process.edit', array_merge(['id' => $checksheet->id], request()->all())),
                                            'deleteRoute'  => route('in_process.destroy', array_merge(request()->query(), ['id' => $checksheet->id])),
                                            'deleteParams' => [],
                                            'statusUrl'    => $isAdmin ? route('admin.in_process.edit_approval', array_merge(['id' => $checksheet->id], request()->all())) : null,
                                        ])
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 pagination-container">
                {{ $checksheets->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- QR Code Traceability Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="qrModalLabel">
                        <i class="fas fa-qrcode mr-2"></i> Traceability QR Code
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width: 25%">QR Raw</th>
                            <td id="modal-qr-raw" style="word-break: break-all; font-family: monospace;"></td>
                        </tr>
                        <tr>
                            <th>Part Code</th>
                            <td id="modal-qr-part"></td>
                        </tr>
                        <tr>
                            <th>Supplier ID</th>
                            <td id="modal-qr-supplier"></td>
                        </tr>
                        <tr>
                            <th>Qty</th>
                            <td id="modal-qr-qty"></td>
                        </tr>
                        <tr>
                            <th>Unique ID</th>
                            <td id="modal-qr-unique"></td>
                        </tr>
                        <tr>
                            <th>SAP Code</th>
                            <td id="modal-qr-sap"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-white" style="border-bottom: 2px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem;">
                    <h5 class="modal-title text-primary font-weight-bold" id="editModalLabel">
                        <i class="fas fa-edit mr-2"></i>Edit Checksheet In-Process
                    </h5>
                    <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light px-4 py-4" id="editModalBody">
                    <!-- Loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted small">Memuat data checksheet...</p>
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
                                action="{{ route('in_process.reject', array_merge(['id' => $cs->id, 'type' => $rejectType], request()->all())) }}"
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

@endsection
@push('scripts')
    <script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/checksheet/in-process.js') }}"></script>
    <script>
        $(document).ready(function () {
            window.initInProcessIndex({
                qrScannerModalId: '#qrScannerModal',
                btnScanId: '#btnScanQRIndex',
                inputQrId: '#filterQrRaw'
            });

            // Initialize Custom Search
            if (typeof initItemSearch === 'function') {
                initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
                initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
                initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
                initItemSearch('filterMethod', { placeholder: 'Ketik Tipe...', maxResults: 5 });
            }

            var form = document.getElementById('filterFormInProcess');
            if (form) {
                // Link Synchronization (Sync Print/Export links with current filter selections)
                function syncExportLinks() {
                    var baseUrlPrint = "{{ route('in_process.print') }}";
                    var baseUrlPdf = "{{ route('in_process.export_pdf') }}";
                    var baseUrlMeasurements = "{{ route('in_process.export_measurements') }}";
                    
                    var baseUrlRecap = "{{ route('in_process.daily_recap') }}";
                    
                    var params = new URLSearchParams();
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        if (pair[1]) params.append(pair[0], pair[1]);
                    }
                    
                    var queryString = params.toString();
                    
                    var printBtn = form.querySelector('a[title="Print"]');
                    var pdfBtn = form.querySelector('a[title="Export to PDF"]');
                    var measurementsBtn = form.querySelector('a[title="Export Data Dimensi (XLSX)"]');
                    var recapBtn = document.getElementById('btnDailyRecap');
                    
                    if (printBtn) printBtn.href = baseUrlPrint + '?' + queryString;
                    if (pdfBtn) pdfBtn.href = baseUrlPdf + '?' + queryString;
                    if (measurementsBtn) measurementsBtn.href = baseUrlMeasurements + '?' + queryString;
                    if (recapBtn) recapBtn.href = baseUrlRecap + '?' + queryString;
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
        });
    </script>

    <!-- Modal Import Data Dimensi -->
    <div class="modal fade" id="importMeasurementsModal" tabindex="-1" role="dialog" aria-labelledby="importMeasurementsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('in_process.import_measurements') }}" method="POST" enctype="multipart/form-data" id="importMeasurementsForm" class="no-loader">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="importMeasurementsModalLabel">
                            <i class="fas fa-file-import mr-2"></i> Import Data 
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Petunjuk:</strong><br>
                            1. Gunakan fitur <strong>Export Data Dimensi</strong> untuk mendapatkan file template <strong>.xlsx</strong>.<br>
                            2. Masukkan nilai hasil ukur pada kolom yang sesuai di Excel.<br>
                            3. Simpan file tetap dalam format <strong>.xlsx</strong> (tidak perlu ubah ke CSV).<br>
                            4. Unggah file di bawah ini. Sistem akan menghitung status OK/NG secara otomatis.
                        </div>
                        <div class="form-group">
                            <label for="importFile" class="font-weight-bold">Pilih File XLSX / CSV:</label>
                            <div class="custom-file">
                                <input type="file" name="file" class="custom-file-input" id="importFile" accept=".xlsx,.xls,.csv" required>
                                <label class="custom-file-label" for="importFile">Pilih file...</label>
                            </div>
                            <small class="form-text text-muted">Format didukung: <strong>.xlsx</strong> (disarankan) atau .csv</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary shadow-sm no-loader" id="btnSubmitImport">
                            <i class="fas fa-upload mr-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Custom file input label update
        $('#importFile').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        $('#importMeasurementsForm').on('submit', function() {
            // Tutup modal agar tidak menutupi layar setelah klik proses
            $('#importMeasurementsModal').modal('hide');
            
            // Gunakan toast kecil atau biarkan browser menangani loading bar
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'Mengunggah & Memproses Data...',
                showConfirmButton: false,
                timerProgressBar: true
            });
        });
    </script>

    @include('partials.qr_scanner_modal')

    @php $bulkApproveRoute = route('in_process.bulk_approve'); @endphp
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

    <script>
        $(document).ready(function() {
            // Restore scroll position
            var savedScroll = sessionStorage.getItem('inProcessScrollPos');
            if (savedScroll) {
                $('.table-responsive').scrollTop(savedScroll);
                sessionStorage.removeItem('inProcessScrollPos');
            }

            // Save scroll position before leaving or reloading
            $(window).on('beforeunload', function() {
                sessionStorage.setItem('inProcessScrollPos', $('.table-responsive').scrollTop());
            });

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

                // Show or hide floating menu
                if (checkedCount > 0) {
                    bulkMenu.fadeIn(200);
                } else {
                    bulkMenu.fadeOut(200);
                }

                // Add slight background color to checked rows
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

            // Handle Bulk Delete
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
                                url: '{{ route("in_process.bulk_destroy") }}' + window.location.search,
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
