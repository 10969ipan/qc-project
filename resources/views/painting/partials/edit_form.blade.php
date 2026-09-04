<form id="editChecksheetForm" class="ajax-form" novalidate
    action="{{ route('painting.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    @php
        $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true) ?? [];
    @endphp
    {{-- Preserve all filter and pagination parameters --}}
    @foreach(request()->all() as $key => $value)
        @if(!in_array($key, ['_token', '_method', 'id', 'plant']))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <input type="hidden" name="plant" value="{{ request('plant') }}">

    <!-- 1. Header: Penelusuran (Traceability) -->
    <div class="alert alert-secondary py-2 mb-3 border-left-info shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-9">
                <div class="small text-muted font-weight-bold text-uppercase mb-1">
                    <i class="fas fa-barcode mr-1"></i> Informasi Traceability (QR Code)
                </div>
                <div class="small text-dark font-italic text-truncate mb-1" title="{{ $checksheet->qrcode }}">
                    <strong>Raw QR:</strong> {{ \Illuminate\Support\Str::limit($checksheet->qrcode, 80) }}
                </div>
                <div class="d-flex flex-wrap" style="gap: 15px;">
                    <span class="small"><strong>Part Code:</strong> {{ $checksheet->part_code }}</span>
                    <span class="small"><strong>Supplier:</strong> {{ $checksheet->supplier_id }}</span>
                    <span class="small"><strong>Qty QR:</strong> {{ $checksheet->quantity }}</span>
                    <span class="small text-danger font-weight-bold"><strong>Unique ID:</strong> {{ $checksheet->unique_code_id }}</span>
                    <span class="small"><strong>SAP Code:</strong> {{ $checksheet->sap_code }}</span>
                </div>
            </div>
            <div class="col-md-3 text-right">
                <span class="badge badge-info p-2 px-3 shadow-sm">
                    ID: {{ $checksheet->id }}
                </span>
            </div>
        </div>
    </div>

    {{-- Hidden inputs to preserve QR & Cycle Time data during update --}}
    <input type="hidden" name="qrcode" value="{{ $checksheet->qrcode }}">
    <input type="hidden" name="part_code" value="{{ $checksheet->part_code }}">
    <input type="hidden" name="supplier_id" value="{{ $checksheet->supplier_id }}">
    <input type="hidden" name="quantity" value="{{ $checksheet->quantity }}">
    <input type="hidden" name="unique_code_id" value="{{ $checksheet->unique_code_id }}">
    <input type="hidden" name="sap_code" value="{{ $checksheet->sap_code }}">
    <input type="hidden" name="is_scanned" value="{{ !empty($checksheet->qrcode) ? 1 : 0 }}">
    <input type="hidden" name="cycle_time" value="{{ $checksheet->cycle_time }}">

    <div class="row">
        <!-- 2. Kolom Kiri: Informasi Produksi -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm h-100 border-0 border-top-primary" style="border-top-width: 3px !important;">
                <div class="card-header bg-white py-2">
                    <h6 class="m-0 font-weight-bold text-primary small">
                        <i class="fas fa-info-circle mr-1"></i> Data Identitas & Produksi
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-gray-700">Item Part <span class="text-danger">*</span></label>
                        <select name="item_id" id="item_id_edit" class="form-control form-control-sm select2-standard" data-field-name="Item Part" required>
                            <option value="" disabled>Pilih Item Part</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                                    data-part-number="{{ $item->part_number }}"
                                    data-customer="{{ $item->customer }}"
                                    data-defects="{{ json_encode($item->defects) }}">
                                    {{ $item->name }} ({{ $item->customer }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Painting Specific Fields (Lot ID & Painting) -->
                    <div class="row bg-light p-2 rounded mb-3 border">
                        <div class="col-md-6 border-right">
                            <label class="x-small font-weight-bold text-primary text-uppercase mb-1">Lot ID <span class="text-danger">*</span></label>
                            <div class="form-group mb-2">
                                <input type="date" name="injection_date" class="form-control form-control-sm" data-field-name="Tanggal Lot ID"
                                    value="{{ $checksheet->injection_date ? $checksheet->injection_date->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <select name="injection_shift" class="form-control form-control-sm" data-field-name="Shift Lot ID" required>
                                    <option value="">Shift</option>
                                    <option value="1" {{ $checksheet->injection_shift == '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ $checksheet->injection_shift == '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ $checksheet->injection_shift == '3' ? 'selected' : '' }}>3</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <input type="text" name="injection_initials" class="form-control form-control-sm text-center" data-field-name="Inisial Lot ID"
                                    value="{{ $checksheet->injection_initials ?? '' }}" placeholder="Inisial Lot ID"
                                    style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="x-small font-weight-bold text-info text-uppercase mb-1">Painting <span class="text-danger">*</span></label>
                            <div class="form-group mb-2">
                                <input type="date" name="painting_date" class="form-control form-control-sm" data-field-name="Tanggal Painting"
                                    value="{{ $checksheet->painting_date ? $checksheet->painting_date->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="row no-gutters">
                                <div class="col-4 pr-1">
                                    <select name="painting_shift" class="form-control form-control-sm" data-field-name="Shift Painting" required>
                                        <option value="">Shf</option>
                                        <option value="1" {{ $checksheet->painting_shift == '1' ? 'selected' : '' }}>1</option>
                                        <option value="2" {{ $checksheet->painting_shift == '2' ? 'selected' : '' }}>2</option>
                                        <option value="3" {{ $checksheet->painting_shift == '3' ? 'selected' : '' }}>3</option>
                                    </select>
                                </div>
                                <div class="col-8">
                                    <input type="text" name="no_lot" class="form-control form-control-sm font-weight-bold" 
                                        placeholder="No Lot" value="{{ $checksheet->no_lot }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-gray-700">Tanggal Quality <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date_edit" class="form-control form-control-sm" data-field-name="Tanggal Quality"
                                    value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-gray-700">Shift Quality <span class="text-danger">*</span></label>
                                <select name="shift" id="shift_edit" class="form-control form-control-sm" data-field-name="Shift Quality" required>
                                    <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                                    <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                                    <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-gray-700">Meja <span class="text-danger">*</span></label>
                                <select name="line" id="line_edit" class="form-control form-control-sm" data-field-name="Meja" required>
                                    <option value="">Pilih Meja</option>
                                    @foreach (range(1, 15) as $num)
                                        <option value="{{ $num }}" {{ $checksheet->line == $num ? 'selected' : '' }}>Meja {{ $num }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-gray-700">Inisial Operator QC <span class="text-danger">*</span></label>
                                <input type="text" name="operator_initials" id="operator_initials_edit" class="form-control form-control-sm text-uppercase" data-field-name="Inisial Operator QC"
                                    value="{{ $checksheet->operator_initials }}" placeholder="Inisial..." required>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->role !== 'inspector')
                    <div class="form-group mb-3 text-primary">
                        <label class="small font-weight-bold">Inspector (System User)</label>
                        <select name="user_id" id="user_id_edit" class="form-control form-control-sm border-primary">
                            <option value="">-- Pertahankan User Saat Ini --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $checksheet->user_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->initials }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-gray-700">Keterangan / Remarks</label>
                        <textarea name="remarks" id="remarks_edit" class="form-control form-control-sm" rows="2" 
                            placeholder="Catatan tambahan...">{{ $checksheet->remarks }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Kolom Kanan: Hasil Pemeriksaan (Sampling & Auto-Judgment) -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm h-100 border-0 border-top-success" style="border-top-width: 3px !important;">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success small">
                        <i class="fas fa-check-double mr-1"></i> Hasil Pemeriksaan (AQL 0.65)
                    </h6>
                    <div id="editJudgmentBadge" class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }} px-3 py-1">
                        {{ $checksheet->judgment }}
                    </div>
                </div>
                <div class="card-body py-3">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small font-weight-bold">Total Qty (Pcs) <span class="text-danger">*</span></label>
                            <input type="number" name="total_qty" id="total_qty_edit" class="form-control form-control-sm font-weight-bold bg-light" data-field-name="Total Qty (Pcs)"
                                value="{{ $checksheet->total_qty }}" required>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-primary">Check Qty (100%) <i class="fas fa-info-circle ml-1"></i></label>
                            <input type="number" name="sampling_qty" id="sampling_qty_edit" class="form-control form-control-sm font-weight-bold text-primary border-primary"
                                value="{{ $checksheet->sampling_qty }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6 border-right">
                            <label class="small font-weight-bold text-success">Total OK</label>
                            <input type="number" name="total_ok" id="total_ok_edit" class="form-control form-control-sm font-weight-bold text-success border-success"
                                value="{{ $checksheet->total_ok }}" readonly>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-danger">Total NG <span class="text-danger">*</span></label>
                            <input type="number" name="total_ng" id="total_ng_edit" class="form-control form-control-sm font-weight-bold text-danger border-danger" data-field-name="Total NG"
                                value="{{ $checksheet->total_ng }}" required readonly>
                        </div>
                    </div>

                    <!-- Defect List -->
                    <div class="bg-gray-100 p-3 rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="small font-weight-bold text-dark mb-0">Daftar Defect (NG)</label>
                            <button type="button" id="editAddDefectBtn" class="btn btn-primary btn-xs">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                        <div id="editDefectContainer">
                            @if(count($defects) > 0)
                                @foreach($defects as $index => $defect)
                                    <div class="row no-gutters mb-2 defect-row align-items-center shadow-sm bg-white p-1 rounded">
                                        <div class="col-7 pr-1">
                                            <select class="form-control form-control-sm defect-select font-weight-bold" name="defect_types[]">
                                                <option value="">-- Pilih Defect --</option>
                                                @php
                                                    $itemDefects = $checksheet->item->defects ?? [];
                                                @endphp
                                                @foreach($itemDefects as $idft)
                                                    <option value="{{ $idft }}" {{ ($defect['type'] ?? '') == $idft ? 'selected' : '' }}>{{ $idft }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4 pr-1">
                                            <input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold" 
                                                name="defect_quantities[]" min="1" value="{{ $defect['qty'] ?? 1 }}">
                                        </div>
                                        <div class="col-1 text-center">
                                            <button type="button" class="btn btn-link text-danger p-0 remove-defect-btn"><i class="fas fa-times-circle"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div id="noDefectMsg" class="text-center py-2 small text-muted font-italic">
                                    Belum ada defect yang ditambahkan
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Judgment & Next Process -->
                    <div id="editNextProsesGroup" style="{{ $checksheet->judgment == 'NG' ? '' : 'display: none;' }}">
                        <div class="form-group mb-0 p-2 border border-danger rounded bg-white">
                            <label class="small font-weight-bold text-danger">Next Proses <span class="text-danger">*</span></label>
                            <select name="next_proses" id="next_proses_edit" class="form-control form-control-sm border-danger" data-field-name="Next Proses">
                                <option value="">-- Pilih Next Proses --</option>
                                @foreach($nextProcesses as $opt)
                                    <option value="{{ $opt->name }}" {{ $checksheet->next_proses == $opt->name ? 'selected' : '' }}>{{ $opt->name }}</option>
                                @endforeach
                                @if($checksheet->next_proses && !$nextProcesses->pluck('name')->contains($checksheet->next_proses))
                                    <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="judgment" id="judgment_edit" value="{{ $checksheet->judgment }}">
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-secondary btn-sm px-4 shadow-sm" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold">
            <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
    </div>
</form>

<script>
    (function () {
        if (typeof jQuery === 'undefined') return;
        
        (function($) {
            // Painting uses 100% check (Sampling = Total Qty)
            function updateJudgment() {
                const totalQty = parseInt($('#total_qty_edit').val()) || 0;
                const totalNg = parseInt($('#total_ng_edit').val()) || 0;
                
                $('#sampling_qty_edit').val(totalQty);
                $('#total_ok_edit').val(Math.max(0, totalQty - totalNg));

                // Standard and Autojudgment logic
                const judgment = totalNg === 0 ? 'OK' : 'NG';
                $('#judgment_edit').val(judgment);
                
                const $badge = $('#editJudgmentBadge');
                $badge.text(judgment).removeClass('badge-success badge-danger');
                if (judgment === 'OK') {
                    $badge.addClass('badge-success');
                    $('#editNextProsesGroup').fadeOut();
                    $('#next_proses_edit').prop('required', false).val('');
                } else {
                    $badge.addClass('badge-danger');
                    $('#editNextProsesGroup').fadeIn();
                    $('#next_proses_edit').prop('required', true);
                }
            }

            function calculateTotalNG() {
                let total = 0;
                $('.defect-qty').each(function () {
                    total += parseInt($(this).val()) || 0;
                });
                $('#total_ng_edit').val(total);
                updateJudgment();
                
                if (total > 0 || $('.defect-row').length > 0) {
                    $('#noDefectMsg').hide();
                } else {
                    $('#noDefectMsg').show();
                }
            }

            function updateDefectOptions() {
                const selectedOption = $('#item_id_edit').find('option:selected');
                let defects = selectedOption.data('defects') || [];
                if (typeof defects === 'string') defects = JSON.parse(defects);

                $('.defect-select').each(function() {
                    const currentVal = $(this).val();
                    let html = '<option value="">-- Pilih Defect --</option>';
                    defects.forEach(d => {
                        html += `<option value="${d}" ${d === currentVal ? 'selected' : ''}>${d}</option>`;
                    });
                    $(this).html(html);
                });
            }

            // Events
            $('#total_qty_edit').on('input', updateJudgment);
            $(document).on('input', '.defect-qty', calculateTotalNG);
            
            $('#editAddDefectBtn').click(function() {
                const row = $('<div class="row no-gutters mb-2 defect-row align-items-center shadow-sm bg-white p-1 rounded">' +
                    '<div class="col-7 pr-1">' +
                    '<select class="form-control form-control-sm defect-select font-weight-bold" name="defect_types[]">' +
                    '<option value="">-- Pilih Defect --</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="col-4 pr-1">' +
                    '<input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1" value="1">' +
                    '</div>' +
                    '<div class="col-1 text-center">' +
                    '<button type="button" class="btn btn-link text-danger p-0 remove-defect-btn"><i class="fas fa-times-circle"></i></button>' +
                    '</div>' +
                    '</div>');
                $('#editDefectContainer').append(row);
                $('#noDefectMsg').hide();
                updateDefectOptions();
                calculateTotalNG();
            });

            $(document).on('click', '.remove-defect-btn', function() {
                $(this).closest('.defect-row').remove();
                calculateTotalNG();
            });

            $('#item_id_edit').change(function() {
                updateDefectOptions();
            });

            // Initial UI state
            updateJudgment();

        })(jQuery);
    })();
</script>
