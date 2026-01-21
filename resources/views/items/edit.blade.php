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
                    <input type="text" class="form-control bg-light" value="{{ strtoupper(optional($item->plant)->name) }}"
                        readonly>
                </div>
                <input type="hidden" name="page" value="{{ request('page', 1) }}">
                <input type="hidden" name="filter_name" value="{{ request('name') }}">
                <input type="hidden" name="filter_category" value="{{ request('category') }}">
                <input type="hidden" name="filter_customer" value="{{ request('customer') }}">
                <input type="hidden" name="filter_part_number" value="{{ request('part_number') }}">
                <input type="hidden" name="plant" value="{{ strtolower(optional($item->plant)->code) }}">
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
                    <div id="pdf-list" class="mb-3">
                        @if($item->file_paths && count($item->file_paths) > 0)
                            @foreach($item->file_paths as $index => $path)
                                <div class="d-flex align-items-center mb-2 p-2 border rounded bg-light pdf-item"
                                    data-index="{{ $index }}">
                                    <div class="mr-3">
                                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small font-weight-bold text-truncate" style="max-width: 300px;">
                                            {{ basename($path) }}
                                        </div>
                                        <a href="{{ route('items.pdf', ['id' => $item->id, 'index' => $index]) }}" target="_blank"
                                            class="badge badge-info mt-1">
                                            <i class="fas fa-eye"></i> Lihat PDF
                                        </a>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger delete-pdf-btn" data-id="{{ $item->id }}"
                                        data-index="{{ $index }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        @elseif($item->file_path)
                            <div class="d-flex align-items-center mb-2 p-2 border rounded bg-light pdf-item" data-index="0">
                                <div class="mr-3">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                </div>
                                <div class="flex-grow-1 text-truncate">
                                    <div class="small font-weight-bold">{{ basename($item->file_path) }}</div>
                                    <a href="{{ route('items.pdf', $item->id) }}" target="_blank" class="badge badge-info mt-1">
                                        <i class="fas fa-eye"></i> Lihat PDF
                                    </a>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger delete-pdf-btn" data-id="{{ $item->id }}"
                                    data-index="0">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @else
                            <p class="text-muted small">Belum ada file PDF.</p>
                        @endif
                    </div>

                    <label class="small font-weight-bold">Tambah File PDF Baru:</label>
                    <input type="file" name="files[]" class="form-control-file @error('files.*') is-invalid @enderror"
                        accept=".pdf" multiple>
                    <small class="text-muted">Format: PDF, Maksimal 10MB per file. Anda bisa memilih lebih dari satu file
                        sekaligus.</small>
                    @error('files.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
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
                // Delete PDF
                $(document).on('click', '.delete-pdf-btn', function () {
                    var btn = $(this);
                    var id = btn.data('id');
                    var index = btn.data('index');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "File PDF ini akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/admin/items/${id}/pdf/${index}`,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function (response) {
                                    if (response.success) {
                                        Swal.fire(
                                            'Terhapus!',
                                            response.message,
                                            'success'
                                        ).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire(
                                            'Gagal!',
                                            response.message,
                                            'error'
                                        );
                                    }
                                },
                                error: function (xhr) {
                                    Swal.fire(
                                        'Gagal!',
                                        'Terjadi kesalahan saat menghapus file.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection