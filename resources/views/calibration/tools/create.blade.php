@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Tambah Master Data Alat - Plant {{ strtoupper($plantCode) }}</h1>
            <a href="{{ route('calibration.tools.index', ['plant' => $plantCode]) }}"
                class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Input Alat</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.tools.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bagian</label>
                                <input type="text" name="bagian" class="form-control @error('bagian') is-invalid @enderror"
                                    value="{{ old('bagian') }}" required>
                                @error('bagian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Nama Alat</label>
                                <input type="text" name="name_alat"
                                    class="form-control @error('name_alat') is-invalid @enderror"
                                    value="{{ old('name_alat') }}" required>
                                @error('name_alat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>No. Seri</label>
                                <input type="text" name="serial_number"
                                    class="form-control @error('serial_number') is-invalid @enderror"
                                    value="{{ old('serial_number') }}" required>
                                @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Range</label>
                                <input type="text" name="range" class="form-control @error('range') is-invalid @enderror"
                                    value="{{ old('range') }}" required>
                                @error('range') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Resolusi</label>
                                <input type="text" name="resolusi"
                                    class="form-control @error('resolusi') is-invalid @enderror"
                                    value="{{ old('resolusi') }}" required>
                                @error('resolusi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lokasi Pakai</label>
                                <input type="text" name="lokasi_pakai"
                                    class="form-control @error('lokasi_pakai') is-invalid @enderror"
                                    value="{{ old('lokasi_pakai') }}" required>
                                @error('lokasi_pakai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Tanggal Beli</label>
                                <input type="date" name="tanggal_beli"
                                    class="form-control @error('tanggal_beli') is-invalid @enderror"
                                    value="{{ old('tanggal_beli') }}" required>
                                @error('tanggal_beli') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Frekuensi Kalibrasi</label>
                                <input type="text" name="frekuensi_kalibrasi"
                                    class="form-control @error('frekuensi_kalibrasi') is-invalid @enderror"
                                    value="{{ old('frekuensi_kalibrasi') }}" placeholder="Contoh: 1 Tahun" required>
                                @error('frekuensi_kalibrasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Jenis Kalibrasi</label>
                                <input type="text" name="jenis_kalibrasi"
                                    class="form-control @error('jenis_kalibrasi') is-invalid @enderror"
                                    value="{{ old('jenis_kalibrasi') }}" required>
                                @error('jenis_kalibrasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group" id="schedule-container">
                                <label>Schedule Planning (Tanggal Kalibrasi Selanjutnya)</label>
                                <div class="input-group mb-2">
                                    <input type="date" name="schedule_planning[]"
                                        class="form-control @error('schedule_planning.0') is-invalid @enderror"
                                        value="{{ old('schedule_planning.0') }}" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-success" type="button" id="add-schedule">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    @error('schedule_planning.0') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if(old('schedule_planning'))
                                    @foreach(old('schedule_planning') as $index => $val)
                                        @if($index > 0)
                                            <div class="input-group mb-2">
                                                <input type="date" name="schedule_planning[]" class="form-control" value="{{ $val }}"
                                                    required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-danger remove-schedule" type="button">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Riwayat Kalibrasi</label>
                        <textarea name="riwayat_kalibrasi"
                            class="form-control @error('riwayat_kalibrasi') is-invalid @enderror"
                            rows="3">{{ old('riwayat_kalibrasi') }}</textarea>
                        @error('riwayat_kalibrasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label>Sertifikasi (Upload PDF)</label>
                        <input type="file" name="certification"
                            class="form-control-file @error('certification') is-invalid @enderror" accept=".pdf">
                        <small class="text-muted">Format PDF, Max 10MB.</small>
                        @error('certification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
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