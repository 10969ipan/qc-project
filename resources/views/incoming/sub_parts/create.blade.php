@extends('layouts.admin')

@section('title', 'Input Data Incoming Sub-Part')

@section('content')
        @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_sub_parts', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 border-right">
                    <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
                        CHECK SHEET INCOMING SUB-PART
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
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: QC-KRW-F-0212</div>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Input Data Incoming Sub-Part</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('incoming.sub_parts.store') }}" method="POST" id="checksheetForm">
                @csrf
                <input type="hidden" name="plant_id" value="{{ request('plant') ?? auth()->user()->plant_id }}">

                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead class="bg-light text-center small font-weight-bold">
                            <tr>
                                <th>Sub-Part Name</th>
                                <th>Tanggal Check</th>
                                <th>Tgl Datang</th>
                                <th>Lot/Batch Number</th>
                                <th>Quantity (Pcs) / Lot</th>
                                <th>Sampling (Pcs)</th>
                                <th>Check Dimensi</th>
                                <th>Expired Date</th>
                                <th style="min-width: 200px;">Detail NG</th>
                                <th>Judgment</th>
                                <th>QC</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-control select2" name="item_id" id="itemSelect" required
                                        style="min-width: 180px;">
                                        <option value="">-- Pilih Sub-Part --</option>
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
                                <td><input type="number" class="form-control text-center" name="quantity" id="lotQtyInput" required></td>
                                <td><input type="number" class="form-control text-center" name="sampling_size_pcs" id="totalCheckInput" required>
                                </td>
                                <td>
                                    <select class="form-control" name="check_dimensi">
                                        <option value="OK">OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                </td>
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
                                    <button type="button" id="addDefectBtn" class="btn btn-outline-info btn-xs"><i
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
                        <i class="fas fa-save mr-1"></i> SIMPAN DATA SUB-PART
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/checksheet/incoming-create.js') }}"></script>
@endpush
