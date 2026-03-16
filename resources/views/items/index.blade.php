@extends('layouts.admin')

@section('title', 'Master Data Item')

@section('content')
    <x-plant-header title="Master Data Items" :plant="$plantCode" />

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @php
    @endphp
    @if(isset($errors) && (is_object($errors) ? $errors->any() : (is_array($errors) && count($errors) > 0)))
        <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
            <ul class="mb-0">
                @foreach(is_object($errors) ? $errors->all() : $errors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif


    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Item</h6>
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                    data-target="#modalTambahItem">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Item
                </button>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('admin.items.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-10 col-sm-12 mb-3">
                        <label for="search" class="font-weight-bold">Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm shadow-sm"
                            value="{{ request('search') }}" placeholder="Cari...">
                    </div>
                    @if(request('plant'))
                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                    @endif
                    <div class="col-md-2 col-sm-12 mb-3">
                        <button type="submit" class="btn btn-primary btn-sm mr-1 shadow-sm">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('admin.items.index', ['plant' => request('plant')]) }}"
                            class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Standard</th>
                            <th>Nama Item</th>
                            <th>Kategori</th>
                            <th>Customer</th>
                            <th>No Part</th>
                            <th>Cavity</th>
                            <th>Kode SAP</th>
                            <th>Plant</th>
                            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            <tr>
                                <td>{{ $items->firstItem() + $index }}</td>
                                <td class="text-nowrap">
                                    @if($item->file_path)
                                        <button type="button" class="btn btn-primary btn-xs view-pdf-btn mr-1" data-toggle="modal"
                                            data-target="#pdfModal" data-src="{{ route('items.pdf', $item->id) }}?t={{ time() }}"
                                            title="Lihat PCCP">
                                            <i class="fas fa-file-pdf"></i> PCCP
                                        </button>
                                    @endif
                                    @if($item->similar_part_file_path)
                                        @php
                                            $catName = strtoupper($item->category->name ?? '');
                                            $isProcess = (str_contains($catName, 'INPROSES') || str_contains($catName, 'IN-PROCESS') || str_contains($catName, 'INPROCESS'));
                                            $standardLabel = $isProcess ? 'Dimensi' : 'Similar';
                                        @endphp
                                        <button type="button" class="btn btn-info btn-xs view-pdf-btn" data-toggle="modal"
                                            data-target="#pdfModal"
                                            data-src="{{ route('items.pdf', ['id' => $item->id, 'index' => 'similar']) }}?t={{ time() }}"
                                            title="Lihat {{ $standardLabel }} Part">
                                            <i class="fas fa-file-alt"></i> {{ $standardLabel }}
                                        </button>
                                    @endif
                                    @if(!$item->file_path && !$item->similar_part_file_path)
                                        <span class="text-muted">No File</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $item->name }}</td>
                                <td class="text-nowrap">
                                    @if($item->category)
                                        @php
                                            $badgeClass = match ($item->category->name) {
                                                'Sub Assy' => 'badge-primary',
                                                'Inprosess' => 'badge-success',
                                                'Cross Cut Plating' => 'badge-warning',
                                                'Cross Cut Painting' => 'badge-info',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $item->category->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $item->customer }}</td>
                                <td class="text-nowrap">{{ $item->part_number }}</td>
                                <td class="text-nowrap">{{ $item->cavity ?? 1 }}</td>
                                <td class="text-nowrap">{{ $item->sap_code ?? '-' }}</td>
                                <td>
                                    <span
                                        class="badge {{ optional($item->plant)->code === 'jakarta' ? 'badge-primary' : 'badge-info' }}">
                                        {{ strtoupper(optional($item->plant)->name ?? '-') }}
                                    </span>
                                </td>
                                @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-warning btn-sm btn-edit-item" data-id="{{ $item->id }}"
                                            style="min-width: 110px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                            <input type="hidden" name="search" value="{{ request('search') }}">
                                            <input type="hidden" name="name" value="{{ request('name') }}">
                                            <input type="hidden" name="category" value="{{ request('category') }}">
                                            <input type="hidden" name="customer" value="{{ request('customer') }}">
                                            <input type="hidden" name="part_number" value="{{ request('part_number') }}">
                                            <input type="hidden" name="sap_code" value="{{ request('sap_code') }}">
                                            <input type="hidden" name="plant" value="{{ request('plant') }}">
                                            <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                style="min-width: 110px;">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        @for($i = count($items); $i < 10; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                    <td>&nbsp;</td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>


    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Lihat PDF</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-2 align-items-center">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="prevPage">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="pageInfo" class="mr-2">Page 1 of ?</span>
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="nextPage">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div class="border-left pl-2 ml-2">
                            <button type="button" class="btn btn-primary btn-sm mr-2" id="zoomIn">
                                <i class="fas fa-search-plus"></i> Zoom In
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mr-2" id="zoomReset">
                                <i class="fas fa-sync-alt"></i> Reset
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="zoomOut">
                                <i class="fas fa-search-minus"></i> Zoom Out
                            </button>
                        </div>
                    </div>
                    <div class="text-center bg-dark" style="overflow: auto; max-height: 80vh;">
                        <canvas id="the-canvas" style="border: 1px solid black; direction: ltr;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalEditItem" tabindex="-1" role="dialog" aria-labelledby="modalEditItemLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditItemLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Master Data Item
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditItem" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="filter_plant" value="{{ request('plant') }}">
                    <input type="hidden" name="page" value="{{ request('page') }}">
                    <input type="hidden" name="filter_search" value="{{ request('search') }}">
                    <input type="hidden" name="filter_name" value="{{ request('name') }}">
                    <input type="hidden" name="filter_category" value="{{ request('category') }}">
                    <input type="hidden" name="filter_customer" value="{{ request('customer') }}">
                    <input type="hidden" name="filter_part_number" value="{{ request('part_number') }}">
                    <input type="hidden" name="filter_sap_code" value="{{ request('sap_code') }}">
                    <input type="hidden" name="plant" id="edit_plant">
                    <input type="hidden" name="item_id" id="edit_item_id">

                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger py-2 mb-3 small">
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Nama Item <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="edit_name" class="form-control form-control-sm"
                                        required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                    <select name="category_id" id="edit_category_id" class="form-control form-control-sm"
                                        required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Customer</label>
                                    <textarea name="customer" id="edit_customer" class="form-control form-control-sm"
                                        rows="2"></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">List Defect</label>
                                    <textarea name="defects" id="edit_defects" class="form-control form-control-sm"
                                        rows="3"></textarea>
                                    <small class="text-muted">Pisahkan setiap defect dengan baris baru.</small>
                                </div>
                            </div>
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Nomor Part</label>
                                    <input type="text" name="part_number" id="edit_part_number"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kode SAP</label>
                                    <input type="text" name="sap_code" id="edit_sap_code"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Cavity</label>
                                    <input type="number" name="cavity" id="edit_cavity" class="form-control form-control-sm"
                                        min="1">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Standar Berat (gr)</label>
                                    <input type="text" name="weight_standard" id="edit_weight_standard"
                                        class="form-control form-control-sm" placeholder="Contoh: 15.5">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Upload PDF Baru (Standard)</label>
                                    <input type="file" name="files[]" class="form-control-file form-control-sm"
                                        accept=".pdf" multiple>
                                    <small class="text-muted text-xs d-block">Bisa upload lebih dari satu file PDF. Max 10MB
                                        per file.</small>
                                    <div id="edit_existing_files" class="mt-2"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Similar / Dimensi Part PDF</label>
                                    <input type="file" name="similar_part_file" class="form-control-file form-control-sm"
                                        accept=".pdf">
                                    <small class="text-muted text-xs d-block">Upload PDF referensi dimensi part. Max
                                        10MB.</small>
                                    <div id="edit_existing_similar_file" class="mt-2"></div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small">Standar Dimensi In-Process</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0" id="edit-modal-dimension-table">
                                            <thead class="bg-light small">
                                                <tr class="text-center">
                                                    <th>Point/No</th>
                                                    <th>Standar</th>
                                                    <th>Min</th>
                                                    <th>Max</th>
                                                    <th>Toleransi (+/-)</th>
                                                    <th style="width: 30px;">
                                                        <button type="button"
                                                            class="btn btn-xs btn-success add-edit-dimension-row">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalTambahItem" tabindex="-1" role="dialog" aria-labelledby="modalTambahItemLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTambahItemLabel">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Master Data Item
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="filter_search" value="{{ request('search') }}">
                    <input type="hidden" name="filter_name" value="{{ request('name') }}">
                    <input type="hidden" name="filter_category" value="{{ request('category') }}">
                    <input type="hidden" name="filter_customer" value="{{ request('customer') }}">
                    <input type="hidden" name="filter_part_number" value="{{ request('part_number') }}">
                    <input type="hidden" name="filter_sap_code" value="{{ request('sap_code') }}">
                    <input type="hidden" name="page" value="{{ request('page') }}">
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger py-2 mb-3 small">
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(!$plantCode)
                            <div class="alert alert-warning py-2 mb-3 small">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Anda sedang di tampilan <strong>Total</strong>. Silakan pilih Plant tujuan item ini.
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Pilih Plant <span class="text-danger">*</span></label>
                                <select name="plant" class="form-control form-control-sm" required id="modal_plant_select">
                                    <option value="">-- Pilih Plant --</option>
                                    @foreach($allPlants as $p)
                                        <option value="{{ $p->code }}" data-uuid="{{ $p->id }}">{{ strtoupper($p->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="plant" value="{{ $plantCode }}">
                            <div class="alert alert-info py-2 px-3 mb-3 small">
                                <i class="fas fa-info-circle mr-1"></i>
                                Item akan otomatis didaftarkan untuk Plant: <strong>{{ strtoupper($plantCode) }}</strong>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Nama Item <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-sm" required
                                        placeholder="Masukkan Nama Item...">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                    <select name="category_id" id="modal_category_select"
                                        class="form-control form-control-sm" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" data-plant="{{ $cat->plant_id }}">{{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Customer</label>
                                    <textarea name="customer" class="form-control form-control-sm" rows="2"
                                        placeholder="Masukkan Nama Customer..."></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">List Defect</label>
                                    <textarea name="defects" class="form-control form-control-sm" rows="3"
                                        placeholder="Pisahkan setiap defect dengan baris baru"></textarea>
                                    <small class="text-muted">Biarkan kosong untuk menggunakan default defects.</small>
                                </div>
                            </div>
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Nomor Part</label>
                                    <input type="text" name="part_number" class="form-control form-control-sm"
                                        placeholder="Masukkan Nomor Part...">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kode SAP</label>
                                    <input type="text" name="sap_code" class="form-control form-control-sm"
                                        placeholder="Masukkan Kode SAP (Opsional)">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Cavity</label>
                                    <input type="number" name="cavity" class="form-control form-control-sm"
                                        placeholder="Jml Cavity" value="1" min="1">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Standar Berat (gr)</label>
                                    <input type="text" name="weight_standard" class="form-control form-control-sm"
                                        placeholder="Masukkan Standar Berat (gr)...">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Upload PDF Standard <span
                                            class="text-danger">*</span></label>
                                    <input type="file" name="files[]" class="form-control-file form-control-sm"
                                        accept=".pdf" multiple required>
                                    <small class="text-muted text-xs d-block">Bisa upload lebih dari satu file PDF. Max 10MB
                                        per file.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Upload Similar / Dimensi Part PDF</label>
                                    <input type="file" name="similar_part_file" class="form-control-file form-control-sm"
                                        accept=".pdf">
                                    <small class="text-muted text-xs d-block">Optional: PDF referensi part serupa. Max
                                        10MB.</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small">Standar Dimensi In-Process</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0" id="modal-dimension-table">
                                            <thead class="bg-light small">
                                                <tr class="text-center">
                                                    <th>Point/No</th>
                                                    <th>Standar</th>
                                                    <th>Min</th>
                                                    <th>Max</th>
                                                    <th>Toleransi (+/-)</th>
                                                    <th style="width: 30px;">
                                                        <button type="button"
                                                            class="btn btn-xs btn-success add-dimension-row">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" name="dimension_points[]"
                                                            class="form-control form-control-sm" placeholder="Contoh: 1, A">
                                                    </td>
                                                    <td><input type="text" name="dimension_sizes[]"
                                                            class="form-control form-control-sm" placeholder="10.5">
                                                    </td>
                                                    <td><input type="text" name="dimension_mins[]"
                                                            class="form-control form-control-sm" placeholder="9.9">
                                                    </td>
                                                    <td><input type="text" name="dimension_maxs[]"
                                                            class="form-control form-control-sm" placeholder="10.1">
                                                    </td>
                                                    <td><input type="text" name="dimension_tolerances[]"
                                                            class="form-control form-control-sm" placeholder="0.1">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                            class="btn btn-xs btn-outline-danger remove-dimension-row">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.__ITEMS__ = {
                routes: {
                    edit: "{{ route('admin.items.edit', ':id') }}",
                    update: "{{ route('admin.items.update', ':id') }}",
                    deletePdf: "{{ route('admin.items.delete-pdf', ['id' => '__ID__', 'index' => '__INDEX__']) }}",
                    deleteSimilarPdf: "{{ route('admin.items.delete-similar-pdf', ':id') }}",
                    viewPdf: "{{ route('items.pdf', ['id' => '__ID__']) }}",
                    pdfWorker: "{{ asset('js/vendor/pdf.worker.min.js') }}"
                },
                csrfToken: "{{ csrf_token() }}"
            };
        </script>
        <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
        <script src="{{ asset('js/items/items-pdf-viewer.js') }}"></script>
        <script src="{{ asset('js/items/items-form-logic.js') }}"></script>
        <script src="{{ asset('js/items/items-actions.js') }}"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if(isset($errors) && (is_object($errors) ? $errors->any() : (is_array($errors) && count($errors) > 0)))
                    @if(old('item_id'))
                        var itemId = "{{ old('item_id') }}";
                        var updateUrl = window.__ITEMS__.routes.update.replace(':id', itemId);
                        $('#formEditItem').attr('action', updateUrl);
                        $('#modalEditItem').modal('show');
                    @else
                        $('#modalTambahItem').modal('show');
                    @endif
                @endif
            });
        </script>
    @endpush
@endsection