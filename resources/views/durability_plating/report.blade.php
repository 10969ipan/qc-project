@extends('layouts.admin')

@section('title', 'Laporan ' . ucwords(str_replace('_', ' ', $testType)) . ' Test')

@section('content')
<style>
    .table-responsive {
        max-height: calc(100vh - 220px) !important;
        min-height: 300px !important; /* Mencegah dropdown terpotong saat baris sedikit */
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }

    @media (max-width: 992px) {
        .table-responsive {
            max-height: 60vh !important;
        }
    }
    #dataTable, table.dataTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    /* All cells: full uniform grid */
    #dataTable td, #dataTable th {
        border: 1px solid #e2e8f0 !important;
    }
    #dataTable tbody td {
        border: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.6rem !important;
        padding: 4px 4px !important;
    }

    /* Global TH sticky setup */
    /* Use box-shadow instead of border to ensure grid lines show on sticky elements */
    #dataTable > thead > tr > th {
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.58rem !important;
        letter-spacing: 0.2px !important;
        padding: 6px 4px !important;
        /* Remove normal border, use box-shadow for grid that works with sticky */
        border: none !important;
        box-shadow: inset 1px 0 0 #e2e8f0, inset 0 1px 0 #e2e8f0, inset -1px 0 0 #e2e8f0, 0 1px 0 #e2e8f0 !important;
        vertical-align: middle !important;
        text-align: center !important;
    }
    
    /* Sticky top offsets are set by JS dynamically */
    #dataTable > thead > tr:nth-child(1) > th { top: 0 !important; z-index: 10 !important; }
    #dataTable > thead > tr:nth-child(2) > th { z-index: 9 !important; }
    #dataTable > thead > tr:nth-child(3) > th { z-index: 8 !important; }

    /* Fix column layout via table-layout auto */
    #dataTable {
        table-layout: auto !important;
    }
    #dataTable th, #dataTable td {
        white-space: nowrap;
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    /* Sticky left columns logic */
    #dataTable th.sticky-col,
    #dataTable td.sticky-col {
        position: sticky !important;
        background-color: #fff !important;
        z-index: 5 !important;
        border-right: 1px solid #e2e8f0 !important;
    }
    
    #dataTable thead th.sticky-col {
        background-color: #f8fafc !important;
        z-index: 11 !important;
    }

    /* Sticky left columns hover highlight */
    #dataTable tbody tr:hover td.sticky-col {
        background-color: #f1f5f9 !important;
    }

    /* STANDAR section - darker background */
    #dataTable > thead > tr > th.th-standar {
        background-color: #cbd5e1 !important;
        color: #1e293b !important;
    }
    #dataTable tbody td.td-standar {
        background-color: #e2e8f0 !important; /* Lighter version of the header's #cbd5e1 */
        color: #334155 !important;
        border-color: #d6dee8 !important; /* Make border slightly lighter so grid isn't too thick */
    }
    
    /* Hover highlight for STANDARD columns */
    #dataTable tbody tr:hover td.td-standar {
        background-color: #cbd5e1 !important; /* Match header color on hover for visual feedback */
        border-color: #aab7c5 !important; /* Softer border so it doesn't look thick */
    }

    /* AKTUAL section - lighter gray/slate to match STANDARD style but distinct */
    #dataTable > thead > tr > th.th-aktual {
        background-color: #e2e8f0 !important; /* slate-200 */
        color: #1e293b !important; /* slate-800 */
        box-shadow: inset 1px 0 0 #cbd5e1, inset 0 1px 0 #cbd5e1, inset -1px 0 0 #cbd5e1, 0 1px 0 #cbd5e1 !important;
    }
    #dataTable tbody td.td-aktual {
        background-color: #f1f5f9 !important; /* slate-100 */
        color: #334155 !important; /* slate-700 */
        border-color: #e2e8f0 !important;
    }
    #dataTable tbody tr:hover td.td-aktual {
        background-color: #e2e8f0 !important;
        border-color: #cbd5e1 !important; /* Darker border so it remains visible */
    }

    /* Selected row override */
    #dataTable tbody tr.table-primary td,
    #dataTable tbody tr.table-primary td.sticky-col,
    #dataTable tbody tr.table-primary td.td-standar,
    #dataTable tbody tr.table-primary td.td-aktual {
        background-color: #cce5ff !important;
        border-color: #b8daff !important;
    }
    
    #dataTable tbody tr.table-primary:hover td,
    #dataTable tbody tr.table-primary:hover td.sticky-col,
    #dataTable tbody tr.table-primary:hover td.td-standar,
    #dataTable tbody tr.table-primary:hover td.td-aktual {
        background-color: #b8daff !important;
    }

    /* Result/Judgment badge size */
    #dataTable td .badge {
        font-size: 0.75rem !important;
        padding: 4px 8px !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px;
    }

    /* Fix text colors overriding by td !important */
    #dataTable tbody td.text-danger, #dataTable tbody td .text-danger {
        color: #e74a3b !important;
    }
    #dataTable tbody td.text-primary, #dataTable tbody td .text-primary {
        color: #000000ff !important;
    }
    #dataTable tbody td.text-success, #dataTable tbody td .text-success {
        color: #1cc88a !important;
    }
</style>

@php
    $plantCode = auth()->check() && auth()->user()->plant ? strtolower(auth()->user()->plant->name) : 'jakarta';
    $docHeader = \App\Models\GeneralSetting::getDocHeader($testType, $plantCode, [
        'no_dokumen' => '-',
        'tgl_terbit' => '-',
        'revisi' => '- / -',
        'halaman' => '1 / 1'
    ]);
@endphp

<div class="card shadow mb-2">
    <div class="card-body p-0">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                    <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;" loading="lazy">
                </td>
                <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                    <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                        LAPORAN {{ strtoupper(str_replace('_', ' ', $testType)) }} TEST
                    </h1>
                </td>
                <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                    <table style="border-collapse:collapse; font-size:0.68rem;">
                        <tr>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                            <td style="padding:1px 2px;">:</td>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['no_dokumen'] }}</td>
                        </tr>
                        <tr>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                            <td style="padding:1px 2px;">:</td>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                        </tr>
                        <tr>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                            <td style="padding:1px 2px;">:</td>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                        </tr>
                        <tr>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                            <td style="padding:1px 2px;">:</td>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded mb-3 mt-3">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded mb-3 mt-3">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded mb-3 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <span class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Terdapat Kesalahan Input:</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <hr class="my-2">
        <ul class="mb-0 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    
    <script>
        // Auto-open modal if there are errors (assuming it's the Add Data modal that failed, because it's the only one submitting directly here. Actually Edit uses update via Ajax or normal form? Let's assume user knows).
        document.addEventListener("DOMContentLoaded", function() {
            if ($('#modalAddData').length) {
                $('#modalAddData').modal('show');
            }
        });
    </script>
@endif

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url()->current() }}" method="GET"
            class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
            style="gap: 8px; overflow-x: auto; white-space: nowrap;">
            
            <!-- Field: Part/Customer -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Part:</label>
                <div style="width: 200px;" class="custom-filter-wrapper">
                    <select name="search" id="filterItem" class="form-control form-control-sm border-0 shadow-sm d-none" onchange="this.form.submit()">
                        <option value="">Semua Part / Customer...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->part_name }}" data-name="{{ $item->part_name }}" data-customer="{{ $item->customer_name }}" data-detail="{{ $item->customer_standard }}" {{ request('search') == $item->part_name ? 'selected' : '' }}>
                                {{ $item->part_name }} - {{ $item->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Field: Customer -->
            <div class="d-flex align-items-center ml-2">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Customer:</label>
                <select name="customer_name" class="form-control form-control-sm border-0 shadow-sm" style="width: 150px; font-size: 0.75rem;" onchange="this.form.submit()">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer }}" {{ request('customer_name') == $customer ? 'selected' : '' }}>
                            {{ $customer }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Field: Tanggal -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tgl:</label>
                <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0"
                        style="width: 120px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                    <span class="px-1 text-gray-500 small">-</span>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0"
                        style="width: 120px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                </div>
            </div>

            <!-- Field: Result -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Result:</label>
                <select name="result_judgment" class="form-control form-control-sm border-0 shadow-sm" style="width: 100px; font-size: 0.75rem;">
                    <option value="">Semua</option>
                    <option value="OK" {{ request('result_judgment') === 'OK' ? 'selected' : '' }}>OK</option>
                    <option value="NG" {{ request('result_judgment') === 'NG' ? 'selected' : '' }}>NG</option>
                </select>
            </div>

            <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                <style>
                    .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                    .custom-filter-wrapper .ips-input { padding: 4px 20px 4px 8px; font-size: 0.75rem; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: calc(1.5em + 0.5rem + 2px); text-transform: none !important; }
                    .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
                    .custom-filter-wrapper { position: relative; top: -1px; }
                </style>
                <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Filter">
                    <i class="fas fa-search fa-sm"></i>
                </button>
                <a href="{{ route('standard-performance-tests.report') }}"
                    class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" title="Reset Filter">
                    <i class="fas fa-undo fa-sm"></i>
                </a>
                <button type="submit" name="print" value="true" formtarget="_blank" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Print Laporan">
                    <i class="fas fa-print fa-sm"></i> Print
                </button>
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm shadow-sm rounded-pill px-3 dropdown-toggle" type="button" id="dropdownMenuLaporan" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Menu Laporan" data-boundary="window">
                        <i class="fas fa-file-alt fa-sm"></i> Laporan
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 animated--fade-in" aria-labelledby="dropdownMenuLaporan" style="font-size:0.85rem; border-radius:8px; min-width: 200px; z-index: 1050;">
                        <div class="dropdown-header font-weight-bold text-primary text-uppercase" style="font-size:0.7rem; letter-spacing:1px; padding: 0.5rem 1.5rem;">Pilih Laporan</div>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route('standard-performance-tests.report') }}">
                            <i class="fas fa-layer-group fa-fw mr-2 text-success"></i> Thickness
                        </a>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route('standard-performance-tests.report.corrodkote') }}">
                            <i class="fas fa-vial fa-fw mr-2 text-info"></i> Corrodkote
                        </a>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route('standard-performance-tests.report.cass') }}">
                            <i class="fas fa-flask fa-fw mr-2 text-primary"></i> Cass Test
                        </a>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route('standard-performance-tests.report.salt_spray') }}">
                            <i class="fas fa-spray-can fa-fw mr-2 text-warning"></i> Salt Spray Test
                        </a>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route('standard-performance-tests.report.porecount') }}">
                            <i class="fas fa-search-plus fa-fw mr-2 text-danger"></i> Porecount Test
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item py-2 font-weight-bold text-secondary" href="{{ route('standard-performance-tests.index') }}">
                            <i class="fas fa-table fa-fw mr-2"></i> Master Standar
                        </a>
                    </div>
                </div>
                @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
                <button type="button" class="btn btn-success btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalAddData" title="Tambah Data Baru">
                    <i class="fas fa-plus fa-sm"></i> Tambah Data
                </button>
                @endif
            </div>
        </form>

        <!-- Loading Spinner -->
        <div id="tableLoader" class="text-center py-5">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <h6 class="text-muted font-weight-bold">Memuat Data Laporan...</h6>
        </div>

        <!-- Table Container (Hidden until initialized) -->
        <div id="tableContainer" style="display: none;">
            <div class="table-responsive mb-0" style="min-height: 280px;">
                <table class="table table-bordered table-hover" id="dataTable">
                    <colgroup>
                        <col style="width: 45px;">       <!-- Checkbox -->
                        <col style="width: 38px;">       <!-- No -->
                        <col style="width: 150px;">      <!-- Nama Part -->
                        <col style="width: 150px;">      <!-- Part No -->
                        <col style="width: 110px;">      <!-- Customer -->
                        <col style="width: 130px;">      <!-- Std Customer -->
                        
                        @if($testType == 'thickness')
                            <!-- STANDAR -->
                            <col style="width: 60px;"> <col style="width: 60px;"> <col style="width: 60px;">
                            <!-- AKTUAL -->
                            <col style="width: 60px;"> <col style="width: 60px;"> <col style="width: 60px;">
                        @elseif($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray')
                            <col style="width: 80px;"> <col style="width: 80px;">
                            <col style="width: 80px;"> <col style="width: 80px;">
                        @elseif($testType == 'porecount')
                            <col style="width: 100px;">
                            <col style="width: 100px;">
                        @endif

                        <col style="width: 85px;">       <!-- Tanggal Test -->
                        <col style="width: 90px;">       <!-- Tgl Produksi -->
                        <col style="width: 50px;">       <!-- Shift -->
                        
                        @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray')
                            <col style="width: 90px;">       <!-- Tgl Masuk -->
                            <col style="width: 70px;">       <!-- Jam Masuk -->
                            <col style="width: 90px;">       <!-- Tgl Keluar -->
                            <col style="width: 70px;">       <!-- Jam Keluar -->
                        @endif

                        <col style="width: 80px;">       <!-- No Lot -->
                        @if($testType == 'corrodkote')
                        <col style="width: 80px;">       <!-- Aktual % Corrosion -->
                        @endif
                        <col style="width: 100px;">      <!-- Result/Judgment -->
                        
                        @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
                            <col style="width: 80px;">       <!-- Evidence -->
                        @endif
                        
                        <col style="width: 95px;">       <!-- PIC -->
                        
                        <col style="width: auto;">       <!-- Description (auto to stretch) -->
                        
                        @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
                            <col style="width: 80px;">       <!-- Thickness Data -->
                        @endif

                        <col style="width: 65px;">       <!-- Actions -->
                    </colgroup>
                                                <thead>
                                        <tr>
                        <th rowspan="2" class="align-middle text-center">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <span style="font-size: 10px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; line-height: 1.2;">SEMUA<br>(<span id="checkedCountDisplay">0</span>)</span>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="checkAllRows">
                                    <label class="custom-control-label" for="checkAllRows" style="cursor:pointer;"></label>
                                </div>
                            </div>
                        </th>
                        <th rowspan="2" class="align-middle text-center">No.</th>
                        <th rowspan="2" class="align-middle sticky-col">Nama Part</th>
                        <th rowspan="2" class="align-middle text-center">Part No</th>
                        <th rowspan="2" class="align-middle">Customer</th>
                        <th rowspan="2" class="align-middle">Standard Customer</th>
                        
                        @if($testType == 'thickness')
                            <th colspan="3" class="text-center th-standar">STANDARD</th>
                            <th colspan="3" class="text-center th-aktual">ACTUAL</th>
                        @elseif($testType == 'corrodkote')
                            <th colspan="2" class="text-center th-standar">STANDARD</th>
                            <th colspan="2" class="text-center th-aktual">ACTUAL</th>
                        @elseif($testType == 'cass')
                            <th colspan="2" class="text-center th-standar">STANDARD</th>
                            <th colspan="2" class="text-center th-aktual">ACTUAL</th>
                        @elseif($testType == 'salt_spray')
                            <th colspan="2" class="text-center th-standar">STANDARD</th>
                            <th colspan="2" class="text-center th-aktual">ACTUAL</th>
                        @elseif($testType == 'porecount')
                            <th class="text-center th-standar">STANDARD</th>
                            <th class="text-center th-aktual">ACTUAL</th>
                        @endif

                        <th rowspan="2" class="align-middle text-center">Tanggal Test</th>
                        <th rowspan="2" class="align-middle text-center">Tgl Produksi</th>
                        <th rowspan="2" class="align-middle text-center">Shift</th>
                        @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray')
                            <th rowspan="2" class="align-middle text-center">Tgl Masuk</th>
                            <th rowspan="2" class="align-middle text-center">Jam Masuk</th>
                            <th rowspan="2" class="align-middle text-center">Tgl Keluar</th>
                            <th rowspan="2" class="align-middle text-center">Jam Keluar</th>
                        @endif
                        <th rowspan="2" class="align-middle text-center">No Lot</th>
                        @if($testType == 'corrodkote')
                        <th rowspan="2" class="align-middle text-center">Aktual % Corrosion</th>
                        @endif
                        <th rowspan="2" class="align-middle text-center">Result</th>
                        @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
                            <th rowspan="2" class="align-middle text-center">Evidence</th>
                        @endif
                        <th rowspan="2" class="align-middle text-center">PIC</th>
                        <th rowspan="2" class="align-middle text-center">Description</th>
                        @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
                            <th rowspan="2" class="align-middle text-center">Thickness</th>
                        @endif
                        <th rowspan="2" class="text-center align-middle">Actions</th>
                    </tr>
                    <tr>
                        @if($testType == 'thickness')
                            <!-- STANDAR THICKNESS -->
                            <th class="text-center th-standar" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Cr</span></th>
                            <th class="text-center th-standar" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Ni</span></th>
                            <th class="text-center th-standar" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Cu</span></th>
                            <!-- AKTUAL THICKNESS -->
                            <th class="text-center th-aktual" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Cr</span></th>
                            <th class="text-center th-aktual" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Ni</span></th>
                            <th class="text-center th-aktual" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Cu</span></th>
                        @elseif($testType == 'corrodkote')
                            <!-- STANDAR CORRODKOTE -->
                            <th class="text-center th-standar" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Time (hours)</span></th>
                            <th class="text-center th-standar" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Standard</span></th>
                            <!-- AKTUAL CORRODKOTE -->
                            <th class="text-center th-aktual" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Time (hours)</span></th>
                            <th class="text-center th-aktual" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Actual</span></th>
                        @elseif($testType == 'cass')
                            <!-- STANDAR CASS -->
                            <th class="text-center th-standar" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Time (hours)</span></th>
                            <th class="text-center th-standar" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">STD. Min RN</span></th>
                            <!-- AKTUAL CASS -->
                            <th class="text-center th-aktual" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Time (hours)</span></th>
                            <th class="text-center th-aktual" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Actual</span></th>
                        @elseif($testType == 'salt_spray')
                            <!-- STANDAR SALT SPRAY -->
                            <th class="text-center th-standar" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Time (hours)</span></th>
                            <th class="text-center th-standar" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">STD. Rusting</span></th>
                            <!-- AKTUAL SALT SPRAY -->
                            <th class="text-center th-aktual" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Time (hours)</span></th>
                            <th class="text-center th-aktual" style="width: 80px;"><span style="text-transform: none !important; font-weight: bold !important;">Actual</span></th>
                        @elseif($testType == 'porecount')
                            <th class="text-center th-standar" style="width: 100px;"><span style="text-transform: none !important; font-weight: bold !important;">Min Pores</span></th>
                            <th class="text-center th-aktual" style="width: 100px;"><span style="text-transform: none !important; font-weight: bold !important;">Actual Pores</span></th>
                        @endif
                    </tr>
                </thead>
                                                <tbody>
                    @foreach($reports as $index => $report)
                        @php
                            $std = $report->standard;
                        @endphp
                        <tr>
                            <td class="align-middle text-center">
                                <div class="custom-control custom-checkbox d-flex justify-content-center align-items-center">
                                    <input type="checkbox" class="custom-control-input row-checkbox" id="checkRow{{ $report->id }}" value="{{ $report->id }}">
                                    <label class="custom-control-label" for="checkRow{{ $report->id }}" style="cursor:pointer; margin-left: 0.5rem;"></label>
                                </div>
                            </td>
                            <td class="text-center">{{ $reports->firstItem() + $index }}</td>
                            <td class="text-center font-weight-bold sticky-col">{{ $std->part_name ?? '-' }}</td>
                            <td class="text-center font-mono">{{ $std->part_number ?? '-' }}</td>
                            <td class="text-center">{{ $std->customer_name ?? '-' }}</td>
                            <td class="text-center">{{ $std->customer_standard ?? '-' }}</td>
                            
                                                        <!-- STANDAR -->
                            @if($testType == 'thickness')
                                <td class="text-center td-standar">{{ $std->thickness_cr ?? '-' }}</td>
                                <td class="text-center td-standar">{{ $std->thickness_ni ?? '-' }}</td>
                                <td class="text-center td-standar">{{ $std->thickness_cu ?? '-' }}</td>
                                @php
                                    $is_cr_ng = (is_numeric($report->actual_cr) && is_numeric($std->thickness_cr)) ? ((float)$report->actual_cr < (float)$std->thickness_cr) : false;
                                    $is_ni_ng = (is_numeric($report->actual_ni) && is_numeric($std->thickness_ni)) ? ((float)$report->actual_ni < (float)$std->thickness_ni) : false;
                                    $is_cu_ng = (is_numeric($report->actual_cu) && is_numeric($std->thickness_cu)) ? ((float)$report->actual_cu < (float)$std->thickness_cu) : false;
                                @endphp
                                <td class="text-center font-weight-bold {{ $is_cr_ng ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->actual_cr ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ $is_ni_ng ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->actual_ni ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ $is_cu_ng ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->actual_cu ?? '-' }}</td>
                            @elseif($testType == 'corrodkote')
                                <td class="text-center td-standar">{{ $std->corrodkote_time ?? '-' }}</td>
                                <td class="text-center td-standar">{{ $std->corrodkote_std_max_corrosion ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ strtolower(trim($report->result_judgment ?? '')) === 'ng' ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->actual_corrodkote_waktu ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ strtolower(trim($report->result_judgment ?? '')) === 'ng' ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->standar_jam_corrodkote ?? '-' }}</td>
                            @elseif($testType == 'cass')
                                <td class="text-center td-standar">{{ $std->cass_time ?? '-' }}</td>
                                <td class="text-center td-standar">{{ $std->cass_std_min_rn ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ strtolower(trim($report->result_judgment ?? '')) === 'ng' ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->actual_cass_waktu ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ strtolower(trim($report->result_judgment ?? '')) === 'ng' ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->standar_jam_cass ?? '-' }}</td>
                            @elseif($testType == 'salt_spray')
                                <td class="text-center td-standar">{{ $std->salt_spray_time ?? '-' }}</td>
                                <td class="text-center td-standar">{{ $std->salt_spray_std_rusting ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ strtolower(trim($report->result_judgment ?? '')) === 'ng' ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->actual_salt_spray_waktu ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ strtolower(trim($report->result_judgment ?? '')) === 'ng' ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->standar_jam_salt_spray ?? '-' }}</td>
                            @elseif($testType == 'porecount')
                                <td class="text-center td-standar">{{ $std->porecount_std_min ?? '-' }}</td>
                                <td class="text-center font-weight-bold {{ strtolower(trim($report->result_judgment ?? '')) === 'ng' ? 'text-danger' : 'text-primary' }} td-aktual">{{ $report->actual_porecount ?? '-' }}</td>
                            @endif

                            <td class="text-center">{{ $report->tanggal_cek ? \Carbon\Carbon::parse($report->tanggal_cek)->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">{{ $report->production_date ? \Carbon\Carbon::parse($report->production_date)->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">{{ $report->shift ?? '-' }}</td>
                            @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray')
                                <td class="text-center">{{ $report->tgl_masuk ? \Carbon\Carbon::parse($report->tgl_masuk)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">{{ $report->jam_masuk ? \Carbon\Carbon::parse($report->jam_masuk)->format('H:i') : '-' }}</td>
                                <td class="text-center">{{ $report->tgl_keluar ? \Carbon\Carbon::parse($report->tgl_keluar)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">{{ $report->jam_keluar ? \Carbon\Carbon::parse($report->jam_keluar)->format('H:i') : '-' }}</td>
                            @endif
                            <td class="text-center">{{ $report->lot_no ?? '-' }}</td>
                            @if($testType == 'corrodkote')
                            <td class="text-center font-weight-bold">
                                {{ (isset($report->aktual_corrosion) && $report->aktual_corrosion !== '' && $report->aktual_corrosion !== '-') ? $report->aktual_corrosion . '%' : '-' }}
                            </td>
                            @endif
                            <td class="text-center">
                                @php
                                    $rj = $report->result_judgment ?? '-';
                                    $rjClass = match(strtolower($rj)) {
                                        'ok' => 'text-success font-weight-bold',
                                        'ng' => 'text-danger font-weight-bold',
                                        default => 'text-muted'
                                    };
                                @endphp
                                <span class="{{ $rjClass }}">{{ $rj }}</span>
                            </td>
                            @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
                                <td class="text-center">
                                    @if($report->evidence_before || $report->evidence_after)
                                        <a href="javascript:void(0)" class="text-primary btn-view-evidence" style="font-size: 0.8rem; text-decoration: underline;"
                                                data-before="{{ $report->evidence_before ? asset($report->evidence_before) : '' }}" 
                                                data-after="{{ $report->evidence_after ? asset($report->evidence_after) : '' }}"
                                                data-before-time="{{ $report->evidence_before_uploaded_at ? \Carbon\Carbon::parse($report->evidence_before_uploaded_at)->format('d-m-Y H:i') : '' }}"
                                                data-after-time="{{ $report->evidence_after_uploaded_at ? \Carbon\Carbon::parse($report->evidence_after_uploaded_at)->format('d-m-Y H:i') : '' }}">
                                            <i class="fas fa-images"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem; font-style: italic;">Tidak ada foto</span>
                                    @endif
                                </td>
                            @endif
                            <td class="text-center">{{ $report->analis ? $report->analis->name : '-' }}</td>
                            <td class="text-center">{{ $report->description ?? '-' }}</td>
                            @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
                                <td class="text-center">
                                    @php
                                        $hasThickness = (!empty(trim($report->actual_cu)) && trim($report->actual_cu) !== '-') ||
                                                        (!empty(trim($report->actual_ni)) && trim($report->actual_ni) !== '-') ||
                                                        (!empty(trim($report->actual_cr)) && trim($report->actual_cr) !== '-');
                                    @endphp
                                    @if($hasThickness)
                                        <a href="{{ route('standard-performance-tests.report', ['report_id' => $report->id]) }}" class="text-primary" style="font-size: 0.8rem; text-decoration: underline;" title="Lihat Data Thickness">
                                            <i class="fas fa-external-link-alt"></i> Data
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem; font-style: italic;">Tidak ada data</span>
                                    @endif
                                </td>
                            @endif

                            <td class="align-middle text-center" style="width: 50px;">
                                <div class="dropdown no-arrow">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" data-boundary="window" type="button" id="dropdownMenuButton-{{ $report->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:50%;">
                                        <i class="fas fa-ellipsis-v text-muted" style="font-size:12px;"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 animated--fade-in" aria-labelledby="dropdownMenuButton-{{ $report->id }}" style="min-width:180px; font-size:0.85rem; border-radius:8px;">
                                        <div class="dropdown-header font-weight-bold text-primary text-uppercase" style="font-size:0.7rem; letter-spacing:1px; padding: 0.5rem 1.5rem;">Aksi Laporan</div>
                                        
                                        <button type="button" class="dropdown-item btn-edit-thickness" 
                                            data-id="{{ $report->id }}" 
                                            data-item="{{ json_encode($report) }}" 
                                            data-part="{{ $std->part_name }}"
                                            data-stdcu="{{ $std->thickness_cu }}"
                                            data-stdni="{{ $std->thickness_ni }}"
                                            data-stdcr="{{ $std->thickness_cr }}">
                                            <i class="fas fa-edit text-info fa-fw mr-2"></i> Edit Laporan
                                        </button>

                                        @if($testType == 'thickness')
                                            <button type="button" class="dropdown-item btn-input-corrodkote" data-id="{{ $report->id }}" data-item="{{ json_encode($report) }}" data-part="{{ $std->part_name }}" data-customer="{{ $std->customer_name }}" data-std="{{ $std->customer_standard }}" data-time="{{ $std->corrodkote_time }}">
                                                <i class="fas fa-plus text-primary fa-fw mr-2"></i> Input Corrodkote
                                            </button>
                                            <button type="button" class="dropdown-item btn-input-cass" data-id="{{ $report->id }}" data-item="{{ json_encode($report) }}" data-part="{{ $std->part_name }}" data-customer="{{ $std->customer_name }}" data-std="{{ $std->customer_standard }}" data-time="{{ $std->cass_time }}">
                                                <i class="fas fa-plus text-primary fa-fw mr-2"></i> Input Cass Test
                                            </button>
                                            <button type="button" class="dropdown-item btn-input-salt-spray" data-id="{{ $report->id }}" data-item="{{ json_encode($report) }}" data-part="{{ $std->part_name }}" data-customer="{{ $std->customer_name }}" data-std="{{ $std->customer_standard }}" data-time="{{ $std->salt_spray_time }}">
                                                <i class="fas fa-plus text-primary fa-fw mr-2"></i> Input Salt Spray
                                            </button>
                                            <button type="button" class="dropdown-item btn-input-porecount" data-id="{{ $report->id }}" data-item="{{ json_encode($report) }}" data-part="{{ $std->part_name }}" data-customer="{{ $std->customer_name }}" data-std="{{ $std->customer_standard }}" data-stdmin="{{ $std->porecount_std_min }}">
                                                <i class="fas fa-plus text-primary fa-fw mr-2"></i> Input Porecount
                                            </button>
                                        @endif
                                        
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('standard-performance-tests.thickness.destroy', ['id' => $report->id, 'type' => $testType]) }}" method="POST" class="d-inline delete-form w-100">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger btn-delete w-100 text-left">
                                                <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($reports->hasPages())
        <div class="pagination-container pt-2 mt-2 d-flex justify-content-end">
            {{ $reports->links() }}
        </div>
        @endif
        </div>
    </div>
</div>

<!-- Modal Edit Thickness -->
<div class="modal fade" id="modalEditThickness" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-edit mr-2 text-info"></i> Edit Laporan {{ ucwords(str_replace('_', ' ', $testType)) }} Test
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="formEditThickness" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Test</label>
                            <input type="date" name="tanggal_cek" id="edit_tanggal_cek" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Produksi</label>
                            <input type="date" name="production_date" id="edit_production_date" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Shift</label>
                            <select name="shift" id="edit_shift" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">No Lot</label>
                            <input type="text" name="lot_no" id="edit_lot_no" class="form-control form-control-sm border-0 shadow-sm" placeholder="No Lot">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-gray-700">Nama Part</label>
                        <input type="text" id="edit_thickness_part_name" class="form-control form-control-sm border-0 shadow-sm" readonly>
                    </div>
                    @if($testType == 'thickness')
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Cr</label>
                            <input type="text" name="actual_cr" id="edit_actual_cr" class="form-control form-control-sm border-0 shadow-sm edit-actual-thickness-input" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Ni</label>
                            <input type="text" name="actual_ni" id="edit_actual_ni" class="form-control form-control-sm border-0 shadow-sm edit-actual-thickness-input" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Cu</label>
                            <input type="text" name="actual_cu" id="edit_actual_cu" class="form-control form-control-sm border-0 shadow-sm edit-actual-thickness-input" required>
                        </div>
                    </div>
                    @elseif($testType == 'corrodkote')
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Test Aktual (Hours)</label>
                            <input type="text" name="actual_corrodkote_waktu" id="edit_actual_corrodkote_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam</label>
                            <input type="text" name="standar_jam_corrodkote" id="edit_standar_jam_corrodkote" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" data-target="edit" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Aktual % Corrosion</label>
                            <input type="text" name="aktual_corrosion" id="edit_aktual_corrosion" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                    </div>
                    @elseif($testType == 'cass')
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Test Aktual (Hours)</label>
                            <input type="text" name="actual_cass_waktu" id="edit_actual_cass_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam</label>
                            <input type="text" name="standar_jam_cass" id="edit_standar_jam_cass" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" data-target="edit" required>
                        </div>
                    </div>
                    @elseif($testType == 'salt_spray')
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Test Aktual (Hours)</label>
                            <input type="text" name="actual_salt_spray_waktu" id="edit_actual_salt_spray_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam</label>
                            <input type="text" name="standar_jam_salt_spray" id="edit_standar_jam_salt_spray" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" data-target="edit" required>
                        </div>
                    </div>
                    @elseif($testType == 'porecount')
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Aktual</label>
                            <input type="text" name="actual_porecount" id="edit_actual_porecount" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>
                    @endif
                    

                    @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray')
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Masuk Chamber</label>
                            <div class="d-flex align-items-center">
                                <input type="date" name="tgl_masuk" id="edit_tgl_masuk" class="form-control form-control-sm border-0 shadow-sm mr-2 auto-calc-trigger" data-target="edit">
                                <input type="time" name="jam_masuk" id="edit_jam_masuk" class="form-control form-control-sm border-0 shadow-sm auto-calc-trigger" data-target="edit">
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Keluar Chamber</label>
                            <div class="d-flex align-items-center">
                                <input type="date" name="tgl_keluar" id="edit_tgl_keluar" class="form-control form-control-sm border-0 shadow-sm mr-2">
                                <input type="time" name="jam_keluar" id="edit_jam_keluar" class="form-control form-control-sm border-0 shadow-sm">
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-2">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Result / Judgment</label>
                            <select name="result_judgment" id="edit_result_judgment" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="-">-</option>
                                <option value="OK">OK</option>
                                <option value="NG">NG</option>
                            </select>
                        </div>
                    </div>

                    @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
                    <input type="hidden" name="delete_evidence_before" id="delete_evidence_before" value="0">
                    <input type="hidden" name="delete_evidence_after"  id="delete_evidence_after"  value="0">

                    <div class="mb-3">
                        <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                            <i class="fas fa-images mr-1 text-info"></i> Evidence Foto
                        </label>
                        <div class="row">
                            {{-- BEFORE --}}
                            <div class="col-6">
                                <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#e8f4fd; border-bottom:1px solid #bee3f8;">
                                        <span class="small font-weight-bold text-primary">
                                            <i class="fas fa-circle-notch mr-1"></i>BEFORE TEST
                                        </span>
                                        <button type="button" id="btn_delete_evidence_before"
                                            class="btn btn-danger btn-sm d-none align-items-center justify-content-center"
                                            style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;"
                                            title="Hapus foto Before">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div id="edit_evidence_before_preview_wrap" style="display:none; background:#f0f4f8;">
                                        <img id="edit_evidence_before_preview" src="" alt="Before"
                                            class="d-block w-100"
                                            style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                    </div>
                                    <div id="edit_evidence_before_empty"
                                        style="display:none; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <small style="font-size:0.72rem;">Belum ada foto</small>
                                    </div>
                                    <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #e8f4fd;">
                                        <small class="text-info d-block mb-1" id="edit_evidence_before_time" style="font-size:0.62rem;"></small>
                                        <input type="file" name="evidence_before" id="input_evidence_before"
                                            class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            {{-- AFTER --}}
                            <div class="col-6">
                                <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#edf7ed; border-bottom:1px solid #b7dbb7;">
                                        <span class="small font-weight-bold text-success">
                                            <i class="fas fa-check-circle mr-1"></i>AFTER TEST
                                        </span>
                                        <button type="button" id="btn_delete_evidence_after"
                                            class="btn btn-danger btn-sm d-none align-items-center justify-content-center"
                                            style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;"
                                            title="Hapus foto After">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div id="edit_evidence_after_preview_wrap" style="display:none; background:#f0f4f8;">
                                        <img id="edit_evidence_after_preview" src="" alt="After"
                                            class="d-block w-100"
                                            style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                    </div>
                                    <div id="edit_evidence_after_empty"
                                        style="display:none; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <small style="font-size:0.72rem;">Belum ada foto</small>
                                    </div>
                                    <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #edf7ed;">
                                        <small class="text-success d-block mb-1" id="edit_evidence_after_time" style="font-size:0.62rem;"></small>
                                        <input type="file" name="evidence_after" id="input_evidence_after"
                                            class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-12 form-group mb-0">
                            <label class="small font-weight-bold text-gray-700">Description / Keterangan</label>
                            <textarea name="description" id="edit_description" class="form-control form-control-sm border-0 shadow-sm" rows="3" placeholder="Masukkan keterangan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal Input Corrodkote -->
<div class="modal fade" id="modalInputCorrodkote" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-plus mr-2 text-primary"></i> Input Corrodkote
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="#" method="POST" id="formInputCorrodkote" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="report_id" id="corrodkote_report_id">
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Nama Part</label>
                            <input type="text" id="corrodkote_part_name" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Customer</label>
                            <input type="text" id="corrodkote_customer" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Std. Customer</label>
                            <input type="text" id="corrodkote_std" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Time (hrs) STD.</label>
                            <input type="text" name="standard_time" id="corrodkote_standard_time" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Time (hrs) Aktual <span class="text-danger">*</span></label>
                            <input type="text" name="actual_corrodkote_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam <span class="text-danger">*</span></label>
                            <input type="text" name="standar_jam_corrodkote" id="corrodkote_standar_jam" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Aktual % Corrosion</label>
                            <input type="text" name="aktual_corrosion" id="corrodkote_aktual_corrosion" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Test</label>
                            <input type="date" name="tanggal_test" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Produksi</label>
                            <input type="date" name="production_date" id="corrodkote_produksi" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Shift</label>
                            <select name="shift" id="corrodkote_shift" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Masuk Chamber</label>
                            <input type="date" name="tgl_masuk" id="corrodkote_tgl_masuk" class="form-control form-control-sm border-0 shadow-sm auto-calc-trigger" data-target="corrodkote" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Jam Masuk</label>
                            <input type="time" name="jam_masuk" id="corrodkote_jam_masuk" class="form-control form-control-sm border-0 shadow-sm auto-calc-trigger" data-target="corrodkote" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Keluar Chamber</label>
                            <input type="date" name="tgl_keluar" id="corrodkote_tgl_keluar" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Jam Keluar</label>
                            <input type="time" name="jam_keluar" id="corrodkote_jam_keluar" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">No Lot</label>
                            <input type="text" name="lot_no" id="corrodkote_lot" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Result</label>
                            <select name="result_judgment" class="form-control form-control-sm border-0 shadow-sm" required>
                                <option value="">Pilih...</option>
                                <option value="OK">OK</option>
                                <option value="NG">NG</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Evidence Before -->
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                                <i class="fas fa-image mr-1 text-info"></i> Evidence Before
                            </label>
                            <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#e8f4fd; border-bottom:1px solid #bee3f8;">
                                    <span class="small font-weight-bold text-primary">
                                        <i class="fas fa-circle-notch mr-1"></i>BEFORE TEST
                                    </span>
                                    <button type="button" id="btn_delete_corrodkote_evidence_before" class="btn btn-danger btn-sm d-none align-items-center justify-content-center" style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;" title="Hapus foto Before"><i class="fas fa-times"></i></button>
                                </div>
                                <div id="corrodkote_evidence_before_preview_wrap" style="display:none; background:#f0f4f8;">
                                    <img id="corrodkote_evidence_before_preview" src="" alt="Before" class="d-block w-100" style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                </div>
                                <div id="corrodkote_evidence_before_empty" style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <small style="font-size:0.72rem;">Belum ada foto</small>
                                </div>
                                <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #e8f4fd;">
                                    <input type="file" name="evidence_before" id="input_corrodkote_evidence_before" class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- Evidence After -->
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                                <i class="fas fa-image mr-1 text-info"></i> Evidence After
                            </label>
                            <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#fdf2e9; border-bottom:1px solid #f9e0cc;">
                                    <span class="small font-weight-bold text-warning" style="color: #d35400 !important;">
                                        <i class="fas fa-check-circle mr-1"></i>AFTER TEST
                                    </span>
                                    <button type="button" id="btn_delete_corrodkote_evidence_after" class="btn btn-danger btn-sm d-none align-items-center justify-content-center" style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;" title="Hapus foto After"><i class="fas fa-times"></i></button>
                                </div>
                                <div id="corrodkote_evidence_after_preview_wrap" style="display:none; background:#f0f4f8;">
                                    <img id="corrodkote_evidence_after_preview" src="" alt="After" class="d-block w-100" style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                </div>
                                <div id="corrodkote_evidence_after_empty" style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <small style="font-size:0.72rem;">Belum ada foto</small>
                                </div>
                                <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #f9e0cc;">
                                    <input type="file" name="evidence_after" id="input_corrodkote_evidence_after" class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Description</label>
                            <textarea name="description" class="form-control form-control-sm border-0 shadow-sm" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Input Cass -->
<div class="modal fade" id="modalInputCass" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-plus mr-2 text-primary"></i> Input Cass Test
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="#" method="POST" id="formInputCass" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="report_id" id="cass_report_id">
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Nama Part</label>
                            <input type="text" id="cass_part_name" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Customer</label>
                            <input type="text" id="cass_customer" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Std. Customer</label>
                            <input type="text" id="cass_std" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Time (hours) STD. Min RN</label>
                            <input type="text" name="standard_time" id="cass_standard_time" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Time (hours) Actual</label>
                            <input type="text" name="actual_cass_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam</label>
                            <input type="text" name="standar_jam_cass" id="cass_standar_jam" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Test</label>
                            <input type="date" name="tanggal_test" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Produksi</label>
                            <input type="date" name="production_date" id="cass_produksi" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Shift</label>
                            <select name="shift" id="cass_shift" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Masuk Chamber</label>
                            <input type="date" name="tgl_masuk" id="cass_tgl_masuk" class="form-control form-control-sm border-0 shadow-sm auto-calc-trigger" data-target="cass" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Jam Masuk</label>
                            <input type="time" name="jam_masuk" id="cass_jam_masuk" class="form-control form-control-sm border-0 shadow-sm auto-calc-trigger" data-target="cass" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Keluar Chamber</label>
                            <input type="date" name="tgl_keluar" id="cass_tgl_keluar" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Jam Keluar</label>
                            <input type="time" name="jam_keluar" id="cass_jam_keluar" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">No Lot</label>
                            <input type="text" name="lot_no" id="cass_lot" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Result</label>
                            <select name="result_judgment" class="form-control form-control-sm border-0 shadow-sm" required>
                                <option value="">Pilih...</option>
                                <option value="OK">OK</option>
                                <option value="NG">NG</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Evidence Before -->
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                                <i class="fas fa-image mr-1 text-info"></i> Evidence Before
                            </label>
                            <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#e8f4fd; border-bottom:1px solid #bee3f8;">
                                    <span class="small font-weight-bold text-primary">
                                        <i class="fas fa-circle-notch mr-1"></i>BEFORE TEST
                                    </span>
                                    <button type="button" id="btn_delete_cass_evidence_before" class="btn btn-danger btn-sm d-none align-items-center justify-content-center" style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;" title="Hapus foto Before"><i class="fas fa-times"></i></button>
                                </div>
                                <div id="cass_evidence_before_preview_wrap" style="display:none; background:#f0f4f8;">
                                    <img id="cass_evidence_before_preview" src="" alt="Before" class="d-block w-100" style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                </div>
                                <div id="cass_evidence_before_empty" style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <small style="font-size:0.72rem;">Belum ada foto</small>
                                </div>
                                <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #e8f4fd;">
                                    <input type="file" name="evidence_before" id="input_cass_evidence_before" class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- Evidence After -->
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                                <i class="fas fa-image mr-1 text-info"></i> Evidence After
                            </label>
                            <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#fdf2e9; border-bottom:1px solid #f9e0cc;">
                                    <span class="small font-weight-bold text-warning" style="color: #d35400 !important;">
                                        <i class="fas fa-check-circle mr-1"></i>AFTER TEST
                                    </span>
                                    <button type="button" id="btn_delete_cass_evidence_after" class="btn btn-danger btn-sm d-none align-items-center justify-content-center" style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;" title="Hapus foto After"><i class="fas fa-times"></i></button>
                                </div>
                                <div id="cass_evidence_after_preview_wrap" style="display:none; background:#f0f4f8;">
                                    <img id="cass_evidence_after_preview" src="" alt="After" class="d-block w-100" style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                </div>
                                <div id="cass_evidence_after_empty" style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <small style="font-size:0.72rem;">Belum ada foto</small>
                                </div>
                                <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #f9e0cc;">
                                    <input type="file" name="evidence_after" id="input_cass_evidence_after" class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Description</label>
                            <textarea name="description" class="form-control form-control-sm border-0 shadow-sm" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Input Salt Spray -->
<div class="modal fade" id="modalInputSaltSpray" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-plus mr-2 text-primary"></i> Input Salt Spray Test
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="#" method="POST" id="formInputSaltSpray" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="report_id" id="salt_report_id">
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Nama Part</label>
                            <input type="text" id="salt_part_name" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Customer</label>
                            <input type="text" id="salt_customer" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Std. Customer</label>
                            <input type="text" id="salt_std" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Time (hours) STD. Rusting</label>
                            <input type="text" name="standard_time" id="salt_standard_time" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Time (hours) STD. Rusting Actual</label>
                            <input type="text" name="actual_salt_spray_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam</label>
                            <input type="text" name="standar_jam_salt_spray" id="salt_standar_jam" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Test</label>
                            <input type="date" name="tanggal_test" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Produksi</label>
                            <input type="date" name="production_date" id="salt_produksi" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Shift</label>
                            <select name="shift" id="salt_shift" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Masuk Chamber</label>
                            <input type="date" name="tgl_masuk" id="salt_tgl_masuk" class="form-control form-control-sm border-0 shadow-sm auto-calc-trigger" data-target="salt" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Jam Masuk</label>
                            <input type="time" name="jam_masuk" id="salt_jam_masuk" class="form-control form-control-sm border-0 shadow-sm auto-calc-trigger" data-target="salt" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Keluar Chamber</label>
                            <input type="date" name="tgl_keluar" id="salt_tgl_keluar" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Jam Keluar</label>
                            <input type="time" name="jam_keluar" id="salt_jam_keluar" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">No Lot</label>
                            <input type="text" name="lot_no" id="salt_lot" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Result</label>
                            <select name="result_judgment" class="form-control form-control-sm border-0 shadow-sm" required>
                                <option value="">Pilih...</option>
                                <option value="OK">OK</option>
                                <option value="NG">NG</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Evidence Before -->
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                                <i class="fas fa-image mr-1 text-info"></i> Evidence Before
                            </label>
                            <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#e8f4fd; border-bottom:1px solid #bee3f8;">
                                    <span class="small font-weight-bold text-primary">
                                        <i class="fas fa-circle-notch mr-1"></i>BEFORE TEST
                                    </span>
                                    <button type="button" id="btn_delete_salt_spray_evidence_before" class="btn btn-danger btn-sm d-none align-items-center justify-content-center" style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;" title="Hapus foto Before"><i class="fas fa-times"></i></button>
                                </div>
                                <div id="salt_spray_evidence_before_preview_wrap" style="display:none; background:#f0f4f8;">
                                    <img id="salt_spray_evidence_before_preview" src="" alt="Before" class="d-block w-100" style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                </div>
                                <div id="salt_spray_evidence_before_empty" style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <small style="font-size:0.72rem;">Belum ada foto</small>
                                </div>
                                <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #e8f4fd;">
                                    <input type="file" name="evidence_before" id="input_salt_spray_evidence_before" class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- Evidence After -->
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                                <i class="fas fa-image mr-1 text-info"></i> Evidence After
                            </label>
                            <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#fdf2e9; border-bottom:1px solid #f9e0cc;">
                                    <span class="small font-weight-bold text-warning" style="color: #d35400 !important;">
                                        <i class="fas fa-check-circle mr-1"></i>AFTER TEST
                                    </span>
                                    <button type="button" id="btn_delete_salt_spray_evidence_after" class="btn btn-danger btn-sm d-none align-items-center justify-content-center" style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;" title="Hapus foto After"><i class="fas fa-times"></i></button>
                                </div>
                                <div id="salt_spray_evidence_after_preview_wrap" style="display:none; background:#f0f4f8;">
                                    <img id="salt_spray_evidence_after_preview" src="" alt="After" class="d-block w-100" style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                </div>
                                <div id="salt_spray_evidence_after_empty" style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <small style="font-size:0.72rem;">Belum ada foto</small>
                                </div>
                                <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #f9e0cc;">
                                    <input type="file" name="evidence_after" id="input_salt_spray_evidence_after" class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Description</label>
                            <textarea name="description" class="form-control form-control-sm border-0 shadow-sm" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Input Porecount -->
<div class="modal fade" id="modalInputPorecount" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-plus mr-2 text-primary"></i> Input Porecount Test
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="#" method="POST" id="formInputPorecount" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="report_id" id="porecount_report_id">
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Nama Part</label>
                            <input type="text" id="porecount_part_name" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Customer</label>
                            <input type="text" id="porecount_customer" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Std. Customer</label>
                            <input type="text" id="porecount_std" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Std. Min</label>
                            <input type="text" name="standard_min" id="porecount_standard_min" class="form-control form-control-sm border-0 shadow-sm" readonly>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Aktual</label>
                            <input type="text" name="actual_porecount" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Test</label>
                            <input type="date" name="tanggal_test" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tanggal Produksi</label>
                            <input type="date" name="production_date" id="porecount_produksi" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Shift</label>
                            <select name="shift" id="porecount_shift" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">No Lot</label>
                            <input type="text" name="lot_no" id="porecount_lot" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Result</label>
                            <select name="result_judgment" class="form-control form-control-sm border-0 shadow-sm" required>
                                <option value="">Pilih...</option>
                                <option value="OK">OK</option>
                                <option value="NG">NG</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Evidence Before -->
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                                <i class="fas fa-image mr-1 text-info"></i> Evidence Before
                            </label>
                            <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#e8f4fd; border-bottom:1px solid #bee3f8;">
                                    <span class="small font-weight-bold text-primary">
                                        <i class="fas fa-circle-notch mr-1"></i>BEFORE TEST
                                    </span>
                                    <button type="button" id="btn_delete_porecount_evidence_before" class="btn btn-danger btn-sm d-none align-items-center justify-content-center" style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;" title="Hapus foto Before"><i class="fas fa-times"></i></button>
                                </div>
                                <div id="porecount_evidence_before_preview_wrap" style="display:none; background:#f0f4f8;">
                                    <img id="porecount_evidence_before_preview" src="" alt="Before" class="d-block w-100" style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                </div>
                                <div id="porecount_evidence_before_empty" style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <small style="font-size:0.72rem;">Belum ada foto</small>
                                </div>
                                <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #e8f4fd;">
                                    <input type="file" name="evidence_before" id="input_porecount_evidence_before" class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- Evidence After -->
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                                <i class="fas fa-image mr-1 text-info"></i> Evidence After
                            </label>
                            <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#fdf2e9; border-bottom:1px solid #f9e0cc;">
                                    <span class="small font-weight-bold text-warning" style="color: #d35400 !important;">
                                        <i class="fas fa-check-circle mr-1"></i>AFTER TEST
                                    </span>
                                    <button type="button" id="btn_delete_porecount_evidence_after" class="btn btn-danger btn-sm d-none align-items-center justify-content-center" style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;" title="Hapus foto After"><i class="fas fa-times"></i></button>
                                </div>
                                <div id="porecount_evidence_after_preview_wrap" style="display:none; background:#f0f4f8;">
                                    <img id="porecount_evidence_after_preview" src="" alt="After" class="d-block w-100" style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                </div>
                                <div id="porecount_evidence_after_empty" style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <small style="font-size:0.72rem;">Belum ada foto</small>
                                </div>
                                <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #f9e0cc;">
                                    <input type="file" name="evidence_after" id="input_porecount_evidence_after" class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Description</label>
                            <textarea name="description" class="form-control form-control-sm border-0 shadow-sm" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Evidence -->
<div class="modal fade" id="modalViewEvidence" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-images mr-2 text-info"></i> Evidence Photo
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8fafc; border-radius: 0 0 12px 12px;">
                <div class="row text-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="font-weight-bold text-gray-700 mb-2">Before Test</h6>
                        <div class="border rounded bg-white p-2 d-flex flex-column align-items-center justify-content-center position-relative" style="min-height: 250px;">
                            <style>
                                .img-zoom { transition: transform 0.3s ease; cursor: zoom-in; }
                                .img-zoom:hover { transform: scale(1.8); z-index: 1055; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important; }
                            </style>
                            <img id="evidenceBeforeImg" src="" alt="Evidence Before" class="img-fluid rounded shadow-sm img-zoom mb-2" style="max-height: 270px; display: none;">
                            <span id="evidenceBeforeEmpty" class="text-muted">Tidak ada foto</span>
                            <small id="evidenceBeforeTimeText" class="text-dark font-weight-bold" style="font-size: 0.7rem; display: none;"></small>
                            <a href="#" download id="btnDownloadBefore" class="btn btn-sm btn-success position-absolute" style="bottom: 10px; right: 10px; display: none;" title="Download Before"><i class="fas fa-download"></i></a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-gray-700 mb-2">After Test</h6>
                        <div class="border rounded bg-white p-2 d-flex flex-column align-items-center justify-content-center position-relative" style="min-height: 250px;">
                            <img id="evidenceAfterImg" src="" alt="Evidence After" class="img-fluid rounded shadow-sm img-zoom mb-2" style="max-height: 270px; display: none;">
                            <span id="evidenceAfterEmpty" class="text-muted">Tidak ada foto</span>
                            <small id="evidenceAfterTimeText" class="text-dark font-weight-bold" style="font-size: 0.7rem; display: none;"></small>
                            <a href="#" download id="btnDownloadAfter" class="btn btn-sm btn-success position-absolute" style="bottom: 10px; right: 10px; display: none;" title="Download After"><i class="fas fa-download"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Float Menu untuk Bulk Delete -->
    <div id="bulkActionMenu" class="position-fixed shadow-lg rounded" style="bottom: 80px; left: 50%; transform: translateX(-50%); display: none; z-index: 1050; background: white; padding: 15px; border: 1px solid #e3e6f0;">
        <div class="d-flex align-items-center">
            <span class="mr-3 font-weight-bold text-gray-800"><span id="bulkSelectedCount">0</span> Data Terpilih</span>
            <button class="btn btn-danger btn-sm shadow-sm" id="btnBulkDelete">
                <i class="fas fa-trash-alt mr-1"></i> Hapus Data
            </button>
        </div>
    </div>

@push('scripts')
<script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
<script>
    window.__DURABILITY_PLATING_REPORT__ = {
        updateUrl: "{{ route('standard-performance-tests.thickness.update', ':id') }}",
        bulkDestroyUrl: "{{ route('standard-performance-tests.thickness.bulk_destroy') }}",
        csrfToken: "{{ csrf_token() }}",
        testType: "{{ $testType }}",
        baseUrl: "{{ rtrim(asset(''), '/') }}/"
    };
</script>
<script src="{{ asset('js/durability_plating/report.js') }}?v={{ filemtime(public_path('js/durability_plating/report.js')) }}"></script>
<script>
    $(document).ready(function() {
        $('.btn-view-evidence').on('click', function() {
            var beforeUrl = $(this).data('before');
            var afterUrl = $(this).data('after');
            var beforeTime = $(this).data('before-time');
            var afterTime = $(this).data('after-time');

            if (beforeUrl) {
                $('#evidenceBeforeImg').attr('src', beforeUrl).show();
                $('#btnDownloadBefore').attr('href', beforeUrl).show();
                $('#evidenceBeforeEmpty').hide();
                if (beforeTime) {
                    $('#evidenceBeforeTimeText').text('Diunggah: ' + beforeTime).show();
                } else {
                    $('#evidenceBeforeTimeText').hide();
                }
            } else {
                $('#evidenceBeforeImg').hide();
                $('#btnDownloadBefore').hide();
                $('#evidenceBeforeEmpty').show();
                $('#evidenceBeforeTimeText').hide();
            }

            if (afterUrl) {
                $('#evidenceAfterImg').attr('src', afterUrl).show();
                $('#btnDownloadAfter').attr('href', afterUrl).show();
                $('#evidenceAfterEmpty').hide();
                if (afterTime) {
                    $('#evidenceAfterTimeText').text('Diunggah: ' + afterTime).show();
                } else {
                    $('#evidenceAfterTimeText').hide();
                }
            } else {
                $('#evidenceAfterImg').hide();
                $('#btnDownloadAfter').hide();
                $('#evidenceAfterEmpty').show();
                $('#evidenceAfterTimeText').hide();
            }

            $('#modalViewEvidence').modal('show');
        });

        // Handle datalist selection for Tambah Data
        $('#add_part_search').on('input change', function() {
            var val = $(this).val().trim().toLowerCase();
            var id = '';
            $('#masterPartList option').each(function() {
                var optionVal = ($(this).attr('value') || '').trim().toLowerCase();
                if (optionVal === val) {
                    id = $(this).attr('data-id');
                    return false; // break loop
                }
            });
            $('#hidden_standard_performance_test_id').val(id);
        });

        $('#formAddData').on('submit', function(e) {
            var val = $('#add_part_search').val().trim().toLowerCase();
            var id = $('#hidden_standard_performance_test_id').val();
            if(!id) {
                $('#masterPartList option').each(function() {
                    var optionVal = ($(this).attr('value') || '').trim().toLowerCase();
                    if (optionVal === val) {
                        id = $(this).attr('data-id');
                        $('#hidden_standard_performance_test_id').val(id);
                        return false; 
                    }
                });
            }
            if(!id) {
                e.preventDefault();
                alert('Part / Customer tidak valid. Silakan pilih dari daftar yang tersedia.');
                $('#add_part_search').focus();
                return false;
            }
        });
        
        function validateForm(formId) {
            var form = document.getElementById(formId);
            if (!form) return;
            
            form.addEventListener('submit', function(e) {
                var emptyFields = [];
                var inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
                
                inputs.forEach(function(input) {
                    if (input.value.trim() === '') {
                        var formGroup = input.closest('.form-group');
                        var label = formGroup ? formGroup.querySelector('label') : null;
                        var labelText = label ? label.innerText.replace('*', '').trim() : input.name;
                        
                        var section = formGroup ? formGroup.closest('.row').previousElementSibling : null;
                        if (section && section.tagName === 'H6') {
                            var sectionName = section.innerText.trim();
                            labelText = sectionName + ' - ' + labelText;
                        }
                        
                        emptyFields.push(labelText);
                    }
                });
                
                if (emptyFields.length > 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap!',
                        html: '<div class="text-center mb-3">Mohon isi semua kolom berikut:<br><small>(Isi dengan tanda strip "-" jika data tidak ada)</small></div><div class="text-left" style="max-height: 200px; overflow-y: auto; background: #f8fafc; padding: 10px; border-radius: 8px;"><ul><li class="text-danger">' + emptyFields.join('</li><li class="text-danger">') + '</li></ul></div>',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Baik, Mengerti'
                    });
                }
            });
        }
        
        validateForm('formEditThickness');
        validateForm('formInputCorrodkote');
        validateForm('formInputCass');
        validateForm('formInputSaltSpray');
        validateForm('formInputPorecount');
        validateForm('formAddData');
    });
</script>
@endpush

@if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray' || $testType == 'porecount')
<!-- Modal Input Data Baru -->
<div class="modal fade" id="modalAddData" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-plus mr-2 text-success"></i> Tambah Data Laporan {{ ucwords(str_replace('_', ' ', $testType)) }} Test
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddData" action="{{ route('standard-performance-tests.thickness.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    
                    <!-- Part Selection -->
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-gray-700">Pilih Part / Customer <span class="text-danger">*</span></label>
                        <input type="text" id="add_part_search" list="masterPartList" class="form-control form-control-sm border-0 shadow-sm" placeholder="Ketik / Pilih Part..." autocomplete="off" required>
                        <input type="hidden" name="standard_performance_test_id" id="hidden_standard_performance_test_id">
                        <datalist id="masterPartList">
                            @foreach($masterItems as $item)
                                <option data-id="{{ $item->id }}" value="{{ $item->part_name }} - {{ $item->customer_name }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <!-- General Fields (Tanggal, Shift, Lot) -->
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Produksi</label>
                            <input type="date" name="production_date" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Shift</label>
                            <select name="shift" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">No Lot</label>
                            <input type="text" name="lot_no" class="form-control form-control-sm border-0 shadow-sm" placeholder="No Lot">
                        </div>
                    </div>

                    <!-- Test Specific Fields -->
                    @if($testType == 'corrodkote')
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Time (hrs) Aktual <span class="text-danger">*</span></label>
                            <input type="text" name="actual_corrodkote_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam <span class="text-danger">*</span></label>
                            <input type="text" name="standar_jam_corrodkote" id="new_standar_jam_corrodkote" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Aktual % Corrosion</label>
                            <input type="text" name="aktual_corrosion" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                    </div>
                    @elseif($testType == 'cass')
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Test Aktual (Hours) <span class="text-danger">*</span></label>
                            <input type="text" name="actual_cass_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam <span class="text-danger">*</span></label>
                            <input type="text" name="standar_jam_cass" id="new_standar_jam_cass" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" required>
                        </div>
                    </div>
                    @elseif($testType == 'salt_spray')
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Test Aktual (Hours) <span class="text-danger">*</span></label>
                            <input type="text" name="actual_salt_spray_waktu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Standar Jam <span class="text-danger">*</span></label>
                            <input type="text" name="standar_jam_salt_spray" id="new_standar_jam_salt" class="form-control form-control-sm border-0 shadow-sm auto-calc-jam" required>
                        </div>
                    </div>
                    @elseif($testType == 'porecount')
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Aktual <span class="text-danger">*</span></label>
                            <input type="text" name="actual_porecount" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Chamber Time -->
                    @if($testType == 'corrodkote' || $testType == 'cass' || $testType == 'salt_spray')
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Masuk Chamber</label>
                            <div class="d-flex align-items-center">
                                <input type="date" name="tgl_masuk" id="new_tgl_masuk" class="form-control form-control-sm border-0 shadow-sm mr-2 auto-calc-trigger" data-target="new">
                                <input type="time" name="jam_masuk" id="new_jam_masuk" class="form-control form-control-sm border-0 shadow-sm auto-calc-trigger" data-target="new">
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Waktu Keluar Chamber</label>
                            <div class="d-flex align-items-center">
                                <input type="date" name="tgl_keluar" id="new_tgl_keluar" class="form-control form-control-sm border-0 shadow-sm mr-2">
                                <input type="time" name="jam_keluar" id="new_jam_keluar" class="form-control form-control-sm border-0 shadow-sm">
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-2">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Result / Judgment</label>
                            <select name="result_judgment" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="-">-</option>
                                <option value="OK">OK</option>
                                <option value="NG">NG</option>
                            </select>
                        </div>
                    </div>

                    <!-- Evidence Upload (Card Style like Edit Modal) -->
                    <div class="mb-3">
                        <label class="small font-weight-bold text-gray-700 mb-2 d-block">
                            <i class="fas fa-images mr-1 text-info"></i> Evidence Foto
                        </label>
                        <div class="row">
                            {{-- BEFORE --}}
                            <div class="col-6">
                                <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#e8f4fd; border-bottom:1px solid #bee3f8;">
                                        <span class="small font-weight-bold text-primary">
                                            <i class="fas fa-circle-notch mr-1"></i>BEFORE TEST
                                        </span>
                                        <button type="button" id="btn_delete_new_evidence_before"
                                            class="btn btn-danger btn-sm d-none align-items-center justify-content-center"
                                            style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;"
                                            title="Hapus foto Before">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div id="new_evidence_before_preview_wrap" style="display:none; background:#f0f4f8;">
                                        <img id="new_evidence_before_preview" src="" alt="Before"
                                            class="d-block w-100"
                                            style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                    </div>
                                    <div id="new_evidence_before_empty"
                                        style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <small style="font-size:0.72rem;">Belum ada foto</small>
                                    </div>
                                    <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #e8f4fd;">
                                        <input type="file" name="evidence_before" id="input_new_evidence_before"
                                            class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            {{-- AFTER --}}
                            <div class="col-6">
                                <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between" style="background:#edf7ed; border-bottom:1px solid #b7dbb7;">
                                        <span class="small font-weight-bold text-success">
                                            <i class="fas fa-check-circle mr-1"></i>AFTER TEST
                                        </span>
                                        <button type="button" id="btn_delete_new_evidence_after"
                                            class="btn btn-danger btn-sm d-none align-items-center justify-content-center"
                                            style="width:22px;height:22px;padding:0;border-radius:50%;font-size:11px;"
                                            title="Hapus foto After">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div id="new_evidence_after_preview_wrap" style="display:none; background:#f0f4f8;">
                                        <img id="new_evidence_after_preview" src="" alt="After"
                                            class="d-block w-100"
                                            style="height:180px; object-fit:contain; background:#f0f4f8; cursor:zoom-in;">
                                    </div>
                                    <div id="new_evidence_after_empty"
                                        style="display:flex; height:180px; background:#f0f4f8; color:#adb5bd; flex-direction:column; align-items:center; justify-content:center;">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <small style="font-size:0.72rem;">Belum ada foto</small>
                                    </div>
                                    <div class="card-footer py-2 px-3" style="background:#fff; border-top:1px solid #edf7ed;">
                                        <input type="file" name="evidence_after" id="input_new_evidence_after"
                                            class="form-control-file" style="font-size:0.72rem;" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group mb-0">
                            <label class="small font-weight-bold text-gray-700">Description / Keterangan</label>
                            <textarea name="description" class="form-control form-control-sm border-0 shadow-sm" rows="3" placeholder="Masukkan keterangan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
