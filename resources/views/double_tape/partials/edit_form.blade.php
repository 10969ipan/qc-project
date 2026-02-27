<form action="{{ route('double_tape.update', $checksheet->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header bg-warning">
        <h5 class="modal-title font-weight-bold">Edit Double Tape #{{ $checksheet->id }}</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 border-right">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Item Part</label>
                    <select class="form-control form-control-sm" name="item_id" required>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-6">
                        <input type="date" class="form-control form-control-sm" name="date"
                            value="{{ $checksheet->date->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <select class="form-control form-control-sm" name="shift" required>
                            <option value="1" {{ $checksheet->shift == 1 ? 'selected' : '' }}>1</option>
                            <option value="2" {{ $checksheet->shift == 2 ? 'selected' : '' }}>2</option>
                            <option value="3" {{ $checksheet->shift == 3 ? 'selected' : '' }}>3</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <label class="small font-weight-bold">Total Qty</label>
                        <input type="number" class="form-control form-control-sm" name="total_qty" id="editTotalQty"
                            value="{{ $checksheet->total_qty }}" required>
                    </div>
                    <div class="col-6">
                        <label class="small font-weight-bold">Sampling Qty</label>
                        <input type="number" class="form-control form-control-sm" name="sampling_qty"
                            id="editSamplingQty" value="{{ $checksheet->sampling_qty }}" required>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-4">
                        <label class="small font-weight-bold">OK</label>
                        <input type="number" class="form-control form-control-sm" name="total_ok"
                            value="{{ $checksheet->total_ok }}" readonly>
                    </div>
                    <div class="col-4">
                        <label class="small font-weight-bold">NG</label>
                        <input type="number" class="form-control form-control-sm" name="total_ng" id="editTotalNG"
                            value="{{ $checksheet->total_ng }}" required>
                    </div>
                    <div class="col-4">
                        <label class="small font-weight-bold">Judgment</label>
                        <select class="form-control form-control-sm font-weight-bold" name="judgment" id="editJudgment"
                            required>
                            <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mt-2">
                    <label class="small font-weight-bold">Operator</label>
                    <input type="text" class="form-control form-control-sm" name="operator_initials"
                        value="{{ $checksheet->operator_initials }}" required>
                </div>
                <textarea class="form-control form-control-sm mt-2" name="remarks"
                    rows="2">{{ $checksheet->remarks }}</textarea>
                <div class="form-group mb-0 mt-2" id="editNextProsesContainer"
                    style="{{ $checksheet->judgment == 'NG' ? '' : 'display:none;' }}">
                    <label class="small font-weight-bold text-danger">Next Proses (If NG)</label>
                    <select class="form-control form-control-sm" name="next_proses" id="editNextProses">
                        <option value="">-- Pilih --</option>
                        <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING
                        </option>
                        <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR
                        </option>
                        <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR
                        </option>
                        <option value="MARKING+FINISHING+PACKING" {{ $checksheet->next_proses == 'MARKING+FINISHING+PACKING' ? 'selected' : '' }}>
                            MARKING+FINISHING+PACKING</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
    $(document).ready(function () {
        function toggleNextProses() {
            if ($('#editJudgment').val() === 'NG') {
                $('#editNextProsesContainer').slideDown();
            } else {
                $('#editNextProsesContainer').slideUp();
                $('#editNextProses').val('');
            }
        }

        function updateEditJudgment() {
            let total = parseInt($('#editSamplingQty').val()) || 0;
            let ng = parseInt($('#editTotalNG').val()) || 0;
            let ok = total - ng;
            $('input[name="total_ok"]').val(ok < 0 ? 0 : ok);

            let judgment = ng > 0 ? 'NG' : 'OK';
            $('#editJudgment').val(judgment);

            if (judgment === 'OK') {
                $('#editJudgment').removeClass('text-danger').addClass('text-success');
            } else {
                $('#editJudgment').removeClass('text-success').addClass('text-danger');
            }

            toggleNextProses();
        }

        $('#editTotalNG, #editSamplingQty').on('input', updateEditJudgment);
        $('#editJudgment').on('change', toggleNextProses);
    });
</script>