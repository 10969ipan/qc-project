<form action="{{ route('cross_cut.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST"
    enctype="multipart/form-data" id="formEditCrossCut">
    @csrf
    @method('PUT')
    <input type="hidden" name="plant" value="{{ request('plant') }}">

    <div class="row">
        <!-- Kolom 1: Informasi Part & Shift -->
        <div class="col-md-4 text-left border-right">
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fas fa-cube mr-1"></i> Informasi Part & Waktu</h6>
            
            <div class="form-group mb-2">
                <label class="small font-weight-bold">Item Part</label>
                <select class="form-control form-control-sm" id="item_id_edit" name="item_id" required>
                    <option value="" disabled style="font-weight: bold; color: #6c757d;">Pilih Item Part</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" data-customer="{{ $item->customer ?? '' }}"
                            data-part-number="{{ $item->part_number ?? '' }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="row">
                <div class="col-6 form-group mb-2">
                    <label class="small font-weight-bold">Customer</label>
                    <input type="text" class="form-control form-control-sm" id="customer_edit" name="customer"
                        value="{{ $checksheet->item->customer ?? '' }}" readonly style="background-color: #e9ecef;">
                </div>
                <div class="col-6 form-group mb-2">
                    <label class="small font-weight-bold">Part No</label>
                    <input type="text" class="form-control form-control-sm" id="part_number_edit" name="part_number"
                        value="{{ $checksheet->item->part_number ?? '' }}" readonly style="background-color: #e9ecef;">
                </div>
            </div>

            <div class="form-group mb-2">
                <label class="small font-weight-bold">Tgl. & Shift Produksi</label>
                <div class="d-flex" style="gap: 5px;">
                    <input type="datetime-local" class="form-control form-control-sm" id="production_datetime_edit" name="production_datetime"
                        value="{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('Y-m-d\TH:i') }}" required>
                    <select class="form-control form-control-sm" id="production_shift_edit" name="production_shift" style="width: 100px;" required>
                        <option value="1" {{ $checksheet->production_shift == 1 ? 'selected' : '' }}>Shift 1</option>
                        <option value="2" {{ $checksheet->production_shift == 2 ? 'selected' : '' }}>Shift 2</option>
                        <option value="3" {{ $checksheet->production_shift == 3 ? 'selected' : '' }}>Shift 3</option>
                    </select>
                </div>
            </div>

            <div class="form-group mb-2">
                <label class="small font-weight-bold">Tgl. & Shift QC</label>
                <div class="d-flex" style="gap: 5px;">
                    <input type="datetime-local" class="form-control form-control-sm" id="qc_datetime_edit" name="qc_datetime"
                        value="{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('Y-m-d\TH:i') }}" required>
                    <select class="form-control form-control-sm" id="qc_shift_edit" name="qc_shift" style="width: 100px;" required>
                        <option value="1" {{ $checksheet->qc_shift == 1 ? 'selected' : '' }}>Shift 1</option>
                        <option value="2" {{ $checksheet->qc_shift == 2 ? 'selected' : '' }}>Shift 2</option>
                        <option value="3" {{ $checksheet->qc_shift == 3 ? 'selected' : '' }}>Shift 3</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-6 form-group mb-0">
                    <label class="small font-weight-bold">Inisial QC</label>
                    <input type="text" class="form-control form-control-sm no-autoupper" id="operator_initials_edit" name="operator_initials"
                        placeholder="Inisial" value="{{ $checksheet->operator_initials }}">
                </div>
            </div>
        </div>

        <!-- Kolom 2: Hasil Pemeriksaan -->
        <div class="col-md-4 text-left border-right">
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fas fa-clipboard-check mr-1"></i> Pemeriksaan & Remark</h6>
            
            <div class="row">
                <div class="col-6 form-group mb-2">
                    <label class="small font-weight-bold">Bak No (Catalyst)</label>
                    <input type="text" class="form-control form-control-sm no-autoupper" name="chemical_catalyst" value="{{ $checksheet->chemical_catalyst }}">
                </div>
                <div class="col-6 form-group mb-2">
                    <label class="small font-weight-bold">Bak No (Abu)</label>
                    <input type="text" class="form-control form-control-sm no-autoupper" name="chemical_abu" value="{{ $checksheet->chemical_abu }}">
                </div>
            </div>

            <div class="row">
                <div class="col-6 form-group mb-2">
                    <label class="small font-weight-bold">Judgment</label>
                    <select class="form-control form-control-sm" name="position_remark_judgment" id="position_remark_judgment_edit" required>
                        <option value="OK" {{ $checksheet->position_remark_judgment == 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="NG" {{ $checksheet->position_remark_judgment == 'NG' ? 'selected' : '' }}>NG</option>
                    </select>
                </div>
                <div class="col-6 form-group mb-2">
                    <label class="small font-weight-bold">No Lot</label>
                    <input type="text" class="form-control form-control-sm" id="position_remark_no_lot_edit" name="position_remark_no_lot" value="{{ $checksheet->position_remark_no_lot }}" required>
                    <small id="noLotHint_edit" class="text-info d-block mt-1 d-none" style="font-size: 0.70rem; line-height: 1.1;"></small>
                </div>
            </div>

            <div id="nextProsesContainer_edit" style="display: {{ $checksheet->position_remark_judgment == 'NG' ? 'block' : 'none' }};">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold text-danger">Next Proses</label>
                    <select name="next_proses" id="next_proses_edit" class="form-control form-control-sm">
                        <option value="">-- Pilih Next Proses --</option>
                        @foreach($nextProcesses as $opt)
                            <option value="{{ $opt->name }}" {{ $checksheet->next_proses == $opt->name ? 'selected' : '' }}>{{ $opt->name }}</option>
                        @endforeach
                        @if($checksheet->next_proses && !$nextProcessesGlobal->pluck('name')->contains($checksheet->next_proses))
                            <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-6 form-group mb-2">
                    <label class="small font-weight-bold">Result Remark</label>
                    <input type="text" class="form-control form-control-sm" id="result_remark_edit" name="result_remark" value="{{ $checksheet->result_remark }}">
                    <small id="remarkHint_edit" class="text-info d-block mt-1 d-none" style="font-size: 0.70rem; line-height: 1.1;"></small>
                </div>
                <div class="col-6 form-group mb-2 mt-4">
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" class="custom-control-input" id="visualOkCheck_edit" name="visual_ok" value="1" {{ $checksheet->visual_ok ? 'checked' : '' }}>
                        <label class="custom-control-label small font-weight-bold" for="visualOkCheck_edit">Visual 100% OK</label>
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-0">
                <label class="small font-weight-bold">Keterangan</label>
                <textarea class="form-control form-control-sm" name="keterangan" id="keteranganInput_edit" rows="2">{{ $checksheet->keterangan }}</textarea>
            </div>
        </div>

        <!-- Kolom 3: Dokumentasi -->
        <div class="col-md-4 text-left">
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fas fa-camera mr-1"></i> Dokumentasi</h6>
            
            <div class="form-group mb-3 text-center">
                <label class="small font-weight-bold d-block text-left">Foto Checksheet</label>
                <div class="mb-2 p-1 border rounded bg-light" style="display: flex; justify-content: center; align-items: center; min-height: 120px;">
                    <img id="previewImage_edit" src="{{ route('cross_cut.image', $checksheet->id) }}" alt="Current Image" style="max-height: 110px; max-width: 100%; object-fit: contain;">
                </div>
                <input type="file" class="form-control-file small" id="image_edit" name="image" accept="image/*">
                <button type="button" id="previewBtn_edit" class="btn btn-info btn-sm mt-2 shadow-sm" style="display: none;" data-toggle="modal" data-target="#imagePreviewModal_edit">Lihat Full Layar</button>
            </div>
        </div>
    </div>

    <!-- Modal Footer style aligned with calibration -->
    <div class="modal-footer bg-light p-2 mt-3 mx-n3 mb-n3 d-flex justify-content-end">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm" id="saveBtn_edit">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal_edit" tabindex="-1" role="dialog" aria-labelledby="imagePreviewModalLabel_edit" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="imagePreviewModalLabel_edit">Preview Foto</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-2 bg-light">
                <img id="previewImageLarge_edit" src="{{ route('cross_cut.image', $checksheet->id) }}" class="img-fluid" alt="Image Preview">
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        $('#item_id_edit').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            var customer = selectedOption.data('customer') || '';
            var partNumber = selectedOption.data('part-number') || '';

            $('#customer_edit').val(customer);
            $('#part_number_edit').val(partNumber);
        });

        $('#image_edit').on('change', function (event) {
            var file = event.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#previewImage_edit').attr('src', e.target.result);
                    $('#previewImageLarge_edit').attr('src', e.target.result);
                    $('#previewBtn_edit').fadeIn();
                }
                reader.readAsDataURL(file);
            }
        });

        // Next Proses logic
        const judgmentSelect = $('#position_remark_judgment_edit');
        const nextProsesContainer = $('#nextProsesContainer_edit');
        const nextProsesSelect = $('#next_proses_edit');

        function toggleNextProses() {
            if (judgmentSelect.val() === 'NG') {
                nextProsesContainer.slideDown();
            } else {
                nextProsesContainer.slideUp();
                nextProsesSelect.val('');
            }
        }

        judgmentSelect.on('change', toggleNextProses);

        // --- Auto-fill Result Remark ---
        var nextRemarkUrl_edit = "{{ route('cross_cut.next_remark') }}";
        var itemSelect_edit    = document.getElementById('item_id_edit');
        var remarkInput_edit   = document.getElementById('result_remark_edit');
        var remarkHint_edit    = document.getElementById('remarkHint_edit');

        function fetchNextRemark_edit() {
            var itemId = itemSelect_edit ? itemSelect_edit.value : '';
            var initials = initialsInput_edit ? initialsInput_edit.value : '';

            if (!itemId) {
                remarkInput_edit.readOnly = false;
                remarkHint_edit.classList.add('d-none');
                return;
            }

            fetch(nextRemarkUrl_edit + '?item_id=' + encodeURIComponent(itemId) + '&operator_initials=' + encodeURIComponent(initials), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.remark && data.count > 0) {
                    remarkInput_edit.value    = data.remark;
                    remarkInput_edit.readOnly = true;
                    remarkInput_edit.title    = 'Klik untuk edit manual';
                    remarkHint_edit.textContent = '\u2139 Auto: ' + data.count + ' data sebelumnya ditemukan. Klik untuk ubah.';
                    remarkHint_edit.classList.remove('d-none');

                    remarkInput_edit.onclick = function() {
                        remarkInput_edit.readOnly = false;
                        remarkInput_edit.title    = '';
                        remarkHint_edit.textContent = '\u270F Mode manual aktif.';
                    };
                } else if (data.remark) {
                    remarkInput_edit.value    = data.remark;
                    remarkInput_edit.readOnly = false;
                    remarkHint_edit.textContent = '\u2713 Pertama kali untuk item ini. Bisa diedit.';
                    remarkHint_edit.classList.remove('d-none');
                } else {
                    remarkInput_edit.readOnly = false;
                    remarkHint_edit.classList.add('d-none');
                }
            })
            .catch(function() {
                remarkInput_edit.readOnly = false;
            });
        }

        if (itemSelect_edit) {
            itemSelect_edit.addEventListener('change', function() {
                fetchNextRemark_edit();
            });
        }

        // --- Auto-fill No Lot QC ---
        var nextNoLotUrl_edit     = "{{ route('cross_cut.next_no_lot') }}";
        var prodDateInput_edit    = document.getElementById('production_datetime_edit');
        var prodShiftInput_edit   = document.getElementById('production_shift_edit');
        var qcShiftInput_edit     = document.getElementById('qc_shift_edit');
        var initialsInput_edit    = document.getElementById('operator_initials_edit');
        var noLotInput_edit       = document.getElementById('position_remark_no_lot_edit');
        var noLotHint_edit        = document.getElementById('noLotHint_edit');

        function fetchNextNoLot_edit() {
            var itemId = itemSelect_edit ? itemSelect_edit.value : '';
            var prodDateFull = prodDateInput_edit ? prodDateInput_edit.value : '';
            // Ambil hanya tanggalnya untuk parameter (YYYY-MM-DD)
            var prodDate = prodDateFull ? prodDateFull.split('T')[0] : '';
            var prodShift = prodShiftInput_edit ? prodShiftInput_edit.value : '1';
            var qcShift = qcShiftInput_edit ? qcShiftInput_edit.value : '1';
            var initials = initialsInput_edit ? initialsInput_edit.value : '';

            if (!itemId || !prodDate || !initials) {
                noLotInput_edit.readOnly = false;
                noLotHint_edit.classList.add('d-none');
                return;
            }

            var queryParams = '?item_id=' + encodeURIComponent(itemId) +
                              '&production_date=' + encodeURIComponent(prodDate) +
                              '&production_shift=' + encodeURIComponent(prodShift) +
                              '&qc_shift=' + encodeURIComponent(qcShift) +
                              '&operator_initials=' + encodeURIComponent(initials);

            fetch(nextNoLotUrl_edit + queryParams, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.no_lot) {
                    noLotInput_edit.value = data.no_lot;
                    noLotInput_edit.readOnly = false; 
                    noLotHint_edit.textContent = '\u2139 Auto Tergenerate. Ubah manual jika perlu.';
                    noLotHint_edit.classList.remove('d-none');
                } else {
                    noLotInput_edit.readOnly = false;
                    noLotHint_edit.classList.add('d-none');
                }
            })
            .catch(function(e) {
                noLotInput_edit.readOnly = false;
            });
        }

        if (itemSelect_edit) itemSelect_edit.addEventListener('change', fetchNextNoLot_edit);
        if (prodDateInput_edit) prodDateInput_edit.addEventListener('change', fetchNextNoLot_edit);
        if (prodShiftInput_edit) prodShiftInput_edit.addEventListener('change', fetchNextNoLot_edit);
        if (qcShiftInput_edit) qcShiftInput_edit.addEventListener('change', fetchNextNoLot_edit);
        if (initialsInput_edit) {
            initialsInput_edit.addEventListener('input', function() {
                clearTimeout(this.delay);
                this.delay = setTimeout(function() {
                    fetchNextNoLot_edit();
                    fetchNextRemark_edit();
                }, 500);
            });
        }

        // --- Auto-fill Keterangan Visual OK ---
        var visualOkCheck_edit = document.getElementById('visualOkCheck_edit');
        var keteranganInput_edit = document.getElementById('keteranganInput_edit');
        if (visualOkCheck_edit && keteranganInput_edit) {
            visualOkCheck_edit.addEventListener('change', function() {
                var currentVal = keteranganInput_edit.value;
                var appendStr = "Visual 100% OK";
                if (this.checked) {
                    if (currentVal.indexOf(appendStr) === -1) {
                        keteranganInput_edit.value = currentVal ? currentVal + '\n' + appendStr : appendStr;
                    }
                } else {
                    keteranganInput_edit.value = currentVal.replace(new RegExp('\n?' + appendStr, 'g'), '').trim();
                }
            });
        }

    })();
</script>
