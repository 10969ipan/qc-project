@extends('layouts.admin')

@section('title', 'Jadwal Kalibrasi')

@section('content')
    <div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body py-3">
            <div class="row align-items-start">
                <div class="col-md-8 border-right">
                    <div class="d-flex align-items-center mb-3">
                        <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase mr-3">
                            SCHEDULE KALIBRASI ALAT UKUR
                        </h1>
                        <span
                            class="badge badge-{{ strtolower($plantCode) === 'jakarta' ? 'info' : 'primary' }}"
                            style="font-size: 0.8rem;">
                            <i class="fas fa-building mr-1"></i>
                            Plant {{ ucfirst($plantCode) }}
                        </span>
                    </div>
                    <div class="mt-2">
                        {{-- Button removed as per user request --}}
                    </div>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <div class="col p-0" style="max-width: 280px;">
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-052' : 'QC-KRW-F-052' }}
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tanggal Terbit</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 25/03/2015</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Revisi Ke-</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 1</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tanggal Revisi</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: 21/03/2018</div>
                        </div>
                        <div class="row">
                            <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Halaman</div>
                            <div class="col-7 text-xs font-weight-bold text-gray-800">: -</div>
                        </div>
                    </div>
                </div>
            </div>
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
            .schedule-table {
                table-layout: fixed;
                border-collapse: collapse !important;
                width: 1650px; /* 180 + 120 + 100 + 90 + (48 * 25) */
                min-width: 1650px;
                background-color: white;
            }

            .schedule-table th,
            .schedule-table td {
                box-sizing: border-box;
                font-size: 10px;
                text-align: center;
                vertical-align: middle !important;
                border: 1px solid #dee2e6 !important;
                padding: 0 !important;
            }

            /* Sticky Columns Positioning */
            .tool-name-col {
                width: 180px;
                min-width: 180px;
                left: 0;
                position: sticky;
                z-index: 30;
                background-color: white !important;
            }

            .serial-col {
                width: 120px;
                min-width: 120px;
                left: 180px;
                position: sticky;
                z-index: 30;
                background-color: white !important;
            }

            .jenis-col {
                width: 100px;
                min-width: 100px;
                left: 300px;
                position: sticky;
                z-index: 30;
                background-color: white !important;
            }

            .status-col {
                width: 90px;
                min-width: 90px;
                left: 400px;
                position: sticky;
                z-index: 30;
                background-color: #f8f9fc !important;
                border-right: 2px solid #5a5c69 !important;
            }

            /* Header Backgrounds */
            thead th {
                background-color: #4e73df !important;
                color: white !important;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 0.8rem;
                position: sticky;
                z-index: 40;
                top: 0;
                border: 1px solid #ffffff44 !important;
            }

            /* Fix sticky header backgrounds */
            thead th.tool-name-col,
            thead th.serial-col,
            thead th.jenis-col,
            thead th.status-col {
                background-color: #4e73df !important;
                color: white !important;
                z-index: 50;
                top: 0;
            }

            /* Month Row (Row 1) */
            th.month-header {
                top: 0;
            }

            /* Week Row (Row 2) */
            th.week-header {
                top: 35px; /* month-header height */
                z-index: 40;
            }

            /* Rowspan headers (NAMA ALAT, etc.) are in row 1, so top: 0 is correct */
            thead th.tool-name-col,
            thead th.serial-col,
            thead th.jenis-col,
            thead th.status-col {
                z-index: 50;
                top: 0;
            }

            /* Overwrite sticky backgrounds for body */
            tbody td.tool-name-col,
            tbody td.serial-col,
            tbody td.jenis-col {
                background-color: white !important;
                z-index: 20;
                padding: 4px !important;
                white-space: normal;
            }

            tbody td.status-col {
                background-color: #f8f9fc !important;
                z-index: 20;
            }

            /* Grid Cells */
            .month-header {
                height: 35px;
            }

            .week-header {
                background-color: #f8f9fc !important;
                color: #5a5c69 !important;
                width: 25px;
                height: 25px;
            }

            .schedule-table td:not(.tool-name-col):not(.serial-col):not(.status-col) {
                width: 25px;
                height: 25px;
            }

            .marker-p { background-color: #1cc88a !important; }
            .marker-a { background-color: #36b9cc !important; }

            .marker-link {
                display: block;
                width: 100%;
                height: 100%;
                min-height: 20px;
            }

            .table-responsive {
                overflow: auto;
                max-height: 75vh;
                border: 1px solid #dee2e6;
            }
        </style>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Jadwal Kalibrasi - {{ $year }}</h6>
                <div class="small">
                    <span class="badge" style="background-color: #1cc88a; color: white;">P</span> Plan
                    <span class="badge ml-2" style="background-color: #36b9cc; color: white;">A</span> Actual
                    <span class="badge ml-2 bg-white border shadow-sm" style="padding: 2px 5px;">
                        <i class="fas fa-hourglass-half text-primary" style="font-size: 0.9rem;"></i>
                        <span class="small ml-1">PR Out</span>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm" style="gap: 15px;">
                    <form action="{{ route('calibration.schedule.index') }}" method="GET" class="d-flex flex-wrap align-items-center w-100" style="gap: 10px;">
                        <input type="hidden" name="plant" value="{{ $plantCode }}">
                        
                        <div class="d-flex align-items-center">
                            <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Cari:</label>
                            <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" style="width: 180px; border-radius: 0.35rem;" placeholder="Nama / No. Seri" value="{{ request('search') }}">
                        </div>

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

                        <div class="d-flex align-items-center">
                            <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Jenis:</label>
                            <select name="jenis_kalibrasi" class="form-control form-control-sm border-0 shadow-sm" style="width: 110px; border-radius: 0.35rem;">
                                <option value="">Semua</option>
                                <option value="Internal" {{ request('jenis_kalibrasi') === 'Internal' ? 'selected' : '' }}>Internal</option>
                                <option value="Eksternal" {{ request('jenis_kalibrasi') === 'Eksternal' ? 'selected' : '' }}>Eksternal</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center">
                            <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Plan:</label>
                            <div class="d-flex align-items-center shadow-sm rounded bg-white overflow-hidden">
                                <input type="date" name="start_date" class="form-control form-control-sm border-0" style="width: 130px; font-size: 0.75rem;" value="{{ request('start_date') }}">
                                <span class="px-2 text-gray-500 small">-</span>
                                <input type="date" name="end_date" class="form-control form-control-sm border-0" style="width: 130px; font-size: 0.75rem;" value="{{ request('end_date') }}">
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Freq:</label>
                            <select name="frequency" class="form-control form-control-sm border-0 shadow-sm" style="width: 90px; border-radius: 0.35rem;">
                                <option value="">Semua</option>
                                <option value="1_year" {{ request('frequency') === '1_year' ? 'selected' : '' }}>1 Thn</option>
                                <option value="more_than_1_year" {{ request('frequency') === 'more_than_1_year' ? 'selected' : '' }}>> 1 Thn</option>
                            </select>
                        </div>

                        <div class="ml-auto d-flex" style="gap: 5px;">
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                            <a href="{{ route('calibration.schedule.index', ['plant' => $plantCode, 'year' => date('Y')]) }}"
                                class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3">
                                <i class="fas fa-undo fa-sm"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <hr class="my-4 border-light">

                <div class="table-responsive">
                    <table class="table table-bordered schedule-table mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle tool-name-col text-center">NAMA ALAT</th>
                                <th rowspan="2" class="align-middle serial-col text-center">NO. SERI</th>
                                <th rowspan="2" class="align-middle jenis-col text-center">JENIS KALIBRASI</th>
                                <th rowspan="2" class="align-middle status-col text-center">PLANING/<br>AKTUAL</th>
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
                                    <td rowspan="2" class="tool-name-col align-middle"
                                        title="{{ $tool->name_alat }}" style="border-bottom: 2px solid #dee2e6;">
                                        <div class="font-weight-bold">{{ $tool->name_alat }}</div>
                                    </td>
                                    <td rowspan="2" class="serial-col align-middle"
                                        title="{{ $tool->serial_number }}" style="border-bottom: 2px solid #dee2e6;">
                                        <div class="small text-muted">{{ $tool->serial_number }}</div>
                                    </td>
                                    <td rowspan="2" class="jenis-col align-middle"
                                        style="border-bottom: 2px solid #dee2e6;">
                                        <div class="small">{{ Str::title($tool->jenis_kalibrasi) }}</div>
                                    </td>
                                    {{-- Status column removed as per request --}}
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
                                    <td class="status-col text-center" style="border-bottom: 2px solid #dee2e6;">A</td>
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