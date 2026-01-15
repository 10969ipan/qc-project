@extends('layouts.admin')

@section('content')
    <x-plant-header title="Edit Data Checksheet Inprocess" :plant="request('plant')" />
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Data Checksheet Inprocess</h1>
        <a href="{{ route('in_process.index', ['plant' => request('plant')]) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Checksheet Inprocess</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('in_process.update', ['id' => $checksheet->id, 'plant' => request('plant')]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="item_id">Item Part</label>
                            <select name="item_id" id="item_id" class="form-control" required>
                                <option value="" disabled style="font-weight: bold; color: #6c757d;">Pilih Item Part
                                </option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}
                                        data-part-number="{{ $item->part_number }}">
                                        {{ $item->name }} ({{ $item->customer }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date">Tanggal</label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ $checksheet->date }}"
                                required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="shift">Shift</label>
                            <select name="shift" id="shift" class="form-control" required>
                                <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>Shift 1</option>
                                <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>Shift 2</option>
                                <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>Shift 3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="code_machine">No Mesin</label>
                            <select name="code_machine" id="code_machine" class="form-control" required>
                                <option value="">Pilih Mesin</option>
                                @for ($i = 1; $i <= 18; $i++)
                                    <option value="{{ $i }}" {{ $checksheet->code_machine == $i ? 'selected' : '' }}>Mesin
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->role !== 'inspector')
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="jam_before">Jam (Before)</label>
                                <input type="time" name="jam_before" id="jam_before" class="form-control"
                                    value="{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="jam_after">Jam (After)</label>
                                <input type="time" name="jam_after" id="jam_after" class="form-control"
                                    value="{{ $checksheet->created_at->format('H:i') }}">
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_qty">Total Qty</label>
                            <input type="number" name="total_qty" id="total_qty" class="form-control"
                                value="{{ $checksheet->total_qty }}" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sampling_qty">Sampling Qty</label>
                            <input type="number" name="sampling_qty" id="sampling_qty" class="form-control"
                                value="{{ $checksheet->sampling_qty }}" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_ok">Total OK</label>
                            <input type="number" name="total_ok" id="total_ok" class="form-control"
                                value="{{ $checksheet->total_ok }}" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="total_ng">Total NG</label>
                            <input type="number" name="total_ng" id="total_ng" class="form-control"
                                value="{{ $checksheet->total_ng }}" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="judgment">Judgment</label>
                            <select name="judgment" id="judgment" class="form-control" required>
                                <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="operator_initials">Inisial Operator</label>
                            <input type="text" name="operator_initials" id="operator_initials" class="form-control"
                                value="{{ $checksheet->operator_initials }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Check Dimensi</label>
                    @php
                        $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true) ?? [];
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" style="min-width: 1000px;">
                            <thead class="text-center">
                                <tr>
                                    <th style="width: 15%;">Cavity</th>
                                    <th>Point 1</th>
                                    <th>Point 2</th>
                                    <th>Point 3</th>
                                    <th>Point 4</th>
                                    <th>Point 5</th>
                                    <th>Point 6</th>
                                    <th>Point 7</th>
                                    <th>Point 8</th>
                                    <th>Point 9</th>
                                    <th>Point 10</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 1; $i <= 8; $i++)
                                    <tr>
                                        <td class="text-center font-weight-bold">Cav {{ $i }}</td>
                                        @for ($j = 1; $j <= 10; $j++)
                                            <td>
                                                <input type="text" class="form-control" style="min-width: 80px; font-size: 16px;"
                                                    name="dimensions[{{ $i }}][{{ $j }}]" value="{{ $dimensions[$i][$j] ?? '' }}"
                                                    placeholder="P{{ $j }}">
                                            </td>
                                        @endfor
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="nextProsesContainer" style="display: {{ $checksheet->judgment == 'NG' ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label for="next_proses" class="text-danger font-weight-bold">Next Proses</label>
                        <select name="next_proses" id="next_proses" class="form-control">
                            <option value="">-- Pilih Next Proses --</option>
                            <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>CRUSHING</option>
                            <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR</option>
                            <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>FINISHING</option>
                            <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR</option>
                            @if($checksheet->next_proses && !in_array($checksheet->next_proses, ['CRUSHING', 'SORTIR', 'FINISHING', 'REPAIR']))
                                <option value="{{ $checksheet->next_proses }}" selected>{{ $checksheet->next_proses }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="remarks">Keterangan</label>
                    <textarea name="remarks" id="remarks" class="form-control"
                        rows="3">{{ $checksheet->remarks }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {


            const partDimensionStandards = JSON.parse('{!! $partDimensionStandards !!}');

            function getAqlLimits(sampleSize) {
                if (sampleSize >= 1250) return { acc: 14, rej: 15 };
                if (sampleSize >= 800) return { acc: 10, rej: 11 };
                if (sampleSize >= 500) return { acc: 7, rej: 8 };
                if (sampleSize >= 315) return { acc: 5, rej: 6 };
                if (sampleSize >= 200) return { acc: 3, rej: 4 };
                if (sampleSize >= 125) return { acc: 2, rej: 3 };
                if (sampleSize >= 80) return { acc: 1, rej: 2 };
                if (sampleSize >= 20) return { acc: 0, rej: 1 };
                return { acc: 0, rej: 1 };
            }

            function updateJudgment() {
                const sampling = parseInt($('#sampling_qty').val()) || 0;
                const ng = parseInt($('#total_ng').val()) || 0;
                const isDimensiInvalid = $('.is-invalid').length > 0;

                // Update Total OK
                if (sampling >= ng) {
                    $('#total_ok').val(sampling - ng);
                } else {
                    $('#total_ok').val(0);
                }

                const limits = getAqlLimits(sampling);
                const judgmentSelect = $('#judgment');

                if (ng > 0 || sampling > 0 || isDimensiInvalid) {
                    if (isDimensiInvalid || ng >= limits.rej) {
                        judgmentSelect.val('NG');
                        judgmentSelect.removeClass('text-success').addClass('text-danger');
                    } else if (ng <= limits.acc) {
                        judgmentSelect.val('OK');
                        judgmentSelect.removeClass('text-danger').addClass('text-success');
                    } else {
                        judgmentSelect.val('NG');
                        judgmentSelect.removeClass('text-success').addClass('text-danger');
                    }
                // Show/Hide Next Proses dropdown based on judgment
                toggleNextProses();
            }

            function toggleNextProses() {
                const judgment = $('#judgment').val();
                const container = $('#nextProsesContainer');
                if (judgment === 'NG') {
                    container.slideDown();
                } else {
                    container.slideUp();
                    $('#next_proses').val('');
                }
            }

            function validateDimensions() {
                const selectedOption = $('#item_id').find('option:selected');
                // We need part_number. In edit.blade.php, it's not in data-attribute.
                // Let's modify the item option to include it or find it from a js object.
                // For now, I'll update the blade template to include data-part-number.
                const itemPartNumber = selectedOption.data('part-number');
                const dimensionStandards = partDimensionStandards[itemPartNumber];

                if (!dimensionStandards) {
                    $('input[name^="dimensions"]').removeClass('is-invalid');
                    updateJudgment();
                    return;
                }

                $('input[name^="dimensions"]').each(function () {
                    const name = $(this).attr('name');
                    const match = name.match(/\[(\d+)\]\[(\d+)\]/);
                    if (!match) return;

                    const point = match[2];
                    const standard = dimensionStandards[point];
                    const valStr = $(this).val().trim();
                    const value = parseFloat(valStr);

                    if (standard && valStr !== '' && !isNaN(value)) {
                        const lowerBound = standard.size - standard.tolerance;
                        const upperBound = standard.size + standard.tolerance;

                        if (value < lowerBound || value > upperBound) {
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                updateJudgment();
            }

            $(document).on('input', 'input[name^="dimensions"]', validateDimensions);
            $('#sampling_qty, #total_ng').on('input', updateJudgment);

            $('#item_id').on('change', function () {
                // In edit mode, changing item might be rare but possible
                validateDimensions();
            });

            // Initial check
            validateDimensions();
        });
    </script>
    <style>
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
        }
    </style>
@endpush