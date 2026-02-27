@extends('layouts.admin')

@section('title', 'Input Data Checksheet')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET CROSS CUT PLATING
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');
                        @endphp
                        <span
                            class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
                            style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0214</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tgl. Terbit</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 25/03/2015</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Revisi / Tgl</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 3 / 22/12/2025</div>
                        </div>
                        <div class="row">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Halaman</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 1 / 1</div>
                        </div>
                    </div>
                </div>
            </div>
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
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet Cross Cut</h6>
        </div>
        <div class="card-body">
            <!-- Plant Selector for Admin -->
            @if(auth()->user()->role === 'admin')
                <form method="GET" action="{{ route('cross_cut.create') }}" class="mb-3">
                    <div class="form-group row">
                        <label for="plant" class="col-sm-2 col-form-label font-weight-bold">Pilih Plant:</label>
                        <div class="col-sm-4">
                            <select name="plant" id="plant" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Semua Plant --</option>
                                <option value="karawang" {{ request('plant') == 'karawang' ? 'selected' : '' }}>Karawang
                                </option>
                                <option value="jakarta" {{ request('plant') == 'jakarta' ? 'selected' : '' }}>Jakarta</option>
                            </select>
                            <small class="text-muted">Pilih plant untuk memfilter daftar item.</small>
                        </div>
                    </div>
                </form>
            @endif

            <form action="{{ route('cross_cut.store') }}" method="POST" enctype="multipart/form-data" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center">
                                <th>Standard</th>
                                <th>Item Part</th>
                                <th>Tanggal & Shift Produksi / QC</th>
                                <th>Hasil Cross Cut</th>
                                <th>Bak No</th>
                                <th>Posisi Remark (Judgement / No Lot QC)</th>
                                <th>Result Remark</th>
                                <th>Inisial QC</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Standard -->
                                <td class="align-middle text-center" id="imageContainer">
                                    <div
                                        style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-image fa-2x text-gray-300"></i>
                                    </div>
                                </td>
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
                                            <input type="date" class="form-control" name="production_date"
                                                value="{{ $defaultDate }}" required>
                                            <select class="form-control" name="production_shift" required>
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
                                            <select class="form-control" name="qc_shift" required>
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
                                <!-- Hasil Cross Cut (Image) -->
                                <td class="align-middle text-center">
                                    <label for="image" class="mb-2 d-block">Ambil Gambar</label>
                                    <!-- Hidden file input -->
                                    <input type="file" class="d-none" id="image" name="image" accept="image/*"
                                        capture="environment" required>
                                    <!-- Custom button untuk trigger file input -->
                                    <button type="button" class="btn btn-primary btn-block mb-2" id="captureBtn">
                                        <i class="fas fa-camera"></i> <span id="captureBtnText">Buka Kamera / Pilih
                                            Foto</span>
                                    </button>
                                    <!-- Preview button -->
                                    <button type="button" id="previewBtn" class="btn btn-info btn-sm btn-block"
                                        style="display: none;">
                                        <i class="fas fa-eye"></i> Preview Foto
                                    </button>
                                    <!-- File name display -->
                                    <small id="fileName" class="text-muted d-block"></small>
                                </td>
                                <!-- Bak No -->
                                <td class="align-middle" style="min-width: 150px;">
                                    <div class="form-group mb-2"><label>Catalyst</label><input type="text"
                                            class="form-control" name="chemical_catalyst"></div>
                                    <div class="form-group mb-0"><label>Abu</label><input type="text" class="form-control"
                                            name="chemical_abu"></div>
                                </td>
                                <!-- Posisi Remark -->
                                <td class="align-middle" style="min-width: 120px;">
                                    <div class="form-group mb-2">
                                        <label>Judgment</label>
                                        <select class="form-control" name="position_remark_judgment" required>
                                            <option value="OK">OK</option>
                                            <option value="NG">NG</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0"><label>No Lot QC</label><input type="text"
                                            class="form-control" name="position_remark_no_lot" required></div>
                                </td>
                                <!-- Result Remark -->
                                <td class="align-middle"><input type="text" class="form-control" name="result_remark">
                                </td>
                                <!-- Inisial QC -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" name="operator_initials"
                                        placeholder="Inisial" value="{{ auth()->user()->initials ?? '' }}" required>
                                </td>
                                <!-- Keterangan -->
                                <td class="align-middle">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next
                                            Proses:</label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            <option value="CRUSHING">CRUSHING</option>
                                            <option value="SORTIR">SORTIR</option>
                                            <option value="FINISHING">FINISHING</option>
                                            <option value="REPAIR">REPAIR</option>
                                            <option value="MARKING+FINISHING+PACKING">MARKING+FINISHING+PACKING</option>
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="keterangan" rows="3"></textarea>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // === INPUT LOCK UNTIL START ===
            // Disable all form inputs in checksheetForm until Start button is clicked
            const formInputs = $('#checksheetForm').find('input, select, textarea');
            formInputs.prop('disabled', true);
            $('#checksheetForm').addClass('inputs-locked');
            $('<style>#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo('head');

            // --- PDF.js Logic ---
            pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/vendor/pdf.worker.min.js') }}";

            let pdfDoc = null;
            let pageNum = 1;
            let pageRendering = false;
            let pageNumPending = null;
            let scale = 1.0;
            const canvas = document.getElementById('the-canvas');
            const ctx = canvas.getContext('2d');

            function renderPage(num) {
                pageRendering = true;
                pdfDoc.getPage(num).then(function (page) {
                    const viewport = page.getViewport({ scale: scale });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    const renderTask = page.render(renderContext);

                    renderTask.promise.then(function () {
                        pageRendering = false;
                        if (pageNumPending !== null) {
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                    });
                });
                document.getElementById('pageInfo').textContent = 'Page ' + num + ' of ' + pdfDoc.numPages;
            }

            function queueRenderPage(num) {
                if (pageRendering) {
                    pageNumPending = num;
                } else {
                    renderPage(num);
                }
            }

            document.getElementById('prevPage').addEventListener('click', function () {
                if (pageNum <= 1) return;
                pageNum--;
                queueRenderPage(pageNum);
            });

            document.getElementById('nextPage').addEventListener('click', function () {
                if (pageNum >= pdfDoc.numPages) return;
                pageNum++;
                queueRenderPage(pageNum);
            });

            document.getElementById('pdfZoomIn').addEventListener('click', function () {
                scale += 0.25;
                queueRenderPage(pageNum);
            });

            document.getElementById('pdfZoomOut').addEventListener('click', function () {
                if (scale > 0.25) {
                    scale -= 0.25;
                    queueRenderPage(pageNum);
                }
            });

            document.getElementById('pdfZoomReset').addEventListener('click', function () {
                scale = 1.0;
                queueRenderPage(pageNum);
            });

            let currentPdfIndex = 0;
            let totalPdfFiles = 0;
            let currentItemId = null;

            function loadPdf(itemId, index) {
                const url = `/items/${itemId}/pdf/${index}`;

                pdfDoc = null;
                pageNum = 1;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                document.getElementById('pageInfo').textContent = 'Loading...';
                document.getElementById('pdfInfo').textContent = `File ${index + 1} of ${totalPdfFiles}`;

                pdfjsLib.getDocument(url).promise.then(function (pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    document.getElementById('pageInfo').textContent = 'Page 1 of ' + pdfDoc.numPages;
                    renderPage(pageNum);
                }, function (reason) {
                    console.error(reason);
                    let errorMsg = 'Error loading PDF. ';
                    if (reason.name === 'MissingPDFException') {
                        errorMsg += 'The PDF file could not be found on the server.';
                    } else {
                        errorMsg += reason.message || reason;
                    }
                    document.getElementById('pageInfo').textContent = 'Error: ' + reason.name;
                    alert(errorMsg);
                });
            }

            document.getElementById('prevPdf').addEventListener('click', function () {
                if (currentPdfIndex <= 0) return;
                currentPdfIndex--;
                loadPdf(currentItemId, currentPdfIndex);
            });

            document.getElementById('nextPdf').addEventListener('click', function () {
                if (currentPdfIndex >= totalPdfFiles - 1) return;
                currentPdfIndex++;
                loadPdf(currentItemId, currentPdfIndex);
            });

            // Trigger PDF Modal from dynamic button (delegated event)
            $(document).on('click', '.view-pdf-btn', function () {
                currentItemId = $(this).data('id');
                totalPdfFiles = $(this).data('count');
                currentPdfIndex = 0;

                // Show modal
                $('#pdfModal').modal('show');

                // Load first PDF
                loadPdf(currentItemId, currentPdfIndex);
            });

            // Update Standard Image/PDF when item is selected
            $('#item_id').on('change', function () {
                var selectedOption = $(this).find('option:selected');
                var imageUrl = selectedOption.data('image');
                var files = selectedOption.data('files');
                var itemId = selectedOption.val();
                var name = selectedOption.data('name');
                var description = selectedOption.data('description');

                var container = $('#imageContainer');
                var htmlContent = '';

                if (files && files.length > 0) {
                    htmlContent += '<button type="button" class="btn btn-danger btn-sm view-pdf-btn mb-1" data-id="' + itemId + '" data-count="' + files.length + '"><i class="fas fa-file-pdf"></i> PDF (' + files.length + ')</button>';
                }

                if (imageUrl) {
                    htmlContent += '<img src="' + imageUrl + '" ' +
                        'style="max-width: 100px; max-height: 80px; border: 1px solid #dee2e6; cursor: pointer; display:block; margin: 0 auto;" ' +
                        'class="img-thumbnail" ' +
                        'data-toggle="modal" ' +
                        'data-target="#imageModal" ' +
                        'data-image="' + imageUrl + '" ' +
                        'data-title="' + name + '" ' +
                        'data-description="' + description + '">';
                }

                if ((!files || files.length === 0) && !imageUrl) {
                    htmlContent = '<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>';
                }

                // Use d-flex column if both exist
                if (files && files.length > 0 && imageUrl) {
                    container.html('<div class="d-flex flex-column align-items-center">' + htmlContent + '</div>');
                } else {
                    container.html(htmlContent);
                }
            });

            // SAP Code Auto-Selection Logic
            $('#sapCodeInput').on('input', function () {
                var sapCode = $(this).val().trim();

                if (sapCode.length >= 1) {
                    // Find matching item by SAP code
                    var matchedOption = $('#item_id option').filter(function () {
                        var itemSapCode = $(this).data('sap-code');
                        return itemSapCode && itemSapCode.toString().toLowerCase() === sapCode.toLowerCase();
                    });

                    if (matchedOption.length > 0) {
                        // Auto-select the matched item
                        $('#item_id').val(matchedOption.val()).trigger('change');
                        // Visual feedback
                        $('#sapCodeInput').removeClass('is-invalid').addClass('is-valid');
                    } else {
                        // No match found
                        $('#sapCodeInput').removeClass('is-valid').addClass('is-invalid');
                    }
                } else {
                    // Clear validation classes when input is empty
                    $('#sapCodeInput').removeClass('is-valid is-invalid');
                }
            });

            // Zoom Logic (Image)
            var currentZoom = 1;
            var zoomStep = 0.25;

            function updateZoom() {
                $('#modalImage').css('transform', 'scale(' + currentZoom + ')');
                if (currentZoom > 1) {
                    $('#modalImage').css('transform-origin', 'top center');
                } else {
                    $('#modalImage').css('transform-origin', 'center center');
                }
            }

            $('#zoomIn').click(function () {
                currentZoom += zoomStep;
                updateZoom();
            });

            $('#zoomOut').click(function () {
                if (currentZoom > zoomStep) {
                    currentZoom -= zoomStep;
                    updateZoom();
                }
            });

            $('#zoomReset').click(function () {
                currentZoom = 1;
                updateZoom();
            });

            $('#imageModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var image = button.data('image');
                var title = button.data('title');
                var description = button.data('description');

                var modal = $(this);
                modal.find('#modalImage').attr('src', image);
                modal.find('#modalTitle').text(title);
                modal.find('#modalDescription').text(description);
                currentZoom = 1;
                updateZoom();
            });

            // Trigger file input when button is clicked
            $('#captureBtn').on('click', function () {
                $('#image').click();
            });

            // Image Preview Logic
            $('#image').on('change', function (event) {
                var file = event.target.files[0];
                if (file) {
                    // Update button text and file name
                    $('#fileName').text('File: ' + file.name);
                    $('#captureBtnText').html('<i class="fas fa-sync"></i> Ganti Foto');
                    $('#captureBtn').removeClass('btn-primary').addClass('btn-warning');

                    // Preview image
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#previewImage').attr('src', e.target.result);
                        $('#previewBtn').show(); // Show the preview button
                    }
                    reader.readAsDataURL(file);
                }
            });

            $('#previewBtn').on('click', function () {
                $('#imagePreviewModal').modal('show'); // Open the modal on button click
            });

            // Timer Logic
            var timerInterval = null;
            var totalSeconds = 0;
            var timerRunning = false;
            var timerDisplay = document.getElementById('timerDisplay');
            var cycleTimeInput = document.getElementById('cycleTimeInput');
            var startTimerBtn = document.getElementById('startTimerBtn');
            var saveBtn = document.getElementById('saveBtn');

            function updateTimerDisplay() {
                var hours = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds % 3600) / 60);
                var seconds = totalSeconds % 60;
                var text = [hours, minutes, seconds].map(v => v < 10 ? "0" + v : v).join(":");
                timerDisplay.textContent = text;
                cycleTimeInput.value = totalSeconds;
            }

            startTimerBtn.addEventListener('click', function () {
                if (!timerRunning) {
                    timerRunning = true;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-secondary');
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-clock"></i> Running...';
                    saveBtn.disabled = false;

                    // === UNLOCK ALL INPUTS ===
                    formInputs.prop('disabled', false);
                    $('form').removeClass('inputs-locked');

                    timerInterval = setInterval(function () {
                        totalSeconds++;
                        updateTimerDisplay();
                    }, 1000);
                }
            });

            // Show/Hide Next Proses dropdown based on judgment (Cross Cut uses position_remark_judgment)
            function toggleNextProsesDropdown() {
                var judgment = $('select[name="position_remark_judgment"]').val();
                if (judgment === 'NG') {
                    $('#nextProsesContainer').slideDown();
                } else {
                    $('#nextProsesContainer').slideUp();
                    $('#nextProses').val(''); // Reset selection
                }
            }

            // Trigger on judgment change
            $('select[name="position_remark_judgment"]').on('change', function () {
                toggleNextProsesDropdown();
            });



            // Initialize on page load
            toggleNextProsesDropdown();

            document.querySelector('#checksheetForm').addEventListener('submit', function (e) {
                e.preventDefault(); // Always prevent default for AJAX

                // Validate: If NG, next_proses must be selected
                var judgment = $('select[name="position_remark_judgment"]').val();
                var nextProses = $('#nextProses').val();

                if (judgment === 'NG' && !nextProses) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Next Proses Wajib Dipilih',
                        text: 'Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!',
                        confirmButtonColor: '#3085d6'
                    });
                    $('#nextProses').focus();
                    return false;
                }

                if (timerRunning) {
                    clearInterval(timerInterval);
                    timerRunning = false;
                    cycleTimeInput.value = totalSeconds;
                }

                // Show loading state
                var $saveBtn = $(saveBtn);
                var originalHtml = $saveBtn.html();
                $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                var formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#global-loader').hide();
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data Berhasil Disimpan',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Lihat Data',
                                cancelButtonText: 'Tutup',
                                reverseButtons: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = response.index_url;
                                } else {
                                    // Reset Form & Re-lock
                                    $('#checksheetForm')[0].reset();
                                    resetState();
                                }
                            });
                        }
                    },
                    error: function (xhr) {
                        $('#global-loader').hide();
                        var errorMsg = 'Gagal menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                        $saveBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            function resetState() {
                clearInterval(timerInterval);
                timerRunning = false;
                totalSeconds = 0;
                updateTimerDisplay();

                startTimerBtn.classList.remove('btn-secondary');
                startTimerBtn.classList.add('btn-success');
                startTimerBtn.disabled = false;
                startTimerBtn.innerHTML = '<i class="fas fa-play"></i> Start';

                // RE-LOCK INPUTS
                formInputs.prop('disabled', true);
                $('#checksheetForm').addClass('inputs-locked');
                saveBtn.disabled = true;

                // Reset specific elements
                $('#imageContainer').html('<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');
                $('#item_id').val('').trigger('change');
                $('#nextProsesContainer').hide();

                // Reset image capture
                $('#fileName').text('');
                $('#captureBtnText').html('Buka Kamera / Pilih Foto');
                $('#captureBtn').removeClass('btn-warning').addClass('btn-primary');
                $('#previewBtn').hide();
                $('#previewImage').attr('src', '');
            }
        });
    </script>
@endpush