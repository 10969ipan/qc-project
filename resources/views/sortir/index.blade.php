@extends('layouts.admin')

@section('title', 'Checksheet Sortir')

@section('content')
<style>
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #checksheetTable, #sortirTable {
        border-collapse: collapse !important;
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
        font-size: 0.68rem !important;
        padding: 4px 6px !important;
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th,
    #sortirTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 6px 12px !important; /* Wider padding so it's not cramped sideways */
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 2px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important; /* Force all headers to be side-by-side */
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export,
    #sortirTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    #checksheetTable .btn,
    #sortirTable .btn {
        min-width: 0 !important; /* Overrides 110px inline style */
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
    #checksheetTable .badge,
    #sortirTable .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    /* Exact sticky heights since headers no longer wrap */
    #checksheetTable > thead > tr:nth-child(1) > th,
    #sortirTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 35px !important; 
    }
    #checksheetTable > thead > tr:nth-child(2) > th,
    #sortirTable > thead > tr:nth-child(2) > th {
        top: 35px !important; 
        z-index: 104 !important;
        height: 30px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"],
    #sortirTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        height: 65px !important; 
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
                            HASIL DATA SORTIR
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? 'QC-JKT-F-034/0' : 'QC-KRW-F-0213' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? '18.02.2022' : '25/03/2015' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? '0 / 30-Dec-99' : '3 / 22/12/2025' }}
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
        <div class="card-body">
            <form action="{{ route('sortir.index') }}" method="GET"
                class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 10px;" id="filterFormSortir">

                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Cari:</label>
                    <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm"
                        style="width: 160px; border-radius: 0.35rem;" placeholder="Nama item..."
                        value="{{ request('search') }}">
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Sumber:</label>
                    <select name="source_type" class="form-control form-control-sm border-0 shadow-sm"
                        style="border-radius: 0.35rem;">
                        <option value="">Semua</option>
                        <option value="sub_assy" {{ request('source_type') == 'sub_assy' ? 'selected' : '' }}>Sub Assy</option>
                        <option value="in_process" {{ request('source_type') == 'in_process' ? 'selected' : '' }}>In Process</option>
                        <option value="cross_cut" {{ request('source_type') == 'cross_cut' ? 'selected' : '' }}>Cross Cut</option>
                    </select>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Shift:</label>
                    <select name="shift" class="form-control form-control-sm border-0 shadow-sm"
                        style="border-radius: 0.35rem; width: 100px;">
                        <option value="">Semua</option>
                        <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                        <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                        <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                    </select>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Dari:</label>
                    <input type="date" name="start_date" class="form-control form-control-sm border-0 shadow-sm"
                        style="border-radius: 0.35rem;" value="{{ request('start_date') }}">
                </div>
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Sampai:</label>
                    <input type="date" name="end_date" class="form-control form-control-sm border-0 shadow-sm"
                        style="border-radius: 0.35rem;" value="{{ request('end_date') }}">
                </div>

                <div class="ml-auto d-flex" style="gap: 5px;">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Cari Data">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                    <a href="{{ route('sortir.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if($canExport)
                    <a href="{{ route('sortir.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('sortir.print', request()->query()) }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
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
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal</th>
                            <th rowspan="2" class="align-middle">Shift</th>
                            <th rowspan="2" class="align-middle">Line</th>
                            <th rowspan="2" class="align-middle">Sumber</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Part No</th>
                            <th rowspan="2" class="align-middle">Total Qty</th>
                            <th rowspan="2" class="align-middle">Sampling Qty</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial</th>

                            <th colspan="2" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle">Keterangan</th>
                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'karu_qc']))
                                <th rowspan="2" class="no-export align-middle">Aksi</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="width: 5%">Pcs</th>
                            <th>Jenis NG</th>
                            <th style="font-size: 10px;">{{ $plantContext === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}
                            </th>
                            <th style="font-size: 10px;">Supervisor QC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checksheets as $checksheet)
                            <tr class="text-center">
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
                                <td class="align-middle text-nowrap">{{ $checksheet->item->name ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
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

                                <td class="text-center align-middle p-0">
                                    @if(count($pcsLines) > 0)
                                        @foreach($pcsLines as $index => $qty)
                                            <div class="{{ $index < count($pcsLines) - 1 ? 'border-bottom' : '' }} py-1">
                                                <small class="text-danger font-weight-bold">{{ $qty }}</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="py-1">-</div>
                                    @endif
                                </td>
                                <td class="text-center align-middle p-0">
                                    @if(count($nameLines) > 0)
                                        @foreach($nameLines as $index => $name)
                                            <div class="{{ $index < count($nameLines) - 1 ? 'border-bottom' : '' }} py-1 px-2">
                                                <small class="text-danger font-weight-bold">{{ $name }}</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="py-1 px-2">-</div>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                                        {{ $checksheet->judgment }}
                                    </span>
                                </td>
                                <td class="align-middle text-uppercase">{{ $checksheet->operator_initials }}</td>

                                {{-- Kashift QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->kashift_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @if($checksheet->kashift_qc_time)
                                            <br><small
                                                class="text-muted">{{ $checksheet->kashift_qc_time->format('d/m/Y H:i') }}</small>
                                        @endif
                                    @elseif($checksheet->kashift_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->kashift_qc }}</small>
                                        @if($checksheet->kashift_qc_time)
                                            <br><small
                                                class="text-muted">{{ $checksheet->kashift_qc_time->format('d/m/Y H:i') }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
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
                                        @if($checksheet->supervisor_qc_time)
                                            <br><small
                                                class="text-muted">{{ $checksheet->supervisor_qc_time->format('d/m/Y H:i') }}</small>
                                        @endif
                                    @elseif($checksheet->supervisor_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->supervisor_qc }}</small>
                                        @if($checksheet->supervisor_qc_time)
                                            <br><small
                                                class="text-muted">{{ $checksheet->supervisor_qc_time->format('d/m/Y H:i') }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                </td>

                                {{-- Sel tabel untuk AM dan Manager dihapus --}}

                                <td class="align-middle text-left">
                                    @if($checksheet->next_proses)
                                        <span class="badge badge-warning">{{ $checksheet->next_proses }}</span><br>
                                    @endif
                                    {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> CLOSE</span>', e($checksheet->remarks ?? '-')) !!}
                                </td>

                                @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'karu_qc']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 300px;">
                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif
                                        @php
                                            $isAdmin = auth()->user()->role === 'admin';
                                            $user = auth()->user();
                                            $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                                            $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                            $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                            $canApproveKashift = ($user->role === 'kashift' || $isAdmin || $isSpvJakarta || $isKaruJakarta) && (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED');
                                            $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) && (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED') && ($checksheet->kashift_qc && $checksheet->kashift_qc !== 'REJECTED');
                                        @endphp

                                        @if($canApproveKashift)
                                            <form
                                                action="{{ route('sortir.approve', ['id' => $checksheet->id, 'type' => 'kashift', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kashift)">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ $isAdmin ? ' KS' : (($isSpvJakarta || $isKaruJakarta) ? ' KR' : '') }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" data-toggle="modal"
                                                data-target="#rejectModal{{ $checksheet->id }}kashift" title="Reject (Kashift)">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @elseif($canApproveSupervisor)
                                            <form
                                                action="{{ route('sortir.approve', ['id' => $checksheet->id, 'type' => 'supervisor', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Supervisor)">
                                                    <i class="fas fa-check"></i> Approve{{ $isAdmin ? ' SPV' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" data-toggle="modal"
                                                data-target="#rejectModal{{ $checksheet->id }}supervisor" title="Reject (Supervisor)">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']) || auth()->user()->role == 'admin' || auth()->user()->name == 'Marsiah')
                                            @if($canEdit)
                                                <a href="{{ route('sortir.edit', ['id' => $checksheet->id, 'plant' => request('plant')]) }}"
                                                    class="btn btn-warning btn-sm m-1 btn-edit-modal no-loader">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($canDelete)
                                                <form
                                                    action="{{ route('sortir.destroy', ['id' => $checksheet->id, 'plant' => request('plant')]) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm m-1 btn-delete"
                                                        onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
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
                                    elseif ($rejectType == 'asst_manager' && ($isAdmin || auth()->user()->role == 'asst_manager'))
                                        $canReject = true;
                                    elseif ($rejectType == 'manager' && ($isAdmin || auth()->user()->role == 'manager'))
                                        $canReject = true;
                                @endphp
                                @if($canReject)
                                    <div class="modal fade" id="rejectModal{{ $checksheet->id }}{{ $rejectType }}" tabindex="-1"
                                        role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Reject Sortir Checksheet - {{ ucfirst($rejectType) }}</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <form
                                                    action="{{ route('sortir.reject', ['id' => $checksheet->id, 'type' => $rejectType, 'plant' => request('plant')]) }}"
                                                    method="POST">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Alasan Rejection:</label>
                                                            <textarea name="rejection_remarks" class="form-control" rows="3"
                                                                required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Batal</button>
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

            <!-- Paginasi -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $checksheets->firstItem() ?? 0 }} to {{ $checksheets->lastItem() ?? 0 }} of
                    {{ $checksheets->total() }} entries
                </div>
                <div>
                    {{ $checksheets->appends(request()->query())->links() }}
                </div>
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
    @php $bulkApproveRoute = route('sortir.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')
@endpush
