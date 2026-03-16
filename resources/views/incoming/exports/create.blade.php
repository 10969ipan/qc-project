@extends('layouts.admin')

@section('title', 'Input Data Incoming Export')

@section('content')
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET INCOMING EXPORT
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
                            <div class="col-7">: QC-KRW-F-0213</div>
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
            <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Export</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.exports.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead class="bg-light text-center small font-weight-bold">
                            <tr>
                                <th>Item Part</th>
                                <th>Tanggal Check</th>
                                <th>Tanggal Delivery</th>
                                <th style="min-width: 250px;">Detail NG</th>
                                <th>Judgment</th>
                                <th>QC Initials</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-control select2" name="item_id" id="itemSelect" required
                                        style="min-width: 200px;">
                                        <option value="">-- Pilih Item --</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" data-defects="{{ json_encode($item->defects) }}">
                                                {{ $item->name }} ({{ $item->part_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="date" class="form-control text-center" name="date"
                                        value="{{ $defaultDate }}" required></td>
                                <td><input type="date" class="form-control text-center" name="tanggal_delivery" required>
                                </td>
                                <td>
                                    <div id="defectContainer">
                                        <div class="input-group mb-2 defect-row">
                                            <select class="form-control defect-select form-control-sm"
                                                name="defect_types[]">
                                                <option value="">-- Defect --</option>
                                            </select>
                                            <input type="number" class="form-control defect-qty form-control-sm"
                                                name="defect_quantities[]" placeholder="Qty" min="1"
                                                style="max-width: 60px;">
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
                                        value="{{ auth()->user()->initials }}" required style="min-width: 80px;"></td>
                                <td><textarea class="form-control" name="remarks" rows="2" placeholder="..."></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-success px-5 font-weight-bold shadow-sm">
                        <i class="fas fa-check-circle mr-1"></i> SUBMIT DATA EXPORT
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@@push('scripts')
    <script src="{{ asset('js/incoming/incoming-create.js') }}"></script>
@endpush