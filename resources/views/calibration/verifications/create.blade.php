@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Input Verifikasi Alat Ukur - Plant {{ strtoupper($plantCode) }}</h1>
            <a href="{{ route('calibration.verifications.index', ['plant' => $plantCode]) }}"
                class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Verifikasi Alat</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.verifications.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pilih Alat Ukur</label>
                                <select name="tool_id" id="tool_select"
                                    class="form-control @error('tool_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Alat --</option>
                                    @foreach($tools as $tool)
                                        <option value="{{ $tool->id }}" 
                                            {{ (isset($selectedToolId) && $selectedToolId == $tool->id) ? 'selected' : '' }}
                                            data-name="{{ $tool->name_alat }}"
                                            data-serial="{{ $tool->serial_number }}" data-range="{{ $tool->range }}"
                                            data-resolusi="{{ $tool->resolusi }}"
                                            data-frekuensi="{{ $tool->frekuensi_kalibrasi }}"
                                            data-lokasi="{{ $tool->lokasi_pakai }}">
                                            {{ $tool->name_alat }} ({{ $tool->serial_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tool_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label>Nama Alat</label>
                                <input type="text" name="name_alat" id="name_alat" class="form-control" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Merk</label>
                                <input type="text" name="merk" class="form-control @error('merk') is-invalid @enderror"
                                    value="{{ old('merk') }}" required>
                            </div>
                            <div class="form-group">
                                <label>No. Seri</label>
                                <input type="text" name="serial_number" id="serial_number" class="form-control" readonly
                                    required>
                            </div>
                            <div class="form-group">
                                <label>Rentang Ukur (Range)</label>
                                <input type="text" name="rentang_ukur" id="rentang_ukur" class="form-control" readonly
                                    required>
                            </div>
                            <div class="form-group">
                                <label>Resolusi</label>
                                <input type="text" name="resolusi" id="resolusi" class="form-control" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Frekuensi Kalibrasi</label>
                                <input type="text" name="frekuensi_kalibrasi" id="frekuensi_kalibrasi" class="form-control"
                                    readonly required>
                            </div>
                            <div class="form-group">
                                <label>Lokasi Penyimpanan</label>
                                <input type="text" name="lokasi_penyimpanan" id="lokasi_penyimpanan" class="form-control"
                                    readonly required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Kalibrasi</label>
                                <input type="date" name="tanggal_kalibrasi"
                                    class="form-control @error('tanggal_kalibrasi') is-invalid @enderror"
                                    value="{{ old('tanggal_kalibrasi') }}" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Verifikasi</label>
                                <input type="date" name="tanggal_verifikasi"
                                    class="form-control @error('tanggal_verifikasi') is-invalid @enderror"
                                    value="{{ old('tanggal_verifikasi') }}" required>
                            </div>
                            <div class="form-group">
                                <label>Next Kalibrasi</label>
                                <input type="date" name="next_kalibrasi"
                                    class="form-control @error('next_kalibrasi') is-invalid @enderror"
                                    value="{{ old('next_kalibrasi') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">Detail Verifikasi</h6>
                            <button type="button" class="btn btn-sm btn-info" id="add-row">
                                <i class="fas fa-plus"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" id="verification-table">
                                    <thead class="bg-white">
                                        <tr>
                                            <th>Nilai Alat</th>
                                            <th>Nilai Koreksi</th>
                                            <th>Nilai Ketidakpastian</th>
                                            <th>Hasil Verifikasi</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="verification-body">
                                        <tr>
                                            <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm"
                                                    required></td>
                                            <td><input type="text" name="nilai_koreksi[]"
                                                    class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="nilai_ketidakpastian[]"
                                                    class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="hasil_verifikasi[]"
                                                    class="form-control form-control-sm" required></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-row"
                                                    disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Judgment</label>
                                <select name="judgment" class="form-control shadow-border" required>
                                    <option value="OK">OK</option>
                                    <option value="NG">NG</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Std. Toleransi</label>
                                <input type="text" name="std_toleransi" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Acuan Toleransi</label>
                                <input type="text" name="acuan_toleransi" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Sertifikasi (Upload PDF Hasil Verifikasi)</label>
                        <input type="file" name="certification"
                            class="form-control-file @error('certification') is-invalid @enderror" accept=".pdf">
                        <small class="text-muted">Format PDF, Max 10MB.</small>
                        @error('certification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary px-4">Simpan Verifikasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#tool_select').on('change', function () {
                var selected = $(this).find('option:selected');
                if (selected.val()) {
                    $('#name_alat').val(selected.data('name'));
                    $('#serial_number').val(selected.data('serial'));
                    $('#rentang_ukur').val(selected.data('range'));
                    $('#resolusi').val(selected.data('resolusi'));
                    $('#frekuensi_kalibrasi').val(selected.data('frekuensi'));
                    $('#lokasi_penyimpanan').val(selected.data('lokasi'));
                }
            });

            // Trigger change if tool_id is pre-selected
            if ($('#tool_select').val()) {
                $('#tool_select').trigger('change');
            }

            // Add Row
            $('#add-row').on('click', function () {
                var newRow = `
                            <tr>
                                <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm" required></td>
                                <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm" required></td>
                                <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm" required></td>
                                <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm" required></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                $('#verification-body').append(newRow);
                updateRemoveButtons();
            });

            // Remove Row
            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
                updateRemoveButtons();
            });

            function updateRemoveButtons() {
                var rowCount = $('#verification-body tr').length;
                if (rowCount <= 1) {
                    $('.remove-row').prop('disabled', true);
                } else {
                    $('.remove-row').prop('disabled', false);
                }
            }
        });
    </script>
@endpush