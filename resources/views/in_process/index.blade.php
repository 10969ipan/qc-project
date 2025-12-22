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
                        <td class="align-middle p-0" data-dimensions='{{ $checksheet->dimension_check }}'>
                            @php
                                $dimensions = json_decode($checksheet->dimension_check, true);
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
                                                    @for ($j = 1; $j <= 3; $j++)
                                                        <td class="p-1">{{ $points[$j] ?? '-' }}</td>
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
        
        const doc = new jsPDF('landscape');
        
        const docInfo = 'No. Dokumen:QC-KRW-F-0004\nTgl. Terbit:25/09/2015\nRevisi Ke:3\nTgl. Revisi:30/09/2020';
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
            styles: { lineColor: [0, 0, 0], lineWidth: 0.1, cellPadding: 2 },
            columnStyles: { 0: { cellWidth: 30 }, 1: {}, 2: { cellWidth: 45 } },
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
        doc.setFontSize(8);
        doc.text('Tanggal Export: ' + new Date().toLocaleString(), 14, finalY + 5);

        const table = document.getElementById('checksheetTable');
        const tableRows = table.querySelectorAll('tbody tr');

        // Dynamically generate headers
        const staticHeadStart = [
            'No', 'Tanggal', 'Jam (Before)', 'Jam (After)', 'Cycle Time (s)',
            'Shift', 'Barang', 'Part No', 'Customer', 'Total Qty', 'Sampling Qty'
        ];
        const staticHeadEnd = [
            'OK', 'NG', 'Pcs', 'Jenis NG', 'Judgment', 'Inisial',
            'Kashift QC', 'Supervisor QC', 'Asst. Manager QC', 'Keterangan'
        ];

        const dimensionPoints = new Set();
        tableRows.forEach(row => {
            const dimensionsData = row.querySelector('[data-dimensions]')?.getAttribute('data-dimensions');
            if (dimensionsData) {
                try {
                    const dimensions = JSON.parse(dimensionsData);
                    Object.values(dimensions).forEach(points => {
                        Object.keys(points).forEach(pointKey => dimensionPoints.add(pointKey));
                    });
                } catch (err) {}
            }
        });

        const sortedDimensionPoints = Array.from(dimensionPoints).sort((a, b) => a - b);
        const dimensionHeaders = sortedDimensionPoints.map(p => `Ø${p}`);
        const finalHead = [[...staticHeadStart, 'Cavity', ...dimensionHeaders, ...staticHeadEnd]];

        // Dynamically generate body
        const body = [];
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowData = {
                no: cells[0].innerText.trim(),
                date: cells[1].innerText.trim(),
                timeBefore: cells[2].innerText.trim(),
                timeAfter: cells[3].innerText.trim(),
                cycleTime: cells[4].innerText.trim(),
                shift: cells[5].innerText.trim(),
                itemName: cells[6].innerText.trim(),
                partNumber: cells[7].innerText.trim(),
                customer: cells[8].innerText.trim(),
                totalQty: cells[9].innerText.trim(),
                samplingQty: cells[10].innerText.trim(),
                totalOk: cells[12].innerText.trim(),
                totalNg: cells[13].innerText.trim(),
                defectPcs: Array.from(cells[14].querySelectorAll('div')).map(d => d.innerText.trim()).join('\n') || (cells[14].innerText.trim() !== '-' ? cells[14].innerText.trim() : '-'),
                defectNames: Array.from(cells[15].querySelectorAll('div')).map(d => d.innerText.trim()).join('\n') || (cells[15].innerText.trim() !== '-' ? cells[15].innerText.trim() : '-'),
                judgment: cells[16].innerText.trim(),
                initials: cells[17].innerText.trim(),
                kashift: cells[18].innerText.trim().replace(/\s+/g, ' '),
                supervisor: cells[19].innerText.trim().replace(/\s+/g, ' '),
                asstManager: cells[20].innerText.trim().replace(/\s+/g, ' '),
                remarks: cells[21] ? cells[21].innerText.trim() : ''
            };

            let dimensions = null;
            const dimensionsData = cells[11].getAttribute('data-dimensions');
            if (dimensionsData) {
                try { dimensions = JSON.parse(dimensionsData); } catch (e) {}
            }
            const hasDimensions = dimensions && typeof dimensions === 'object' && Object.keys(dimensions).length > 0;

            if (hasDimensions) {
                const cavities = Object.keys(dimensions);
                cavities.forEach((cavity, index) => {
                    const cavityPoints = dimensions[cavity];
                    const dimensionValues = sortedDimensionPoints.map(p => cavityPoints[p] || '-');
                    if (index === 0) {
                        body.push([
                            rowData.no, rowData.date, rowData.timeBefore, rowData.timeAfter, rowData.cycleTime,
                            rowData.shift, rowData.itemName, rowData.partNumber, rowData.customer,
                            rowData.totalQty, rowData.samplingQty, cavity, ...dimensionValues,
                            rowData.totalOk, rowData.totalNg, rowData.defectPcs, rowData.defectNames,
                            rowData.judgment, rowData.initials, rowData.kashift, rowData.supervisor,
                            rowData.asstManager, rowData.remarks
                        ]);
                    } else {
                        body.push([
                            ...Array(staticHeadStart.length).fill(''),
                            cavity, ...dimensionValues,
                            ...Array(staticHeadEnd.length).fill('')
                        ]);
                    }
                });
            } else {
                const emptyDimensions = Array(dimensionHeaders.length).fill('-');
                body.push([
                    rowData.no, rowData.date, rowData.timeBefore, rowData.timeAfter, rowData.cycleTime,
                    rowData.shift, rowData.itemName, rowData.partNumber, rowData.customer,
                    rowData.totalQty, rowData.samplingQty, '-', ...emptyDimensions,
                    rowData.totalOk, rowData.totalNg, rowData.defectPcs, rowData.defectNames,
                    rowData.judgment, rowData.initials, rowData.kashift, rowData.supervisor,
                    rowData.asstManager, rowData.remarks
                ]);
            }
        });

        doc.autoTable({
            head: finalHead,
            body: body,
            startY: finalY + 7,
            theme: 'grid',
            styles: {
                fontSize: 6, cellPadding: 1, valign: 'middle', halign: 'center',
                lineColor: [0, 0, 0], lineWidth: 0.1
            },
            headStyles: {
                fillColor: [78, 115, 223], textColor: [255, 255, 255],
                fontStyle: 'bold'
            },
            didDrawCell: function(data) {
                // Handle multi-line text for defect columns
                if (data.section === 'body' && (data.column.dataKey === 14 || data.column.dataKey === 15) && /\n/.test(data.cell.text)) {
                    // This is handled by default line break processing in recent jspdf-autotable
                }
            }
        });

        doc.save('Laporan_Checksheet_InProcess_' + new Date().toISOString().slice(0, 10) + '.pdf');
    });
});
</script>
@endpush
