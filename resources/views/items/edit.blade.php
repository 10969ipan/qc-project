@extends('layouts.admin')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Item</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Plant</label>
                    <input type="text" class="form-control bg-light" value="{{ strtoupper($item->plant) }}" readonly>
                </div>
                <input type="hidden" name="page" value="{{ request('page', 1) }}">
                <input type="hidden" name="filter_name" value="{{ request('name') }}">
                <input type="hidden" name="filter_category" value="{{ request('category') }}">
                <input type="hidden" name="filter_customer" value="{{ request('customer') }}">
                <input type="hidden" name="filter_part_number" value="{{ request('part_number') }}">
                <input type="hidden" name="plant" value="{{ $item->plant }}">
                <div class="form-group">
                    <label>Nama Item</label>
                    <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                </div>
                <div class="form-group">
                    <label>Kategori <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                        <option value="" disabled>-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $item->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>File (PDF)</label>
                    @if($item->file_path)
                        <div class="mb-2">
                            <a href="{{ route('items.pdf', $item->id) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-file-pdf"></i> Lihat PDF Saat Ini
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
                    <label>Kode SAP</label>
                    <input type="text" name="sap_code" class="form-control @error('sap_code') is-invalid @enderror"
                        value="{{ $item->sap_code }}" placeholder="Opsional">
                    <small class="text-muted">Kode SAP harus unique jika diisi</small>
                    @error('sap_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>List Defect (Pisahkan dengan baris baru, biarkan kosong untuk default)</label>
                    <textarea name="defects" class="form-control"
                        rows="5">{{ $item->defects ? implode("\n", $item->defects) : '' }}</textarea>
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
                                @if($item->dimension_standards && count($item->dimension_standards) > 0)
                                    @foreach($item->dimension_standards as $standard)
                                        <tr>
                                            <td><input type="text" name="dimension_points[]" class="form-control"
                                                    value="{{ $standard['point'] ?? '' }}"></td>
                                            <td><input type="text" name="dimension_sizes[]" class="form-control"
                                                    value="{{ $standard['size'] ?? '' }}"></td>
                                            <td><input type="number" step="0.01" name="dimension_tolerances[]" class="form-control"
                                                    value="{{ $standard['tolerance'] ?? '' }}"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                        class="fas fa-trash"></i></button></td>
                                        </tr>
                                    @endforeach
                                @else
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
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.items.index', [
        'page' => request('page', 1),
        'name' => request('name'),
        'category' => request('category'),
        'customer' => request('customer'),
        'part_number' => request('part_number')
    ]) }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                // Add Row
                $(document).on('click', '.add-row', function () {
                    var newRow = `
                                                                <tr>
                                                                    <td><input type="text" name="dimension_points[]" class="form-control" placeholder="Contoh: 1, A"></td>
                                                                    <td><input type="text" name="dimension_sizes[]" class="form-control" placeholder="Contoh: 10.5"></td>
                                                                    <td><input type="number" step="0.01" name="dimension_tolerances[]" class="form-control" placeholder="Contoh: 0.1"></td>
                                                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                                                                </tr>
                                                            `;
                    $('#dimension-table tbody').append(newRow);
                });

                // Remove Row
                $(document).on('click', '.remove-row', function () {
                    // Provide a safer remove that respects existing logic but also allows clearing lines
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