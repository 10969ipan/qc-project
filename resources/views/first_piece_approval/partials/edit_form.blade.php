<form id="editChecksheetForm" class="ajax-form"
    action="{{ route('first_piece_approval.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    @php
        $defectsArr = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true) ?? [];
    @endphp
    {{-- Preserve filter parameters (scalar only, excluding form fields) --}}
    @php
        $formFields = ['item_id', 'date', 'shift', 'code_machine', 'category', 'total_qty', 'sampling_qty', 'total_ok', 'total_ng', 'judgment', 'operator_initials', 'part_weight', 'remarks', 'next_proses', 'dimensions', 'defect_types', 'defect_quantities', '_token', '_method', 'id'];
    @endphp
    @foreach(request()->all() as $key => $value)
        @if(!in_array($key, $formFields) && is_scalar($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach


    {{-- Hidden inputs to preserve QR data --}}
    <input type="hidden" name="qrcode" value="{{ $checksheet->qrcode }}">
    <input type="hidden" name="part_code" value="{{ $checksheet->part_code }}">
    <input type="hidden" name="supplier_id" value="{{ $checksheet->supplier_id }}">
    <input type="hidden" name="unique_code_id" value="{{ $checksheet->unique_code_id }}">
    <input type="hidden" name="sap_code" value="{{ $checksheet->sap_code }}">

    <div class="row">
        <!-- 2. Kolom Kiri: Informasi Produksi -->
        <div class="col-md-6 mb-3">
            <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">DATA IDENTITAS & PRODUKSI</div>
            
            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Item Part <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select name="item_id" id="item_id" class="form-control form-control-sm border-0 shadow-sm select2-standard" required>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                                data-part-number="{{ $item->part_number }}"
                                data-customer="{{ $item->customer }}"
                                data-weight-standard="{{ $item->weight_standard }}"
                                data-dimension-standards="{{ json_encode($item->dimension_standards) }}"
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
                    <input type="date" name="date" id="date" class="form-control form-control-sm border-0 shadow-sm"
                        value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Shift <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select name="shift" id="shift" class="form-control form-control-sm border-0 shadow-sm" required>
                        <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                        <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                        <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">No Mesin <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select name="code_machine" id="code_machine" class="form-control form-control-sm border-0 shadow-sm" required>
                        <option value="">-- Pilih --</option>
                        @php
                            $plantCode = strtolower($checksheet->plant->code ?? 'karawang');
                            $jakartaNumbers = [1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
                            $karawangNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19];
                            $numbers = ($plantCode === 'jakarta') ? $jakartaNumbers : $karawangNumbers;
                        @endphp
                        @foreach ($numbers as $num)
                            <option value="{{ $num }}" {{ $checksheet->code_machine == $num ? 'selected' : '' }}>Machine {{ $num }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Kategori</label>
                <div class="col-sm-8">
                    <select name="category" id="edit_category" class="form-control form-control-sm border-0 shadow-sm">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach (($fpaCategories ?? \App\Models\GeneralSetting::getFpaCategories()) as $cat)
                            <option value="{{ $cat }}" {{ $checksheet->category == $cat ? 'selected' : '' }}>{{ strtoupper($cat) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Inisial Operator</label>
                <div class="col-sm-8">
                    <input type="text" name="operator_initials" id="operator_initials" class="form-control form-control-sm border-0 shadow-sm text-uppercase font-weight-bold"
                        value="{{ $checksheet->operator_initials }}" placeholder="Inisial...">
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Inspector</label>
                <div class="col-sm-8">
                    <select name="user_id" id="user_id" class="form-control form-control-sm border-0 shadow-sm">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $checksheet->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->initials }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-start mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Keterangan / Remarks</label>
                <div class="col-sm-8">
                    <textarea name="remarks" id="remarks" class="form-control form-control-sm border-0 shadow-sm" rows="3" placeholder="Catatan tambahan...">{{ $checksheet->remarks }}</textarea>
                </div>
            </div>

            <!-- Section Berat Part -->
                    <div id="editBeratPartRow" class="mt-3 bg-white p-3 rounded shadow-sm border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="small font-weight-bold text-gray-700 mb-0">
                                <i class="fas fa-weight mr-1"></i> Berat Part (gr.)
                                <span class="badge badge-secondary ml-2 font-weight-normal" id="weightStdBadge">
                                    Std: {{ $checksheet->item->weight_standard ?? '-' }} gr
                                </span>
                            </label>
                            
                            <div class="d-flex align-items-center" style="gap:5px;">
                                <div class="btn-group shadow-sm">
                                    <button type="button" id="editAddWeightCavBtn" class="btn btn-primary btn-xs px-2" title="Tambah Cavity" style="font-size: 0.7rem;"><i class="fas fa-plus"></i></button>
                                    <button type="button" id="editRemoveWeightCavBtn" class="btn btn-danger btn-xs px-2" title="Kurangi Cavity" style="font-size: 0.7rem;"><i class="fas fa-minus"></i></button>
                                </div>
                                @php
                                    $wts = is_array($checksheet->part_weight) ? $checksheet->part_weight : json_decode($checksheet->part_weight, true) ?? [null];
                                @endphp
                                <span id="editWeightCavCount" class="badge badge-primary px-2 py-1" style="font-size: 0.7rem;">{{ count($wts) }} Cav</span>
                            </div>
                        </div>

                        <div id="editWeightCavContainer">
                            @foreach($wts as $idx => $wVal)
                                <div class="input-group input-group-sm mb-2 edit-weight-cav-row">
                                    <div class="input-group-prepend shadow-sm">
                                        <span class="input-group-text bg-light border-0" style="min-width:60px; justify-content:center; font-weight:600;">CAV {{ $idx + 1 }}</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" class="form-control text-center font-weight-bold border-0 shadow-sm"
                                        name="part_weight[]" placeholder="0.00" value="{{ $wVal }}">
                                    <div class="input-group-append shadow-sm">
                                        <span class="input-group-text bg-light border-0 text-muted">gr</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
        </div>
        <!-- 3. Kolom Kanan: Hasil Kualitas -->
        <div class="col-md-6 mb-3">
            <div class="font-weight-bold text-primary mb-3 pb-2 d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                <span class="font-weight-bold">HASIL PEMERIKSAAN & KUALITAS</span>
            </div>

            @if(auth()->user()->role !== 'inspector')
            <div class="row mb-3">
                <div class="col-6">
                    <label class="small font-weight-bold text-gray-700">Jam (Before)</label>
                    <input type="time" name="jam_before" id="jam_before" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-white"
                        value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}">
                </div>
                <div class="col-6">
                    <label class="small font-weight-bold text-gray-700">Jam (After)</label>
                    <input type="time" name="jam_after" id="jam_after" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-white"
                        value="{{ $checksheet->created_at->format('H:i') }}">
                </div>
            </div>
            @endif

            <div class="row mb-3 pb-3 border-bottom">
                <div class="col-6">
                    <label class="small font-weight-bold text-gray-700">Total Produksi (Qty) <span class="text-danger">*</span></label>
                    <input type="number" name="total_qty" id="total_qty" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-light"
                        value="{{ $checksheet->total_qty }}" min="0" required>
                </div>
                <div class="col-6">
                    <label class="small font-weight-bold text-gray-700">Sampling Qty</label>
                    <input type="number" name="sampling_qty" id="sampling_qty" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-white"
                        value="{{ $checksheet->sampling_qty }}" min="0" required readonly>
                </div>
            </div>

            <div class="form-group mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="small font-weight-bold text-gray-700 mb-0">Detail NG (Defect List)</label>
                    <button type="button" id="editAddDefectBtn" class="btn btn-info btn-xs px-3" style="font-size: 0.7rem;">
                        <i class="fas fa-plus mr-1"></i> Tambah Jenis NG
                    </button>
                </div>
                
                <div id="editDefectContainer" class="bg-light p-2 rounded border-dashed" style="border: 1px dashed #ced4da;">
                    @forelse($defectsArr as $idx => $def)
                        @php
                            $defType = isset($def['type']) && strtolower($def['type']) === 'dimension' ? 'Dimensi' : ($def['type'] ?? '');
                        @endphp
                        <div class="row no-gutters mb-2 defect-row align-items-center shadow-sm bg-white p-1 rounded">
                            <div class="col-8 pr-1">
                                <select class="form-control form-control-sm defect-select font-weight-bold" name="defect_types[]">
                                    <option value="">-- Pilih Defect --</option>
                                    <option value="{{ $defType }}" selected>{{ $defType }}</option>
                                </select>
                            </div>
                            <div class="col-3 pr-1">
                                <input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold" 
                                    name="defect_quantities[]" value="{{ $def['qty'] }}" min="1">
                            </div>
                            <div class="col-1 text-center">
                                <button type="button" class="btn btn-danger btn-xs px-2 remove-defect-btn" title="Hapus"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-2 text-muted small" id="noDefectMsg">
                            <i class="fas fa-check-circle mr-1 text-success"></i> Tidak ada data defect tercatat.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="row align-items-end mb-3">
                <div class="col-4">
                    <label class="small font-weight-bold text-success text-uppercase">Total OK</label>
                    <input type="number" name="total_ok" id="total_ok" class="form-control form-control-sm font-weight-bold border-success"
                        value="{{ $checksheet->total_ok }}" min="0" required>
                </div>
                <div class="col-4">
                    <label class="small font-weight-bold text-danger text-uppercase">Total NG</label>
                    <input type="number" name="total_ng" id="total_ng" class="form-control form-control-sm font-weight-bold border-danger"
                        value="{{ $checksheet->total_ng }}" min="0" required>
                </div>
                <div class="col-4">
                     <label class="small font-weight-bold text-uppercase">Judgment</label>
                     <select name="judgment" id="judgment" class="d-none" required>
                        <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                    </select>
                    {{-- Visual display for judgment --}}
                    <div id="judgmentDisplay" class="alert mb-0 p-1 text-center font-weight-bold border shadow-sm {{ $checksheet->judgment == 'OK' ? 'alert-success border-success text-success' : 'alert-danger border-danger text-danger' }}" style="height: 31px; line-height: 20px;">
                        {{ $checksheet->judgment }}
                    </div>
                </div>
            </div>

            <!-- Next Proses -->
            <div id="nextProsesContainer" style="{{ $checksheet->judgment == 'NG' ? '' : 'display: none;' }}">
                <div class="form-group mb-0 p-3 rounded" style="background: #fff5f5; border: 1px dashed #e74a3b;">
                    <label class="small font-weight-bold text-danger">Next Proses <span class="text-danger">*</span></label>
                    <select name="next_proses" id="next_proses" class="form-control form-control-sm border-0 shadow-sm font-weight-bold text-danger">
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
        </div>
    </div>


    <!-- 4. Footer Row: Check Dimensi (Full Width) -->
    <div class="font-weight-bold text-primary mb-3 pb-2 d-flex justify-content-between align-items-center mt-3" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
        <span class="font-weight-bold">PEMERIKSAAN DIMENSI (MM)</span>
        <div class="btn-group shadow-sm">
            <button type="button" class="btn btn-primary btn-xs px-3" id="editAddCavityBtn" title="Tambah Cavity" style="font-size: 0.7rem;">
                <i class="fas fa-plus mr-1"></i> Cavity
            </button>
            <button type="button" class="btn btn-info btn-xs px-3" id="editAddPointBtn" title="Tambah Point" style="font-size: 0.7rem;">
                <i class="fas fa-plus mr-1"></i> Point
            </button>
        </div>
    </div>

    <div class="table-responsive bg-white rounded shadow-sm border mb-4" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-bordered table-hover mb-0" id="editDimensionTable">
            <thead class="bg-light text-center small font-weight-bold">
                <tr id="editDimensionHeadRow">
                    <th style="min-width: 100px; position: sticky; top: 0; left: 0; z-index: 10; background: #f8f9fc; border-right: 2px solid #dee2e6;">Cavity / Point</th>
                    @php
                        $dims = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true) ?? [];
                        $maxC = count($dims) > 0 ? max(array_keys($dims)) : 5;
                        $maxP = 5;
                        foreach($dims as $pts) { if(is_array($pts) && count($pts) > 0) $maxP = max($maxP, max(array_keys($pts))); }
                    @endphp
                    @for ($j = 1; $j <= $maxP; $j++)
                        <th class="point-header" style="position: sticky; top: 0; background-color: #f8f9fc !important; color: #475569 !important; z-index: 9;">P{{ $j }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody id="editDimensionBody">
                @for ($i = 1; $i <= $maxC; $i++)
                    <tr class="edit-cavity-row" data-cavity="{{ $i }}">
                        <td class="text-center font-weight-bold bg-light small" style="position: sticky; left: 0; z-index: 5; background: #f8f9fc !important; border-right: 2px solid #dee2e6; vertical-align: middle;">
                            Cavity {{ $i }}
                        </td>
                        @for ($j = 1; $j <= $maxP; $j++)
                            <td class="p-0">
                                <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold"
                                    style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;" name="dimensions[{{ $i }}][{{ $j }}]"
                                    value="{{ $dims[$i][$j] ?? '' }}" placeholder="-">
                            </td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="bg-white border-top py-3 px-4 d-flex justify-content-end align-items-center" style="margin: 1.5rem -1.5rem -1.5rem -1.5rem; border-radius: 0 0 12px 12px;">
        <button type="button" class="btn btn-light border px-4 font-weight-bold mr-2" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSubmit"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
    </div>
</form>

<script>
    (function($) {
        // Data from server
        const partDimensionStandards = @json($partDimensionStandards);
        let currentPoints = {{ $maxP }};

        // UI Handlers
        $('#editAddWeightCavBtn').click(function() {
            const count = $('#editWeightCavContainer .edit-weight-cav-row').length + 1;
            $('#editWeightCavContainer').append(`
                <div class="input-group input-group-sm mb-1 edit-weight-cav-row">
                    <div class="input-group-prepend"><span class="input-group-text bg-white" style="min-width:60px; justify-content:center; font-weight:600;">CAV ${count}</span></div>
                    <input type="number" step="0.01" min="0" class="form-control text-center font-weight-bold" name="part_weight[]" placeholder="0.00">
                    <div class="input-group-append"><span class="input-group-text bg-white text-muted">gr</span></div>
                </div>
            `);
            $('#editWeightCavCount').text(count + ' Cav');
        });

        $('#editRemoveWeightCavBtn').click(function() {
            if($('#editWeightCavContainer .edit-weight-cav-row').length > 1) {
                $('#editWeightCavContainer .edit-weight-cav-row:last').remove();
                const count = $('#editWeightCavContainer .edit-weight-cav-row').length;
                $('#editWeightCavCount').text(count + ' Cav');
            }
        });

        $('#editAddDefectBtn').click(function() {
            let itemDefects = $('#item_id').find('option:selected').data('defects') || [];
            if (typeof itemDefects === 'string') {
                try { itemDefects = JSON.parse(itemDefects); } catch(e) { itemDefects = []; }
            }
            if (!Array.isArray(itemDefects)) itemDefects = [];

            let options = '<option value="">-- Pilih Defect --</option>';
            itemDefects.forEach(d => options += `<option value="${d}">${d}</option>`);
            // Pastikan opsi Dimensi selalu ada
            if (options.indexOf('value="dimension"') === -1 && options.indexOf('Dimensi') === -1) {
                options += '<option value="dimension">Dimensi</option>';
            }
            
            $('#noDefectMsg').remove();
            $('#editDefectContainer').append(`
                <div class="row no-gutters mb-2 defect-row align-items-center shadow-sm bg-white p-1 rounded">
                    <div class="col-8 pr-1"><select name="defect_types[]" class="form-control form-control-sm defect-select font-weight-bold">${options}</select></div>
                    <div class="col-3 pr-1"><input type="number" name="defect_quantities[]" class="form-control form-control-sm defect-qty text-center font-weight-bold" value="1" min="1"></div>
                    <div class="col-1 text-center"><button type="button" class="btn btn-link text-danger p-0 remove-defect-btn"><i class="fas fa-times-circle"></i></button></div>
                </div>
            `);
        });

        $(document).on('click', '.remove-defect-btn', function() {
            $(this).closest('.defect-row').remove();
            if($('.defect-row').length === 0) {
                $('#editDefectContainer').append('<div class="text-center py-2 text-muted small" id="noDefectMsg"><i class="fas fa-check-circle mr-1 text-success"></i> Tidak ada data defect tercatat.</div>');
            }
            calculateTotalNG();
        });

        $('#editAddCavityBtn').click(function() {
            const nextC = $('#editDimensionBody tr').length + 1;
            let row = `<tr class="edit-cavity-row" data-cavity="${nextC}">
                <td class="text-center font-weight-bold bg-light small" style="position: sticky; left: 0; z-index: 5; background: #f8f9fc !important; border-right: 2px solid #dee2e6; vertical-align: middle;">Cavity ${nextC}</td>`;
            for(let j=1; j<=currentPoints; j++) {
                row += `<td class="p-0"><input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold" style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;" name="dimensions[${nextC}][${j}]" placeholder="-"></td>`;
            }
            row += '</tr>';
            $('#editDimensionBody').append(row);
        });

        $('#editAddPointBtn').click(function() {
            currentPoints++;
            $('#editDimensionHeadRow').append(`<th class="point-header">P${currentPoints}</th>`);
            $('.edit-cavity-row').each(function() {
                const c = $(this).data('cavity');
                $(this).append(`<td class="p-0"><input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold" style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;" name="dimensions[${c}][${currentPoints}]" placeholder="-"></td>`);
            });
        });

        // Calculation Logic
        function getAqlLimits(s) {
            if (s >= 1250) return { a: 14, r: 15 }; if (s >= 800) return { a: 10, r: 11 };
            if (s >= 500) return { a: 7, r: 8 }; if (s >= 315) return { a: 5, r: 6 };
            if (s >= 200) return { a: 3, r: 4 }; if (s >= 125) return { a: 2, r: 3 };
            if (s >= 80) return { a: 1, r: 2 }; if (s >= 50) return { a: 1, r: 2 };
            if (s >= 32) return { a: 0, r: 1 }; return { a: 0, r: 1 };
        }

        function getSampleSize(l) {
            if (l >= 500001) return 1250; if (l >= 150001) return 800;
            if (l >= 35001) return 500; if (l >= 10001) return 315;
            if (l >= 3201) return 200; if (l >= 1201) return 125;
            if (l >= 501) return 80; if (l >= 281) return 50;
            if (l >= 151) return 32; if (l >= 20) return 20; return l;
        }

        function calculateTotalNG() {
            let t = 0; $('.defect-qty').each(function(){ t += parseInt($(this).val()) || 0; });
            $('#total_ng').val(t).trigger('change');
        }

        function updateJudgment() {
            const sampling = parseInt($('#sampling_qty').val()) || 0;
            const ng = parseInt($('#total_ng').val()) || 0;
            const isDimInvalid = $('.edit-dimension-input.is-invalid').length > 0;
            
            // Auto-defect Dimension
            let hasDimDef = false;
            $('.defect-select').each(function(){ 
                const text = ($(this).find('option:selected').text() || '').trim().toLowerCase();
                const val = ($(this).val() || '').trim().toLowerCase();
                if(text === 'dimensi' || text === 'dimension' || text === 'ng dimensi' || val === 'dimensi' || val === 'dimension' || val === 'ng dimensi') hasDimDef = true; 
            });
            
            if(isDimInvalid && !hasDimDef) {
                const dimDefName = ($('#item_id option:selected').data('defects') || []).find(d => (String(d)).toLowerCase() === 'dimensi' || (String(d)).toLowerCase() === 'dimension') || 'Dimensi';
                $('#noDefectMsg').remove();
                $('#editDefectContainer').prepend(`
                    <div class="row no-gutters mb-2 defect-row align-items-center shadow-sm bg-white p-1 rounded">
                        <div class="col-8 pr-1"><select name="defect_types[]" class="form-control form-control-sm defect-select font-weight-bold"><option value="${dimDefName}" selected>${dimDefName}</option></select></div>
                        <div class="col-3 pr-1"><input type="number" name="defect_quantities[]" class="form-control form-control-sm defect-qty text-center font-weight-bold" value="1" min="1"></div>
                        <div class="col-1 text-center"><button type="button" class="btn btn-link text-danger p-0 remove-defect-btn"><i class="fas fa-times-circle"></i></button></div>
                    </div>
                `);
                return calculateTotalNG(); 
            } else if (!isDimInvalid && hasDimDef) {
                // Remove Dimensi defect if it exists and dimensions are OK
                $('.defect-select').each(function() {
                    const text = ($(this).find('option:selected').text() || '').trim().toLowerCase();
                    const val = ($(this).val() || '').trim().toLowerCase();
                    if(text === 'dimensi' || text === 'dimension' || text === 'ng dimensi' || val === 'dimensi' || val === 'dimension' || val === 'ng dimensi') {
                        $(this).closest('.defect-row').remove();
                    }
                });
                if($('.defect-row').length === 0) {
                    $('#editDefectContainer').append('<div class="text-center py-2 text-muted small" id="noDefectMsg"><i class="fas fa-check-circle mr-1 text-success"></i> Tidak ada data defect tercatat.</div>');
                }
                return calculateTotalNG();
            }

            const currentOk = Math.max(0, sampling - ng);
            $('#total_ok').val(currentOk);

            const lim = getAqlLimits(sampling);
            let j = 'OK';
            if(isDimInvalid || ng >= lim.r) j = 'NG';
            else if(ng <= lim.a) j = 'OK';
            else j = 'NG';

            $('#judgment').val(j);
            $('#judgmentDisplay').text(j).removeClass('alert-success alert-danger border-success border-danger text-success text-danger')
                .addClass(j === 'OK' ? 'alert-success border-success text-success' : 'alert-danger border-danger text-danger');

            if(j === 'NG') $('#nextProsesContainer').slideDown();
            else $('#nextProsesContainer').slideUp();
        }

        // Validation Logic
        function normalizePN(p){ return p ? p.toString().replace(/[\u2012\u2013\u2014\u2212]/g, '-').replace(/\s+/g, '').toUpperCase() : ''; }
        function normalizeStd(v){ return (v === null || v === undefined || v === '') ? null : v.toString().replace(',', '.').replace(/[\u2012\u2013\u2014\u2212]/g, '-').replace(/±/g, '').trim(); }

        function validateDimensions() {
            const sel = $('#item_id option:selected');
            const pn = normalizePN(sel.data('part-number'));

            let dimensionStandards = sel.data('dimension-standards');
            if (typeof dimensionStandards === 'string') {
                try { dimensionStandards = JSON.parse(dimensionStandards); }
                catch (e) { dimensionStandards = null; }
            }
            if (!dimensionStandards) {
                dimensionStandards = (typeof partDimensionStandards !== 'undefined') ? partDimensionStandards[pn] : null;
            }

            $('.edit-dimension-input').each(function() {
                const name = $(this).attr('name');
                const m = name.match(/\[\d+\]\[(\d+)\]/); if(!m) return;
                const point = m[1];
                const valStr = $(this).val().trim();
                const val = parseFloat(valStr.replace(',', '.'));

                let standard = null;
                if (dimensionStandards) {
                    if (Array.isArray(dimensionStandards)) {
                        standard = dimensionStandards.find(s => String(s.point) === String(point))
                            || dimensionStandards[point - 1];
                    } else {
                        standard = dimensionStandards[point];
                    }
                }

                $(this).removeClass('is-invalid is-valid bg-danger text-white');

                if(standard && valStr !== '' && !isNaN(val)) {
                    let isInvalid = false;
                    const epsilon = 0.00001;

                    // 1. Min / Max absolut
                    if (standard.min != null && standard.min !== '') {
                        const minBound = parseFloat(String(standard.min).replace(',', '.'));
                        if (!isNaN(minBound) && val < minBound - epsilon) isInvalid = true;
                    }
                    if (!isInvalid && standard.max != null && standard.max !== '') {
                        const maxBound = parseFloat(String(standard.max).replace(',', '.'));
                        if (!isNaN(maxBound) && val > maxBound + epsilon) isInvalid = true;
                    }

                    // 2. Size ± Tolerance
                    if (!isInvalid &&
                        standard.size != null && standard.tolerance != null &&
                        standard.size !== '' && standard.tolerance !== '') {
                        const stdSzStr = normalizeStd(standard.size);
                        if (stdSzStr && !stdSzStr.startsWith('+') && !stdSzStr.startsWith('-')) {
                            const base = parseFloat(stdSzStr);
                            const tol = normalizeStd(standard.tolerance);
                            let lb = base, ub = base;

                            if (tol.includes('/')) {
                                tol.split('/').forEach(p => {
                                    p = normalizeStd(p);
                                    const fv = parseFloat(p);
                                    if (p.startsWith('+') || fv > 0) ub = base + Math.abs(fv);
                                    else if (p.startsWith('-') || fv < 0) lb = base - Math.abs(fv);
                                });
                            } else if (tol.startsWith('+')) {
                                ub = base + parseFloat(tol.substring(1));
                            } else if (tol.startsWith('-')) {
                                lb = base + parseFloat(tol);
                            } else {
                                const tv = parseFloat(tol);
                                lb = base - tv;
                                ub = base + tv;
                            }

                            if (val < lb - epsilon || val > ub + epsilon) isInvalid = true;
                        }
                    }

                    // 3. Size dengan prefix +/- (tanpa tolerance)
                    if (!isInvalid && standard.size != null && standard.size !== '') {
                        const sz = String(standard.size);
                        if (sz.startsWith('+') || sz.startsWith('-')) {
                            const op = sz.charAt(0);
                            const bound = parseFloat(sz.substring(1));
                            if (!isNaN(bound)) {
                                if (op === '+' && val < bound - epsilon) isInvalid = true;
                                else if (op === '-' && val > bound + epsilon) isInvalid = true;
                            }
                        }
                    }

                    if(isInvalid) {
                        $(this).addClass('is-invalid bg-danger text-white');
                    } else {
                        $(this).addClass('is-valid');
                    }
                }
            });
            updateJudgment();
        }

        // Global Listeners
        $(document).on('input', '#total_qty', function(){ $('#sampling_qty').val(getSampleSize(parseInt($(this).val()) || 0)).trigger('input'); });
        $(document).on('input change', '#sampling_qty, #total_ng', updateJudgment);
        $(document).on('input', '.defect-qty', calculateTotalNG);
        $(document).on('change', '.defect-select', calculateTotalNG);
        $(document).on('input', '.edit-dimension-input', validateDimensions);
        $(document).on('change', '#item_id', validateDimensions);

        // Initial run
        validateDimensions();
    })(jQuery);
</script>

<style>
    .btn-xs { padding: 0.15rem 0.4rem; font-size: 0.75rem; }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
    .point-header { min-width: 60px; font-size: 0.75rem; background: #f8f9fc; }
    .is-invalid { border-color: #e74a3b !important; }
    .is-valid { border-color: #1cc88a !important; }
</style>
