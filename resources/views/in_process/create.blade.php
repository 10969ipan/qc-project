@extends('layouts.admin')

@section('title', 'Input Data Checksheet')

@section('content')



    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4 border-left-info">
        <a href="#collapseMachineStatus" class="d-block card-header py-3" data-toggle="collapse" role="button"
            aria-expanded="true" aria-controls="collapseMachineStatus">
            <h6 class="m-0 font-weight-bold text-info">Control Status Mesin (Manual)</h6>
        </a>
        <div class="collapse" id="collapseMachineStatus">
            <div class="card-body">
                <form action="{{ route('machine-status.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="machine">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Pilih Mesin</label>
                            <select name="number" class="form-control form-control-sm" required>
                                <option value="">- Pilih Mesin -</option>
                                @for($i = 1; $i <= 19; $i++)
                                    @if($i != 13)
                                        <option value="{{ $i }}">MESIN-{{ $i }}</option>
                                    @endif
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Status</label>
                            <select name="status" class="form-control form-control-sm" required>
                                <option value="normal">NORMAL (Auto)</option>
                                <option value="maintenance">GANTI MOLD/SETTING (Kuning)</option>
                                <option value="stopped">STAND BY (Hitam)</option>
                                <option value="trouble">TROUBLE (Merah)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Keterangan (Optional)</label>
                            <input type="text" name="description" class="form-control form-control-sm"
                                placeholder="Keterangan...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-info btn-sm btn-block">
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
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet Inprocess</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('in_process.store') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <tr class="text-center">
                            <th rowspan="2" style="align-middle">Standard</th>
                            <th rowspan="2" style="align-middle">Item Part</th>
                            <th rowspan="2" style="align-middle">Tanggal / Shift</th>
                            <th rowspan="2" style="align-middle">Total Qty</th>
                            <th rowspan="2" style="align-middle">Sampling Qty</th>
                            <th rowspan="2" style="align-middle">Check Dimensi</th>
                            <th rowspan="2" style="align-middle">Jenis (OK/NG) & Detail NG</th>
                            <th rowspan="2" style="align-middle">Total (OK/NG)</th>
                            <th rowspan="2" style="align-middle">Judgment</th>
                            <th rowspan="2" style="align-middle">Inisial QC</th>
                            <th rowspan="2" style="align-middle">Keterangan</th>
                        </tr>
                        <tbody>
                            <tr>
                                <!-- Ilustrasi Barang -->
                                <td class="align-middle text-center" id="imageContainer">
                                    <div
                                        style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-image fa-2x text-gray-300"></i>
                                    </div>
                                </td>

                                <!-- Pilihan Barang -->
                                <td class="align-middle">
                                    <select class="form-control" name="item_id" id="itemSelect" required
                                        style="min-width: 300px;">
                                        <option value="" disabled selected style="font-weight: bold; color: #6c757d;">Pilih
                                            Item Part</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}"
                                                data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                data-name="{{ $item->name }}" data-part-number="{{ $item->part_number }}"
                                                data-description="{{ $item->description }}"
                                                data-defects="{{ json_encode($item->defects) }}">
                                                {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Tanggal / Shift -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label class="sr-only">Tanggal</label>
                                        <input type="date" class="form-control" style="min-width: 150px; font-size: 16px;"
                                            name="date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="sr-only">Shift</label>
                                        <select class="form-control" style="min-width: 100px; font-size: 16px;" name="shift"
                                            required>
                                            <option value="1">Shift 1</option>
                                            <option value="2">Shift 2</option>
                                            <option value="3">Shift 3</option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label class="sr-only">No Mesin</label>
                                        <select name="code_machine" id="code_machine" class="form-control"
                                            style="min-width: 100px; font-size: 16px;" required>
                                            <option value="">Pilih Mesin</option>
                                            @for ($i = 1; $i <= 19; $i++)
                                                @if($i != 13)
                                                    <option value="{{ $i }}">Mesin {{ $i }}</option>
                                                @endif
                                            @endfor
                                        </select>
                                    </div>
                                </td>

                                <!-- Total Quality (Total Quantity produced) -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center"
                                        style="min-width: 100px; font-size: 16px;" name="total_qty" placeholder="0" min="0"
                                        required>
                                </td>

                                <!-- Sampling Check Quantity -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center"
                                        style="min-width: 100px; font-size: 16px;" name="sampling_qty" placeholder="0"
                                        min="0" required>
                                </td>

                                <!-- Check Dimensi (Cavity & Points) -->
                                <td class="align-middle">
                                    <table class="table table-sm table-bordered mb-0" style="min-width: 800px;">
                                        <thead class="text-center">
                                            <tr>
                                                <th style="width: 25%;">Cavity</th>
                                                <th>Point 1</th>
                                                <th>Point 2</th>
                                                <th>Point 3</th>
                                                <th>Point 4</th>
                                                <th>Point 5</th>
                                                <th>Point 6</th>
                                                <th>Point 7</th>
                                                <th>Point 8</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 1; $i <= 8; $i++)
                                                <tr>
                                                    <td class="text-center font-weight-bold">Cav {{ $i }}</td>
                                                    @for ($j = 1; $j <= 8; $j++)
                                                        <td>
                                                            <input type="text" class="form-control"
                                                                style="min-width: 80px; font-size: 16px;"
                                                                name="dimensions[{{ $i }}][{{ $j }}]" placeholder="Ø{{ $j }}">
                                                        </td>
                                                    @endfor
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </td>

                                <!-- Jenis (OK/NG) & Detail Varian NG -->
                                <td class="align-middle">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="check_ok" value="1"
                                            id="checkOK">
                                        <label class="form-check-label text-success font-weight-bold" for="checkOK">OK
                                            (Pass)</label>
                                    </div>
                                    <hr class="my-2">
                                    <small class="font-weight-bold text-secondary">Defect List (NG):</small>
                                    <div id="defectContainer">
                                        <div class="input-group mb-2 defect-row">
                                            <select class="form-control defect-select"
                                                style="min-width: 120px; font-size: 16px;" name="defect_types[]"
                                                id="defectSelect">
                                                <option value="">-- Pilih Defect --</option>
                                            </select>
                                            <input type="number" class="form-control defect-qty"
                                                style="min-width: 80px; font-size: 16px;" name="defect_quantities[]"
                                                placeholder="Qty" min="1">
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-info mt-1"
                                        style="display: none;">
                                        <i class="fas fa-plus"></i> Tambah Jenis
                                    </button>
                                </td>

                                <!-- Total OK / NG -->
                                <td class="align-middle" style="min-width: 120px;">
                                    <div class="row no-gutters mb-1">
                                        <div
                                            class="col-4 text-center bg-success text-white py-1 rounded-left small font-weight-bold">
                                            OK</div>
                                        <div class="col-8">
                                            <input type="number"
                                                class="form-control form-control-sm rounded-0 rounded-right text-center"
                                                style="font-size: 14px;" name="total_ok" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                    <div class="row no-gutters">
                                        <div
                                            class="col-4 text-center bg-danger text-white py-1 rounded-left small font-weight-bold">
                                            NG</div>
                                        <div class="col-8">
                                            <input type="number"
                                                class="form-control form-control-sm rounded-0 rounded-right text-center"
                                                style="font-size: 14px;" name="total_ng" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle">
                                    <select class="form-control font-weight-bold" name="judgment" id="judgmentSelect"
                                        required>
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
                                    <input type="text" class="form-control text-center"
                                        style="min-width: 80px; font-size: 16px;" name="operator_initials"
                                        placeholder="Inisial" value="{{ $initial }}" required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next
                                            Proses:</label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            <option value="HOLD">HOLD</option>
                                            <option value="REPAIR">REPAIR</option>
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="remarks" rows="4"
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
    </div>
    </div>

    <!-- Image Modal -->
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

    <!-- PDF Modal (Added) -->
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

            // --- Existing Logic ---
            // AQL 0.65 Logic
            function getSampleSize(lotSize) {
                if (lotSize >= 500001) return 1250;
                if (lotSize >= 150001) return 800;
                if (lotSize >= 35001) return 500;
                if (lotSize >= 10001) return 315;
                if (lotSize >= 3201) return 200;
                if (lotSize >= 1201) return 125;
                if (lotSize >= 501) return 80;
                if (lotSize >= 281) return 50;
                if (lotSize >= 151) return 32;
                if (lotSize >= 20) return 20;
                return lotSize;
            }

            // AQL 0.65 Standard Table (Acc/Rej Limits)
            function getAqlLimits(sampleSize) {
                // Mapping based on standard AQL 0.65 (General Inspection Level II)
                // The user requested details if LOT SIZE check exceeds standard. 
                // We interpret this as ensuring the logic handles any sample size correctly.
                if (sampleSize >= 1250) return { acc: 14, rej: 15 };
                if (sampleSize >= 800) return { acc: 10, rej: 11 };
                if (sampleSize >= 500) return { acc: 7, rej: 8 };
                if (sampleSize >= 315) return { acc: 5, rej: 6 };
                if (sampleSize >= 200) return { acc: 3, rej: 4 };
                if (sampleSize >= 125) return { acc: 2, rej: 3 };
                if (sampleSize >= 80) return { acc: 1, rej: 2 };
                // For smaller samples (20, 32, 50), AQL 0.65 is very strict (Acc 0, Rej 1)
                if (sampleSize >= 20) return { acc: 0, rej: 1 };

                // Fallback for very small custom samples
                return { acc: 0, rej: 1 };
            }

            $('input[name="total_qty"]').on('input', function () {
                var lotSize = parseInt($(this).val()) || 0;
                var sampleSize = getSampleSize(lotSize);
                $('input[name="sampling_qty"]').val(sampleSize).trigger('input');
            });

            function updateJudgment() {
                var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
                var ng = parseInt($('input[name="total_ng"]').val()) || 0;
                var isDimensiInvalid = $('.is-invalid').length > 0;

                // 1. Calculate Total OK
                if (sampling >= ng) {
                    $('input[name="total_ok"]').val(sampling - ng);
                } else {
                    $('input[name="total_ok"]').val(Math.max(0, sampling - ng));
                }

                // 2. Determine Acc/Rej Limits
                var limits = getAqlLimits(sampling);
                $('#acc_val').text(limits.acc);
                $('#rej_val').text(limits.rej);
                $('#aql_info').show();

                // 3. Auto-Judgment Logic
                var judgmentSelect = $('#judgmentSelect');
                if (ng > 0 || sampling > 0 || isDimensiInvalid) { // Only judge if there is data
                    if (isDimensiInvalid || ng >= limits.rej) {
                        judgmentSelect.val('NG');
                        judgmentSelect.removeClass('text-success').addClass('text-danger');
                    } else if (ng <= limits.acc) {
                        judgmentSelect.val('OK');
                        judgmentSelect.removeClass('text-danger').addClass('text-success');
                    } else {
                        judgmentSelect.val('NG'); // Fail safe
                        judgmentSelect.removeClass('text-success').addClass('text-danger');
                    }
                } else {
                    judgmentSelect.val('');
                    judgmentSelect.removeClass('text-success text-danger');
                }

                // Show/Hide Next Proses dropdown based on judgment
                toggleNextProsesDropdown();
            }

            function toggleNextProsesDropdown() {
                var judgment = $('#judgmentSelect').val();
                if (judgment === 'NG') {
                    $('#nextProsesContainer').slideDown();
                } else {
                    $('#nextProsesContainer').slideUp();
                    $('#nextProses').val(''); // Reset selection
                }
            }

            $('input[name="total_ng"], input[name="sampling_qty"]').on('input', function () {
                updateJudgment();
            });

            // Also trigger on manual judgment change
            $('#judgmentSelect').on('change', function () {
                toggleNextProsesDropdown();
            });

            // Store default defects for fallback
            var defaultDefects = [
                { value: 'scratch', text: 'BARET' },
                { value: 'silver', text: 'SILVER' },
                { value: 'flow', text: 'FLOW' },
                { value: 'flash', text: 'FLASH' },
                { value: 'shoot_mold', text: 'SHOOT MOLD' },
                { value: 'bending', text: 'BENDING' },
                { value: 'sinkmark', text: 'SINKMARK' },
                { value: 'dimension', text: 'Dimensi' }
            ];

            // Update Image/PDF and Defects on Select Change
            $('#itemSelect').change(function () {
                var selectedOption = $(this).find('option:selected');
                var imageUrl = selectedOption.data('image');
                var fileUrl = selectedOption.data('file');
                var name = selectedOption.data('name');
                var description = selectedOption.data('description');
                var defectsData = selectedOption.data('defects');

                var container = $('#imageContainer');
                var htmlContent = '';

                // Prioritize PDF if available, or show both, or toggle. 
                // Request said "tampilkan juga" (display also). 
                // Given the small space (100x100), maybe show an icon that opens the PDF modal.

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

                // Update Defect Dropdown
                // Reset to single row first
                $('#defectContainer').html('<div class="input-group mb-2 defect-row">' +
                    '<select class="form-control defect-select" name="defect_types[]" id="defectSelect">' +
                    '<option value="">-- Pilih Defect --</option>' +
                    '</select>' +
                    '<input type="number" class="form-control defect-qty" name="defect_quantities[]" placeholder="Qty" min="1" style="max-width: 80px;">' +
                    '</div>');

                var defectSelect = $('#defectSelect');
                // defectSelect.empty(); // No need
                // defectSelect.append('<option value="">-- Pilih Defect --</option>');

                // Defensive parse: handle if defectsData is a string
                if (typeof defectsData === 'string') {
                    try {
                        defectsData = JSON.parse(defectsData);
                    } catch (e) {
                        console.error('Error parsing defects data', e);
                        defectsData = [];
                    }
                }

                if (Array.isArray(defectsData) && defectsData.length > 0) {
                    // Use specific defects from item
                    $.each(defectsData, function (index, value) {
                        defectSelect.append('<option value="' + value + '">' + value + '</option>');
                    });
                } else {
                    // Use default defects
                    $.each(defaultDefects, function (index, defect) {
                        defectSelect.append('<option value="' + defect.value + '">' + defect.text + '</option>');
                    });
                }

                calculateTotalNG();
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

            $('#checkOK').change(function () {
                if ($(this).is(':checked')) {
                    $('select[name="judgment"]').val('OK');
                    // We don't clear defect selects here anymore because user might want to log 'minor' defects even if overall OK
                    // OR more likely, if OK, there are no defects. But let's leave that to user discretion or specific requirement.
                    // The previous code cleared #defectSelect. With multiple rows, clearing all might be annoying if accidental click.
                    // For now, removing the clearing logic or just clearing the first one?
                    // Let's stick to minimal interference: just set Judgment OK.
                }
            });

            // Add Defect Button Logic
            $('#addDefectBtn').click(function () {
                var rowCount = $('.defect-row').length;
                if (rowCount < 4) {
                    var firstSelect = $('#defectSelect'); // The original one
                    var newRow = $('<div class="input-group mb-2 defect-row">' +
                        '<select class="form-control defect-select" style="min-width: 120px; font-size: 16px;" name="defect_types[]">' +
                        firstSelect.html() +
                        '</select>' +
                        '<input type="number" class="form-control defect-qty" style="min-width: 80px; font-size: 16px;" name="defect_quantities[]" placeholder="Qty" min="1">' +
                        '<div class="input-group-append">' +
                        '<button class="btn btn-danger btn-sm remove-defect-btn" type="button"><i class="fas fa-minus"></i></button>' +
                        '</div>' +
                        '</div>');
                    $('#defectContainer').append(newRow);
                }
                if ($('.defect-row').length >= 4) {
                    $(this).hide();
                }
            });

            // Calculate Total NG from Defect Quantities
            function calculateTotalNG() {
                var total = 0;
                $('.defect-qty').each(function () {
                    var qty = parseInt($(this).val()) || 0;
                    total += qty;
                });
                $('input[name="total_ng"]').val(total).trigger('input');
            }

            // Listener for defect qty changes
            $(document).on('input', '.defect-qty', function () {
                calculateTotalNG();
            });

            // Remove Defect Button Logic
            $(document).on('click', '.remove-defect-btn', function () {
                $(this).closest('.defect-row').remove();
                calculateTotalNG();
                if ($('.defect-row').length < 4) {
                    $('#addDefectBtn').show();
                }
            });

            // Toggle "Add Defect" button based on NG count >= 1
            $('input[name="total_ng"]').on('input', function () {
                var ng = parseInt($(this).val()) || 0;
                if (ng >= 1) {
                    if ($('.defect-row').length < 4) {
                        $('#addDefectBtn').show();
                    }
                } else {
                    $('#addDefectBtn').hide();
                }
            });

            // --- Timer Logic (Cycle Time) ---
            var timerInterval = null;
            var totalSeconds = 0;
            var timerRunning = false;

            function updateTimerDisplay() {
                var hours = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds % 3600) / 60);
                var seconds = totalSeconds % 60;

                var text =
                    (hours < 10 ? "0" + hours : hours) + ":" +
                    (minutes < 10 ? "0" + minutes : minutes) + ":" +
                    (seconds < 10 ? "0" + seconds : seconds);

                $('#timerDisplay').text(text);
                $('#cycleTimeInput').val(totalSeconds);
            }

            $('#startTimerBtn').click(function () {
                if (!timerRunning) {
                    timerRunning = true;
                    $(this).removeClass('btn-success').addClass('btn-secondary').attr('disabled', true).html('<i class="fas fa-clock"></i> Running...');

                    // Instead of enabling directly, check state
                    checkSaveButtonState();

                    timerInterval = setInterval(function () {
                        totalSeconds++;
                        updateTimerDisplay();
                    }, 1000);
                }
            });

            // Stop timer on form submit
            $('form').on('submit', function () {
                if (timerRunning) {
                    clearInterval(timerInterval);
                    timerRunning = false;
                    // Update final value
                    $('#cycleTimeInput').val(totalSeconds);
                }
            });

            // Optional: Reset timer on form reset
            $('button[type="reset"]').click(function () {
                clearInterval(timerInterval);
                timerRunning = false;
                totalSeconds = 0;
                updateTimerDisplay();
                $('#startTimerBtn').removeClass('btn-secondary').addClass('btn-success').removeAttr('disabled').html('<i class="fas fa-play"></i> Start');
            });

            // --- Centralized Dimension Validation Logic ---
            // The dimension standards are now passed from the controller.
            const partDimensionStandards = JSON.parse('{!! $partDimensionStandards !!}');

            function checkSaveButtonState() {
                // Check if timer is running (or has been started, implying we can save)
                // The existing logic sets timerRunning = true when started.
                // But we need to make sure we don't enable it if timer hasn't started.
                if (!timerRunning) {
                    $('#saveBtn').prop('disabled', true);
                    return;
                }

                // Check if at least one required dimension is filled
                const selectedOption = $('#itemSelect').find('option:selected');
                const itemPartNumber = selectedOption.data('part-number');
                const dimensionStandards = partDimensionStandards[itemPartNumber];

                let anyRequiredFilled = false;
                let hasStandards = false;

                if (dimensionStandards) {
                    // Check if the part has any standards defined
                    // If it's an object/array, verify it has keys/items
                    if (Object.keys(dimensionStandards).length > 0) {
                        hasStandards = true;
                    }

                    if (hasStandards) {
                        $('input[name^="dimensions"]').each(function () {
                            const name = $(this).attr('name');
                            const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                            if (!match) return;

                            const point = match[2];
                            const standard = dimensionStandards[point];

                            // If a standard exists for this point, check if it has a value
                            if (standard) {
                                if ($(this).val().trim() !== '') {
                                    anyRequiredFilled = true;
                                    return false; // Break the loop, we found one
                                }
                            }
                        });
                    } else {
                        // No standards for this part, so dimension check is not applicable/mandatory
                        // Treat as "filled" (or not required)
                        anyRequiredFilled = true;
                    }
                } else {
                    // No standards data found at all for this part
                    anyRequiredFilled = true;
                }

                // Enable save button only if timer is running AND at least one required field is filled (if standards exist)
                if (anyRequiredFilled) {
                    $('#saveBtn').prop('disabled', false);
                } else {
                    $('#saveBtn').prop('disabled', true);
                }
            }

            function validateDimensions() {
                const selectedOption = $('#itemSelect').find('option:selected');
                const itemPartNumber = selectedOption.data('part-number');

                // Get the dimension standards for the currently selected item.
                const dimensionStandards = partDimensionStandards[itemPartNumber];

                // If no specific standards exist for this part, clear any validation and exit.
                if (!dimensionStandards) {
                    $('input[name^="dimensions"]').removeClass('is-invalid');
                    updateJudgment();
                    checkSaveButtonState();
                    return;
                }

                $('input[name^="dimensions"]').each(function () {
                    const name = $(this).attr('name');
                    // Extracts the point number from the input name (e.g., dimensions[1][2] -> '2').
                    const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                    if (!match) return;

                    const point = match[2]; // The point number (e.g., '1', '2', '3').
                    // Look up the standard for the current point for the selected part.
                    const standard = dimensionStandards[point];
                    const valStr = $(this).val().trim();
                    const value = parseFloat(valStr); // The user-entered dimension value.

                    // Check if a standard exists for this point and the input is a valid number.
                    if (standard && valStr !== '' && !isNaN(value)) {
                        // Calculate the valid range.
                        const lowerBound = standard.size - standard.tolerance;
                        const upperBound = standard.size + standard.tolerance;

                        // Compare the entered value against the valid range.
                        if (value < lowerBound || value > upperBound) {
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Trigger judgment update to reflect dimension status
                updateJudgment();
                checkSaveButtonState();
            }

            $(document).on('input', 'input[name^="dimensions"]', validateDimensions);
            $('#itemSelect').on('change', function () {
                $('input[name^="dimensions"]').removeClass('is-invalid').val('');
                validateDimensions();
            });

            // Add CSS for invalid inputs
            $('<style>.is-invalid { border-color: #dc3545 !important; background-color: #f8d7da !important; }</style>').appendTo('head');
        });
    </script>
@endpush