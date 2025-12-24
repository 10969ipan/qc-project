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
                        <td class="align-middle p-0" data-dimensions='{{ $checksheet->dimension_check }}'>
                            @php
                                $dimensions = json_decode($checksheet->dimension_check, true);
                                $itemPartNumber = $checksheet->item->part_number ?? '';
                                $standards = $partDimensionStandards[$itemPartNumber] ?? [];
                            @endphp
                            @if(is_array($dimensions) && count($dimensions) > 0)
                                <div style="max-height: 120px; overflow-y: auto; font-size: 0.7rem;">
                                    <table class="table table-bordered table-sm m-0">
                                        <thead class="text-center" style="font-size: 0.6rem;">
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
                                                        <td class="p-1 {{ $isNG ? 'text-danger font-weight-bold' : '' }}">{{ $val }}</td>
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
                                <span class="badge badge-success">{{ $checksheet->kashift_qc }}</span>
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
                                <span class="badge badge-success">{{ $checksheet->supervisor_qc }}</span>
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
                                <span class="badge badge-success">{{ $checksheet->asst_manager_qc }}</span>
                            @else
                                <span class="badge badge-warning">PENDING</span>
                            @endif
                            @if($checksheet->asst_manager_approved_at)
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->asst_manager_approved_at)->format('d/m/Y H:i') }}</small>
                            @endif
                        </td>

                        {{-- Manager QC --}}
                        <td class="align-middle">
                            @if($checksheet->manager_qc === 'REJECTED')
                                <span class="badge badge-danger">REJECTED</span>
                            @elseif($checksheet->manager_qc)
                                <span class="badge badge-success">{{ $checksheet->manager_qc }}</span>
                            @else
                                <span class="badge badge-warning">PENDING</span>
                            @endif
                            @if($checksheet->manager_approved_at)
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</small>
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
                                $canApproveManager = (auth()->user()->role === 'manager' || auth()->user()->role === 'admin') && !$checksheet->manager_qc;
                            @endphp

                            <div class="mr-2">
                                @if($canApproveKashift)
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <form action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'kashift']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Approve (Kashift)">
                                                <i class="fas fa-check"></i>{{ (auth()->user()->role === 'admin') ? ' KS' : '' }}
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
                                                <i class="fas fa-check"></i>{{ (auth()->user()->role === 'admin') ? ' SPV' : '' }}
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
                                                <i class="fas fa-check"></i>{{ (auth()->user()->role === 'admin') ? ' AM' : '' }}
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

                                @if($canApproveManager)
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <form action="{{ route('in_process.approve', ['id' => $checksheet->id, 'type' => 'manager']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Approve (MGR)">
                                                <i class="fas fa-check"></i>{{ (auth()->user()->role === 'admin') ? ' MGR' : '' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('in_process.reject', ['id' => $checksheet->id, 'type' => 'manager']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger" title="Reject (MGR)">
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
            
            const doc = new jsPDF('landscape');
            
            // Generate Header Table
            doc.autoTable({
                startY: 10,
                head: [],
                body: [
                    [
                        { content: '', rowSpan: 4, styles: { minCellHeight: 25, valign: 'middle' } },
                        { content: 'LAPORAN CHECK SHEET INPROCESS', rowSpan: 4, styles: { halign: 'center', valign: 'middle', fontSize: 14, fontStyle: 'bold' } },
                        { content: 'No. Dokumen', styles: { halign: 'left', valign: 'middle', fontSize: 4 } },
                        { content: 'QC-KRW-F-0004', styles: { halign: 'left', valign: 'middle', fontSize: 4 } }
                    ],
                    [
                        { content: 'Tgl. Terbit', styles: { halign: 'left', valign: 'middle', fontSize: 4 } },
                        { content: '25/09/2015', styles: { halign: 'left', valign: 'middle', fontSize: 4 } }
                    ],
                    [
                        { content: 'Revisi Ke', styles: { halign: 'left', valign: 'middle', fontSize: 4 } },
                        { content: '3', styles: { halign: 'left', valign: 'middle', fontSize: 4 } }
                    ],
                    [
                        { content: 'Tgl. Revisi', styles: { halign: 'left', valign: 'middle', fontSize: 4 } },
                        { content: '30/09/2020', styles: { halign: 'left', valign: 'middle', fontSize: 4 } }
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
                    1: { }, // Title Column
                    2: { cellWidth: 25 }, // Label Column
                    3: { cellWidth: 25 }  // Value Column
                },
                didDrawCell: function(data) {
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
                    0: { cellWidth: 8 },  // No
                    1: { cellWidth: 12 }, // Tanggal
                    2: { cellWidth: 8 },  // Jam B
                    3: { cellWidth: 8 },  // Jam A
                    4: { cellWidth: 8 },  // Cycle
                    5: { cellWidth: 8 },  // Shift
                    6: { cellWidth: 15 }, // Barang
                    7: { cellWidth: 15 }, // Part No
                    8: { cellWidth: 10 }, // Cust
                    9: { cellWidth: 8 },  // Tot Qty
                    10: { cellWidth: 8 }, // Samp Qty
                    11: { cellWidth: 28, halign: 'left' }, // Check Dimensi
                    12: { cellWidth: 8 }, // OK
                    13: { cellWidth: 8 }, // NG
                    14: { cellWidth: 6 }, // Pcs
                    15: { cellWidth: 15 }, // Jenis NG
                    16: { cellWidth: 10 }, // Judg
                    17: { cellWidth: 8 }, // Inisial
                    18: { cellWidth: 11 }, // KS
                    19: { cellWidth: 11 }, // SPV
                    20: { cellWidth: 11 }, // AM
                    21: { cellWidth: 11 }, // MGR
                    22: { cellWidth: 15 }  // Ket
                },
                didParseCell: function(data) {
                    // Check Dimensi (Column 11) - Parse JSON and format text
                    if (data.section === 'body' && data.column.index === 11) {
                        try {
                            const raw = data.cell.raw.getAttribute('data-dimensions');
                            if (raw) {
                                const dimensions = JSON.parse(raw);
                                let text = '';
                                if (dimensions && typeof dimensions === 'object') {
                                    for (const [cavity, points] of Object.entries(dimensions)) {
                                        // Simple text format: "Cav 1: Ø1=X, Ø2=Y..."
                                        let pointsStr = [];
                                        for (const [key, val] of Object.entries(points)) {
                                            pointsStr.push(`Ø${key}:${val}`);
                                        }
                                        text += `Cav ${cavity}: ${pointsStr.join(', ')}\n`;
                                    }
                                }
                                data.cell.text = text.trim();
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
                didDrawCell: function(data) {
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
            doc.save('Laporan_Checksheet_Inprocess_' + new Date().toISOString().slice(0,10) + '.pdf');
        });
    });
</script>
@endpush
