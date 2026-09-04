@extends('layouts.admin')

@section('title', 'Incoming Sub-Part')

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
        white-space: nowrap !important;
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important; /* Solid background for opacity */
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important; /* Matched to in-process */
        letter-spacing: 0.2px;
        padding: 6px 12px !important; /* Matched to in-process */
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #cbd5e1;
    }

    #checksheetTable tbody tr:hover {
        background-color: #f1f5f9 !important;
        transition: background-color 0.2s ease;
    }

    /* Forced overrides for compact view - consistency with In-Process */
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    
    /* Minimalist Dimension Table Styles (matched to In-Process) */
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

        $approvalOrder = $approvalOrder ?? ['kashift', 'supervisor', 'asst_manager', 'manager'];

        // Resolve menu ID for permission checks
        $currentMenu = \App\Models\AppMenu::where('route', 'incoming.sub_parts.index')->first();
        $menuId = $currentMenu ? $currentMenu->id : null;
        $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
        $canEdit = $menuId ? auth()->user()->hasPermission($menuId, 'edit') : true;
        $canDelete = $menuId ? auth()->user()->hasPermission($menuId, 'delete') : true;

        $docHeader = $docHeader ?? \App\Models\GeneralSetting::getDocHeader('incoming_sub_parts', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0212',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="mb-3">
                <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                LAPORAN DATA INCOMING SUB-PART
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
            <form action="{{ route('incoming.sub_parts.index') }}" method="GET"
                class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 8px; overflow-x: auto; white-space: nowrap;" id="filterFormIncoming">
                
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                @if(request()->has('view_mode'))
                    <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
                @endif
                @if(request()->has('entry_method'))
                    <input type="hidden" name="entry_method" value="{{ request('entry_method') }}">
                @endif

                <!-- Field: Part -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cari:</label>
                    <div style="width: 200px;" class="custom-filter-wrapper">
                        <select name="item_id" id="filterItem" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Ketik Sub-Part Name...</option>
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
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tgl Check:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                            style="width: 120px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                        <span class="px-1 text-gray-500 small">-</span>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                            style="width: 120px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                    </div>
                </div>

                <!-- Field: Tanggal Datang -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tanggal Datang:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <input type="date" name="start_tgl_datang" id="start_tgl_datang" class="form-control form-control-sm border-0"
                            style="width: 120px; font-size: 0.75rem;" value="{{ request('start_tgl_datang') }}">
                        <span class="px-1 text-gray-500 small">-</span>
                        <input type="date" name="end_tgl_datang" id="end_tgl_datang" class="form-control form-control-sm border-0"
                            style="width: 120px; font-size: 0.75rem;" value="{{ request('end_tgl_datang') }}">
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
                    <a href="{{ route('incoming.sub_parts.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if(\Illuminate\Support\Facades\Schema::hasColumn('incoming_sub_parts', 'qrcode'))
                        @if(request('view_mode') !== 'verifikasi')
                            <a href="{{ route('incoming.sub_parts.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'qr', 'plant' => request('plant')])) }}"
                                class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold" title="Data Hasil Verifikasi"
                                style="background-color: #6f42c1; color: white;">
                                <i class="fas fa-clipboard-check fa-sm mr-1"></i> Hasil Verifikasi
                            </a>
                        @else
                            <a href="{{ route('incoming.sub_parts.index', ['plant' => request('plant')]) }}"
                                class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold" title="Kembali ke Data Regular"
                                style="background-color: #6c757d; color: white;">
                                <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
                            </a>
                        @endif
                    @endif
                    @include('partials.bulk_approve_button')
                    @if($canExport)
                    <a href="{{ route('incoming.sub_parts.print', request()->query()) }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader btn-print-direct" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i> Cetak
                    </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover text-center" width="100%" cellspacing="0" id="checksheetTable">
                    <thead class="bg-light">
                        @php $rs = request('view_mode') === 'verifikasi' ? 1 : 2; @endphp
                        <tr class="align-middle text-center">
                            @if(auth()->user()->role === 'admin')
                                <th rowspan="{{ $rs }}" class="align-middle text-center" style="width: 55px;">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <span style="font-size: 9px; font-weight: bold; margin-bottom: 3px; text-transform: uppercase; line-height: 1.1;">SEMUA<br>(<span id="checkedCountDisplay">0</span>)</span>
                                        <div class="custom-control custom-checkbox d-inline-block" style="min-height: 1.2rem; padding-left: 1.2rem; margin: 0 auto;">
                                            <input type="checkbox" class="custom-control-input" id="checkAllRows">
                                            <label class="custom-control-label" for="checkAllRows" style="cursor:pointer;"></label>
                                        </div>
                                    </div>
                                </th>
                            @endif
                            <th rowspan="{{ $rs }}" class="align-middle">No</th>
                            @if(request('view_mode') === 'verifikasi')
                                <th rowspan="{{ $rs }}" class="align-middle">QR-Code</th>
                            @endif
                            <th rowspan="{{ $rs }}" class="align-middle">Checked<br>(Tgl / Shift / Inisial)</th>
                            <th rowspan="{{ $rs }}" class="align-middle text-nowrap">Waktu Check<br>(Start - Finish / CT)</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Sub-Part Name / Part No</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Supplier</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Tanggal Datang</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Lot/Batch</th>
                            <th colspan="2" class="align-middle">Qty (Pcs)</th>
                            <th rowspan="{{ $rs }}" class="align-middle" style="min-width: 170px;">Check Dimensi</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Judgment</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="2" class="align-middle">Detail NG</th>
                                <th colspan="4" class="align-middle">Approval Status</th>
                            @endif
                            <th rowspan="{{ $rs }}" class="align-middle">Description</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Action</th>
                        </tr>
                        @if(request('view_mode') !== 'verifikasi')
                            <tr class="text-center">
                                <th>Total (Pcs)</th>
                                <th>Sampling Size</th>
                                <th style="width: 45px; min-width: 45px;">Pcs</th>
                                <th style="min-width: 70px;" class="text-nowrap">Jenis NG</th>
                                <th style="font-size: 10px; min-width: 120px;">{{ $plantCode === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                                <th style="font-size: 10px; min-width: 120px;">Supervisor QC</th>
                                <th style="font-size: 10px; min-width: 120px;">Asst Manager QC</th>
                                <th style="font-size: 10px; min-width: 120px;">Manager QC</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @forelse($checksheets as $cs)
                            @php
                                $user = auth()->user();
                                $isAdmin = $user->role === 'admin';
                                $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                                $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                $canApproveKashift = (in_array($user->role, ['kashift', 'kashift_qc']) || $isAdmin || $isSpvJakarta || $isKaruJakarta) 
                                    && (empty($cs->kashift_qc) || $cs->kashift_qc === 'REJECTED');

                                $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) 
                                    && (empty($cs->supervisor_qc) || $cs->supervisor_qc === 'REJECTED')
                                    && (!empty($cs->kashift_qc) && $cs->kashift_qc !== 'REJECTED');

                                $canApproveAsst = (in_array($user->role, ['asst_manager', 'asst_manager_qc']) || $isAdmin) 
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
                                        @if(!empty($cs->qrcode) || !empty($cs->unique_code_id))
                                            <button type="button" class="btn btn-outline-primary btn-xs btn-qr-detail" 
                                                data-qr="{{ $cs->qrcode }}"
                                                style="padding: 0.2rem 0.5rem; font-size: 0.80rem;" title="Lihat Detail QR Code">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="align-middle text-nowrap font-weight-bold" style="font-size: 0.70rem;">
                                    {{ date('d-m-Y', strtotime($cs->date)) }} / {{ $cs->shift ?? '1' }} / {{ $cs->operator_initials ?? '-' }}
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
                                <td class="align-middle text-nowrap text-gray-700">{{ $cs->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ date('d-m-Y', strtotime($cs->tanggal_datang)) }}</td>
                                <td class="align-middle">{{ $cs->lot_batch_number }}</td>
                                <td class="align-middle font-weight-bold">{{ (float) $cs->quantity }}</td>
                                <td class="align-middle">{{ (float) $cs->sampling_size_pcs }}</td>
                                {{-- Detail Cek Dimensi (Point Only) --}}
                                <td class="align-middle p-0 text-nowrap" style="min-width: 170px; white-space: nowrap;">
                                    @php
                                        $dimData = is_array($cs->check_dimensi) ? $cs->check_dimensi : json_decode($cs->check_dimensi, true);
                                        $dimData = $dimData ?: [];

                                        // Flatten point inputs from 1D array or nested cavity array
                                        $hasUserInputs = false;
                                        $flatPoints = [];
                                        if (is_array($dimData)) {
                                            foreach ($dimData as $k => $v) {
                                                if (is_array($v)) {
                                                    foreach ($v as $pIdx => $pVal) {
                                                        if ($pVal !== null && $pVal !== '' && $pVal !== '-') {
                                                            $hasUserInputs = true;
                                                            $flatPoints[$pIdx] = $pVal;
                                                        }
                                                    }
                                                } else {
                                                    if ($v !== null && $v !== '' && $v !== '-') {
                                                        $hasUserInputs = true;
                                                        $pIdx = is_numeric($k) ? (int) $k : $k;
                                                        $flatPoints[$pIdx] = $v;
                                                    }
                                                }
                                            }
                                        }
                                        $itemStandardsRaw = $cs->item->dimension_standards ?? null;
                                        $standards = [];
                                        if (!empty($itemStandardsRaw) && is_array($itemStandardsRaw)) {
                                            foreach ($itemStandardsRaw as $idx => $std) {
                                                if (is_array($std)) {
                                                    $pKey = (string)($std['point'] ?? ($idx + 1));
                                                    $standards[$pKey] = [
                                                        'size' => $std['size'] ?? null,
                                                        'tolerance' => $std['tolerance'] ?? null,
                                                        'min' => $std['min'] ?? null,
                                                        'max' => $std['max'] ?? null,
                                                    ];
                                                }
                                            }
                                        }
                                        if (empty($standards)) {
                                            $itemPartNumber = str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', str_replace(["\xe2\x80\x92", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"], '-', $cs->item->part_number ?? ''));
                                            $itemPartNumber = strtoupper($itemPartNumber);
                                            $standards = ($partDimensionStandards ?? [])[$itemPartNumber] ?? [];
                                        }


                                        // Active point indexes
                                        $activePoints = [];
                                        foreach ($flatPoints as $pKey => $pVal) {
                                            $activePoints[$pKey] = true;
                                        }
                                        foreach ($standards as $pKey => $std) {
                                            $activePoints[$pKey] = true;
                                        }
                                        $activePoints = array_keys($activePoints);
                                        sort($activePoints);

                                        if (empty($activePoints) && $hasUserInputs) {
                                            $activePoints = range(1, count($flatPoints));
                                        }
                                    @endphp
                                    @if($hasUserInputs && !empty($activePoints))
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
                                                            @foreach ($activePoints as $j)
                                                                <td class="dim-header text-std-header">
                                                                    Min: {{ (isset($standards[$j]) && ($standards[$j]['min'] ?? null) !== null) ? $standards[$j]['min'] : '-' }}
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
                                                            @foreach ($activePoints as $j)
                                                                <td class="dim-header text-std-header">
                                                                    Max: {{ (isset($standards[$j]) && ($standards[$j]['max'] ?? null) !== null) ? $standards[$j]['max'] : '-' }}
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
                                                            @foreach ($activePoints as $j)
                                                                <td class="dim-header text-std-header">
                                                                    {{ isset($standards[$j]) ? '±' . ($standards[$j]['tolerance'] ?? '-') : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Baris Header Utama Point --}}
                                                    <tr>
                                                        @foreach ($activePoints as $j)
                                                            <td class="dim-header">Ø{{ $j }}</td>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- Pengukuran Aktual (Satu Baris per Sub-Part) --}}
                                                    <tr>
                                                        @foreach ($activePoints as $j)
                                                            @php
                                                                $val = $flatPoints[$j] ?? '-';
                                                                $isNG = false;

                                                                $std = null;
                                                                if (!empty($standards)) {
                                                                    if (isset($standards[$j])) {
                                                                        $std = $standards[$j];
                                                                    } elseif (isset($standards[(string)$j])) {
                                                                        $std = $standards[(string)$j];
                                                                    } elseif (isset($standards[$j - 1])) {
                                                                        $std = $standards[$j - 1];
                                                                    } else {
                                                                        foreach ($standards as $itemStd) {
                                                                            if (isset($itemStd['point']) && (string)$itemStd['point'] === (string)$j) {
                                                                                $std = $itemStd;
                                                                                break;
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                $cleanValStr = trim((string)$val);
                                                                $numStr = str_replace(',', '.', $cleanValStr);
                                                                if ($std && $cleanValStr !== '-' && $cleanValStr !== '' && is_numeric($numStr)) {
                                                                    $fVal = (float)$numStr;
                                                                    $epsilon = 0.00001;

                                                                    // 1. Min / Max
                                                                    if (($std['min'] ?? null) !== null && $std['min'] !== '' && $std['min'] !== '-') {
                                                                        $minBound = (float)str_replace(',', '.', (string)$std['min']);
                                                                        if ($fVal < ($minBound - $epsilon)) $isNG = true;
                                                                    }
                                                                    if (!$isNG && ($std['max'] ?? null) !== null && $std['max'] !== '' && $std['max'] !== '-') {
                                                                        $maxBound = (float)str_replace(',', '.', (string)$std['max']);
                                                                        if ($fVal > ($maxBound + $epsilon)) $isNG = true;
                                                                    }

                                                                    // 2. Size +/- Tolerance
                                                                    if (!$isNG && ($std['size'] ?? null) !== null && ($std['tolerance'] ?? null) !== null && $std['size'] !== '' && $std['tolerance'] !== '' && $std['size'] !== '-' && $std['tolerance'] !== '-') {
                                                                        $szStr = trim((string)$std['size']);
                                                                        if (!str_starts_with($szStr, '+') && !str_starts_with($szStr, '-')) {
                                                                            $base = (float)str_replace(',', '.', $szStr);
                                                                            $tol = trim((string)$std['tolerance']);
                                                                            $tol = str_replace(['±', ' '], '', $tol);
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
                                                                                $tv = (float)str_replace(',', '.', $tol);
                                                                                $lb = $base - $tv; $ub = $base + $tv;
                                                                            }
                                                                            
                                                                            if ($fVal < ($lb - $epsilon) || $fVal > ($ub + $epsilon)) $isNG = true;
                                                                        }
                                                                    }

                                                                    // 3. Signed Size (+17 or -17)
                                                                    if (!$isNG && ($std['size'] ?? null) !== null && $std['size'] !== '' && $std['size'] !== '-') {
                                                                        $szStr = trim((string)$std['size']);
                                                                        if (str_starts_with($szStr, '+') || str_starts_with($szStr, '-')) {
                                                                            $op = $szStr[0];
                                                                            $bound = (float)substr($szStr, 1);
                                                                            if ($op === '+' && $fVal < ($bound - $epsilon)) $isNG = true;
                                                                            elseif ($op === '-' && $fVal > ($bound + $epsilon)) $isNG = true;
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                            <td class="dim-data {{ $isNG ? 'text-danger font-weight-bold' : '' }}" @if($isNG) style="color: #dc3545 !important; font-weight: bold !important;" @endif>
                                                                {{ $val }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <span class="text-muted small">{{ is_string($cs->check_dimensi) && $cs->check_dimensi ? $cs->check_dimensi : '-' }}</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">
                                        {{ $cs->judgment }}
                                    </span>
                                </td>

                                @php
                                    $defectsData = is_array($cs->defects) ? $cs->defects : json_decode($cs->defects, true);
                                    $pcsLines = [];
                                    $nameLines = [];
                                    if (is_array($defectsData)) {
                                        foreach ($defectsData as $d) {
                                            if (is_array($d) && isset($d['type'])) {
                                                $pcsLines[] = $d['qty'] ?? 1;
                                                $nameLines[] = $d['type'];
                                            } elseif (is_string($d)) {
                                                $pcsLines[] = 1;
                                                $nameLines[] = $d;
                                            }
                                        }
                                    }
                                @endphp
                                @if(request('view_mode') !== 'verifikasi')
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
                                @endif

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

                                <td class="align-middle" style="min-width: 200px;">
                                    @if($cs->rejection_remarks)
                                        <div class="text-danger font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i> REJECTED
                                        </div>
                                        <small class="text-muted">{{ $cs->rejection_remarks }}</small>
                                    @else
                                        @if($cs->next_proses ?? false)
                                            <div class="mb-1">
                                                <span class="badge badge-danger px-2 py-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    LABEL MERAH: {{ $cs->next_proses }}
                                                </span>
                                                <br>
                                                @if(!str_contains($cs->remarks ?? '', '[SORTIR_CLOSED]'))
                                                    <span class="text-danger small font-weight-bold ml-1">
                                                        <i class="fas fa-clock"></i> STATUS: OPEN
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($cs->remarks)) !!}
                                    @endif
                                </td>
                                <td class="align-middle text-center text-nowrap no-export" style="min-width: 160px;">
                                    @if($loop->first)
                                        @include('partials.bulk_approve_button')
                                    @endif

                                    @if($canApproveKashift)
                                        <form action="{{ route('incoming.sub_parts.approve', array_merge(['id' => $cs->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve ({{ $isJakarta ? 'Kepala Regu' : 'Kashift' }})" style="min-width: 90px;">
                                                <i class="fas fa-check"></i> Approve{{ ($user->role === 'admin') ? ($isJakarta ? ' KR' : ' KS') : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject ({{ $isJakarta ? 'Kepala Regu' : 'Kashift' }})" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}kashift" style="min-width: 90px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                    @if($canApproveSupervisor)
                                        <form action="{{ route('incoming.sub_parts.approve', array_merge(['id' => $cs->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV)" style="min-width: 90px;">
                                                <i class="fas fa-check"></i> Approve{{ (auth()->user()->role === 'admin') ? ' SPV' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}supervisor" style="min-width: 90px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                    @if($canApproveAsst)
                                        <form action="{{ route('incoming.sub_parts.approve', array_merge(['id' => $cs->id, 'type' => 'asst_manager'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (AM)" style="min-width: 90px;">
                                                <i class="fas fa-check"></i> Approve{{ (auth()->user()->role === 'admin') ? ' AM' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (AM)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}asst_manager" style="min-width: 90px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                    @if($canApproveManager)
                                        <form action="{{ route('incoming.sub_parts.approve', array_merge(['id' => $cs->id, 'type' => 'manager'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (MGR)" style="min-width: 90px;">
                                                <i class="fas fa-check"></i> Approve{{ (auth()->user()->role === 'admin') ? ' MGR' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (MGR)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}manager" style="min-width: 90px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif

                                    @include('partials.action_dropdown', [
                                        'canEdit'      => $canEdit && !in_array($user->role, ['inspector']),
                                        'canDelete'    => $canDelete && !in_array($user->role, ['inspector']),
                                        'editUrl'      => route('incoming.sub_parts.edit', array_merge(['id' => $cs->id], request()->all())),
                                        'deleteRoute'  => route('incoming.sub_parts.destroy', array_merge(request()->query(), ['id' => $cs->id])),
                                        'deleteParams' => [],
                                        'statusUrl'    => auth()->user()->role === 'admin' && Route::has('admin.incoming.sub_parts.edit_approval') ? route('admin.incoming.sub_parts.edit_approval', $cs->id) : null,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="24" class="text-center py-4 text-muted">
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
    @php $bulkApproveRoute = route('incoming.sub_parts.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 0;">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-primary" id="editModalLabel" style="font-size: 1.1rem;">
                        <i class="fas fa-edit mr-2"></i>Edit Checksheet Incoming Sub-Part
                    </h5>
                    <button type="button" class="close text-gray-500 hover:text-gray-800" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light px-4 py-4" id="editModalBody" style="max-height: 85vh; overflow-y: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Status Approval -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 0;">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-primary" id="statusModalLabel" style="font-size: 1.1rem;">
                        <i class="fas fa-user-check mr-2"></i>Edit Status Approval Incoming Sub-Part
                    </h5>
                    <button type="button" class="close text-gray-500 hover:text-gray-800" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light px-4 py-4" id="statusModalBody" style="max-height: 85vh; overflow-y: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
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
                    $rejectType == 'kashift' && ((in_array($user->role, ['kashift', 'kashift_qc']) || $isAdmin || $isSpvJakarta || $isKaruJakarta) &&
                        (empty($cs->kashift_qc) || $cs->kashift_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'supervisor' && (($user->role === 'supervisor' || $isAdmin) && (empty($cs->supervisor_qc) ||
                        $cs->supervisor_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'asst_manager' && ((in_array($user->role, ['asst_manager', 'asst_manager_qc']) || $isAdmin) && (empty($cs->asst_manager_qc) ||
                        $cs->asst_manager_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'manager' && ((in_array($user->role, ['manager', 'manager_qc']) || $isAdmin) && (empty($cs->manager_qc) || $cs->manager_qc
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
                                action="{{ route('incoming.sub_parts.reject', array_merge(['id' => $cs->id, 'type' => $rejectType], request()->all())) }}"
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
    <script>
        $(document).ready(function() {
            // Edit Modal
            $(document).on('click', '.btn-edit-modal', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                $('#editModal').modal('show');
                $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div><p class="mt-2 text-muted small">Memuat data checksheet...</p></div>');
                
                $.get(url, function(data) {
                    $('#editModalBody').html(data);
                }).fail(function() {
                    $('#editModalBody').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
                });
            });

            // Status Approval Modal
            $(document).on('click', '.btn-status-modal', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                $('#statusModal').modal('show');
                $('#statusModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.get(url, function(data) {
                    $('#statusModalBody').html(data);
                }).fail(function() {
                    $('#statusModalBody').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
                });
            });

            // Delete Confirm via AJAX
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data checksheet Incoming Sub-Part akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (form.length && form[0]) {
                            Swal.fire({
                                title: 'Menghapus...',
                                text: 'Mohon tunggu sebentar',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading(); }
                            });

                            $.ajax({
                                url: form.attr('action'),
                                type: 'POST',
                                data: form.serialize(),
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil!',
                                            text: response.message || 'Data berhasil dihapus.',
                                            timer: 1200,
                                            showConfirmButton: false
                                        }).then(() => {
                                            window.location.href = response.redirect || window.location.href;
                                        });
                                    } else {
                                        window.location.reload();
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                                }
                            });
                        }
                    }
                });
            });

            // Prevent dropdown menu from being clipped inside .table-responsive
            $(document).on('show.bs.dropdown', '.table-responsive .dropdown', function () {
                var $dropdown = $(this);
                var $menu = $dropdown.children('.dropdown-menu');
                if (!$menu.length) return;

                $menu.data('parent', $dropdown);
                $('body').append($menu);

                $menu.css({
                    'display': 'block',
                    'min-width': '180px',
                    'position': 'absolute',
                    'z-index': '1095',
                    'margin': '0'
                });

                var eOffset = $dropdown.offset();
                var btnWidth = $dropdown.outerWidth();
                var btnHeight = $dropdown.outerHeight();
                var menuWidth = $menu.outerWidth() || 180;
                var menuHeight = $menu.outerHeight() || 250;

                var windowScrollTop = $(window).scrollTop();
                var windowHeight = $(window).height();
                var windowBottom = windowScrollTop + windowHeight;

                var top = eOffset.top + btnHeight + 2;
                var left = eOffset.left + btnWidth - menuWidth;

                if (top + menuHeight > windowBottom - 10 && eOffset.top - menuHeight > windowScrollTop + 10) {
                    top = eOffset.top - menuHeight - 2;
                }

                if (left < 10) left = 10;

                $menu.css({
                    'top': top + 'px',
                    'left': left + 'px'
                });
            });

            $(document).on('hide.bs.dropdown', '.table-responsive .dropdown', function () {
                var $dropdown = $(this);
                var $menu = $('body').children('.dropdown-menu').filter(function () {
                    return $(this).data('parent') && $(this).data('parent').is($dropdown);
                });

                if ($menu.length) {
                    $menu.css({
                        'display': '',
                        'min-width': '',
                        'position': '',
                        'z-index': '',
                        'top': '',
                        'left': '',
                        'margin': ''
                    });
                    $dropdown.append($menu);
                }
            });
        });
    </script>
@endpush

@push('scripts')
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script>
        $(document).ready(function() {
            if (typeof initItemSearch === 'function') {
                initItemSearch('filterItem', { placeholder: 'Ketik Sub-Part Name...', maxResults: 50 });
            }
        });
    </script>
@endpush
