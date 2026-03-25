<form id="editChecksheetForm" class="ajax-form"
    action="{{ route('first_piece_approval.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    {{-- Preserve all filter and pagination parameters --}}
    @foreach(request()->all() as $key => $value)
        @if(!in_array($key, ['_token', '_method', 'id']))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="row">
        <div class="col-md-6 border-right">
            <div class="form-group mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="small font-weight-bold mb-0">Item Part <span class="text-danger">*</span></label>
                    <a href="#" id="view-item-pdf" class="btn btn-xs btn-outline-info d-none" target="_blank">
                        <i class="fas fa-file-pdf"></i> View PDF
                    </a>
                </div>
                <select name="item_id" id="item_id" class="form-control form-control-sm" required>
                    <option value="" disabled style="font-weight: bold; color: #6c757d;">Pilih Item Part</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                            data-part-number="{{ $item->part_number }}"
                            data-customer="{{ $item->customer }}"
                            data-weight-standard="{{ $item->weight_standard }}"
                            data-defects="{{ json_encode($item->defects) }}">
                            {{ $item->name }} ({{ $item->customer }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date" class="form-control form-control-sm"
                            value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Shift <span class="text-danger">*</span></label>
                        <select name="shift" id="shift" class="form-control form-control-sm" required>
                            <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">No Mesin <span class="text-danger">*</span></label>
                        <select name="code_machine" id="code_machine" class="form-control form-control-sm" required>
                            <option value="">Pilih Mesin</option>
                            @php
                                $plantCode = strtolower($checksheet->plant->code ?? 'karawang');
                                $jakartaMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
                                $karawangMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19];
                                $machineNumbers = ($plantCode === 'jakarta') ? $jakartaMachineNumbers : $karawangMachineNumbers;
                            @endphp
                            @foreach ($machineNumbers as $num)
                                <option value="{{ $num }}" {{ $checksheet->code_machine == $num ? 'selected' : '' }}>Mesin {{ $num }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Inisial Operator</label>
                        <input type="text" name="operator_initials" id="operator_initials" class="form-control form-control-sm"
                            value="{{ $checksheet->operator_initials }}" placeholder="Inisial...">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total Qty <span class="text-danger">*</span></label>
                        <input type="number" name="total_qty" id="total_qty" class="form-control form-control-sm"
                            value="{{ $checksheet->total_qty }}" min="0" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Sampling Qty <span class="text-danger">*</span></label>
                        <input type="number" name="sampling_qty" id="sampling_qty" class="form-control form-control-sm"
                            value="{{ $checksheet->sampling_qty }}" min="0" required>
                    </div>
                </div>
            </div>

            <div class="row" id="editBeratPartContainer" style="display: none;">
                <div class="col-12">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Berat Part (gr.)</label>
                        {{-- Cavity controls --}}
                        <div class="d-flex align-items-center mb-1" style="gap:4px;">
                            <button type="button" id="editAddWeightCavBtn"
                                class="btn btn-primary btn-xs" title="Tambah Cavity">
                                <i class="fas fa-plus"></i> Cav
                            </button>
                            <button type="button" id="editRemoveWeightCavBtn"
                                class="btn btn-outline-danger btn-xs" title="Hapus Cavity Terakhir">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span id="editWeightCavCount" class="badge badge-secondary ml-1">1 Cav</span>
                        </div>
                        {{-- Per-cavity rows --}}
                        <div id="editWeightCavContainer">
                            @php
                                $existingWeights = is_array($checksheet->part_weight)
                                    ? $checksheet->part_weight
                                    : (is_string($checksheet->part_weight) && str_starts_with($checksheet->part_weight, '[') ? json_decode($checksheet->part_weight, true) : ($checksheet->part_weight ? [$checksheet->part_weight] : [null]));
                            @endphp
                            @foreach($existingWeights as $cavIdx => $wVal)
                                <div class="input-group input-group-sm mb-1 edit-weight-cav-row">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="min-width:60px; justify-content:center; font-weight:600;">CAV {{ $cavIdx + 1 }}</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" class="form-control text-center"
                                        name="part_weight[]" placeholder="0.00" value="{{ $wVal }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text text-muted small">gr</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-1">
                            <span class="badge badge-secondary" id="editWeightStandardBadge"
                                style="display: none;">Std: <span
                                    id="editWeightStandardDisplay">-</span> gr.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @if(auth()->user()->role !== 'inspector')
                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Jam (Before)</label>
                            <input type="time" name="jam_before" id="jam_before" class="form-control form-control-sm"
                                value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Jam (After)</label>
                            <input type="time" name="jam_after" id="jam_after" class="form-control form-control-sm"
                                value="{{ $checksheet->created_at->format('H:i') }}">
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                     <div class="form-group mb-2">
                        <label class="small font-weight-bold">Jenis (OK/NG) & Detail NG</label>
                        <div id="editDefectContainer">
                            {{-- Rows will be populated by JS or Server-side loop --}}
                            @php
                                $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true) ?? [];
                            @endphp
                            
                            @if(count($defects) > 0)
                                @foreach($defects as $index => $defect)
                                    <div class="input-group mb-2 defect-row">
                                        <select class="form-control form-control-sm defect-select" name="defect_types[]">
                                            <option value="">-- Pilih Defect --</option>
                                            <option value="{{ $defect['type'] }}" selected>{{ $defect['type'] }}</option>
                                        </select>
                                        <input type="number" class="form-control form-control-sm defect-qty" name="defect_quantities[]" 
                                            value="{{ $defect['qty'] }}" min="1" placeholder="Qty" style="max-width: 80px;">
                                        <div class="input-group-append">
                                            <button class="btn btn-danger btn-xs remove-defect-btn" type="button"><i class="fas fa-minus"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                                {{-- Empty initial row for structure, hidden or shown based on needs --}}
                                <div class="input-group mb-2 defect-row" style="display:none"></div>
                            @endif
                        </div>
                        <button type="button" id="editAddDefectBtn" class="btn btn-info btn-xs mt-1 {{ count($defects) > 0 || $checksheet->total_ng > 0 ? '' : 'd-none' }}">
                            <i class="fas fa-plus"></i> Tambah Jenis
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total OK (pcs) <span class="text-danger">*</span></label>
                        <input type="number" name="total_ok" id="total_ok" class="form-control form-control-sm"
                            value="{{ $checksheet->total_ok }}" min="0" required readonly>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Total NG (pcs) <span class="text-danger">*</span></label>
                        <input type="number" name="total_ng" id="total_ng" class="form-control form-control-sm"
                            value="{{ $checksheet->total_ng }}" min="0" required readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6 text-center">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Judgment Final <span class="text-danger">*</span></label>
                        @php
                            $judgmentColor = ($checksheet->judgment == 'OK') ? '#28a745' : (($checksheet->judgment == 'NG') ? '#dc3545' : 'transparent');
                        @endphp
                        <div id="judgmentBadge" class="mb-2 p-3 font-weight-bold h4 rounded {{ $checksheet->judgment ? '' : 'd-none' }} shadow-sm {{ $checksheet->judgment == 'OK' ? 'text-success' : 'text-danger' }}"
                            style="border: 2px solid {{ $judgmentColor }}; background-color: #fff;">
                            {{ $checksheet->judgment ?? '-' }}
                        </div>
                        <div id="aql_info" class="small mt-1 font-weight-bold text-center" style="display: none;">
                            <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                            <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                        </div>
                        <select name="judgment" id="judgmentSelect" class="form-control form-control-sm font-weight-bold d-none {{ $checksheet->judgment == 'OK' ? 'text-success' : 'text-danger' }}" required>
                            <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                        </select>
                    </div>
                </div>
                <div class="col-6 {{ $checksheet->judgment == 'NG' ? '' : 'd-none' }}" id="nextProsesContainer">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold text-danger">Next Proses <span class="text-danger">*</span></label>
                        <select class="form-control" id="next_proses" name="next_proses">
                            <option value="">-- Pilih Next Proses --</option>
                            <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING</option>
                            <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR</option>
                            <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>FINISHING</option>
                            <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR</option>
                            <option value="SORTIR + FINISHING" {{ $checksheet->next_proses == 'SORTIR + FINISHING' ? 'selected' : '' }}>SORTIR + FINISHING</option>
                            <option value="FINISHING + PASANG SUB PART" {{ $checksheet->next_proses == 'FINISHING + PASANG SUB PART' ? 'selected' : '' }}>FINISHING + PASANG SUB PART</option>
                            <option value="FINISHING + PACKING" {{ $checksheet->next_proses == 'FINISHING + PACKING' ? 'selected' : '' }}>FINISHING + PACKING</option>
                            <option value="REBUS + FINISHING + PACKING" {{ $checksheet->next_proses == 'REBUS + FINISHING + PACKING' ? 'selected' : '' }}>REBUS + FINISHING + PACKING</option>
                            <option value="SORTIR + CRUSHING" {{ $checksheet->next_proses == 'SORTIR + CRUSHING' ? 'selected' : '' }}>SORTIR + CRUSHING</option>
                            @if($checksheet->next_proses && !in_array($checksheet->next_proses, ['CRUSHING', 'SORTIR', 'FINISHING', 'REPAIR', 'SORTIR + FINISHING', 'FINISHING + PASANG SUB PART', 'FINISHING + PACKING', 'REBUS + FINISHING + PACKING', 'SORTIR + CRUSHING']))
                                <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="small font-weight-bold">Keterangan / Remarks</label>
                <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="3" placeholder="Tambahkan keterangan...">{{ $checksheet->remarks }}</textarea>
            </div>
        </div>
    </div>

    <!-- Check Dimensi Styled exactly like Jadwal Kalibrasi in screenshot -->
    <div class="form-group mb-3 mt-3">
        <label class="small font-weight-bold">Check Dimensi (mm) <span class="text-danger">*</span></label>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center" style="background-color: #4e73df !important;">
                <h6 class="m-0 font-weight-bold small text-center flex-grow-1">Tampilan Titik Pengukuran</h6>
                <button type="button" class="btn btn-success btn-xs" id="editAddCavityBtn" title="Tambah Cavity" style="background-color: #1cc88a !important; border:none; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0">
                @php
                    $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true) ?? [];
                    $maxCavityFound = 5;
                    $maxPointFound = 5;
                    foreach ($dimensions as $cav => $pts) {
                        if (is_numeric($cav)) $maxCavityFound = max($maxCavityFound, (int) $cav);
                        if (is_array($pts)) {
                            foreach (array_keys($pts) as $pt) {
                                if (is_numeric($pt)) $maxPointFound = max($maxPointFound, (int) $pt);
                            }
                        }
                    }
                @endphp
                <div class="table-responsive" style="max-height: 350px;">
                    <table class="table table-sm table-bordered mb-0" id="editDimensionTable">
                        <thead class="text-center bg-white small">
                            <tr id="editDimensionHeadRow">
                                <th style="min-width: 80px; position: sticky; left: 0; z-index: 2; background: white;">Cavity</th>
                                @for ($j = 1; $j <= $maxPointFound; $j++)
                                    <th class="point-header">P{{ $j }}</th>
                                @endfor
                                <th style="width: 40px;"><button type="button" class="btn btn-primary btn-xs" id="editAddPointBtn"><i class="fas fa-plus"></i></button></th>
                            </tr>
                        </thead>
                        <tbody id="editDimensionBody">
                            @for ($i = 1; $i <= $maxCavityFound; $i++)
                                <tr class="edit-cavity-row" data-cavity="{{ $i }}">
                                    <td class="text-center font-weight-bold bg-light small" style="position: sticky; left: 0; z-index: 1;">Cav {{ $i }}</td>
                                    @for ($j = 1; $j <= $maxPointFound; $j++)
                                        <td class="p-0">
                                            <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center"
                                                style="min-width: 50px; font-size: 0.75rem; height: 35px;" name="dimensions[{{ $i }}][{{ $j }}]"
                                                value="{{ $dimensions[$i][$j] ?? '' }}" placeholder="-">
                                        </td>
                                    @endfor
                                    <td class="text-center bg-light">
                                        <button type="button" class="btn btn-link text-danger p-0" disabled><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 pb-2 text-right">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm" id="btnSubmit">
            <i class="fas fa-save mr-1"></i> Update
        </button>
    </div>
</form>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div class="text-center">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="text-white mt-3 font-weight-bold">Menyimpan data...</div>
    </div>
</div>

@php
// We remove @push because this is loaded via AJAX and @push doesn't work in AJAX responses.
// The script will execute normally when inserted into the DOM.
@endphp
<script id="edit-fpa-data" type="application/json" 
    data-cavities="{{ $maxCavityFound }}" 
    data-points="{{ $maxPointFound }}"
    data-pdf-route-pattern="{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}">
    @json($partDimensionStandards)
</script>
<script>
    (function() {
        if (typeof jQuery === 'undefined') {
            setTimeout(arguments.callee, 50);
            return;
        }
        
        const dataEl = document.getElementById('edit-fpa-data');
        if (!dataEl) return;
        
        const partDimensionStandards = JSON.parse(dataEl.textContent);
        const currentCavities = parseInt(dataEl.getAttribute('data-cavities') || 0);
        const currentPoints = parseInt(dataEl.getAttribute('data-points') || 0);
        
        window.initFpaEdit({
            partDimensionStandards: partDimensionStandards,
            pdfRoutePattern: dataEl.getAttribute('data-pdf-route-pattern'),
            currentCavities: currentCavities,
            currentPoints: currentPoints,
            maxCavities: 30,
            maxPoints: 30
        });
    })();
</script>
<style>
    .is-invalid {
        border-color: #dc3545 !important;
        background-color: #f8d7da !important;
    }

    .btn-xs {
        padding: 1px 5px;
        font-size: 12px;
        line-height: 1.5;
        border-radius: 3px;
    }
</style>
