@extends('layouts.admin')

@section('title', 'Input Data Painting Checksheet')

@section('content')

    @push('styles')
    <style>
        #checksheetTable th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f8f9fc; }
        #checksheetTable td { font-size: 0.85rem; }
        .ok-label { background-color: #28a745; color: white; padding: 4px 8px; font-weight: bold; font-size: 0.7rem; border-radius: 4px 0 0 4px; min-width: 35px; text-align: center; display: inline-block; }
        .ng-label { background-color: #dc3545; color: white; padding: 4px 8px; font-weight: bold; font-size: 0.7rem; border-radius: 4px 0 0 4px; min-width: 35px; text-align: center; display: inline-block; }
        .form-control-sm.text-center { font-weight: bold; border-color: #d1d3e2; }
        .form-control-sm.text-center:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
        #judgmentBadge { min-width: 80px; min-height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
    @endpush

    @php
        $plant = $plant ?? request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');
    @endphp



    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="font-weight-bold">Terjadi Kesalahan!</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">INPUT DATA PAINTING</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('painting.store') }}" method="POST" id="checksheetForm" class="ajax-form" novalidate>
                @csrf
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="qrcode" id="qrcodeInput">
                <input type="hidden" name="part_code" id="partCodeInput">
                <input type="hidden" name="supplier_id" id="supplierIdInput">
                <input type="hidden" name="quantity" id="quantityInput">
                <input type="hidden" name="unique_code_id" id="uniqueCodeInput">
                <input type="hidden" name="sap_code" id="sapCodeInputHidden">
                <input type="hidden" name="is_scanned" id="isScannedInput" value="0">

                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2" style="vertical-align: middle;">Item Part</th>
                                <th rowspan="2" style="vertical-align: middle;" id="thLotId">Lot ID<br>(Tgl / Shift / Inisial)</th>
                                <th rowspan="2" style="vertical-align: middle;" id="thPainting">Painting<br>(Tgl / Shift / Lot)</th>
                                <th colspan="2" style="vertical-align: middle;">Quality</th>
                                <th rowspan="2" style="vertical-align: middle;">Total Qty (Lot)</th>
                                <th rowspan="2" style="vertical-align: middle; min-width: 150px;">Jenis (OK/NG) &amp; Detail NG
                                </th>
                                <th rowspan="2" style="vertical-align: middle;">Total (OK/NG)</th>
                                <th rowspan="2" class="judgment-column" style="vertical-align: middle;">Judgment</th>
                                <th rowspan="2" style="vertical-align: middle;">Inisial QC</th>
                                <th rowspan="2" style="vertical-align: middle;">DESCRIPTION</th>
                            </tr>
                            <tr class="text-center">
                                <th class="small py-1" style="vertical-align: middle;">Tanggal / Shift</th>
                                <th class="small py-1" style="vertical-align: middle;">Meja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Pilihan Barang -->
                                <td class="align-middle" style="min-width: 450px;">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small mb-1">
                                            Scan QR Code (Autofill)
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="sapCodeInput"
                                                placeholder="Silahkan Scan" autocomplete="off" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btnScanQR"
                                                    title="Buka QR Scanner">
                                                    <i class="fas fa-qrcode mr-1"></i> Scan QR Code
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold small">Item Part</label>
                                        <select class="form-control form-control-sm" name="item_id" id="itemSelect" required
                                            style="min-width: 400px;">
                                            <option value="" disabled selected>Pilih Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-standard="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-similar="{{ $item->similar_part_file_path ? route('items.pdf', ['id' => $item->id, 'index' => 'similar']) : '' }}"
                                                    data-name="{{ $item->name }}" data-part-number="{{ $item->part_number ?? '' }}" 
                                                    data-customer="{{ $item->customer ?? '' }}"
                                                    data-description="{{ $item->description }}"
                                                    data-defects="{{ json_encode($item->defects) }}"
                                                    data-sap-code="{{ $item->sap_code ?? '' }}">
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                    {{ $item->sap_code ? '- SAP: ' . $item->sap_code : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Lot ID (Injection) -->
                                <td class="align-middle" id="tdLotId">
                                    <input type="date" class="form-control form-control-sm mb-1" style="min-width: 120px;"
                                        name="injection_date" id="injectionDateInput" value="{{ $defaultDate }}" data-field-name="Tanggal Lot ID" data-scan-optional="1">
                                    <select class="form-control form-control-sm mb-1" name="injection_shift" id="injectionShiftInput" data-field-name="Shift Lot ID" data-scan-optional="1">
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                    <input type="text" class="form-control form-control-sm text-center" name="injection_initials" id="injectionInitialsInput"
                                        placeholder="Inisial" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" data-field-name="Inisial Lot ID" data-scan-optional="1">
                                </td>

                                <!-- Painting -->
                                 <td class="align-middle" id="tdPainting">
                                    <input type="date" class="form-control form-control-sm mb-1" style="min-width: 120px;"
                                        name="painting_date" id="paintingDateInput" value="{{ $defaultDate }}" data-scan-optional="1">
                                    <select class="form-control form-control-sm mb-1" name="painting_shift" id="paintingShiftInput" data-scan-optional="1">
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                    <input type="text" class="form-control form-control-sm" name="no_lot" id="noLotInput"
                                        placeholder="No Lot..." autocomplete="off" data-scan-optional="1">
                                </td>

                                <!-- Kualitas (Tanggal/Shift/Meja yang Ada) -->
                                <td class="align-middle">
                                    <input type="date" class="form-control form-control-sm mb-1" style="min-width: 110px;"
                                        name="date" value="{{ $defaultDate }}" required>
                                    <select class="form-control form-control-sm" name="shift" id="shiftInput" required>
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </td>
                                <td class="align-middle">
                                    <select name="line" id="lineSelect" class="form-control form-control-sm" style="min-width: 85px;"
                                        required>
                                        <option value="">Meja</option>
                                        @foreach (range(1, 15) as $i)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Total Qty -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 80px;"
                                        name="total_qty" id="totalQty" placeholder="0" min="0" required>
                                </td>

                                <td class="align-middle" style="min-width: 240px;">
                                    <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="row no-gutters mb-2 defect-row align-items-center">
                                            <div class="col-7 pr-1">
                                                <select class="form-control defect-select font-weight-bold"
                                                    name="defect_types[]" id="defectSelect">
                                                    <option value="">-- Pilih Defect --</option>
                                                </select>
                                            </div>
                                            <div class="col-4 pr-1">
                                                <input type="number" class="form-control defect-qty text-center font-weight-bold"
                                                    name="defect_quantities[]" placeholder="Qty" min="1">
                                            </div>
                                            <div class="col-1 text-center"></div>
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-info mt-1"
                                        style="display: none;">
                                        <i class="fas fa-plus"></i> Tambah Jenis
                                    </button>
                                </td>

                                <!-- Total OK / NG -->
                                <td class="align-middle" style="min-width: 120px;">
                                    <div class="d-flex align-items-center mb-1" style="gap:4px;">
                                        <span class="ok-label">OK</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0; background:#f0fdf4;"
                                            name="total_ok" value="0" min="0" required readonly>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:4px;">
                                        <span class="ng-label">NG</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0; background:#fef2f2;"
                                            name="total_ng" id="totalNG" value="0" min="0" required readonly>
                                    </div>
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle text-center judgment-column" style="min-width: 150px;">
                                    <div id="judgmentBadge" class="mb-2 p-3 font-weight-bold h4 rounded d-none shadow-sm"
                                        style="border: 2px solid transparent;">
                                        -
                                    </div>
                                    <select class="form-control font-weight-bold d-none" name="judgment" id="judgmentSelect"
                                        required>
                                        <option value="" disabled selected>-- Result --</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                </td>

                                <!-- Inisial QC -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" id="operatorInitialsInput"
                                        style="min-width: 80px; text-transform: uppercase;"
                                        name="operator_initials" value="{{ auth()->user()->initials ?? '' }}"
                                        oninput="this.value = this.value.toUpperCase()" placeholder="Inisial" required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle" style="min-width: 320px;">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger small">Next
                                            Proses:</label>
                                        <select class="form-control form-control-sm" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih --</option>
                                            @foreach($nextProcesses as $opt)
                                                <option value="{{ $opt->name }}">{{ $opt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="remarks" rows="6"
                                        style="min-height:140px; min-width:300px; width:100%; resize:both;"
                                        placeholder="Catatan..."></textarea>
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
                        <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                            <i class="fas fa-save fa-sm"></i> Simpan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bagian Tampilan PDF Berdampingan -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">STANDARD</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">PCCP</h6>
                        <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                            <div class="mr-2 border-right pr-2 d-flex align-items-center file-nav" style="display:none;">
                                <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardFile"
                                    title="Previous File">
                                    <i class="fas fa-file-pdf"></i> <i class="fas fa-arrow-left fa-xs"></i>
                                </button>
                                <span id="standardFileInfo" class="small font-weight-bold mx-1">1/1</span>
                                <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardFile"
                                    title="Next File">
                                    <i class="fas fa-arrow-right fa-xs"></i> <i class="fas fa-file-pdf"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center page-nav">
                                <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevStandardPage"
                                    title="Previous Page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextStandardPage"
                                    title="Next Page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn mr-1" id="fullStandardBtn"
                                style="display:none;">
                                <i class="fas fa-expand"></i> Full
                            </button>
                            <a id="downloadStandardBtn" class="btn btn-sm btn-success" href="#" download title="Download PCCP PDF" style="display:none;">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div id="standardPdfContainer" class="rounded border"
                        style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="standardPdfPlaceholder"
                            class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-pdf fa-3x mb-3"></i>
                            <p class="mb-0">Pilih Item untuk menampilkan Standard PDF</p>
                        </div>
                        <canvas id="standardPdfCanvas" class="d-none" style="margin: 0 auto;"></canvas>
                        <div id="standardPdfLoading" class="h-100 d-none align-items-center justify-content-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">SIMILAR PART</h6>
                        <div class="d-flex align-items-center similar-nav-controls" style="display:none;">
                            <div class="d-flex align-items-center page-nav">
                                <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevSimilarPage"
                                    title="Previous Page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="similarPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextSimilarPage"
                                    title="Next Page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-info view-pdf-btn mr-1" id="fullSimilarBtn"
                                style="display:none;">
                                <i class="fas fa-expand"></i> Full
                            </button>
                            <a id="downloadSimilarBtn" class="btn btn-sm btn-info" href="#" download title="Download Similar Part PDF" style="display:none;">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div id="similarPdfContainer" class="rounded border"
                        style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="similarPdfPlaceholder"
                            class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <p class="mb-0">Pilih Item untuk menampilkan Similar Part</p>
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

    <!-- Modal PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div
                        class="d-flex justify-content-center py-2 bg-light sticky-top border-bottom align-items-center flex-wrap">
                        <div class="mr-3 mb-2">
                            <button type="button" class="btn btn-dark btn-sm" id="prevPdf">
                                <i class="fas fa-file-pdf"></i> <i class="fas fa-arrow-left"></i>
                            </button>
                            <span id="pdfInfo" class="mx-2 font-weight-bold small">File 1 of ?</span>
                            <button type="button" class="btn btn-dark btn-sm" id="nextPdf">
                                <i class="fas fa-arrow-right"></i> <i class="fas fa-file-pdf"></i>
                            </button>
                        </div>
                        <div class="mr-3 mb-2 border-left pl-3">
                            <button type="button" class="btn btn-secondary btn-sm" id="prevPage">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="pageInfo" class="mx-2 small">Page 1 of ?</span>
                            <button type="button" class="btn btn-secondary btn-sm" id="nextPage">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="border-left pl-3 mb-2">
                            <button type="button" class="btn btn-primary btn-sm mr-1" id="pdfZoomIn">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mr-1" id="pdfZoomReset">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="pdfZoomOut">
                                <i class="fas fa-search-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-center bg-dark" style="overflow: auto; max-height: 85vh;">
                        <canvas id="modalPdfCanvas"
                            style="border: 1px solid black; direction: ltr; margin: 10px auto;"></canvas>
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-0 text-center">
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
                    <div id="qr-reader-results" class="p-3 d-none">
                         <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
                         <p class="mt-2 mb-0">Sedang memproses data QR...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gambar -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">STANDARD</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid mb-3">
                    <h5 id="modalTitle" class="font-weight-bold"></h5>
                    <p id="modalDescription"></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}"></script>
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/painting.js') }}?v={{ time() }}"></script>
    <script>
        $(document).ready(function () {
            window.initPaintingCreate({
                pdfWorkerSrc: "{{ asset('js/vendor/pdf.worker.min.js') }}",
                pdfRoute: "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}",
                itemSearchUrl: "{{ route('items.search-by-part') }}",
                qrUniqueUrl: "{{ route('items.check-qr-unique') }}"
            });
            window.initItemSearch('itemSelect');

            // --- Auto-fill Logic ---
            const nextNoLotUrl = "{{ route('painting.next_no_lot') }}";
            const lastDataUrl = "{{ route('painting.last_data') }}";

            const itemSelect = document.getElementById('itemSelect');
            const initialsInput = document.getElementById('operatorInitialsInput');
            const paintingDateInput = document.getElementById('paintingDateInput');
            const paintingShiftInput = document.getElementById('paintingShiftInput');
            const shiftInput = document.getElementById('shiftInput');
            const noLotInput = document.getElementById('noLotInput');

            const injectionDateInput = document.getElementById('injectionDateInput');
            const injectionShiftInput = document.getElementById('injectionShiftInput');
            const injectionInitialsInput = document.getElementById('injectionInitialsInput');
            const lineSelect = document.getElementById('lineSelect');

            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this,
                        args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(function() {
                        func.apply(context, args);
                    }, wait);
                };
            }

            function fetchNextNoLot() {
                const itemId = itemSelect ? itemSelect.value : '';
                const paintingDate = paintingDateInput ? paintingDateInput.value : '';
                const paintingShift = paintingShiftInput ? paintingShiftInput.value : '1';
                const initials = initialsInput ? initialsInput.value : '';

                if (!itemId || !paintingDate || !initials) return;

                const params = new URLSearchParams({
                    item_id: itemId,
                    painting_date: paintingDate,
                    painting_shift: paintingShift,
                    shift: shiftInput ? shiftInput.value : '1',
                    operator_initials: initials
                });

                fetch(nextNoLotUrl + '?' + params.toString())
                    .then(r => r.json())
                    .then(data => {
                        if (data.no_lot) noLotInput.value = data.no_lot;
                    })
                    .catch(e => console.error('Error fetching no lot:', e));
            }

            function fetchLastData() {
                const itemId = itemSelect ? itemSelect.value : '';
                const initials = initialsInput ? initialsInput.value : '';

                if (!itemId || !initials) return;

                const params = new URLSearchParams({
                    item_id: itemId,
                    operator_initials: initials
                });

                fetch(lastDataUrl + '?' + params.toString())
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (data.injection_date) injectionDateInput.value = data.injection_date;
                            if (data.injection_shift) injectionShiftInput.value = data.injection_shift;
                            if (data.injection_initials && injectionInitialsInput) injectionInitialsInput.value = data.injection_initials;
                            if (data.line) lineSelect.value = data.line;
                        }
                    })
                    .catch(e => console.error('Error fetching last data:', e));
            }

            const debouncedFetch = debounce(() => {
                fetchNextNoLot();
                fetchLastData();
            }, 500);

            if (itemSelect) {
                $(itemSelect).on('change', () => {
                    fetchNextNoLot();
                    fetchLastData();
                });
            }
            if (initialsInput) initialsInput.addEventListener('input', debouncedFetch);
            if (paintingDateInput) paintingDateInput.addEventListener('change', fetchNextNoLot);
            if (paintingShiftInput) paintingShiftInput.addEventListener('change', fetchNextNoLot);
            if (shiftInput) shiftInput.addEventListener('change', fetchNextNoLot);

            // Initial trigger
            setTimeout(() => {
                if (itemSelect && itemSelect.value) {
                    fetchNextNoLot();
                    fetchLastData();
                }
            }, 500);
        });
    </script>
@endpush
