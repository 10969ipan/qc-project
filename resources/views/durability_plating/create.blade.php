@extends('layouts.admin')

@section('title', 'Input Checksheet Durability Plating')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">Input Checksheet Durability Plating - {{ strtoupper($plantCode) }}</h6>
        <a href="{{ route('durability_plating.index', ['plant' => $plantCode]) }}" class="btn btn-sm btn-light font-weight-bold text-primary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
    <div class="card-body bg-light">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('durability_plating.store') }}" method="POST" id="checksheetForm">
            @csrf
            <input type="hidden" name="plant" value="{{ $plantCode }}">
            
            <div class="row">
                <!-- Data Header -->
                <div class="col-md-4">
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-header bg-white font-weight-bold text-primary border-bottom-0 pb-0">Data Informasi</div>
                        <div class="card-body pt-2">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Tanggal Check <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control form-control-sm" value="{{ old('date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Tanggal Produksi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_produksi" class="form-control form-control-sm" value="{{ old('tanggal_produksi', $defaultDate) }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Shift <span class="text-danger">*</span></label>
                                <select name="shift" class="form-control form-control-sm" required>
                                    <option value="1" {{ old('shift', $defaultShift) == '1' ? 'selected' : '' }}>Shift 1</option>
                                    <option value="2" {{ old('shift', $defaultShift) == '2' ? 'selected' : '' }}>Shift 2</option>
                                    <option value="3" {{ old('shift', $defaultShift) == '3' ? 'selected' : '' }}>Shift 3</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">No Lot Produksi</label>
                                <input type="text" name="no_lot_produksi" class="form-control form-control-sm" value="{{ old('no_lot_produksi') }}" placeholder="Opsional">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Part Name <span class="text-danger">*</span></label>
                                <select name="thickness_standard_id" id="thickness_standard_id" class="form-control form-control-sm select2" required>
                                    <option value="">Pilih Part...</option>
                                    @foreach($standards as $std)
                                        <option value="{{ $std->id }}" 
                                            data-std-cu="{{ $std->thickness_cu_std }}"
                                            data-std-ni="{{ $std->thickness_ni_std }}"
                                            data-std-cr="{{ $std->thickness_cr_std }}"
                                            {{ old('thickness_standard_id') == $std->id ? 'selected' : '' }}>
                                            {{ $std->part_name }} | {{ $std->customer }} ({{ $std->standard_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold mb-1">Analis / Operator <span class="text-danger">*</span></label>
                                <input type="text" name="analis" class="form-control form-control-sm text-uppercase" value="{{ old('analis') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Data & Average -->
                <div class="col-md-8">
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-header bg-white font-weight-bold text-primary border-bottom-0 pb-0">Input Data Thickness (mµ)</div>
                        <div class="card-body pt-2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center align-middle" style="font-size:0.8rem;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Unsur</th>
                                            <th>Standard (Min)</th>
                                            <th>Point 1</th>
                                            <th>Point 2</th>
                                            <th>Point 3</th>
                                            <th class="bg-primary text-white">Average</th>
                                            <th class="bg-info text-white">Judgment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Cu -->
                                        <tr>
                                            <td class="font-weight-bold">Cu</td>
                                            <td><span id="label_std_cu" class="font-weight-bold">-</span></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-cu" placeholder="Pt 1"></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-cu" placeholder="Pt 2"></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-cu" placeholder="Pt 3"></td>
                                            <td><input type="text" name="thickness_cu" id="avg_cu" class="form-control form-control-sm font-weight-bold bg-white text-center" readonly value="{{ old('thickness_cu') }}"></td>
                                            <td><span id="judge_cu" class="badge badge-secondary p-1">-</span></td>
                                        </tr>
                                        <!-- Ni -->
                                        <tr>
                                            <td class="font-weight-bold">Ni</td>
                                            <td><span id="label_std_ni" class="font-weight-bold">-</span></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-ni" placeholder="Pt 1"></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-ni" placeholder="Pt 2"></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-ni" placeholder="Pt 3"></td>
                                            <td><input type="text" name="thickness_ni" id="avg_ni" class="form-control form-control-sm font-weight-bold bg-white text-center" readonly value="{{ old('thickness_ni') }}"></td>
                                            <td><span id="judge_ni" class="badge badge-secondary p-1">-</span></td>
                                        </tr>
                                        <!-- Cr -->
                                        <tr>
                                            <td class="font-weight-bold">Cr</td>
                                            <td><span id="label_std_cr" class="font-weight-bold">-</span></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-cr" placeholder="Pt 1"></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-cr" placeholder="Pt 2"></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-cr" placeholder="Pt 3"></td>
                                            <td><input type="text" name="thickness_cr" id="avg_cr" class="form-control form-control-sm font-weight-bold bg-white text-center" readonly value="{{ old('thickness_cr') }}"></td>
                                            <td><span id="judge_cr" class="badge badge-secondary p-1">-</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border-0">
                                <div class="card-header bg-white font-weight-bold text-primary border-bottom-0 pb-0">Testing SB & MP</div>
                                <div class="card-body pt-2">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold mb-1">Step Test SB</label>
                                        <input type="text" name="step_test_sb" class="form-control form-control-sm" value="{{ old('step_test_sb') }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold mb-1">Step Test MP</label>
                                        <input type="text" name="step_test_mp" class="form-control form-control-sm" value="{{ old('step_test_mp') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border-0 h-100">
                                <div class="card-header bg-white font-weight-bold text-primary border-bottom-0 pb-0">Final Judgment & Remarks</div>
                                <div class="card-body pt-2">
                                    <div class="form-group mb-3 text-center">
                                        <label class="small font-weight-bold mb-1">Result (Keseluruhan)</label><br>
                                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                            <label class="btn btn-outline-success font-weight-bold active" id="btn_ok">
                                                <input type="radio" name="result" value="OK" checked required> OK
                                            </label>
                                            <label class="btn btn-outline-danger font-weight-bold" id="btn_ng">
                                                <input type="radio" name="result" value="NG" required> NG
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold mb-1">Keterangan / Remarks</label>
                                        <textarea name="keterangan" class="form-control form-control-sm" rows="2">{{ old('keterangan') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mt-3">
                <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btnSubmit">
                    <i class="fas fa-save mr-1"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 31px;
        font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 31px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'classic'
        });

        // Initialize variables
        let stdCu = 0, stdNi = 0, stdCr = 0;

        $('#thickness_standard_id').on('change', function() {
            var selected = $(this).find('option:selected');
            
            stdCu = parseFloat(selected.data('std-cu')) || 0;
            stdNi = parseFloat(selected.data('std-ni')) || 0;
            stdCr = parseFloat(selected.data('std-cr')) || 0;

            $('#label_std_cu').text(stdCu > 0 ? stdCu : '-');
            $('#label_std_ni').text(stdNi > 0 ? stdNi : '-');
            $('#label_std_cr').text(stdCr > 0 ? stdCr : '-');
            
            calculateAll();
        });

        // Calculate Average and Judgment
        function calculateAvg(elementClass, stdValue, avgInputId, judgeSpanId) {
            let sum = 0;
            let count = 0;
            
            $(elementClass).each(function() {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) {
                    sum += val;
                    count++;
                }
            });

            if (count > 0) {
                let avg = (sum / count).toFixed(2);
                $(avgInputId).val(avg);
                
                // Judgment Logic: if avg >= standard (OK) else (NG)
                if (stdValue > 0) {
                    if (parseFloat(avg) >= stdValue) {
                        $(judgeSpanId).removeClass('badge-secondary badge-danger').addClass('badge-success').text('OK');
                        return 'OK';
                    } else {
                        $(judgeSpanId).removeClass('badge-secondary badge-success').addClass('badge-danger').text('NG');
                        return 'NG';
                    }
                } else {
                    $(judgeSpanId).removeClass('badge-danger badge-success').addClass('badge-secondary').text('-');
                    return 'OK'; // No standard means OK
                }
            } else {
                $(avgInputId).val('');
                $(judgeSpanId).removeClass('badge-danger badge-success').addClass('badge-secondary').text('-');
                return 'OK';
            }
        }

        function calculateAll() {
            let judgeCu = calculateAvg('.calc-cu', stdCu, '#avg_cu', '#judge_cu');
            let judgeNi = calculateAvg('.calc-ni', stdNi, '#avg_ni', '#judge_ni');
            let judgeCr = calculateAvg('.calc-cr', stdCr, '#avg_cr', '#judge_cr');
            
            // Auto Final Judgment
            if (judgeCu === 'NG' || judgeNi === 'NG' || judgeCr === 'NG') {
                $('input[name="result"][value="NG"]').prop('checked', true).parent().button('toggle');
            } else {
                $('input[name="result"][value="OK"]').prop('checked', true).parent().button('toggle');
            }
        }

        $('.calc-cu, .calc-ni, .calc-cr').on('input', function() {
            calculateAll();
        });

        // Trigger change on load if old value exists
        if ($('#thickness_standard_id').val()) {
            $('#thickness_standard_id').trigger('change');
        }
    });
</script>
@endpush
@endsection
