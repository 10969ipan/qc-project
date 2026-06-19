@extends('layouts.admin')

@section('title', 'Master Data Item')

@section('content')
<style>
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }
    #checksheetTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    
    #checksheetTable td, #checksheetTable th {
        border-left: none !important;
        border-right: none !important;
    }

    #checksheetTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.68rem !important;
        padding: 4px 6px !important;
    }

    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 6px 12px !important;
        border: none !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    #checksheetTable .btn {
        min-width: 0 !important; /* Overrides inline style */
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
    #checksheetTable .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
    }

    /* Sticky Header */
    #checksheetTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 35px !important; 
    }
</style>
    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:1.15rem; letter-spacing:0.3px;">
                            MASTER DATA - PLANT {{ strtoupper($plantCode ?? ($currentPlant->name ?? '')) }}
                        </h1>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('import_warnings'))
        <div class="alert alert-warning alert-dismissible fade show mx-3" role="alert" style="max-height: 250px; overflow-y: auto;">
            <strong>Peringatan Import:</strong>
            <ul class="mb-0 pl-3 small">
                @foreach(session('import_warnings') as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

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
                <div class="d-flex" style="gap: 10px;">
                    <button type="button" class="btn btn-sm btn-success shadow-sm" data-toggle="modal"
                        data-target="#modalImportItem">
                        <i class="fas fa-file-excel fa-sm text-white-50"></i> Import Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                        data-target="#modalTambahItem">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Item
                    </button>
                </div>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('admin.items.index') }}" method="GET"
                class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 10px;" id="filterFormItems">
                
                <input type="hidden" name="plant" value="{{ request('plant') }}">
                <input type="hidden" name="f_search" id="hiddenSearchInput" value="{{ request('f_search', request('search')) }}">

                <!-- Field: Part (Dropdown Item Search) -->
                <div class="d-flex align-items-center" style="min-width: 280px; max-width: 400px;">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Part:</label>
                    <div class="custom-filter-wrapper flex-grow-1">
                        <select name="f_item_id" id="filterItem" class="form-control form-control-sm border-0 shadow-sm d-none">
                            <option value="">Semua Item / Part No.</option>
                            @foreach($allItemsList as $itm)
                                <option value="{{ $itm->id }}" 
                                    data-name="{{ $itm->name }}" 
                                    data-part-number="{{ $itm->part_number }}"
                                    data-customer="{{ $itm->customer }}"
                                    data-sap-code="{{ $itm->sap_code }}"
                                    data-detail="{{ optional($itm->category)->name }}"
                                    {{ request('f_item_id') == $itm->id ? 'selected' : '' }}>
                                    {{ $itm->name }} {{ $itm->part_number ? '- '.$itm->part_number : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Field: Category -->
                <div class="d-flex align-items-center" style="min-width: 180px;">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Kategori:</label>
                    <select name="category" id="filterCategory" class="form-control form-control-sm border-0 shadow-sm select2-standard" style="width: 150px;">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Field: Customer -->
                <div class="d-flex align-items-center" style="min-width: 200px;">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Customer:</label>
                    <select name="customer" id="filterCustomer" class="form-control form-control-sm border-0 shadow-sm select2-standard" style="width: 180px;">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust }}" {{ request('customer') == $cust ? 'selected' : '' }}>
                                {{ $cust }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="ml-auto d-flex" style="gap: 5px;">
                    <style>
                        .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
                        .custom-filter-wrapper .ips-input { padding: 4px 20px 4px 8px; font-size: 0.75rem; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: calc(1.5em + 0.5rem + 2px); }
                        .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
                        .custom-filter-wrapper { position: relative; top: -1px; }
                    </style>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Cari Data">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                    <a href="{{ route('admin.items.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                </div>
            </form>

            @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Sync the proxy input text for broad search capability
                    setTimeout(function() {
                        const proxyInput = document.querySelector('.custom-filter-wrapper .ips-input');
                        const hiddenSearch = document.getElementById('hiddenSearchInput');
                        const itemSelect = document.getElementById('filterItem');

                        if (proxyInput && hiddenSearch) {
                            // If we have a search value but no item_id, populate the proxy input for visual consistency
                            if (hiddenSearch.value && !itemSelect.value) {
                                proxyInput.value = hiddenSearch.value;
                            }

                            proxyInput.addEventListener('input', function() {
                                hiddenSearch.value = this.value;
                            });

                            // Handle clear button click
                            document.addEventListener('mousedown', function(e) {
                                if (e.target.classList.contains('ips-clear') && e.target.closest('.custom-filter-wrapper')) {
                                    hiddenSearch.value = '';
                                }
                            });
                        }
                    }, 500); 
                });
            </script>
            @endpush

            <div class="table-responsive">
                <table id="checksheetTable" class="table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th style="width:1%; white-space:nowrap;">Standard</th>
                            <th>Nama Item</th>
                            <th>Kategori</th>
                            <th>Customer</th>
                            <th>No Part</th>
                            <th>Cavity</th>
                            <th class="text-primary"><i class="fas fa-stopwatch mr-1"></i> SCT (Plating)</th>
                            <th>Kode SAP</th>
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
                                    @php
                                        $filePaths = $item->file_paths ?? [];
                                        if (empty($filePaths) && $item->file_path) {
                                            $filePaths = [$item->file_path];
                                        }
                                        $fileUrls = [];
                                        foreach ($filePaths as $fIdx => $fPath) {
                                            $fileUrls[] = route('items.pdf', ['id' => $item->id, 'index' => $fIdx]) . '?t=' . time();
                                        }
                                    @endphp

                                    @if(count($fileUrls) > 0)
                                        <button type="button" class="btn btn-primary btn-xs view-pdf-btn mr-1 mb-1"
                                            data-toggle="modal" data-target="#pdfModal"
                                            data-src="{{ $fileUrls[0] }}"
                                            data-files="{{ json_encode($fileUrls) }}"
                                            title="Lihat PCCP ({{ count($fileUrls) }} file)">
                                            <i class="fas fa-file-pdf"></i> PCCP
                                            @if(count($fileUrls) > 1)
                                                <span class="badge badge-light ml-1">{{ count($fileUrls) }}</span>
                                            @endif
                                        </button>
                                    @endif

                                    @if($item->similar_part_file_path)
                                        @php
                                            $catName = strtoupper($item->category->name ?? '');
                                            $isProcess = (str_contains($catName, 'INPROSES') || str_contains($catName, 'IN-PROCESS') || str_contains($catName, 'INPROCESS'));
                                            $standardLabel = $isProcess ? 'Dimensi' : 'Similar';
                                        @endphp
                                        <button type="button" class="btn btn-info btn-xs view-pdf-btn mb-1" data-toggle="modal"
                                            data-target="#pdfModal"
                                            data-src="{{ route('items.pdf', ['id' => $item->id, 'index' => 'similar']) }}?t={{ time() }}"
                                            data-files="{{ json_encode([route('items.pdf', ['id' => $item->id, 'index' => 'similar']) . '?t=' . time()]) }}"
                                            title="Lihat {{ $standardLabel }} Part">
                                            <i class="fas fa-file-alt"></i> {{ $standardLabel }}
                                        </button>
                                    @endif

                                    @if(empty($fileUrls) && !$item->similar_part_file_path)
                                        <span class="text-muted">No File</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $item->name }}</td>
                                <td class="text-nowrap">
                                    @if($item->category)
                                        {{ $item->category->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $item->customer }}</td>
                                <td class="text-nowrap">{{ $item->part_number }}</td>
                                <td class="text-nowrap">{{ $item->cavity ?? 1 }}</td>
                                <td class="text-nowrap font-weight-bold text-primary">
                                    @if($item->standard_cycle_time > 0)
                                        {{ number_format($item->standard_cycle_time, 2) }}m
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $item->sap_code ?? '-' }}</td>
                                @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                    <td class="no-export">
                                        <button type="button" class="btn btn-warning btn-sm btn-edit-item" data-id="{{ $item->id }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm btn-log-item" data-id="{{ $item->id }}">
                                            <i class="fas fa-history"></i> Log
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
                                            <button type="button" class="btn btn-danger btn-sm delete-btn">
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


    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Lihat PDF</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- File navigation (shown only when multiple files) --}}
                    <div id="pdfFileNav" class="d-none d-flex justify-content-center align-items-center mb-2 py-1 px-2 bg-light rounded" style="gap:8px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="prevFile">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="fileInfo" class="small font-weight-bold text-muted">File 1 / 1</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="nextFile">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    {{-- Page navigation --}}
                    <div class="d-flex justify-content-center mb-2 align-items-center">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="prevPage">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="pageInfo" class="mr-2">Page 1 of ?</span>
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="nextPage">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div class="border-left pl-2 ml-2 d-flex align-items-center">
                            <button type="button" class="btn btn-primary btn-sm mr-2" id="zoomIn">
                                <i class="fas fa-search-plus"></i> Zoom In
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mr-2" id="zoomReset">
                                <i class="fas fa-sync-alt"></i> Reset
                            </button>
                            <button type="button" class="btn btn-primary btn-sm mr-2" id="zoomOut">
                                <i class="fas fa-search-minus"></i> Zoom Out
                            </button>
                            <a id="downloadPdfBtn" href="#" class="btn btn-success btn-sm ml-1" download>
                                <i class="fas fa-download"></i> Download PDF
                            </a>
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
        <div class="modal-dialog modal-xl" role="document" style="max-width: 1200px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white text-dark" style="border-bottom: 1px solid #f1f5f9;">
                    <h5 class="modal-title font-weight-bold" id="modalEditItemLabel" style="font-size: 1.1rem; letter-spacing: 0.2px;">
                        Edit Master Data Item
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
                                    <input type="text" name="name" id="edit_name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                    <select name="category_id" id="edit_category_id" class="form-control form-control-sm @error('category_id') is-invalid @enderror"
                                        required>
                                        <option value="">Pilih Kategori</option>
                                        @if(!$plantCode)
                                            @foreach($categories->groupBy('plant_id') as $pId => $group)
                                                <optgroup label="{{ strtoupper(optional($group->first()->plant)->name ?? 'N/A') }}">
                                                    @foreach($group as $cat)
                                                        <option value="{{ $cat->id }}" data-plant="{{ $cat->plant_id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                            {{ $cat->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @else
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" data-plant="{{ $cat->plant_id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Customer</label>
                                    <textarea name="customer" id="edit_customer" class="form-control form-control-sm"
                                        rows="2"></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">List Defect</label>
                                    <textarea name="defects" id="edit_defects" class="form-control form-control-sm"
                                        rows="18"></textarea>
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
                                <div class="form-group mb-3 sct-field-wrapper" style="display: none;">
                                    <label class="font-weight-bold text-primary"><i class="fas fa-stopwatch mr-1"></i> Standar Cycletime Plating (menit)</label>
                                    <input type="number" step="0.01" name="standard_cycle_time" id="edit_standard_cycle_time"
                                        class="form-control form-control-sm" placeholder="Contoh: 0.16">
                                    <small class="text-muted">Khusus untuk kategori PLATING. Masukkan dalam satuan MENIT.</small>
                                </div>
                                {{-- PDF Upload: Horizontal side-by-side layout --}}
                                <div class="d-flex mb-3" style="border: 1px solid #e9ecef; border-radius: 6px;">
                                    {{-- Left: Standard PDF --}}
                                    <div class="p-3" style="flex: 1; min-width: 0; border-right: 2px solid #e9ecef; overflow: hidden;">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> Upload PDF Baru (Standard)
                                        </label>
                                        <input type="file" id="edit_files_input" name="files[]" class="form-control-file form-control-sm"
                                            accept=".pdf" multiple>
                                        <small class="text-muted text-xs d-block mt-1">Bisa upload lebih dari satu file PDF. Max 10MB per file.</small>
                                        <div id="edit_existing_files" class="mt-2"></div>
                                        <div id="edit_preview_new_files" class="mt-1"></div>
                                    </div>
                                    {{-- Right: Similar / Dimensi Part PDF --}}
                                    <div class="p-3" style="flex: 1; min-width: 0;">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-alt text-info mr-1"></i> Similar / Dimensi Part PDF
                                        </label>
                                        <input type="file" id="edit_similar_input" name="similar_part_file" class="form-control-file form-control-sm"
                                            accept=".pdf">
                                        <small class="text-muted text-xs d-block mt-1">Upload PDF referensi dimensi part. Max 10MB.</small>
                                        <div id="edit_existing_similar_file" class="mt-2"></div>
                                        <div id="edit_preview_new_similar" class="mt-1"></div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small">Standar Dimensi In-Process</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0" id="edit-modal-dimension-table">
                                            <thead class="bg-white text-dark small">
                                                <tr class="text-center">
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Point/No</th>
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Standar</th>
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Min</th>
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Max</th>
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Toleransi (+/-)</th>
                                                    <th style="width: 30px; background-color: #ffffff !important; color: #333 !important;">
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
                    <div class="modal-footer bg-white border-top-0 p-3" style="border-top: 1px solid #f1f5f9 !important;">
                        <button type="button" class="btn btn-light btn-sm shadow-sm px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                            Update Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalImportItem" tabindex="-1" role="dialog" aria-labelledby="modalImportItemLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white text-dark" style="border-bottom: 1px solid #f1f5f9;">
                    <h5 class="modal-title font-weight-bold" id="modalImportItemLabel" style="font-size: 1.1rem; letter-spacing: 0.2px;">
                        Import Master Data Item
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.items.import') }}" method="POST" enctype="multipart/form-data" id="formImportItem">
                    @csrf
                    <div class="modal-body text-left">
                        @if(!$plantCode)
                            <div class="alert alert-warning py-2 mb-3 small">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Anda sedang di tampilan <strong>Total</strong>. Silakan pilih Plant tujuan import ini.
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Pilih Plant <span class="text-danger">*</span></label>
                                <select name="plant" class="form-control form-control-sm" required>
                                    <option value="">-- Pilih Plant --</option>
                                    @foreach($allPlants as $p)
                                        <option value="{{ $p->code }}">{{ strtoupper($p->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="plant" value="{{ $plantCode }}">
                            <div class="alert alert-info py-2 px-3 mb-3 small">
                                <i class="fas fa-info-circle mr-1"></i>
                                Item akan diimport untuk Plant: <strong>{{ strtoupper($plantCode) }}</strong>
                            </div>
                        @endif

                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Pilih File Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control-file form-control-sm" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted text-xs d-block mt-1">Format file yang didukung: .xlsx, .xls, .csv. Ukuran maks 5MB.</small>
                        </div>

                        <div class="border rounded p-3 bg-light mb-3">
                            <h6 class="font-weight-bold text-gray-800 small mb-2"><i class="fas fa-info-circle mr-1"></i> Petunjuk Import:</h6>
                            <ul class="pl-3 mb-0 text-xs text-muted" style="line-height: 1.5;">
                                <li>Gunakan template Excel resmi yang disediakan agar format kolom sesuai.</li>
                                <li>Kolom <strong>Nama Item</strong> dan <strong>Kategori</strong> wajib diisi.</li>
                                <li>Kategori baru akan otomatis terbuat jika belum ada di sistem.</li>
                                <li>Jika <strong>Nomor Part</strong>, <strong>Kode SAP</strong>, atau <strong>Nama Item</strong> sudah ada di Plant & Kategori yang sama, data item tersebut akan <strong>diperbarui (updated)</strong>.</li>
                            </ul>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('admin.items.import-template', ['plant' => $plantCode ?? request('plant')]) }}" class="btn btn-sm btn-outline-primary font-weight-bold px-3">
                                Unduh Template Excel
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top-0 p-3" style="border-top: 1px solid #f1f5f9 !important;">
                        <button type="button" class="btn btn-light btn-sm shadow-sm px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm" id="btnSubmitImport">
                            Mulai Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalTambahItem" tabindex="-1" role="dialog" aria-labelledby="modalTambahItemLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 1200px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white text-dark" style="border-bottom: 1px solid #f1f5f9;">
                    <h5 class="modal-title font-weight-bold" id="modalTambahItemLabel" style="font-size: 1.1rem; letter-spacing: 0.2px;">
                        Tambah Master Data Item
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                                <select name="plant" class="form-control form-control-sm @error('plant') is-invalid @enderror" required id="modal_plant_select">
                                    <option value="">-- Pilih Plant --</option>
                                    @foreach($allPlants as $p)
                                        <option value="{{ $p->code }}" data-uuid="{{ $p->id }}" {{ old('plant') == $p->code ? 'selected' : '' }}>{{ strtoupper($p->name) }}</option>
                                    @endforeach
                                </select>
                                @error('plant')
                                    <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                @enderror
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
                                    <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" required
                                        value="{{ old('name') }}" placeholder="Masukkan Nama Item...">
                                    @error('name')
                                        <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                    <select name="category_id" id="modal_category_select"
                                        class="form-control form-control-sm @error('category_id') is-invalid @enderror" required>
                                        <option value="">Pilih Kategori</option>
                                        @if(!$plantCode)
                                            @foreach($categories->groupBy('plant_id') as $pId => $group)
                                                <optgroup label="{{ strtoupper(optional($group->first()->plant)->name ?? 'N/A') }}">
                                                    @foreach($group as $cat)
                                                        <option value="{{ $cat->id }}" data-plant="{{ $cat->plant_id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                            {{ $cat->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @else
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" data-plant="{{ $cat->plant_id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Customer</label>
                                    <textarea name="customer" class="form-control form-control-sm" rows="2"
                                        placeholder="Masukkan Nama Customer..."></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">List Defect</label>
                                    <textarea name="defects" class="form-control form-control-sm" rows="18"
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
                                <div class="form-group mb-3 sct-field-wrapper" style="display: none;">
                                    <label class="font-weight-bold text-primary"><i class="fas fa-stopwatch mr-1"></i> Standar Cycletime Plating (menit)</label>
                                    <input type="number" step="0.01" name="standard_cycle_time" class="form-control form-control-sm"
                                        placeholder="Masukkan Standar Cycletime (menit)...">
                                    <small class="text-muted">Khusus untuk kategori PLATING. Masukkan dalam satuan MENIT.</small>
                                </div>
                                {{-- PDF Upload: Horizontal side-by-side layout --}}
                                <div class="d-flex mb-3" style="border: 1px solid #e9ecef; border-radius: 6px;">
                                    {{-- Left: Standard PDF --}}
                                    <div class="p-3" style="flex: 1; min-width: 0; border-right: 2px solid #e9ecef; overflow: hidden;">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> Upload PDF Standard <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" id="tambah_files_input" name="files[]"
                                            class="form-control-file form-control-sm @if($errors->has('files') || $errors->has('files.*')) is-invalid @endif"
                                            accept=".pdf" multiple required>
                                        @if($errors->has('files'))
                                            <div class="invalid-feedback d-block animate__animated animate__fadeInDown">{{ $errors->first('files') }}</div>
                                        @endif
                                        @if($errors->has('files.*'))
                                            <div class="invalid-feedback d-block animate__animated animate__fadeInDown">{{ $errors->first('files.*') }}</div>
                                        @endif
                                        <small class="text-muted text-xs d-block mt-1">Bisa upload lebih dari satu file PDF. Max 10MB per file.</small>
                                        <div id="tambah_preview_files" class="mt-2"></div>
                                    </div>
                                    {{-- Right: Similar / Dimensi Part PDF --}}
                                    <div class="p-3" style="flex: 1; min-width: 0;">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-alt text-info mr-1"></i> Similar / Dimensi Part PDF
                                        </label>
                                        <input type="file" id="tambah_similar_input" name="similar_part_file"
                                            class="form-control-file form-control-sm" accept=".pdf">
                                        <small class="text-muted text-xs d-block mt-1">Optional: PDF referensi part serupa. Max 10MB.</small>
                                        <div id="tambah_preview_similar" class="mt-2"></div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small">Standar Dimensi In-Process</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0" id="modal-dimension-table">
                                            <thead class="bg-white text-dark small">
                                                <tr class="text-center">
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Point/No</th>
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Standar</th>
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Min</th>
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Max</th>
                                                    <th style="background-color: #ffffff !important; color: #333 !important;">Toleransi (+/-)</th>
                                                    <th style="width: 30px; background-color: #ffffff !important; color: #333 !important;">
                                                        <button type="button" class="btn btn-xs btn-success add-dimension-row">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" name="dimension_points[]" class="form-control form-control-sm" value="1" readonly style="background:#f8f9fa; color:#495057;"></td>
                                                    <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-xs btn-outline-danger remove-dimension-row">
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
                    <div class="modal-footer bg-white border-top-0 p-3" style="border-top: 1px solid #f1f5f9 !important;">
                        <button type="button" class="btn btn-light btn-sm shadow-sm px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Log Item -->
    <div class="modal fade" id="modalLogItem" tabindex="-1" role="dialog" aria-labelledby="modalLogItemLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white text-dark" style="border-bottom: 1px solid #f1f5f9;">
                    <h5 class="modal-title font-weight-bold" id="modalLogItemLabel" style="font-size: 1.1rem; letter-spacing: 0.2px;">
                        Riwayat Perubahan Data
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover table-sm mb-0" id="tableLogItem">
                            <thead class="bg-white text-dark" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="border-top-0 border-bottom-0 pl-4 py-2 bg-white text-dark" style="background-color: #ffffff !important; color: #333 !important;">Waktu</th>
                                    <th class="border-top-0 border-bottom-0 py-2 bg-white text-dark" style="background-color: #ffffff !important; color: #333 !important;">User</th>
                                    <th class="border-top-0 border-bottom-0 py-2 bg-white text-dark" style="background-color: #ffffff !important; color: #333 !important;">Aksi</th>
                                    <th class="border-top-0 border-bottom-0 pr-4 py-2 bg-white text-dark" style="background-color: #ffffff !important; color: #333 !important;">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0 p-3" style="border-top: 1px solid #f1f5f9 !important;">
                    <button type="button" class="btn btn-light btn-sm shadow-sm px-4" data-dismiss="modal">Tutup</button>
                </div>
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
        <script src="{{ asset('js/items/items-form-logic.js') }}?v={{ time() }}"></script>
        <script src="{{ asset('js/items/items-actions.js') }}?v={{ time() }}"></script>
        <script src="{{ asset('js/vendor/item-search.js') }}?v={{ time() }}"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if(isset($errors) && (is_object($errors) ? $errors->any() : (is_array($errors) && count($errors) > 0)))
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Duplikat atau Tidak Valid',
                        text: 'Silakan periksa kembali inputan Anda. Nama, Nomor Part, atau Kode SAP mungkin sudah terdaftar.',
                        confirmButtonText: 'OK'
                    });

                    @if(old('item_id'))
                        var itemId = "{{ old('item_id') }}";
                        var updateUrl = window.__ITEMS__.routes.update.replace(':id', itemId);
                        $('#formEditItem').attr('action', updateUrl);
                        $('#modalEditItem').modal('show');
                    @else
                        $('#modalTambahItem').modal('show');
                    @endif
                @endif

                if (typeof initItemSearch === 'function') {
                    initItemSearch('filterItem', { placeholder: 'Ketik Nama / Part No / SAP...', maxResults: 50 });
                }

                // Auto-submit filter on dropdown change
                const filters = ['filterItem', 'filterCategory', 'filterCustomer'];
                filters.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        $(el).on('change', function() {
                            var form = document.getElementById('filterFormItems');
                            if (form) form.submit();
                        });
                    }
                });

                // Fix body scrolling when multiple modals are open and one is closed
                $('.modal').on('hidden.bs.modal', function () {
                    if ($('.modal.show').length > 0) {
                        $('body').addClass('modal-open');
                    }
                });

                // --- INLINE VALIDATION FOR MASTER ITEMS ---
                const validateField = (form, name, label) => {
                    const field = $(form).find(`[name="${name}"], [name="${name}[]"]`).first();
                    if (!field.length || !field.is(':visible')) return true;

                    let isEmpty = false;
                    if (field.is('input[type="file"]')) {
                        isEmpty = field.prop('required') && field[0].files.length === 0;
                    } else {
                        isEmpty = !field.val() || field.val().trim() === '';
                    }

                    if (isEmpty) {
                        field.addClass('is-invalid');
                        if (field.next('.invalid-feedback').length === 0) {
                            field.after(`<div class="invalid-feedback js-inline-error">Field ${label} wajib diisi!</div>`);
                        }
                        return false;
                    } else {
                        field.removeClass('is-invalid');
                        field.next('.js-inline-error').remove();
                        return true;
                    }
                };

                $(document).on('submit', '#modalTambahItem form, #formEditItem', function (e) {
                    const form = this;
                    let isValid = true;
                    const mandatoryFields = [
                        { name: 'name', label: 'Nama Item' },
                        { name: 'category_id', label: 'Kategori' },
                        { name: 'plant', label: 'Plant' }
                    ];

                    // For 'store' action, PDF is mandatory
                    if ($(form).attr('action').includes('/store')) {
                        mandatoryFields.push({ name: 'files', label: 'Upload PDF Standard' });
                    }

                    const errorLabels = [];
                    mandatoryFields.forEach(f => {
                        if (!validateField(form, f.name, f.label)) {
                            isValid = false;
                            errorLabels.push(f.label);
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan!',
                            text: 'Mohon lengkapi data wajib: ' + errorLabels.join(', '),
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            $(form).find('.is-invalid').first().focus();
                        });
                        return false;
                    }
                });

                // --- LOADING STATE FOR IMPORT FORM ---
                $('#formImportItem').on('submit', function() {
                    var btn = $('#btnSubmitImport');
                    btn.prop('disabled', true);
                    btn.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Memproses...');
                    return true;
                });

                // --- LOG ITEM ---
                $('.btn-log-item').on('click', function() {
                    const itemId = $(this).data('id');
                    const tbody = $('#tableLogItem tbody');
                    tbody.html('<tr><td colspan="4" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary" role="status"></span> Memuat data...</td></tr>');
                    $('#modalLogItem').modal('show');

                    $.ajax({
                        url: "{{ route('items.logs', ':id') }}".replace(':id', itemId),
                        method: 'GET',
                        success: function(response) {
                            if(response.success) {
                                tbody.empty();
                                if(response.logs.length === 0) {
                                    tbody.html('<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat perubahan.</td></tr>');
                                } else {
                                    response.logs.forEach(function(log) {
                                        let actionBadge = '';
                                        if(log.action === 'created') actionBadge = '<span class="badge badge-success px-2 py-1">Dibuat</span>';
                                        else if(log.action === 'updated') actionBadge = '<span class="badge badge-warning px-2 py-1">Diedit</span>';
                                        else if(log.action === 'deleted') actionBadge = '<span class="badge badge-danger px-2 py-1">Dihapus</span>';
                                        else actionBadge = `<span class="badge badge-secondary px-2 py-1">${log.action}</span>`;

                                        tbody.append(`
                                            <tr>
                                                <td class="pl-4 align-middle text-nowrap"><small>${log.date}</small></td>
                                                <td class="align-middle font-weight-bold"><small>${log.user}</small></td>
                                                <td class="align-middle">${actionBadge}</td>
                                                <td class="pr-4 align-middle"><small>${log.description || '-'}</small></td>
                                            </tr>
                                        `);
                                    });
                                }
                            } else {
                                tbody.html('<tr><td colspan="4" class="text-center py-4 text-danger">Gagal memuat data log.</td></tr>');
                            }
                        },
                        error: function() {
                            tbody.html('<tr><td colspan="4" class="text-center py-4 text-danger">Terjadi kesalahan pada server.</td></tr>');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
