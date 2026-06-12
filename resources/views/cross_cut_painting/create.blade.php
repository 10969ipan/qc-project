@extends('layouts.admin')

@section('title', 'Input Data Checksheet')

@section('content')

    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                            CHECK SHEET CROSS CUT PAINTING
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">QC-KRW-0055/2</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">25/03/2015</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">3 / 22/12/2025</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">1 / 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet Cross Cut Painting</h6>
        </div>
        <div class="card-body">

            @php
                $plant = $plant ?? request('plant') ?? auth()->user()->plant_id;
            @endphp

            <form action="{{ route('cross_cut_painting.store') }}" method="POST" enctype="multipart/form-data" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="{{ $plant }}">
                <div class="table-responsive">
                    <table class="table" id="checksheetTable" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center">

                                <th>Item Part</th>
                                <th>Tanggal &amp; Shift Produksi / QC</th>
                                <th>Hasil Cross Cut, Pencil Scratch &amp; Tap Test</th>
                                <th>Posisi Remark (Judgement)</th>
                                <th>Inisial QC</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>

                                <!-- Item Part -->
                                <td class="align-middle" style="min-width: 150px;">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold">Kode SAP</label>
                                        <input type="text" class="form-control" id="sapCodeInput"
                                            placeholder="Ketik Kode SAP..." style="min-width: 150px;">
                                        <small class="text-muted">Auto-select item berdasarkan SAP code</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part</label>
                                        <select class="form-control" id="item_id" name="item_id" required>
                                            <option value="" disabled selected style="font-weight: bold; color: #6c757d;">
                                                Pilih Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-name="{{ $item->name }}"
                                                    data-part-number="{{ $item->part_number ?? '' }}"
                                                    data-customer="{{ $item->customer ?? '' }}"
                                                    data-description="{{ $item->description ?? '' }}"
                                                    data-sap-code="{{ $item->sap_code ?? '' }}">
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                    {{ $item->sap_code ? '- SAP: ' . $item->sap_code : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <!-- Tanggal & Shift Produksi / QC -->
                                <td class="align-middle" style="min-width: 180px;">
                                    <div class="form-group mb-2">
                                        <label>Tgl. & Shift Produksi</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" name="production_date" id="productionDateInput"
                                                value="{{ $defaultDate }}" required>
                                            <select class="form-control" name="production_shift" id="productionShiftInput" required>
                                                <option value="1" {{ ($defaultShift ?? 1) == 1 ? 'selected' : '' }}>Shift 1
                                                </option>
                                                <option value="2" {{ ($defaultShift ?? 1) == 2 ? 'selected' : '' }}>Shift 2
                                                </option>
                                                <option value="3" {{ ($defaultShift ?? 1) == 3 ? 'selected' : '' }}>Shift 3
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Tgl. & Shift QC</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" name="qc_date"
                                                value="{{ $defaultDate }}" required>
                                            <select class="form-control" name="qc_shift" id="qcShiftInput" required>
                                                <option value="1" {{ ($defaultShift ?? 1) == 1 ? 'selected' : '' }}>Shift 1
                                                </option>
                                                <option value="2" {{ ($defaultShift ?? 1) == 2 ? 'selected' : '' }}>Shift 2
                                                </option>
                                                <option value="3" {{ ($defaultShift ?? 1) == 3 ? 'selected' : '' }}>Shift 3
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <!-- Hasil Cross Cut (Gambar) -->
                                <!-- Hasil Cross Cut, Pencil Scratch & Tap Test -->
                                <td class="align-middle text-center" style="min-width: 280px;">
                                    <div class="row">
                                        <div class="col-12 form-group mb-2">
                                            <label for="image" class="mb-1 d-block font-weight-bold">Ambil Gambar</label>
                                            <!-- Input file tersembunyi -->
                                            <input type="file" class="d-none" id="image" name="image" accept="image/*"
                                                capture="environment" required>
                                            <!-- Tombol kustom untuk memicu input file -->
                                            <button type="button" class="btn btn-primary btn-block mb-1" id="captureBtn">
                                                <i class="fas fa-camera"></i> <span id="captureBtnText">Buka Kamera / Pilih Foto</span>
                                            </button>
                                            <!-- Tombol pratinjau -->
                                            <button type="button" id="previewBtn" class="btn btn-info btn-sm btn-block mb-1"
                                                style="display: none;">
                                                <i class="fas fa-eye"></i> Preview Foto
                                            </button>
                                            <!-- Tampilan nama file -->
                                            <small id="fileName" class="text-muted d-block"></small>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-4 px-1 form-group mb-0">
                                            <label class="font-weight-bold mb-1" style="font-size: 11px;">Cross Cut</label>
                                            <select class="form-control form-control-sm" name="defects[cross_cut]" id="defectCrossCut" required>
                                                <option value="OK">OK</option>
                                                <option value="NG">NG</option>
                                            </select>
                                        </div>
                                        <div class="col-4 px-1 form-group mb-0">
                                            <label class="font-weight-bold mb-1" style="font-size: 11px;">Pencil Scratch</label>
                                            <select class="form-control form-control-sm" name="pencil_scratch" id="defectPencilScratch" required>
                                                <option value="OK">OK</option>
                                                <option value="NG">NG</option>
                                            </select>
                                        </div>
                                        <div class="col-4 px-1 form-group mb-0">
                                            <label class="font-weight-bold mb-1" style="font-size: 11px;">Tap Test</label>
                                            <select class="form-control form-control-sm" name="tap_test" id="defectTapTest" required>
                                                <option value="OK">OK</option>
                                                <option value="NG">NG</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <!-- Posisi Remark (Judgment) -->
                                <td class="align-middle" style="min-width: 120px;">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Judgment</label>
                                        <select class="form-control" name="position_remark_judgment" required>
                                            <option value="OK">OK</option>
                                            <option value="NG">NG</option>
                                        </select>
                                     </div>
                                </td>
                                <!-- Inisial QC -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" name="operator_initials" id="operatorInitialsInput"
                                        placeholder="Inisial" value="{{ auth()->user()->initials ?? '' }}" required>
                                </td>
                                <!-- Keterangan -->
                                <td class="align-middle" style="min-width: 320px;">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next Proses:</label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            @foreach($nextProcesses as $opt)
                                                <option value="{{ $opt->name }}">{{ $opt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="keterangan" id="keteranganInput" rows="6"
                                        style="min-height:140px; min-width:300px; width:100%; resize:both;"></textarea>
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

    <!-- Bagian Tampilan PDF -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">STANDARD</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">STANDARD PDF</h6>
                        <div class="d-flex align-items-center">
                            <!-- Kontrol Zoom -->
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutStandard"
                                    title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetStandard"
                                    title="Reset Zoom">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInStandard"
                                    title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                                <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardPage"
                                    title="Previous Page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardPage"
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
                            <a id="downloadStandardBtn" class="btn btn-sm btn-success" href="#" download title="Download Standard PDF" style="display:none;">
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
            </div>
        </div>
    </div>

    <!-- Image Preview Modal (for Cross Cut result) -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-labelledby="imagePreviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagePreviewModalLabel">Image Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImage" src="" class="img-fluid" alt="Image Preview">
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal (for Standard Image) -->
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

    <!-- PDF Modal (for Standard PDF) -->
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
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/cross-cut.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initCrossCutCreate({
                pdfWorkerSrc: "{{ asset('js/vendor/pdf.worker.min.js') }}",
                pdfUrlPattern: "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}"
            });
            window.initItemSearch('item_id');
        });
    </script>
@endpush
