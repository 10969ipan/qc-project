<!DOCTYPE html>
@php
    $plantCode = strtolower($plantCode ?: 'karawang');
    $isJkt     = in_array($plantCode, ['jakarta', 'jkt']);
    $docHeader = \App\Models\GeneralSetting::getDocHeader('kepatuhan_operator', $plantCode, [
        'no_dokumen' => $isJkt ? 'QC-JKT-F-051' : 'PI-KRW-F-051',
        'tgl_terbit' => '31/03/2022',
        'revisi'     => '08/09/2023',
        'halaman'    => '1 / 2'
    ]);
    $monthNames = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];

    // Bagi item ke dalam 2 halaman cetak (~20 baris di halaman 1, sisanya + ringkasan di halaman 2)
    // agar setiap halaman muat 100% di 1 lembar A4 Landscape tanpa terpotong ke halaman liar
    $maxRowsPerPage = 20;
    $pages = [];
    $currentPageGroups = [];
    $currentPageCount = 0;

    foreach ($groupedItems as $prinsip => $itemList) {
        $groupItems = $itemList->values();
        $groupCount = $groupItems->count();

        if ($currentPageCount > 0 && ($currentPageCount + $groupCount) > $maxRowsPerPage) {
            $pages[] = $currentPageGroups;
            $currentPageGroups = [];
            $currentPageCount = 0;
        }

        if ($groupCount > $maxRowsPerPage) {
            foreach ($groupItems->chunk($maxRowsPerPage) as $chunk) {
                if ($currentPageCount > 0) {
                    $pages[] = $currentPageGroups;
                    $currentPageGroups = [];
                    $currentPageCount = 0;
                }
                $currentPageGroups[] = [
                    'prinsip' => $prinsip,
                    'items'   => $chunk->values(),
                ];
                $pages[] = $currentPageGroups;
                $currentPageGroups = [];
                $currentPageCount = 0;
            }
        } else {
            $currentPageGroups[] = [
                'prinsip' => $prinsip,
                'items'   => $groupItems,
            ];
            $currentPageCount += $groupCount;
        }
    }

    if (!empty($currentPageGroups)) {
        $pages[] = $currentPageGroups;
    }

    if (empty($pages)) {
        $pages[] = [];
    }

    $totalPages = count($pages);
    // Total lebar kolom non-tanggal = 2.0% + 7.8% + 10.0% + 18.2% + 4.3% = 42.3%
    // Sisa 57.7% dibagi rata ke seluruh tanggal (28-31 hari)
    $dayColPct  = 57.7 / $daysInMonth;

    $sundayMap = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $sundayMap[$d] = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday();
    }
@endphp
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Checksheet Kepatuhan Operator - {{ $selectedOperator->name }} - {{ $monthNames[$month] }} {{ $year }}</title>
<style>
@page {
    size: A4 landscape;
    margin: 5mm 6mm;
}
* {
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}
html, body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.8pt;
    color: #000;
    background: #fff;
    margin: 0;
    padding: 0;
    width: 100%;
}

/* ===== NO-PRINT TOOLBAR ===== */
.no-print-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 16px;
    background: #1e293b;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    box-shadow: 0 2px 8px rgba(0,0,0,.3);
}
.btn-print {
    background: #3b82f6;
    color: #fff;
    border: none;
    padding: 6px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}
.btn-print:hover { background: #2563eb; }
.btn-back {
    background: #475569;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
}
.btn-back:hover { background: #334155; }
.no-print-lbl {
    color: #94a3b8;
    font-size: 12px;
    margin-left: 4px;
}

.print-wrapper {
    padding: 48px 10px 20px 10px;
    margin: 0 auto;
    width: 100%;
}

.print-page {
    width: 100%;
    background: #fff;
    margin-bottom: 18px;
    page-break-inside: avoid;
    break-inside: avoid;
}

.print-page.page-break {
    page-break-after: always;
    break-after: page;
}

@media print {
    @page {
        size: A4 landscape;
        margin: 5mm 6mm;
    }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        background: #fff !important;
    }
    .no-print-bar {
        display: none !important;
    }
    .print-wrapper {
        padding: 0 !important;
        margin: 0 auto !important;
        width: 100% !important;
    }
    .print-page {
        margin-bottom: 0 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .print-page.page-break {
        page-break-after: always !important;
        break-after: page !important;
    }
    tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }
}

/* ===== HEADER DOKUMEN ISO ===== */
.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 3px;
    table-layout: auto;
}
.header-table td {
    border: 1px solid #000;
    padding: 2px 4px;
    vertical-align: middle;
}

/* ===== INFO OPERATOR (PROPORSIONAL & TIDAK TERPOTONG) ===== */
.operator-info {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 3px;
    font-size: 6.8pt;
    table-layout: fixed;
}
.operator-info td {
    border: 1px solid #000;
    padding: 2px 5px;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.operator-info .lbl {
    font-weight: 700;
    background: #f1f5f9;
}

/* ===== MAIN MATRIX TABLE (STRICT UNIFORM COLUMN LAYOUT) ===== */
.matrix-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed !important;
    margin-bottom: 3px;
}
.matrix-table th,
.matrix-table td {
    border: 1px solid #000;
    padding: 1.5px 2px;
    text-align: center;
    vertical-align: middle;
    font-size: 6.4pt;
    line-height: 1.12;
    word-wrap: break-word;
    overflow-wrap: break-word;
    overflow: hidden;
}
.matrix-table thead th {
    background: #e2e8f0 !important;
    color: #000 !important;
    font-weight: 700;
    font-size: 6.2pt;
    text-transform: uppercase;
    padding: 2.5px 1px;
    line-height: 1.1;
}

/* ===== PRINSIP DASAR KOLOM (BUNGKUS PER KATA UTUH, DILARANG POTONG TENGAH KATA) ===== */
.cell-prinsip {
    background: #f8fafc !important;
    padding: 2px 3px !important;
    text-align: center !important;
    vertical-align: middle !important;
    font-weight: 700;
    font-size: 5.6pt !important;
    line-height: 1.15 !important;
    color: #0f172a;
    text-transform: uppercase;
    word-break: keep-all !important;
    overflow-wrap: normal !important;
    word-wrap: normal !important;
    hyphens: none !important;
}

.col-item {
    text-align: left !important;
    padding: 1.5px 3px !important;
    font-weight: 600;
    font-size: 6.3pt !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;
}
.col-std {
    text-align: left !important;
    padding: 1.5px 3px !important;
    font-size: 6.2pt !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;
}

/* ===== STATUS SEL HARIAN ===== */
.cell-day {
    padding: 1px 0 !important;
    font-size: 6.8pt !important;
}
.cell-ok {
    background: #d1fae5 !important;
    color: #065f46 !important;
    font-weight: 900;
    font-size: 7.5pt !important;
}
.cell-ng {
    background: #fee2e2 !important;
    color: #7f1d1d !important;
    font-weight: 900;
    font-size: 7.5pt !important;
}
.cell-na {
    background: #f1f5f9 !important;
    color: #475569 !important;
    font-weight: 700;
}
.cell-sun {
    background: #fff1f2 !important;
    color: #be123c !important;
}
.matrix-table thead th.cell-sun {
    background: #ffe4e6 !important;
    color: #be123c !important;
}

/* ===== BARIS SUMMARY & VERIFIKASI (HANYA DI HALAMAN TERAKHIR) ===== */
.summary-row td {
    background: #f1f5f9 !important;
    font-weight: 700;
    font-size: 6.2pt !important;
    padding: 2px 1px !important;
}
.verify-row td {
    background: #fafafa !important;
    font-size: 5.8pt !important;
    padding: 1.5px 1px !important;
    white-space: nowrap;
}
.tfoot-lbl {
    text-align: right !important;
    padding-right: 5px !important;
    white-space: nowrap;
    font-size: 6.2pt !important;
    font-weight: 700;
    text-transform: uppercase;
}
.clr-ok  { color: #065f46 !important; }
.clr-pct { color: #1e3a8a !important; }
.clr-ng  { color: #b91c1c !important; }

.leader-chk-mark {
    font-weight: 900;
    color: #065f46;
    font-size: 6.5pt;
    line-height: 1;
}
.leader-chk-name {
    font-size: 4.8pt;
    color: #334155;
    line-height: 1;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: clip;
}

/* ===== LEGEND ===== */
.legend {
    margin-top: 3px;
    font-size: 6.3pt;
    border: 1px solid #000;
    padding: 2px 6px;
    background: #f8fafc;
}
.legend span {
    margin-right: 12px;
}

/* ===== TABEL DAFTAR ITEM MASALAH ===== */
.section-title {
    font-size: 6.8pt;
    font-weight: 700;
    text-transform: uppercase;
    border-bottom: 1px solid #000;
    padding-bottom: 1px;
    margin: 4px 0 2px 0;
}
.problem-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 6.2pt;
    table-layout: fixed !important;
}
.problem-table th,
.problem-table td {
    border: 1px solid #000;
    padding: 1.5px 3px;
    vertical-align: middle;
    word-wrap: break-word;
    overflow-wrap: break-word;
}
.problem-table thead th {
    background: #e2e8f0 !important;
    font-weight: 700;
    text-align: center;
    text-transform: uppercase;
    padding: 2px 3px;
}
.badge-open     { background: #fee2e2 !important; color: #7f1d1d; font-weight: 700; padding: 0.5px 3px; border-radius: 2px; }
.badge-progress { background: #fef3c7 !important; color: #78350f; font-weight: 700; padding: 0.5px 3px; border-radius: 2px; }
.badge-closed   { background: #d1fae5 !important; color: #065f46; font-weight: 700; padding: 0.5px 3px; border-radius: 2px; }
</style>
</head>
<body>

{{-- TOMBOL AKSI CETAK / KEMBALI (TIDAK IKUT TERCETAK) --}}
<div class="no-print-bar">
    <button type="button" class="btn-print" id="btnPrintAction">🖨 Cetak / Print</button>
    <button type="button" class="btn-back" id="btnBackAction">← Kembali</button>
    <span class="no-print-lbl">
        Preview Cetak ({{ $totalPages }} Halaman) — Checksheet Kepatuhan Operator:
        <strong style="color:#e2e8f0;">{{ $selectedOperator->name }}</strong>
        &nbsp;|&nbsp;
        <strong style="color:#e2e8f0;">{{ $monthNames[$month] }} {{ $year }}</strong>
    </span>
</div>

<div class="print-wrapper">
    @php $itemNumber = 1; @endphp

    @foreach($pages as $pageIdx => $pageGroups)
        @php
            $pageNum    = $pageIdx + 1;
            $isLastPage = ($pageNum === $totalPages);
        @endphp

        <div class="print-page {{ !$isLastPage ? 'page-break' : '' }}">

            {{-- ===== HEADER DOKUMEN ISO (TAMPIL DI SETIAP HALAMAN) ===== --}}
            <table class="header-table">
                <tr>
                    <td style="width:68px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" style="max-width:52px; max-height:36px; object-fit:contain;">
                    </td>
                    <td style="border-left:none; padding:3px 8px; text-align:center; vertical-align:middle; font-weight:bold; font-size:10pt; color:#000; text-transform:uppercase; letter-spacing:0.3px;">
                        CHECKSHEET KEPATUHAN OPERATOR QUALITY
                    </td>
                    <td style="width:1px; padding:0 !important; vertical-align:top; white-space:nowrap;">
                        <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                            <tr style="height:100%;">

                                {{-- ===== [3A] Tabel No. Dokumen ===== --}}
                                <td style="border:none; padding:1.5px 3px 1.5px 1.5px; vertical-align:top; height:100%; white-space:nowrap;">
                                    <table style="border-collapse:collapse; border:1px solid #000; font-size:6.3pt; line-height:1.15; background:#fff; height:100%; width:100%;">
                                        <tr>
                                            <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#334155; white-space:nowrap;">No. Dokumen</td>
                                            <td style="border:1px solid #000; padding:1px 3px; text-align:center; color:#334155;">:</td>
                                            <td style="border:1px solid #000; padding:1px 4px; font-weight:700; color:#000; white-space:nowrap;">{{ $docHeader['no_dokumen'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#334155; white-space:nowrap;">Tgl. Terbit</td>
                                            <td style="border:1px solid #000; padding:1px 3px; text-align:center; color:#334155;">:</td>
                                            <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#000; white-space:nowrap;">{{ $docHeader['tgl_terbit'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#334155; white-space:nowrap;">Revisi / Tgl</td>
                                            <td style="border:1px solid #000; padding:1px 3px; text-align:center; color:#334155;">:</td>
                                            <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#000; white-space:nowrap;">{{ $docHeader['revisi'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#334155; white-space:nowrap;">Halaman</td>
                                            <td style="border:1px solid #000; padding:1px 3px; text-align:center; color:#334155;">:</td>
                                            <td style="border:1px solid #000; padding:1px 4px; font-weight:700; color:#000; white-space:nowrap;">{{ $pageNum }} / {{ $totalPages }}</td>
                                        </tr>
                                    </table>
                                </td>

                                {{-- ===== [3B] Tabel Signatures ===== --}}
                                <td style="border:none; padding:1.5px 1.5px 1.5px 0; vertical-align:top; height:100%;">
                                    <table style="border-collapse:collapse; border:1px solid #000; text-align:center; font-size:6pt; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:290px;">
                                        <thead>
                                            <tr>
                                                <th style="border:1px solid #000; padding:1px; font-weight:600; color:#334155; background:#fff; width:20px;">Tgl.</th>
                                                <th style="border:1px solid #000; padding:1px 3px; font-weight:600; color:#334155; background:#fff; width:90px;">Dibuat</th>
                                                <th style="border:1px solid #000; padding:1px 3px; font-weight:600; color:#334155; background:#fff; width:90px;">Diperiksa</th>
                                                <th style="border:1px solid #000; padding:1px 3px; font-weight:600; color:#334155; background:#fff; width:90px;">Diketahui</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td rowspan="3" style="border:1px solid #000; padding:1px; vertical-align:middle; text-align:center; width:20px;">
                                                    <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:5.5pt; font-weight:600; margin:0 auto; color:#475569;">
                                                        {{ date('d-M-y') }}
                                                    </div>
                                                </td>
                                                <td style="border:1px solid #000; padding:1px; vertical-align:middle; height:32px; background:#fff; text-align:center;">
                                                    @if($isJkt)
                                                        <img src="{{ asset('signatures/suli.png') }}" alt="Masuli" style="max-height:36px; max-width:80px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.15); transform-origin:center;">
                                                    @else
                                                        <img src="{{ asset('signatures/arif.png') }}" alt="Arief H" style="max-height:36px; max-width:80px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.15); transform-origin:center;">
                                                    @endif
                                                </td>
                                                <td style="border:1px solid #000; padding:1px; vertical-align:middle; height:32px; background:#fff; text-align:center;">
                                                    <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:36px; max-width:80px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.15); transform-origin:center;">
                                                </td>
                                                <td style="border:1px solid #000; padding:1px; vertical-align:middle; height:32px; background:#fff; text-align:center;">
                                                    <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:36px; max-width:80px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.15); transform-origin:center;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #000; padding:1px 2px; font-weight:700; font-size:5.8pt; color:#000; white-space:nowrap;">{{ $isJkt ? 'Masuli' : 'Arief H' }}</td>
                                                <td style="border:1px solid #000; padding:1px 2px; font-weight:700; font-size:5.8pt; color:#000; white-space:nowrap;">Iwan S</td>
                                                <td style="border:1px solid #000; padding:1px 2px; font-weight:700; font-size:5.8pt; color:#000; white-space:nowrap;">Desti K</td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #000; padding:1px 2px; font-size:5.5pt; color:#334155; white-space:nowrap;">Spv. QC</td>
                                                <td style="border:1px solid #000; padding:1px 2px; font-size:5.5pt; color:#334155; white-space:nowrap;">Asst. Mgr Quality</td>
                                                <td style="border:1px solid #000; padding:1px 2px; font-size:5.5pt; color:#334155; white-space:nowrap;">Mgr. Quality</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>

                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- ===== INFO OPERATOR (LEBAR KOLOM SEIMBANG AGAR PLANT TIDAK TERPOTONG) ===== --}}
            <table class="operator-info">
                <colgroup>
                    <col style="width: 9.5%;">
                    <col style="width: 21.5%;">
                    <col style="width: 8.5%;">
                    <col style="width: 24.5%;">
                    <col style="width: 8.5%;">
                    <col style="width: 13.5%;">
                    <col style="width: 5.0%;">
                    <col style="width: 9.0%;">
                </colgroup>
                <tr>
                    <td class="lbl">Nama Operator</td>
                    <td>: <strong>{{ $selectedOperator->name }}</strong></td>
                    <td class="lbl">Bagian / Unit</td>
                    <td>: {{ $checksheet->bagian ?: 'Quality Control' }}</td>
                    <td class="lbl">Periode Audit</td>
                    <td>: <strong>{{ $monthNames[$month] }} {{ $year }}</strong></td>
                    <td class="lbl">Plant</td>
                    <td>: <strong>{{ strtoupper($plantCode) }}</strong></td>
                </tr>
            </table>

            {{-- ===== TABEL UTAMA MATRIKS AUDIT KEPATUHAN OPERATOR ===== --}}
            <table class="matrix-table">
                <colgroup>
                    <col style="width: 2.0%;">
                    <col style="width: 7.8%;">
                    <col style="width: 10.0%;">
                    <col style="width: 18.2%;">
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        <col style="width: {{ number_format($dayColPct, 4, '.', '') }}%;">
                    @endfor
                    <col style="width: 4.3%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>PRINSIP<br>DASAR</th>
                        <th style="text-align:left; padding-left:3px;">ITEM CHECK</th>
                        <th style="text-align:left; padding-left:3px;">STANDARD / KRITERIA AUDIT</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            <th class="{{ $sundayMap[$d] ? 'cell-sun' : '' }}">{{ $d }}</th>
                        @endfor
                        <th>RATA2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pageGroups as $grp)
                        @php
                            $prinsip  = $grp['prinsip'];
                            $itemList = $grp['items'];
                            $first    = true;
                            $grpCount = count($itemList);
                        @endphp
                        @foreach($itemList as $item)
                            <tr>
                                <td style="font-weight:700;">{{ $itemNumber++ }}</td>
                                @if($first)
                                    <td rowspan="{{ $grpCount }}" class="cell-prinsip">
                                        {{ $prinsip }}
                                    </td>
                                    @php $first = false; @endphp
                                @endif
                                <td class="col-item">{{ $item->item_check }}</td>
                                <td class="col-std">{{ $item->standard }}</td>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $st  = $entriesMatrix[$item->id][$d] ?? null;
                                        $sun = $sundayMap[$d];
                                        $cls = 'cell-day' . ($sun ? ' cell-sun' : '');
                                        $sym = '';
                                        if ($st === 'OK')     { $cls .= ' cell-ok'; $sym = '✓'; }
                                        elseif ($st === 'NG') { $cls .= ' cell-ng'; $sym = '✕'; }
                                        elseif ($st === 'NA') { $cls .= ' cell-na'; $sym = '-'; }
                                    @endphp
                                    <td class="{{ $cls }}">{{ $sym }}</td>
                                @endfor
                                @php
                                    $iOk = 0; $iF = 0;
                                    for ($d = 1; $d <= $daysInMonth; $d++) {
                                        $st = $entriesMatrix[$item->id][$d] ?? null;
                                        if ($st === 'OK') { $iOk++; $iF++; }
                                        elseif ($st === 'NG') { $iF++; }
                                    }
                                    $iPct = $iF > 0 ? round(($iOk / $iF) * 100) : '-';
                                @endphp
                                <td style="font-weight:700; font-size:6.3pt;">{{ $iPct }}{{ is_numeric($iPct) ? '%' : '' }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                    {{-- BARIS SUMMARY & VERIFIKASI DILETAKKAN DI DALAM TBODY KHUSUS HALAMAN TERAKHIR AGAR TIDAK BERULANG OTOMATIS OLEH BROWSER --}}
                    @if($isLastPage)
                        {{-- Jumlah Sesuai --}}
                        <tr class="summary-row">
                            <td colspan="4" class="tfoot-lbl">JUMLAH SESUAI (✓)</td>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                <td class="clr-ok {{ $sundayMap[$d] ? 'cell-sun' : '' }}">{{ $dailyScores[$d]['ok'] ?: '' }}</td>
                            @endfor
                            <td class="clr-ok">{{ $totalOkMonth }}</td>
                        </tr>
                        {{-- Persentase Kepatuhan --}}
                        <tr class="summary-row">
                            <td colspan="4" class="tfoot-lbl">PERSENTASE KEPATUHAN (%)</td>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $pct = $dailyScores[$d]['pct'];
                                    $pc  = $pct !== null && $pct < 100 ? 'clr-ng' : 'clr-pct';
                                @endphp
                                <td class="{{ $pc }} {{ $sundayMap[$d] ? 'cell-sun' : '' }}" style="font-size:5.5pt !important;">
                                    {{ $pct !== null ? round($pct).'%' : '-' }}
                                </td>
                            @endfor
                            <td class="clr-pct">{{ $monthlyPct }}%</td>
                        </tr>
                        {{-- DINILAI (KASHIFT/KARU) --}}
                        @php $lc = $checksheet->leader_checks ?? []; @endphp
                        <tr class="verify-row">
                            <td colspan="4" class="tfoot-lbl">DINILAI (KASHIFT / KARU)</td>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $lChk = $lc[$d] ?? null;
                                    $chk  = !empty($lChk['checked']);
                                    $uNm  = $lChk['user_name'] ?? '';
                                    $shortName = $uNm ? explode(' ', trim($uNm))[0] : '';
                                @endphp
                                <td class="{{ $sundayMap[$d] ? 'cell-sun' : '' }}" style="vertical-align:middle; padding:1.5px 0.5px !important;">
                                    @if($chk)
                                        <div class="leader-chk-mark">✓</div>
                                        @if($shortName)
                                            <div class="leader-chk-name">{{ \Illuminate\Support\Str::limit($shortName, 6, '') }}</div>
                                        @endif
                                    @else
                                        <span style="color:#94a3b8;">-</span>
                                    @endif
                                </td>
                            @endfor
                            <td style="font-weight:700;">Harian</td>
                        </tr>
                        {{-- DIPERIKSA (SPV) --}}
                        @php
                            $sc = $checksheet->spv_checks ?? [];
                            $weeks = [1=>['s'=>1,'e'=>7],2=>['s'=>8,'e'=>14],3=>['s'=>15,'e'=>21],4=>['s'=>22,'e'=>28],5=>['s'=>29,'e'=>$daysInMonth]];
                        @endphp
                        <tr class="verify-row">
                            <td colspan="4" class="tfoot-lbl">DIPERIKSA / DIKONTROL (SPV)</td>
                            @foreach($weeks as $wNum => $wr)
                                @if($wr['s'] <= $daysInMonth)
                                    @php
                                        $wE   = min($wr['e'], $daysInMonth);
                                        $wSp  = $wE - $wr['s'] + 1;
                                        $sC   = $sc[$wNum] ?? null;
                                        $sChk = !empty($sC['checked']);
                                        $sU   = $sC['user_name'] ?? '';
                                    @endphp
                                    <td colspan="{{ $wSp }}" style="text-align:center; vertical-align:middle; padding:1.5px 1px !important;">
                                        <div style="font-weight:700; font-size:5.8pt; {{ $sChk ? 'color:#065f46;' : 'color:#64748b;' }}">
                                            {{ $sChk ? ('✓ W'.$wNum.' Approved') : ('Minggu-' . $wNum) }}
                                        </div>
                                        @if($sChk && $sU)
                                            <div style="font-size:5.2pt; color:#334155;">{{ $sU }}</div>
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                            <td style="font-weight:700;">Mingguan</td>
                        </tr>
                        {{-- DIKETAHUI (ASST. MNGR) --}}
                        <tr class="verify-row">
                            <td colspan="4" class="tfoot-lbl">DIKETAHUI (ASST. MNGR)</td>
                            <td colspan="{{ $daysInMonth }}" style="text-align:center; padding:2px 4px !important; vertical-align:middle;">
                                @if($checksheet->mgr_checked)
                                    <span style="color:#065f46; font-weight:700; font-size:6.3pt;">✓ Approved</span>
                                    @if($checksheet->manager)
                                        &nbsp;<span style="font-size:5.8pt; color:#334155; font-weight:600;">
                                            — {{ $checksheet->manager->name }}
                                            ({{ $checksheet->mgr_checked_at ? $checksheet->mgr_checked_at->format('d/m/Y H:i') : '' }})
                                        </span>
                                    @endif
                                @else
                                    <span style="color:#64748b; font-size:5.8pt;">— Belum Terverifikasi ({{ $monthNames[$month] }} {{ $year }}) —</span>
                                @endif
                            </td>
                            <td style="font-weight:700;">Bulanan</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- LEGEND & DAFTAR ITEM MASALAH DI HALAMAN TERAKHIR --}}
            @if($isLastPage)
                <div class="legend">
                    <strong>Keterangan:</strong>
                    <span><strong>✓</strong> = OK / Sesuai</span>
                    <span><strong>✕</strong> = NG / Tidak Sesuai</span>
                    <span><strong>–</strong> = NA / Tidak Dinilai (Tidak dihitung dalam kalkulasi %)</span>
                    <span style="float:right; color:#334155; margin-right:0;">Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
                </div>

                @if($problems->count() > 0)
                    <div class="section-title">Daftar Item Masalah (Kontrol Kepatuhan) — {{ $problems->count() }} Item</div>
                    <table class="problem-table">
                        <thead>
                            <tr>
                                <th style="width:3.5%;">NO</th>
                                <th style="width:9%;">TGL TEMUAN</th>
                                <th style="width:34%;">KONDISI ABNORMAL / ITEM MASALAH</th>
                                <th style="width:28.5%;">TINDAKAN PERBAIKAN / ACTION</th>
                                <th style="width:10%;">PIC</th>
                                <th style="width:8%;">TARGET</th>
                                <th style="width:7%;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($problems as $pi => $prob)
                                <tr>
                                    <td style="text-align:center; font-weight:700;">{{ $pi + 1 }}</td>
                                    <td style="text-align:center;">{{ $prob->problem_date ? $prob->problem_date->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $prob->problem_description }}</td>
                                    <td>{{ $prob->corrective_action ?: '-' }}</td>
                                    <td style="font-weight:600; text-align:center;">{{ $prob->pic_name ?: '-' }}</td>
                                    <td style="text-align:center;">{{ $prob->target_date ? $prob->target_date->format('d/m/Y') : '-' }}</td>
                                    <td style="text-align:center;">
                                        @if($prob->status === 'Closed')
                                            <span class="badge-closed">Closed</span>
                                        @elseif($prob->status === 'In Progress')
                                            <span class="badge-progress">In Progress</span>
                                        @else
                                            <span class="badge-open">Open</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

        </div>
    @endforeach
</div>

<script src="{{ asset('js/checksheet/operator-compliance-print.js') }}"></script>
</body>
</html>
