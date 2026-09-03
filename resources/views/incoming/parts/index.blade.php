@extends('layouts.admin')

@section('title', 'Laporan Data Incoming Part')

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
    
    /* Target tableOpenArrivals headers to override global admin blue thead th style */
    #tableOpenArrivals,
    #tableOpenArrivals > thead > tr > th,
    #tableOpenArrivals > thead > tr > td,
    #tableOpenArrivals th,
    #tableOpenArrivals tr:first-child th,
    #modalAddArrival table thead th,
    #modalAddArrival table tr:first-child th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.65rem !important;
        letter-spacing: 0.3px !important;
        border-bottom: 2px solid #cbd5e1 !important;
        border-right: 1px solid #e2e8f0 !important;
        border-left: none !important;
        border-top: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
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
        padding: 6px 8px !important;
        white-space: nowrap !important;
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
        padding: 6px 10px !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #e2e8f0;
    }

    #checksheetTable .btn {
        min-width: 0 !important;
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
    #checksheetTable .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    /* Natural sticky positions without artificial height clipping */
    #checksheetTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
    }
    #checksheetTable > thead > tr:nth-child(2) > th {
        top: 26px !important; 
        z-index: 104 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        top: 0 !important;
        z-index: 106 !important;
    }

    /* Minimalist Filter Style Override */
    .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
    .custom-filter-wrapper .ips-input { padding: 2px 18px 2px 6px !important; font-size: 0.68rem !important; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: 26px !important; }
    .custom-filter-wrapper .ips-clear { right: 5px; font-size: 10px; }
    .custom-filter-wrapper { position: relative; top: 0px; }
</style>

    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        // Resolve menu IDs for permission checks (support for duplicate plant routes)
        $menuIds = \App\Models\AppMenu::where('route', 'incoming.parts.index')->pluck('id');
        $canExport = true; $canEdit = true; $canDelete = true;
        if ($menuIds->isNotEmpty()) {
            $canExport = false; $canEdit = false; $canDelete = false;
            foreach ($menuIds as $mId) {
                if (auth()->user()->hasPermission($mId, 'export')) $canExport = true;
                if (auth()->user()->hasPermission($mId, 'edit')) $canEdit = true;
                if (auth()->user()->hasPermission($mId, 'delete')) $canDelete = true;
            }
        }

        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_parts', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0210',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '0',
            'halaman' => '- / -'
        ]);
    @endphp

    <!-- Hidden Logo for PDF Export -->
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
                                LAPORAN DATA INCOMING PART
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
            <!-- Filter Bar Terpadu (Action Bar Selaras Standard) -->
            <form action="{{ route('incoming.parts.index') }}" method="GET"
                class="d-flex flex-wrap align-items-end bg-light p-2 rounded mb-2 shadow-sm"
                style="gap: 8px; overflow-x: auto;" id="filterFormIncomingPart">
                
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                @if(request()->has('view_mode'))
                    <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
                @endif
                @if(request()->has('entry_method'))
                    <input type="hidden" name="entry_method" value="{{ request('entry_method') }}">
                @endif
                
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
                    <div style="width: 110px;" class="custom-filter-wrapper">
                        <select name="customer" id="filterCustomer" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Customer</option>
                            @foreach($customers ?? [] as $customer)
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
                    <div style="width: 90px;" class="custom-filter-wrapper">
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
                    <div style="width: 110px;" class="custom-filter-wrapper">
                        <select name="operator_initials" id="filterInisial" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Inisial</option>
                            @foreach($initials ?? [] as $initial)
                                <option value="{{ $initial }}" {{ request('operator_initials') == $initial ? 'selected' : '' }}>{{ $initial }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 6. Field: QR Raw (Khusus Data Hasil Verifikasi) -->
                @if(request('view_mode') === 'verifikasi')
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">QR Code</label>
                    <div class="input-group input-group-sm shadow-sm rounded" style="width: 180px;">
                        <input type="text" name="qr_raw" id="filterQrRaw" class="form-control border-0"
                            placeholder="Scan/Ketik QR..." value="{{ request('qr_raw') }}" style="font-size: 0.70rem; height: 26px;">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary border-0 py-0 px-2" id="btnScanQRIndex" title="Scan QR Code" style="height: 26px; touch-action: manipulation;">
                                <i class="fas fa-qrcode" style="pointer-events: none; font-size: 0.70rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Tombol Filter & Reset -->
                <div class="d-flex align-items-center" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important; margin-left: 20px;">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-2 py-1 d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Cari Data">
                        <i class="fas fa-search fa-sm mr-1"></i> Filter
                    </button>
                    <a href="{{ route('incoming.parts.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Reset Filter">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </a>
                </div>

                <!-- Tombol Navigasi & Ekspor (Paling Kanan) -->
                <div class="d-flex align-items-center ml-auto" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important;">
                    @if(request('view_mode') !== 'verifikasi')
                        <a href="{{ route('incoming.parts.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'verification', 'plant' => request('plant')])) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader font-weight-bold d-flex align-items-center" title="Data Hasil Verifikasi"
                            style="background-color: #6f42c1; color: white; font-size: 0.68rem; height: 26px;">
                            <i class="fas fa-qrcode fa-sm mr-1"></i> Hasil Verifikasi
                        </a>
                    @else
                        <a href="{{ route('incoming.parts.index', ['plant' => request('plant')]) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader font-weight-bold d-flex align-items-center" title="Kembali ke Data Regular"
                            style="background-color: #6c757d; color: white; font-size: 0.68rem; height: 26px;">
                            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
                        </a>
                    @endif
                    @if($canExport)
                    <a href="{{ route('incoming.parts.print', request()->query()) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-print-direct d-flex align-items-center" title="Cetak Direct"
                        style="background-color: #17a589; color: white; font-size: 0.68rem; height: 26px;">
                        <i class="fas fa-print fa-sm mr-1"></i> Cetak
                    </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover text-center" width="100%" cellspacing="0" id="checksheetTable">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle text-center" style="width: 55px;">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <span style="font-size: 9px; font-weight: bold; margin-bottom: 3px; text-transform: uppercase; line-height: 1.1;">SEMUA<br>(<span id="checkedCountDisplay">0</span>)</span>
                                    <div class="custom-control custom-checkbox d-inline-block" style="min-height: 1.2rem; padding-left: 1.2rem; margin: 0 auto;">
                                        <input type="checkbox" class="custom-control-input" id="checkAllRows">
                                        <label class="custom-control-label" for="checkAllRows" style="cursor:pointer;"></label>
                                    </div>
                                </div>
                            </th>
                            <th rowspan="2" class="align-middle">No</th>
                            @if(request('view_mode') === 'verifikasi')
                                <th rowspan="2" class="align-middle">QR-Code</th>
                            @endif
                            <th rowspan="2" class="align-middle">Checked<br>(Tgl / Shift / Inisial)</th>
                            <th rowspan="2" class="align-middle text-nowrap">Waktu Check<br>(Start - Finish / CT)</th>
                            <th rowspan="2" class="align-middle">Item Part / Part No</th>
                            <th rowspan="2" class="align-middle">Customer / Supplier</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th rowspan="2" class="align-middle">Tgl &amp; Shift Datang</th>
                                <th rowspan="2" class="align-middle">Qty Datang Awal</th>
                            @endif
                            <th rowspan="2" class="align-middle text-nowrap">Qty<br>(Total / Sampling)</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th rowspan="2" class="align-middle">Qty Balance Sisa</th>
                            @endif
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="2" class="align-middle">Detail NG</th>
                            @endif
                            <th rowspan="2" class="align-middle">Judgment</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="4" class="align-middle">Approval Status</th>
                            @endif
                            <th rowspan="2" class="align-middle">Remarks</th>
                            <th rowspan="2" class="align-middle">Action</th>
                        </tr>
                        <tr class="text-center">
                            @if(request('view_mode') !== 'verifikasi')
                                <th style="width: 45px; min-width: 45px;">Pcs</th>
                                <th style="min-width: 70px;" class="text-nowrap">Jenis NG</th>
                                <th style="font-size: 10px; min-width: 120px;">{{ $plantCode === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                                <th style="font-size: 10px; min-width: 120px;">Supervisor QC</th>
                                <th style="font-size: 10px; min-width: 120px;">Asst Manager QC</th>
                                <th style="font-size: 10px; min-width: 120px;">Manager QC</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checksheets as $cs)
                            @php
                                $user = auth()->user();
                                $isAdmin = $user->role === 'admin';
                                $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta' || strtolower(request('plant') ?? '') === 'jakarta';
                                $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                $canApproveKashift = (in_array($user->role, ['kashift', 'kashift_qc']) || $isAdmin || $isSpvJakarta || $isKaruJakarta) 
                                    && (empty($cs->kashift_qc) || $cs->kashift_qc === 'REJECTED');

                                $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) 
                                    && (empty($cs->supervisor_qc) || $cs->supervisor_qc === 'REJECTED')
                                    && (!empty($cs->kashift_qc) && $cs->kashift_qc !== 'REJECTED');

                                $canApproveAsstManager = (in_array($user->role, ['asst_manager', 'asst_manager_qc']) || $isAdmin) 
                                    && (empty($cs->asst_manager_qc) || $cs->asst_manager_qc === 'REJECTED')
                                    && (!empty($cs->supervisor_qc) && $cs->supervisor_qc !== 'REJECTED');

                                $canApproveManager = (in_array($user->role, ['manager', 'manager_qc']) || $isAdmin) 
                                    && (empty($cs->manager_qc) || $cs->manager_qc === 'REJECTED')
                                    && (!empty($cs->asst_manager_qc) && $cs->asst_manager_qc !== 'REJECTED');
                            @endphp
                            <tr class="text-center">
                                @if(auth()->user()->role === 'admin')
                                    <td class="align-middle text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input row-checkbox" id="check_{{ $cs->id }}" value="{{ $cs->id }}">
                                            <label class="custom-control-label" for="check_{{ $cs->id }}" style="cursor:pointer;"></label>
                                        </div>
                                    </td>
                                @endif
                                <td class="align-middle text-nowrap">{{ $checksheets->firstItem() + $loop->index }}</td>
                                @if(request('view_mode') === 'verifikasi')
                                    <td class="align-middle text-center text-nowrap">
                                        @if(!empty($cs->qrcode) || !empty($cs->unique_code_id) || in_array($cs->scan_method, ['hardware', 'camera']))
                                            <button type="button" class="btn btn-outline-primary btn-xs btn-qr-detail" 
                                                data-qr="{{ $cs->qrcode }}"
                                                data-part="{{ $cs->part_code }}"
                                                data-supplier="{{ $cs->supplier_id }}"
                                                data-qty="{{ $cs->quantity }}"
                                                data-unique="{{ $cs->unique_code_id }}"
                                                data-sap="{{ $cs->sap_code ?? '-' }}"
                                                style="padding: 0.2rem 0.5rem; font-size: 0.80rem;" title="Lihat Detail QR Code">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="align-middle text-nowrap font-weight-bold" style="font-size: 0.70rem;">
                                    {{ date('d-m-Y', strtotime($cs->date)) }} / {{ $cs->shift }} / {{ $cs->operator_initials ?? '-' }}
                                </td>
                                @php
                                    $sec = (int) ($cs->cycle_time ?? 0);
                                    $ctStr = ($sec > 0) ? (($sec < 60) ? ($sec . 's') : (floor($sec / 60) . 'm' . (($sec % 60 > 0) ? ' ' . ($sec % 60) . 's' : ''))) : '-';
                                @endphp
                                <td class="align-middle text-nowrap">
                                    {{ $cs->created_at ? $cs->created_at->copy()->subSeconds($sec)->format('H:i') : '-' }} - {{ $cs->created_at ? $cs->created_at->format('H:i') : '-' }} <span class="text-muted">({{ $ctStr }})</span>
                                </td>
                                <td class="align-middle text-left text-nowrap">
                                    <span class="font-weight-bold text-gray-800">{{ $cs->item->name ?? '-' }}</span><br>
                                    <small class="text-muted">{{ $cs->item->part_number ?? '-' }}</small>
                                </td>
                                
                                {{-- Kolom Customer / Supplier --}}
                                <td class="align-middle text-nowrap text-gray-700">{{ $cs->item->customer ?? '-' }}</td>

                                @if(request('view_mode') !== 'verifikasi')
                                    {{-- Tgl & Shift Datang --}}
                                    <td class="align-middle text-nowrap">
                                        @if($cs->tanggal_datang)
                                            {{ date('d-m-Y', strtotime($cs->tanggal_datang)) }}
                                            @php
                                                $shiftDatangShow = $cs->arrival ? $cs->arrival->shift_datang : null;
                                            @endphp
                                            @if($shiftDatangShow)
                                                / {{ $shiftDatangShow }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- Qty Kedatangan Awal --}}
                                    <td class="align-middle text-nowrap">
                                        {{ number_format($cs->arrival ? $cs->arrival->qty_datang : ($cs->lot_qty ?? 0)) }} Pcs
                                    </td>
                                @endif

                                {{-- Qty Total / Sampling --}}
                                <td class="align-middle text-nowrap" style="font-size: 0.75rem;">
                                    <span class="font-weight-bold text-dark">{{ number_format($cs->total_check) }}</span>
                                    <span class="text-muted font-weight-normal">/ {{ number_format($cs->sampling_qty ?? $cs->total_check) }} Pcs</span>
                                </td>

                                @if(request('view_mode') !== 'verifikasi')
                                    {{-- Qty Balance Sisa --}}
                                    <td class="align-middle text-nowrap">
                                        @php
                                            if ($cs->arrival) {
                                                if ($cs->arrival->status === 'COMPLETED' || $cs->arrival->qty_sisa <= 0) {
                                                    $sisaDisplay = 0;
                                                    $statusDisplay = 'COMPLETED';
                                                } else {
                                                    $sisaDisplay = $cs->arrival->qty_sisa;
                                                    $statusDisplay = 'OPEN';
                                                }
                                            } else {
                                                $sisaDisplay = isset($cs->qty_balance_sisa) ? $cs->qty_balance_sisa : 0;
                                                $statusDisplay = ($sisaDisplay <= 0) ? 'COMPLETED' : 'OPEN';
                                            }
                                        @endphp
                                        <span>{{ number_format($sisaDisplay) }} Pcs</span>
                                        <br>
                                        <small class="text-muted">({{ $statusDisplay }})</small>
                                    </td>
                                @endif

                                {{-- OK & NG --}}
                                <td class="align-middle text-nowrap text-success font-weight-bold">{{ number_format(max(0, $cs->total_check - $cs->total_ng)) }}</td>
                                <td class="align-middle text-nowrap text-danger font-weight-bold">{{ number_format($cs->total_ng) }}</td>
                                
                                @php
                                    $defectsData = is_array($cs->defects) ? $cs->defects : json_decode($cs->defects, true);
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

                                <td class="align-middle text-center" style="width: 45px; min-width: 45px; padding: 2px 4px !important;">
                                    @if(count($pcsLines) > 0)
                                        <span class="text-danger font-weight-bold" style="font-size: 0.68rem; line-height: 1.1; display: block;">{!! implode('<br>', $pcsLines) !!}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle text-center text-nowrap" style="min-width: 70px; padding: 2px 4px !important;">
                                    @if(count($nameLines) > 0)
                                        <span class="text-danger font-weight-bold" style="font-size: 0.68rem; line-height: 1.1; display: block;">{!! implode('<br>', $nameLines) !!}</span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="align-middle text-nowrap">
                                    <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }} px-2 py-1" style="font-size: 0.65rem;">
                                        {{ $cs->judgment }}
                                    </span>
                                </td>
                                
                                @if(request('view_mode') !== 'verifikasi')
                                    {{-- Unified Approval Columns (4 Roles) --}}
                                    @foreach ($approvalOrder as $role)
                                        @php
                                            $field = getApprovalField($role);
                                            $dateField = getApprovalDateField($role);
                                            $status = $cs->$field;
                                            $date = $cs->$dateField;
                                        @endphp
                                        <td class="align-middle text-center" style="white-space: nowrap; min-width: 120px;">
                                            @if($status === 'REJECTED')
                                                <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;" data-toggle="tooltip" title="{{ $cs->rejection_remarks }}">
                                                    <i class="fas fa-times-circle mr-1"></i> REJECTED
                                                </span>
                                                <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                                    <div>oleh {{ getRejectorName($cs->rejection_remarks) }}</div>
                                                    @if($date)
                                                        <div>{{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i') }}</div>
                                                    @endif
                                                </div>
                                            @elseif($status && $status !== 'Pending')
                                                <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">
                                                    <i class="fas fa-check-circle mr-1"></i> APPROVED
                                                </span>
                                                <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                                    <div>oleh {{ $status }}</div>
                                                    @if($date)
                                                        <div>{{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i') }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 0.65rem;">
                                                    <i class="fas fa-clock mr-1"></i> PENDING
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                @endif

                                <td class="align-middle text-left small" style="min-width: 150px; white-space: normal;">{{ $cs->remarks ?? '-' }}</td>

                                <td class="align-middle text-center text-nowrap no-export" style="min-width: 160px;">
                                    @if($loop->first)
                                        @include('partials.bulk_approve_button')
                                    @endif

                                    @if($canApproveKashift)
                                        <form action="{{ route('incoming.parts.approve', array_merge(['id' => $cs->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve ({{ $isJakarta ? 'Kepala Regu' : 'Kashift QC' }})" style="min-width: 90px;">
                                                <i class="fas fa-check"></i> Approve{{ $isAdmin ? ($isJakarta ? ' KR' : ' KS') : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject ({{ $isJakarta ? 'Kepala Regu' : 'Kashift QC' }})" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}kashift" style="min-width: 90px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif

                                    @if($canApproveSupervisor)
                                        <form action="{{ route('incoming.parts.approve', array_merge(['id' => $cs->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Supervisor QC)" style="min-width: 90px;">
                                                <i class="fas fa-check"></i> Approve{{ $isAdmin ? ' SPV' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Supervisor QC)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}supervisor" style="min-width: 90px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif

                                    @if($canApproveAsstManager)
                                        <form action="{{ route('incoming.parts.approve', array_merge(['id' => $cs->id, 'type' => 'asst_manager'], request()->all())) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Asst Manager QC)" style="min-width: 90px;">
                                                <i class="fas fa-check"></i> Approve{{ $isAdmin ? ' AM' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Asst Manager QC)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}asst_manager" style="min-width: 90px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif

                                    @if($canApproveManager)
                                        <form action="{{ route('incoming.parts.approve', array_merge(['id' => $cs->id, 'type' => 'manager'], request()->all())) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Manager QC)" style="min-width: 90px;">
                                                <i class="fas fa-check"></i> Approve{{ $isAdmin ? ' MGR' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Manager QC)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}manager" style="min-width: 90px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                    
                                    {{-- Action 3-dot Dropdown Selaras In-Process --}}
                                    @include('partials.action_dropdown', [
                                        'canEdit'      => $canEdit,
                                        'canDelete'    => $canDelete,
                                        'editUrl'      => route('incoming.parts.edit', array_merge(['id' => $cs->id], request()->all())),
                                        'deleteRoute'  => route('incoming.parts.destroy', array_merge(request()->query(), ['id' => $cs->id])),
                                        'deleteParams' => [],
                                        'statusUrl'    => auth()->user()->role === 'admin' && Route::has('incoming.parts.edit_approval') ? route('incoming.parts.edit_approval', array_merge(['id' => $cs->id], request()->all())) : null,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="24" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-gray-400"></i>
                                    Data checksheet tidak ditemukan.
                                </td>
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

    <!-- QR Code Traceability Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold" id="qrModalLabel">
                        <i class="fas fa-qrcode mr-2"></i> Traceability QR Code
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <table class="table table-bordered table-striped bg-white shadow-sm mb-0">
                        <tr>
                            <th style="width: 30%" class="bg-light text-dark font-weight-bold">QR Raw</th>
                            <td id="modal-qr-raw" style="word-break: break-all; font-family: monospace;" class="text-primary font-weight-bold"></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-dark font-weight-bold">Part Code</th>
                            <td id="modal-qr-part" class="font-weight-bold"></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-dark font-weight-bold">Supplier ID</th>
                            <td id="modal-qr-supplier"></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-dark font-weight-bold">Qty</th>
                            <td id="modal-qr-qty" class="font-weight-bold text-success"></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-dark font-weight-bold">Unique ID</th>
                            <td id="modal-qr-unique" class="font-weight-bold text-info"></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-dark font-weight-bold">SAP Code</th>
                            <td id="modal-qr-sap"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer bg-white" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary px-4 shadow-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal (Standar UI In-Process) -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document" style="max-width: 760px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-white" style="border-bottom: 2px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem;">
                    <h5 class="modal-title text-primary font-weight-bold" id="editModalLabel">
                        Edit Checksheet Incoming Part
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

    <!-- Modal Penolakan untuk setiap checksheet -->
    @foreach($checksheets as $cs)
        @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
            @php
                $user = auth()->user();
                $isAdmin = $user->role === 'admin';
                $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta' || strtolower(request('plant') ?? '') === 'jakarta';
                $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;
                $canReject = false;
                if ($rejectType == 'kashift' && ((in_array($user->role, ['kashift', 'kashift_qc']) || $isAdmin || $isSpvJakarta || $isKaruJakarta) && (empty($cs->kashift_qc) || $cs->kashift_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor' && (($user->role === 'supervisor' || $isAdmin) && (empty($cs->supervisor_qc) || $cs->supervisor_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'asst_manager' && ((in_array($user->role, ['asst_manager', 'asst_manager_qc']) || $isAdmin) && (empty($cs->asst_manager_qc) || $cs->asst_manager_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'manager' && ((in_array($user->role, ['manager', 'manager_qc']) || $isAdmin) && (empty($cs->manager_qc) || $cs->manager_qc === 'REJECTED'))) {
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
                            <form action="{{ route('incoming.parts.reject', array_merge(['id' => $cs->id, 'type' => $rejectType], request()->all())) }}" method="POST">
                                @csrf
                                <div class="modal-body text-left">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle"></i> Anda akan menolak checksheet ini sebagai
                                        <strong>{{ ($isJakarta && $rejectType === 'kashift') ? 'Kepala Regu (KR)' : ($rejectType === 'kashift' ? 'Kashift QC' : 'Supervisor QC') }}</strong>
                                    </div>
                                    <div class="form-group">
                                        <label for="rejection_remarks{{ $cs->id }}{{ $rejectType }}" class="font-weight-bold">
                                            Alasan Rejection <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('rejection_remarks') is-invalid @enderror"
                                            id="rejection_remarks{{ $cs->id }}{{ $rejectType }}" name="rejection_remarks" rows="4"
                                            placeholder="Masukkan alasan rejection (minimal 10 karakter)" required minlength="10"
                                            maxlength="500">{{ old('rejection_remarks') }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times-circle mr-1"></i> Konfirmasi Rejection
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endforeach

    <!-- Float Menu untuk Selected Box (Bulk Actions) -->
    <div id="bulkActionMenu" class="position-fixed shadow-lg rounded-pill" style="bottom: 40px; left: 50%; transform: translateX(-50%); display: none; z-index: 9999; background: white; padding: 12px 24px; border: 1px solid #cbd5e1; box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;">
        <div class="d-flex align-items-center">
            <span class="mr-3 font-weight-bold text-gray-800" style="font-size: 0.85rem;"><span id="bulkSelectedCount">0</span> Data Terpilih</span>
            @if($canDelete)
                <button type="button" class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 mr-2" id="btnBulkDelete">
                    <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih
                </button>
            @endif
            @if(auth()->user()->role !== 'operator')
                <button type="button" class="btn btn-success btn-sm shadow-sm rounded-pill px-3" id="btnBulkApproveSelected">
                    <i class="fas fa-check-circle mr-1"></i> Approve Terpilih
                </button>
            @endif
        </div>
    </div>

    <!-- Modal Input & List Stok Kedatangan Awal -->
    <div class="modal fade" id="modalAddArrival" tabindex="-1" role="dialog" aria-labelledby="modalAddArrivalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background-color: #ffffff; overflow: hidden;">
                <div class="modal-header bg-white py-3 px-4" style="border-bottom: 2px solid #f1f5f9;">
                    <h5 class="modal-title font-weight-bold text-gray-800 mb-0" id="modalAddArrivalTitle">
                        Kelola Stok &amp; Input Kedatangan Awal
                    </h5>
                    <button type="button" class="close text-secondary opacity-100" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-left" style="background-color: #ffffff;">
                    <!-- Form Input Kedatangan Baru -->
                    <div class="card border-0 mb-4 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 10px;">
                        <div class="card-header bg-light py-2 border-bottom" style="border-bottom: 1px solid #e2e8f0 !important;">
                            <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem;">
                                Form Input Kedatangan Part Baru
                            </h6>
                        </div>
                        <div class="card-body p-3 bg-white">
                            <form action="{{ route('incoming.parts.store_arrival') }}" method="POST" id="formAddArrival">
                                @csrf
                                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-5 mb-2">
                                        <label class="font-weight-bold text-gray-700 small mb-1">Item Part <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm select2" name="item_id" required style="width:100%;">
                                            <option value="" disabled selected>-- Pilih Item Part --</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3 mb-2">
                                        <label class="font-weight-bold text-gray-700 small mb-1">Tanggal Datang <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" name="tanggal_datang" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group col-md-2 mb-2">
                                        <label class="font-weight-bold text-gray-700 small mb-1">Shift <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" name="shift_datang" required>
                                            <option value="1" selected>Shift 1</option>
                                            <option value="2">Shift 2</option>
                                            <option value="3">Shift 3</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2 mb-2">
                                        <label class="font-weight-bold text-gray-700 small mb-1">Qty Datang <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm font-weight-bold" name="qty_datang" min="1" placeholder="Pcs" required>
                                    </div>
                                </div>
                                <div class="text-right mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-4 shadow-sm" id="btnSubmitArrivalIndex">
                                        <i class="fas fa-save mr-1"></i> Simpan Stok Kedatangan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table Daftar Stok Kedatangan (Open Arrivals) -->
                    <div class="card border-0 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 10px;">
                        <div class="card-header bg-light py-2 border-bottom d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e2e8f0 !important;">
                            <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem;">
                                Daftar Tanggal &amp; Shift Kedatangan (Stok Open)
                            </h6>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold mr-2 px-2 py-1 shadow-sm" id="btnOpenArrivalLogModal" title="Lihat Log Riwayat Stok">
                                    <i class="fas fa-history mr-1"></i> Log Data Stok
                                </button>
                                <span class="badge badge-info px-2 py-1 font-weight-bold" id="openArrivalCountBadge">
                                    {{ count($openArrivals ?? []) }} Lot Open
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0 bg-white">
                            <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                                <table class="table table-hover table-sm text-center mb-0" id="tableOpenArrivals" style="font-size: 0.78rem;">
                                    <thead style="background-color: #f8fafc !important; color: #475569 !important; position: sticky; top: 0; z-index: 10; border-bottom: 2px solid #cbd5e1 !important;">
                                        <tr>
                                            <th class="py-2 text-center" style="width: 45px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">No</th>
                                            <th class="py-2 text-left" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Nama Part / Part No</th>
                                            <th class="py-2 text-center" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Tgl &amp; Shift Datang</th>
                                            <th class="py-2 text-center" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Qty Datang</th>
                                            <th class="py-2 text-center" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Qty Sisa Stok</th>
                                            <th class="py-2 text-center" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; background-color: #f8fafc !important; color: #475569 !important;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody style="color: #334155;">
                                        @forelse($openArrivals ?? [] as $arr)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="align-middle" style="border-right: 1px solid #f1f5f9;">{{ $loop->iteration }}</td>
                                                <td class="align-middle text-left font-weight-bold" style="border-right: 1px solid #f1f5f9;">
                                                    {{ $arr->item->name ?? '-' }}
                                                    <br><small class="text-muted">{{ $arr->item->part_number ?? '-' }}</small>
                                                </td>
                                                <td class="align-middle" style="border-right: 1px solid #f1f5f9;">
                                                    <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($arr->tanggal_datang)->format('d/m/Y') }}</span>
                                                    <br><small class="text-muted">Shift {{ $arr->shift_datang }}</small>
                                                </td>
                                                <td class="align-middle" style="border-right: 1px solid #f1f5f9;">{{ number_format($arr->qty_datang) }} pcs</td>
                                                <td class="align-middle font-weight-bold text-dark" style="border-right: 1px solid #f1f5f9;">{{ number_format($arr->qty_sisa) }} pcs</td>
                                                <td class="align-middle">
                                                    <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">OPEN</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="emptyArrivalRow">
                                                <td colspan="6" class="text-center text-muted py-3">Belum ada stok kedatangan part yang OPEN.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2 px-4" style="border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-4" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Log Riwayat Data Stok Kedatangan -->
    <div class="modal fade" id="modalArrivalLog" tabindex="-1" role="dialog" aria-labelledby="modalArrivalLogTitleIndex" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background-color: #ffffff; overflow: hidden;">
                <div class="modal-header bg-white py-3 px-4" style="border-bottom: 2px solid #f1f5f9;">
                    <h5 class="modal-title font-weight-bold text-gray-800 mb-0" id="modalArrivalLogTitleIndex" style="font-size: 0.95rem;">
                        Log Activity &amp; Riwayat Stok Kedatangan (IN / OUT / UPDATE / DELETE)
                    </h5>
                    <button type="button" class="close text-secondary opacity-100" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-left" style="background-color: #ffffff;">
                    <!-- Filter bar -->
                    <div class="card border-0 shadow-sm mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 10px;">
                        <div class="card-header bg-light py-2 border-bottom" style="border-bottom: 1px solid #e2e8f0 !important;">
                            <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem;">
                                Filter Data Log Stok
                            </h6>
                        </div>
                        <div class="card-body p-3 bg-white">
                            <div class="form-row align-items-center">
                                <div class="col-md-5 mb-2 mb-md-0">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm border-left-0" id="arrivalLogSearch" placeholder="Cari Nama Part, Part No, User, Keterangan..." autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <select class="form-control form-control-sm" id="arrivalLogFilterAction">
                                        <option value="">-- Semua Jenis Aksi --</option>
                                        <option value="IN">IN (Stok Masuk / Kedatangan Baru)</option>
                                        <option value="OUT">OUT (Stok Keluar / Checksheet QC)</option>
                                        <option value="UPDATE">UPDATE (Perubahan Data)</option>
                                        <option value="DELETE">DELETE (Penghapusan Stok)</option>
                                    </select>
                                </div>
                                <div class="col-md-3 text-right">
                                    <button type="button" class="btn btn-sm btn-primary font-weight-bold btn-block shadow-sm" id="btnRefreshArrivalLogs">
                                        <i class="fas fa-sync-alt mr-1"></i> Refresh Log
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Log Table -->
                    <div class="card border-0 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 10px;">
                        <div class="card-header bg-light py-2 border-bottom" style="border-bottom: 1px solid #e2e8f0 !important;">
                            <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem;">
                                Tabel Riwayat Perubahan Stok
                            </h6>
                        </div>
                        <div class="card-body p-0 bg-white">
                            <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                                <table class="table table-hover table-sm text-center mb-0" id="tableArrivalLogs" style="font-size: 0.78rem;">
                                    <thead style="background-color: #f8fafc !important; color: #475569 !important; position: sticky; top: 0; z-index: 10; border-bottom: 2px solid #cbd5e1 !important;">
                                        <tr>
                                            <th class="py-2 text-center" style="width: 45px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">No</th>
                                            <th class="py-2 text-center" style="width: 140px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Waktu Log</th>
                                            <th class="py-2 text-center" style="width: 130px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Diubah Oleh (User)</th>
                                            <th class="py-2 text-left" style="min-width: 180px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Nama Part / Part No</th>
                                            <th class="py-2 text-center" style="width: 120px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Tgl &amp; Shift Datang</th>
                                            <th class="py-2 text-center" style="width: 110px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Aksi</th>
                                            <th class="py-2 text-center" style="width: 180px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Detail Stok (Awal &rarr; Ubah &rarr; Sisa)</th>
                                            <th class="py-2 text-left" style="min-width: 180px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; background-color: #f8fafc !important; color: #475569 !important;">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="arrivalLogTableBody" style="color: #334155;">
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block text-primary"></i>
                                                Memuat log data stok...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-2 px-3 border-top d-flex justify-content-between align-items-center" style="border-top: 1px solid #e2e8f0 !important;" id="arrivalLogPaginationCardFooter">
                            <small class="text-muted font-weight-bold" id="arrivalLogPaginationInfo">
                                Menampilkan 0 - 0 dari 0 log data
                            </small>
                            <nav aria-label="Navigasi Halaman Log">
                                <ul class="pagination pagination-sm m-0" id="arrivalLogPaginationNav">
                                    <!-- Dynamic pagination links -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2 px-4" style="border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-4" data-dismiss="modal">Tutup</button>
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
                    var href = this.getAttribute('href');
                    if (!href || href === '#') return;

                    var iframe = document.getElementById('silentPrintIframe');
                    if (!iframe) {
                        iframe = document.createElement('iframe');
                        iframe.id = 'silentPrintIframe';
                        iframe.style.position = 'fixed';
                        iframe.style.right = '0';
                        iframe.style.bottom = '0';
                        iframe.style.width = '0';
                        iframe.style.height = '0';
                        iframe.style.border = '0';
                        document.body.appendChild(iframe);
                    }

                    iframe.src = href;
                    iframe.onload = function() {
                        try {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                        } catch (err) {
                            window.open(href, '_blank');
                        }
                    };
                });
            });
        });
    </script>
<script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Smart Autocomplete Search Dropdowns (Matches Sub Assy behavior)
        if (typeof initItemSearch === 'function') {
            initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
            initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
            initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
        }

        // Traceability QR Code Modal Handler
        $(document).on('click', '.btn-qr-detail', function () {
            const qr = $(this).data('qr') || '-';
            const part = $(this).data('part') || '-';
            const supplier = $(this).data('supplier') || '-';
            const qty = $(this).data('qty') || '-';
            const unique = $(this).data('unique') || '-';
            const sap = $(this).data('sap') || '-';

            $('#modal-qr-raw').text(qr);
            $('#modal-qr-part').text(part);
            $('#modal-qr-supplier').text(supplier);
            $('#modal-qr-qty').text(qty);
            $('#modal-qr-unique').text(unique);
            $('#modal-qr-sap').text(sap);

            $('#qrModal').modal('show');
        });

        // Selected Box (Bulk Select All & Row Checkboxes) Handler
        function updateSelectedCount() {
            const checkedBoxes = $('.row-checkbox:checked');
            const totalBoxes = $('.row-checkbox');
            const count = checkedBoxes.length;

            $('#checkedCountDisplay').text(count);
            $('#bulkSelectedCount').text(count);

            if (totalBoxes.length > 0) {
                $('#checkAllRows').prop('checked', count === totalBoxes.length);
            }

            if (count > 0) {
                $('#bulkActionMenu').css('display', 'flex').stop(true, true).fadeIn(200);
            } else {
                $('#bulkActionMenu').stop(true, true).fadeOut(200);
            }

            $('.row-checkbox').each(function () {
                const row = $(this).closest('tr');
                if ($(this).is(':checked')) {
                    row.addClass('table-primary');
                } else {
                    row.removeClass('table-primary');
                }
            });
        }

        $(document).on('change', '#checkAllRows', function () {
            const isChecked = $(this).prop('checked');
            $('.row-checkbox').prop('checked', isChecked);
            updateSelectedCount();
        });

        $(document).on('change', '.row-checkbox', function (e) {
            e.stopPropagation();
            updateSelectedCount();
        });

        $(document).on('click', '#btnBulkDelete', function () {
            const selectedIds = $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Hapus ' + selectedIds.length + ' Data Terpilih?',
                text: "Data yang dipilih akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus Data...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('incoming.parts.bulk_destroy') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            ids: selectedIds
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        },
                        error: function (xhr) {
                            const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghapus data.';
                            Swal.fire('Gagal!', msg, 'error');
                        }
                    });
                }
            });
        });

        $(document).on('click', '#btnBulkApproveSelected', function () {
            const selectedIds = $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            const userRole = "{{ auth()->user()->role }}";
            const plantCode = "{{ strtolower(request('plant') ?? optional(auth()->user()->plant)->code ?? 'karawang') }}";
            const kashiftLabel = plantCode === 'jakarta' ? 'Kepala Regu (KR)' : 'Kashift QC';

            function processBulkApproveAjax(approvalType) {
                Swal.fire({
                    title: 'Memproses Approval...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('incoming.parts.bulk_approve') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: selectedIds,
                        approval_type: approvalType
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat approve data.';
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }

            if (userRole === 'admin') {
                Swal.fire({
                    title: 'Pilih Level Approval',
                    input: 'select',
                    inputOptions: {
                        'kashift': kashiftLabel,
                        'supervisor': 'Supervisor QC'
                    },
                    inputPlaceholder: 'Pilih level...',
                    showCancelButton: true,
                    confirmButtonText: 'Approve Data Terpilih',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#1cc88a',
                    inputValidator: (value) => {
                        if (!value) return 'Anda harus memilih level approval!';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        processBulkApproveAjax(result.value);
                    }
                });
            } else {
                Swal.fire({
                    title: 'Approve ' + selectedIds.length + ' Data Terpilih?',
                    text: "Seluruh data terpilih akan disetujui untuk level approval Anda.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1cc88a',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Approve Semua!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        processBulkApproveAjax(userRole);
                    }
                });
            }
        });

        // Handle SweetAlert2 Delete Confirmation (Selaras In-Process UI Standardization)
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Data checksheet Incoming Part akan dihapus!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    form.submit();
                }
            }
        });

        // AJAX Edit Modal Handler
        $(document).on('click', '.btn-edit-modal', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#editModal').modal('show');
            $('#editModalBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted small">Memuat data checksheet...</p>
                </div>
            `);

            $.get(url, function (data) {
                $('#editModalBody').html(data);
                if ($.fn.select2) {
                    $('#editModalBody .select2').select2({ dropdownParent: $('#editModal') });
                }
            }).fail(function () {
                $('#editModalBody').html(`
                    <div class="alert alert-danger text-center mb-0">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat form edit checksheet.
                    </div>
                `);
            });
        });

        // AJAX Add Arrival Form Submission
        $('#formAddArrival').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#btnSubmitArrivalIndex');
            var origText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $btn.prop('disabled', false).html(origText);
                    if (res.success) {
                        $('#modalAddArrival').modal('hide');
                        $('#formAddArrival')[0].reset();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false });
                        } else {
                            alert(res.message);
                        }

                        if (res.arrival) {
                            $('#emptyArrivalRow').remove();
                            var arr = res.arrival;
                            var itemName = arr.item ? arr.item.name : '-';
                            var partNo = arr.item && arr.item.part_number ? arr.item.part_number : '-';
                            var tglClean = (arr.tanggal_datang || '').split('T')[0];
                            var tglParts = tglClean.split('-');
                            var tglFmt = tglParts.length === 3 ? (tglParts[2] + '/' + tglParts[1] + '/' + tglParts[0]) : tglClean;
                            var qtyDatangFmt = new Intl.NumberFormat().format(arr.qty_datang);
                            var qtySisaFmt = new Intl.NumberFormat().format(arr.qty_sisa);
                            
                            var $existingRow = $('#arrivalRow_' + arr.id);
                            if ($existingRow.length > 0) {
                                $existingRow.find('td:nth-child(4)').text(qtyDatangFmt + ' pcs');
                                $existingRow.find('td:nth-child(5)').text(qtySisaFmt + ' pcs');
                            } else {
                                var newRow = '<tr style="border-bottom: 1px solid #f1f5f9;" id="arrivalRow_' + arr.id + '">' +
                                    '<td class="align-middle" style="border-right: 1px solid #f1f5f9;">1</td>' +
                                    '<td class="align-middle text-left font-weight-bold" style="border-right: 1px solid #f1f5f9;">' + itemName + '<br><small class="text-muted">' + partNo + '</small></td>' +
                                    '<td class="align-middle" style="border-right: 1px solid #f1f5f9;"><span class="font-weight-bold text-dark">' + tglFmt + '</span><br><small class="text-muted">Shift ' + arr.shift_datang + '</small></td>' +
                                    '<td class="align-middle" style="border-right: 1px solid #f1f5f9;">' + qtyDatangFmt + ' pcs</td>' +
                                    '<td class="align-middle font-weight-bold text-dark" style="border-right: 1px solid #f1f5f9;">' + qtySisaFmt + ' pcs</td>' +
                                    '<td class="align-middle"><span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">OPEN</span></td>' +
                                    '</tr>';
                                    
                                $('#tableOpenArrivals tbody').append(newRow);
                            }

                            $('#tableOpenArrivals tbody tr').each(function(idx) {
                                $(this).find('td:first').text(idx + 1);
                            });
                            $('#openArrivalCountBadge').text($('#tableOpenArrivals tbody tr').length + ' Lot Open');
                        }
                    } else {
                        alert(res.message || 'Gagal menyimpan stok kedatangan.');
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html(origText);
                    var errMsg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Terjadi kesalahan.';
                    alert(errMsg);
                }
            });
        });

        // --- Log Activity & Riwayat Stok Kedatangan ---
        function fetchArrivalLogsIndex() {
            var $tbody = $('#arrivalLogTableBody');
            $tbody.html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 d-block text-primary"></i><br>Memuat log data stok...</td></tr>');

            var params = {
                plant: '{{ request("plant") ?? auth()->user()->plant_id }}',
                search: $('#arrivalLogSearch').val() || '',
                action_type: $('#arrivalLogFilterAction').val() || ''
            };

            $.ajax({
                url: '{{ route("incoming.parts.arrival_logs") }}',
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.logs && res.logs.length > 0) {
                        var html = '';
                        $.each(res.logs, function(idx, log) {
                            var badgeClass = 'badge-secondary';
                            var badgeIcon = 'fa-info-circle';
                            var actionLabel = log.action_type;

                            if (log.action_type === 'IN') {
                                badgeClass = 'badge-success';
                                badgeIcon = 'fa-arrow-down';
                                actionLabel = 'STOK MASUK (IN)';
                            } else if (log.action_type === 'OUT') {
                                badgeClass = 'badge-danger';
                                badgeIcon = 'fa-arrow-up';
                                actionLabel = 'STOK KELUAR (OUT)';
                            } else if (log.action_type === 'UPDATE') {
                                badgeClass = 'badge-warning text-dark';
                                badgeIcon = 'fa-edit';
                                actionLabel = 'UPDATE DATA';
                            } else if (log.action_type === 'DELETE') {
                                badgeClass = 'badge-dark';
                                badgeIcon = 'fa-trash';
                                actionLabel = 'HAPUS DATA';
                            }

                            var changeColor = log.qty_change_raw > 0 ? 'text-success font-weight-bold' : (log.qty_change_raw < 0 ? 'text-danger font-weight-bold' : 'text-muted');

                            html += '<tr>';
                            html += '<td class="align-middle text-center">' + (idx + 1) + '</td>';
                            html += '<td class="align-middle text-center font-weight-bold text-dark" style="font-size:0.75rem;">' + log.created_at + '</td>';
                            html += '<td class="align-middle text-center"><span class="badge badge-light border text-dark font-weight-bold px-2 py-1"><i class="fas fa-user mr-1 text-primary"></i>' + log.user_name + '</span></td>';
                            html += '<td class="align-middle text-left font-weight-bold">' + log.item_name + '<br><small class="text-muted">' + log.part_number + '</small></td>';
                            html += '<td class="align-middle text-center">' + log.tanggal_datang + '<br><small class="text-muted">Shift ' + log.shift_datang + '</small></td>';
                            html += '<td class="align-middle text-center"><span class="badge ' + badgeClass + ' px-2 py-1" style="font-size:0.68rem;"><i class="fas ' + badgeIcon + ' mr-1"></i>' + actionLabel + '</span></td>';
                            html += '<td class="align-middle text-center" style="font-size:0.78rem;">' +
                                    '<span class="text-muted">' + log.qty_before + '</span> &rarr; ' +
                                    '<span class="' + changeColor + '">' + log.qty_change + '</span> &rarr; ' +
                                    '<span class="font-weight-bold text-dark">' + log.qty_after + ' pcs</span>' +
                                    '</td>';
                            html += '<td class="align-middle text-left text-muted small">' + log.description + '</td>';
                            html += '</tr>';
                        });
                        $tbody.html(html);
                    } else {
                        $tbody.html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-history fa-2x mb-2 d-block text-gray-400"></i>Belum ada log data stok kedatangan recorded.</td></tr>');
                    }
                },
                error: function(err) {
                    $tbody.html('<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Gagal memuat log data stok.</td></tr>');
                }
            });
        }

        $(document).on('click', '#btnOpenArrivalLogModal', function(e) {
            if (e) e.preventDefault();
            $('#modalArrivalLog').modal('show');
            fetchArrivalLogsIndex();
        });

        $(document).on('click', '#btnRefreshArrivalLogs', function(e) {
            if (e) e.preventDefault();
            fetchArrivalLogsIndex();
        });

        var logSearchTimerIndex;
        $(document).on('keyup', '#arrivalLogSearch', function() {
            clearTimeout(logSearchTimerIndex);
            logSearchTimerIndex = setTimeout(function() {
                fetchArrivalLogsIndex();
            }, 300);
        });

        $(document).on('change', '#arrivalLogFilterAction', function() {
            fetchArrivalLogsIndex();
        });
    });
</script>
@php $bulkApproveRoute = route('incoming.parts.bulk_approve'); @endphp
@include('partials.bulk_approve_script')
@endpush
