@extends('layouts.admin')

@section('title', 'Laporan Data Cross Cut')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Laporan Data Checksheet Cross Cut</h1>
    <!-- Hidden Logo for PDF Export -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">
</div>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Masuk</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('cross_cut.index') }}" method="GET" class="mb-4">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label for="start_date">Tanggal Awal</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label for="end_date">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label for="item_id">Item Part</label>
                        <select id="item_id" name="item_id" class="form-control">
                            <option value="">Semua Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label for="approval_status">Status Approval</label>
                        <select id="approval_status" name="approval_status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 text-right">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Cari
                        </button>
                        <a href="{{ route('cross_cut.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                        <a href="#" id="exportPdfBtn" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
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
                        <th rowspan="2" class="align-middle">Item Part</th>
                        <th rowspan="2" class="align-middle no-export">Hasil Cross Cut</th>
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
                        <th rowspan="2" class="align-middle no-export">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($checksheets as $checksheet)
                    <tr class="text-center">
                        <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('Y-m-d') }}</td>
                        <td class="align-middle">{{ $checksheet->production_shift }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('Y-m-d') }}</td>
                        <td class="align-middle">{{ $checksheet->qc_shift }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}</td>
                        <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                        <td class="align-middle">{{ $checksheet->item->name }}</td>
                        <td class="align-middle no-export">
                            <button class="btn btn-primary btn-sm view-image-btn" data-id="{{ $checksheet->id }}" data-toggle="modal" data-target="#imageModal">
                                View Image
                            </button>
                        </td>
                        <td class="align-middle p-0 kimia-col">
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
                        <td class="align-middle no-export">
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
                        <td colspan="{{ auth()->user()->role !== 'inspector' ? 20 : 19 }}" class="text-center">No data available</td>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
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
            exportPdfBtn.addEventListener('click', function(e) {
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
                        1: { },
                        2: { },
                        3: { }
                    },
                    didDrawCell: function(data) {
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
                doc.save('Laporan_Checksheet_Cross_Cut_' + new Date().toISOString().slice(0,10) + '.pdf');
            });
        }
    });
</script>
@endpush
