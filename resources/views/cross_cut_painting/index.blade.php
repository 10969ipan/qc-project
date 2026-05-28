@extends('layouts.admin')

@section('title', 'Cross Cut Painting')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        LAPORAN CROSS CUT PAINTING
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');

                            // Resolve menu ID for permission checks
                            $currentMenu = \App\Models\AppMenu::where('route', 'cross_cut_painting.index')->first();
                            $menuId = $currentMenu ? $currentMenu->id : null;
                            $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
                        @endphp
                        <span class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0" style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 250px;">
                            <div class="row mb-1">
                                <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                                <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0215</div>
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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Laporan Cross Cut Painting</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('cross_cut_painting.index') }}" method="GET" class="mb-4" id="filterFormPainting">
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                <div class="row align-items-end">
                    <!-- Live Search -->
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

                    <!-- Filter Shift -->
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="shift" class="small font-weight-bold">Shift QC</label>
                            <select name="shift" id="shift" class="form-control form-control-sm">
                                <option value="">Semua Shift</option>
                                <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                                <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                                <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                            </select>
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
                                <a href="{{ route('cross_cut_painting.index', ['plant' => request('plant')]) }}"
                                    class="btn btn-secondary btn-sm mr-2 no-loader" title="Reset Filter">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                                @if($canExport)
                                <a href="{{ route('cross_cut_painting.export_pdf') }}"
                                    class="btn btn-danger btn-sm no-loader" title="Export to PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Export
                                </a>
                                <a href="{{ route('cross_cut_painting.print') }}"
                                    class="btn btn-success btn-sm no-loader ml-2" title="Print" target="_blank">
                                    <i class="fas fa-print"></i> Print
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <hr>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="text-center">
                        <tr class="text-center">
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal Produksi</th>
                            <th rowspan="2" class="align-middle">Shift Prod</th>
                            <th rowspan="2" class="align-middle">Tanggal QC</th>
                            <th rowspan="2" class="align-middle">Shift QC</th>
                            <th rowspan="2" class="align-middle">Jam Before</th>
                            <th rowspan="2" class="align-middle">Jam After</th>
                            <th rowspan="2" class="align-middle">Cycle (s)</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Pengujian (Foto/Tap/Pencil)</th>
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
                    <tbody style="font-size: 12px;">
                        @forelse ($checksheets as $checksheet)
                            <tr class="text-center">
                                <td class="align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->production_shift }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->qc_shift }}</td>
                                <td class="align-middle font-weight-bold text-primary">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                </td>
                                <td class="align-middle font-weight-bold text-success">
                                    {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}
                                </td>
                                <td class="align-middle font-weight-bold">{{ $checksheet->cycle_time ?? '-' }}</td>
                                <td class="align-middle text-nowrap text-left">{{ $checksheet->item->name ?? 'N/A' }}</td>

                                {{-- Unified Foto (Cross / Tap / Pencil) --}}
                                <td class="text-center align-middle">
                                    @if ($checksheet->image_path)
                                        <button type="button" class="btn btn-sm btn-info view-image-btn p-1 px-2" data-toggle="modal"
                                            data-target="#viewImageModal"
                                            data-image="{{ asset('storage/' . $checksheet->image_path) }}">
                                            <i class="fas fa-image"></i>
                                        </button>
                                        <small class="d-block mt-1">Tap: {{ $checksheet->tap_test ?? '-' }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle font-weight-bold {{ $checksheet->position_remark_judgment === 'OK' ? 'text-success' : 'text-danger' }}">
                                    {{ $checksheet->position_remark_judgment }}
                                </td>
                                <td class="align-middle text-uppercase">{{ $checksheet->operator_initials }}</td>

                                {{-- Approval Columns --}}
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
                                        @elseif($status && $status !== 'Pending')
                                            <span class="badge badge-success px-2 py-1" style="font-size: 0.75rem;">
                                                <i class="fas fa-check-circle"></i> APPROVED
                                            </span>
                                            <br><small class="text-muted" style="font-size: 10px;">{{ $status }}</small>
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
                                                <button type="button" class="btn btn-success btn-sm m-1" title="Approve ({{ $label }})"
                                                    data-toggle="modal" data-target="#approvalModal" 
                                                    data-id="{{ $checksheet->id }}" data-type="{{ $role }}"
                                                    data-label="{{ getApprovalLabel($role, $plantCode) }}"
                                                    style="min-width: 80px;">
                                                    <i class="fas fa-check"></i> Approve {{ $label }}
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm m-1" title="Reject ({{ $label }})"
                                                    onclick="toggleApprovalModal('{{ $checksheet->id }}', '{{ $role }}', '{{ getApprovalLabel($role, $plantCode) }}', true)"
                                                    style="min-width: 80px;">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                        @endforeach

                                        {{-- Standard Actions --}}
                                        @if($isAdmin)
                                            <a href="{{ route('admin.cross_cut_painting.edit_approval', ['id' => $checksheet->id]) }}"
                                                class="btn btn-info btn-sm m-1 btn-status-modal no-loader" title="Edit Approval Status"
                                                style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        
                                        @if(!in_array($user->role, ['manager', 'asst_manager', 'manager_plating']))
                                            <a href="javascript:void(0)" class="btn btn-warning btn-sm m-1 edit-btn" 
                                                data-id="{{ $checksheet->id }}" title="Edit"
                                                style="min-width: 80px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('cross_cut_painting.destroy', $checksheet->id) }}"
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
            <div class="d-flex justify-content-end mt-3">
                {{ $checksheets->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>

    {{-- View Image Modal --}}
    <div class="modal fade" id="viewImageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-image mr-2"></i>Bukti Foto & Tap Test</h5>
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

                        <div class="form-group">
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
                docNo: 'QC-KRW-F-0193',
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
                        if (pair[1]) params.append(pair[0], pair[pair[0] === 'search' ? 'search' : pair[0]]); // Key fix: FormData uses name attribute
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
@endpush
