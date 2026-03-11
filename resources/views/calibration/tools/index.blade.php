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
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-body py-3">
                    <div class="row align-items-start">
                        <div class="col-md-8 border-right">
                            <div class="d-flex align-items-center mb-3">
                                <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase mr-3">
                                    MASTER DATA ALAT UKUR
                                </h1>
                                <span class="badge badge-{{ strtolower($plantCode) === 'jakarta' ? 'info' : 'primary' }}"
                                    style="font-size: 0.8rem;">
                                    <i class="fas fa-building mr-1"></i>
                                    Plant {{ ucfirst($plantCode) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex justify-content-end">
                            <div class="col p-0" style="max-width: 280px;">
                                <div class="row mb-1">
                                    <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">No. Dokumen</div>
                                    <div class="col-7 text-xs font-weight-bold text-gray-800">:
                                        {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-0215' : 'QC-KRW-F-0215' }}
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tanggal Terbit
                                    </div>
                                    <div class="col-7 text-xs font-weight-bold text-gray-800">: 28/11/2019</div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Revisi Ke-</div>
                                    <div class="col-7 text-xs font-weight-bold text-gray-800">: 0</div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5 text-xs font-weight-bold text-gray-800 text-uppercase">Tanggal Rev.</div>
                                    <div class="col-7 text-xs font-weight-bold text-gray-800">: -</div>
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
                        <a href="{{ route('calibration.tools.problem-logs', ['plant' => $plantCode]) }}"
                            class="btn btn-sm btn-info shadow-sm ml-2">
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

                                                        if ($hasVerification) {
                                                            $statIcon = '<i class="fas fa-check-circle text-success fa-lg" title="Sudah Verifikasi"></i>';
                                                            $statIsClickable = true;
                                                            if ($relevantSchedule) {
                                                                $statPrDate = property_exists($relevantSchedule, 'pr_date') && $relevantSchedule->pr_date ? \Carbon\Carbon::parse($relevantSchedule->pr_date)->format('d/m/Y') : '-';
                                                                $statLinkParams = [
                                                                    'plant' => $plantCode,
                                                                    'tool_id' => $tool->id,
                                                                    'start_date' => \Carbon\Carbon::parse($relevantSchedule->schedule_date)->copy()->startOfMonth()->format('Y-m-d'),
                                                                    'end_date' => \Carbon\Carbon::parse($relevantSchedule->schedule_date)->copy()->endOfMonth()->format('Y-m-d')
                                                                ];
                                                            } else {
                                                                // No schedule data, just link to verifications filtered by tool
                                                                $statLinkParams = [
                                                                    'plant' => $plantCode,
                                                                    'tool_id' => $tool->id,
                                                                ];
                                                            }
                                                        } elseif ($relevantSchedule && !empty($relevantSchedule->pr_number)) {
                                                            $statIcon = '<i class="fas fa-hourglass-half text-primary fa-lg" title="PR Out - Menunggu Verifikasi"></i>';
                                                            $statPrDate = $relevantSchedule->pr_date ? \Carbon\Carbon::parse($relevantSchedule->pr_date)->format('d/m/Y') : '-';
                                                        } elseif (strtoupper($tool->jenis_kalibrasi) === 'INTERNAL') {
                                                            $statIcon = str_replace(['text-secondary" style'], ['text-info" style'], $icon_base);
                                                        } else {
                                                            $statIcon = $icon_base;
                                                        }

                                                        // Override: warning for approaching/past schedule (only if NOT verified and NO PR)
                                                        if (!$hasVerification && !($relevantSchedule && !empty($relevantSchedule->pr_number))) {
                                                            $schedulePlanDate = $relevantSchedule ? \Carbon\Carbon::parse((string) $relevantSchedule->schedule_date) : ($tool->schedule_planning ? \Carbon\Carbon::parse((string) $tool->schedule_planning) : null);
                                                            $today = now()->startOfDay();

                                                            if ($schedulePlanDate && $today->gt($schedulePlanDate)) {
                                                                // Past schedule planning date → red danger
                                                                $statIcon = '<i class="fas fa-exclamation-circle text-danger fa-lg" title="Melewati Jadwal Kalibrasi"></i>';
                                                            } elseif ($schedulePlanDate && $today->gte($schedulePlanDate->copy()->subMonth())) {
                                                                // Within 1 month before schedule planning → yellow warning
                                                                $statIcon = '<i class="fas fa-exclamation-triangle text-warning fa-lg" title="Mendekati Jadwal Kalibrasi"></i>';
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="mb-1 pb-1"
                                                        style="display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 40px;">
                                                        <div class="d-flex flex-column align-items-center justify-content-center" style="gap: 2px;">
                                                            {{-- Problem Icon - Prioritized --}}
                                                            @if($hasPendingLog)
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <a href="{{ route('calibration.tools.problem-logs', ['plant' => $plantCode, 'year' => $year]) }}" 
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
                                                                    <a href="{{ route('calibration.verifications.index', $statLinkParams) }}"
                                                                        style="text-decoration: none;">
                                                                        {!! $statIcon !!}
                                                                    </a>
                                                                @else
                                                                    {!! $statIcon !!}
                                                                @endif

                                                                {{-- Verification Date below check icon --}}
                                                                @if($hasVerification && $latestVerifDate)
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
                                                            <button type="button" class="btn btn-sm btn-success btn-verifikasi" data-toggle="modal"
                                                                data-target="#modalVerifikasiBaru" data-tool-id="{{ $tool->id }}"
                                                                title="Input Verifikasi">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>

                                                            <button type="button" class="btn btn-sm btn-info btn-edit-tool"
                                                                data-id="{{ $tool->id }}" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>

                                                            <button type="button" class="btn btn-sm btn-warning btn-report-problem"
                                                                data-toggle="modal" data-target="#modalReportProblem" data-tool-id="{{ $tool->id }}"
                                                                data-tool-name="{{ $tool->name_alat }}" title="Lapor Masalah">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                            </button>

                                                            <button type="button" class="btn btn-sm btn-danger"
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
                                                        @endif

                                                        @if($tool->certification_path)
                                                            <button type="button" class="btn btn-sm btn-primary view-pdf" data-toggle="modal"
                                                                data-target="#pdfModal"
                                                                data-url="{{ asset('storage/' . $tool->certification_path) }}"
                                                                data-title="Sertifikat - {{ $tool->name_alat }}">
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
                                                            <input type="text" name="range" class="form-control form-control-sm no-autoupper" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small font-weight-bold">Resolusi</label>
                                                            <input type="text" name="resolusi" class="form-control form-control-sm no-autoupper"
                                                                required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-left">
                                                <div class="form-group mb-2">
                                                    <label class="small font-weight-bold">Tanggal Beli</label>
                                                    <input type="date" name="tanggal_beli" class="form-control form-control-sm" required>
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
                                                        <input type="date" name="schedule_planning[]" class="form-control" required>
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
                                            <label class="small font-weight-bold">Schedule Kalibrasi (Planning) <span
                                                    class="text-danger">*</span></label>
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.addEventListener('input', function(e) {
                    var input = e.target;
                    if (!input || input.tagName !== 'INPUT') return;
                    var td = input.closest('td');
                    if (!td) return;
                    var tr = td.closest('tr');
                    if (!tr) return;
                    var tbody = tr.closest('tbody');
                    if (!tbody) return;
                    var tbodyId = tbody.getAttribute('id');
                    if (tbodyId !== 'modal-verif-verification-body') return;
                    var cells = Array.from(tr.querySelectorAll('td'));
                    var cellIndex = cells.indexOf(td);
                    if (cellIndex !== 1 && cellIndex !== 2) return;
                    var koreksiInput = cells[1] ? cells[1].querySelector('input') : null;
                    var ketidakpastianInput = cells[2] ? cells[2].querySelector('input') : null;
                    var hasilInput = cells[3] ? cells[3].querySelector('input') : null;
                    if (!koreksiInput || !ketidakpastianInput || !hasilInput) return;
                    var kv = (koreksiInput.value || '').trim();
                    var kpv = (ketidakpastianInput.value || '').trim();
                    if (kv === '' && kpv === '') {
                        hasilInput.value = '';
                    } else {
                        hasilInput.value = parseFloat(((parseFloat(kv) || 0) + (parseFloat(kpv) || 0)).toFixed(6));
                    }
                });
            });
        </script>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Modal Add Schedule rows
            $('#modal-add-schedule-btn').click(function () {
                var html = `
                                                                                    <div class="input-group input-group-sm mb-2">
                                                                                        <input type="date" name="schedule_planning[]" class="form-control" required>
                                                                                        <div class="input-group-append">
                                                                                            <button class="btn btn-danger modal-remove-schedule" type="button">
                                                                                                <i class="fas fa-minus"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>`;
                $('#modal-schedule-container').append(html);
            });

            $(document).on('click', '.modal-remove-schedule', function () {
                $(this).closest('.input-group').remove();
            });

            // --- Modal Verifikasi logic ---
            $('.btn-verifikasi').on('click', function () {
                var toolId = $(this).data('tool-id');
                $('#modal_verif_tool_select').val(toolId).trigger('change');
            });

            $('#modal_verif_tool_select').on('change', function () {
                var selected = $(this).find('option:selected');
                if (selected.val()) {
                    $('#modal_verif_name_alat').val(selected.data('name'));
                    $('#modal_verif_serial_number').val(selected.data('serial'));
                    $('#modal_verif_rentang_ukur').val(selected.data('range'));
                    $('#modal_verif_resolusi').val(selected.data('resolusi'));
                    $('#modal_verif_frekuensi_kalibrasi').val(selected.data('frekuensi'));

                    modalVerifUpdateNextCalibrationDate();
                }
            });

            $('#modal_verif_tanggal_verifikasi').on('change', function () {
                modalVerifUpdateNextCalibrationDate();
            });

            function modalVerifUpdateNextCalibrationDate() {
                var selected = $('#modal_verif_tool_select').find('option:selected');
                var verifDate = $('#modal_verif_tanggal_verifikasi').val();

                if (!selected.val() || !selected.data('schedules')) return;

                var schedules = selected.data('schedules');
                if (typeof schedules === 'string') {
                    schedules = JSON.parse(schedules);
                }

                if (schedules.length > 0) {
                    schedules.sort();
                    var referenceDate = verifDate || new Date().toISOString().split('T')[0];
                    // Find the SMALLEST schedule date that is GREATER than the reference date
                    var nextDate = schedules.find(date => {
                        // Ensure we compare Y-m-d strings
                        var d = typeof date === 'string' ? date.substring(0, 10) : date;
                        return d > referenceDate;
                    });
                    if (nextDate) {
                        $('#modal_verif_next_kalibrasi').val(nextDate.substring(0, 10));
                    }
                }
            }

            $('#modal-verif-add-row').on('click', function () {
                var newRow = `
                                                                    <tr>
                                                                        <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm no-autoupper"></td>
                                                                        <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm no-autoupper"></td>
                                                                        <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm no-autoupper"></td>
                                                                        <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm no-autoupper bg-light" readonly></td>
                                                                        <td class="text-center">
                                                                            <button type="button" class="btn btn-sm btn-outline-danger modal-verif-remove-row">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>`;
                $('#modal-verif-verification-body').append(newRow);
                modalVerifUpdateRemoveButtons();
            });

            $(document).on('click', '.modal-verif-remove-row', function () {
                $(this).closest('tr').remove();
                modalVerifUpdateRemoveButtons();
            });

            function modalVerifUpdateRemoveButtons() {
                var rowCount = $('#modal-verif-verification-body tr').length;
                if (rowCount <= 1) {
                    $('.modal-verif-remove-row').prop('disabled', true);
                } else {
                    $('.modal-verif-remove-row').prop('disabled', false);
                }
            }

            // Initialize DataTable if it exists
            if ($.fn.DataTable) {
                var table = $('#dataTable').DataTable({
                    dom: "<'row px-2 mb-2 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-end align-items-center'<'year-filter-container mr-3'>f>>" +
                        "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
                        "<'row px-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(difilter dari _MAX_ total data)",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    },
                    initComplete: function() {
                        // Create Year Filter
                        var selectedYear = "{{ $year ?? date('Y') }}";
                        var availableYears = @json($availableYears);

                        var yearHtml = '<div class="d-flex align-items-center mr-3">' +
                            '<label class="mb-0 mr-2" style="font-weight: normal; color: #858796;">Tahun:</label>' +
                            '<select id="customYearFilter" class="form-control form-control-sm" style="width: 85px; border-radius: 0.35rem;">';
                        
                        // All option
                        var allSelected = (selectedYear == 'all') ? 'selected' : '';
                        yearHtml += '<option value="all" ' + allSelected + '>All</option>';

                        availableYears.forEach(function(y) {
                            var selected = (y == selectedYear && selectedYear != 'all') ? 'selected' : '';
                            yearHtml += '<option value="' + y + '" ' + selected + '>' + y + '</option>';
                        });
                        
                        yearHtml += '</select></div>';
                        
                        $('.year-filter-container').html(yearHtml);
                        
                        $('#customYearFilter').on('change', function() {
                            var val = $(this).val();
                            window.location.href = "{{ route('calibration.tools.index') }}?plant={{ $plantCode }}&year=" + val;
                        });
                    }
                });
            }

            // Using DataTables compatible modal trigger
            $('#pdfModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var url = button.data('url');
                var title = button.data('title');

                var modal = $(this);
                modal.find('#pdfModalLabel').text(title);
                modal.find('#pdfFrame').attr('src', url);
                modal.find('#downloadPdf').attr('href', url);
            });

            // Clear iframe src when modal is closed to stop loading/playback
            $('#pdfModal').on('hidden.bs.modal', function () {
                $(this).find('#pdfFrame').attr('src', '');
            });

            // PR Input Change (delegated for DataTables pagination)
            $(document).on('change', '.pr-input', function () {
                var input = $(this);
                var toolId = input.data('tool-id');
                var prNumber = input.val();

                $.ajax({
                    url: "{{ route('calibration.tools.update-pr') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        tool_id: toolId,
                        pr_number: prNumber
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memperbarui PR.'
                        });
                    }
                });
            });

            // Reset PR Click (delegated for DataTables pagination)
            $(document).on('click', '.reset-pr', function () {
                var button = $(this);
                var toolId = button.data('tool-id');

                Swal.fire({
                    title: 'Reset PR?',
                    text: "Nomor dan tanggal PR akan dihapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Reset!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('calibration.tools.update-pr') }}",
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                tool_id: toolId,
                                pr_number: "" // Send empty to reset
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: 'PR telah direset.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000);
                                }
                            }
                        });
                    }
                });
            });
        });

        function confirmDeleteTool(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Seluruh riwayat verifikasi alat ini juga akan terhapus dan tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-tool-form-' + id).submit();
                }
            });
        }

        // Edit Tool Logic
        $('.btn-edit-tool').on('click', function () {
            var id = $(this).data('id');
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            var editUrl = "{{ route('calibration.tools.edit', ':id') }}".replace(':id', id);

            $.ajax({
                url: editUrl,
                type: 'GET',
                data: { plant: "{{ $plantCode }}" },
                success: function (response) {
                    var tool = response.tool;
                    $('#edit_bagian').val(tool.bagian);
                    $('#edit_name_alat').val(tool.name_alat);
                    $('#edit_merk').val(tool.merk);
                    $('#edit_merk').val(tool.merk);
                    $('#edit_serial_number').val(tool.serial_number);
                    $('#edit_range').val(tool.range);
                    $('#edit_resolusi').val(tool.resolusi);
                    $('#edit_tanggal_beli').val(tool.tanggal_beli_formatted ? tool.tanggal_beli_formatted : '');
                    $('#edit_frekuensi_kalibrasi').val(tool.frekuensi_kalibrasi);
                    $('#edit_jenis_kalibrasi').val(tool.jenis_kalibrasi);
                    $('#edit_riwayat_kalibrasi').val(tool.riwayat_kalibrasi);

                    // Certificate
                    if (tool.certification_path) {
                        $('#edit_existing_cert').html(`<a href="/storage/${tool.certification_path}" target="_blank" class="badge badge-info"><i class="fas fa-file-pdf mr-1"></i> Lihat Sertifikat</a>`);
                    } else {
                        $('#edit_existing_cert').html('');
                    }

                    // Schedules
                    var schHtml = '';
                    if (tool.schedules && tool.schedules.length > 0) {
                        tool.schedules.forEach(function (sch) {
                            schHtml += `
                                                                        <tr>
                                                                            <td>
                                                                                <input type="hidden" name="schedule_ids[]" value="${sch.id}">
                                                                                <input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${sch.schedule_date_formatted}" required>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" value="${sch.pr_number || ''}" placeholder="PR Number...">
                                                                            </td>
                                                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                                                                        </tr>`;
                        });
                    } else if (tool.schedule_planning) {
                        schHtml = `
                                                                    <tr>
                                                                        <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm" value="${tool.schedule_planning.substring(0, 10)}" required></td>
                                                                        <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" placeholder="PR Number..."></td>
                                                                        <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                                                                    </tr>`;
                    } else {
                        schHtml = `
                                                                    <tr>
                                                                        <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm" required></td>
                                                                        <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" placeholder="PR Number..."></td>
                                                                        <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                                                                    </tr>`;
                    }
                    $('#edit-schedule-table tbody').html(schHtml);

                    // Update form action
                    var url = "{{ route('calibration.tools.update', ':id') }}";
                    url = url.replace(':id', id);
                    $('#formEditAlat').attr('action', url);

                    $('#modalEditAlat').modal('show');
                    btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
                },
                error: function (xhr) {
                    var errorMsg = 'Gagal mengambil data alat.';
                    if (xhr.status === 404) errorMsg += ' (Error 404: Alat tidak ditemukan)';
                    else if (xhr.status === 403) errorMsg += ' (Error 403: Anda tidak memiliki akses)';
                    else if (xhr.status === 500) errorMsg += ' (Error 500: Terjadi kesalahan di server)';

                    alert(errorMsg);
                    btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
                }
            });
        });

        $(document).on('click', '.add-edit-schedule-row', function () {
            var newRow = `
                                                        <tr>
                                                            <td><input type="date" name="schedule_planning[]" class="form-control form-control-sm" required></td>
                                                            <td><input type="text" name="schedule_pr_numbers[]" class="form-control form-control-sm no-autoupper" placeholder="PR Number..."></td>
                                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-schedule-row"><i class="fas fa-trash"></i></button></td>
                                                        </tr>`;
            $('#edit-schedule-table tbody').append(newRow);
        });

        $(document).on('click', '.remove-schedule-row', function () {
            $(this).closest('tr').remove();
        });

        // Report Problem Logic
        $(document).on('click', '.btn-report-problem', function() {
            var toolId = $(this).data('tool-id');
            var toolName = $(this).data('tool-name');

            $('#problem_tool_id').val(toolId);
            $('#problem_tool_name').val(toolName);

            // Reset modal
            $('#problem_type').val('');
            $('#action_taken_wrapper').hide();
            $('#action_taken').prop('required', false);
            $('#action_taken').val('');
            $('#rusak_info').hide();
        });

        $('#problem_type').on('change', function() {
            var type = $(this).val();
            if (type === 'ERROR' || type === 'RUSAK') {
                $('#action_taken_wrapper').show();
                $('#action_taken').prop('required', true);

                if (type === 'RUSAK') {
                    $('#rusak_info').show();
                } else {
                    $('#rusak_info').hide();
                }
            } else {
                $('#action_taken_wrapper').hide();
                $('#action_taken').prop('required', false);
            }
        });
    </script>
@endpush