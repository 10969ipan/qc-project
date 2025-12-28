@extends('layouts.admin')

@section('title', 'Edit Status Approval Cross Cut')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Status Approval Cross Cut</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Checksheet ID: {{ $checksheet->id }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.cross_cut.update_approval', $checksheet->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Tanggal Produksi:</strong> {{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('Y-m-d') }}</p>
                    <p><strong>Barang:</strong> {{ $checksheet->item->name ?? '-' }}</p>
                    <p><strong>Part No:</strong> {{ $checksheet->item->part_number ?? '-' }}</p>
                </div>
            </div>

            <hr>

            <div class="row">
                <!-- Kashift QC -->
                <div class="form-group col-md-3">
                    <label for="kashift_qc">Status Kashift QC</label>
                    <select name="kashift_qc" id="kashift_qc" class="form-control">
                        <option value="Pending" @if(is_null($checksheet->kashift_qc)) selected @endif>Pending</option>
                        <option value="Approved" @if($checksheet->kashift_qc && $checksheet->kashift_qc !== 'REJECTED') selected @endif>Approved</option>
                        <option value="Rejected" @if($checksheet->kashift_qc === 'REJECTED') selected @endif>Rejected</option>
                    </select>
                </div>

                <!-- Supervisor QC -->
                <div class="form-group col-md-3">
                    <label for="supervisor_qc">Status Supervisor QC</label>
                    <select name="supervisor_qc" id="supervisor_qc" class="form-control">
                        <option value="Pending" @if(is_null($checksheet->supervisor_qc)) selected @endif>Pending</option>
                        <option value="Approved" @if($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED') selected @endif>Approved</option>
                        <option value="Rejected" @if($checksheet->supervisor_qc === 'REJECTED') selected @endif>Rejected</option>
                    </select>
                </div>

                <!-- Asst. Manager QC -->
                <div class="form-group col-md-3">
                    <label for="asst_manager_qc">Status Asst. Manager QC</label>
                    <select name="asst_manager_qc" id="asst_manager_qc" class="form-control">
                        <option value="Pending" @if(is_null($checksheet->asst_manager_qc)) selected @endif>Pending</option>
                        <option value="Approved" @if($checksheet->asst_manager_qc && $checksheet->asst_manager_qc !== 'REJECTED') selected @endif>Approved</option>
                        <option value="Rejected" @if($checksheet->asst_manager_qc === 'REJECTED') selected @endif>Rejected</option>
                    </select>
                </div>

                <!-- Manager QC -->
                <div class="form-group col-md-3">
                    <label for="manager_qc">Status Manager QC</label>
                    <select name="manager_qc" id="manager_qc" class="form-control">
                        <option value="Pending" @if(is_null($checksheet->manager_qc)) selected @endif>Pending</option>
                        <option value="Approved" @if($checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED') selected @endif>Approved</option>
                        <option value="Rejected" @if($checksheet->manager_qc === 'REJECTED') selected @endif>Rejected</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('cross_cut.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
