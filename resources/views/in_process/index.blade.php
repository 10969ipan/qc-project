@extends('layouts.admin')

@section('title', 'Checksheet In-Process')

@section('content')
<style>
    .table-responsive {
        max-height: 68vh !important;
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
        font-size: 0.60rem !important;
        padding: 2px 4px !important;
        line-height: 1.1 !important;
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

    #checksheetTable tbody tr:hover {
        background-color: #f1f5f9 !important;
        transition: background-color 0.2s ease;
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    #checksheetTable .btn {
        min-width: 0 !important;
        padding: 0.1rem 0.3rem !important;
        font-size: 0.58rem !important;
        margin: 0px !important;
    }
    #checksheetTable .badge {
        font-size: 0.58rem !important;
        padding: 0.1rem 0.3rem !important;
    }

    /* Exact sticky heights */
    #checksheetTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 24px !important; 
    }
    #checksheetTable > thead > tr:nth-child(2) > th {
        top: 24px !important; 
        z-index: 104 !important;
        height: 20px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        top: 0 !important;
        height: 44px !important; /* 24 + 20 */
        z-index: 106 !important;
    }

    /* Minimalist Dimension Table Styles */
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
        padding: 2px !important;
        text-align: center !important;
    }

    #checksheetTable .table-dimension-minimalist .dim-header {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.55rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
        line-height: 1 !important;
    }

    #checksheetTable .table-dimension-minimalist .dim-data {
        font-size: 0.60rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #1e293b !important;
        line-height: 1.1 !important;
    }

    #checksheetTable .table-dimension-minimalist tr:last-child .dim-data {
        border-bottom: none !important;
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

        $docHeader = \App\Models\GeneralSetting::getDocHeader('in_process', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0201',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '0',
            'halaman' => '- / -'
        ]);
    @endphp

    <!-- Logo Tersembunyi untuk Ekspor PDF -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-2">
        <div class="card-body p-2">
            <div class="mb-2">
                <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                LAPORAN DATA IN PROCESS
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
            <form action="{{ route('in_process.index') }}" method="GET"
                class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 8px; overflow-x: auto; white-space: nowrap;" id="filterFormInProcess">

                {{-- Preserve Context Parameters --}}
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                @if(request('view_mode'))
                    <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
                @endif
                @if(request('entry_method'))
                    <input type="hidden" name="entry_method" value="{{ request('entry_method') }}">
                @endif

                <!-- 1. Field: Part Name -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Part Name</label>
                    <div style="width: 200px;" class="custom-filter-wrapper">
                        <select name="item_id" id="filterItem" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Part Name</option>
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
                    <label class="mb-1 small font-weight-bold text-gray-700">Customer</label>
                    <div style="width: 140px;" class="custom-filter-wrapper">
                        <select name="customer" id="filterCustomer" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer }}" {{ request('customer') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 3. Field: Tanggal (dari - sampai) -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Tanggal</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden" style="border: 1px solid #e2e8f0;">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                            style="width: 125px; font-size: 0.75rem;" value="{{ request('start_date') }}" title="Dari Tanggal">
                        <span class="px-2 text-gray-500 font-weight-bold small">s/d</span>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                            style="width: 125px; font-size: 0.75rem;" value="{{ request('end_date') }}" title="Sampai Tanggal">
                    </div>
                </div>

                <!-- 4. Field: Shift -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Shift</label>
                    <div style="width: 95px;" class="custom-filter-wrapper">
                        <select name="shift" id="filterShift" class="form-control form-control-sm border-0 shadow-sm">
                            <option value="">Semua</option>
                            <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>

                <!-- 5. Field: Inisial -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Inisial</label>
                    <div style="width: 120px;" class="custom-filter-wrapper">
                        <select name="operator_initials" id="filterInisial" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Inisial</option>
                            @foreach($initials as $initial)
                                <option value="{{ $initial }}" {{ request('operator_initials') == $initial ? 'selected' : '' }}>{{ $initial }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 6. Field: WIP / FG -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">WIP / FG</label>
                    <div style="width: 100px;" class="custom-filter-wrapper">
                        <select name="tujuan" id="filterTujuan" class="form-control form-control-sm border-0 shadow-sm">
                            <option value="">Semua</option>
                            <option value="WIP" {{ request('tujuan') == 'WIP' ? 'selected' : '' }}>WIP</option>
                            <option value="FG" {{ request('tujuan') == 'FG' ? 'selected' : '' }}>FG</option>
                        </select>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->role === 'admin')
                <!-- 7. Field: Mesin -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Mesin</label>
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

                <!-- Tombol Filter & Reset -->
                <div class="d-flex align-items-center" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important; margin-left: 10px;">
                    <style>
                        .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                        .custom-filter-wrapper .ips-input { padding: 2px 18px 2px 6px !important; font-size: 0.68rem !important; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: 26px !important; }
                        .custom-filter-wrapper .ips-clear { right: 5px; font-size: 10px; }
                        .custom-filter-wrapper { position: relative; top: 0px; }
                        #filterFormInProcess label { white-space: nowrap; }
                    </style>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Cari Data">
                        <i class="fas fa-search fa-sm mr-1"></i> Filter
                    </button>
                    <a href="{{ route('in_process.index', array_merge(['plant' => request('plant')], request('view_mode') ? ['view_mode' => request('view_mode')] : [])) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Reset Filter">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </a>
                </div>

                <!-- Field: QR Code (Khusus Mode Verifikasi) -->
                @if(request('view_mode') === 'verifikasi')
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">QR Code</label>
                    <div class="input-group input-group-sm shadow-sm rounded" style="width: 190px;">
                        <input type="text" name="qr_raw" id="filterQrRaw" class="form-control border-0"
                            placeholder="Scan/Ketik QR..." value="{{ request('qr_raw') }}" style="font-size: 0.75rem;">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary border-0" id="btnScanQRIndex" title="Scan QR Code" style="min-width: 40px; touch-action: manipulation;">
                                <i class="fas fa-qrcode" style="pointer-events: none;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Tombol Navigasi & Ekspor (Paling Kanan) -->
                <div class="d-flex align-items-center ml-auto" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important;">
                    @if(request('view_mode') !== 'verifikasi')
                        <a href="{{ route('in_process.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'verification', 'plant' => request('plant')])) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Hasil Verifikasi"
                            style="background-color: #6f42c1; color: white; font-size: 0.68rem; height: 26px;">
                            <i class="fas fa-clipboard-check fa-sm mr-1"></i> Hasil Verifikasi
                        </a>
                    @else
                        <a href="{{ route('in_process.index', ['plant' => request('plant')]) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Kembali ke Data Regular"
                            style="background-color: #6c757d; color: white; font-size: 0.68rem; height: 26px;">
                            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
                        </a>
                    @endif
                    <a href="{{ route('in_process.daily_recap', ['start_date' => request('start_date') ?: now()->toDateString(), 'plant' => request('plant')]) }}"
                        class="btn btn-dark btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Rekap Harian Verification" id="btnDailyRecap"
                        style="font-size: 0.68rem; height: 26px;">
                        <i class="fas fa-list-alt fa-sm mr-1"></i> Rekap Harian
                    </a>
                    @if($canExport)
                    <a href="{{ route('in_process.print', request()->query()) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-print-direct d-flex align-items-center" title="Print"
                        style="background-color: #17a589; color: white; font-size: 0.68rem; height: 26px;">
                        <i class="fas fa-print fa-sm mr-1"></i> Cetak
                    </a>
                    @endif
                    <a href="{{ route('in_process.export_measurements', request()->query()) }}"
                        class="btn btn-warning btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Export Data Dimensi (XLSX)"
                        style="background-color: #d97706; color: white; font-size: 0.68rem; height: 26px;">
                        <i class="fas fa-file-excel fa-sm mr-1"></i> Dimensi
                    </a>
                    <button type="button" 
                        class="btn btn-info btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" style="font-size: 0.68rem; height: 26px;"
                        data-toggle="modal" data-target="#importMeasurementsModal" 
                        title="Import Data Dimensi (CSV/XLSX)">
                        <i class="fas fa-file-import fa-sm mr-1"></i> Import
                    </button>
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
                            @if(request('view_mode') === 'verifikasi')
                                <th rowspan="2" class="align-middle">QR-Code</th>
                            @endif
                            <th rowspan="2" class="bg-light align-middle">Checked<br>(Tgl / Shift / Inisial)</th>
                            <th rowspan="2" class="align-middle">Jam (Before)</th>
                            <th rowspan="2" class="align-middle">Jam (After)</th>
                            <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'asst_manager', 'manager', 'supervisor_plating', 'manager_plating']))
                                <th rowspan="2" class="align-middle">No Mesin</th>
                            @endif
                            <th rowspan="2" class="align-middle d-none">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part / Part No</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Total Qty</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th rowspan="2" class="align-middle">Sampling Qty</th>
                                <th rowspan="2" class="align-middle" style="min-width: 170px;">Check Dimensi</th>
                                <th rowspan="2" class="align-middle">Berat Part</th>
                            @endif
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">WIP/FG</th>

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
                                <th style="font-size: 10px;">{{ $plantContext === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                                <th style="font-size: 10px;">Supervisor QC</th>
                                <th style="font-size: 10px;">Asst Manager QC</th>
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
                                @if(request('view_mode') === 'verifikasi')
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
                                 @endif
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }} / {{ $checksheet->shift }} / {{ strtoupper($checksheet->user->initials ?? $checksheet->operator_initials ?? '-') }}
                                </td>
                                <td class="align-middle">
                                    {{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->created_at->format('H:i') }}</td>
                                <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'asst_manager', 'manager', 'supervisor_plating', 'manager_plating', 'oshef']))
                                    <td class="align-middle">{{ $checksheet->code_machine ?? '-' }}</td>
                                @endif
                                <td class="align-middle text-nowrap d-none">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-left text-nowrap">
                                    <span class="font-weight-bold text-gray-800">{{ $checksheet->item->name ?? '-' }}</span><br>
                                    <small class="text-muted"><i class="fas fa-tag mr-1"></i>{{ $checksheet->item->part_number ?? '-' }}</small>
                                </td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->total_qty }}</td>
                                @if(request('view_mode') !== 'verifikasi')
                                <td class="align-middle">{{ $checksheet->sampling_qty }}</td>

                                {{-- Detail Cek Dimensi --}}
                                <td class="align-middle p-0 text-nowrap" style="min-width: 170px; white-space: nowrap;">
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
                                        <div class="px-2 py-1 text-center text-nowrap">
                                            <span class="text-dark font-weight-bold" style="font-size: 0.68rem; white-space: nowrap;">
                                                {{ $checksheet->scan_method === 'hardware' ? 'VERIFIKASI QUALITY IN PROCESS' : 'TIDAK ADA PENGUKURAN DIMENSI' }}
                                            </span>
                                        </div>
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
                                @endif

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
                                 @if(request('view_mode') !== 'verifikasi')
                                 {{-- Kashift QC --}}
                                 <td class="align-middle text-center text-nowrap" style="min-width: 120px;">
                                     @if($checksheet->kashift_qc === 'REJECTED')
                                         <span class="badge badge-danger px-2 py-1">
                                             <i class="fas fa-times-circle mr-1"></i> REJECTED
                                         </span>
                                         <br><small class="text-muted">oleh {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                     @elseif($checksheet->kashift_qc)
                                         <span class="badge badge-success px-2 py-1">
                                             <i class="fas fa-check-circle mr-1"></i> APPROVED
                                         </span>
                                         <br><small class="text-muted">oleh {{ $checksheet->kashift_qc }}</small>
                                     @else
                                         <span class="badge badge-warning text-dark px-2 py-1">
                                             <i class="fas fa-clock mr-1"></i> PENDING
                                         </span>
                                     @endif
                                     @if($checksheet->kashift_approved_at)
                                         <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->kashift_approved_at)->format('d/m/Y H:i') }}</small>
                                     @endif
                                 </td>

                                 {{-- Supervisor QC --}}
                                 <td class="align-middle text-center text-nowrap" style="min-width: 120px;">
                                     @if($checksheet->supervisor_qc === 'REJECTED')
                                         <span class="badge badge-danger px-2 py-1">
                                             <i class="fas fa-times-circle mr-1"></i> REJECTED
                                         </span>
                                         <br><small class="text-muted">oleh {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                     @elseif($checksheet->supervisor_qc)
                                         <span class="badge badge-success px-2 py-1">
                                             <i class="fas fa-check-circle mr-1"></i> APPROVED
                                         </span>
                                         <br><small class="text-muted">oleh {{ $checksheet->supervisor_qc }}</small>
                                     @else
                                         <span class="badge badge-warning text-dark px-2 py-1">
                                             <i class="fas fa-clock mr-1"></i> PENDING
                                         </span>
                                     @endif
                                     @if($checksheet->supervisor_approved_at)
                                         <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->supervisor_approved_at)->format('d/m/Y H:i') }}</small>
                                     @endif
                                 </td>

                                 {{-- Asst Manager QC --}}
                                 <td class="align-middle text-center text-nowrap" style="min-width: 120px;">
                                     @if($checksheet->asst_manager_qc === 'REJECTED')
                                         <span class="badge badge-danger px-2 py-1">
                                             <i class="fas fa-times-circle mr-1"></i> REJECTED
                                         </span>
                                         <br><small class="text-muted">oleh {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                     @elseif($checksheet->asst_manager_qc)
                                         <span class="badge badge-success px-2 py-1">
                                             <i class="fas fa-check-circle mr-1"></i> APPROVED
                                         </span>
                                         <br><small class="text-muted">oleh {{ $checksheet->asst_manager_qc }}</small>
                                     @else
                                         <span class="badge badge-warning text-dark px-2 py-1">
                                             <i class="fas fa-clock mr-1"></i> PENDING
                                         </span>
                                     @endif
                                     @if($checksheet->asst_manager_approved_at)
                                         <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->asst_manager_approved_at)->format('d/m/Y H:i') }}</small>
                                     @endif
                                 </td>

                                 {{-- Manager QC --}}
                                 <td class="align-middle text-center text-nowrap" style="min-width: 120px;">
                                     @if($checksheet->manager_qc === 'REJECTED')
                                         <span class="badge badge-danger px-2 py-1">
                                             <i class="fas fa-times-circle mr-1"></i> REJECTED
                                         </span>
                                         <br><small class="text-muted">oleh {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                     @elseif($checksheet->manager_qc)
                                         <span class="badge badge-success px-2 py-1">
                                             <i class="fas fa-check-circle mr-1"></i> APPROVED
                                         </span>
                                         <br><small class="text-muted">oleh {{ $checksheet->manager_qc }}</small>
                                     @else
                                         <span class="badge badge-warning text-dark px-2 py-1">
                                             <i class="fas fa-clock mr-1"></i> PENDING
                                         </span>
                                     @endif
                                     @if($checksheet->manager_approved_at)
                                         <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</small>
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
                                        @php
                                            // defects disimpan sebagai [['type'=>'Dimensi','qty'=>1], ...]
                                            $defectsArr = (array) ($checksheet->defects ?? []);
                                            $hasNonDimensiDefect = false;
                                            $hasAnyQtyDefect = false;
                                            foreach ($defectsArr as $d) {
                                                $dType = is_array($d) ? ($d['type'] ?? '') : (string) $d;
                                                $dQty  = is_array($d) ? (int) ($d['qty'] ?? 1) : 1;
                                                if ($dQty > 0 && !empty($dType)) {
                                                    $hasAnyQtyDefect = true;
                                                    if (!preg_match('/dimensi|dimension/i', trim($dType))) {
                                                        $hasNonDimensiDefect = true;
                                                    }
                                                }
                                            }
                                            $isOnlyDimensiDefect = $hasAnyQtyDefect && !$hasNonDimensiDefect;
                                        @endphp
                                        @if($checksheet->next_proses && !$isOnlyDimensiDefect)
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
                                    <td class="align-middle text-center text-nowrap no-export" style="{{ auth()->user()->role === 'admin' ? 'width: 50px;' : 'min-width: 170px;' }}">
                                        @php
                                            $user = auth()->user();
                                            $isAdmin = $user->role === 'admin';
                                            $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                                            $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                            $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                            $canApproveKashift = ($user->role === 'kashift' || $isAdmin || $isSpvJakarta || $isKaruJakarta) && (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED');
                                            $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) && (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED');
                                            $canApproveAsst = ($user->role === 'asst_manager' || $isAdmin) && (!$checksheet->asst_manager_qc || $checksheet->asst_manager_qc === 'REJECTED');
                                            $canApproveManager = ($user->role === 'manager' || $isAdmin) && (!$checksheet->manager_qc || $checksheet->manager_qc === 'REJECTED');

                                            $plantContext = strtolower(request('plant') ?? optional($user->plant)->code ?? 'karawang');
                                            $kashiftLabel = ($plantContext === 'jakarta') ? 'Kepala Regu' : 'Kashift QC';
                                            $kashiftAcronym = ($plantContext === 'jakarta') ? '' : ' KS';

                                            $showEdit = (request('view_mode') === 'verifikasi' || $canEdit);
                                            $showDel = (request('view_mode') === 'verifikasi' || $canDelete);
                                            $statusUrl = $isAdmin ? route('admin.in_process.edit_approval', array_merge(['id' => $checksheet->id], request()->all())) : null;
                                        @endphp

                                        @if(request('view_mode') !== 'verifikasi' && $loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif

                                        {{-- Non-Admin Roles: Show Inline Approve/Reject Button for User's Own Role --}}
                                        @if(request('view_mode') !== 'verifikasi' && !$isAdmin)
                                            @if(($user->role === 'kashift' || $isSpvJakarta || $isKaruJakarta) && $canApproveKashift)
                                                <form action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                    <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                    <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kashift)">
                                                        <i class="fas fa-check"></i> Approve{{ $kashiftAcronym }}
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kashift)" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @elseif($user->role === 'supervisor' && $canApproveSupervisor)
                                                <form action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                    <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                    <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV)">
                                                        <i class="fas fa-check"></i> Approve SPV
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV)" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @elseif($user->role === 'asst_manager' && $canApproveAsst)
                                                <form action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'asst_manager'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                    <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                    <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Asst Manager)">
                                                        <i class="fas fa-check"></i> Approve AM
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Asst Manager)" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}asst_manager">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @elseif($user->role === 'manager' && $canApproveManager)
                                                <form action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'manager'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                    <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                    <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Manager)">
                                                        <i class="fas fa-check"></i> Approve MGR
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Manager)" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                        @endif

                                        {{-- 3-Dots Dropdown Menu --}}
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-light btn-sm border shadow-sm" type="button"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                    style="width:32px;height:32px;border-radius:8px;padding:0;" title="Opsi Aksi">
                                                <i class="fas fa-ellipsis-v text-secondary"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius:8px;min-width:180px;">
                                                @if(request('view_mode') !== 'verifikasi' && $isAdmin)
                                                    {{-- Approve Kashift (Admin Only in Dropdown) --}}
                                                    @if($canApproveKashift)
                                                        <form action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline w-100 ajax-form">
                                                            @csrf
                                                            <input type="hidden" name="page" value="{{ request('page') }}">
                                                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                            <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                            <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                            <input type="hidden" name="search" value="{{ request('search') }}">
                                                            <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                            <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                            <button type="submit" class="dropdown-item text-success font-weight-bold">
                                                                <i class="fas fa-check-circle text-success fa-fw mr-2"></i> Approve {{ $kashiftLabel }}
                                                            </button>
                                                        </form>
                                                        <button type="button" class="dropdown-item text-danger font-weight-bold" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift">
                                                            <i class="fas fa-times-circle text-danger fa-fw mr-2"></i> Reject {{ $kashiftLabel }}
                                                        </button>
                                                        <div class="dropdown-divider"></div>
                                                    @endif

                                                    {{-- Approve Supervisor (Admin Only in Dropdown) --}}
                                                    @if($canApproveSupervisor)
                                                        <form action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline w-100 ajax-form">
                                                            @csrf
                                                            <input type="hidden" name="page" value="{{ request('page') }}">
                                                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                            <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                            <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                            <input type="hidden" name="search" value="{{ request('search') }}">
                                                            <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                            <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                            <button type="submit" class="dropdown-item text-success font-weight-bold">
                                                                <i class="fas fa-check-circle text-success fa-fw mr-2"></i> Approve SPV
                                                            </button>
                                                        </form>
                                                        <button type="button" class="dropdown-item text-danger font-weight-bold" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor">
                                                            <i class="fas fa-times-circle text-danger fa-fw mr-2"></i> Reject SPV
                                                        </button>
                                                        <div class="dropdown-divider"></div>
                                                    @endif

                                                    {{-- Approve Asst Manager (Admin Only in Dropdown) --}}
                                                    @if($canApproveAsst)
                                                        <form action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'asst_manager'], request()->all())) }}" method="POST" class="d-inline w-100 ajax-form">
                                                            @csrf
                                                            <input type="hidden" name="page" value="{{ request('page') }}">
                                                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                            <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                            <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                            <input type="hidden" name="search" value="{{ request('search') }}">
                                                            <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                            <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                            <button type="submit" class="dropdown-item text-success font-weight-bold">
                                                                <i class="fas fa-check-circle text-success fa-fw mr-2"></i> Approve AM
                                                            </button>
                                                        </form>
                                                        <button type="button" class="dropdown-item text-danger font-weight-bold" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}asst_manager">
                                                            <i class="fas fa-times-circle text-danger fa-fw mr-2"></i> Reject AM
                                                        </button>
                                                        <div class="dropdown-divider"></div>
                                                    @endif

                                                    {{-- Approve Manager (Admin Only in Dropdown) --}}
                                                    @if($canApproveManager)
                                                        <form action="{{ route('in_process.approve', array_merge(['id' => $checksheet->id, 'type' => 'manager'], request()->all())) }}" method="POST" class="d-inline w-100 ajax-form">
                                                            @csrf
                                                            <input type="hidden" name="page" value="{{ request('page') }}">
                                                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                            <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                            <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                            <input type="hidden" name="search" value="{{ request('search') }}">
                                                            <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                            <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                            <button type="submit" class="dropdown-item text-success font-weight-bold">
                                                                <i class="fas fa-check-circle text-success fa-fw mr-2"></i> Approve MGR
                                                            </button>
                                                        </form>
                                                        <button type="button" class="dropdown-item text-danger font-weight-bold" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager">
                                                            <i class="fas fa-times-circle text-danger fa-fw mr-2"></i> Reject MGR
                                                        </button>
                                                        <div class="dropdown-divider"></div>
                                                    @endif

                                                    @if($statusUrl)
                                                        <a href="{{ $statusUrl }}" class="dropdown-item no-loader btn-status-modal">
                                                            <i class="fas fa-user-check text-info fa-fw mr-2"></i> Status Approval
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                    @endif
                                                @endif

                                                @if($showEdit)
                                                    <a href="{{ route('in_process.edit', array_merge(['id' => $checksheet->id], request()->all())) }}" class="dropdown-item no-loader btn-edit-modal">
                                                        <i class="fas fa-edit text-warning fa-fw mr-2"></i> Edit
                                                    </a>
                                                @endif

                                                @if($showDel)
                                                    @if($showEdit) <div class="dropdown-divider"></div> @endif
                                                    <form action="{{ route('in_process.destroy', array_merge(request()->query(), ['id' => $checksheet->id])) }}" method="POST" class="d-inline w-100">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger btn-delete w-100 text-left">
                                                            <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
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
    <script src="{{ asset('js/checksheet/in-process.js') }}?v={{ time() }}"></script>
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

            // Direct Print (Tanpa Buka Halaman Baru & Tanpa Double Dialog)
            $(document).on('click', '.btn-print-direct', function(e) {
                e.preventDefault();
                var printUrl = $(this).attr('href');
                if (!printUrl || printUrl === '#') return;

                var oldIframe = document.getElementById('silentPrintIframe');
                if (oldIframe) {
                    oldIframe.parentNode.removeChild(oldIframe);
                }

                var iframe = document.createElement('iframe');
                iframe.id = 'silentPrintIframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                iframe.style.opacity = '0';
                iframe.src = printUrl;

                document.body.appendChild(iframe);
            });
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
