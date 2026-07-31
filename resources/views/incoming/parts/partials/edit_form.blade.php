<form action="{{ route('incoming.parts.update', $checksheet->id) }}" method="POST" id="editChecksheetForm">
    @csrf
    @method('PUT')

    @php
        $tglDatangVal = $checksheet->tanggal_datang ?? ($checksheet->arrival->tanggal_datang ?? null);
        $formattedTglDatang = $tglDatangVal ? \Carbon\Carbon::parse($tglDatangVal)->format('Y-m-d') : '';
        $shiftDatangVal = (string) ($checksheet->arrival->shift_datang ?? '1');
        $qtyDatangVal = $checksheet->arrival->qty_datang ?? ($checksheet->lot_qty ?? 0);
        $qtySisaVal = isset($checksheet->qty_balance_sisa) ? $checksheet->qty_balance_sisa : ($checksheet->arrival->qty_sisa ?? 0);
        $formattedTglCheck = $checksheet->date ? \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') : '';
        $shiftQCVal = (string) $checksheet->shift;
        $samplingQtyVal = $checksheet->sampling_qty ?? $checksheet->total_check;

        $selectedItem = $items->firstWhere('id', $checksheet->item_id);
        $itemDefectsList = [];
        if ($selectedItem) {
            $rawDefects = is_array($selectedItem->defects) ? $selectedItem->defects : json_decode($selectedItem->defects, true);
            if (is_array($rawDefects)) {
                foreach ($rawDefects as $df) {
                    $defName = is_string($df) ? $df : ($df['name'] ?? ($df['type'] ?? ''));
                    if ($defName) {
                        $itemDefectsList[] = $defName;
                    }
                }
            }
        }
        $itemDefectsList = array_values(array_unique($itemDefectsList));
    @endphp

    <!-- Section 1: Informasi Item Part -->
    <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem; letter-spacing: 0.5px;">
        1. INFORMASI ITEM PART
    </div>

    <div class="form-group mb-3">
        <label class="small font-weight-bold text-gray-700">Item Part <span class="text-danger">*</span></label>
        <select class="form-control form-control-sm border-0 shadow-sm select2" name="item_id" id="editItemIdSelect" required style="width: 100%;">
            @foreach($items as $item)
                @php
                    $itemDefects = is_array($item->defects) ? json_encode($item->defects) : ($item->defects ?: '[]');
                @endphp
                <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                    data-defects="{{ $itemDefects }}">
                    {{ $item->name }} ({{ $item->part_number ?? '-' }}) {{ $item->customer ? '- Customer: '.$item->customer : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Section 2: Data Kedatangan & Inspeksi QC -->
    <div class="font-weight-bold text-primary mb-3 pb-2 mt-4" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem; letter-spacing: 0.5px;">
        2. DATA KEDATANGAN &amp; INSPEKSI QC
    </div>

    <!-- Baris Kedatangan Supplier -->
    <div class="form-row mb-2">
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Tgl. Kedatangan Supplier <span class="text-danger">*</span></label>
            <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="tanggal_datang" value="{{ $formattedTglDatang }}" required>
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Shift Kedatangan Supplier <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm border-0 shadow-sm" name="shift_datang" required>
                <option value="1" {{ $shiftDatangVal === '1' ? 'selected' : '' }}>Shift 1</option>
                <option value="2" {{ $shiftDatangVal === '2' ? 'selected' : '' }}>Shift 2</option>
                <option value="3" {{ $shiftDatangVal === '3' ? 'selected' : '' }}>Shift 3</option>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Qty Kedatangan Awal (pcs) <span class="text-danger">*</span></label>
            <input type="number" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="qty_datang" value="{{ $qtyDatangVal }}" min="0" required>
        </div>
    </div>

    <!-- Baris Inspeksi QC -->
    <div class="form-row mb-2">
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Tanggal Check QC <span class="text-danger">*</span></label>
            <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="date" value="{{ $formattedTglCheck }}" required>
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Shift QC <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm border-0 shadow-sm" name="shift" required>
                <option value="1" {{ $shiftQCVal === '1' ? 'selected' : '' }}>Shift 1</option>
                <option value="2" {{ $shiftQCVal === '2' ? 'selected' : '' }}>Shift 2</option>
                <option value="3" {{ $shiftQCVal === '3' ? 'selected' : '' }}>Shift 3</option>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Total Check (pcs) <span class="text-danger">*</span></label>
            <input type="number" class="form-control form-control-sm border-0 shadow-sm font-weight-bold text-center" name="total_check" value="{{ $checksheet->total_check }}" required min="1">
        </div>
    </div>

    <div class="form-row mb-2">
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Qty Sampling (pcs)</label>
            <input type="number" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="sampling_qty" value="{{ $samplingQtyVal }}" min="0">
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Qty Balance Sisa (pcs)</label>
            <input type="number" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="qty_balance_sisa" value="{{ $qtySisaVal }}" min="0">
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Judgment / Result <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm border-0 shadow-sm font-weight-bold" name="judgment" id="editJudgmentSelect" required>
                <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
            </select>
        </div>
    </div>

    <!-- Section 3: Detail Cacat (Defect List) -->
    <div class="font-weight-bold text-primary mb-3 pb-2 mt-4" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem; letter-spacing: 0.5px;">
        3. DETAIL CACAT (DEFECT LIST NG)
    </div>

    <div class="form-group mb-3">
        <div id="editDefectContainer">
            @php
                $defects = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
            @endphp
            @forelse($defects ?? [] as $d)
                <div class="form-row mb-2 edit-defect-row align-items-center bg-white p-2 rounded shadow-sm border">
                    <div class="col-7">
                        <select class="form-control form-control-sm border-0 shadow-sm edit-defect-select font-weight-bold" name="defect_types[]">
                            <option value="">-- Pilih Defect --</option>
                            @foreach($itemDefectsList as $opt)
                                <option value="{{ $opt }}" {{ ($d['type'] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                            @if(!empty($d['type']) && !in_array($d['type'], $itemDefectsList))
                                <option value="{{ $d['type'] }}" selected>{{ $d['type'] }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm border-0 shadow-sm edit-defect-qty text-center font-weight-bold" name="defect_quantities[]" value="{{ $d['qty'] ?? '' }}" placeholder="Qty Pcs" min="1">
                    </div>
                    <div class="col-1 text-center action-col">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-defect-btn" title="Hapus Baris"><i class="fas fa-times-circle fa-lg"></i></button>
                    </div>
                </div>
            @empty
                <div class="form-row mb-2 edit-defect-row align-items-center bg-white p-2 rounded shadow-sm border">
                    <div class="col-7">
                        <select class="form-control form-control-sm border-0 shadow-sm edit-defect-select font-weight-bold" name="defect_types[]">
                            <option value="">-- Pilih Defect --</option>
                            @foreach($itemDefectsList as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm border-0 shadow-sm edit-defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty Pcs" min="1">
                    </div>
                    <div class="col-1 text-center action-col">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-defect-btn" title="Hapus Baris"><i class="fas fa-times-circle fa-lg"></i></button>
                    </div>
                </div>
            @endforelse
        </div>
        <button type="button" id="editAddDefectBtn" class="btn btn-outline-info btn-sm shadow-sm mt-1">
            <i class="fas fa-plus"></i> Tambah Baris Defect
        </button>
    </div>

    <!-- Section 4: Inisial & Remarks -->
    <div class="font-weight-bold text-primary mb-3 pb-2 mt-4" style="border-bottom: 2px solid #e2e8f0; font-size: 0.85rem; letter-spacing: 0.5px;">
        4. OPERATOR, CYCLE TIME &amp; REMARKS
    </div>

    <div class="form-row mb-3">
        <div class="form-group col-md-3 mb-0">
            <label class="small font-weight-bold text-gray-700">QC Initials <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold text-uppercase" name="operator_initials" value="{{ $checksheet->operator_initials }}" required>
        </div>
        <div class="form-group col-md-3 mb-0">
            <label class="small font-weight-bold text-gray-700">Cycle Time (s)</label>
            <input type="number" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold" name="cycle_time" value="{{ $checksheet->cycle_time }}" min="0" placeholder="0">
        </div>
        <div class="form-group col-md-6 mb-0">
            <label class="small font-weight-bold text-gray-700">Remarks</label>
            <textarea class="form-control form-control-sm border-0 shadow-sm" name="remarks" rows="2" placeholder="Catatan/Keterangan opsional...">{{ $checksheet->remarks }}</textarea>
        </div>
    </div>

    <div class="modal-footer px-0 pb-0 pt-3 border-top d-flex justify-content-end" style="gap: 8px; background: transparent;">
        <button type="button" class="btn btn-light border px-4 font-weight-bold" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold">Simpan Perubahan</button>
    </div>
</form>

<script src="{{ asset('js/checksheet/incoming-edit.js') }}"></script>
