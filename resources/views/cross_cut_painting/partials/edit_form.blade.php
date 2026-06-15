<form action="{{ route('cross_cut_painting.update', array_merge(['id' => $checksheet->id], request()->query())) }}" method="POST"
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
                <div class="col-4 form-group mb-2 pr-1">
                    <label class="small font-weight-bold">Cross Cut</label>
                    <select class="form-control form-control-sm" name="defects[cross_cut]" id="defectCrossCut_edit" required>
                        <option value="OK" {{ ($checksheet->defects['cross_cut'] ?? 'OK') == 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="NG" {{ ($checksheet->defects['cross_cut'] ?? 'OK') == 'NG' ? 'selected' : '' }}>NG</option>
                    </select>
                </div>
                <div class="col-4 form-group mb-2 px-1">
                    <label class="small font-weight-bold">Pencil Scratch</label>
                    <select class="form-control form-control-sm" name="pencil_scratch" id="defectPencilScratch_edit" required>
                        <option value="OK" {{ ($checksheet->pencil_scratch ?? 'OK') == 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="NG" {{ ($checksheet->pencil_scratch ?? 'OK') == 'NG' ? 'selected' : '' }}>NG</option>
                    </select>
                </div>
                <div class="col-4 form-group mb-2 pl-1">
                    <label class="small font-weight-bold">Tap Test</label>
                    <select class="form-control form-control-sm" name="tap_test" id="defectTapTest_edit" required>
                        <option value="OK" {{ ($checksheet->tap_test ?? 'OK') == 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="NG" {{ ($checksheet->tap_test ?? 'OK') == 'NG' ? 'selected' : '' }}>NG</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-12 form-group mb-2">
                    <label class="small font-weight-bold">Judgment</label>
                    <select class="form-control form-control-sm" name="position_remark_judgment" id="position_remark_judgment_edit" required>
                        <option value="OK" {{ $checksheet->position_remark_judgment == 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="NG" {{ $checksheet->position_remark_judgment == 'NG' ? 'selected' : '' }}>NG</option>
                    </select>
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
                    </select>
                </div>
            </div>
            
            <div class="form-group mb-0">
                <label class="small font-weight-bold">Keterangan</label>
                <textarea class="form-control form-control-sm" name="keterangan" id="keteranganInput_edit" rows="4">{{ $checksheet->keterangan }}</textarea>
            </div>
        </div>

        <!-- Kolom 3: Dokumentasi -->
        <div class="col-md-4 text-left">
            <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fas fa-camera mr-1"></i> Dokumentasi</h6>
            
            <div class="form-group mb-3 text-center">
                <label class="small font-weight-bold d-block text-left">Foto Checksheet</label>
                <div class="mb-2 p-1 border rounded bg-light" style="display: flex; justify-content: center; align-items: center; min-height: 120px;">
                    <img id="previewImage_edit" src="{{ route('cross_cut_painting.image', $checksheet->id) }}" alt="Current Image" style="max-height: 110px; max-width: 100%; object-fit: contain;">
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
                <img id="previewImageLarge_edit" src="{{ route('cross_cut_painting.image', $checksheet->id) }}" class="img-fluid" alt="Image Preview">
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

        // Auto judgment logic for edit form
        const crossCutEdit = $('#defectCrossCut_edit');
        const pencilScratchEdit = $('#defectPencilScratch_edit');
        const tapTestEdit = $('#defectTapTest_edit');

        function updateEditJudgment() {
            if (crossCutEdit.val() === 'NG' || pencilScratchEdit.val() === 'NG' || tapTestEdit.val() === 'NG') {
                judgmentSelect.val('NG').trigger('change');
            } else {
                judgmentSelect.val('OK').trigger('change');
            }
        }

        crossCutEdit.on('change', updateEditJudgment);
        pencilScratchEdit.on('change', updateEditJudgment);
        tapTestEdit.on('change', updateEditJudgment);
    })();
</script>
