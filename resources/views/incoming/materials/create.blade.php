@extends('layouts.admin')

@section('title', 'Input Data Incoming Material')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET INCOMING MATERIAL
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
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0211</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tgl. Terbit</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 01/01/2026</div>
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
            <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Material</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.materials.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                <div class="table-responsive">
                    <table class="table table-bordered" id="checksheetTable" width="100%" cellspacing="0">
                        <thead class="bg-light text-center small font-weight-bold">
                            <tr>
                                <th rowspan="2">Material Name</th>
                                <th rowspan="2">Tanggal Check</th>
                                <th rowspan="2">Tgl Datang</th>
                                <th rowspan="2">Lot/Batch Number</th>
                                <th colspan="3">Quantity Details (Kg)</th>
                                <th rowspan="2">Expired Date</th>
                                <th rowspan="2" style="min-width: 200px;">Detail NG</th>
                                <th rowspan="2">Judgment</th>
                                <th rowspan="2">QC</th>
                                <th rowspan="2">Remarks</th>
                            </tr>
                            <tr>
                                <th>Lot Qty (Kg)</th>
                                <th>Komper/Karung</th>
                                <th>Sampling Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Material Name -->
                                <td>
                                    <select class="form-control select2" name="item_id" id="itemSelect" required
                                        style="min-width: 200px;">
                                        <option value="">-- Pilih Material --</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" data-defects="{{ json_encode($item->defects) }}">
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Tanggal Check -->
                                <td>
                                    <input type="date" class="form-control" name="date" value="{{ $defaultDate }}" required>
                                </td>

                                <!-- Tanggal Datang -->
                                <td>
                                    <input type="date" class="form-control" name="tanggal_datang" required>
                                </td>

                                <!-- Lot Number -->
                                <td>
                                    <input type="text" class="form-control" name="lot_batch_number" placeholder="Lot #"
                                        required>
                                </td>

                                <!-- Quantity Details -->
                                <td>
                                    <input type="number" step="0.01" class="form-control text-center" name="quantity_kg"
                                        id="lotQtyInput" placeholder="0.00" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control text-center"
                                        name="komper_karung_kg" placeholder="0.00" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control text-center"
                                        name="sampling_size_karung_kg" id="totalCheckInput" placeholder="0.00" required>
                                </td>

                                <!-- Expired Date -->
                                <td>
                                    <input type="date" class="form-control" name="expired_date" required>
                                </td>

                                <!-- Defect Details -->
                                <td>
                                    <div id="defectContainer">
                                        <div class="row no-gutters mb-2 defect-row align-items-center">
                                            <div class="col-8 pr-1">
                                                <select class="form-control defect-select font-weight-bold" name="defect_types[]">
                                                    <option value="">-- Pilih Defect --</option>
                                                </select>
                                            </div>
                                            <div class="col-3 pr-1">
                                                <input type="number" class="form-control defect-qty text-center font-weight-bold" name="defect_quantities[]"
                                                    placeholder="Qty" min="1">
                                            </div>
                                            <div class="col-1 text-center"></div>
                                        </div>
                                    </div>
                                    <button type="button" id="addDefectBtn" class="btn btn-outline-info btn-xs mt-1">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>

                                <!-- Result -->
                                <td>
                                    <select class="form-control font-weight-bold" name="judgment" id="judgmentSelect"
                                        required>
                                        <option value="OK">OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                    <input type="hidden" name="total_ng" id="totalNgInput" value="0">
                                </td>

                                <!-- QC -->
                                <td>
                                    <input type="text" class="form-control text-center" name="operator_initials"
                                        value="{{ auth()->user()->initials ?? '' }}" required style="min-width: 60px;">
                                </td>

                                <!-- Remarks -->
                                <td>
                                    <textarea class="form-control" name="remarks" rows="2" placeholder="..."></textarea>
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

@push('scripts')
    <script src="{{ asset('js/checksheet/incoming-create.js') }}"></script>
@endpush
