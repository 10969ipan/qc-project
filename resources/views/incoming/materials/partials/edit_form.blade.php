<form action="{{ route('incoming.materials.update', $checksheet->id) }}" method="POST" id="editChecksheetForm">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6 form-group">
            <label class="font-weight-bold">Material Name</label>
            <select class="form-control select2" name="item_id" required>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                        data-defects="{{ json_encode($item->defects) }}">
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 form-group">
            <label class="font-weight-bold">Tanggal Check</label>
            <input type="date" class="form-control" name="date" value="{{ $checksheet->date }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label class="font-weight-bold">Tanggal Datang</label>
            <input type="date" class="form-control" name="tanggal_datang" value="{{ $checksheet->tanggal_datang }}"
                required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">Lot/Batch Number</label>
            <input type="text" class="form-control" name="lot_batch_number" value="{{ $checksheet->lot_batch_number }}"
                required>
        </div>
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">Expired Date</label>
            <input type="date" class="form-control" name="expired_date" value="{{ $checksheet->expired_date }}"
                required>
        </div>
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">Judgment</label>
            <select class="form-control font-weight-bold" name="judgment" id="editJudgmentSelect" required>
                <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">Quantity (Kg)</label>
            <input type="number" step="0.01" class="form-control" name="quantity_kg"
                value="{{ $checksheet->quantity_kg }}" required>
        </div>
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">Komper/Karung (Kg)</label>
            <input type="number" step="0.01" class="form-control" name="komper_karung_kg"
                value="{{ $checksheet->komper_karung_kg }}" required>
        </div>
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">Sampling Size (Kg)</label>
            <input type="number" step="0.01" class="form-control" name="sampling_size_karung_kg"
                value="{{ $checksheet->sampling_size_karung_kg }}" required>
        </div>
    </div>

    <div class="form-group">
        <label class="font-weight-bold text-danger">Defect Details (NG)</label>
        <div id="editDefectContainer">
            @php
                $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
            @endphp
            @forelse($defects ?? [] as $d)
                <div class="input-group mb-2 edit-defect-row">
                    <select class="form-control edit-defect-select" name="defect_types[]">
                        <option value="{{ $d['type'] }}">{{ $d['type'] }}</option>
                    </select>
                    <input type="number" class="form-control edit-defect-qty" name="defect_quantities[]"
                        value="{{ $d['qty'] }}" placeholder="Qty" min="1" style="max-width: 80px;">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-defect-btn"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            @empty
                <div class="input-group mb-2 edit-defect-row">
                    <select class="form-control edit-defect-select" name="defect_types[]">
                        <option value="">-- Defect --</option>
                    </select>
                    <input type="number" class="form-control edit-defect-qty" name="defect_quantities[]" placeholder="Qty"
                        min="1" style="max-width: 80px;">
                </div>
            @endforelse
        </div>
        <button type="button" id="editAddDefectBtn" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i>
            Tambah</button>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label class="font-weight-bold">QC Initials</label>
            <input type="text" class="form-control" name="operator_initials"
                value="{{ $checksheet->operator_initials }}" required>
        </div>
        <div class="col-md-8 form-group">
            <label class="font-weight-bold">Remarks</label>
            <textarea class="form-control" name="remarks" rows="2">{{ $checksheet->remarks }}</textarea>
        </div>
    </div>

    <div class="text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>