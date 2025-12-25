@extends('layouts.admin')

@section('title', 'Laporan Data Cross Cut')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Laporan Data Checksheet Cross Cut</h1>
</div>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Masuk</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('cross_cut.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date">Tanggal Awal</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_date">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="item_id">Item Part</label>
                        <select id="item_id" name="item_id" class="form-control">
                            <option value="">Semua Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="approval_status">Status Approval</label>
                        <select id="approval_status" name="approval_status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('cross_cut.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <a href="{{ route('cross_cut.export_pdf', request()->query()) }}" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </form>
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr class="text-center">
                        <th rowspan="2" class="align-middle">No</th>
                        <th rowspan="2" class="align-middle">Tanggal Produksi</th>
                        <th rowspan="2" class="align-middle">Tanggal QC</th>
                        <th rowspan="2" class="align-middle">Shift Produksi</th>
                        <th rowspan="2" class="align-middle">Shift QC</th>
                        <th rowspan="2" class="align-middle">Jam Before</th>
                        <th rowspan="2" class="align-middle">Jam After</th>
                        <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                        <th rowspan="2" class="align-middle">Item Part</th>
                        <th rowspan="2" class="align-middle">Hasil Cross Cut</th>
                        <th rowspan="2" class="align-middle">Kimia</th>
                        <th rowspan="2" class="align-middle">Posisi Remark</th>
                        <th rowspan="2" class="align-middle">Result Remark</th>
                        <th rowspan="2" class="align-middle">Inisial</th>
                        <th rowspan="2" class="align-middle">Kashift QC</th>
                        <th rowspan="2" class="align-middle">Supervisor QC</th>
                        <th rowspan="2" class="align-middle">Asst. Manager QC</th>
                        <th rowspan="2" class="align-middle">Manager QC</th>
                        <th rowspan="2" class="align-middle">Keterangan</th>
                        @if(auth()->user()->role !== 'inspector')
                        <th rowspan="2" class="align-middle">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($checksheets as $checksheet)
                    <tr class="text-center">
                        <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('Y-m-d') }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('Y-m-d') }}</td>
                        <td class="align-middle">{{ $checksheet->production_shift }}</td>
                        <td class="align-middle">{{ $checksheet->qc_shift }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}</td>
                        <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                        <td class="align-middle">{{ $checksheet->item->name }}</td>
                        <td class="align-middle">
                            <button class="btn btn-primary btn-sm view-image-btn" data-id="{{ $checksheet->id }}" data-toggle="modal" data-target="#imageModal">
                                View Image
                            </button>
                        </td>
                        <td class="align-middle p-0">
                            <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
                                <tbody>
                                    <tr>
                                        <th class="p-1">Copper</th>
                                        <td class="p-1">{{ $checksheet->chemical_copper ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="p-1">Nikel</th>
                                        <td class="p-1">{{ $checksheet->chemical_nikel ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="p-1">Eching</th>
                                        <td class="p-1">{{ $checksheet->chemical_eching ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="p-1">Abu</th>
                                        <td class="p-1">{{ $checksheet->chemical_abu ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="align-middle">{{ $checksheet->position_remark_judgment }} - {{ $checksheet->position_remark_no_lot }}</td>
                        <td class="align-middle">{{ $checksheet->result_remark }}</td>
                        <td class="align-middle">{{ $checksheet->operator_initials }}</td>
                        
                        {{-- Approval Status Columns --}}
                        <td class="align-middle">
                            @if($checksheet->kashift_qc === 'REJECTED')
                                <span class="badge badge-danger">REJECTED</span>
                            @elseif($checksheet->kashift_qc)
                                <span class="badge badge-success">APPROVED</span>
                            @else
                                <span class="badge badge-warning">PENDING</span>
                            @endif
                            @if($checksheet->kashift_approved_at)
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->kashift_approved_at)->format('d/m/Y H:i') }}</small>
                            @endif
                        </td>
                        <td class="align-middle">
                            @if($checksheet->supervisor_qc === 'REJECTED')
                                <span class="badge badge-danger">REJECTED</span>
                            @elseif($checksheet->supervisor_qc)
                                <span class="badge badge-success">APPROVED</span>
                            @else
                                <span class="badge badge-warning">PENDING</span>
                            @endif
                             @if($checksheet->supervisor_approved_at)
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->supervisor_approved_at)->format('d/m/Y H:i') }}</small>
                            @endif
                        </td>
                        <td class="align-middle">
                            @if($checksheet->asst_manager_qc === 'REJECTED')
                                <span class="badge badge-danger">REJECTED</span>
                            @elseif($checksheet->asst_manager_qc)
                                <span class="badge badge-success">APPROVED</span>
                            @else
                                <span class="badge badge-warning">PENDING</span>
                            @endif
                            @if($checksheet->asst_manager_approved_at)
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->asst_manager_approved_at)->format('d/m/Y H:i') }}</small>
                            @endif
                        </td>
                        <td class="align-middle">
                            @if($checksheet->manager_qc === 'REJECTED')
                                <span class="badge badge-danger">REJECTED</span>
                            @elseif($checksheet->manager_qc)
                                <span class="badge badge-success">APPROVED</span>
                            @else
                                <span class="badge badge-warning">PENDING</span>
                            @endif
                             @if($checksheet->manager_approved_at)
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</small>
                            @endif
                        </td>
                        
                        <td class="align-middle">{{ $checksheet->keterangan }}</td>

                        @if(auth()->user()->role !== 'inspector')
                        <td class="align-middle">
                            @php
                                $canApproveKashift = (auth()->user()->role === 'kashift' || auth()->user()->role === 'admin') && !$checksheet->kashift_qc;
                                $canApproveSupervisor = (auth()->user()->role === 'supervisor' || auth()->user()->role === 'admin') && $checksheet->kashift_qc && $checksheet->kashift_qc !== 'REJECTED' && !$checksheet->supervisor_qc;
                                $canApproveAsst = (auth()->user()->role === 'asst_manager' || auth()->user()->role === 'admin') && $checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED' && !$checksheet->asst_manager_qc;
                                $canApproveManager = (auth()->user()->role === 'manager' || auth()->user()->role === 'admin') && $checksheet->asst_manager_qc && $checksheet->asst_manager_qc !== 'REJECTED' && !$checksheet->manager_qc;
                            @endphp

                            @if($canApproveKashift)
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'kashift']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success" title="Approve (Kashift)"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form action="{{ route('cross_cut.reject', ['id' => $checksheet->id, 'type' => 'kashift']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" title="Reject (Kashift)"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                            @endif

                            @if($canApproveSupervisor)
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'supervisor']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success" title="Approve (SPV)"><i class="fas fa-check"></i></button>
                                    </form>
                                     <form action="{{ route('cross_cut.reject', ['id' => $checksheet->id, 'type' => 'supervisor']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" title="Reject (SPV)"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                            @endif

                             @if($canApproveAsst)
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'asst_manager']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success" title="Approve (AM)"><i class="fas fa-check"></i></button>
                                    </form>
                                     <form action="{{ route('cross_cut.reject', ['id' => $checksheet->id, 'type' => 'asst_manager']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" title="Reject (AM)"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                            @endif

                             @if($canApproveManager)
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('cross_cut.approve', ['id' => $checksheet->id, 'type' => 'manager']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success" title="Approve (MGR)"><i class="fas fa-check"></i></button>
                                    </form>
                                     <form action="{{ route('cross_cut.reject', ['id' => $checksheet->id, 'type' => 'manager']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" title="Reject (MGR)"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                            @endif

                            <div class="btn-group btn-group-sm mt-1" role="group">
                                <a href="{{ route('cross_cut.edit', $checksheet->id) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('cross_cut.destroy', $checksheet->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role !== 'inspector' ? 19 : 18 }}" class="text-center">No data available</td>
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
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
                        modalImage.src = data.image_url; // This now points to the serveImage route
                        modalItemName.textContent = `Item: ${data.item_name}`;
                        modalQcDatetime.textContent = `QC Datetime: ${data.qc_datetime}`;
                    })
                    .catch(error => {
                        console.error('Error fetching image data:', error);
                        modalImage.src = ''; // Clear image on error
                        modalItemName.textContent = 'Gagal memuat data gambar.';
                        modalQcDatetime.textContent = '';
                    });
            });
        });
    });
</script>
@endpush
