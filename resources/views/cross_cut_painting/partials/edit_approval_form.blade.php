<div class="alert alert-info">
    <h6 class="align-middle m-0 font-weight-bold">Checksheet ID: {{ $checksheet->id }}</h6>
</div>

<form action="{{ route('admin.cross_cut_painting.update_approval', array_merge(['id' => $checksheet->id], request()->query())) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <div class="col-md-12">
            <p class="mb-1"><strong>Tanggal Produksi:</strong>
                {{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('Y-m-d') }}</p>
            <p class="mb-1"><strong>Barang:</strong> {{ $checksheet->item->name ?? '-' }}</p>
            <p class="mb-1"><strong>Part No:</strong> {{ $checksheet->item->part_number ?? '-' }}</p>
        </div>
    </div>

    <hr>

    <div class="row">
        <!-- Level 1: Karu QC -->
        <div class="form-group col-md-3">
            <label for="karu_qc">Status Kepala Regu</label>
            <select name="karu_qc" id="karu_qc" class="form-control">
                <option value="Pending" @if(is_null($checksheet->karu_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->karu_qc && $checksheet->karu_qc !== 'REJECTED') selected
                @endif>Approved</option>
                <option value="Rejected" @if($checksheet->karu_qc === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>

        <!-- Level 2: Kashift Painting -->
        <div class="form-group col-md-3">
            <label for="kashift_plating">Status Kashift Painting</label>
            <select name="kashift_plating" id="kashift_plating" class="form-control">
                <option value="Pending" @if(is_null($checksheet->kashift_plating)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->kashift_plating && $checksheet->kashift_plating !== 'REJECTED')
                selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->kashift_plating === 'REJECTED') selected @endif>Rejected
                </option>
            </select>
        </div>

        <!-- Level 3: SPV Painting -->
        <div class="form-group col-md-3">
            <label for="supervisor_plating">Status SPV Painting</label>
            <select name="supervisor_plating" id="supervisor_plating" class="form-control">
                <option value="Pending" @if(is_null($checksheet->supervisor_plating)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->supervisor_plating && $checksheet->supervisor_plating !== 'REJECTED') selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->supervisor_plating === 'REJECTED') selected @endif>Rejected
                </option>
            </select>
        </div>

        <!-- Level 3.5: Asst Manager Painting -->
        <div class="form-group col-md-3">
            <label for="asst_manager_plating">Status Asst Mgr Painting</label>
            <select name="asst_manager_plating" id="asst_manager_plating" class="form-control">
                <option value="Pending" @if(is_null($checksheet->asst_manager_plating)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->asst_manager_plating && $checksheet->asst_manager_plating !== 'REJECTED') selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->asst_manager_plating === 'REJECTED') selected @endif>Rejected
                </option>
            </select>
        </div>
    </div>

    <div class="row">
        <!-- Level 4: SPV Quality -->
        <div class="form-group col-md-6">
            <label for="supervisor_qc">Status SPV QC</label>
            <select name="supervisor_qc" id="supervisor_qc" class="form-control">
                <option value="Pending" @if(is_null($checksheet->supervisor_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED')
                selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->supervisor_qc === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>

        <!-- Level 5: Asst Manager QC -->
        <div class="form-group col-md-6">
            <label for="asst_manager_qc">Status Asst Manager QC</label>
            <select name="asst_manager_qc" id="asst_manager_qc" class="form-control">
                <option value="Pending" @if(is_null($checksheet->asst_manager_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->asst_manager_qc && $checksheet->asst_manager_qc !== 'REJECTED') selected
                @endif>Approved</option>
                <option value="Rejected" @if($checksheet->asst_manager_qc === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>
    </div>

    <div class="mt-4 text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Batal
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Perubahan
        </button>
    </div>
</form>
