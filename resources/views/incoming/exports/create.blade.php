@extends('layouts.admin')

@section('title', 'Input Data Incoming Export')

@push('styles')
<style>
    #checksheetTable th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f8f9fc; }
    #checksheetTable td { font-size: 0.85rem; }
    .form-control-sm.text-center { font-weight: bold; border-color: #d1d3e2; }
    .form-control-sm.text-center:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
    #judgmentBadge { min-width: 80px; min-height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');

        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_exports', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0213',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '-',
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
                            CHECK SHEET INCOMING EXPORT 
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

    <div class="row">
        <!-- Kolom Kiri: Input Data -->
        <div class="col-xl-6 col-lg-6 col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Export</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('incoming.exports.store') }}" method="POST" id="checksheetForm" novalidate>
                        @csrf
                        <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                        <input type="hidden" name="qrcode" id="qrcodeInput">
                        <input type="hidden" name="part_code" id="partCodeInput">
                        <input type="hidden" name="supplier_id" id="supplierIdInput">
                        <input type="hidden" name="quantity" id="quantityInput">
                        <input type="hidden" name="unique_code_id" id="uniqueCodeInput">
                        <input type="hidden" name="sap_code" id="sapCodeInputHidden">
                        <input type="hidden" name="scan_method" id="scanMethodInput" value="manual">
                        {{-- Field wajib yang tidak punya input visibel --}}
                        <input type="hidden" name="tanggal_delivery" id="tanggalDeliveryInput" value="{{ $defaultDate }}">
                        <input type="hidden" name="lot_qty" id="lotQtyInput" value="0">
                        <input type="hidden" name="shift" id="shiftInput" value="{{ $defaultShift }}">

                        <!-- SECTION 1: INFORMASI ITEM PART & SCAN VERIFIKASI -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            INFORMASI ITEM PART &amp; SCAN VERIFIKASI
                        </div>

                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-gray-700 mb-1">Scan Verifikasi Quality</label>
                            <div class="input-group input-group-sm mb-2">
                                <input type="text" class="form-control border-0 shadow-sm" id="sapCodeInput"
                                    placeholder="Tap kolom ini, lalu scan barcode label" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="btnScanQR" title="Buka QR Scanner">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block mb-2"><i class="fas fa-info-circle mr-1"></i>Arahkan kursor ke sini sebelum menembak QR</small>
                            <select class="form-control select2" name="item_id" id="itemSelect" required style="width: 100%;">
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

                        <!-- SECTION 2: TANGGAL & TOTAL CHECK -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            TANGGAL &amp; TOTAL CHECK
                        </div>

                        <div class="bg-light p-3 rounded border mb-4 shadow-sm">
                            <div class="form-row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Tanggal Check <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="date" value="{{ $defaultDate }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="small font-weight-bold text-gray-700 mb-1">Total Check <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="total_check" id="totalCheckInput" placeholder="0" min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: HASIL INSPEKSI & JUDGMENT -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem;">
                            HASIL INSPEKSI &amp; JUDGMENT
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-gray-700 d-block mb-1">Defect List (NG):</label>
                            <div id="defectContainer">
                                <div class="row no-gutters mb-2 defect-row align-items-center">
                                    <div class="col-7 pr-1">
                                        <select class="form-control form-control-sm defect-select font-weight-bold border-0 shadow-sm" name="defect_types[]" id="defectSelect">
                                            <option value="">-- Pilih Defect --</option>
                                        </select>
                                    </div>
                                    <div class="col-3 pr-1">
                                        <input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold border-0 shadow-sm" name="defect_quantities[]" placeholder="Qty" min="1">
                                    </div>
                                    <div class="col-2 text-center action-col">
                                        <button type="button" id="addDefectBtn" class="btn btn-primary btn-sm shadow-sm" style="display: none;" title="Tambah Jenis">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded border mb-3 shadow-sm">
                            <div class="form-row align-items-center">
                                <div class="col-md-6 mb-2 mb-md-0 text-center border-right">
                                    <label class="small font-weight-bold text-gray-700 d-block mb-1">Judgment Result</label>
                                    <div id="judgmentBadge" class="mb-1 p-2 font-weight-bold h5 rounded d-none shadow-sm" style="border: 2px solid transparent;">-</div>
                                    <select class="form-control form-control-sm font-weight-bold d-none" name="judgment" id="judgmentSelect" required>
                                        <option value="" disabled selected>-- Result --</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                    <input type="hidden" name="total_ng" id="totalNgInput" value="0">
                                    <div id="aql_info" class="small font-weight-bold text-center" style="display:none;">
                                        <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                                        <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                                    </div>
                                </div>
                                <div class="col-md-6 pl-md-3">
                                    <label class="small font-weight-bold text-gray-700">QC Initials <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm text-center font-weight-bold border-0 shadow-sm" name="operator_initials" value="{{ auth()->user()->initials ?? '' }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-gray-700">Remarks / Catatan</label>
                            <textarea class="form-control form-control-sm border-0 shadow-sm" name="remarks" rows="2" placeholder="Tuliskan catatan opsional di sini..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0 font-weight-bold text-gray-800" id="timerDisplay">00:00:00</h5>
                                <input type="hidden" name="cycle_time" id="cycleTimeInput" value="0">
                            </div>
                            <div>
                                <button type="button" class="btn btn-success btn-sm mr-2 shadow-sm font-weight-bold px-3" id="startTimerBtn">
                                    <i class="fas fa-play mr-1"></i> Start
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold" id="saveBtn" disabled>
                                    <i class="fas fa-save mr-1"></i> SIMPAN DATA
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card Daftar Scan Sementara (Queue List - Selaras UI Incoming Part) -->
            <div class="card shadow mb-4 d-none" id="tempQueueCard">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                    <h6 class="m-0 font-weight-bold text-gray-800">
                        Daftar Antrian Scan Incoming Export
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
                                    <th style="width: 140px;">Tanggal Check</th>
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
        </div>

        <!-- Kolom Kanan: STANDARD -->
        <div class="col-xl-6 col-lg-6 col-md-12">
            <div class="card shadow mb-4" id="pdfDisplaySection">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">STANDARD</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- PCCP / Standard -->
                        <div class="col-md-12 border-bottom mb-4 pb-4">
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
                            <div id="standardPdfContainer" class="rounded border" style="height: 650px; min-height: 550px; position: relative; background-color: #eee; overflow: auto;">
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
                        <!-- Similar Part / Dimensi -->
                        <div class="col-md-12">
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
                            <div id="similarPdfContainer" class="rounded border" style="height: 650px; min-height: 550px; position: relative; background-color: #eee; overflow: auto;">
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
@endsection

@push('scripts')
    <script>
        window.INCOMING_PART_CONFIG = {
            arrivalsUrl: "",
            checkFirstTimeUrl: "",
            qrUniqueUrl: "",
            itemSearchUrl: "{{ route('items.search-by-part') }}",
            index_url: "{{ route('incoming.exports.index', ['plant' => request('plant') ?? auth()->user()->plant_id]) }}",
            useQueue: true
        };
    </script>
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script>
        window.pdfWorkerSrc = "{{ asset('js/vendor/pdf.worker.min.js') }}";
        window.pdfUrlPattern = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}";
    </script>
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/incoming-create.js') }}"></script>
    <script>
        $(document).ready(function () {
            if(typeof window.initItemSearch === 'function') {
                window.initItemSearch('itemSelect');
            }
        });
    </script>
@endpush
