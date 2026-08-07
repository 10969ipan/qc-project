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

    /* Smart Filter Dropdown Style (In-Process Pattern) */
    .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
    .custom-filter-wrapper .ips-input { padding: 4px 20px 4px 8px; font-size: 0.75rem; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: calc(1.5em + 0.5rem + 2px); }
    .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
    .custom-filter-wrapper { position: relative; top: -1px; }
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
    @endphp

    <!-- Header Dokumen IPP (Desain Selaras In-Process) -->
    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                            LAPORAN DATA CHECKSHEET INCOMING PART
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">QC-KRW-F-0210</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">01/01/2026</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">0</td>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            @if(request('view_mode') === 'verifikasi')
                <h6 class="m-0 font-weight-bold text-gray-800">Data Hasil Verifikasi Incoming Part</h6>
            @else
                <h6 class="m-0 font-weight-bold text-gray-800">Data Masuk Incoming Part</h6>
            @endif
        </div>
        <div class="card-body">
            <!-- Filter Bar Terpadu (Action Bar Selaras In-Process) -->
            <!-- Filter Bar Terpadu (Action Bar Selaras Sub Assy & In-Process) -->
            <form action="{{ route('incoming.parts.index') }}" method="GET"
                class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 8px; overflow-x: auto; white-space: nowrap;" id="filterFormIncomingPart">
                
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
                            @foreach($initials ?? [] as $initial)
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
                            @foreach($customers ?? [] as $customer)
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

                <!-- Field: QR Raw (Khusus Data Hasil Verifikasi) -->
                @if(request('view_mode') === 'verifikasi')
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
                @endif

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
                    <a href="{{ route('incoming.parts.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if(request('view_mode') !== 'verifikasi')
                        <a href="{{ route('incoming.parts.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'verification', 'plant' => request('plant')])) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold" title="Data Hasil Verifikasi"
                            style="background-color: #6f42c1; color: white;">
                            Hasil Verifikasi
                        </a>
                    @else
                        <a href="{{ route('incoming.parts.index', ['plant' => request('plant')]) }}"
                            class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold" title="Kembali ke Data Regular"
                            style="background-color: #6c757d; color: white;">
                            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
                        </a>
                    @endif
                    @if($canExport)
                    <a href="{{ route('incoming.parts.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('incoming.parts.print', request()->query()) }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print Laporan"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
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
                            <th rowspan="2" class="align-middle">Tgl &amp; Shift Check</th>
                            <th rowspan="2" class="align-middle">Jam (Before)</th>
                            <th rowspan="2" class="align-middle">Jam (After)</th>
                            <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Customer / Supplier</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th rowspan="2" class="align-middle">Tgl &amp; Shift Datang</th>
                                <th rowspan="2" class="align-middle">Qty Datang Awal</th>
                            @endif
                            <th rowspan="2" class="align-middle">Total Check</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th rowspan="2" class="align-middle">Qty Balance Sisa</th>
                            @endif
                            <th rowspan="2" class="align-middle">Qty Sampling</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="4" class="align-middle">Approval Status</th>
                            @endif
                            <th rowspan="2" class="align-middle">Remarks</th>
                            <th rowspan="2" class="align-middle">Action</th>
                        </tr>
                        <tr>
                            <th style="width: 60px;">Pcs</th>
                            <th style="min-width: 140px;">Jenis NG</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th style="font-size: 10px;">{{ $plantCode === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                                <th style="font-size: 10px;">Supervisor QC</th>
                                <th style="font-size: 10px;">Asst. Manager QC</th>
                                <th style="font-size: 10px;">Manager QC</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checksheets as $cs)
                            <tr>
                                <td class="align-middle text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input row-checkbox" id="check_{{ $cs->id }}" value="{{ $cs->id }}">
                                        <label class="custom-control-label" for="check_{{ $cs->id }}" style="cursor:pointer;"></label>
                                    </div>
                                </td>
                                <td class="align-middle text-nowrap">{{ $checksheets->firstItem() + $loop->index }}</td>
                                @if(request('view_mode') === 'verifikasi')
                                    <td class="align-middle text-nowrap">
                                        @if(!empty($cs->qrcode) || !empty($cs->unique_code_id) || in_array($cs->scan_method, ['hardware', 'camera']))
                                            <button type="button" class="btn btn-sm btn-primary shadow-sm btn-qr-detail py-1 px-2" 
                                                data-qr="{{ $cs->qrcode }}"
                                                data-part="{{ $cs->part_code }}"
                                                data-supplier="{{ $cs->supplier_id }}"
                                                data-qty="{{ $cs->quantity }}"
                                                data-unique="{{ $cs->unique_code_id }}"
                                                data-sap="{{ $cs->sap_code ?? '-' }}">
                                                <i class="fas fa-qrcode mr-1"></i> QR-CODE
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="align-middle text-nowrap">
                                    {{ date('d/m/Y', strtotime($cs->date)) }}
                                    <br><small class="text-muted">Shift {{ $cs->shift }}</small>
                                </td>
                                <td class="align-middle text-nowrap">
                                    {{ $cs->created_at ? $cs->created_at->copy()->subSeconds($cs->cycle_time ?? 0)->format('H:i') : '-' }}
                                </td>
                                <td class="align-middle text-nowrap">
                                    {{ $cs->created_at ? $cs->created_at->format('H:i') : '-' }}
                                </td>
                                <td class="align-middle text-nowrap">
                                    {{ $cs->cycle_time ? $cs->cycle_time . ' s' : '-' }}
                                </td>
                                <td class="align-middle text-left text-nowrap">
                                    <span class="font-weight-bold text-gray-800">{{ $cs->item->name ?? '-' }}</span><br>
                                    <small class="text-muted"><i class="fas fa-tag mr-1"></i>{{ $cs->item->part_number ?? '-' }}</small>
                                </td>
                                
                                {{-- Kolom Customer / Supplier --}}
                                <td class="align-middle text-nowrap text-gray-700">{{ $cs->item->customer ?? '-' }}</td>

                                @if(request('view_mode') !== 'verifikasi')
                                    {{-- Tgl & Shift Datang --}}
                                    <td class="align-middle text-nowrap">
                                        @if($cs->tanggal_datang)
                                            {{ date('d/m/Y', strtotime($cs->tanggal_datang)) }}
                                            @php
                                                $shiftDatangShow = $cs->arrival ? $cs->arrival->shift_datang : null;
                                            @endphp
                                            @if($shiftDatangShow)
                                                <br><small class="text-muted">Shift {{ $shiftDatangShow }}</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- Qty Kedatangan Awal --}}
                                    <td class="align-middle text-nowrap">
                                        {{ number_format($cs->arrival ? $cs->arrival->qty_datang : ($cs->lot_qty ?? 0)) }} pcs
                                    </td>
                                @endif

                                {{-- Total Check --}}
                                <td class="align-middle text-nowrap">{{ number_format($cs->total_check) }} pcs</td>

                                @if(request('view_mode') !== 'verifikasi')
                                    {{-- Qty Balance Sisa --}}
                                    <td class="align-middle text-nowrap">
                                        @php
                                            $sisaDisplay = isset($cs->qty_balance_sisa) ? $cs->qty_balance_sisa : ($cs->arrival ? $cs->arrival->qty_sisa : 0);
                                            $statusDisplay = ($sisaDisplay <= 0) ? 'COMPLETED' : 'OPEN';
                                        @endphp
                                        <span>{{ number_format($sisaDisplay) }} pcs</span>
                                        <br>
                                        <small class="text-muted">({{ $statusDisplay }})</small>
                                    </td>
                                @endif

                                {{-- Qty Sampling --}}
                                <td class="align-middle text-nowrap text-primary">{{ number_format($cs->sampling_qty ?? $cs->total_check) }} pcs</td>

                                {{-- OK & NG --}}
                                <td class="align-middle text-nowrap text-success font-weight-bold">{{ number_format($cs->total_check - $cs->total_ng) }}</td>
                                <td class="align-middle text-nowrap text-danger font-weight-bold">{{ number_format($cs->total_ng) }}</td>
                                
                                @php
                                    $defects = is_array($cs->defects) ? $cs->defects : json_decode($cs->defects, true);
                                    $validDefects = array_filter($defects ?? [], function($d) {
                                        return !empty($d['type']) || (!empty($d['qty']) && $d['qty'] > 0);
                                    });
                                @endphp
                                <td class="p-0 align-middle text-nowrap">
                                    @forelse($validDefects as $d)
                                        <div class="border-bottom py-1">{{ $d['qty'] ?? 0 }}</div>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td class="p-0 align-middle text-center text-nowrap">
                                    @forelse($validDefects as $d)
                                        <div class="border-bottom py-1">{{ $d['type'] ?? '-' }}</div>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>

                                <td class="align-middle text-nowrap">
                                    <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }} px-2 py-1">
                                        {{ $cs->judgment }}
                                    </span>
                                </td>
                                <td class="align-middle text-nowrap text-uppercase">{{ $cs->operator_initials }}</td>
                                
                                @if(request('view_mode') !== 'verifikasi')
                                    {{-- Approval Columns --}}
                                    @foreach(['kashift_qc', 'supervisor_qc', 'asst_manager_qc', 'manager_qc'] as $lvl)
                                        <td class="align-middle text-nowrap">
                                            @if($cs->$lvl == 'REJECTED')
                                                <span class="badge badge-danger">REJ</span>
                                            @elseif($cs->$lvl)
                                                <span class="badge badge-success">APP</span>
                                                <br><small class="text-muted" style="font-size: 0.55rem;">{{ $cs->$lvl }}</small>
                                            @else
                                                <span class="badge badge-warning">PEN</span>
                                            @endif
                                        </td>
                                    @endforeach
                                @endif

                                <td class="align-middle text-left small" style="min-width: 150px; white-space: normal;">{{ $cs->remarks ?? '-' }}</td>

                                <td class="align-middle text-nowrap">
                                    @if($loop->first)
                                        @include('partials.bulk_approve_button')
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
                                <td colspan="26" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-gray-400"></i>
                                    Data checksheet tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Sticky Bottom Pagination Bar -->
            <div class="pagination-container d-flex justify-content-between align-items-center mt-3">
                <div class="small text-muted font-weight-bold">
                    Menampilkan {{ $checksheets->firstItem() ?? 0 }} - {{ $checksheets->lastItem() ?? 0 }} dari total {{ $checksheets->total() ?? 0 }} data
                </div>
                <div>
                    {{ $checksheets->withQueryString()->links() }}
                </div>
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
                <button type="button" class="btn btn-success btn-sm shadow-sm rounded-pill px-3" id="btnBulkApprove">
                    <i class="fas fa-check-circle mr-1"></i> Approve Terpilih
                </button>
            @endif
        </div>
    </div>

    @php $bulkApproveRoute = route('incoming.parts.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')

@endsection

@push('scripts')
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

        $(document).on('click', '#btnBulkApprove', function () {
            const selectedIds = $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

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
                            approval_type: "{{ auth()->user()->role }}"
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
            });
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
    });
</script>
@endpush
