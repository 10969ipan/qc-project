<form action="{{ route('admin.checksheets.update', ['checksheet' => $checksheet->id, 'plant' => request('plant')]) }}"
    method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="plant" value="{{ request('plant') }}">

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="item_id">Item Part</label>
                <select name="item_id" id="item_id_edit" class="form-control" required>
                    <option value="" disabled style="font-weight: bold; color: #6c757d;">Pilih Item Part
                    </option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" 
                            data-defects="{{ json_encode($item->defects) }}"
                            {{ $checksheet->item_id == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} ({{ $item->customer }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="date">Tanggal</label>
                <input type="date" name="date" id="date_edit" class="form-control" 
                    value="{{ $checksheet->date ? $checksheet->date->format('Y-m-d') : '' }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="shift">Shift</label>
                <select name="shift" id="shift_edit" class="form-control" required>
                    <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                    <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                    <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                </select>
            </div>
            @php
                $plant = strtolower(auth()->user()->plant ?? $checksheet->plant ?? '');
                $tableOptions = range(1, 15);
                if ($plant === 'jakarta') {
                    $tableOptions = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
                }
            @endphp
            <div class="form-group">
                <label for="line">Meja</label>
                <select name="line" id="line_edit" class="form-control" required>
                    <option value="">Pilih Meja</option>
                    @foreach ($tableOptions as $i)
                        <option value="{{ $i }}" {{ $checksheet->line == $i ? 'selected' : '' }}>Meja {{ $i }}
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
                    <input type="time" name="jam_before" id="jam_before_edit" class="form-control"
                        value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="jam_after">Jam (After)</label>
                    <input type="time" name="jam_after" id="jam_after_edit" class="form-control"
                        value="{{ $checksheet->created_at->format('H:i') }}">
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="total_qty">Total Qty</label>
                <input type="number" name="total_qty" id="total_qty_edit" class="form-control"
                    value="{{ $checksheet->total_qty }}" min="0" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="sampling_qty">Sampling Qty</label>
                <input type="number" name="sampling_qty" id="sampling_qty_edit" class="form-control"
                    value="{{ $checksheet->sampling_qty }}" min="0" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="total_ok">Total OK</label>
                <input type="number" name="total_ok" id="total_ok_edit" class="form-control"
                    value="{{ $checksheet->total_ok }}" min="0" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="total_ng">Total NG</label>
                <input type="number" name="total_ng" id="total_ng_edit" class="form-control"
                    value="{{ $checksheet->total_ng }}" min="0" required>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <label class="font-weight-bold text-dark d-block mb-1">Defect List (NG):</label>
            <div id="defectContainer_edit">
                @php
                    $existingDefects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects ?? '[]', true);
                    $itemDefects = $checksheet->item->defects ?? [];
                @endphp
                
                @if(count($existingDefects) > 0)
                    @foreach($existingDefects as $index => $defect)
                        <div class="input-group mb-2 defect-row-edit">
                            <select class="form-control defect-select-edit" style="min-width: 180px;" name="defect_types[]">
                                <option value="">-- Pilih Defect --</option>
                                @foreach($itemDefects as $idft)
                                    <option value="{{ $idft }}" {{ ($defect['type'] ?? '') == $idft ? 'selected' : '' }}>{{ $idft }}</option>
                                @endforeach
                            </select>
                            <input type="number" class="form-control defect-qty-edit" style="max-width: 100px;" name="defect_quantities[]" value="{{ $defect['qty'] ?? 1 }}" placeholder="Qty" min="1">
                            @if($index > 0)
                                <div class="input-group-append">
                                    <button class="btn btn-danger btn-sm remove-defect-btn-edit" type="button"><i class="fas fa-minus"></i></button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="input-group mb-2 defect-row-edit">
                        <select class="form-control defect-select-edit" style="min-width: 180px;" name="defect_types[]">
                            <option value="">-- Pilih Defect --</option>
                            @foreach($itemDefects as $idft)
                                <option value="{{ $idft }}">{{ $idft }}</option>
                            @endforeach
                        </select>
                        <input type="number" class="form-control defect-qty-edit" style="max-width: 100px;" name="defect_quantities[]" placeholder="Qty" min="1">
                    </div>
                @endif
            </div>
            <button type="button" id="addDefectBtn_edit" class="btn btn-info btn-sm mt-1" style="display: {{ $checksheet->total_ng > 0 ? 'inline-block' : 'none' }};">
                <i class="fas fa-plus"></i> Tambah Jenis
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="judgment">Judgment</label>
                <select name="judgment" id="judgment_edit" class="form-control" required>
                    <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                    <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="operator_initials">Inisial Operator</label>
                <input type="text" name="operator_initials" id="operator_initials_edit" class="form-control"
                    value="{{ $checksheet->operator_initials }}">
            </div>
        </div>
    </div>

    <div id="nextProsesContainer_edit" style="display: {{ $checksheet->judgment == 'NG' ? 'block' : 'none' }};">
        <div class="form-group">
            <label for="next_proses" class="text-danger font-weight-bold">Next Proses</label>
            <select name="next_proses" id="next_proses_edit" class="form-control">
                <option value="">-- Pilih Next Proses --</option>
                <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING
                </option>
                <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR
                </option>
                <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>
                    FINISHING</option>
                <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR
                </option>
                <option value="MARKING+FINISHING+PACKING" {{ $checksheet->next_proses == 'MARKING+FINISHING+PACKING' ? 'selected' : '' }}>MARKING+FINISHING+PACKING</option>
                @if($checksheet->next_proses && !in_array($checksheet->next_proses, ['CRUSHING', 'SORTIR', 'FINISHING', 'REPAIR', 'MARKING+FINISHING+PACKING']))
                    <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                @endif
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="remarks">Keterangan</label>
        <textarea name="remarks" id="remarks_edit" class="form-control" rows="3">{{ $checksheet->remarks }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form>

<script>
    (function () {
        // Use suffixed IDs to avoid conflict
        const judgmentSelect = document.getElementById('judgment_edit');
        const nextProsesContainer = document.getElementById('nextProsesContainer_edit');
        const nextProsesSelect = document.getElementById('next_proses_edit');
        const totalNgInput = document.getElementById('total_ng_edit');
        const totalOkInput = document.getElementById('total_ok_edit');
        const samplingQtyInput = document.getElementById('sampling_qty_edit');
        const addDefectBtn = $('#addDefectBtn_edit');
        const defectContainer = $('#defectContainer_edit');

        function toggleNextProses() {
            if (judgmentSelect.value === 'NG') {
                $(nextProsesContainer).slideDown();
            } else {
                $(nextProsesContainer).slideUp();
                nextProsesSelect.value = '';
            }
        }

        function calculateTotalNG() {
            let total = 0;
            $('.defect-qty-edit').each(function () {
                total += parseInt($(this).val()) || 0;
            });
            totalNgInput.value = total;
            
            // Sync Total OK
            const sampling = parseInt(samplingQtyInput.value) || 0;
            totalOkInput.value = Math.max(0, sampling - total);
            
            if (total > 0) {
                judgmentSelect.value = 'NG';
                addDefectBtn.show();
            } else {
                judgmentSelect.value = 'OK';
                addDefectBtn.hide();
            }
            toggleNextProses();
        }

        judgmentSelect.addEventListener('change', toggleNextProses);
        samplingQtyInput.addEventListener('input', calculateTotalNG);

        $(document).on('input', '.defect-qty-edit', calculateTotalNG);

        addDefectBtn.click(function () {
            const firstRow = $('.defect-row-edit').first();
            const newRow = $('<div class="input-group mb-2 defect-row-edit">' +
                '<select class="form-control defect-select-edit" style="min-width: 180px;" name="defect_types[]">' +
                firstRow.find('select').html() +
                '</select>' +
                '<input type="number" class="form-control defect-qty-edit" style="max-width: 100px;" name="defect_quantities[]" placeholder="Qty" min="1">' +
                '<div class="input-group-append">' +
                '<button class="btn btn-danger btn-sm remove-defect-btn-edit" type="button"><i class="fas fa-minus"></i></button>' +
                '</div>' +
                '</div>');
            defectContainer.append(newRow);
            if ($('.defect-row-edit').length >= 5) addDefectBtn.hide();
        });

        $(document).on('click', '.remove-defect-btn-edit', function () {
            $(this).closest('.defect-row-edit').remove();
            calculateTotalNG();
            if ($('.defect-row-edit').length < 5 && parseInt(totalNgInput.value) > 0) addDefectBtn.show();
        });

        // Handle item change to update defect options
        $('#item_id_edit').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            let defects = selectedOption.data('defects');
            
            if (typeof defects === 'string') defects = JSON.parse(defects);
            
            let optionsHtml = '<option value="">-- Pilih Defect --</option>';
            if (Array.isArray(defects)) {
                defects.forEach(d => {
                    optionsHtml += `<option value="${d}">${d}</option>`;
                });
            }
            
            // Update all select dropdowns
            $('.defect-select-edit').html(optionsHtml);
        });
    })();
</script>
