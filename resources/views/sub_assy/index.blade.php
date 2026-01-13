@extends('layouts.admin')

@section('title', 'Laporan Data Checksheet')

@section('content')
    <!-- Hidden Logo for PDF Export -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Masuk Sub Assy</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.checksheets.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
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
                            <a href="{{ route('admin.checksheets.index') }}" class="btn btn-secondary btn-sm">
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
                                <td class="align-middle text-success font-weight-bold">{{ $checksheet->total_ok }}</td>
                                <td class="align-middle text-danger font-weight-bold">{{ $checksheet->total_ng }}</td>

                                @php
                                    $defectsData = json_decode($checksheet->defects, true);
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
                                                    LABEL MERAH:
                                                    {{ $checksheet->next_proses == 'PENDING' ? 'HOLD' : $checksheet->next_proses }}
                                                </span>
                                            </div>
                                        @endif
                                        {{ $checksheet->remarks }}
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
                                                action="{{ route('admin.checksheets.approve', ['id' => $checksheet->id, 'type' => 'kashift']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
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
                                                action="{{ route('admin.checksheets.approve', ['id' => $checksheet->id, 'type' => 'supervisor']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
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
                                                action="{{ route('admin.checksheets.approve', ['id' => $checksheet->id, 'type' => 'asst_manager']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
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
                                                action="{{ route('admin.checksheets.approve', ['id' => $checksheet->id, 'type' => 'manager']) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
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
                                            <a href="{{ route('admin.checksheets.edit_approval', $checksheet->id) }}"
                                                class="btn btn-info btn-sm m-1" title="Edit Approval Status" style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.checksheets.edit', $checksheet->id) }}"
                                            class="btn btn-warning btn-sm m-1" title="Edit" style="min-width: 110px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.checksheets.destroy', $checksheet->id) }}" method="POST"
                                            class="d-inline">
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
                            <form action="{{ route('admin.checksheets.reject', ['id' => $cs->id, 'type' => $rejectType]) }}"
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
                        window.location.href = '{{ route('admin.checksheets.index') }}?' + params.toString();
                    }, 500);
                });
            }

            const { jsPDF } = window.jspdf;

            document.getElementById('exportPdfBtn').addEventListener('click', function (e) {
                e.preventDefault();

                const doc = new jsPDF('landscape'); // Landscape to fit columns

                // Generate Header Table
                doc.autoTable({
                    startY: 10,
                    head: [],
                    body: [
                        [
                            { content: '', rowSpan: 4, styles: { minCellHeight: 25, valign: 'middle' } },
                            { content: 'LAPORAN CHECK SHEET OUTGOING SUB ASSY INJECTION', rowSpan: 4, styles: { halign: 'center', valign: 'middle', fontSize: 14, fontStyle: 'bold' } },
                            { content: 'No. Dokumen', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: 'QC-KRW-F-0213', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Tgl. Terbit', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '25/03/2015', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Revisi Ke', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '3', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Tgl. Revisi', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '22/12/2025', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
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
                        1: {}, // Title Column (auto width)
                        2: {}, // Label Column
                        3: {}  // Value Column
                    },
                    didDrawCell: function (data) {
                        // Draw Logo in the first cell of the body
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

                // Capture the final Y position of the header table
                const finalY = doc.lastAutoTable.finalY;

                // Add Export Date below header
                doc.setFontSize(6);
                doc.text('Tanggal Export: ' + new Date().toLocaleString(), 14, finalY + 5);

                // Clone the table to manipulate it for export (removing 'Aksi' column)
                const originalTable = document.getElementById('checksheetTable');
                const tableClone = originalTable.cloneNode(true);

                // Remove all elements with class 'no-export' from the clone
                const noExportElements = tableClone.querySelectorAll('.no-export');
                noExportElements.forEach(el => el.remove());

                // Temporarily append clone to DOM to ensure correct parsing
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
                        cellPadding: 1.5,
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
                        // Auto width for all columns
                    },
                    didParseCell: function (data) {
                        // Hide default text for multi-item cells in Pcs (12) and Jenis NG (13) to draw manually later
                        if (data.section === 'body' && (data.column.index === 12 || data.column.index === 13)) {
                            const td = data.cell.raw;
                            if (td && td.children.length > 1) {
                                data.cell.styles.textColor = [255, 255, 255]; // Hide original text
                            }
                        }
                    },
                    didDrawCell: function (data) {
                        // Draw horizontal lines and manual text for separated defects in Pcs (12) and Jenis NG (13)
                        if (data.section === 'body' && (data.column.index === 12 || data.column.index === 13)) {
                            const td = data.cell.raw;
                            if (td && td.children.length > 1) {
                                const count = td.children.length;
                                const height = data.cell.height;
                                const step = height / count;
                                const textArray = data.cell.text; // Original text array

                                // Draw horizontal lines between items
                                for (let i = 1; i < count; i++) {
                                    const y = data.cell.y + (step * i);
                                    doc.setDrawColor(0, 0, 0); // Black line
                                    doc.setLineWidth(0.1);
                                    doc.line(data.cell.x, y, data.cell.x + data.cell.width, y);
                                }

                                // Draw text manually centered in each sub-cell
                                doc.setTextColor(0, 0, 0);
                                doc.setFontSize(5);
                                for (let i = 0; i < count; i++) {
                                    const yCenter = data.cell.y + (step * i) + (step / 2);
                                    // Ensure text is string and handle array/string mismatch
                                    const textStr = Array.isArray(textArray) ? (textArray[i] || '') : (i === 0 ? textArray : '');
                                    doc.text(String(textStr), data.cell.x + data.cell.width / 2, yCenter, { align: 'center', baseline: 'middle' });
                                }
                            }
                        }
                    },
                    exportHiddenCells: false
                });

                // Remove the clone from DOM
                document.body.removeChild(tableClone);

                doc.save('Laporan_Checksheet_Sub_Assy_' + new Date().toISOString().slice(0, 10) + '.pdf');
            });
        });
    </script>
@endpush