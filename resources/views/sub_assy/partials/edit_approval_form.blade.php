<div class="alert alert-info">
    <h6 class="align-middle m-0 font-weight-bold">Checksheet ID: {{ $checksheet->id }}</h6>
</div>

<form action="{{ route('admin.checksheets.update_approval', $checksheet->id) }}" method="POST" class="ajax-form">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <div class="col-md-12">
            <p class="mb-1"><strong>Tanggal:</strong> {{ $checksheet->date }}</p>
            <p class="mb-1"><strong>Barang:</strong> {{ $checksheet->item->name ?? '-' }}</p>
            <p class="mb-1"><strong>Part No:</strong> {{ $checksheet->item->part_number ?? '-' }}</p>
        </div>
    </div>

    <hr>

    <div class="row">
        <!-- Kashift QC -->
        <div class="form-group col-md-3">
            <label for="kashift_qc">Status <x-approval-label level="kashift" /></label>
            <select name="kashift_qc" id="kashift_qc" class="form-control">
                <option value="Pending" @if(is_null($checksheet->kashift_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->kashift_qc && $checksheet->kashift_qc !== 'REJECTED') selected
                @endif>Approved</option>
                <option value="Rejected" @if($checksheet->kashift_qc === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>

        <!-- Supervisor QC -->
        <div class="form-group col-md-3">
            <label for="supervisor_qc">Status Supervisor QC</label>
            <select name="supervisor_qc" id="supervisor_qc" class="form-control">
                <option value="Pending" @if(is_null($checksheet->supervisor_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED')
                selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->supervisor_qc === 'REJECTED') selected @endif>Rejected</option>
            </select>
        </div>

        <!-- Asst. Manager QC -->
        <div class="form-group col-md-3">
            <label for="asst_manager_qc">Status Asst. Manager QC</label>
            <select name="asst_manager_qc" id="asst_manager_qc" class="form-control">
                <option value="Pending" @if(is_null($checksheet->asst_manager_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->asst_manager_qc && $checksheet->asst_manager_qc !== 'REJECTED')
                selected @endif>Approved</option>
                <option value="Rejected" @if($checksheet->asst_manager_qc === 'REJECTED') selected @endif>Rejected
                </option>
            </select>
        </div>

        <!-- Manager QC -->
        <div class="form-group col-md-3">
            <label for="manager_qc">Status Manager QC</label>
            <select name="manager_qc" id="manager_qc" class="form-control">
                <option value="Pending" @if(is_null($checksheet->manager_qc)) selected @endif>Pending</option>
                <option value="Approved" @if($checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED') selected
                @endif>Approved</option>
                <option value="Rejected" @if($checksheet->manager_qc === 'REJECTED') selected @endif>Rejected</option>
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
