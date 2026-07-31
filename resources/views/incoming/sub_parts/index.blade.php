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
    
    /* Robust fix for rowspan headers to match sum of row heights */
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        top: 0 !important;
        height: 86px !important; /* 48 + 38 */
        z-index: 106 !important;
    }
</style>
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        // Resolve menu ID for permission checks
        $currentMenu = \App\Models\AppMenu::where('route', 'incoming.sub_parts.index')->first();
        $menuId = $currentMenu ? $currentMenu->id : null;
        $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;

        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_sub_parts', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0212',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
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
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            @if(request('view_mode') === 'verifikasi')
                <h6 class="m-0 font-weight-bold" style="color: #6f42c1;"><i class="fas fa-clipboard-check mr-2"></i>Data Hasil Verifikasi Incoming Sub-Part</h6>
            @else
                <h6 class="m-0 font-weight-bold text-primary">Data Masuk Incoming Sub-Part</h6>
            @endif
        </div>
        <div class="card-body">
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

                <!-- Field: Tgl Datang -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tgl Datang:</label>
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
                    @if($canExport)
                    <a href="{{ route('incoming.sub_parts.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('incoming.sub_parts.print', request()->query()) }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
                    </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover text-center" width="100%" cellspacing="0" id="checksheetTable">
                    <thead class="bg-light">
                        <tr class="align-middle">
                            <th rowspan="2">No</th>
                            <th rowspan="2">Tanggal Check</th>
                            <th rowspan="2">Jam (Before)</th>
                            <th rowspan="2">Jam (After)</th>
                            <th rowspan="2">Cycle Time (s)</th>
                            <th rowspan="2">Sub-Part Name</th>
                            <th rowspan="2">Tgl Datang</th>
                            <th rowspan="2">Lot/Batch</th>
                            <th colspan="2">Qty (Pcs)</th>
                            <th rowspan="2">Result</th>
                            <th colspan="2">Detail NG</th>
                            <th rowspan="2">QC</th>
                            <th colspan="4">Approval</th>
                            <th rowspan="2">Description</th>
                            <th rowspan="2">Action</th>
                        </tr>
                        <tr>
                            <th>Total (Pcs)</th>
                            <th>Sampling Size</th>
                            <th>Pcs</th>
                            <th>Jenis</th>
                            <th style="font-size: 10px;">{{ $plantCode === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                            <th style="font-size: 10px;">Supervisor QC</th>
                            <th style="font-size: 10px;">Asst. Manager QC</th>
                            <th style="font-size: 10px;">Manager QC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checksheets as $cs)
                            @php
                                $user = auth()->user();
                                $isAdmin = $user->role === 'admin';
                                $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                                $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                $canApproveKashift = ($user->role === 'kashift' || $isAdmin || $isSpvJakarta ||
                                    $isKaruJakarta) && (!$cs->kashift_qc || $cs->kashift_qc === 'REJECTED');
                                $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) &&
                                    (!$cs->supervisor_qc || $cs->supervisor_qc === 'REJECTED');
                                $canApproveAsst = ($user->role === 'asst_manager' || $isAdmin) &&
                                    (!$cs->asst_manager_qc || $cs->asst_manager_qc === 'REJECTED');
                                $canApproveManager = ($user->role === 'manager' || $isAdmin) && (!$cs->manager_qc ||
                                    $cs->manager_qc === 'REJECTED');
                            @endphp
                            <tr>
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle">{{ date('d/m/Y', strtotime($cs->date)) }}</td>
                                <td class="align-middle">{{ $cs->created_at->copy()->subSeconds($cs->cycle_time ?? 0)->format('H:i') }}</td>
                                <td class="align-middle">{{ $cs->created_at->format('H:i') }}</td>
                                <td class="align-middle">{{ $cs->cycle_time ?? '-' }}</td>
                                <td class="align-middle">{{ $cs->item->name }}</td>
                                <td class="align-middle">{{ date('d/m/Y', strtotime($cs->tanggal_datang)) }}</td>
                                <td class="align-middle">{{ $cs->lot_batch_number }}</td>
                                <td class="align-middle font-weight-bold">{{ (float) $cs->quantity }}</td>
                                <td class="align-middle">{{ (float) $cs->sampling_size_pcs }}</td>
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
                                <td class="p-0 align-middle">
                                    @if(count($pcsLines) > 0)
                                        @foreach($pcsLines as $q)
                                            <div class="border-bottom py-1">{{ $q }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="p-0 align-middle">
                                    @if(count($nameLines) > 0)
                                        @foreach($nameLines as $n)
                                            <div class="border-bottom py-1">{{ $n }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="align-middle text-uppercase">{{ $cs->operator_initials }}</td>

                                {{-- Kashift QC --}}
                                <td class="align-middle text-center">
                                    @if($cs->kashift_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh {{ getRejectorName($cs->rejection_remarks) }}</small>
                                    @elseif($cs->kashift_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $cs->kashift_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($cs->kashift_approved_at)
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($cs->kashift_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Supervisor QC --}}
                                <td class="align-middle text-center">
                                    @if($cs->supervisor_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh {{ getRejectorName($cs->rejection_remarks) }}</small>
                                    @elseif($cs->supervisor_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $cs->supervisor_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($cs->supervisor_approved_at)
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($cs->supervisor_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Asst Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($cs->asst_manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh {{ getRejectorName($cs->rejection_remarks) }}</small>
                                    @elseif($cs->asst_manager_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $cs->asst_manager_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($cs->asst_manager_approved_at)
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($cs->asst_manager_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($cs->manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh {{ getRejectorName($cs->rejection_remarks) }}</small>
                                    @elseif($cs->manager_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $cs->manager_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($cs->manager_approved_at)
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($cs->manager_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

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
                                <td class="align-middle text-center text-nowrap no-export" style="min-width: 350px;">
                                    @if($loop->first)
                                        @include('partials.bulk_approve_button')
                                    @endif
                                    @if($canApproveKashift)
                                        <form action="{{ route('incoming.sub_parts.approve', array_merge(['id' => $cs->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kashift)" style="min-width: 110px;">
                                                <i class="fas fa-check"></i> Approve{{ ($user->role === 'admin') ? ' KS' : (($isSpvJakarta || $isKaruJakarta) ? '' : '') }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kashift)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}kashift" style="min-width: 110px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                    @if($canApproveSupervisor)
                                        <form action="{{ route('incoming.sub_parts.approve', array_merge(['id' => $cs->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV)" style="min-width: 110px;">
                                                <i class="fas fa-check"></i> Approve{{ (auth()->user()->role === 'admin') ? ' SPV' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}supervisor" style="min-width: 110px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                    @if($canApproveAsst)
                                        <form action="{{ route('incoming.sub_parts.approve', array_merge(['id' => $cs->id, 'type' => 'asst_manager'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (AM)" style="min-width: 110px;">
                                                <i class="fas fa-check"></i> Approve{{ (auth()->user()->role === 'admin') ? ' AM' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (AM)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}asst_manager" style="min-width: 110px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                    @if($canApproveManager)
                                        <form action="{{ route('incoming.sub_parts.approve', array_merge(['id' => $cs->id, 'type' => 'manager'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (MGR)" style="min-width: 110px;">
                                                <i class="fas fa-check"></i> Approve{{ (auth()->user()->role === 'admin') ? ' MGR' : '' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (MGR)" data-toggle="modal" data-target="#rejectModal{{ $cs->id }}manager" style="min-width: 110px;">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif

                                    <div class="btn-group">
                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('admin.incoming.sub_parts.edit_approval', $cs->id) }}"
                                                class="btn btn-info btn-xs mx-1 btn-status-modal" data-id="{{ $cs->id }}" title="Status Approval">
                                                <i class="fas fa-user-check"></i>
                                            </a>
                                        @endif
                                        @if(!in_array(auth()->user()->role, ['inspector']))
                                            <a href="{{ route('incoming.sub_parts.edit', $cs->id) }}"
                                                class="btn btn-warning btn-xs mx-1 btn-edit-modal" data-id="{{ $cs->id }}" title="Edit Checksheet">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('incoming.sub_parts.destroy', $cs->id) }}" method="POST"
                                                class="d-inline form-delete">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-xs mx-1 btn-delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="19" class="text-center">Data tidak ditemukan.</td>
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

@endsection

@push('scripts')
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Edit Modal
            $('.btn-edit-modal').click(function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                $('#editModal').modal('show');
                $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.get(url, function(data) {
                    $('#editModalBody').html(data);
                }).fail(function() {
                    $('#editModalBody').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
                });
            });

            // Status Approval Modal
            $('.btn-status-modal').click(function(e) {
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

            // Delete Confirm
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data checksheet Incoming Sub Part akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (form.length && form[0]) {
                            form[0].submit();
                        }
                    }
                });
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
