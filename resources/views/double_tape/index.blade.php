@extends('layouts.admin')

@section('title', 'Double Tape')

@section('content')
<style>
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: 1px solid #dee2e6 !important;
    }
    #checksheetTable { border-collapse: separate !important; border-spacing: 0 !important; }
    
    /* Pengaturan sticky TH Global */
    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #4e73df !important;
        color: white !important;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 6px 4px !important;
        border: 1px solid #ffffff44 !important;
        vertical-align: middle !important;
        height: 35px !important;
    }

    /* Baris pertama sticky di atas: 0 */
    #checksheetTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
    }

    /* Baris kedua sticky di atas: 35px (menyesuaikan tinggi baris 1) */
    #checksheetTable > thead > tr:nth-child(2) > th {
        top: 35px !important;
        z-index: 104 !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
    }

    /* Sel Rowspan ditangani secara khusus untuk mengisi kedua baris dengan benar */
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        height: 70px !important;
    }
</style>
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        LAPORAN DATA CHECKSHEET DOUBLE TAPE
                        @php
                            $plantCode = 'karawang'; // For Double Tape it is always Karawang
                        @endphp
                        <span class="badge badge-primary d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
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
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0213</div>
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
    <!-- Logo Tersembunyi untuk Ekspor PDF -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk Double Tape</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('double_tape.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <!-- Pencarian Langsung -->
                    <div class="col-lg-3 col-md-12 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label for="search" class="small font-weight-bold">Pencarian</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="liveSearch" name="search" class="form-control" placeholder="Cari..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                    </div>

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

                    <!-- Tombol: Cari, Reset, Ekspor -->
                    <div class="col-lg-3 col-md-4 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold d-block">&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary btn-sm mr-2" title="Cari Data">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('double_tape.index', ['plant' => request('plant')]) }}"
                                    class="btn btn-secondary btn-sm mr-2 no-loader" title="Reset Filter">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                                <a href="{{ route('double_tape.export_pdf', request()->query()) }}"
                                    class="btn btn-danger btn-sm no-loader btn-download" title="Export to PDF">
                                    <i class="fas fa-file-pdf"></i> Export
                                </a>
                            </div>
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
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial</th>

                            <th colspan="4" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle">Keterangan</th>
                            @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                <th rowspan="2" class="no-export align-middle">Aksi</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="width: 5%">Pcs</th>
                            <th>Jenis NG</th>
                            <th style="font-size: 10px;">Kashift QC</th>
                            <th style="font-size: 10px;"><x-approval-label level="supervisor" /></th>
                            <th style="font-size: 10px;"><x-approval-label level="asst_manager" /></th>
                            <th style="font-size: 10px;"><x-approval-label level="manager" /></th>
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
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
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
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
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
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
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
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
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
                                            </div>
                                        @endif
                                        {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($checksheet->remarks)) !!}
                                    @endif
                                </td>

                                @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 350px;">
                                        @if($loop->first)
                                            @include('partials.bulk_approve_button')
                                        @endif
                                        @php
                                            $user = auth()->user();
                                            $isAdmin = $user->role === 'admin';
                                            $canApproveKashift = ($user->role === 'kashift' || $isAdmin) && (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED');
                                            $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) && (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED');
                                            $canApproveAsst = ($user->role === 'asst_manager' || $isAdmin) && (!$checksheet->asst_manager_qc || $checksheet->asst_manager_qc === 'REJECTED');
                                            $canApproveManager = ($user->role === 'manager' || $isAdmin) && (!$checksheet->manager_qc || $checksheet->manager_qc === 'REJECTED');
                                        @endphp

                                        @if($canApproveKashift)
                                            <form
                                                action="{{ route('double_tape.approve', ['id' => $checksheet->id, 'type' => 'kashift']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kashift)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i> Approve KS
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
                                                action="{{ route('double_tape.approve', ['id' => $checksheet->id, 'type' => 'supervisor']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i> Approve SPV
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
                                                action="{{ route('double_tape.approve', ['id' => $checksheet->id, 'type' => 'asst_manager']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (AM)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i> Approve AM
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
                                                action="{{ route('double_tape.approve', ['id' => $checksheet->id, 'type' => 'manager']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (MGR)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i> Approve MGR
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (MGR)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        @if($isAdmin)
                                            <a href="{{ route('double_tape.edit_approval', ['id' => $checksheet->id]) }}"
                                                class="btn btn-info btn-sm m-1 btn-status-modal no-loader" title="Edit Approval Status"
                                                style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                            <a href="{{ route('double_tape.edit', $checksheet->id) }}"
                                                class="btn btn-warning btn-sm m-1 btn-edit-modal no-loader" title="Edit"
                                                style="min-width: 110px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('double_tape.destroy', $checksheet->id) }}" method="POST"
                                                class="d-inline">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $checksheets->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Checksheet Double Tape</h5>
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

    <!-- Modal Status -->
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

    <!-- Modal Penolakan -->
    @foreach($checksheets as $cs)
        @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
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
                        <form action="{{ route('double_tape.reject', ['id' => $cs->id, 'type' => $rejectType]) }}" method="POST">
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
                                    <textarea class="form-control" id="rejection_remarks{{ $cs->id }}{{ $rejectType }}"
                                        name="rejection_remarks" rows="4"
                                        placeholder="Masukkan alasan rejection (minimal 10 karakter)" required minlength="10"
                                        maxlength="500"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Tolak Checksheet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

@endsection

@@push('scripts')
    <script src="{{ asset('js/checksheet/double-tape.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initDoubleTapeIndex({
                indexRoute: "{{ route('double_tape.index') }}"
            });
        });
    </script>
    @php $bulkApproveRoute = route('double_tape.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')
@endpush