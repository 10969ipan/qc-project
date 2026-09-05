<div class="alert alert-info py-2 shadow-sm border-0 d-flex justify-content-between align-items-center mb-3"
    style="background-color: #e3f2fd; color: #0d47a1;">
    <h6 class="m-0 font-weight-bold small"><i class="fas fa-barcode mr-1"></i> ID: {{ $checksheet->id }}</h6>
    <div class="small">
        <span class="mr-3"><strong>Tgl:</strong> {{ \Carbon\Carbon::parse($checksheet->date)->format('d/m/Y') }}</span>
        <span><strong>Part:</strong> {{ $checksheet->item->name ?? '-' }}
            ({{ $checksheet->item->part_number ?? '-' }})</span>
    </div>
</div>

<form id="statusApprovalForm" class="ajax-form"
    action="{{ route('admin.in_process.update_approval', $checksheet->id) }}" method="POST">
    <div id="modal-errors" class="mb-3" style="display: none;"></div>
    @csrf
    @method('PUT')
    {{-- Preserve all filter and pagination parameters for redirect --}}
    @foreach(request()->except(['_token', '_method', 'id', 'kashift_qc', 'supervisor_qc', 'asst_manager_qc', 'manager_qc']) as $key => $value)
        @if(!is_array($value) && $value !== null && $value !== '')
            <input type="hidden" name="redirect_params[{{ $key }}]" value="{{ $value }}">
        @endif
    @endforeach

    <div class="row">
        <div class="col-md-6">
            <!-- Kashift QC -->
            <div class="form-group mb-2">
                <label class="small font-weight-bold" for="kashift_qc">Status Kashift QC</label>
                <select name="kashift_qc" id="kashift_qc" class="form-control form-control-sm">
                    <option value="Pending" @if(is_null($checksheet->kashift_qc)) selected @endif>Pending</option>
                    <option value="Approved" @if($checksheet->kashift_qc && $checksheet->kashift_qc !== 'REJECTED')
                    selected @endif>Approved</option>
                    <option value="Rejected" @if($checksheet->kashift_qc === 'REJECTED') selected @endif>Rejected</option>
                </select>
            </div>

            <!-- Supervisor QC -->
            <div class="form-group mb-2">
                <label class="small font-weight-bold" for="supervisor_qc">Status Supervisor QC</label>
                <select name="supervisor_qc" id="supervisor_qc" class="form-control form-control-sm">
                    <option value="Pending" @if(is_null($checksheet->supervisor_qc)) selected @endif>Pending</option>
                    <option value="Approved" @if($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED')
                    selected @endif>Approved</option>
                    <option value="Rejected" @if($checksheet->supervisor_qc === 'REJECTED') selected @endif>Rejected
                    </option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Asst. Manager QC -->
            <div class="form-group mb-2">
                <label class="small font-weight-bold" for="asst_manager_qc">Status Asst. Manager QC</label>
                <select name="asst_manager_qc" id="asst_manager_qc" class="form-control form-control-sm">
                    <option value="Pending" @if(is_null($checksheet->asst_manager_qc)) selected @endif>Pending</option>
                    <option value="Approved" @if($checksheet->asst_manager_qc && $checksheet->asst_manager_qc !== 'REJECTED') selected @endif>Approved</option>
                    <option value="Rejected" @if($checksheet->asst_manager_qc === 'REJECTED') selected @endif>Rejected
                    </option>
                </select>
            </div>

            <!-- Manager QC -->
            <div class="form-group mb-2">
                <label class="small font-weight-bold" for="manager_qc">Status Manager QC</label>
                <select name="manager_qc" id="manager_qc" class="form-control form-control-sm">
                    <option value="Pending" @if(is_null($checksheet->manager_qc)) selected @endif>Pending</option>
                    <option value="Approved" @if($checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED')
                    selected @endif>Approved</option>
                    <option value="Rejected" @if($checksheet->manager_qc === 'REJECTED') selected @endif>Rejected</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mt-2 small text-muted">
        <i class="fas fa-info-circle mr-1"></i> Mengubah status di sini akan langsung memperbarui progres
        checksheet.
    </div>

    <div class="mt-4 pb-2 text-right">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm">
            <i class="fas fa-save mr-1"></i> Update
        </button>
    </div>
</form>
