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
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Dibuat</th>
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Diperiksa</th>
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Diketahui</th>
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
                .rekap-kpi-box {
                    background: #fff;
                    border: 1px solid #e2e8f0;
                    border-radius: 6px;
                    padding: 8px 12px;
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
                    <label class="mb-1 small font-weight-bold">Tahun</label>
                    <select name="year" id="rekapYear" class="form-control form-control-sm rekap-filter-select" style="width: 100px;">
                        @php $cy = (int) date('Y'); @endphp
                        @for($y = $cy - 2; $y <= $cy + 2; $y++)
                            <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold">Bagian</label>
                    <select name="bagian" id="rekapBagian" class="form-control form-control-sm rekap-filter-select" style="width: 240px;">
                        <option value="">-- Semua Bagian --</option>
                        @foreach($bagianOptions as $opt)
                            <option value="{{ $opt }}" {{ $bagianFilter === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold">Cari Inspector</label>
                    <input type="text" id="rekapSearchInspector" class="form-control form-control-sm rekap-filter-select"
                           style="width: 210px;" placeholder="Ketik nama inspector / bagian..." autocomplete="off">
                </div>

                <div class="ml-auto d-flex flex-wrap align-items-center mt-auto" style="gap: 6px;">
                    <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode]) }}"
                       class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height: 31px;">
                        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali ke Checksheet
                    </a>
                    <a href="{{ route('checksheet.leveling_inspector.rekap.print', ['plant' => $plantCode, 'year' => $year, 'bagian' => $bagianFilter]) }}"
                       id="btnPrintRekap"
                       class="btn btn-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center btn-print-direct no-loader"
                       style="height: 31px;"
                       title="Cetak Rekap Leveling Inspector Tahunan">
                        <i class="fas fa-print fa-sm mr-1"></i> Cetak
                    </a>
                </div>
            </form>

            <!-- 3. Ringkasan Monitoring Tahunan (tanpa badge, teks berwarna saja) -->
            <div class="row mx-0 mb-3" style="gap: 0;">
                <div class="col-6 col-md-3 px-1 mb-2 mb-md-0">
                    <div class="rekap-kpi-box">
                        <div class="text-muted" style="font-size:0.68rem;">Total Inspector ({{ strtoupper($plantCode) }})</div>
                        <div class="font-weight-bold text-dark" style="font-size:1.1rem;">{{ $summary['total_inspectors'] }} Orang</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 px-1 mb-2 mb-md-0">
                    <div class="rekap-kpi-box">
                        <div class="text-muted" style="font-size:0.68rem;">Realisasi Periode 1 (Jan – Jun)</div>
                        <div class="font-weight-bold text-primary" style="font-size:1.1rem;">
                            {{ $summary['p1_done'] }} / {{ $summary['total_inspectors'] }}
                            <span style="font-size:0.8rem;">({{ $summary['p1_pct'] }}%)</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 px-1">
                    <div class="rekap-kpi-box">
                        <div class="text-muted" style="font-size:0.68rem;">Realisasi Periode 2 (Jul – Des)</div>
                        <div class="font-weight-bold text-success" style="font-size:1.1rem;">
                            {{ $summary['p2_done'] }} / {{ $summary['total_inspectors'] }}
                            <span style="font-size:0.8rem;">({{ $summary['p2_pct'] }}%)</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 px-1">
                    <div class="rekap-kpi-box">
                        <div class="text-muted" style="font-size:0.68rem;">Temuan NG Belum Closed ({{ $year }})</div>
                        <div class="font-weight-bold {{ $summary['total_open_ng'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size:1.1rem;">
                            {{ $summary['total_open_ng'] }} Temuan
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Tabel Matriks Monitoring Tahunan -->
            <div class="table-responsive rekap-table-wrapper">
                <table class="table table-hover"
                       id="rekapLevelingTable"
                       data-approve-url="{{ route('checksheet.leveling_inspector.rekap.approve') }}"
                       data-plant="{{ $plantCode }}"
                       data-year="{{ $year }}"
                       data-csrf="{{ csrf_token() }}"
                       width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:36px;">NO</th>
                            <th rowspan="2" style="min-width:155px;">NAMA INSPECTOR</th>
                            <th rowspan="2" style="min-width:155px;">BAGIAN</th>
                            <th colspan="9">PERIODE 1 (JAN – JUN {{ $year }})</th>
                            <th colspan="9">PERIODE 2 (JUL – DES {{ $year }})</th>
                            <th rowspan="2" style="width:65px;">NG OPEN</th>
                            <th colspan="4">APPROVAL</th>
                            <th rowspan="2" class="no-print text-nowrap">AKSI</th>
                        </tr>
                        <tr>
                            <th style="width:62px;">STATUS</th>
                            <th style="width:74px;">TGL AUDIT</th>
                            @foreach($prinsipList as $p)
                                <th class="th-prinsip-{{ strtolower($p) }}" style="width:38px;">{{ $p }}</th>
                            @endforeach
                            <th style="width:52px;">RATA-RATA LEVEL</th>
                            <th class="no-print" style="width:48px;">RESULT</th>

                            <th style="width:62px;">STATUS</th>
                            <th style="width:74px;">TGL AUDIT</th>
                            @foreach($prinsipList as $p)
                                <th class="th-prinsip-{{ strtolower($p) }}" style="width:38px;">{{ $p }}</th>
                            @endforeach
                            <th style="width:52px;">RATA-RATA LEVEL</th>
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
                                <td colspan="27" class="text-center text-muted py-4">
                                    Tidak ada data inspector untuk filter yang dipilih.
                                </td>
                            </tr>
                        @else
                            @foreach($rows as $idx => $r)
                                <tr class="js-rekap-row"
                                    data-search="{{ strtolower($r['inspector_name'] . ' ' . $r['bagian']) }}">
                                    <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                                    <td class="font-weight-bold text-dark">{{ $r['inspector_name'] }}</td>
                                    <td>{{ $r['bagian'] }}</td>

                                    {{-- PERIODE 1 (JAN - JUN) --}}
                                    @if($r['p1'])
                                        <td class="text-center">
                                            <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode, 'inspector_id' => $r['inspector_id'], 'audit_id' => $r['p1']['audit_id']]) }}"
                                               class="rekap-link text-success"
                                               title="Buka detail audit ({{ $r['p1']['part_name'] }})">
                                                Sudah
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
                                               class="text-primary font-weight-bold"
                                               style="text-decoration: underline; font-size:0.65rem;"
                                               title="Lihat hasil audit Periode 1 ({{ $r['p1']['audit_date'] }} - {{ $r['p1']['part_name'] }})">
                                                View
                                            </a>
                                        </td>
                                    @else
                                        <td class="text-center text-danger font-weight-bold">Belum</td>
                                        <td class="text-center text-muted">-</td>
                                        @foreach($prinsipList as $p)
                                            <td class="text-center text-muted">-</td>
                                        @endforeach
                                        <td class="text-center text-muted">-</td>
                                        <td class="text-center text-muted no-print">-</td>
                                    @endif

                                    {{-- PERIODE 2 (JUL - DES) --}}
                                    @if($r['p2'])
                                        <td class="text-center">
                                            <a href="{{ route('checksheet.leveling_inspector.index', ['plant' => $plantCode, 'inspector_id' => $r['inspector_id'], 'audit_id' => $r['p2']['audit_id']]) }}"
                                               class="rekap-link text-success"
                                               title="Buka detail audit ({{ $r['p2']['part_name'] }})">
                                                Sudah
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
                                               class="text-primary font-weight-bold"
                                               style="text-decoration: underline; font-size:0.65rem;"
                                               title="Lihat hasil audit Periode 2 ({{ $r['p2']['audit_date'] }} - {{ $r['p2']['part_name'] }})">
                                                View
                                            </a>
                                        </td>
                                    @else
                                        <td class="text-center text-danger font-weight-bold">Belum</td>
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
                                                            title="Batal Approval">
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
