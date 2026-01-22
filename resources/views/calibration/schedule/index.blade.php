@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Jadwal Kalibrasi - Plant {{ strtoupper($plantCode) }}</h1>
            <a href="{{ route('calibration.tools.create', ['plant' => $plantCode]) }}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Alat
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filter</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('calibration.schedule.index') }}" method="GET" class="row align-items-end">
                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Pencarian Alat</label>
                            <input type="text" name="search" class="form-control" placeholder="Nama / No. Seri" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Jenis Kalibrasi</label>
                            <select name="jenis_kalibrasi" class="form-control">
                                <option value="">Semua</option>
                                <option value="Internal" {{ request('jenis_kalibrasi') === 'Internal' ? 'selected' : '' }}>Internal</option>
                                <option value="Eksternal" {{ request('jenis_kalibrasi') === 'Eksternal' ? 'selected' : '' }}>Eksternal</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Planning Dari</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Sampai</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Frekuensi</label>
                            <select name="frequency" class="form-control">
                                <option value="">Semua</option>
                                <option value="1_year" {{ request('frequency') === '1_year' ? 'selected' : '' }}>1 Thn</option>
                                <option value="more_than_1_year" {{ request('frequency') === 'more_than_1_year' ? 'selected' : '' }}> > 1 Thn</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex" style="gap: 5px;">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search fa-sm"></i> 
                            </button>
                            <a href="{{ route('calibration.schedule.index', ['plant' => $plantCode]) }}"
                                class="btn btn-secondary flex-fill">
                                <i class="fas fa-undo fa-sm"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>


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
            }

            .serial-col {
                width: 120px;
                min-width: 120px;
                left: 180px;
                position: sticky;
                z-index: 30;
            }

            .jenis-col {
                width: 100px;
                min-width: 100px;
                left: 300px;
                position: sticky;
                z-index: 30;
            }

            .status-col {
                width: 90px;
                min-width: 90px;
                left: 400px;
                position: sticky;
                z-index: 30;
                border-right: 2px solid #5a5c69 !important;
            }

            /* Header Backgrounds */
            thead th {
                background-color: #4e73df !important;
                color: white !important;
                font-weight: bold;
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
                overflow-x: auto;
                max-height: 75vh;
                border: 1px solid #dee2e6;
            }
        </style>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Jadwal Kalibrasi - {{ date('Y') }}</h6>
                <div class="small">
                    <span class="badge" style="background-color: #1cc88a; color: white;">P</span> Plan
                    <span class="badge ml-2" style="background-color: #36b9cc; color: white;">A</span> Actual
                </div>
            </div>
            <div class="card-body">
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
                                    if (empty($plans) && $tool->schedule_planning && $tool->schedule_planning->format('Y') == date('Y')) {
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
                                        <div class="small">{{ $tool->jenis_kalibrasi }}</div>
                                    </td>
                                    {{-- Status column removed as per request --}}
                                    <td class="status-col text-center">P</td>
                                    @for($m = 1; $m <= 12; $m++)
                                        @for($w = 1; $w <= 4; $w++)
                                            @php $isPlan = isset($plans[$m][$w]); @endphp
                                            <td class="{{ $isPlan ? 'marker-p' : '' }}">
                                                @if($isPlan)
                                                    <a href="{{ route('calibration.tools.index', ['plant' => $plantCode, 'tool_id' => $tool->id]) }}"
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
                                            @php $isActual = isset($actuals[$m][$w]); @endphp
                                            <td class="{{ $isActual ? 'marker-a' : '' }}">
                                                @if($isActual)
                                                    <a href="{{ route('calibration.verifications.index', ['plant' => $plantCode, 'tool_id' => $tool->id]) }}"
                                                        class="marker-link"
                                                        title="Klik untuk lihat hasil verifikasi: {{ $tool->name_alat }}"></a>
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