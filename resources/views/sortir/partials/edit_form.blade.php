<form action="{{ route('sortir.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST"
    id="editSortirForm">
    @csrf
    @method('PUT')

    <div class="table-responsive">
        <table class="table table-bordered" width="100%" cellspacing="0">
            <tr class="text-center">
                <th>Item Part</th>
                <th>Tanggal / Shift</th>
                <th>Total Qty</th>
                <th>Sampling Qty</th>
                <th>Jenis (OK/NG) & Detail NG</th>
                <th>Total (OK/NG)</th>
                <th>Judgment</th>
                <th>Inisial QC</th>
                <th>Keterangan</th>
            </tr>
            <tbody>
                <tr>
                    <!-- Item Part -->
                    <td class="align-middle">
                        <div class="form-group mb-0">
                            <select class="form-control" name="item_id" required readonly>
                                <option value="{{ $checksheet->item_id }}" selected>
                                    {{ $checksheet->item->name }} ({{ $checksheet->item->part_number }})
                                </option>
                            </select>
                            <small class="text-muted">Item tidak dapat diubah</small>
                        </div>
                    </td>

                    <!-- Tanggal / Shift -->
                    <td class="align-middle">
                        <div class="form-group mb-2">
                            <input type="date" class="form-control" style="min-width: 150px;" name="date"
                                value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
                        </div>
                        <div class="form-group mb-2">
                            <select class="form-control" name="shift" required>
                                <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1
                                </option>
                                <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2
                                </option>
                                <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3
                                </option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <select name="line" class="form-control">
                                <option value="">Pilih Meja</option>
                                @php
                                    $plant = strtolower(auth()->user()->plant ?? request('plant') ?? '');
                                    $tableOptions = range(1, 15);
                                    if ($plant === 'jakarta') {
                                        $tableOptions = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
                                    }
                                @endphp
                                @foreach ($tableOptions as $i)
                                    <option value="{{ $i }}" {{ $checksheet->line == $i ? 'selected' : '' }}>Meja
                                        {{ $i }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>

                    <!-- Total Qty -->
                    <td class="align-middle">
                        <input type="number" class="form-control text-center" name="total_qty"
                            value="{{ $checksheet->total_qty }}" min="0" required>
                    </td>

                    <!-- Sampling Qty -->
                    <td class="align-middle">
                        <input type="number" class="form-control text-center" name="sampling_qty"
                            value="{{ $checksheet->sampling_qty }}" min="0" required>
                    </td>

                    <!-- Jenis (OK/NG) & Detail NG -->
                    <td class="align-middle">
                        <hr class="my-2">
                        <small class="font-weight-bold text-secondary">Defect List (NG):</small>
                        <div id="defectContainer">
                            @php
                                $defects = is_array($checksheet->defects) ? $checksheet->defects : (json_decode($checksheet->defects, true) ?: []);
                            @endphp
                            @if(count($defects) > 0)
                                @foreach($defects as $index => $defect)
                                    <div class="input-group mb-2 defect-row">
                                        <input type="text" class="form-control" name="defect_types[]"
                                            value="{{ $defect['type'] ?? $defect }}" placeholder="Jenis">
                                        <input type="number" class="form-control" name="defect_quantities[]"
                                            value="{{ $defect['qty'] ?? 1 }}" min="1">
                                        @if($index > 0)
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger btn-sm remove-defect"><i
                                                        class="fas fa-times"></i></button>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group mb-2 defect-row">
                                    <input type="text" class="form-control" name="defect_types[]" placeholder="Jenis">
                                    <input type="number" class="form-control" name="defect_quantities[]" min="1">
                                </div>
                            @endif
                        </div>
                        <button type="button" id="addDefectBtn" class="btn btn-info btn-sm mt-1">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </td>

                    <!-- Total OK / NG -->
                    <td class="align-middle">
                        <div class="row no-gutters mb-1">
                            <div
                                class="col-4 text-center bg-success text-white py-1 rounded-left small font-weight-bold">
                                OK</div>
                            <div class="col-8">
                                <input type="number" class="form-control form-control-sm text-center" name="total_ok"
                                    value="{{ $checksheet->total_ok }}" min="0" required>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div
                                class="col-4 text-center bg-danger text-white py-1 rounded-left small font-weight-bold">
                                NG</div>
                            <div class="col-8">
                                <input type="number" class="form-control form-control-sm text-center" name="total_ng"
                                    value="{{ $checksheet->total_ng }}" min="0" required>
                            </div>
                        </div>
                    </td>

                    <!-- Judgment -->
                    <td class="align-middle text-center">
                        <select class="form-control font-weight-bold" name="judgment" id="judgmentSelect" required>
                            <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }} class="text-success">
                                OK</option>
                            <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }} class="text-danger">
                                NG</option>
                        </select>
                    </td>

                    <!-- Inisial QC -->
                    <td class="align-middle">
                        <input type="text" class="form-control text-center" name="operator_initials"
                            value="{{ $checksheet->operator_initials }}" required>
                    </td>

                    <!-- Keterangan -->
                    <td class="align-middle">
                        <div class="form-group mb-2" id="nextProsesContainer"
                            style="{{ $checksheet->judgment == 'NG' ? '' : 'display: none;' }}">
                            <label class="font-weight-bold text-danger">Next Proses:</label>
                            <select class="form-control form-control-sm" name="next_proses">
                                <option value="">-- Pilih --</option>
                                <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>
                                    CRUSHING</option>
                                <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR
                                </option>
                                <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>
                                    FINISHING</option>
                                <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR
                                </option>
                            </select>
                        </div>
                        <textarea class="form-control" name="remarks" rows="4">{{ $checksheet->remarks }}</textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-md-12 text-right">
            <input type="hidden" name="cycle_time" value="{{ $checksheet->cycle_time }}">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Data Sortir
            </button>
        </div>
    </div>
</form>

<script>
    (function () {
        function updateTotals() {
            var totalNG = 0;
            $('input[name="defect_quantities[]"]').each(function () {
                totalNG += parseInt($(this).val()) || 0;
            });
            $('input[name="total_ng"]').val(totalNG);

            var sampling = parseInt($('input[name="sampling_qty"]').val()) || 0;
            $('input[name="total_ok"]').val(Math.max(0, sampling - totalNG));

            if (totalNG > 0) {
                $('#judgmentSelect').val('NG').removeClass('text-success').addClass('text-danger');
                $('#nextProsesContainer').slideDown();
            } else {
                $('#judgmentSelect').val('OK').removeClass('text-danger').addClass('text-success');
                $('#nextProsesContainer').slideUp();
            }
        }

        $(document).on('input', 'input[name="defect_quantities[]"], input[name="sampling_qty"]', updateTotals);

        // Bind click event for dynamic elements using delegation if necessary, or direct binding
        // Since this script runs every time modal opens, we should be careful about duplicate bindings on document
        // We can scope events to the form
        $('#editSortirForm').on('click', '#addDefectBtn', function () {
            var newRow = `
                    <div class="input-group mb-2 defect-row">
                        <input type="text" class="form-control" name="defect_types[]" placeholder="Jenis">
                        <input type="number" class="form-control" name="defect_quantities[]" placeholder="Qty" min="1">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger btn-sm remove-defect"><i class="fas fa-times"></i></button>
                        </div>
                    </div>`;
            $(this).prev('#defectContainer').append(newRow);
        });

        $('#editSortirForm').on('click', '.remove-defect', function () {
            $(this).closest('.defect-row').remove();
            updateTotals();
        });

        $('#judgmentSelect').on('change', function () {
            if ($(this).val() === 'NG') {
                $('#nextProsesContainer').slideDown();
            } else {
                $('#nextProsesContainer').slideUp();
            }
        });
    })();
</script>