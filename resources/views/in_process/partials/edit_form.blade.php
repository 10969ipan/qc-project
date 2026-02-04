<form id="editChecksheetForm" class="ajax-form"
    action="{{ route('in_process.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    {{-- Preserve all filter and pagination parameters --}}
    @foreach(request()->all() as $key => $value)
        @if(!in_array($key, ['_token', '_method', 'id']))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="row">
        <div class="col-md-6 border-right">
            <div class="form-group mb-2">
                <label class="small font-weight-bold">Item Part <span class="text-danger">*</span></label>
                <select name="item_id" id="item_id" class="form-control form-control-sm" required>
                    <option value="" disabled style="font-weight: bold; color: #6c757d;">Pilih Item Part</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                            data-part-number="{{ $item->part_number }}"
                            data-defects="{{ json_encode($item->defects) }}">
                            {{ $item->name }} ({{ $item->customer }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date" class="form-control form-control-sm"
                            value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Shift <span class="text-danger">*</span></label>
                        <select name="shift" id="shift" class="form-control form-control-sm" required>
                            <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">No Mesin <span class="text-danger">*</span></label>
                        <select name="code_machine" id="code_machine" class="form-control form-control-sm" required>
                            <option value="">Pilih Mesin</option>
                            @php
                                $plantCode = strtolower($checksheet->plant->code ?? 'karawang');
                                $jakartaMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
                                $karawangMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19];
                                $machineNumbers = ($plantCode === 'jakarta') ? $jakartaMachineNumbers : $karawangMachineNumbers;
                            @endphp
                            @foreach ($machineNumbers as $num)
                                <option value="{{ $num }}" {{ $checksheet->code_machine == $num ? 'selected' : '' }}>Mesin {{ $num }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Inisial Operator</label>
                        <input type="text" name="operator_initials" id="operator_initials" class="form-control form-control-sm"
                            value="{{ $checksheet->operator_initials }}" placeholder="Inisial...">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total Qty <span class="text-danger">*</span></label>
                        <input type="number" name="total_qty" id="total_qty" class="form-control form-control-sm"
                            value="{{ $checksheet->total_qty }}" min="0" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Sampling Qty <span class="text-danger">*</span></label>
                        <input type="number" name="sampling_qty" id="sampling_qty" class="form-control form-control-sm"
                            value="{{ $checksheet->sampling_qty }}" min="0" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @if(auth()->user()->role !== 'inspector')
                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Jam (Before)</label>
                            <input type="time" name="jam_before" id="jam_before" class="form-control form-control-sm"
                                value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Jam (After)</label>
                            <input type="time" name="jam_after" id="jam_after" class="form-control form-control-sm"
                                value="{{ $checksheet->created_at->format('H:i') }}">
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                     <div class="form-group mb-2">
                        <label class="small font-weight-bold">Detail NG (Defect List)</label>
                        <div id="editDefectContainer">
                            {{-- Rows will be populated by JS or Server-side loop --}}
                            @php
                                $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true) ?? [];
                            @endphp
                            
                            @if(count($defects) > 0)
                                @foreach($defects as $index => $defect)
                                    <div class="input-group mb-2 defect-row">
                                        <select class="form-control form-control-sm defect-select" name="defect_types[]">
                                            <option value="">-- Pilih Defect --</option>
                                            <option value="{{ $defect['type'] }}" selected>{{ $defect['type'] }}</option>
                                        </select>
                                        <input type="number" class="form-control form-control-sm defect-qty" name="defect_quantities[]" 
                                            value="{{ $defect['qty'] }}" min="1" placeholder="Qty" style="max-width: 80px;">
                                        <div class="input-group-append">
                                            <button class="btn btn-danger btn-xs remove-defect-btn" type="button"><i class="fas fa-minus"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                {{-- Empty initial row for structure, hidden or shown based on needs --}}
                                <div class="input-group mb-2 defect-row" style="display:none"></div>
                            @endif
                        </div>
                        <button type="button" id="editAddDefectBtn" class="btn btn-info btn-xs mt-1" style="{{ count($defects) > 0 || $checksheet->total_ng > 0 ? '' : 'display:none;' }}">
                            <i class="fas fa-plus"></i> Tambah Jenis
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total OK (pcs) <span class="text-danger">*</span></label>
                        <input type="number" name="total_ok" id="total_ok" class="form-control form-control-sm"
                            value="{{ $checksheet->total_ok }}" min="0" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total NG (pcs) <span class="text-danger">*</span></label>
                        <input type="number" name="total_ng" id="total_ng" class="form-control form-control-sm"
                            value="{{ $checksheet->total_ng }}" min="0" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Judgment Final <span class="text-danger">*</span></label>
                        <select name="judgment" id="judgment" class="form-control form-control-sm font-weight-bold {{ $checksheet->judgment == 'OK' ? 'text-success' : 'text-danger' }}" required>
                            <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                        </select>
                    </div>
                </div>
                <div class="col-6" id="nextProsesContainer" style="display: {{ $checksheet->judgment == 'NG' ? 'block' : 'none' }};">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-danger">Next Proses <span class="text-danger">*</span></label>
                        <select name="next_proses" id="next_proses" class="form-control form-control-sm border-danger">
                            <option value="">-- Pilih Next Proses --</option>
                            <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING</option>
                            <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR</option>
                            <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>FINISHING</option>
                            <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR</option>
                            @if($checksheet->next_proses && !in_array($checksheet->next_proses, ['CRUSHING', 'SORTIR', 'FINISHING', 'REPAIR']))
                                <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="small font-weight-bold">Keterangan / Remarks</label>
                <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="3" placeholder="Tambahkan keterangan...">{{ $checksheet->remarks }}</textarea>
            </div>
        </div>
    </div>

    <!-- Check Dimensi Styled exactly like Jadwal Kalibrasi in screenshot -->
    <div class="form-group mb-3 mt-3">
        <label class="small font-weight-bold">Check Dimensi (mm) <span class="text-danger">*</span></label>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center" style="background-color: #4e73df !important;">
                <h6 class="m-0 font-weight-bold small text-center flex-grow-1">Tampilan Titik Pengukuran</h6>
                <button type="button" class="btn btn-success btn-xs" id="editAddCavityBtn" title="Tambah Cavity" style="background-color: #1cc88a !important; border:none; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0">
                @php
                    $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true) ?? [];
                    $maxCavityFound = 5;
                    $maxPointFound = 5;
                    foreach ($dimensions as $cav => $pts) {
                        if (is_numeric($cav)) $maxCavityFound = max($maxCavityFound, (int) $cav);
                        if (is_array($pts)) {
                            foreach (array_keys($pts) as $pt) {
                                if (is_numeric($pt)) $maxPointFound = max($maxPointFound, (int) $pt);
                            }
                        }
                    }
                @endphp
                <div class="table-responsive" style="max-height: 350px;">
                    <table class="table table-sm table-bordered mb-0" id="editDimensionTable">
                        <thead class="text-center bg-white small">
                            <tr id="editDimensionHeadRow">
                                <th style="min-width: 80px; position: sticky; left: 0; z-index: 2; background: white;">Cavity</th>
                                @for ($j = 1; $j <= $maxPointFound; $j++)
                                    <th class="point-header">P{{ $j }}</th>
                                @endfor
                                <th style="width: 40px;"><button type="button" class="btn btn-primary btn-xs" id="editAddPointBtn"><i class="fas fa-plus"></i></button></th>
                            </tr>
                        </thead>
                        <tbody id="editDimensionBody">
                            @for ($i = 1; $i <= $maxCavityFound; $i++)
                                <tr class="edit-cavity-row" data-cavity="{{ $i }}">
                                    <td class="text-center font-weight-bold bg-light small" style="position: sticky; left: 0; z-index: 1;">Cav {{ $i }}</td>
                                    @for ($j = 1; $j <= $maxPointFound; $j++)
                                        <td class="p-0">
                                            <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center"
                                                style="min-width: 50px; font-size: 0.75rem; height: 35px;" name="dimensions[{{ $i }}][{{ $j }}]"
                                                value="{{ $dimensions[$i][$j] ?? '' }}" placeholder="-">
                                        </td>
                                    @endfor
                                    <td class="text-center bg-light">
                                        <button type="button" class="btn btn-link text-danger p-0" disabled><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 pb-2 text-right">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm" id="btnSubmit">
            <i class="fas fa-save mr-1"></i> Update
        </button>
    </div>
</form>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div class="text-center">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="text-white mt-3 font-weight-bold">Menyimpan data...</div>
    </div>
</div>

@php
// We remove @push because this is loaded via AJAX and @push doesn't work in AJAX responses.
// The script will execute normally when inserted into the DOM.
@endphp
<script>
    // Ensure jQuery is loaded before executing
    (function() {
        // Check if jQuery is available
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded! Waiting for it...');
            // Retry after a short delay
            setTimeout(arguments.callee, 50);
            return;
        }
        
        // jQuery is available, proceed with initialization
        (function ($) {
        // Use PHP to inject the variable
        const partDimensionStandards = JSON.parse('{!! $partDimensionStandards !!}');

        // Initial counts from PHP
        let currentCavities = {{ $maxCavityFound }};
        let currentPoints = {{ $maxPointFound }};
        const maxCavities = 30;
        const maxPoints = 30;

        $('#editAddCavityBtn').click(function () {
            if (currentCavities < maxCavities) {
                currentCavities++;
                let newRow = `<tr class="edit-cavity-row" data-cavity="${currentCavities}">
                                        <td class="text-center font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 1;">Cav ${currentCavities}</td>`;

                for (let j = 1; j <= currentPoints; j++) {
                    newRow += `<td class="point-cell">
                                            <input type="text" class="form-control form-control-sm edit-dimension-input" 
                                                style="min-width: 60px;"
                                                name="dimensions[${currentCavities}][${j}]" 
                                                placeholder="P${j}">
                                        </td>`;
                }
                newRow += `</tr>`;
                $('#editDimensionBody').append(newRow);
            } else {
                alert('Maximum 30 cavities reached');
            }
        });

        $('#editAddPointBtn').click(function () {
            if (currentPoints < maxPoints) {
                currentPoints++;
                // Add header
                $('#editDimensionHeadRow').append(`<th class="point-header">Point ${currentPoints}</th>`);

                // Add cells to each row
                $('.edit-cavity-row').each(function () {
                    let cavityNum = $(this).data('cavity');
                    $(this).append(`<td class="point-cell">
                                            <input type="text" class="form-control form-control-sm edit-dimension-input" 
                                                style="min-width: 60px;"
                                                name="dimensions[${cavityNum}][${currentPoints}]" 
                                                placeholder="P${currentPoints}">
                                        </td>`);
                });
            } else {
                alert('Maximum 30 points reached');
            }
        });

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
            const sampling = parseInt($('#sampling_qty').val()) || 0;
            const ng = parseInt($('#total_ng').val()) || 0;
            const isDimensiInvalid = $('.is-invalid').length > 0;

            if (sampling >= ng) {
                $('#total_ok').val(sampling - ng);
            } else {
                $('#total_ok').val(Math.max(0, sampling - ng));
            }

            const limits = getAqlLimits(sampling);
            const judgmentSelect = $('#judgment');

            if (ng > 0 || sampling > 0 || isDimensiInvalid) {
                if (isDimensiInvalid || ng >= limits.rej) {
                    judgmentSelect.val('NG').removeClass('text-success').addClass('text-danger');
                } else if (ng <= limits.acc) {
                    judgmentSelect.val('OK').removeClass('text-danger').addClass('text-success');
                } else {
                    judgmentSelect.val('NG').removeClass('text-success').addClass('text-danger');
                }
            }
            toggleNextProses();
        }

        function toggleNextProses() {
            const judgment = $('#judgment').val();
            const container = $('#nextProsesContainer');
            if (judgment === 'NG') {
                container.slideDown();
            } else {
                container.slideUp();
                $('#next_proses').val('');
            }
        }

        function normalizePartNumber(pn) {
            if (!pn) return '';
            return pn.toString()
                .replace(/[\u2012\u2013\u2014\u2212]/g, '-') // EN, EM, FIGURE DASH, MINUS
                .replace(/\s+/g, '') // Remove all whitespace
                .toUpperCase();
        }

        function validateDimensions() {
            const selectedOption = $('#item_id').find('option:selected');
            const rawPartNumber = selectedOption.data('part-number');
            const itemPartNumber = normalizePartNumber(rawPartNumber);
            const dimensionStandards = partDimensionStandards[itemPartNumber];

            $('.edit-dimension-input').each(function () {
                const name = $(this).attr('name');
                const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                if (!match) return;

                const point = match[2];
                const standard = dimensionStandards ? dimensionStandards[point] : null;
                const valStr = $(this).val().trim();
                const value = parseFloat(valStr.replace(',', '.'));

                if (standard && valStr !== '' && !isNaN(value)) {
                    let isInvalid = false;

                    if (standard.min !== null && value < standard.min) {
                        isInvalid = true;
                    }
                    if (standard.max !== null && value > standard.max) {
                        isInvalid = true;
                    }

                    // Fallback to Size +/- Tolerance
                    if (standard.min === null && standard.max === null) {
                        if (standard.size !== null && standard.tolerance !== null) {
                            const lowerBound = standard.size - standard.tolerance;
                            const upperBound = standard.size + standard.tolerance;
                            if (value < lowerBound || value > upperBound) {
                                isInvalid = true;
                            }
                        }
                    }

                    if (isInvalid) {
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            updateJudgment();
        }

        $(document).on('input', '.edit-dimension-input', validateDimensions);
        $('#sampling_qty, #total_ng').on('input', updateJudgment);
        $('#item_id').on('change', validateDimensions);
        $('#judgment').on('change', toggleNextProses);

        // Form Submit Validation
        $('#editChecksheetForm').on('submit', function (e) {
            const judgment = $('#judgment').val();
            const nextProses = $('#next_proses').val();

            // Log data yang akan dikirim untuk debugging
            console.log('=== Form Edit Checksheet Submit ===');
            console.log('Judgment:', judgment);
            console.log('Next Proses:', nextProses);
            
            // Collect dimension data
            const dimensionData = {};
            $('.edit-dimension-input').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (value && value.trim() !== '') {
                    const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                    if (match) {
                        const cavity = match[1];
                        const point = match[2];
                        if (!dimensionData[cavity]) dimensionData[cavity] = {};
                        dimensionData[cavity][point] = value;
                    }
                }
            });
            console.log('Dimension Data:', dimensionData);
            console.log('Dimension Count:', Object.keys(dimensionData).length);
            
            // Collect defect data
            const defectData = [];
            $('.defect-row').each(function() {
                const type = $(this).find('.defect-select').val();
                const qty = $(this).find('.defect-qty').val();
                if (type && qty) {
                    defectData.push({ type, qty });
                }
            });
            console.log('Defect Data:', defectData);
            console.log('Defect Count:', defectData.length);
            
            // Collect all form data
            const formData = $(this).serializeArray();
            console.log('All Form Data:', formData);
            console.log('===================================');

            if (judgment === 'NG' && !nextProses) {
                e.preventDefault();
                console.warn('Validation Failed: Next Proses required for NG judgment');
                Swal.fire({
                    icon: 'warning',
                    title: 'Next Proses Wajib Dipilih',
                    text: 'Untuk hasil NG, silakan pilih Next Proses terlebih dahulu!',
                    confirmButtonColor: '#3085d6'
                });
                $('#next_proses').focus();
                return false;
            }
            
            console.log('Form validation passed, submitting...');
            
            // Pastikan tidak ada input yang disabled (yang bisa menghalangi data terkirim)
            $('#editChecksheetForm').find(':input:disabled').each(function() {
                console.warn('Found disabled input:', $(this).attr('name'), '- Enabling temporarily');
                $(this).prop('disabled', false).addClass('was-disabled');
            });
            
            // Show loading overlay
            $('#loadingOverlay').css('display', 'flex');
            
            // Disable submit button to prevent double submit
            $('#btnSubmit').prop('disabled', true);
        });
        
        // Re-enable disabled inputs after form is processed (in case of error)
        $(document).on('ajaxComplete ajaxError', function() {
            $('.was-disabled').prop('disabled', true).removeClass('was-disabled');
            $('#loadingOverlay').hide();
            $('#btnSubmit').prop('disabled', false);
        });

        // Initial check
        validateDimensions();

        // --- Defect & NG Logic (Copied/Adapted from create.blade.php) ---
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

        function updateDefectOptions() {
            var selectedOption = $('#item_id').find('option:selected');
            var defectsData = selectedOption.data('defects');

            // Normalize defectsData
            if (typeof defectsData === 'string') {
                try {
                    defectsData = JSON.parse(defectsData);
                } catch (e) {
                    defectsData = [];
                }
            }

            // Provide options for ALL defect selects
            $('.defect-select').each(function() {
                var currentVal = $(this).val(); // preserve selection
                $(this).empty();
                $(this).append('<option value="">-- Pilih Defect --</option>');

                if (Array.isArray(defectsData) && defectsData.length > 0) {
                    var that = this;
                    $.each(defectsData, function (index, value) {
                        $(that).append('<option value="' + value + '">' + value + '</option>');
                    });
                } else {
                    var that = this;
                    $.each(defaultDefects, function (index, defect) {
                        $(that).append('<option value="' + defect.text + '">' + defect.text + '</option>'); // Using text as value to match existing data likely
                    });
                }
                
                if (currentVal) {
                    $(this).val(currentVal);
                }
            });
        }

        // Run updates on item change
        $('#item_id').change(function() {
            updateDefectOptions();
            validateDimensions(); // existing call
        });

        // Trigger on load to ensure dropdowns have options (if not already populated nicely)
        // Since we manually put the 'selected' option in HTML, we just need to fill the rest.
        updateDefectOptions();

        $('#editAddDefectBtn').click(function () {
            var rowCount = $('.defect-row').length;
            if (rowCount < 5) { // Limit to reasonable number
                var newRow = $('<div class="input-group mb-2 defect-row">' +
                    '<select class="form-control form-control-sm defect-select" name="defect_types[]">' +
                    '<option value="">-- Pilih Defect --</option>' +
                    '</select>' +
                    '<input type="number" class="form-control form-control-sm defect-qty" name="defect_quantities[]" placeholder="Qty" min="1" style="max-width: 80px;">' +
                    '<div class="input-group-append">' +
                    '<button class="btn btn-danger btn-xs remove-defect-btn" type="button"><i class="fas fa-minus"></i></button>' +
                    '</div>' +
                    '</div>');
                
                $('#editDefectContainer').append(newRow);
                updateDefectOptions(); // Populate options for new row
            }
        });

        $(document).on('click', '.remove-defect-btn', function () {
            $(this).closest('.defect-row').remove();
            calculateTotalNG();
        });

        function calculateTotalNG() {
            var total = 0;
            $('.defect-qty').each(function () {
                var qty = parseInt($(this).val()) || 0;
                total += qty;
            });
            $('#total_ng').val(total).trigger('input');
            
            // Toggle Add Defect button
            if (total >= 0 || $('.defect-row').length > 0) {
                 $('#editAddDefectBtn').show();
            }
        }

        $(document).on('input', '.defect-qty', function () {
            calculateTotalNG();
        });

        // Toggle "Add Defect" button based on NG count
        $('#total_ng').on('input', function () {
            var ng = parseInt($(this).val()) || 0;
            // If user manually types NG, we should ensure there is at least one defect row if NG > 0
            // But if they just want to type NG without details, we shouldn't force it?
            // The constraint is matching NG to defects. 
            // Let's just show the button if NG > 0
            if (ng > 0) {
                $('#editAddDefectBtn').show();
            } 
            // Logic to add a row if none exists but NG > 0? 
            if (ng > 0 && $('.defect-row').length === 0) {
                // Perhaps auto-add one?
                $('#editAddDefectBtn').trigger('click');
            }
        });
        })(jQuery); // Pass jQuery to the function
    })(); // Self-executing function
</script>
<style>
    .is-invalid {
        border-color: #dc3545 !important;
        background-color: #f8d7da !important;
    }

    .btn-xs {
        padding: 1px 5px;
        font-size: 12px;
        line-height: 1.5;
        border-radius: 3px;
    }
</style>