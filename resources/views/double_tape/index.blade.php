@extends('layouts.admin')

@section('title', 'Double Tape')

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

    #checksheetTable .btn-qr-detail,
    #sortirTable .btn-qr-detail {
        border-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        transition: transform 0.1s;
    }
    #checksheetTable .btn-qr-detail:hover,
    #sortirTable .btn-qr-detail:hover {
        transform: scale(1.05);
    }
</style>
    @php
        // Resolve menu ID for permission checks
        $currentMenu = \App\Models\AppMenu::where('route', 'double_tape.index')->first();
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

        $docHeader = \App\Models\GeneralSetting::getDocHeader('double_tape', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0206',
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
                                LAPORAN DATA DOUBLE TAPE TEST
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
            <form action="{{ route('double_tape.index') }}" method="GET"
                class="d-flex flex-wrap align-items-end bg-light p-2 rounded mb-2 shadow-sm"
                style="gap: 8px; overflow-x: auto;" id="filterFormDoubleTape">

                <input type="hidden" name="plant" value="{{ request('plant') }}">
                @if(request('view_mode'))
                    <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
                    <input type="hidden" name="entry_method" value="verification">
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

                @if(request('view_mode') === 'verifikasi')
                <!-- 6. Field: QR Code (Tampilkan HANYA untuk Mode Verifikasi disamping Inisial) -->
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
                @else
                <!-- 6. Field: Tipe (Sampling / Fullcheck) - Sembunyikan di Mode Verifikasi -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Tipe</label>
                    <div class="d-flex align-items-center bg-white px-2 py-1 shadow-sm rounded" style="border: 1px solid #e2e8f0; height: 26px; gap: 6px;">
                        <div class="form-check form-check-inline mb-0 mr-0">
                            <input class="form-check-input" type="checkbox" name="check_type[]" id="filterSampling" value="sampling"
                                {{ in_array('sampling', (array) request('check_type', [])) ? 'checked' : '' }}>
                            <label class="form-check-label small font-weight-bold" for="filterSampling" style="color: #4e73df; font-size: 0.68rem;">Smpl</label>
                        </div>
                        <div class="form-check form-check-inline mb-0 mr-0">
                            <input class="form-check-input" type="checkbox" name="check_type[]" id="filterFullcheck" value="fullcheck"
                                {{ in_array('fullcheck', (array) request('check_type', [])) ? 'checked' : '' }}>
                            <label class="form-check-label small font-weight-bold" for="filterFullcheck" style="color: #1cc88a; font-size: 0.68rem;">Full</label>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Tombol Filter & Reset (Tepat di Samping Field dengan 10px Space) -->
                <div class="d-flex align-items-center" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important; margin-left: 10px;">
                    <style>
                        .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                        .custom-filter-wrapper .ips-input { padding: 2px 18px 2px 6px !important; font-size: 0.68rem !important; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: 26px !important; }
                        .custom-filter-wrapper .ips-clear { right: 5px; font-size: 10px; }
                        .custom-filter-wrapper { position: relative; top: 0px; }
                        #filterFormDoubleTape label { white-space: nowrap; }
                    </style>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Cari Data">
                        <i class="fas fa-search fa-sm mr-1"></i> Filter
                    </button>
                    <a href="{{ route('double_tape.index', array_merge(['plant' => request('plant')], request('view_mode') ? ['view_mode' => request('view_mode')] : [])) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Reset Filter">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </a>
                </div>

                <!-- Tombol Navigasi & Ekspor (Paling Kanan) -->
                <div class="d-flex align-items-center ml-auto" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important;">
                    @if(request('view_mode') !== 'verifikasi')
                        <a href="{{ route('double_tape.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'verification', 'plant' => request('plant')])) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Data Hasil Verifikasi"
                            style="background-color: #6f42c1; color: white; font-size: 0.68rem; height: 26px;">
                            <i class="fas fa-clipboard-check fa-sm mr-1"></i> Hasil Verifikasi
                        </a>
                    @else
                        <a href="{{ route('double_tape.index', ['plant' => request('plant')]) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Kembali ke Data Regular"
                            style="background-color: #6c757d; color: white; font-size: 0.68rem; height: 26px;">
                            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
                        </a>
                    @endif

                    <a href="{{ route('double_tape.daily_recap', ['start_date' => request('start_date') ?: now()->toDateString(), 'plant' => request('plant')]) }}"
                        id="btnDailyRecap"
                        class="btn btn-dark btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" title="Rekap Harian Verification"
                        style="font-size: 0.68rem; height: 26px;" target="_blank">
                        <i class="fas fa-list-alt fa-sm mr-1"></i> Rekap Harian
                    </a>

                    @if($canExport)
                    <a href="{{ route('double_tape.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-download d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm mr-1"></i> PDF
                    </a>
                    <a href="{{ route('double_tape.print', request()->query()) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-print-direct d-flex align-items-center" title="Print"
                        style="background-color: #17a589; color: white; font-size: 0.68rem; height: 26px;">
                        <i class="fas fa-print fa-sm mr-1"></i> Cetak
                    </a>
                    @endif
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-hover" width="100%" cellspacing="0" id="checksheetTable">
                    <thead>
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
                                <th rowspan="2" class="bg-light align-middle">Checked<br>(Tgl / Shift / Inisial)</th>
                                <th rowspan="2" class="align-middle">Jam (Before)</th>
                                <th rowspan="2" class="align-middle">Jam (After)</th>
                                <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            @else
                                <th rowspan="2" class="bg-light align-middle">Lot ID<br>(Tgl / Shift / Inisial)</th>
                                <th rowspan="2" class="bg-light align-middle">Checked<br>(Tgl / Shift / Inisial)</th>
                                <th rowspan="2" class="align-middle">Jam (Before)</th>
                                <th rowspan="2" class="align-middle">Jam (After)</th>
                                <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            @endif
                            <th rowspan="2" class="align-middle d-none">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part / Part No</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Total Qty</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="2" class="align-middle">Detail NG</th>
                            @endif
                            <th rowspan="2" class="align-middle">Judgment</th>

                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="2" class="align-middle">Approval Status</th>
                            @endif
                            <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            @if(request('view_mode') === 'verifikasi' ? auth()->user()->role !== 'inspector' : !in_array(auth()->user()->role, ['inspector']))
                                <th rowspan="2" class="no-export align-middle">Actions</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            @if(request('view_mode') !== 'verifikasi')
                                <th style="width: 60px; min-width: 60px;">Pcs</th>
                                <th style="min-width: 150px;">Jenis NG</th>
                                <th style="font-size: 10px; min-width: 120px;">Kashift QC</th>
                                <th style="font-size: 10px; min-width: 120px;">Supervisor QC</th>
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
                                        <button type="button" class="btn btn-sm btn-primary btn-qr-detail" 
                                            data-qr="{{ $checksheet->qrcode }}"
                                            data-part="{{ $checksheet->part_code ?? '-' }}"
                                            data-supplier="{{ $checksheet->supplier_id ?? '-' }}"
                                            data-qty="{{ $checksheet->quantity ?? '-' }}"
                                            data-unique="{{ $checksheet->unique_code_id ?? '-' }}"
                                            data-sap="{{ $checksheet->sap_code ?? '-' }}">
                                            <i class="fas fa-qrcode"></i> View
                                        </button>
                                    </td>
                                    <td class="align-middle text-nowrap">
                                        {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }} / {{ $checksheet->shift }} / {{ $checksheet->operator_initials ?? '-' }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                    </td>
                                    <td class="align-middle">{{ $checksheet->created_at->format('H:i') }}</td>
                                    <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                @else
                                    <td class="align-middle text-nowrap">
                                        {{ $checksheet->injection_date ? $checksheet->injection_date->format('d-m-Y') : '-' }} / {{ $checksheet->injection_shift ?? '-' }} / {{ $checksheet->injection_initials ?? '-' }}
                                    </td>
                                    <td class="align-middle text-nowrap">
                                        {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }} / {{ $checksheet->shift }} / {{ $checksheet->operator_initials ?? '-' }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                    </td>
                                    <td class="align-middle">{{ $checksheet->created_at->format('H:i') }}</td>
                                    <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                @endif
                                <td class="align-middle text-nowrap d-none">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-left text-nowrap">
                                    <span class="font-weight-bold text-gray-800">{{ $checksheet->item->name ?? '-' }}</span><br>
                                    <small class="text-muted"><i class="fas fa-tag mr-1"></i>{{ $checksheet->item->part_number ?? '-' }}</small>
                                </td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->total_qty }}</td>
                                <td class="align-middle text-success font-weight-bold">{{ max(0, $checksheet->total_qty - $checksheet->total_ng) }}</td>
                                <td class="align-middle text-danger font-weight-bold">{{ $checksheet->total_ng }}</td>

                                @if(request('view_mode') !== 'verifikasi')
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
                                @endif

                                <td class="align-middle font-weight-bold" style="white-space: nowrap;">
                                    <span class="text-success">
                                        @if(request('view_mode') === 'verifikasi')
                                            OK
                                        @else
                                            {!! $checksheet->check_type === 'fullcheck' ? 'OK<br><span style="white-space: nowrap;">Full Check</span>' : 'OK<br>Sampling' !!}
                                        @endif
                                    </span>
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

                                            $canApproveKashift = ($user->role === 'kashift' || $isAdmin) && (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED');
                                            $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) && (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED');

                                            $showEdit = (request('view_mode') === 'verifikasi' || $canEdit);
                                            $showDel = (request('view_mode') === 'verifikasi' || $canDelete);
                                            $statusUrl = $isAdmin ? route('double_tape.edit_approval', ['id' => $checksheet->id]) : null;
                                        @endphp

                                        @if(request('view_mode') !== 'verifikasi' && $loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif

                                        {{-- Non-Admin Roles: Show Inline Approve/Reject Button for User's Own Role --}}
                                        @if(request('view_mode') !== 'verifikasi' && !$isAdmin)
                                            @if($user->role === 'kashift' && $canApproveKashift)
                                                <form action="{{ route('double_tape.approve', ['id' => $checksheet->id, 'type' => 'kashift']) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                    <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kashift)">
                                                        <i class="fas fa-check"></i> Approve KS
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kashift)" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @elseif($user->role === 'supervisor' && $canApproveSupervisor)
                                                <form action="{{ route('double_tape.approve', ['id' => $checksheet->id, 'type' => 'supervisor']) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
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
                                                        <form action="{{ route('double_tape.approve', ['id' => $checksheet->id, 'type' => 'kashift']) }}" method="POST" class="d-inline w-100">
                                                            @csrf
                                                            <input type="hidden" name="page" value="{{ request('page') }}">
                                                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                            <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                            <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                            <button type="submit" class="dropdown-item text-success font-weight-bold">
                                                                <i class="fas fa-check-circle text-success fa-fw mr-2"></i> Approve Kashift QC
                                                            </button>
                                                        </form>
                                                        <button type="button" class="dropdown-item text-danger font-weight-bold" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift">
                                                            <i class="fas fa-times-circle text-danger fa-fw mr-2"></i> Reject Kashift QC
                                                        </button>
                                                        <div class="dropdown-divider"></div>
                                                    @endif

                                                    {{-- Approve Supervisor (Admin Only in Dropdown) --}}
                                                    @if($canApproveSupervisor)
                                                        <form action="{{ route('double_tape.approve', ['id' => $checksheet->id, 'type' => 'supervisor']) }}" method="POST" class="d-inline w-100">
                                                            @csrf
                                                            <input type="hidden" name="page" value="{{ request('page') }}">
                                                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                            <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                            <input type="hidden" name="shift" value="{{ request('shift') }}">
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
                                                    <a href="{{ route('double_tape.edit', $checksheet->id) }}" class="dropdown-item no-loader btn-edit-modal">
                                                        <i class="fas fa-edit text-warning fa-fw mr-2"></i> Edit
                                                    </a>
                                                @endif

                                                @if($showDel)
                                                    @if($showEdit) <div class="dropdown-divider"></div> @endif
                                                    <form action="{{ route('double_tape.destroy', $checksheet->id) }}" method="POST" class="d-inline w-100">
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

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 0;">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-primary" id="editModalLabel" style="font-size: 1.1rem;">
                        <i class="fas fa-edit mr-2"></i>Edit Checksheet Double Tape
                    </h5>
                    <button type="button" class="close text-gray-500 hover:text-gray-800" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light px-4 py-4" id="editModalBody" style="max-height: 65vh; overflow-y: auto;">
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

    <!-- Modal Rejection untuk setiap checksheet dan tipe -->
    @foreach($checksheets as $cs)
        @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
            @php
                $user = auth()->user();
                $isAdmin = $user->role === 'admin';
                $canReject = false;
                if ($rejectType == 'kashift' && (($user->role === 'kashift' || $isAdmin) && (!$cs->kashift_qc || $cs->kashift_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor' && (($user->role === 'supervisor' || $isAdmin) && (!$cs->supervisor_qc || $cs->supervisor_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'asst_manager' && (($user->role === 'asst_manager' || $isAdmin) && (!$cs->asst_manager_qc || $cs->asst_manager_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'manager' && ((auth()->user()->role === 'manager' || $isAdmin) && (!$cs->manager_qc || $cs->manager_qc === 'REJECTED'))) {
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
                            <form action="{{ route('double_tape.reject', ['id' => $cs->id, 'type' => $rejectType]) }}" method="POST">
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

    {{-- Modal Traceability QR Code --}}
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

@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/checksheet/double-tape.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.initDoubleTapeIndex === 'function') {
                window.initDoubleTapeIndex({
                    indexRoute: "{{ route('double_tape.index') }}",
                    qrScannerModalId: '#qrScannerModal',
                    btnScanId: '#btnScanQRIndex',
                    inputQrId: '#filterQrRaw'
                });
            }

            // Initialize Custom Search (Standardized across modules)
            if (typeof initItemSearch === 'function') {
                initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
                initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
                initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
            }

            var filterForm = document.getElementById('filterFormDoubleTape');
            if (filterForm) {
                // Link Synchronization (Sync Print/Export links with current filter selections)
                function syncExportLinks() {
                    var baseUrlPrint = "{{ route('double_tape.print') }}";
                    var baseUrlPdf = "{{ route('double_tape.export_pdf') }}";
                    var baseUrlRecap = "{{ route('double_tape.daily_recap') }}";
                    
                    var params = new URLSearchParams();
                    var formData = new FormData(filterForm);
                    for (var pair of formData.entries()) {
                        if (pair[0] === 'check_type[]') {
                            params.append(pair[0], pair[1]);
                        } else if (pair[1]) {
                            params.append(pair[0], pair[1]);
                        }
                    }
                    
                    var queryString = params.toString();
                    
                    var printBtn = filterForm.querySelector('a[title="Print"]');
                    var pdfBtn = filterForm.querySelector('a[title="Export to PDF"]');
                    var recapBtn = document.getElementById('btnDailyRecap');
                    
                    if (printBtn) printBtn.href = baseUrlPrint + '?' + queryString;
                    if (pdfBtn) pdfBtn.href = baseUrlPdf + '?' + queryString;
                    if (recapBtn) {
                        var startDate = filterForm.querySelector('#start_date').value || new Date().toISOString().slice(0,10);
                        var endDate = filterForm.querySelector('#end_date').value || startDate;
                        var plant = filterForm.querySelector('input[name="plant"]')?.value || 'karawang';
                        var shift = filterForm.querySelector('select[name="shift"]')?.value || '';
                        var operatorInitials = filterForm.querySelector('select[name="operator_initials"]')?.value || '';
                        
                        recapBtn.href = baseUrlRecap + '?start_date=' + startDate + '&end_date=' + endDate + '&plant=' + plant + '&shift=' + shift + '&operator_initials=' + operatorInitials;
                    }
                }

                $(filterForm).find('input, select').on('change', syncExportLinks);
                syncExportLinks();

                // Date validation on submit
                $(filterForm).on('submit', function(e) {
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

            // Restore scroll position
            var savedScroll = sessionStorage.getItem('doubleTapeScrollPos');
            if (savedScroll) {
                $('.table-responsive').scrollTop(savedScroll);
                sessionStorage.removeItem('doubleTapeScrollPos');
            }

            // Save scroll position before leaving or reloading
            $(window).on('beforeunload', function() {
                sessionStorage.setItem('doubleTapeScrollPos', $('.table-responsive').scrollTop());
            });
        });
    </script>
    @include('partials.qr_scanner_modal')

    @php $bulkApproveRoute = route('double_tape.bulk_approve'); @endphp
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
                                url: '{{ route("double_tape.bulk_destroy") }}',
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
