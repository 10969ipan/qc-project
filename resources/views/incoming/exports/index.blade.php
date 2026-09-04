@extends('layouts.admin')

@section('title', 'Incoming Export')

@section('content')
<style>
    .table-responsive {
        max-height: calc(100vh - 220px) !important;
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
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 6px 12px !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #e2e8f0;
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
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
        height: 86px !important;
        z-index: 106 !important;
    }
</style>

    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        $approvalOrder = $approvalOrder ?? ['kashift', 'supervisor', 'asst_manager', 'manager'];

        // Resolve menu ID for permission checks
        $currentMenu = \App\Models\AppMenu::where('route', 'incoming.exports.index')->first();
        $menuId = $currentMenu ? $currentMenu->id : null;
        $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
        $canEdit = $menuId ? auth()->user()->hasPermission($menuId, 'edit') : true;
        $canDelete = $menuId ? auth()->user()->hasPermission($menuId, 'delete') : true;

        $docHeader = $docHeader ?? \App\Models\GeneralSetting::getDocHeader('incoming_exports', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0215',
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
                                LAPORAN DATA INCOMING EXPORT
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
        <form action="{{ route('incoming.exports.index') }}" method="GET" class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm" style="gap: 8px; overflow-x: auto; white-space: nowrap;" id="filterFormIncoming">
            <input type="hidden" name="plant" value="{{ request('plant') }}">
            @if(request('view_mode'))
                <input type="hidden" name="view_mode" value="{{ request('view_mode') }}">
            @endif
            @if(request('entry_method'))
                <input type="hidden" name="entry_method" value="{{ request('entry_method') }}">
            @endif

            <!-- Field: Part -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cari:</label>
                <div style="width: 200px;" class="custom-filter-wrapper">
                    <select name="item_id" id="filterItem" class="form-control form-control-sm border-0 shadow-sm d-none">
                        <option value="">Ketik Material / Part No...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" data-name="{{ $item->name }}" data-part-number="{{ $item->part_number }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} {{ $item->part_number ? '- '.$item->part_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tgl:</label>
                <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0" style="width: 120px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                    <span class="px-1 text-gray-500 small">-</span>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0" style="width: 120px; font-size: 0.75rem;" value="{{ request('end_date') }}">
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

            <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                <style>
                    .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                    .custom-filter-wrapper .ips-input { padding: 4px 20px 4px 8px; font-size: 0.75rem; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: calc(1.5em + 0.5rem + 2px); }
                    .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
                    .custom-filter-wrapper { position: relative; top: -1px; }
                </style>
                <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Cari Data"><i class="fas fa-search fa-sm"></i></button>
                <a href="{{ route('incoming.exports.index', ['plant' => request('plant')]) }}" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter"><i class="fas fa-undo fa-sm"></i></a>
                @if(request('view_mode') !== 'verifikasi')
                    <a href="{{ route('incoming.exports.index', array_merge(request()->except('view_mode', 'page'), ['view_mode' => 'verifikasi', 'entry_method' => 'verification', 'plant' => request('plant')])) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold" title="Data Hasil Verifikasi"
                        style="background-color: #6f42c1; color: white;">
                        <i class="fas fa-clipboard-check fa-sm mr-1"></i> Hasil Verifikasi
                    </a>
                @else
                    <a href="{{ route('incoming.exports.index', ['plant' => request('plant')]) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader font-weight-bold" title="Kembali ke Data Regular"
                        style="background-color: #6c757d; color: white;">
                        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
                    </a>
                @endif
                @include('partials.bulk_approve_button')
                <a href="{{ route('incoming.exports.print', request()->query()) }}"
                    target="_blank"
                    class="btn btn-sm shadow-sm rounded-pill px-3 no-loader btn-print-direct" title="Print"
                    style="background-color: #17a589; color: white;">
                    <i class="fas fa-print fa-sm"></i> Cetak
                </a>
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
                            <th rowspan="{{ $rs }}" class="align-middle">Item Part / Part No</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Supplier</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Tanggal Delivery</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Lot Qty</th>
                            <th rowspan="{{ $rs }}" class="align-middle">Total Check</th>
                            <th rowspan="{{ $rs }}" class="align-middle">OK</th>
                            <th rowspan="{{ $rs }}" class="align-middle">NG</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="2" class="align-middle">Detail NG</th>
                            @endif
                            <th rowspan="{{ $rs }}" class="align-middle">Judgment</th>
                            @if(request('view_mode') !== 'verifikasi')
                                <th colspan="4" class="align-middle">Approval Status</th>
                            @endif
                            <th rowspan="{{ $rs }}" class="align-middle">Description</th>
                            <th rowspan="{{ $rs }}" class="no-export align-middle">Actions</th>
                        </tr>
                        @if(request('view_mode') !== 'verifikasi')
                            <tr class="text-center">
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
                                <td class="align-middle text-nowrap">{{ date('d-m-Y', strtotime($cs->tanggal_delivery)) }}</td>
                                <td class="align-middle font-weight-bold text-dark">{{ $cs->lot_qty }}</td>
                                <td class="align-middle font-weight-bold text-dark">{{ $cs->total_check }}</td>
                                <td class="align-middle text-success font-weight-bold">{{ $cs->total_check - $cs->total_ng }}</td>
                                <td class="align-middle text-danger font-weight-bold">{{ $cs->total_ng }}</td>
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

                                <td class="align-middle text-left small" style="min-width: 150px; white-space: normal;">
                                    @if($cs->rejection_remarks)
                                        <div class="text-danger font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i> REJECTED
                                        </div>
                                        <small class="text-muted">{{ $cs->rejection_remarks }}</small>
                                    @else
                                        {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($cs->remarks)) !!}
                                    @endif
                                </td>
                                <td class="align-middle text-center text-nowrap no-export" style="min-width: 160px;">
                                    @if($loop->first)
                                        @include('partials.bulk_approve_button')
                                    @endif

                                    @if($canApproveKashift)
                                        <form action="{{ route('incoming.exports.approve', array_merge(['id' => $cs->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline ajax-form">
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
                                        <form action="{{ route('incoming.exports.approve', array_merge(['id' => $cs->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline ajax-form">
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
                                        <form action="{{ route('incoming.exports.approve', array_merge(['id' => $cs->id, 'type' => 'asst_manager'], request()->all())) }}" method="POST" class="d-inline ajax-form">
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
                                        <form action="{{ route('incoming.exports.approve', array_merge(['id' => $cs->id, 'type' => 'manager'], request()->all())) }}" method="POST" class="d-inline ajax-form">
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
                                        'editUrl'      => route('incoming.exports.edit', array_merge(['id' => $cs->id], request()->all())),
                                        'deleteRoute'  => route('incoming.exports.destroy', array_merge(request()->query(), ['id' => $cs->id])),
                                        'deleteParams' => [],
                                        'statusUrl'    => auth()->user()->role === 'admin' && Route::has('admin.incoming.exports.edit_approval') ? route('admin.incoming.exports.edit_approval', $cs->id) : null,
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
            <div class="mt-4">{{ $checksheets->withQueryString()->links() }}</div>
        </div>
    </div>
    @php $bulkApproveRoute = route('incoming.exports.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')

    <!-- QR Detail Modal -->
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

    {{-- Modal Edit Incoming Export --}}
    <div class="modal fade" id="editExportModal" tabindex="-1" role="dialog" aria-labelledby="editExportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); border:0;">
                <div class="modal-header" style="background:#fff; padding: 0.75rem 1.5rem; border-radius:12px 12px 0 0; border-bottom:1px solid #e2e8f0;">
                    <h5 class="modal-title font-weight-bold" id="editExportModalLabel">
                        <i class="fas fa-edit mr-2 text-primary"></i> Edit Data Incoming Export
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background:#f8fafc; padding:1.5rem; max-height:65vh; overflow-y:auto;">
                    <div id="editExportFormContainer">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
                            <p class="mt-2 text-muted">Memuat form edit...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor/item-search.js') }}"></script>
<script>
    $(document).ready(function() {
        if (typeof initItemSearch === 'function') {
            initItemSearch('filterItem', { placeholder: 'Ketik Material / Part No...', maxResults: 50 });
        }

        // QR Detail — event delegation agar kompatibel dengan pagination
        $(document).on('click', '.btn-qr-detail', function() {
            $('#modal-qr-raw').text($(this).data('qr') || '-');
            $('#modal-qr-part').text($(this).data('part') || '-');
            $('#modal-qr-supplier').text($(this).data('supplier') || '-');
            $('#modal-qr-qty').text($(this).data('qty') || '-');
            $('#modal-qr-unique').text($(this).data('unique') || '-');
            $('#modal-qr-sap').text($(this).data('sap') || '-');
            $('#qrModal').modal('show');
        });

        // Edit Export — buka modal, load form via AJAX
        $(document).on('click', '.btn-edit-export', function() {
            const url = $(this).data('url');
            const updateUrl = $(this).data('update-url');
            $('#editExportFormContainer').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat form edit...</p></div>'
            );
            $('#editExportModal').modal('show');
            $.ajax({
                url: url,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(html) {
                    $('#editExportFormContainer').html(html);
                    $('#editExportFormContainer form').attr('action', updateUrl);
                    if (typeof $.fn.select2 !== 'undefined') {
                        $('#editExportFormContainer .select2').select2({ dropdownParent: $('#editExportModal') });
                    }
                },
                error: function() {
                    $('#editExportFormContainer').html('<p class="text-danger text-center py-4">Gagal memuat form. Silakan coba lagi.</p>');
                }
            });
        });

        // Submit form edit via AJAX
        $(document).on('submit', '#editChecksheetForm', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const btn = form.find('[type=submit]');
            const orig = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                success: function() {
                    $('#editExportModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data berhasil diperbarui.', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(orig);
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan perubahan.';
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                }
            });
        });

        // Hapus data
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menghapus...', allowOutsideClick: false });
                    Swal.showLoading();
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
