@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Master Data Alat - Plant {{ strtoupper($plantCode) }}</h1>
            <a href="{{ route('calibration.tools.index', ['plant' => $plantCode]) }}"
                class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Edit Alat</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.tools.update', $tool->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bagian</label>
                                <input type="text" name="bagian" class="form-control @error('bagian') is-invalid @enderror"
                                    value="{{ old('bagian', $tool->bagian) }}" required>
                                @error('bagian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Nama Alat</label>
                                <input type="text" name="name_alat"
                                    class="form-control @error('name_alat') is-invalid @enderror"
                                    value="{{ old('name_alat', $tool->name_alat) }}" required>
                                @error('name_alat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>No. Seri</label>
                                <input type="text" name="serial_number"
                                    class="form-control @error('serial_number') is-invalid @enderror"
                                    value="{{ old('serial_number', $tool->serial_number) }}" required>
                                @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Range</label>
                                <input type="text" name="range" class="form-control @error('range') is-invalid @enderror"
                                    value="{{ old('range', $tool->range) }}" required>
                                @error('range') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Resolusi</label>
                                <input type="text" name="resolusi"
                                    class="form-control @error('resolusi') is-invalid @enderror"
                                    value="{{ old('resolusi', $tool->resolusi) }}" required>
                                @error('resolusi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lokasi Pakai</label>
                                <input type="text" name="lokasi_pakai"
                                    class="form-control @error('lokasi_pakai') is-invalid @enderror"
                                    value="{{ old('lokasi_pakai', $tool->lokasi_pakai) }}" required>
                                @error('lokasi_pakai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Tanggal Beli</label>
                                <input type="date" name="tanggal_beli"
                                    class="form-control @error('tanggal_beli') is-invalid @enderror"
                                    value="{{ old('tanggal_beli', $tool->tanggal_beli ? $tool->tanggal_beli->format('Y-m-d') : '') }}"
                                    required>
                                @error('tanggal_beli') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Frekuensi Kalibrasi</label>
                                <input type="text" name="frekuensi_kalibrasi"
                                    class="form-control @error('frekuensi_kalibrasi') is-invalid @enderror"
                                    value="{{ old('frekuensi_kalibrasi', $tool->frekuensi_kalibrasi) }}" required>
                                @error('frekuensi_kalibrasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Jenis Kalibrasi</label>
                                <input type="text" name="jenis_kalibrasi"
                                    class="form-control @error('jenis_kalibrasi') is-invalid @enderror"
                                    value="{{ old('jenis_kalibrasi', $tool->jenis_kalibrasi) }}" required>
                                @error('jenis_kalibrasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group" id="schedule-container">
                                <label>Schedule Planning (Tanggal Kalibrasi Selanjutnya)</label>
                                @php
                                    $schedules = $tool->schedules->pluck('schedule_date')->toArray();
                                    if (empty($schedules)) {
                                        $schedules = [$tool->schedule_planning]; // Fallback if no schedules record yet
                                    }
                                    if (old('schedule_planning')) {
                                        $schedules = old('schedule_planning');
                                    }
                                @endphp

                                @foreach($schedules as $index => $date)
                                    <div class="input-group mb-2">
                                        <input type="date" name="schedule_planning[]"
                                            class="form-control @error('schedule_planning.' . $index) is-invalid @enderror"
                                            value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}" required>
                                        <div class="input-group-append">
                                            @if($loop->first)
                                                <button class="btn btn-success" type="button" id="add-schedule">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-danger remove-schedule" type="button">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            @endif
                                        </div>
                                        @error('schedule_planning.' . $index) <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Riwayat Kalibrasi</label>
                        <textarea name="riwayat_kalibrasi"
                            class="form-control @error('riwayat_kalibrasi') is-invalid @enderror"
                            rows="3">{{ old('riwayat_kalibrasi', $tool->riwayat_kalibrasi) }}</textarea>
                        @error('riwayat_kalibrasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label>Sertifikasi (Upload PDF Baru untuk Mengganti)</label>
                        @if($tool->certification_path)
                            <div class="mb-2">
                                <a href="{{ asset('storage/' . $tool->certification_path) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i> Lihat Sertifikat Saat Ini
                                </a>
                            </div>
                        @endif
                        <input type="file" name="certification"
                            class="form-control-file @error('certification') is-invalid @enderror" accept=".pdf">
                        <small class="text-muted">Format PDF, Max 10MB.</small>
                        @error('certification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary px-4">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#add-schedule').click(function () {
                    var html = `
                            <div class="input-group mb-2">
                                <input type="date" name="schedule_planning[]" class="form-control" required>
                                <div class="input-group-append">
                                    <button class="btn btn-danger remove-schedule" type="button">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>`;
                    $('#schedule-container').append(html);
                });

                $(document).on('click', '.remove-schedule', function () {
                    $(this).closest('.input-group').remove();
                });
            });
        </script>
    @endpush
@endsection