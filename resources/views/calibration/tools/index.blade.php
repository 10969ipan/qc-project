@extends('layouts.admin')

@section('title', 'Master Data Alat Ukur')

@section('content')
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
                        MASTER DATA ALAT UKUR - {{ strtoupper($plantCode) }}
                    </h1>
                </td>
                <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                    <table style="border-collapse:collapse; font-size:0.68rem;">
                        <tr>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                            <td style="padding:1px 2px;">:</td>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                {{ $plantCode === 'jakarta' ? 'QC-JKT-F-0215' : 'QC-KRW-F-0215' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                            <td style="padding:1px 2px;">:</td>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">28/11/2019</td>
                        </tr>
                        <tr>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                            <td style="padding:1px 2px;">:</td>
                            <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">- / -</td>
                        </tr>
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
        <!-- Filter Bar Minimalis (Style In-Process) -->
        <form action="{{ route('calibration.tools.index') }}" method="GET"
            class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
            style="gap: 12px; overflow-x: auto; white-space: nowrap;" id="filterForm">

            <input type="hidden" name="plant" value="{{ $plantCode }}">

            <!-- Tahun -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tahun:</label>
                <div style="width: 100px;">
                    <select name="year" class="form-control form-control-sm border-0 shadow-sm" onchange="this.form.submit()">
                        <option value="all" {{ $year == 'all' ? 'selected' : '' }}>Semua</option>
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Nama Alat -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Alat:</label>
                <div style="width: 180px;" class="custom-filter-wrapper">
                    <select name="name_alat" class="form-control form-control-sm border-0 shadow-sm select2-filter d-none">
                        <option value="">Semua Alat</option>
                        @foreach($uniqueNames as $name)
                            <option value="{{ $name }}" {{ request('name_alat') == $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Bagian -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Bagian:</label>
                <div style="width: 130px;" class="custom-filter-wrapper">
                    <select name="bagian" class="form-control form-control-sm border-0 shadow-sm select2-filter d-none">
                        <option value="">Semua</option>
                        @foreach($uniqueBagian as $b)
                            <option value="{{ $b }}" {{ request('bagian') == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Stat:</label>
                <div style="width: 140px;">
                    <select name="status_kalibrasi" class="form-control form-control-sm border-0 shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="calibrated" {{ request('status_kalibrasi') == 'calibrated' ? 'selected' : '' }}>Terkalibrasi (OK)</option>
                        <option value="overdue" {{ request('status_kalibrasi') == 'overdue' ? 'selected' : '' }}>Overdue (Telat)</option>
                        <option value="broken" {{ request('status_kalibrasi') == 'broken' ? 'selected' : '' }}>Rusak (Broken)</option>
                    </select>
                </div>
            </div>

            <!-- Cari Cepat -->
            <div class="d-flex align-items-center">
                <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" 
                    placeholder="Cari No. Seri, Merk..." value="{{ request('search') }}" style="width: 160px; font-size: 0.75rem;">
            </div>

            <!-- Tombol Aksi -->
            <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Filter">
                    <i class="fas fa-search fa-sm"></i>
                </button>
                <a href="{{ route('calibration.tools.index', ['plant' => $plantCode]) }}"
                    class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" title="Reset Filter">
                    <i class="fas fa-undo fa-sm"></i>
                </a>
                <a href="{{ route('calibration.tools.problem-logs') }}"
                    class="btn btn-warning btn-sm shadow-sm rounded-pill px-3" title="Problem Logs">
                    <i class="fas fa-exclamation-circle fa-sm mr-1"></i> <span class="small font-weight-bold">Log</span>
                </a>
                <a href="{{ route('calibration.tools.pdf', request()->query()) }}" class="btn btn-danger btn-sm shadow-sm rounded-pill px-3" target="_blank" title="Export PDF">
                    <i class="fas fa-file-pdf fa-sm"></i>
                </a>
                <a href="{{ route('calibration.tools.print', request()->query()) }}" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" target="_blank" title="Print" style="background-color: #17a589; border-color: #17a589; color: white;">
                    <i class="fas fa-print fa-sm"></i>
                </a>
                <button type="button" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalTambahAlat" title="Tambah Alat">
                    <i class="fas fa-plus fa-sm"></i>
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>BAGIAN</th>
                        <th>NAMA ALAT</th>
                        <th>MERK</th>
                        <th>NO. SERI</th>
                        <th>RENTANG UKUR</th>
                        <th>RESOLUSI</th>
                        <th>TGL. BELI</th>
                        <th>FREKUENSI KALIBRASI</th>
                        <th>RIWAYAT KALIBRASI</th>
                        <th>JENIS KALIBRASI</th>
                        <th>PR NUMBER</th>
                        <th>STATUS</th>
                        <th class="no-export">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tools as $index => $tool)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $tool->bagian }}</td>
                            <td class="text-left font-weight-bold">{{ $tool->name_alat }}</td>
                            <td>{{ $tool->merk ?? '-' }}</td>
                            <td>{{ $tool->serial_number }}</td>
                            <td>{{ $tool->range }}</td>
                            <td>{{ $tool->resolusi }}</td>
                            <td>{{ $tool->tanggal_beli ? $tool->tanggal_beli->format('d/m/Y') : '-' }}</td>
                            <td>{{ $tool->frekuensi_kalibrasi }}</td>
                            <td>{{ $tool->all_verifications_count }} Kali</td>
                            <td>{{ Str::title($tool->jenis_kalibrasi) }}</td>
                            <td>
                                @if(strtoupper($tool->jenis_kalibrasi) === 'EKSTERNAL')
                                    @php
                                        $existingPr = null;
                                        foreach ($tool->schedules as $sch) {
                                            if ($sch->pr_number) { $existingPr = $sch->pr_number; break; }
                                        }
                                    @endphp
                                    <input type="text" class="form-control form-control-sm pr-input text-center no-autoupper"
                                        data-tool-id="{{ $tool->id }}" placeholder="PR..." value="{{ $existingPr }}"
                                        style="width: 70px; font-size: 0.65rem; padding: 2px;">
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                 @php
                                    $status = $tool->status_kalibrasi;
                                    $icon = '';
                                    if ($status === 'calibrated') $icon = '<i class="fas fa-check-circle text-success" style="font-size: 1rem;" title="OK"></i>';
                                    elseif ($status === 'due_soon') $icon = '<i class="fas fa-calendar-check text-warning" style="font-size: 1rem;" title="Mendekati Jadwal"></i>';
                                    elseif ($status === 'overdue') $icon = '<i class="fas fa-exclamation-circle text-danger" style="font-size: 1rem;" title="Overdue"></i>';
                                    elseif ($status === 'problem') $icon = '<i class="fas fa-wrench text-warning" style="font-size: 1rem;" title="Bermasalah"></i>';
                                    elseif ($status === 'broken') $icon = '<i class="fas fa-times-circle text-secondary" style="font-size: 1rem;" title="Rusak"></i>';
                                    else $icon = '<i class="fas fa-question-circle text-muted" style="font-size: 1rem;" title="Unknown"></i>';
                                    
                                    $lastVerif = $tool->latestVerification;
                                @endphp
                                <div class="d-flex flex-column align-items-center">
                                    {!! $icon !!}
                                    @if($lastVerif && $lastVerif->tanggal_verifikasi)
                                        <span class="small font-weight-bold text-gray-600 mt-1" style="font-size: 0.65rem;">
                                            {{ \Carbon\Carbon::parse($lastVerif->tanggal_verifikasi)->format('d/m/y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="no-export">
                                <div class="d-flex justify-content-center flex-wrap" style="gap: 2px;">
                                    @if($tool->certification_path)
                                    <button type="button" class="btn btn-primary btn-sm view-pdf shadow-sm" 
                                        data-toggle="modal" 
                                        data-target="#pdfModal" 
                                        data-url="{{ route('calibration.tools.serve-pdf', $tool->id) }}"
                                        data-title="Sertifikat - {{ $tool->name_alat }}">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    @endif

                                    <button type="button" class="btn btn-success btn-sm btn-verifikasi shadow-sm" 
                                        data-tool-id="{{ $tool->id }}" 
                                        data-toggle="modal" 
                                        data-target="#modalVerifikasiBaru" 
                                        title="Verifikasi">
                                        <i class="fas fa-check-circle"></i>
                                    </button>

                                    <button type="button" class="btn btn-info btn-sm btn-edit-tool shadow-sm" data-id="{{ $tool->id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-warning btn-sm btn-report-problem shadow-sm" 
                                        data-id="{{ $tool->id }}" 
                                        data-name="{{ $tool->name_alat }}" 
                                        title="Lapor Masalah">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>

                                    @if(auth()->user()->role === 'admin')
                                    <form action="{{ route('calibration.tools.destroy', $tool->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm shadow-sm btn-delete" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Card Legenda Status Terpisah -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0 overflow-hidden">
        <div class="row no-gutters">
            <!-- Kolom 1 -->
            <div class="col-md-3 p-3 border-right">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-check-circle text-success mr-2" style="font-size: 1.1rem;"></i>
                    <span class="small font-weight-bold text-muted">Sudah Verifikasi</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-alt text-warning mr-2" style="font-size: 1.1rem;"></i>
                    <span class="small font-weight-bold" style="color: #f1c40f;">Mendekati Jadwal Verifikasi</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-hourglass-half text-primary mr-2" style="font-size: 1.1rem;"></i>
                    <span class="small font-weight-bold" style="color: #3498db;">PR Out - Menunggu Verifikasi</span>
                </div>
            </div>
            <!-- Kolom 2 -->
            <div class="col-md-3 p-3 border-right">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-check text-info mr-2" style="font-size: 1.1rem;"></i>
                    <span class="small font-weight-bold" style="color: #17a2b8;">Alat Internal - Menunggu Verifikasi</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-times text-secondary mr-2" style="font-size: 1.1rem;"></i>
                    <span class="small font-weight-bold text-secondary">Alat Eksternal - Belum Ada PR</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-wrench text-secondary mr-2" style="font-size: 1.1rem;"></i>
                    <span class="small font-weight-bold text-secondary">Alat Cadangan</span>
                </div>
            </div>
            <!-- Kolom 3 -->
            <div class="col-md-3 p-3 border-right">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-exclamation-circle text-danger mr-2" style="font-size: 1.1rem;"></i>
                    <span class="small font-weight-bold" style="color: #e74c3c;">Melewati Jadwal Planning</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-wrench text-warning mr-2" style="font-size: 1.1rem;"></i>
                    <span class="small font-weight-bold" style="color: #f39c12;">Alat Bermasalah / Judgment</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge badge-danger mr-2" style="font-size: 0.6rem;">BROKEN</span>
                    <span class="small font-weight-bold" style="color: #e74c3c;">Alat Rusak / Tidak Digunakan</span>
                </div>
            </div>
            <!-- Catatan -->
            <div class="col-md-3 p-3 bg-light">
                <span class="small font-weight-bold d-block mb-1">Catatan:</span>
                <ul class="pl-3 mb-0" style="font-size: 0.65rem; color: #6c757d;">
                    <li>Icon status berubah otomatis berdasarkan verifikasi & planning.</li>
                    <li>Klik pada icon untuk melihat detail per bulan.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Modals and Scripts kept at the bottom --}}
@include('calibration.tools.modals')
@include('calibration.tools.modal_verif')

{{-- Inline Auto-Calc Script --}}
<script id="calibration-tools-data" type="application/json"
    data-plant-code="{{ $plantCode }}"
    data-year="{{ $year ?? date('Y') }}"
    data-csrf="{{ csrf_token() }}"
    data-route-index="{{ route('calibration.tools.index') }}"
    data-route-update-pr="{{ route('calibration.tools.update-pr') }}"
    data-route-edit="{{ route('calibration.tools.edit', ':id') }}"
    data-route-update="{{ route('calibration.tools.update', ':id') }}">
    @json($availableYears)
</script>
<script>
    (function() {
        const dataEl = document.getElementById('calibration-tools-data');
        if (!dataEl) return;

        window.__CALIBRATION_TOOLS__ = {
            plantCode: dataEl.getAttribute('data-plant-code'),
            year: dataEl.getAttribute('data-year'),
            availableYears: JSON.parse(dataEl.textContent),
            csrf: dataEl.getAttribute('data-csrf'),
            routes: {
                index: dataEl.getAttribute('data-route-index'),
                updatePr: dataEl.getAttribute('data-route-update-pr'),
                edit: dataEl.getAttribute('data-route-edit'),
                update: dataEl.getAttribute('data-route-update')
            }
        };
    })();
</script>

@endsection

@push('scripts')
<script src="{{ asset('js/calibration/calibration-tools.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/vendor/item-search.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 like in In-Process
        if (typeof window.initCustomItemSearch === 'function') {
            window.initCustomItemSearch('#filterForm select.select2-filter');
        }
    });

    // PDF Modal Handler
    $('#pdfModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var url = button.data('url');
        var title = button.data('title');
        var modal = $(this);
        modal.find('#pdfModalLabel').text(title);
        modal.find('#pdfFrame').attr('src', url);
        var downloadUrl = url + (url.indexOf('?') !== -1 ? '&' : '?') + 'download=1';
        modal.find('#downloadPdf').attr('href', downloadUrl);
    });
    $('#pdfModal').on('hidden.bs.modal', function () {
        $(this).find('#pdfFrame').attr('src', '');
    });

    // Inline failsafe for Edit Tool to bypass caching/loading issues
    window.openEditToolModal = function (id, btnElement) {
        console.log('--- Edit Modal Failsafe Triggered ---');
        
        if (typeof $ !== 'undefined') {
            $('#global-loader').fadeOut(200);
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow', 'auto');
        }

        if (!window.__CALIBRATION_TOOLS__ || !window.__CALIBRATION_TOOLS__.routes) {
            alert('Konfigurasi sistem belum siap. Mohon refresh halaman.');
            return;
        }

        var btn = btnElement ? $(btnElement) : $(`.btn-edit-tool[data-id="${id}"]`);
        var originalContent = btn.html();
        if (btn.length) btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        var editUrl = window.__CALIBRATION_TOOLS__.routes.edit.replace(':id', id);

        $.ajax({
            url: editUrl,
            type: 'GET',
            data: { plant: window.__CALIBRATION_TOOLS__.plantCode },
            success: function (response) {
                if (!response || !response.tool) {
                    alert('Gagal mengambil data.');
                    if (btn.length) btn.prop('disabled', false).html(originalContent);
                    return;
                }

                const tool = response.tool;
                $('#edit_bagian').val(tool.bagian);
                $('#edit_name_alat').val(tool.name_alat);
                $('#edit_merk').val(tool.merk);
                $('#edit_serial_number').val(tool.serial_number);
                $('#edit_range').val(tool.range);
                $('#edit_resolusi').val(tool.resolusi);
                $('#edit_tanggal_beli').val(tool.tanggal_beli_formatted || '');
                $('#edit_frekuensi_kalibrasi').val(tool.frekuensi_kalibrasi);
                $('#edit_jenis_kalibrasi').val(tool.jenis_kalibrasi);
                
                $('#edit_existing_cert').html(tool.certification_path 
                    ? `<a href="/storage/${tool.certification_path}" target="_blank" class="badge badge-info"><i class="fas fa-file-pdf mr-1"></i> Sertifikat</a>` 
                    : '');

                let schHtml = '';
                if (tool.schedules && tool.schedules.length > 0) {
                    tool.schedules.forEach(sch => {
                        schHtml += `<tr>
                            <td><input type="hidden" name="schedule_ids[]" value="${sch.id}"><input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${sch.schedule_date_formatted}"></td>
                            <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" value="${sch.pr_number || ''}" placeholder="PR..."></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                    });
                }
                $('#edit-schedule-table tbody').html(schHtml);
                $('#formEditAlat').attr('action', window.__CALIBRATION_TOOLS__.routes.update.replace(':id', id));
                $('#modalEditAlat').modal('show');
                if (btn.length) btn.prop('disabled', false).html(originalContent);
            },
            error: function () {
                alert('Gagal mengambil data alat.');
                if (btn.length) btn.prop('disabled', false).html(originalContent);
            }
        });
    };
</script>
@endpush
