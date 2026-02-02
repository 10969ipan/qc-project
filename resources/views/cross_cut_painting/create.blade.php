@extends('layouts.admin')

@section('title', 'Input Data Checksheet Painting')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET CROSS CUT PAINTING
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
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0215</div>
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

    @php $hasErrors = isset($errors) && (is_array($errors) ? count($errors) > 0 : $errors->any()); @endphp
    @if ($hasErrors)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach (is_array($errors) ? $errors : $errors->all() as $error)
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
            <h6 class="m-0 font-weight-bold text-primary">Input Data Checksheet Cross Cut Painting</h6>
        </div>
        <div class="card-body">
            @if(auth()->user()->role === 'admin')
                <form method="GET" action="{{ route('cross_cut_painting.create') }}" class="mb-3">
                    <div class="form-group row">
                        <label for="plant" class="col-sm-2 col-form-label font-weight-bold">Pilih Plant:</label>
                        <div class="col-sm-4">
                            <select name="plant" id="plant" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Semua Plant --</option>
                                <option value="karawang" {{ request('plant') == 'karawang' ? 'selected' : '' }}>Karawang</option>
                                <option value="jakarta" {{ request('plant') == 'jakarta' ? 'selected' : '' }}>Jakarta</option>
                            </select>
                        </div>
                    </div>
                </form>
            @endif

            <form action="{{ route('cross_cut_painting.store') }}" method="POST" enctype="multipart/form-data"
                id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr class="text-center">
                                <th>Standard</th>
                                <th>Item Part</th>
                                <th>Tanggal & Shift Produksi / QC</th>
                                <th>Hasil Foto Cross Cut / Tap Test & Pencil Scratch</th>
                                <th>Judgement</th>
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
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part</label>
                                        <select class="form-control" id="item_id" name="item_id" required>
                                            <option value="" disabled selected>Pilih Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? '/' . $item->image_path : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-name="{{ $item->name }}"
                                                    data-description="{{ $item->description ?? '' }}"
                                                    data-sap-code="{{ $item->sap_code ?? '' }}">
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <!-- Dates -->
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
                                <!-- Cross Cut / Tap Test / Pencil Scratch Image -->
                                <td class="align-middle text-center">
                                    <label for="image" class="mb-2 d-block text-primary font-weight-bold">
                                        <i class="fas fa-camera mr-1"></i> AMBIL GAMBAR
                                    </label>
                                    <input type="file" class="d-none" id="image" name="image" accept="image/*"
                                        capture="environment" required>
                                    <button type="button" class="btn btn-primary btn-block mb-2" id="captureBtn">
                                        <i class="fas fa-camera"></i> <span id="captureBtnText">Buka Kamera / Pilih
                                            Foto</span>
                                    </button>
                                    <small class="text-muted d-block mb-2">Foto Checksheet & Bukti Tap / Pencil
                                        Scratch</small>
                                    <button type="button" id="previewBtn" class="btn btn-info btn-sm btn-block"
                                        style="display: none;">
                                        <i class="fas fa-eye"></i> Preview Foto
                                    </button>
                                    <small id="fileName" class="text-muted d-block"></small>
                                </td>
                                <!-- Judgment -->
                                <td class="align-middle" style="min-width: 120px;">
                                    <select class="form-control" name="position_remark_judgment" required>
                                        <option value="OK">OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                </td>
                                <!-- Inisial QC -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" name="operator_initials"
                                        placeholder="Inisial" value="{{ auth()->user()->initials ?? '' }}" required>
                                </td>
                                <!-- Keterangan -->
                                <td class="align-middle">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next Proses:</label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih --</option>
                                            <option value="CRUSHING">CRUSHING</option>
                                            <option value="SORTIR">SORTIR</option>
                                            <option value="FINISHING">FINISHING</option>
                                            <option value="REPAIR">REPAIR</option>
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="keterangan" rows="3"
                                        placeholder="Keterangan tambahan..."></textarea>
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

                <input type="hidden" name="production_datetime" id="production_datetime">
                <input type="hidden" name="qc_datetime" id="qc_datetime">
            </form>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Preview Foto</h5><button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body"><img id="previewImage" src="" class="img-fluid"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>STANDARD (Gambar)</h5><button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid mb-3">
                    <h5 id="modalTitle" class="font-weight-bold"></h5>
                    <p id="modalDescription"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 95%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>STANDARD (PDF)</h5><button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-3">
                        <button class="btn btn-dark btn-sm mr-2" id="prevPdf"><i class="fas fa-arrow-left"></i> File
                            Prev</button>
                        <span id="pdfInfo" class="mx-3 font-weight-bold mt-1">File 1 of ?</span>
                        <button class="btn btn-dark btn-sm" id="nextPdf">File Next <i
                                class="fas fa-arrow-right"></i></button>
                    </div>
                    <div class="d-flex justify-content-center mb-2">
                        <button class="btn btn-secondary btn-sm mr-2" id="prevPage"><i class="fas fa-chevron-left"></i>
                            Page</button>
                        <span id="pageInfo" class="mx-2 mt-1">Page 1 of ?</span>
                        <button class="btn btn-secondary btn-sm" id="nextPage"><i class="fas fa-chevron-right"></i>
                            Page</button>
                    </div>
                    <div class="text-center bg-dark p-2" style="overflow: auto;"><canvas id="the-canvas"></canvas></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/js/vendor/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
            formInputs.prop('disabled', true);
            $('#checksheetForm').addClass('inputs-locked');
            $('<style>#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo('head');

            var timerInterval = null, totalSeconds = 0, timerRunning = false;
            $('#startTimerBtn').click(function () {
                if (!timerRunning) {
                    timerRunning = true;
                    $(this).prop('disabled', true).html('<i class="fas fa-clock"></i> SEDANG BERJALAN').removeClass('btn-success').addClass('btn-secondary');
                    $('#saveBtn').prop('disabled', false);
                    formInputs.prop('disabled', false);
                    timerInterval = setInterval(function () {
                        totalSeconds++;
                        var hours = Math.floor(totalSeconds / 3600), minutes = Math.floor((totalSeconds % 3600) / 60), seconds = totalSeconds % 60;
                        $('#timerDisplay').text([hours, minutes, seconds].map(v => v < 10 ? "0" + v : v).join(":"));
                        $('#cycleTimeInput').val(totalSeconds);
                    }, 1000);
                }
            });

            $('#sapCodeInput').on('input', function () {
                var code = $(this).val().trim().toLowerCase();
                var matched = $('#item_id option').filter(function () { return $(this).data('sap-code') && $(this).data('sap-code').toString().toLowerCase() === code; });
                if (matched.length) { $('#item_id').val(matched.val()).trigger('change'); $(this).addClass('is-valid').removeClass('is-invalid'); }
                else { $(this).removeClass('is-valid').addClass('is-invalid'); }
            });

            $('#item_id').on('change', function () {
                var opt = $(this).find('option:selected');
                var html = '';
                var files = opt.data('files');
                if (files && files.length) html += `<button type="button" class="btn btn-danger btn-sm view-pdf-btn mb-2" data-id="${opt.val()}" data-count="${files.length}"><i class="fas fa-file-pdf"></i> PDF (${files.length})</button>`;
                if (opt.data('image')) html += `<img src="${opt.data('image')}" class="img-thumbnail" style="max-width:100px; cursor:pointer;" data-toggle="modal" data-target="#imageModal" data-image="${opt.data('image')}" data-title="${opt.data('name')}" data-description="${opt.data('description')}">`;
                $('#imageContainer').html(html || '<i class="fas fa-image fa-2x text-gray-300"></i>');
            });

            $('#captureBtn').click(function () { $('#image').click(); });
            $('#image').on('change', function (e) {
                var file = e.target.files[0];
                if (file) {
                    $('#fileName').text(file.name);
                    $('#previewBtn').show();
                    var reader = new FileReader();
                    reader.onload = function (e) { $('#previewImage').attr('src', e.target.result); }
                    reader.readAsDataURL(file);
                }
            });
            $('#previewBtn').click(function () { $('#imagePreviewModal').modal('show'); });

            $('select[name="position_remark_judgment"]').on('change', function () {
                if ($(this).val() === 'NG') $('#nextProsesContainer').slideDown();
                else { $('#nextProsesContainer').slideUp(); $('#nextProses').val(''); }
            });

            $('form').on('submit', function (e) {

                if ($('select[name="position_remark_judgment"]').val() === 'NG' && !$('#nextProses').val()) {
                    e.preventDefault();
                    alert('Untuk hasil NG, silakan pilih Next Proses!');
                    return false;
                }
            });

            var pdfDoc = null, pageNum = 1, currentItemId = null, totalPdfs = 0, currentPdfIdx = 0;
            $(document).on('click', '.view-pdf-btn', function () {
                currentItemId = $(this).data('id'); totalPdfs = $(this).data('count'); currentPdfIdx = 0;
                $('#pdfModal').modal('show'); loadPdf(currentItemId, 0);
            });
            function loadPdf(id, idx) {
                pdfjsLib.getDocument(`/items/${id}/pdf/${idx}`).promise.then(function (doc) {
                    pdfDoc = doc; pageNum = 1; renderPage(1);
                    $('#pdfInfo').text(`File ${idx + 1} of ${totalPdfs}`);
                });
            }
            function renderPage(num) {
                pdfDoc.getPage(num).then(function (page) {
                    var canvas = document.getElementById('the-canvas'), ctx = canvas.getContext('2d'), vp = page.getViewport({ scale: 1.5 });
                    canvas.height = vp.height; canvas.width = vp.width;
                    page.render({ canvasContext: ctx, viewport: vp });
                    $('#pageInfo').text(`Page ${num} of ${pdfDoc.numPages}`);
                });
            }
            $('#prevPage').click(function () { if (pageNum > 1) renderPage(--pageNum); });
            $('#nextPage').click(function () { if (pageNum < pdfDoc.numPages) renderPage(++pageNum); });
            $('#prevPdf').click(function () { if (currentPdfIdx > 0) loadPdf(currentItemId, --currentPdfIdx); });
            $('#nextPdf').click(function () { if (currentPdfIdx < totalPdfs - 1) loadPdf(currentItemId, ++currentPdfIdx); });
        });
    </script>
@endpush