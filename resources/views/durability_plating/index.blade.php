@extends('layouts.admin')

@section('title', 'Master Data Standard Performance Test Plating Plastic')

@section('content')
<style>
    /* Sembunyikan search & entries default karena ada custom filter */
    #dataTable_filter, #dataTable_length {
        display: none !important;
    }
    .table-responsive {
        max-height: calc(100vh - 220px) !important;
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
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    #dataTable td, #dataTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }
    #dataTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.68rem !important;
        padding: 4px 6px !important;
    }

    /* Global TH sticky setup */
    #dataTable > thead > tr > th {
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px !important;
        padding: 6px 12px !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        top: 0 !important;
        z-index: 105 !important;
    }
    
    #dataTable > thead > tr:nth-child(2) > th {
        top: 27px !important; /* adjust based on first row height */
    }

    #dataTable .btn {
        min-width: 0 !important;
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
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
                        STANDARD PERFORMANCE TEST PLATING PLASTIC
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
        <form id="filterFormMaster" onsubmit="return false;"
            class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
            style="gap: 12px;">
            
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cari:</label>
                <input type="text" name="search_master" id="search_master" class="form-control form-control-sm border-0 shadow-sm no-autoupper" 
                    placeholder="Nama Part / Customer..." value="{{ request('search') }}" style="width: 250px; font-size: 0.75rem; text-transform: none !important;">
            </div>

            <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                <button type="button" id="btnFilterSearchMaster" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Filter">
                    <i class="fas fa-search fa-sm"></i>
                </button>
                <button type="button" id="btnResetFilterMaster" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" title="Reset Filter">
                    <i class="fas fa-undo fa-sm"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm shadow-sm rounded-pill px-3 dropdown-toggle" type="button" id="dropdownMenuLaporan" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Menu Laporan" data-boundary="window">
                        <i class="fas fa-file-alt fa-sm"></i> Laporan
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 animated--fade-in" aria-labelledby="dropdownMenuLaporan" style="font-size:0.85rem; border-radius:8px; min-width: 200px; z-index: 1050;">
                        <div class="dropdown-header font-weight-bold text-primary text-uppercase" style="font-size:0.7rem; letter-spacing:1px; padding: 0.5rem 1.5rem;">Pilih Laporan</div>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route(!empty($isTrial) ? 'standard-performance-tests-trial.report' : 'standard-performance-tests.report') }}">
                            <i class="fas fa-layer-group fa-fw mr-2 text-success"></i> Thickness
                        </a>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route(!empty($isTrial) ? 'standard-performance-tests-trial.report.corrodkote' : 'standard-performance-tests.report.corrodkote') }}">
                            <i class="fas fa-vial fa-fw mr-2 text-info"></i> Corrodkote
                        </a>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route(!empty($isTrial) ? 'standard-performance-tests-trial.report.cass' : 'standard-performance-tests.report.cass') }}">
                            <i class="fas fa-flask fa-fw mr-2 text-primary"></i> Cass Test
                        </a>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route(!empty($isTrial) ? 'standard-performance-tests-trial.report.salt_spray' : 'standard-performance-tests.report.salt_spray') }}">
                            <i class="fas fa-spray-can fa-fw mr-2 text-warning"></i> Salt Spray Test
                        </a>
                        <a class="dropdown-item py-2 font-weight-bold" href="{{ route(!empty($isTrial) ? 'standard-performance-tests-trial.report.porecount' : 'standard-performance-tests.report.porecount') }}">
                            <i class="fas fa-search-plus fa-fw mr-2 text-danger"></i> Porecount Test
                        </a>
                    </div>
                </div>

                <button type="button" class="btn btn-success btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalImport" title="Import Data">
                    <i class="fas fa-file-excel fa-sm"></i> Import
                </button>
                <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalTambah" title="Tambah Data">
                    <i class="fas fa-plus fa-sm"></i> Tambah
                </button>
            </div>
        </form>
        <!-- Loading Spinner -->
        <div id="tableLoader" class="text-center py-5">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <h6 class="text-muted font-weight-bold">Memuat Data...</h6>
        </div>

        <!-- Table Container (Hidden until initialized) -->
        <div id="tableContainer" style="display: none;">
            <div class="table-responsive" style="min-height: 300px;">
                <table class="table table-hover text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th rowspan="2">No.</th>
                        <th rowspan="2">Nama Part</th>
                        <th rowspan="2">Part No.</th>
                        <th rowspan="2">Customer</th>
                        <th rowspan="2">Standard Customer<br>OEM / ELECTRONIC</th>
                        <th rowspan="2">Kategori</th>
                        <th colspan="4" class="text-center">Thickness (<span style="text-transform: none !important;">m&micro;</span>)</th>
                        <th colspan="3" class="text-center">Corrodkote</th>
                        <th colspan="3" class="text-center">Cass Test</th>
                        <th colspan="3" class="text-center">Salt Spray Test</th>
                        <th colspan="2" class="text-center">Porecount Test (Min Porous)</th>
                        <th rowspan="2">Actions</th>
                    </tr>
                    <tr>
                        <th><span style="text-transform: none !important; font-weight: bold !important;">Cr</span></th>
                        <th><span style="text-transform: none !important; font-weight: bold !important;">Ni</span></th>
                        <th><span style="text-transform: none !important; font-weight: bold !important;">Cu</span></th>
                        <th>Frek.</th>
                        <th>Waktu (Jam)</th>
                        <th>Std Max % Corrosion</th>
                        <th>Frek.</th>
                        <th>Waktu (Jam)</th>
                        <th>Std. Min RN</th>
                        <th>Frek.</th>
                        <th>Waktu (Jam)</th>
                        <th>Std. Rusting</th>
                        <th>Frek.</th>
                        <th>Std. Min</th>
                        <th>Frek</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($standards as $index => $std)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-left font-weight-bold">{{ $std->part_name }}</td>
                            <td>{{ $std->part_number ?: '-' }}</td>
                            <td>{{ $std->customer_name }}</td>
                            <td>{{ $std->customer_standard }}</td>
                            <td>{{ $std->category ?: '-' }}</td>
                            
                            <!-- Thickness -->
                            <td>{{ $std->thickness_cr ?: '-' }}</td>
                            <td>{{ $std->thickness_ni ?: '-' }}</td>
                            <td>{{ $std->thickness_cu ?: '-' }}</td>
                            <td>{{ $std->thickness_freq ?: '-' }}</td>
                            
                            <!-- Corrodkote -->
                            <td>{{ $std->corrodkote_time ?: '-' }}</td>
                            <td>{{ $std->corrodkote_std_max_corrosion ?: '-' }}</td>
                            <td>{{ $std->corrodkote_freq ?: '-' }}</td>
                            
                            <!-- Cass -->
                            <td>{{ $std->cass_time ?: '-' }}</td>
                            <td>{{ $std->cass_std_min_rn ?: '-' }}</td>
                            <td>{{ $std->cass_freq ?: '-' }}</td>
                            
                            <!-- Salt Spray -->
                            <td>{{ $std->salt_spray_time ?: '-' }}</td>
                            <td>{{ $std->salt_spray_std_rusting ?: '-' }}</td>
                            <td>{{ $std->salt_spray_freq ?: '-' }}</td>
                            
                            <!-- Porecount -->
                            <td>{{ $std->porecount_std_min ?: '-' }}</td>
                            <td>{{ $std->porecount_freq ?: '-' }}</td>

                            <td class="align-middle text-center" style="width:50px;">
                                <div class="dropdown no-arrow">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" data-display="static" type="button" id="dropdownMenuButton-{{ $std->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:50%;">
                                        <i class="fas fa-ellipsis-v text-muted" style="font-size:12px;"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 animated--fade-in" aria-labelledby="dropdownMenuButton-{{ $std->id }}" style="min-width:180px; font-size:0.85rem; border-radius:8px;">
                                        <div class="dropdown-header font-weight-bold text-primary text-uppercase" style="font-size:0.7rem; letter-spacing:1px; padding: 0.5rem 1.5rem;">Aksi Data</div>
                                        
                                        <button type="button" class="dropdown-item btn-thickness" data-id="{{ $std->id }}" data-name="{{ $std->part_name }}" data-cu="{{ $std->thickness_cu }}" data-ni="{{ $std->thickness_ni }}" data-cr="{{ $std->thickness_cr }}" data-item="{{ json_encode($std) }}">
                                            <i class="fas fa-layer-group text-success fa-fw mr-2"></i> Input Thickness
                                        </button>
                                        
                                        <button type="button" class="dropdown-item btn-edit" data-id="{{ $std->id }}" data-item="{{ json_encode($std) }}">
                                            <i class="fas fa-edit text-info fa-fw mr-2"></i> Edit Data
                                        </button>

                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('standard-performance-tests.destroy', $std->id) }}" method="POST" class="d-inline delete-form w-100">
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
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i> Tambah Standard Performance Test
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('standard-performance-tests.store') }}" method="POST" id="formTambahMaster" novalidate>
                @csrf
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Nama Part <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <input type="text" name="part_name" id="tambah_part_name" list="partNamesList" class="form-control form-control-sm border-0 shadow-sm" required autocomplete="off">
                                <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 py-0 px-2" onclick="deleteFromDatalist('tambah_part_name', 'partNamesList', 'Nama Part')" title="Hapus dari Daftar"><i class="fas fa-times"></i></button>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 py-0 px-2" onclick="addToDatalist('tambah_part_name', 'partNamesList', 'Nama Part')" title="Tambah Baru"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Part No. <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <input type="text" name="part_number" id="tambah_part_number" list="partNumbersList" class="form-control form-control-sm border-0 shadow-sm" required autocomplete="off">
                                <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 py-0 px-2" onclick="deleteFromDatalist('tambah_part_number', 'partNumbersList', 'Part No')" title="Hapus dari Daftar"><i class="fas fa-times"></i></button>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 py-0 px-2" onclick="addToDatalist('tambah_part_number', 'partNumbersList', 'Part No')" title="Tambah Baru"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Customer <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <select name="customer_name" id="tambah_customer_name" class="form-control form-control-sm border-0 shadow-sm" required>
                                    <option value="">- Pilih -</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 py-0 px-2" onclick="deleteFromSelect('tambah_customer_name', 'Customer')" title="Hapus dari Daftar"><i class="fas fa-times"></i></button>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 py-0 px-2" onclick="addToSelect('tambah_customer_name', 'Customer')" title="Tambah Baru"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Standard Customer OEM / ELECTRONIC <span class="text-danger">*</span></label>
                            <input type="text" name="customer_standard" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Kategori</label>
                            <input type="text" name="category" id="tambah_category" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                    </div>
                    
                    <h6 class="font-weight-bold mt-3 mb-3 text-primary border-bottom pb-2"><i class="fas fa-layer-group mr-1"></i> Thickness (<span style="text-transform: none !important;">m&micro;</span>)</h6>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Cr <span class="text-danger">*</span></label>
                            <input type="text" name="thickness_cr" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Ni <span class="text-danger">*</span></label>
                            <input type="text" name="thickness_ni" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Cu <span class="text-danger">*</span></label>
                            <input type="text" name="thickness_cu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="thickness_freq" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-3 text-primary border-bottom pb-2"><i class="fas fa-vial mr-1"></i> Corrodkote</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Waktu (Jam) <span class="text-danger">*</span></label>
                            <input type="text" name="corrodkote_time" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Std Max % Corrosion <span class="text-danger">*</span></label>
                            <input type="text" name="corrodkote_std_max_corrosion" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="corrodkote_freq" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-3 text-primary border-bottom pb-2"><i class="fas fa-flask mr-1"></i> Cass Test</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Waktu (Jam) <span class="text-danger">*</span></label>
                            <input type="text" name="cass_time" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Std. Min RN <span class="text-danger">*</span></label>
                            <input type="text" name="cass_std_min_rn" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="cass_freq" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-3 text-primary border-bottom pb-2"><i class="fas fa-spray-can mr-1"></i> Salt Spray Test</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Waktu (Jam) <span class="text-danger">*</span></label>
                            <input type="text" name="salt_spray_time" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Std. Rusting <span class="text-danger">*</span></label>
                            <input type="text" name="salt_spray_std_rusting" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="salt_spray_freq" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-3 text-primary border-bottom pb-2"><i class="fas fa-search-plus mr-1"></i> Porecount Test</h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-gray-700">Std. Min <span class="text-danger">*</span></label>
                            <input type="text" name="porecount_std_min" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="porecount_freq" class="form-control form-control-sm border-0 shadow-sm" required>
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

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-edit mr-2 text-info"></i> Edit Standard Performance Test
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="formEdit" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Nama Part <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <input type="text" name="part_name" id="edit_part_name" list="partNamesList" class="form-control form-control-sm border-0 shadow-sm" required autocomplete="off">
                                <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 py-0 px-2" onclick="deleteFromDatalist('edit_part_name', 'partNamesList', 'Nama Part')" title="Hapus dari Daftar"><i class="fas fa-times"></i></button>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 py-0 px-2" onclick="addToDatalist('edit_part_name', 'partNamesList', 'Nama Part')" title="Tambah Baru"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Part No. <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <input type="text" name="part_number" id="edit_part_number" list="partNumbersList" class="form-control form-control-sm border-0 shadow-sm" required autocomplete="off">
                                <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 py-0 px-2" onclick="deleteFromDatalist('edit_part_number', 'partNumbersList', 'Part No')" title="Hapus dari Daftar"><i class="fas fa-times"></i></button>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 py-0 px-2" onclick="addToDatalist('edit_part_number', 'partNumbersList', 'Part No')" title="Tambah Baru"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Customer <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <select name="customer_name" id="edit_customer_name" class="form-control form-control-sm border-0 shadow-sm" required>
                                    <option value="">- Pilih -</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 py-0 px-2" onclick="deleteFromSelect('edit_customer_name', 'Customer')" title="Hapus dari Daftar"><i class="fas fa-times"></i></button>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 py-0 px-2" onclick="addToSelect('edit_customer_name', 'Customer')" title="Tambah Baru"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Standard Customer OEM / ELECTRONIC <span class="text-danger">*</span></label>
                            <input type="text" name="customer_standard" id="edit_customer_standard" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Kategori</label>
                            <input type="text" name="category" id="edit_category" class="form-control form-control-sm border-0 shadow-sm" placeholder="Contoh: R2/R4">
                        </div>
                    </div>
                    
                    <h6 class="font-weight-bold mt-3 mb-3 text-info border-bottom pb-2"><i class="fas fa-layer-group mr-1"></i> Thickness (<span style="text-transform: none !important;">m&micro;</span>)</h6>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Cr <span class="text-danger">*</span></label>
                            <input type="text" name="thickness_cr" id="edit_thickness_cr" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Ni <span class="text-danger">*</span></label>
                            <input type="text" name="thickness_ni" id="edit_thickness_ni" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Cu <span class="text-danger">*</span></label>
                            <input type="text" name="thickness_cu" id="edit_thickness_cu" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="thickness_freq" id="edit_thickness_freq" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-3 text-info border-bottom pb-2"><i class="fas fa-vial mr-1"></i> Corrodkote</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Waktu (Jam) <span class="text-danger">*</span></label>
                            <input type="text" name="corrodkote_time" id="edit_corrodkote_time" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Std Max % Corrosion <span class="text-danger">*</span></label>
                            <input type="text" name="corrodkote_std_max_corrosion" id="edit_corrodkote_std_max_corrosion" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="corrodkote_freq" id="edit_corrodkote_freq" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-3 text-info border-bottom pb-2"><i class="fas fa-flask mr-1"></i> Cass Test</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Waktu (Jam) <span class="text-danger">*</span></label>
                            <input type="text" name="cass_time" id="edit_cass_time" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Std. Min RN <span class="text-danger">*</span></label>
                            <input type="text" name="cass_std_min_rn" id="edit_cass_std_min_rn" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="cass_freq" id="edit_cass_freq" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-3 text-info border-bottom pb-2"><i class="fas fa-spray-can mr-1"></i> Salt Spray Test</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Waktu (Jam) <span class="text-danger">*</span></label>
                            <input type="text" name="salt_spray_time" id="edit_salt_spray_time" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Std. Rusting <span class="text-danger">*</span></label>
                            <input type="text" name="salt_spray_std_rusting" id="edit_salt_spray_std_rusting" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="salt_spray_freq" id="edit_salt_spray_freq" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-3 text-info border-bottom pb-2"><i class="fas fa-search-plus mr-1"></i> Porecount Test</h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-gray-700">Std. Min <span class="text-danger">*</span></label>
                            <input type="text" name="porecount_std_min" id="edit_porecount_std_min" class="form-control form-control-sm border-0 shadow-sm" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold text-gray-700">Frek. <span class="text-danger">*</span></label>
                            <input type="text" name="porecount_freq" id="edit_porecount_freq" class="form-control form-control-sm border-0 shadow-sm" required>
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

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-file-excel mr-2 text-success"></i> Import Data Excel
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('standard-performance-tests.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    <div class="alert alert-info border-0 shadow-sm small py-2 mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Pastikan format kolom sesuai dengan template Excel yang disediakan. Data dengan nama Part yang sudah ada akan otomatis diperbarui.
                        <div class="mt-2 text-center">
                            <a href="{{ route('standard-performance-tests.template') }}" class="btn btn-info btn-sm rounded-pill px-3 shadow-sm">
                                <i class="fas fa-download mr-1"></i> Download Template Excel
                            </a>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-gray-700">File Excel (.xlsx, .xls) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control-file border-0 p-1 shadow-sm rounded bg-white mt-1" accept=".xlsx,.xls" required>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-upload mr-1"></i> Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Thickness -->
<div class="modal fade" id="modalThickness" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog {{ !$isTrial ? 'modal-xl' : 'modal-lg' }}" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" style="font-size: 1.1rem;">
                    <i class="fas fa-layer-group mr-2 text-success"></i> {{ !$isTrial ? 'Input Thickness (Data 1 & Data 2)' : 'Input Thickness' }}
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('standard-performance-tests.thickness.store') }}" method="POST" id="formThickness" novalidate>
                @csrf
                <input type="hidden" name="standard_performance_test_id" id="thickness_test_id">
                @if($isTrial)
                <input type="hidden" name="is_trial" value="1">
                @endif
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 70vh; overflow-y: auto;">
                    
                    {{-- Common Batch Fields --}}
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
                        <div class="card-body p-3">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-gray-700">Nama Part</label>
                                <input type="text" id="thickness_part_name" class="form-control form-control-sm border-0 bg-light" readonly>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group mb-0">
                                    <label class="small font-weight-bold text-gray-700">Tgl Produksi <span class="text-danger">*</span></label>
                                    <input type="date" name="production_date" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                    <label class="small font-weight-bold text-gray-700">Shift <span class="text-danger">*</span></label>
                                    <select name="shift" class="form-control form-control-sm border-0 shadow-sm" required>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                    <label class="small font-weight-bold text-gray-700">No Lot <span class="text-danger">*</span></label>
                                    <input type="text" name="lot_no" class="form-control form-control-sm border-0 shadow-sm" placeholder="No Lot" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$isTrial)
                    {{-- Horizontal 2-Column Split: DATA 1 & DATA 2 --}}
                    <div class="row">
                        {{-- DATA 1 Column (Left) --}}
                        <div class="col-md-6">
                            <div class="card border shadow-sm mb-3" style="border-radius: 10px; border: 1px solid #e2e8f0;">
                                <div class="card-header bg-white text-dark py-2 px-3 font-weight-bold d-flex align-items-center justify-content-between" style="font-size:0.85rem; border-radius: 9px 9px 0 0; border-bottom: 1px solid #e2e8f0;">
                                    <div class="font-weight-bold">
                                        DATA 1 (AKTUAL)
                                    </div>
                                    <div class="badge badge-light border font-weight-bold shadow-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                                        STD: Cr <span id="thickness_std_cr_display">-</span> | Ni <span id="thickness_std_ni_display">-</span> | Cu <span id="thickness_std_cu_display">-</span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Cr</label>
                                            <input type="text" name="actual_cr" id="actual_cr_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Ni</label>
                                            <input type="text" name="actual_ni" id="actual_ni_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Cu</label>
                                            <input type="text" name="actual_cu" id="actual_cu_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-gray-700">Result / Judgment</label>
                                        <select name="result_judgment" id="result_judgment_select" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="-">-</option>
                                            <option value="OK">OK</option>
                                            <option value="NG">NG</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-gray-700">Description / Keterangan</label>
                                        <textarea name="description" class="form-control form-control-sm border-0 shadow-sm" rows="2" placeholder="Keterangan Data 1..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DATA 2 Column (Right) --}}
                        <div class="col-md-6">
                            <div class="card border shadow-sm mb-3" style="border-radius: 10px; border: 1px solid #e2e8f0;">
                                <div class="card-header bg-white text-dark py-2 px-3 font-weight-bold d-flex align-items-center justify-content-between" style="font-size:0.85rem; border-radius: 9px 9px 0 0; border-bottom: 1px solid #e2e8f0;">
                                    <div class="font-weight-bold">
                                        DATA 2
                                    </div>
                                    <div class="badge badge-light border text-info font-weight-bold shadow-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                                        STD: Cr <span id="thickness_std_cr_display_2">-</span> | Ni <span id="thickness_std_ni_display_2">-</span> | Cu <span id="thickness_std_cu_display_2">-</span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Cr</label>
                                            <input type="text" name="actual_cr_trial" id="actual_cr_trial_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Ni</label>
                                            <input type="text" name="actual_ni_trial" id="actual_ni_trial_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Cu</label>
                                            <input type="text" name="actual_cu_trial" id="actual_cu_trial_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-gray-700">Result / Judgment</label>
                                        <select name="result_judgment_trial" id="result_judgment_trial_select" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="-">-</option>
                                            <option value="OK">OK</option>
                                            <option value="NG">NG</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-gray-700">Description / Keterangan</label>
                                        <textarea name="description_trial" class="form-control form-control-sm border-0 shadow-sm" rows="2" placeholder="Keterangan Data 2..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    {{-- Single Column layout --}}
                    <div class="card border shadow-sm mb-3" style="border-radius: 10px; border: 1px solid #e2e8f0;">
                        <div class="card-header bg-white text-dark py-2 px-3 font-weight-bold d-flex align-items-center justify-content-between" style="font-size:0.85rem; border-radius: 9px 9px 0 0; border-bottom: 1px solid #e2e8f0;">
                            <div class="font-weight-bold">
                                HASIL PENGUJIAN
                            </div>
                            <div class="badge badge-light border text-info font-weight-bold shadow-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                                STD: Cr <span id="thickness_std_cr_display_single">-</span> | Ni <span id="thickness_std_ni_display_single">-</span> | Cu <span id="thickness_std_cu_display_single">-</span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="small font-weight-bold text-gray-700">Cr</label>
                                    <input type="text" name="actual_cr" id="actual_cr_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="small font-weight-bold text-gray-700">Ni</label>
                                    <input type="text" name="actual_ni" id="actual_ni_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="small font-weight-bold text-gray-700">Cu</label>
                                    <input type="text" name="actual_cu" id="actual_cu_input" class="form-control form-control-sm border-0 shadow-sm actual-thickness-input">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-gray-700">Result / Judgment</label>
                                <select name="result_judgment" id="result_judgment_select" class="form-control form-control-sm border-0 shadow-sm">
                                    <option value="-">-</option>
                                    <option value="OK">OK</option>
                                    <option value="NG">NG</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-gray-700">Description / Keterangan</label>
                                <textarea name="description" class="form-control form-control-sm border-0 shadow-sm" rows="2" placeholder="Keterangan..."></textarea>
                            </div>
                        </div>
                    </div>
                    @endif

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

<!-- Datalists for Autocomplete -->
<datalist id="partNamesList">
    @foreach($partNames as $pn)
        <option value="{{ $pn }}">{{ $pn }}</option>
    @endforeach
</datalist>
<datalist id="partNumbersList">
    @foreach($partNumbers as $pno)
        <option value="{{ $pno }}">{{ $pno }}</option>
    @endforeach
</datalist>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#dataTable').DataTable({
            deferRender: true,
            processing: true,
            initComplete: function(settings, json) {
                $('#tableLoader').hide();
                $('#tableContainer').fadeIn('fast', function() {
                    table.columns.adjust();
                });
            }
        });

        // DataTables draw event for Auto-Highlight
        table.on('draw', function() {
            var tbody = table.table().body();
            
            // Unmark previous
            $(tbody).find('mark.hlt').each(function() {
                $(this).replaceWith(this.childNodes);
            });
            tbody.normalize();

            var searchStr = table.search();
            if (!searchStr) return;

            var keywords = searchStr.split(' ').filter(w => w.trim().length > 1);
            if (keywords.length === 0) return;

            keywords = keywords.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).sort((a, b) => b.length - a.length);
            var regex = new RegExp("(" + keywords.join('|') + ")", "gi");

            table.rows({ page: 'current' }).nodes().each(function(row) {
                $(row).find('td:not(:last-child)').each(function() {
                    var walker = document.createTreeWalker(this, NodeFilter.SHOW_TEXT, null, false);
                    var nodes = [];
                    while (walker.nextNode()) {
                        nodes.push(walker.currentNode);
                    }
                    nodes.forEach(function(node) {
                        var text = node.nodeValue;
                        if (text.trim() && regex.test(text)) {
                            var span = document.createElement('span');
                            span.innerHTML = text.replace(regex, "<mark class='hlt' style='background-color: #fffa90; color: #000000; padding: 0 2px; border-radius: 2px;'>$1</mark>");
                            var frag = document.createDocumentFragment();
                            while (span.firstChild) {
                                frag.appendChild(span.firstChild);
                            }
                            node.parentNode.replaceChild(frag, node);
                        }
                    });
                });
            });
        });

        // Instant smart search on keyup
        $('#search_master').on('keypress', function (e) {
            if (e.which == 13) e.preventDefault();
        });

        $('#search_master').on('keyup input', function () {
            table.search($(this).val()).draw();
        });

        // Search Button Fallback
        $('#btnFilterSearchMaster').on('click', function () {
            table.search($('#search_master').val()).draw();
        });

        // Client-side DataTables Reset
        $('#btnResetFilterMaster').on('click', function () {
            $('#search_master').val('');
            table.search('').draw();
        });
        // Thickness Modal
        var stdCr = 0, stdNi = 0, stdCu = 0;
        $('#dataTable').on('click', '.btn-thickness', function() {
            var item = $(this).data('item');
            var rawCr = (item && item.thickness_cr !== null && item.thickness_cr !== undefined && item.thickness_cr !== '') ? item.thickness_cr : ($(this).attr('data-cr') || $(this).data('cr') || '-');
            var rawNi = (item && item.thickness_ni !== null && item.thickness_ni !== undefined && item.thickness_ni !== '') ? item.thickness_ni : ($(this).attr('data-ni') || $(this).data('ni') || '-');
            var rawCu = (item && item.thickness_cu !== null && item.thickness_cu !== undefined && item.thickness_cu !== '') ? item.thickness_cu : ($(this).attr('data-cu') || $(this).data('cu') || '-');

            var name = (item && item.part_name) ? item.part_name : ($(this).data('name') || '');
            var id = (item && item.id) ? item.id : $(this).data('id');

            $('#thickness_test_id').val(id);
            $('#thickness_part_name').val(name);

            // Display standard values in badges
            $('#thickness_std_cr_display, #thickness_std_cr_display_2, #thickness_std_cr_display_single').text(rawCr);
            $('#thickness_std_ni_display, #thickness_std_ni_display_2, #thickness_std_ni_display_single').text(rawNi);
            $('#thickness_std_cu_display, #thickness_std_cu_display_2, #thickness_std_cu_display_single').text(rawCu);

            stdCr = parseFloat(rawCr) || 0;
            stdNi = parseFloat(rawNi) || 0;
            stdCu = parseFloat(rawCu) || 0;

            $('.actual-thickness-input').val('');
            $('#result_judgment_select').val('-');
            $('#result_judgment_trial_select').val('-');
            $('#modalThickness').modal('show');
        });

        // Auto judgment logic Data 1 & Data 2
        $('.actual-thickness-input').on('keyup change', function() {
            // Data 1
            var actCr = parseFloat($('#actual_cr_input').val());
            var actNi = parseFloat($('#actual_ni_input').val());
            var actCu = parseFloat($('#actual_cu_input').val());
            
            if (!isNaN(actCr) && !isNaN(actNi) && !isNaN(actCu)) {
                if (actCr >= stdCr && actNi >= stdNi && actCu >= stdCu) {
                    $('#result_judgment_select').val('OK');
                } else {
                    $('#result_judgment_select').val('NG');
                }
            } else {
                $('#result_judgment_select').val('-');
            }

            // Data 2
            var actCrTrial = parseFloat($('#actual_cr_trial_input').val());
            var actNiTrial = parseFloat($('#actual_ni_trial_input').val());
            var actCuTrial = parseFloat($('#actual_cu_trial_input').val());

            if (!isNaN(actCrTrial) && !isNaN(actNiTrial) && !isNaN(actCuTrial)) {
                if (actCrTrial >= stdCr && actNiTrial >= stdNi && actCuTrial >= stdCu) {
                    $('#result_judgment_trial_select').val('OK');
                } else {
                    $('#result_judgment_trial_select').val('NG');
                }
            } else {
                $('#result_judgment_trial_select').val('-');
            }
        });

        $('#dataTable').on('click', '.btn-edit', function() {
            var item = $(this).data('item');
            var url = '{{ route("standard-performance-tests.update", ":id") }}';
            url = url.replace(':id', item.id);
            $('#formEdit').attr('action', url);
            
            $('#edit_part_name').val(item.part_name);
            $('#edit_part_number').val(item.part_number);
            
            var selCust = document.getElementById('edit_customer_name');
            if (selCust && item.customer_name && !Array.from(selCust.options).some(o => o.value == item.customer_name)) {
                selCust.appendChild(new Option(item.customer_name, item.customer_name));
            }
            $('#edit_customer_name').val(item.customer_name);
            
            $('#edit_customer_standard').val(item.customer_standard);
            $('#edit_category').val(item.category || '');
            
            $('#edit_thickness_cr').val(item.thickness_cr);
            $('#edit_thickness_ni').val(item.thickness_ni);
            $('#edit_thickness_cu').val(item.thickness_cu);
            $('#edit_thickness_freq').val(item.thickness_freq);
            
            $('#edit_corrodkote_time').val(item.corrodkote_time);
            $('#edit_corrodkote_std_max_corrosion').val(item.corrodkote_std_max_corrosion);
            $('#edit_corrodkote_freq').val(item.corrodkote_freq);
            
            $('#edit_cass_time').val(item.cass_time);
            $('#edit_cass_std_min_rn').val(item.cass_std_min_rn);
            $('#edit_cass_freq').val(item.cass_freq);
            
            $('#edit_salt_spray_time').val(item.salt_spray_time);
            $('#edit_salt_spray_std_rusting').val(item.salt_spray_std_rusting);
            $('#edit_salt_spray_freq').val(item.salt_spray_freq);
            
            $('#edit_porecount_std_min').val(item.porecount_std_min);
            $('#edit_porecount_freq').val(item.porecount_freq);

            $('#modalEdit').modal('show');
        });
        
        // Add form validation with SweetAlert
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
        
        
        validateForm('formTambahMaster');
        validateForm('formEdit');
        validateForm('formThickness');
    });
    
    // Global Functions for Datalist & Select manipulations
    window.addToDatalist = function(inputId, listId, title) {
        Swal.fire({
            title: 'Tambah ' + title + ' Baru',
            input: 'text',
            inputAttributes: { autocapitalize: 'off', placeholder: 'Masukkan ' + title.toLowerCase() + ' baru...' },
            showCancelButton: true,
            confirmButtonText: 'Tambah',
            cancelButtonText: 'Batal',
            preConfirm: (val) => {
                if (!val || !val.trim()) { Swal.showValidationMessage(title + ' tidak boleh kosong!'); return false; }
                return val.trim();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var newVal = result.value;
                var datalist = document.getElementById(listId);
                if (datalist && !Array.from(datalist.options).some(o => o.value === newVal)) {
                    datalist.appendChild(new Option(newVal, newVal));
                }
                var inp = document.getElementById(inputId);
                if (inp) inp.value = newVal;
                Swal.fire({ icon: 'success', title: 'Berhasil', text: title + ' baru ditambahkan ke daftar saran.', timer: 1500, showConfirmButton: false });
            }
        });
    };

    window.deleteFromDatalist = function(inputId, listId, title) {
        var inp = document.getElementById(inputId);
        var val = inp ? inp.value.trim() : '';
        if (!val) { Swal.fire('Peringatan', 'Isi ' + title + ' yang ingin dihapus dari daftar terlebih dahulu.', 'warning'); return; }
        Swal.fire({
            title: 'Hapus dari Daftar?',
            text: '"' + val + '" akan dihapus dari opsi saran dropdown.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                var datalist = document.getElementById(listId);
                if (datalist) { Array.from(datalist.options).filter(o => o.value === val).forEach(o => o.remove()); }
                if (inp && inp.value === val) inp.value = '';
                Swal.fire({ icon: 'success', title: 'Berhasil', text: title + ' dihapus dari daftar saran.', timer: 1500, showConfirmButton: false });
            }
        });
    };

    window.addToSelect = function(selectId, title) {
        Swal.fire({
            title: 'Tambah ' + title + ' Baru',
            input: 'text',
            inputAttributes: { autocapitalize: 'off', placeholder: 'Masukkan ' + title.toLowerCase() + ' baru...' },
            showCancelButton: true,
            confirmButtonText: 'Tambah',
            cancelButtonText: 'Batal',
            preConfirm: (val) => {
                if (!val || !val.trim()) { Swal.showValidationMessage(title + ' tidak boleh kosong!'); return false; }
                return val.trim();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var newVal = result.value;
                // Add to both Add and Edit selects
                ['tambah_customer_name', 'edit_customer_name'].forEach(function(id) {
                    var s = document.getElementById(id);
                    if (s && !Array.from(s.options).some(o => o.value === newVal)) {
                        s.appendChild(new Option(newVal, newVal));
                    }
                });
                var activeSelect = document.getElementById(selectId);
                if (activeSelect) activeSelect.value = newVal;
                Swal.fire({ icon: 'success', title: 'Berhasil', text: title + ' baru ditambahkan.', timer: 1500, showConfirmButton: false });
            }
        });
    };

    window.deleteFromSelect = function(selectId, title) {
        var activeSelect = document.getElementById(selectId);
        var val = activeSelect ? activeSelect.value : '';
        if (!val) { Swal.fire('Peringatan', 'Pilih ' + title + ' yang ingin dihapus dari daftar terlebih dahulu.', 'warning'); return; }
        Swal.fire({
            title: 'Hapus dari Daftar?',
            text: '"' + val + '" akan dihapus dari opsi dropdown.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                ['tambah_customer_name', 'edit_customer_name'].forEach(function(id) {
                    var s = document.getElementById(id);
                    if (s) {
                        Array.from(s.options).filter(o => o.value === val).forEach(o => o.remove());
                        if (s.value === val) s.value = '';
                    }
                });
                Swal.fire({ icon: 'success', title: 'Berhasil', text: title + ' dihapus dari daftar.', timer: 1500, showConfirmButton: false });
            }
        });
    };
</script>
@endpush
