<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? strtolower($plantCode) : 'karawang';
    $isJkt           = in_array($headerPlantCode, ['jakarta', 'jkt']);
    $docHeader       = \App\Models\GeneralSetting::getDocHeader('leveling_inspector', $headerPlantCode, [
        'judul'      => 'CHECKSHEET LEVELING INSPECTOR',
        'no_dokumen' => $isJkt ? 'QC-JKT-F-052' : 'PI-KRW-F-052',
        'tgl_terbit' => date('d/m/Y'),
        'revisi'     => '-',
        'halaman'    => '1 / 1',
    ]);
    $pageTitle = 'Checksheet Leveling Inspector - ' . ($audit->inspector->name ?? '-') . ' - ' . $audit->audit_date->format('d-m-Y');
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
            font-size: 8.5px;
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
            margin-top: 0;
            color: #000 !important;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        .table th {
            border: 1px solid #000 !important;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2 !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
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
            padding: 3px 5px;
            vertical-align: middle;
            font-size: 7.8px;
            line-height: 1.25;
            color: #000 !important;
        }

        .prinsip-cell {
            text-align: center !important;
            vertical-align: middle !important;
            font-weight: 700;
            font-size: 8.5px;
            letter-spacing: 0.3px;
            background: #f2f2f2;
            width: 40px;
            white-space: nowrap;
        }
        .prinsip-cell.lvl-prinsip-msk { background: #dbeafe; color: #1e40af !important; }
        .prinsip-cell.lvl-prinsip-kt { background: #dcfce7; color: #166534 !important; }
        .prinsip-cell.lvl-prinsip-png { background: #fef3c7; color: #92400e !important; }
        .prinsip-cell.lvl-prinsip-pssp { background: #ede9fe; color: #5b21b6 !important; }
        .prinsip-cell.lvl-prinsip-kao { background: #fce7f3; color: #9d174d !important; }

        .prinsip-vertical {
            display: inline-block;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            margin: 0 auto;
            white-space: nowrap;
            vertical-align: middle;
        }

        /* Visual merge tanpa rowspan (bebas bug overflow rowspan lintas halaman di Chrome Print) */
        .table tbody td.vm-top {
            border-bottom: none !important;
        }
        .table tbody td.vm-mid {
            border-top: none !important;
            border-bottom: none !important;
        }
        .table tbody td.vm-bot {
            border-top: none !important;
        }

        /* Penutup border bawah otomatis di setiap halaman cetak */
        .table tfoot.print-page-closer td {
            border: none !important;
            border-top: 1px solid #000 !important;
            padding: 0 !important;
            height: 0 !important;
            line-height: 0 !important;
            font-size: 0 !important;
            background: transparent !important;
        }

        .text-ok { color: #15803d !important; font-weight: 700; }
        .text-pri { color: #1d4ed8 !important; font-weight: 700; }
        .text-ng { color: #b91c1c !important; font-weight: 700; }
        .text-warn { color: #b45309 !important; font-weight: 700; }
        .text-muted { color: #64748b !important; }
        .row-ng { background: #fff5f5 !important; }

        .thumb-print {
            max-width: 155px;
            max-height: 95px;
            object-fit: contain;
            border: 1px solid #94a3b8;
            border-radius: 2px;
            display: block;
            margin: 0 auto;
        }

        .lvl-guidance-content,
        .lvl-guidance-content * {
            font-size: 8px !important;
            line-height: 1.3 !important;
        }
        .lvl-guidance-content p { margin: 0 0 3px 0; }
        .lvl-guidance-content p:last-child,
        .lvl-guidance-content ol:last-child,
        .lvl-guidance-content ul:last-child { margin-bottom: 0; }
        .lvl-guidance-content ol,
        .lvl-guidance-content ul { margin: 2px 0 3px 0; padding-left: 14px; }
        .lvl-guidance-content li { margin-bottom: 1px; }

        .summary-row {
            page-break-before: avoid;
            break-before: avoid;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .summary-row td {
            background: #f2f2f2 !important;
            padding: 4px 8px !important;
            font-size: 8.2px !important;
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
            {{-- Header Dokumen ISO Berulang di Setiap Halaman Cetak --}}
            <tr style="border:none !important; background:transparent !important;">
                <th colspan="7" class="print-header-cell">
                    <table class="header-table">
                        <tr>
                            <td style="width:72px; text-align:center;">
                                <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:56px; max-height:42px; object-fit:contain;">
                            </td>
                            <td style="text-align:center; font-weight:bold; font-size:10.5pt; text-transform:uppercase;">
                                {{ $docHeader['judul'] ?? 'CHECKSHEET LEVELING INSPECTOR' }}
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
                                                    <th style="width:91px; background:#fff !important; font-size:6.5pt;">Dibuat</th>
                                                    <th style="width:91px; background:#fff !important; font-size:6.5pt;">Diperiksa</th>
                                                    <th style="width:91px; background:#fff !important; font-size:6.5pt;">Diketahui</th>
                                                </tr>
                                                <tr>
                                                    <td rowspan="3" style="padding:2px;">
                                                        <div class="prinsip-vertical" style="font-size:6.5pt; color:#495057;">{{ $audit->audit_date->format('d-M-y') }}</div>
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
                            <td style="width:14%;"><strong>Tanggal:</strong> {{ $audit->audit_date->format('d/m/Y') }}</td>
                            <td style="width:24%;"><strong>Proses/Line/Leader:</strong> {{ $audit->bagian ?: '-' }}</td>
                            <td style="width:30%;"><strong>Nama Part / No. Part:</strong> {{ $audit->part_name }}{{ $audit->part_number ? ' / ' . $audit->part_number : '' }}</td>
                            <td style="width:16%;"><strong>Inspector:</strong> {{ $audit->inspector->name ?? '-' }}</td>
                            <td style="width:16%;"><strong>Auditor:</strong> {{ $audit->auditor_name }}</td>
                        </tr>
                    </table>
                </th>
            </tr>
            <tr>
                <th rowspan="2" style="width:36px;">PRINSIP</th>
                <th rowspan="2" style="width:32px;">LEVEL</th>
                <th rowspan="2" style="width:23%;">POINT CHECK</th>
                <th rowspan="2" style="width:31%;">GUIDANCE PENILAIAN</th>
                <th colspan="2">RESULT</th>
                <th rowspan="2" style="width:185px;">EVIDENCE</th>
            </tr>
            <tr>
                <th style="width:46px;">OK / NG</th>
                <th style="width:36px;">LEVEL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prinsipGroups as $pGroup)
                @php
                    $pTotal = (int) $pGroup['rowspan'];
                    $pIdx = 0;
                    $lvlVal = $pGroup['achieved_level'];
                    $lvlColor = $lvlVal === '3' ? 'text-ok' : ($lvlVal === '2' ? 'text-pri' : ($lvlVal === '0' ? 'text-ng' : ($lvlVal === '-' ? 'text-muted' : '')));
                @endphp
                @foreach($pGroup['levels'] as $lGroup)
                    @php
                        $lTotal = (int) $lGroup['rowspan'];
                        $lIdx = 0;
                    @endphp
                    @foreach($lGroup['rows'] as $row)
                        @php
                            $item = $row['item'];
                            $res = $row['result'];
                            $val = $res ? $res->result : null;
                            $evUrls = $res ? array_map(fn($p) => \Illuminate\Support\Facades\Storage::url($p), $res->getEvidencePathsList()) : [];

                            $pVmClass = $pTotal <= 1 ? '' : ($pIdx === 0 ? 'vm-top' : ($pIdx === $pTotal - 1 ? 'vm-bot' : 'vm-mid'));
                            $lVmClass = $lTotal <= 1 ? '' : ($lIdx === 0 ? 'vm-top' : ($lIdx === $lTotal - 1 ? 'vm-bot' : 'vm-mid'));
                            $showPrinsipLabel = ($pIdx === intdiv($pTotal - 1, 2));
                            $showLevelLabel = ($lIdx === intdiv($lTotal - 1, 2));
                        @endphp
                        <tr class="{{ $val === 'NG' ? 'row-ng' : '' }}">
                            <td class="prinsip-cell lvl-prinsip-{{ strtolower($pGroup['prinsip']) }} {{ $pVmClass }}">
                                @if($showPrinsipLabel)
                                    <div class="prinsip-vertical">{{ $pGroup['prinsip'] }}</div>
                                @endif
                            </td>

                            <td class="{{ $lVmClass }}" style="text-align:center; font-weight:700;">
                                @if($showLevelLabel)
                                    {{ $lGroup['level'] }}
                                @endif
                            </td>

                            <td><strong>{{ $row['row_num'] }}.</strong> {{ $item->point_check }}</td>
                            <td style="vertical-align:top;"><div class="lvl-guidance-content">{!! $row['guidance_html'] !!}</div></td>
                            <td style="text-align:center;">
                                @if($val === 'OK')
                                    <span class="text-ok">OK</span>
                                @elseif($val === 'NG')
                                    <span class="text-ng">NG</span>
                                    @if($res->ng_status === 'closed')
                                        <div class="text-ok" style="font-size:7px;">Closed</div>
                                    @elseif($res->ng_status === 'waiting_verify')
                                        <div class="text-warn" style="font-size:7px;">Waiting Verify</div>
                                    @else
                                        <div class="text-ng" style="font-size:7px;">Open</div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="{{ $lvlColor }} {{ $pVmClass }}" style="text-align:center; font-weight:800; font-size:11px; background:#f8fafc;">
                                @if($showPrinsipLabel)
                                    {{ $lvlVal }}
                                @endif
                            </td>

                            <td style="text-align:center;">
                                @if(count($evUrls) > 0)
                                    @if(!empty($res?->evidence_note))
                                        <div style="font-size:7px; text-align:left; margin-bottom:3px; color:#111;"><strong>Ket:</strong> {{ $res->evidence_note }}</div>
                                    @endif
                                    <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:3px;">
                                        @foreach($evUrls as $eUrl)
                                            <img src="{{ $eUrl }}" alt="Evidence" class="thumb-print" style="{{ count($evUrls) > 1 ? 'max-width:85px; max-height:60px;' : '' }}">
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @php
                            $pIdx++;
                            $lIdx++;
                        @endphp
                    @endforeach
                @endforeach
            @endforeach
            <tr class="summary-row">
                <td colspan="4" style="text-align:left;">
                    Total OK: <span class="text-ok">{{ $summary['total_ok'] }}</span> &nbsp;|&nbsp;
                    Total NG: <span class="text-ng">{{ $summary['total_ng'] }}</span> &nbsp;|&nbsp;
                    Belum Diisi: <span class="text-muted">{{ $summary['total_empty'] }}</span>
                </td>
                <td colspan="3" style="text-align:right;">
                    Rata-rata Level: <strong>{{ $summary['score'] !== null ? $summary['score'] : '-' }}</strong>
                </td>
            </tr>
        </tbody>
        <tfoot class="print-page-closer">
            <tr>
                <td colspan="7"></td>
            </tr>
        </tfoot>
    </table>

    <div class="print-footer" id="printFooter">
        <span id="footerDateTime"></span>
    </div>

    <script src="{{ asset('js/checksheet/leveling-print.js') }}?v={{ time() }}"></script>
</body>

</html>
