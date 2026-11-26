<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? strtolower($plantCode) : 'karawang';
    $isJkt           = in_array($headerPlantCode, ['jakarta', 'jkt']);
    $docHeader       = \App\Models\GeneralSetting::getDocHeader('leveling_inspector', $headerPlantCode, [
        'judul'      => 'REKAP & MONITORING LEVELING INSPECTOR TAHUN ' . $year,
        'no_dokumen' => $isJkt ? 'QC-JKT-F-052' : 'PI-KRW-F-052',
        'tgl_terbit' => date('d/m/Y'),
        'revisi'     => '-',
        'halaman'    => '1 / 1',
    ]);
    $pageTitle = 'Rekap Leveling Inspector Tahunan - ' . strtoupper($headerPlantCode) . ' - ' . $year;
@endphp
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 6mm 8mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000 !important;
            background: #fff !important;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            color: #000 !important;
        }

        .header-table td,
        .header-table th {
            border: 1px solid #000 !important;
            padding: 3px 4px;
            vertical-align: middle;
            color: #000 !important;
        }

        .sub-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 8px;
            color: #000 !important;
        }

        .sub-header td {
            border: 1px solid #000 !important;
            padding: 3px 6px;
            color: #000 !important;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            color: #000 !important;
        }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        .table th {
            border: 1px solid #000 !important;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2 !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
            color: #000 !important;
        }

        .table th.print-header-cell {
            border: none !important;
            background: transparent !important;
            padding: 0 0 4px 0 !important;
            font-weight: normal !important;
            text-transform: none !important;
            text-align: left !important;
        }

        .table tbody td {
            border: 1px solid #000 !important;
            padding: 3px 4px;
            vertical-align: middle;
            font-size: 7.5px;
            color: #000 !important;
        }

        .th-prinsip-msk { color: #1e40af !important; }
        .th-prinsip-kt { color: #166534 !important; }
        .th-prinsip-png { color: #92400e !important; }
        .th-prinsip-pssp { color: #5b21b6 !important; }
        .th-prinsip-kao { color: #9d174d !important; }

        .text-ok { color: #15803d !important; font-weight: 700; }
        .text-ng { color: #b91c1c !important; font-weight: 700; }
        .text-pri { color: #1d4ed8 !important; font-weight: 700; }
        .text-muted { color: #64748b !important; }
        .rekap-row-ng td { background-color: #fee2e2 !important; }

        .print-footer {
            margin-top: 5mm;
            font-size: 7.5px;
            color: #000 !important;
            text-align: left;
        }
    </style>
</head>

<body>
    <table class="table">
        <thead>
            <tr style="border:none !important; background:transparent !important;">
                <th colspan="25" class="print-header-cell">
                    <table class="header-table">
                        <tr>
                            <td style="width:72px; text-align:center;">
                                <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:56px; max-height:42px; object-fit:contain;">
                            </td>
                            <td style="text-align:center; font-weight:bold; font-size:10.5pt; text-transform:uppercase;">
                                REKAP &amp; MONITORING LEVELING INSPECTOR TAHUN {{ $year }}
                            </td>
                            <td style="width:1px; padding:0 !important; vertical-align:top; white-space:nowrap;">
                                <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                                    <tr>
                                        <td style="border:none !important; padding:3px 5px 3px 3px; vertical-align:top; white-space:nowrap;">
                                            <table style="border-collapse:collapse; font-size:7.5pt; line-height:1.2; width:100%;">
                                                <tr>
                                                    <td style="font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                                    <td style="text-align:center;">:</td>
                                                    <td style="font-weight:700; white-space:nowrap;">{{ $docHeader['no_dokumen'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                                    <td style="text-align:center;">:</td>
                                                    <td style="white-space:nowrap;">{{ $docHeader['tgl_terbit'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                                    <td style="text-align:center;">:</td>
                                                    <td style="white-space:nowrap;">{{ $docHeader['revisi'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:600; white-space:nowrap;">Halaman</td>
                                                    <td style="text-align:center;">:</td>
                                                    <td style="white-space:nowrap;">{{ $docHeader['halaman'] ?? '1 / 1' }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="border:none !important; padding:3px 3px 3px 0; vertical-align:top;">
                                            <table style="border-collapse:collapse; text-align:center; font-size:7pt; line-height:1.1; table-layout:fixed; width:295px;">
                                                <tr>
                                                    <th style="width:22px; background:#fff !important; font-size:6.5pt;">Tgl.</th>
                                                    <th style="width:91px; background:#fff !important; font-size:6.5pt;">Prepared By</th>
                                                    <th style="width:91px; background:#fff !important; font-size:6.5pt;">Checked By</th>
                                                    <th style="width:91px; background:#fff !important; font-size:6.5pt;">Approved By</th>
                                                </tr>
                                                <tr>
                                                    <td rowspan="3" style="padding:2px;">
                                                        <div style="writing-mode:vertical-rl; transform:rotate(180deg); margin:0 auto; font-size:6.5pt; color:#495057;">{{ date('d-M-y') }}</div>
                                                    </td>
                                                    <td style="height:40px; padding:2px;">
                                                        @if($isJkt)
                                                            <img src="{{ asset('signatures/suli.png') }}" alt="Masuli" style="max-height:44px; max-width:82px; object-fit:contain; mix-blend-mode:multiply;">
                                                        @else
                                                            <img src="{{ asset('signatures/arif.png') }}" alt="Arief H" style="max-height:44px; max-width:82px; object-fit:contain; mix-blend-mode:multiply;">
                                                        @endif
                                                    </td>
                                                    <td style="height:40px; padding:2px;">
                                                        <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:44px; max-width:82px; object-fit:contain; mix-blend-mode:multiply;">
                                                    </td>
                                                    <td style="height:40px; padding:2px;">
                                                        <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:44px; max-width:82px; object-fit:contain; mix-blend-mode:multiply;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:700; padding:1px 3px;">{{ $isJkt ? 'Masuli' : 'Arief H' }}</td>
                                                    <td style="font-weight:700; padding:1px 3px;">Iwan S</td>
                                                    <td style="font-weight:700; padding:1px 3px;">Desti K</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:1px 3px;">Spv. QC</td>
                                                    <td style="padding:1px 3px;">Asst. Mgr Quality</td>
                                                    <td style="padding:1px 3px;">Mgr. Quality</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <table class="sub-header">
                        <tr>
                            <td style="white-space:nowrap;"><strong>Plant:</strong> {{ strtoupper($headerPlantCode) }}</td>
                            <td style="white-space:nowrap;"><strong>Section:</strong> {{ $bagianFilter ?: 'All Sections' }}</td>
                            <td style="white-space:nowrap;"><strong>Period 1:</strong> {{ $summary['p1_done'] }}/{{ $summary['total_inspectors'] }} ({{ $summary['p1_pct'] }}%)</td>
                            <td style="white-space:nowrap;"><strong>Period 2:</strong> {{ $summary['p2_done'] }}/{{ $summary['total_inspectors'] }} ({{ $summary['p2_pct'] }}%)</td>
                            <td style="white-space:nowrap;"><strong>NG Closed:</strong> {{ $summary['total_closed_ng'] }}/{{ $summary['total_ng'] }} ({{ $summary['total_ng'] > 0 ? $summary['ng_closure_pct'] . '%' : '-' }})</td>
                            <td style="white-space:nowrap;"><strong>Avg. Level:</strong> {{ $summary['avg_level_overall'] !== null ? number_format($summary['avg_level_overall'], 2) . '/3' : '-' }}</td>
                            <td style="white-space:nowrap;"><strong>Result OK:</strong> {{ $summary['result_ok_count'] }}/{{ $summary['audited_count'] }} ({{ $summary['result_ok_pct'] }}%)</td>
                            <td style="white-space:nowrap;"><strong>Approval:</strong> {{ $summary['approved_stages_count'] }}/{{ $summary['total_stages_count'] }} ({{ $summary['approval_stage_pct'] }}%)</td>
                        </tr>
                    </table>
                </th>
            </tr>
            <tr>
                <th rowspan="2" style="width:26px;">NO</th>
                <th rowspan="2">INSPECTOR NAME</th>
                <th rowspan="2">SECTION</th>
                <th rowspan="2" style="width:50px;">TABLE / MACHINE</th>
                <th colspan="8">PERIOD 1 (JAN – JUN {{ $year }})</th>
                <th colspan="8">PERIOD 2 (JUL – DEC {{ $year }})</th>
                <th rowspan="2" style="width:45px;">NG OPEN</th>
                <th colspan="4">APPROVAL</th>
            </tr>
            <tr>
                <th style="width:42px;">STATUS</th>
                <th style="width:55px;">AUDIT DATE</th>
                @foreach($prinsipList as $p)
                    <th class="th-prinsip-{{ strtolower($p) }}" style="width:28px;">{{ $p }}</th>
                @endforeach
                <th style="width:42px; line-height:1.15;">AVG.<br>LEVEL</th>

                <th style="width:42px;">STATUS</th>
                <th style="width:55px;">AUDIT DATE</th>
                @foreach($prinsipList as $p)
                    <th class="th-prinsip-{{ strtolower($p) }}" style="width:28px;">{{ $p }}</th>
                @endforeach
                <th style="width:42px; line-height:1.15;">AVG.<br>LEVEL</th>

                <th style="width:58px;">AUDITOR</th>
                <th style="width:65px;">KARU/KASHIFT</th>
                <th style="width:58px;">SPV</th>
                <th style="width:62px;">ASST. MGR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $idx => $r)
                <tr class="{{ !empty($r['has_ng']) ? 'rekap-row-ng' : '' }}">
                    <td style="text-align:center; font-weight:700;">{{ $idx + 1 }}</td>
                    <td style="font-weight:700;">{{ $r['inspector_name'] }}</td>
                    <td>{{ $r['bagian'] }}</td>
                    <td style="text-align:center; white-space:nowrap;">{{ $r['meja_mesin'] ?? '-' }}</td>

                    @if($r['p1'])
                        <td style="text-align:center;" class="text-ok">Done</td>
                        <td style="text-align:center; white-space:nowrap;">{{ $r['p1']['audit_date'] }}</td>
                        @foreach($prinsipList as $p)
                            @php $lv = $r['p1']['prinsip_levels'][$p] ?? '-'; @endphp
                            <td style="text-align:center; font-weight:700;" class="{{ $lv === '3' ? 'text-ok' : ($lv === '2' ? 'text-pri' : ($lv === '0' ? 'text-ng' : '')) }}">{{ $lv }}</td>
                        @endforeach
                        <td style="text-align:center;" class="text-pri">{{ $r['p1']['score'] !== null ? $r['p1']['score'] : '-' }}</td>
                    @else
                        <td style="text-align:center;" class="text-ng">Pending</td>
                        <td style="text-align:center;" class="text-muted">-</td>
                        @foreach($prinsipList as $p)
                            <td style="text-align:center;" class="text-muted">-</td>
                        @endforeach
                        <td style="text-align:center;" class="text-muted">-</td>
                    @endif

                    @if($r['p2'])
                        <td style="text-align:center;" class="text-ok">Done</td>
                        <td style="text-align:center; white-space:nowrap;">{{ $r['p2']['audit_date'] }}</td>
                        @foreach($prinsipList as $p)
                            @php $lv = $r['p2']['prinsip_levels'][$p] ?? '-'; @endphp
                            <td style="text-align:center; font-weight:700;" class="{{ $lv === '3' ? 'text-ok' : ($lv === '2' ? 'text-pri' : ($lv === '0' ? 'text-ng' : '')) }}">{{ $lv }}</td>
                        @endforeach
                        <td style="text-align:center;" class="text-pri">{{ $r['p2']['score'] !== null ? $r['p2']['score'] : '-' }}</td>
                    @else
                        <td style="text-align:center;" class="text-ng">Pending</td>
                        <td style="text-align:center;" class="text-muted">-</td>
                        @foreach($prinsipList as $p)
                            <td style="text-align:center;" class="text-muted">-</td>
                        @endforeach
                        <td style="text-align:center;" class="text-muted">-</td>
                    @endif

                    <td style="text-align:center;" class="{{ $r['open_ng_count'] > 0 ? 'text-ng' : 'text-ok' }}">{{ $r['open_ng_count'] }}</td>
                    @foreach($r['approval'] as $st)
                        <td style="text-align:center; font-size:6.3px; line-height:1.25;">
                            @if(!$r['has_audit'])
                                <span class="text-muted">-</span>
                            @elseif($st['approved'])
                                <div class="text-ok">Approved</div>
                                <div>{{ $st['approver_name'] }}</div>
                                @if($st['approved_at'])
                                    <div class="text-muted">{{ $st['approved_at'] }}</div>
                                @endif
                            @else
                                <div class="text-muted">Pending</div>
                                @if(!empty($st['pending_name']))
                                    <div>{{ $st['pending_name'] }}</div>
                                @endif
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="print-footer" id="printFooter">
        <span id="footerDateTime"></span>
    </div>

    <script src="{{ asset('js/checksheet/leveling-print.js') }}?v={{ time() }}"></script>
</body>

</html>
