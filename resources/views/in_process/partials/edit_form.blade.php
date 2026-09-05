<form id="editChecksheetForm" class="ajax-form"
    action="{{ route('in_process.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    @php
        $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true) ?? [];
    @endphp
    {{-- Preserve all filter and pagination parameters for redirect --}}
    @foreach(request()->except(['_token', '_method', 'id']) as $key => $value)
        @if(!is_array($value) && $value !== null && $value !== '')
            <input type="hidden" name="redirect_params[{{ $key }}]" value="{{ $value }}">
        @endif
    @endforeach

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

    {{-- Hidden inputs to preserve QR data during update --}}
    <input type="hidden" name="qrcode" value="{{ $checksheet->qrcode }}">
    <input type="hidden" name="part_code" value="{{ $checksheet->part_code }}">
    <input type="hidden" name="supplier_id" value="{{ $checksheet->supplier_id }}">
    <input type="hidden" name="quantity" value="{{ $checksheet->quantity }}">
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
                        <option value="" disabled>Pilih Item Part</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                                data-part-number="{{ $item->part_number }}"
                                data-customer="{{ $item->customer }}"
                                data-weight-standard="{{ $item->weight_standard }}"
                                data-dimension-standards='@json($item->dimension_standards ?? [])'
                                data-defects='@json($item->defects ?? [])'>
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
                            $jakartaMachineNumbers = [1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
                            $karawangMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19];
                            $machineNumbers = ($plantCode === 'jakarta') ? $jakartaMachineNumbers : $karawangMachineNumbers;
                        @endphp
                        @foreach ($machineNumbers as $num)
                            <option value="{{ $num }}" {{ $checksheet->code_machine == $num ? 'selected' : '' }}>Machine {{ $num }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Inisial Operator</label>
                <div class="col-sm-8">
                    <input type="text" name="operator_initials" id="operator_initials" class="form-control form-control-sm border-0 shadow-sm text-uppercase bg-light font-weight-bold"
                        value="{{ $checksheet->operator_initials }}" placeholder="Inisial..." readonly>
                </div>
            </div>

            @if(auth()->user()->role !== 'inspector')
            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Inspector (System)</label>
                <div class="col-sm-8">
                    <select name="user_id" id="user_id" class="form-control form-control-sm border-0 shadow-sm">
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
                    <textarea name="remarks" id="remarks" class="form-control form-control-sm border-0 shadow-sm" rows="3" placeholder="Catatan tambahan...">{{ $checksheet->remarks }}</textarea>
                </div>
            </div>

            <!-- Section Berat Part (Hanya muncul jika customer tertentu) -->
            <div id="editBeratPartRow" style="display: none;" class="mt-3 bg-white p-3 rounded shadow-sm border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="small font-weight-bold text-gray-700 mb-0">
                        <i class="fas fa-weight mr-1"></i> Berat Part (gr.)
                        <span class="badge badge-secondary ml-2 font-weight-normal" id="editWeightStandardBadge" style="display: none;">
                            Std: <span id="editWeightStandardDisplay">-</span> gr
                        </span>
                    </label>
                    
                    <div class="d-flex align-items-center" style="gap:5px;">
                        <div class="btn-group shadow-sm">
                            <button type="button" id="editAddWeightCavBtn" class="btn btn-primary btn-xs px-2" title="Tambah Cavity" style="font-size: 0.7rem;"><i class="fas fa-plus"></i></button>
                            <button type="button" id="editRemoveWeightCavBtn" class="btn btn-danger btn-xs px-2" title="Kurangi Cavity" style="font-size: 0.7rem;"><i class="fas fa-minus"></i></button>
                        </div>
                        <span id="editWeightCavCount" class="badge badge-primary px-2 py-1" style="font-size: 0.7rem;">1 Cav</span>
                    </div>
                </div>

                <div id="editWeightCavContainer">
                    @php
                        $existingWeights = is_array($checksheet->part_weight)
                            ? $checksheet->part_weight
                            : (is_string($checksheet->part_weight) && str_starts_with($checksheet->part_weight, '[') ? json_decode($checksheet->part_weight, true) : ($checksheet->part_weight ? [$checksheet->part_weight] : [null]));
                    @endphp
                    @foreach($existingWeights as $cavIdx => $wVal)
                        <div class="input-group input-group-sm mb-2 edit-weight-cav-row">
                            <div class="input-group-prepend shadow-sm">
                                <span class="input-group-text bg-light border-0" style="min-width:60px; justify-content:center; font-weight:600;">CAV {{ $cavIdx + 1 }}</span>
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
                    <button type="button" id="editAddDefectBtn" class="btn btn-info btn-xs px-3" style="font-size: 0.7rem; {{ count($defects) > 0 || $checksheet->total_ng > 0 ? '' : 'display:none;' }}">
                        <i class="fas fa-plus mr-1"></i> Tambah Jenis NG
                    </button>
                </div>
                
                <div id="editDefectContainer" class="bg-light p-2 rounded border-dashed" style="border: 1px dashed #ced4da;">
                    @forelse($defects as $index => $defect)
                        <div class="row no-gutters mb-2 defect-row align-items-center shadow-sm bg-white p-1 rounded">
                            <div class="col-8 pr-1">
                                <select class="form-control form-control-sm defect-select font-weight-bold" name="defect_types[]">
                                    <option value="">-- Pilih Defect --</option>
                                    <option value="{{ $defect['type'] }}" selected>{{ $defect['type'] }}</option>
                                </select>
                            </div>
                            <div class="col-3 pr-1">
                                <input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold" 
                                    name="defect_quantities[]" value="{{ $defect['qty'] }}" min="1">
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

    @php
        $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : (json_decode($checksheet->dimension_check, true) ?? []);
        $maxCavityFound = 5;
        $maxPointFound = 5;
        foreach ($dimensions as $cav => $pts) {
            if (is_numeric($cav)) $maxCavityFound = max($maxCavityFound, (int) $cav);
            if (is_array($pts)) {
                foreach ($pts as $pt => $val) {
                    if (is_numeric($pt)) $maxPointFound = max($maxPointFound, (int) $pt);
                }
            }
        }
    @endphp

    {{-- SHOOT 1 TABLE (ATAS) --}}
    <div class="mb-3">
        <small class="font-weight-bold text-muted d-block mb-1" style="font-size: 0.75rem;">Shoot 1:</small>
        <div class="table-responsive bg-white rounded shadow-sm border" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" id="editDimensionTableShoot1">
                <thead class="bg-light text-center small font-weight-bold">
                    <tr id="editDimensionHeadRowShoot1">
                        <th style="min-width: 100px; position: sticky; top: 0; left: 0; z-index: 10; background: #f8f9fc; border-right: 2px solid #dee2e6;">Cavity / Point</th>
                        @for ($j = 1; $j <= $maxPointFound; $j++)
                            <th class="point-header" style="position: sticky; top: 0; background-color: #f8f9fc !important; color: #475569 !important; z-index: 9;">P{{ $j }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody id="editDimensionBodyShoot1">
                    @for ($i = 1; $i <= $maxCavityFound; $i++)
                        <tr class="edit-cavity-row-shoot1" data-cavity="{{ $i }}">
                            <td class="text-center font-weight-bold bg-light small" style="position: sticky; left: 0; z-index: 5; background: #f8f9fc !important; border-right: 2px solid #dee2e6; vertical-align: middle;">
                                Cavity {{ $i }}
                            </td>
                            @for ($j = 1; $j <= $maxPointFound; $j++)
                                @php
                                    $valCheck = $dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? ($dimensions[$i]["$j"] ?? null));
                                    $v1 = is_array($valCheck) ? ($valCheck['p1'] ?? ($valCheck['s1'] ?? ($valCheck[0] ?? ''))) : ($valCheck ?? '');
                                @endphp
                                <td class="p-0">
                                    <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold"
                                        style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;" name="dimensions[{{ $i }}][{{ $j }}][p1]"
                                        value="{{ $v1 }}" placeholder="-">
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    {{-- SHOOT 2 TABLE (BAWAH) --}}
    <div class="mb-4">
        <small class="font-weight-bold text-muted d-block mb-1" style="font-size: 0.75rem;">Shoot 2:</small>
        <div class="table-responsive bg-white rounded shadow-sm border" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" id="editDimensionTableShoot2">
                <thead class="bg-light text-center small font-weight-bold">
                    <tr id="editDimensionHeadRowShoot2">
                        <th style="min-width: 100px; position: sticky; top: 0; left: 0; z-index: 10; background: #f8f9fc; border-right: 2px solid #dee2e6;">Cavity / Point</th>
                        @for ($j = 1; $j <= $maxPointFound; $j++)
                            <th class="point-header" style="position: sticky; top: 0; background-color: #f8f9fc !important; color: #475569 !important; z-index: 9;">P{{ $j }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody id="editDimensionBodyShoot2">
                    @for ($i = 1; $i <= $maxCavityFound; $i++)
                        <tr class="edit-cavity-row-shoot2" data-cavity="{{ $i }}">
                            <td class="text-center font-weight-bold bg-light small" style="position: sticky; left: 0; z-index: 5; background: #f8f9fc !important; border-right: 2px solid #dee2e6; vertical-align: middle;">
                                Cavity {{ $i }}
                            </td>
                            @for ($j = 1; $j <= $maxPointFound; $j++)
                                @php
                                    $valCheck = $dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? ($dimensions[$i]["$j"] ?? null));
                                    $v2 = is_array($valCheck) ? ($valCheck['p2'] ?? ($valCheck['s2'] ?? ($valCheck[1] ?? ''))) : '';
                                @endphp
                                <td class="p-0">
                                    <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold"
                                        style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;" name="dimensions[{{ $i }}][{{ $j }}][p2]"
                                        value="{{ $v2 }}" placeholder="-">
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border-top py-3 px-4 d-flex justify-content-end align-items-center" style="margin: 1.5rem -1.5rem -1.5rem -1.5rem; border-radius: 0 0 12px 12px;">
        <button type="button" class="btn btn-light border px-4 font-weight-bold mr-2" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSubmit"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
    </div>
</form>

@php
// We remove @push because this is loaded via AJAX and @push doesn't work in AJAX responses.
// The script will execute normally when inserted into the DOM.
@endphp
<script>
    (function () {
        (function ($) {
            // Initial counts from PHP
            // Note: $partDimensionStandards is already a JSON string from the controller
            const partDimensionStandards = {!! $partDimensionStandards ?: '{}' !!};
            let currentCavities = {{ $maxCavityFound }};
            let currentPoints = {{ $maxPointFound }};
            const maxCavities = 50;
            const maxPoints = 50;

        $('#editAddCavityBtn').click(function () {
            if (currentCavities < maxCavities) {
                currentCavities++;
                let newRow1 = `<tr class="edit-cavity-row-shoot1" data-cavity="${currentCavities}">
                                <td class="text-center font-weight-bold bg-light small" style="position: sticky; left: 0; z-index: 5; background: #f8f9fc !important; border-right: 2px solid #dee2e6; vertical-align: middle;">
                                    Cavity ${currentCavities}
                                </td>`;

                for (let j = 1; j <= currentPoints; j++) {
                    newRow1 += `<td class="p-0">
                                    <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold" 
                                        style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;"
                                        name="dimensions[${currentCavities}][${j}][p1]" 
                                        placeholder="-">
                                </td>`;
                }
                newRow1 += `</tr>`;
                $('#editDimensionBodyShoot1').append(newRow1);

                let newRow2 = `<tr class="edit-cavity-row-shoot2" data-cavity="${currentCavities}">
                                <td class="text-center font-weight-bold bg-light small" style="position: sticky; left: 0; z-index: 5; background: #f8f9fc !important; border-right: 2px solid #dee2e6; vertical-align: middle;">
                                    Cavity ${currentCavities}
                                </td>`;

                for (let j = 1; j <= currentPoints; j++) {
                    newRow2 += `<td class="p-0">
                                    <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold" 
                                        style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;"
                                        name="dimensions[${currentCavities}][${j}][p2]" 
                                        placeholder="-">
                                </td>`;
                }
                newRow2 += `</tr>`;
                $('#editDimensionBodyShoot2').append(newRow2);
            } else {
                Swal.fire('Limit!', 'Maksimum 50 cavities.', 'warning');
            }
        });

        $('#editAddPointBtn').click(function () {
            if (currentPoints < maxPoints) {
                currentPoints++;
                // Add header to both tables
                $('#editDimensionHeadRowShoot1, #editDimensionHeadRowShoot2').append(`<th class="point-header" style="background-color: #f8f9fc !important; color: #475569 !important;">P${currentPoints}</th>`);

                // Add cells to Shoot 1
                $('.edit-cavity-row-shoot1').each(function () {
                    let cavityNum = $(this).data('cavity');
                    $(this).append(`<td class="p-0">
                                    <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold" 
                                        style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;"
                                        name="dimensions[${cavityNum}][${currentPoints}][p1]" 
                                        placeholder="-">
                                </td>`);
                });

                // Add cells to Shoot 2
                $('.edit-cavity-row-shoot2').each(function () {
                    let cavityNum = $(this).data('cavity');
                    $(this).append(`<td class="p-0">
                                    <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold" 
                                        style="min-width: 60px; font-size: 0.8rem; height: 38px; border-radius: 0;"
                                        name="dimensions[${cavityNum}][${currentPoints}][p2]" 
                                        placeholder="-">
                                </td>`);
                });
            } else {
                Swal.fire('Limit!', 'Maksimum 50 points.', 'warning');
            }
        });

        function getSampleSize(lotSize) {
            if (lotSize >= 500001) return 1250;
            if (lotSize >= 150001) return 800;
            if (lotSize >= 35001) return 500;
            if (lotSize >= 10001) return 315;
            if (lotSize >= 3201) return 200;
            if (lotSize >= 1201) return 125;
            if (lotSize >= 501) return 80;
            if (lotSize >= 281) return 50;
            if (lotSize >= 151) return 32;
            if (lotSize >= 20) return 20;
            return lotSize; // 100% Check for lots < 20
        }

        function getAqlLimits(sampleSize) {
            if (sampleSize >= 1250) return { acc: 14, rej: 15 };
            if (sampleSize >= 800) return { acc: 10, rej: 11 };
            if (sampleSize >= 500) return { acc: 7, rej: 8 };
            if (sampleSize >= 315) return { acc: 5, rej: 6 };
            if (sampleSize >= 200) return { acc: 3, rej: 4 };
            if (sampleSize >= 125) return { acc: 2, rej: 3 };
            if (sampleSize >= 80) return { acc: 1, rej: 2 };
            if (sampleSize >= 50) return { acc: 1, rej: 2 };
            if (sampleSize >= 32) return { acc: 0, rej: 1 };
            if (sampleSize >= 20) return { acc: 0, rej: 1 };
            return { acc: 0, rej: 1 };
        }

        function updateJudgment() {
            const sampling = parseInt($('#sampling_qty').val()) || 0;
            const ng = parseInt($('#total_ng').val()) || 0;
            const isDimensiInvalid = $('.edit-dimension-input.is-invalid').length > 0;

            // 0. Handle Dimension Defect independently of Lot Judgment
            var hasDimensiDefect = false;
            $('.defect-select').each(function () {
                var text = ($(this).find('option:selected').text() || '').trim().toLowerCase();
                var val = ($(this).val() || '').trim().toLowerCase();
                if (text === 'dimensi' || text === 'dimension' || text === 'ng dimensi' || val === 'dimensi' || val === 'dimension' || val === 'ng dimensi') {
                    hasDimensiDefect = true;
                    return false;
                }
            });

            if (isDimensiInvalid && !hasDimensiDefect) {
                autoAddDimensionDefect();
            } else if (!isDimensiInvalid && hasDimensiDefect) {
                autoRemoveDimensionDefect();
            }

            // RE-INITIALIZE NG after auto-defect might have changed it
            const currentNg = parseInt($('#total_ng').val()) || 0;

            if (sampling >= currentNg) {
                $('#total_ok').val(sampling - currentNg);
            } else {
                $('#total_ok').val(Math.max(0, sampling - currentNg));
            }

            const limits = getAqlLimits(sampling);
            const judgmentSelect = $('#judgment');
            const judgmentDisplay = $('#judgmentDisplay');

            if (currentNg > 0 || sampling > 0 || isDimensiInvalid) {
                let finalJudgment = 'OK';
                if (isDimensiInvalid || currentNg >= limits.rej) {
                    finalJudgment = 'NG';
                } else if (currentNg <= limits.acc) {
                    finalJudgment = 'OK';
                } else {
                    finalJudgment = 'NG';
                }

                judgmentSelect.val(finalJudgment);
                judgmentDisplay.text(finalJudgment);
                
                if (finalJudgment === 'OK') {
                    judgmentSelect.removeClass('text-danger').addClass('text-success');
                    judgmentDisplay.removeClass('alert-danger border-danger text-danger').addClass('alert-success border-success text-success');
                } else {
                    judgmentSelect.removeClass('text-success').addClass('text-danger');
                    judgmentDisplay.removeClass('alert-success border-success text-success').addClass('alert-danger border-danger text-danger');
                }
            } else {
                judgmentSelect.val('');
                judgmentDisplay.text('-').removeClass('alert-success alert-danger border-success border-danger');
            }
            toggleNextProses();
        }

        function autoAddDimensionDefect() {
            var foundRow = null;
            $('.defect-select').each(function () {
                var val = $(this).val();
                var text = $(this).find('option:selected').text();
                if (val === 'dimension' || text.toLowerCase() === 'dimensi') {
                    foundRow = $(this).closest('.defect-row');
                    return false;
                }
            });

            if (foundRow) {
                var qtyInput = foundRow.find('.defect-qty');
                if (!qtyInput.val() || parseInt(qtyInput.val()) <= 0) {
                    qtyInput.val(1).trigger('input');
                }
                return;
            }

            var targetSelect = null;
            $('.defect-select').each(function () {
                if ($(this).val() === '') {
                    targetSelect = $(this);
                    return false;
                }
            });

            if (!targetSelect) {
                var rowCount = $('.defect-row').length;
                if (rowCount < 5) {
                    $('#editAddDefectBtn').trigger('click');
                    targetSelect = $('.defect-select').last();
                } else {
                    targetSelect = $('.defect-select').first();
                }
            }

            if (targetSelect) {
                var options = targetSelect.find('option');
                var foundVal = '';
                options.each(function () {
                    if ($(this).val() === 'dimension' || $(this).text().toLowerCase() === 'dimensi') {
                        foundVal = $(this).val();
                        if (!foundVal) foundVal = $(this).text();
                        return false;
                    }
                });

                if (!foundVal) {
                    targetSelect.append('<option value="Dimensi">Dimensi</option>');
                    foundVal = 'Dimensi';
                }

                targetSelect.val(foundVal).trigger('change');
                targetSelect.closest('.defect-row').find('.defect-qty').val(1).trigger('input');
                calculateTotalNG();
            }
        }

        function autoRemoveDimensionDefect() {
            $('.defect-select').each(function () {
                var val = $(this).val();
                var text = $(this).find('option:selected').text();

                if (val === 'dimension' || text.toLowerCase() === 'dimensi') {
                    var row = $(this).closest('.defect-row');
                    if ($('.defect-row').length === 1) {
                        $(this).val('').trigger('change');
                        row.find('.defect-qty').val('');
                    } else {
                        row.remove();
                    }
                    calculateTotalNG();
                    return false;
                }
            });
        }

        function isOnlyDimensiDefect() {
            let hasNonDimensiDefect = false;
            let hasAnyDefect = false;

            $('.defect-select').each(function () {
                const typeVal = ($(this).val() || '').trim().toLowerCase();
                const typeText = ($(this).find('option:selected').text() || '').trim().toLowerCase();
                const qtyInput = $(this).closest('.defect-row').find('.defect-qty');
                const qty = parseInt(qtyInput.val()) || 0;

                if (typeVal !== '' && qty > 0) {
                    hasAnyDefect = true;
                    if (typeVal !== 'dimensi' && typeVal !== 'ng dimensi' && typeVal !== 'dimension' &&
                        typeText !== 'dimensi' && typeText !== 'ng dimensi' && typeText !== 'dimension') {
                        hasNonDimensiDefect = true;
                    }
                }
            });

            const isDimensiInvalid = $('.edit-dimension-input.is-invalid').length > 0;
            
            // Return true if NG is caused ONLY by dimensions (no non-dimension defects present)
            if (!hasNonDimensiDefect && (isDimensiInvalid || hasAnyDefect)) {
                return true;
            }

            return false;
        }

        function toggleNextProses() {
            const judgment = $('#judgment').val();
            const container = $('#nextProsesContainer');

            // If NG is strictly dimension-only, Next Proses is NOT mandatory, hide container
            if (judgment === 'NG' && !isOnlyDimensiDefect()) {
                container.fadeIn();
            } else {
                container.fadeOut();
            }
        }

        function normalizePartNumber(pn) {
            if (!pn) return '';
            return pn.toString()
                .replace(/[\u2012\u2013\u2014\u2212]/g, '-') // EN, EM, FIGURE DASH, MINUS
                .replace(/\s+/g, '') // Remove all whitespace
                .toUpperCase();
        }

        function normalizeStandardValue(val) {
            if (val === null || val === undefined || val === '') return null;
            return val.toString()
                .replace(',', '.')
                .replace(/[\u2012\u2013\u2014\u2212]/g, '-')
                .replace(/[Ø⌀ø±\u00B1\u00D8\u00F8\u2300]/g, '')
                .trim();
        }

        function validateDimensions() {
            const selectedOption = $('#item_id').find('option:selected');
            const rawPartNumber = selectedOption.data('part-number');
            const itemPartNumber = normalizePartNumber(rawPartNumber);
            
            // Prioritize direct standards, fallback to global
            let dimensionStandards = selectedOption.data('dimension-standards');
            if (typeof dimensionStandards === 'string') {
                try { dimensionStandards = JSON.parse(dimensionStandards); } catch(e) { dimensionStandards = null; }
            }
            if (!dimensionStandards) {
                dimensionStandards = partDimensionStandards[itemPartNumber];
            }

            $('.edit-dimension-input').each(function () {
                const name = $(this).attr('name');
                const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                if (!match) return;

                const point = match[2];

                // Robust lookup for standard (no cross-point fallback)
                let standard = null;
                if (dimensionStandards) {
                    if (Array.isArray(dimensionStandards)) {
                        standard = dimensionStandards.find(s => String(s.point) === String(point)) || null;
                    } else {
                        standard = dimensionStandards[point] || null;
                    }
                }
                const valStr = $(this).val().trim();
                const value = parseFloat(valStr.replace(',', '.'));

                $(this).removeClass('is-invalid is-valid');

                if (standard && valStr !== '' && !isNaN(value)) {
                    let isInvalid = false;
                    const epsilon = 0.00001;

                    const stdSizeStr = normalizeStandardValue(standard.size);
                    
                    // 1. Check Absolute Min/Max
                    if (standard.min != null && standard.min !== '') {
                        const minBound = parseFloat(String(standard.min).replace(',', '.'));
                        if (!isNaN(minBound) && value < (minBound - epsilon)) isInvalid = true;
                    }
                    if (!isInvalid && standard.max != null && standard.max !== '') {
                        const maxBound = parseFloat(String(standard.max).replace(',', '.'));
                        if (!isNaN(maxBound) && value > (maxBound + epsilon)) isInvalid = true;
                    }

                    // 2. Check Size +/- Tolerance
                    if (!isInvalid && standard.size != null && standard.tolerance != null && standard.size !== '' && standard.tolerance !== '') {
                        if (stdSizeStr && !stdSizeStr.startsWith('+') && !stdSizeStr.startsWith('-')) {
                            const base = parseFloat(stdSizeStr);
                            const tol = normalizeStandardValue(standard.tolerance);
                            let lb = base, ub = base;
                            
                            if (tol.includes('/')) {
                                tol.split('/').forEach(p => {
                                    p = normalizeStandardValue(p);
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
                                lb = base - tv; ub = base + tv;
                            }
                            
                            if (value < (lb - epsilon) || value > (ub + epsilon)) isInvalid = true;
                        }
                    }

                    // 3. Check Special Size (with prefix)
                    if (!isInvalid && standard.size != null && standard.size !== '') {
                        if (stdSizeStr && (stdSizeStr.startsWith('+') || stdSizeStr.startsWith('-'))) {
                            const op = stdSizeStr.charAt(0);
                            const bound = parseFloat(stdSizeStr.substring(1));
                            if (!isNaN(bound)) {
                                if (op === '+' && value < (bound - epsilon)) isInvalid = true;
                                else if (op === '-' && value > (bound + epsilon)) isInvalid = true;
                            }
                        }
                    }

                    if (isInvalid) {
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).addClass('is-valid');
                    }
                }
            });

            updateJudgment();
        }

        $(document).on('input', '.edit-dimension-input', function() {
            let val = $(this).val();
            if (val.startsWith('+0')) {
                $(this).val(val.replace(/^\+0/, ''));
            }
        });

        $(document).on('input', '.edit-dimension-input', validateDimensions);

        // --- Otomatisasi Input & Kalkulasi (AQL & OK/NG) ---
        
        // 1. Saat Total Produksi diubah -> Update Sampling Qty (AQL)
        // Menggunakan delegasi pada form untuk keandalan maksimal
        $(document).on('input keyup change', '#total_qty, input[name="total_qty"]', function() {
            const lotSize = parseInt($(this).val()) || 0;
            const sampleSize = getSampleSize(lotSize);
            
            // Update Sampling Qty
            const $sampling = $('#sampling_qty, input[name="sampling_qty"]');
            $sampling.val(sampleSize);
            
            // Panggil paksa updateJudgment untuk menghitung ulang Total OK
            updateJudgment();
            
            // Trigger event input agar listener lain ikut terupdate
            $sampling.trigger('input').trigger('change');
        });

        // 2. Saat Sampling Qty atau Total NG diubah -> Hitung Total OK & Judgment
        $(document).on('input change', '#sampling_qty, input[name="sampling_qty"], #total_ng, input[name="total_ng"]', function() {
            updateJudgment();
        });

        $(document).on('change', '#item_id', function() {
            validateDimensions();
            updateJudgment();
        });
        
        $(document).on('change', '#judgment', updateJudgment);

        // Form Submit Validation
        $('#editChecksheetForm').on('submit', function (e) {
            const judgment = $('#judgment').val();
            const nextProses = $('#next_proses').val();
            const totalNg = parseInt($('#total_ng').val()) || 0;

            if (judgment === 'NG' && !nextProses && !isOnlyDimensiDefect()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Next Proses Wajib Dipilih',
                    text: 'Untuk hasil NG visual/fisik, silakan pilih Next Proses terlebih dahulu!',
                    confirmButtonColor: '#3085d6'
                });
                
                const $nextProses = $('#next_proses');
                $nextProses.addClass('is-invalid').focus();
                setTimeout(function() {
                    $nextProses.removeClass('is-invalid');
                }, 3000);
                
                return false;
            }

            if (totalNg > 0) {
                let isDefectEmpty = false;
                let defectCount = 0;
                
                $('.defect-select').each(function() {
                    defectCount++;
                    if ($(this).val() === '') {
                        isDefectEmpty = true;
                        $(this).addClass('is-invalid');
                        
                        let that = $(this);
                        setTimeout(function() {
                            that.removeClass('is-invalid');
                        }, 3000);
                    }
                });

                if (defectCount === 0 || isDefectEmpty) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Defect Belum Dipilih',
                        text: 'Total NG terisi, silakan pilih jenis defect pada daftar Detail NG!',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            }
            
            $('#editChecksheetForm').find(':input:disabled').each(function() {
                $(this).prop('disabled', false).addClass('was-disabled');
            });
            
            $('#btnSubmit').prop('disabled', true);
            $('#btnSubmit').html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
        });
        
        $(document).on('ajaxComplete ajaxError', function() {
            $('.was-disabled').prop('disabled', true).removeClass('was-disabled');
            $('#btnSubmit').prop('disabled', false);
            $('#btnSubmit').html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
        });

        // Initial check
        validateDimensions();

        // --- Defect & NG Logic ---
        var defaultDefects = [
            { value: 'scratch', text: 'BARET' },
            { value: 'silver', text: 'SILVER' },
            { value: 'flow', text: 'FLOW' },
            { value: 'flash', text: 'FLASH' },
            { value: 'shoot_mold', text: 'SHOOT MOLD' },
            { value: 'bending', text: 'BENDING' },
            { value: 'sinkmark', text: 'SINKMARK' },
            { value: 'dimension', text: 'Dimensi' }
        ];

        function updateDefectOptions() {
            var selectedOption = $('#item_id').find('option:selected');
            var defectsData = selectedOption.data('defects');

            if (typeof defectsData === 'string') {
                try { defectsData = JSON.parse(defectsData); } catch (e) { defectsData = []; }
            }

            $('.defect-select').each(function() {
                var currentVal = $(this).val();
                $(this).empty();
                $(this).append('<option value="">-- Pilih Defect --</option>');

                if (Array.isArray(defectsData) && defectsData.length > 0) {
                    var that = this;
                    $.each(defectsData, function (index, value) {
                        $(that).append('<option value="' + value + '">' + value + '</option>');
                    });
                } else {
                    var that = this;
                    $.each(defaultDefects, function (index, defect) {
                        $(that).append('<option value="' + defect.text + '">' + defect.text + '</option>');
                    });
                }
                
                if (currentVal) $(this).val(currentVal);
            });
        }

        $('#item_id').change(function() {
            var selectedOption = $(this).find('option:selected');
            var customer = selectedOption.data('customer');
            var weightStandard = selectedOption.data('weight-standard');

            if (customer && (customer.toUpperCase().includes('ASTRA HONDA MOTOR') || customer.toUpperCase().includes('AHM') || customer.toUpperCase().includes('PT. TAKAGI SARI MULTI UTAMA'))) {
                $('#editBeratPartRow').fadeIn();
                if (weightStandard) {
                    $('#editWeightStandardDisplay').text(weightStandard);
                    $('#editWeightStandardBadge').show();
                } else {
                    $('#editWeightStandardBadge').hide();
                }
            } else {
                $('#editBeratPartRow').fadeOut();
                $('#editWeightCavContainer input').val('');
                $('#editWeightStandardBadge').hide();
            }

            updateDefectOptions();
            validateDimensions();
        });

        updateDefectOptions();
        $('#item_id').trigger('change');

        $('#editAddDefectBtn').click(function () {
            var rowCount = $('.defect-row').length;
            if (rowCount < 8) {
                var newRow = $('<div class="row no-gutters mb-2 defect-row align-items-center shadow-sm bg-white p-1 rounded">' +
                    '<div class="col-8 pr-1">' +
                    '<select class="form-control form-control-sm defect-select font-weight-bold" name="defect_types[]">' +
                    '<option value="">-- Pilih Defect --</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="col-3 pr-1">' +
                    '<input type="number" class="form-control form-control-sm defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty" min="1">' +
                    '</div>' +
                    '<div class="col-1 text-center">' +
                    '<button type="button" class="btn btn-danger btn-xs px-2 remove-defect-btn" title="Hapus"><i class="fas fa-minus"></i></button>' +
                    '</div>' +
                    '</div>');
                
                $('#editDefectContainer').append(newRow);
                $('#noDefectMsg').hide();
                updateDefectOptions();
            }
        });

        $(document).on('click', '.remove-defect-btn', function () {
            $(this).closest('.defect-row').fadeOut(200, function() {
                $(this).remove();
                calculateTotalNG();
                if ($('.defect-row').length === 0) {
                    $('#noDefectMsg').fadeIn();
                }
            });
        });

        function calculateTotalNG() {
            var total = 0;
            $('.defect-qty').each(function () {
                var qty = parseInt($(this).val()) || 0;
                total += qty;
            });
            $('#total_ng').val(total).trigger('input');
            
            if (total >= 0 || $('.defect-row').length > 0) {
                 $('#editAddDefectBtn').show();
            }
        }

        $(document).on('input', '.defect-qty', calculateTotalNG);

        $('#total_ng').on('input', function (e) {
            // Prevent recursive loop if triggered by calculateTotalNG programmatically
            if (e.originalEvent === undefined) return;
            
            var ng = parseInt($(this).val()) || 0;
            if (ng > 0) {
                $('#editAddDefectBtn').show();
                if ($('.defect-row').length === 0) {
                    $('#editAddDefectBtn').trigger('click');
                }
                
                if ($('.defect-row').length === 1) {
                    $('.defect-row:first').find('.defect-qty').val(ng);
                }
            } else if (ng === 0) {
                if ($('.defect-row').length === 1) {
                    $('.defect-row:first').find('.defect-qty').val('');
                }
            }
        });

        // ============================================================
        // EDIT WEIGHT CAVITY HELPERS
        // ============================================================
        const EDIT_MAX_WEIGHT_CAV = 12;

        function buildEditWeightCavRow(cavNum, value) {
            value = value || '';
            return `<div class="input-group input-group-sm mb-1 edit-weight-cav-row">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white" style="min-width:60px; justify-content:center; font-weight:600;">CAV ${cavNum}</span>
                </div>
                <input type="number" step="0.01" min="0" class="form-control text-center font-weight-bold"
                    name="part_weight[]" placeholder="0.00" value="${value}">
                <div class="input-group-append">
                    <span class="input-group-text bg-white text-muted">gr</span>
                </div>
            </div>`;
        }

        function updateEditWeightCavBadge() {
            var cnt = $('#editWeightCavContainer .edit-weight-cav-row').length;
            $('#editWeightCavCount').text(cnt + ' Cav');
        }
        updateEditWeightCavBadge();

        $('#editAddWeightCavBtn').click(function () {
            var cnt = $('#editWeightCavContainer .edit-weight-cav-row').length;
            if (cnt >= EDIT_MAX_WEIGHT_CAV) return;
            $('#editWeightCavContainer').append(buildEditWeightCavRow(cnt + 1));
            updateEditWeightCavBadge();
        });

        $('#editRemoveWeightCavBtn').click(function () {
            var rows = $('#editWeightCavContainer .edit-weight-cav-row');
            if (rows.length <= 1) return;
            rows.last().remove();
            updateEditWeightCavBadge();
        });
        })(jQuery); // Pass jQuery to the function
    })(); // Self-executing function
</script>
<style>
    .is-invalid {
        border-color: #dc3545 !important;
        background-color: #f8d7da !important;
    }
    .defect-row {
        transition: all 0.2s ease;
    }
    .defect-row:hover {
        transform: translateX(3px);
    }
    .border-dashed {
        border-style: dashed !important;
        border-width: 2px !important;
    }
</style>
