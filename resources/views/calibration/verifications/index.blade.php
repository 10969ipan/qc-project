@extends('layouts.admin')

@section('title', 'Hasil Verifikasi')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-2">
            <div class="card-body p-0">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo"
                                 style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800"
                                style="font-size:0.85rem; letter-spacing:0.3px;">
                                HASIL VERIFIKASI ALAT UKUR
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                            <table style="border-collapse:collapse; font-size:0.68rem;">
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                        {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-238' : 'QC-KRW-F-238' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">14/07/2025</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">-</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">- / -</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <style>
            .table-responsive {
                max-height: 75vh !important;
                overflow: auto !important;
                border: none !important;
                box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
            }
            #dataTable, table.dataTable {
                border-collapse: separate !important;
                border-spacing: 0 !important;
                border: none !important;
                border-top: none !important;
                width: 100% !important;
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
                font-size: 0.68rem !important;
                padding: 4px 6px !important;
            }

            #dataTable > thead > tr > th {
                position: -webkit-sticky !important;
                position: sticky !important;
                background-color: #f8fafc !important;
                background-clip: padding-box !important;
                color: #475569 !important;
                font-weight: 700 !important;
                text-transform: uppercase;
                font-size: 0.62rem !important;
                letter-spacing: 0.2px;
                padding: 6px 12px !important;
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

            /* Remove DataTables default sorting icons and background */
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
                font-size: 0.6rem !important;
                margin: 1px !important;
            }
            #dataTable .badge {
                font-size: 0.6rem !important;
                padding: 0.2rem 0.4rem !important;
            }
            
            /* Measurement Cell Styling */
            .meas-cell-row {
                height: 52px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 4px;
                text-align: center;
                overflow: hidden;
            }
            .meas-cell-row:not(:last-child) {
                border-bottom: 1px solid #f1f5f9;
            }

            .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
            .custom-filter-wrapper .ips-input { padding: 4px 20px 4px 8px; font-size: 0.75rem; border: none; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); height: calc(1.5em + 0.5rem + 2px); }
            .custom-filter-wrapper .ips-clear { right: 5px; font-size: 11px; }
            .custom-filter-wrapper { position: relative; top: -1px; }

            /* Force hide DataTables default elements (failsafe for caching) */
            .dataTables_length, 
            .dataTables_filter, 
            .year-filter-container {
                display: none !important;
            }

            /* Fixed Pagination at the bottom of card */
            .dataTables_wrapper > .row:last-child {
                position: sticky !important;
                bottom: 0 !important; 
                background-color: #ffffff !important;
                z-index: 106 !important;
                padding: 10px 0 !important;
                margin: 0 !important;
                border-top: 1px solid #e2e8f0 !important;
                border-bottom-left-radius: 0.35rem !important;
                border-bottom-right-radius: 0.35rem !important;
            }
            
            /* Ensure info and pagination look clean */
            .dataTables_info {
                font-size: 0.7rem !important;
                color: #475569 !important;
                font-weight: 600 !important;
                padding-top: 5px !important;
            }
            .dataTables_paginate .pagination {
                margin: 0 !important;
            }
            .page-link {
                padding: 0.3rem 0.6rem !important;
                font-size: 0.7rem !important;
            }
        </style>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
                <strong><i class="fas fa-exclamation-circle mr-1"></i> Kesalahan Sistem:</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @php
            $plant = $plantCode;
            // Resolve menu ID for permission checks (there might be multiple menus for different plants)
            $menuIds = \App\Models\AppMenu::where('route', 'calibration.verifications.index')->pluck('id')->toArray();
            
            $isAdmin = auth()->user()->role === 'admin';
            
            $canExport = $isAdmin;
            $canEdit = $isAdmin;
            $canDelete = $isAdmin;

            if (!$isAdmin) {
                foreach ($menuIds as $mId) {
                    if (auth()->user()->hasPermission($mId, 'export')) $canExport = true;
                    if (auth()->user()->hasPermission($mId, 'edit')) $canEdit = true;
                    if (auth()->user()->hasPermission($mId, 'delete')) $canDelete = true;
                }
            }

            /** @var \Illuminate\Support\ViewErrorBag $errors */
        @endphp
        @if(isset($errors) && method_exists($errors, 'any') && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Terjadi kesalahan validasi:</strong>
                <ul class="mb-0 mt-1 pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            @if(session('modal') == 'edit')
            {{-- Edit modal script moved to external JS --}}

            @endif
        @endif


        <div class="card shadow mb-4">
            <div class="card-body">
                <!-- Filter Bar Minimalis -->
                <form id="filterFormVerif" action="{{ route('calibration.verifications.index') }}" method="GET" 
                    class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm" 
                    style="gap: 12px; overflow-x: auto; white-space: nowrap;">
                    
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tahun:</label>
                        <div style="width: 85px;">
                            <select name="year" id="yearVerif" class="form-control form-control-sm border-0 shadow-sm">
                                @foreach($availableYears as $ay)
                                    <option value="{{ $ay }}" {{ $year == $ay ? 'selected' : '' }}>{{ $ay }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Periode:</label>
                        <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                            <input type="date" name="start_date" id="startDateVerif" 
                                class="form-control form-control-sm border-0" 
                                style="width: 130px; font-size: 0.75rem;" 
                                value="{{ request('start_date') }}">
                            <span class="px-2 text-gray-500 small">-</span>
                            <input type="date" name="end_date" id="endDateVerif" 
                                class="form-control form-control-sm border-0" 
                                style="width: 130px; font-size: 0.75rem;" 
                                value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <!-- Cari Umum -->
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Cari:</label>
                        <input type="text" name="search" id="searchVerif"
                            class="form-control form-control-sm border-0 shadow-sm" 
                            style="width: 250px; font-size: 0.75rem;" 
                            placeholder="Cari Alat, Merk, No. Seri..." 
                            value="{{ request('search') }}" autocomplete="off">
                    </div>

                    <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Filter">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                        
                        <a href="{{ route('calibration.verifications.index', ['plant' => $plantCode, 'year' => $year]) }}"
                            class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" title="Reset Filter">
                            <i class="fas fa-undo fa-sm"></i>
                        </a>
                        
                        @if($canExport)
                        <a id="printBtnVerif"
                            href="{{ route('calibration.verifications.print', array_merge(request()->all(), ['plant' => $plantCode])) }}"
                            target="_blank" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3"
                            style="background-color: #17a589; border-color: #17a589; color: white;" title="Print">
                            <i class="fas fa-print fa-sm"></i>
                        </a>
                        
                        <a href="{{ route('calibration.verifications.pdf', array_merge(request()->all(), ['plant' => $plantCode])) }}"
                            target="_blank" class="btn btn-danger btn-sm shadow-sm rounded-pill px-3" title="Export PDF">
                            <i class="fas fa-file-pdf fa-sm"></i>
                        </a>
                        @endif
                        
                        <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalVerifikasiBaru" title="Input Baru">
                            <i class="fas fa-plus fa-sm"></i>
                        </button>
                    </div>
                </form>

                <table class="table table-hover text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="align-middle">NO.</th>
                            <th class="align-middle">SERTIFIKASI</th>
                            <th class="align-middle">NAMA ALAT</th>
                            <th class="align-middle">MERK</th>
                            <th class="align-middle">NO. SERI</th>
                            <th class="align-middle">RENTANG UKUR</th>
                            <th class="align-middle">RESOLUSI</th>
                            <th class="align-middle">FREKUENSI KALIBRASI</th>
                            <th class="align-middle">TANGGAL KALIBRASI</th>
                            <th class="align-middle">TANGGAL VERIFIKASI</th>
                            <th class="align-middle">NEXT KALIBRASI</th>
                            <th class="align-middle">NILAI ALAT</th>
                            <th class="align-middle">NILAI KOREKSI</th>
                            <th class="align-middle">NILAI KETIDAKPASTIAN</th>
                            <th class="align-middle">HASIL VERIFIKASI</th>
                            <th class="align-middle">JUDGEMENT</th>
                            <th class="align-middle">STD. TOLERANSI</th>
                            <th class="align-middle">ACUAN TOLERANSI</th>
                            <th class="align-middle">AKSI</th>
                        </tr>
                    </thead>
                <tbody>
                    @forelse($verifications as $index => $v)
                        <tr>
                            <td class="align-middle">{{ $index + 1 }}</td>
                            <td class="text-center align-middle">
                                @if($v->certification_path)
                                    <button type="button" class="btn btn-sm btn-primary view-pdf" data-toggle="modal"
                                        data-target="#pdfModal" data-url="{{ route('calibration.verifications.serve-pdf', $v->id) }}"
                                        data-title="Sertifikat - {{ $v->name_alat }}">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="align-middle">{{ $v->name_alat }}</td>
                            <td class="align-middle">{{ $v->merk }}</td>
                            <td class="align-middle">{{ $v->serial_number }}</td>
                            <td class="align-middle">{{ $v->rentang_ukur }}</td>
                            <td class="align-middle">{{ $v->resolusi }}</td>
                            <td class="align-middle">{{ $v->frekuensi_kalibrasi }}</td>
                            <td class="align-middle">
                                {{ $v->tanggal_kalibrasi ? \Carbon\Carbon::parse($v->tanggal_kalibrasi)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="align-middle">
                                {{ $v->tanggal_verifikasi ? \Carbon\Carbon::parse($v->tanggal_verifikasi)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="align-middle">
                                {{ $v->next_kalibrasi ? \Carbon\Carbon::parse($v->next_kalibrasi)->format('d/m/Y') : '-' }}
                            </td>
                            @php
                                $arrAlat = is_array($v->nilai_alat) ? $v->nilai_alat : [$v->nilai_alat];
                                $arrKoreksi = is_array($v->nilai_koreksi) ? $v->nilai_koreksi : [$v->nilai_koreksi];
                                $arrKetidakpastian = is_array($v->nilai_ketidakpastian) ? $v->nilai_ketidakpastian : [$v->nilai_ketidakpastian];
                                $arrHasil = is_array($v->hasil_verifikasi) ? $v->hasil_verifikasi : [$v->hasil_verifikasi];
                                $maxRows = max(count($arrAlat), count($arrKoreksi), count($arrKetidakpastian), count($arrHasil));
                            @endphp
                            <td class="align-middle p-0">
                                @for($i = 0; $i < $maxRows; $i++)
                                    <div class="meas-cell-row">
                                        {{ $arrAlat[$i] ?? '' }}
                                    </div>
                                @endfor
                            </td>
                            <td class="align-middle p-0">
                                @for($i = 0; $i < $maxRows; $i++)
                                    <div class="meas-cell-row">
                                        {{ $arrKoreksi[$i] ?? '' }}
                                    </div>
                                @endfor
                            </td>
                            <td class="align-middle p-0">
                                @for($i = 0; $i < $maxRows; $i++)
                                    <div class="meas-cell-row">
                                        {{ $arrKetidakpastian[$i] ?? '' }}
                                    </div>
                                @endfor
                            </td>
                            <td class="align-middle p-0">
                                @for($i = 0; $i < $maxRows; $i++)
                                    <div class="meas-cell-row">
                                        {{ $arrHasil[$i] ?? '' }}
                                    </div>
                                @endfor
                            </td>
                            <td class="align-middle">
                                @if($v->judgment === 'OK')
                                    <span class="badge badge-success">OK</span>
                                @elseif($v->judgment === 'NG')
                                    <span class="badge badge-danger">NG</span>
                                @else
                                    {{ $v->judgment ?: '-' }}
                                @endif
                            </td>
                            <td class="align-middle">{{ $v->std_toleransi }}</td>
                            <td class="align-middle">{{ $v->acuan_toleransi }}</td>
                            <td class="align-middle no-export">
                                <div class="d-flex justify-content-center" style="gap: 5px;">
                                    @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'oshef']))
                                        @if($canEdit)
                                            <button type="button"
                                                class="btn btn-sm btn-info btn-edit-verif shadow-sm"
                                                data-id="{{ $v->id }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        @if($canExport)
                                            <button type="button"
                                                class="btn btn-sm btn-dark btn-qr-modal shadow-sm"
                                                data-id="{{ $v->id }}" title="QR Code">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        @endif
                                        @if($canDelete)
                                            <form
                                                action="{{ route('calibration.verifications.destroy', [$v->id, 'plant' => $plantCode]) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="year" value="{{ $year }}">
                                                <button type="button"
                                                    class="btn btn-sm btn-danger shadow-sm btn-delete" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="text-center">Tidak ada data hasil verifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PDF Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="pdfModalLabel">Lihat Sertifikat</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdfFrame" src="" width="100%" height="600px" style="border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <a id="downloadPdf" href="#" class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Verifikasi Baru -->
    <!-- Modal Edit Verifikasi -->
    <div class="modal fade" id="modalEditVerifikasi" tabindex="-1" role="dialog" aria-labelledby="modalEditVerifikasiLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg text-left">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modalEditVerifikasiLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Verifikasi Alat Ukur
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditVerif" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="tool_id" id="edit_tool_id">
                    <div class="modal-body p-4 bg-light">
                        @if($errors->any() && session('modal') == 'edit')
                            <div class="alert alert-danger px-3 py-2 small shadow-sm border-0 border-left-danger">
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="row">
                            {{-- Section 1: Informasi Alat --}}
                            <div class="col-md-5">
                                <div class="card border-0 shadow-sm rounded-lg h-100">
                                    <div class="card-header bg-white py-3 border-bottom-0">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-info-circle mr-2"></i> Informasi Alat
                                        </h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Nama Alat</label>
                                            <input type="text" name="name_alat" id="edit_name_alat"
                                                class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                style="border-radius: 8px;" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Merk <span class="text-danger">*</span></label>
                                            <input type="text" name="merk" id="edit_merk" 
                                                class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                style="border-radius: 8px;" required>
                                        </div>
                                        <div class="row px-0">
                                            <div class="col-6">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">No. Seri</label>
                                                    <input type="text" name="serial_number" id="edit_serial_number"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Resolusi</label>
                                                    <input type="text" name="resolusi" id="edit_resolusi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Rentang Ukur (Range)</label>
                                            <input type="text" name="rentang_ukur" id="edit_rentang_ukur"
                                                class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                style="border-radius: 8px;" required>
                                        </div>
                                        <div class="row px-0">
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="small font-weight-bold text-gray-700">Freq. Kalibrasi</label>
                                                    <input type="text" name="frekuensi_kalibrasi" id="edit_frekuensi_kalibrasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="small font-weight-bold text-gray-700">Riwayat Kalibrasi</label>
                                                    <input type="text" name="riwayat_kalibrasi" id="edit_riwayat_kalibrasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section 2: Jadwal & Hasil --}}
                            <div class="col-md-7">
                                <div class="card border-0 shadow-sm rounded-lg h-100">
                                    <div class="card-header bg-white py-3 border-bottom-0">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="fas fa-calendar-check mr-2"></i> Jadwal & Hasil Kalibrasi
                                        </h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Tgl. Kalibrasi <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_kalibrasi" id="edit_tanggal_kalibrasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Tgl. Verifikasi <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_verifikasi" id="edit_tanggal_verifikasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Next Kalibrasi <span class="text-danger">*</span></label>
                                                    <input type="date" name="next_kalibrasi" id="edit_next_kalibrasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Judgment <span class="text-danger">*</span></label>
                                                    <select name="judgment" id="edit_judgment" 
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                        <option value="-">-</option>
                                                        <option value="OK">OK</option>
                                                        <option value="NG">NG</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Std. Toleransi</label>
                                                    <input type="text" name="std_toleransi" id="edit_std_toleransi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" placeholder="Internal/Manual">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Acuan Toleransi</label>
                                                    <input type="text" name="acuan_toleransi" id="edit_acuan_toleransi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" placeholder="Contoh: JIS B 7507">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold text-gray-700 d-flex align-items-center">
                                                Upload PDF Baru (Sertifikat)
                                                <i class="fas fa-file-upload ml-2 text-muted"></i>
                                            </label>
                                            <div class="custom-file custom-file-sm">
                                                <input type="file" name="certification" class="custom-file-input" id="edit_cert_file" accept=".pdf">
                                                <label class="custom-file-label border-0 bg-light" for="edit_cert_file" style="border-radius: 8px;">Pilih file PDF...</label>
                                            </div>
                                            <div id="edit_existing_pdf" class="mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        {{-- Measurement Table --}}
                        <div class="card border-0 shadow-sm rounded-lg">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-ruler-combined mr-2"></i> Data Pengukuran & Koreksi
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-success shadow-xs px-3" id="edit-modal-add-row" style="border-radius: 20px;">
                                    <i class="fas fa-plus mr-1"></i> Tambah Baris
                                </button>
                            </div>
                            <div class="card-body pt-0 px-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 text-center" style="min-width: 600px;">
                                        <thead class="bg-dark text-white small text-uppercase">
                                            <tr>
                                                <th class="py-3 border-0" style="width: 25%;">Nilai Ditunjukkan Alat</th>
                                                <th class="py-3 border-0" style="width: 25%;">Nilai Koreksi Alat</th>
                                                <th class="py-3 border-0" style="width: 20%;">Ketidakpastian</th>
                                                <th class="py-3 border-0" style="width: 25%;">Hasil Verifikasi <small class="d-block text-white-50 opacity-75">(Koreksi + U)</small></th>
                                                <th class="py-3 border-0" style="width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="edit-modal-verification-body">
                                            {{-- Filled by JS --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-0 p-4 justify-content-end shadow-sm">
                        <button type="button" class="btn btn-light btn-sm px-4 mr-2" data-dismiss="modal" style="border-radius: 20px;">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-5 shadow-sm" style="border-radius: 20px; font-weight: 600;">
                            <i class="fas fa-save mr-2"></i> SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVerifikasiBaru" tabindex="-1" role="dialog" aria-labelledby="modalVerifikasiBaruLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg text-left">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modalVerifikasiBaruLabel">
                        <i class="fas fa-plus-circle mr-2"></i> Input Verifikasi Alat Ukur Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('calibration.verifications.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="modal-body p-4 bg-light">
                        @if($errors->any() && session('modal') == 'create')
                            <div class="alert alert-danger px-3 py-2 small shadow-sm border-0 border-left-danger">
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            {{-- Section 1: Informasi Alat --}}
                            <div class="col-md-5">
                                <div class="card border-0 shadow-sm rounded-lg h-100">
                                    <div class="card-header bg-white py-3 border-bottom-0">
                                        <h6 class="m-0 font-weight-bold text-success">
                                            <i class="fas fa-search mr-2"></i> Informasi Alat
                                        </h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Pilih Alat Ukur <span class="text-danger">*</span></label>
                                            <select name="tool_id" id="modal_tool_select" 
                                                class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                style="border-radius: 8px;" required>
                                                <option value="">-- Pilih Alat --</option>
                                                @foreach($tools as $tool)
                                                    <option value="{{ $tool->id }}" data-name="{{ $tool->name_alat }}"
                                                        data-serial="{{ $tool->serial_number }}" data-range="{{ $tool->range }}"
                                                        data-resolusi="{{ $tool->resolusi }}"
                                                        data-frekuensi="{{ $tool->frekuensi_kalibrasi }}"
                                                        data-riwayat="{{ $tool->riwayat_kalibrasi }}"
                                                        data-schedules="{{ json_encode($tool->schedules->pluck('schedule_date')->map(fn($d) => $d->format('Y-m-d'))) }}">
                                                        {{ $tool->name_alat }} ({{ $tool->serial_number }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Nama Alat</label>
                                            <input type="text" name="name_alat" id="modal_name_alat"
                                                class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                style="border-radius: 8px;" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700">Merk <span class="text-danger">*</span></label>
                                            <input type="text" name="merk" 
                                                class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                style="border-radius: 8px;" required>
                                        </div>
                                        <div class="row px-0">
                                            <div class="col-6">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">No. Seri</label>
                                                    <input type="text" name="serial_number" id="modal_serial_number"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Resolusi</label>
                                                    <input type="text" name="resolusi" id="modal_resolusi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold text-gray-700">Rentang Ukur (Range)</label>
                                            <input type="text" name="rentang_ukur" id="modal_rentang_ukur"
                                                class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                style="border-radius: 8px;" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section 2: Jadwal & Hasil --}}
                            <div class="col-md-7">
                                <div class="card border-0 shadow-sm rounded-lg h-100">
                                    <div class="card-header bg-white py-3 border-bottom-0">
                                        <h6 class="m-0 font-weight-bold text-success">
                                            <i class="fas fa-calendar-check mr-2"></i> Jadwal & Hasil Kalibrasi
                                        </h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row px-0">
                                            <div class="col-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Tgl. Kalibrasi <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_kalibrasi" 
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Tgl. Verifikasi <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_verifikasi" id="modal_tanggal_verifikasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Next Kalibrasi <span class="text-danger">*</span></label>
                                                    <input type="date" name="next_kalibrasi" id="modal_next_kalibrasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row px-0">
                                            <div class="col-6">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Frekuensi Kalibrasi</label>
                                                    <input type="text" name="frekuensi_kalibrasi" id="modal_frekuensi_kalibrasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Riwayat Kalibrasi</label>
                                                    <input type="text" name="riwayat_kalibrasi" id="modal_riwayat_kalibrasi"
                                                        class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row px-0">
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Judgment <span class="text-danger">*</span></label>
                                                    <select name="judgment" class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required>
                                                        <option value="-">-</option>
                                                        <option value="OK">OK</option>
                                                        <option value="NG">NG</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Std. Toleransi <span class="text-danger">*</span></label>
                                                    <input type="text" name="std_toleransi" class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required placeholder="Input Std. Toleransi">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="small font-weight-bold text-gray-700">Acuan Toleransi <span class="text-danger">*</span></label>
                                                    <input type="text" name="acuan_toleransi" class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                        style="border-radius: 8px;" required placeholder="Input Acuan Toleransi">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold text-gray-700 d-flex align-items-center">
                                                Upload PDF (Sertifikat)
                                                <i class="fas fa-file-upload ml-2 text-muted"></i>
                                            </label>
                                            <div class="custom-file custom-file-sm">
                                                <input type="file" name="certification" class="custom-file-input" id="modal_cert_file" accept=".pdf">
                                                <label class="custom-file-label border-0 bg-light" for="modal_cert_file" style="border-radius: 8px;">Pilih file PDF...</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        {{-- Measurement Table --}}
                        <div class="card border-0 shadow-sm rounded-lg">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-success">
                                    <i class="fas fa-ruler-combined mr-2"></i> Data Pengukuran & Koreksi
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary shadow-xs px-3" id="modal-add-row" style="border-radius: 20px;">
                                    <i class="fas fa-plus mr-1"></i> Tambah Baris
                                </button>
                            </div>
                            <div class="card-body pt-0 px-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 text-center" style="min-width: 600px;">
                                        <thead class="bg-dark text-white small text-uppercase">
                                            <tr>
                                                <th class="py-3 border-0" style="width: 25%;">Nilai Ditunjukkan Alat</th>
                                                <th class="py-3 border-0" style="width: 25%;">Nilai Koreksi Alat</th>
                                                <th class="py-3 border-0" style="width: 20%;">Ketidakpastian</th>
                                                <th class="py-3 border-0" style="width: 25%;">Hasil Verifikasi <small class="d-block text-white-50 opacity-75">(Koreksi + U)</small></th>
                                                <th class="py-3 border-0" style="width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-verification-body">
                                            <tr>
                                                <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm border-0 bg-light mx-auto" style="border-radius: 6px; width: 80%;"></td>
                                                <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm border-0 bg-light mx-auto calc-input" style="border-radius: 6px; width: 80%;"></td>
                                                <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm border-0 bg-light mx-auto calc-input" style="border-radius: 6px; width: 80%;"></td>
                                                <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm border-0 bg-light mx-auto shadow-none" style="border-radius: 6px; width: 80%;" readonly></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm text-danger modal-remove-row" title="Hapus">
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
                    <div class="modal-footer bg-white border-0 p-4 justify-content-end shadow-sm">
                        <button type="button" class="btn btn-light btn-sm px-4 mr-2" data-dismiss="modal" style="border-radius: 20px;">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm px-5 shadow-sm" style="border-radius: 20px; font-weight: 600;">
                            <i class="fas fa-save mr-2"></i> SIMPAN DATA VERIFIKASI
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- Modal QR Code -->
    <div class="modal fade" id="modalQrCode" tabindex="-1" role="dialog" aria-labelledby="modalQrCodeLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalQrCodeLabel">
                        <i class="fas fa-qrcode mr-2"></i> Label QR Code Verifikasi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <div id="qr-modal-image" class="mb-4 bg-white d-inline-block p-3 rounded shadow-sm">
                        <!-- QR Image will be injected here -->
                    </div>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <table class="table table-sm table-borderless m-0 text-left">
                                <tr>
                                    <th width="40%">Nama Alat</th>
                                    <td>: <span id="qr-modal-tool-name" class="font-weight-bold"></span></td>
                                </tr>
                                <tr>
                                    <th>Serial Number</th>
                                    <td>: <span id="qr-modal-serial"></span></td>
                                </tr>
                                <tr>
                                    <th>Tgl Verifikasi</th>
                                    <td>: <span id="qr-modal-date"></span></td>
                                </tr>
                                <tr>
                                    <th>Judgment</th>
                                    <td>: <span id="qr-modal-judgment"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <p class="text-muted small mb-4">
                        <i class="fas fa-mobile-alt mr-1"></i> Scan QR di atas untuk mendownload Laporan Hasil Lengkap.
                    </p>

                    <div class="row no-gutters p-0" style="gap: 10px;">
                        <div class="col">
                            <button type="button" id="qr-modal-download-img" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-download mr-1"></i> DOWNLOAD GAMBAR QR
                            </button>
                        </div>
                        <div class="col">
                            <a href="#" id="qr-modal-download-pdf" target="_blank"
                                class="btn btn-outline-danger btn-block shadow-sm no-loader">
                                <i class="fas fa-file-pdf mr-1"></i> DOWNLOAD PDF (2-PAGE)
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">TUTUP</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.__CALIBRATION_VERIFICATIONS__ = {
            plantCode: "{{ $plantCode }}",
            year: "{{ $year ?? date('Y') }}",
            csrf: "{{ csrf_token() }}",
            routes: {
                edit: "{{ route('calibration.verifications.edit', ':id') }}",
                update: "{{ route('calibration.verifications.update', ':id') }}",
                qrData: "{{ route('calibration.verifications.qr-data', ':id') }}"
            }
        };
    </script>
    <script src="{{ asset('js/calibration/calibration-verifications.js') }}?v={{ filemtime(public_path('js/calibration/calibration-verifications.js')) }}"></script>
    <script>
        $(document).ready(function() {
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush

