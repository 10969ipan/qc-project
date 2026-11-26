<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? strtolower($plantCode) : 'karawang';
    $isJkt           = in_array($headerPlantCode, ['jakarta', 'jkt']);
    $docHeader       = \App\Models\GeneralSetting::getDocHeader('leveling_inspector', $headerPlantCode, [
        'judul'      => 'DAFTAR TEMUAN NG & VERIFIKASI PERBAIKAN LEVELING INSPECTOR',
        'no_dokumen' => $isJkt ? 'QC-JKT-F-052' : 'PI-KRW-F-052',
        'tgl_terbit' => date('d/m/Y'),
        'revisi'     => '-',
        'halaman'    => '1 / 1',
    ]);
    $statusLabelMap = [
        'open'           => 'Open',
        'waiting_verify' => 'Waiting Verify',
        'closed'         => 'Closed',
    ];
    $filterStatusText = !empty($filters['status']) ? ($statusLabelMap[$filters['status']] ?? $filters['status']) : 'All Statuses';
    $filterDateText = (!empty($filters['date_from']) || !empty($filters['date_to']))
        ? trim(($filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : '...') . ' - ' . ($filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : '...'))
        : 'All Dates';
    $pageTitle = 'NG Findings Leveling Inspector - ' . strtoupper($headerPlantCode);
@endphp
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 7mm 5mm 7mm;
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
            padding: 4px 4px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2 !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.2px;
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

        .table tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .table tbody td {
            border: 1px solid #000 !important;
            padding: 3.5px 5px;
            vertical-align: middle;
            font-size: 7.8px;
            line-height: 1.25;
            color: #000 !important;
        }

        .text-ok { color: #15803d !important; font-weight: 700; }
        .text-ng { color: #b91c1c !important; font-weight: 700; }
        .text-warn { color: #b45309 !important; font-weight: 700; }
        .text-muted { color: #64748b !important; }

        .thumb-print {
            max-width: 95px;
            max-height: 65px;
            object-fit: contain;
            border: 1px solid #94a3b8;
            border-radius: 2px;
            display: block;
            margin: 0 auto;
        }

        .summary-row {
            page-break-before: avoid;
            break-before: avoid;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .summary-row td {
            background: #f2f2f2 !important;
            padding: 4px 8px !important;
            font-size: 8px !important;
        }

        .print-footer {
            margin-top: 2mm;
            font-size: 7.2px;
            color: #000 !important;
            text-align: left;
            page-break-before: avoid;
            break-before: avoid;
        }
    </style>
</head>

<body>
    <table class="table">
        <thead>
            <tr style="border:none !important; background:transparent !important;">
                <th colspan="10" class="print-header-cell">
                    <table class="header-table">
                        <tr>
                            <td style="width:72px; text-align:center;">
                                <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:56px; max-height:42px; object-fit:contain;">
                            </td>
                            <td style="text-align:center; font-weight:bold; font-size:10pt; text-transform:uppercase;">
                                DAFTAR TEMUAN NG &amp; VERIFIKASI PERBAIKAN LEVELING INSPECTOR
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
                            <td style="width:18%;"><strong>Plant:</strong> {{ strtoupper($headerPlantCode) }}</td>
                            <td style="width:24%;"><strong>Status Filter:</strong> {{ $filterStatusText }}</td>
                            <td style="width:30%;"><strong>Date Range:</strong> {{ $filterDateText }}</td>
                            <td style="width:28%;"><strong>Inspector:</strong> {{ $inspectorName ?: 'All Inspectors' }}</td>
                        </tr>
                    </table>
                </th>
            </tr>
            <tr>
                <th style="width:24px;">NO</th>
                <th style="width:52px;">DATE</th>
                <th style="width:105px;">INSPECTOR / SECTION</th>
                <th style="width:125px;">PART NAME / PART NO.</th>
                <th style="width:74px;">PRINCIPLE / LEVEL</th>
                <th>CHECK POINT</th>
                <th style="width:105px;">FINDING PHOTOS</th>
                <th>CORRECTIVE ACTION</th>
                <th style="width:105px;">CORRECTIVE PHOTOS</th>
                <th style="width:68px;">STATUS / VERIFICATION</th>
            </tr>
        </thead>
        <tbody>
            @if($findings->isEmpty())
                <tr>
                    <td colspan="10" style="text-align:center; padding:16px;" class="text-muted">
                        No NG findings match the selected filters.
                    </td>
                </tr>
            @else
                @foreach($findings as $idx => $r)
                    @php
                        $evUrls = array_map(fn($p) => \Illuminate\Support\Facades\Storage::url($p), $r->getEvidencePathsList());
                        $corrEvUrls = array_map(fn($p) => \Illuminate\Support\Facades\Storage::url($p), $r->getCorrectiveEvidencePathsList());
                    @endphp
                    <tr>
                        <td style="text-align:center; font-weight:700;">{{ $idx + 1 }}</td>
                        <td style="text-align:center; white-space:nowrap;">{{ $r->audit?->audit_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <strong>{{ $r->audit?->inspector?->name ?? '-' }}</strong>
                            <div class="text-muted" style="font-size:7px;">{{ $r->audit?->proses_line_label ?? '-' }}</div>
                        </td>
                        <td>
                            <strong>{{ $r->audit?->part_name ?? '-' }}</strong>
                            @if($r->audit?->part_number)
                                <div class="text-muted" style="font-size:7px; white-space:nowrap;">{{ $r->audit->part_number }}</div>
                            @endif
                        </td>
                        <td style="text-align:center; white-space:nowrap;">
                            <strong>{{ $r->prinsip }}</strong> / LEVEL {{ $r->level }}
                        </td>
                        <td>{{ $r->point_check }}</td>
                        <td style="text-align:center;">
                            @if(count($evUrls) > 0)
                                @if(!empty($r->evidence_note))
                                    <div style="font-size:6.8px; text-align:left; margin-bottom:3px; color:#111;"><strong>Note:</strong> {{ $r->evidence_note }}</div>
                                @endif
                                <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:3px;">
                                    @foreach($evUrls as $u)
                                        <img src="{{ $u }}" alt="Finding Photo" class="thumb-print" style="{{ count($evUrls) > 1 ? 'max-width:65px; max-height:48px;' : '' }}">
                                    @endforeach
                                </div>
                            @else
                                <span class="text-ng">None</span>
                            @endif
                        </td>
                        <td>
                            @if($r->why_1 || $r->why_2 || $r->why_3 || $r->root_cause || $r->corrective_action)
                                @if($r->why_1)<div class="lvl-guidance-content" style="font-size:7.2px;"><strong>Why 1:</strong> {!! $r->why_1 !!}</div>@endif
                                @if($r->why_2)<div class="lvl-guidance-content" style="font-size:7.2px;"><strong>Why 2:</strong> {!! $r->why_2 !!}</div>@endif
                                @if($r->why_3)<div class="lvl-guidance-content" style="font-size:7.2px;"><strong>Why 3:</strong> {!! $r->why_3 !!}</div>@endif
                                @if($r->root_cause)<div class="lvl-guidance-content" style="font-size:7.2px;"><strong>Root Cause:</strong> {!! $r->root_cause !!}</div>@endif
                                @if($r->corrective_action)<div class="lvl-guidance-content" style="font-size:7.2px;"><strong>Action:</strong> {!! $r->corrective_action !!}</div>@endif
                                @if($r->corrective_date)
                                    <div class="text-muted" style="font-size:6.8px;">Date: {{ $r->corrective_date->format('d/m/Y') }}</div>
                                @endif
                                @if($r->corrector)
                                    <div class="text-muted" style="font-size:6.8px;">By: {{ $r->corrector->name }}</div>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                            @if($r->reject_reason)
                                <div class="text-ng" style="font-size:7px;">Reject reason: {{ $r->reject_reason }}</div>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if(count($corrEvUrls) > 0)
                                <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:3px;">
                                    @foreach($corrEvUrls as $u)
                                        <img src="{{ $u }}" alt="Corrective Photo" class="thumb-print" style="{{ count($corrEvUrls) > 1 ? 'max-width:65px; max-height:48px;' : '' }}">
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($r->ng_status === 'closed')
                                <div class="text-ok">Closed</div>
                                <div class="text-muted" style="font-size:6.8px;">{{ $r->verifier?->name ?? '-' }}</div>
                                @if($r->verified_at)
                                    <div class="text-muted" style="font-size:6.5px;">{{ $r->verified_at->format('d/m/Y') }}</div>
                                @endif
                            @elseif($r->ng_status === 'waiting_verify')
                                <div class="text-warn">Waiting Verify</div>
                                <div class="text-muted" style="font-size:6.8px;">{{ $r->audit?->auditor_name ?? '-' }}</div>
                            @else
                                <div class="text-ng">Open</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
            <tr class="summary-row">
                <td colspan="6" style="text-align:left;">
                    Total Findings: <strong>{{ $summary['total'] }}</strong> &nbsp;|&nbsp;
                    Open: <span class="text-ng">{{ $summary['open'] }}</span> &nbsp;|&nbsp;
                    Waiting Verify: <span class="text-warn">{{ $summary['waiting_verify'] }}</span> &nbsp;|&nbsp;
                    Closed: <span class="text-ok">{{ $summary['closed'] }}</span>
                </td>
                <td colspan="4" style="text-align:right;">
                    Plant: <strong>{{ strtoupper($headerPlantCode) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="print-footer" id="printFooter">
        <span id="footerDateTime"></span>
    </div>

    <script src="{{ asset('js/checksheet/leveling-print.js') }}?v={{ time() }}"></script>
</body>

</html>
