<form action="{{ route('incoming.materials.update', $checksheet->id) }}" method="POST" id="editChecksheetForm">
    @csrf
    @method('PUT')

    <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">INFORMASI MATERIAL & WAKTU</div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="small font-weight-bold text-gray-700">Material Name</label>
            <select class="form-control form-control-sm border-0 shadow-sm select2" name="item_id" required>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                        data-defects="{{ json_encode($item->defects) }}">
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Supplier</label>
            <input type="text" class="form-control form-control-sm border-0 shadow-sm bg-light" value="{{ $checksheet->item->customer ?? '-' }}" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Part No</label>
            <input type="text" class="form-control form-control-sm border-0 shadow-sm bg-light" value="{{ $checksheet->item->part_number ?? '-' }}" readonly>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Tanggal Check</label>
            <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="date" value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Jam (Before)</label>
            <input type="time" class="form-control form-control-sm border-0 shadow-sm bg-light" value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Jam (After)</label>
            <input type="time" class="form-control form-control-sm border-0 shadow-sm bg-light" value="{{ $checksheet->created_at->format('H:i') }}" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Cycle Time (s)</label>
            <input type="text" class="form-control form-control-sm border-0 shadow-sm bg-light" value="{{ $checksheet->cycle_time ?? '-' }}" readonly>
        </div>
    </div>

    <div class="font-weight-bold text-primary mt-4 mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">DATA KEDATANGAN</div>
    <div class="row">
        <div class="col-md-4 form-group">
            <label class="small font-weight-bold text-gray-700">Tanggal Datang</label>
            <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="tanggal_datang" value="{{ \Carbon\Carbon::parse($checksheet->tanggal_datang)->format('Y-m-d') }}" required>
        </div>
        <div class="col-md-4 form-group">
            <label class="small font-weight-bold text-gray-700">Expired Date</label>
            <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="expired_date" value="{{ \Carbon\Carbon::parse($checksheet->expired_date)->format('Y-m-d') }}" required>
        </div>
        <div class="col-md-4 form-group">
            <label class="small font-weight-bold text-gray-700">Lot/Batch Number</label>
            <input type="text" class="form-control form-control-sm border-0 shadow-sm" name="lot_batch_number" value="{{ $checksheet->lot_batch_number }}" required>
        </div>
    </div>

    <div class="font-weight-bold text-primary mt-4 mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">KUANTITAS & HASIL</div>
    <div class="row">
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Quantity (Kg)</label>
            <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm" name="quantity_kg" value="{{ (float) $checksheet->quantity_kg }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Komper/Karung (Kg)</label>
            <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm" name="komper_karung_kg" value="{{ (float) $checksheet->komper_karung_kg }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Sampling Size (Kg)</label>
            <input type="number" step="any" class="form-control form-control-sm border-0 shadow-sm" name="sampling_size_karung_kg" value="{{ (float) $checksheet->sampling_size_karung_kg }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label class="small font-weight-bold text-gray-700">Judgment</label>
            <select class="form-control form-control-sm border-0 shadow-sm font-weight-bold" name="judgment" id="editJudgmentSelect" required>
                <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
            </select>
        </div>
    </div>

    <div class="font-weight-bold text-primary mt-4 mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">DETAIL DEFECT (NG) & CATATAN</div>
    <div class="form-group">
        <label class="small font-weight-bold text-gray-700">Defect Details (NG)</label>
        <div id="editDefectContainer">
            @php
                $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
            @endphp
            @forelse($defects ?? [] as $d)
                <div class="row no-gutters mb-2 edit-defect-row align-items-center">
                    <div class="col-8 pr-1">
                        <select class="form-control form-control-sm border-0 shadow-sm edit-defect-select font-weight-bold" name="defect_types[]">
                            <option value="{{ $d['type'] }}">{{ $d['type'] }}</option>
                        </select>
                    </div>
                    <div class="col-3 pr-1">
                        <input type="number" class="form-control form-control-sm border-0 shadow-sm edit-defect-qty text-center font-weight-bold" name="defect_quantities[]" value="{{ $d['qty'] }}" min="1">
                    </div>
                    <div class="col-1 text-center">
                        <button type="button" class="btn btn-link text-danger p-0 remove-defect-btn"><i class="fas fa-times-circle"></i></button>
                    </div>
                </div>
            @empty
                <div class="row no-gutters mb-2 edit-defect-row align-items-center">
                    <div class="col-8 pr-1">
                        <select class="form-control form-control-sm border-0 shadow-sm edit-defect-select font-weight-bold" name="defect_types[]">
                            <option value="">-- Pilih Defect --</option>
                        </select>
                    </div>
                    <div class="col-3 pr-1">
                        <input type="number" class="form-control form-control-sm border-0 shadow-sm edit-defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1">
                    </div>
                    <div class="col-1 text-center"></div>
                </div>
            @endforelse
        </div>
        <button type="button" id="editAddDefectBtn" class="btn btn-outline-info btn-sm mt-1"><i class="fas fa-plus"></i> Tambah</button>
    </div>

    <div class="row mt-3">
        <div class="col-md-4 form-group">
            <label class="small font-weight-bold text-gray-700">QC Initials</label>
            <input type="text" class="form-control form-control-sm border-0 shadow-sm" name="operator_initials" value="{{ $checksheet->operator_initials }}" required>
        </div>
        <div class="col-md-8 form-group">
            <label class="small font-weight-bold text-gray-700">Remarks</label>
            <textarea class="form-control form-control-sm border-0 shadow-sm" name="remarks" rows="2">{{ $checksheet->remarks }}</textarea>
        </div>
    </div>

    <div class="text-right mt-4 pt-3" style="border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-light border shadow-sm" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary shadow-sm" id="btnSimpanEdit">Simpan Perubahan</button>
    </div>
</form>

<script>
    $('#editChecksheetForm').on('submit', function(e) {
        var isValid = true;
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap!',
                text: 'Pastikan semua kolom yang wajib (bertanda merah/required) sudah terisi sebelum menyimpan.',
                confirmButtonColor: '#4e73df'
            });
        }
    });

    $('#editChecksheetForm input, #editChecksheetForm select').on('change', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
    });
</script>
