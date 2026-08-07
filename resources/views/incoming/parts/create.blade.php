@extends('layouts.admin')

@section('title', 'Input Data Incoming Part')

@push('styles')
<style>
    #checksheetTable th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f8f9fc; vertical-align: middle; }
    #checksheetTable td { font-size: 0.85rem; vertical-align: middle; }
    .form-control-sm.text-center { font-weight: bold; border-color: #d1d3e2; }
    .form-control-sm.text-center:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
    #judgmentBadge { min-width: 80px; min-height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    
    /* Style Daftar Antrian Scan (Queue Table) presisi 100% selaras dengan #checksheetTable */
    #tempQueueCard {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }

    /* Target tableOpenArrivals headers to override global admin blue thead th style */
    #tableOpenArrivals,
    #tableOpenArrivals > thead > tr > th,
    #tableOpenArrivals > thead > tr > td,
    #tableOpenArrivals th,
    #tableOpenArrivals tr:first-child th,
    #modalAddArrival table thead th,
    #modalAddArrival table tr:first-child th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.65rem !important;
        letter-spacing: 0.3px !important;
        border-bottom: 2px solid #cbd5e1 !important;
        border-right: 1px solid #e2e8f0 !important;
        border-left: none !important;
        border-top: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
    }
    #tempQueueCard .card-header {
        background-color: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    #tempQueueTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: 1px solid #e2e8f0 !important;
        width: 100% !important;
    }
    #tempQueueTable > thead > tr > th,
    #tempQueueTable th {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 105 !important;
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px !important;
        padding: 8px 12px !important;
        border: 1px solid #e2e8f0 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        vertical-align: middle !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        text-align: center !important;
    }
    #tempQueueTable > tbody > tr > td,
    #tempQueueTable td {
        border: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.8rem !important;
        padding: 8px 10px !important;
    }
    #tempQueueTable tfoot td {
        background-color: #f8fafc !important;
        border-top: 2px solid #cbd5e1 !important;
        font-size: 0.8rem !important;
    }
</style>
@endpush

@section('content')
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_parts', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0210',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '0',
            'halaman' => '- / -'
        ]);
    @endphp
    
    <!-- Header Document -->
    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                            CHECK SHEET INCOMING PART
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
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Part</h6>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm px-3 py-2" style="font-size: 0.8rem; letter-spacing: 0.5px; border-radius: 6px;" data-toggle="modal" data-target="#modalAddArrival">
                    <i class="fas fa-boxes mr-1"></i> INPUT STOK KEDATANGAN AWAL
                </button>
                <span class="badge badge-primary px-3 py-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                    <i class="fas fa-industry mr-1"></i> PLANT: {{ strtoupper($plantCode) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.parts.store') }}" method="POST" id="checksheetForm" novalidate>
                @csrf
                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <input type="hidden" name="arrival_id" id="arrivalIdInput" value="">
                <input type="hidden" id="initialBalanceInput" value="0">
                <input type="hidden" name="qrcode" id="qrcodeInput">
                <input type="hidden" name="part_code" id="partCodeInput">
                <input type="hidden" name="supplier_id" id="supplierIdInput">
                <input type="hidden" name="quantity" id="quantityInput">
                <input type="hidden" name="unique_code_id" id="uniqueCodeInput">
                <input type="hidden" name="sap_code" id="sapCodeInputHidden">
                <input type="hidden" name="scan_method" id="scanMethodInput" value="manual">

                <!-- Main Checksheet Table (Combined Tgl & Shift Kedatangan) -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <thead class="bg-light text-center small font-weight-bold">
                            <tr>
                                <th style="min-width: 220px;">Item Part</th>
                                <th style="width: 160px;">Tgl &amp; Shift Kedatangan Supplier</th>
                                <th style="width: 120px;">Qty Balance</th>
                                <th style="width: 160px;">Tanggal &amp; Shift Check</th>
                                <th style="width: 110px;">Total Check</th>
                                <th style="width: 110px;">Qty Sampling</th>
                                <th style="min-width: 230px;">Detail NG</th>
                                <th style="width: 110px;">Judgment</th>
                                <th style="width: 90px;">QC Initials</th>
                                <th style="min-width: 150px;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- 1. Item Part -->
                                <td>
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small mb-1">
                                            Scan Verifikasi Quality
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="sapCodeInput"
                                                placeholder="Tap kolom ini, lalu scan QR" autocomplete="off" spellcheck="false">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btnScanQR" title="Buka QR Scanner">
                                                    <i class="fas fa-qrcode"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Scan untuk pilih item otomatis</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <select class="form-control select2" name="item_id" id="itemSelect" required style="min-width: 200px;">
                                            <option value="" disabled selected style="font-weight:bold; color:#6c757d;">-- Pilih Item Part --</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-part-number="{{ $item->part_number ?? '' }}"
                                                    data-sap_code="{{ $item->sap_code ?? '' }}"
                                                    data-name="{{ $item->name }}"
                                                    data-defects="{{ json_encode($item->defects) }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-standard="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-similar="{{ $item->similar_part_file_path ? route('items.pdf', ['id' => $item->id, 'index' => 'similar']) : '' }}"
                                                    data-description="{{ $item->description ?? '' }}"
                                                    data-customer="{{ $item->customer ?? '' }}"
                                                    data-weight-standard="{{ $item->weight_standard ?? '' }}"
                                                    data-dimension-standards="{{ json_encode($item->dimension_standards) }}">
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                    {{ $item->sap_code ? '- SAP: '.$item->sap_code : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- 2. Tgl & Shift Kedatangan Supplier (Combined Column) -->
                                <td>
                                    <input type="date" class="form-control mb-2" name="tanggal_datang" id="tanggalDatangInput" value="{{ $defaultDate }}" required>
                                    <select class="form-control" name="shift_datang" id="shiftDatangSelect" required>
                                        <option value="1" selected>Shift 1</option>
                                        <option value="2">Shift 2</option>
                                        <option value="3">Shift 3</option>
                                    </select>
                                    <small class="text-muted d-block text-center mt-1" id="arrivalStatusHint">Auto / Wajib</small>
                                </td>

                                <!-- 3. Qty Balance -->
                                <td>
                                    <input type="number" class="form-control text-center" 
                                        name="qty_datang" id="qtyBalanceInput" placeholder="0" min="0" required>
                                    <small class="text-muted d-block text-center mt-1" id="balanceHint">Sisa balance</small>
                                </td>

                                <!-- 4. Tanggal & Shift Check -->
                                <td>
                                    <input type="date" class="form-control mb-2" name="date" value="{{ $defaultDate }}" required>
                                    <select class="form-control" name="shift" required>
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </td>

                                <!-- 5. Total Check -->
                                <td>
                                    <input type="number" class="form-control text-center" 
                                        name="total_check" id="totalCheckInput" placeholder="0" min="1" required style="min-width: 90px;">
                                    <small class="text-muted d-block text-center mt-1" id="maxCheckHint"></small>
                                </td>

                                <!-- 6. Qty Sampling (AQL Auto) -->
                                <td>
                                    <input type="number" class="form-control text-center" 
                                        name="sampling_qty" id="qtySamplingInput" placeholder="0" min="0" style="min-width: 90px;">
                                    <small class="text-muted d-block text-center mt-1">Auto AQL</small>
                                </td>

                                <!-- 7. Detail NG -->
                                <td>
                                    <label class="font-weight-bold text-dark d-block mb-1 small">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="row no-gutters mb-2 defect-row align-items-center">
                                            <div class="col-7 pr-1">
                                                <select class="form-control defect-select font-weight-bold" name="defect_types[]" id="defectSelect">
                                                    <option value="">-- Pilih Defect --</option>
                                                </select>
                                            </div>
                                            <div class="col-3 pr-1">
                                                <input type="number" class="form-control defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1">
                                            </div>
                                            <div class="col-2 text-center action-col">
                                                <button type="button" id="addDefectBtn" class="btn btn-primary btn-sm shadow-sm" style="display: none;" title="Tambah Jenis">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- 7. Judgment -->
                                <td>
                                    <div id="judgmentBadge" class="mb-2 p-2 font-weight-bold h5 rounded d-none shadow-sm text-center" style="border: 2px solid transparent;">-</div>
                                    <select class="form-control font-weight-bold d-none" name="judgment" id="judgmentSelect" required>
                                        <option value="OK" selected>OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                    <input type="hidden" name="total_ng" id="totalNgInput" value="0">
                                    <div id="aql_info" class="small mt-1 font-weight-bold text-center" style="display:none;">
                                        <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                                        <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                                    </div>
                                </td>

                                <!-- 8. QC Initials -->
                                <td>
                                    <input type="text" class="form-control text-center" name="operator_initials" value="{{ auth()->user()->initials ?? '' }}" required style="min-width: 60px;">
                                </td>

                                <!-- 9. Remarks -->
                                <td>
                                    <textarea class="form-control" name="remarks" rows="2" placeholder="..."></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-right d-flex justify-content-end align-items-center">
                        <h5 class="mr-3 mb-0 font-weight-bold text-gray-800" id="timerDisplay">00:00:00</h5>
                        <input type="hidden" name="cycle_time" id="cycleTimeInput" value="0">

                        <button type="button" class="btn btn-success mr-3" id="startTimerBtn">
                            <i class="fas fa-play"></i> Start
                        </button>
                        <button type="submit" class="btn btn-primary px-5" id="saveBtn" disabled>
                            <i class="fas fa-save mr-1"></i> SIMPAN DATA
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card Daftar Scan Sementara (Queue List) -->
    <div class="card shadow mb-4 d-none" id="tempQueueCard">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
            <h6 class="m-0 font-weight-bold text-gray-800">
                Daftar Antrian Scan Incoming Part
            </h6>
            <span class="badge badge-secondary px-3 py-2 font-weight-bold" id="queueBadge" style="font-size: 0.8rem; background-color: #eaecf4; color: #5a5c69;">0 Data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center mb-0" id="tempQueueTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-center small font-weight-bold">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Item Part</th>
                            <th>QR Raw</th>
                            <th style="width: 140px;">Tanggal &amp; Shift Check</th>
                            <th style="width: 100px;">Total Check</th>
                            <th style="width: 90px;">Judgment</th>
                            <th style="width: 85px;">Inisial QC</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tempQueueBody">
                        <!-- Dinamik via JS -->
                    </tbody>
                    <tfoot class="bg-light font-weight-bold" style="font-size: 0.85rem;">
                        <tr>
                            <td colspan="4" class="text-right font-weight-bold text-uppercase">Total Qty Check:</td>
                            <td id="totalQtyCheckDisplay" class="text-center font-weight-bold text-primary" style="font-size: 0.95rem;">0</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                <div id="saveProgressContainer" class="w-100 w-md-50 mb-3 mb-md-0 d-none">
                    <div class="progress" style="height: 18px; border-radius: 9px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="saveProgressBar" role="progressbar" style="width: 0%; font-size: 0.75rem; font-weight: 700;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <small class="text-muted mt-1 d-block font-weight-bold" id="saveProgressText">Menyimpan data...</small>
                </div>
                <div class="text-right ml-auto">
                    <button type="button" class="btn btn-danger btn-sm mr-2 shadow-sm" id="btnClearQueue">
                        <i class="fas fa-trash-alt mr-1"></i> Kosongkan List
                    </button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnSaveQueue">
                        <i class="fas fa-cloud-upload-alt mr-1"></i> Simpan Semua Data List (<span id="queueCountDisplay">0</span> Data)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Tampilan PDF Berdampingan -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">STANDARD</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Kolom Kiri: PCCP / Standard -->
                <div class="col-md-6 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">PCCP DAN SIMILAR PART</h6>
                        <div class="d-flex align-items-center">
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutStandard" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetStandard" title="Reset Zoom"><i class="fas fa-sync-alt"></i></button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInStandard" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                            </div>
                            <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                                <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardPage" title="Previous Page"><i class="fas fa-chevron-left"></i></button>
                                <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardPage" title="Next Page"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn mr-1" id="fullStandardBtn" style="display:none;"><i class="fas fa-expand"></i> Full</button>
                            <a id="downloadStandardBtn" class="btn btn-sm btn-success" href="#" download title="Download Standard PDF" style="display:none;"><i class="fas fa-download"></i></a>
                        </div>
                    </div>
                    <div id="standardPdfContainer" class="rounded border" style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="standardPdfPlaceholder" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-pdf fa-3x mb-3"></i>
                            <p class="mb-0">Pilih Item untuk menampilkan Standard PDF</p>
                        </div>
                        <canvas id="standardPdfCanvas" class="d-none" style="margin: 0 auto;"></canvas>
                        <div id="standardPdfLoading" class="h-100 d-none align-items-center justify-content-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <!-- Kolom Kanan: Similar Part / Dimensi -->
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">DIMENSI</h6>
                        <div class="d-flex align-items-center">
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutSimilar" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetSimilar" title="Reset Zoom"><i class="fas fa-sync-alt"></i></button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInSimilar" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                            </div>
                            <div class="d-flex align-items-center similar-nav-controls" style="display:none;">
                                <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevSimilarPage" title="Previous Page"><i class="fas fa-chevron-left"></i></button>
                                <span id="similarPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextSimilarPage" title="Next Page"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-info view-pdf-btn mr-1" id="fullSimilarBtn" style="display:none;"><i class="fas fa-expand"></i> Full</button>
                            <a id="downloadSimilarBtn" class="btn btn-sm btn-info" href="#" download title="Download Dimensi Part PDF" style="display:none;"><i class="fas fa-download"></i></a>
                        </div>
                    </div>
                    <div id="similarPdfContainer" class="rounded border" style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="similarPdfPlaceholder" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <p class="mb-0">Pilih Item untuk menampilkan Dimensi Part</p>
                            <p class="small mt-2" id="similarStatusText"></p>
                        </div>
                        <canvas id="similarPdfCanvas" class="d-none" style="margin: 0 auto;"></canvas>
                        <div id="similarPdfLoading" class="h-100 d-none align-items-center justify-content-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pemindai QR -->
    <div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrScannerModalLabel"><i class="fas fa-qrcode mr-2"></i>QR Code Scanner</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="position-relative">
                        <video id="qr-video" class="w-100" autoplay muted playsinline style="border-radius: 8px;"></video>
                        <button type="button" id="toggleFlashBtn" class="btn btn-sm btn-dark position-absolute d-none" style="top: 10px; left: 10px; opacity: 0.7; z-index: 10;">
                            <i class="fas fa-bolt text-white"></i> Flash
                        </button>
                        <button type="button" id="toggleMirrorBtn" class="btn btn-sm btn-dark position-absolute" style="top: 10px; right: 10px; opacity: 0.7; z-index: 10;">
                            <i class="fas fa-arrows-alt-h text-white"></i> Flip
                        </button>
                    </div>
                    <style>
                        #qr-video.mirrored { transform: scaleX(-1) !important; }
                        #zoomContainer { background: rgba(0,0,0,0.5); border-radius: 0 0 8px 8px; }
                        #zoomSlider { height: 6px; cursor: pointer; }
                    </style>
                    <div id="zoomContainer" class="p-2 d-none">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-search-minus text-white mr-2"></i>
                            <input type="range" id="zoomSlider" class="custom-range flex-grow-1" min="1" max="1" step="0.1" value="1">
                            <i class="fas fa-search-plus text-white ml-2"></i>
                        </div>
                    </div>
                    <div id="qr-reader-results" class="p-3 text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Memuat...</span>
                        </div>
                        <p class="mt-2 text-muted">Memproses data QR...</p>
                    </div>
                    <div class="p-3 border-top bg-light">
                        <label class="font-weight-bold">Atau Unggah Gambar QR:</label>
                        <input type="file" id="qr-input-file" accept="image/*" class="form-control-file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Input & List Stok Kedatangan Awal -->
    <div class="modal fade" id="modalAddArrival" tabindex="-1" role="dialog" aria-labelledby="modalAddArrivalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background-color: #ffffff; overflow: hidden;">
                <div class="modal-header bg-white py-3 px-4" style="border-bottom: 2px solid #f1f5f9;">
                    <h5 class="modal-title font-weight-bold text-gray-800 mb-0" id="modalAddArrivalTitle">
                        Kelola Stok &amp; Input Kedatangan Awal
                    </h5>
                    <button type="button" class="close text-secondary opacity-100" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-left" style="background-color: #ffffff;">
                    <!-- Form Input Kedatangan Baru -->
                    <div class="card border-0 mb-4 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 10px;">
                        <div class="card-header bg-light py-2 border-bottom" style="border-bottom: 1px solid #e2e8f0 !important;">
                            <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem;">
                                Form Input Kedatangan Part Baru
                            </h6>
                        </div>
                        <div class="card-body p-3 bg-white">
                            <form action="{{ route('incoming.parts.store_arrival') }}" method="POST" id="formAddArrival">
                                @csrf
                                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-5 mb-2">
                                        <label class="font-weight-bold text-gray-700 small mb-1">Item Part <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm select2" name="item_id" required id="arrivalItemSelect" style="width:100%;">
                                            <option value="" disabled selected>-- Pilih Item Part --</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3 mb-2">
                                        <label class="font-weight-bold text-gray-700 small mb-1">Tanggal Datang <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" name="tanggal_datang" value="{{ $defaultDate ?? date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group col-md-2 mb-2">
                                        <label class="font-weight-bold text-gray-700 small mb-1">Shift <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" name="shift_datang" required>
                                            <option value="1" selected>Shift 1</option>
                                            <option value="2">Shift 2</option>
                                            <option value="3">Shift 3</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2 mb-2">
                                        <label class="font-weight-bold text-gray-700 small mb-1">Qty Datang <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm font-weight-bold" name="qty_datang" min="1" placeholder="Pcs" required>
                                    </div>
                                </div>
                                <div class="text-right mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-4 shadow-sm" id="btnSubmitArrival">
                                        <i class="fas fa-save mr-1"></i> Simpan Stok Kedatangan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table Daftar Stok Kedatangan (Open Arrivals) -->
                    <div class="card border-0 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 10px;">
                        <div class="card-header bg-light py-2 border-bottom d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #e2e8f0 !important;">
                            <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.85rem;">
                                Daftar Tanggal &amp; Shift Kedatangan (Stok Open)
                            </h6>
                            <span class="badge badge-info px-2 py-1 font-weight-bold" id="openArrivalCountBadge">
                                {{ count($openArrivals ?? []) }} Lot Open
                            </span>
                        </div>
                        <div class="card-body p-0 bg-white">
                            <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                                <table class="table table-hover table-sm text-center mb-0" id="tableOpenArrivals" style="font-size: 0.78rem;">
                                    <thead style="background-color: #f8fafc !important; color: #475569 !important; position: sticky; top: 0; z-index: 10; border-bottom: 2px solid #cbd5e1 !important;">
                                        <tr>
                                            <th class="py-2 text-center" style="width: 45px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">No</th>
                                            <th class="py-2 text-left" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Nama Part / Part No</th>
                                            <th class="py-2 text-center" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Tgl &amp; Shift Datang</th>
                                            <th class="py-2 text-center" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Qty Datang</th>
                                            <th class="py-2 text-center" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Qty Sisa Stok</th>
                                            <th class="py-2 text-center" style="font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; border-right: 1px solid #e2e8f0; background-color: #f8fafc !important; color: #475569 !important;">Status</th>
                                            <th class="py-2 text-center text-nowrap" style="width: 90px; min-width: 90px; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.3px; background-color: #f8fafc !important; color: #475569 !important;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody style="color: #334155;">
                                        @forelse($openArrivals ?? [] as $arr)
                                            <tr style="border-bottom: 1px solid #f1f5f9;" id="arrivalRow_{{ $arr->id }}">
                                                <td class="align-middle" style="border-right: 1px solid #f1f5f9;">{{ $loop->iteration }}</td>
                                                <td class="align-middle text-left font-weight-bold" style="border-right: 1px solid #f1f5f9;">
                                                    {{ $arr->item->name ?? '-' }}
                                                    <br><small class="text-muted">{{ $arr->item->part_number ?? '-' }}</small>
                                                </td>
                                                <td class="align-middle" style="border-right: 1px solid #f1f5f9;">
                                                    <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($arr->tanggal_datang)->format('d/m/Y') }}</span>
                                                    <br><small class="text-muted">Shift {{ $arr->shift_datang }}</small>
                                                </td>
                                                <td class="align-middle" style="border-right: 1px solid #f1f5f9;">{{ number_format($arr->qty_datang) }} pcs</td>
                                                <td class="align-middle font-weight-bold text-dark" style="border-right: 1px solid #f1f5f9;">{{ number_format($arr->qty_sisa) }} pcs</td>
                                                <td class="align-middle" style="border-right: 1px solid #f1f5f9;">
                                                    <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">OPEN</span>
                                                </td>
                                                <td class="align-middle text-center text-nowrap" style="white-space: nowrap;">
                                                    <div class="d-inline-flex align-items-center justify-content-center" style="gap: 4px;">
                                                        <button type="button" class="btn btn-xs btn-outline-warning btn-edit-arrival" 
                                                            data-id="{{ $arr->id }}" 
                                                            data-item-name="{{ e($arr->item->name ?? '-') }}" 
                                                            data-tgl="{{ \Carbon\Carbon::parse($arr->tanggal_datang)->format('Y-m-d') }}"
                                                            data-shift="{{ $arr->shift_datang }}"
                                                            data-qty-datang="{{ $arr->qty_datang }}" 
                                                            data-qty-sisa="{{ $arr->qty_sisa }}" 
                                                            title="Edit Stok">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-xs btn-outline-danger btn-delete-arrival" 
                                                            data-id="{{ $arr->id }}" 
                                                            data-item-name="{{ e($arr->item->name ?? '-') }}" 
                                                            title="Hapus Stok">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="emptyArrivalRow">
                                                <td colspan="7" class="text-center text-muted py-3">Belum ada stok kedatangan part yang OPEN.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2 px-4" style="border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-4" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.INCOMING_PART_CONFIG = {
            arrivalsUrl: "{{ route('incoming.parts.arrivals') }}",
            updateArrivalBaseUrl: "{{ url('/checksheet/incoming-part/arrival') }}",
            checkFirstTimeUrl: "{{ route('incoming.parts.check_first_time') }}",
            qrUniqueUrl: "{{ route('items.check-qr-unique') }}",
            itemSearchUrl: "{{ route('items.search-by-part') }}",
            index_url: "{{ route('incoming.parts.index', ['plant' => request('plant') ?? auth()->user()->plant_id]) }}",
            useQueue: true
        };
        window.pdfWorkerSrc = "{{ asset('js/vendor/pdf.worker.min.js') }}";
        window.pdfUrlPattern = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}";
        window.csrfToken = "{{ csrf_token() }}";
    </script>
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/incoming-create.js') }}?v={{ filemtime(public_path('js/checksheet/incoming-create.js')) }}"></script>
@endpush
