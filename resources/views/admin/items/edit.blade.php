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
                    <label>Nama Item</label>
                    <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                </div>
                <div class="form-group">
                    <label>File (PDF)</label>
                    @if($item->file_path)
                        <div class="mb-2">
                            <a href="{{ url('/storage/' . $item->file_path) }}" target="_blank" class="btn btn-sm btn-info">
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
                    <textarea name="defects" class="form-control"
                        rows="5">{{ $item->defects ? implode("\n", $item->defects) : '' }}</textarea>
                </div>

                <div class="form-group">
                    <label>Standar Dimensi In-Process</label>
                    <div class="mb-2 d-flex align-items-center">
                        <select class="form-control mr-2" id="dimension_preset">
                            <option value="">-- Pilih Standar (Opsional) --</option>
                            @foreach($partDimensionStandards as $key => $standards)
                                <option value="{{ $key }}">{{ $key }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-info btn-sm" id="load_preset">Load</button>
                    </div>
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
                <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                // Add Row
                $('.add-row').on('click', function () {
                    var html = '';
                    html += '<tr>';
                    html += '<td><input type="text" name="dimension_points[]" class="form-control" placeholder="Contoh: 1, A"></td>';
                    html += '<td><input type="text" name="dimension_sizes[]" class="form-control" placeholder="Contoh: 10.5"></td>';
                    html += '<td><input type="number" step="0.01" name="dimension_tolerances[]" class="form-control" placeholder="Contoh: 0.1"></td>';
                    html += '<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>';
                    html += '</tr>';
                    $('#dimension-table tbody').append(html);
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

                // Load Preset
                var standardsData = @json($partDimensionStandards);
                $('#load_preset').on('click', function () {
                    var selectedKey = $('#dimension_preset').val();
                    if (selectedKey && standardsData[selectedKey]) {
                        Swal.fire({
                            title: 'Konfirmasi Load Standar',
                            text: "Ini akan mengganti semua data tabel dimensi saat ini. Lanjutkan?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Lanjutkan!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var data = standardsData[selectedKey];
                                var html = '';
                                $.each(data, function (index, item) {
                                    html += '<tr>';
                                    html += '<td><input type="text" name="dimension_points[]" class="form-control" value="' + index + '"></td>';
                                    html += '<td><input type="text" name="dimension_sizes[]" class="form-control" value="' + item.size + '"></td>';
                                    html += '<td><input type="number" step="0.01" name="dimension_tolerances[]" class="form-control" value="' + item.tolerance + '"></td>';
                                    html += '<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>';
                                    html += '</tr>';
                                });
                                $('#dimension-table tbody').html(html);
                                Swal.fire(
                                    'Berhasil!',
                                    'Standar dimensi telah dimuat.',
                                    'success'
                                )
                            }
                        })
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Silakan pilih standar terlebih dahulu.'
                        });
                    }
                });
            });
        </script>
    @endpush
@endsection