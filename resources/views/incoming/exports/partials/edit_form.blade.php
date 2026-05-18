<form action="{{ route('incoming.exports.update', $checksheet->id) }}" method="POST" id="editChecksheetForm">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="font-weight-bold">Item Part Name</label>
            <select class="form-control select2" name="item_id" required>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                        data-defects="{{ json_encode($item->defects) }}">
                        {{ $item->name }} ({{ $item->part_number }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 form-group">
            <label class="font-weight-bold">Tanggal Check</label>
            <input type="date" class="form-control" name="date" value="{{ $checksheet->date }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label class="font-weight-bold">Tanggal Delivery</label>
            <input type="date" class="form-control" name="tanggal_delivery" value="{{ $checksheet->tanggal_delivery }}"
                required>
        </div>
    </div>

    <div class="form-group">
        <label class="font-weight-bold text-danger">Defect Details (NG)</label>
        <div id="editDefectContainer">
            @php $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true); @endphp
            @forelse($defects ?? [] as $d)
                <div class="row no-gutters mb-2 edit-defect-row align-items-center bg-white p-1 rounded shadow-sm">
                    <div class="col-8 pr-1">
                        <select class="form-control edit-defect-select font-weight-bold" name="defect_types[]">
                            <option value="{{ $d['type'] }}">{{ $d['type'] }}</option>
                        </select>
                    </div>
                    <div class="col-3 pr-1">
                        <input type="number" class="form-control edit-defect-qty text-center font-weight-bold" name="defect_quantities[]"
                            value="{{ $d['qty'] }}" min="1">
                    </div>
                    <div class="col-1 text-center">
                        <button type="button" class="btn btn-link text-danger p-0 remove-defect-btn"><i class="fas fa-times-circle"></i></button>
                    </div>
                </div>
            @empty
                <div class="row no-gutters mb-2 edit-defect-row align-items-center bg-white p-1 rounded shadow-sm">
                    <div class="col-8 pr-1">
                        <select class="form-control edit-defect-select font-weight-bold" name="defect_types[]">
                            <option value="">-- Pilih Defect --</option>
                        </select>
                    </div>
                    <div class="col-3 pr-1">
                        <input type="number" class="form-control edit-defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty"
                            min="1">
                    </div>
                    <div class="col-1 text-center"></div>
                </div>
            @endforelse
        </div>
        <button type="button" id="editAddDefectBtn" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i>
            Tambah</button>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">Judgment</label>
            <select class="form-control font-weight-bold" name="judgment" required>
                <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">QC Initials</label>
            <input type="text" class="form-control text-center" name="operator_initials"
                value="{{ $checksheet->operator_initials }}" required>
        </div>
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">Remarks</label>
            <textarea class="form-control" name="remarks" rows="2">{{ $checksheet->remarks }}</textarea>
        </div>
    </div>

    <div class="text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Perubahan Export</button>
    </div>
</form>
