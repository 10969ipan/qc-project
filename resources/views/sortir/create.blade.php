@extends('layouts.admin')

@section('title', 'Input Data Sortir')

@section('content')
    <x-plant-header title="Input Data Sortir" :plant="request('plant')" />
    @php
        $plant = strtolower(optional(auth()->user()->plant)->code ?? request('plant') ?? '');
        $tableOptions = range(1, 15);
        if ($plant === 'jakarta') {
            $tableOptions = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Sortir (Item NG dari Sub Assy, In-Process & Cross Cut)
            </h6>
        </div>
        <div class="card-body">
            <!-- Plant Selector for Admin -->
            @if(auth()->user()->role === 'admin')
                <form method="GET" action="{{ route('sortir.create') }}" class="mb-3">
                    <div class="form-group row">
                        <label for="plant" class="col-sm-2 col-form-label font-weight-bold">Pilih Plant:</label>
                        <div class="col-sm-4">
                            <select name="plant" id="plant" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Semua Plant --</option>
                                <option value="karawang" {{ request('plant') == 'karawang' ? 'selected' : '' }}>Karawang</option>
                                <option value="jakarta" {{ request('plant') == 'jakarta' ? 'selected' : '' }}>Jakarta</option>
                            </select>
                            <small class="text-muted">Pilih plant untuk memfilter daftar item NG.</small>
                        </div>
                    </div>
                </form>
            @endif

            <form action="{{ route('sortir.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <input type="hidden" name="source_type" id="sourceType">
                <input type="hidden" name="source_id" id="sourceId">

                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <tr class="text-center">
                            <th rowspan="2" style="width: 120px;">Standard</th>
                            <th rowspan="2">Item Part (NG)</th>
                            <th rowspan="2">Tanggal / Shift</th>
                            <th rowspan="2">Total Qty</th>
                            <th rowspan="2">Sampling Qty</th>
                            <th rowspan="2" style="min-width: 280px;">Jenis (OK/NG) & Detail NG</th>
                            <th rowspan="2">Total (OK/NG)</th>
                            <th rowspan="2">Judgment</th>
                            <th rowspan="2">Inisial QC</th>
                            <th rowspan="2">Keterangan</th>
                        </tr>
                        <tbody>
                            <tr>
                                <!-- Standard (PDF) -->
                                <td class="align-middle text-center" id="imageContainer">
                                    <div
                                        style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-image fa-2x text-gray-300"></i>
                                    </div>
                                </td>
                                <!-- Item Part (NG Only) -->
                                <td class="align-middle">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part NG</label>
                                        <select class="form-control" name="item_id" id="ngItemSelect" required
                                            style="min-width: 300px;">
                                            <option value="" disabled selected>Pilih Item NG</option>
                                            @foreach($ngItems as $ngItem)
                                                <option value="{{ $ngItem['item_id'] }}"
                                                    data-source-type="{{ $ngItem['source_type'] }}"
                                                    data-source-id="{{ $ngItem['source_id'] }}"
                                                    data-source-date="{{ $ngItem['date'] }}"
                                                    data-source-shift="{{ $ngItem['shift'] }}"
                                                    data-remaining-qty="{{ $ngItem['remaining_qty'] }}"
                                                    data-files="{{ json_encode($ngItem['file_paths'] ?? ($ngItem['file_path'] ? [$ngItem['file_path']] : [])) }}">
                                                    {{ $ngItem['item_name'] }} ({{ $ngItem['part_number'] }})
                                                    - {{ strtoupper(str_replace('_', ' ', $ngItem['source_type'])) }}
                                                    @if(!empty($ngItem['next_proses']))
                                                        [{{ $ngItem['next_proses'] }}]
                                                    @endif
                                                    - {{ $ngItem['date'] }} Shift {{ $ngItem['shift'] }}
                                                    | Total: {{ number_format($ngItem['total_qty']) }} pcs
                                                    @if($ngItem['sorted_qty'] > 0)
                                                        | Sudah: {{ number_format($ngItem['sorted_qty']) }}
                                                    @endif
                                                    | Sisa: {{ number_format($ngItem['remaining_qty']) }} pcs
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Hanya menampilkan item NG dengan sisa qty > 0</small>
                                    </div>
                                </td>

                                <!-- Tanggal / Shift -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <input type="date" class="form-control" style="min-width: 110px;" name="date"
                                            value="{{ $defaultDate }}" required>
                                    </div>
                                    <div class="form-group mb-2">
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
                                        <select name="line" class="form-control" style="min-width: 80px;">
                                            <option value="">Pilih Meja (Optional)</option>
                                            @foreach ($tableOptions as $i)
                                                <option value="{{ $i }}">Meja {{ $i }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Total Qty -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="total_qty" placeholder="0" min="0" required>
                                </td>

                                <!-- Sampling Qty -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;"
                                        name="sampling_qty" placeholder="0" min="0" required>
                                </td>

                                <td class="align-middle" style="min-width: 280px;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="check_ok" value="1"
                                            id="checkOK">
                                        <label class="form-check-label text-success font-weight-bold" for="checkOK">OK
                                            (Pass)</label>
                                    </div>
                                    <hr class="my-2">
                                    <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="input-group mb-2 defect-row">
                                            <input type="text" class="form-control" style="min-width: 180px;"
                                                name="defect_types[]" placeholder="Jenis Defect">
                                            <input type="number" class="form-control" style="min-width: 100px;"
                                                name="defect_quantities[]" placeholder="Qty" min="1">
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-info btn-sm mt-1">
                                        <i class="fas fa-plus"></i> Tambah Defect
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

                                <!-- Inisial QC -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" style="min-width: 60px;"
                                        name="operator_initials" placeholder="Inisial"
                                        value="{{ auth()->user()->initials ?? '' }}" required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next Proses:</label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            <option value="CRUSHING">CRUSHING</option>
                                            <option value="SORTIR">SORTIR</option>
                                            <option value="FINISHING">FINISHING</option>
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
                            <i class="fas fa-save fa-sm"></i> Simpan Data Sortir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PDF Viewer Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Standard PDF Viewer</h5>
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
                // Use Laravel route helper to generate robust URL, replacing placeholders with actual values
                const routePattern = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}";
                const url = routePattern.replace('ID_PLACEHOLDER', itemId).replace('INDEX_PLACEHOLDER', index);

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

            $(document).on('click', '.view-pdf-btn', function () {
                currentItemId = $(this).data('id');
                totalPdfFiles = $(this).data('count');
                currentPdfIndex = 0;
                $('#pdfModal').modal('show');
                loadPdf(currentItemId, currentPdfIndex);
            });

            // === INPUT LOCK UNTIL START ===
            // Disable all form inputs in checksheetForm until Start button is clicked
            var formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
            formInputs.prop('disabled', true);
            $('#checksheetForm').addClass('inputs-locked');
            $('<style>#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo('head');

            // AQL 0.65 Logic (Similar to In-Process)
            // In Sortir, we usually sort 100% of the NG lot.
            function getSampleSize(lotSize) {
                return lotSize;
            }

            function getAqlLimits(sampleSize) {
                if (sampleSize >= 1250) return { acc: 14, rej: 15 };
                if (sampleSize >= 800) return { acc: 10, rej: 11 };
                if (sampleSize >= 500) return { acc: 7, rej: 8 };
                if (sampleSize >= 315) return { acc: 5, rej: 6 };
                if (sampleSize >= 200) return { acc: 3, rej: 4 };
                if (sampleSize >= 125) return { acc: 2, rej: 3 };
                if (sampleSize >= 80) return { acc: 1, rej: 2 };
                if (sampleSize >= 20) return { acc: 0, rej: 1 };
                return { acc: 0, rej: 1 };
            }

            function updateJudgment() {
                var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
                var ng = parseInt($('input[name="total_ng"]').val()) || 0;
                var ok = parseInt($('input[name="total_ok"]').val()) || 0;

                // Sortir is 100% check, bypass AQL
                $('#aql_info').hide();

                var judgmentSelect = $('#judgmentSelect');
                if (sampling > 0 || ng > 0) {
                    if (ng > 0) {
                        judgmentSelect.val('NG').removeClass('text-success').addClass('text-danger');
                    } else {
                        judgmentSelect.val('OK').removeClass('text-danger').addClass('text-success');
                    }
                } else {
                    judgmentSelect.val('').removeClass('text-success text-danger');
                }

                toggleNextProsesDropdown();
            }

            // Event Listeners for Auto-fill
            $('input[name="total_qty"]').on('input', function () {
                var lotSize = parseInt($(this).val()) || 0;
                var sampleSize = getSampleSize(lotSize);
                $('input[name="sampling_qty"]').val(sampleSize);
                // Auto fill OK and NG
                $('input[name="total_ok"]').val(sampleSize);
                $('input[name="total_ng"]').val(0);
                $('#checkOK').prop('checked', true);
                updateJudgment();
            });

            $('#checkOK').on('change', function () {
                if ($(this).is(':checked')) {
                    var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
                    $('input[name="total_ok"]').val(sampling);
                    $('input[name="total_ng"]').val(0);
                    updateJudgment();
                }
            });

            // Bi-directional Synchronization
            $('input[name="sampling_qty"]').on('input', function () {
                var sampling = parseInt($(this).val()) || 0;
                var ok = parseInt($('input[name="total_ok"]').val()) || 0;
                // For Sortir, if sampling changes, we assume remaining are NG
                $('input[name="total_ng"]').val(Math.max(0, sampling - ok));
                updateJudgment();
            });

            $('input[name="total_ok"]').on('input', function () {
                var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
                var ok = parseInt($(this).val()) || 0;
                // Update NG based on OK
                $('input[name="total_ng"]').val(Math.max(0, sampling - ok));
                updateJudgment();
            });

            $('input[name="total_ng"]').on('input', function () {
                var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
                var ng = parseInt($(this).val()) || 0;
                // Update OK based on NG
                $('input[name="total_ok"]').val(Math.max(0, sampling - ng));
                updateJudgment();
            });

            // Update defects quantity also triggers NG update
            $(document).on('input', 'input[name="defect_quantities[]"]', function () {
                var totalNG = 0;
                $('input[name="defect_quantities[]"]').each(function () {
                    totalNG += parseInt($(this).val()) || 0;
                });
                // Updating total_ng input directly will trigger its 'input' listener
                $('input[name="total_ng"]').val(totalNG).trigger('input');
            });

            // Auto-fill source type, source id, and total_qty when item is selected
            $('#ngItemSelect').on('change', function () {
                var selectedOption = $(this).find('option:selected');
                $('#sourceType').val(selectedOption.data('source-type'));
                $('#sourceId').val(selectedOption.data('source-id'));

                // Update Image/PDF Container
                var files = selectedOption.data('files');
                var itemId = selectedOption.val();
                var container = $('#imageContainer');

                if (files && files.length > 0) {
                    container.html('<button type="button" class="btn btn-danger btn-sm view-pdf-btn" data-id="' + itemId + '" data-count="' + files.length + '"><i class="fas fa-file-pdf"></i> PDF (' + files.length + ')</button>');
                } else {
                    container.html('<div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');
                }

                // Auto-fill total_qty with remaining qty and set max limit
                var remainingQty = parseInt(selectedOption.data('remaining-qty')) || 0;
                var totalQtyInput = $('input[name="total_qty"]');
                totalQtyInput.val(remainingQty);
                totalQtyInput.attr('max', remainingQty);

                // Also update sampling_qty, total_ok, total_ng
                $('input[name="sampling_qty"]').val(remainingQty);
                $('input[name="total_ok"]').val(remainingQty);
                $('input[name="total_ng"]').val(0);
                $('#checkOK').prop('checked', true);
                updateJudgment();
            });

            // Validate total_qty doesn't exceed remaining qty - show confirmation
            $('input[name="total_qty"]').on('change', function () {
                var input = $(this);
                var max = parseInt(input.attr('max')) || 0;
                var val = parseInt(input.val()) || 0;
                if (max > 0 && val > max) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qty Melebihi Sisa',
                        html: 'Qty yang diinput (<b>' + val + ' pcs</b>) melebihi sisa qty tercatat (<b>' + max + ' pcs</b>).<br><br>Apakah Anda yakin ingin melanjutkan?',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Reset ke Sisa Qty'
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            // User chose to reset
                            input.val(max);
                            // Update dependent fields
                            $('input[name="sampling_qty"]').val(max);
                            $('input[name="total_ok"]').val(max);
                            $('input[name="total_ng"]').val(0);
                            updateJudgment();
                        } else {
                            // User chose to continue, update dependent fields with new value
                            $('input[name="sampling_qty"]').val(val);
                            $('input[name="total_ok"]').val(val);
                            $('input[name="total_ng"]').val(0);
                            updateJudgment();
                        }
                    });
                }
            });

            // Add defect row
            $('#addDefectBtn').on('click', function () {
                var newRow = `
                                                                                                                                                            <div class="input-group mb-2 defect-row">
                                                                                                                                                         <input type="text" class="form-control" style="min-width: 180px;" name="defect_types[]" placeholder="Jenis Defect">
                                                                                                                                                         <input type="number" class="form-control" style="min-width: 100px;" name="defect_quantities[]" placeholder="Qty" min="1">
                                                                                                                                                         <div class="input-group-append">
                                                                                                                                                             <button type="button" class="btn btn-danger btn-sm remove-defect"><i class="fas fa-times"></i></button>
                                                                                                                                                         </div>
                                                                                                                                                     </div>
                                                                                                                                                    `;
                $('#defectContainer').append(newRow);
            });

            // Remove defect row
            $(document).on('click', '.remove-defect', function () {
                $(this).closest('.defect-row').remove();
                // Trigger NG recalculation
                $('input[name="defect_quantities[]"]').first().trigger('input');
            });

            // Timer Logic
            var timerInterval = null;
            var totalSeconds = 0;
            var timerRunning = false;

            function updateTimerDisplay() {
                var hours = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds % 3600) / 60);
                var seconds = totalSeconds % 60;
                var text = [hours, minutes, seconds].map(v => v < 10 ? "0" + v : v).join(":");
                $('#timerDisplay').text(text);
                $('#cycleTimeInput').val(totalSeconds);
            }

            $('#startTimerBtn').on('click', function () {
                if (!timerRunning) {
                    timerRunning = true;
                    $(this).removeClass('btn-success').addClass('btn-secondary').prop('disabled', true);
                    $(this).html('<i class="fas fa-clock"></i> Running...');
                    $('#saveBtn').prop('disabled', false);

                    // === UNLOCK ALL INPUTS ===
                    formInputs.prop('disabled', false);
                    $('form').removeClass('inputs-locked');

                    timerInterval = setInterval(function () {
                        totalSeconds++;
                        updateTimerDisplay();
                    }, 1000);
                }
            });

            // Show/Hide Next Proses dropdown based on judgment
            function toggleNextProsesDropdown() {
                var judgment = $('#judgmentSelect').val();
                if (judgment === 'NG') {
                    $('#nextProsesContainer').slideDown();
                } else {
                    $('#nextProsesContainer').slideUp();
                    $('#nextProses').val('');
                }
            }

            $('#judgmentSelect').on('change', function () {
                toggleNextProsesDropdown();
            });

            $('form').on('submit', function (e) {
                // Validate: If NG, next_proses must be selected
                var judgment = $('#judgmentSelect').val();
                var nextProses = $('#nextProses').val();

                if (judgment === 'NG' && !nextProses) {
                    e.preventDefault();
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
                    $('#cycleTimeInput').val(totalSeconds);
                }
            });
        });
    </script>
@endpush