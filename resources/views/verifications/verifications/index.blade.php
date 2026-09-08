@extends('layouts.admin')

@section('title', 'Hasil Verifikasi Alat')

@section('content')
<style>
    .table-responsive {
        max-height: 72vh !important;
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
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
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
        font-size: 0.70rem !important;
        padding: 5px 8px !important;
        line-height: 1.2 !important;
    }

    /* Sticky Header matching Tools index */
    #dataTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 105 !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
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

    #dataTable .badge {
        font-size: 0.63rem !important;
        padding: 0.25rem 0.65rem !important;
        border-radius: 50rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        display: inline-block;
        min-width: 55px;
    }
</style>

<div class="container-fluid px-2">
    <!-- Single Unified Card Container (IPP Header + Plant Tabs & Filter + Table) -->
    <div class="card shadow mb-2 border-0" style="border-radius: 10px;">
        <div class="card-body p-2">
            <!-- IPP Style Header -->
            <div class="mb-2">
                <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e8;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e8; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e8; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                HASIL VERIFIKASI ALAT (JIG, MAL, C/F)
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e8; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                            <table style="border-collapse:collapse; font-size:0.68rem;">
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">QC-F-VER-002</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">06-Jan-2025</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Plant</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap; text-transform:uppercase;">{{ $plantCode }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Plant Selector Tabs & Filter Bar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between bg-light p-2 rounded mb-2 shadow-sm" style="gap: 8px;">
                <!-- Plant Tabs -->
                <div class="nav nav-pills" role="tablist">
                    <a class="nav-link btn-sm px-3 py-1 font-weight-bold {{ strtolower($plantCode) === 'jakarta' ? 'active bg-primary text-white shadow-sm' : 'text-gray-700 bg-white border' }}" 
                       href="{{ route('verifications.verifications.index', array_merge(request()->query(), ['plant' => 'jakarta'])) }}" 
                       style="font-size: 0.72rem; border-radius: 6px;">
                       <i class="fas fa-building mr-1"></i> JAKARTA
                    </a>
                    <a class="nav-link btn-sm px-3 py-1 font-weight-bold ml-1 {{ strtolower($plantCode) === 'karawang' ? 'active bg-primary text-white shadow-sm' : 'text-gray-700 bg-white border' }}" 
                       href="{{ route('verifications.verifications.index', array_merge(request()->query(), ['plant' => 'karawang'])) }}" 
                       style="font-size: 0.72rem; border-radius: 6px;">
                       <i class="fas fa-industry mr-1"></i> KARAWANG
                    </a>
                </div>

                <!-- Search Form -->
                <form action="{{ route('verifications.verifications.index') }}" method="GET" class="d-flex align-items-center" style="gap: 6px;">
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div style="width: 220px;">
                        <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" 
                            style="border-radius: 0.35rem; font-size:0.75rem;" 
                            placeholder="Cari Nama Part / Model..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary shadow-sm px-3" style="font-size:0.72rem;">
                        <i class="fas fa-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('verifications.verifications.index', ['plant' => $plantCode]) }}" class="btn btn-sm btn-secondary shadow-sm px-2" style="font-size:0.72rem;" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Unified Table Section -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center" id="dataTable" width="100%">
                    <thead>
                        <tr>
                            <th style="width: 4%;">NO.</th>
                            <th style="width: 28%;" class="text-left">IDENTITAS ALAT VERIFIKASI</th>
                            <th style="width: 14%;">TANGGAL VERIFIKASI</th>
                            <th style="width: 32%;">STANDAR & HASIL DIMENSI</th>
                            <th style="width: 8%;">JUDGMENT</th>
                            <th style="width: 10%;">CATATAN</th>
                            <th style="width: 4%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verifications as $index => $v)
                            <tr>
                                <td class="align-middle font-weight-bold text-gray-700">{{ $index + 1 }}</td>
                                
                                <!-- Identitas Alat Verifikasi -->
                                <td class="text-left align-middle py-2 px-2" style="min-width: 200px;">
                                    <div class="font-weight-bold text-dark text-uppercase mb-1" style="font-size:0.76rem;">
                                        {{ $v->name_part }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.68rem; line-height: 1.35;">
                                        <div><span class="font-weight-bold text-dark">Part Code:</span> <span class="font-weight-bold text-dark">{{ $v->tool->part_code ?? '-' }}</span></div>
                                        <div><span class="font-weight-bold text-dark">Model:</span> <span class="text-dark">{{ $v->no_part }}</span></div>
                                        <div><span class="font-weight-bold text-dark">Customer:</span> <span class="text-dark">{{ $v->tool->customer ?? '-' }}</span></div>
                                        <div><span class="font-weight-bold text-dark">Jenis Alat:</span> <span class="text-dark">{{ $v->tool->tool_type ?? '-' }}</span></div>
                                        <div><span class="font-weight-bold text-dark">Frekuensi:</span> <span class="text-dark">{{ $v->tool->verification_frequency ?? '-' }}</span></div>
                                    </div>
                                </td>

                                <!-- Tanggal Verifikasi -->
                                <td class="align-middle text-center" style="white-space:nowrap;">
                                    <div class="font-weight-bold text-dark" style="font-size:0.75rem;">
                                        {{ $v->tanggal_verifikasi?->format('d/m/Y') ?? '-' }}
                                    </div>
                                    <div class="small text-muted mt-1" style="font-size:0.65rem;">
                                        Next: <span class="font-weight-bold text-dark">{{ $v->next_verifikasi?->format('d/m/Y') ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- Standar & Hasil Dimensi -->
                                <td class="align-middle p-1">
                                    @if($v->tool && !empty($v->tool->dimension_standards) && count($v->tool->dimension_standards) > 0)
                                        <div class="table-responsive" style="max-height: 120px; overflow-y: auto;">
                                            <table class="table table-bordered table-sm m-0 text-center bg-white" style="font-size:0.65rem; border-collapse:collapse; border: 1px solid #e2e8f0;">
                                                <thead style="background-color: #f8fafc;">
                                                    <tr>
                                                        <th style="padding: 2px 4px !important; font-size: 0.60rem !important; border: 1px solid #cbd5e1 !important; color:#475569 !important;">POINT</th>
                                                        <th style="padding: 2px 4px !important; font-size: 0.60rem !important; border: 1px solid #cbd5e1 !important; color:#475569 !important;">STD</th>
                                                        <th style="padding: 2px 4px !important; font-size: 0.60rem !important; border: 1px solid #cbd5e1 !important; color:#475569 !important;">TOLERANSI</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($v->tool->dimension_standards as $dim)
                                                        <tr>
                                                            <td style="padding: 2px 4px !important; font-weight:700; border: 1px solid #e2e8f0 !important; color:#334155;">
                                                                {{ $dim['point'] ?? 'Point' }}
                                                            </td>
                                                            <td style="padding: 2px 4px !important; font-weight:600; border: 1px solid #e2e8f0 !important; color:#1e293b;">
                                                                {{ $dim['standard'] ?? '-' }}
                                                            </td>
                                                            <td style="padding: 2px 4px !important; color:#64748b; font-weight:600; border: 1px solid #e2e8f0 !important;">
                                                                {{ $dim['tolerance'] ?? (isset($dim['min']) && isset($dim['max']) ? ($dim['min'] . ' - ' . $dim['max']) : '-') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle text-center">
                                    <span class="badge {{ $v->judgment == 'OK' ? 'badge-success' : ($v->judgment == 'NG' ? 'badge-danger' : 'badge-secondary') }}">
                                        {{ $v->judgment ?? 'OK' }}
                                    </span>
                                </td>

                                <!-- Catatan / Remarks -->
                                <td class="align-middle text-left" style="font-size:0.68rem; color:#475569;">
                                    {{ $v->remarks ?: '-' }}
                                </td>

                                <!-- Aksi -->
                                <td class="align-middle text-center">
                                    @if($v->certification_path)
                                        <a href="{{ asset('storage/' . $v->certification_path) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm rounded-circle p-1" title="Lihat Sertifikat PDF" style="width:28px; height:28px; line-height:20px;">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted small">
                                    <i class="fas fa-info-circle mr-1"></i> Belum ada data hasil verifikasi untuk plant ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

