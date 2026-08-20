@extends('layouts.admin')

@section('title', 'Checksheet Sortir')

@section('content')
<style>
    .table-responsive {
        max-height: 68vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #sortirTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    
    #sortirTable td, #sortirTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }

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

    #sortirTable tbody tr:hover {
        background-color: #f1f5f9 !important;
        transition: background-color 0.2s ease;
    }

    /* Forced overrides for compact view */
    #sortirTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    #sortirTable .btn {
        min-width: 0 !important;
        padding: 0.1rem 0.3rem !important;
        font-size: 0.58rem !important;
        margin: 0px !important;
    }
    #sortirTable .badge {
        font-size: 0.58rem !important;
        padding: 0.1rem 0.3rem !important;
    }

    /* Exact sticky heights */
    #sortirTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 24px !important; 
    }
    #sortirTable > thead > tr:nth-child(2) > th {
        top: 24px !important; 
        z-index: 104 !important;
        height: 20px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    #sortirTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        top: 0 !important;
        height: 44px !important; /* 24 + 20 */
        z-index: 106 !important;
    }
</style>
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        // Resolve menu ID for permission checks
        $currentMenu = \App\Models\AppMenu::where('route', 'sortir.index')->first();
        $menuId = $currentMenu ? $currentMenu->id : null;
        $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
        $canEdit = $menuId ? auth()->user()->hasPermission($menuId, 'edit') : true;
        $canDelete = $menuId ? auth()->user()->hasPermission($menuId, 'delete') : true;

        $docHeader = \App\Models\GeneralSetting::getDocHeader('sortir', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0208',
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
                                LAPORAN DATA SORTIR
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
            <form action="{{ route('sortir.index') }}" method="GET"
                class="d-flex flex-wrap align-items-end bg-light p-2 rounded mb-2 shadow-sm"
                style="gap: 8px; overflow-x: auto;" id="filterFormSortir">

                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <!-- 1. Field: Part Name / Search -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Part Name</label>
                    <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm"
                        style="width: 150px; font-size: 0.70rem; height: 26px;" placeholder="Cari Item..."
                        value="{{ request('search') }}">
                </div>

                <!-- 2. Field: Sumber -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Sumber</label>
                    <div style="width: 120px;" class="custom-filter-wrapper">
                        <select name="source_type" class="form-control form-control-sm border-0 shadow-sm" style="font-size: 0.70rem; height: 26px;">
                            <option value="">Semua</option>
                            <option value="sub_assy" {{ request('source_type') == 'sub_assy' ? 'selected' : '' }}>Sub Assy</option>
                            <option value="in_process" {{ request('source_type') == 'in_process' ? 'selected' : '' }}>In Process</option>
                            <option value="cross_cut" {{ request('source_type') == 'cross_cut' ? 'selected' : '' }}>Cross Cut</option>
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
                    <div style="width: 95px;" class="custom-filter-wrapper">
                        <select name="shift" id="filterShift" class="form-control form-control-sm border-0 shadow-sm" style="font-size: 0.70rem; height: 26px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>

                <!-- Tombol Filter & Reset -->
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
                    <a href="{{ route('sortir.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Reset Filter">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </a>
                </div>

                <!-- Tombol Ekspor (Paling Kanan) -->
                <div class="d-flex align-items-center ml-auto" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important;">
                    @if($canExport)
                    <a href="{{ route('sortir.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-download d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm mr-1"></i> PDF
                    </a>
                    <a href="{{ route('sortir.print', request()->query()) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-print-direct d-flex align-items-center" style="background-color: #17a589; color: white; font-size: 0.68rem; height: 26px;" title="Cetak Direct">
                        <i class="fas fa-print fa-sm mr-1"></i> Cetak
                    </a>
                    @endif
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="sortirTable">
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
                            @if(in_array(auth()->user()->role, ['admin']))
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
                            <th rowspan="2" class="align-middle">Tanggal</th>
                            <th rowspan="2" class="align-middle">Shift</th>
                            <th rowspan="2" class="align-middle">Line</th>
                            <th rowspan="2" class="align-middle">Sumber</th>
                            <th rowspan="2" class="align-middle">Item Part / Part No</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Total Qty</th>
                            <th rowspan="2" class="align-middle">Sampling Qty</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial</th>

                            <th colspan="2" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            @if(!in_array(auth()->user()->role, ['inspector']))
                                <th rowspan="2" class="no-export align-middle">Actions</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="width: 60px; min-width: 60px;">Pcs</th>
                            <th style="min-width: 150px;">Jenis NG</th>
                            <th style="font-size: 10px; min-width: 120px;">{{ $plantContext === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                            <th style="font-size: 10px; min-width: 120px;">Supervisor QC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checksheets as $checksheet)
                            <tr class="text-center">
                                @if(in_array(auth()->user()->role, ['admin']))
                                    <td class="align-middle text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input row-checkbox" id="checkRow{{ $checksheet->id }}" value="{{ $checksheet->id }}">
                                            <label class="custom-control-label" for="checkRow{{ $checksheet->id }}" style="cursor:pointer;"></label>
                                        </div>
                                    </td>
                                @endif
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->shift }}</td>
                                <td class="align-middle">{{ $checksheet->line ?? '-' }}</td>
                                <td class="align-middle">
                                    @php
                                        $sourceRoute = '#';
                                        $badgeClass = 'secondary';
                                        if ($checksheet->source_type == 'sub_assy') {
                                            $sourceRoute = route('admin.checksheets.index', ['id' => $checksheet->source_id]);
                                            $badgeClass = 'warning';
                                        } elseif ($checksheet->source_type == 'in_process') {
                                            $sourceRoute = route('in_process.index', ['id' => $checksheet->source_id]);
                                            $badgeClass = 'info';
                                        } elseif ($checksheet->source_type == 'cross_cut') {
                                            $sourceRoute = route('cross_cut.index', ['id' => $checksheet->source_id]);
                                            $badgeClass = 'primary';
                                        }
                                    @endphp
                                    <a href="{{ $sourceRoute }}" class="badge badge-{{ $badgeClass }} p-2"
                                        title="Lihat Data Sumber (NG)">
                                        <i class="fas fa-external-link-alt mr-1"></i>
                                        {{ strtoupper(str_replace('_', ' ', $checksheet->source_type)) }}
                                    </a>
                                </td>
                                <td class="align-middle text-left text-nowrap">
                                    <span class="font-weight-bold text-gray-800">{{ $checksheet->item->name ?? '-' }}</span><br>
                                    <small class="text-muted"><i class="fas fa-tag mr-1"></i>{{ $checksheet->item->part_number ?? '-' }}</small>
                                </td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->total_qty }}</td>
                                <td class="align-middle">{{ $checksheet->sampling_qty }}</td>
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

                                <td class="align-middle">
                                    <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                                        {{ $checksheet->judgment }}
                                    </span>
                                </td>
                                <td class="align-middle text-uppercase">{{ $checksheet->operator_initials }}</td>

                                {{-- Kashift QC --}}
                                <td class="align-middle text-center" style="font-size: 0.65rem;">
                                    @if($checksheet->kashift_qc === 'REJECTED')
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><span class="text-muted" style="font-size: 0.62rem; line-height: 1.2;">oleh {{ getRejectorName($checksheet->rejection_remarks) }}</span>
                                        @if($checksheet->kashift_qc_time)
                                            <br><span class="text-muted" style="font-size: 0.62rem; line-height: 1.2;">{{ $checksheet->kashift_qc_time->format('d/m/Y H:i') }}</span>
                                        @endif
                                    @elseif($checksheet->kashift_qc)
                                        <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><span class="text-muted" style="font-size: 0.62rem; line-height: 1.2;">oleh {{ $checksheet->kashift_qc }}</span>
                                        @if($checksheet->kashift_qc_time)
                                            <br><span class="text-muted" style="font-size: 0.62rem; line-height: 1.2;">{{ $checksheet->kashift_qc_time->format('d/m/Y H:i') }}</span>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                </td>

                                {{-- Supervisor QC --}}
                                <td class="align-middle text-center" style="font-size: 0.65rem;">
                                    @if($checksheet->supervisor_qc === 'REJECTED')
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><span class="text-muted" style="font-size: 0.62rem; line-height: 1.2;">oleh {{ getRejectorName($checksheet->rejection_remarks) }}</span>
                                        @if($checksheet->supervisor_qc_time)
                                            <br><span class="text-muted" style="font-size: 0.62rem; line-height: 1.2;">{{ $checksheet->supervisor_qc_time->format('d/m/Y H:i') }}</span>
                                        @endif
                                    @elseif($checksheet->supervisor_qc)
                                        <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><span class="text-muted" style="font-size: 0.62rem; line-height: 1.2;">oleh {{ $checksheet->supervisor_qc }}</span>
                                        @if($checksheet->supervisor_qc_time)
                                            <br><span class="text-muted" style="font-size: 0.62rem; line-height: 1.2;">{{ $checksheet->supervisor_qc_time->format('d/m/Y H:i') }}</span>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                </td>

                                <td class="align-middle text-left">
                                    @if($checksheet->next_proses)
                                        <span class="badge badge-warning">{{ $checksheet->next_proses }}</span><br>
                                    @endif
                                    {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> CLOSE</span>', e($checksheet->remarks ?? '-')) !!}
                                </td>

                                @if(!in_array(auth()->user()->role, ['inspector']))
                                    <td class="align-middle text-center text-nowrap no-export" style="{{ auth()->user()->role === 'admin' ? 'width: 50px;' : 'min-width: 170px;' }}">
                                        @php
                                            $user = auth()->user();
                                            $isAdmin = $user->role === 'admin';
                                            $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                                            $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                            $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                            $canApproveKashift = ($user->role === 'kashift' || $isAdmin || $isSpvJakarta || $isKaruJakarta) && (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED');
                                            $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) && (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED') && ($checksheet->kashift_qc && $checksheet->kashift_qc !== 'REJECTED');

                                            $plantContext = strtolower(request('plant') ?? optional($user->plant)->code ?? 'karawang');
                                            $kashiftLabel = ($plantContext === 'jakarta') ? 'Kepala Regu' : 'Kashift QC';
                                            $kashiftAcronym = ($plantContext === 'jakarta') ? '' : ' KS';

                                            $showEdit = $canEdit;
                                            $showDel = $canDelete;
                                            $statusUrl = $isAdmin ? route('sortir.edit_approval', array_merge(['id' => $checksheet->id], request()->all())) : null;
                                        @endphp

                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif

                                        {{-- Non-Admin Roles: Show Inline Approve/Reject Button for User's Own Role --}}
                                        @if(!$isAdmin)
                                            @if(($user->role === 'kashift' || $isSpvJakarta || $isKaruJakarta) && $canApproveKashift)
                                                <form action="{{ route('sortir.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <button type="submit" class="btn btn-success btn-sm m-1" title="Approve ({{ $kashiftLabel }})">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift" title="Reject ({{ $kashiftLabel }})">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @elseif(($user->role === 'supervisor' && !$isJakarta) && $canApproveSupervisor)
                                                <form action="{{ route('sortir.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())) }}" method="POST" class="d-inline ajax-form">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Supervisor)">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1" data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor" title="Reject (Supervisor)">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                        @endif

                                        {{-- Standard 3-Dot Action Dropdown Menu --}}
                                        @include('partials.action_dropdown', [
                                            'canEdit'        => $showEdit,
                                            'canDelete'      => $showDel,
                                            'editUrl'        => route('sortir.edit', array_merge(['id' => $checksheet->id], request()->all())),
                                            'deleteRoute'    => route('sortir.destroy', ['id' => $checksheet->id]),
                                            'deleteParams'   => request()->all(),
                                            'statusUrl'      => $statusUrl,
                                            'canApproveKashift' => $canApproveKashift,
                                            'approveKashiftUrl' => route('sortir.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())),
                                            'rejectKashiftModalTarget' => '#rejectModal' . $checksheet->id . 'kashift',
                                            'canApproveSupervisor' => $canApproveSupervisor,
                                            'approveSupervisorUrl' => route('sortir.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())),
                                            'rejectSupervisorModalTarget' => '#rejectModal' . $checksheet->id . 'supervisor',
                                            'kashiftLabel' => $kashiftLabel,
                                            'kashiftAcronym' => $kashiftAcronym,
                                        ])
                                    </td>
                                @endif
                            </tr>

                            <!-- Modal Penolakan -->
                            @foreach(['kashift', 'supervisor'] as $rejectType)
                                @php
                                    $isAdmin = auth()->user()->role === 'admin';
                                    $user = auth()->user();
                                    $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                                    $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                    $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                    $canReject = false;
                                    if ($rejectType == 'kashift' && ($isAdmin || $user->role == 'kashift' || $isSpvJakarta || $isKaruJakarta))
                                        $canReject = true;
                                    elseif ($rejectType == 'supervisor' && ($isAdmin || auth()->user()->role == 'supervisor'))
                                        $canReject = true;
                                @endphp
                                @if($canReject)
                                    <div class="modal fade" id="rejectModal{{ $checksheet->id }}{{ $rejectType }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Reject Sortir Checksheet - {{ ucfirst($rejectType) }}</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('sortir.reject', array_merge(['id' => $checksheet->id, 'type' => $rejectType], request()->all())) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Alasan Rejection:</label>
                                                            <textarea name="rejection_remarks" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger btn-confirm-reject">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pagination-container">
                {{ $checksheets->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data Sortir</h5>
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
@endsection

@push('scripts')
    <script src="{{ asset('js/checksheet/sortir.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initSortirIndex();
        });

        // Auto-submit filter
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('filterFormSortir');
            if (!form) return;

            function debounce(fn, delay) {
                var timer;
                return function () { clearTimeout(timer); timer = setTimeout(fn, delay); };
            }

            var searchInput = form.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(function () { form.submit(); }, 500));
            }

            form.querySelectorAll('input[type="date"], select').forEach(function (el) {
                el.addEventListener('change', function () { form.submit(); });
            });
        });
    </script>

    <!-- Script Cetak Langsung (Direct Silent Print) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-print-direct').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var printUrl = this.getAttribute('href');
                    var iframe = document.createElement('iframe');
                    iframe.style.position = 'fixed';
                    iframe.style.right = '0';
                    iframe.style.bottom = '0';
                    iframe.style.width = '0';
                    iframe.style.height = '0';
                    iframe.style.border = '0';
                    iframe.src = printUrl;
                    document.body.appendChild(iframe);
                    iframe.onload = function() {
                        try {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                        } catch (err) {
                            console.error('Print iframe error:', err);
                            window.open(printUrl, '_blank');
                        }
                        setTimeout(function() {
                            document.body.removeChild(iframe);
                        }, 60000);
                    };
                });
            });
        });
    </script>

    @php $bulkApproveRoute = route('sortir.bulk_approve'); @endphp
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

                if (checkedCount > 0) {
                    bulkMenu.fadeIn(200);
                } else {
                    bulkMenu.fadeOut(200);
                }

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
                                url: '{{ route("sortir.bulk_destroy") }}',
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
    @endif
@endpush
