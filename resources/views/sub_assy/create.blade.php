@extends('layouts.admin')

@section('title', 'Input Data Checksheet')

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

@section('content')

    @php
        $plant = $plant ?? request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');
    @endphp


    @php
        $plant = strtolower(optional(auth()->user()->plant)->code ?? request('plant') ?? '');
        $tableOptions = range(1, 15);
        if ($plant === 'jakarta') {
            $tableOptions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        }
    @endphp



    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($errors) && (is_array($errors) ? count($errors) > 0 : $errors->any()))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="font-weight-bold">Terjadi Kesalahan!</h6>
            <ul class="mb-0">
                @foreach(is_array($errors) ? $errors : $errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <a href="#collapseLineStatus" class="d-block card-header py-3" data-toggle="collapse" role="button"
            aria-expanded="true" aria-controls="collapseLineStatus">
            <h6 class="m-0 font-weight-bold text-warning">Control Status Meja (Manual)</h6>
        </a>
        <div class="collapse" id="collapseLineStatus">
            <div class="card-body">
                <form action="{{ route('machine-status.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="line">
                    <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Pilih Meja</label>
                            <select name="number" class="form-control form-control-sm" required>
                                <option value="">- Pilih Meja -</option>
                                @foreach($tableOptions as $i)
                                    <option value="{{ $i }}">MEJA-{{ $i }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Status</label>
                            <select name="status" class="form-control form-control-sm" required>
                                <option value="normal">NORMAL (Auto)</option>
                                <option value="maintenance">MAINTENANCE (Kuning)</option>
                                <option value="stopped">IDLE (Hitam)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Keterangan (Optional)</label>
                            <input type="text" name="description" class="form-control form-control-sm"
                                placeholder="Keterangan...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-warning btn-sm btn-block">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet Sub Assy</h6>
        </div>
        <div class="card-body">


            @php
                $currentPlant = strtolower(request('plant') ?? optional(auth()->user()->plant)->code ?? '');
                $isJakarta = ($currentPlant === 'jakarta');
            @endphp

            {{-- Pilihan Tipe Pengecekan - Hanya untuk Plant Jakarta --}}
            @if($isJakarta)
                <div class="alert alert-info mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="font-weight-bold mb-0"><i class="fas fa-clipboard-check"></i> Tipe Pengecekan:</label>
                        </div>
                        <div class="col-md-9">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary active" id="labelSampling">
                                    <input type="radio" name="check_type_option" id="checkTypeSampling" value="sampling"
                                        checked>
                                    <i class="fas fa-chart-pie"></i> Sampling (AQL 0.65)
                                </label>
                                <label class="btn btn-outline-success" id="labelFullcheck">
                                    <input type="radio" name="check_type_option" id="checkTypeFullcheck" value="fullcheck">
                                    <i class="fas fa-check-double"></i> Fullcheck (Export) - 100%
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('checksheet.store') }}" method="POST" id="checksheetForm" novalidate>
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <input type="hidden" name="check_type" id="checkTypeInput" value="sampling">
                <input type="hidden" name="qrcode" id="qrcodeInput">
                <input type="hidden" name="part_code" id="partCodeInput">
                <input type="hidden" name="supplier_id" id="supplierIdInput">
                <input type="hidden" name="quantity" id="quantityInput">
                <input type="hidden" name="unique_code_id" id="uniqueCodeInput">
                <input type="hidden" name="sap_code" id="sapCodeInputHidden">
                <input type="hidden" name="scan_method" id="scanMethodInput" value="manual">
                <div class="table-responsive">
                    <table class="table" id="checksheetTable" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2" class="align-middle">Item Part</th>
                                <th rowspan="2" class="align-middle">Tanggal / Shift</th>
                                <th rowspan="2" class="align-middle">Total Qty</th>
                                <th rowspan="2" class="align-middle">Sampling Qty</th>
                                <th rowspan="2" class="align-middle" style="min-width: 280px;">Jenis (OK/NG) &amp; Detail NG</th>
                                <th rowspan="2" class="align-middle">Total (OK/NG)</th>
                                <th rowspan="2" class="align-middle">Judgment</th>
                                <th rowspan="2" class="align-middle">Inisial QC</th>
                                <th rowspan="2" class="align-middle">DESCRIPTION</th>
                            </tr>
                            <tr></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Pilihan Barang -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small font-weight-bold mb-1">
                                            Scan Verifikasi Quality
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="sapCodeInput"
                                                placeholder="Tap kolom ini, lalu scan barcode label" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btnScanQR"
                                                    title="Buka QR Scanner">
                                                    <i class="fas fa-qrcode"></i>
                                                    <span class="d-none d-md-inline ml-1"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Arahkan kursor ke sini sebelum menembak QR</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part</label>
                                        <select class="form-control" name="item_id" id="itemSelect" required
                                            style="min-width: 300px;">
                                            <option value="" disabled selected style="font-weight: bold; color: #6c757d;">
                                                Pilih
                                                Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-standard="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-similar="{{ $item->similar_part_file_path ? route('items.pdf', ['id' => $item->id, 'index' => 'similar']) : '' }}"
                                                    data-name="{{ $item->name }}"
                                                    data-part-number="{{ $item->part_number ?? '' }}"
                                                    data-customer="{{ $item->customer ?? '' }}"
                                                    data-description="{{ $item->description }}"
                                                    data-defects="{{ json_encode($item->defects) }}"
                                                    data-sap-code="{{ $item->sap_code ?? '' }}"
                                                    data-plant-id="{{ $item->plant_id }}">
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                    {{ $item->sap_code ? '- SAP: ' . $item->sap_code : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Tanggal / Shift -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label class="sr-only">Tanggal</label>
                                        <input type="date" class="form-control" style="min-width: 110px;" name="date"
                                            value="{{ $defaultDate }}" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="sr-only">Shift</label>

                                        <select class="form-control" style="min-width: 80px;" name="shift" required>
                                            <option value="1" {{ ($defaultShift ?? 1) == 1 ? 'selected' : '' }}>Shift 1
                                            </option>
                                            <option value="2" {{ ($defaultShift ?? 1) == 2 ? 'selected' : '' }}>Shift 2
                                            </option>
                                            <option value="3" {{ ($defaultShift ?? 1) == 3 ? 'selected' : '' }}>Shift 3
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="sr-only">Meja</label>
                                        <select name="line" id="line" class="form-control" style="min-width: 80px;"
                                            required>
                                            <option value="">Pilih Meja</option>
                                            @foreach ($tableOptions as $i)
                                                <option value="{{ $i }}">Meja {{ $i }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Total Kuantitas (Total Kuantitas yang diproduksi) -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="total_qty" placeholder="0" min="0" required>
                                </td>

                                <!-- Sampling Qty -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 80px;"
                                        name="sampling_qty" placeholder="0" min="1" required readonly>
                                </td>

                                <td class="align-middle" style="min-width: 280px;">
                                    <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="row no-gutters mb-2 defect-row align-items-center">
                                            <div class="col-8 pr-1">
                                                <select class="form-control defect-select font-weight-bold"
                                                    name="defect_types[]" id="defectSelect">
                                                    <option value="">-- Pilih Defect --</option>
                                                </select>
                                            </div>
                                            <div class="col-3 pr-1">
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
                                <td class="align-middle" style="min-width: 150px;">
                                    <div class="d-flex align-items-center mb-2" style="gap:0;">
                                        <span class="ok-label">OK</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0; font-size: 1.1rem; height: 38px;" name="total_ok" value="0" min="0" required readonly>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:0;">
                                        <span class="ng-label">NG</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0; font-size: 1.1rem; height: 38px;" name="total_ng" value="0" min="0" required readonly>
                                    </div>
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle text-center" style="min-width: 150px;">
                                    <div id="judgmentBadge" class="mb-2 p-3 font-weight-bold h4 rounded d-none shadow-sm"
                                        style="border: 2px solid transparent;">
                                        -
                                    </div>
                                    <select class="form-control font-weight-bold d-none" name="judgment" id="judgmentSelect"
                                        style="min-width: 100px;">
                                        <option value="" disabled selected>-- Result --</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                    <div id="aql_info" class="small mt-1 font-weight-bold text-center"
                                        style="display:none;">
                                        <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                                        <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                                    </div>
                                </td>

                                <!-- Inisial Operator -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center bg-light font-weight-bold" style="min-width: 60px; text-transform: uppercase;"
                                        name="operator_initials" placeholder="Inisial"
                                        value="{{ auth()->user()->initials ?? '' }}" readonly required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle" style="min-width: 320px;">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next
                                            Proses:</label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            @foreach($nextProcesses as $opt)
                                                <option value="{{ $opt->name }}">{{ $opt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="remarks" rows="6"
                                        style="min-height:140px; min-width:300px; width:100%; resize:both;"
                                        placeholder="Catatan tambahan..."></textarea>
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
                        <div class="d-flex align-items-center">
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutStandard" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetStandard" title="Reset"><i class="fas fa-sync-alt"></i></button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInStandard" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                            </div>
                            <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                                <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardPage"><i class="fas fa-chevron-left"></i></button>
                                <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardPage"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <a id="downloadStandardBtn" class="btn btn-sm btn-success" href="#" download title="Download PCCP PDF" style="display:none;">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                    <div id="standardPdfContainer" class="rounded border" style="height: 600px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="standardPdfPlaceholder" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-pdf fa-3x mb-3"></i>
                            <p class="mb-0 text-xs">Pilih Item untuk menampilkan Standard</p>
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
                        <div class="d-flex align-items-center">
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutSimilar" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetSimilar" title="Reset"><i class="fas fa-sync-alt"></i></button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInSimilar" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                            </div>
                            <div class="d-flex align-items-center similar-nav-controls" style="display:none;">
                                <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevSimilarPage"><i class="fas fa-chevron-left"></i></button>
                                <span id="similarPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextSimilarPage"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <a id="downloadSimilarBtn" class="btn btn-sm btn-info" href="#" download title="Download Similar Part PDF" style="display:none;">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                    <div id="similarPdfContainer" class="rounded border" style="height: 600px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="similarPdfPlaceholder" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <p class="mb-0 text-xs">Pilih Item untuk menampilkan Similar Part</p>
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
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">STANDARD (Image)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-2">
                        <button type="button" class="btn btn-primary btn-sm mr-2" id="zoomIn">
                            <i class="fas fa-search-plus"></i> Zoom In
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="zoomReset">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="zoomOut">
                            <i class="fas fa-search-minus"></i> Zoom Out
                        </button>
                    </div>
                    <div class="text-center" style="overflow: auto; max-height: 70vh;">
                        <img id="modalImage" src="" class="img-fluid mb-3" alt="Detail Gambar"
                            style="transition: transform 0.2s ease;">
                    </div>
                    <div class="text-center">
                        <h5 id="modalTitle" class="font-weight-bold"></h5>
                        <p id="modalDescription"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal PDF (Ditambahkan) -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">STANDARD (PDF)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-2 align-items-center flex-wrap">
                        <div class="mr-3 mb-2">
                            <button type="button" class="btn btn-dark btn-sm" id="prevPdf">
                                <i class="fas fa-file-pdf"></i> <i class="fas fa-arrow-left"></i>
                            </button>
                            <span id="pdfInfo" class="mx-2 font-weight-bold">File 1 of ?</span>
                            <button type="button" class="btn btn-dark btn-sm" id="nextPdf">
                                <i class="fas fa-arrow-right"></i> <i class="fas fa-file-pdf"></i>
                            </button>
                        </div>
                        <div class="mr-3 mb-2 border-left pl-3">
                            <button type="button" class="btn btn-secondary btn-sm" id="prevPage">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="pageInfo" class="mx-2">Page 1 of ?</span>
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
                    <div class="text-center bg-dark" style="overflow: auto; max-height: 80vh;">
                        <canvas id="the-canvas" style="border: 1px solid black; direction: ltr;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}"></script>
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/sub-assy.js') }}?v={{ time() }}"></script>
    <script>
        $(document).ready(function () {
            window.initSubAssyCreate({
                pdfWorkerSrc: "{{ asset('js/vendor/pdf.worker.min.js') }}",
                pdfUrlPattern: "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}",
                itemSearchUrl: "{{ route('items.search-by-part') }}",
                qrUniqueUrl: "{{ route('items.check-qr-unique') }}",
                lastLineUrl: "{{ route('sub_assy.last_line') }}"
            });
            window.initItemSearch('itemSelect');
        });
    </script>
@endpush
