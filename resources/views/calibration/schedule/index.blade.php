@extends('layouts.admin')

@section('title', 'Jadwal Kalibrasi')

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
                            SCHEDULE KALIBRASI ALAT UKUR
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-052' : 'QC-KRW-F-052' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">25/03/2015</td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">1 / 21/03/2018</td>
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


        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif



        <style>
            /* Force Light Theme for Calibration Schedule - High Specificity */
            #content-wrapper .calibration-schedule-grid-wrapper {
                overflow: auto;
                max-height: 75vh;
                border: 1px solid #e2e8f0 !important;
                border-radius: 8px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                background-color: #fff !important;
            }

            body #content-wrapper .calibration-schedule-grid {
                table-layout: fixed !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
                width: 1650px !important;
                min-width: 1650px !important;
                background-color: #fff !important;
                border: none !important;
                margin-bottom: 0 !important;
            }

            /* Header Specificity */
            body #content-wrapper .calibration-schedule-grid thead th {
                background-color: #f8fafc !important;
                background-image: none !important;
                color: #475569 !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                font-size: 0.62rem !important;
                letter-spacing: 0.3px !important;
                padding: 8px 4px !important;
                border: 1px solid #e2e8f0 !important;
                vertical-align: middle !important;
                position: sticky !important;
                top: 0 !important;
                z-index: 100 !important;
                box-shadow: none !important;
            }

            /* Month & Week Headers */
            body #content-wrapper .calibration-schedule-grid thead th.month-header {
                height: 38px !important;
                background-color: #f1f5f9 !important;
                color: #334155 !important;
            }

            body #content-wrapper .calibration-schedule-grid thead th.week-header {
                height: 24px !important;
                top: 38px !important;
                background-color: #f8fafc !important;
                color: #64748b !important;
                font-size: 0.58rem !important;
                font-weight: 600 !important;
            }

            /* Sticky Columns - Header Level */
            body #content-wrapper .calibration-schedule-grid thead th.tool-name-col, 
            body #content-wrapper .calibration-schedule-grid thead th.serial-col, 
            body #content-wrapper .calibration-schedule-grid thead th.jenis-col, 
            body #content-wrapper .calibration-schedule-grid thead th.status-col {
                z-index: 110 !important;
                background-color: #f1f5f9 !important;
            }

            /* Sticky Columns - Positioning */
            body #content-wrapper .calibration-schedule-grid .tool-name-col { width: 220px !important; min-width: 220px !important; left: 0 !important; position: sticky !important; z-index: 10 !important; }
            body #content-wrapper .calibration-schedule-grid .serial-col { width: 140px !important; min-width: 140px !important; left: 220px !important; position: sticky !important; z-index: 10 !important; }
            body #content-wrapper .calibration-schedule-grid .jenis-col { width: 100px !important; min-width: 100px !important; left: 360px !important; position: sticky !important; z-index: 10 !important; }
            body #content-wrapper .calibration-schedule-grid .status-col { width: 65px !important; min-width: 65px !important; left: 460px !important; position: sticky !important; z-index: 10 !important; border-right: 2px solid #cbd5e1 !important; }

            /* Sticky Columns - Body Level */
            body #content-wrapper .calibration-schedule-grid tbody td.tool-name-col,
            body #content-wrapper .calibration-schedule-grid tbody td.serial-col,
            body #content-wrapper .calibration-schedule-grid tbody td.jenis-col {
                background-color: #fff !important;
                z-index: 10 !important;
                color: #334155 !important;
                font-size: 0.65rem !important;
                padding: 6px 10px !important;
                border-bottom: 1px solid #f1f5f9 !important;
                line-height: 1.3 !important;
            }

            body #content-wrapper .calibration-schedule-grid tbody td.status-col {
                background-color: #f8fafc !important;
                font-weight: 800 !important;
                color: #475569 !important;
                font-size: 0.65rem !important;
                z-index: 10 !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            /* Table Cell Styling */
            body #content-wrapper .calibration-schedule-grid td {
                border: 1px solid #f1f5f9 !important;
                height: 30px !important;
                padding: 0 !important;
                vertical-align: middle !important;
                background-color: #fff;
            }

            /* Plan & Actual Markers */
            body #content-wrapper .calibration-schedule-grid .marker-p { background-color: #10b981 !important; position: relative !important; }
            body #content-wrapper .calibration-schedule-grid .marker-a { background-color: #06b6d4 !important; position: relative !important; }
            
            body #content-wrapper .calibration-schedule-grid .marker-link {
                display: block !important;
                width: 100% !important;
                height: 100% !important;
                min-height: 30px !important;
                text-decoration: none !important;
            }

            /* Row Highlighting */
            body #content-wrapper .calibration-schedule-grid tbody tr:hover td:not(.marker-p):not(.marker-a) {
                background-color: #f8fafc !important;
            }

            .badge-legend {
                padding: 2px 6px;
                font-size: 0.65rem;
                border-radius: 4px;
                font-weight: 700;
            }
        </style>

        <div class="card shadow mb-4">
            <div class="card-body">
                <!-- Filter Bar Minimalis -->
                <form action="{{ route('calibration.schedule.index') }}" method="GET" 
                    class="d-flex flex-nowrap align-items-center bg-light p-2 rounded mb-3 shadow-sm" 
                    style="gap: 12px; overflow-x: auto; white-space: nowrap;">
                    
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Tahun:</label>
                        <div style="width: 85px;">
                            <select name="year" class="form-control form-control-sm border-0 shadow-sm" onchange="this.form.submit()">
                                @php
                                    $currentYear = date('Y');
                                    $selectedYear = $year ?? $currentYear;
                                @endphp
                                @for($y = $currentYear - 2; $y <= $currentYear + 2; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Jenis:</label>
                        <div style="width: 100px;">
                            <select name="jenis_kalibrasi" class="form-control form-control-sm border-0 shadow-sm" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                <option value="Internal" {{ request('jenis_kalibrasi') === 'Internal' ? 'selected' : '' }}>Internal</option>
                                <option value="Eksternal" {{ request('jenis_kalibrasi') === 'Eksternal' ? 'selected' : '' }}>Eksternal</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1 small font-weight-bold text-gray-700">Plan:</label>
                        <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                            <input type="date" name="start_date" class="form-control form-control-sm border-0" style="width: 130px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                            <span class="px-2 text-gray-500 small">-</span>
                            <input type="date" name="end_date" class="form-control form-control-sm border-0" style="width: 130px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" 
                            style="width: 180px; font-size: 0.75rem;" 
                            placeholder="Nama / No. Seri..." value="{{ request('search') }}">
                    </div>

                    <div class="ml-auto d-flex flex-nowrap" style="gap: 5px;">
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Filter">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                        <a href="{{ route('calibration.schedule.index', ['plant' => $plantCode, 'year' => date('Y')]) }}"
                            class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" title="Reset Filter">
                            <i class="fas fa-undo fa-sm"></i>
                        </a>
                        <div class="d-flex align-items-center px-2" style="gap: 5px; border-left: 1px solid #e2e8f0;">
                            <a href="{{ route('calibration.schedule.pdf', request()->all()) }}" class="btn btn-danger btn-sm shadow-sm rounded-pill px-3" target="_blank" title="Export PDF">
                                <i class="fas fa-file-pdf fa-sm"></i>
                            </a>
                            <a href="{{ route('calibration.schedule.print', request()->all()) }}" class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3" target="_blank" title="Print" style="background-color: #17a589; border-color: #17a589; color: white;">
                                <i class="fas fa-print fa-sm"></i>
                            </a>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 8px; border-left: 1px solid #e2e8f0; padding-left: 10px;">
                            <div class="d-flex align-items-center" style="gap: 3px;">
                                <span class="badge-legend" style="background-color: #10b981; color: white;">P</span>
                                <span class="small text-muted font-weight-bold" style="font-size: 0.6rem;">PLAN</span>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 3px;">
                                <span class="badge-legend" style="background-color: #06b6d4; color: white;">A</span>
                                <span class="small text-muted font-weight-bold" style="font-size: 0.6rem;">ACT</span>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 3px;">
                                <span class="badge bg-white border shadow-xs" style="padding: 2px 4px; font-size: 0.65rem;"><i class="fas fa-hourglass-half text-primary"></i></span>
                                <span class="small text-muted font-weight-bold" style="font-size: 0.6rem;">PR</span>
                            </div>
                        </div>
                    </div>
                </form>

                <hr class="my-4 border-light">

                <div class="calibration-schedule-grid-wrapper">
                    <table class="table table-bordered calibration-schedule-grid mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle tool-name-col text-center">NAMA ALAT</th>
                                <th rowspan="2" class="align-middle serial-col text-center">NO. SERI</th>
                                <th rowspan="2" class="align-middle jenis-col text-center">JENIS KALIBRASI</th>
                                <th rowspan="2" class="align-middle status-col text-center">PLANING /<br>AKTUAL</th>
                                @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agust', 'Sept', 'Okt', 'Nov', 'Des'] as $m)
                                    <th colspan="4" class="month-header">{{ $m }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @for($i = 0; $i < 12; $i++)
                                    <th class="week-header">1</th>
                                    <th class="week-header">2</th>
                                    <th class="week-header">3</th>
                                    <th class="week-header">4</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tools as $tool)
                                @php
                                    // Get all planning dates
                                    $plans = [];
                                    foreach ($tool->schedules as $s) {
                                        $m = (int) $s->schedule_date->format('n');
                                        $d = (int) $s->schedule_date->format('j');
                                        $w = (int) ceil($d / 7.75);
                                        if ($w > 4)
                                            $w = 4;
                                        $plans[$m][$w] = true;
                                    }

                                    // Fallback for tools without new schedules records
                                    if (empty($plans) && $tool->schedule_planning && $tool->schedule_planning->format('Y') == $year) {
                                        $m = (int) $tool->schedule_planning->format('n');
                                        $d = (int) $tool->schedule_planning->format('j');
                                        $w = (int) ceil($d / 7.75);
                                        if ($w > 4)
                                            $w = 4;
                                        $plans[$m][$w] = true;
                                    }

                                    // Get Actual Verifications this year
                                    $actuals = [];
                                    foreach ($tool->verifications as $v) {
                                        $m = (int) $v->tanggal_verifikasi->format('n');
                                        $d = (int) $v->tanggal_verifikasi->format('j');
                                        $w = (int) ceil($d / 7.75);
                                        if ($w > 4)
                                            $w = 4;
                                        $actuals[$m][$w] = true;
                                    }

                                    // Identify PR Pending (PR exists, but no Actual yet in that month/week)
                                    $prPendings = [];
                                    foreach ($tool->schedules as $s) {
                                        if (!empty($s->pr_number)) {
                                            $m = (int) $s->schedule_date->format('n');
                                            $d = (int) $s->schedule_date->format('j');
                                            $w = (int) ceil($d / 7.75);
                                            if ($w > 4) $w = 4;
                                            // Only if not already verification exists
                                            if (!isset($actuals[$m][$w])) {
                                                $prPendings[$m][$w] = true;
                                            }
                                        }
                                    }
                                @endphp
                                <!-- Plan Row -->
                                <tr>
                                    <td rowspan="2" class="tool-name-col align-middle text-left font-weight-bold">
                                        {{ $tool->name_alat }}
                                    </td>
                                    <td rowspan="2" class="serial-col align-middle text-center text-muted">
                                        {{ $tool->serial_number }}
                                    </td>
                                    <td rowspan="2" class="jenis-col align-middle text-center">
                                        {{ Str::title($tool->jenis_kalibrasi) }}
                                    </td>
                                    <td class="status-col text-center">P</td>
                                    @for($m = 1; $m <= 12; $m++)
                                        @for($w = 1; $w <= 4; $w++)
                                            @php $isPlan = isset($plans[$m][$w]); @endphp
                                            <td class="{{ $isPlan ? 'marker-p' : '' }}">
                                                @if($isPlan)
                                                    <a href="{{ route('calibration.tools.index', ['plant' => $plantCode, 'tool_id' => $tool->id, 'year' => $year]) }}"
                                                        class="marker-link"
                                                        title="Klik untuk lihat daftar alat: {{ $tool->name_alat }}"></a>
                                                @endif
                                            </td>
                                        @endfor
                                    @endfor
                                </tr>
                                <!-- Actual Row -->
                                <tr>
                                    <td class="status-col text-center">A</td>
                                    @for($m = 1; $m <= 12; $m++)
                                        @for($w = 1; $w <= 4; $w++)
                                            @php 
                                                $isActual = isset($actuals[$m][$w]); 
                                                $isPrPending = isset($prPendings[$m][$w]);
                                            @endphp
                                            <td class="{{ $isActual ? 'marker-a' : '' }}">
                                                @if($isActual)
                                                    <a href="{{ route('calibration.verifications.index', ['plant' => $plantCode, 'tool_id' => $tool->id, 'year' => $year]) }}"
                                                        class="marker-link"
                                                        title="Klik untuk lihat hasil verifikasi: {{ $tool->name_alat }}"></a>
                                                @elseif($isPrPending)
                                                    <i class="fas fa-hourglass-half text-primary" title="PR Out: Menunggu Verifikasi" style="font-size: 1rem;"></i>
                                                @endif
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // No DataTable needed here as we removed the tool list
        });
    </script>
@endpush
