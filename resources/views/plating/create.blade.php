@extends('layouts.admin')

@section('title', 'Input Data Plating Checksheet')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET OUTGOING PLATING
                        <span class="badge badge-primary d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
                            style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant Karawang
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0213</div>
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
            <h6 class="m-0 font-weight-bold text-primary">Input Data Plating Checksheet</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('plating.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="karawang">

                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr class="text-center">
                                <th rowspan="2" style="vertical-align: middle;">Standard</th>
                                <th rowspan="2" style="vertical-align: middle;">Item Part</th>
                                <th rowspan="2" style="vertical-align: middle;">Injection<br>(Tgl / Shift)</th>
                                <th rowspan="2" style="vertical-align: middle;">Plating<br>(Tgl / Shift / Lot)</th>
                                <th colspan="2" style="vertical-align: middle;">Quality</th>
                                <th rowspan="2" style="vertical-align: middle;">Total Qty (Lot)</th>
                                <th rowspan="2" style="vertical-align: middle; min-width: 250px;">Jenis (OK/NG) & Detail NG
                                </th>
                                <th rowspan="2" style="vertical-align: middle;">Total (OK/NG)</th>
                                <th rowspan="2" style="vertical-align: middle;">Judgment</th>
                                <th rowspan="2" style="vertical-align: middle;">Inisial QC</th>
                                <th rowspan="2" style="vertical-align: middle;">Keterangan</th>
                            </tr>
                            <tr class="text-center">
                                <th class="small py-1" style="vertical-align: middle;">Tanggal / Shift</th>
                                <th class="small py-1" style="vertical-align: middle;">Meja</th>
                            </tr>
                        </thead>
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
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small">Kode SAP</label>
                                        <input type="text" class="form-control form-control-sm" id="sapCodeInput"
                                            placeholder="Kode SAP..." style="min-width: 120px;">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold small">Item Part</label>
                                        <select class="form-control form-control-sm" name="item_id" id="itemSelect" required
                                            style="min-width: 200px;">
                                            <option value="" disabled selected>Pilih Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-standard="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-similar="{{ $item->similar_part_file_path ? route('items.pdf', ['id' => $item->id, 'index' => 'similar']) : '' }}"
                                                    data-name="{{ $item->name }}" data-description="{{ $item->description }}"
                                                    data-defects="{{ json_encode($item->defects) }}"
                                                    data-sap-code="{{ $item->sap_code ?? '' }}">
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Injection -->
                                <td class="align-middle">
                                    <input type="date" class="form-control form-control-sm mb-1" style="min-width: 120px;"
                                        name="injection_date" value="{{ $defaultDate }}" required>
                                    <select class="form-control form-control-sm" name="injection_shift" required>
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </td>

                                <!-- Plating -->
                                <td class="align-middle">
                                    <input type="date" class="form-control form-control-sm mb-1" style="min-width: 120px;"
                                        name="plating_date" value="{{ $defaultDate }}" required>
                                    <select class="form-control form-control-sm mb-1" name="plating_shift" required>
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                    <input type="text" class="form-control form-control-sm" name="no_lot"
                                        placeholder="No Lot...">
                                </td>

                                <!-- Quality (Existing Tanggal/Shift/Meja) -->
                                <td class="align-middle">
                                    <input type="date" class="form-control form-control-sm mb-1" style="min-width: 110px;"
                                        name="date" value="{{ $defaultDate }}" required>
                                    <select class="form-control form-control-sm" name="shift" required>
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </td>
                                <td class="align-middle">
                                    <select name="line" class="form-control form-control-sm" style="min-width: 85px;"
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

                                <td class="align-middle" style="min-width: 280px;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="checkOK">
                                        <label class="form-check-label text-success font-weight-bold" for="checkOK">OK (Pass
                                            All)</label>
                                    </div>
                                    <hr class="my-2">
                                    <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="input-group mb-2 defect-row">
                                            <select class="form-control defect-select" style="min-width: 180px;"
                                                name="defect_types[]" id="defectSelect">
                                                <option value="">-- Pilih Defect --</option>
                                            </select>
                                            <input type="number" class="form-control defect-qty" style="min-width: 100px;"
                                                name="defect_quantities[]" placeholder="Qty" min="1">
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
                                                name="total_ok" placeholder="0" min="0" required readonly>
                                        </div>
                                    </div>
                                    <div class="row no-gutters">
                                        <div
                                            class="col-4 text-center bg-danger text-white py-1 rounded-left small font-weight-bold">
                                            NG</div>
                                        <div class="col-8">
                                            <input type="number"
                                                class="form-control form-control-sm rounded-0 rounded-right text-center"
                                                name="total_ng" id="totalNG" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle">
                                    <select class="form-control font-weight-bold" name="judgment" id="judgmentSelect"
                                        style="min-width: 100px;" required>
                                        <option value="" disabled selected>Result</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                </td>

                                <!-- Inisial QC -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" style="min-width: 80px;"
                                        name="operator_initials" value="{{ auth()->user()->initials ?? '' }}"
                                        placeholder="Inisial" required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger small">Next
                                            Proses:</label>
                                        <select class="form-control form-control-sm" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih --</option>
                                            <option value="CRUSHING">CRUSHING</option>
                                            <option value="SORTIR">SORTIR</option>
                                            <option value="REPAIR">REPAIR</option>
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="remarks" rows="4"
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

    <!-- PDF Side-by-Side Display Section -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-eye mr-2"></i>Reference View</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">STANDARD PCCP</h6>
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
                        <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn" id="fullStandardBtn"
                            style="display:none;">
                            <i class="fas fa-expand"></i> Full
                        </button>
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
                        <button type="button" class="btn btn-sm btn-outline-info view-pdf-btn" id="fullSimilarBtn"
                            style="display:none;">
                            <i class="fas fa-expand"></i> Full
                        </button>
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

    <!-- PDF Modal -->
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

    <!-- Image Modal -->
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
    <script>
        $(document).ready(function () {
            // --- PDF.js Logic ---
            pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/vendor/pdf.worker.min.js') }}";

            // PDF Cache & Render Logic
            const pdfCache = {};

            // State for Reference Views
            let refStandardPdfDoc = null;
            let refStandardPageNum = 1;
            let refStandardFileIndex = 0;
            let refStandardFiles = [];

            let refSimilarPdfDoc = null;
            let refSimilarPageNum = 1;

            let modalPdfDoc = null;
            let modalPageNum = 1;
            let modalPdfScale = 1.0;
            let currentModalType = ''; // 'standard' or 'similar'

            // Render Function
            window.renderPdfToCanvas = function (url, canvasId, placeholderId, loadingId, pageNum = 1) {
                const canvas = document.getElementById(canvasId);
                const ctx = canvas.getContext('2d');
                const $placeholder = $('#' + placeholderId);
                const $loading = $('#' + loadingId);
                const $canvas = $(canvas);

                // Reset UI: Show Loading, Hide others
                $placeholder.removeClass('d-flex').addClass('d-none');
                $canvas.addClass('d-none').hide();
                $loading.removeClass('d-none').addClass('d-flex');

                const renderPageOnCanvas = function (pdf, canvas, ctx, $loading, $canvas, num) {
                    pdf.getPage(num).then(function (page) {
                        const containerWidth = $canvas.parent().width() || 500;
                        const availableWidth = containerWidth - 40;
                        const viewport = page.getViewport({ scale: 1.0 });
                        const scale = availableWidth / viewport.width;
                        const scaledViewport = page.getViewport({ scale: scale });

                        canvas.height = scaledViewport.height;
                        canvas.width = scaledViewport.width;

                        $canvas.css('width', '100%').css('height', 'auto');

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: scaledViewport
                        };

                        page.render(renderContext).promise.then(function () {
                            $loading.removeClass('d-flex').addClass('d-none');
                            $canvas.removeClass('d-none').show();

                            if (canvasId === 'standardPdfCanvas') {
                                refStandardPdfDoc = pdf;
                                $('#standardPageInfo').text('P ' + num + '/' + pdf.numPages);
                                $('#fullStandardBtn').show();
                            } else if (canvasId === 'similarPdfCanvas') {
                                refSimilarPdfDoc = pdf;
                                $('#similarPageInfo').text('P ' + num + '/' + pdf.numPages);
                                $('#fullSimilarBtn').show();
                            } else if (canvasId === 'modalPdfCanvas') {
                                modalPdfDoc = pdf;
                                $('#pageInfo').text('Page ' + num + ' of ' + pdf.numPages);
                            }
                        });
                    });
                };

                if (pdfCache[url]) {
                    renderPageOnCanvas(pdfCache[url], canvas, ctx, $loading, $canvas, pageNum);
                } else {
                    pdfjsLib.getDocument(url).promise.then(function (pdf) {
                        pdfCache[url] = pdf;
                        renderPageOnCanvas(pdf, canvas, ctx, $loading, $canvas, pageNum);
                    }).catch(function (error) {
                        $loading.removeClass('d-flex').addClass('d-none');
                        $placeholder.removeClass('d-none').addClass('d-flex').find('p').text('Gagal memuat PDF: ' + (error.message || 'Unknown error'));
                    });
                }
            };
            // === INPUT LOCK UNTIL START (SUB ASSY LOGIC) ===
            var formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
            formInputs.prop('disabled', true);
            $('#checksheetForm').addClass('inputs-locked');
            $('<style>#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo('head');

            // Timer Variables
            var timerInterval = null;
            var totalSeconds = 0;
            var timerRunning = false;

            function updateTimerDisplay() {
                var hours = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds % 3600) / 60);
                var seconds = totalSeconds % 60;
                var text = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
                $('#timerDisplay').text(text);
                $('#cycleTimeInput').val(totalSeconds);
            }

            // Start Button Logic
            $('#startTimerBtn').click(function () {
                if (!timerRunning) {
                    timerRunning = true;
                    $(this).removeClass('btn-success').addClass('btn-secondary').attr('disabled', true).html('<i class="fas fa-clock"></i> Running...');
                    $('#saveBtn').prop('disabled', false);

                    // === UNLOCK ALL INPUTS ===
                    formInputs.prop('disabled', false);
                    $('#checksheetForm').removeClass('inputs-locked');

                    // Specific readonly for Plating
                    $('input[name="total_ok"]').prop('readonly', true);

                    timerInterval = setInterval(function () {
                        totalSeconds++;
                        updateTimerDisplay();
                    }, 1000);

                    $('#itemSelect').focus();
                }
            });

            // Auto Judgment & OK/NG Calculation
            $('#totalNG, #totalQty').on('input', function () {
                let total = parseInt($('#totalQty').val()) || 0;
                let ng = parseInt($('#totalNG').val()) || 0;
                let ok = total - ng;
                $('input[name="total_ok"]').val(ok < 0 ? 0 : ok);

                if (total > 0 || ng > 0) {
                    $('#judgmentSelect').val(ng > 0 ? 'NG' : 'OK').trigger('change');
                } else {
                    $('#judgmentSelect').val('');
                }
            });

            $('#judgmentSelect').change(function () {
                if ($(this).val() === 'NG') {
                    $('#nextProsesContainer').slideDown();
                } else {
                    $('#nextProsesContainer').slideUp();
                }
            });

            $('#checkOK').change(function () {
                if ($(this).is(':checked')) {
                    $('#totalNG').val(0).trigger('input');
                    $('#defectContainer').find('.defect-row').not(':first').remove();
                    $('.defect-select').val('');
                    $('.defect-qty').val('');
                    $('#judgmentSelect').val('OK').trigger('change');
                }
            });

            // SAP Code Auto-Selection
            $('#sapCodeInput').on('input', function () {
                var sapCode = $(this).val().trim();
                if (sapCode.length >= 1) {
                    var matchedOption = $('#itemSelect option').filter(function () {
                        var itemSapCode = $(this).data('sap-code');
                        return itemSapCode && itemSapCode.toString().toLowerCase() === sapCode.toLowerCase();
                    });
                    if (matchedOption.length > 0) {
                        $('#itemSelect').val(matchedOption.val()).trigger('change');
                        $('#sapCodeInput').removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#sapCodeInput').removeClass('is-valid').addClass('is-invalid');
                    }
                } else {
                    $('#sapCodeInput').removeClass('is-valid is-invalid');
                }
            });

            // Item Selection - load image and defects
            $('#itemSelect').change(function () {
                var selected = $(this).find('option:selected');
                var img = selected.data('image');
                var name = selected.data('name');
                var desc = selected.data('description');
                var defects = selected.data('defects');

                if (img) {
                    $('#imageContainer').html(`<img src="${img}" class="img-thumbnail" style="max-width:100px; cursor:pointer;" data-toggle="modal" data-target="#imageModal" onclick="$('#modalImage').attr('src', '${img}'); $('#modalTitle').text('${name}'); $('#modalDescription').text('${desc}');">`);
                } else {
                    $('#imageContainer').html('<div style="width:100px; height:100px; background-color:#f8f9fa; border:1px solid #dee2e6; display:flex; align-items:center; justify-content:center; margin:0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');
                }

                // Reset and populate defect selection
                var defectSelect = $('#defectSelect');
                defectSelect.html('<option value="">-- Pilih Defect --</option>');

                if (typeof defects === 'string') {
                    try { defects = JSON.parse(defects); } catch (e) { defects = null; }
                }

                if (Array.isArray(defects) && defects.length > 0) {
                    defects.forEach(d => defectSelect.append(`<option value="${d}">${d}</option>`));
                } else {
                    const defaultDefects = ['BARET', 'SILVER', 'FLOW', 'FLASH', 'KOTOR', 'DENYUT'];
                    defaultDefects.forEach(d => defectSelect.append(`<option value="${d}">${d}</option>`));
                }
                // PDF Logic
                var standardFilesJson = selected.data('files');
                refStandardFiles = [];
                try {
                    refStandardFiles = typeof standardFilesJson === 'string' ? JSON.parse(standardFilesJson) : (standardFilesJson || []);
                } catch (e) { console.error("Error parsing files", e); }

                var standardUrl = selected.data('standard');
                var similarUrl = selected.data('similar');

                // Standard PDF
                if (standardUrl && refStandardFiles.length > 0) {
                    refStandardFileIndex = 0;
                    refStandardPageNum = 1;
                    window.renderPdfToCanvas(standardUrl, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', 1);
                    $('.standard-nav-controls').show();
                    if (refStandardFiles.length > 1) {
                        $('.standard-nav-controls .file-nav').show();
                        $('#standardFileInfo').text('1/' + refStandardFiles.length);
                    } else {
                        $('.standard-nav-controls .file-nav').hide();
                    }
                } else {
                    $('#standardPdfCanvas').hide();
                    $('#standardPdfPlaceholder').show().find('p').text('Standard PDF tidak tersedia');
                    $('.standard-nav-controls').hide();
                    $('#fullStandardBtn').hide();
                }

                // Similar PDF
                if (similarUrl) {
                    refSimilarPageNum = 1;
                    window.renderPdfToCanvas(similarUrl, 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading', 1);
                    $('.similar-nav-controls').show();
                    $('#similarStatusText').text('');
                } else {
                    $('#similarPdfCanvas').hide();
                    $('#similarPdfPlaceholder').show().find('p').text('Similar Part tidak tersedia');
                    $('.similar-nav-controls').hide();
                    $('#fullSimilarBtn').hide();
                }

                $('#addDefectBtn').show();
            });

            // Navigation Handlers
            $('#prevStandardPage').click(function () {
                if (refStandardPageNum > 1) {
                    refStandardPageNum--;
                    window.renderPdfToCanvas($('#itemSelect').find('option:selected').data('standard'), 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', refStandardPageNum);
                }
            });
            $('#nextStandardPage').click(function () {
                if (refStandardPdfDoc && refStandardPageNum < refStandardPdfDoc.numPages) {
                    refStandardPageNum++;
                    window.renderPdfToCanvas($('#itemSelect').find('option:selected').data('standard'), 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', refStandardPageNum);
                }
            });

            $('#prevStandardFile').click(function () {
                if (refStandardFileIndex > 0) {
                    refStandardFileIndex--;
                    refStandardPageNum = 1;
                    const itemId = $('#itemSelect').val();
                    const url = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}"
                        .replace('ID_PLACEHOLDER', itemId).replace('INDEX_PLACEHOLDER', refStandardFileIndex);
                    window.renderPdfToCanvas(url, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', 1);
                    $('#standardFileInfo').text((refStandardFileIndex + 1) + '/' + refStandardFiles.length);
                }
            });

            $('#nextStandardFile').click(function () {
                if (refStandardFileIndex < refStandardFiles.length - 1) {
                    refStandardFileIndex++;
                    refStandardPageNum = 1;
                    const itemId = $('#itemSelect').val();
                    const url = "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}"
                        .replace('ID_PLACEHOLDER', itemId).replace('INDEX_PLACEHOLDER', refStandardFileIndex);
                    window.renderPdfToCanvas(url, 'standardPdfCanvas', 'standardPdfPlaceholder', 'standardPdfLoading', 1);
                    $('#standardFileInfo').text((refStandardFileIndex + 1) + '/' + refStandardFiles.length);
                }
            });

            $('#prevSimilarPage').click(function () {
                if (refSimilarPageNum > 1) {
                    refSimilarPageNum--;
                    window.renderPdfToCanvas($('#itemSelect').find('option:selected').data('similar'), 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading', refSimilarPageNum);
                }
            });
            $('#nextSimilarPage').click(function () {
                if (refSimilarPdfDoc && refSimilarPageNum < refSimilarPdfDoc.numPages) {
                    refSimilarPageNum++;
                    window.renderPdfToCanvas($('#itemSelect').find('option:selected').data('similar'), 'similarPdfCanvas', 'similarPdfPlaceholder', 'similarPdfLoading', refSimilarPageNum);
                }
            });

            // Modal Logic
            function renderModalPage(num) {
                const canvas = document.getElementById('modalPdfCanvas');
                const ctx = canvas.getContext('2d');
                modalPdfDoc.getPage(num).then(function (page) {
                    const viewport = page.getViewport({ scale: modalPdfScale });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    const renderContext = { canvasContext: ctx, viewport: viewport };
                    page.render(renderContext).promise.then(function () {
                        $('#pageInfo').text('Page ' + num + ' of ' + modalPdfDoc.numPages);
                    });
                });
            }

            $('.view-pdf-btn').click(function () {
                currentModalType = $(this).attr('id') === 'fullStandardBtn' ? 'standard' : 'similar';
                modalPdfDoc = currentModalType === 'standard' ? refStandardPdfDoc : refSimilarPdfDoc;
                modalPageNum = currentModalType === 'standard' ? refStandardPageNum : refSimilarPageNum;
                modalPdfScale = 1.5;

                $('#pdfModal').modal('show');

                if (currentModalType === 'standard') {
                    if (refStandardFiles.length > 1) {
                        $('#prevPdf, #nextPdf').show();
                        $('#pdfInfo').text('File ' + (refStandardFileIndex + 1) + ' of ' + refStandardFiles.length);
                    } else {
                        $('#prevPdf, #nextPdf').hide();
                        $('#pdfInfo').text('Standard PDF');
                    }
                } else {
                    $('#prevPdf, #nextPdf').hide();
                    $('#pdfInfo').text('Similar Part PDF');
                }

                setTimeout(() => renderModalPage(modalPageNum), 200);
            });

            $('#prevPage').click(function () { if (modalPageNum > 1) { modalPageNum--; renderModalPage(modalPageNum); } });
            $('#nextPage').click(function () { if (modalPageNum < modalPdfDoc.numPages) { modalPageNum++; renderModalPage(modalPageNum); } });
            $('#pdfZoomIn').click(function () { modalPdfScale += 0.25; renderModalPage(modalPageNum); });
            $('#pdfZoomOut').click(function () { if (modalPdfScale > 0.5) { modalPdfScale -= 0.25; renderModalPage(modalPageNum); } });
            $('#pdfZoomReset').click(function () { modalPdfScale = 1.5; renderModalPage(modalPageNum); });

            $('#prevPdf').click(function () {
                if (currentModalType === 'standard' && refStandardFileIndex > 0) {
                    $('#prevStandardFile').click();
                    setTimeout(() => {
                        modalPdfDoc = refStandardPdfDoc;
                        modalPageNum = 1;
                        $('#pdfInfo').text('File ' + (refStandardFileIndex + 1) + ' of ' + refStandardFiles.length);
                        renderModalPage(modalPageNum);
                    }, 500);
                }
            });
            $('#nextPdf').click(function () {
                if (currentModalType === 'standard' && refStandardFileIndex < refStandardFiles.length - 1) {
                    $('#nextStandardFile').click();
                    setTimeout(() => {
                        modalPdfDoc = refStandardPdfDoc;
                        modalPageNum = 1;
                        $('#pdfInfo').text('File ' + (refStandardFileIndex + 1) + ' of ' + refStandardFiles.length);
                        renderModalPage(modalPageNum);
                    }, 500);
                }
            });

            $('#addDefectBtn').click(function () {
                let firstSelect = $('#defectSelect');
                let clone = $('<div class="input-group mb-2 defect-row">' +
                    '<select class="form-control defect-select" name="defect_types[]">' + firstSelect.html() + '</select>' +
                    '<input type="number" class="form-control defect-qty" name="defect_quantities[]" placeholder="Qty" min="1">' +
                    '<div class="input-group-append"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-minus"></i></button></div>' +
                    '</div>');
                $('#defectContainer').append(clone);
            });

            $(document).on('click', '.btn-remove-row', function () {
                $(this).closest('.defect-row').remove();
                calculateTotalNG();
            });

            $(document).on('input', '.defect-qty', function () {
                calculateTotalNG();
            });

            function calculateTotalNG() {
                let totalNG = 0;
                $('.defect-qty').each(function () {
                    totalNG += parseInt($(this).val()) || 0;
                });
                $('#totalNG').val(totalNG).trigger('input');
            }

            // Stop timer on form submit & Validate NG
            $('#checksheetForm').on('submit', function (e) {
                e.preventDefault(); // Always prevent default for AJAX

                var judgment = $('#judgmentSelect').val();
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
                    $('#cycleTimeInput').val(totalSeconds);
                }

                // Show loading state
                var saveBtn = $('#saveBtn');
                var originalHtml = saveBtn.html();
                saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

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
                        saveBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            function resetState() {
                clearInterval(timerInterval);
                timerRunning = false;
                totalSeconds = 0;
                updateTimerDisplay();
                $('#startTimerBtn').removeClass('btn-secondary').addClass('btn-success').removeAttr('disabled').html('<i class="fas fa-play"></i> Start');

                // RE-LOCK INPUTS
                formInputs.prop('disabled', true);
                $('#checksheetForm').addClass('inputs-locked');
                $('#saveBtn').prop('disabled', true);

                // Reset specific elements
                $('#addDefectBtn').hide();
                $('#defectContainer').find('.defect-row').not(':first').remove();
                $('#imageContainer').html('<div style="width:100px; height:100px; background-color:#f8f9fa; border:1px solid #dee2e6; display:flex; align-items:center; justify-content:center; margin:0 auto;"><i class="fas fa-image fa-2x text-gray-300"></i></div>');
                $('#itemSelect').val('').trigger('change');
                $('#aql_info').hide();
                $('#nextProsesContainer').hide();

                // Reset PDF views
                $('#standardPdfCanvas, #similarPdfCanvas').hide();
                $('#standardPdfPlaceholder').show().find('p').text('Pilih Item untuk menampilkan Standard PDF');
                $('#similarPdfPlaceholder').show().find('p').text('Pilih Item untuk menampilkan Similar Part');
                $('#similarStatusText').text('');
                $('#fullStandardBtn, #fullSimilarBtn').hide();
                $('.standard-nav-controls, .similar-nav-controls').hide();

                // Reset PDF Reference State
                refStandardPdfDoc = null;
                refStandardPageNum = 1;
                refStandardFileIndex = 0;
                refStandardFiles = [];
                refSimilarPdfDoc = null;
                refSimilarPageNum = 1;
            }
        });
    </script>
@endpush