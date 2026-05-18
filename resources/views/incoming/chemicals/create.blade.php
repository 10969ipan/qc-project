@extends('layouts.admin')

@section('title', 'Input Data Incoming Chemical')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET INCOMING CHEMICAL
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
                <div class="col-md-4 d-flex justify-content-end text-xs font-weight-bold">
                    <div style="max-width: 250px;">
                        <div class="row mb-1">
                            <div class="col-5">No. Dokumen</div>
                            <div class="col-7">: QC-KRW-F-0214</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5">Tgl. Terbit</div>
                            <div class="col-7">: 01/01/2026</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Chemical</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.chemicals.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead class="bg-light text-center small font-weight-bold">
                            <tr>
                                <th rowspan="2">Chemical Name</th>
                                <th rowspan="2">Tanggal Check</th>
                                <th rowspan="2">Tgl Datang</th>
                                <th rowspan="2">Lot Number</th>
                                <th colspan="3">Quantity (Kg)</th>
                                <th rowspan="2">Expired Date</th>
                                <th rowspan="2" style="min-width: 200px;">Detail NG</th>
                                <th rowspan="2">Judgment</th>
                                <th rowspan="2">QC</th>
                                <th rowspan="2">Remarks</th>
                            </tr>
                            <tr>
                                <th>Lot Qty (Kg)</th>
                                <th>Komp/Jirigen</th>
                                <th>Samp. Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-control select2" name="item_id" id="itemSelect" required
                                        style="min-width: 180px;">
                                        <option value="">-- Pilih Chemical --</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" data-defects="{{ json_encode($item->defects) }}">
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="date" class="form-control" name="date" value="{{ $defaultDate }}" required>
                                </td>
                                <td><input type="date" class="form-control" name="tanggal_datang" required></td>
                                <td><input type="text" class="form-control" name="lot_batch_number" required></td>
                                <td><input type="number" step="0.01" class="form-control text-center" name="quantity_kg"
                                        id="lotQtyInput" required></td>
                                <td><input type="number" step="0.01" class="form-control text-center"
                                        name="komper_jirigen_kg" required></td>
                                <td><input type="number" step="0.01" class="form-control text-center"
                                        name="sampling_size_jirigen_kg" id="totalCheckInput" required></td>
                                <td><input type="date" class="form-control" name="expired_date" required></td>
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
                                    <button type="button" id="addDefectBtn" class="btn btn-outline-info btn-xs mt-1"><i
                                            class="fas fa-plus"></i></button>
                                </td>
                                <td>
                                    <select class="form-control font-weight-bold" name="judgment" id="judgmentSelect"
                                        required>
                                        <option value="OK">OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                    <input type="hidden" name="total_ng" id="totalNgInput" value="0">
                                </td>
                                <td><input type="text" class="form-control text-center" name="operator_initials"
                                        value="{{ auth()->user()->initials }}" required style="min-width: 60px;"></td>
                                <td><textarea class="form-control" name="remarks" rows="2"></textarea></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary px-5 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> SIMPAN DATA CHEMICAL
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/checksheet/incoming-create.js') }}"></script>
@endpush
