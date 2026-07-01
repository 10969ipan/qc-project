@extends('layouts.admin')

@section('title', 'Laporan Standard Performance Test Plating Plastic')

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

    /* Fix column layout via table-layout fixed */
    #dataTable {
        table-layout: fixed !important;
    }
    #dataTable th, #dataTable td {
        word-break: break-word;
        overflow: hidden;
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

    /* Result/Judgment badge size */
    #dataTable td .badge {
        font-size: 0.75rem !important;
        padding: 4px 8px !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px;
    }
</style>

@php
    $plantCode = auth()->check() && auth()->user()->plant ? strtolower(auth()->user()->plant->name) : 'jakarta';
    $docHeader = \App\Models\GeneralSetting::getDocHeader('master_standard_performance_test', $plantCode, [
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
                        LAPORAN STANDARD PERFORMANCE TEST PLATING PLASTIC
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

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('standard-performance-tests.report') }}" method="GET"
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
                <button type="submit" name="print" value="true" formtarget="_blank" class="btn btn-warning btn-sm shadow-sm rounded-pill px-3" title="Print Laporan">
                    <i class="fas fa-print fa-sm"></i> Print
                </button>
                <a href="{{ route('standard-performance-tests.index') }}" class="btn btn-light border btn-sm shadow-sm rounded-pill px-3" title="Kembali">
                    <i class="fas fa-arrow-left fa-sm"></i> Kembali
                </a>
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
                    <col style="width: 38px;">       <!-- No -->
                    <col style="width: 150px;">      <!-- Nama Part -->
                    <col style="width: 110px;">      <!-- Customer -->
                    <col style="width: 130px;">      <!-- Std Customer -->
                    <!-- STANDAR -->
                    <col style="width: 60px;">       <!-- S Cu -->
                    <col style="width: 60px;">       <!-- S Ni -->
                    <col style="width: 60px;">       <!-- S Cr -->
                    <col style="width: 75px;">       <!-- S Corrodkote Waktu -->
                    <col style="width: 75px;">       <!-- S Cass Waktu -->
                    <col style="width: 75px;">       <!-- S Salt Spray Waktu -->
                    <col style="width: 90px;">       <!-- S Porecount -->
                    <!-- AKTUAL -->
                    <col style="width: 60px;">       <!-- A Cu -->
                    <col style="width: 60px;">       <!-- A Ni -->
                    <col style="width: 60px;">       <!-- A Cr -->
                    <col style="width: 75px;">       <!-- A Corrodkote Waktu -->
                    <col style="width: 65px;">       <!-- A Corrodkote Hasil -->
                    <col style="width: 75px;">       <!-- A Cass Waktu -->
                    <col style="width: 65px;">       <!-- A Cass Hasil -->
                    <col style="width: 75px;">       <!-- A Salt Spray Waktu -->
                    <col style="width: 65px;">       <!-- A Salt Spray Hasil -->
                    <col style="width: 90px;">       <!-- A Porecount -->
                    <!-- Trail -->
                    <col style="width: 90px;">       <!-- Tgl Produksi -->
                    <col style="width: 50px;">       <!-- Shift -->
                    <col style="width: 80px;">       <!-- No Lot -->
                    <col style="width: 85px;">       <!-- Tanggal Check -->
                    <col style="width: 100px;">      <!-- Result/Judgment -->
                    <col style="width: 95px;">       <!-- PIC -->
                    <col style="width: 140px;">      <!-- Description -->
                    <col style="width: 65px;">       <!-- Actions -->
                </colgroup>
                                                <thead>
                    <tr>
                        <th rowspan="3" class="align-middle text-center">No.</th>
                        <th rowspan="3" class="align-middle sticky-col">Name Part</th>
                        <th rowspan="3" class="align-middle">Customer</th>
                        <th rowspan="3" class="align-middle">Standard Customer<br>OEM / ELECTRONIC</th>
                        <th colspan="7" class="text-center th-standar">STANDARD</th>
                        <th colspan="10" class="text-center th-aktual">ACTUAL</th>
                        <th rowspan="3" class="align-middle text-center">Tgl Produksi</th>
                        <th rowspan="3" class="align-middle text-center">Shift</th>
                        <th rowspan="3" class="align-middle text-center">No Lot</th>
                        <th rowspan="3" class="align-middle text-center">Tanggal Check</th>
                        <th rowspan="3" class="align-middle text-center">Result / Judgment</th>
                        <th rowspan="3" class="align-middle text-center">PIC</th>
                        <th rowspan="3" class="align-middle text-center">Description</th>
                        <th rowspan="3" class="text-center align-middle">Actions</th>
                    </tr>
                    <tr>
                        <!-- STANDAR -->
                        <th colspan="3" class="text-center th-standar">Thickness (<span style="text-transform: none !important;">m&micro;</span>)</th>
                        <th class="text-center align-middle th-standar">Corrodkote</th>
                        <th class="text-center align-middle th-standar">Cass Test</th>
                        <th class="text-center align-middle th-standar">Salt Spray Test</th>
                        <th rowspan="2" class="text-center align-middle th-standar">Porecount Test</th>
                        <!-- AKTUAL -->
                        <th colspan="3" class="text-center th-aktual">Thickness (<span style="text-transform: none !important;">m&micro;</span>)</th>
                        <th colspan="2" class="text-center align-middle th-aktual">Corrodkote</th>
                        <th colspan="2" class="text-center align-middle th-aktual">Cass Test</th>
                        <th colspan="2" class="text-center align-middle th-aktual">Salt Spray Test</th>
                        <th rowspan="2" class="text-center align-middle th-aktual">Porecount Test</th>
                    </tr>
                    <tr>
                        <!-- STANDAR THICKNESS -->
                        <th class="text-center th-standar" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Cu</span></th>
                        <th class="text-center th-standar" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Ni</span></th>
                        <th class="text-center th-standar" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Cr</span></th>
                        <!-- STANDAR Corrodkote -->
                        <th class="text-center th-standar" style="min-width: 70px;">Time (Hours)</th>
                        <!-- STANDAR Cass -->
                        <th class="text-center th-standar" style="min-width: 70px;">Time (Hours)</th>
                        <!-- STANDAR Salt Spray -->
                        <th class="text-center th-standar" style="min-width: 70px;">Time (Hours)</th>

                        <!-- AKTUAL THICKNESS -->
                        <th class="text-center th-aktual" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Cu</span></th>
                        <th class="text-center th-aktual" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Ni</span></th>
                        <th class="text-center th-aktual" style="width: 60px; min-width: 60px; max-width: 60px;"><span style="text-transform: none !important; font-weight: bold !important;">Cr</span></th>
                        <!-- AKTUAL Corrodkote -->
                        <th class="text-center th-aktual" style="min-width: 70px;">Time (Hours)</th>
                        <th class="text-center th-aktual" style="min-width: 70px;">Result</th>
                        <!-- AKTUAL Cass -->
                        <th class="text-center th-aktual" style="min-width: 70px;">Time (Hours)</th>
                        <th class="text-center th-aktual" style="min-width: 70px;">Result</th>
                        <!-- AKTUAL Salt Spray -->
                        <th class="text-center th-aktual" style="min-width: 70px;">Time (Hours)</th>
                        <th class="text-center th-aktual" style="min-width: 70px;">Result</th>
                    </tr>
                </thead>
                                                <tbody>
                    @foreach($reports as $index => $report)
                        @php
                            $std = $report->standard;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $reports->firstItem() + $index }}</td>
                            <td class="text-center font-weight-bold sticky-col">{{ $std->part_name ?? '-' }}</td>
                            <td class="text-center">{{ $std->customer_name ?? '-' }}</td>
                            <td class="text-center">{{ $std->customer_standard ?? '-' }}</td>
                            
                            <!-- STANDAR -->
                            <td class="text-center td-standar">{{ $std->thickness_cu ?? '-' }}</td>
                            <td class="text-center td-standar">{{ $std->thickness_ni ?? '-' }}</td>
                            <td class="text-center td-standar">{{ $std->thickness_cr ?? '-' }}</td>
                            <td class="text-center td-standar">{{ $std->corrodkote_time ?? '-' }}</td>
                            <td class="text-center td-standar">{{ $std->cass_time ?? '-' }}</td>
                            <td class="text-center td-standar">{{ $std->salt_spray_time ?? '-' }}</td>
                            <td class="text-center td-standar">{{ $std->porecount_std_min ?? '-' }}</td>
                            
                            <!-- AKTUAL -->
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_cu ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_ni ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_cr ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_corrodkote_waktu ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_corrodkote ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_cass_waktu ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_cass ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_salt_spray_waktu ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_salt_spray ?? '-' }}</td>
                            <td class="text-center font-weight-bold text-success td-aktual">{{ $report->actual_porecount ?? '-' }}</td>

                            <td class="text-center">{{ $report->production_date ? \Carbon\Carbon::parse($report->production_date)->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">{{ $report->shift ?? '-' }}</td>
                            <td class="text-center">{{ $report->lot_no ?? '-' }}</td>
                            <td class="text-center">{{ $report->tanggal_cek ? \Carbon\Carbon::parse($report->tanggal_cek)->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">
                                @php
                                    $rj = $report->result_judgment ?? '-';
                                    $rjClass = match(strtolower($rj)) {
                                        'ok' => 'badge badge-success',
                                        'ng' => 'badge badge-danger',
                                        default => 'text-muted'
                                    };
                                @endphp
                                <span class="{{ $rjClass }}">{{ $rj }}</span>
                            </td>
                            <td class="text-center">{{ $report->analis ? $report->analis->name : '-' }}</td>
                            <td class="text-center">{{ $report->description ?? '-' }}</td>

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
                                            data-part="{{ $std->part_name }}">
                                            <i class="fas fa-edit text-info fa-fw mr-2"></i> Edit Laporan
                                        </button>
                                        
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('standard-performance-tests.thickness.destroy', $report->id) }}" method="POST" class="d-inline delete-form w-100">
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
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-edit mr-2 text-info"></i> Edit Laporan Thickness
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="formEditThickness">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tgl Produksi</label>
                            <input type="date" name="production_date" id="edit_production_date" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Shift</label>
                            <select name="shift" id="edit_shift" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">No Lot</label>
                            <input type="text" name="lot_no" id="edit_lot_no" class="form-control form-control-sm border-0 shadow-sm" placeholder="No Lot">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-gray-700">Nama Part</label>
                        <input type="text" id="edit_thickness_part_name" class="form-control form-control-sm border-0 shadow-sm" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Cu</label>
                            <input type="text" name="actual_cu" id="edit_actual_cu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Ni</label>
                            <input type="text" name="actual_ni" id="edit_actual_ni" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Cr</label>
                            <input type="text" name="actual_cr" id="edit_actual_cr" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Corrodkote</label>
                            <div class="row no-gutters">
                                <div class="col pr-1">
                                    <span class="x-small text-muted" style="font-size:0.7rem;">Waktu (Jam)</span>
                                    <input type="text" name="actual_corrodkote_waktu" id="edit_actual_corrodkote_waktu" class="form-control form-control-sm border-0 shadow-sm" placeholder="-">
                                </div>
                                <div class="col pl-1">
                                    <span class="x-small text-muted" style="font-size:0.7rem;">Hasil</span>
                                    <input type="text" name="actual_corrodkote" id="edit_actual_corrodkote" class="form-control form-control-sm border-0 shadow-sm" placeholder="-">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Cass Test</label>
                            <div class="row no-gutters">
                                <div class="col pr-1">
                                    <span class="x-small text-muted" style="font-size:0.7rem;">Waktu (Jam)</span>
                                    <input type="text" name="actual_cass_waktu" id="edit_actual_cass_waktu" class="form-control form-control-sm border-0 shadow-sm" placeholder="-">
                                </div>
                                <div class="col pl-1">
                                    <span class="x-small text-muted" style="font-size:0.7rem;">Hasil</span>
                                    <input type="text" name="actual_cass" id="edit_actual_cass" class="form-control form-control-sm border-0 shadow-sm" placeholder="-">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-0">
                            <label class="small font-weight-bold text-gray-700">Salt Spray Test</label>
                            <div class="row no-gutters">
                                <div class="col pr-1">
                                    <span class="x-small text-muted" style="font-size:0.7rem;">Waktu (Jam)</span>
                                    <input type="text" name="actual_salt_spray_waktu" id="edit_actual_salt_spray_waktu" class="form-control form-control-sm border-0 shadow-sm" placeholder="-">
                                </div>
                                <div class="col pl-1">
                                    <span class="x-small text-muted" style="font-size:0.7rem;">Hasil</span>
                                    <input type="text" name="actual_salt_spray" id="edit_actual_salt_spray" class="form-control form-control-sm border-0 shadow-sm" placeholder="-">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label class="small font-weight-bold text-gray-700">Porecount Test</label>
                            <span class="x-small text-muted d-block" style="font-size:0.7rem;">Hasil</span>
                            <input type="text" name="actual_porecount" id="edit_actual_porecount" class="form-control form-control-sm border-0 shadow-sm" placeholder="Hasil" value="-">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6 form-group mb-0">
                            <label class="small font-weight-bold text-gray-700">Result / Judgment</label>
                            <select name="result_judgment" id="edit_result_judgment" class="form-control form-control-sm border-0 shadow-sm">
                                <option value="-">-</option>
                                <option value="OK">OK</option>
                                <option value="NG">NG</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label class="small font-weight-bold text-gray-700">Description / Keterangan</label>
                            <textarea name="description" id="edit_description" class="form-control form-control-sm border-0 shadow-sm" rows="1" placeholder="Masukkan keterangan (opsional)..."></textarea>
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

@push('scripts')
<script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
<script>
    $(document).ready(function() {
        if (typeof initItemSearch === 'function') {
            initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No...' });
        }
        $('.btn-edit-thickness').click(function() {
            let item = $(this).data('item');
            let partName = $(this).data('part');
            let url = "{{ route('standard-performance-tests.thickness.update', ':id') }}";
            url = url.replace(':id', item.id);
            
            $('#formEditThickness').attr('action', url);
            
            $('#edit_thickness_part_name').val(partName);
            $('#edit_production_date').val(item.production_date);
            $('#edit_shift').val(item.shift);
            $('#edit_lot_no').val(item.lot_no);
            $('#edit_actual_cu').val(item.actual_cu);
            $('#edit_actual_ni').val(item.actual_ni);
            $('#edit_actual_cr').val(item.actual_cr);
            $('#edit_actual_corrodkote_waktu').val(item.actual_corrodkote_waktu);
            $('#edit_actual_corrodkote').val(item.actual_corrodkote);
            $('#edit_actual_cass_waktu').val(item.actual_cass_waktu);
            $('#edit_actual_cass').val(item.actual_cass);
            $('#edit_actual_salt_spray_waktu').val(item.actual_salt_spray_waktu);
            $('#edit_actual_salt_spray').val(item.actual_salt_spray);
            $('#edit_actual_porecount').val(item.actual_porecount);
            $('#edit_result_judgment').val(item.result_judgment ?? '-');
            $('#edit_description').val(item.description);
            
            $('#modalEditThickness').modal('show');
        });

        // Delete SweetAlert
        $('.delete-form').submit(function(e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: 'Hapus Laporan?',
                text: "Laporan yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Dynamically set sticky top for thead rows 2 and 3
        function fixStickyHeaderTops() {
            var $rows = $('#dataTable > thead > tr');
            if ($rows.length < 2) return;
            var row1H = $rows.eq(0).outerHeight();
            var row2H = $rows.length > 2 ? $rows.eq(1).outerHeight() : 0;
            $rows.eq(1).find('th').css('top', row1H + 'px');
            if ($rows.length > 2) {
                $rows.eq(2).find('th').css('top', (row1H + row2H) + 'px');
            }
        }
        
        // Hide loader and show container, then calculate sticky headers
        $('#tableLoader').hide();
        $('#tableContainer').fadeIn('fast', function() {
            fixStickyHeaderTops();
        });
        
        $(window).on('resize', fixStickyHeaderTops);
    });
</script>
@endpush
@endsection
