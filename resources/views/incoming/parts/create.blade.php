@extends('layouts.admin')

@section('title', 'Input Data Incoming Part')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET INCOMING PART
                        @php
                            $plant = request('plant') ?? auth()->user()->plant_id;
                            $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
                            $plantCode = strtolower($plantCode ?: 'karawang');
                        @endphp
                        <span
                            class="badge badge-{{ $plantCode === 'jakarta' ? 'info' : 'primary' }} d-block d-md-inline-block ml-md-2 mt-2 mt-md-0"
                            style="font-size: 0.8rem; width: fit-content;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </h1>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0210</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tgl. Terbit</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 01/01/2026</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Revisi</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Part</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.parts.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>Item Part</th>
                                <th>Tanggal / Shift</th>
                                <th>Total Check</th>
                                <th>Tanggal Datang</th>
                                <th style="min-width: 250px;">Detail NG</th>
                                <th>Result</th>
                                <th>QC Initials</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Item Part -->
                                <td>
                                    <select class="form-control select2" name="item_id" id="itemSelect" required>
                                        <option value="">-- Pilih Item --</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" data-defects="{{ json_encode($item->defects) }}">
                                                {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Date & Shift -->
                                <td>
                                    <input type="date" class="form-control mb-2" name="date" value="{{ $defaultDate }}"
                                        required>
                                    <select class="form-control" name="shift" required>
                                        <option value="1" {{ $defaultShift == 1 ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $defaultShift == 2 ? 'selected' : '' }}>Shift 2</option>
                                        <option value="3" {{ $defaultShift == 3 ? 'selected' : '' }}>Shift 3</option>
                                    </select>
                                </td>

                                <!-- Total Check -->
                                <td>
                                    <input type="number" class="form-control text-center" name="total_check" placeholder="0"
                                        min="0" required>
                                </td>

                                <!-- Tanggal Datang -->
                                <td>
                                    <input type="date" class="form-control" name="tanggal_datang" required>
                                </td>

                                <!-- Defect Details -->
                                <td>
                                    <div id="defectContainer">
                                        <div class="input-group mb-2 defect-row">
                                            <select class="form-control defect-select" name="defect_types[]">
                                                <option value="">-- Pilih Defect --</option>
                                            </select>
                                            <input type="number" class="form-control defect-qty" name="defect_quantities[]"
                                                placeholder="Qty" min="1" style="max-width: 80px;">
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-outline-info btn-sm mt-1">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </td>

                                <!-- Result/Judgment -->
                                <td>
                                    <select class="form-control font-weight-bold" name="judgment" id="judgmentSelect"
                                        required>
                                        <option value="OK">OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                    <div class="mt-2 text-center">
                                        <label class="small font-weight-bold">Total NG</label>
                                        <input type="number" class="form-control form-control-sm text-center"
                                            name="total_ng" id="totalNgInput" value="0" readonly>
                                    </div>
                                </td>

                                <!-- QC Initials -->
                                <td>
                                    <input type="text" class="form-control text-center" name="operator_initials"
                                        value="{{ auth()->user()->initials ?? '' }}" required>
                                </td>

                                <!-- Remarks -->
                                <td>
                                    <textarea class="form-control" name="remarks" rows="3" placeholder="..."></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-save mr-1"></i> SIMPAN DATA
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@@push('scripts')
    <script src="{{ asset('js/incoming/incoming-create.js') }}"></script>
@endpush