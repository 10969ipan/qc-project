@extends('layouts.admin')

@section('title', 'Master Data Alat Verifikasi')

@section('content')
    <style>
        .table-responsive {
            max-height: 68vh !important;
            overflow: auto !important;
            border: none !important;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.02);
        }
        #dataTable {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: none !important;
            width: 100% !important;
            table-layout: auto !important;
        }
        
        #dataTable td, #dataTable th {
            border-left: none !important;
            border-right: 1px solid #f1f5f9 !important;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        #dataTable tbody td {
            border-bottom: 1px solid #f1f5f9 !important;
            border-top: none !important;
            vertical-align: middle !important;
            color: #334155 !important;
            font-size: 0.70rem !important;
            padding: 5px 8px !important;
            line-height: 1.2 !important;
        }

        /* Sticky Header matching Plating Checksheet */
        #dataTable > thead > tr > th {
            position: -webkit-sticky !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 105 !important;
            background-color: #f8fafc !important;
            background-clip: padding-box !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            text-transform: uppercase;
            font-size: 0.62rem !important;
            letter-spacing: 0.2px;
            padding: 6px 8px !important;
            border-left: none !important;
            border-right: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            vertical-align: middle !important;
            line-height: 1.2 !important;
            white-space: nowrap !important;
            box-shadow: inset 0 -1px 0 #cbd5e1;
        }

        #dataTable tbody tr:hover {
            background-color: #f1f5f9 !important;
            transition: background-color 0.2s ease;
        }

        #dataTable td.no-export {
            min-width: 0 !important;
            white-space: nowrap !important;
        }
        #dataTable .btn {
            min-width: 0 !important;
            padding: 0.15rem 0.35rem !important;
            font-size: 0.65rem !important;
            margin: 0px !important;
        }
        #dataTable .badge {
            font-size: 0.63rem !important;
            padding: 0.25rem 0.65rem !important;
            border-radius: 50rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.3px;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            display: inline-block;
            min-width: 60px;
        }

        /* Filter Styles from Checksheet */
        .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
        .custom-filter-wrapper .ips-input { 
            padding: 4px 20px 4px 8px; 
            font-size: 0.75rem !important; 
            border: none !important; 
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important; 
            height: calc(1.5em + 0.5rem + 2px) !important; 
            background: white !important;
            border-radius: 0.35rem !important;
        }
        
        #filterForm .form-control-sm {
            font-size: 0.75rem !important;
            border: none !important;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
        }
    </style>

    <div class="container-fluid px-2">
        <!-- Single Unified Card (IPP Header + Filter + Table) -->
        <div class="card shadow mb-2">
            <div class="card-body p-2">
                <!-- IPP Style Header -->
                <div class="mb-2">
                    <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                        <tr>
                            <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                                <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo"
                                     style="max-width:58px; max-height:44px; object-fit:contain;">
                            </td>
                            <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                                <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800"
                                    style="font-size:0.85rem; letter-spacing:0.3px;">
                                    MASTER DATA ALAT VERIFIKASI (JIG, MAL, C/F)
                                </h1>
                            </td>
                            <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                                <table style="border-collapse:collapse; font-size:0.68rem;">
                                    <tr>
                                        <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                        <td style="padding:1px 2px;">:</td>
                                        <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-0007' : 'QC-KRW-F-0007' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                        <td style="padding:1px 2px;">:</td>
                                        <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">06-Jan-2025</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                        <td style="padding:1px 2px;">:</td>
                                        <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">-</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                        <td style="padding:1px 2px;">:</td>
                                        <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">1/1</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Filter Bar (In-Process Style) -->
                <form action="{{ route('verifications.tools.index') }}" method="GET"
                    class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-2 shadow-sm"
                    style="gap: 8px;" id="filterForm">
                    
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <!-- Field: Search Item -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Cari Data</label>
                        <div style="width: 170px;">
                            <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" 
                                style="border-radius: 0.35rem;" 
                                placeholder="Nama / No Part..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Field: Jenis Alat -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Jenis Alat</label>
                        <div style="width: 130px;">
                            <select name="tool_type" class="form-control form-control-sm border-0 shadow-sm" style="border-radius: 0.35rem;">
                                <option value="">Semua Jenis</option>
                                @foreach($toolTypes as $type)
                                    <option value="{{ $type }}" {{ request('tool_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Field: Customer -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Customer</label>
                        <div style="width: 130px;">
                            <select name="customer" class="form-control form-control-sm border-0 shadow-sm" style="border-radius: 0.35rem;">
                                <option value="">Semua Customer</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust }}" {{ request('customer') == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Field: Verifikasi -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Tipe Verifikasi</label>
                        <div style="width: 110px;">
                            <select name="verification_type" class="form-control form-control-sm border-0 shadow-sm" style="border-radius: 0.35rem;">
                                <option value="">Semua</option>
                                @foreach($verificationTypes as $vType)
                                    <option value="{{ $vType }}" {{ request('verification_type') == $vType ? 'selected' : '' }}>{{ $vType }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Field: Drawing -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Drawing</label>
                        <div style="width: 95px;">
                            <select name="drawing" class="form-control form-control-sm border-0 shadow-sm" style="border-radius: 0.35rem;">
                                <option value="">Semua</option>
                                @foreach($drawings as $dwg)
                                    <option value="{{ $dwg }}" {{ request('drawing') == $dwg ? 'selected' : '' }}>{{ $dwg }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Field: Judgment -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Judgment</label>
                        <div style="width: 95px;">
                            <select name="judgment" class="form-control form-control-sm border-0 shadow-sm" style="border-radius: 0.35rem;">
                                <option value="">Semua</option>
                                @foreach($judgments as $jdg)
                                    <option value="{{ $jdg }}" {{ request('judgment') == $jdg ? 'selected' : '' }}>{{ $jdg }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Field: Tool Status -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700" style="font-size: 0.68rem;">Status</label>
                        <div style="width: 100px;">
                            <select name="tool_status" class="form-control form-control-sm border-0 shadow-sm" style="border-radius: 0.35rem;">
                                <option value="">Semua</option>
                                @foreach($statuses as $st)
                                    <option value="{{ $st }}" {{ request('tool_status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tombol Filter & Reset (Disamping Dropdown, Rata Tengah Atas-Bawah) -->
                    <div class="d-flex align-items-center align-self-center my-auto" style="gap: 5px; margin-left: 4px;">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm font-weight-bold" title="Cari Data">
                            <i class="fas fa-search fa-sm mr-1"></i> Filter
                        </button>
                        <a href="{{ route('verifications.tools.index', ['plant' => $plantCode]) }}"
                            class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm font-weight-bold" title="Reset Filter">
                            <i class="fas fa-undo fa-sm mr-1"></i> Reset
                        </a>
                    </div>

                    <!-- Tombol Tambah Alat (Kanan, Rata Tengah Atas-Bawah) -->
                    <div class="ml-auto align-self-center my-auto">
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm font-weight-bold" data-toggle="modal"
                            data-target="#modalTambahAlat">
                            <i class="fas fa-plus-circle fa-sm mr-1"></i> Tambah Alat
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="col-no">NO.</th>
                                <th class="col-part-name">NAMA PART</th>
                                <th class="col-part-code">PART CODE</th>
                                <th class="col-part-no">MODEL</th>
                                <th class="col-tool-type">JENIS ALAT</th>
                                <th class="col-cust">CUSTOMER</th>
                                <th class="col-qty">QTY</th>
                                <th class="col-freq">FREKUENSI</th>
                                <th class="col-type">JENIS VERIFIKASI</th>
                                <th class="col-drawing">DRAWING</th>
                                <th class="col-judgment">JUDGMENT</th>
                                <th class="col-status-text">STATUS</th>
                                <th class="col-verif-history">RIWAYAT VERIFIKASI</th>
                                <th class="col-stat-icon">STAT</th>
                                <th class="col-aksi no-export">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tools as $index => $tool)
                                <tr>
                                    <td>{{ ($tools->currentPage() - 1) * $tools->perPage() + $index + 1 }}</td>
                                    <td class="text-left font-weight-bold" style="color: #1e293b;">{{ $tool->name_part }}</td>
                                    <td class="text-nowrap font-weight-bold text-primary" style="font-size: 0.68rem;">{{ $tool->part_code ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $tool->no_part }}</td>
                                    <td>{{ $tool->tool_type }}</td>
                                    <td>{{ $tool->customer }}</td>
                                    <td>{{ $tool->quantity }}</td>
                                    <td>{{ $tool->verification_frequency }}</td>
                                    <td>{{ $tool->verification_type }}</td>
                                    <td>
                                        @if($tool->drawing_path)
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-0 font-weight-bold" onclick="previewDrawing('{{ asset($tool->drawing_path) }}', '{{ $tool->name_part }}')" title="Lihat Gambar Drawing" style="font-size: 0.65rem;">
                                                <i class="fas fa-image mr-1"></i> ADA
                                            </button>
                                        @else
                                            <span class="badge rounded-pill {{ $tool->drawing === 'ADA' ? 'badge-success' : 'badge-danger' }}">
                                                {{ $tool->drawing }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill {{ $tool->tool_judgment === 'OK' ? 'badge-success' : ($tool->tool_judgment === 'NG' ? 'badge-danger' : 'badge-secondary') }}">
                                            {{ $tool->tool_judgment ?? 'BELUM' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill {{ ($tool->tool_status ?? 'AKTIF') === 'AKTIF' ? 'badge-primary' : 'badge-warning text-dark' }}">
                                            {{ $tool->tool_status ?? 'AKTIF' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $vTotal = ($tool->verifications_count ?? 0) + ($tool->actual_schedules_count ?? 0);
                                        @endphp
                                        <span class="badge rounded-pill bg-light text-primary border shadow-sm font-weight-bold px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-history text-primary mr-1"></i> {{ $vTotal }} Kali
                                        </span>
                                    </td>
                                    <td>
                                        @if($tool->tool_judgment)
                                            <i class="fas fa-check-circle text-success fa-lg" title="Sudah Verifikasi"></i>
                                        @else
                                            <div class="d-inline-block position-relative" style="width: 25px; height: 25px; vertical-align: middle;" title="Menunggu Verifikasi">
                                                <i class="fas fa-calendar text-secondary" style="font-size: 1.1rem;"></i>
                                                <i class="fas fa-clock text-secondary" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.65rem; background: white; border-radius: 50%; box-shadow: 0 0 0 2px white;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="no-export">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 shadow-sm rounded-circle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 28px; height: 28px; padding: 0; line-height: 28px;" title="Aksi">
                                                <i class="fas fa-ellipsis-v text-secondary"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 py-1" style="font-size: 0.75rem; border-radius: 0.5rem; min-width: 140px;">
                                                <a class="dropdown-item py-1.5 font-weight-bold text-success d-flex align-items-center" href="javascript:void(0)" onclick="openInputVerifikasiModal('{{ $tool->id }}')">
                                                    <i class="fas fa-clipboard-check mr-2 text-success" style="width: 14px;"></i> Input Verifikasi
                                                </a>
                                                <div class="dropdown-divider my-1"></div>
                                                <a class="dropdown-item py-1.5 font-weight-bold text-info d-flex align-items-center" href="javascript:void(0)" onclick="editTool('{{ $tool->id }}')">
                                                    <i class="fas fa-edit mr-2 text-info" style="width: 14px;"></i> Edit
                                                </a>
                                                <div class="dropdown-divider my-1"></div>
                                                <a class="dropdown-item py-1.5 font-weight-bold text-danger d-flex align-items-center" href="javascript:void(0)" onclick="confirmDeleteTool('{{ $tool->id }}')">
                                                    <i class="fas fa-trash-alt mr-2 text-danger" style="width: 14px;"></i> Hapus
                                                </a>
                                            </div>
                                        </div>
                                        <form id="delete-tool-form-{{ $tool->id }}"
                                            action="{{ route('verifications.tools.destroy', $tool->id) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="plant" value="{{ $plantCode }}">
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center py-4 text-muted small">Tidak ada data alat ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Bar -->
                @if($tools->hasPages() || $tools->total() > 0)
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 pt-2 border-top">
                        <div class="small text-muted font-weight-bold">
                            Menampilkan {{ $tools->firstItem() ?? 0 }} - {{ $tools->lastItem() ?? 0 }} dari total {{ $tools->total() }} data
                        </div>
                        <div>
                            {{ $tools->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal Tambah Alat -->
        <div class="modal fade" id="modalTambahAlat" tabindex="-1" role="dialog" aria-labelledby="modalTambahAlatLabel"
            aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-lg" role="document" style="max-width: 850px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-white" style="border-bottom: 2px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem;">
                        <h5 class="modal-title text-dark font-weight-bold" id="modalTambahAlatLabel">
                            Tambah Master Data Alat Verifikasi
                        </h5>
                        <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('verifications.tools.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="plant" value="{{ $plantCode }}">
                        <div class="modal-body bg-light px-4 py-3">
                            <div class="row">
                                <div class="col-md-6 text-left">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Nama Part</label>
                                        <input type="text" name="name_part" class="form-control form-control-sm border-0 shadow-sm" required placeholder="Contoh: HOLDER, HARNESS">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-dark">Part Code</label>
                                        <input type="text" name="part_code" class="form-control form-control-sm border-0 shadow-sm" placeholder="Contoh: 50361-K3V -N000">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Model (No. Part)</label>
                                        <input type="text" name="no_part" class="form-control form-control-sm border-0 shadow-sm" required placeholder="Contoh: K3VA">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Jenis Alat</label>
                                        <input type="text" name="tool_type" class="form-control form-control-sm border-0 shadow-sm" required placeholder="Contoh: CHECKING FIXTURE">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Customer</label>
                                        <select name="customer" class="form-control form-control-sm border-0 shadow-sm">
                                            <option value="PT. AHM">PT. AHM</option>
                                            <option value="PT. HPM">PT. HPM</option>
                                            <option value="PT. YMMI">PT. YMMI</option>
                                            <option value="PT. ADM">PT. ADM</option>
                                            <option value="LAINNYA">LAINNYA</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 text-left">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Qty (Unit)</label>
                                        <input type="number" name="quantity" class="form-control form-control-sm border-0 shadow-sm" value="1">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Frekuensi Verifikasi</label>
                                        <input type="text" name="verification_frequency" class="form-control form-control-sm border-0 shadow-sm"
                                            placeholder="Contoh: 1 Tahun" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-primary">Tanggal Rencana Verifikasi</label>
                                        <input type="date" name="planned_verification_date" class="form-control form-control-sm border-0 shadow-sm">
                                        <small class="form-text text-muted" style="font-size:0.65rem;">Jadwal P pada tabel schedule akan otomatis terupdate.</small>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Jenis Verifikasi</label>
                                        <select name="verification_type" class="form-control form-control-sm border-0 shadow-sm" required>
                                            <option value="INTERNAL">INTERNAL</option>
                                            <option value="EXTERNAL">EXTERNAL</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-gray-700">Status Drawing</label>
                                                <select name="drawing" class="form-control form-control-sm border-0 shadow-sm" required>
                                                    <option value="ADA">ADA</option>
                                                    <option value="TIDAK ADA">TIDAK ADA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-gray-700">Status Alat</label>
                                                <select name="tool_status" class="form-control form-control-sm border-0 shadow-sm" required>
                                                    <option value="AKTIF">AKTIF</option>
                                                    <option value="TIDAK AKTIF">TIDAK AKTIF</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-gray-700">Upload File Drawing (Gambar)</label>
                                        <input type="file" name="drawing_file" class="form-control-file form-control-sm" accept="image/*">
                                    </div>
                                </div>
                            </div>

                            <!-- Standar Toleransi Dimensi Part -->
                            <div class="border-top pt-3 mt-3 text-left">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="small font-weight-bold text-dark mb-0">Standar Toleransi Dimensi Part</label>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0" onclick="addDimStandardRow('tambah')" style="font-size: 0.65rem;">
                                        <i class="fas fa-plus mr-1"></i> Tambah Point
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.7rem; border-collapse: collapse; border: 1px solid #e2e8f0;">
                                        <thead style="background-color: #f8fafc;">
                                            <tr>
                                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-family: 'Nunito', sans-serif !important; text-transform: uppercase; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; border-bottom: 2px solid #cbd5e1 !important; width: 30%;">Point</th>
                                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-family: 'Nunito', sans-serif !important; text-transform: uppercase; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; border-bottom: 2px solid #cbd5e1 !important; width: 30%;">STD</th>
                                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-family: 'Nunito', sans-serif !important; text-transform: uppercase; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; border-bottom: 2px solid #cbd5e1 !important; width: 30%;">Toleransi</th>
                                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-family: 'Nunito', sans-serif !important; text-transform: uppercase; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; border-bottom: 2px solid #cbd5e1 !important; width: 10%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tambahDimStandardTbody">
                                            <!-- Dynamic rows injected via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-white py-2 px-4" style="border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Alat -->
        <div class="modal fade" id="modalEditAlat" tabindex="-1" role="dialog" aria-labelledby="modalEditAlatLabel"
            aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-lg" role="document" style="max-width: 850px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-white" style="border-bottom: 2px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem;">
                        <h5 class="modal-title text-dark font-weight-bold" id="modalEditAlatLabel">
                            Edit Master Data Alat Verifikasi
                        </h5>
                        <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="formEditTool" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="plant" value="{{ $plantCode }}">
                        <div class="modal-body bg-light px-4 py-3">
                            <div class="row">
                                <div class="col-md-6 text-left">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Nama Part</label>
                                        <input type="text" name="name_part" id="edit_name_part" class="form-control form-control-sm border-0 shadow-sm" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-dark">Part Code</label>
                                        <input type="text" name="part_code" id="edit_part_code" class="form-control form-control-sm border-0 shadow-sm">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Model (No. Part)</label>
                                        <input type="text" name="no_part" id="edit_no_part" class="form-control form-control-sm border-0 shadow-sm" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Jenis Alat</label>
                                        <input type="text" name="tool_type" id="edit_tool_type" class="form-control form-control-sm border-0 shadow-sm" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Customer</label>
                                        <input type="text" name="customer" id="edit_customer" class="form-control form-control-sm border-0 shadow-sm">
                                    </div>
                                </div>
                                <div class="col-md-6 text-left">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Qty (Unit)</label>
                                        <input type="number" name="quantity" id="edit_quantity" class="form-control form-control-sm border-0 shadow-sm" value="1">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Frekuensi Verifikasi</label>
                                        <input type="text" name="verification_frequency" id="edit_verification_frequency" class="form-control form-control-sm border-0 shadow-sm" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-info">Tanggal Rencana Verifikasi</label>
                                        <input type="date" name="planned_verification_date" id="edit_planned_verification_date" class="form-control form-control-sm border-0 shadow-sm">
                                        <small class="form-text text-muted" style="font-size:0.65rem;">Mengubah tanggal ini otomatis mengupdate jadwal P pada tabel schedule.</small>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-700">Jenis Verifikasi</label>
                                        <select name="verification_type" id="edit_verification_type" class="form-control form-control-sm border-0 shadow-sm" required>
                                            <option value="INTERNAL">INTERNAL</option>
                                            <option value="EXTERNAL">EXTERNAL</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-gray-700">Status Drawing</label>
                                                <select name="drawing" id="edit_drawing" class="form-control form-control-sm border-0 shadow-sm" required>
                                                    <option value="ADA">ADA</option>
                                                    <option value="TIDAK ADA">TIDAK ADA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-gray-700">Status Alat</label>
                                                <select name="tool_status" id="edit_tool_status" class="form-control form-control-sm border-0 shadow-sm" required>
                                                    <option value="AKTIF">AKTIF</option>
                                                    <option value="TIDAK AKTIF">TIDAK AKTIF</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-gray-700">Ganti File Drawing (Gambar)</label>
                                        <input type="file" name="drawing_file" class="form-control-file form-control-sm" accept="image/*">
                                    </div>
                                </div>
                            </div>

                             <!-- Standar Toleransi Dimensi Part -->
                            <div class="border-top pt-3 mt-3 text-left">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="small font-weight-bold text-dark mb-0">Standar Toleransi Dimensi Part</label>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0" onclick="addDimStandardRow('edit')" style="font-size: 0.65rem;">
                                        <i class="fas fa-plus mr-1"></i> Tambah Point
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.7rem; border-collapse: collapse; border: 1px solid #e2e8f0;">
                                        <thead style="background-color: #f8fafc;">
                                            <tr>
                                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-family: 'Nunito', sans-serif !important; text-transform: uppercase; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; border-bottom: 2px solid #cbd5e1 !important; width: 30%;">Point</th>
                                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-family: 'Nunito', sans-serif !important; text-transform: uppercase; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; border-bottom: 2px solid #cbd5e1 !important; width: 30%;">STD</th>
                                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-family: 'Nunito', sans-serif !important; text-transform: uppercase; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; border-bottom: 2px solid #cbd5e1 !important; width: 30%;">Toleransi</th>
                                                <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-family: 'Nunito', sans-serif !important; text-transform: uppercase; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; border-bottom: 2px solid #cbd5e1 !important; width: 10%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="editDimStandardTbody">
                                            <!-- Dynamic rows injected via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-white py-2 px-4" style="border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm">
                                <i class="fas fa-save mr-1"></i> Update Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Input Verifikasi Alat -->
        <div class="modal fade" id="modalInputVerifikasi" tabindex="-1" role="dialog" aria-labelledby="modalInputVerifikasiLabel" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-xl" role="document" style="max-width: 92vw;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-white" style="border-bottom: 2px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem;">
                        <h5 class="modal-title text-dark font-weight-bold" id="modalInputVerifikasiLabel">
                            Form Input Verifikasi Alat
                        </h5>
                        <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="formInputVerifikasi">
                        @csrf
                        <input type="hidden" id="verif_tool_id">
                        <input type="hidden" id="verif_judgment_val" name="judgment" value="OK">
                        <div class="modal-body bg-light px-4 py-3">
                            <div class="row">
                                <!-- Left Column: Tool Details & Drawing Illustration -->
                                <div class="col-md-5 text-left border-right">
                                    <!-- Ilustrasi Drawing -->
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-header bg-white py-2 px-3 border-bottom">
                                            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.75rem;">ILUSTRASI DRAWING ALAT</h6>
                                        </div>
                                        <div class="card-body p-2 text-center bg-white" style="min-height: 350px; display: flex; align-items: center; justify-content: center;">
                                            <div id="verif_drawing_container" class="w-100">
                                                <img id="verif_drawing_img" src="" alt="Ilustrasi Drawing" class="img-fluid rounded border shadow-sm" style="max-height: 420px; width: 100%; object-fit: contain; display: none; cursor: pointer;" onclick="previewDrawing(this.src, 'Ilustrasi Drawing')">
                                                <div id="verif_drawing_placeholder" class="py-5 text-muted small">
                                                    <i class="fas fa-file-image fa-4x text-gray-300 mb-2"></i>
                                                    <p class="mb-0 font-weight-bold">File Drawing belum diunggah untuk alat ini.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold text-dark">Tanggal Verifikasi <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_verifikasi" id="verif_tanggal_verifikasi" class="form-control form-control-sm border-0 shadow-sm" required value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-dark">Next Verifikasi</label>
                                                <input type="date" name="next_verifikasi" id="verif_next_verifikasi" class="form-control form-control-sm border-0 shadow-sm" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Identitas Alat Verifikasi -->
                                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                                        <div class="card-header bg-white py-2 px-3 border-bottom">
                                            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.75rem;">IDENTITAS ALAT VERIFIKASI</h6>
                                        </div>
                                        <div class="card-body p-3" style="font-size: 0.75rem;">
                                            <div class="row mb-1">
                                                <div class="col-5 font-weight-bold text-gray-700">Nama Part:</div>
                                                <div class="col-7 font-weight-bold text-dark" id="verif_display_name_part"></div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-5 font-weight-bold text-gray-700">Part Code:</div>
                                                <div class="col-7 font-weight-bold text-dark" id="verif_display_part_code"></div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-5 font-weight-bold text-gray-700">Model:</div>
                                                <div class="col-7 text-dark" id="verif_display_model"></div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-5 font-weight-bold text-gray-700">Customer:</div>
                                                <div class="col-7 text-dark" id="verif_display_customer"></div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-5 font-weight-bold text-gray-700">Frekuensi:</div>
                                                <div class="col-7 text-dark" id="verif_display_frequency"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Dimension Check & Judgment -->
                                <div class="col-md-7 text-left">
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-header bg-white py-2 px-3 border-bottom">
                                            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.75rem;">PENGUJIAN DIMENSI (STANDAR VS ACTUAL)</h6>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="table-responsive mb-2">
                                                <table class="table table-bordered table-sm text-center mb-0" id="dimensionTable" style="font-size: 0.72rem;">
                                                     <thead style="background-color: #f8fafc;">
                                                         <tr>
                                                             <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; width: 28%;">ITEM / POINT MEASURE</th>
                                                             <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; width: 20%;">STD</th>
                                                             <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; width: 22%;">TOLERANSI</th>
                                                             <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; width: 20%;">NILAI ACTUAL</th>
                                                             <th style="background-color: #f8fafc !important; color: #475569 !important; font-weight: 700 !important; font-size: 0.65rem !important; border: 1px solid #e2e8f0 !important; width: 10%;">STAT</th>
                                                         </tr>
                                                     </thead>
                                                    <tbody id="verifDimensionTbody">
                                                        <!-- Dynamic Dimension Rows injected via JS -->
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Real-time Judgment Display (Combined inside Pengujian Dimensi card) -->
                                            <div class="pt-2 px-2 mt-2 border-top d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.8rem;">FINAL JUDGMENT AUTOMATIC:</h6>
                                                    <small class="text-muted" style="font-size: 0.68rem;">Evaluasi otomatis dari seluruh nilai input pengujian dimensi.</small>
                                                </div>
                                                <div>
                                                    <span id="verifJudgmentBadgeDisplay" class="badge badge-success px-4 py-2 font-weight-bold shadow-sm" style="font-size: 1.1rem; border-radius: 20px;">
                                                        OK
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-dark">Keterangan / Catatan Verifikasi</label>
                                        <textarea name="remarks" id="verif_remarks" class="form-control form-control-sm border-0 shadow-sm" rows="3" placeholder="Masukan catatan hasil pemeriksaan verifikasi jika ada..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-white py-2 px-4" style="border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Batal</button>
                            <button type="button" onclick="submitInputVerifikasiForm()" class="btn btn-success btn-sm px-4 shadow-sm font-weight-bold">
                                <i class="fas fa-check-circle mr-1"></i> Simpan Hasil Verifikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Drawing Preview -->
        <div class="modal fade" id="modalDrawingPreview" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1070;">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 90vw;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header py-2 px-3 bg-white" style="border-bottom: 1px solid #e2e8f0;">
                        <h6 class="modal-title font-weight-bold text-dark" id="previewDrawingTitle">Preview Drawing</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-2 text-center bg-light">
                        <img id="previewDrawingImg" src="" class="img-fluid rounded shadow-sm" style="max-height: 85vh; width: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: @json(session('success')),
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: @json(session('error')),
            confirmButtonColor: '#e74a3b'
        });
    @endif

    function previewDrawing(url, name) {
        $('#previewDrawingTitle').text('Drawing Spec - ' + name);
        $('#previewDrawingImg').attr('src', url);
        $('#modalDrawingPreview').modal('show');
    }

    function addDimStandardRow(mode, pointName = '', stdVal = '', tolVal = '') {
        let tbodyId = mode === 'tambah' ? '#tambahDimStandardTbody' : '#editDimStandardTbody';
        let rowCount = $(tbodyId + ' tr').length + 1;
        
        let pName = (!pointName || pointName === 'null' || pointName === 'undefined') ? ('Point ' + rowCount) : pointName;
        let sVal = (!stdVal || stdVal === 'null' || stdVal === 'undefined') ? '' : stdVal;
        let tVal = (!tolVal || tolVal === 'null' || tolVal === 'undefined' || tolVal === 'null - null') ? '' : tolVal;

        let rowHtml = `
            <tr>
                <td class="align-middle">
                    <input type="text" name="dimension_standards[${rowCount-1}][point]" class="form-control form-control-sm text-center font-weight-bold" value="${pName}" style="font-size:0.7rem;" placeholder="">
                </td>
                <td class="align-middle">
                    <input type="text" name="dimension_standards[${rowCount-1}][standard]" class="form-control form-control-sm text-center" value="${sVal}" style="font-size:0.7rem;" placeholder="">
                </td>
                <td class="align-middle">
                    <input type="text" name="dimension_standards[${rowCount-1}][tolerance]" class="form-control form-control-sm text-center" value="${tVal}" style="font-size:0.7rem;" placeholder="">
                </td>
                <td class="align-middle">
                    <button type="button" class="btn btn-sm btn-light border-0 shadow-sm rounded-circle text-danger" onclick="$(this).closest('tr').remove();" style="width:26px; height:26px; padding:0; line-height:26px;" title="Hapus Point">
                        <i class="fas fa-trash-alt text-danger" style="font-size: 0.72rem;"></i>
                    </button>
                </td>
            </tr>
        `;
        $(tbodyId).append(rowHtml);
    }

    $('#modalTambahAlat').on('show.bs.modal', function () {
        if ($('#tambahDimStandardTbody tr').length === 0) {
            addDimStandardRow('tambah', 'Point 1', '', '');
            addDimStandardRow('tambah', 'Point 2', '', '');
            addDimStandardRow('tambah', 'Point 3', '', '');
        }
    });

    let currentToolFrequency = '1 Tahun';

    function toTitleCase(str) {
        if (!str || str.trim() === '' || str.toLowerCase() === 'alat') return 'Alat';
        return str.toLowerCase().split(' ').map(function(word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    function openInputVerifikasiModal(toolId) {
        $.ajax({
            url: '/verifications/tools/' + toolId + '/verification-data',
            type: 'GET',
            success: function(data) {
                $('#verif_tool_id').val(data.id);
                let toolTitleType = toTitleCase(data.tool_type);
                $('#modalInputVerifikasiLabel').text('Form Input Verifikasi ' + toolTitleType);
                $('#verif_name_part_title').text(data.name_part);
                $('#verif_display_name_part').text(data.name_part);
                $('#verif_display_part_code').text(data.part_code || '-');
                $('#verif_display_model').text(data.no_part || '-');
                $('#verif_display_customer').text(data.customer || '-');
                $('#verif_display_frequency').text(data.verification_frequency || '1 Tahun');
                currentToolFrequency = data.verification_frequency || '1 Tahun';

                // Drawing Preview
                if (data.drawing_url) {
                    $('#verif_drawing_img').attr('src', data.drawing_url).show();
                    $('#verif_drawing_placeholder').hide();
                } else {
                    $('#verif_drawing_img').hide();
                    $('#verif_drawing_placeholder').show();
                }

                // Render Dimension Table
                let tbody = '';
                if (data.dimensions && data.dimensions.length > 0) {
                    data.dimensions.forEach((dim, idx) => {
                        tbody += `
                            <tr>
                                <td class="font-weight-bold text-center align-middle" style="width:28%;">${dim.point}</td>
                                <td class="align-middle font-weight-bold" style="width:20%;">${dim.standard || '-'}</td>
                                <td class="align-middle text-muted font-weight-bold" style="width:22%;">${dim.tolerance || '-'}</td>
                                <td class="align-middle" style="width:20%;">
                                    <input type="number" step="0.01" class="form-control form-control-sm dimension-val-input text-center font-weight-bold" 
                                        data-std="${dim.standard || ''}"
                                        data-tol="${dim.tolerance || ''}"
                                        data-min="${dim.min !== null && dim.min !== undefined ? dim.min : ''}" 
                                        data-max="${dim.max !== null && dim.max !== undefined ? dim.max : ''}" 
                                        value="" placeholder="-" oninput="evaluateVerifDimensions()" style="font-size:0.75rem;">
                                </td>
                                <td class="align-middle" style="width:10%;">
                                    <span class="badge badge-secondary dim-row-status rounded-pill px-2">-</span>
                                </td>
                            </tr>
                        `;
                    });
                    $('#verifDimensionTbody').html(tbody);
                } else {
                    $('#verifDimensionTbody').html(`
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">
                                <i class="fas fa-exclamation-triangle text-warning mr-1"></i> Belum ada standar toleransi dimensi yang diatur untuk alat ini.<br>
                                <span class="text-xs">Silakan atur standar dimensi pada menu Edit Alat.</span>
                            </td>
                        </tr>
                    `);
                }

                // Auto calculate Next Verification Date
                updateNextVerifDate();

                evaluateVerifDimensions();
                $('#modalInputVerifikasi').modal('show');
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat data verifikasi alat.' });
            }
        });
    }

    $('#verif_tanggal_verifikasi').on('change', function() {
        updateNextVerifDate();
    });

    function updateNextVerifDate() {
        let vDateVal = $('#verif_tanggal_verifikasi').val();
        if (!vDateVal) return;

        let dateObj = new Date(vDateVal);
        let freq = (currentToolFrequency || '').toLowerCase();
        
        if (freq.includes('bulan')) {
            let months = parseInt(freq) || 1;
            dateObj.setMonth(dateObj.getMonth() + months);
        } else {
            // Default 1 year
            dateObj.setFullYear(dateObj.getFullYear() + 1);
        }

        let nextStr = dateObj.toISOString().split('T')[0];
        $('#verif_next_verifikasi').val(nextStr);
    }

    function parseMinMax(stdStr, tolStr, rawMin, rawMax) {
        if (rawMin !== '' && rawMin !== null && rawMin !== undefined && rawMin !== 'null' &&
            rawMax !== '' && rawMax !== null && rawMax !== undefined && rawMax !== 'null') {
            return { min: parseFloat(rawMin), max: parseFloat(rawMax) };
        }

        let std = parseFloat(stdStr);
        if (isNaN(std)) return { min: null, max: null };

        let tol = (tolStr || '').trim();

        // Format 1: Range "23.10 - 23.90"
        let mRange = tol.match(/^\s*([0-9.]+)\s*-\s*([0-9.]+)\s*$/);
        if (mRange) {
            return { min: parseFloat(mRange[1]), max: parseFloat(mRange[2]) };
        }

        // Format 2: Asymmetric "+0.1/-0.2" or "+0.2 / -0.4" or "+0/-0.6" or "+0.21/-0.6"
        let mAsym = tol.match(/^\s*([+-]?[0-9.]+)\s*\/\s*([+-]?[0-9.]+)\s*$/);
        if (mAsym) {
            let v1 = parseFloat(mAsym[1]);
            let v2 = parseFloat(mAsym[2]);
            let upper = Math.max(v1, v2);
            let lower = Math.min(v1, v2);
            return { min: std + lower, max: std + upper };
        }

        // Format 3: Symmetric "±0.4" or "0.4"
        let mSym = tol.match(/^\s*±?\s*([0-9.]+)\s*$/);
        if (mSym) {
            let tVal = parseFloat(mSym[1]);
            return { min: std - tVal, max: std + tVal };
        }

        return { min: null, max: null };
    }

    function evaluateVerifDimensions() {
        let hasNgRow = false;
        let hasFilledInput = false;
        let totalInputs = 0;
        let filledInputs = 0;

        $('.dimension-val-input').each(function() {
            totalInputs++;
            let rawVal = $(this).val();
            let statusBadge = $(this).closest('tr').find('.dim-row-status');

            if (rawVal === '' || rawVal === null || rawVal === undefined) {
                statusBadge.removeClass('badge-success badge-danger').addClass('badge-secondary').text('-');
                $(this).css({'border-color': '#e2e8f0', 'background-color': '#f8fafc'});
            } else {
                hasFilledInput = true;
                filledInputs++;
                let val = parseFloat(rawVal);
                let stdStr = $(this).attr('data-std');
                let tolStr = $(this).attr('data-tol');
                let rawMin = $(this).attr('data-min');
                let rawMax = $(this).attr('data-max');

                let bounds = parseMinMax(stdStr, tolStr, rawMin, rawMax);
                let isOk = true;

                if (bounds.min !== null && bounds.max !== null) {
                    if (isNaN(val) || val < bounds.min || val > bounds.max) {
                        isOk = false;
                    }
                }

                if (isOk) {
                    statusBadge.removeClass('badge-secondary badge-danger').addClass('badge-success').text('OK');
                    $(this).css({'border-color': '#28a745', 'background-color': '#f0fff4'});
                } else {
                    statusBadge.removeClass('badge-secondary badge-success').addClass('badge-danger').text('NG');
                    $(this).css({'border-color': '#dc3545', 'background-color': '#fff5f5'});
                    hasNgRow = true;
                }
            }
        });

        if (hasNgRow) {
            $('#verif_judgment_val').val('NG');
            $('#verifJudgmentBadgeDisplay').removeClass('badge-success badge-secondary').addClass('badge-danger').text('NG');
        } else if (filledInputs > 0 && filledInputs === totalInputs) {
            $('#verif_judgment_val').val('OK');
            $('#verifJudgmentBadgeDisplay').removeClass('badge-danger badge-secondary').addClass('badge-success').text('OK');
        } else {
            $('#verif_judgment_val').val('BELUM');
            $('#verifJudgmentBadgeDisplay').removeClass('badge-success badge-danger').addClass('badge-secondary').text('BELUM');
        }
    }

    function submitInputVerifikasiForm() {
        let toolId = $('#verif_tool_id').val();
        let payload = {
            _token: '{{ csrf_token() }}',
            tanggal_verifikasi: $('#verif_tanggal_verifikasi').val(),
            next_verifikasi: $('#verif_next_verifikasi').val(),
            judgment: $('#verif_judgment_val').val(),
            remarks: $('#verif_remarks').val()
        };

        $.ajax({
            url: '/verifications/tools/' + toolId + '/store-verification',
            type: 'POST',
            data: payload,
            success: function(resp) {
                $('#modalInputVerifikasi').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: resp.message || 'Hasil verifikasi berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    location.reload();
                });
            },
            error: function(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Simpan',
                    text: 'Terjadi kesalahan saat menyimpan hasil verifikasi.'
                });
            }
        });
    }

    function confirmDeleteTool(id) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: 'Data alat verifikasi ini dan semua jadwal terkait akan dihapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-tool-form-' + id).submit();
            }
        });
    }

    function editTool(id) {
        $.ajax({
            url: '/verifications/tools/' + id + '/edit',
            type: 'GET',
            success: function(data) {
                $('#formEditTool').attr('action', '/verifications/tools/' + id);
                $('#edit_name_part').val(data.name_part);
                $('#edit_part_code').val(data.part_code);
                $('#edit_no_part').val(data.no_part);
                $('#edit_tool_type').val(data.tool_type);
                $('#edit_customer').val(data.customer);
                $('#edit_quantity').val(data.quantity);
                $('#edit_verification_frequency').val(data.verification_frequency);
                $('#edit_planned_verification_date').val(data.planned_verification_date);
                $('#edit_verification_type').val(data.verification_type);
                $('#edit_drawing').val(data.drawing || 'ADA');
                $('#edit_tool_status').val(data.tool_status || 'AKTIF');

                // Populate dimension standards table
                $('#editDimStandardTbody').empty();
                if (data.dimension_standards && data.dimension_standards.length > 0) {
                    data.dimension_standards.forEach((ds, idx) => {
                        let stdVal = (ds.standard && ds.standard !== 'null') ? ds.standard : '';
                        let tolVal = (ds.tolerance && ds.tolerance !== 'null') ? ds.tolerance : '';
                        if (!tolVal && ds.min !== undefined && ds.min !== null && ds.min !== 'null' && ds.max !== undefined && ds.max !== null && ds.max !== 'null') {
                            tolVal = ds.min + ' - ' + ds.max;
                        }
                        addDimStandardRow('edit', ds.point || ('Point ' + (idx+1)), stdVal, tolVal);
                    });
                } else {
                    addDimStandardRow('edit', 'Point 1', '', '');
                    addDimStandardRow('edit', 'Point 2', '', '');
                    addDimStandardRow('edit', 'Point 3', '', '');
                }

                $('#modalEditAlat').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengambil data alat verifikasi.'
                });
            }
        });
    }
</script>
@endpush



