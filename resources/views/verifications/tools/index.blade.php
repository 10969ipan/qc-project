@extends('layouts.admin')

@section('title', 'Master Data Alat Verifikasi')

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
    #dataTable {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            border: none !important;
            width: 100% !important;
            table-layout: auto !important;
            font-size: 0.68rem;
        }
    }
    #dataTable td,
        #dataTable th {
            border-left: none !important;
            border-right: 1px solid #f1f5f9 !important;
            vertical-align: middle !important;
        }
    }
    #dataTable tbody td {
            border-bottom: 1px solid #f1f5f9 !important;
            border-top: none !important;
            color: #334155 !important;
            padding: 6px 8px !important;
        }
    }
    #dataTable thead th {
            position: sticky !important;
            top: 0 !important;
            z-index: 10 !important;
            background-color: #f8fafc !important; /* Industrial Slate like Checksheet */
            color: #475569 !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            font-size: 0.62rem !important;
            letter-spacing: 0.2px;
            padding: 8px 10px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            white-space: nowrap !important;
        }

        /* Kill Bootstrap Blue Border */
        .table thead th {
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .table {
            border: none !important;
        }

        .col-aksi {
            width: 80px !important;
            min-width: 80px !important;
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

    <div class="container-fluid">
        <!-- IPP Style Header -->
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
                                MASTER DATA ALAT VERIFIKASI (JIG, MAL, C/F)
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:0; vertical-align:middle; white-space:nowrap;">
                            <table style="width:100%; border-collapse:collapse; font-size:0.68rem; border:none;">
                                <tr>
                                    <td style="padding:2px 5px; font-weight:600; border-bottom:1px solid #dee2e6; border-right:1px solid #dee2e6;">No. Dokumen</td>
                                    <td style="padding:2px 8px; font-weight:400; border-bottom:1px solid #dee2e6;">
                                        {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-0007' : 'QC-KRW-F-0007' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:2px 5px; font-weight:600; border-bottom:1px solid #dee2e6; border-right:1px solid #dee2e6;">Tgl. Terbit</td>
                                    <td style="padding:2px 8px; font-weight:400; border-bottom:1px solid #dee2e6;">06-Jan-2025</td>
                                </tr>
                                <tr>
                                    <td style="padding:2px 5px; font-weight:600; border-bottom:1px solid #dee2e6; border-right:1px solid #dee2e6;">Revisi ke</td>
                                    <td style="padding:2px 8px; font-weight:400; border-bottom:1px solid #dee2e6;">-</td>
                                </tr>
                                <tr>
                                    <td style="padding:2px 5px; font-weight:600; border-bottom:1px solid #dee2e6; border-right:1px solid #dee2e6;">Tgl. Revisi</td>
                                    <td style="padding:2px 8px; font-weight:400; border-bottom:1px solid #dee2e6;">-</td>
                                </tr>
                                <tr>
                                    <td style="padding:2px 5px; font-weight:600; border-right:1px solid #dee2e6;">Halaman</td>
                                    <td style="padding:2px 8px; font-weight:400;">1/1</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-body">
                <!-- Filter Bar (Checksheet Style) -->
                <form action="{{ route('verifications.tools.index') }}" method="GET"
                    class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                    style="gap: 12px;" id="filterForm">
                    
                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <!-- Field: Search Item -->
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Cari:</label>
                        <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" 
                            style="width: 180px; border-radius: 0.35rem;" 
                            placeholder="Nama / No Part..." value="{{ request('search') }}">
                    </div>

                    <!-- Field: Jenis Alat -->
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Jenis:</label>
                        <select name="tool_type" class="form-control form-control-sm border-0 shadow-sm" style="width: 140px; border-radius: 0.35rem;">
                            <option value="">Semua Jenis</option>
                            @foreach($toolTypes as $type)
                                <option value="{{ $type }}" {{ request('tool_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field: Customer -->
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Customer:</label>
                        <select name="customer" class="form-control form-control-sm border-0 shadow-sm" style="width: 140px; border-radius: 0.35rem;">
                            <option value="">Semua Customer</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust }}" {{ request('customer') == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field: Verifikasi -->
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Tipe:</label>
                        <select name="verification_type" class="form-control form-control-sm border-0 shadow-sm" style="width: 120px; border-radius: 0.35rem;">
                            <option value="">Semua</option>
                            @foreach($verificationTypes as $vType)
                                <option value="{{ $vType }}" {{ request('verification_type') == $vType ? 'selected' : '' }}>{{ $vType }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field: Drawing -->
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Dwg:</label>
                        <select name="drawing" class="form-control form-control-sm border-0 shadow-sm" style="width: 100px; border-radius: 0.35rem;">
                            <option value="">Semua</option>
                            @foreach($drawings as $dwg)
                                <option value="{{ $dwg }}" {{ request('drawing') == $dwg ? 'selected' : '' }}>{{ $dwg }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field: Judgment -->
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Judg:</label>
                        <select name="judgment" class="form-control form-control-sm border-0 shadow-sm" style="width: 100px; border-radius: 0.35rem;">
                            <option value="">Semua</option>
                            @foreach($judgments as $jdg)
                                <option value="{{ $jdg }}" {{ request('judgment') == $jdg ? 'selected' : '' }}>{{ $jdg }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Field: Tool Status -->
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Status:</label>
                        <select name="tool_status" class="form-control form-control-sm border-0 shadow-sm" style="width: 110px; border-radius: 0.35rem;">
                            <option value="">Semua</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" {{ request('tool_status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="ml-auto d-flex" style="gap: 5px;">
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-2" title="Cari Data">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                        <a href="{{ route('verifications.tools.index', ['plant' => $plantCode]) }}"
                            class="btn btn-secondary btn-sm shadow-sm rounded-pill px-2" title="Reset Filter">
                            <i class="fas fa-undo fa-sm"></i>
                        </a>
                        <button type="button" class="btn btn-success btn-sm shadow-sm rounded-pill px-2 ml-1" data-toggle="modal"
                            data-target="#modalTambahAlat">
                            <i class="fas fa-plus-circle fa-sm"></i>
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="col-no">NO.</th>
                                <th class="col-part-name">NAMA PART</th>
                                <th class="col-part-no">NO. PART</th>
                                <th class="col-tool-type">JENIS ALAT</th>
                                <th class="col-cust">CUSTOMER</th>
                                <th class="col-qty">QTY</th>
                                <th class="col-freq">FREKUENSI</th>
                                <th class="col-type">JENIS VERIFIKASI</th>
                                <th class="col-drawing">DRAWING</th>
                                <th class="col-judgment">JUDGMENT</th>
                                <th class="col-status-text">STATUS</th>
                                <th class="col-stat-icon">STAT</th>
                                <th class="col-aksi no-export">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tools as $index => $tool)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-left font-weight-bold" style="color: #1e293b;">{{ $tool->name_part }}</td>
                                    <td class="text-nowrap">{{ $tool->no_part }}</td>
                                    <td>{{ $tool->tool_type }}</td>
                                    <td>{{ $tool->customer }}</td>
                                    <td>{{ $tool->quantity }}</td>
                                    <td>{{ $tool->verification_frequency }}</td>
                                    <td>{{ $tool->verification_type }}</td>
                                    <td>
                                        <span class="badge {{ $tool->drawing === 'ADA' ? 'badge-success' : 'badge-danger' }}" 
                                            style="font-size: 0.65rem; min-width: 60px; padding: 4px 6px;">
                                            {{ $tool->drawing }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $tool->tool_judgment === 'OK' ? 'badge-success' : ($tool->tool_judgment === 'NG' ? 'badge-danger' : 'badge-secondary') }}" 
                                            style="font-size: 0.65rem; min-width: 60px; padding: 4px 6px;">
                                            {{ $tool->tool_judgment ?? 'BELUM' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ ($tool->tool_status ?? 'AKTIF') === 'AKTIF' ? 'badge-primary' : 'badge-warning text-dark' }}" 
                                            style="font-size: 0.65rem; min-width: 75px; padding: 4px 6px;">
                                            {{ $tool->tool_status ?? 'AKTIF' }}
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
                                        <div class="d-flex justify-content-center" style="gap: 4px;">
                                            <button type="button" class="btn btn-sm btn-info shadow-sm"
                                                onclick="editTool('{{ $tool->id }}')" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm"
                                                onclick="confirmDeleteTool('{{ $tool->id }}')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="delete-tool-form-{{ $tool->id }}"
                                                action="{{ route('verifications.tools.destroy', $tool->id) }}" method="POST"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="plant" value="{{ $plantCode }}">
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4 text-muted small">Tidak ada data alat ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Alat -->
        <div class="modal fade" id="modalTambahAlat" tabindex="-1" role="dialog" aria-labelledby="modalTambahAlatLabel"
            aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white py-2">
                        <h5 class="modal-title" id="modalTambahAlatLabel" style="font-size: 1rem;">
                            <i class="fas fa-plus-circle mr-2"></i> Tambah Master Data Alat Verifikasi
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('verifications.tools.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plant" value="{{ $plantCode }}">
                        <div class="modal-body p-3">
                            <div class="row">
                                <div class="col-md-6 text-left">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Nama Part</label>
                                        <input type="text" name="name_part" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">No. Part</label>
                                        <input type="text" name="no_part" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Jenis Alat</label>
                                        <input type="text" name="tool_type" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Customer</label>
                                        <input type="text" name="customer" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-md-6 text-left">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Qty (Unit)</label>
                                        <input type="number" name="quantity" class="form-control form-control-sm" value="1">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Frekuensi Verifikasi</label>
                                        <input type="text" name="verification_frequency" class="form-control form-control-sm"
                                            placeholder="Contoh: 1 Tahun" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Jenis Verifikasi</label>
                                        <select name="verification_type" class="form-control form-control-sm" required>
                                            <option value="INTERNAL">INTERNAL</option>
                                            <option value="EXTERNAL">EXTERNAL</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Drawing</label>
                                                <select name="drawing" class="form-control form-control-sm" required>
                                                    <option value="ADA">ADA</option>
                                                    <option value="TIDAK ADA">TIDAK ADA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Status</label>
                                                <select name="tool_status" class="form-control form-control-sm" required>
                                                    <option value="AKTIF">AKTIF</option>
                                                    <option value="TIDAK AKTIF">TIDAK AKTIF</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-left mb-0">
                                <label class="small font-weight-bold">Riwayat Kalibrasi / Keterangan</label>
                                <textarea name="calibration_history" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function confirmDeleteTool(id) {
        if (confirm('Yakin ingin menghapus data alat ini? Semua jadwal terkait juga akan terhapus.')) {
            document.getElementById('delete-tool-form-' + id).submit();
        }
    }

    function editTool(id) {
        alert('Fitur edit modal sedang disinkronkan. Mohon tunggu.');
    }
</script>
@endpush



