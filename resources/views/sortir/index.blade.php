@extends('layouts.admin')

@section('title', 'Hasil Data Sortir')

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
                                    class="btn btn-secondary btn-sm mr-2" title="Reset Filter">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                                <button type="button" id="exportPdfBtn" class="btn btn-danger btn-sm" title="Export to PDF">
                                    <i class="fas fa-file-pdf"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="sortirTable">
                    <thead>
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
                            <th rowspan="2" class="align-middle"><x-approval-label level="kashift" /></th>
                            <th rowspan="2" class="align-middle">Supervisor QC</th>
                            <th rowspan="2" class="align-middle">Keterangan</th>
                            @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'karu_qc']))
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
                                        @php
                                            $isAdmin = auth()->user()->role === 'admin';
                                            $user = auth()->user();
                                            $isJakarta = optional($user->plant)->code === 'jakarta';
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

                                        @if(auth()->user()->role == 'admin')
                                            <a href="{{ route('sortir.edit', ['id' => $checksheet->id, 'plant' => request('plant')]) }}"
                                                class="btn btn-warning btn-sm m-1">
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
                                    $isJakarta = optional($user->plant)->code === 'jakarta';
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
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
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

            // PDF Export Functionality
            const { jsPDF } = window.jspdf;
            document.getElementById('exportPdfBtn').addEventListener('click', function (e) {
                e.preventDefault();
                const doc = new jsPDF('landscape');

                // Header Table
                doc.autoTable({
                    startY: 10,
                    head: [],
                    body: [
                        [
                            { content: '', rowSpan: 4, styles: { minCellHeight: 25, valign: 'middle' } },
                            { content: 'LAPORAN DATA HASIL SORTIR', rowSpan: 4, styles: { halign: 'center', valign: 'middle', fontSize: 14, fontStyle: 'bold' } },
                            { content: 'No. Dokumen', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: 'QC-KRW-F-0214', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Tgl. Terbit', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '01/01/2026', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Revisi Ke', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '0', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ],
                        [
                            { content: 'Tgl. Revisi', styles: { halign: 'left', valign: 'middle', fontSize: 7 } },
                            { content: '-', styles: { halign: 'left', valign: 'middle', fontSize: 7 } }
                        ]
                    ],
                    theme: 'grid',
                    styles: { lineColor: [0, 0, 0], lineWidth: 0.1, cellPadding: 1.5 },
                    columnStyles: { 0: { cellWidth: 30 } },
                    didDrawCell: function (data) {
                        if (data.section === 'body' && data.column.index === 0) {
                            const img = document.getElementById('pdf-logo');
                            if (img) {
                                try { doc.addImage(img, 'PNG', data.cell.x + 2, data.cell.y + 2, 26, 21); }
                                catch (err) { console.warn('Error adding logo:', err); }
                            }
                        }
                    }
                });

                const finalY = doc.lastAutoTable.finalY;
                doc.setFontSize(6);
                doc.text('Tanggal Export: ' + new Date().toLocaleString(), 14, finalY + 5);

                // Clone table and remove 'Aksi' column
                const originalTable = document.getElementById('sortirTable');
                const tableClone = originalTable.cloneNode(true);
                const noExportElements = tableClone.querySelectorAll('.no-export');
                noExportElements.forEach(el => el.remove());

                tableClone.style.position = 'absolute';
                tableClone.style.top = '-9999px';
                tableClone.style.left = '-9999px';
                document.body.appendChild(tableClone);

                doc.autoTable({
                    html: tableClone,
                    startY: finalY + 7,
                    theme: 'grid',
                    styles: { fontSize: 5, cellPadding: 1, valign: 'middle', halign: 'center', lineColor: [0, 0, 0], lineWidth: 0.1 },
                    headStyles: { fillColor: [78, 115, 223], textColor: [255, 255, 255], valign: 'middle', halign: 'center', lineColor: [0, 0, 0], lineWidth: 0.1 },
                    didParseCell: function (data) {
                        // Detail NG (Col 12, 13) - Hide default text for manual drawing if multiple lines
                        if (data.section === 'body' && (data.column.index === 12 || data.column.index === 13)) {
                            const td = data.cell.raw;
                            if (td && td.children.length > 1) {
                                data.cell.styles.textColor = [255, 255, 255];
                            }
                        }
                    },
                    didDrawCell: function (data) {
                        if (data.section === 'body' && (data.column.index === 12 || data.column.index === 13)) {
                            const td = data.cell.raw;
                            if (td && td.children.length > 1) {
                                const count = td.children.length;
                                const height = data.cell.height;
                                const step = height / count;
                                const textArray = data.cell.text;

                                for (let i = 1; i < count; i++) {
                                    const yLine = data.cell.y + (step * i);
                                    doc.setDrawColor(0, 0, 0);
                                    doc.setLineWidth(0.1);
                                    doc.line(data.cell.x, yLine, data.cell.x + data.cell.width, yLine);
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
                    }
                });

                document.body.removeChild(tableClone);
                doc.save('Laporan_Data_Hasil_Sortir_' + new Date().toISOString().slice(0, 10) + '.pdf');
            });
        });
    </script>
@endpush