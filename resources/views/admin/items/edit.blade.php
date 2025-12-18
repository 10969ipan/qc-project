@extends('layouts.admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Barang</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
            </div>
            <div class="form-group">
                <label>File (PDF)</label>
                @if($item->file_path)
                    <div class="mb-2">
                        <a href="{{ asset($item->file_path) }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-file-pdf"></i> Lihat PDF
                        </a>
                    </div>
                @endif
                <input type="file" name="file" class="form-control-file">
                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah file</small>
            </div>
            <div class="form-group">
                <label>Customer</label>
                <textarea name="customer" class="form-control" rows="3">{{ $item->customer }}</textarea>
            </div>
            <div class="form-group">
                <label>No Part</label>
                <input type="text" name="part_number" class="form-control" value="{{ $item->part_number }}">
            </div>
            <div class="form-group">
                <label>List Defect (Pisahkan dengan baris baru, biarkan kosong untuk default)</label>
                <textarea name="defects" class="form-control" rows="5">{{ $item->defects ? implode("\n", $item->defects) : '' }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
