@extends('layouts.admin')

@section('title', 'Input Data Double Tape Checksheet')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET DOUBLE TAPE
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Double Tape</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('double_tape.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant" value="karawang">

                <div class="alert alert-info mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="font-weight-bold mb-0"><i class="fas fa-clipboard-check"></i> Tipe Pengecekan:</label>
                        </div>
                        <div class="col-md-9">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary active" id="labelSampling">
                                    <input type="radio" name="check_type_option" id="checkTypeSampling" value="sampling" checked>
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

                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <tr class="text-center">
                            <th rowspan="2" style="align-middle">Standard</th>
                            <th rowspan="2" style="align-middle">Item Part</th>
                            <th rowspan="2" style="align-middle">Tanggal / Shift</th>
                            <th rowspan="2" style="align-middle">Total Qty (Lot)</th>
                            <th rowspan="2" style="align-middle">Sampling Qty</th>
                            <th rowspan="2" style="align-middle; min-width: 280px;">Detail NG</th>
                            <th rowspan="2" style="align-middle">Total (OK/NG)</th>
                            <th rowspan="2" style="align-middle">Judgment</th>
                            <th rowspan="2" style="align-middle">Inisial QC</th>
                            <th rowspan="2" style="align-middle">Keterangan</th>
                        </tr>
                        <tbody>
                            <tr>
                                <!-- Ilustrasi Barang -->
                                <td class="align-middle text-center" id="imageContainer">
                                    <div style="width: 100px; height: 100px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-image fa-2x text-gray-300"></i>
                                    </div>
                                </td>

                                <!-- Pilihan Barang -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold">Kode SAP</label>
                                        <input type="text" class="form-control" id="sapCodeInput" placeholder="Ketik Kode SAP..." style="min-width: 200px;">
                                        <small class="text-muted">Auto-select item berdasarkan SAP code</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part</label>
                                        <select class="form-control" name="item_id" id="itemSelect" required style="min-width: 300px;">
                                            <option value="" disabled selected>Pilih Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-name="{{ $item->name }}" data-description="{{ $item->description }}"
                                                    data-defects="{{ json_encode($item->defects) }}"
                                                    data-sap-code="{{ $item->sap_code ?? '' }}">
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
                                        <input type="date" class="form-control" style="min-width: 110px;" name="date" value="{{ $defaultDate }}" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <select class="form-control" style="min-width: 80px;" name="shift" required>
                                            <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                            <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                            <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                        </select>
                                    </div>
                                </td>

                                <!-- Total Qty -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;" name="total_qty" id="totalQty" placeholder="0" min="0" required>
                                </td>

                                <!-- Sampling Qty -->
                                <td class="align-middle">
                                    <input type="number" class="form-control text-center" style="min-width: 60px;" name="sampling_qty" id="samplingQty" placeholder="0" min="0" required>
                                    <div id="aql_info" class="text-xs text-center mt-1 font-weight-bold" style="display: none;">
                                        <span class="text-success">Acc: <span id="acc_val">0</span></span> |
                                        <span class="text-danger">Rej: <span id="rej_val">1</span></span>
                                    </div>
                                </td>

                                <td class="align-middle" style="min-width: 280px;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="checkOK">
                                        <label class="form-check-label text-success font-weight-bold" for="checkOK">OK (Pass All)</label>
                                    </div>
                                    <hr class="my-2">
                                    <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
                                    <div id="defectContainer">
                                        <div class="input-group mb-2 defect-row">
                                            <select class="form-control defect-select" style="min-width: 180px;" name="defect_types[]" id="defectSelect">
                                                <option value="">-- Pilih Defect --</option>
                                            </select>
                                            <input type="number" class="form-control defect-qty" style="min-width: 100px;" name="defect_quantities[]" placeholder="Qty" min="1">
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-info mt-1" style="display: none;">
                                        <i class="fas fa-plus"></i> Tambah Jenis
                                    </button>
                                </td>

                                <!-- Total OK / NG -->
                                <td class="align-middle" style="min-width: 120px;">
                                    <div class="row no-gutters mb-1">
                                        <div class="col-4 text-center bg-success text-white py-1 rounded-left small font-weight-bold">OK</div>
                                        <div class="col-8">
                                            <input type="number" class="form-control form-control-sm rounded-0 rounded-right text-center" name="total_ok" placeholder="0" min="0" required readonly>
                                        </div>
                                    </div>
                                    <div class="row no-gutters">
                                        <div class="col-4 text-center bg-danger text-white py-1 rounded-left small font-weight-bold">NG</div>
                                        <div class="col-8">
                                            <input type="number" class="form-control form-control-sm rounded-0 rounded-right text-center" name="total_ng" id="totalNG" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle">
                                    <select class="form-control font-weight-bold" name="judgment" id="judgmentSelect" required>
                                        <option value="" disabled selected>-- Result --</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                </td>

                                <!-- Inisial QC -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center" style="min-width: 60px;" name="operator_initials" value="{{ auth()->user()->initials ?? '' }}" placeholder="Inisial" required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger small">Next Proses:</label>
                                        <select class="form-control form-control-sm" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih --</option>
                                            <option value="CRUSHING">CRUSHING</option>
                                            <option value="SORTIR">SORTIR</option>
                                            <option value="REPAIR">REPAIR</option>
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="remarks" rows="4" placeholder="Catatan..."></textarea>
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
    <script>
        $(document).ready(function () {
            // === INPUT LOCK UNTIL START (SUB ASSY LOGIC) ===
            var formInputs = $('#checksheetForm input:not([type="hidden"]):not(#startTimerBtn), #checksheetForm select, #checksheetForm textarea, #checksheetForm button:not(#startTimerBtn)');
            formInputs.prop('disabled', true);
            $('#checksheetForm').addClass('inputs-locked');
            $('<style>#checksheetForm.inputs-locked input:disabled, #checksheetForm.inputs-locked select:disabled, #checksheetForm.inputs-locked textarea:disabled { background-color: #f0f0f0 !important; cursor: not-allowed; }</style>').appendTo('head');

            // Variables
            var timerInterval = null;
            var totalSeconds = 0;
            var timerRunning = false;
            var isFullcheck = false;

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
                    
                    // Specific readonly logic
                    $('#samplingQty').prop('readonly', isFullcheck);
                    $('input[name="total_ok"]').prop('readonly', true);

                    timerInterval = setInterval(function () {
                        totalSeconds++;
                        updateTimerDisplay();
                    }, 1000);
                    
                    $('#itemSelect').focus();
                }
            });

            // Tipe Pengecekan handler
            $('input[name="check_type_option"]').change(function () {
                isFullcheck = ($(this).val() === 'fullcheck');
                if (timerRunning) {
                    $('#samplingQty').prop('readonly', isFullcheck);
                }
                $('#totalQty').trigger('input');
            });

            // AQL Sampling Logic
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

            $('#totalQty').on('input', function () {
                let lot = parseInt($(this).val()) || 0;
                if (lot > 0) {
                    let sample = isFullcheck ? lot : getSampleSize(lot);
                    $('#samplingQty').val(sample).trigger('input');
                } else {
                    $('#samplingQty').val(0).trigger('input');
                }
            });

            // Auto Judgment & OK/NG Calculation
            $('#totalNG, #samplingQty').on('input', function () {
                let total = parseInt($('#samplingQty').val()) || 0;
                let ng = parseInt($('#totalNG').val()) || 0;
                let ok = total - ng;
                $('input[name="total_ok"]').val(ok < 0 ? 0 : ok);

                if (total > 0 || ng > 0) {
                    let limits = getAqlLimits(total);
                    $('#acc_val').text(limits.acc);
                    $('#rej_val').text(limits.rej);
                    $('#aql_info').show();

                    if (ng <= limits.acc) {
                        $('#judgmentSelect').val('OK').trigger('change');
                    } else {
                        $('#judgmentSelect').val('NG').trigger('change');
                    }
                } else {
                    $('#aql_info').hide();
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
                $('#addDefectBtn').show();
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