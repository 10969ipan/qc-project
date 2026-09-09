<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('first_piece_approval', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet First Piece Approval</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            color: #333;
            margin: 0;
            padding: 10mm 10mm 5mm 10mm;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .logo { width: 90px; text-align: center; }
        .title { text-align: center; font-size: 13px; font-weight: bold; color: #000; }
        .doc-info { width: 160px; font-size: 8.5px; }
        .doc-info table { width: 100%; border: none; }
        .doc-info td { border: none; padding: 1px 2px; }
        .sub-header { margin-bottom: 8px; font-size: 9px; }

        .table { width: 100%; border-collapse: collapse; table-layout: auto; }
        thead { display: table-header-group; }

        .table th {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
            white-space: nowrap;
        }
        .table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
            word-wrap: break-word;
        }
        tbody tr { page-break-inside: avoid; break-inside: avoid; }

        .dimension-table { width: 100%; border-collapse: collapse; margin: 0; }
        .dimension-table td, .dimension-table th {
            padding: 1px !important;
            font-size: 5.5px;
            line-height: 1.1;
            border: 1px solid #000 !important;
            text-align: center;
        }
        .dimension-table th { background-color: #f2f2f2 !important; font-weight: bold; }

        .badge { display: inline-block; padding: .2em .3em; font-size: 70%; font-weight: 700; line-height: 1; text-align: center; border-radius: .2rem; }
        .badge-success { color: #fff; background-color: #28a745; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-danger { color: #fff; background-color: #dc3545; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .text-success { color: #28a745; }
        .text-danger  { color: #dc3545; }
        .text-uppercase { text-transform: uppercase; }
        .col-compact { white-space: nowrap; width: 1%; }
        .w-barang { width: 12%; }
        .w-part-no { width: 10%; }
        .w-cust { width: 10%; }
        .w-dimensi { width: 38%; }
        .w-ket { width: 7%; }
        .print-footer { margin-top: 8mm; font-size: 7.5px; color: #666; }
    </style>
</head>
<body>

    @php
        $docHeader = \App\Models\GeneralSetting::getDocHeader('first_piece_approval', $plantCode, [
            'no_dokumen' => $plantCode === 'jakarta' ? 'QC-JKT-F-032/0' : 'QC-KRW-F-0207',
            'tgl_terbit' => $plantCode === 'jakarta' ? '21.02.2023' : '25/03/2015',
            'revisi'     => $plantCode === 'jakarta' ? '1 / 14.06.2023' : '3 / 22/12/2025',
            'halaman'    => '1/1'
        ]);
    @endphp
    {{-- Header: Logo | Judul | Info Dokumen + Signatures --}}
    <table class="header-table" style="width:100%; border-collapse:collapse; border:1px solid #000; margin-bottom:8px;">
        <tr>
            <td class="logo" style="width:75px; border:1px solid #000; padding:5px; text-align:center; vertical-align:middle;">
                <img src="{{ asset('master item/ipp.jpg') }}" style="max-width:58px; max-height:44px; object-fit:contain;">
            </td>
            <td class="title" style="border:1px solid #000; padding:5px 8px; text-align:center; vertical-align:middle; font-weight:bold; font-size:10pt; color:#000; text-transform:uppercase;">
                {{ $docHeader['judul'] ?? 'LAPORAN DATA CHECKSHEET FIRST PIECE APPROVAL' }}
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
                                                06-Jan-26
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

    <div class="sub-header">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}<br>
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <td colspan="{{ auth()->check() && auth()->user()->role === 'admin' ? 20 : 19 }}" style="height:4mm; border:none; padding:0; background:#fff;"></td>
            </tr>
            <tr>
                <th rowspan="2" class="col-compact">No</th>
                <th rowspan="2" class="col-compact">Tgl</th>
                <th rowspan="2" class="col-compact">Jam (Before)</th>
                <th rowspan="2" class="col-compact">Jam (After)</th>
                <th rowspan="2" class="col-compact">Cycle</th>
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <th rowspan="2" class="col-compact">No Mesin</th>
                @endif
                <th rowspan="2" class="col-compact">Shift</th>
                <th rowspan="2" class="w-barang">Barang</th>
                <th rowspan="2" class="w-part-no">Part No</th>
                <th rowspan="2" class="w-cust">Customer</th>
                <th rowspan="2" class="col-compact">Total</th>
                <th rowspan="2" class="col-compact">Sample</th>
                <th rowspan="2" class="w-dimensi">Check Dimensi</th>
                <th rowspan="2" class="col-compact">Berat</th>
                <th rowspan="2" class="col-compact">OK</th>
                <th rowspan="2" class="col-compact">NG</th>
                <th colspan="2" class="col-compact">Detail NG</th>
                <th rowspan="2" class="col-compact">Judgment</th>
                <th rowspan="2" class="col-compact">Inisial</th>
                <th rowspan="2" class="w-ket">Ket</th>
            </tr>
            <tr>
                <th class="col-compact">Pcs</th>
                <th class="col-compact">Jenis NG</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $checksheet)
                @php
                    $defectsData = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
                    $pcsLines  = [];
                    $nameLines = [];
                    if (is_array($defectsData)) {
                        foreach ($defectsData as $d) {
                            if (is_array($d) && isset($d['type'])) { $pcsLines[] = $d['qty'] ?? 1; $nameLines[] = $d['type']; }
                            elseif (is_string($d)) { $pcsLines[] = 1; $nameLines[] = $d; }
                        }
                    }
                    $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true);
                    $dimensions = $dimensions ?: [];
                    $itemStandardsRaw = $checksheet->item->dimension_standards ?? null;
                    $standards = [];
                    if (!empty($itemStandardsRaw) && is_array($itemStandardsRaw)) {
                        foreach ($itemStandardsRaw as $idx => $std) {
                            if (is_array($std)) {
                                $pKey = (string)($std['point'] ?? ($idx + 1));
                                $standards[$pKey] = [
                                    'size' => $std['size'] ?? null,
                                    'tolerance' => $std['tolerance'] ?? null,
                                    'min' => $std['min'] ?? null,
                                    'max' => $std['max'] ?? null,
                                ];
                            }
                        }
                    }
                    if (empty($standards)) {
                        $itemPartNumber = strtoupper(str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', str_replace(["\xe2\x80\x92", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"], '-', $checksheet->item->part_number ?? '')));
                        $standards = $partDimensionStandards[$itemPartNumber] ?? [];
                    }
                    $activePoints = [];
                    foreach ($dimensions as $cavKey => $points) {
                        if (is_array($points)) {
                            foreach ($points as $pKey => $pVal) {
                                if ($pVal !== null && $pVal !== '' && $pVal !== '-' && $pVal !== 0 && $pVal !== '0') $activePoints[$pKey] = true;
                            }
                        }
                    }
                    foreach ($standards as $pKey => $std) { $activePoints[$pKey] = true; }
                    $activePoints = array_keys($activePoints);
                    sort($activePoints);
                    if (empty($activePoints)) { $activePoints = range(1, 5); }
                    $actualMaxCavity = 0;
                    foreach ($dimensions as $cavKey => $pts) {
                        $cavNum = (int) filter_var($cavKey, FILTER_SANITIZE_NUMBER_INT);
                        $actualMaxCavity = max($actualMaxCavity, $cavNum);
                    }
                    $displayMaxCavity = max(5, $actualMaxCavity);
                    $pointCount = count($activePoints);
                    $fontSize = 6.5;
                    if ($pointCount > 20) $fontSize = 4.5;
                    elseif ($pointCount > 10) $fontSize = 5.5;
                    $weights = is_array($checksheet->part_weight) ? $checksheet->part_weight
                        : (is_string($checksheet->part_weight) && str_starts_with($checksheet->part_weight, '[') ? json_decode($checksheet->part_weight, true)
                        : ($checksheet->part_weight ? [$checksheet->part_weight] : []));
                @endphp
                <tr>
                    <td class="col-compact">{{ $loop->iteration }}</td>
                    <td class="col-compact">{{ \Carbon\Carbon::parse($checksheet->date)->format('d/m/y') }}</td>
                    <td class="col-compact">{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                    <td class="col-compact">{{ $checksheet->created_at->format('H:i') }}</td>
                    <td class="col-compact">{{ $checksheet->cycle_time ?? '-' }}</td>
                    @if(auth()->check() && auth()->user()->role === 'admin')
                        <td class="col-compact">{{ $checksheet->code_machine ?? '-' }}</td>
                    @endif
                    <td class="col-compact">{{ $checksheet->shift }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->name ?? '-' }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->customer ?? '-' }}</td>
                    <td class="col-compact">{{ $checksheet->total_qty }}</td>
                    <td class="col-compact">{{ $checksheet->sampling_qty }}</td>
                    <td style="padding:0; vertical-align:top;">
                        @if(count($dimensions) > 0)
                            <table class="dimension-table" style="table-layout:fixed; width:100%; font-size:{{ $fontSize }}px;">
                                <thead>
                                    <tr>
                                        <th style="width:10%;">Cav</th>
                                        @foreach($activePoints as $j) <th>Ø{{ $j }}</th> @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 1; $i <= $displayMaxCavity; $i++)
                                        @php
                                            $rowHasData = false;
                                            foreach ($activePoints as $j) {
                                                $val = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null));
                                                if ($val !== null && $val !== '' && $val !== '-' && $val !== 0 && $val !== '0') { $rowHasData = true; break; }
                                            }
                                        @endphp
                                        @if($rowHasData)
                                            <tr>
                                                <td style="font-weight:bold; background:#f9f9f9;">{{ $i }}</td>
                                                @foreach($activePoints as $j)
                                                    @php
                                                        $val = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? '-'));
                                                        $isNG = false;
                                                        
                                                        // Robust lookup for standard in PHP
                                                        $std = null;
                                                        if (!empty($standards)) {
                                                            if (isset($standards[$j])) {
                                                                $std = $standards[$j];
                                                            } else {
                                                                // Fallback for array structure if needed
                                                                foreach ($standards as $itemStd) {
                                                                    if (isset($itemStd['point']) && (string)$itemStd['point'] === (string)$j) {
                                                                        $std = $itemStd;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($std && is_numeric($val)) {
                                                            $fVal = (float)$val;
                                                            $epsilon = 0.00001;

                                                            // 1. Check Absolute Min/Max
                                                            if (($std['min'] ?? null) !== null && $std['min'] !== '') {
                                                                $minBound = (float)$std['min'];
                                                                if ($fVal < ($minBound - $epsilon)) $isNG = true;
                                                            }
                                                            if (!$isNG && ($std['max'] ?? null) !== null && $std['max'] !== '') {
                                                                $maxBound = (float)$std['max'];
                                                                if ($fVal > ($maxBound + $epsilon)) $isNG = true;
                                                            }

                                                            // 2. Check Size +/- Tolerance
                                                            if (!$isNG && ($std['size'] ?? null) !== null && ($std['tolerance'] ?? null) !== null && $std['size'] !== '' && $std['tolerance'] !== '') {
                                                                $szStr = (string)$std['size'];
                                                                if (!str_starts_with($szStr, '+') && !str_starts_with($szStr, '-')) {
                                                                    $base = (float)$szStr;
                                                                    $tol = (string)$std['tolerance'];
                                                                    $lb = $base; $ub = $base;
                                                                    
                                                                    if (str_contains($tol, '/')) {
                                                                        $parts = explode('/', $tol);
                                                                        foreach ($parts as $p) {
                                                                            $p = trim(str_replace(',', '.', $p));
                                                                            $fv = (float)$p;
                                                                            if (str_starts_with($p, '+') || $fv > 0) $ub = $base + abs($fv);
                                                                            elseif (str_starts_with($p, '-') || $fv < 0) $lb = $base - abs($fv);
                                                                        }
                                                                    } elseif (str_starts_with($tol, '+')) {
                                                                        $ub = $base + (float)substr($tol, 1);
                                                                    } elseif (str_starts_with($tol, '-')) {
                                                                        $lb = $base + (float)$tol;
                                                                    } else {
                                                                        $tv = (float)$tol;
                                                                        $lb = $base - $tv; $ub = $base + $tv;
                                                                    }
                                                                    
                                                                    if ($fVal < ($lb - $epsilon) || $fVal > ($ub + $epsilon)) $isNG = true;
                                                                }
                                                            }

                                                            // 3. Check Special Size (prefix)
                                                            if (!$isNG && ($std['size'] ?? null) !== null && $std['size'] !== '') {
                                                                $szStr = (string)$std['size'];
                                                                if (str_starts_with($szStr, '+') || str_starts_with($szStr, '-')) {
                                                                    $op = $szStr[0];
                                                                    $bound = (float)substr($szStr, 1);
                                                                    if ($op === '+' && $fVal < ($bound - $epsilon)) $isNG = true;
                                                                    elseif ($op === '-' && $fVal > ($bound + $epsilon)) $isNG = true;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <td @if($isNG) style="color:#dc3545; font-weight:bold;" @endif>{{ $val }}</td>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endfor
                                </tbody>
                            </table>
                        @else
                            <div style="padding:3px; font-size:6px; color:#999;">-</div>
                        @endif
                    </td>
                    <td class="col-compact" style="white-space:nowrap; text-align:left; font-size:6px;">
                        @foreach(array_filter($weights ?? [], fn($w) => $w !== null && $w !== '') as $ci => $wv)
                            CAV{{ $ci+1 }}: {{ $wv }}gr<br>
                        @endforeach
                        @if(empty(array_filter($weights ?? [], fn($w) => $w !== null && $w !== ''))) - @endif
                    </td>
                    <td class="col-compact text-success">{{ $checksheet->total_ok }}</td>
                    <td class="col-compact text-danger">{{ $checksheet->total_ng }}</td>
                    <td class="col-compact text-danger" style="font-size:6px;">{!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}</td>
                    <td class="col-compact text-danger" style="font-size:6px;">{!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}</td>
                    <td class="col-compact">
                        <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">{{ $checksheet->judgment }}</span>
                    </td>
                    <td class="col-compact text-uppercase">{{ $checksheet->operator_initials ?? '-' }}</td>
                    <td style="text-align:left; font-size:6px;">{{ $checksheet->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="print-footer"><span id="footerDateTime"></span></div>

    <script>
        (function () {
            var now = new Date();
            var pad = function(n){ return n < 10 ? '0' + n : n; };
            document.getElementById('footerDateTime').textContent = 'Dicetak: '
                + pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear()
                + '  ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
        })();
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
