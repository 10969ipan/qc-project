<!DOCTYPE html>
@php
    $plantCode = strtolower($plantCode ?: 'karawang');
    $isJkt     = in_array($plantCode, ['jakarta', 'jkt']);
    $docHeader = \App\Models\GeneralSetting::getDocHeader('kepatuhan_operator', $plantCode, [
        'no_dokumen' => 'PI-KRW-F-051',
        'tgl_terbit' => '31/03/2022',
        'revisi'     => '08/09/2023',
        'halaman'    => '1 / 1'
    ]);
    $monthNames = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
@endphp
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Cetak – Checksheet Kepatuhan Operator – {{ $selectedOperator->name }} – {{ $monthNames[$month] }} {{ $year }}</title>
<style>
@page {
    size: A4 landscape;
    margin: 5mm;
}
* { box-sizing: border-box; }
html, body {
    font-family: Arial, sans-serif;
    font-size: 6.5px;
    color: #000;
    background: #fff;
    margin: 0;
    padding: 0;
    width: 100%;
}

/* ===== NO-PRINT TOOLBAR ===== */
.no-print-bar {
    display: flex; align-items: center; gap: 10px; padding: 6px 14px;
    background: #1e293b; position: fixed; top: 0; left: 0; right: 0;
    z-index: 9999; box-shadow: 0 2px 8px rgba(0,0,0,.3);
}
.btn-print {
    background: #3b82f6; color: #fff; border: none; padding: 5px 16px;
    border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;
}
.btn-print:hover { background: #2563eb; }
.btn-back {
    background: #475569; color: #fff; border: none; padding: 5px 12px;
    border-radius: 6px; font-size: 12px; cursor: pointer;
}
.btn-back:hover { background: #334155; }
.no-print-lbl { color: #94a3b8; font-size: 12px; margin-left: 4px; }

.print-wrapper {
    padding-top: 44px;
    margin: 0 auto;
    width: 100%;
}

@media print {
    @page {
        size: A4 landscape;
        margin: 5mm;
    }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        background: #fff !important;
    }
    .no-print-bar { display: none !important; }
    .print-wrapper {
        padding-top: 0 !important;
        margin: 0 auto !important;
        width: 100% !important;
        zoom: 78%; /* Auto-scale so all items + headers & footers fit strictly on 1 single A4 landscape page */
    }
    tr { page-break-inside: avoid; break-inside: avoid; }
}

/* ===== PRINSIP DASAR Vertical Layout matching Index ===== */
.prinsip-vertical-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 24px;
    width: 100%;
    margin: 0 auto;
}

.prinsip-vertical-text {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    -webkit-transform: rotate(180deg);
    white-space: nowrap;
    text-align: center;
    font-weight: 700;
    font-size: 5.8px;
    color: #1e293b;
    letter-spacing: 0.2px;
    margin: 0 auto;
    text-transform: uppercase;
}

/* ===== HEADER DOKUMEN ===== */
.header-table { width: 100%; border-collapse: collapse; margin-bottom: 3px; table-layout: auto; }
.header-table td { border: 1px solid #000; padding: 2px 3px; vertical-align: middle; }

/* ===== INFO OPERATOR ===== */
.operator-info { width: 100%; border-collapse: collapse; margin-bottom: 3px; font-size: 6.5px; table-layout: auto; }
.operator-info td { border: 1px solid #000; padding: 1px 4px; }
.operator-info .lbl { font-weight: 600; width: 75px; background: #f5f5f5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

/* ===== MAIN MATRIX TABLE WITH PROPORTIONAL FIXED LAYOUT ===== */
.matrix-table { width: 100%; border-collapse: collapse; table-layout: fixed !important; margin-bottom: 3px; }
.matrix-table th, .matrix-table td { border: 1px solid #000; padding: 0.5px 1px; text-align: center; vertical-align: middle; font-size: 6.2px; word-wrap: break-word; overflow-wrap: break-word; }
.matrix-table thead th { background: #e8edf4 !important; font-weight: 700; font-size: 5.8px; text-transform: uppercase; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 1.5px 1px; }

.col-item    { text-align: left !important; padding-left: 3px !important; }
.col-std     { text-align: left !important; padding-left: 3px !important; }
.cell-ok  { background: #d1fae5 !important; color: #065f46 !important; font-weight: 900; font-size: 8px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.cell-ng  { background: #fee2e2 !important; color: #7f1d1d !important; font-weight: 900; font-size: 8px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.cell-na  { background: #f1f5f9 !important; color: #475569 !important; font-weight: 700; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.cell-sun { background: #fff1f2 !important; color: #be123c !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.summary-row td { background: #f0f4f8 !important; font-weight: 700; font-size: 5.8px; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 1px; }
.verify-row  td { background: #fafafa !important; font-size: 5.2px; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 0.5px 1px; }
.tfoot-lbl { text-align: right !important; padding-right: 4px !important; white-space: nowrap; font-size: 5.8px; font-weight: 700; }
.clr-ok  { color: #065f46; }
.clr-pct { color: #1e3a8a; }
.clr-ng  { color: #7f1d1d; }

/* ===== PROBLEM TABLE ===== */
.section-title { font-size: 7px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 1px; margin: 3px 0 1px 0; }
.problem-table { width: 100%; border-collapse: collapse; font-size: 5.5px; table-layout: fixed !important; }
.problem-table th, .problem-table td { border: 1px solid #000; padding: 1px 2px; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
.problem-table thead th { background: #e8edf4 !important; font-weight: 700; text-align: center; text-transform: uppercase; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 1.5px 2px; }
.badge-open     { background: #fee2e2 !important; color: #7f1d1d; font-weight: 700; padding: 0 2px; border-radius: 2px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.badge-progress { background: #fef3c7 !important; color: #78350f; font-weight: 700; padding: 0 2px; border-radius: 2px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.badge-closed   { background: #d1fae5 !important; color: #065f46; font-weight: 700; padding: 0 2px; border-radius: 2px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

/* ===== LEGEND ===== */
.legend { margin-top: 3px; font-size: 5.8px; border: 1px solid #000; padding: 1px 3px; }
.legend span { margin-right: 10px; }
</style>
</head>
<body>

{{-- TOMBOL AKSI CETAK / KEMBALI (TIDAK IKUT TERCETAK) --}}
<div class="no-print-bar">
    <button class="btn-print" onclick="window.print()">🖨 Cetak / Print</button>
    <button class="btn-back" onclick="history.back()">← Kembali</button>
    <span class="no-print-lbl">
        Preview Cetak — Checksheet Kepatuhan Operator:
        <strong style="color:#e2e8f0;">{{ $selectedOperator->name }}</strong>
        &nbsp;|&nbsp;
        <strong style="color:#e2e8f0;">{{ $monthNames[$month] }} {{ $year }}</strong>
    </span>
</div>

<div class="print-wrapper">

    {{-- ===== HEADER DOKUMEN ===== --}}
    <table class="header-table">
        <tr>
            <td style="width:70px; text-align:center; vertical-align:middle;">
                <img src="{{ asset('master item/ipp.jpg') }}" style="max-width:52px; max-height:38px; object-fit:contain;">
            </td>
            <td style="border-left:none; padding:3px 6px; text-align:center; vertical-align:middle; font-weight:bold; font-size:9pt; color:#000; text-transform:uppercase;">
                CHECKSHEET KEPATUHAN OPERATOR QUALITY
            </td>
            <td style="width:1px; padding:0 !important; vertical-align:top; white-space:nowrap;">
                <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                    <tr style="height:100%;">

                        {{-- ===== [3A] Tabel No. Dokumen ===== --}}
                        <td style="border:none; padding:2px 4px 2px 2px; vertical-align:top; height:100%; white-space:nowrap;">
                            <table style="border-collapse:collapse; border:1px solid #000; font-size:6.5pt; line-height:1.2; background:#fff; height:100%; width:100%;">
                                <tr>
                                    <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                    <td style="border:1px solid #000; padding:1px 3px; text-align:center; color:#495057;">:</td>
                                    <td style="border:1px solid #000; padding:1px 4px; font-weight:700; color:#212529; white-space:nowrap;">{{ $docHeader['no_dokumen'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                    <td style="border:1px solid #000; padding:1px 3px; text-align:center; color:#495057;">:</td>
                                    <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['tgl_terbit'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                    <td style="border:1px solid #000; padding:1px 3px; text-align:center; color:#495057;">:</td>
                                    <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['revisi'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                    <td style="border:1px solid #000; padding:1px 3px; text-align:center; color:#495057;">:</td>
                                    <td style="border:1px solid #000; padding:1px 4px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['halaman'] ?? '1/1' }}</td>
                                </tr>
                            </table>
                        </td>

                        {{-- ===== [3B] Tabel Signatures ===== --}}
                        <td style="border:none; padding:2px 2px 2px 0; vertical-align:top; height:100%;">
                            <table style="border-collapse:collapse; border:1px solid #000; text-align:center; font-size:6pt; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:295px;">
                                <thead>
                                    <tr>
                                        <th style="border:1px solid #000; padding:1.5px; font-weight:600; color:#495057; background:#fff; width:20px;">Tgl.</th>
                                        <th style="border:1px solid #000; padding:1.5px 3px; font-weight:600; color:#495057; background:#fff; width:90px;">Dibuat</th>
                                        <th style="border:1px solid #000; padding:1.5px 3px; font-weight:600; color:#495057; background:#fff; width:90px;">Diperiksa</th>
                                        <th style="border:1px solid #000; padding:1.5px 3px; font-weight:600; color:#495057; background:#fff; width:90px;">Diketahui</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td rowspan="3" style="border:1px solid #000; padding:1px; vertical-align:middle; text-align:center; width:20px;">
                                            <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:5.5pt; font-weight:400; margin:0 auto; color:#6c757d;">
                                                {{ date('d-M-y') }}
                                            </div>
                                        </td>
                                        <td style="border:1px solid #000; padding:1px; vertical-align:middle; height:38px; background:#fff; text-align:center;">
                                            @if($isJkt)
                                                <img src="{{ asset('signatures/suli.png') }}" alt="Masuli" style="max-height:44px; max-width:85px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.2); transform-origin:center;">
                                            @else
                                                <img src="{{ asset('signatures/arif.png') }}" alt="Arief H" style="max-height:44px; max-width:85px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.2); transform-origin:center;">
                                            @endif
                                        </td>
                                        <td style="border:1px solid #000; padding:1px; vertical-align:middle; height:38px; background:#fff; text-align:center;">
                                            <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:44px; max-width:85px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.2); transform-origin:center;">
                                        </td>
                                        <td style="border:1px solid #000; padding:1px; vertical-align:middle; height:38px; background:#fff; text-align:center;">
                                            <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:44px; max-width:85px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.2); transform-origin:center;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #000; padding:1px 2px; font-weight:600; font-size:5.8pt; color:#212529; white-space:nowrap;">{{ $isJkt ? 'Masuli' : 'Arief H' }}</td>
                                        <td style="border:1px solid #000; padding:1px 2px; font-weight:600; font-size:5.8pt; color:#212529; white-space:nowrap;">Iwan S</td>
                                        <td style="border:1px solid #000; padding:1px 2px; font-weight:600; font-size:5.8pt; color:#212529; white-space:nowrap;">Desti K</td>
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #000; padding:1px 2px; font-size:5.8pt; color:#495057; white-space:nowrap;">Spv. QC</td>
                                        <td style="border:1px solid #000; padding:1px 2px; font-size:5.8pt; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                        <td style="border:1px solid #000; padding:1px 2px; font-size:5.8pt; color:#495057; white-space:nowrap;">Mgr. Quality</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>

                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== INFO OPERATOR ===== --}}
    <table class="operator-info">
        <tr>
            <td class="lbl">Nama Operator</td><td>: <strong>{{ $selectedOperator->name }}</strong></td>
            <td class="lbl">Bagian / Unit</td><td>: {{ $checksheet->bagian ?: 'Quality Control' }}</td>
            <td class="lbl">Periode Audit</td><td>: <strong>{{ $monthNames[$month] }} {{ $year }}</strong></td>
            <td class="lbl">Plant</td><td>: {{ strtoupper($plantCode) }}</td>
        </tr>
    </table>

    {{-- ===== TABEL UTAMA MATRIKS AUDIT KEPATUHAN OPERATOR ===== --}}
    <table class="matrix-table">
        @php
            $dayColPct = 53.0 / $daysInMonth;
        @endphp
        <thead>
            <tr>
                <th style="width: 2.0%;">NO</th>
                <th style="width: 3.5%; padding:1.5px 1px;">
                    <div style="line-height:1.1;font-size:5.5px;font-weight:700;">PRINSIP<br>DASAR</div>
                </th>
                <th style="width: 14.5%; text-align:left; padding-left:3px;">ITEM CHECK</th>
                <th style="width: 23.0%; text-align:left; padding-left:3px;">STANDARD / KRITERIA AUDIT</th>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php $isSun = \Carbon\Carbon::createFromDate($year,$month,$d)->isSunday(); @endphp
                    <th style="width: {{ number_format($dayColPct, 4, '.', '') }}%;" class="{{ $isSun ? 'cell-sun' : '' }}">{{ $d }}</th>
                @endfor
                <th style="width: 4.0%;">RATA-RATA</th>
            </tr>
        </thead>
        <tbody>
            @php $idx = 1; @endphp
            @foreach($groupedItems as $prinsip => $itemList)
                @php $first = true; $grpCount = count($itemList); @endphp
                @foreach($itemList as $item)
                    <tr>
                        <td style="font-weight:700;">{{ $idx++ }}</td>
                        @if($first)
                            <td rowspan="{{ $grpCount }}"
                                style="background:#f5f8fc;padding:1px 1px;text-align:center;vertical-align:middle;-webkit-print-color-adjust:exact;print-color-adjust:exact;">
                                <div class="prinsip-vertical-wrapper">
                                    <div class="prinsip-vertical-text">
                                        {{ $prinsip }}
                                    </div>
                                </div>
                            </td>
                            @php $first = false; @endphp
                        @endif
                        <td class="col-item" style="font-size:5.8px;line-height:1.15;">{{ $item->item_check }}</td>
                        <td class="col-std" style="font-size:5.8px;line-height:1.15;">{{ $item->standard }}</td>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $st  = $entriesMatrix[$item->id][$d] ?? null;
                                $sun = \Carbon\Carbon::createFromDate($year,$month,$d)->isSunday();
                                $cls = $sun ? 'cell-sun' : '';
                                $sym = '';
                                if ($st==='OK')     { $cls.=' cell-ok'; $sym='✓'; }
                                elseif($st==='NG')  { $cls.=' cell-ng'; $sym='✕'; }
                                elseif($st==='NA')  { $cls.=' cell-na'; $sym='-'; }
                            @endphp
                            <td class="{{ $cls }}">{{ $sym }}</td>
                        @endfor
                        @php
                            $iOk=0; $iF=0;
                            for($d=1;$d<=$daysInMonth;$d++){
                                $st=$entriesMatrix[$item->id][$d]??null;
                                if($st==='OK'){$iOk++;$iF++;}
                                elseif($st==='NG'){$iF++;}
                            }
                            $iPct=$iF>0?round(($iOk/$iF)*100):'-';
                        @endphp
                        <td style="font-weight:700;font-size:6px;">{{ $iPct }}{{ is_numeric($iPct)?'%':'' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            {{-- Jumlah Sesuai --}}
            <tr class="summary-row">
                <td colspan="4" class="tfoot-lbl">Jumlah Sesuai (✓)</td>
                @for($d=1;$d<=$daysInMonth;$d++)
                    @php $sun=\Carbon\Carbon::createFromDate($year,$month,$d)->isSunday(); @endphp
                    <td class="clr-ok {{ $sun?'cell-sun':'' }}" style="font-weight:700;">{{ $dailyScores[$d]['ok']?:'' }}</td>
                @endfor
                <td class="clr-ok" style="font-weight:700;">{{ $totalOkMonth }}</td>
            </tr>
            {{-- Persentase Kepatuhan --}}
            <tr class="summary-row">
                <td colspan="4" class="tfoot-lbl">Persentase Kepatuhan (%)</td>
                @for($d=1;$d<=$daysInMonth;$d++)
                    @php
                        $pct=$dailyScores[$d]['pct'];
                        $sun=\Carbon\Carbon::createFromDate($year,$month,$d)->isSunday();
                        $pc=$pct!==null&&$pct<100?'clr-ng':'clr-pct';
                    @endphp
                    <td class="{{ $pc }} {{ $sun?'cell-sun':'' }}" style="font-weight:700;font-size:5.2px;">{{ $pct!==null?$pct.'%':'-' }}</td>
                @endfor
                <td class="clr-pct" style="font-weight:700;">{{ $monthlyPct }}%</td>
            </tr>
            {{-- DINILAI (KASHIFT/KARU) --}}
            @php $lc = $checksheet->leader_checks ?? []; @endphp
            <tr class="verify-row">
                <td colspan="4" class="tfoot-lbl" style="font-size:5.5px;">DINILAI (KASHIFT / KARU)</td>
                @for($d=1;$d<=$daysInMonth;$d++)
                    @php
                        $sun    = \Carbon\Carbon::createFromDate($year,$month,$d)->isSunday();
                        $lChk   = $lc[$d] ?? null;
                        $chk    = !empty($lChk['checked']);
                        $uNm    = $lChk['user_name'] ?? '';
                        $uTm    = $lChk['time'] ?? '';
                    @endphp
                    <td class="{{ $sun?'cell-sun':'' }}" style="vertical-align:top;padding:0.5px;" title="{{ $chk&&$uNm?$uNm.' ('.$uTm.')':'' }}">
                        @if($chk)
                            <div style="font-weight:900;color:#065f46;font-size:7px;text-align:center;">✓</div>
                            <div style="font-size:3.5px;color:#555;text-align:center;">{{ $uNm }}</div>
                        @endif
                    </td>
                @endfor
                <td style="font-size:5.5px;font-weight:700;">Harian</td>
            </tr>
            {{-- DIPERIKSA (SPV) --}}
            @php
                $sc = $checksheet->spv_checks ?? [];
                $weeks = [1=>['s'=>1,'e'=>7],2=>['s'=>8,'e'=>14],3=>['s'=>15,'e'=>21],4=>['s'=>22,'e'=>28],5=>['s'=>29,'e'=>$daysInMonth]];
            @endphp
            <tr class="verify-row">
                <td colspan="4" class="tfoot-lbl" style="font-size:5.5px;">DIPERIKSA / DIKONTROL (SPV)</td>
                @foreach($weeks as $wNum=>$wr)
                    @if($wr['s'] <= $daysInMonth)
                        @php
                            $wE  = min($wr['e'],$daysInMonth);
                            $wSp = $wE-$wr['s']+1;
                            $sC  = $sc[$wNum] ?? null;
                            $sChk= !empty($sC['checked']);
                            $sU  = $sC['user_name'] ?? '';
                            $sT  = $sC['time'] ?? '';
                        @endphp
                        <td colspan="{{ $wSp }}" style="text-align:center;vertical-align:middle;padding:0.5px;white-space:nowrap;">
                            <div style="font-weight:700;font-size:5.2px;{{ $sChk?'color:#065f46':'color:#aaa' }}">Minggu-{{ $wNum }} {{ $sChk?'✓':'' }}</div>
                            @if($sChk && $sU)
                                <div style="font-size:3.5px;color:#555;">{{ $sU }}</div>
                            @endif
                        </td>
                    @endif
                @endforeach
                <td style="font-size:5.5px;font-weight:700;">Mingguan</td>
            </tr>
            {{-- DIKETAHUI (ASST. MNGR) --}}
            <tr class="verify-row">
                <td colspan="4" class="tfoot-lbl" style="font-size:5.5px;">DIKETAHUI (ASST. MNGR)</td>
                <td colspan="{{ $daysInMonth }}" style="text-align:center;padding:1px 3px;vertical-align:middle;">
                    @if($checksheet->mgr_checked)
                        <span style="color:#065f46;font-weight:700;font-size:6px;">✓ Verified (Asst Mgr)</span>
                        @if($checksheet->manager)
                            &nbsp;<span style="font-size:5px;color:#555;">
                                {{ $checksheet->manager->name }}
                                ({{ $checksheet->mgr_checked_at ? $checksheet->mgr_checked_at->format('d/m/Y H:i') : '' }})
                            </span>
                        @endif
                    @else
                        <span style="color:#aaa;font-size:5px;">— Belum Terverifikasi —</span>
                    @endif
                </td>
                <td style="font-size:5.5px;font-weight:700;">Bulanan</td>
            </tr>
        </tfoot>
    </table>

    {{-- LEGEND --}}
    <div class="legend">
        <strong>Keterangan:</strong>
        <span><strong>✓</strong> = OK / Sesuai</span>
        <span><strong>✕</strong> = NG / Tidak Sesuai</span>
        <span><strong>–</strong> = NA / Tidak Dinilai</span>
        <span style="margin-left:14px;color:#555;">Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    {{-- DAFTAR ITEM MASALAH --}}
    @if($problems->count() > 0)
        <div class="section-title">Daftar Item Masalah (Kontrol Kepatuhan) — {{ $problems->count() }} Item</div>
        <table class="problem-table">
            <thead>
                <tr>
                    <th style="width:18px;">NO</th>
                    <th style="width:48px;">TGL TEMUAN</th>
                    <th>KONDISI ABNORMAL / ITEM MASALAH</th>
                    <th>TINDAKAN PERBAIKAN / ACTION</th>
                    <th style="width:70px;">PIC</th>
                    <th style="width:48px;">TARGET</th>
                    <th style="width:40px;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($problems as $pi => $prob)
                    <tr>
                        <td style="text-align:center;font-weight:700;">{{ $pi+1 }}</td>
                        <td style="text-align:center;">{{ $prob->problem_date ? $prob->problem_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $prob->problem_description }}</td>
                        <td>{{ $prob->corrective_action ?: '-' }}</td>
                        <td style="font-weight:600;">{{ $prob->pic_name ?: '-' }}</td>
                        <td style="text-align:center;">{{ $prob->target_date ? $prob->target_date->format('d/m/Y') : '-' }}</td>
                        <td style="text-align:center;">
                            @if($prob->status==='Closed')
                                <span class="badge-closed">Closed</span>
                            @elseif($prob->status==='In Progress')
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

</div>{{-- end .print-wrapper --}}

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
                window.print();
            }
        }, 600);
    });
</script>
</body>
</html>
