<form id="editChecksheetForm" class="ajax-form"
    action="{{ route('incoming.sub_parts.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    @php
        $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true) ?? [];
    @endphp
    {{-- Preserve filter parameters --}}
    @foreach(request()->all() as $key => $value)
        @if(!in_array($key, ['_token', '_method', 'id']))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="row">
        <!-- 1. Kolom Kiri: Informasi Identitas & Kedatangan -->
        <div class="col-md-6 mb-3">
            <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                <i class="fas fa-info-circle mr-1"></i> DATA IDENTITAS & KEDATANGAN
            </div>
            
            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Sub-Part Name <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select name="item_id" id="item_id" class="form-control form-control-sm border-0 shadow-sm select2-standard" required>
                        <option value="" disabled>Pilih Sub-Part</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                                data-defects="{{ json_encode($item->defects) }}">
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Tanggal Check <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="date" name="date" class="form-control form-control-sm border-0 shadow-sm"
                        value="{{ \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Tanggal Datang <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="date" name="tanggal_datang" class="form-control form-control-sm border-0 shadow-sm"
                        value="{{ $checksheet->tanggal_datang ? \Carbon\Carbon::parse($checksheet->tanggal_datang)->format('Y-m-d') : '' }}" required>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Lot / Batch # <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="text" name="lot_batch_number" class="form-control form-control-sm border-0 shadow-sm font-weight-bold"
                        value="{{ $checksheet->lot_batch_number }}" required>
                </div>
            </div>

            <div class="form-group row align-items-center mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700">Inisial QC</label>
                <div class="col-sm-8">
                    <input type="text" name="operator_initials" class="form-control form-control-sm border-0 shadow-sm text-uppercase bg-light font-weight-bold text-center"
                        value="{{ $checksheet->operator_initials }}" placeholder="Inisial QC...">
                </div>
            </div>

            <div class="form-group row align-items-start mb-2">
                <label class="col-sm-4 col-form-label small font-weight-bold text-gray-700 pt-2">Remarks / Catatan</label>
                <div class="col-sm-8">
                    <textarea name="remarks" class="form-control form-control-sm border-0 shadow-sm" rows="3" placeholder="Catatan tambahan...">{{ $checksheet->remarks }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. Kolom Kanan: Hasil Pemeriksaan & Kualitas -->
        <div class="col-md-6 mb-3">
            <div class="font-weight-bold text-primary mb-3 pb-2 d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                <span><i class="fas fa-clipboard-check mr-1"></i> HASIL PEMERIKSAAN & KUALITAS</span>
            </div>

            <div class="row mb-3 pb-3 border-bottom">
                <div class="col-6">
                    <label class="small font-weight-bold text-gray-700">Quantity (Pcs) <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-light"
                        value="{{ $checksheet->quantity }}" min="0" required>
                </div>
                <div class="col-6">
                    <label class="small font-weight-bold text-gray-700">Sampling (Pcs) <span class="text-danger">*</span></label>
                    <input type="number" name="sampling_size_pcs" class="form-control form-control-sm border-0 shadow-sm font-weight-bold bg-white"
                        value="{{ $checksheet->sampling_size_pcs }}" min="0" required>
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
                    @forelse($defects as $index => $defect)
                        <div class="row no-gutters mb-2 edit-defect-row align-items-center shadow-sm bg-white p-1 rounded">
                            <div class="col-8 pr-1">
                                <select class="form-control form-control-sm edit-defect-select font-weight-bold" name="defect_types[]">
                                    <option value="">-- Pilih Defect --</option>
                                    <option value="{{ $defect['type'] ?? '' }}" selected>{{ $defect['type'] ?? '' }}</option>
                                </select>
                            </div>
                            <div class="col-3 pr-1">
                                <input type="number" class="form-control form-control-sm edit-defect-qty text-center font-weight-bold" 
                                    name="defect_quantities[]" value="{{ $defect['qty'] ?? 1 }}" min="1">
                            </div>
                            <div class="col-1 text-center">
                                <button type="button" class="btn btn-danger btn-xs px-2 remove-defect-btn" title="Hapus"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                    @empty
                        <div class="row no-gutters mb-2 edit-defect-row align-items-center shadow-sm bg-white p-1 rounded">
                            <div class="col-8 pr-1">
                                <select class="form-control form-control-sm edit-defect-select font-weight-bold" name="defect_types[]">
                                    <option value="">-- Pilih Defect --</option>
                                </select>
                            </div>
                            <div class="col-3 pr-1">
                                <input type="number" class="form-control form-control-sm edit-defect-qty text-center font-weight-bold" 
                                    name="defect_quantities[]" placeholder="Qty" min="1">
                            </div>
                            <div class="col-1 text-center">
                                <button type="button" class="btn btn-danger btn-xs px-2 remove-defect-btn" title="Hapus"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="row align-items-end mb-3">
                <div class="col-12">
                    <label class="small font-weight-bold text-uppercase">Judgment <span class="text-danger">*</span></label>
                    <select name="judgment" id="judgmentSelect" class="form-control form-control-sm font-weight-bold shadow-sm" required>
                        <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Footer Row: Check Dimensi (Full Width) -->
    <div class="font-weight-bold text-primary mb-3 pb-2 d-flex justify-content-between align-items-center mt-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
        <span class="font-weight-bold"><i class="fas fa-ruler-combined mr-2"></i>PEMERIKSAAN DIMENSI (MM)</span>
        <div class="btn-group shadow-sm">
            <button type="button" class="btn btn-info btn-xs px-3" id="editAddPointBtn" title="Tambah Point" style="font-size: 0.75rem;">
                <i class="fas fa-plus mr-1"></i> Point
            </button>
            <button type="button" class="btn btn-outline-danger btn-xs px-2" id="editDeletePointBtn" title="Hapus Point Terakhir" style="font-size: 0.75rem;">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>

    @php
        $rawDims = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true) ?? [];
        $flatDims = [];
        if (!empty($rawDims)) {
            if (isset($rawDims[1]) && is_array($rawDims[1])) {
                $flatDims = $rawDims[1];
            } elseif (isset($rawDims["1"]) && is_array($rawDims["1"])) {
                $flatDims = $rawDims["1"];
            } else {
                $flatDims = $rawDims;
            }
        }
        $itemStd = optional($checksheet->item)->dimension_standards;
        $stds = is_array($itemStd) ? $itemStd : (json_decode($itemStd, true) ?? []);
        $maxPointFound = count($stds) > 0 ? count($stds) : 1;
        foreach ($flatDims as $pt => $v) {
            if (is_numeric($pt)) {
                $maxPointFound = max($maxPointFound, (int)$pt);
            }
        }
    @endphp

    <div class="table-responsive bg-white rounded shadow-sm border mb-4" style="max-height: 300px; overflow-y: auto;">
        <table class="table table-sm table-bordered table-hover mb-0" id="editDimensionTable">
            <thead class="bg-light text-center small font-weight-bold">
                <tr id="editDimensionHeadRow">
                    <th style="width: 100px; background-color: #f8f9fc !important; color: #475569 !important;">Point</th>
                    <th style="background-color: #f8f9fc !important; color: #475569 !important;">Hasil Ukur (mm)</th>
                </tr>
            </thead>
            <tbody id="editDimensionBody">
                @for ($j = 1; $j <= $maxPointFound; $j++)
                    @php
                        $val = $flatDims[$j] ?? ($flatDims["$j"] ?? '');
                    @endphp
                    <tr class="edit-point-row" data-point="{{ $j }}">
                        <td class="text-center font-weight-bold bg-light align-middle" style="font-size: 0.8rem; background: #f8f9fc !important;">P{{ $j }}</td>
                        <td class="p-0">
                            <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold"
                                style="font-size: 0.85rem; height: 38px; border-radius: 0;" name="dimensions[{{ $j }}]"
                                value="{{ $val }}" placeholder="P{{ $j }}">
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <!-- Modal Footer -->
    <div class="bg-white border-top py-3 px-4 d-flex justify-content-end align-items-center" style="margin: 1.5rem -1.5rem -1.5rem -1.5rem; border-radius: 0 0 12px 12px;">
        <button type="button" class="btn btn-light border px-4 font-weight-bold mr-2" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSubmit"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
    </div>
</form>

<script src="{{ asset('js/checksheet/incoming-edit.js') }}"></script>
<script>
    $(document).ready(function() {
        let currentEditPoints = {{ $maxPointFound }};
        const maxEditPoints = 50;

        $('#editAddPointBtn').on('click', function() {
            if (currentEditPoints < maxEditPoints) {
                currentEditPoints++;
                let newRow = `<tr class="edit-point-row" data-point="${currentEditPoints}">
                    <td class="text-center font-weight-bold bg-light align-middle" style="font-size: 0.8rem; background: #f8f9fc !important;">P${currentEditPoints}</td>
                    <td class="p-0">
                        <input type="text" class="form-control form-control-sm edit-dimension-input border-0 text-center font-weight-bold"
                            style="font-size: 0.85rem; height: 38px; border-radius: 0;" name="dimensions[${currentEditPoints}]"
                            placeholder="P${currentEditPoints}">
                    </td>
                </tr>`;
                $('#editDimensionBody').append(newRow);
            } else {
                Swal.fire('Limit!', 'Maksimum 50 points.', 'warning');
            }
        });

        $('#editDeletePointBtn').on('click', function() {
            if (currentEditPoints > 1) {
                $('#editDimensionBody tr.edit-point-row:last').remove();
                currentEditPoints--;
            }
        });
    });
</script>
