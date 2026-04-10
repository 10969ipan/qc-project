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

    <style>
        .table-responsive {
            max-height: 75vh !important;
            overflow: auto !important;
            border: none !important;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.02);
            border-radius: 0.5rem;
        }

        .schedule-table {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            border: none !important;
            width: 1750px;
            min-width: 1750px;
            table-layout: fixed;
            font-size: 0.68rem;
            background-color: white;
        }

        .schedule-table td,
        .schedule-table th {
            border-left: none !important;
            border-right: 1px solid #f1f5f9 !important;
            vertical-align: middle !important;
            padding: 4px 6px !important;
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
            font-weight: 600 !important;
            text-transform: uppercase;
            font-size: 0.62rem !important;
            letter-spacing: 0.2px;
            padding: 8px 10px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: none !important;
            white-space: nowrap !important;
        }

        /* Sticky Columns positioning - Ensuring colors match palette */
        .tool-name-col { width: 180px; min-width: 180px; left: 0; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .part-no-col { width: 150px; min-width: 150px; left: 180px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .tool-type-col { width: 120px; min-width: 120px; left: 330px; position: sticky; z-index: 102; background-color: #ffffff !important; border-bottom: 1px solid #f1f5f9 !important; }
        .status-col { width: 70px; min-width: 70px; left: 450px; position: sticky; z-index: 102; background-color: #f8fafc !important; border-right: 2px solid #e2e8f0 !important; border-bottom: 1px solid #f1f5f9 !important; }

        /* Ensure sticky headers are above sticky columns */
        thead th.tool-name-col, thead th.part-no-col, thead th.tool-type-col, thead th.status-col {
            z-index: 105 !important;
            background-color: #f8fafc !important;
        }

        /* Markers */
        .marker-p { background-color: #ecfdf5 !important; color: #059669 !important; font-weight: 700 !important; font-size: 0.6rem; }
        .marker-a { background-color: #eff6ff !important; color: #2563eb !important; font-weight: 700 !important; font-size: 0.6rem; }

        .week-header {
            background-color: #f1f5f9 !important;
            color: #64748b !important;
            font-size: 0.55rem !important;
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
                            <span class="badge" style="background-color: #ecfdf5; color: #059669; font-size: 0.6rem; border: 1px solid #d1fae5;">P</span>
                            <span class="small font-weight-bold text-muted" style="font-size: 0.65rem;">Rencana</span>
                        </span>
                        <span class="d-flex align-items-center" style="gap: 4px;">
                            <span class="badge" style="background-color: #eff6ff; color: #2563eb; font-size: 0.6rem; border: 1px solid #dbeafe;">A</span>
                            <span class="small font-weight-bold text-muted" style="font-size: 0.65rem;">Aktual/OK</span>
                        </span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm px-3 font-weight-bold" style="font-size: 0.7rem; border-radius: 0.35rem;">
                        <i class="fas fa-search fa-sm mr-1"></i> CARI
                    </button>
                </div>
            </form>

            <!-- Industrial Schedule Table -->
            <div class="table-responsive">
                <table class="table table-bordered schedule-table mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle tool-name-col text-center">NAMA PART</th>
                            <th rowspan="2" class="align-middle part-no-col text-center">NO. PART</th>
                            <th rowspan="2" class="align-middle tool-type-col text-center">JENIS ALAT</th>
                            <th rowspan="2" class="align-middle status-col text-center">RENCANA/<br>AKTUAL</th>
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
                                $schedules = $tool->schedules->groupBy(function($s) {
                                    return $s->month . '-' . $s->week;
                                });
                            @endphp
                            <!-- Plan Row -->
                            <tr>
                                <td rowspan="2" class="tool-name-col align-middle text-left" title="{{ $tool->name_part }}">
                                    <div class="px-1" style="font-size: 0.65rem; color: #334155; font-weight: 600;">{{ $tool->name_part }}</div>
                                </td>
                                <td rowspan="2" class="part-no-col align-middle" title="{{ $tool->no_part }}">
                                    <div style="font-size: 0.65rem; color: #64748b;">{{ $tool->no_part }}</div>
                                </td>
                                <td rowspan="2" class="tool-type-col align-middle text-center">
                                    <span class="badge badge-light text-muted" style="font-size: 0.55rem;">{{ $tool->tool_type }}</span>
                                </td>
                                <td class="status-col text-center font-weight-bold" style="font-size: 0.6rem; color: #64748b;">P</td>
                                @for($m = 1; $m <= 12; $m++)
                                    @for($w = 1; $w <= 4; $w++)
                                        @php 
                                            $sched = $schedules->get($m.'-'.$w)?->first();
                                            $isPlan = $sched && $sched->planning_status === 'P';
                                        @endphp
                                        <td class="{{ $isPlan ? 'marker-p' : '' }} text-center">
                                            @if($isPlan) P @endif
                                        </td>
                                    @endfor
                                @endfor
                            </tr>
                            <!-- Actual Row -->
                            <tr>
                                <td class="status-col text-center font-weight-bold" style="font-size: 0.6rem; color: #2563eb;">A</td>
                                @for($m = 1; $m <= 12; $m++)
                                    @for($w = 1; $w <= 4; $w++)
                                        @php 
                                            $sched = $schedules->get($m.'-'.$w)?->first();
                                            $isActual = $sched && $sched->actual_status;
                                        @endphp
                                        <td class="{{ $isActual ? 'marker-a' : '' }} text-center">
                                            @if($isActual) {{ $sched->actual_status }} @endif
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
