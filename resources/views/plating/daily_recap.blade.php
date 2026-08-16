@extends('layouts.admin')

@section('title', 'Rekap Harian Verification Plating')

@section('content')
<style>
    /* UI Styles */
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #recapTable, #performanceTable, #ngRecapTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
    }
    #recapTable td, #recapTable th, #performanceTable td, #performanceTable th, #ngRecapTable td, #ngRecapTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }
    #recapTable tbody td, #performanceTable tbody td, #ngRecapTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        color: #000000 !important;
        font-size: 0.75rem !important;
        padding: 8px 12px !important;
    }
    #recapTable thead th, #performanceTable thead th, #ngRecapTable thead th {
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        background-color: #f8fafc !important;
        color: #000000 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.7rem !important;
        letter-spacing: 0.2px;
        padding: 10px 12px !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    #recapTable tfoot td, #performanceTable tfoot td, #ngRecapTable tfoot td {
        background-color: #f8fafc !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        border-top: 2px solid #e2e8f0 !important;
    }
    .custom-filter-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    /* Print Styles to match plating/print.blade.php */
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            margin: 0;
            padding: 0;
            background: #fff !important;
        }
        .navbar, .topbar, .sidebar, .footer, .btn, .no-print, .custom-filter-card, .d-flex.align-items-center.justify-content-between.mb-3, .card-header {
            display: none !important;
        }
        #content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
        }
        .container-fluid {
            padding: 0 !important;
            width: 100% !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 0 !important;
        }
        .card-body {
            padding: 0 !important;
        }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; display: table !important; }
        .header-table td { border: 1px solid #000 !important; padding: 5px; vertical-align: middle; }
        .logo { width: 90px; text-align: center; }
        .title { text-align: center; font-size: 13px; font-weight: bold; color: #000; }
        .doc-info { width: 160px; font-size: 8.5px; }
        .doc-info table { width: 100%; border: none !important; }
        .doc-info td { border: none !important; padding: 1px 2px; }

        .sub-header { margin-bottom: 8px; font-size: 10px; display: block !important; }

        #recapTable, #performanceTable {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: auto !important;
        }
        #recapTable thead th, #performanceTable thead th {
            border: 1px solid #000 !important;
            background-color: #f2f2f2 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 8px !important;
            padding: 4px 6px !important;
            position: static !important;
        }
        #recapTable tbody td, #performanceTable tbody td {
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
            font-size: 9px !important;
            color: #000 !important;
        }
        #recapTable tfoot td, #performanceTable tfoot td {
            border: 1px solid #000 !important;
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 9px !important;
        }
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
        #recapTable .badge {
            background-color: transparent !important;
            border: none !important;
            padding: 0 !important;
            color: inherit !important;
            font-weight: normal !important;
            box-shadow: none !important;
            display: inline !important;
        }
        a[href]:after {
            content: none !important;
        }
    }
</style>

<div class="container-fluid">
    <!-- UI Header (Hidden when printing) -->
    <div class="d-flex align-items-center justify-content-between mb-3 no-print">
        <div>
            <h1 class="h4 mb-0 text-gray-800 font-weight-bold">
                REKAP HARIAN PLATING
            </h1>
        </div>
        <div class="d-flex" style="gap: 10px;">
            <button onclick="window.print()" class="btn btn-sm btn-dark shadow-sm rounded-pill px-3">
                <i class="fas fa-print fa-sm mr-1"></i> Print Recap
            </button>
            <a href="{{ route('plating.index') }}" class="btn btn-sm btn-secondary shadow-sm rounded-pill px-3">
                <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Print Header (Visible only when printing) -->
    <div class="d-none d-print-block">
        <table class="header-table">
            <tr>
                <td class="logo" style="border-right: none !important; width: 120px;">
                    <img src="{{ asset('master item/ipp.jpg') }}" style="max-width: 100px; max-height: 60px; object-fit: contain;">
                </td>
                <td class="title" style="border-left: none !important; text-align: center;">
                    <div style="font-size: 16px;">REKAP HARIAN VERIFICATION PLATING</div>
                </td>
            </tr>
        </table>
        <div class="sub-header">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} @if($startDate != $endDate) - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} @endif
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Plant:</strong> {{ strtoupper($plantName) }}
            @if(request('shift'))
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Shift:</strong> {{ request('shift') }}
            @endif
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden custom-filter-card no-print">
        <div class="card-body py-2">
            <form action="{{ route('plating.daily_recap') }}" method="GET" class="row align-items-center">
                
                <div class="col-md-auto mb-2 mb-md-0 d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Mulai:</label>
                    <input type="date" name="start_date" class="form-control form-control-sm shadow-sm" value="{{ $startDate }}" style="width: 140px;">
                </div>

                <div class="col-md-auto mb-2 mb-md-0 d-flex align-items-center ml-md-2">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Sampai:</label>
                    <input type="date" name="end_date" class="form-control form-control-sm shadow-sm" value="{{ $endDate }}" style="width: 140px;">
                </div>

                <div class="col-md-auto mb-2 mb-md-0 d-flex align-items-center ml-md-3">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Shift:</label>
                    <select name="shift" class="form-control form-control-sm shadow-sm" style="width: 110px;">
                        <option value="">Semua</option>
                        <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                        <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                        <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                    </select>
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-4">
                        <i class="fas fa-search fa-sm mr-1"></i> Filter
                    </button>
                    <a href="{{ route('plating.daily_recap') }}" class="btn btn-light btn-sm shadow-sm rounded-pill px-3 border ml-1">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                </div>

                <div class="col text-md-right mt-2 mt-md-0">
                    <span class="badge badge-white border px-3 py-2 rounded-pill shadow-sm">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i> 
                        <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M y') }}</strong>
                        @if($startDate != $endDate)
                            - <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M y') }}</strong>
                        @endif
                    </span>
                    <span class="badge badge-white border px-3 py-2 rounded-pill shadow-sm ml-2">
                        <i class="fas fa-industry mr-1 text-primary"></i> <strong>{{ strtoupper($plantName) }}</strong>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- ITEM RECAP CARD -->
    <div class="card shadow mb-4 border-0 rounded-lg overflow-hidden recap-card" id="cardVerifikasiItem">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark">Rekap Verifikasi per Item</h6>
            <button type="button" onclick="printCardSection('cardVerifikasiItem')" class="btn btn-sm btn-outline-dark rounded-pill px-3 no-print" title="Cetak Rekap Ini Saja">
                <i class="fas fa-print fa-sm mr-1"></i> Print Rekap Ini
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="recapTable">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Barang</th>
                            <th>Part Number</th>
                            <th>Customer</th>
                            <th class="text-center">Shift</th>
                            <th class="text-center">Packing</th>
                            <th class="text-center">Total Packing</th>
                            <th class="text-center text-success">Total OK</th>
                            <th class="text-center text-danger">Total NG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recap as $index => $row)
                            <tr>
                                <td class="text-center font-weight small">{{ $index + 1 }}</td>
                                <td class="font-weight">{{ $row->item->name ?? '-' }}</td>
                                <td class="text-uppercase small font-weight">{{ $row->item->part_number ?? '-' }}</td>
                                <td class="small">{{ $row->item->customer ?? '-' }}</td>
                                <td class="text-center">
                                    Shift {{ $row->shift }}
                                </td>
                                <td class="text-center font-weight">{{ number_format($row->packing_size) }} pcs</td>
                                <td class="text-center font-weight">{{ number_format($row->total_packing) }} box/bucket/plastik</td>
                                <td class="text-center font-weight">{{ number_format($row->total_ok_sum) }}</td>
                                <td class="text-center font-weight">{{ number_format($row->total_ng_sum) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="py-5">
                                        <i class="fas fa-folder-open fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">Tidak ada data verification pada kriteria ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- INSPECTOR PERFORMANCE CARD -->
    <div class="card shadow mb-4 border-0 rounded-lg overflow-hidden recap-card" id="cardPerformanceInspector">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark">Rekap Performance Inspector</h6>
            <button type="button" onclick="printCardSection('cardPerformanceInspector')" class="btn btn-sm btn-outline-dark rounded-pill px-3 no-print" title="Cetak Rekap Ini Saja">
                <i class="fas fa-print fa-sm mr-1"></i> Print Rekap Ini
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="performanceTable">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Inisial Inspector</th>
                            <th>Nama Barang</th>
                            <th>Part Number</th>
                            <th class="text-center">Total Qty</th>
                            <th class="text-center" style="background-color: #28a745; color: white;">AKT. DURA (MENIT)</th>
                            <th class="text-center">STD CT (MENIT)</th>
                            <th class="text-center">Target Pencapaian (pcs)</th>
                            <th class="text-center">Plus / Minus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $globalIndex = 1; @endphp
                        @forelse($inspectorRecap->groupBy('operator_initials') as $operator => $rows)
                            @foreach($rows as $index => $row)
                                <tr>
                                    <td class="text-center font-weight small align-middle">
                                        {{ $globalIndex++ }}
                                    </td>
                                    <td class="font-weight-bold text-primary align-middle" style="text-transform: uppercase;">
                                        {{ $operator }}
                                    </td>
                                    <td class="font-weight">{{ $row->item->name ?? '-' }}</td>
                                    <td class="text-uppercase small font-weight">{{ $row->item->part_number ?? '-' }}</td>
                                    <td class="text-center font-weight">{{ number_format($row->total_qty_sum) }} pcs</td>
                                    <td class="text-center font-weight">{{ number_format($row->total_act / 60, 1) }}</td>
                                    <td class="text-center small">
                                        @if($row->sct > 0)
                                            {{ number_format($row->sct, 2) }}
                                        @else
                                            <span class="text-muted italic">Not set</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        @if($row->sct > 0 )
                                            {{ number_format($row->target, 0) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        @if($row->sct > 0)
                                            @php $roundedPM = round($row->plus_minus); @endphp
                                            @if($roundedPM == 0)
                                                0
                                            @else
                                                {{ $roundedPM > 0 ? '+' : '' }}{{ $roundedPM }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-5">
                                        <i class="fas fa-user-clock fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">Tidak ada data performa pada kriteria ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- NG DATA RECAP CARD -->
    <div class="card shadow mb-4 border-0 rounded-lg overflow-hidden recap-card" id="cardNgDefect">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark">Rekap Data NG Per Defect</h6>
            <button type="button" onclick="printCardSection('cardNgDefect')" class="btn btn-sm btn-outline-dark rounded-pill px-3 no-print" title="Cetak Rekap Ini Saja">
                <i class="fas fa-print fa-sm mr-1"></i> Print Rekap Ini
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="ngRecapTable">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Barang</th>
                            <th>Part Number</th>
                            <th>Customer</th>
                            <th class="text-center">Total Checked</th>
                            <th class="text-center text-danger">Defect NG</th>
                            <th class="text-center text-danger">Qty NG</th>
                            <th class="text-center text-danger">% NG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $globalNgIndex = 1; @endphp
                        @forelse($ngRecap as $itemIndex => $row)
                            @foreach($row->defects as $defectIndex => $defect)
                                <tr>
                                    <td class="text-center font-weight small align-middle">
                                        {{ $globalNgIndex++ }}
                                    </td>
                                    <td class="font-weight align-middle">
                                        {{ $row->item->name ?? '-' }}
                                    </td>
                                    <td class="text-uppercase small font-weight align-middle">
                                        {{ $row->item->part_number ?? '-' }}
                                    </td>
                                    <td class="small align-middle">
                                        {{ $row->item->customer ?? '-' }}
                                    </td>
                                    <td class="text-center font-weight align-middle">
                                        {{ number_format($row->total_qty_sum) }} pcs
                                    </td>
                                    <td class="font-weight-bold text-danger align-middle">
                                        {{ $defect->defect_type }}
                                    </td>
                                    <td class="text-center font-weight text-danger align-middle">
                                        {{ number_format($defect->defect_qty) }} pcs
                                    </td>
                                    <td class="text-center font-weight-bold text-danger align-middle">
                                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.75rem;">
                                            {{ number_format($defect->percentage, 2) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-5">
                                        <i class="fas fa-check-circle fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">Tidak ada temuan defect NG pada kriteria ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#recapTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            $('#performanceTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            $('#ngRecapTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });
        }
    });

    function printCardSection(cardId) {
        $('.recap-card').addClass('d-print-none');
        $('#' + cardId).removeClass('d-print-none');
        window.print();
        $('.recap-card').removeClass('d-print-none');
    }
</script>
@endpush
