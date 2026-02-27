<form action="{{ route('cross_cut.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="plant" value="{{ request('plant') }}">
    <div class="table-responsive">
        <table class="table table-bordered" width="100%" cellspacing="0">
            <thead>
                <tr class="text-center">
                    <th>Item Part</th>
                    <th>Customer</th>
                    <th>Part No</th>
                    <th>Tanggal & Shift Produksi / QC</th>
                    <th>Hasil Cross Cut</th>
                    <th>Bak No</th>
                    <th>Posisi Remark (Judgement / No Lot)</th>
                    <th>Result Remark</th>
                    <th>Inisial QC</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Item Part -->
                    <td class="align-middle" style="min-width: 200px;">
                        <select class="form-control" id="item_id_edit" name="item_id" required>
                            <option value="" disabled style="font-weight: bold; color: #6c757d;">Pilih Item Part
                            </option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-customer="{{ $item->customer ?? '' }}"
                                    data-part-number="{{ $item->part_number ?? '' }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <!-- Customer -->
                    <td class="align-middle">
                        <input type="text" class="form-control" id="customer_edit" name="customer"
                            value="{{ $checksheet->item->customer ?? '' }}" readonly style="background-color: #e9ecef;">
                    </td>
                    <!-- Part No -->
                    <td class="align-middle">
                        <input type="text" class="form-control" id="part_number_edit" name="part_number"
                            value="{{ $checksheet->item->part_number ?? '' }}" readonly
                            style="background-color: #e9ecef;">
                    </td>
                    <!-- Tanggal & Shift Produksi / QC -->
                    <td class="align-middle" style="min-width: 250px;">
                        <div class="form-group mb-2">
                            <label>Tgl. & Shift Produksi</label>
                            <div class="input-group">
                                <input type="datetime-local" class="form-control" name="production_datetime"
                                    value="{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('Y-m-d\TH:i') }}"
                                    required>
                                <select class="form-control" name="production_shift" required>
                                    <option value="1" {{ $checksheet->production_shift == 1 ? 'selected' : '' }}>Shift 1
                                    </option>
                                    <option value="2" {{ $checksheet->production_shift == 2 ? 'selected' : '' }}>Shift 2
                                    </option>
                                    <option value="3" {{ $checksheet->production_shift == 3 ? 'selected' : '' }}>Shift 3
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Tgl. & Shift QC</label>
                            <div class="input-group">
                                <input type="datetime-local" class="form-control" name="qc_datetime"
                                    value="{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('Y-m-d\TH:i') }}"
                                    required>
                                <select class="form-control" name="qc_shift" required>
                                    <option value="1" {{ $checksheet->qc_shift == 1 ? 'selected' : '' }}>Shift 1</option>
                                    <option value="2" {{ $checksheet->qc_shift == 2 ? 'selected' : '' }}>Shift 2</option>
                                    <option value="3" {{ $checksheet->qc_shift == 3 ? 'selected' : '' }}>Shift 3</option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <!-- Hasil Cross Cut (Image) -->
                    <td class="align-middle text-center">
                        <label for="image_edit" class="mb-2">Ganti Gambar (Opsional)</label>
                        <input type="file" class="form-control-file mb-2" id="image_edit" name="image" accept="image/*">
                        <button type="button" id="previewBtn_edit" class="btn btn-info btn-sm"
                            style="display: none;">Preview Foto Baru</button>
                        <hr>
                        <label>Gambar Saat Ini:</label><br>
                        <img src="{{ route('cross_cut.image', $checksheet->id) }}" alt="Current Image"
                            class="img-thumbnail" style="max-width: 150px;">
                    </td>
                    <!-- Bak No -->
                    <td class="align-middle" style="min-width: 200px;">
                        <div class="form-group mb-2"><label>Catalyst</label><input type="text" class="form-control"
                                name="chemical_catalyst" value="{{ $checksheet->chemical_catalyst }}"></div>
                        <div class="form-group mb-0"><label>Abu</label><input type="text" class="form-control"
                                name="chemical_abu" value="{{ $checksheet->chemical_abu }}"></div>
                    </td>
                    <!-- Posisi Remark -->
                    <td class="align-middle" style="min-width: 200px;">
                        <div class="form-group mb-2">
                            <label>Judgment</label>
                            <select class="form-control" name="position_remark_judgment"
                                id="position_remark_judgment_edit" required>
                                <option value="OK" {{ $checksheet->position_remark_judgment == 'OK' ? 'selected' : '' }}>
                                    OK</option>
                                <option value="NG" {{ $checksheet->position_remark_judgment == 'NG' ? 'selected' : '' }}>
                                    NG</option>
                            </select>
                        </div>
                        <div class="form-group mb-0"><label>No Lot</label><input type="text" class="form-control"
                                name="position_remark_no_lot" value="{{ $checksheet->position_remark_no_lot }}"
                                required></div>
                    </td>
                    <!-- Result Remark -->
                    <td class="align-middle"><input type="text" class="form-control" name="result_remark"
                            value="{{ $checksheet->result_remark }}"></td>
                    <!-- Inisial QC -->
                    <td class="align-middle"><input type="text" class="form-control" name="operator_initials"
                            placeholder="Inisial" value="{{ $checksheet->operator_initials }}"></td>
                    <!-- Keterangan -->
                    <td class="align-middle">
                        <div id="nextProsesContainer_edit"
                            style="display: {{ $checksheet->position_remark_judgment == 'NG' ? 'block' : 'none' }};">
                            <div class="form-group mb-2">
                                <label class="text-danger font-weight-bold">Next Proses</label>
                                <select name="next_proses" id="next_proses_edit" class="form-control">
                                    <option value="">-- Pilih Next Proses --</option>
                                    <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING</option>
                                    <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>
                                        SORTIR</option>
                                    <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>FINISHING</option>
                                    <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>
                                        REPAIR</option>
                                    <option value="MARKING+FINISHING+PACKING" {{ $checksheet->next_proses == 'MARKING+FINISHING+PACKING' ? 'selected' : '' }}>
                                        MARKING+FINISHING+PACKING</option>
                                    @if($checksheet->next_proses && !in_array($checksheet->next_proses, ['CRUSHING', 'SORTIR', 'FINISHING', 'REPAIR', 'MARKING+FINISHING+PACKING']))
                                        <option value="{{ $checksheet->next_proses }}" selected>
                                            {{ $checksheet->next_proses }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <textarea class="form-control" name="keterangan"
                            rows="3">{{ $checksheet->keterangan }}</textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-md-12 text-right d-flex justify-content-end align-items-center">
            <h5 class="mr-3 mb-0 font-weight-bold text-gray-800" id="timerDisplay_edit">
                {{ gmdate("H:i:s", $checksheet->cycle_time ?? 0) }}
            </h5>
            <input type="hidden" name="cycle_time" id="cycleTimeInput_edit" value="{{ $checksheet->cycle_time ?? 0 }}">

            <button type="button" class="btn btn-success mr-3" id="startTimerBtn_edit">
                <i class="fas fa-play"></i> Start/Reset
            </button>
            <button type="submit" class="btn btn-primary" id="saveBtn_edit">
                <i class="fas fa-save fa-sm"></i> Update Data
            </button>
        </div>
    </div>
</form>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal_edit" tabindex="-1" role="dialog"
    aria-labelledby="imagePreviewModalLabel_edit" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel_edit">Image Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage_edit" src="" class="img-fluid" alt="Image Preview">
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        // IDs are suffixed with _edit to avoid conflicts if multiple modals were present (though usually one)
        // Update Customer and Part No when item is selected
        $('#item_id_edit').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            var customer = selectedOption.data('customer') || '';
            var partNumber = selectedOption.data('part-number') || '';

            $('#customer_edit').val(customer);
            $('#part_number_edit').val(partNumber);
        });

        // Initialize customer and part_number if needed (values are already set by blade, but events might be needed)
        // Blade sets value via attributes, so display is correct.

        // Image Preview Logic
        $('#image_edit').on('change', function (event) {
            var file = event.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#previewImage_edit').attr('src', e.target.result);
                    $('#previewBtn_edit').show();
                }
                reader.readAsDataURL(file);
            }
        });

        $('#previewBtn_edit').on('click', function () {
            // We need to make sure this modal stacking works or use just a show/hide
            // Since we are in a modal already, opening another defaults to stacking in Bootstrap 4 if handled correctly
            $('#imagePreviewModal_edit').modal('show');
        });

        // Timer Logic
        var timerInterval = null;
        var totalSeconds = parseInt(document.getElementById('cycleTimeInput_edit').value) || 0;
        var timerRunning = false;
        var timerDisplay = document.getElementById('timerDisplay_edit');
        var cycleTimeInput = document.getElementById('cycleTimeInput_edit');
        var startTimerBtn = document.getElementById('startTimerBtn_edit');

        function updateTimerDisplay() {
            var hours = Math.floor(totalSeconds / 3600);
            var minutes = Math.floor((totalSeconds % 3600) / 60);
            var seconds = totalSeconds % 60;
            var text = [hours, minutes, seconds].map(v => v < 10 ? "0" + v : v).join(":");
            timerDisplay.textContent = text;
            cycleTimeInput.value = totalSeconds;
        }

        startTimerBtn.addEventListener('click', function () {
            if (timerRunning) { // If running, stop and reset
                clearInterval(timerInterval);
                timerRunning = false;
                totalSeconds = 0;
                updateTimerDisplay();
                this.innerHTML = '<i class="fas fa-play"></i> Start/Reset';
            } else { // If not running, start
                timerRunning = true;
                this.innerHTML = '<i class="fas fa-undo"></i> Reset';

                timerInterval = setInterval(function () {
                    totalSeconds++;
                    updateTimerDisplay();
                }, 1000);
            }
        });

        $('#saveBtn_edit').on('click', function () {
            if (timerRunning) {
                clearInterval(timerInterval);
            }
            cycleTimeInput.value = totalSeconds;
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
    })();
</script>