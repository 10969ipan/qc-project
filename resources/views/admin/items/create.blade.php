@extends('layouts.admin')

@section('title', 'Tambah Barang Master Data')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Barang Master Data</h1>
</div>

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
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Barang</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Nama Barang <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label>File (PDF) <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control-file @error('file') is-invalid @enderror" accept=".pdf" required>
                <small class="text-muted">Format: PDF, Maksimal 5MB</small>
                @error('file')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label>Customer</label>
                <textarea name="customer" class="form-control @error('customer') is-invalid @enderror" rows="3">{{ old('customer') }}</textarea>
                @error('customer')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label>No Part</label>
                <input type="text" name="part_number" class="form-control @error('part_number') is-invalid @enderror" value="{{ old('part_number') }}">
                @error('part_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label>List Defect</label>
                <textarea name="defects" class="form-control @error('defects') is-invalid @enderror" rows="5" placeholder="Pisahkan setiap defect dengan baris baru">{{ old('defects') }}</textarea>
                <small class="text-muted">Pisahkan setiap defect dengan baris baru. Biarkan kosong untuk menggunakan default defects.</small>
                @error('defects')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
@endsection
