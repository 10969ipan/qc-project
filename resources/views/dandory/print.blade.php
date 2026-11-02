<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? strtolower($plantCode) : 'karawang';
    $isJkt           = in_array($headerPlantCode, ['jakarta', 'jkt']);
    $docHeader       = \App\Models\GeneralSetting::getDocHeader('checksheet_dandory', $headerPlantCode, [
        'judul'      => 'CHECK SHEET DANDORY QUALITY',
        'no_dokumen' => $isJkt ? 'QC-JKT-F-0094' : 'QC-KRW-F-0094',
        'tgl_terbit' => '17/10/2025',
        'revisi'     => '-',
        'halaman'    => '1/1'
    ]);

    $formattedDate = isset($date) && $date ? \Carbon\Carbon::parse($date)->translatedFormat('d F Y') : ($monthNames[$month] . ' ' . $year);
    $pageTitle     = 'Checksheet Dandory Quality - ' . ($selectedOperator->name ?? 'Semua Operator') . ' - ' . $formattedDate;
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
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000 !important;
            background: #fff !important;
            margin: 0;
            padding: 0;
        }

        /* ===== HEADER DOKUMEN ISO ===== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            color: #000 !important;
        }

        .header-table td {
            border: 1px solid #000 !important;
            padding: 4px;
            vertical-align: middle;
            color: #000 !important;
        }

        .logo {
            width: 75px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            color: #000 !important;
            text-transform: uppercase;
        }

        /* ===== SUB-HEADER INFO ===== */
        .sub-header {
            margin-bottom: 6px;
            font-size: 8.5px;
            color: #000 !important;
        }

        /* ===== TABEL DATA UTAMA ===== */
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
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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

        .table td {
            border: 1px solid #000 !important;
            padding: 2.5px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            color: #000 !important;
        }

        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Simbol Penilaian */
        .sym-ok {
            color: #047857;
            font-weight: bold;
            font-size: 8.5px;
        }

        .sym-ng {
            color: #b91c1c;
            font-weight: bold;
            font-size: 8.5px;
        }

        .sym-na {
            color: #475569;
            font-weight: normal;
            font-size: 8.5px;
        }

        /* Tabel Referensi Parameter Standar ISO di Bawah */
        .std-ref-box {
            margin-top: 8px;
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 6.8px;
            background-color: #fff;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .std-ref-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .std-ref-box th {
            background-color: #f2f2f2 !important;
            border: 1px solid #000 !important;
            padding: 2px 4px;
            font-weight: bold;
            font-size: 6.8px;
            text-align: left;
            text-transform: uppercase;
        }

        .std-ref-box td {
            border: 1px solid #ccc !important;
            padding: 1.5px 4px;
            font-size: 6.5px;
            vertical-align: top;
        }

        /* Footer Timestamp */
        .print-footer {
            margin-top: 6mm;
            font-size: 7.5px;
            color: #000 !important;
            text-align: left;
        }
    </style>
</head>

<body>

    {{-- Tabel Data dengan Header yang Berulang di Setiap Halaman Cetak --}}
    <table class="table">
        <thead>
            {{-- Header Dokumen Berulang pada Setiap Halaman Cetak --}}
            <tr style="border:none !important; background:transparent !important;">
                <th colspan="100" class="print-header-cell">

                    {{-- Header: Logo | Judul | Info Dokumen + Signatures --}}
                    <table class="header-table" style="width:100%; border-collapse:collapse; border:1px solid #000; margin-bottom:6px;">
                        <tr>
                            <td class="logo" style="width:75px; border:1px solid #000; padding:5px; text-align:center; vertical-align:middle;">
                                <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                            </td>
                            <td class="title" style="border:1px solid #000; padding:5px 8px; text-align:center; vertical-align:middle; font-weight:bold; font-size:10pt; color:#000; text-transform:uppercase;">
                                {{ $docHeader['judul'] ?? 'CHECK SHEET DANDORY QUALITY' }}
                            </td>
                            <td style="width:1px; border:1px solid #000; padding:0 !important; vertical-align:top; white-space:nowrap;">
                                <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                                    <tr style="height:100%;">

                                        {{-- ===== [3A] Tabel No. Dokumen ===== --}}
                                        <td style="border:none; padding:4px 6px 4px 4px; vertical-align:top; height:100%; white-space:nowrap;">
                                            <table style="border-collapse:collapse; border:1px solid #000; font-size:7.5pt; line-height:1.2; background:#fff; height:100%; width:100%;">
                                                <tr>
                                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:700; color:#212529; white-space:nowrap;">{{ $docHeader['no_dokumen'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['tgl_terbit'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['revisi'] ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['halaman'] ?? '1/1' }}</td>
                                                </tr>
                                            </table>
                                        </td>

                                        {{-- ===== [3B] Tabel Signatures ===== --}}
                                        <td style="border:none; padding:4px 4px 4px 0; vertical-align:top; height:100%;">
                                            <table style="border-collapse:collapse; border:1px solid #000; text-align:center; font-size:7pt; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:322px;">
                                                <thead>
                                                    <tr>
                                                        <th style="border:1px solid #000; padding:2px 2px; font-weight:600; color:#495057; background:#fff; width:22px;">Tgl.</th>
                                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:100px;">Dibuat</th>
                                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:100px;">Diperiksa</th>
                                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:100px;">Diketahui</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td rowspan="3" style="border:1px solid #000; padding:2px; vertical-align:middle; text-align:center; width:22px;">
                                                            <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:6.5pt; font-weight:400; margin:0 auto; color:#6c757d;">
                                                                {{ date('d-M-y') }}
                                                            </div>
                                                        </td>
                                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                                            @if(in_array(strtolower($headerPlantCode ?? $plantCode ?? 'karawang'), ['jakarta', 'jkt']))
                                                                <img src="{{ asset('signatures/suli.png') }}" alt="Masuli" style="max-height:58px; max-width:110px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                            @else
                                                                <img src="{{ asset('signatures/arif.png') }}" alt="Arief H" style="max-height:58px; max-width:110px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                            @endif
                                                        </td>
                                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                                            <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:58px; max-width:110px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        </td>
                                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                                            <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:58px; max-width:110px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border:1px solid #000; padding:1px 5px; font-weight:600; font-size:6.5pt; color:#212529; white-space:nowrap;">{{ in_array(strtolower($headerPlantCode ?? $plantCode ?? 'karawang'), ['jakarta', 'jkt']) ? 'Masuli' : 'Arief H' }}</td>
                                                        <td style="border:1px solid #000; padding:1px 5px; font-weight:600; font-size:6.5pt; color:#212529; white-space:nowrap;">Iwan S</td>
                                                        <td style="border:1px solid #000; padding:1px 5px; font-weight:600; font-size:6.5pt; color:#212529; white-space:nowrap;">Desti K</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border:1px solid #000; padding:1px 5px; font-size:6.5pt; color:#495057; white-space:nowrap;">Spv. QC</td>
                                                        <td style="border:1px solid #000; padding:1px 5px; font-size:6.5pt; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                                        <td style="border:1px solid #000; padding:1px 5px; font-size:6.5pt; color:#495057; white-space:nowrap;">Mgr. Quality</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>

                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    {{-- Sub-header: Identitas Pemeriksaan & Keterangan Simbol --}}
                    <div class="sub-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:8pt;">
                        <div>
                            <strong>Nama Operator:</strong> {{ $selectedOperator->name }}
                            &nbsp;&nbsp;|&nbsp;&nbsp;<strong>Bagian:</strong> {{ $opConfig->bagian ?: 'Quality Control' }}
                            &nbsp;&nbsp;|&nbsp;&nbsp;<strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                            @if(!empty($shiftFilter))
                                &nbsp;&nbsp;|&nbsp;&nbsp;<strong>Shift:</strong> {{ $shiftFilter }}
                            @endif
                        </div>
                        <div>
                            <strong>Keterangan:</strong>
                            <span class="sym-ok">✓</span> Dilakukan &nbsp;&nbsp;|&nbsp;&nbsp;
                            <span class="sym-ng">✕</span> Tidak dilakukan &nbsp;&nbsp;|&nbsp;&nbsp;
                            <span class="sym-na">-</span> Item tidak ada
                        </div>
                    </div>

                </th>
            </tr>

            {{-- Kolom Data Transaksi (Header Tingkat 1 & Tingkat 2) --}}
            <tr>
                <th rowspan="2" style="width:25px;">NO</th>
                <th rowspan="2" style="width:55px;">TANGGAL</th>
                <th rowspan="2" style="width:45px;">SHIFT</th>
                <th rowspan="2" style="width:45px;">MEJA</th>
                <th rowspan="2" style="min-width:160px; text-align:left; padding-left:5px;">NAMA PART</th>
                <th colspan="{{ count($awalItems) ?: 1 }}">AWAL DANDORY</th>
                <th colspan="{{ count($akhirItems) ?: 1 }}">AKHIR DANDORY</th>
                <th rowspan="2" style="width:80px;">DIPERIKSA<br>(LEADER)</th>
                <th rowspan="2" style="min-width:90px; text-align:left; padding-left:5px;">CATATAN</th>
            </tr>
            <tr>
                @foreach($awalItems as $it)
                    <th style="width:18px;" title="Awal #{{ $it->order_no }}: {{ $it->item_check }} - {{ $it->standard }}">
                        {{ $it->order_no }}
                    </th>
                @endforeach
                @foreach($akhirItems as $it)
                    <th style="width:18px;" title="Akhir #{{ $it->order_no }}: {{ $it->item_check }} - {{ $it->standard }}">
                        {{ $it->order_no }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @php
                $rows = isset($checksheets) ? $checksheets : (isset($chunks) ? collect($chunks)->flatten(1) : collect([]));
            @endphp

            @forelse($rows as $index => $cs)
                @php
                    $awalMap  = (array) ($cs->awal_checks ?? []);
                    $akhirMap = (array) ($cs->akhir_checks ?? []);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($cs->check_date)->format('d/m/y') }}
                        @if($cs->created_at)
                            <div style="font-size: 6.5px; color: #666; font-weight: normal; line-height: 1.1; margin-top: 1px;">
                                {{ \Carbon\Carbon::parse($cs->created_at)->format('H:i') }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $cs->shift }}</td>
                    <td>{{ $cs->table_no }}</td>
                    <td style="text-align:left; padding-left:5px; font-weight:600;">
                        <div>{{ $cs->part_name }}</div>
                        @if($cs->item && ($cs->item->part_number || $cs->item->sap_code))
                            <div style="font-size: 6.5px; font-weight: normal; color: #555; line-height: 1.1; margin-top: 1px;">
                                @if($cs->item->part_number) PN: {{ $cs->item->part_number }} @endif
                                @if($cs->item->part_number && $cs->item->sap_code) | @endif
                                @if($cs->item->sap_code) SAP: {{ $cs->item->sap_code }} @endif
                            </div>
                        @endif
                    </td>

                    {{-- 9 Sel Awal Dandory --}}
                    @foreach($awalItems as $it)
                        @php
                            $val = $awalMap[(string)$it->order_no] ?? null;
                        @endphp
                        <td>
                            @if($val === 'OK') <span class="sym-ok">✓</span>
                            @elseif($val === 'NG') <span class="sym-ng">✕</span>
                            @elseif($val === 'NA') <span class="sym-na">-</span>
                            @endif
                        </td>
                    @endforeach

                    {{-- 10 Sel Akhir Dandory --}}
                    @foreach($akhirItems as $it)
                        @php
                            $val = $akhirMap[(string)$it->order_no] ?? null;
                        @endphp
                        <td>
                            @if($val === 'OK') <span class="sym-ok">✓</span>
                            @elseif($val === 'NG') <span class="sym-ng">✕</span>
                            @elseif($val === 'NA') <span class="sym-na">-</span>
                            @endif
                        </td>
                    @endforeach

                    {{-- Diperiksa Leader --}}
                    <td style="font-size:6pt; padding:1px;">
                        @if($cs->leader_checked)
                            <span class="sym-ok" style="font-size:7pt;">✓</span><br>
                            <strong>{{ $cs->leader_name }}</strong><br>
                            <span style="color:#64748b;">{{ $cs->leader_checked_at ? $cs->leader_checked_at->format('d/m H:i') : '' }}</span>
                        @elseif($cs->is_rejected)
                            <span class="sym-ng" style="font-size:7pt;">✕</span><br>
                            <span style="color:#b91c1c; font-weight:bold;">REJECT</span>
                        @else
                            <span style="color:#94a3b8;">-</span>
                        @endif
                    </td>

                    {{-- Catatan --}}
                    <td style="text-align:left; padding-left:5px; font-size:6.5pt;">
                        {{ $cs->notes ?: '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($awalItems) + count($akhirItems) + 7 }}" style="padding:16px; color:#64748b; font-style:italic;">
                        Belum ada data checksheet Dandory Quality untuk filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tabel Parameter Standar ISO di Bawah (Traceability Lengkap Dokumen ISO) --}}
    <div class="std-ref-box">
        <table>
            <tr>
                <td style="width:48%; padding-right:6px; vertical-align:top;">
                    <strong style="display:block; margin-bottom:2px; font-size:7pt; color:#1e293b; text-transform:uppercase;">
                        Parameter Awal Dandory (1–{{ count($awalItems) }})
                    </strong>
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="width:20px; text-align:center;">No</th>
                                <th style="width:110px;">Item Check</th>
                                <th>Standard</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($awalItems as $it)
                                <tr>
                                    <td style="text-align:center; font-weight:bold;">{{ $it->order_no }}</td>
                                    <td style="font-weight:600;">{{ $it->item_check }}</td>
                                    <td>{{ $it->standard }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td style="width:52%; padding-left:6px; vertical-align:top;">
                    <strong style="display:block; margin-bottom:2px; font-size:7pt; color:#1e293b; text-transform:uppercase;">
                        Parameter Akhir Dandory (1–{{ count($akhirItems) }})
                    </strong>
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="width:20px; text-align:center;">No</th>
                                <th style="width:110px;">Item Check</th>
                                <th>Standard</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($akhirItems as $it)
                                <tr>
                                    <td style="text-align:center; font-weight:bold;">{{ $it->order_no }}</td>
                                    <td style="font-weight:600;">{{ $it->item_check }}</td>
                                    <td>{{ $it->standard }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer Timestamp Cetak --}}
    <div class="print-footer" id="printFooter">
        <span id="footerDateTime"></span>
    </div>

    {{-- Script Otomatis Cetak (Standar Modul Checksheet Proyek) --}}
    <script>
        (function () {
            var now = new Date();
            var pad = function (n) { return n < 10 ? '0' + n : n; };
            var dateStr = pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear()
                        + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
            var el = document.getElementById('footerDateTime');
            if (el) el.textContent = 'Dicetak: ' + dateStr;
        })();

        window.onload = function () {
            window.print();
        };
    </script>

</body>

</html>
