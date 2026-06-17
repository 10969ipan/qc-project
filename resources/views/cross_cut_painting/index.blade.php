@extends('layouts.admin')

@section('title', 'Cross Cut Painting')

@section('content')
<style>
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #checksheetTable {
        border-collapse: collapse !important;
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
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 6px 12px !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 2px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
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
        height: 35px !important; 
    }
    #checksheetTable > thead > tr:nth-child(2) > th {
        top: 35px !important; 
        z-index: 104 !important;
        height: 30px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        height: 65px !important; 
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

    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                            LAPORAN DATA CHECKSHEET CROSS CUT PAINTING
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">QC-KRW-F-0215</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">25/03/2015</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">3 / 22/12/2025</td>
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

    <!-- Hidden Logo for PDF Export -->
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

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('cross_cut_painting.index') }}" method="GET" class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm" id="filterFormPainting" style="gap: 10px;">
                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Part:</label>
                    <div style="width: 240px;" class="custom-filter-wrapper">
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

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Tanggal:</label>
                    <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                            style="width: 130px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                        <span class="px-2 text-gray-500 small">-</span>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                            style="width: 130px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Inisial:</label>
                    <div style="width: 120px;" class="custom-filter-wrapper">
                        <select name="operator_initials" id="filterInisial" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Inisial</option>
                            @foreach($initials as $initial)
                                <option value="{{ $initial }}" {{ request('operator_initials') == $initial ? 'selected' : '' }}>{{ $initial }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Customer:</label>
                    <div style="width: 130px;" class="custom-filter-wrapper">
                        <select name="customer" id="filterCustomer" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer }}" {{ request('customer') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Shift:</label>
                    <div style="width: 100px;" class="custom-filter-wrapper">
                        <select name="shift" id="filterShift" class="form-control form-control-sm border-0 shadow-sm">
                            <option value="">Semua Shift</option>
                            <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>

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
                    <a href="{{ route('cross_cut_painting.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    @if($canExport)
                    <a href="{{ route('cross_cut_painting.export_pdf') }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader" title="Export to PDF" target="_blank">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('cross_cut_painting.print') }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
                    </a>
                    @endif
                </div>
            </form>

            <hr>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="checksheetTable">
                    <thead>
                        <tr class="text-center">
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal Produksi</th>
                            <th rowspan="2" class="align-middle">Shift Produksi</th>
                            <th rowspan="2" class="align-middle">Tanggal QC</th>
                            <th rowspan="2" class="align-middle">Shift QC</th>
                            <th rowspan="2" class="align-middle">Jam Before</th>
                            <th rowspan="2" class="align-middle">Jam After</th>
                            <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            <th rowspan="2" class="align-middle d-none">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Part No</th>
                            <th rowspan="2" class="align-middle">Hasil Cross Cut, Pencil Scratch &amp; Tap Test</th>
                            <th rowspan="2" class="align-middle">Judgement</th>
                            <th rowspan="2" class="align-middle">Inisial</th>
                            <th colspan="6" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle">Keterangan</th>
                            @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                <th rowspan="2" class="align-middle no-export">Aksi</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="font-size: 10px;">Kepala Regu QC</th>
                            <th style="font-size: 10px;">Kepala Shift Plating</th>
                            <th style="font-size: 10px;">Supervisor Quality</th>
                            <th style="font-size: 10px;">Supervisor Plating</th>
                            <th style="font-size: 10px;">Manager QC</th>
                            <th style="font-size: 10px;">Manager Plating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checksheets as $checksheet)
                            <tr class="text-center">
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->production_shift }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->qc_shift }}</td>
                                <td class="align-middle">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}</td>
                                <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                <td class="align-middle text-nowrap d-none">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->name }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
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
                                <td class="align-middle text-uppercase">{{ $checksheet->operator_initials }}</td>

                                {{-- Unified Approval Columns --}}
                                @foreach ($approvalOrder as $role)
                                    @php
                                        $field = getApprovalField($role);
                                        $dateField = getApprovalDateField($role);
                                        $status = $checksheet->$field;
                                        $date = $checksheet->$dateField;
                                    @endphp
                                    <td class="align-middle text-center" style="min-width: 100px;">
                                        @if($status === 'REJECTED')
                                            <span class="badge badge-danger px-2 py-1" style="font-size: 0.75rem;" data-toggle="tooltip" 
                                                title="{{ $checksheet->rejection_remarks }}">
                                                <i class="fas fa-times-circle"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted" style="font-size: 10px;">oleh {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @elseif($status && $status !== 'Pending')
                                            <span class="badge badge-success px-2 py-1" style="font-size: 0.75rem;">
                                                <i class="fas fa-check-circle"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted" style="font-size: 10px;">oleh {{ $status }}</small>
                                        @else
                                            <span class="badge badge-warning px-2 py-1" style="font-size: 0.75rem;">
                                                <i class="fas fa-clock"></i> PENDING
                                            </span>
                                        @endif
                                        @if($date)
                                            <br><small class="text-muted" style="font-size: 9px;">{{ \Carbon\Carbon::parse($date)->format('d/m H:i') }}</small>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="align-middle text-left" style="min-width: 150px;">
                                    {{ $checksheet->keterangan ?? '-' }}
                                    @if($checksheet->rejection_remarks)
                                        <div class="text-danger small font-weight-bold mt-1">
                                            <i class="fas fa-exclamation-triangle"></i> REJECTED:
                                            <span class="text-muted">{{ $checksheet->rejection_remarks }}</span>
                                        </div>
                                    @endif
                                </td>

                                @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
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
                                                'kashift_plating' => 'KS Plt',
                                                'supervisor' => 'SPV Q',
                                                'supervisor_plating' => 'SPV P',
                                                'manager' => 'MGR Q',
                                                'manager_plating' => 'MGR P'
                                            ];

                                            $currentRole = $user->role;
                                        @endphp

                                        {{-- Approval Buttons for Roles --}}
                                        @foreach($rolesToApprove as $role => $label)
                                            @php
                                                $field = getApprovalField($role);
                                                $canApproveThis = ($isAdmin || $currentRole === $role) && (!$checksheet->$field || $checksheet->$field === 'REJECTED');
                                            @endphp
                                            
                                            @if($canApproveThis)
                                                @if($role === 'kashift_plating')
                                                    <button type="button" class="btn btn-success btn-sm m-1" title="Approve ({{ $label }})"
                                                        onclick="toggleApprovalModal('{{ $checksheet->id }}', '{{ $role }}', '{{ getApprovalLabel($role, $plantCode) }}', false)"
                                                        style="min-width: 80px;">
                                                        <i class="fas fa-check"></i> Approve {{ $label }}
                                                    </button>
                                                @else
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
                                                            <i class="fas fa-check"></i> Approve {{ $label }}
                                                        </button>
                                                    </form>
                                                @endif
                                                <button type="button" class="btn btn-danger btn-sm m-1" title="Reject ({{ $label }})"
                                                    onclick="toggleApprovalModal('{{ $checksheet->id }}', '{{ $role }}', '{{ getApprovalLabel($role, $plantCode) }}', true)"
                                                    style="min-width: 80px;">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                        @endforeach

                                        {{-- Standard Actions --}}
                                        @if($isAdmin)
                                            <a href="{{ route('admin.cross_cut_painting.edit_approval', array_merge(['id' => $checksheet->id], request()->query())) }}"
                                                class="btn btn-info btn-sm m-1 btn-status-modal no-loader" title="Edit Approval Status"
                                                style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        
                                        @if(!in_array($user->role, ['manager', 'asst_manager', 'manager_plating']))
                                            <a href="{{ route('cross_cut_painting.edit', array_merge(['id' => $checksheet->id], request()->query())) }}" class="btn btn-warning btn-sm m-1 edit-btn" 
                                                data-id="{{ $checksheet->id }}" title="Edit"
                                                style="min-width: 80px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('cross_cut_painting.destroy', array_merge(['id' => $checksheet->id], request()->query())) }}"
                                                method="POST" class="d-inline p-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm m-1 btn-delete" 
                                                    title="Hapus" style="min-width: 80px;">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role !== 'inspector' ? 21 : 20 }}" class="text-center py-4">
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
            <div class="d-flex justify-content-end mt-3">
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
                <div class="modal-body">
                    <img id="modalViewImage" src="" class="img-fluid border shadow-sm">
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

                        {{-- Special input for Kashift Plating (if needed) --}}
                        <div class="form-group" id="approverNameGroup" style="display: none;">
                            <label class="font-weight-bold">Nama Approver <span class="text-danger">*</span></label>
                            <input type="text" name="approver_name" id="approver_name_input" class="form-control" placeholder="Masukkan Nama...">
                        </div>

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
    
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof initItemSearch === 'function') {
                initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...', maxResults: 50 });
                initItemSearch('filterInisial', { placeholder: 'Ketik Inisial...', maxResults: 20 });
                initItemSearch('filterCustomer', { placeholder: 'Ketik Customer...', maxResults: 30 });
            }
        });
    </script>
@endpush
