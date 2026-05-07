<form id="editSortirForm" class="ajax-form"
    action="{{ route('sortir.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    {{-- Preserve filter parameters --}}
    @foreach(request()->all() as $key => $value)
        @if(!in_array($key, ['_token', '_method', 'id', 'plant']))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="row">
        <!-- Left Column: Source & Metadata -->
        <div class="col-md-6 border-right">
            <div class="form-group mb-2">
                <label class="small font-weight-bold">Item Part</label>
                <input type="text" class="form-control form-control-sm bg-light"
                    value="{{ $checksheet->item->name }} ({{ $checksheet->item->part_number }})" readonly>
                <input type="hidden" name="item_id" value="{{ $checksheet->item_id }}">
            </div>

            @if(auth()->user()->role === 'admin')
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Plant <span class="text-danger">*</span></label>
                    <select name="plant" class="form-control form-control-sm" required>
                        @foreach(\App\Models\Plant::all() as $p)
                            <option value="{{ $p->code }}" {{ $checksheet->plant_id == $p->id ? 'selected' : '' }}>{{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control form-control-sm"
                            value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Shift <span class="text-danger">*</span></label>
                        <select name="shift" class="form-control form-control-sm" required>
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
                        <label class="small font-weight-bold">Meja/Line</label>
                        <select name="line" class="form-control form-control-sm">
                            <option value="">-- Pilih --</option>
                            @php
                                $plant = strtolower(auth()->user()->plant->code ?? request('plant') ?? '');
                                $tableOptions = range(1, 15);
                                if ($plant === 'jakarta') {
                                    $tableOptions = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
                                }
                            @endphp
                            @foreach ($tableOptions as $i)
                                <option value="{{ $i }}" {{ $checksheet->line == $i ? 'selected' : '' }}>Meja {{ $i }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Inisial QC <span class="text-danger">*</span></label>
                        <input type="text" name="operator_initials" class="form-control form-control-sm"
                            value="{{ $checksheet->operator_initials }}" placeholder="Inisial..." required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total Qty <span class="text-danger">*</span></label>
                        <input type="number" name="total_qty" class="form-control form-control-sm"
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

            <div class="form-group mb-0">
                <label class="small font-weight-bold text-muted">Sumber Data:</label>
                <div class="p-2 bg-light rounded border small">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ strtoupper(str_replace('_', ' ', $checksheet->source_type)) }} (ID: {{ $checksheet->source_id }})
                </div>
            </div>
        </div>

        <!-- Right Column: Defects, Judgment, Remarks -->
        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="small font-weight-bold">Detail NG (Defect List)</label>
                <div id="defectContainer">
                    @php
                        $defects = is_array($checksheet->defects) ? $checksheet->defects : (json_decode($checksheet->defects, true) ?: []);
                    @endphp
                    @if(count($defects) > 0)
                        @foreach($defects as $index => $defect)
                            <div class="input-group mb-2 defect-row">
                                <input type="text" class="form-control form-control-sm" name="defect_types[]"
                                    value="{{ $defect['type'] ?? $defect }}" placeholder="Jenis NG">
                                <input type="number" class="form-control form-control-sm defect-qty" name="defect_quantities[]"
                                    value="{{ $defect['qty'] ?? 1 }}" min="1" style="max-width: 80px;">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger btn-xs remove-defect"><i
                                            class="fas fa-minus"></i></button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="input-group mb-2 defect-row">
                            <input type="text" class="form-control form-control-sm" name="defect_types[]"
                                placeholder="Jenis NG">
                            <input type="number" class="form-control form-control-sm defect-qty" name="defect_quantities[]"
                                min="1" style="max-width: 80px;">
                        </div>
                    @endif
                </div>
                <button type="button" id="addDefectBtn" class="btn btn-info btn-xs mt-1">
                    <i class="fas fa-plus"></i> Tambah Jenis
                </button>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total OK (pcs)</label>
                        <input type="number" name="total_ok" id="total_ok" class="form-control form-control-sm bg-light"
                            value="{{ $checksheet->total_ok }}" readonly>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total NG (pcs)</label>
                        <input type="number" name="total_ng" id="total_ng" class="form-control form-control-sm bg-light"
                            value="{{ $checksheet->total_ng }}" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Judgment Final <span class="text-danger">*</span></label>
                        <select name="judgment" id="judgmentSelect"
                            class="form-control form-control-sm font-weight-bold" required>
                            <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }} class="text-success">
                                OK</option>
                            <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }} class="text-danger">
                                NG</option>
                        </select>
                    </div>
                </div>
                <div class="col-6" id="nextProsesContainer"
                    style="display: {{ $checksheet->judgment == 'NG' ? 'block' : 'none' }};">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-danger">Next Proses <span
                                class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="nextProses" name="next_proses">
                            <option value="">-- Pilih --</option>
                            @foreach($nextProcesses as $opt)
                                <option value="{{ $opt->name }}" {{ $checksheet->next_proses == $opt->name ? 'selected' : '' }}>{{ $opt->name }}</option>
                            @endforeach
                            @if($checksheet->next_proses && !$nextProcessesGlobal->pluck('name')->contains($checksheet->next_proses))
                                <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="small font-weight-bold">Keterangan / Remarks</label>
                <textarea name="remarks" class="form-control form-control-sm" rows="3"
                    placeholder="Keterangan tambahan...">{{ $checksheet->remarks }}</textarea>
            </div>
        </div>
    </div>

    <div class="mt-4 text-right">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
            <i class="fas fa-save mr-1"></i> Update Data
        </button>
    </div>
</form>

<script>
    (function () {
        function updateTotals() {
            var totalNG = 0;
            $('.defect-qty').each(function () {
                totalNG += parseInt($(this).val()) || 0;
            });
            $('#total_ng').val(totalNG);

            var sampling = parseInt($('#sampling_qty').val()) || 0;
            $('#total_ok').val(Math.max(0, sampling - totalNG));

            var $judgment = $('#judgmentSelect');
            if (totalNG > 0) {
                $judgment.val('NG').removeClass('text-success').addClass('text-danger');
                $('#nextProsesContainer').slideDown();
            } else {
                $judgment.val('OK').removeClass('text-danger').addClass('text-success');
                $('#nextProsesContainer').slideUp();
                $('#nextProses').val('');
            }
        }

        $(document).on('input', '.defect-qty, #sampling_qty', updateTotals);

        $('#addDefectBtn').on('click', function () {
            var newRow = `
                <div class="input-group mb-2 defect-row">
                    <input type="text" class="form-control form-control-sm" name="defect_types[]" placeholder="Jenis NG">
                    <input type="number" class="form-control form-control-sm defect-qty" name="defect_quantities[]" value="1" min="1" style="max-width: 80px;">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger btn-xs remove-defect"><i class="fas fa-minus"></i></button>
                    </div>
                </div>`;
            $('#defectContainer').append(newRow);
            updateTotals();
        });

        $(document).on('click', '.remove-defect', function () {
            $(this).closest('.defect-row').remove();
            updateTotals();
        });

        $('#judgmentSelect').on('change', function () {
            if ($(this).val() === 'NG') {
                $('#nextProsesContainer').slideDown();
            } else {
                $('#nextProsesContainer').slideUp();
                $('#nextProses').val('');
            }
        });
    })();
</script>

<style>
    .btn-xs {
        padding: 1px 5px;
        font-size: 12px;
        line-height: 1.5;
        border-radius: 3px;
    }
</style>
