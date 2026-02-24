<form action="{{ route('plating.update', $checksheet->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header bg-warning">
        <h5 class="modal-title font-weight-bold text-dark">Edit Checksheet Plating #{{ $checksheet->id }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 border-right">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Item Part</label>
                    <select class="form-control form-control-sm select2" name="item_id" required>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->part_number ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label
                                class="small font-weight-bold font-italic text-primary border-bottom pb-1 w-100">Injection
                                (Tgl/Shf)</label>
                            <input type="date" class="form-control form-control-sm mb-1" name="injection_date"
                                value="{{ $checksheet->injection_date ? $checksheet->injection_date->format('Y-m-d') : '' }}">
                            <select class="form-control form-control-sm" name="injection_shift">
                                <option value="">- Shift -</option>
                                <option value="1" {{ $checksheet->injection_shift == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $checksheet->injection_shift == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $checksheet->injection_shift == 3 ? 'selected' : '' }}>3</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold font-italic text-info border-bottom pb-1 w-100">Plating
                                (Tgl/Shf/Lot)</label>
                            <input type="date" class="form-control form-control-sm mb-1" name="plating_date"
                                value="{{ $checksheet->plating_date ? $checksheet->plating_date->format('Y-m-d') : '' }}">
                            <select class="form-control form-control-sm mb-1" name="plating_shift">
                                <option value="">- Shift -</option>
                                <option value="1" {{ $checksheet->plating_shift == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $checksheet->plating_shift == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $checksheet->plating_shift == 3 ? 'selected' : '' }}>3</option>
                            </select>
                            <input type="text" class="form-control form-control-sm font-weight-bold" name="no_lot"
                                value="{{ $checksheet->no_lot }}" placeholder="No Lot">
                        </div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Quality (Tanggal)</label>
                            <input type="date" class="form-control form-control-sm" name="date"
                                value="{{ $checksheet->date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Quality (Shift)</label>
                            <select class="form-control form-control-sm" name="shift" required>
                                <option value="1" {{ $checksheet->shift == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $checksheet->shift == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $checksheet->shift == 3 ? 'selected' : '' }}>3</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Meja</label>
                    <select name="line" class="form-control form-control-sm" required>
                        @foreach(range(1, 15) as $i)
                            <option value="{{ $i }}" {{ $checksheet->line == $i ? 'selected' : '' }}>Meja {{ $i }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Total Qty</label>
                            <input type="number" class="form-control form-control-sm" name="total_qty" id="editTotalQty"
                                value="{{ $checksheet->total_qty }}" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Check Qty (100%)</label>
                            <input type="number" class="form-control form-control-sm" name="sampling_qty"
                                id="editSamplingQty" value="{{ $checksheet->sampling_qty }}" required readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-4">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Total OK</label>
                            <input type="number" class="form-control form-control-sm text-success font-weight-bold"
                                name="total_ok" value="{{ $checksheet->total_ok }}" readonly>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Total NG</label>
                            <input type="number" class="form-control form-control-sm text-danger font-weight-bold"
                                name="total_ng" id="editTotalNG" value="{{ $checksheet->total_ng }}" required>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Judgment</label>
                            <select class="form-control form-control-sm font-weight-bold" name="judgment"
                                id="editJudgment" required>
                                <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}
                                    class="text-success">OK</option>
                                <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}
                                    class="text-danger">NG</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Inisial Operator</label>
                    <input type="text" class="form-control form-control-sm" name="operator_initials"
                        value="{{ $checksheet->operator_initials }}" required>
                </div>
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Remarks</label>
                    <textarea class="form-control form-control-sm" name="remarks"
                        rows="2">{{ $checksheet->remarks }}</textarea>
                </div>
                <div class="form-group mb-0" id="editNextProses"
                    style="{{ $checksheet->judgment == 'NG' ? '' : 'display:none;' }}">
                    <label class="small font-weight-bold">Next Proses (If NG)</label>
                    <select class="form-control form-control-sm" name="next_proses">
                        <option value="">-- Pilih --</option>
                        <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING
                        </option>
                        <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR
                        </option>
                        <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('#editTotalQty').on('input', function () {
            $('#editSamplingQty').val($(this).val()).trigger('input');
        });

        $('#editTotalNG, #editSamplingQty').on('input', function () {
            let total = parseInt($('#editSamplingQty').val()) || 0;
            let ng = parseInt($('#editTotalNG').val()) || 0;
            let ok = total - ng;
            $('input[name="total_ok"]').val(ok < 0 ? 0 : ok);
            $('#editJudgment').val(ng > 0 ? 'NG' : 'OK').trigger('change');
        });

        $('#editJudgment').change(function () {
            if ($(this).val() === 'NG') {
                $('#editNextProses').slideDown();
            } else {
                $('#editNextProses').slideUp();
            }
        });
    });
</script>