@extends('layouts.admin')

@php
    $plantCode = strtolower($plantCode ?: 'karawang');
    $isJkt = in_array($plantCode, ['jakarta', 'jkt']);
    $docHeader = \App\Models\GeneralSetting::getDocHeader('leveling_inspector', $plantCode, [
        'no_dokumen' => $isJkt ? 'QC-JKT-F-052' : 'PI-KRW-F-052',
        'tgl_terbit' => date('d/m/Y'),
        'revisi'     => '-',
        'halaman'    => '1 / 1',
    ]);
    $pageTitle = 'Rekap & Monitoring Leveling Inspector Tahunan - ' . $year;
@endphp

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-2 border-0">
        <div class="card-body p-2">

            <!-- 1. Header Dokumen ISO per Plant -->
            <div class="mb-2">
                <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                REKAP &amp; MONITORING LEVELING INSPECTOR TAHUN {{ $year }}
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e6; padding:0 !important; vertical-align:top; white-space:nowrap;">
                            <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                                <tr style="height:100%;">
                                    <td style="border:none; padding:4px 6px 4px 4px; vertical-align:top; height:100%; white-space:nowrap;">
                                        <table style="border-collapse:collapse; border:1px solid #dee2e6; font-size:0.65rem; background:#fff; height:100%; width:100%;">
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:700; color:#212529; white-space:nowrap;">{{ $docHeader['no_dokumen'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="border:none; padding:4px 4px 4px 0; vertical-align:top; height:100%;">
                                        <table style="border-collapse:collapse; border:1px solid #dee2e6; text-align:center; font-size:0.65rem; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:388px;">
                                            <thead>
                                                <tr>
                                                    <th style="border:1px solid #dee2e6; padding:3px 2px; font-weight:600; color:#495057; background:#fff; width:28px;">Tgl.</th>
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Prepared By</th>
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Checked By</th>
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Approved By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td rowspan="3" style="border:1px solid #dee2e6; padding:2px; vertical-align:middle; text-align:center; width:28px;">
                                                        <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:0.58rem; font-weight:400; margin:0 auto; color:#6c757d;">
                                                            {{ date('d-M-y') }}
                                                        </div>
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        @if($isJkt)
                                                            <img src="{{ asset('signatures/suli.png') }}" alt="Masuli" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        @else
                                                            <img src="{{ asset('signatures/arif.png') }}" alt="Arief H" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        @endif
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">{{ $isJkt ? 'Masuli' : 'Arief H' }}</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Iwan S</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Desti K</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Spv. QC</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Mgr. Quality</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <style>
                .rekap-filter-select {
                    padding: 2px 8px !important;
                    font-size: 0.75rem !important;
                    border: 1px solid #cbd5e1 !important;
                    background: #fff !important;
                    height: 31px !important;
                    border-radius: 4px !important;
                    font-weight: 600 !important;
                    color: #212529 !important;
                }
                .rekap-kpi-grid {
                    display: grid;
                    grid-template-columns: repeat(7, minmax(0, 1fr));
                    gap: 8px;
                }
                @media (max-width: 1199.98px) {
                    .rekap-kpi-grid {
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                    }
                }
                @media (max-width: 767.98px) {
                    .rekap-kpi-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }
                .rekap-kpi-box {
                    background: #fff;
                    border: 1px solid #e2e8f0;
                    border-radius: 6px;
                    padding: 7px 10px;
                    min-width: 0;
                }
                .rekap-kpi-title {
                    font-size: 0.63rem;
                    color: #6c757d;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .rekap-kpi-val {
                    font-size: 0.94rem;
                    font-weight: 700;
                    line-height: 1.25;
                    white-space: nowrap;
                }
                .rekap-kpi-sub {
                    font-size: 0.61rem;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                #rekapLevelingTable tbody tr.rekap-row-ng td {
                    background-color: #fee2e2 !important;
                }
                #rekapLevelingTable tbody tr.rekap-row-ng:hover td {
                    background-color: #fecaca !important;
                }
                .rekap-meja-editable {
                    cursor: pointer;
                    user-select: none;
                }
                .rekap-meja-editable:hover .rekap-meja-text {
                    text-decoration: underline;
                    color: #2563eb;
                }
                .rekap-view-link {
                    color: #212529 !important;
                    font-weight: 700;
                    font-size: 0.65rem;
                    text-decoration: none;
                }
                .rekap-view-link:hover {
                    color: #2563eb !important;
                    text-decoration: underline;
                }
                .rekap-meja-select {
                    font-size: 0.7rem !important;
                    height: 24px !important;
                    padding: 1px 6px !important;
                    border: 1px solid #4e73df !important;
                    border-radius: 4px !important;
                    background-color: #fff !important;
                    color: #212529 !important;
                    min-width: 105px;
                    cursor: pointer;
                }
                .rekap-table-wrapper {
                    max-height: none !important;
                    overflow-x: auto !important;
                    overflow-y: visible !important;
                    border: 1px solid #e2e8f0 !important;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                    background: #fff !important;
                }
                #rekapLevelingTable {
                    border-collapse: separate !important;
                    border-spacing: 0 !important;
                    width: 100% !important;
                    margin-bottom: 0 !important;
                    background: #fff !important;
                }
                #rekapLevelingTable > thead > tr > th {
                    position: -webkit-sticky !important;
                    position: sticky !important;
                    background-color: #f8fafc !important;
                    color: #475569 !important;
                    font-weight: 700 !important;
                    text-transform: uppercase;
                    font-size: 0.62rem !important;
                    padding: 5px 4px !important;
                    border: 1px solid #cbd5e1 !important;
                    text-align: center !important;
                    vertical-align: middle !important;
                    white-space: nowrap !important;
                }
                #rekapLevelingTable > thead > tr:nth-child(1) > th {
                    top: 0 !important;
                    z-index: 105 !important;
                    height: 26px !important;
                }
                #rekapLevelingTable > thead > tr:nth-child(2) > th {
                    top: 26px !important;
                    z-index: 104 !important;
                    height: 22px !important;
                }
                #rekapLevelingTable > thead > tr:nth-child(1) > th[rowspan="2"] {
                    top: 0 !important;
                    height: 48px !important;
                    z-index: 106 !important;
                }
                #rekapLevelingTable > thead > tr > th.th-prinsip-msk { color: #1e40af !important; }
                #rekapLevelingTable > thead > tr > th.th-prinsip-kt { color: #166534 !important; }
                #rekapLevelingTable > thead > tr > th.th-prinsip-png { color: #92400e !important; }
                #rekapLevelingTable > thead > tr > th.th-prinsip-pssp { color: #5b21b6 !important; }
                #rekapLevelingTable > thead > tr > th.th-prinsip-kao { color: #9d174d !important; }
                #rekapLevelingTable tbody td {
                    border: 1px solid #cbd5e1 !important;
                    vertical-align: middle !important;
                    color: #334155 !important;
                    font-size: 0.68rem !important;
                    padding: 5px 6px !important;
                    line-height: 1.25 !important;
                }
                #rekapLevelingTable tbody tr:hover {
                    background-color: #f8fafc !important;
                }
                .rekap-link {
                    text-decoration: none !important;
                    font-weight: 700;
                }
                .rekap-link:hover {
                    text-decoration: underline !important;
                }
                @media print {
                    @page { size: A4 landscape; margin: 6mm; }
                    .no-print, #accordionSidebar, nav.topbar, footer.sticky-footer { display: none !important; }
                    #content-wrapper, .container-fluid, .card, .card-body {
                        padding: 0 !important;
                        margin: 0 !important;
                        box-shadow: none !important;
                        border: none !important;
                    }
                    #rekapLevelingTable > thead > tr > th { position: static !important; }
                }
            </style>

            <!-- 2. Bar Filter & Tombol Aksi -->
            <form action="{{ route('checksheet.leveling_inspector.rekap') }}" method="GET"
                  id="rekapFilterForm"
                  class="d-flex flex-wrap align-items-end bg-white p-0 rounded mb-3 no-print"
                  style="gap: 12px;">
                <input type="hidden" name="plant" value="{{ $plantCode }}">

                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold">Search Inspector</label>
                    <input type="text" id="rekapSearchInspector" class="form-control form-control-sm rekap-filter-select"
                           style="width: 210px;" placeholder="Type inspector / section..." autocomplete="off">
                </div>

                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold">Section</label>
                    <select name="bagian" id="rekapBagian" class="form-control form-control-sm rekap-filter-select" style="width: 240px;">
                        <option value="">-- All Sections --</option>
                        @foreach($bagianOptions as $opt)
                            <option value="{{ $opt }}" {{ $bagianFilter === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold">Year</label>
                    <select name="year" id="rekapYear" class="form-control form-control-sm rekap-filter-select" style="width: 100px;">
                        @php $cy = (int) date('Y'); @endphp
                        @for($y = $cy - 2; $y <= $cy + 2; $y++)
                            <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="ml-auto d-flex flex-wrap align-items-center mt-auto" style="gap: 6px;">
                    <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode]) }}"
                       class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height: 31px;">
                        <i class="fas fa-arrow-left fa-sm mr-1"></i> Back to Checksheet
                    </a>
                    <a href="{{ route('checksheet.leveling_inspector.rekap.print', ['plant' => $plantCode, 'year' => $year, 'bagian' => $bagianFilter]) }}"
                       id="btnPrintRekap"
                       class="btn btn-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center btn-print-direct no-loader"
                       style="height: 31px;"
                       title="Print Annual Leveling Inspector Recap">
                        <i class="fas fa-print fa-sm mr-1"></i> Print
                    </a>
                </div>
            </form>

            <!-- 3. Ringkasan Monitoring Tahunan (1 baris, tanpa badge) -->
            <div class="rekap-kpi-grid mb-3">
                <div class="rekap-kpi-box">
                    <div class="rekap-kpi-title" title="Total Inspectors ({{ strtoupper($plantCode) }})">Total Inspectors ({{ strtoupper($plantCode) }})</div>
                    <div class="rekap-kpi-val text-dark">{{ $summary['total_inspectors'] }}</div>
                    <div class="rekap-kpi-sub text-muted">Audited {{ $year }}: {{ $summary['audited_count'] }}</div>
                </div>

                <div class="rekap-kpi-box">
                    <div class="rekap-kpi-title" title="Period 1 Realization (Jan – Jun)">Period 1 (Jan – Jun)</div>
                    <div class="rekap-kpi-val text-primary">
                        {{ $summary['p1_done'] }} / {{ $summary['total_inspectors'] }}
                        <span style="font-size:0.72rem;">({{ $summary['p1_pct'] }}%)</span>
                    </div>
                    <div class="rekap-kpi-sub text-muted">Pending: {{ max(0, $summary['total_inspectors'] - $summary['p1_done']) }}</div>
                </div>

                <div class="rekap-kpi-box">
                    <div class="rekap-kpi-title" title="Period 2 Realization (Jul – Dec)">Period 2 (Jul – Dec)</div>
                    <div class="rekap-kpi-val text-success">
                        {{ $summary['p2_done'] }} / {{ $summary['total_inspectors'] }}
                        <span style="font-size:0.72rem;">({{ $summary['p2_pct'] }}%)</span>
                    </div>
                    <div class="rekap-kpi-sub text-muted">Pending: {{ max(0, $summary['total_inspectors'] - $summary['p2_done']) }}</div>
                </div>

                <div class="rekap-kpi-box">
                    <div class="rekap-kpi-title" title="NG Closure ({{ $year }})">NG Closure ({{ $year }})</div>
                    @if($summary['total_ng'] > 0)
                        <div class="rekap-kpi-val {{ $summary['total_open_ng'] > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $summary['total_closed_ng'] }} / {{ $summary['total_ng'] }} Closed
                            <span style="font-size:0.72rem;">({{ $summary['ng_closure_pct'] }}%)</span>
                        </div>
                        <div class="rekap-kpi-sub {{ $summary['total_open_ng'] > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $summary['total_open_ng'] }} open
                        </div>
                    @else
                        <div class="rekap-kpi-val text-success">0 / 0 Closed</div>
                        <div class="rekap-kpi-sub text-muted">No NG findings</div>
                    @endif
                </div>

                <div class="rekap-kpi-box">
                    <div class="rekap-kpi-title" title="Average Level ({{ $year }})">Average Level ({{ $year }})</div>
                    <div class="rekap-kpi-val text-primary">
                        @if($summary['avg_level_overall'] !== null)
                            {{ number_format($summary['avg_level_overall'], 2) }} <span class="text-muted" style="font-size:0.72rem;">/ 3.00</span>
                        @else
                            -
                        @endif
                    </div>
                    <div class="rekap-kpi-sub text-muted">
                        Periode 1 : {{ $summary['avg_level_p1'] !== null ? number_format($summary['avg_level_p1'], 2) : '-' }} | Periode 2 : {{ $summary['avg_level_p2'] !== null ? number_format($summary['avg_level_p2'], 2) : '-' }}
                    </div>
                </div>

                <div class="rekap-kpi-box">
                    <div class="rekap-kpi-title" title="Pass Rate / Result OK ({{ $year }})">Result OK ({{ $year }})</div>
                    <div class="rekap-kpi-val {{ $summary['result_ng_count'] > 0 ? 'text-primary' : 'text-success' }}">
                        {{ $summary['result_ok_count'] }} / {{ $summary['audited_count'] }} OK
                        <span style="font-size:0.72rem;">({{ $summary['result_ok_pct'] }}%)</span>
                    </div>
                    <div class="rekap-kpi-sub {{ $summary['result_ng_count'] > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $summary['result_ng_count'] }} with NG
                    </div>
                </div>

                <div class="rekap-kpi-box">
                    <div class="rekap-kpi-title" title="Approval Progress ({{ $year }})">Approval ({{ $year }})</div>
                    <div id="kpiApprovalVal" class="rekap-kpi-val {{ $summary['total_stages_count'] > 0 && $summary['approved_stages_count'] === $summary['total_stages_count'] ? 'text-success' : ($summary['approved_stages_count'] > 0 ? 'text-primary' : 'text-dark') }}">
                        {{ $summary['approved_stages_count'] }} / {{ $summary['total_stages_count'] }} Stages
                        <span style="font-size:0.72rem;">({{ $summary['approval_stage_pct'] }}%)</span>
                    </div>
                    <div id="kpiApprovalSub" class="rekap-kpi-sub text-muted">
                        {{ $summary['full_approved_count'] }} / {{ $summary['audited_count'] }} full approved
                    </div>
                </div>
            </div>

            <!-- 4. Tabel Matriks Monitoring Tahunan -->
            <div class="table-responsive rekap-table-wrapper">
                <table class="table table-hover"
                       id="rekapLevelingTable"
                       data-approve-url="{{ route('checksheet.leveling_inspector.rekap.approve') }}"
                       data-meja-url="{{ route('checksheet.leveling_inspector.rekap.meja_mesin') }}"
                       data-plant="{{ $plantCode }}"
                       data-year="{{ $year }}"
                       data-csrf="{{ csrf_token() }}"
                       width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:36px;">NO</th>
                            <th rowspan="2" style="min-width:155px;">INSPECTOR NAME</th>
                            <th rowspan="2" style="min-width:155px;">SECTION</th>
                            <th rowspan="2" style="min-width:85px;">TABLE / MACHINE</th>
                            <th colspan="9">PERIOD 1 (JAN – JUN {{ $year }})</th>
                            <th colspan="9">PERIOD 2 (JUL – DEC {{ $year }})</th>
                            <th rowspan="2" style="width:65px;">NG OPEN</th>
                            <th colspan="4">APPROVAL</th>
                            <th rowspan="2" class="no-print text-nowrap">ACTION</th>
                        </tr>
                        <tr>
                            <th style="width:62px;">STATUS</th>
                            <th style="width:74px;">AUDIT DATE</th>
                            @foreach($prinsipList as $p)
                                <th class="th-prinsip-{{ strtolower($p) }}" style="width:38px;">{{ $p }}</th>
                            @endforeach
                            <th style="width:58px; white-space:normal !important; line-height:1.15;">AVG.<br>LEVEL</th>
                            <th class="no-print" style="width:48px;">RESULT</th>

                            <th style="width:62px;">STATUS</th>
                            <th style="width:74px;">AUDIT DATE</th>
                            @foreach($prinsipList as $p)
                                <th class="th-prinsip-{{ strtolower($p) }}" style="width:38px;">{{ $p }}</th>
                            @endforeach
                            <th style="width:58px; white-space:normal !important; line-height:1.15;">AVG.<br>LEVEL</th>
                            <th class="no-print" style="width:48px;">RESULT</th>

                            <th style="min-width:95px;">AUDITOR</th>
                            <th style="min-width:105px;">KARU / KASHIFT</th>
                            <th style="min-width:95px;">SPV</th>
                            <th style="min-width:100px;">ASST. MANAGER</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(empty($rows))
                            <tr>
                                <td colspan="28" class="text-center text-muted py-4">
                                    No inspector data found for the selected filter.
                                </td>
                            </tr>
                        @else
                            @foreach($rows as $idx => $r)
                                <tr class="js-rekap-row {{ !empty($r['has_ng']) ? 'rekap-row-ng' : '' }}"
                                    data-has-audit="{{ $r['has_audit'] ? '1' : '0' }}"
                                    data-search="{{ strtolower($r['inspector_name'] . ' ' . $r['bagian'] . ' ' . ($r['meja_mesin'] ?? '')) }}">
                                    <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                                    <td class="font-weight-bold text-dark">{{ $r['inspector_name'] }}</td>
                                    <td>{{ $r['bagian'] }}</td>
                                    <td class="text-center text-nowrap {{ (!empty($canEditMejaMesin) && !empty($r['latest_audit_id'])) ? 'rekap-meja-editable js-rekap-meja-cell' : '' }}"
                                        @if(!empty($canEditMejaMesin) && !empty($r['latest_audit_id'])) title="Double-click to edit Table / Machine" @endif>
                                        @if(!empty($canEditMejaMesin) && !empty($r['latest_audit_id']))
                                            @php
                                                $currentMeja = ($r['meja_mesin'] ?? '-') === '-' ? '' : $r['meja_mesin'];
                                                $bagianLower = strtolower((string) ($r['bagian'] ?? ''));
                                                $isMachineRow = str_contains($bagianLower, 'inproses') || str_contains($bagianLower, 'in process') || str_contains($bagianLower, 'in-process');
                                                $rowMejaOpts = $isMachineRow ? ($mesinOptions ?? []) : ($mejaOptions ?? []);
                                            @endphp
                                            <span class="rekap-meja-text js-rekap-meja-text">{{ $r['meja_mesin'] ?? '-' }}</span>
                                            <select class="form-control form-control-sm rekap-meja-select js-rekap-meja-select d-none"
                                                    data-audit-id="{{ $r['latest_audit_id'] }}"
                                                    data-prev="{{ $currentMeja }}">
                                                <option value="">-</option>
                                                @foreach($rowMejaOpts as $optVal)
                                                    <option value="{{ $optVal }}" {{ $currentMeja === $optVal ? 'selected' : '' }}>{{ $optVal }}</option>
                                                @endforeach
                                                @if($currentMeja !== '' && !in_array($currentMeja, $rowMejaOpts, true))
                                                    <option value="{{ $currentMeja }}" selected>{{ $currentMeja }}</option>
                                                @endif
                                            </select>
                                        @else
                                            {{ $r['meja_mesin'] ?? '-' }}
                                        @endif
                                    </td>

                                    {{-- PERIOD 1 (JAN - JUN) --}}
                                    @if($r['p1'])
                                        <td class="text-center">
                                            <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode, 'inspector_id' => $r['inspector_id'], 'audit_id' => $r['p1']['audit_id']]) }}"
                                               class="rekap-link text-success"
                                               title="Open audit detail ({{ $r['p1']['part_name'] }})">
                                                Done
                                            </a>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode, 'inspector_id' => $r['inspector_id'], 'audit_id' => $r['p1']['audit_id']]) }}"
                                               class="rekap-link text-dark"
                                               title="Part: {{ $r['p1']['part_name'] }}">
                                                {{ $r['p1']['audit_date'] }}
                                            </a>
                                        </td>
                                        @foreach($prinsipList as $p)
                                            @php
                                                $lv = $r['p1']['prinsip_levels'][$p] ?? '-';
                                                $lvCls = $lv === '3' ? 'text-success' : ($lv === '2' ? 'text-primary' : ($lv === '1' ? 'text-dark' : ($lv === '0' ? 'text-danger' : 'text-muted')));
                                            @endphp
                                            <td class="text-center font-weight-bold {{ $lvCls }}">{{ $lv }}</td>
                                        @endforeach
                                        <td class="text-center font-weight-bold text-primary">
                                            {{ $r['p1']['score'] !== null ? $r['p1']['score'] : '-' }}
                                        </td>
                                        <td class="text-center text-nowrap no-print">
                                            <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode, 'inspector_id' => $r['inspector_id'], 'audit_id' => $r['p1']['audit_id']]) }}"
                                               class="rekap-view-link"
                                               title="View Period 1 audit result ({{ $r['p1']['audit_date'] }} - {{ $r['p1']['part_name'] }})">
                                                View
                                            </a>
                                        </td>
                                    @else
                                        <td class="text-center text-danger font-weight-bold">Pending</td>
                                        <td class="text-center text-muted">-</td>
                                        @foreach($prinsipList as $p)
                                            <td class="text-center text-muted">-</td>
                                        @endforeach
                                        <td class="text-center text-muted">-</td>
                                        <td class="text-center text-muted no-print">-</td>
                                    @endif

                                    {{-- PERIOD 2 (JUL - DEC) --}}
                                    @if($r['p2'])
                                        <td class="text-center">
                                            <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode, 'inspector_id' => $r['inspector_id'], 'audit_id' => $r['p2']['audit_id']]) }}"
                                               class="rekap-link text-success"
                                               title="Open audit detail ({{ $r['p2']['part_name'] }})">
                                                Done
                                            </a>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode, 'inspector_id' => $r['inspector_id'], 'audit_id' => $r['p2']['audit_id']]) }}"
                                               class="rekap-link text-dark"
                                               title="Part: {{ $r['p2']['part_name'] }}">
                                                {{ $r['p2']['audit_date'] }}
                                            </a>
                                        </td>
                                        @foreach($prinsipList as $p)
                                            @php
                                                $lv = $r['p2']['prinsip_levels'][$p] ?? '-';
                                                $lvCls = $lv === '3' ? 'text-success' : ($lv === '2' ? 'text-primary' : ($lv === '1' ? 'text-dark' : ($lv === '0' ? 'text-danger' : 'text-muted')));
                                            @endphp
                                            <td class="text-center font-weight-bold {{ $lvCls }}">{{ $lv }}</td>
                                        @endforeach
                                        <td class="text-center font-weight-bold text-primary">
                                            {{ $r['p2']['score'] !== null ? $r['p2']['score'] : '-' }}
                                        </td>
                                        <td class="text-center text-nowrap no-print">
                                            <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode, 'inspector_id' => $r['inspector_id'], 'audit_id' => $r['p2']['audit_id']]) }}"
                                               class="rekap-view-link"
                                               title="View Period 2 audit result ({{ $r['p2']['audit_date'] }} - {{ $r['p2']['part_name'] }})">
                                                View
                                            </a>
                                        </td>
                                    @else
                                        <td class="text-center text-danger font-weight-bold">Pending</td>
                                        <td class="text-center text-muted">-</td>
                                        @foreach($prinsipList as $p)
                                            <td class="text-center text-muted">-</td>
                                        @endforeach
                                        <td class="text-center text-muted">-</td>
                                        <td class="text-center text-muted no-print">-</td>
                                    @endif

                                    <td class="text-center font-weight-bold {{ $r['open_ng_count'] > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $r['open_ng_count'] }}
                                    </td>
                                    @foreach($r['approval'] as $st)
                                        <td class="text-center align-middle js-rekap-approval-stage"
                                            data-inspector-id="{{ $r['inspector_id'] }}"
                                            data-stage="{{ $st['stage'] }}"
                                            data-approved="{{ ($r['has_audit'] && $st['approved']) ? '1' : '0' }}"
                                            style="font-size:0.65rem; line-height:1.3;">
                                            @if(!$r['has_audit'])
                                                <span class="text-muted">-</span>
                                            @elseif($st['approved'])
                                                <div class="font-weight-bold text-success" style="font-size:0.65rem;">Approved</div>
                                                <div class="text-dark font-weight-bold" style="font-size:0.63rem;">{{ $st['approver_name'] }}</div>
                                                @if($st['approved_at'])
                                                    <div class="text-muted" style="font-size:0.6rem;">{{ $st['approved_at'] }}</div>
                                                @endif
                                            @else
                                                <div class="text-muted">Pending</div>
                                                @if(!empty($st['pending_name']))
                                                    <div class="text-dark font-weight-bold" style="font-size:0.62rem;">{{ $st['pending_name'] }}</div>
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center text-nowrap no-print">
                                        @php
                                            $approveStage = collect($r['approval'])->firstWhere('can_approve', true);
                                            $cancelStage = collect($r['approval'])->firstWhere('can_cancel', true);
                                        @endphp
                                        <div class="d-inline-flex align-items-center justify-content-center flex-nowrap js-rekap-approval-actions" data-inspector-id="{{ $r['inspector_id'] }}" style="gap: 4px; white-space: nowrap;">
                                            @if($r['has_audit'] && ($approveStage || $cancelStage))
                                                @if($approveStage)
                                                    <button type="button"
                                                            class="btn btn-success btn-sm px-2 py-0 js-rekap-approve-btn"
                                                            style="font-size:0.63rem; line-height:1.5;"
                                                            data-inspector-id="{{ $r['inspector_id'] }}"
                                                            data-stage="{{ $approveStage['stage'] }}"
                                                            data-label="{{ $approveStage['label'] }}"
                                                            title="Approve">
                                                        <i class="fas fa-check mr-1"></i>Approve
                                                    </button>
                                                @endif
                                                @if($cancelStage)
                                                    <button type="button"
                                                            class="btn btn-outline-dark btn-sm px-2 py-0 js-rekap-cancel-approve-btn"
                                                            style="font-size:0.63rem; line-height:1.5;"
                                                            data-inspector-id="{{ $r['inspector_id'] }}"
                                                            data-stage="{{ $cancelStage['stage'] }}"
                                                            data-label="{{ $cancelStage['label'] }}"
                                                            title="Undo Approval">
                                                        <i class="fas fa-undo-alt"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/checksheet/leveling-rekap.js') }}?v={{ time() }}"></script>
@endpush
