@extends('layouts.admin')

@section('title', 'Laporan Data Checksheet Inprocess')

@section('content')
    <x-plant-header title="Laporan Data Checksheet In-Process" :plant="request()->get('plant')" />
    <!-- Hidden Logo for PDF Export -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk Inprocess</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('in_process.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    @if(auth()->user()->role === 'admin')
                        <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                            <div class="form-group mb-0">
                                <label for="plant_select" class="small font-weight-bold text-primary">Plant Context</label>
                                <select name="plant" id="plant_select" class="form-control form-control-sm border-primary"
                                    onchange="this.form.submit()">
                                    <option value="" {{ !request('plant') ? 'selected' : '' }}>All Plants</option>
                                    <option value="karawang" {{ request('plant') == 'karawang' ? 'selected' : '' }}>Karawang
                                    </option>
                                    <option value="jakarta" {{ request('plant') == 'jakarta' ? 'selected' : '' }}>Jakarta</option>
                                </select>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                    @endif

                    <!-- Filter Tanggal -->
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="start_date" class="small font-weight-bold">Dari Tanggal</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm"
                                value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="end_date" class="small font-weight-bold">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                                value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <!-- Button Cari dan Reset -->
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            <a href="{{ route('in_process.index', ['plant' => request('plant')]) }}"
                                class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>

                    <!-- Live Search -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label for="search" class="small font-weight-bold">Pencarian</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="liveSearch" class="form-control form-control-sm"
                                    placeholder="Cari Item Part, Customer, Part No, Inisial..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Button Export PDF -->
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2 text-right">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold d-block">&nbsp;</label>
                            <a href='#' id="exportPdfBtn" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="checksheetTable">
                    <thead>
                        <tr class="text-center">
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal</th>
                            <th rowspan="2" class="align-middle">Jam (Before)</th>
                            <th rowspan="2" class="align-middle">Jam (After)</th>
                            <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            <th rowspan="2" class="align-middle">Shift</th>
                            <th rowspan="2" class="align-middle">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Part No</th>
                            <th rowspan="2" class="align-middle">Total Qty</th>
                            <th rowspan="2" class="align-middle">Sampling Qty</th>
                            <th rowspan="2" class="align-middle">Check Dimensi</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial</th>
                            <th rowspan="2" class="align-middle">Kashift QC</th>
                            <th rowspan="2" class="align-middle">Supervisor QC</th>
                            <th rowspan="2" class="align-middle">Asst. Manager QC</th>
                            <th rowspan="2" class="align-middle">Manager QC</th>
                            <th rowspan="2" class="align-middle">Keterangan</th>
                            @if(auth()->user()->role !== 'inspector')
                                <th rowspan="2" class="no-export align-middle">Aksi</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="width: 5%">Pcs</th>
                            <th>Jenis NG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checksheets as $checksheet)
                            <tr class="text-center">
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">
                                    {{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->created_at->format('H:i') }}</td>
                                <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->shift }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->name ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->total_qty }}</td>
                                <td class="align-middle">{{ $checksheet->sampling_qty }}</td>

                                {{-- Dimension Check Detail --}}
                                <td class="align-middle p-0" data-dimensions='@json($checksheet->dimension_check)'>
                                    @php
                                        $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true);
                                        $itemPartNumber = $checksheet->item->part_number ?? '';
                                        $standards = $partDimensionStandards[$itemPartNumber] ?? [];
                                    @endphp
                                    @if(is_array($dimensions) && count($dimensions) > 0)
                                        <div style="max-height: 170px; overflow-y: auto; font-size: 0.7rem;">
                                            <table class="table table-bordered table-sm m-0">
                                                <thead class="text-center" style="font-size: 0.6rem;">
                                                    {{-- Standard Row --}}
                                                    <tr class="bg-light" style="font-size: 0.55rem;">
                                                        <th class="p-1">Std</th>
                                                        @for ($j = 1; $j <= 8; $j++)
                                                            <th class="p-1 text-muted">
                                                                @if(isset($standards[$j]))
                                                                    {{ $standards[$j]['size'] }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </th>
                                                        @endfor
                                                    </tr>
                                                    {{-- Tolerance Row --}}
                                                    <tr class="bg-light" style="font-size: 0.55rem;">
                                                        <th class="p-1">Tol</th>
                                                        @for ($j = 1; $j <= 8; $j++)
                                                            <th class="p-1 text-muted">
                                                                @if(isset($standards[$j]))
                                                                    ±{{ $standards[$j]['tolerance'] }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </th>
                                                        @endfor
                                                    </tr>
                                                    {{-- Main Header Row --}}
                                                    <tr>
                                                        <th>Cav</th>
                                                        <th>Ø1</th>
                                                        <th>Ø2</th>
                                                        <th>Ø3</th>
                                                        <th>Ø4</th>
                                                        <th>Ø5</th>
                                                        <th>Ø6</th>
                                                        <th>Ø7</th>
                                                        <th>Ø8</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- Actual Measurements --}}
                                                    @foreach($dimensions as $cavity => $points)
                                                        <tr>
                                                            <td class="font-weight-bold p-1">{{ $cavity }}</td>
                                                            @for ($j = 1; $j <= 8; $j++)
                                                                @php
                                                                    $val = $points[$j] ?? '-';
                                                                    $isNG = false;
                                                                    if (isset($standards[$j]) && is_numeric($val)) {
                                                                        $std = $standards[$j];
                                                                        $min = $std['size'] - $std['tolerance'];
                                                                        $max = $std['size'] + $std['tolerance'];
                                                                        if ($val < $min || $val > $max) {
                                                                            $isNG = true;
                                                                        }
                                                                    }
                                                                @endphp
                                                                <td class="p-1 {{ $isNG ? 'text-danger font-weight-bold' : '' }}" @if($isNG)
                                                                style="color: #dc3545 !important; font-weight: bold !important;" @endif>
                                                                    {{ $val }}
                                                                </td>
                                                            @endfor
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

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
                                            } elseif (is_string($d)) {
                                                $pcsLines[] = 1;
                                                $nameLines[] = $d;
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
                                <td class="align-middle">{{ $checksheet->operator_initials }}</td>

                                {{-- Kashift QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->kashift_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                    @elseif($checksheet->kashift_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->kashift_qc }}</small>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->kashift_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->kashift_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Supervisor QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->supervisor_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                    @elseif($checksheet->supervisor_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->supervisor_qc }}</small>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->supervisor_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->supervisor_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Asst Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->asst_manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                    @elseif($checksheet->asst_manager_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->asst_manager_qc }}</small>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->asst_manager_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->asst_manager_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                    @elseif($checksheet->manager_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->manager_qc }}</small>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->manager_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</small>
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
                                                @if(!str_contains($checksheet->remarks ?? '', '[SORTIR_CLOSED]'))
                                                    <span class="text-danger small font-weight-bold ml-1">
                                                        <i class="fas fa-clock"></i> STATUS: OPEN
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($checksheet->remarks)) !!}
                                    @endif
                                </td>


                                @if(auth()->user()->role !== 'inspector')
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 350px;">
                                        {{-- Action Buttons for Approvals --}}
                                        @php
                                            $isAdmin = auth()->user()->role === 'admin';
                                            $canApproveKashift = (auth()->user()->role === 'kashift' || $isAdmin) && (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED');
                                            $canApproveSupervisor = (auth()->user()->role === 'supervisor' || $isAdmin) && (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED');
                                            $canApproveAsst = (auth()->user()->role === 'asst_manager' || $isAdmin) && (!$checksheet->asst_manager_qc || $checksheet->asst_manager_qc === 'REJECTED');
                                            $canApproveManager = (auth()->user()->role === 'manager' || $isAdmin) && (!$checksheet->manager_qc || $checksheet->manager_qc === 'REJECTED');
                                        @endphp

                                        @if($canApproveKashift)
                                            <form
                                                action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'kashift']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kashift)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' KS' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kashift)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveSupervisor)
                                            <form
                                                action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'supervisor', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' SPV' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveAsst)
                                            <form
                                                action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'asst_manager', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (AM)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' AM' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (AM)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}asst_manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveManager)
                                            <form
                                                action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'manager', 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (MGR)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' MGR' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (MGR)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('admin.in_process.edit_approval', $checksheet->id) }}"
                                                class="btn btn-info btn-sm m-1" title="Edit Approval Status" style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        <a href="{{ route('in_process.edit', $checksheet->id) }}" class="btn btn-warning btn-sm m-1"
                                            title="Edit" style="min-width: 110px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form
                                            action="{{ route('in_process.destroy', ['id' => $checksheet->id, 'plant' => request('plant')]) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm m-1 btn-delete" title="Delete"
                                                style="min-width: 110px;">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $checksheets->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Rejection Modal for each checksheet and type -->
    @foreach($checksheets as $cs)
        @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
            @php
                $isAdmin = auth()->user()->role === 'admin';
                $canReject = false;
                if ($rejectType == 'kashift' && ((auth()->user()->role === 'kashift' || $isAdmin) && (!$cs->kashift_qc || $cs->kashift_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'supervisor' && ((auth()->user()->role === 'supervisor' || $isAdmin) && (!$cs->supervisor_qc || $cs->supervisor_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'asst_manager' && ((auth()->user()->role === 'asst_manager' || $isAdmin) && (!$cs->asst_manager_qc || $cs->asst_manager_qc === 'REJECTED'))) {
                    $canReject = true;
                } elseif ($rejectType == 'manager' && ((auth()->user()->role === 'manager' || $isAdmin) && (!$cs->manager_qc || $cs->manager_qc === 'REJECTED'))) {
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
                                action="{{ route('in_process.reject', ['id' => $cs->id, 'type' => $rejectType, 'plant' => request('plant')]) }}"
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

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Pass standards to JS
        const partDimensionStandards = @json($partDimensionStandards);

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
                        window.location.href = '{{ route('in_process.index') }}?' + params.toString();
                    }, 500);
                });
            }

            const { jsPDF } = window.jspdf;

            document.getElementById('exportPdfBtn').addEventListener('click', function (e) {
                e.preventDefault();

                const doc = new jsPDF('landscape');

                // Generate Header Table
                doc.autoTable({
                    startY: 10,
                    head: [],
                    body: [
                        [
                            { content: '', rowSpan: 4, styles: { minCellHeight: 25, valign: 'middle' } },
                            { content: 'LAPORAN CHECK SHEET INPROCESS', rowSpan: 4, styles: { halign: 'center', valign: 'middle', fontSize: 14, fontStyle: 'bold' } },
                            { content: 'No. Dokumen', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: 'QC-KRW-F-0004', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Tgl. Terbit', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '25/09/2015', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Revisi Ke', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '3', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Tgl. Revisi', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '30/09/2020', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ]
                    ],
                    theme: 'grid',
                    styles: {
                        lineColor: [0, 0, 0],
                        lineWidth: 0.1,
                        cellPadding: 1.5
                    },
                    columnStyles: {
                        0: { cellWidth: 30 }, // Logo Column
                        1: {}, // Title Column
                        2: {}, // Label Column
                        3: {}  // Value Column
                    },
                    didDrawCell: function (data) {
                        // Draw Logo
                        if (data.section === 'body' && data.column.index === 0) {
                            const img = document.getElementById('pdf-logo');
                            if (img) {
                                try {
                                    doc.addImage(img, 'PNG', data.cell.x + 2, data.cell.y + 2, 26, 21);
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

                // Clone table and remove 'Aksi' column
                const originalTable = document.getElementById('checksheetTable');
                const tableClone = originalTable.cloneNode(true);

                const noExportElements = tableClone.querySelectorAll('.no-export');
                noExportElements.forEach(el => el.remove());

                tableClone.style.position = 'absolute';
                tableClone.style.top = '-9999px';
                tableClone.style.left = '-9999px';
                document.body.appendChild(tableClone);

                // Generate Main Data Table
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
                    columnStyles: {
                        11: { halign: 'left' } // Check Dimensi
                    },
                    didParseCell: function (data) {
                        // Check Dimensi (Column 11) - Parse JSON to reserve space
                        if (data.section === 'body' && data.column.index === 11) {
                            try {
                                const raw = data.cell.raw.getAttribute('data-dimensions');
                                if (raw) {
                                    const dimensions = JSON.parse(raw);
                                    // Set text to newlines to reserve height for custom drawing
                                    // We need 1 row for header + 1 row per cavity
                                    if (dimensions && typeof dimensions === 'object') {
                                        let lineCount = 1; // Header
                                        lineCount += Object.keys(dimensions).length;
                                        data.cell.text = Array(lineCount).fill(' ').join('\n');

                                        // Store data for didDrawCell
                                        data.cell.customDimensions = dimensions;
                                        // Get part number from column 7 (index 7)
                                        // Note: data.row.cells is an array-like object of Cell objects
                                        // We can try to access the text of column 7. 
                                        // Since didParseCell runs for each cell, the row might not be fully populated yet if we are at index 11.
                                        // However, column 7 is before 11, so it should be parsed.
                                        if (data.row.cells[7]) {
                                            let partNo = data.row.cells[7].text;
                                            if (Array.isArray(partNo)) partNo = partNo.join('');
                                            data.cell.customPartNumber = partNo.trim();
                                        }
                                    }
                                }
                            } catch (e) {
                                console.error('Error parsing dimensions', e);
                            }
                        }

                        // Detail NG (Col 14, 15) - Hide default text for manual drawing if multiple lines
                        if (data.section === 'body' && (data.column.index === 14 || data.column.index === 15)) {
                            const td = data.cell.raw;
                            if (td && td.children.length > 1) {
                                data.cell.styles.textColor = [255, 255, 255];
                            }
                        }
                    },
                    didDrawCell: function (data) {
                        // Check Dimensi (Column 11) - Manual Grid Draw
                        if (data.section === 'body' && data.column.index === 11 && data.cell.customDimensions) {
                            const dimensions = data.cell.customDimensions;
                            const partNo = data.cell.customPartNumber;
                            const standards = partDimensionStandards[partNo] || [];

                            const x = data.cell.x;
                            const y = data.cell.y;
                            const w = data.cell.width;
                            const h = data.cell.height;

                            // Calculate grid
                            const cavities = Object.keys(dimensions);
                            const rowCount = cavities.length + 1; // +1 for Header
                            const colCount = 9; // Cav, 1..8

                            const rowH = h / rowCount;
                            const colW = w / colCount;

                            doc.setFontSize(4); // Small font for grid
                            doc.setLineWidth(0.05);
                            doc.setDrawColor(0, 0, 0);

                            // Draw Header Row
                            const headers = ['Cv', '1', '2', '3', '4', '5', '6', '7', '8'];
                            headers.forEach((hdr, i) => {
                                // Draw cell border
                                doc.rect(x + (i * colW), y, colW, rowH);
                                // Draw text
                                doc.setTextColor(0, 0, 0);
                                doc.text(hdr, x + (i * colW) + (colW / 2), y + (rowH / 2), { align: 'center', baseline: 'middle' });
                            });

                            // Draw Data Rows
                            cavities.forEach((cavity, rIndex) => {
                                const cy = y + ((rIndex + 1) * rowH);
                                const points = dimensions[cavity];

                                // Col 0: Cavity Name
                                doc.rect(x, cy, colW, rowH);
                                doc.setTextColor(0, 0, 0);
                                doc.text(String(cavity), x + (colW / 2), cy + (rowH / 2), { align: 'center', baseline: 'middle' });

                                // Cols 1..8: Points
                                for (let j = 1; j <= 8; j++) {
                                    const val = points[j];
                                    const cx = x + (j * colW);

                                    doc.rect(cx, cy, colW, rowH);

                                    if (val !== undefined && val !== null) {
                                        // Check NG
                                        let isNG = false;
                                        if (standards[j] && !isNaN(val)) {
                                            const std = standards[j];
                                            const min = std.size - std.tolerance;
                                            const max = std.size + std.tolerance;
                                            if (val < min || val > max) {
                                                isNG = true;
                                            }
                                        }

                                        if (isNG) {
                                            doc.setTextColor(255, 0, 0);
                                        } else {
                                            doc.setTextColor(0, 0, 0);
                                        }
                                        doc.text(String(val), cx + (colW / 2), cy + (rowH / 2), { align: 'center', baseline: 'middle' });
                                    } else {
                                        doc.setTextColor(0, 0, 0);
                                        doc.text('-', cx + (colW / 2), cy + (rowH / 2), { align: 'center', baseline: 'middle' });
                                    }
                                }
                            });

                            // Prevent default text drawing (if any remains)
                            return false;
                        }

                        // Detail NG (Col 14, 15) - Manual Draw
                        if (data.section === 'body' && (data.column.index === 14 || data.column.index === 15)) {
                            const td = data.cell.raw;
                            if (td && td.children.length > 1) {
                                const count = td.children.length;
                                const height = data.cell.height;
                                const step = height / count;
                                const textArray = data.cell.text;

                                for (let i = 1; i < count; i++) {
                                    const y = data.cell.y + (step * i);
                                    doc.setDrawColor(0, 0, 0);
                                    doc.setLineWidth(0.1);
                                    doc.line(data.cell.x, y, data.cell.x + data.cell.width, y);
                                }

                                doc.setTextColor(0, 0, 0);
                                doc.setFontSize(5);
                                for (let i = 0; i < count; i++) {
                                    const yCenter = data.cell.y + (step * i) + (step / 2);
                                    const textStr = Array.isArray(textArray) ? (textArray[i] || '') : (i === 0 ? textArray : '');
                                    doc.text(String(textStr), data.cell.x + data.cell.width / 2, yCenter, { align: 'center', baseline: 'middle' });
                                }
                            }
                        }
                    },
                    exportHiddenCells: false
                });

                document.body.removeChild(tableClone);
                doc.save('Laporan_Checksheet_Inprocess_' + new Date().toISOString().slice(0, 10) + '.pdf');
            });
        });
    </script>
@endpush