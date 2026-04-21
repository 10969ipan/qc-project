@extends('layouts.admin')

@section('title', 'Daftar Alat Ukur')

@section('content')
    <style>
        .last-child-no-border:last-child {
                border-bottom: none !important;
            }

            .whitespace-nowrap {
                white-space: nowrap;
            }

            #dataTable {
                font-size: 0.75rem;
            }

            #dataTable thead th {
                padding: 0.4rem 0.2rem !important;
                vertical-align: middle;
                font-size: 0.7rem;
            }

            #dataTable td {
                padding: 0.25rem 0.15rem !important;
                white-space: normal !important;
                word-wrap: break-word;
                vertical-align: middle;
            }

            .col-aksi {
                width: 100px !important;
                min-width: 100px !important;
            }

            .col-no {
                width: 25px;
            }

            .col-bagian {
                width: 60px;
            }

            .col-name {
                width: 110px;
            }

            .col-merk {
                width: 1%;
                white-space: nowrap !important;
            }

            .col-seri {
                width: 80px;
            }

            .col-range {
                width: 60px;
            }

            .col-res {
                width: 50px;
            }

            .col-lokasi {
                width: 70px;
            }

            .col-tgl {
                width: 70px;
            }

            .col-freq {
                width: 70px;
            }

            .col-hist {
                width: 50px;
            }

            .col-jenis {
                width: 60px;
            }

            .col-sch {
                width: 75px;
            }

            .col-pr {
                width: 90px;
            }

            .col-status {
                width: 45px;
            }

            /* Compact elements inside table */
            #dataTable .form-control-sm {
                height: calc(1.5em + 0.25rem + 2px) !important;
                padding: 0.125rem 0.25rem !important;
                font-size: 0.7rem !important;
            }

            #dataTable .btn-sm {
                padding: 0.15rem 0.3rem !important;
                font-size: 0.65rem !important;
            }

            .schedule-item {
                min-height: 40px !important;
                margin-bottom: 2px !important;
                padding-bottom: 2px !important;
            }

            /* Sticky Header */
            #dataTable thead th {
                position: sticky;
                top: 0;
                z-index: 10;
                background-color: #4e73df !important; /* Royal Blue */
                color: white !important;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 0.85rem;
                border: 1px solid #ffffff44 !important;
                box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
            }
        </style>
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
                                MASTER DATA ALAT UKUR
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                            <table style="border-collapse:collapse; font-size:0.68rem;">
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                        {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-0215' : 'QC-KRW-F-0215' }}
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
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">- / -</td>
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


            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Alat Ukur</h6>
                    <div class="d-flex">
                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'oshef']))
                            <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                                data-target="#modalTambahAlat">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Alat
                            </button>
                        @endif
                        <div class="dropdown ml-2">
                            <button class="btn btn-sm btn-info dropdown-toggle shadow-sm" type="button" id="dropdownExport"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-download fa-sm text-white-50"></i> Export / Print
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownExport">
                                <a class="dropdown-item" href="{{ route('calibration.tools.print', request()->all()) }}" target="_blank">
                                    <i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i> Print View
                                </a>
                                <a class="dropdown-item" href="{{ route('calibration.tools.pdf', request()->all()) }}">
                                    <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-gray-400"></i> Export PDF
                                </a>
                            </div>
                        </div>
                        <a href="{{ route('calibration.tools.problem-logs', ['plant' => $plantCode]) }}"
                            class="btn btn-sm btn-secondary shadow-sm ml-2">
                            <i class="fas fa-history fa-sm text-white-50"></i> Laporan Problem Alat
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filter Form Removed and Moved to DataTable Header via JS --}}

                    <table class="table table-bordered table-sm text-center align-middle" id="dataTable" width="100%"
                                    cellspacing="0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="align-middle col-no">NO.</th>
                                            <th class="align-middle col-bagian">BAGIAN</th>
                                            <th class="align-middle col-name">NAMA ALAT</th>
                                            <th class="align-middle col-merk">MERK</th>
                                            <th class="align-middle col-seri">NO. SERI</th>
                                            <th class="align-middle col-range">RANGE</th>
                                            <th class="align-middle col-res">RESOLUSI</th>
                                            <th class="align-middle col-tgl">TANGGAL BELI</th>
                                            <th class="align-middle col-freq">FREKUENSI KALIBRASI</th>
                                            <th class="align-middle col-hist">RIWAYAT</th>
                                            <th class="align-middle col-jenis">JENIS</th>
                                            <th class="align-middle col-sch">SCHEDULE</th>
                                            <th class="align-middle whitespace-nowrap col-pr">PR</th>
                                            <th class="align-middle col-status">STAT</th>
                                            <th class="align-middle text-center col-aksi">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tools as $index => $tool)
                                            @php /** @var \App\Models\CalibrationTool $tool */ @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    {{ $tool->bagian }}
                                                    @if($tool->status === 'BROKEN')
                                                        <br><span class="badge badge-danger" style="font-size: 0.65rem;">BROKEN</span>
                                                    @endif
                                                </td>
                                                <td>{{ $tool->name_alat }}</td>
                                                <td>{{ $tool->merk ?? '-' }}</td>
                                                <td>{{ $tool->serial_number }}</td>
                                                <td>{{ $tool->range }}</td>
                                                <td>{{ $tool->resolusi }}</td>
                                                <td>{{ $tool->tanggal_beli ? \Carbon\Carbon::parse($tool->tanggal_beli)->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $tool->frekuensi_kalibrasi }}</td>
                                                <td>{{ $tool->riwayat_kalibrasi ?? '-' }}</td>
                                                <td>{{ Str::title($tool->jenis_kalibrasi) }}</td>
                                                <td>
                                                    @php
                                                        $scheduledStatuses = $tool->getScheduledStatuses($year);
                                                    @endphp
                                                    @if(!empty($scheduledStatuses))
                                                        @php $first = $scheduledStatuses[0];
                                                        $count = count($scheduledStatuses); @endphp
                                                        <div class="mb-1 pb-1 schedule-item"
                                                            style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                            <span
                                                                class="badge badge-info">{{ \Carbon\Carbon::parse($first->schedule_date)->format('d/m/Y') }}</span>
                                                            @if($count > 1)
                                                                <span class="badge badge-light border mt-1" style="font-size: 0.7rem;">+{{ $count - 1 }}
                                                                    more</span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(strtoupper($tool->jenis_kalibrasi) === 'EKSTERNAL')
                                                        @php
                                                            // Get existing PR from any schedule in current year
                                                            $existingPr = null;
                                                            $existingPrDate = null;
                                                            foreach ($tool->schedules as $sch) {
                                                                if ($sch->pr_number) {
                                                                    $existingPr = $sch->pr_number;
                                                                    $existingPrDate = $sch->pr_date;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="mb-1 pb-1 schedule-item"
                                                            style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                            <div class="d-flex align-items-center justify-content-center" style="gap: 5px;">
                                                                <input type="text" class="form-control form-control-sm pr-input text-center no-autoupper"
                                                                    data-tool-id="{{ $tool->id }}" placeholder="PR..." value="{{ $existingPr }}"
                                                                    style="width: 70px;">
                                                                @if($existingPr)
                                                                    <button type="button" class="btn btn-sm btn-outline-danger reset-pr"
                                                                        data-tool-id="{{ $tool->id }}" title="Reset PR">
                                                                        <i class="fas fa-undo"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        // Determine status icon using updated scheduled statuses
                                                        $statIcon = null;
                                                        $statPrDate = '-';
                                                        $statIsClickable = false;
                                                        $statLinkParams = [];
                                                        $hasPendingLog = $tool->pendingLogs->count() > 0;

                                                        // Find the most relevant schedule for display
                                                        $relevantSchedule = null;
                                                        if (!empty($scheduledStatuses)) {
                                                            // Priority 1: First schedule that is NOT OK
                                                            $relevantSchedule = collect($scheduledStatuses)->first(fn($s) => !$s->is_ok);
                                                            // Priority 2: If all are OK, take the first one of the year
                                                            if (!$relevantSchedule) {
                                                                $relevantSchedule = $scheduledStatuses[0];
                                                            }
                                                        }

                                                        $hasVerification = $tool->all_verifications_count > 0;
                                                        $latestVerifDate = $tool->latestVerification && $tool->latestVerification->tanggal_verifikasi ? \Carbon\Carbon::parse($tool->latestVerification->tanggal_verifikasi)->format('d/m/y') : null;

                                                        $icon_base = '<div class="d-inline-block position-relative" style="width: 25px; height: 25px; vertical-align: middle;">' .
                                                            '<i class="fas fa-calendar text-secondary" style="font-size: 1.2rem;"></i>' .
                                                            '<i class="fas fa-clock text-secondary" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.7rem; background: white; border-radius: 50%; box-shadow: 0 0 0 2px white;"></i>' .
                                                            '</div>';

                                                        // Check if next unverified schedule is within 30 days or overdue
                                                        $nextUnverifiedSchedule = null;
                                                        $isApproachingNextVerif = false;
                                                        $isOverdueNextVerif = false;
                                                        if ($hasVerification && !empty($scheduledStatuses)) {
                                                            foreach ($scheduledStatuses as $sch) {
                                                                if (!$sch->is_ok) {
                                                                    $nextUnverifiedSchedule = $sch;
                                                                    break;
                                                                }
                                                            }
                                                            if ($nextUnverifiedSchedule) {
                                                                $nextSchDate = \Carbon\Carbon::parse((string) $nextUnverifiedSchedule->schedule_date)->startOfDay();
                                                                $today = now()->startOfDay();
                                                                $daysUntil = $today->diffInDays($nextSchDate, false);
                                                                
                                                                if ($daysUntil < 0) {
                                                                    $isOverdueNextVerif = true;
                                                                } elseif ($daysUntil <= 30) {
                                                                    $isApproachingNextVerif = true;
                                                                }
                                                            }
                                                        }

                                                        // Set default route for status icons (Schedule)
                                                        $statRouteName = 'calibration.schedule.index';

                                                        if ($hasVerification) {
                                                            if ($isOverdueNextVerif) {
                                                                 // Missed a schedule → show red exclamation circle
                                                                 $statIcon = '<i class="fas fa-exclamation-circle text-danger fa-lg" title="Melewati Jadwal Kalibrasi"></i>';
                                                                 $statIsClickable = true;
                                                            } elseif ($isApproachingNextVerif) {
                                                                 // Next verification is within 30 days → show calendar-clock warning icon
                                                                 $isInternal = strtoupper($tool->jenis_kalibrasi) === 'INTERNAL';
                                                                 $iconColor = $isInternal ? 'info' : 'warning';
                                                                 $iconTitle = $isInternal ? 'Alat Internal - Menunggu Verifikasi' : 'Mendekati Jadwal Kalibrasi';

                                                                 $statIcon = '<div class="d-inline-block position-relative" style="width: 25px; height: 25px; vertical-align: middle;" title="' . $iconTitle . '">' .
                                                                     '<i class="fas fa-calendar text-' . $iconColor . '" style="font-size: 1.2rem;"></i>' .
                                                                     '<i class="fas fa-clock text-' . $iconColor . '" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.7rem; background: white; border-radius: 50%; box-shadow: 0 0 0 2px white;"></i>' .
                                                                     '</div>';
                                                                 $statIsClickable = true;
                                                            } else {
                                                                 $statIcon = '<i class="fas fa-check-circle text-success fa-lg" title="Sudah Verifikasi"></i>';
                                                                 $statIsClickable = true;
                                                                 $statRouteName = 'calibration.verifications.index'; // Verified tool links to history
                                                            }
                                                            
                                                            // Simply link to the tool's history without restrictive date ranges
                                                            $statLinkParams = [
                                                                'plant' => $plantCode,
                                                                'tool_id' => $tool->id,
                                                                'year' => $statRouteName === 'calibration.verifications.index' ? 'all' : $year
                                                            ];
                                                        } elseif ($relevantSchedule && !empty($relevantSchedule->pr_number)) {
                                                            $statIcon = '<i class="fas fa-hourglass-half text-primary fa-lg" title="PR Out - Menunggu Verifikasi"></i>';
                                                            $statPrDate = $relevantSchedule->pr_date ? \Carbon\Carbon::parse($relevantSchedule->pr_date)->format('d/m/Y') : '-';
                                                            $statIsClickable = true;
                                                            $statLinkParams = ['plant' => $plantCode, 'tool_id' => $tool->id];
                                                        } elseif (empty($scheduledStatuses) && !$hasVerification) {
                                                            // No schedules and no verification → Temporary status (Wrench + Clock composite)
                                                            $statIcon = '<div class="d-inline-block position-relative" style="width: 25px; height: 25px; vertical-align: middle;" title="Data Sementara - Belum Ada Jadwal">' .
                                                                '<i class="fas fa-wrench text-secondary" style="font-size: 1.1rem;"></i>' .
                                                                '<i class="fas fa-clock text-secondary" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.7rem; background: white; border-radius: 50%; box-shadow: 0 0 0 2px white;"></i>' .
                                                                '</div>';
                                                            $statIsClickable = true;
                                                            $statLinkParams = ['plant' => $plantCode, 'tool_id' => $tool->id];
                                                        } elseif (strtoupper($tool->jenis_kalibrasi) === 'INTERNAL') {
                                                            $statIcon = str_replace(['text-secondary" style'], ['text-info" style'], $icon_base);
                                                            $statIsClickable = true;
                                                            $statLinkParams = ['plant' => $plantCode, 'tool_id' => $tool->id];
                                                        } else {
                                                            $statIcon = $icon_base;
                                                            $statIsClickable = true;
                                                            $statLinkParams = ['plant' => $plantCode, 'tool_id' => $tool->id];
                                                        }

                                                        // Override: warning for approaching/past schedule (only if NOT verified and NO PR)
                                                        if (!$hasVerification && !($relevantSchedule && !empty($relevantSchedule->pr_number))) {
                                                            $schedulePlanDate = $relevantSchedule ? \Carbon\Carbon::parse((string) $relevantSchedule->schedule_date) : ($tool->schedule_planning ? \Carbon\Carbon::parse((string) $tool->schedule_planning) : null);
                                                            $today = now()->startOfDay();

                                                            if ($schedulePlanDate && $today->gt($schedulePlanDate)) {
                                                                // Past schedule planning date → red danger
                                                                $statIcon = '<i class="fas fa-exclamation-circle text-danger fa-lg" title="Melewati Jadwal Kalibrasi"></i>';
                                                            } elseif ($schedulePlanDate && $today->gte($schedulePlanDate->copy()->subMonth())) {
                                                                // Within 1 month before schedule planning
                                                                $isInternal = strtoupper($tool->jenis_kalibrasi) === 'INTERNAL';
                                                                $iconColor = $isInternal ? 'info' : 'warning';
                                                                $iconTitle = $isInternal ? 'Alat Internal - Menunggu Verifikasi' : 'Mendekati Jadwal Kalibrasi';

                                                                $statIcon = '<div class="d-inline-block position-relative" style="width: 25px; height: 25px; vertical-align: middle;" title="' . $iconTitle . '">' .
                                                                    '<i class="fas fa-calendar text-' . $iconColor . '" style="font-size: 1.2rem;"></i>' .
                                                                    '<i class="fas fa-clock text-' . $iconColor . '" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.7rem; background: white; border-radius: 50%; box-shadow: 0 0 0 2px white;"></i>' .
                                                                    '</div>';
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="mb-1 pb-1"
                                                        style="display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 40px;">
                                                        <div class="d-flex flex-column align-items-center justify-content-center" style="gap: 2px;">
                                                            {{-- Problem Icon - Prioritized --}}
                                                            @if($hasPendingLog)
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <a href="{{ route('calibration.tools.problem-logs', ['plant' => $plantCode, 'tool_id' => $tool->id, 'year' => 'all']) }}" 
                                                                       class="text-warning" 
                                                                       title="Alat dilaporkan bermasalah & menunggu judgment"
                                                                       style="font-size: 1.2rem;">
                                                                        <i class="fas fa-wrench"></i>
                                                                    </a>
                                                                    <span class="text-muted font-weight-bold" style="font-size: 0.65rem; line-height: 1;">
                                                                        {{ $tool->pendingLogs->max('reported_date')->format('d/m/y') }}
                                                                    </span>
                                                                </div>
                                                            @else
                                                                {{-- Normal Status Icon --}}
                                                                @if($statIsClickable && !empty($statLinkParams))
                                                                    @php $statLinkParams['year'] = $year; @endphp
                                                                    <a href="{{ route($statRouteName, $statLinkParams) }}"
                                                                        style="text-decoration: none;">
                                                                        {!! $statIcon !!}
                                                                    </a>
                                                                @else
                                                                    {!! $statIcon !!}
                                                                @endif

                                                                {{-- Verification Date below check icon --}}
                                                                @if($hasVerification && $latestVerifDate && !$isOverdueNextVerif && !$isApproachingNextVerif)
                                                                    <span class="text-muted font-weight-bold" style="font-size: 0.6rem; margin-top: -2px;">
                                                                        {{ $latestVerifDate }}
                                                                    </span>
                                                                @endif
                                                            @endif
                                                        </div>

                                                        {{-- PR Date - Always show if available, hide dash if empty --}}
                                                        @if($statPrDate !== '-')
                                                            <small class="pr-date-display text-muted font-weight-bold mt-1" style="font-size: 0.65rem;">
                                                                {{ $statPrDate }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center" style="gap: 5px; white-space: nowrap;">
                                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'oshef']))
                                                            {{-- Sertifikat (Blue PDF) --}}
                                                            @if($tool->certification_path)
                                                                <button type="button" class="btn btn-sm btn-primary view-pdf shadow-sm" data-toggle="modal"
                                                                    data-target="#pdfModal"
                                                                    data-url="{{ asset('storage/' . $tool->certification_path) }}"
                                                                    data-title="Check Sertifikat - {{ $tool->name_alat }}" title="Check Sertifikat">
                                                                    <i class="fas fa-file-pdf"></i>
                                                                </button>
                                                            @endif

                                                            {{-- Input Verifikasi (Green Check) --}}
                                                            <button type="button" class="btn btn-sm btn-success btn-verifikasi shadow-sm" data-toggle="modal"
                                                                data-target="#modalVerifikasiBaru" data-tool-id="{{ $tool->id }}"
                                                                title="Input Verifikasi">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                    
                                                            {{-- Edit (Cyan Pencil) --}}
                                                            <button type="button" class="btn btn-sm btn-info btn-edit-tool shadow-sm"
                                                                onclick="window.openEditToolModal('{{ $tool->id }}', this)" title="Edit"
                                                                data-id="{{ $tool->id }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                    
                                                            {{-- Lapor Masalah (Orange) --}}
                                                            <button type="button" class="btn btn-sm btn-warning btn-report-problem shadow-sm"
                                                                data-toggle="modal" data-target="#modalReportProblem" data-tool-id="{{ $tool->id }}"
                                                                data-tool-name="{{ $tool->name_alat }}" title="Lapor Masalah">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                            </button>
                    
                                                            {{-- Hapus (Red) --}}
                                                            <button type="button" class="btn btn-sm btn-danger shadow-sm"
                                                                onclick="confirmDeleteTool('{{ $tool->id }}')" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            <form id="delete-tool-form-{{ $tool->id }}"
                                                                action="{{ route('calibration.tools.destroy', $tool->id) }}" method="POST"
                                                                style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="plant" value="{{ $plantCode }}">
                                                            </form>
                                                        @elseif($tool->certification_path)
                                                            {{-- Manager can only view PDF --}}
                                                            <button type="button" class="btn btn-sm btn-primary view-pdf shadow-sm" data-toggle="modal"
                                                                data-target="#pdfModal"
                                                                data-url="{{ asset('storage/' . $tool->certification_path) }}"
                                                                data-title="Check Sertifikat - {{ $tool->name_alat }}" title="Check Sertifikat">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center">Tidak ada data alat.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                                        <div class="card shadow mb-4">
                        <div class="card-header py-2">
                            <h6 class="m-0 font-weight-bold text-primary small">Keterangan Status</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="row no-gutters text-left">
                                <!-- Column 1 -->
                                <div class="col-md-3 border-right">
                                    <div class="p-2 border-bottom d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        </div>
                                        <span class="small">Sudah Verifikasi</span>
                                    </div>
                                    <div class="p-2 border-bottom d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <div class="position-relative" style="width: 25px; height: 25px;">
                                                <i class="fas fa-calendar text-warning" style="font-size: 1.1rem;"></i>
                                                <i class="fas fa-clock text-warning" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.6rem; background: white; border-radius: 50%; box-shadow: 0 0 0 1px white;"></i>
                                            </div>
                                        </div>
                                        <span class="small text-warning font-weight-bold">Mendekati Jadwal Verifikasi</span>
                                    </div>
                                    <div class="p-2 d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <i class="fas fa-hourglass-half text-primary fa-lg"></i>
                                        </div>
                                        <span class="small font-weight-bold text-primary">PR Out - Menunggu Verifikasi</span>
                                    </div>
                                </div>
                                <!-- Column 2 -->
                                <div class="col-md-3 border-right">
                                    <div class="p-2 border-bottom d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <div class="position-relative" style="width: 25px; height: 25px;">
                                                <i class="fas fa-calendar text-info" style="font-size: 1.1rem;"></i>
                                                <i class="fas fa-clock text-info" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.6rem; background: white; border-radius: 50%; box-shadow: 0 0 0 1px white;"></i>
                                            </div>
                                        </div>
                                        <span class="small text-info">Alat Internal - Menunggu Verifikasi</span>
                                    </div>
                                    <div class="p-2 border-bottom d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <div class="position-relative" style="width: 25px; height: 25px;">
                                                <i class="fas fa-calendar text-secondary" style="font-size: 1.1rem;"></i>
                                                <i class="fas fa-clock text-secondary" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.6rem; background: white; border-radius: 50%; box-shadow: 0 0 0 1px white;"></i>
                                            </div>
                                        </div>
                                        <span class="small text-secondary">Alat Eksternal - Belum Ada PR</span>
                                    </div>
                                    <div class="p-2 d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <div class="position-relative" style="width: 25px; height: 25px;">
                                                <i class="fas fa-wrench text-secondary" style="font-size: 1.1rem;"></i>
                                                <i class="fas fa-clock text-secondary" style="position: absolute; bottom: -2px; right: -2px; font-size: 0.6rem; background: white; border-radius: 50%; box-shadow: 0 0 0 1px white;"></i>
                                            </div>
                                        </div>
                                        <span class="small text-secondary font-weight-bold">Alat Cadangan</span>
                                    </div>
                                </div>
                                <!-- Column 3 -->
                                <div class="col-md-3 border-right">
                                    <div class="p-2 d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <i class="fas fa-exclamation-circle text-danger fa-lg"></i>
                                        </div>
                                        <span class="small text-danger">Melewati Jadwal Planning</span>
                                    </div>
                                    <div class="p-2 border-bottom d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <i class="fas fa-wrench text-warning fa-lg"></i>
                                        </div>
                                        <span class="small text-warning font-weight-bold">Alat Bermasalah / Judgment</span>
                                    </div>
                                    <div class="p-2 d-flex align-items-center" style="height: 45px;">
                                        <div style="width: 35px; display: flex; justify-content: center;" class="mr-2">
                                            <span class="badge badge-danger" style="width: 100%; font-size: 0.55rem; padding: 2px 1px;">BROKEN</span>
                                        </div>
                                        <span class="small text-danger">Alat Rusak / Tidak Digunakan</span>
                                    </div>
                                </div>
                                <!-- Column 4 (Notes) -->
                                <div class="col-md-3 bg-light">
                                    <div class="p-2 h-100 d-flex flex-column justify-content-center">
                                        <p class="mb-1 font-weight-bold small">Catatan:</p>
                                        <ul class="pl-3 mb-0" style="font-size: 0.7rem;">
                                            <li>Icon status berubah otomatis berdasarkan verifikasi & planning.</li>
                                            <li>Klik pada icon untuk melihat detail per bulan.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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
                    <!-- Modal Tambah Alat -->
                    <div class="modal fade" id="modalTambahAlat" tabindex="-1" role="dialog" aria-labelledby="modalTambahAlatLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="modalTambahAlatLabel">
                                        <i class="fas fa-plus-circle mr-2"></i> Tambah Master Data Alat
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('calibration.tools.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 text-left">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Bagian</label>
                                                    <input type="text" name="bagian" class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Nama Alat</label>
                                                    <input type="text" name="name_alat" class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Merk</label>
                                                    <input type="text" name="merk" class="form-control form-control-sm no-autoupper">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">No. Seri</label>
                                                    <input type="text" name="serial_number" class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Range</label>
                                                            <input type="text" name="range" class="form-control form-control-sm no-autoupper">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Resolusi</label>
                                                            <input type="text" name="resolusi" class="form-control form-control-sm no-autoupper">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-left">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Tanggal Beli</label>
                                                    <input type="date" name="tanggal_beli" class="form-control form-control-sm">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Frekuensi Kalibrasi</label>
                                                    <input type="text" name="frekuensi_kalibrasi" class="form-control form-control-sm no-autoupper"
                                                        placeholder="Contoh: 1 Tahun" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Jenis Kalibrasi</label>
                                                    <select name="jenis_kalibrasi" class="form-control form-control-sm" required>
                                                        <option value="Internal">Internal</option>
                                                        <option value="Eksternal">Eksternal</option>
                                                    </select>
                                                </div>
                                                <div class="form-group mb-2" id="modal-schedule-container">
                                                    <label class="small font-weight-bold">Schedule Planning</label>
                                                    <div class="input-group input-group-sm mb-2">
                                                        <input type="date" name="schedule_planning[]" class="form-control">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-success" type="button" id="modal-add-schedule-btn">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group text-left mb-2">
                                            <label class="small font-weight-bold">Riwayat Kalibrasi</label>
                                            <textarea name="riwayat_kalibrasi" class="form-control form-control-sm no-autoupper" rows="2"></textarea>
                                        </div>

                                        <div class="form-group text-left mb-0">
                                            <label class="small font-weight-bold">Sertifikasi (PDF)</label>
                                            <input type="file" name="certification" class="form-control-file" accept=".pdf">
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light p-2">
                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
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
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title" id="modalEditAlatLabel">
                                        <i class="fas fa-edit mr-2"></i> Edit Master Data Alat
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form id="formEditAlat" action="" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 text-left">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Bagian <span class="text-danger">*</span></label>
                                                    <input type="text" name="bagian" id="edit_bagian" class="form-control form-control-sm no-autoupper"
                                                        required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Nama Alat <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="name_alat" id="edit_name_alat"
                                                        class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Merk</label>
                                                    <input type="text" name="merk" id="edit_merk" class="form-control form-control-sm no-autoupper">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">No. Seri <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="serial_number" id="edit_serial_number"
                                                        class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Range</label>
                                                            <input type="text" name="range" id="edit_range"
                                                                class="form-control form-control-sm no-autoupper">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Resolusi</label>
                                                            <input type="text" name="resolusi" id="edit_resolusi"
                                                                class="form-control form-control-sm no-autoupper">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-left">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Tgl. Beli</label>
                                                    <input type="date" name="tanggal_beli" id="edit_tanggal_beli"
                                                        class="form-control form-control-sm">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Freq. Kalibrasi <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="frekuensi_kalibrasi" id="edit_frekuensi_kalibrasi"
                                                        class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Jenis Kalibrasi <span
                                                            class="text-danger">*</span></label>
                                                    <select name="jenis_kalibrasi" id="edit_jenis_kalibrasi"
                                                        class="form-control form-control-sm" required>
                                                        <option value="Internal">Internal</option>
                                                        <option value="Eksternal">Eksternal</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group text-left mb-2">
                                            <label class="small font-weight-bold">Schedule Kalibrasi (Planning)</label>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm mb-0" id="edit-schedule-table">
                                                    <thead class="bg-light small text-center">
                                                        <tr>
                                                            <th>Tanggal Planning</th>
                                                            <th>PR Number</th>
                                                            <th width="40"><button type="button"
                                                                    class="btn btn-xs btn-success add-edit-schedule-row"><i
                                                                        class="fas fa-plus"></i></button></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- Will be filled by JS --}}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="form-group text-left mb-2">
                                            <label class="small font-weight-bold">Riwayat Kalibrasi</label>
                                            <textarea name="riwayat_kalibrasi" id="edit_riwayat_kalibrasi"
                                                class="form-control form-control-sm no-autoupper" rows="2"></textarea>
                                        </div>

                                        <div class="form-group text-left mb-0">
                                            <label class="small font-weight-bold">Sertifikasi (PDF Baru)</label>
                                            <input type="file" name="certification" class="form-control-file" accept=".pdf">
                                            <div id="edit_existing_cert" class="mt-1"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light p-2">
                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm">
                                            <i class="fas fa-save mr-1"></i> Update
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Modal Verifikasi Baru -->
                    <div class="modal fade" id="modalVerifikasiBaru" tabindex="-1" role="dialog" aria-labelledby="modalVerifikasiBaruLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content border-0 shadow text-left">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="modalVerifikasiBaruLabel">
                                        <i class="fas fa-check-circle mr-2"></i> Input Verifikasi Alat Ukur
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('calibration.verifications.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="plant" value="{{ $plantCode }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Pilih Alat Ukur <span
                                                            class="text-danger">*</span></label>
                                                    <select name="tool_id" id="modal_verif_tool_select" class="form-control form-control-sm"
                                                        required>
                                                        <option value="">-- Pilih Alat --</option>
                                                        @foreach($tools as $t)
                                                            <option value="{{ $t->id }}" data-name="{{ $t->name_alat }}"
                                                                data-serial="{{ $t->serial_number }}" data-range="{{ $t->range }}"
                                                                data-resolusi="{{ $t->resolusi }}"
                                                                data-frekuensi="{{ $t->frekuensi_kalibrasi }}"
                                                                data-schedules="{{ json_encode($t->schedules->pluck('schedule_date')->map(fn($d) => $d->format('Y-m-d'))) }}">
                                                                {{ $t->name_alat }} ({{ $t->serial_number }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Nama Alat</label>
                                                    <input type="text" name="name_alat" id="modal_verif_name_alat"
                                                        class="form-control form-control-sm" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Merk <span class="text-danger">*</span></label>
                                                    <input type="text" name="merk" class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">No. Seri</label>
                                                            <input type="text" name="serial_number" id="modal_verif_serial_number"
                                                                class="form-control form-control-sm" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Resolusi</label>
                                                            <input type="text" name="resolusi" id="modal_verif_resolusi"
                                                                class="form-control form-control-sm" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Rentang Ukur (Range)</label>
                                                    <input type="text" name="rentang_ukur" id="modal_verif_rentang_ukur"
                                                        class="form-control form-control-sm" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Tgl. Kalibrasi <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" name="tanggal_kalibrasi" class="form-control form-control-sm"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Tgl. Verifikasi <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" name="tanggal_verifikasi" id="modal_verif_tanggal_verifikasi"
                                                                class="form-control form-control-sm" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Next Kalibrasi <span
                                                            class="text-danger">*</span></label>
                                                    <input type="date" name="next_kalibrasi" id="modal_verif_next_kalibrasi"
                                                        class="form-control form-control-sm" required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Frekuensi Kalibrasi</label>
                                                    <input type="text" name="frekuensi_kalibrasi" id="modal_verif_frekuensi_kalibrasi"
                                                        class="form-control form-control-sm" required>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Table Detail Verifikasi --}}
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card bg-light mb-3">
                                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                                        <h6 class="m-0 font-weight-bold small">Detail Verifikasi</h6>
                                                        <button type="button" class="btn btn-xs btn-info" id="modal-verif-add-row">
                                                    <i class="fas fa-plus"></i> Tambah Baris
                                                </button>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm mb-0">
                                                        <thead class="bg-white small">
                                                            <tr class="text-center">
                                                                <th>Nilai Alat</th>
                                                                <th>Nilai Koreksi</th>
                                                                <th>Ketidakpastian</th>
                                                                <th>Hasil Verifikasi<br><small class="text-muted">(Koreksi + Ketidakpastian)</small></th>
                                                                <th style="width: 40px;"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="modal-verif-verification-body">
                                                            <tr>
                                                                <td><input type="text" name="nilai_alat[]"
                                                                        class="form-control form-control-sm no-autoupper"></td>
                                                                <td><input type="text" name="nilai_koreksi[]"
                                                                        class="form-control form-control-sm no-autoupper"></td>
                                                                <td><input type="text" name="nilai_ketidakpastian[]"
                                                                        class="form-control form-control-sm no-autoupper"></td>
                                                                <td><input type="text" name="hasil_verifikasi[]"
                                                                        class="form-control form-control-sm no-autoupper bg-light" readonly></td>
                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger modal-verif-remove-row"
                                                                        disabled>
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
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Judgment <span
                                                    class="text-danger">*</span></label>
                                            <select name="judgment" class="form-control form-control-sm" required>
                                                <option value="-">-</option>
                                                <option value="OK">OK</option>
                                                <option value="NG">NG</option>
                                            </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Std. Toleransi <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="std_toleransi" class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Acuan Toleransi <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="acuan_toleransi" class="form-control form-control-sm no-autoupper" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold">Sertifikasi (PDF)</label>
                                            <input type="file" name="certification" class="form-control-file small" accept=".pdf">
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light p-2">
                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-sm px-4">
                                            <i class="fas fa-save mr-1"></i> Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Modal Lapor Masalah -->
                        <div class="modal fade" id="modalReportProblem" tabindex="-1" role="dialog" aria-labelledby="modalReportProblemLabel"
                            aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title" id="modalReportProblemLabel">
                                            <i class="fas fa-exclamation-triangle mr-2"></i> Lapor Masalah Alat
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('calibration.tools.store-problem') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="plant" value="{{ $plantCode }}">
                                        <input type="hidden" name="year" value="{{ $year }}">
                                        <input type="hidden" name="calibration_tool_id" id="problem_tool_id">
                                        <div class="modal-body text-left">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold">Nama Alat</label>
                                                <input type="text" id="problem_tool_name" class="form-control form-control-sm bg-light" readonly>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold">Tanggal Kejadian <span class="text-danger">*</span></label>
                                                <input type="date" name="reported_date" class="form-control form-control-sm" 
                                                    value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold">Jenis Problem <span class="text-danger">*</span></label>
                                                <select name="problem_type" id="problem_type" class="form-control form-control-sm" required>
                                                    <option value="">-- Pilih Jenis --</option>
                                                    <option value="ERROR">ERROR (Masih bisa diperbaiki)</option>
                                                    <option value="RUSAK">RUSAK (Mati total / pecah / tidak bisa dipakai)</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-3" id="action_taken_wrapper" style="display: none;">
                                                <label class="small font-weight-bold">Aksi Lanjut <span class="text-danger">*</span></label>
                                                <input type="text" name="action_taken" id="action_taken" class="form-control form-control-sm" 
                                                    placeholder="Contoh: Service Internal, PO GA, dll..." list="action_suggestions">
                                                <datalist id="action_suggestions">
                                                    <option value="SERVICE_INTERNAL">
                                                    <option value="SERVICE_EXTERNAL">
                                                    <option value="PO_GA">
                                                    <option value="REPLACE">
                                                </datalist>
                                                <div class="alert alert-danger small mt-2 py-1 mb-0" id="rusak_info" style="display: none;">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    Alat <strong>RUSAK</strong> akan otomatis di-set statusnya menjadi <strong>BROKEN</strong> dan seluruh jadwal mendatang akan dihapus.
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold">Bukti Foto / PDF</label>
                                                <input type="file" name="evidence_report" class="form-control-file form-control-sm" 
                                                    accept=".jpg,.jpeg,.png,.pdf">
                                                <small class="text-muted">Max: 5MB (JPG/PDF)</small>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small font-weight-bold">Detail Masalah <span class="text-danger">*</span></label>
                                                <textarea name="description" class="form-control form-control-sm no-autoupper" rows="3" 
                                                    placeholder="Jelaskan detail masalahnya..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light p-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning btn-sm px-4 shadow-sm">
                                                <i class="fas fa-paper-plane mr-1"></i> Kirim Laporan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
    <script src="{{ asset('js/calibration/calibration-tools.js') }}?v={{ filemtime(public_path('js/calibration/calibration-tools.js')) }}"></script>

    <script>
    // Inline failsafe for Edit Tool to bypass caching/loading issues
    window.openEditToolModal = function (id, btnElement) {
        console.log('--- Edit Modal Failsafe v2 Triggered ---');
        console.log('Target ID:', id);
        
        // Safety: Force hide any stuck loader or backdrop that might block interaction
        if (typeof $ !== 'undefined') {
            $('#global-loader').fadeOut(200);
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow', 'auto');
        }

        if (!window.__CALIBRATION_TOOLS__ || !window.__CALIBRATION_TOOLS__.routes) {
            console.error('Calibration config missing!');
            alert('Konfigurasi sistem belum siap. Mohon refresh halaman jika masalah berlanjut.');
            return;
        }

        var btn = btnElement ? $(btnElement) : $(`.btn-edit-tool[data-id="${id}"]`);
        var originalContent = btn.html();
        
        if (btn.length) {
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        }

        var editUrl = window.__CALIBRATION_TOOLS__.routes.edit.replace(':id', id);

        $.ajax({
            url: editUrl,
            type: 'GET',
            data: { plant: window.__CALIBRATION_TOOLS__.plantCode },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function (response) {
                console.log('Edit Data Received:', response);
                if (!response || !response.tool) {
                    alert('Gagal mengambil data: Response tidak valid.');
                    if (btn.length) btn.prop('disabled', false).html(originalContent);
                    return;
                }

                const tool = response.tool;
                // Populate Form
                $('#edit_bagian').val(tool.bagian);
                $('#edit_name_alat').val(tool.name_alat);
                $('#edit_merk').val(tool.merk);
                $('#edit_serial_number').val(tool.serial_number);
                $('#edit_range').val(tool.range);
                $('#edit_resolusi').val(tool.resolusi);
                $('#edit_tanggal_beli').val(tool.tanggal_beli_formatted || '');
                $('#edit_frekuensi_kalibrasi').val(tool.frekuensi_kalibrasi);
                $('#edit_jenis_kalibrasi').val(tool.jenis_kalibrasi);
                $('#edit_riwayat_kalibrasi').val(tool.riwayat_kalibrasi);

                $('#edit_existing_cert').html(tool.certification_path 
                    ? `<a href="/storage/${tool.certification_path}" target="_blank" class="badge badge-info"><i class="fas fa-file-pdf mr-1"></i> Lihat Sertifikat</a>` 
                    : '');

                let schHtml = '';
                if (tool.schedules && tool.schedules.length > 0) {
                    tool.schedules.forEach(sch => {
                        schHtml += `<tr>
                            <td><input type="hidden" name="schedule_ids[]" value="${sch.id}"><input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${sch.schedule_date_formatted}"></td>
                            <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" value="${sch.pr_number || ''}" placeholder="PR Number..."></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                    });
                } else {
                    let spDate = tool.schedule_planning ? String(tool.schedule_planning).substring(0, 10) : '';
                    schHtml = `<tr>
                        <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${spDate}"></td>
                        <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" placeholder="PR Number..."></td>
                        <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                }
                $('#edit-schedule-table tbody').html(schHtml);
                $('#formEditAlat').attr('action', window.__CALIBRATION_TOOLS__.routes.update.replace(':id', id));
                
                // Show Modal
                $('#modalEditAlat').modal('show');
                
                if (btn.length) btn.prop('disabled', false).html(originalContent);
            },
            error: function (xhr) {
                console.error('Edit Error:', xhr);
                let msg = 'Gagal mengambil data alat (Error ' + xhr.status + ')';
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                else alert(msg);
                if (btn.length) btn.prop('disabled', false).html(originalContent);
            }
        });
    };
    </script>
@endpush
