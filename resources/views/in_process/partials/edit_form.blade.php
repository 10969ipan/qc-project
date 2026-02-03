<form action="{{ route('in_process.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="plant" value="{{ request('plant') }}">

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="item_id">Item Part</label>
                <select name="item_id" id="item_id" class="form-control" required>
                    <option value="" disabled style="font-weight: bold; color: #6c757d;">Pilih Item Part
                    </option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                            data-part-number="{{ $item->part_number }}">
                            {{ $item->name }} ({{ $item->customer }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="date">Tanggal</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $checksheet->date }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="shift">Shift</label>
                <select name="shift" id="shift" class="form-control" required>
                    <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                    <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                    <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                </select>
            </div>
            <div class="form-group">
                <label for="code_machine">No Mesin</label>
                <select name="code_machine" id="code_machine" class="form-control" required>
                    <option value="">Pilih Mesin</option>
                    @php
                        $plantCode = strtolower($checksheet->plant->code ?? 'karawang');
                        $jakartaMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
                        $karawangMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19];
                        $machineNumbers = ($plantCode === 'jakarta') ? $jakartaMachineNumbers : $karawangMachineNumbers;
                    @endphp
                    @foreach ($machineNumbers as $num)
                        <option value="{{ $num }}" {{ $checksheet->code_machine == $num ? 'selected' : '' }}>Mesin
                            {{ $num }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if(auth()->user()->role !== 'inspector')
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="jam_before">Jam (Before)</label>
                    <input type="time" name="jam_before" id="jam_before" class="form-control"
                        value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="jam_after">Jam (After)</label>
                    <input type="time" name="jam_after" id="jam_after" class="form-control"
                        value="{{ $checksheet->created_at->format('H:i') }}">
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="total_qty">Total Qty</label>
                <input type="number" name="total_qty" id="total_qty" class="form-control"
                    value="{{ $checksheet->total_qty }}" min="0" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="sampling_qty">Sampling Qty</label>
                <input type="number" name="sampling_qty" id="sampling_qty" class="form-control"
                    value="{{ $checksheet->sampling_qty }}" min="0" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="total_ok">Total OK</label>
                <input type="number" name="total_ok" id="total_ok" class="form-control"
                    value="{{ $checksheet->total_ok }}" min="0" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="total_ng">Total NG</label>
                <input type="number" name="total_ng" id="total_ng" class="form-control"
                    value="{{ $checksheet->total_ng }}" min="0" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="judgment">Judgment</label>
                <select name="judgment" id="judgment" class="form-control" required>
                    <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                    <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="operator_initials">Inisial Operator</label>
                <input type="text" name="operator_initials" id="operator_initials" class="form-control"
                    value="{{ $checksheet->operator_initials }}">
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="mb-0 font-weight-bold">Check Dimensi</label>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary btn-xs" id="editAddPointBtn" title="Tambah Point">
                    <i class="fas fa-plus"></i> Point
                </button>
                <button type="button" class="btn btn-outline-info btn-xs" id="editAddCavityBtn" title="Tambah Cavity">
                    <i class="fas fa-plus"></i> Cavity
                </button>
            </div>
        </div>
        @php
            $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true) ?? [];

            // Determine max cavity and point from data to render correctly
            $maxCavityFound = 5;
            $maxPointFound = 5;
            foreach ($dimensions as $cav => $pts) {
                if (is_numeric($cav))
                    $maxCavityFound = max($maxCavityFound, (int) $cav);
                if (is_array($pts)) {
                    foreach (array_keys($pts) as $pt) {
                        if (is_numeric($pt))
                            $maxPointFound = max($maxPointFound, (int) $pt);
                    }
                }
            }
        @endphp
        <div class="table-responsive" style="max-height: 400px; overflow: auto;">
            <table class="table table-sm table-bordered mb-0" id="editDimensionTable">
                <thead class="text-center bg-light">
                    <tr id="editDimensionHeadRow">
                        <th style="min-width: 100px; position: sticky; left: 0; z-index: 2; background: #f8f9fa;">Cavity
                        </th>
                        @for ($j = 1; $j <= $maxPointFound; $j++)
                            <th class="point-header">Point {{ $j }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody id="editDimensionBody">
                    @for ($i = 1; $i <= $maxCavityFound; $i++)
                        <tr class="edit-cavity-row" data-cavity="{{ $i }}">
                            <td class="text-center font-weight-bold bg-light"
                                style="position: sticky; left: 0; z-index: 1;">Cav {{ $i }}</td>
                            @for ($j = 1; $j <= $maxPointFound; $j++)
                                <td class="point-cell">
                                    <input type="text" class="form-control form-control-sm edit-dimension-input"
                                        style="min-width: 60px;" name="dimensions[{{ $i }}][{{ $j }}]"
                                        value="{{ $dimensions[$i][$j] ?? '' }}" placeholder="P{{ $j }}">
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <small class="text-muted">Max 20x20. Default 5x5 (atau sesuai data).</small>
    </div>

    <div id="nextProsesContainer" style="display: {{ $checksheet->judgment == 'NG' ? 'block' : 'none' }};">
        <div class="form-group">
            <label for="next_proses" class="text-danger font-weight-bold">Next Proses</label>
            <select name="next_proses" id="next_proses" class="form-control">
                <option value="">-- Pilih Next Proses --</option>
                <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING</option>
                <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR</option>
                <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>FINISHING
                </option>
                <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR</option>
                @if($checksheet->next_proses && !in_array($checksheet->next_proses, ['CRUSHING', 'SORTIR', 'FINISHING', 'REPAIR']))
                    <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                @endif
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="remarks">Keterangan</label>
        <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ $checksheet->remarks }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form>

<script>
    (function () {
        // Use PHP to inject the variable
        const partDimensionStandards = JSON.parse('{!! $partDimensionStandards !!}');

        // Initial counts from PHP
        let currentCavities = {{ $maxCavityFound }};
        let currentPoints = {{ $maxPointFound }};
        const maxCavities = 20;
        const maxPoints = 20;

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
                alert('Maximum 20 cavities reached');
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
                alert('Maximum 20 points reached');
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
                $('#total_ok').val(0);
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

        function validateDimensions() {
            const selectedOption = $('#item_id').find('option:selected');
            const itemPartNumber = selectedOption.data('part-number');
            const dimensionStandards = partDimensionStandards[itemPartNumber];

            if (!dimensionStandards) {
                $('.edit-dimension-input').removeClass('is-invalid');
                updateJudgment();
                return;
            }

            $('.edit-dimension-input').each(function () {
                const name = $(this).attr('name');
                const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                if (!match) return;

                const point = match[2];
                const standard = dimensionStandards[point];
                const valStr = $(this).val().trim();
                const value = parseFloat(valStr);

                if (standard && valStr !== '' && !isNaN(value)) {
                    const lowerBound = standard.size - standard.tolerance;
                    const upperBound = standard.size + standard.tolerance;

                    if (value < lowerBound || value > upperBound) {
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

        // Initial check
        validateDimensions();
    })();
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