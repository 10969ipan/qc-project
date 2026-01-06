@extends('layouts.admin')

@section('content')
    <div class="container-fluid">


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
                <form action="{{ route('cross_cut.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th>Standard</th>
                                    <th>Item Part</th>
                                    <th>Tanggal & Shift Produksi / QC</th>
                                    <th>Hasil Cross Cut</th>
                                    <th>Kimia</th>
                                    <th>Posisi Remark (Judgement / No Lot)</th>
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
                                    <td class="align-middle" style="min-width: 200px;">
                                        <select class="form-control" id="item_id" name="item_id" required>
                                            <option value="" disabled selected style="font-weight: bold; color: #6c757d;">
                                                Pilih Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-name="{{ $item->name }}"
                                                    data-description="{{ $item->description ?? '' }}">
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <!-- Tanggal & Shift Produksi / QC -->
                                    <td class="align-middle" style="min-width: 250px;">
                                        <div class="form-group mb-2">
                                            <label>Tgl. & Shift Produksi</label>
                                            <div class="input-group">
                                                <input type="datetime-local" class="form-control" name="production_datetime"
                                                    required>
                                                <select class="form-control" name="production_shift" required>
                                                    <option value="1">Shift 1</option>
                                                    <option value="2">Shift 2</option>
                                                    <option value="3">Shift 3</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Tgl. & Shift QC</label>
                                            <div class="input-group">
                                                <input type="datetime-local" class="form-control" name="qc_datetime"
                                                    required>
                                                <select class="form-control" name="qc_shift" required>
                                                    <option value="1">Shift 1</option>
                                                    <option value="2">Shift 2</option>
                                                    <option value="3">Shift 3</option>
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
                                    <!-- Kimia -->
                                    <td class="align-middle" style="min-width: 200px;">
                                        <div class="form-group mb-2"><label>Copper</label><input type="text"
                                                class="form-control" name="chemical_copper"></div>
                                        <div class="form-group mb-2"><label>Nikel</label><input type="text"
                                                class="form-control" name="chemical_nikel"></div>
                                        <div class="form-group mb-2"><label>Eching</label><input type="text"
                                                class="form-control" name="chemical_eching"></div>
                                        <div class="form-group mb-0"><label>Abu</label><input type="text"
                                                class="form-control" name="chemical_abu"></div>
                                    </td>
                                    <!-- Posisi Remark -->
                                    <td class="align-middle" style="min-width: 200px;">
                                        <div class="form-group mb-2">
                                            <label>Judgment</label>
                                            <select class="form-control" name="position_remark_judgment" required>
                                                <option value="OK">OK</option>
                                                <option value="NG">NG</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0"><label>No Lot</label><input type="text"
                                                class="form-control" name="position_remark_no_lot" required></div>
                                    </td>
                                    <!-- Result Remark -->
                                    <td class="align-middle"><input type="text" class="form-control" name="result_remark">
                                    </td>
                                    <!-- Inisial QC -->
                                    <td class="align-middle">
                                        @php
                                            $initial = '';
                                            if (auth()->check()) {
                                                $name = strtolower(auth()->user()->name);
                                                if (str_contains($name, 'anggi')) {
                                                    $initial = 'AP';
                                                } elseif (str_contains($name, 'irfan')) {
                                                    $initial = 'IA';
                                                } elseif (str_contains($name, 'gugun')) {
                                                    $initial = 'GK';
                                                } elseif (str_contains($name, 'dede')) {
                                                    $initial = 'DS';
                                                } elseif (str_contains($name, 'arga')) {
                                                    $initial = 'AY';
                                                } elseif (str_contains($name, 'sopian')) {
                                                    $initial = 'SH';
                                                } elseif (str_contains($name, 'yono')) {
                                                    $initial = 'YS';
                                                } elseif (str_contains($name, 'dinar')) {
                                                    $initial = 'DA';
                                                }
                                            }
                                        @endphp
                                        <input type="text" class="form-control text-center" name="operator_initials"
                                            placeholder="Inisial" value="{{ $initial }}" required>
                                    </td>
                                    <!-- Keterangan -->
                                    <td class="align-middle"><textarea class="form-control" name="keterangan"
                                            rows="3"></textarea></td>
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
                    <h5 class="modal-title" id="imageModalLabel">STANDARD</h5>
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
                    <div class="d-flex justify-content-center mb-2 align-items-center">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="prevPage">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="pageInfo" class="mr-2">Page 1 of ?</span>
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="nextPage">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div class="border-left pl-2 ml-2">
                            <button type="button" class="btn btn-primary btn-sm mr-2" id="pdfZoomIn">
                                <i class="fas fa-search-plus"></i> Zoom In
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mr-2" id="pdfZoomReset">
                                <i class="fas fa-sync-alt"></i> Reset
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="pdfZoomOut">
                                <i class="fas fa-search-minus"></i> Zoom Out
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- PDF.js Logic ---
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

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

            // Trigger PDF Modal from dynamic button (delegated event)
            $(document).on('click', '.view-pdf-btn', function () {
                const url = $(this).data('src');

                // Reset state
                pdfDoc = null;
                pageNum = 1;
                scale = 1.0;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                document.getElementById('pageInfo').textContent = 'Loading...';

                // Show modal
                $('#pdfModal').modal('show');

                // Load PDF
                pdfjsLib.getDocument(url).promise.then(function (pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    document.getElementById('pageInfo').textContent = 'Page 1 of ' + pdfDoc.numPages;
                    renderPage(pageNum);
                }, function (reason) {
                    console.error(reason);
                    let errorMsg = 'Error loading PDF. ';
                    if (reason.name === 'MissingPDFException') {
                        errorMsg += 'The PDF file could not be found on the server. Please check Master Data Items or re-upload the file.';
                    } else {
                        errorMsg += reason.message || reason;
                    }

                    document.getElementById('pageInfo').textContent = 'Error: ' + reason.name;
                    alert(errorMsg);
                });
            });

            // Update Standard Image/PDF when item is selected
            $('#item_id').on('change', function () {
                var selectedOption = $(this).find('option:selected');
                var imageUrl = selectedOption.data('image');
                var fileUrl = selectedOption.data('file');
                var name = selectedOption.data('name');
                var description = selectedOption.data('description');

                var container = $('#imageContainer');
                var htmlContent = '';

                if (fileUrl) {
                    htmlContent += '<button type="button" class="btn btn-danger btn-sm view-pdf-btn mb-1" data-src="' + fileUrl + '"><i class="fas fa-file-pdf"></i> PDF</button>';
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

                if (!fileUrl && !imageUrl) {
                    htmlContent = '<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>';
                }

                // Use d-flex column if both exist
                if (fileUrl && imageUrl) {
                    container.html('<div class="d-flex flex-column align-items-center">' + htmlContent + '</div>');
                } else {
                    container.html(htmlContent);
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

                    timerInterval = setInterval(function () {
                        totalSeconds++;
                        updateTimerDisplay();
                    }, 1000);
                }
            });

            document.querySelector('form').addEventListener('submit', function () {
                if (timerRunning) {
                    clearInterval(timerInterval);
                    timerRunning = false;
                    cycleTimeInput.value = totalSeconds;
                }
            });
        });
    </script>
@endpush