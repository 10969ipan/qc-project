<form action="{{ route('plating.update_approval', $checksheet->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header bg-info">
        <h5 class="modal-title font-weight-bold text-white">Approval Checksheet Plating #{{ $checksheet->id }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="alert alert-light border small">
            <div class="row">
                <div class="col-md-6">
                    <strong>Item:</strong> {{ $checksheet->item->name ?? '-' }}<br>
                    <strong>Tanggal:</strong> {{ $checksheet->date->format('d/m/Y') }}<br>
                    <strong>Shift:</strong> {{ $checksheet->shift }}
                </div>
                <div class="col-md-6 border-left">
                    <strong>Total Qty:</strong> {{ $checksheet->total_qty }}<br>
                    <strong>Check Qty:</strong> {{ $checksheet->sampling_qty }}<br>
                    <strong>Result:</strong> <span
                        class="{{ $checksheet->judgment == 'OK' ? 'text-success' : 'text-danger' }} font-weight-bold">{{ $checksheet->judgment }}</span>
                </div>
            </div>
        </div>

        @php
            $rolesMapping = [
                'kashift_qc' => 'Ka Shift',
                'supervisor_qc' => 'Supervisor',
                'asst_manager_qc' => 'Asst Manager',
                'manager_qc' => 'Manager'
            ];
        @endphp

        @foreach($rolesMapping as $field => $label)
            <div class="form-group row border-bottom pb-2">
                <label class="col-sm-4 col-form-label font-weight-bold small">{{ $label }}</label>
                <div class="col-sm-8">
                    @php
                        $currentVal = $checksheet->$field;
                        $status = 'Pending';
                        if ($currentVal === 'REJECTED')
                            $status = 'Rejected';
                        elseif ($currentVal)
                            $status = 'Approved';
                    @endphp
                    <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                        <label class="btn btn-outline-warning btn-sm flex-fill {{ $status == 'Pending' ? 'active' : '' }}">
                            <input type="radio" name="{{ $field }}" value="Pending" {{ $status == 'Pending' ? 'checked' : '' }}> Pending
                        </label>
                        <label class="btn btn-outline-success btn-sm flex-fill {{ $status == 'Approved' ? 'active' : '' }}">
                            <input type="radio" name="{{ $field }}" value="Approved" {{ $status == 'Approved' ? 'checked' : '' }}> Approve
                        </label>
                        <label class="btn btn-outline-danger btn-sm flex-fill {{ $status == 'Rejected' ? 'active' : '' }}">
                            <input type="radio" name="{{ $field }}" value="Rejected" {{ $status == 'Rejected' ? 'checked' : '' }}> Reject
                        </label>
                    </div>
                    @if($currentVal && $currentVal !== 'REJECTED')
                        <small class="text-success mt-1 d-block"><i class="fas fa-check-double"></i> Oleh:
                            {{ $currentVal }}</small>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan Status Approval</button>
    </div>
</form>
