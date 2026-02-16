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
                                <th>Total</th>
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
                                        required></td>
                                <td><input type="number" step="0.01" class="form-control text-center"
                                        name="komper_jirigen_kg" required></td>
                                <td><input type="number" step="0.01" class="form-control text-center"
                                        name="sampling_size_jirigen_kg" required></td>
                                <td><input type="date" class="form-control" name="expired_date" required></td>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#itemSelect').on('change', function () {
                const defects = $(this).find(':selected').data('defects');
                updateDefectOptions(defects);
            });

            function updateDefectOptions(defects) {
                $('.defect-select').each(function () {
                    const currentVal = $(this).val();
                    $(this).empty().append('<option value="">-- Defect --</option>');
                    if (defects) {
                        const defectList = Array.isArray(defects) ? defects : JSON.parse(defects || '[]');
                        defectList.forEach(defect => {
                            const val = typeof defect === 'string' ? defect : (defect.name || defect.type);
                            $(this).append(`<option value="${val}">${val}</option>`);
                        });
                    }
                    $(this).val(currentVal);
                });
            }

            $('#addDefectBtn').on('click', function () {
                const firstRow = $('.defect-row').first().clone();
                firstRow.find('input').val('');
                firstRow.find('select').val('');
                $('#defectContainer').append(firstRow);
            });

            $(document).on('input', '.defect-qty, .defect-select', function () {
                let totalNg = 0;
                $('.defect-row').each(function () {
                    const qty = parseInt($(this).find('.defect-qty').val()) || 0;
                    if ($(this).find('.defect-select').val()) {
                        totalNg += qty;
                    }
                });
                $('#totalNgInput').val(totalNg);
                $('#judgmentSelect').val(totalNg > 0 ? 'NG' : 'OK');
            });

            $('#checksheetForm').on('submit', function (e) {
                e.preventDefault(); // Always prevent default for AJAX

                // Show loading state
                var saveBtn = $(this).find('button[type="submit"]');
                var originalHtml = saveBtn.html();
                saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                var formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#global-loader').hide();
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data Berhasil Disimpan',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Lihat Data',
                                cancelButtonText: 'Tutup',
                                reverseButtons: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = response.index_url;
                                } else {
                                    // Reset Form & Defect Rows
                                    resetState();
                                }
                            });
                        }
                    },
                    error: function (xhr) {
                        $('#global-loader').hide();
                        var errorMsg = 'Gagal menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                        saveBtn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            function resetState() {
                $('#checksheetForm')[0].reset();
                $('#itemSelect').val('').trigger('change');
                $('#defectContainer').find('.defect-row').not(':first').remove();
                $('#totalNgInput').val(0);
                $('#judgmentSelect').val('OK');
            }
        });
    </script>
@endpush