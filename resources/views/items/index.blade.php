@extends('layouts.admin')

@section('title', 'Master Data Items')

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
        /** @var \Illuminate\Support\ViewErrorBag $errors */
    @endphp
    @if(isset($errors) && method_exists($errors, 'any') && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
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
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12 mb-3">
                        <label for="name" class="font-weight-bold">Nama Item</label>
                        <input type="text" name="name" class="form-control form-control-sm shadow-sm"
                            value="{{ request('name') }}" placeholder="Cari Nama Item...">
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <label for="category" class="font-weight-bold">Kategori</label>
                        <select name="category" class="form-control form-control-sm shadow-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <label for="customer" class="font-weight-bold">Customer</label>
                        <input type="text" name="customer" class="form-control form-control-sm shadow-sm"
                            value="{{ request('customer') }}" placeholder="Cari Customer...">
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <label for="part_number" class="font-weight-bold">No Part</label>
                        <input type="text" name="part_number" class="form-control form-control-sm shadow-sm"
                            value="{{ request('part_number') }}" placeholder="Cari No Part...">
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <label for="sap_code" class="font-weight-bold">Kode SAP</label>
                        <input type="text" name="sap_code" class="form-control form-control-sm shadow-sm"
                            value="{{ request('sap_code') }}" placeholder="Kode SAP...">
                    </div>
                    {{-- Preserve plant parameter for all users --}}
                    @if(request('plant'))
                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                    @endif
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12 mb-3">
                        <label class="d-none d-xl-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-sm mr-2 shadow-sm">
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
                                <td>
                                    @if($item->file_path)
                                        <button type="button" class="btn btn-primary btn-sm view-pdf-btn" data-toggle="modal"
                                            data-target="#pdfModal" data-src="{{ route('items.pdf', $item->id) }}">
                                            <i class="fas fa-file-pdf"></i> Lihat
                                        </button>
                                    @else
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
                                            <input type="hidden" name="name" value="{{ request('name') }}">
                                            <input type="hidden" name="category" value="{{ request('category') }}">
                                            <input type="hidden" name="customer" value="{{ request('customer') }}">
                                            <input type="hidden" name="part_number" value="{{ request('part_number') }}">
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

    <!-- PDF Modal -->
    <!-- PDF Modal -->
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

    {{-- Modal Edit Item --}}
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
                    {{-- Preserve filters --}}
                    <input type="hidden" name="filter_plant" value="{{ $plantCode }}">
                    <input type="hidden" name="plant" id="edit_plant">

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
                                    <label class="font-weight-bold">Upload PDF Baru (Standard)</label>
                                    <input type="file" name="files[]" class="form-control-file form-control-sm"
                                        accept=".pdf" multiple>
                                    <small class="text-muted text-xs d-block">Bisa upload lebih dari satu file PDF. Max 10MB
                                        per file.</small>
                                    <div id="edit_existing_files" class="mt-2"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Similar Part PDF</label>
                                    <input type="file" name="similar_part_file" class="form-control-file form-control-sm"
                                        accept=".pdf">
                                    <small class="text-muted text-xs d-block">Upload PDF referensi part serupa. Max
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
                                                {{-- Will be filled by JS --}}
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
                                    <label class="font-weight-bold">Upload PDF Standard <span
                                            class="text-danger">*</span></label>
                                    <input type="file" name="files[]" class="form-control-file form-control-sm"
                                        accept=".pdf" multiple required>
                                    <small class="text-muted text-xs d-block">Bisa upload lebih dari satu file PDF. Max 10MB
                                        per file.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Upload Similar Part PDF</label>
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
        <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Auto-open modal if there are validation errors
                @if($errors->any())
                    @if(old('_method') === 'PUT')
                        // For Edit Modal, we might need the ID, but usually old input is enough to show what went wrong.
                        // However, since Edit is AJAX-based, it's safer to just handle Store for now 
                        // or check if a specific flag was set in session.
                    @else
                        $('#modalTambahItem').modal('show');
                    @endif
                @endif

                // Initialize PDF.js
                pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/vendor/pdf.worker.min.js') }}";

                let pdfDoc = null;
                let pageNum = 1;
                let pageRendering = false;
                let pageNumPending = null;
                let scale = 1.0;
                const canvas = document.getElementById('the-canvas');
                const ctx = canvas.getContext('2d');

                /**
                 * Get page info from document, resize canvas accordingly, and render page.
                 * @param num Page number.
                 */
                function renderPage(num) {
                    pageRendering = true;
                    // Fetch page
                    pdfDoc.getPage(num).then(function (page) {
                        const viewport = page.getViewport({ scale: scale });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        // Render PDF page into canvas context
                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };
                        const renderTask = page.render(renderContext);

                        // Wait for render to finish
                        renderTask.promise.then(function () {
                            pageRendering = false;
                            if (pageNumPending !== null) {
                                renderPage(pageNumPending);
                                pageNumPending = null;
                            }
                        });
                    });

                    // Update page counters
                    document.getElementById('pageInfo').textContent = 'Page ' + num + ' of ' + pdfDoc.numPages;
                }

                /**
                 * If another page rendering in progress, waits until the rendering is
                 * finised. Otherwise, executes rendering immediately.
                 */
                function queueRenderPage(num) {
                    if (pageRendering) {
                        pageNumPending = num;
                    } else {
                        renderPage(num);
                    }
                }

                /**
                 * Displays previous page.
                 */
                function onPrevPage() {
                    if (pageNum <= 1) {
                        return;
                    }
                    pageNum--;
                    queueRenderPage(pageNum);
                }
                document.getElementById('prevPage').addEventListener('click', onPrevPage);

                /**
                 * Displays next page.
                 */
                function onNextPage() {
                    if (pageNum >= pdfDoc.numPages) {
                        return;
                    }
                    pageNum++;
                    queueRenderPage(pageNum);
                }
                document.getElementById('nextPage').addEventListener('click', onNextPage);

                // Zoom controls
                document.getElementById('zoomIn').addEventListener('click', function () {
                    scale += 0.25;
                    queueRenderPage(pageNum);
                });

                document.getElementById('zoomOut').addEventListener('click', function () {
                    if (scale > 0.25) {
                        scale -= 0.25;
                        queueRenderPage(pageNum);
                    }
                });

                document.getElementById('zoomReset').addEventListener('click', function () {
                    scale = 1.0;
                    queueRenderPage(pageNum);
                });

                // Handle Modal Open
                $('.view-pdf-btn').on('click', function () {
                    const url = $(this).data('src');

                    // Reset state
                    pdfDoc = null;
                    pageNum = 1;
                    scale = 1.0;
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    document.getElementById('pageInfo').textContent = 'Loading...';

                    // Load PDF
                    pdfjsLib.getDocument(url).promise.then(function (pdfDoc_) {
                        pdfDoc = pdfDoc_;
                        document.getElementById('pageInfo').textContent = 'Page 1 of ' + pdfDoc.numPages;
                        renderPage(pageNum);
                    }, function (reason) {
                        // PDF loading error
                        console.error(reason);
                        let errorMsg = 'Error loading PDF. ';
                        if (reason.name === 'MissingPDFException') {
                            errorMsg += 'The PDF file could not be found on the server. Please contact admin or re-upload the file.';
                        } else {
                            errorMsg += reason.message || reason;
                        }

                        document.getElementById('pageInfo').textContent = 'Error: ' + reason.name;
                        alert(errorMsg);
                    });
                });
            });

            $(document).ready(function () {
                // Add Dimension Row
                $('.add-dimension-row').on('click', function () {
                    var newRow = `
                                                                                                                                    <tr>
                                                                                                                                        <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" placeholder="Contoh: 1, A"></td>
                                                                                                                                        <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm" placeholder="10.5"></td>
                                                                                                                                <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm" placeholder="9.9"></td>
                                                                                                                                        <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm" placeholder="10.1"></td>
                                                                                                                                        <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm" placeholder="0.1"></td>
                                                                                                                                        <td class="text-center">
                                                                                                                                            <button type="button" class="btn btn-xs btn-outline-danger remove-dimension-row">
                                                                                                                                                <i class="fas fa-trash"></i>
                                                                                                                                            </button>
                                                                                                                                        </td>
                                                                                                                                    </tr>`;
                    $('#modal-dimension-table tbody').append(newRow);
                });

                // Remove Dimension Row
                $(document).on('click', '.remove-dimension-row', function () {
                    var tableBody = $(this).closest('tbody');
                    if (tableBody.find('tr').length > 1) {
                        $(this).closest('tr').remove();
                    }
                });

                // Filter categories based on plant selection in modal
                $('#modal_plant_select').on('change', function () {
                    var selectedPlantUuid = $(this).find(':selected').data('uuid');
                    var categorySelect = $('#modal_category_select');

                    categorySelect.val('');
                    categorySelect.find('option').each(function () {
                        var optionPlant = $(this).data('plant');
                        if (!optionPlant || optionPlant == selectedPlantUuid) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });
            });

            // Edit Item Logic
            $('.btn-edit-item').on('click', function () {
                var id = $(this).data('id');
                var btn = $(this);

                // Show loading state if needed
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('admin.items.edit', ':id') }}".replace(':id', id),
                    type: 'GET',
                    success: function (response) {
                        var item = response.item;
                        $('#edit_name').val(item.name);
                        $('#edit_category_id').val(item.category_id);
                        $('#edit_customer').val(item.customer);
                        $('#edit_part_number').val(item.part_number);
                        $('#edit_sap_code').val(item.sap_code);
                        $('#edit_defects').val(response.defects_text);
                        $('#edit_plant').val(response.plant_code);


                        // Existing files
                        var filesHtml = '';
                        const viewPdfUrlTemplate = "{{ route('items.pdf', ['id' => '__ID__']) }}";

                        if (item.file_paths && item.file_paths.length > 0) {
                            item.file_paths.forEach(function (path, index) {
                                let viewPdfUrl = viewPdfUrlTemplate.replace('__ID__', item.id) + '/' + index;
                                filesHtml += `
                                                                                            <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small">
                                                                                                <span class="text-truncate mr-2" style="max-width: 150px;">${path.split('/').pop()}</span>
                                                                                                <a href="${viewPdfUrl}" target="_blank" class="badge badge-info mr-1">View</a>
                                                                                                <button type="button" class="badge badge-danger border-0 btn-delete-pdf" data-id="${item.id}" data-index="${index}" style="cursor: pointer;">Hapus</button>
                                                                                            </div>`;
                            });
                        } else if (item.file_path) {
                            let viewPdfUrl = viewPdfUrlTemplate.replace('__ID__', item.id);
                            filesHtml += `
                                                                                        <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small">
                                                                                            <span class="text-truncate mr-2" style="max-width: 150px;">${item.file_path.split('/').pop()}</span>
                                                                                            <a href="${viewPdfUrl}" target="_blank" class="badge badge-info mr-1">View</a>
                                                                                            <button type="button" class="badge badge-danger border-0 btn-delete-pdf" data-id="${item.id}" data-index="0" style="cursor: pointer;">Hapus</button>
                                                                                        </div>`;
                        }
                        $('#edit_existing_files').html(item.file_paths || item.file_path ? '<label class="small font-weight-bold mb-1">Standard PDFs:</label>' + filesHtml : '');

                        // Existing Similar Part PDF
                        var similarFileHtml = '';
                        if (item.similar_part_file_path) {
                            similarFileHtml = `
                                        <div class="d-flex align-items-center mb-1 p-1 border rounded bg-light x-small text-primary font-weight-bold">
                                            <span class="text-truncate mr-2" style="max-width: 150px;">${item.similar_part_file_path.split('/').pop()}</span>
                                            <a href="/public/${item.similar_part_file_path}" target="_blank" class="badge badge-info mr-1">View</a>
                                        </div>`;
                        }
                        $('#edit_existing_similar_file').html(similarFileHtml ? '<label class="small font-weight-bold mb-1">Similar Part PDF Terdaftar:</label>' + similarFileHtml : '');

                        // Dimensions
                        var dimHtml = '';
                        if (item.dimension_standards && item.dimension_standards.length > 0) {
                            item.dimension_standards.forEach(function (dim) {
                                dimHtml += `
                                                                                                                                        <tr>
                                                                                                                                            <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" value="${dim.point || ''}"></td>
                                                                                                                                            <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm" value="${dim.size || ''}"></td>
                                                                                                                                            <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm" value="${dim.min || ''}"></td>
                                                                                                                                            <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm" value="${dim.max || ''}"></td>
                                                                                                                                            <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm" value="${dim.tolerance || ''}"></td>
                                                                                                                                            <td class="text-center">
                                                                                                                                                <button type="button" class="btn btn-xs btn-outline-danger remove-dimension-row">
                                                                                                                                                    <i class="fas fa-trash"></i>
                                                                                                                                                </button>
                                                                                                                                            </td>
                                                                                                                                        </tr>`;
                            });
                        } else {
                            dimHtml = `
                                                                                                                                    <tr>
                                                                                                                                        <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" placeholder="Contoh: 1, A"></td>
                                                                                                                                        <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm" placeholder="10.5"></td>
                                                                                                                                <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm" placeholder="9.9"></td>
                                                                                                                                        <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm" placeholder="10.1"></td>
                                                                                                                                        <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm" placeholder="0.1"></td>
                                                                                                                                        <td class="text-center">
                                                                                                                                            <button type="button" class="btn btn-xs btn-outline-danger remove-dimension-row">
                                                                                                                                                <i class="fas fa-trash"></i>
                                                                                                                                            </button>
                                                                                                                                        </td>
                                                                                                                                    </tr>`;
                        }
                        $('#edit-modal-dimension-table tbody').html(dimHtml);

                        // Update form action
                        var url = "{{ route('admin.items.update', ':id') }}";
                        url = url.replace(':id', id);
                        $('#formEditItem').attr('action', url);

                        $('#modalEditItem').modal('show');
                        btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
                    },
                    error: function (xhr) {
                        var message = 'Gagal mengambil data item.';
                        if (xhr.status === 404) {
                            message = 'Item tidak ditemukan.';
                        } else if (xhr.status === 403) {
                            message = 'Anda tidak memiliki akses untuk mengedit item ini.';
                        } else if (xhr.status === 500) {
                            message = 'Terjadi kesalahan pada server saat mengambil data.';
                        }
                        alert(message);
                        btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
                    }
                });
            });

            // Delete PDF File
            $(document).on('click', '.btn-delete-pdf', function () {
                var btn = $(this);
                var id = btn.data('id');
                var index = btn.data('index');

                if (!confirm('Apakah Anda yakin ingin menghapus file ini?')) return;

                // Show loading state
                var originalText = btn.text();
                btn.text('...').prop('disabled', true);

                var deletePdfUrlTemplate = "{{ route('admin.items.delete-pdf', ['id' => '__ID__', 'index' => '__INDEX__']) }}";
                var url = deletePdfUrlTemplate.replace('__ID__', id).replace('__INDEX__', index);

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            btn.closest('div').remove();
                        } else {
                            alert('Gagal menghapus file: ' + response.message);
                            btn.text(originalText).prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Terjadi kesalahan saat menghapus file.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg += ' ' + xhr.responseJSON.message;
                        }
                        alert(msg);
                        btn.text(originalText).prop('disabled', false);
                    }
                });
            });

            // Add Dimension Row for Edit Modal
            $(document).on('click', '.add-edit-dimension-row', function () {
                var newRow = `
                                                                                                                        <tr>
                                                                                                                            <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" placeholder="Contoh: 1, A"></td>
                                                                                                                            <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm" placeholder="10.5"></td>
                                                                                                                            <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm" placeholder="9.9"></td>
                                                                                                                            <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm" placeholder="10.1"></td>
                                                                                                                            <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm" placeholder="0.1"></td>
                                                                                                                            <td class="text-center">
                                                                                                                                <button type="button" class="btn btn-xs btn-outline-danger remove-dimension-row">
                                                                                                                                    <i class="fas fa-trash"></i>
                                                                                                                                </button>
                                                                                                                            </td>
                                                                                                                        </tr>`;
                $('#edit-modal-dimension-table tbody').append(newRow);
            });
        </script>

        {{-- SweetAlert for Delete Confirmation --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Handle delete button clicks
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        const form = this.closest('form');

                        Swal.fire({
                            title: 'Apakah Anda yakin?',
                            text: "Data item ini akan dihapus permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection