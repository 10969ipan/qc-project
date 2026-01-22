@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Verifikasi Alat Ukur - Plant {{ strtoupper($plantCode) }}</h1>
            <a href="{{ route('calibration.verifications.index', ['plant' => $plantCode]) }}"
                class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Edit Verifikasi Alat</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.verifications.update', $verification->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pilih Alat Ukur</label>
                                <select name="tool_id" id="tool_select"
                                    class="form-control @error('tool_id') is-invalid @enderror" required>
                                    @foreach($tools as $tool)
                                        <option value="{{ $tool->id }}" 
                                            {{ $verification->tool_id == $tool->id ? 'selected' : '' }}
                                            data-name="{{ $tool->name_alat }}"
                                            data-serial="{{ $tool->serial_number }}" data-range="{{ $tool->range }}"
                                            data-resolusi="{{ $tool->resolusi }}"
                                            data-frekuensi="{{ $tool->frekuensi_kalibrasi }}"
                                            data-lokasi="{{ $tool->lokasi_pakai }}"
                                            data-schedules="{{ json_encode($tool->schedules->pluck('schedule_date')->map(fn($d) => $d->format('Y-m-d'))) }}">
                                            {{ $tool->name_alat }} ({{ $tool->serial_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tool_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label>Nama Alat</label>
                                <input type="text" name="name_alat" id="name_alat" class="form-control" 
                                    value="{{ $verification->name_alat }}" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Merk</label>
                                <input type="text" name="merk" class="form-control @error('merk') is-invalid @enderror"
                                    value="{{ old('merk', $verification->merk) }}" required>
                            </div>
                            <div class="form-group">
                                <label>No. Seri</label>
                                <input type="text" name="serial_number" id="serial_number" class="form-control" 
                                    value="{{ $verification->serial_number }}" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Rentang Ukur (Range)</label>
                                <input type="text" name="rentang_ukur" id="rentang_ukur" class="form-control" 
                                    value="{{ $verification->rentang_ukur }}" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Resolusi</label>
                                <input type="text" name="resolusi" id="resolusi" class="form-control" 
                                    value="{{ $verification->resolusi }}" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Frekuensi Kalibrasi</label>
                                <input type="text" name="frekuensi_kalibrasi" id="frekuensi_kalibrasi" class="form-control"
                                    value="{{ $verification->frekuensi_kalibrasi }}" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Lokasi Penyimpanan</label>
                                <input type="text" name="lokasi_penyimpanan" id="lokasi_penyimpanan" class="form-control"
                                    value="{{ $verification->lokasi_penyimpanan }}" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Kalibrasi</label>
                                <input type="date" name="tanggal_kalibrasi"
                                    class="form-control @error('tanggal_kalibrasi') is-invalid @enderror"
                                    value="{{ old('tanggal_kalibrasi', $verification->tanggal_kalibrasi ? $verification->tanggal_kalibrasi->format('Y-m-d') : '') }}" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Verifikasi</label>
                                <input type="date" name="tanggal_verifikasi"
                                    class="form-control @error('tanggal_verifikasi') is-invalid @enderror"
                                    value="{{ old('tanggal_verifikasi', $verification->tanggal_verifikasi ? $verification->tanggal_verifikasi->format('Y-m-d') : '') }}" required>
                            </div>
                            <div class="form-group">
                                <label>Next Kalibrasi</label>
                                <input type="date" name="next_kalibrasi"
                                    class="form-control @error('next_kalibrasi') is-invalid @enderror"
                                    value="{{ old('next_kalibrasi', $verification->next_kalibrasi ? $verification->next_kalibrasi->format('Y-m-d') : '') }}" required>
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
                                        @php
                                            $nilaiAlat = is_array($verification->nilai_alat) ? $verification->nilai_alat : (array) ($verification->nilai_alat ?? ['']);
                                            $nilaiKoreksi = is_array($verification->nilai_koreksi) ? $verification->nilai_koreksi : (array) ($verification->nilai_koreksi ?? ['']);
                                            $nilaiKetidakpastian = is_array($verification->nilai_ketidakpastian) ? $verification->nilai_ketidakpastian : (array) ($verification->nilai_ketidakpastian ?? ['']);
                                            $hasilVerifikasi = is_array($verification->hasil_verifikasi) ? $verification->hasil_verifikasi : (array) ($verification->hasil_verifikasi ?? ['']);
                                            $rowCount = count($nilaiAlat);
                                        @endphp
                                        @for($i = 0; $i < $rowCount; $i++)
                                        <tr>
                                            <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm" value="{{ $nilaiAlat[$i] ?? '' }}" required></td>
                                            <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm" value="{{ $nilaiKoreksi[$i] ?? '' }}" required></td>
                                            <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm" value="{{ $nilaiKetidakpastian[$i] ?? '' }}" required></td>
                                            <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm" value="{{ $hasilVerifikasi[$i] ?? '' }}" required></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-row" {{ $rowCount <= 1 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endfor
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
                                    <option value="OK" {{ $verification->judgment === 'OK' ? 'selected' : '' }}>OK</option>
                                    <option value="NG" {{ $verification->judgment === 'NG' ? 'selected' : '' }}>NG</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Std. Toleransi</label>
                                <input type="text" name="std_toleransi" class="form-control" 
                                    value="{{ old('std_toleransi', $verification->std_toleransi) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Acuan Toleransi</label>
                                <input type="text" name="acuan_toleransi" class="form-control" 
                                    value="{{ old('acuan_toleransi', $verification->acuan_toleransi) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Sertifikasi (Upload PDF Hasil Verifikasi)</label>
                        @if($verification->certification_path)
                            <div class="mb-2">
                                <a href="{{ asset('storage/' . $verification->certification_path) }}" target="_blank" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-file-pdf"></i> Lihat File Saat Ini
                                </a>
                            </div>
                        @endif
                        <input type="file" name="certification"
                            class="form-control-file @error('certification') is-invalid @enderror" accept=".pdf">
                        <small class="text-muted">Format PDF, Max 10MB. Biarkan kosong jika tidak ingin mengubah file.</small>
                        @error('certification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary px-4">Update Verifikasi</button>
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

                    // Only update next calibration if it's a new selection 
                    // or if we explicitly want to recalculate
                    // updateNextCalibrationDate();
                } else {
                    $('#name_alat, #serial_number, #rentang_ukur, #resolusi, #frekuensi_kalibrasi, #lokasi_penyimpanan').val('');
                }
            });

            $('input[name="tanggal_verifikasi"]').on('change', function() {
                updateNextCalibrationDate();
            });

            function updateNextCalibrationDate() {
                var selected = $('#tool_select').find('option:selected');
                var verifDate = $('input[name="tanggal_verifikasi"]').val();
                
                if (!selected.val() || !selected.data('schedules')) return;

                var schedules = selected.data('schedules');
                if (typeof schedules === 'string') {
                    schedules = JSON.parse(schedules);
                }

                if (schedules.length > 0) {
                    schedules.sort();
                    var referenceDate = verifDate || new Date().toISOString().split('T')[0];
                    var nextDate = schedules.find(date => date > referenceDate);
                    
                    if (nextDate) {
                        $('input[name="next_kalibrasi"]').val(nextDate);
                    }
                }
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
