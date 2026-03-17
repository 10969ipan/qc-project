@extends('layouts.admin')

@section('title', 'Upload Laporan Bulanan')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Upload Laporan Bulanan</h1>
            <a href="{{ route('admin.monthly-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Form Upload Laporan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.monthly-reports.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="month">Bulan <span class="text-danger">*</span></label>
                                <select name="month" id="month" class="form-control @error('month') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Bulan --</option>
                                    <option value="1" {{ old('month') == 1 ? 'selected' : '' }}>Januari</option>
                                    <option value="2" {{ old('month') == 2 ? 'selected' : '' }}>Februari</option>
                                    <option value="3" {{ old('month') == 3 ? 'selected' : '' }}>Maret</option>
                                    <option value="4" {{ old('month') == 4 ? 'selected' : '' }}>April</option>
                                    <option value="5" {{ old('month') == 5 ? 'selected' : '' }}>Mei</option>
                                    <option value="6" {{ old('month') == 6 ? 'selected' : '' }}>Juni</option>
                                    <option value="7" {{ old('month') == 7 ? 'selected' : '' }}>Juli</option>
                                    <option value="8" {{ old('month') == 8 ? 'selected' : '' }}>Agustus</option>
                                    <option value="9" {{ old('month') == 9 ? 'selected' : '' }}>September</option>
                                    <option value="10" {{ old('month') == 10 ? 'selected' : '' }}>Oktober</option>
                                    <option value="11" {{ old('month') == 11 ? 'selected' : '' }}>November</option>
                                    <option value="12" {{ old('month') == 12 ? 'selected' : '' }}>Desember</option>
                                </select>
                                @error('month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="year">Tahun <span class="text-danger">*</span></label>
                                <input type="number" name="year" id="year"
                                    class="form-control @error('year') is-invalid @enderror"
                                    value="{{ old('year', date('Y')) }}" min="2020" max="2100" required>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title">Judul Laporan <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" placeholder="Contoh: Laporan QC Januari 2026" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="pdf_file">File PDF <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="pdf_file" id="pdf_file"
                                class="custom-file-input @error('pdf_file') is-invalid @enderror" accept=".pdf" required>
                            <label class="custom-file-label" for="pdf_file">Pilih file PDF...</label>
                        </div>
                        <small class="form-text text-muted">Maksimal ukuran file: 10MB</small>
                        @error('pdf_file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1" {{ old('is_active') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">
                                Tampilkan di Dashboard
                            </label>
                        </div>
                        <small class="form-text text-muted">Jika dicentang, laporan ini akan ditampilkan di halaman
                            dashboard</small>
                    </div>

                    <hr>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Laporan
                        </button>
                        <a href="{{ route('admin.monthly-reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Update file input label with selected filename
        document.querySelector('.custom-file-input').addEventListener('change', function (e) {
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    </script>
@endsection
