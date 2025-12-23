@extends('layouts.admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Barang</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>File (PDF)</label>
                <input type="file" name="file" class="form-control-file" required>
            </div>
            <div class="form-group">
                <label>Customer</label>
                <textarea name="customer" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>No Part</label>
                <input type="text" name="part_number" class="form-control">
            </div>
            <div class="form-group">
                <label>Standard Cycle Time (detik)</label>
                <input type="number" name="standard_cycle_time" class="form-control" min="1" placeholder="Contoh: 15">
                <small class="form-text text-muted">Masukkan nilai target standar cycle time dalam satuan detik.</small>
            </div>
            <div class="form-group">
                <label>List Defect (Pisahkan dengan baris baru, biarkan kosong untuk default)</label>
                <textarea name="defects" class="form-control" rows="5" placeholder="Contoh:
Baret
Penyok
Warna Pudar"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
