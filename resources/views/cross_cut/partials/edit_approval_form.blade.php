<form action="{{ route('admin.cross_cut.update_approval', $checksheet->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
        <div>
            <h6 class="m-0 font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Checksheet ID: {{ $checksheet->id }}</h6>
            <div class="small mt-1 text-dark">
                <strong>Part:</strong> {{ $checksheet->item->name ?? '-' }} | 
                <strong>No Lot:</strong> {{ $checksheet->position_remark_no_lot ?? '-' }}
            </div>
        </div>
        <div class="text-right small text-dark">
            <strong>Tgl Prod:</strong> <br>
            {{ $checksheet->production_datetime ? \Carbon\Carbon::parse($checksheet->production_datetime)->format('d M Y') : '-' }}
        </div>
    </div>

    <div class="row">
        <!-- Level 1: Karu QC -->
        <div class="col-md-4 form-group mb-3">
            <label class="small font-weight-bold text-gray-700" for="karu_qc">Karu QC</label>
            <select name="karu_qc" id="karu_qc" class="form-control form-control-sm">
                <option value="Pending" @if(is_null($checksheet->karu_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->karu_qc && $checksheet->karu_qc !== 'REJECTED') selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->karu_qc === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>

        <!-- Level 2: Kashift Plating -->
        <div class="col-md-4 form-group mb-3">
            <label class="small font-weight-bold text-gray-700" for="kashift_plating">Kashift Plating</label>
            <select name="kashift_plating" id="kashift_plating" class="form-control form-control-sm">
                <option value="Pending" @if(is_null($checksheet->kashift_plating)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->kashift_plating && $checksheet->kashift_plating !== 'REJECTED') selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->kashift_plating === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>

        <!-- Level 3: SPV Plating -->
        <div class="col-md-4 form-group mb-3">
            <label class="small font-weight-bold text-gray-700" for="supervisor_plating">SPV Plating</label>
            <select name="supervisor_plating" id="supervisor_plating" class="form-control form-control-sm">
                <option value="Pending" @if(is_null($checksheet->supervisor_plating)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->supervisor_plating && $checksheet->supervisor_plating !== 'REJECTED') selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->supervisor_plating === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>
    </div>

    <div class="row">
        <!-- Level 4: SPV Quality -->
        <div class="col-md-4 form-group mb-2">
            <label class="small font-weight-bold text-gray-700" for="supervisor_qc">SPV QC</label>
            <select name="supervisor_qc" id="supervisor_qc" class="form-control form-control-sm">
                <option value="Pending" @if(is_null($checksheet->supervisor_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED') selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->supervisor_qc === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>

        <!-- Level 5: Manager Plating -->
        <div class="col-md-4 form-group mb-2">
            <label class="small font-weight-bold text-gray-700" for="manager_plating">Manager Plating</label>
            <select name="manager_plating" id="manager_plating" class="form-control form-control-sm">
                <option value="Pending" @if(is_null($checksheet->manager_plating)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->manager_plating && $checksheet->manager_plating !== 'REJECTED') selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->manager_plating === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>

        <!-- Level 6: Manager QC (Final) -->
        <div class="col-md-4 form-group mb-2">
            <label class="small font-weight-bold text-gray-700" for="manager_qc">Manager QC (Final)</label>
            <select name="manager_qc" id="manager_qc" class="form-control form-control-sm">
                <option value="Pending" @if(is_null($checksheet->manager_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED') selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->manager_qc === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>
    </div>

    <!-- Modal Footer style aligned with calibration -->
    <div class="modal-footer bg-light p-2 mt-3 mx-n3 mb-n3 d-flex justify-content-end">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
            <i class="fas fa-save mr-1"></i> Simpan Status
        </button>
    </div>
</form>
