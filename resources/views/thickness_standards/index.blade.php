@extends('layouts.admin')

@section('title', 'Master Data Thickness Standard')

@section('content')
<style>
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
        margin-bottom: 0 !important;
    }
    #dataTable, table.dataTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        border-top: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    
    #dataTable td, #dataTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
    }

    #dataTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.75rem !important;
        padding: 6px 8px !important;
    }

    #dataTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.7rem !important;
        letter-spacing: 0.2px;
        padding: 8px 12px !important;
        border-top: 1px solid #e2e8f0 !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #e2e8f0;
        top: 0 !important;
        z-index: 105 !important;
    }

    #dataTable.dataTable thead .sorting:before,
    #dataTable.dataTable thead .sorting:after,
    #dataTable.dataTable thead .sorting_asc:before,
    #dataTable.dataTable thead .sorting_asc:after,
    #dataTable.dataTable thead .sorting_desc:before,
    #dataTable.dataTable thead .sorting_desc:after {
        display: none !important;
    }
    #dataTable.dataTable thead th,
    #dataTable.dataTable thead .sorting,
    #dataTable.dataTable thead .sorting_asc,
    #dataTable.dataTable thead .sorting_desc {
        background-image: none !important;
        background-color: #f8fafc !important;
    }

    #dataTable .btn {
        min-width: 0 !important;
        padding: 0.2rem 0.4rem !important;
        font-size: 0.7rem !important;
        margin: 1px !important;
    }
</style>

@php
    $plantCode = strtolower($plantCode ?: 'jakarta');
@endphp

<div class="card shadow mb-2">
    <div class="card-body p-0">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                    <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                </td>
                <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                    <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                        MASTER DATA THICKNESS - {{ strtoupper($plantCode) }}
                    </h1>
                </td>
                <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                    <table style="border-collapse:collapse; font-size:0.68rem;">
                        <tr>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                            <td style="padding:1px 2px;">:</td>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">1 / 1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <!-- Filter Bar -->
        <form action="{{ route('admin.thickness-standards.index') }}" method="GET"
            class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
            style="gap: 12px; overflow-x: auto; white-space: nowrap;">

            <input type="hidden" name="plant" value="{{ $plantCode }}">

            <!-- Cari Cepat -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cari:</label>
                <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" 
                    placeholder="Part Name, Customer..." value="{{ request('search') }}" style="width: 200px; font-size: 0.75rem;">
            </div>

            <!-- Tombol Aksi -->
            <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Filter">
                    <i class="fas fa-search fa-sm"></i>
                </button>
                <a href="{{ route('admin.thickness-standards.index', ['plant' => $plantCode]) }}"
                    class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" title="Reset Filter">
                    <i class="fas fa-undo fa-sm"></i>
                </a>
                <button type="button" class="btn btn-success btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalImport" title="Import Excel">
                    <i class="fas fa-file-excel fa-sm mr-1"></i> Import
                </button>
                <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalTambah" title="Tambah Data">
                    <i class="fas fa-plus fa-sm"></i>
                </button>
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th rowspan="2">NO.</th>
                        <th rowspan="2">NAMA PART</th>
                        <th rowspan="2">CUSTOMER</th>
                        <th rowspan="2">STD CODE</th>
                        <th rowspan="2">STANDARD</th>
                        <th colspan="3" class="text-center">THICKNESS (mµ)</th>
                        <th rowspan="2">CORRODKOTE</th>
                        <th rowspan="2">CASS TEST</th>
                        <th rowspan="2">SALT SPRAY</th>
                        <th rowspan="2">PORECOUNT</th>
                        <th rowspan="2">CROSS CUT</th>
                        <th rowspan="2" class="no-export">AKSI</th>
                    </tr>
                    <tr>
                        <th>Cu</th>
                        <th>Ni</th>
                        <th>Cr</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standards as $index => $std)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-left font-weight-bold">{{ $std->part_name }}</td>
                            <td>{{ $std->customer }}</td>
                            <td>{{ $std->standard_code }}</td>
                            <td>{{ $std->standard_name }}</td>
                            <td>{{ $std->thickness_cu_std ?? '-' }}</td>
                            <td>{{ $std->thickness_ni_std ?? '-' }}</td>
                            <td>{{ $std->thickness_cr_std ?? '-' }}</td>
                            <td>{{ $std->corrodkote ?? '-' }}</td>
                            <td>{{ $std->cass_test ?? '-' }}</td>
                            <td>{{ $std->salt_spray_test ?? '-' }}</td>
                            <td>{{ $std->porecount_test ?? '-' }}</td>
                            <td>{{ $std->cross_cut_test ?? '-' }}</td>
                            <td class="no-export">
                                <div class="d-flex justify-content-center flex-wrap" style="gap: 2px;">
                                    <button type="button" class="btn btn-info btn-sm btn-edit shadow-sm" data-std="{{ json_encode($std) }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if(auth()->user()->role === 'admin')
                                    <form action="{{ route('admin.thickness-standards.destroy', $std->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm btn-delete" title="Hapus" onclick="return confirm('Hapus data ini?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.thickness-standards.store') }}" method="POST" id="formTambah">
            @csrf
            <input type="hidden" name="plant" value="{{ $plantCode }}">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalTitle">Tambah Master Data Thickness</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Nama Part <span class="text-danger">*</span></label>
                            <input type="text" name="part_name" id="part_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Customer</label>
                            <input type="text" name="customer" id="customer" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Standard Code</label>
                            <input type="text" name="standard_code" id="standard_code" class="form-control" placeholder="Misal: R2, R4">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Standard Name</label>
                            <input type="text" name="standard_name" id="standard_name" class="form-control" placeholder="Misal: HES D 6001-04A">
                        </div>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold mb-3">Thickness (mµ)</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small">Cu (Minimum)</label>
                            <input type="number" step="0.01" name="thickness_cu_std" id="thickness_cu_std" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small">Ni (Minimum)</label>
                            <input type="number" step="0.01" name="thickness_ni_std" id="thickness_ni_std" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold small">Cr (Minimum)</label>
                            <input type="number" step="0.01" name="thickness_cr_std" id="thickness_cr_std" class="form-control">
                        </div>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold mb-3">Testing & Others</h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Corrodkote</label>
                            <input type="text" name="corrodkote" id="corrodkote" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Cass Test</label>
                            <input type="text" name="cass_test" id="cass_test" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Salt Spray Test</label>
                            <input type="text" name="salt_spray_test" id="salt_spray_test" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small">Porecount Test</label>
                            <input type="text" name="porecount_test" id="porecount_test" class="form-control">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small">Cross Cut Test</label>
                            <input type="text" name="cross_cut_test" id="cross_cut_test" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.thickness-standards.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="plant" value="{{ $plantCode }}">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">Import Data Excel</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih File Excel (.xlsx, .xls)</label>
                        <input type="file" name="file" class="form-control-file" required accept=".xlsx, .xls">
                        <small class="form-text text-muted mt-2">
                            Pastikan format data mengikuti sheet <strong>"List Part (Std)"</strong> dari file referensi (baris data dimulai dari baris 6).
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-upload mr-1"></i> Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.btn-edit').click(function() {
            var std = $(this).data('std');
            $('#modalTitle').text('Edit Master Data Thickness');
            
            var url = '{{ route("admin.thickness-standards.update", ":id") }}';
            url = url.replace(':id', std.id);
            
            var form = $('#formTambah');
            form.attr('action', url);
            
            if (form.find('input[name="_method"]').length === 0) {
                form.prepend('<input type="hidden" name="_method" value="PUT">');
            }
            
            $('#part_name').val(std.part_name);
            $('#customer').val(std.customer);
            $('#standard_code').val(std.standard_code);
            $('#standard_name').val(std.standard_name);
            $('#thickness_cu_std').val(std.thickness_cu_std);
            $('#thickness_ni_std').val(std.thickness_ni_std);
            $('#thickness_cr_std').val(std.thickness_cr_std);
            $('#corrodkote').val(std.corrodkote);
            $('#cass_test').val(std.cass_test);
            $('#salt_spray_test').val(std.salt_spray_test);
            $('#porecount_test').val(std.porecount_test);
            $('#cross_cut_test').val(std.cross_cut_test);
            
            $('#modalTambah').modal('show');
        });

        $('#modalTambah').on('hidden.bs.modal', function () {
            var form = $('#formTambah');
            form.attr('action', '{{ route("admin.thickness-standards.store") }}');
            form.find('input[name="_method"]').remove();
            form[0].reset();
            $('#modalTitle').text('Tambah Master Data Thickness');
        });
    });
</script>
@endpush
