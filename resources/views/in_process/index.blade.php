@extends('layouts.admin')

@section('title', 'Laporan Data Checksheet Inprocess')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Laporan Data Checksheet Inprocess</h1>
    <!-- Hidden Logo for PDF Export -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">
</div>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Data Masuk Inprocess</h6>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-12">
                <form action="{{ route('in_process.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="start_date">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="item_id">Filter Part/Barang</label>
                            <select name="item_id" class="form-control">
                                <option value="">Semua Barang</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="approval_status">Status Approval</label>
                            <select name="approval_status" class="form-control">
                                <option value="">Semua</option>
                                <option value="Pending" {{ request('approval_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ request('approval_status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('approval_status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end">
                            <button type="submit" class="btn btn-primary mr-2">Cari</button>
                            <a href="{{ route('in_process.index') }}" class="btn btn-secondary mr-2">Reset</a>
                            
                            @if(auth()->user()->role !== 'inspector')
                                <a href="#" id="exportPdfBtn" class="btn btn-danger">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
                        <th rowspan="2" class="align-middle">Barang</th>
                        <th rowspan="2" class="align-middle">Part No</th>
                        <th rowspan="2" class="align-middle">Customer</th>
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
                        <td class="align-middle">{{ $checksheet->date }}</td>
                        <td class="align-middle">{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                        <td class="align-middle">{{ $checksheet->created_at->format('H:i') }}</td>
                        <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                        <td class="align-middle">{{ $checksheet->shift }}</td>
                        <td class="align-middle">{{ $checksheet->item->name ?? '-' }}</td>
                        <td class="align-middle">{{ $checksheet->item->part_number ?? '-' }}</td>
                        <td class="align-middle">{{ $checksheet->item->customer ?? '-' }}</td>
                        <td class="align-middle">{{ $checksheet->total_qty }}</td>
                        <td class="align-middle">{{ $checksheet->sampling_qty }}</td>
                        
                        {{-- Dimension Check Detail --}}
                        <td class="align-middle p-0">
                             @php
                                $dims = json_decode($checksheet->dimension_check, true);
                             @endphp
                             @if(is_array($dims) && count($dims) > 0)
                                <div style="max-height: 100px; overflow-y: auto; font-size: 0.75rem;">
                                    <table class="table table-borderless table-sm m-0">
                                    @foreach($dims as $key => $val)
                                        @if($val)
                                        <tr>
                                            <td class="py-0 px-1 text-right border-right"><small>Ø{{ $key + 1 }}</small></td>
                                            <td class="py-0 px-1 text-left"><small class="font-weight-bold">{{ $val }}</small></td>
                                        </tr>
                                        @endif
                                    @endforeach
                                    </table>
                                </div>
                             @else
                                <span class="text-muted">-</span>
                             @endif
                        </td>

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

                        {{-- Supervisor QC --}}
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

                        {{-- Asst Manager QC --}}
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
                        
                        <td class="align-middle">{{ $checksheet->remarks }}</td>
                        
                        @if(auth()->user()->role !== 'inspector')
                        <td class="align-middle d-flex align-items-center justify-content-center no-export">
                            {{-- Action Buttons for Approvals --}}
                            @php
                                $canApproveKashift = (auth()->user()->role === 'kashift' || auth()->user()->role === 'admin') && !$checksheet->kashift_qc;
                                $canApproveSupervisor = (auth()->user()->role === 'supervisor' || auth()->user()->role === 'admin') && !$checksheet->supervisor_qc;
                                $canApproveAsst = (auth()->user()->role === 'asst_manager' || auth()->user()->role === 'admin') && !$checksheet->asst_manager_qc;
                            @endphp

                            <div class="mr-2">
                                @if($canApproveKashift)
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <form action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'kashift']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Approve (Kashift)">
                                                <i class="fas fa-check"></i>{{ auth()->user()->role === 'admin' ? ' KS' : '' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('in_process.reject', ['id' => $checksheet->id, 'type' => 'kashift']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger" title="Reject (Kashift)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                @if($canApproveSupervisor)
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <form action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'supervisor']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Approve (SPV)">
                                                <i class="fas fa-check"></i>{{ auth()->user()->role === 'admin' ? ' SPV' : '' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('in_process.reject', ['id' => $checksheet->id, 'type' => 'supervisor']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger" title="Reject (SPV)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                @if($canApproveAsst)
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <form action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'asst_manager']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Approve (AM)">
                                                <i class="fas fa-check"></i>{{ auth()->user()->role === 'admin' ? ' AM' : '' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('in_process.reject', ['id' => $checksheet->id, 'type' => 'asst_manager']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger" title="Reject (AM)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            {{-- Edit/Delete Actions --}}
                            {{-- Allowed for: Admin, Supervisor, Kashift, AsstManager. Not Inspector. --}}
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('in_process.edit', $checksheet->id) }}" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('in_process.destroy', $checksheet->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
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
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const { jsPDF } = window.jspdf;

        document.getElementById('exportPdfBtn').addEventListener('click', function(e) {
            e.preventDefault();
            
            const doc = new jsPDF('landscape'); // Landscape to fit columns
            
            // Define Document Info Text
            const docInfo = 'No. Dokumen:QC-KRW-F-0004\nTgl. Terbit:25/09/2015\nRevisi Ke:3\nTgl. Revisi:30/09/2020';

            // Generate Header Table
            doc.autoTable({
                startY: 10,
                head: [],
                body: [
                    [
                        { content: '', styles: { minCellHeight: 25, valign: 'middle' } },
                        { content: 'LAPORAN CHECK SHEET INPROCESS', styles: { halign: 'center', valign: 'middle', fontSize: 14, fontStyle: 'bold' } },
                        { content: docInfo, styles: { halign: 'left', valign: 'middle', fontSize: 8 } }
                    ]
                ],
                theme: 'grid',
                styles: {
                    lineColor: [0, 0, 0],
                    lineWidth: 0.1,
                    cellPadding: 2
                },
                columnStyles: {
                    0: { cellWidth: 30 }, // Logo Column
                    1: { }, // Title Column (auto width)
                    2: { cellWidth: 45 } // Doc Info Column
                },
                didDrawCell: function(data) {
                    // Draw Logo in the first cell of the body
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
                    0: { cellWidth: 12 }, // Tanggal
                    1: { cellWidth: 8 }, // Jam Before
                    2: { cellWidth: 8 }, // Jam After
                    3: { cellWidth: 15 }, // Cycle Time
                    4: { cellWidth: 10 },  // Shift
                    5: { cellWidth: 20 }, // Barang
                    12: { cellWidth: 6 }, // Pcs
                    13: { cellWidth: 22 }, // Jenis NG
                    19: { cellWidth: 30 }  // Keterangan
                },
                didParseCell: function(data) {
                    // Hide default text for multi-item cells in Pcs (12) and Jenis NG (13) to draw manually later
                    if (data.section === 'body' && (data.column.index === 12 || data.column.index === 13)) {
                        const td = data.cell.raw;
                        if (td && td.children.length > 1) {
                            data.cell.styles.textColor = [255, 255, 255]; // Hide original text
                        }
                    }
                },
                didDrawCell: function(data) {
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

            doc.save('Laporan_Checksheet_Sub_Assy_' + new Date().toISOString().slice(0,10) + '.pdf');
        });
    });
</script>
@endpush
