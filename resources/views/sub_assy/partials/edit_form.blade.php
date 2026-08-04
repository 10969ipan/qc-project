<form id="editChecksheetForm" class="ajax-form" action="{{ route('admin.checksheets.update', ['checksheet' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    @php
        $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true) ?? [];
    @endphp
    @foreach(request()->all() as $key => $value)
        @if(!in_array($key, ['_token', '_method', 'id']))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <input type="hidden" name="qrcode" value="{{ $checksheet->qrcode }}">
    <input type="hidden" name="part_code" value="{{ $checksheet->part_code }}">
    <input type="hidden" name="supplier_id" value="{{ $checksheet->supplier_id }}">
    <input type="hidden" name="quantity" value="{{ $checksheet->quantity }}">
    <input type="hidden" name="unique_code_id" value="{{ $checksheet->unique_code_id }}">
    <input type="hidden" name="sap_code" value="{{ $checksheet->sap_code }}">
    <input type="hidden" name="cycle_time" value="{{ $checksheet->cycle_time }}">

    <!-- 1. Header: Penelusuran (Traceability) -->
    <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">INFORMASI TRACEABILITY (QR CODE)</div>
    <div class="bg-white p-3 mb-4 shadow-sm border" style="border-radius: 8px;">
        <div class="row align-items-center">
            <div class="col-md-9">
                <div class="small font-weight-bold text-gray-700 mb-1">
                    <i class="fas fa-barcode mr-1"></i> Data QR Tag
                </div>
                <div class="small text-dark mb-1" title="{{ $checksheet->qrcode }}">
                    <span class="font-weight-bold text-gray-700">Raw QR:</span> {{ \Illuminate\Support\Str::limit($checksheet->qrcode, 80) }}
                </div>
                <div class="d-flex flex-wrap" style="gap: 15px;">
                    <span class="small"><span class="font-weight-bold text-gray-700">Part Code:</span> <span class="text-dark">{{ $checksheet->part_code }}</span></span>
                    <span class="small"><span class="font-weight-bold text-gray-700">Supplier:</span> <span class="text-dark">{{ $checksheet->supplier_id }}</span></span>
                    <span class="small"><span class="font-weight-bold text-gray-700">Qty QR:</span> <span class="text-dark">{{ $checksheet->quantity }}</span></span>
                    <span class="small"><span class="font-weight-bold text-gray-700">Unique ID:</span> <span class="text-danger font-weight-bold">{{ $checksheet->unique_code_id }}</span></span>
                    <span class="small"><span class="font-weight-bold text-gray-700">SAP Code:</span> <span class="text-dark">{{ $checksheet->sap_code }}</span></span>
                </div>
            </div>
            <div class="col-md-3 text-right">
                <span class="badge badge-info p-2 px-3 shadow-sm" style="font-size: 0.8rem;">
                    ID: {{ $checksheet->id }}
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 2. Kolom Kiri: Informasi Produksi -->
        <div class="col-md-6 mb-3">
            <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">DATA IDENTITAS & PRODUKSI</div>
            
            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Item Part <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select name="item_id" id="item_id_edit" class="form-control form-control-sm border-0 shadow-sm select2-standard" required>
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
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Tanggal <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="date" name="date" id="date_edit" class="form-control form-control-sm border-0 shadow-sm"
                        value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Shift <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select name="shift" id="shift_edit" class="form-control form-control-sm border-0 shadow-sm" required>
                        <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                        <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                        <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Jam Before / After</label>
                <div class="col-sm-4">
                    <input type="time" name="jam_before" id="jam_before_edit" class="form-control form-control-sm border-0 shadow-sm"
                        value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}">
                </div>
                <div class="col-sm-4">
                    <input type="time" name="jam_after" id="jam_after_edit" class="form-control form-control-sm border-0 shadow-sm"
                        value="{{ $checksheet->created_at->format('H:i') }}">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-12 text-right">
                    <small class="text-muted font-weight-bold">
                        Recalculated Cycle Time: <span id="recalculated_cycle_time_display" class="text-primary">{{ $checksheet->cycle_time ?? 0 }}</span> s
                    </small>
                </div>
            </div>

            @php
                $plant = strtolower(auth()->user()->plant->code ?? request('plant') ?? 'karawang');
                $tableOptions = range(1, 15);
                if ($plant === 'jakarta') {
                    $tableOptions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                }
            @endphp
            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Meja <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select name="line" id="line_edit" class="form-control form-control-sm border-0 shadow-sm" required>
                        <option value="">Pilih Meja</option>
                        @foreach ($tableOptions as $num)
                            <option value="{{ $num }}" {{ $checksheet->line == $num ? 'selected' : '' }}>Meja {{ $num }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Inisial Operator</label>
                <div class="col-sm-8">
                    <input type="text" name="operator_initials" id="operator_initials_edit" class="form-control form-control-sm border-0 shadow-sm text-uppercase bg-light font-weight-bold"
                        value="{{ $checksheet->operator_initials }}" placeholder="Inisial..." readonly>
                </div>
            </div>

            @if(auth()->user()->role !== 'inspector')
            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Inspector (System)</label>
                <div class="col-sm-8">
                    <select name="user_id" id="user_id_edit" class="form-control form-control-sm border-0 shadow-sm">
                        <option value="">-- Pertahankan User Saat Ini --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $checksheet->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->initials }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <div class="form-group row align-items-start mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Keterangan / Remarks</label>
                <div class="col-sm-8">
                    <textarea name="remarks" id="remarks_edit" class="form-control form-control-sm border-0 shadow-sm" rows="3" 
                        placeholder="Catatan tambahan...">{{ $checksheet->remarks }}</textarea>
                </div>
            </div>
        </div>

        <!-- 3. Kolom Kanan: Hasil Pemeriksaan -->
        <div class="col-md-6 mb-3">
            <div class="font-weight-bold text-primary mb-3 pb-2 d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                <span class="font-weight-bold">SAMPLING & HASIL (AQL 0.65)</span>
                <div id="editJudgmentBadge" class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }} px-3 py-1 shadow-sm" style="font-size: 0.75rem;">
                    {{ $checksheet->judgment }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <label class="small font-weight-bold text-gray-700">Total Qty <span class="text-danger">*</span></label>
                    <input type="number" name="total_qty" id="total_qty_edit" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-light"
                        value="{{ $checksheet->total_qty }}" required>
                </div>
                <div class="col-6">
                    <label class="small font-weight-bold text-primary">Sampling Qty</label>
                    <input type="number" name="sampling_qty" id="sampling_qty_edit" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-white"
                        style="color: #4e73df !important; border: 1px solid #4e73df !important;" value="{{ $checksheet->sampling_qty }}" readonly>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <label class="small font-weight-bold text-success">Total OK</label>
                    <input type="number" name="total_ok" id="total_ok_edit" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-white"
                        style="color: #1cc88a !important; border: 1px solid #1cc88a !important;" value="{{ $checksheet->total_ok }}" readonly>
                </div>
                <div class="col-6">
                    <label class="small font-weight-bold text-danger">Total NG <span class="text-danger">*</span></label>
                    <input type="number" name="total_ng" id="total_ng_edit" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-white"
                        style="color: #e74a3b !important; border: 1px solid #e74a3b !important;" value="{{ $checksheet->total_ng }}" required readonly>
                </div>
            </div>

            <div class="font-weight-bold text-primary mb-3 pb-2 d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                <span class="font-weight-bold">DAFTAR DEFECT (NG)</span>
                <button type="button" id="editAddDefectBtn" class="btn btn-primary btn-sm shadow-sm" style="padding: 0.1rem 0.5rem; font-size: 0.7rem;">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
            
            <div id="editDefectContainer" class="mb-3">
                @if(count($defects) > 0)
                    @foreach($defects as $index => $defect)
                        <div class="row no-gutters mb-2 defect-row align-items-center p-1 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="col-8 pr-1 pl-1">
                                <select class="form-control form-control-sm defect-select font-weight-bold border-0 shadow-sm" name="defect_types[]">
                                    <option value="">-- Pilih Defect --</option>
                                    @php
                                        $itemDefects = $checksheet->item->defects ?? [];
                                    @endphp
                                    @foreach($itemDefects as $idft)
                                        <option value="{{ $idft }}" {{ ($defect['type'] ?? '') == $idft ? 'selected' : '' }}>{{ $idft }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3 pr-1">
                                <input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold border-0 shadow-sm" 
                                    name="defect_quantities[]" min="1" value="{{ $defect['qty'] ?? 1 }}">
                            </div>
                            <div class="col-1 text-center">
                                <button type="button" class="btn btn-link text-danger p-0 remove-defect-btn"><i class="fas fa-times-circle"></i></button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div id="noDefectMsg" class="text-center py-3 small text-muted font-italic bg-white rounded shadow-sm border border-light">
                        Belum ada defect yang ditambahkan
                    </div>
                @endif
            </div>

            <!-- Judgment & Next Process -->
            <div id="editNextProsesGroup" style="{{ $checksheet->judgment == 'NG' ? '' : 'display: none;' }}">
                <div class="form-group mb-0 p-3 rounded" style="background: #fff5f5; border: 1px dashed #e74a3b;">
                    <label class="small font-weight-bold text-danger">Next Proses <span class="text-danger">*</span></label>
                    <select name="next_proses" id="next_proses_edit" class="form-control form-control-sm border-0 shadow-sm font-weight-bold text-danger">
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

            <input type="hidden" name="judgment" id="judgment_edit" value="{{ $checksheet->judgment }}">
        </div>
    </div>

    <div class="bg-white border-top py-3 px-4 d-flex justify-content-end align-items-center" style="margin: 1.5rem -1.5rem -1.5rem -1.5rem; border-radius: 0 0 12px 12px;">
        <button type="button" class="btn btn-light border px-4 font-weight-bold mr-2" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
    </div>
</form>

<script>
    (function () {
        // Self-executing function to isolate scope
        if (typeof jQuery === 'undefined') return;
        
        (function($) {
            // Helper functions identik dengan In-Process (Standard AQL 0.65)
            function getSampleSize(totalQty) {
                if (totalQty <= 8) return totalQty;
                if (totalQty <= 15) return 20;
                if (totalQty <= 25) return 20;
                if (totalQty <= 50) return 20;
                if (totalQty <= 90) return 20;
                if (totalQty <= 150) return 20;
                if (totalQty <= 280) return 32;
                if (totalQty <= 500) return 50;
                if (totalQty <= 1200) return 80;
                if (totalQty <= 3200) return 125;
                if (totalQty <= 10000) return 200;
                if (totalQty <= 35000) return 315;
                if (totalQty <= 150000) return 500;
                if (totalQty <= 500000) return 800;
                return 1250;
            }

            function getAqlLimits(sampleSize) {
                const limits = {
                    2:   { acc: 0, rej: 1 },
                    3:   { acc: 0, rej: 1 },
                    5:   { acc: 0, rej: 1 },
                    8:   { acc: 0, rej: 1 },
                    13:  { acc: 0, rej: 1 },
                    20:  { acc: 0, rej: 1 },
                    32:  { acc: 0, rej: 1 },
                    50:  { acc: 0, rej: 1 },
                    80:  { acc: 1, rej: 2 },
                    125: { acc: 2, rej: 3 },
                    200: { acc: 3, rej: 4 },
                    315: { acc: 5, rej: 6 },
                    500: { acc: 7, rej: 8 },
                    800: { acc: 10, rej: 11 },
                    1250:{ acc: 14, rej: 15 }
                };
                return limits[sampleSize] || { acc: 0, rej: 1 };
            }

            function updateJudgment() {
                const totalQty = parseInt($('#total_qty_edit').val()) || 0;
                const sampleSize = getSampleSize(totalQty);
                const limits = getAqlLimits(sampleSize);
                const totalNg = parseInt($('#total_ng_edit').val()) || 0;
                
                $('#sampling_qty_edit').val(sampleSize);
                $('#total_ok_edit').val(Math.max(0, sampleSize - totalNg));

                const judgment = totalNg <= limits.acc ? 'OK' : 'NG';
                $('#judgment_edit').val(judgment);
                
                const $badge = $('#editJudgmentBadge');
                $badge.text(judgment).removeClass('badge-success badge-danger text-success text-danger');
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
                const row = $('<div class="row no-gutters mb-2 defect-row align-items-center p-1 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">' +
                    '<div class="col-8 pr-1 pl-1">' +
                    '<select class="form-control form-control-sm defect-select font-weight-bold border-0 shadow-sm" name="defect_types[]">' +
                    '<option value="">-- Pilih Defect --</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="col-3 pr-1">' +
                    '<input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold border-0 shadow-sm" name="defect_quantities[]" placeholder="Qty" min="1" value="1">' +
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

            // Cycle Time Calculation Logic
            function calculateRecalculatedCycleTime() {
                const jamBefore = $('#jam_before_edit').val();
                const jamAfter = $('#jam_after_edit').val();
                
                if (jamBefore && jamAfter) {
                    const today = new Date().toISOString().split('T')[0];
                    let before = new Date(`${today}T${jamBefore}`);
                    let after = new Date(`${today}T${jamAfter}`);
                    
                    if (after < before) {
                        after.setDate(after.getDate() + 1);
                    }
                    
                    const diffSeconds = Math.floor((after - before) / 1000);
                    
                    $('input[name="cycle_time"]').val(diffSeconds);
                    $('#recalculated_cycle_time_display').text(diffSeconds);
                }
            }

            $('#jam_before_edit, #jam_after_edit').on('change input', calculateRecalculatedCycleTime);

            // Initial UI state
            updateJudgment();

        })(jQuery);
    })();
</script>
