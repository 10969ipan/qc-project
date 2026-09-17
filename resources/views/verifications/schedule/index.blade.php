@extends('layouts.admin')

@section('title', 'Jadwal Verifikasi Jig, Mal, C/F')

@section('content')
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
                            SCHEDULE VERIFIKASI JIG, MAL, DAN C/F
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

        .schedule-table {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            border: none !important;
            width: 2040px;
            min-width: 2040px;
            table-layout: fixed;
            font-size: 0.62rem;
            background-color: white;
        }

        .schedule-table td,
        .schedule-table th {
            border-left: none !important;
            border-right: 1px solid #f1f5f9 !important;
            vertical-align: middle !important;
            padding: 3px 4px !important;
            line-height: 1.15 !important;
        }

        .schedule-table tbody td {
            border-bottom: 1px solid #f1f5f9 !important;
            border-top: none !important;
            color: #334155 !important;
        }

        .schedule-table thead th {
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
            background-color: #f8fafc !important; /* Industrial Slate */
            color: #475569 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.58rem !important;
            letter-spacing: 0.1px;
            padding: 4px 4px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            white-space: normal !important;
            text-align: center !important;
        }

        /* Compact Sticky Left Columns positioning - Zero Wasted Space */
        .col-no { width: 35px; min-width: 35px; left: 0px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .tool-name-col { width: 150px; min-width: 150px; left: 35px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .part-no-col { width: 80px; min-width: 80px; left: 185px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .tool-type-col { width: 95px; min-width: 95px; left: 265px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .col-customer { width: 85px; min-width: 85px; left: 360px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .col-qty { width: 35px; min-width: 35px; left: 445px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .col-freq { width: 110px; min-width: 110px; left: 480px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .col-history { width: 100px; min-width: 100px; left: 590px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .col-verif-type { width: 95px; min-width: 95px; left: 690px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .status-col { width: 40px; min-width: 40px; left: 785px; position: sticky; z-index: 102; background-color: #f8fafc !important; border-right: 2px solid #cbd5e1 !important; border-bottom: 1px solid #f1f5f9 !important; }

        /* Ensure sticky headers are above sticky columns */
        thead th.col-no, thead th.tool-name-col, thead th.part-no-col, thead th.tool-type-col, 
        thead th.col-customer, thead th.col-qty, thead th.col-freq, thead th.col-history, 
        thead th.col-verif-type, thead th.status-col {
            z-index: 105 !important;
            background-color: #f8fafc !important;
        }

        /* Excel-identical Markers */
        .marker-p { background-color: #fef08a !important; color: #854d0e !important; font-weight: 700 !important; font-size: 0.58rem; }
        .marker-a { background-color: #bbf7d0 !important; color: #15803d !important; font-weight: 700 !important; font-size: 0.58rem; }
        .marker-ng { background-color: #fecaca !important; color: #b91c1c !important; font-weight: 700 !important; font-size: 0.58rem; }

        .week-header {
            background-color: #f1f5f9 !important;
            color: #64748b !important;
            font-size: 0.55rem !important;
            width: 22px !important;
            min-width: 22px !important;
            padding: 2px 0px !important;
        }

        .month-header {
            background-color: #f8fafc !important;
            border-bottom: 2px solid #e2e8f0 !important;
        }

        #filterForm .form-control-sm {
            font-size: 0.75rem !important;
            border: none !important;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
            background: white !important;
            border-radius: 0.35rem !important;
        }
        
        .schedule-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }
    </style>

    <div class="card shadow mb-4">
        <div class="card-body">
            <!-- Filter Bar (Checksheet Style) -->
            <form action="{{ route('verifications.schedule.index') }}" method="GET"
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

                <!-- Field: Year -->
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Tahun:</label>
                    <select name="year" class="form-control form-control-sm border-0 shadow-sm" style="width: 85px; border-radius: 0.35rem;">
                        @php
                            $currentYear = date('Y');
                            $selectedYear = $year ?? $currentYear;
                        @endphp
                        @for($y = $currentYear - 2; $y <= $currentYear + 2; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="ml-auto d-flex align-items-center" style="gap: 12px;">
                    <div class="d-flex align-items-center bg-white px-2 py-1 rounded shadow-sm mr-2" style="gap: 8px; border: 1px solid #f1f5f9;">
                        <span class="d-flex align-items-center" style="gap: 4px;">
                            <span class="badge" style="background-color: #fef08a; color: #854d0e; font-size: 0.6rem; border: 1px solid #fef08a;">P</span>
                            <span class="small font-weight-bold text-muted" style="font-size: 0.65rem;">Rencana</span>
                        </span>
                        <span class="d-flex align-items-center" style="gap: 4px;">
                            <span class="badge" style="background-color: #bbf7d0; color: #15803d; font-size: 0.6rem; border: 1px solid #bbf7d0;">A</span>
                            <span class="small font-weight-bold text-muted" style="font-size: 0.65rem;">Aktual/OK</span>
                        </span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm px-3 font-weight-bold" style="font-size: 0.7rem; border-radius: 0.35rem;">
                        <i class="fas fa-search fa-sm mr-1"></i> CARI
                    </button>
                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'karu_qc', 'manager', 'asst_manager']))
                        <button type="button" class="btn btn-success btn-sm shadow-sm px-3 font-weight-bold ml-1" style="font-size: 0.7rem; border-radius: 0.35rem;" data-toggle="modal" data-target="#modalImportExcel">
                            <i class="fas fa-file-excel fa-sm mr-1"></i> IMPORT EXCEL
                        </button>
                    @endif
                </div>
            </form>

            <!-- Industrial Schedule Table (Compact Auto Layout) -->
            <div class="table-responsive">
                <table class="table table-bordered schedule-table mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle col-no text-center">NO</th>
                            <th rowspan="2" class="align-middle tool-name-col text-center">NAMA PART</th>
                            <th rowspan="2" class="align-middle part-no-col text-center">NO. PART</th>
                            <th rowspan="2" class="align-middle tool-type-col text-center">JENIS ALAT</th>
                            <th rowspan="2" class="align-middle col-customer text-center">CUSTOMER</th>
                            <th rowspan="2" class="align-middle col-qty text-center">QTY</th>
                            <th rowspan="2" class="align-middle col-freq text-center">FREKUENSI<br>VERIFIKASI</th>
                            <th rowspan="2" class="align-middle col-history text-center">RIWAYAT<br>KALIBRASI</th>
                            <th rowspan="2" class="align-middle col-verif-type text-center">JENIS<br>VERIFIKASI</th>
                            <th rowspan="2" class="align-middle status-col text-center">P / A</th>
                            @foreach(['Jan', 'Feb', 'Mar', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'Sept', 'Okt', 'Nov', 'Des'] as $m)
                                <th colspan="4" class="month-header text-center">{{ $m }}</th>
                            @endforeach
                            <th rowspan="2" class="align-middle text-center" style="width: 75px; min-width: 75px;">JUDGMENT<br>ALAT</th>
                            <th rowspan="2" class="align-middle text-center" style="width: 85px; min-width: 85px;">TANGGAL<br>VERIFIKASI</th>
                        </tr>
                        <tr>
                            @for($i = 0; $i < 12; $i++)
                                <th class="week-header text-center">1</th>
                                <th class="week-header text-center">2</th>
                                <th class="week-header text-center">3</th>
                                <th class="week-header text-center">4</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tools as $index => $tool)
                            @php
                                $schedules = $tool->schedules->groupBy(function($s) {
                                    return $s->month . '-' . $s->week;
                                });
                                $latestVerif = $tool->latestVerification;
                            @endphp
                            <!-- Plan Row -->
                            <tr>
                                <td rowspan="2" class="col-no align-middle text-center font-weight-bold" style="font-size:0.62rem;">
                                    {{ $index + 1 }}
                                </td>
                                <td rowspan="2" class="tool-name-col align-middle text-left" title="{{ $tool->name_part }}">
                                    <div class="px-1 text-truncate" style="font-size: 0.62rem; color: #334155; font-weight: 600;">{{ $tool->name_part }}</div>
                                </td>
                                <td rowspan="2" class="part-no-col align-middle text-center" title="{{ $tool->no_part }}">
                                    <div class="text-truncate" style="font-size: 0.62rem; color: #64748b;">{{ $tool->no_part }}</div>
                                </td>
                                <td rowspan="2" class="tool-type-col align-middle text-center">
                                    <span class="badge badge-light text-muted px-1" style="font-size: 0.52rem;">{{ $tool->tool_type }}</span>
                                </td>
                                <td rowspan="2" class="col-customer align-middle text-center" title="{{ $tool->customer }}">
                                    <div class="text-truncate" style="font-size: 0.60rem; color: #475569;">{{ $tool->customer ?: '-' }}</div>
                                </td>
                                <td rowspan="2" class="col-qty align-middle text-center font-weight-bold" style="font-size: 0.62rem;">
                                    {{ $tool->quantity ?: 1 }}
                                </td>
                                <td rowspan="2" class="col-freq align-middle text-center" style="font-size: 0.60rem;">
                                    {{ $tool->verification_frequency ?: '-' }}
                                </td>
                                <td rowspan="2" class="col-history align-middle text-center" style="font-size: 0.60rem;">
                                    {{ $tool->calibration_history ?: '-' }}
                                </td>
                                <td rowspan="2" class="col-verif-type align-middle text-center">
                                    <span class="badge {{ strtolower($tool->verification_type) === 'external' ? 'badge-warning' : 'badge-secondary' }} px-1" style="font-size: 0.52rem;">
                                        {{ $tool->verification_type ?: 'INTERNAL' }}
                                    </span>
                                </td>
                                <td class="status-col text-center font-weight-bold" style="font-size: 0.58rem; color: #854d0e; background-color: #fef08a;">P</td>
                                @php
                                    $pDate = $tool->planned_verification_date ? \Carbon\Carbon::parse($tool->planned_verification_date) : null;
                                    $pYear = $pDate?->year;
                                    $pMonth = $pDate?->month;
                                    $pWeek = $pDate ? min(4, (int)ceil($pDate->day / 7)) : null;
                                @endphp
                                @for($m = 1; $m <= 12; $m++)
                                    @for($w = 1; $w <= 4; $w++)
                                        @php 
                                            $sched = $schedules->get($m.'-'.$w)?->first();
                                            $isPlan = ($pDate && $selectedYear == $pYear && $m == $pMonth && $w == $pWeek);
                                        @endphp
                                        <td class="{{ $isPlan ? 'marker-p' : '' }} text-center p-0">
                                            @if($isPlan) P @endif
                                        </td>
                                    @endfor
                                @endfor
                                <td rowspan="2" class="align-middle text-center">
                                    @if($tool->tool_judgment === 'OK')
                                        <span class="badge badge-success px-1 py-1" style="font-size:0.55rem;">OK</span>
                                    @elseif($tool->tool_judgment === 'NG')
                                        <span class="badge badge-danger px-1 py-1" style="font-size:0.55rem;">NG</span>
                                    @else
                                        <span class="badge badge-light text-muted px-1 py-1" style="font-size:0.55rem;">BELUM</span>
                                    @endif
                                </td>
                                <td rowspan="2" class="align-middle text-center" style="font-size: 0.60rem; color: #475569;">
                                    {{ $latestVerif?->tanggal_verifikasi ? $latestVerif->tanggal_verifikasi->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                            <!-- Actual Row -->
                            <tr>
                                <td class="status-col text-center font-weight-bold" style="font-size: 0.58rem; color: #15803d; background-color: #bbf7d0;">A</td>
                                @for($m = 1; $m <= 12; $m++)
                                    @for($w = 1; $w <= 4; $w++)
                                        @php 
                                            $sched = $schedules->get($m.'-'.$w)?->first();
                                            $actStat = $sched?->actual_status;
                                            $isActual = !empty($actStat);
                                        @endphp
                                        <td class="{{ $isActual ? ($actStat === 'NG' ? 'marker-ng' : 'marker-a') : '' }} text-center p-0">
                                            @if($isActual) {{ $actStat }} @endif
                                        </td>
                                    @endfor
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal Import Excel -->
    <div class="modal fade" id="modalImportExcel" tabindex="-1" role="dialog" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="modalImportExcelLabel" style="font-size: 0.9rem;">
                        <i class="fas fa-file-excel mr-2"></i> Import Schedule & Master Data (Excel)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('verifications.schedule.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body py-3">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Pilih Plant Target</label>
                            <select name="plant" class="form-control form-control-sm" required>
                                <option value="jakarta" {{ strtolower($plantCode) === 'jakarta' ? 'selected' : '' }}>Plant Jakarta</option>
                                <option value="karawang" {{ strtolower($plantCode) === 'karawang' ? 'selected' : '' }}>Plant Karawang</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Tahun Target</label>
                            <select name="year" class="form-control form-control-sm" required>
                                @php $cYear = date('Y'); @endphp
                                @for($y = $cYear - 1; $y <= $cYear + 2; $y++)
                                    <option value="{{ $y }}" {{ ($year ?? $cYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-gray-700">Nama Sheet Excel</label>
                            <input type="text" name="sheet_name" class="form-control form-control-sm" value="2026 NEW" placeholder="Contoh: 2026 NEW">
                            <small class="form-text text-muted">Secara default membaca sheet <code>2026 NEW</code>.</small>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-gray-700">File Excel (.xlsx, .xls)</label>
                            <input type="file" name="file" class="form-control-file form-control-sm" accept=".xlsx,.xls" required>
                        </div>
                        <div class="alert alert-info py-2 px-3 mb-0 mt-3" style="font-size: 0.72rem;">
                            <i class="fas fa-info-circle mr-1"></i> Data master alat (Nama Part, No. Part, Customer, Frekuensi, dll) dan grid Rencana/Aktual 52 minggu akan otomatis diproses dan di-upsert ke dalam sistem.
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3">
                            <i class="fas fa-upload fa-sm mr-1"></i> Process Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

