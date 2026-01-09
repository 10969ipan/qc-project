@extends('layouts.admin')

@section('title', 'Edit Kategori')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Kategori</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $category->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Nama kategori harus unik</small>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Kategori ini memiliki
                    <strong>{{ $category->items()->count() }}</strong> item
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection