<form action="{{ route('incoming.parts.update', $checksheet->id) }}" method="POST" id="editChecksheetForm">
    @csrf
    @method('PUT')

    @php
        $tglDatangVal = $checksheet->tanggal_datang ?? ($checksheet->arrival->tanggal_datang ?? null);
        $formattedTglDatang = $tglDatangVal ? \Carbon\Carbon::parse($tglDatangVal)->format('Y-m-d') : '';
        $formattedTglCheck = $checksheet->date ? \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d') : '';
        $shiftQCVal = (string) $checksheet->shift;
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

    <div class="form-row mb-2">
        <div class="form-group col-md-4">
            <label class="small font-weight-bold text-gray-700">Tgl. Kedatangan Supplier</label>
            <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="tanggal_datang" value="{{ $formattedTglDatang }}">
        </div>
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
    </div>

    <div class="form-row mb-2">
        <div class="form-group col-md-6">
            <label class="small font-weight-bold text-gray-700">Total Check QC (pcs) <span class="text-danger">*</span></label>
            <input type="number" class="form-control form-control-sm border-0 shadow-sm font-weight-bold text-center" name="total_check" value="{{ $checksheet->total_check }}" required min="1">
        </div>
        <div class="form-group col-md-6">
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
                            <option value="{{ $d['type'] ?? '' }}">{{ $d['type'] ?? '-- Pilih Defect --' }}</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm border-0 shadow-sm edit-defect-qty text-center font-weight-bold" name="defect_quantities[]" value="{{ $d['qty'] ?? '' }}" placeholder="Qty Pcs" min="1">
                    </div>
                    <div class="col-1 text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-defect-btn" title="Hapus Baris"><i class="fas fa-times-circle fa-lg"></i></button>
                    </div>
                </div>
            @empty
                <div class="form-row mb-2 edit-defect-row align-items-center bg-white p-2 rounded shadow-sm border">
                    <div class="col-7">
                        <select class="form-control form-control-sm border-0 shadow-sm edit-defect-select font-weight-bold" name="defect_types[]">
                            <option value="">-- Pilih Defect --</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm border-0 shadow-sm edit-defect-qty text-center font-weight-bold" name="defect_quantities[]" placeholder="Qty Pcs" min="1">
                    </div>
                    <div class="col-1 text-center">
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
        4. OPERATOR &amp; REMARKS
    </div>

    <div class="form-row mb-3">
        <div class="form-group col-md-4 mb-0">
            <label class="small font-weight-bold text-gray-700">QC Initials <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm border-0 shadow-sm text-center font-weight-bold text-uppercase" name="operator_initials" value="{{ $checksheet->operator_initials }}" required>
        </div>
        <div class="form-group col-md-8 mb-0">
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
