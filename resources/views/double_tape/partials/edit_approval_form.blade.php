<form action="{{ route('double_tape.update_approval', $checksheet->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header bg-info">
        <h5 class="modal-title font-weight-bold text-white">Approval Double Tape #{{ $checksheet->id }}</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
        <div class="alert alert-light border small">
            <strong>Item:</strong> {{ $checksheet->item->name ?? '-' }} |
            <strong>Tanggal:</strong> {{ $checksheet->date->format('d/m/Y') }} |
            <strong>Judgment:</strong> <span
                class="{{ $checksheet->judgment == 'OK' ? 'text-success' : 'text-danger' }} font-weight-bold">{{ $checksheet->judgment }}</span>
        </div>

        @foreach(['kashift_qc' => 'Ka Shift', 'supervisor_qc' => 'Supervisor', 'asst_manager_qc' => 'Asst Manager', 'manager_qc' => 'Manager'] as $field => $label)
            <div class="form-group row border-bottom pb-2">
                <label class="col-sm-4 small font-weight-bold">{{ $label }}</label>
                <div class="col-sm-8 text-right">
                    @php $status = $checksheet->$field == 'REJECTED' ? 'Rejected' : ($checksheet->$field ? 'Approved' : 'Pending'); @endphp
                    <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
                        <label class="btn btn-outline-warning {{ $status == 'Pending' ? 'active' : '' }}"><input
                                type="radio" name="{{ $field }}" value="Pending" {{ $status == 'Pending' ? 'checked' : '' }}>
                            P</label>
                        <label class="btn btn-outline-success {{ $status == 'Approved' ? 'active' : '' }}"><input
                                type="radio" name="{{ $field }}" value="Approved" {{ $status == 'Approved' ? 'checked' : '' }}> A</label>
                        <label class="btn btn-outline-danger {{ $status == 'Rejected' ? 'active' : '' }}"><input
                                type="radio" name="{{ $field }}" value="Rejected" {{ $status == 'Rejected' ? 'checked' : '' }}> R</label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary text-xs">Simpan</button>
    </div>
</form>
