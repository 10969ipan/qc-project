@extends('layouts.admin')

@section('title', 'Rekap Harian Verification')

@section('content')
<style>
    /* UI Styles */
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #recapTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
    }
    #recapTable td, #recapTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }
    #recapTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.75rem !important;
        padding: 8px 12px !important;
    }
    #recapTable thead th {
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.7rem !important;
        letter-spacing: 0.2px;
        padding: 10px 12px !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    #recapTable tfoot td {
        background-color: #f8fafc !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        border-top: 2px solid #e2e8f0 !important;
    }
    .custom-filter-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    /* Print Styles to match in_process/print.blade.php */
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
        .navbar, .topbar, .sidebar, .footer, .btn, .no-print, .custom-filter-card, .d-flex.align-items-center.justify-content-between.mb-3 {
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

        #recapTable {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: auto !important;
        }
        #recapTable thead th {
            border: 1px solid #000 !important;
            background-color: #f2f2f2 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 8px !important;
            padding: 4px 6px !important;
            position: static !important;
        }
        #recapTable tbody td {
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
            font-size: 9px !important;
            color: #000 !important;
        }
        #recapTable tfoot td {
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
                REKAP HARIAN 
            </h1>
            
        </div>
        <div class="d-flex" style="gap: 10px;">
            <button onclick="window.print()" class="btn btn-sm btn-dark shadow-sm rounded-pill px-3">
                <i class="fas fa-print fa-sm mr-1"></i> Print Recap
            </button>
            <a href="{{ route('in_process.index', ['plant' => request('plant')]) }}" class="btn btn-sm btn-secondary shadow-sm rounded-pill px-3">
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
                    <div style="font-size: 16px;">REKAP HARIAN VERIFICATION IN-PROCESS</div>
                </td>
            </tr>
        </table>
        <div class="sub-header">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
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
            <form action="{{ route('in_process.daily_recap') }}" method="GET" class="row align-items-center">
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                
                <div class="col-md-auto mb-2 mb-md-0 d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Tanggal:</label>
                    <input type="date" name="date" class="form-control form-control-sm shadow-sm" value="{{ $date }}" style="width: 150px;">
                </div>

                <div class="col-md-auto mb-2 mb-md-0 d-flex align-items-center ml-md-3">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Shift:</label>
                    <select name="shift" class="form-control form-control-sm shadow-sm" style="width: 120px;">
                        <option value="">Semua Shift</option>
                        <option value="1" {{ request('shift') == '1' ? 'selected' : '' }}>Shift 1</option>
                        <option value="2" {{ request('shift') == '2' ? 'selected' : '' }}>Shift 2</option>
                        <option value="3" {{ request('shift') == '3' ? 'selected' : '' }}>Shift 3</option>
                    </select>
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-4">
                        <i class="fas fa-search fa-sm mr-1"></i> Filter
                    </button>
                    <a href="{{ route('in_process.daily_recap', ['plant' => request('plant')]) }}" class="btn btn-light btn-sm shadow-sm rounded-pill px-3 border ml-1">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                </div>

                <div class="col text-md-right mt-2 mt-md-0">
                    <span class="badge badge-white border px-3 py-2 rounded-pill shadow-sm">
                        <i class="far fa-calendar-alt mr-1 text-primary"></i> <strong>{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</strong>
                    </span>
                    <span class="badge badge-white border px-3 py-2 rounded-pill shadow-sm ml-2">
                        <i class="fas fa-industry mr-1 text-primary"></i> <strong>{{ strtoupper($plantName) }}</strong>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4 border-0 rounded-lg overflow-hidden">
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
                        @php 
                            $totalOkAll = 0; 
                            $totalNgAll = 0; 
                            $totalQtyAll = 0;
                            $totalPackingAll = 0;
                        @endphp
                        @forelse($recap as $index => $row)
                            @php
                                $totalOkAll += $row->total_ok_sum;
                                $totalNgAll += $row->total_ng_sum;
                                $totalQtyAll += $row->total_qty_sum;
                                $totalPackingAll += $row->total_packing;
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold text-muted small">{{ $index + 1 }}</td>
                                <td class="font-weight-bold text-gray-800">{{ $row->item->name ?? '-' }}</td>
                                <td class="text-uppercase small font-weight-bold">{{ $row->item->part_number ?? '-' }}</td>
                                <td class="small">{{ $row->item->customer ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-light border rounded-pill px-3 shadow-sm">Shift {{ $row->shift }}</span>
                                </td>
                                <td class="text-center font-weight-bold text-gray-700">{{ number_format($row->packing_size) }}</td>
                                <td class="text-center font-weight-bold text-info">{{ number_format($row->total_packing) }}</td>
                                <td class="text-center text-success font-weight-bold">{{ number_format($row->total_ok_sum) }}</td>
                                <td class="text-center text-danger font-weight-bold">{{ number_format($row->total_ng_sum) }}</td>
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
                    @if($recap->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right py-3 text-uppercase small letter-spacing-1">Grand Total</td>
                            <td class="text-center py-3 text-gray-700">-</td>
                            <td class="text-center py-3 text-info">{{ number_format($totalPackingAll) }}</td>
                            <td class="text-center py-3 text-success">{{ number_format($totalOkAll) }}</td>
                            <td class="text-center py-3 text-danger">{{ number_format($totalNgAll) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
