@extends('layouts.admin')

@section('title', 'Tambah Item Master Data')

@section('content')


    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Item</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Nama Item <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>File (PDF) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control-file @error('file') is-invalid @enderror"
                        accept=".pdf" required>
                    <small class="text-muted">Format: PDF, Maksimal 5MB</small>
                    @error('file')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Customer</label>
                    <textarea name="customer" class="form-control @error('customer') is-invalid @enderror"
                        rows="3">{{ old('customer') }}</textarea>
                    @error('customer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>No Part</label>
                    <input type="text" name="part_number" class="form-control @error('part_number') is-invalid @enderror"
                        value="{{ old('part_number') }}">
                    @error('part_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>List Defect</label>
                    <textarea name="defects" class="form-control @error('defects') is-invalid @enderror" rows="5"
                        placeholder="Pisahkan setiap defect dengan baris baru">{{ old('defects') }}</textarea>
                    <small class="text-muted">Pisahkan setiap defect dengan baris baru. Biarkan kosong untuk menggunakan
                        default defects.</small>
                    @error('defects')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Standar Dimensi In-Process</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dimension-table">
                            <thead>
                                <tr>
                                    <th>Point/No</th>
                                    <th>Standar</th>
                                    <th>Toleransi (+/-)</th>
                                    <th width="50px"><button type="button" class="btn btn-success btn-sm add-row"><i
                                                class="fas fa-plus"></i></button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="dimension_points[]" class="form-control"
                                            placeholder="Contoh: 1, A"></td>
                                    <td><input type="text" name="dimension_sizes[]" class="form-control"
                                            placeholder="Contoh: 10.5"></td>
                                    <td><input type="number" step="0.01" name="dimension_tolerances[]" class="form-control"
                                            placeholder="Contoh: 0.1"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                class="fas fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            $(document).ready(function () {
                // Remove Row
                $(document).on('click', '.remove-row', function () {
                    if ($('#dimension-table tbody tr').length > 1) {
                        $(this).closest('tr').remove();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan',
                            text: 'Minimal satu baris harus ada.'
                        });
                    }
                });
            });
        </script>
    @endpush
@endsection