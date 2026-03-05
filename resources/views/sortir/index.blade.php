@extends('layouts.admin')

@section('title', 'Checksheet Sortir')

@section('content')
    <x-plant-header title="Hasil Data Sortir" :plant="request()->get('plant')" />
    <!-- Hidden Logo for PDF Export -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Hasil Sortir</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('sortir.index') }}" method="GET" class="mb-4">
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

                    <!-- Filter Sumber -->
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <div class="form-group mb-0">
                            <label for="source_type" class="small font-weight-bold">Sumber</label>
                            <select name="source_type" id="source_type" class="form-control form-control-sm">
                                <option value="">Semua Sumber</option>
                                <option value="sub_assy" {{ request('source_type') == 'sub_assy' ? 'selected' : '' }}>SUB ASSY
                                </option>
                                <option value="in_process" {{ request('source_type') == 'in_process' ? 'selected' : '' }}>IN
                                    PROCESS</option>
                                <option value="cross_cut" {{ request('source_type') == 'cross_cut' ? 'selected' : '' }}>CROSS
                                    CUT</option>
                            </select>
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

                    <!-- Buttons: Cari, Reset, Export -->
                    <div class="col-lg-3 col-md-4 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold d-block">&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary btn-sm mr-2" title="Cari Data">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('sortir.index', ['plant' => request('plant')]) }}"
                                    class="btn btn-secondary btn-sm mr-2 no-loader" title="Reset Filter">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                                <a href="{{ route('sortir.export_pdf', request()->query()) }}"
                                    class="btn btn-danger btn-sm no-loader btn-download" title="Export to PDF">
                                    <i class="fas fa-file-pdf"></i> Export
                                </a>
                            </div>
                        </div>
                    </div>
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
                                <td class="align-middle">{{ $checksheet->operator_initials }}</td>

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

                                {{-- Body cells for AM and Manager removed --}}

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
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Supervisor)">
                                                    <i class="fas fa-check"></i> Approve{{ $isAdmin ? ' SPV' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" data-toggle="modal"
                                                data-target="#rejectModal{{ $checksheet->id }}supervisor" title="Reject (Supervisor)">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        @if(auth()->user()->role == 'admin' || auth()->user()->name == 'Marsiah')
                                            <a href="{{ route('sortir.edit', ['id' => $checksheet->id, 'plant' => request('plant')]) }}"
                                                class="btn btn-warning btn-sm m-1 btn-edit-modal no-loader">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form
                                                action="{{ route('sortir.destroy', ['id' => $checksheet->id, 'plant' => request('plant')]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm m-1"
                                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            <!-- Rejection Modals -->
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
                                                        <button type="submit" class="btn btn-danger">Reject</button>
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

            <!-- Pagination -->
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

    <!-- Edit Modal -->
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
    <script src="{{ asset('js/vendor/jspdf.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/jspdf.plugin.autotable.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Live search functionality
            let searchTimeout;
            $('#liveSearch').on('input', function () {
                clearTimeout(searchTimeout);
                const searchTerm = $(this).val();

                searchTimeout = setTimeout(function () {
                    const url = new URL(window.location.href);
                    if (searchTerm) {
                        url.searchParams.set('search', searchTerm);
                    } else {
                        url.searchParams.delete('search');
                    }
                    window.location.href = url.toString();
                }, 500);
            });

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

            // PDF Export Functionality Removed (Replaced by Server-Side Export)
        });
    </script>
    @php $bulkApproveRoute = route('sortir.bulk_approve'); @endphp
    @include('partials.bulk_approve_script')
@endpush