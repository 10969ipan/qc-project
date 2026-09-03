@extends('layouts.admin')

@section('title', 'Cross Cut Painting')

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
</style>

    @php
        // Resolve menu ID for permission checks
        $currentMenu = \App\Models\AppMenu::where('route', 'cross_cut_painting.index')->first();
        $menuId = $currentMenu ? $currentMenu->id : null;
        $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
        $canEdit = $menuId ? auth()->user()->hasPermission($menuId, 'edit') : true;
        $canDelete = $menuId ? auth()->user()->hasPermission($menuId, 'delete') : true;
    @endphp

    <!-- Logo Tersembunyi untuk Ekspor PDF -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Card Header Dokumen Pengaturan --}}
    @if(isset($docHeader))
    <div class="card shadow mb-2">
        <div class="card-body p-2">
            <div class="table-responsive" style="max-height: none !important; overflow: visible !important;">
                <table class="table table-bordered mb-0" style="font-size: 0.75rem;">
                    <tr>
                        <td class="text-center align-middle" style="width: 15%;">
                            <img src="{{ asset('master item/ipp.jpg') }}" style="max-width: 80px; max-height: 50px; object-fit: contain;">
                        </td>
                        <td class="text-center align-middle font-weight-bold text-uppercase" style="width: 55%; font-size: 1rem;">
                            {{ $docHeader['judul'] }}
                        </td>
                        <td class="align-middle p-0" style="width: 30%;">
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.70rem;">
                                <tr><td class="font-weight-bold py-0" style="width: 45%;">No. Dokumen</td><td class="py-0">: {{ $docHeader['no_dokumen'] }}</td></tr>
                                <tr><td class="font-weight-bold py-0">Tgl. Terbit</td><td class="py-0">: {{ $docHeader['tgl_terbit'] }}</td></tr>
                                <tr><td class="font-weight-bold py-0">Revisi Ke</td><td class="py-0">: {{ $docHeader['revisi'] }}</td></tr>
                                <tr><td class="font-weight-bold py-0">Tgl. Revisi</td><td class="py-0">: {{ $docHeader['tgl_revisi'] ?? '-' }}</td></tr>
                                <tr><td class="font-weight-bold py-0">Halaman</td><td class="py-0">: {{ $docHeader['halaman'] }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow mb-2">
        <div class="card-header py-2 px-3">
            <h6 class="m-0 font-weight-bold text-dark text-uppercase" style="font-size: 0.80rem;">DATA MASUK CROSS CUT PAINTING</h6>
        </div>
        <div class="card-body p-2">
            <form action="{{ route('cross_cut_painting.index') }}" method="GET"
                class="d-flex flex-wrap align-items-end bg-light p-2 rounded mb-2 shadow-sm"
                style="gap: 8px; overflow-x: auto;" id="filterFormPainting">

                <input type="hidden" name="plant" value="{{ request('plant') }}">

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
                    <div style="width: 120px;" class="custom-filter-wrapper">
                        <select name="customer" id="filterCustomer" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Customer</option>
                            @foreach($customers as $customer)
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
                    <a href="{{ route('cross_cut_painting.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2 py-1 no-loader d-flex align-items-center" style="font-size: 0.68rem; height: 26px;" title="Reset Filter">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </a>
                </div>

                <!-- Tombol Ekspor (Paling Kanan) -->
                <div class="d-flex align-items-center ml-auto" style="gap: 4px; align-self: flex-end; margin-bottom: 8px !important;">
                    @if($canExport)
                    <a href="{{ route('cross_cut_painting.print', request()->query()) }}"
                        class="btn btn-sm shadow-sm rounded-pill px-2 py-1 no-loader btn-print-direct d-flex align-items-center" style="background-color: #17a589; color: white; font-size: 0.68rem; height: 26px;" title="Cetak Direct">
                        <i class="fas fa-print fa-sm mr-1"></i> Cetak
                    </a>
                    @endif
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="checksheetTable">
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
                            @if(!in_array(auth()->user()->role, ['inspector']) && auth()->user()->role === 'admin')
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
                            <th rowspan="2" class="align-middle text-nowrap">Prod.<br>(Tgl / Shift)</th>
                            <th rowspan="2" class="align-middle text-nowrap">Checked<br>(Tgl / Shift / Inisial)</th>
                            <th rowspan="2" class="align-middle text-nowrap">Waktu Check<br>(Start - Finish / CT)</th>
                            <th rowspan="2" class="align-middle d-none">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part / Part No</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Hasil Cross Cut, Pencil Scratch &amp; Tap Test</th>
                            <th rowspan="2" class="align-middle">Judgement</th>
                            <th colspan="6" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle" style="min-width: 400px;">DESCRIPTION</th>
                            @if(!in_array(auth()->user()->role, ['inspector']))
                                <th rowspan="2" class="align-middle no-export">Aksi</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="font-size: 10px; min-width: 120px;">Kepala Regu</th>
                            <th style="font-size: 10px; min-width: 120px;">Kashift Painting</th>
                            <th style="font-size: 10px; min-width: 120px;">Supervisor Quality</th>
                            <th style="font-size: 10px; min-width: 120px;">Supervisor Painting</th>
                            <th style="font-size: 10px; min-width: 120px;">Asst Manager Quality</th>
                            <th style="font-size: 10px; min-width: 120px;">Asst Manager Painting</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checksheets as $checksheet)
                            <tr class="text-center">
                                @if(!in_array(auth()->user()->role, ['inspector']) && auth()->user()->role === 'admin')
                                    <td class="align-middle text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input row-checkbox" id="checkRow{{ $checksheet->id }}" value="{{ $checksheet->id }}">
                                            <label class="custom-control-label" for="checkRow{{ $checksheet->id }}" style="cursor:pointer;"></label>
                                        </div>
                                    </td>
                                @endif
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('d-m-Y') }} / {{ $checksheet->production_shift }}
                                </td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d-m-Y') }} / {{ $checksheet->qc_shift }} / {{ $checksheet->operator_initials ?? '-' }}
                                </td>
                                @php
                                    $sec = (int) ($checksheet->cycle_time ?? 0);
                                    $ctStr = ($sec > 0) ? (($sec < 60) ? ($sec . 's') : (floor($sec / 60) . 'm' . (($sec % 60 > 0) ? ' ' . ($sec % 60) . 's' : ''))) : '-';
                                @endphp
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($sec)->format('H:i') }} - {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }} <span class="text-muted">({{ $ctStr }})</span>
                                </td>
                                <td class="align-middle text-nowrap d-none">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-left text-nowrap">
                                    <span class="font-weight-bold text-gray-800">{{ $checksheet->item->name }}</span><br>
                                    <small class="text-muted">{{ $checksheet->item->part_number ?? '-' }}</small>
                                </td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle p-2" style="min-width: 180px;">
                                    <table class="table table-bordered mb-2 text-left mx-auto" style="font-size: 0.8rem; max-width: 200px;">
                                        <tbody>
                                            <tr>
                                                <th class="p-1 py-0" style="background-color: #f8f9fc;">Cross Cut</th>
                                                <td class="p-1 py-0 text-center font-weight-bold {{ ($checksheet->defects['cross_cut'] ?? 'OK') === 'OK' ? 'text-success' : 'text-danger' }}">
                                                    {{ $checksheet->defects['cross_cut'] ?? 'OK' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="p-1 py-0" style="background-color: #f8f9fc;">Pencil Scratch</th>
                                                <td class="p-1 py-0 text-center font-weight-bold {{ ($checksheet->pencil_scratch ?? 'OK') === 'OK' ? 'text-success' : 'text-danger' }}">
                                                    {{ $checksheet->pencil_scratch ?? 'OK' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="p-1 py-0" style="background-color: #f8f9fc;">Tap Test</th>
                                                <td class="p-1 py-0 text-center font-weight-bold {{ ($checksheet->tap_test ?? 'OK') === 'OK' ? 'text-success' : 'text-danger' }}">
                                                    {{ $checksheet->tap_test ?? 'OK' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    @if ($checksheet->image_path)
                                         <div class="text-center mt-2">
                                             <button class="btn btn-outline-primary btn-xs view-image-btn" data-id="{{ $checksheet->id }}"
                                                 data-image="{{ route('cross_cut_painting.image', $checksheet->id) }}"
                                                 data-toggle="modal" data-target="#imageModal"
                                                 style="padding: 0.2rem 0.4rem; font-size: 0.75rem; white-space: nowrap;" title="Lihat Hasil">
                                                 <i class="fas fa-image"></i> Lihat Hasil
                                             </button>
                                         </div>
                                    @endif
                                </td>
                                <td class="align-middle font-weight-bold {{ $checksheet->position_remark_judgment === 'OK' ? 'text-success' : 'text-danger' }}">
                                    {{ $checksheet->position_remark_judgment }}
                                </td>

                                {{-- Unified Approval Columns --}}
                                @foreach ($approvalOrder as $role)
                                    @php
                                        $field = getApprovalField($role);
                                        $dateField = getApprovalDateField($role);
                                        $status = $checksheet->$field;
                                        $date = $checksheet->$dateField;
                                    @endphp
                                    <td class="align-middle text-center" style="white-space: nowrap; min-width: 120px;">
                                        @if($status === 'REJECTED')
                                            <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;" data-toggle="tooltip" title="{{ $checksheet->rejection_remarks }}">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                                                <div>oleh {{ getRejectorName($checksheet->rejection_remarks) }}</div>
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

                                <td class="align-middle text-left" style="min-width: 400px; word-wrap: break-word;">
                                    {!! nl2br(e($checksheet->keterangan ?? '-')) !!}
                                    @if($checksheet->rejection_remarks)
                                        <div class="text-danger small font-weight-bold mt-1">
                                            <i class="fas fa-exclamation-triangle"></i> REJECTED:
                                            <span class="text-muted">{{ $checksheet->rejection_remarks }}</span>
                                        </div>
                                    @endif
                                </td>

                                @if(!in_array(auth()->user()->role, ['inspector']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 150px;">
                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif
                                        @php
                                            $user = auth()->user();
                                            $isAdmin = $user->role === 'admin';
                                            $plantCode = request('plant') ?? optional($user->plant)->code ?? 'karawang';
                                            
                                            // Mapping current role to its button label
                                            $rolesToApprove = [
                                                'karu_qc' => 'KR',
                                                'kashift_plating' => 'Kashift P',
                                                'supervisor' => 'SPV Q',
                                                'supervisor_plating' => 'SPV P',
                                                'asst_manager' => 'Asst Mgr Q',
                                                'asst_manager_plating' => 'Asst Mgr P',
                                            ];
                                            $approvalKeys = array_keys($rolesToApprove);

                                            $currentRole = $user->role;
                                        @endphp

                                        {{-- Approval Buttons for Roles --}}
                                        @foreach($rolesToApprove as $role => $label)
                                            @php
                                                $field = getApprovalField($role);

                                                $idx = array_search($role, $approvalKeys);
                                                $prevApproved = true;
                                                if ($idx > 0) {
                                                    for ($i = $idx - 1; $i >= 0; $i--) {
                                                        $prevF = getApprovalField($approvalKeys[$i]);
                                                        if (empty($checksheet->$prevF) || $checksheet->$prevF === 'REJECTED') {
                                                            $prevApproved = false;
                                                            break;
                                                        }
                                                    }
                                                }

                                                $canApproveThis = ($isAdmin || $currentRole === $role) 
                                                                  && (empty($checksheet->$field) || $checksheet->$field === 'REJECTED')
                                                                  && $prevApproved;
                                            @endphp
                                            
                                            @if($canApproveThis)
                                                <form action="{{ route('cross_cut_painting.approve', ['id' => $checksheet->id, 'type' => $role]) }}" method="POST" class="d-inline p-0">
                                                    @csrf
                                                    <input type="hidden" name="page" value="{{ request('page') }}">
                                                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                    <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                    <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                                                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                    <input type="hidden" name="operator_initials" value="{{ request('operator_initials') }}">
                                                    <input type="hidden" name="customer" value="{{ request('customer') }}">
                                                    <input type="hidden" name="action_type" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm m-1" title="Approve ({{ $label }})" style="min-width: 80px;">
                                                        <i class="fas fa-check"></i> Approve{{ $isAdmin ? ' ' . $label : '' }}
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm m-1" title="Reject ({{ $label }})"
                                                    onclick="toggleApprovalModal('{{ $checksheet->id }}', '{{ $role }}', '{{ getApprovalLabel($role, $plantCode) }}', true)"
                                                    style="min-width: 80px;">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                        @endforeach

                                        {{-- Standard Actions --}}
                                        @include('partials.action_dropdown', [
                                            'canEdit'      => $canEdit,
                                            'canDelete'    => $canDelete,
                                            'editUrl'      => route('cross_cut_painting.edit', array_merge(['id' => $checksheet->id], request()->query())),
                                            'deleteRoute'  => route('cross_cut_painting.destroy', array_merge(['id' => $checksheet->id], request()->query())),
                                            'deleteParams' => [],
                                            'statusUrl'    => $isAdmin ? route('cross_cut_painting.edit_approval', array_merge(['id' => $checksheet->id], request()->query())) : null,
                                        ])
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role !== 'inspector' ? 22 : 21 }}" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                        Tidak ada data yang tersedia
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 pagination-container">
                {{ $checksheets->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>

    {{-- View Image Modal --}}
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-image mr-2"></i>Bukti Foto Hasil Cross Cut</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body overflow-auto">
                    <img id="modalViewImage" src="" class="img-fluid border shadow-sm" style="cursor: zoom-in; transition: transform 0.25s ease;" onclick="this.style.transform = this.style.transform === 'scale(2)' ? 'scale(1)' : 'scale(2)'; this.style.cursor = this.style.transform === 'scale(2)' ? 'zoom-out' : 'zoom-in';">
                </div>
                <div class="modal-footer justify-content-center">
                    <a href="#" id="downloadImageBtnPainting" class="btn btn-primary" download>
                        <i class="fas fa-download mr-1"></i>Download Gambar
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Modal --}}
    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form id="approvalForm" method="POST">
                    @csrf
                    <input type="hidden" name="page" value="{{ request('page') }}">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                    <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="shift" value="{{ request('shift') }}">
                    <input type="hidden" name="plant" value="{{ request('plant') }}">
                    <input type="hidden" name="operator_initials" value="{{ request('operator_initials') }}">
                    <input type="hidden" name="customer" value="{{ request('customer') }}">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-check-circle mr-2"></i>Konfirmasi Approval</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-4">Anda akan melakukan tindakan sebagai <strong id="approvalLabelText"></strong>.</p>



                        <div class="form-group d-none">
                            <label class="font-weight-bold">Tindakan:</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-success active">
                                    <input type="radio" name="action_type" value="approve" checked 
                                        onchange="toggleRejectReason(false)"> <i class="fas fa-check mr-1"></i> Approve
                                </label>
                                <label class="btn btn-outline-danger">
                                    <input type="radio" name="action_type" value="reject" 
                                        onchange="toggleRejectReason(true)"> <i class="fas fa-times mr-1"></i> Reject
                                </label>
                            </div>
                        </div>

                        <div class="form-group mt-3" id="rejectReasonGroup" style="display:none;">
                            <label class="text-danger font-weight-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="rejection_remarks" class="form-control" rows="3" 
                                placeholder="Jelaskan alasan reject (minimal 10 karakter)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit Data Cross Cut Painting</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>
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
    <script src="{{ asset('js/vendor/jspdf.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/jspdf.plugin.autotable.min.js') }}"></script>
    <script src="{{ asset('js/checksheet/cross-cut.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initCrossCutIndex({
                isPainting: true,
                moduleName: 'Cross_Cut_Painting',
                pdfTitle: 'LAPORAN CHECKSHEET CROSS CUT PAINTING',
                docNo: 'QC-KRW-F-0215',
                approveRoute: "{{ route('cross_cut_painting.approve', ['id' => ':id', 'type' => ':type']) }}"
            });

            // Update Download Button link on Modal Show
            $('#imageModal').on('show.bs.modal', function (e) {
                var btn = $(e.relatedTarget);
                var imagePath = btn.data('image');
                $('#downloadImageBtnPainting').attr('href', imagePath);
            });

            // Link Synchronization (Sync Print/Export links with current filter selections)
            var form = document.getElementById('filterFormPainting');
            if (form) {
                function syncExportLinks() {
                    var baseUrlPrint = "{{ route('cross_cut_painting.print') }}";
                    var baseUrlPdf = "{{ route('cross_cut_painting.export_pdf') }}";
                    
                    var params = new URLSearchParams();
                    var formData = new FormData(form);
                    for (var pair of formData.entries()) {
                        if (pair[1]) params.set(pair[0], pair[1]);
                    }
                    
                    var queryString = params.toString();
                    
                    var printBtn = form.querySelector('a[title="Print"]');
                    var pdfBtn = form.querySelector('a[title="Export to PDF"]');
                    
                    if (printBtn) printBtn.href = baseUrlPrint + '?' + queryString;
                    if (pdfBtn) pdfBtn.href = baseUrlPdf + '?' + queryString;
                }

                $(form).find('input, select').on('change', syncExportLinks);
                $(form).find('input[type="text"]').on('input', syncExportLinks);
                syncExportLinks();

                $(form).on('submit', function(e) {
                    var startDate = document.getElementById('start_date').value;
                    var endDate = document.getElementById('end_date').value;

                    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Rentang Tanggal Tidak Valid',
                            text: 'Tanggal Akhir tidak boleh lebih kecil dari Tanggal Mulai!'
                        });
                    }
                });
            }
        });
    </script>
    @php $bulkApproveRoute = route('cross_cut_painting.bulk_approve'); @endphp
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

    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof initItemSearch === 'function') {
                initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
                initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
                initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
            }
        });

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
                                url: '{{ route("cross_cut_painting.bulk_destroy") }}',
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
