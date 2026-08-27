@extends('layouts.admin')

@section('title', 'Checksheet Plating')

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
</style>

    @php
        $currentMenu = \App\Models\AppMenu::where('route', 'plating.index')->first();
        $menuId = $currentMenu ? $currentMenu->id : null;
        $canExport = true; $canEdit = true; $canDelete = true;
        if ($menuId) {
            $canExport = false; $canEdit = false; $canDelete = false;
            if (auth()->user()->role === 'admin') {
                $canExport = true; $canEdit = true; $canDelete = true;
            } else {
                if (auth()->user()->hasPermission($menuId, 'export')) $canExport = true;
                if (auth()->user()->hasPermission($menuId, 'edit')) $canEdit = true;
                if (auth()->user()->hasPermission($menuId, 'delete')) $canDelete = true;
            }
        }
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        $docHeader = \App\Models\GeneralSetting::getDocHeader('plating', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0202',
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
                                LAPORAN DATA PLATING
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
            <form action="{{ route('plating.index') }}" method="GET"
                class="d-flex flex-wrap align-items-end bg-light p-2 rounded mb-2 shadow-sm"
                style="gap: 8px; overflow-x: auto;" id="filterFormPlating">

                {{-- Preserve Context Parameters --}}
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                @if(request('view_mode'))
                    <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
                @endif

                <!-- 1. Field: Part Name -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Part Name</label>
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
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Customer</label>
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

                <!-- 5. Field: Inisial -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Inisial</label>
                    <div style="width: 120px;" class="custom-filter-wrapper">
                        <select name="operator_initials" id="filterInisial" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Inisial</option>
                            @foreach($initials as $initial)
                                <option value="{{ $initial }}" {{ request('operator_initials') == $initial ? 'selected' : '' }}>{{ $initial }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tombol Filter & Reset (Di Samping Inisial dengan 2x Space) -->
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
                    <a href="{{ route('plating.index', array_merge(['plant' => request('plant')], request('view_mode') ? ['view_mode' => request('view_mode')] : [])) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Reset Filter">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </a>
                </div>

                @if(request('view_mode') === 'verifikasi')
                <!-- 6. Field: QR Code (Tampilkan HANYA untuk Mode Verifikasi) -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">QR Code</label>
                    <div class="input-group input-group-sm shadow-sm rounded" style="width: 180px;">
                        <input type="text" name="qr_raw" id="filterQrRaw" class="form-control border-0"
                            placeholder="Scan/Ketik QR..." value="{{ request('qr_raw') }}" style="font-size: 0.70rem; height: 26px;">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary border-0" id="btnScanQRIndex" title="Scan QR Code" style="height: 26px; padding: 0 8px;">
                                <i class="fas fa-qrcode"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Tombol Navigasi & Ekspor (Paling Kanan) -->
                <div class="d-flex align-items-center ml-auto" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important;">
                    @if(request('view_mode') !== 'verifikasi')
                        <a href="{{ route('plating.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'verification', 'plant' => request('plant')])) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Hasil Verifikasi"
                            style="background-color: #6f42c1; color: white; font-size: 0.68rem; height: 26px;">
                            <i class="fas fa-clipboard-check fa-sm mr-1"></i> Hasil Verifikasi
                        </a>
                    @else
                        <a href="{{ route('plating.index', ['plant' => request('plant')]) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Kembali ke Data Regular"
                            style="background-color: #6c757d; color: white; font-size: 0.68rem; height: 26px;">
                            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
                        </a>
                    @endif
                    <a href="{{ route('plating.daily_recap', ['start_date' => request('start_date') ?: now()->toDateString(), 'plant' => request('plant')]) }}"
                        id="btnDailyRecap"
                        class="btn btn-dark btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Rekap Harian Verification"
                        style="font-size: 0.68rem; height: 26px;" target="_blank">
                        <i class="fas fa-list-alt fa-sm mr-1"></i> Rekap
                    </a>
                    @if($canExport)
                    <a href="{{ route('plating.print', request()->query()) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-print-direct d-flex align-items-center" title="Print"
                        style="background-color: #17a589; color: white; font-size: 0.68rem; height: 26px;">
                        <i class="fas fa-print fa-sm mr-1"></i> Cetak
                    </a>
                    @endif
                </div>
            </form>

            <style>
                .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                .custom-filter-wrapper .ips-input { 
                    padding: 4px 20px 4px 8px; 
                    font-size: 0.75rem; 
                    border: none !important; 
                    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important; 
                    height: calc(1.5em + 0.5rem + 2px); 
                    border-radius: 0.35rem;
                }
                .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
                .custom-filter-wrapper { position: relative; top: -1px; }
                .card-body form label { white-space: nowrap; }
            </style>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0" id="checksheetTable">
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
                            <th rowspan="2" class="align-middle" style="width: 50px;">No</th>
                            @if(request('view_mode') === 'verifikasi')
                                <th rowspan="2" class="align-middle">QR-Code</th>
                                <th rowspan="2" class="bg-light align-middle">Checked<br>(Tgl / Shift / Inisial)</th>
                                <th rowspan="2" class="align-middle">Waktu Check<br>(Start - Finish / Cycle Time)</th>
                            @else
                                <th rowspan="2" class="bg-light align-middle">Injection<br>(Tgl / Shift / Inisial)</th>
                                <th rowspan="2" class="bg-light align-middle">Plating<br>(Tgl / Shift / Lot)</th>
                                <th rowspan="2" class="bg-light align-middle">Checked<br>(Tgl / Shift / Inisial)</th>
                                <th rowspan="2" class="align-middle">Waktu Check<br>(Start - Finish / Cycle Time)</th>
                            @endif
                            <th rowspan="2" class="align-middle">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part / Part No</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Qty<br>(Total / Check)</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>

                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="4" class="align-middle">Approval Status</th>
                            @endif
                            <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            @if(request('view_mode') === 'verifikasi' ? auth()->user()->role !== 'inspector' : !in_array(auth()->user()->role, ['inspector']))
                                <th rowspan="2" class="no-export align-middle">Action</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="width: 50px; min-width: 40px; white-space: nowrap;">Pcs</th>
                            <th style="white-space: nowrap;">Jenis NG</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th style="font-size: 10px; min-width: 120px;">{{ $plantContext === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                                <th style="font-size: 10px; min-width: 120px;">Supervisor QC</th>
                                <th style="font-size: 10px; min-width: 120px;">Asst Manager QC</th>
                                <th style="font-size: 10px; min-width: 120px;">Manager QC</th>
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
                                        @php
                                            $wipQr    = '-';
                                            $pasangQr = '-';
                                            $cabutQr  = '-';
                                            $qcQr     = $checksheet->qrcode_verifikasi ?: '-';

                                            $rawQr = $checksheet->qrcode;

                                            // Determine if the stored qrcode is CBT format (6 segments, last segment starts with CBT-)
                                            $isCbtFormat = $rawQr && preg_match('/\|CBT-\d+$/', $rawQr);

                                            if ($checksheet->platingCabutSplit && $checksheet->platingCabutSplit->cabutRecord) {
                                                // Found the CBT split record — resolve the full chain
                                                $cabutRec = $checksheet->platingCabutSplit->cabutRecord;
                                                $cabutQr  = $checksheet->platingCabutSplit->generated_qrcode ?: ($isCbtFormat ? $rawQr : '-');
                                                $wipQr    = $cabutRec->pasangRecord ? ($cabutRec->pasangRecord->wip_qrcode ?: '-') : '-';
                                                $pasangQr = $cabutRec->pasangRecord ? ($cabutRec->pasangRecord->generated_qrcode ?: '-') : '-';
                                            } elseif ($isCbtFormat) {
                                                // No split record yet but qrcode looks like CBT — show in Cabut slot
                                                $cabutQr = $rawQr;
                                            } elseif ($rawQr && $qcQr === '-') {
                                                // qrcode is NOT CBT format and no qrcode_verifikasi — treat as QC Verifikasi
                                                $qcQr = $rawQr;
                                            }
                                        @endphp
                                        <button type="button" class="btn btn-sm btn-primary btn-qr-detail" 
                                            data-qr="{{ $checksheet->qrcode }}"
                                            data-part="{{ $checksheet->part_code ?? '-' }}"
                                            data-supplier="{{ $checksheet->supplier_id ?? '-' }}"
                                            data-qty="{{ $checksheet->quantity ?? '-' }}"
                                            data-unique="{{ $checksheet->unique_code_id ?? '-' }}"
                                            data-sap="{{ $checksheet->sap_code ?? '-' }}"
                                            data-qr-wip="{{ $wipQr }}"
                                            data-qr-pasang="{{ $pasangQr }}"
                                            data-qr-cabut="{{ $cabutQr }}"
                                            data-qr-qc="{{ $qcQr }}">
                                            <i class="fas fa-qrcode"></i> View
                                        </button>
                                        @else
                                        <span class="badge badge-light text-muted small"><i class="fas fa-lock mr-1"></i> No Access</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-nowrap">
                                        {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }} / {{ $checksheet->shift }} / {{ strtoupper($checksheet->user->initials ?? $checksheet->operator_initials ?? '-') }}
                                    </td>
                                    @php
                                        $sec = (int) ($checksheet->cycle_time ?? 0);
                                        $ctStr = ($sec > 0) ? (($sec < 60) ? ($sec . 's') : (floor($sec / 60) . 'm' . (($sec % 60 > 0) ? ' ' . ($sec % 60) . 's' : ''))) : '-';
                                    @endphp
                                    <td class="align-middle text-nowrap">
                                        {{ $checksheet->created_at->copy()->subSeconds($sec)->format('H:i') }} - {{ $checksheet->created_at->format('H:i') }} <span class="text-muted">({{ $ctStr }})</span>
                                    </td>
                                @else
                                    <td class="align-middle text-nowrap">
                                        {{ $checksheet->injection_date ? $checksheet->injection_date->format('d-m-Y') : '-' }} / {{ $checksheet->injection_shift ?? '-' }} / {{ strtoupper($checksheet->injection_initials ?? '-') }}
                                    </td>
                                    <td class="align-middle text-nowrap">
                                        {{ $checksheet->plating_date ? $checksheet->plating_date->format('d-m-Y') : '-' }} / {{ $checksheet->plating_shift ?? '-' }} / {{ $checksheet->no_lot ?? '-' }}
                                    </td>
                                    <td class="align-middle text-nowrap">
                                        {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }} / {{ $checksheet->shift }} / {{ strtoupper($checksheet->user->initials ?? $checksheet->operator_initials ?? '-') }}
                                    </td>
                                    @php
                                        $sec = (int) ($checksheet->cycle_time ?? 0);
                                        $ctStr = ($sec > 0) ? (($sec < 60) ? ($sec . 's') : (floor($sec / 60) . 'm' . (($sec % 60 > 0) ? ' ' . ($sec % 60) . 's' : ''))) : '-';
                                    @endphp
                                    <td class="align-middle text-nowrap">
                                        {{ $checksheet->created_at->copy()->subSeconds($sec)->format('H:i') }} - {{ $checksheet->created_at->format('H:i') }} <span class="text-muted">({{ $ctStr }})</span>
                                    </td>
                                @endif
                                <td class="align-middle text-nowrap">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-left text-nowrap">
                                    <span class="font-weight-bold text-gray-800">{{ $checksheet->item->name ?? '-' }}</span><br>
                                    <small class="text-muted">{{ $checksheet->item->part_number ?? '-' }}</small>
                                </td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->total_qty }}</td>
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
                                @endphp

                                <td colspan="2" class="align-middle" style="padding: 0px !important; vertical-align: middle !important; white-space: nowrap;">
                                    @if(count($pcsLines) > 0)
                                        <table style="width: 100% !important; border-collapse: collapse !important; margin: 0px !important; padding: 0px !important; border: none !important; table-layout: auto;">
                                            <tbody>
                                                @foreach($pcsLines as $index => $qty)
                                                    <tr style="border: none !important; border-bottom: {{ $index < count($pcsLines) - 1 ? '1.5px solid #dee2e6 !important' : 'none !important' }}; background: transparent !important;">
                                                        <td style="width: 60px; min-width: 60px; max-width: 60px; border: none !important; border-right: 1.5px solid #dee2e6 !important; padding: 4px 6px !important; vertical-align: middle !important; background: transparent !important;" class="text-center">
                                                            <small class="text-danger font-weight-bold">{{ $qty }}</small>
                                                        </td>
                                                        <td style="border: none !important; padding: 4px 6px !important; vertical-align: middle !important; background: transparent !important; white-space: nowrap;" class="text-center">
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
                                    @if($checksheet->qrcode)
                                        <span class="badge badge-pill px-3 py-1 font-weight-bold" 
                                            style="background-color: {{ $checksheet->judgment == 'OK' ? '#1cc88a' : '#e74a3b' }}; color: white; font-size: 0.7rem;">
                                            {{ $checksheet->judgment }}
                                        </span>
                                    @else
                                        <span class="text-muted font-weight-bold">-</span>
                                    @endif
                                </td>

                                @if(request('view_mode') !== 'verifikasi')
                                {{-- Kashift QC --}}
                                <td class="align-middle text-center" style="white-space: nowrap; min-width: 120px;">
                                    @if($checksheet->kashift_qc === 'REJECTED')
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                            <div>oleh {{ getRejectorName($checksheet->rejection_remarks) }}</div>
                                            @if($checksheet->kashift_approved_at)
                                                <div>{{ \Carbon\Carbon::parse($checksheet->kashift_approved_at)->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @elseif($checksheet->kashift_qc)
                                        <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                            <div>oleh {{ $checksheet->kashift_qc }}</div>
                                            @if($checksheet->kashift_approved_at)
                                                <div>{{ \Carbon\Carbon::parse($checksheet->kashift_approved_at)->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                </td>

                                {{-- Supervisor QC --}}
                                <td class="align-middle text-center" style="white-space: nowrap; min-width: 120px;">
                                    @if($checksheet->supervisor_qc === 'REJECTED')
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                            <div>oleh {{ getRejectorName($checksheet->rejection_remarks) }}</div>
                                            @if($checksheet->supervisor_approved_at)
                                                <div>{{ \Carbon\Carbon::parse($checksheet->supervisor_approved_at)->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @elseif($checksheet->supervisor_qc)
                                        <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                            <div>oleh {{ $checksheet->supervisor_qc }}</div>
                                            @if($checksheet->supervisor_approved_at)
                                                <div>{{ \Carbon\Carbon::parse($checksheet->supervisor_approved_at)->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                </td>

                                {{-- Asst Manager QC --}}
                                <td class="align-middle text-center" style="white-space: nowrap; min-width: 120px;">
                                    @if($checksheet->asst_manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                            <div>oleh {{ getRejectorName($checksheet->rejection_remarks) }}</div>
                                            @if($checksheet->asst_manager_approved_at)
                                                <div>{{ \Carbon\Carbon::parse($checksheet->asst_manager_approved_at)->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @elseif($checksheet->asst_manager_qc)
                                        <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                            <div>oleh {{ $checksheet->asst_manager_qc }}</div>
                                            @if($checksheet->asst_manager_approved_at)
                                                <div>{{ \Carbon\Carbon::parse($checksheet->asst_manager_approved_at)->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                </td>

                                {{-- Manager QC --}}
                                <td class="align-middle text-center" style="white-space: nowrap; min-width: 120px;">
                                    @if($checksheet->manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                            <div>oleh {{ getRejectorName($checksheet->rejection_remarks) }}</div>
                                            @if($checksheet->manager_approved_at)
                                                <div>{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @elseif($checksheet->manager_qc)
                                        <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                            <div>oleh {{ $checksheet->manager_qc }}</div>
                                            @if($checksheet->manager_approved_at)
                                                <div>{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
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

                                            $plantContext = strtolower(request('plant') ?? optional($user->plant)->code ?? 'karawang');
                                            $kashiftLabel = ($plantContext === 'jakarta') ? 'Kepala Regu' : 'Kashift QC';
                                            $kashiftAcronym = ($plantContext === 'jakarta') ? '' : ' KS';

                                            $showEdit = (request('view_mode') === 'verifikasi' || $canEdit);
                                            $showDel = (request('view_mode') === 'verifikasi' || $canDelete);
                                            $statusUrl = $isAdmin ? route('plating.edit_approval', array_merge(['id' => $checksheet->id], request()->all())) : null;
                                        @endphp

                                        @if(request('view_mode') !== 'verifikasi' && $loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif

                                        {{-- Non-Admin Roles: Show Inline Approve/Reject Button for User's Own Role --}}
                                        @if(request('view_mode') !== 'verifikasi' && !$isAdmin)
                                            @if(($user->role === 'kashift' || $isSpvJakarta || $isKaruJakarta) && $canApproveKashift)
                                                <form action="{{ route('plating.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline ajax-form">
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
                                                <form action="{{ route('plating.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline ajax-form">
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
                                                        <form action="{{ route('plating.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline w-100 ajax-form">
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
                                                        <form action="{{ route('plating.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline w-100 ajax-form">
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

                                                    @if($statusUrl)
                                                        <a href="{{ $statusUrl }}" class="dropdown-item no-loader btn-status-modal">
                                                            <i class="fas fa-user-check text-info fa-fw mr-2"></i> Status Approval
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                    @endif
                                                @endif

                                                @if($showEdit)
                                                    <a href="{{ route('plating.edit', array_merge(['id' => $checksheet->id], request()->all())) }}" class="dropdown-item no-loader btn-edit-modal">
                                                        <i class="fas fa-edit text-warning fa-fw mr-2"></i> Edit
                                                    </a>
                                                @endif

                                                @if($showDel)
                                                    @if($showEdit) <div class="dropdown-divider"></div> @endif
                                                    <form action="{{ route('plating.destroy', array_merge(['id' => $checksheet->id], request()->all())) }}" method="POST" class="d-inline w-100">
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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Checksheet Plating</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="editModalBody">
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
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="statusModalLabel">Edit Status Approval</h5>
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

    <!-- Modal Penolakan -->
    @foreach($checksheets as $cs)
        @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
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
                        <form action="{{ route('plating.reject', ['id' => $cs->id, 'type' => $rejectType]) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle"></i> Anda akan menolak checksheet ini sebagai
                                    <strong>{{ ucfirst(str_replace('_', ' ', $rejectType)) }}</strong>
                                </div>
                                <div class="form-group">
                                    <label for="rejection_remarks{{ $cs->id }}{{ $rejectType }}" class="font-weight-bold">
                                        Alasan Rejection <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="rejection_remarks{{ $cs->id }}{{ $rejectType }}"
                                        name="rejection_remarks" rows="4"
                                        placeholder="Masukkan alasan rejection (minimal 10 karakter)" required minlength="10"
                                        maxlength="500"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger btn-confirm-reject">Tolak Checksheet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    {{-- Modal Traceability QR Code --}}
    <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title font-weight-bold text-dark" id="qrModalLabel">
                        <i class="fas fa-route text-primary mr-2"></i>Traceability QR Code
                    </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <style>
                        .trace-h-item { position: relative; padding: 15px; border-radius: 8px; background: #fff; border: 1px solid #e3e6f0; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); height: 100%; display: flex; flex-direction: column; }
                        .trace-h-header { display: flex; align-items: center; margin-bottom: 10px; }
                        .trace-badge { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; background-color: #858796; color: white; margin-right: 8px; flex-shrink: 0; }
                        .trace-title { font-size: 0.85rem; font-weight: 700; margin: 0; line-height: 1.2; color: #5a5c69; }
                        .qr-code-box { background: #f8f9fc; border: 1px dashed #d1d3e2; padding: 5px; border-radius: 4px; margin-bottom: 12px; }
                        .qr-string { font-size: 0.65rem; color: #6e707e; word-break: break-all; display: block; text-align: center; }
                        .trace-info { font-size: 0.75rem; flex-grow: 1; display: flex; flex-direction: column; gap: 6px; }
                        .trace-row { display: flex; justify-content: space-between; border-bottom: 1px solid #f8f9fc; padding-bottom: 3px; }
                        .trace-row:last-child { border-bottom: none; }
                        .trace-label { color: #858796; font-weight: 600; width: 40%; }
                        .trace-val { color: #3a3b45; font-weight: 700; width: 60%; text-align: right; word-break: break-word; }
                        .arrow-connector { position: absolute; top: 40%; right: -15px; z-index: 10; color: #d1d3e2; font-size: 20px; transform: translateY(-50%); }
                    </style>
                    <div class="row position-relative">
                        {{-- STAGE 1 --}}
                        <div class="col-md-3 px-2 mb-3 mb-md-0 position-relative">
                            <div class="trace-h-item step-1">
                                <div class="trace-h-header">
                                    <div class="trace-badge">1</div>
                                    <h6 class="trace-title">WIP / Injection</h6>
                                </div>
                                <div class="qr-code-box">
                                    <code id="modal-trace-wip" class="qr-string">-</code>
                                </div>
                                <div id="trace-detail-wip" class="trace-info d-none">
                                    <div class="trace-row"><div class="trace-label">Part Code</div><div id="trace-wip-part" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">PO</div><div id="trace-wip-po" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Qty</div><div id="trace-wip-qty" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Lot/Unq</div><div id="trace-wip-lot" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">SAP</div><div id="trace-wip-sap" class="trace-val">-</div></div>
                                </div>
                            </div>
                            <div class="arrow-connector d-none d-md-block"><i class="fas fa-chevron-right"></i></div>
                        </div>

                        {{-- STAGE 2 --}}
                        <div class="col-md-3 px-2 mb-3 mb-md-0 position-relative">
                            <div class="trace-h-item step-2">
                                <div class="trace-h-header">
                                    <div class="trace-badge">2</div>
                                    <h6 class="trace-title">Plating Pasang</h6>
                                </div>
                                <div class="qr-code-box">
                                    <code id="modal-trace-pasang" class="qr-string">-</code>
                                </div>
                                <div id="trace-detail-pasang" class="trace-info d-none">
                                    <div class="trace-row" style="display:none;"><div class="trace-label">Part Code</div><div id="trace-pasang-part" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Lot ID</div><div id="trace-pasang-lot" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Unique</div><div id="trace-pasang-unique" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Tgl/Shf</div><div class="trace-val"><span id="trace-pasang-date">-</span> <small>(<span id="trace-pasang-ops">-</span>)</small></div></div>
                                    <div class="trace-row"><div class="trace-label">Qty</div><div id="trace-pasang-qty" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">JIG</div><div id="trace-pasang-jig" class="trace-val">-</div></div>
                                </div>
                            </div>
                            <div class="arrow-connector d-none d-md-block"><i class="fas fa-chevron-right"></i></div>
                        </div>

                        {{-- STAGE 3 --}}
                        <div class="col-md-3 px-2 mb-3 mb-md-0 position-relative">
                            <div class="trace-h-item step-3">
                                <div class="trace-h-header">
                                    <div class="trace-badge">3</div>
                                    <h6 class="trace-title">Plating Cabut</h6>
                                </div>
                                <div class="qr-code-box">
                                    <code id="modal-trace-cabut" class="qr-string">-</code>
                                </div>
                                <div id="trace-detail-cabut" class="trace-info d-none">
                                    <div class="trace-row" style="display:none;"><div class="trace-label">Part</div><div id="trace-cabut-part" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">PO</div><div id="trace-cabut-po" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Tgl/Shf</div><div class="trace-val"><span id="trace-cabut-date">-</span> <small>(<span id="trace-cabut-ops">-</span>)</small></div></div>
                                    <div class="trace-row"><div class="trace-label">Bucket</div><div id="trace-cabut-bucket" class="trace-val text-success">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Qty Orig</div><div id="trace-cabut-qty-orig" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Qty Split</div><div id="trace-cabut-qty-split" class="trace-val text-success">-</div></div>
                                </div>
                            </div>
                            <div class="arrow-connector d-none d-md-block"><i class="fas fa-chevron-right"></i></div>
                        </div>

                        {{-- STAGE 4 --}}
                        <div class="col-md-3 px-2 mb-0">
                            <div class="trace-h-item step-4">
                                <div class="trace-h-header">
                                    <div class="trace-badge">4</div>
                                    <h6 class="trace-title text-dark">QC Verifikasi</h6>
                                </div>
                                <div class="qr-code-box">
                                    <code id="modal-trace-qc" class="qr-string">-</code>
                                </div>
                                <div id="trace-detail-qc" class="trace-info d-none">
                                    <div class="trace-row"><div class="trace-label">Part</div><div id="trace-qc-part" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">PO</div><div id="trace-qc-po" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Qty</div><div id="trace-qc-qty" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">Lot/Unq</div><div id="trace-qc-lot" class="trace-val">-</div></div>
                                    <div class="trace-row"><div class="trace-label">SAP</div><div id="trace-qc-sap" class="trace-val">-</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/checksheet/plating.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initPlatingIndex({
                indexRoute: "{{ route('plating.index') }}",
                qrScannerModalId: '#qrScannerModal',
                btnScanId: '#btnScanQRIndex',
                inputQrId: '#filterQrRaw'
            });

            // Initialize Custom Search (Standardized across modules)
            if (typeof initItemSearch === 'function') {
                initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
                initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
                initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
                initItemSearch('filterMethod', { placeholder: 'Ketik Tipe...', maxResults: 5 });
            }

            var form = document.getElementById('filterFormPlating');
            if (form) {
                // Link Synchronization (Sync Print/Export links with current filter selections)
                function syncExportLinks() {
                    var baseUrlPrint = "{{ route('plating.print') }}";
                    var baseUrlPdf = "{{ route('plating.export_pdf') }}";
                    var baseUrlRecap = "{{ route('plating.daily_recap') }}";
                    
                    var params = new URLSearchParams();
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        if (pair[1]) params.append(pair[0], pair[1]);
                    }
                    
                    var queryString = params.toString();
                    
                    var printBtn = form.querySelector('a[title="Print Preview"]');
                    var pdfBtn = form.querySelector('a[title="Export to PDF"]');
                    var recapBtn = document.getElementById('btnDailyRecap');
                    
                    if (printBtn) printBtn.href = baseUrlPrint + '?' + queryString;
                    if (pdfBtn) pdfBtn.href = baseUrlPdf + '?' + queryString;
                    if (recapBtn) {
                        var startDate = form.querySelector('#start_date').value || new Date().toISOString().slice(0,10);
                        var plant = form.querySelector('input[name="plant"]')?.value || 'karawang';
                        recapBtn.href = baseUrlRecap + '?start_date=' + startDate + '&plant=' + plant;
                    }
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

            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.trigger('submit');
                    }
                });
            });
        });
    </script>
    @include('partials.qr_scanner_modal')

    @php $bulkApproveRoute = route('plating.bulk_approve'); @endphp
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
                                url: '{{ route("plating.bulk_destroy") }}',
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
    </script>
@endpush
