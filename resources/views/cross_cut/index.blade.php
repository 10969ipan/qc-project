@extends('layouts.admin')

@section('title', 'Laporan Data Cross Cut')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        LAPORAN DATA CROSS CUT PLATING
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');
                        @endphp
                        <span
                            class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
                            style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0214</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tgl. Terbit</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 25/03/2015</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Revisi / Tgl</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 3 / 22/12/2025</div>
                        </div>
                        <div class="row">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Halaman</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 1 / 1</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hidden Logo for PDF Export -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk Cross Cut Plating</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('cross_cut.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    {{-- Preserve plant parameter for all users --}}
                    @if(request('plant'))
                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                    @endif


                    <!-- Live Search -->
                    <div class="col-lg-3 col-md-12 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label for="search" class="small font-weight-bold">Pencarian</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="liveSearch" class="form-control" placeholder="Cari..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Filter Tanggal -->
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="start_date" class="small font-weight-bold">Dari Tanggal</label>
                            <input type="date" id="start_date" name="start_date" class="form-control form-control-sm"
                                value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="end_date" class="small font-weight-bold">Sampai Tanggal</label>
                            <input type="date" id="end_date" name="end_date" class="form-control form-control-sm"
                                value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <!-- Buttons: Cari, Reset, Export -->
                    <div class="col-lg-3 col-md-4 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold d-block">&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary btn-sm mr-2" title="Cari Data">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('cross_cut.index', ['plant' => request('plant')]) }}"
                                    class="btn btn-secondary btn-sm mr-2 no-loader" title="Reset Filter">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                                <a href="{{ route('cross_cut.export_pdf', request()->query()) }}"
                                    class="btn btn-danger btn-sm no-loader btn-download" title="Export to PDF">
                                    <i class="fas fa-file-pdf"></i> Export
                                </a>
                            </div>
                        </div>
                    </div>
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
                            <th rowspan="2" class="align-middle">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Part No</th>
                            <th rowspan="2" class="align-middle no-export">Hasil Cross Cut</th>
                            <th rowspan="2" class="align-middle">Bak No</th>
                            <th rowspan="2" class="align-middle">Posisi Remark</th>
                            <th rowspan="2" class="align-middle">Result Remark</th>
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
                                <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->name }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
                                <td class="align-middle no-export">
                                    <button class="btn btn-primary btn-sm view-image-btn" data-id="{{ $checksheet->id }}"
                                        data-toggle="modal" data-target="#imageModal">
                                        <i class="fas fa-eye"></i> Lihat Foto
                                    </button>
                                </td>
                                <td class="align-middle p-0 kimia-col">
                                    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
                                        <tbody>
                                            <tr>
                                                <th class="p-1">Catalyst</th>
                                                <td class="p-1">{{ $checksheet->chemical_catalyst ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="p-1">Abu</th>
                                                <td class="p-1">{{ $checksheet->chemical_abu ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="align-middle">{{ $checksheet->position_remark_judgment }} -
                                    {{ $checksheet->position_remark_no_lot }}
                                </td>
                                <td class="align-middle">{{ $checksheet->result_remark }}</td>
                                <td class="align-middle">{{ $checksheet->operator_initials }}</td>

                                {{-- Approval Status Columns --}}
                                {{-- Level 1: Karu QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->karu_qc)
                                        @if($checksheet->karu_qc === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->karu_qc }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->karu_qc_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->karu_qc_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Level 2: Kashift Plating --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->kashift_plating)
                                        @if($checksheet->kashift_plating === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->kashift_plating }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->kashift_plating_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->kashift_plating_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Level 3: SPV Quality --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->supervisor_qc)
                                        @if($checksheet->supervisor_qc === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->supervisor_qc }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->supervisor_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->supervisor_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Level 4: SPV Plating --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->supervisor_plating)
                                        @if($checksheet->supervisor_plating === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->supervisor_plating }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->supervisor_plating_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->supervisor_plating_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Level 5: Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->manager_qc)
                                        @if($checksheet->manager_qc === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->manager_qc }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->manager_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Level 6: Manager Plating --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->manager_plating)
                                        @if($checksheet->manager_plating === 'REJECTED')
                                            <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-times-circle mr-1"></i> REJECTED
                                            </span>
                                            <br><small class="text-muted">oleh
                                                {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                        @else
                                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                                <i class="fas fa-check-circle mr-1"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted">oleh {{ $checksheet->manager_plating }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->manager_plating_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_plating_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

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
                                                <br>
                                                @if(!str_contains($checksheet->keterangan ?? '', '[SORTIR_CLOSED]'))
                                                    <span class="text-danger small font-weight-bold ml-1">
                                                        <i class="fas fa-clock"></i> STATUS: OPEN
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($checksheet->keterangan)) !!}
                                    @endif
                                </td>

                                @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 350px;">
                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif
                                        @php
                                            // Modified: Allow approval at any level without waiting for previous levels
                                            $canApproveKaruQc = (auth()->user()->role === 'karu_qc' || auth()->user()->role === 'admin') && (!$checksheet->karu_qc || $checksheet->karu_qc === 'REJECTED');
                                            $canApproveKashiftPlating = (auth()->user()->role === 'kashift_plating' || auth()->user()->role === 'admin') && (!$checksheet->kashift_plating || $checksheet->kashift_plating === 'REJECTED');
                                            $canApproveSupervisorPlating = (auth()->user()->role === 'supervisor_plating' || auth()->user()->role === 'admin') && (!$checksheet->supervisor_plating || $checksheet->supervisor_plating === 'REJECTED');
                                            $canApproveSupervisor = (auth()->user()->role === 'supervisor' || auth()->user()->role === 'admin') && (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED');
                                            $canApproveManagerPlating = (auth()->user()->role === 'manager_plating' || auth()->user()->role === 'admin') && (!$checksheet->manager_plating || $checksheet->manager_plating === 'REJECTED');
                                            $canApproveManager = (auth()->user()->role === 'manager' || auth()->user()->role === 'admin') && (!$checksheet->manager_qc || $checksheet->manager_qc === 'REJECTED');
                                        @endphp

                                        {{-- Level 1: Karu QC --}}
                                        @if($canApproveKaruQc)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'karu_qc', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kepala Regu)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' KR' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kepala Regu)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}karu_qc"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        {{-- Level 2: Kashift Plating --}}
                                        @if($canApproveKashiftPlating)
                                            <button type="button" class="btn btn-success btn-sm m-1" title="Approve (Kashift Plating)"
                                                style="min-width: 110px;" data-toggle="modal"
                                                data-target="#approveModal{{ $checksheet->id }}kashift_plating">
                                                <i class="fas fa-check"></i>
                                                Approve{{ (auth()->user()->role === 'admin') ? ' KS Plt' : '' }}
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kashift Plating)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift_plating"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        {{-- Level 3: SPV Quality --}}
                                        @if($canApproveSupervisor)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'supervisor', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV Quality)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' SPV Q' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV Quality)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        {{-- Level 4: SPV Plating --}}
                                        @if($canApproveSupervisorPlating)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'supervisor_plating', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV Plating)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' SPV P' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV Plating)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor_plating"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        {{-- Level 5: Manager QC --}}
                                        @if($canApproveManager)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'manager', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Manager QC)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' MGR Q' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Manager QC)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        {{-- Level 6: Manager Plating --}}
                                        @if($canApproveManagerPlating)
                                            <form
                                                action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'manager_plating', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1"
                                                    title="Approve (Manager Plating)" style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' MGR P' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Manager Plating)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager_plating"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('admin.cross_cut.edit_approval', ['id' => $checksheet->id]) }}"
                                                class="btn btn-info btn-sm m-1 btn-status-modal no-loader" title="Edit Approval Status"
                                                style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                            <a href="{{ route('cross_cut.edit', ['id' => $checksheet->id]) }}"
                                                class="btn btn-warning btn-sm m-1 btn-edit-modal no-loader" title="Edit"
                                                style="min-width: 110px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form
                                                action="{{ route('cross_cut.destroy', ['id' => $checksheet->id, 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm m-1 btn-delete" title="Delete"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role !== 'inspector' ? 22 : 21 }}" class="text-center">No data
                                    available</td>
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

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Cross Cut Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Cross Cut Image">
                    <p id="modalItemName" class="mt-2"></p>
                    <p id="modalQcDatetime"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Checksheet Cross Cut</h5>
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

    <!-- Status Modal -->
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

    <!-- Rejection Modal for each checksheet and type -->
    @foreach($checksheets as $cs)
        @foreach(['karu_qc', 'kashift_plating', 'supervisor_plating', 'supervisor', 'manager_plating', 'manager'] as $rejectType)
            @php
                $canReject = false;
                // Modified: Allow rejection at any level without waiting for previous levels
                if ($rejectType == 'karu_qc' && ((auth()->user()->role === 'karu_qc' || auth()->user()->role === 'admin') && (!$cs->karu_qc || $cs->karu_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'kashift_plating' && ((auth()->user()->role === 'kashift_plating' || auth()->user()->role === 'admin') && (!$cs->kashift_plating || $cs->kashift_plating === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor_plating' && ((auth()->user()->role === 'supervisor_plating' || auth()->user()->role === 'admin') && (!$cs->supervisor_plating || $cs->supervisor_plating === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor' && ((auth()->user()->role === 'supervisor' || auth()->user()->role === 'admin') && (!$cs->supervisor_qc || $cs->supervisor_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'manager_plating' && ((auth()->user()->role === 'manager_plating' || auth()->user()->role === 'admin') && (!$cs->manager_plating || $cs->manager_plating === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'manager' && ((auth()->user()->role === 'manager' || auth()->user()->role === 'admin') && (!$cs->manager_qc || $cs->manager_qc === 'REJECTED'))) {
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
                                action="{{ route('cross_cut.reject', ['id' => $cs->id, 'type' => $rejectType, 'plant' => request('plant')]) }}"
                                method="POST">
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
                                    <button type="submit" class="btn btn-danger">
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

    <!-- Approval Modal for Kashift Plating -->
    @foreach($checksheets as $cs)
        @php
            $canApproveKashiftPlating = (auth()->user()->role === 'kashift_plating' || auth()->user()->role === 'admin') && (!$cs->kashift_plating || $cs->kashift_plating === 'REJECTED');
        @endphp
        @if($canApproveKashiftPlating)
            <div class="modal fade" id="approveModal{{ $cs->id }}kashift_plating" tabindex="-1" role="dialog"
                aria-labelledby="approveModalLabel{{ $cs->id }}kashift_plating" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="approveModalLabel{{ $cs->id }}kashift_plating">
                                <i class="fas fa-check-circle mr-2"></i>Konfirmasi Approval Kepala Shift Plating
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('cross_cut.approve', ['id' => $cs->id, 'type' => 'kashift_plating']) }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="page" value="{{ request('page') }}">
                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                            <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                            <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Anda akan menyetujui checksheet ini sebagai
                                    <strong>Kepala Shift Plating</strong>
                                </div>
                                <div class="form-group">
                                    <label for="approver_name{{ $cs->id }}kashift_plating" class="font-weight-bold">
                                        Nama User/Approver <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('approver_name') is-invalid @enderror"
                                        id="approver_name{{ $cs->id }}kashift_plating" name="approver_name"
                                        placeholder="Masukkan nama Anda (minimal 3 karakter)" required minlength="3" maxlength="100"
                                        value="{{ old('approver_name') }}">
                                    <small class="form-text text-muted">
                                        Masukkan nama lengkap Anda untuk konfirmasi approval
                                    </small>
                                    @error('approver_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Setujui Checksheet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Image Modal --}}
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-font-weight-bold" id="imageModalLabel">
                        <i class="fas fa-image mr-2"></i>Detail Cross Cut Checksheet
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Image Section --}}
                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-camera mr-2"></i>Hasil Cross Cut
                                    </h6>
                                </div>
                                <div class="card-body text-center p-2">
                                    <div id="imageContainer"
                                        style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Details Section --}}
                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-info-circle mr-2"></i>Informasi Checksheet
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tbody id="detailsContainer">
                                            <tr>
                                                <td colspan="2" class="text-center"><em>Loading...</em></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Tutup
                    </button>
                    <a href="#" id="downloadImageBtn" class="btn btn-primary" download>
                        <i class="fas fa-download mr-1"></i>Download Gambar
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/jspdf.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/jspdf.plugin.autotable.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Character counter for rejection remarks
            @foreach($checksheets as $cs)
                @foreach(['karu_qc', 'kashift_plating', 'supervisor', 'supervisor_plating', 'manager', 'manager_plating'] as $rejectType)
                    const textarea{{ $cs->id }}{{ $rejectType }} = document.getElementById('rejection_remarks{{ $cs->id }}{{ $rejectType }}');
                    const charCount{{ $cs->id }}{{ $rejectType }} = document.getElementById('charCount{{ $cs->id }}{{ $rejectType }}');
                    if (textarea{{ $cs->id }}{{ $rejectType }}) {
                        textarea{{ $cs->id }}{{ $rejectType }}.addEventListener('input', function () {
                            charCount{{ $cs->id }}{{ $rejectType }}.textContent = this.value.length;
                        });
                    }
                @endforeach
            @endforeach

                                                                                                                                                                                                                    // Image Modal Handler
                                                                                                                                                const imageModal = document.getElementById('imageModal');
            const viewImageBtns = document.querySelectorAll('.view-image-btn');

            viewImageBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const checksheetId = this.getAttribute('data-id');
                    loadChecksheetImage(checksheetId);
                });
            });

            function loadChecksheetImage(id) {
                const imageContainer = document.getElementById('imageContainer');
                const detailsContainer = document.getElementById('detailsContainer');
                const downloadBtn = document.getElementById('downloadImageBtn');

                // Show loading state
                imageContainer.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>';
                detailsContainer.innerHTML = '<tr><td colspan="2" class="text-center"><em>Loading...</em></td></tr>';

                // Fetch checksheet data
                const url = "{{ route('cross_cut.data', ':id') }}".replace(':id', id);
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // Display image
                        if (data.image_path) {
                            const imagePath = `/storage/${data.image_path}`;
                            imageContainer.innerHTML = `
                                                                                                                                                                    <img src="${imagePath}" 
                                                                                                                                                                         class="img-fluid rounded shadow" 
                                                                                                                                                                         alt="Cross Cut Image"
                                                                                                                                                                         style="max-height: 600px; width: auto; cursor: zoom-in;"
                                                                                                                                                                         onclick="window.open('${imagePath}', '_blank')">
                                                                                                                                                                `;
                            downloadBtn.href = imagePath;
                            downloadBtn.style.display = 'inline-block';
                        } else {
                            imageContainer.innerHTML = `
                                                                                                                                                                    <div class="alert alert-warning">
                                                                                                                                                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                                                                                                                                                        Tidak ada gambar untuk checksheet ini
                                                                                                                                                                    </div>
                                                                                                                                                                `;
                            downloadBtn.style.display = 'none';
                        }

                        // Display details
                        const details = `
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Item Part:</th>
                                                                                                                                                                    <td>${data.item_name || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Customer:</th>
                                                                                                                                                                    <td>${data.customer || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Part No:</th>
                                                                                                                                                                    <td>${data.part_number || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Kode SAP:</th>
                                                                                                                                                                    <td>${data.sap_code || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Tanggal Produksi:</th>
                                                                                                                                                                    <td>${data.production_date || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Tanggal QC:</th>
                                                                                                                                                                    <td>${data.qc_date || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Shift Prod./QC:</th>
                                                                                                                                                                    <td>${data.production_shift || '-'} / ${data.qc_shift || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Kimia Copper:</th>
                                                                                                                                                                    <td>${data.chemical_copper || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Kimia Nikel:</th>
                                                                                                                                                                    <td>${data.chemical_nikel || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Kimia Eching:</th>
                                                                                                                                                                    <td>${data.chemical_eching || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Kimia Abu:</th>
                                                                                                                                                                    <td>${data.chemical_abu || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Posisi Remark:</th>
                                                                                                                                                                    <td>${data.position_remark_judgment || '-'} - ${data.position_remark_no_lot || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Result Remark:</th>
                                                                                                                                                                    <td>${data.result_remark || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                                <tr>
                                                                                                                                                                    <th class="text-nowrap">Operator:</th>
                                                                                                                                                                    <td>${data.operator_initials || '-'}</td>
                                                                                                                                                                </tr>
                                                                                                                                                            `;
                        detailsContainer.innerHTML = details;
                    })
                    .catch(error => {
                        console.error('Error loading image:', error);
                        imageContainer.innerHTML = `
                                                                                                                                                                <div class="alert alert-danger">
                                                                                                                                                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                                                                                                                                                    Gagal memuat gambar. Silakan coba lagi.
                                                                                                                                                                </div>
                                                                                                                                                            `;
                        detailsContainer.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Error loading data</td></tr>';
                    });
            }

            // Live Search Functionality - Server-side search across all pages
            const liveSearchInput = document.getElementById('liveSearch');

            if (liveSearchInput) {
                let searchTimeout;

                liveSearchInput.addEventListener('keyup', function () {
                    const searchTerm = this.value.trim();

                    // Clear previous timeout
                    clearTimeout(searchTimeout);

                    // Debounce: wait 500ms after user stops typing
                    searchTimeout = setTimeout(function () {
                        // Get current filter values
                        const startDate = document.getElementById('start_date').value;
                        const endDate = document.getElementById('end_date').value;

                        // Build URL with all parameters
                        const params = new URLSearchParams();
                        if (searchTerm) params.append('search', searchTerm);
                        if (startDate) params.append('start_date', startDate);
                        if (endDate) params.append('end_date', endDate);

                        // Redirect to index with search parameter
                        window.location.href = '{{ route('cross_cut.index') }}?' + params.toString();
                    }, 500);
                });
            }

            const viewImageButtons = document.querySelectorAll('.view-image-btn');
            const modalImage = document.getElementById('modalImage');
            const modalItemName = document.getElementById('modalItemName');
            const modalQcDatetime = document.getElementById('modalQcDatetime');
            const jsonInfoUrlTemplate = "{{ route('cross_cut.show', ['id' => ':id']) }}";

            viewImageButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const checksheetId = this.getAttribute('data-id');
                    const fetchUrl = jsonInfoUrlTemplate.replace(':id', checksheetId);

                    fetch(fetchUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            modalImage.src = data.image_url;
                            modalItemName.textContent = `Item: ${data.item_name}`;
                            modalQcDatetime.textContent = `QC Datetime: ${data.qc_datetime}`;
                        })
                        .catch(error => {
                            console.error('Error fetching image data:', error);
                            modalImage.src = '';
                            modalItemName.textContent = 'Gagal memuat data gambar.';
                            modalQcDatetime.textContent = '';
                        });
                });
            });

            // PDF Export
            const exportPdfBtn = document.getElementById('exportPdfBtn');

            if (exportPdfBtn) {
                exportPdfBtn.classList.add('btn-download'); // Add btn-download class
                exportPdfBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    const searchTerm = document.getElementById('liveSearch').value.trim();

                    const params = new URLSearchParams();
                    if (startDate) params.append('start_date', startDate);
                    if (endDate) params.append('end_date', endDate);
                    if (searchTerm) params.append('search', searchTerm);

                    window.location.href = '{{ route('cross_cut.export_pdf') }}?' + params.toString();
                });
            }
            // Edit Modal Handler
            $('.btn-edit-modal').on('click', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                $('#editModal').modal('show');
                $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

                $.ajax({
                    url: url,
                    success: function (response) {
                        $('#editModalBody').html(response);
                    },
                    error: function (xhr) {
                        var message = 'Gagal memuat data checksheet.';
                        if (xhr.status === 404) {
                            message = 'Data checksheet tidak ditemukan.';
                        } else if (xhr.status === 403) {
                            message = 'Anda tidak memiliki akses untuk mengedit checksheet ini.';
                        } else if (xhr.status === 500) {
                            message = 'Terjadi kesalahan pada server.';
                        }
                        $('#editModalBody').html('<div class="alert alert-danger">' + message + '</div>');
                    }
                });
            });
        });
    </script>
@endpush

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Cross Cut Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Cross Cut Image">
                <p id="modalItemName" class="mt-2"></p>
                <p id="modalQcDatetime"></p>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal for each checksheet and type -->
@foreach($checksheets as $cs)
    @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
        @php
            $canReject = false;
            if ($rejectType == 'kashift' && ((auth()->user()->role === 'kashift' || auth()->user()->role === 'admin') && !$cs->kashift_qc)) {
                $canReject = true;
            } elseif ($rejectType == 'supervisor' && ((auth()->user()->role === 'supervisor' || auth()->user()->role === 'admin') && $cs->kashift_qc && $cs->kashift_qc !== 'REJECTED' && !$cs->supervisor_qc)) {
                $canReject = true;
            } elseif ($rejectType == 'asst_manager' && ((auth()->user()->role === 'asst_manager' || auth()->user()->role === 'admin') && $cs->supervisor_qc && $cs->supervisor_qc !== 'REJECTED' && !$cs->asst_manager_qc)) {
                $canReject = true;
            } elseif ($rejectType == 'manager' && ((auth()->user()->role === 'manager' || auth()->user()->role === 'admin') && $cs->asst_manager_qc && $cs->asst_manager_qc !== 'REJECTED' && !$cs->manager_qc)) {
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
                        <form action="{{ route('cross_cut.reject', ['id' => $cs->id, 'type' => $rejectType]) }}" method="POST">
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
                                <button type="submit" class="btn btn-danger">
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

@push('scripts')
    <script src="{{ asset('js/vendor/jspdf.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/jspdf.plugin.autotable.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Character counter for rejection remarks
            @foreach($checksheets as $cs)
                @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
                    const textarea{{ $cs->id }}{{ $rejectType }} = document.getElementById('rejection_remarks{{ $cs->id }}{{ $rejectType }}');
                    const charCount{{ $cs->id }}{{ $rejectType }} = document.getElementById('charCount{{ $cs->id }}{{ $rejectType }}');
                    if (textarea{{ $cs->id }}{{ $rejectType }}) {
                        textarea{{ $cs->id }}{{ $rejectType }}.addEventListener('input', function () {
                            charCount{{ $cs->id }}{{ $rejectType }}.textContent = this.value.length;
                        });
                    }
                @endforeach
            @endforeach

                                                                                                                                                                                                                                        // Live Search Functionality
                                                                                                                                                                                                                                        const liveSearchInput = document.getElementById('liveSearch');
            const checksheetTable = document.getElementById('checksheetTable');
            const tableRows = checksheetTable.querySelectorAll('tbody tr');

            if (liveSearchInput) {
                liveSearchInput.addEventListener('keyup', function () {
                    const searchTerm = this.value.toLowerCase().trim();

                    tableRows.forEach(function (row) {
                        // Get text content from relevant columns (Cross Cut has different column indices)
                        const itemPart = row.cells[8] ? row.cells[8].textContent.toLowerCase() : '';
                        const customer = row.cells[9] ? row.cells[9].textContent.toLowerCase() : '';
                        const partNo = row.cells[10] ? row.cells[10].textContent.toLowerCase() : '';
                        const initials = row.cells[14] ? row.cells[14].textContent.toLowerCase() : '';

                        // Check if any column contains the search term
                        const matches = itemPart.includes(searchTerm) ||
                            customer.includes(searchTerm) ||
                            partNo.includes(searchTerm) ||
                            initials.includes(searchTerm);

                        // Show or hide row based on match
                        if (matches || searchTerm === '') {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // View Image Modal
            const viewImageButtons = document.querySelectorAll('.view-image-btn');
            const modalImage = document.getElementById('modalImage');
            const modalItemName = document.getElementById('modalItemName');
            const modalQcDatetime = document.getElementById('modalQcDatetime');
            const jsonInfoUrlTemplate = "{{ route('cross_cut.show', ['id' => ':id']) }}";

            viewImageButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const checksheetId = this.getAttribute('data-id');
                    const fetchUrl = jsonInfoUrlTemplate.replace(':id', checksheetId);

                    fetch(fetchUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            modalImage.src = data.image_url;
                            modalItemName.textContent = `Item: ${data.item_name}`;
                            modalQcDatetime.textContent = `QC Datetime: ${data.qc_datetime}`;
                        })
                        .catch(error => {
                            console.error('Error fetching image data:', error);
                            modalImage.src = '';
                            modalItemName.textContent = 'Gagal memuat data gambar.';
                            modalQcDatetime.textContent = '';
                        });
                });
            });

            // PDF Export
            const { jsPDF } = window.jspdf;
            const exportPdfBtn = document.getElementById('exportPdfBtn');

            if (exportPdfBtn) {
                exportPdfBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const doc = new jsPDF('landscape');

                    // Header Table
                    doc.autoTable({
                        startY: 10,
                        head: [],
                        body: [
                            [
                                { content: '', rowSpan: 4, styles: { minCellHeight: 25, valign: 'middle' } },
                                { content: 'LAPORAN CHECKSHEET CROSS CUT', rowSpan: 4, styles: { halign: 'center', valign: 'middle', fontSize: 14, fontStyle: 'bold' } },
                                { content: 'No. Dokumen', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                                { content: 'QC-KRW-F-XXXX', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                            ],
                            [
                                { content: 'Tgl. Terbit', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                                { content: '-', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                            ],
                            [
                                { content: 'Revisi Ke', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                                { content: '-', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                            ],
                            [
                                { content: 'Tgl. Revisi', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                                { content: '-', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                            ]
                        ],
                        theme: 'grid',
                        styles: {
                            lineColor: [0, 0, 0],
                            lineWidth: 0.1,
                            cellPadding: 1.5
                        },
                        columnStyles: {
                            0: { cellWidth: 30 },
                            1: {},
                            2: {},
                            3: {}
                        },
                        didDrawCell: function (data) {
                            if (data.section === 'body' && data.column.index === 0) {
                                const img = document.getElementById('pdf-logo');
                                if (img) {
                                    try {
                                        doc.addImage(img, 'JPEG', data.cell.x + 2, data.cell.y + 2, 26, 21);
                                    } catch (err) {
                                        console.warn('Error adding logo:', err);
                                    }
                                }
                            }
                        }
                    });

                    const finalY = doc.lastAutoTable.finalY;
                    doc.setFontSize(6);
                    doc.text('Tanggal Export: ' + new Date().toLocaleString(), 14, finalY + 5);

                    const originalTable = document.getElementById('checksheetTable');
                    const tableClone = originalTable.cloneNode(true);

                    // Remove no-export elements
                    const noExportElements = tableClone.querySelectorAll('.no-export');
                    noExportElements.forEach(el => el.remove());

                    // Process clone rows to flatten Kimia column
                    const kimiaCells = tableClone.querySelectorAll('.kimia-col');
                    kimiaCells.forEach(kimiaCell => {
                        const nestedTable = kimiaCell.querySelector('table');
                        if (nestedTable) {
                            const trs = nestedTable.querySelectorAll('tr');
                            let text = [];
                            trs.forEach(tr => {
                                const th = tr.querySelector('th');
                                const td = tr.querySelector('td');
                                if (th && td) {
                                    text.push(`${th.textContent.trim()}: ${td.textContent.trim()}`);
                                }
                            });
                            // Use textContent to replace the entire table content
                            kimiaCell.textContent = text.join('\n');
                            // Ensure styles are reset if they were inherited oddly
                            kimiaCell.style.whiteSpace = 'pre-wrap';
                        }
                    });

                    tableClone.style.position = 'absolute';
                    tableClone.style.top = '-9999px';
                    tableClone.style.left = '-9999px';
                    document.body.appendChild(tableClone);

                    doc.autoTable({
                        html: tableClone,
                        startY: finalY + 7,
                        theme: 'grid',
                        styles: {
                            fontSize: 5,
                            cellPadding: 1,
                            valign: 'middle',
                            halign: 'center',
                            lineColor: [0, 0, 0],
                            lineWidth: 0.1
                        },
                        headStyles: {
                            fillColor: [78, 115, 223],
                            textColor: [255, 255, 255],
                            valign: 'middle',
                            halign: 'center',
                            lineColor: [0, 0, 0],
                            lineWidth: 0.1
                        },
                        exportHiddenCells: false
                    });

                    document.body.removeChild(tableClone);
                    doc.save('Laporan_Checksheet_Cross_Cut_' + new Date().toISOString().slice(0, 10) + '.pdf');
                });
            });
                                                                                        }

        // Edit Modal Handler
        $('.btn-edit-modal').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $('#editModal').modal('show');
            $('#editModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

            $.ajax({
                url: url,
                success: function (response) {
                    $('#editModalBody').html(response);
                },
                error: function () {
                    $('#editModalBody').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
                }
            });
        });

        // Status Modal Handler
        $('.btn-status-modal').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $('#statusModal').modal('show');
            $('#statusModalBody').html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');

            $.ajax({
                url: url,
                success: function (response) {
                    $('#statusModalBody').html(response);
                },
                error: function (xhr) {
                    var message = 'Gagal memuat data status approval.';
                    if (xhr.status === 404) {
                        message = 'Data tidak ditemukan.';
                    } else if (xhr.status === 403) {
                        message = 'Anda tidak memiliki akses untuk mengubah status approval ini.';
                    }
                    $('#statusModalBody').html('<div class="alert alert-danger">' + message + '</div>');
                }
            });
        });
                                                                                    });
    </script>
    @php $bulkApproveRoute = route('cross_cut.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')
@endpush