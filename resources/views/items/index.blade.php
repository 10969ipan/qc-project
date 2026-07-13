@extends('layouts.admin')

@section('title', 'Master Data Item')

@section('content')
<style>
    .table-responsive {
        max-height: calc(100vh - 220px) !important;
        overflow: auto !important;
        border: none !important;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
    }

    @media (max-width: 992px) {
        .table-responsive {
            max-height: 60vh !important;
        }
    }
    #checksheetTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    #checksheetTable td, #checksheetTable th {
        border-left: none !important;
        border-right: 1px solid #f1f5f9 !important;
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
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px !important;
        padding: 6px 12px !important;
        border-left: none !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #cbd5e1 !important;
    }

    /* Forced overrides for compact view */
    #checksheetTable td.no-export {
        min-width: 0 !important;
        white-space: nowrap !important; 
    }
    }
    #checksheetTable .btn {
        min-width: 0 !important; /* Overrides inline style */
        padding: 0.2rem 0.4rem !important;
        font-size: 0.6rem !important;
        margin: 1px !important;
    }
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
    @php
        $plantCodeStr = strtolower($plantCode ?? ($currentPlant->name ?? ''));
        $docHeader = \App\Models\GeneralSetting::getDocHeader('master_data', $plantCodeStr, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp

    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:1.15rem; letter-spacing:0.3px;">
                            MASTER DATA - PLANT {{ strtoupper($plantCodeStr) }}
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['no_dokumen'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>



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



    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Item</h6>
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                <div class="d-flex" style="gap: 10px;">
                    <button type="button" class="btn btn-sm btn-success shadow-sm" data-toggle="modal"
                        data-target="#modalImportItem">
                        <i class="fas fa-file-excel fa-sm text-white-50"></i> Import Excel
                    </button>
                    @if(auth()->user()->role === 'admin')
                        <button type="button" class="btn btn-sm btn-warning shadow-sm" data-toggle="modal"
                            data-target="#modalBulkUploadPdf" title="Ganti PDF semua item di satu kategori sekaligus">
                            <i class="fas fa-layer-group fa-sm text-white-50"></i> Upload PDF Sekaligus
                        </button>
                    @endif
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
                                <th>Action</th>
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
                                            title="Lihat Dokumen 1 ({{ count($fileUrls) }} file)">
                                            <i class="fas fa-file-pdf"></i> Dokumen 1
                                            @if(count($fileUrls) > 1)
                                                <span class="badge badge-light ml-1">{{ count($fileUrls) }}</span>
                                            @endif
                                        </button>
                                    @endif

                                    @if($item->similar_part_file_path)
                                        @php
                                            $catName = strtoupper($item->category->name ?? '');
                                            $isProcess = (str_contains($catName, 'INPROSES') || str_contains($catName, 'IN-PROCESS') || str_contains($catName, 'INPROCESS'));
                                            $standardLabel = 'Dokumen 2';
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
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" id="pdfModalLabel" style="font-size: 1.1rem;"><i class="fas fa-file-pdf mr-2 text-danger"></i> Lihat PDF</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 d-flex flex-column" style="background-color: #f8fafc; height: 85vh; overflow: hidden;">
                    <div class="p-3 border-bottom bg-white flex-shrink-0">
                        {{-- File navigation (shown only when multiple files) --}}
                        <div id="pdfFileNav" class="d-none justify-content-center align-items-center mb-2 py-1 px-2 bg-light rounded" style="gap:8px;">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="prevFile">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="fileInfo" class="small font-weight-bold text-muted">File 1 / 1</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="nextFile">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        {{-- Page navigation --}}
                        <div class="d-flex justify-content-center align-items-center">
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
                    </div>
                    <div class="flex-grow-1 bg-dark" style="overflow: auto; position: relative;">
                        <canvas id="the-canvas" style="border: 1px solid black; direction: ltr; margin: 20px auto; display: block;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalEditItem" tabindex="-1" role="dialog" aria-labelledby="modalEditItemLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 1200px;">
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" id="modalEditItemLabel" style="font-size: 1.1rem;"><i class="fas fa-edit mr-2 text-primary"></i> Edit Master Data Item
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditItem" action="" method="POST" enctype="multipart/form-data" novalidate>
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

                    <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">

                        <div class="row">
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Nama Item <span class="text-danger">*</span></label>
                                    <div class="d-flex w-100 align-items-start">
                                        <div class="flex-grow-1">
                                            <input type="text" name="name" id="edit_name_input" class="form-control form-control-sm border-0 shadow-sm @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                                required list="itemNamesList" autocomplete="off" placeholder="Ketik atau pilih Nama Item...">
                                            @error('name')
                                                <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 mt-1" onclick="deleteItemName('edit_name_input')" title="Hapus Nama Item dari Daftar"><i class="fas fa-times"></i></button>
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 mt-1" onclick="addNewItemName('edit_name_input')" title="Tambah Nama Item Baru"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                    <select name="category_id" id="edit_category_id" class="form-control form-control-sm border-0 shadow-sm @error('category_id') is-invalid @enderror"
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
                                    <label class="font-weight-bold">Customer <span class="text-danger">*</span></label>
                                    <div class="d-flex w-100 align-items-start">
                                        <div class="flex-grow-1">
                                            <select class="form-control form-control-sm border-0 shadow-sm @error('customer') is-invalid @enderror" name="customer" id="edit_customer_select">
                                                <option value="">- Pilih Customer -</option>
                                                @foreach($customers as $cust)
                                                    <option value="{{ $cust }}">{{ $cust }}</option>
                                                @endforeach
                                            </select>
                                            @error('customer')
                                                <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 mt-1" onclick="deleteItemCustomer('edit_customer_select')" title="Hapus Customer Terpilih"><i class="fas fa-times"></i></button>
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 mt-1" onclick="addNewItemCustomer('edit_customer_select')" title="Tambah Customer Baru"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">List Defect</label>
                                    <div class="d-flex w-100 mb-1">
                                        <input type="text" id="edit_defect_search" class="form-control form-control-sm border-0 shadow-sm no-autoupper" list="defectsList" placeholder="Cari / ketik defect..." autocomplete="off">
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1" onclick="appendDefect('edit_defect_search', 'edit')" title="Tambahkan ke list"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <div id="edit_defect_container" class="w-100 mt-2"></div>
                                    <input type="hidden" name="defects" id="edit_defects_hidden">
                                </div>
                            </div>
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Nomor Part <span class="text-danger">*</span></label>
                                    <input type="text" name="part_number" id="edit_part_number"
                                        class="form-control form-control-sm border-0 shadow-sm @error('part_number') is-invalid @enderror">
                                    @error('part_number')
                                        <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kode SAP</label>
                                    <input type="text" name="sap_code" id="edit_sap_code"
                                        class="form-control form-control-sm border-0 shadow-sm">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Cavity</label>
                                    <input type="number" name="cavity" id="edit_cavity" class="form-control form-control-sm border-0 shadow-sm"
                                        min="1">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Standar Berat (gr)</label>
                                    <input type="text" name="weight_standard" id="edit_weight_standard"
                                        class="form-control form-control-sm border-0 shadow-sm" placeholder="Contoh: 15.5">
                                </div>
                                <div class="form-group mb-3 sct-field-wrapper" style="display: none;">
                                    <label class="font-weight-bold">Standar Cycletime Plating (menit)</label>
                                    <input type="number" step="0.01" name="standard_cycle_time" id="edit_standard_cycle_time"
                                        class="form-control form-control-sm border-0 shadow-sm" placeholder="Contoh: 0.16">
                                    <small class="text-muted">Khusus untuk kategori PLATING. Masukkan dalam satuan MENIT.</small>
                                </div>
                                {{-- PDF Upload: Horizontal side-by-side layout --}}
                                <div class="d-flex mb-3" style="border: 1px solid #e9ecef; border-radius: 6px;">
                                    {{-- Left: Standard PDF --}}
                                    <div class="p-3" style="flex: 1; min-width: 0; border-right: 2px solid #e9ecef; overflow: hidden;">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> Dokumen 1
                                        </label>
                                        <input type="file" id="edit_files_input" name="files[]" class="form-control-file form-control-sm border-0 shadow-sm"
                                            accept=".pdf" multiple>
                                        <small class="text-muted text-xs d-block mt-1">Upload PDF referensi dokumen part. Max 10MB.</small>
                                        <div id="edit_existing_files" class="mt-2"></div>
                                        <div id="edit_preview_new_files" class="mt-1"></div>
                                    </div>
                                    {{-- Right: Dokumen 2 Part PDF --}}
                                    <div class="p-3" style="flex: 1; min-width: 0;">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-alt text-info mr-1"></i> Dokumen 2
                                        </label>
                                        <input type="file" id="edit_similar_input" name="similar_part_file" class="form-control-file form-control-sm border-0 shadow-sm @error('similar_part_file') is-invalid @enderror"
                                            accept=".pdf">
                                        <small class="text-muted text-xs d-block mt-1">Upload PDF referensi dokumen part. Max 10MB.</small>
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
                    <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
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
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" id="modalImportItemLabel" style="font-size: 1.1rem;"><i class="fas fa-file-excel mr-2 text-success"></i> Import Master Data Item
                        
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
                                <select name="plant" class="form-control form-control-sm border-0 shadow-sm" required>
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
                            <input type="file" name="file" class="form-control-file form-control-sm border-0 shadow-sm" accept=".xlsx,.xls,.csv" required>
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
                    <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
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
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" id="modalTambahItemLabel" style="font-size: 1.1rem;"><i class="fas fa-plus-circle mr-2 text-primary"></i>
                        Tambah Master Data Item
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    <input type="hidden" name="filter_search" value="{{ request('search') }}">
                    <input type="hidden" name="filter_name" value="{{ request('name') }}">
                    <input type="hidden" name="filter_category" value="{{ request('category') }}">
                    <input type="hidden" name="filter_customer" value="{{ request('customer') }}">
                    <input type="hidden" name="filter_part_number" value="{{ request('part_number') }}">
                    <input type="hidden" name="filter_sap_code" value="{{ request('sap_code') }}">
                    <input type="hidden" name="page" value="{{ request('page') }}">
                    <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">

                        @if(!$plantCode)
                            <div class="alert alert-warning py-2 mb-3 small">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Anda sedang di tampilan <strong>Total</strong>. Silakan pilih Plant tujuan item ini.
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Pilih Plant <span class="text-danger">*</span></label>
                                <select name="plant" class="form-control form-control-sm border-0 shadow-sm @error('plant') is-invalid @enderror" required id="modal_plant_select">
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
                                    <div class="d-flex w-100 align-items-start">
                                        <div class="flex-grow-1">
                                            <input type="text" name="name" id="tambah_name_input" class="form-control form-control-sm border-0 shadow-sm @error('name') is-invalid @enderror" required
                                                value="{{ old('name') }}" placeholder="Ketik atau pilih Nama Item..." list="itemNamesList" autocomplete="off">
                                            @error('name')
                                                <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 mt-1" onclick="deleteItemName('tambah_name_input')" title="Hapus Nama Item dari Daftar"><i class="fas fa-times"></i></button>
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 mt-1" onclick="addNewItemName('tambah_name_input')" title="Tambah Nama Item Baru"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                    <select name="category_id" id="modal_category_select"
                                        class="form-control form-control-sm border-0 shadow-sm @error('category_id') is-invalid @enderror" required>
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
                                    <label class="font-weight-bold">Customer <span class="text-danger">*</span></label>
                                    <div class="d-flex w-100 align-items-start">
                                        <div class="flex-grow-1">
                                            <select class="form-control form-control-sm border-0 shadow-sm @error('customer') is-invalid @enderror" name="customer" id="tambah_customer_select">
                                                <option value="">- Pilih Customer -</option>
                                                @foreach($customers as $cust)
                                                    <option value="{{ $cust }}">{{ $cust }}</option>
                                                @endforeach
                                            </select>
                                            @error('customer')
                                                <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger shadow-sm ml-1 mt-1" onclick="deleteItemCustomer('tambah_customer_select')" title="Hapus Customer Terpilih"><i class="fas fa-times"></i></button>
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1 mt-1" onclick="addNewItemCustomer('tambah_customer_select')" title="Tambah Customer Baru"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">List Defect</label>
                                    <div class="d-flex w-100 mb-1">
                                        <input type="text" id="add_defect_search" class="form-control form-control-sm border-0 shadow-sm no-autoupper" list="defectsList" placeholder="Cari / ketik defect..." autocomplete="off">
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm ml-1" onclick="appendDefect('add_defect_search', 'add')" title="Tambahkan ke list"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <div id="add_defect_container" class="w-100 mt-2"></div>
                                    <input type="hidden" name="defects" id="add_defects_hidden">
                                    <small class="text-muted mt-1 d-block">Biarkan kosong untuk menggunakan default defects.</small>
                                </div>
                            </div>
                            <div class="col-md-6 text-left">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Nomor Part <span class="text-danger">*</span></label>
                                    <input type="text" name="part_number" class="form-control form-control-sm border-0 shadow-sm @error('part_number') is-invalid @enderror"
                                        placeholder="Masukkan Nomor Part...">
                                    @error('part_number')
                                        <div class="invalid-feedback animate__animated animate__fadeInDown">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Kode SAP</label>
                                    <input type="text" name="sap_code" class="form-control form-control-sm border-0 shadow-sm"
                                        placeholder="Masukkan Kode SAP (Opsional)">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Cavity</label>
                                    <input type="number" name="cavity" class="form-control form-control-sm border-0 shadow-sm"
                                        placeholder="Jml Cavity" value="1" min="1">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">Standar Berat (gr)</label>
                                    <input type="text" name="weight_standard" class="form-control form-control-sm border-0 shadow-sm"
                                        placeholder="Masukkan Standar Berat (gr)...">
                                </div>
                                <div class="form-group mb-3 sct-field-wrapper" style="display: none;">
                                    <label class="font-weight-bold">Standar Cycletime Plating (menit)</label>
                                    <input type="number" step="0.01" name="standard_cycle_time" class="form-control form-control-sm border-0 shadow-sm"
                                        placeholder="Masukkan Standar Cycletime (menit)...">
                                    <small class="text-muted">Khusus untuk kategori PLATING. Masukkan dalam satuan MENIT.</small>
                                </div>
                                {{-- PDF Upload: Horizontal side-by-side layout --}}
                                <div class="d-flex mb-3" style="border: 1px solid #e9ecef; border-radius: 6px;">
                                    {{-- Left: Standard PDF --}}
                                    <div class="p-3" style="flex: 1; min-width: 0; border-right: 2px solid #e9ecef; overflow: hidden;">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-pdf text-danger mr-1"></i> Dokumen 1 / 2 <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" id="tambah_files_input" name="files[]"
                                            class="form-control-file form-control-sm border-0 shadow-sm @if($errors->has('files') || $errors->has('files.*')) is-invalid @endif"
                                            accept=".pdf" multiple>
                                        <small class="text-muted text-xs d-block mt-1">Bisa upload lebih dari satu file PDF. Max 10MB per file.</small>
                                        <div id="tambah_preview_files" class="mt-2"></div>
                                    </div>
                                    {{-- Right: Dokumen 2 Part PDF --}}
                                    <div class="p-3" style="flex: 1; min-width: 0;">
                                        <label class="font-weight-bold d-block mb-1" style="font-size:0.82rem;">
                                            <i class="fas fa-file-alt text-info mr-1"></i> Dokumen 2
                                        </label>
                                        <input type="file" id="tambah_similar_input" name="similar_part_file"
                                            class="form-control-file form-control-sm border-0 shadow-sm @error('similar_part_file') is-invalid @enderror" accept=".pdf">
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
                                                    <td><input type="text" name="dimension_points[]" class="form-control form-control-sm border-0 shadow-sm" value="1" readonly style="background:#f8f9fa; color:#495057;"></td>
                                                    <td><input type="text" name="dimension_sizes[]" class="form-control form-control-sm border-0 shadow-sm"></td>
                                                    <td><input type="text" name="dimension_mins[]" class="form-control form-control-sm border-0 shadow-sm"></td>
                                                    <td><input type="text" name="dimension_maxs[]" class="form-control form-control-sm border-0 shadow-sm"></td>
                                                    <td><input type="text" name="dimension_tolerances[]" class="form-control form-control-sm border-0 shadow-sm"></td>
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
                    <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
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
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-gray-800" id="modalLogItemLabel" style="font-size: 1.1rem;"><i class="fas fa-history mr-2 text-info"></i> Riwayat Perubahan Data
                        Riwayat Perubahan Data
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover table-sm mb-0" id="tableLogItem">
                            <thead class="bg-white text-dark" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="border-top-0 border-bottom-0 pl-4 py-2 bg-white text-dark" style="background-color: #ffffff !important; color: #333 !important;">Waktu</th>
                                    <th class="border-top-0 border-bottom-0 py-2 bg-white text-dark" style="background-color: #ffffff !important; color: #333 !important;">User</th>
                                    <th class="border-top-0 border-bottom-0 py-2 bg-white text-dark" style="background-color: #ffffff !important; color: #333 !important;">Action</th>
                                    <th class="border-top-0 border-bottom-0 pr-4 py-2 bg-white text-dark" style="background-color: #ffffff !important; color: #333 !important;">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
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
                    addCustomer: "{{ route('admin.items.add-customer') }}",
                    deleteCustomer: "{{ route('admin.items.delete-customer') }}",
                    pdfWorker: "{{ asset('js/vendor/pdf.worker.min.js') }}"
                },
                csrfToken: "{{ csrf_token() }}",
                plant: "{{ $plantCode ?? '' }}"
            };
        </script>
        <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
        <script src="{{ asset('js/items/items-pdf-viewer.js') }}"></script>
        <script src="{{ asset('js/items/items-form-logic.js') }}?v={{ filemtime(public_path('js/items/items-form-logic.js')) }}"></script>
        <script src="{{ asset('js/items/items-actions.js') }}?v={{ filemtime(public_path('js/items/items-actions.js')) }}"></script>
        <script src="{{ asset('js/vendor/item-search.js') }}?v={{ filemtime(public_path('js/vendor/item-search.js')) }}"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if(isset($errors) && (is_object($errors) ? $errors->any() : (is_array($errors) && count($errors) > 0)))
                    var errorMessages = @json(is_object($errors) ? $errors->all() : $errors);
                    Swal.fire({
                        icon: 'error',
                        title: 'Peringatan Validasi',
                        html: '<div class="text-left small">Silakan periksa kembali inputan Anda:<ul class="mt-2 pl-3 mb-0"><li>' + errorMessages.join('</li><li>') + '</li></ul></div>',
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

    {{-- ===================== MODAL BULK UPLOAD PDF ===================== --}}
    <div class="modal fade" id="modalBulkUploadPdf" tabindex="-1" role="dialog" aria-labelledby="modalBulkUploadPdfLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header py-3 px-4 bg-white border-bottom" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-dark" id="modalBulkUploadPdfLabel" style="font-size: 1rem;">
                        <i class="fas fa-layer-group text-warning mr-2"></i> Upload PDF Sekaligus (Per Kategori)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.items.bulk-upload-pdf') }}" method="POST" enctype="multipart/form-data" id="formBulkUploadPdf">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="alert alert-warning py-2 px-3 mb-3 small" style="border-radius: 8px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Perhatian:</strong> PDF yang diupload akan <strong>menggantikan</strong> file PDF lama pada <strong>semua item</strong> di kategori yang dipilih.
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small">Pilih Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" id="bulk_category_id" class="form-control form-control-sm border-0 shadow-sm" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small">Tipe PDF <span class="text-danger">*</span></label>
                            <div class="d-flex" style="gap: 16px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="pdf_type" id="bulk_type_standard" value="standard" checked>
                                    <label class="form-check-label small" for="bulk_type_standard">
                                        <i class="fas fa-file-pdf text-danger mr-1"></i> Dokumen 1
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="pdf_type" id="bulk_type_similar" value="similar">
                                    <label class="form-check-label small" for="bulk_type_similar">
                                        <i class="fas fa-file-alt text-info mr-1"></i> Dokumen 2
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small">File PDF <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="bulk_pdf_file" name="pdf_file" accept=".pdf" required>
                                <label class="custom-file-label small" for="bulk_pdf_file">Pilih file PDF...</label>
                            </div>
                            <small class="text-muted">Maks. 10MB. File ini akan diterapkan ke semua item dalam kategori yang dipilih.</small>
                        </div>
                        <div id="bulk_preview_filename" class="mt-2 d-none">
                            <div class="d-flex align-items-center p-2 bg-light rounded" style="gap:8px;">
                                <i class="fas fa-file-pdf text-danger fa-lg"></i>
                                <span id="bulk_fname_text" class="small font-weight-bold text-truncate"></span>
                            </div>
                        </div>

                        <!-- Upload Progress Bar -->
                        <div id="bulk_upload_progress_container" class="mt-3 d-none">
                            <label class="font-weight-bold small mb-1" id="bulk_upload_status">Mengunggah... 0%</label>
                            <div class="progress" style="height: 10px;">
                                <div id="bulk_upload_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-light btn-sm shadow-sm px-4" data-dismiss="modal" id="btnCancelBulkUpload">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold" id="btnSubmitBulkUpload">
                            <i class="fas fa-upload mr-1"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Datalist for Item Names -->
    <datalist id="itemNamesList">
        @foreach($allItemsList->pluck('name')->unique()->sort() as $itemName)
            <option value="{{ $itemName }}"></option>
        @endforeach
    </datalist>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Show filename preview for bulk PDF upload
    const pdfInput = document.getElementById('bulk_pdf_file');
    if (pdfInput) {
        pdfInput.addEventListener('change', function () {
            const preview = document.getElementById('bulk_preview_filename');
            const fname   = document.getElementById('bulk_fname_text');
            const label   = document.querySelector('label[for="bulk_pdf_file"]');
            if (this.files && this.files[0]) {
                const name = this.files[0].name;
                if (label) label.textContent = name;
                fname.textContent = name;
                preview.classList.remove('d-none');
            } else {
                if (label) label.textContent = 'Pilih file PDF...';
                preview.classList.add('d-none');
            }
        });
    }

    // Confirm before submit
    const bulkForm = document.getElementById('formBulkUploadPdf');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            e.preventDefault();
            
            const cat  = document.getElementById('bulk_category_id');
            if(cat.selectedIndex <= 0) {
                Swal.fire('Peringatan', 'Silakan pilih kategori terlebih dahulu.', 'warning');
                return;
            }
            
            const catLabel = cat.options[cat.selectedIndex]?.text || '';
            const type = document.querySelector('input[name="pdf_type"]:checked')?.value || '';
            const typeLabel = type === 'standard' ? 'Dokumen 1' : 'Dokumen 2';
            
            Swal.fire({
                title: 'Konfirmasi Upload Sekaligus',
                text: `Yakin ingin mengganti PDF tipe "${typeLabel}" untuk SEMUA item di kategori "${catLabel}"?\nTindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Ganti PDF!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    processBulkUpload(bulkForm);
                }
            });
        });
    }

    function processBulkUpload(form) {
        const formData = new FormData(form);
        const progressContainer = document.getElementById('bulk_upload_progress_container');
        const progressBar = document.getElementById('bulk_upload_progress_bar');
        const statusText = document.getElementById('bulk_upload_status');
        const btnSubmit = document.getElementById('btnSubmitBulkUpload');
        const btnCancel = document.getElementById('btnCancelBulkUpload');
        
        progressContainer.classList.remove('d-none');
        progressBar.style.width = '0%';
        progressBar.setAttribute('aria-valuenow', '0');
        progressBar.classList.add('progress-bar-animated');
        statusText.textContent = 'Mengunggah... 0%';
        
        btnSubmit.disabled = true;
        btnCancel.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
        
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        progressBar.style.width = percentComplete + '%';
                        progressBar.setAttribute('aria-valuenow', percentComplete);
                        statusText.textContent = 'Mengunggah... ' + percentComplete + '%';
                        
                        if(percentComplete === 100) {
                             statusText.textContent = 'Memproses data di server... Mohon tunggu';
                             progressBar.classList.remove('progress-bar-animated');
                        }
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message || 'Upload PDF sekaligus berhasil dilakukan.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                let errorMsg = 'Gagal melakukan upload.';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Gagal!',
                    text: errorMsg,
                    icon: 'error'
                });
                
                progressContainer.classList.add('d-none');
                btnSubmit.disabled = false;
                btnCancel.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-upload mr-1"></i> Upload';
            }
        });
    }
});

    window.updateHiddenDefects = function(prefix) {
        let container = document.getElementById(prefix + '_defect_container');
        let hidden = document.getElementById(prefix + '_defects_hidden');
        if (!container || !hidden) return;

        let inputs = container.querySelectorAll('input[type="text"]');
        let vals = [];
        inputs.forEach(function(inp) {
            let val = inp.value.trim();
            val = val.replace(/^\d+\.\s*/, '');
            vals.push(val);
        });
        hidden.value = vals.join('\n');
    };

    window.removeDefect = function(btn, prefix) {
        btn.parentElement.remove();
        window.updateHiddenDefects(prefix);
    };

    window.addDefectBadge = function(val, prefix) { // Keep name as addDefectBadge so items-form-logic.js works without changes
        let container = document.getElementById(prefix + '_defect_container');
        if (!container) return;
        
        let div = document.createElement('div');
        div.className = 'd-flex align-items-center mb-1';
        div.innerHTML = '<input type="text" class="defect-text form-control form-control-sm border-0 shadow-sm bg-light font-weight-bold" value="' + val + '" readonly>' +
                        '<button type="button" class="btn btn-sm btn-danger shadow-sm ml-1" onclick="window.removeDefect(this, \'' + prefix + '\')" title="Hapus"><i class="fas fa-times"></i></button>';
        container.appendChild(div);
        window.updateHiddenDefects(prefix);
    };

    window.appendDefect = function(inputId, prefix) {
        let inputEl = document.getElementById(inputId);
        let val = inputEl.value.trim();
        
        if (val !== '') {
            // Optional: Validate datalist (disabled for now to allow new custom defects)
            // let listId = inputEl.getAttribute('list');
            // let datalist = document.getElementById(listId);
            // let exists = false;
            // if (datalist) {
            //     for (let i = 0; i < datalist.options.length; i++) {
            //         if (datalist.options[i].value === val) { exists = true; break; }
            //     }
            // }
            // if (!exists) {
            //     Swal.fire('Peringatan', 'Defect tidak terdaftar!', 'warning');
            //     return;
            // }

            window.addDefectBadge(val, prefix);
            inputEl.value = '';
            inputEl.focus();
        }
    };
</script>

<datalist id="defectsList">
    @php
        $allDefects = [];
        $itemsWithDefects = \App\Models\Item::whereNotNull('defects')->pluck('defects');
        foreach($itemsWithDefects as $defectsArray) {
            if (is_array($defectsArray)) {
                foreach($defectsArray as $d) {
                    $allDefects[] = trim($d);
                }
            } elseif (is_string($defectsArray)) {
                $decoded = json_decode($defectsArray, true);
                if (is_array($decoded)) {
                    foreach($decoded as $d) {
                        $allDefects[] = trim($d);
                    }
                }
            }
        }
        $allDefects = array_unique(array_filter($allDefects));
        sort($allDefects);
    @endphp
    @foreach($allDefects as $d)
        <option value="{{ $d }}"></option>
    @endforeach
</datalist>

@endpush




