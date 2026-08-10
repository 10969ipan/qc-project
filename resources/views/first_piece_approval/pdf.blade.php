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
        @page {
            margin: 10px;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: auto;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .table thead th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
            white-space: nowrap;
        }

        /* Large font for info columns */
        .col-info {
            font-size: 8px !important;
            text-align: left !important;
            padding-left: 4px !important;
            padding-right: 4px !important;
            word-wrap: break-word;
        }

        /* Fixed column widths */
        .w-barang { width: 13%; }
        .w-part-no { width: 12%; }
        .w-cust { width: 12%; }
        .w-dimensi { width: 37%; }
        .w-berat { width: 8%; }
        .w-ket { width: 8%; }

        /* Compact columns: prevent expansion beyond header text */
        .col-compact {
            white-space: nowrap;
            width: 1%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .logo {
            width: 80px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        .doc-info {
            width: 150px;
            font-size: 9px;
            text-align: left;
        }

        .doc-info table {
            width: 100%;
            border: none;
        }

        .doc-info td {
            border: none;
            padding: 1px 2px;
            text-align: left;
        }

        /* Dimension table specific tweaks */
        .dimension-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .dimension-table td,
        .dimension-table th {
            padding: 2px !important;
            font-size: 6px;
            line-height: 1.2;
            border: 1px solid #000 !important;
            text-align: center;
        }

        .dimension-table th {
            background-color: #f2f2f2 !important;
            font-weight: bold;
        }

        .dimension-table tr.limit-row th {
            font-size: 4px;
            /* Even smaller for limits */
            color: #555;
            font-weight: normal;
        }

        /* Helpers */
        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .badge {
            display: inline-block;
            padding: .25em .4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }

        .badge-success {
            color: #fff;
            background-color: #28a745;
        }

        .badge-danger {
            color: #fff;
            background-color: #dc3545;
        }

        .badge-warning {
            color: #212529;
            background-color: #ffc107;
        }

        .text-uppercase {
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo"><img src="{{ public_path('master item/ipp.jpg') }}" style="max-width: 60px;"></td>
            <td class="title">LAPORAN DATA CHECKSHEET FIRST PIECE APPROVAL</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-0201</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 01/01/2026</td>
                    </tr>
                    <tr>
                        <td>Revisi</td>
                        <td>: 0</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 5px; font-size: 10px;">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }} |
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
    </div>

    <table class="table">
        <thead>
            <tr class="text-center">
                <th rowspan="2" class="col-compact">No</th>
                <th rowspan="2" class="col-compact">Tgl</th>
                <th rowspan="2" class="col-compact">Jam (Bef)</th>
                <th rowspan="2" class="col-compact">Jam (Aft)</th>
                <th rowspan="2" class="col-compact">Cycle</th>
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <th rowspan="2" class="col-compact">No Mesin</th>
                @endif
                <th rowspan="2" class="col-compact">Shift</th>
                <th rowspan="2" class="w-barang">Barang</th>
                <th rowspan="2" class="w-part-no">Part No</th>
                <th rowspan="2" class="w-cust">Cust</th>
                <th rowspan="2" class="col-compact">Total</th>
                <th rowspan="2" class="col-compact">Smpl</th>
                <th rowspan="2" class="w-dimensi">Check Dimensi</th>
                <th rowspan="2" class="w-berat">Berat Part</th>
                <th rowspan="2" class="col-compact">OK</th>
                <th rowspan="2" class="col-compact">NG</th>
                <th colspan="2" class="col-compact">Detail NG</th>
                <th rowspan="2" class="col-compact">Judg</th>
                <th rowspan="2" class="col-compact">Insl</th>
                <th rowspan="2" class="w-ket">Ket</th>
            </tr>
            <tr>
                <th class="col-compact">Pcs</th>
                <th class="col-compact">Jenis NG</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $checksheet)
                <tr class="text-center">
                    <td class="col-compact">{{ $loop->iteration }}</td>
                    <td class="col-compact">{{ \Carbon\Carbon::parse($checksheet->date)->format('d/m/y') }}</td>
                    <td class="col-compact">{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                    <td class="col-compact">{{ $checksheet->created_at->format('H:i') }}</td>
                    <td class="col-compact">{{ $checksheet->cycle_time ?? '-' }}</td>
                    @if(auth()->check() && auth()->user()->role === 'admin')
                        <td class="col-compact">{{ $checksheet->code_machine ?? '-' }}</td>
                    @endif
                    <td class="col-compact">{{ $checksheet->shift }}</td>
                    <td class="col-info">{{ $checksheet->item->name ?? '-' }}</td>
                    <td class="col-info">{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td class="col-info">{{ $checksheet->item->customer ?? '-' }}</td>
                    <td class="col-compact">{{ $checksheet->total_qty }}</td>
                    <td class="col-compact">{{ $checksheet->sampling_qty }}</td>

                    <td style="padding: 0; vertical-align: top;">
                        @php
                            $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check :
                                json_decode($checksheet->dimension_check, true);
                            $dimensions = $dimensions ?: [];
                            $itemPartNumber = str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', str_replace(["\xe2\x80\x92", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"], '-', $checksheet->item->part_number ?? ''));
                            $itemPartNumber = strtoupper($itemPartNumber);
                            $standards = $partDimensionStandards[$itemPartNumber] ?? [];

                            // Find active points
                            $activePoints = [];
                            foreach ($dimensions as $cavKey => $points) {
                                if (is_array($points)) {
                                    foreach ($points as $pKey => $pVal) {
                                        if ($pVal !== null && $pVal !== '' && $pVal !== '-' && $pVal !== 0 && $pVal !== '0') {
                                            $activePoints[$pKey] = true;
                                        }
                                    }
                                }
                            }
                            foreach ($standards as $pKey => $std) {
                                $activePoints[$pKey] = true;
                            }
                            $activePoints = array_keys($activePoints);
                            sort($activePoints);

                            if (empty($activePoints)) {
                                $activePoints = range(1, 5);
                            }

                            $actualMaxCavity = 0;
                            foreach ($dimensions as $cavKey => $points) {
                                $cavNum = (int) filter_var($cavKey, FILTER_SANITIZE_NUMBER_INT);
                                $actualMaxCavity = max($actualMaxCavity, $cavNum);
                            }
                            $displayMaxCavity = max(5, $actualMaxCavity);
                        @endphp
                        @if(count($dimensions) > 0 || $displayMaxCavity > 0)
                            @php
                                $pointCount = count($activePoints);
                                $pointWidth = $pointCount > 0 ? (90 / $pointCount) : 0;
                                $fontSize = 6.5;
                                if ($pointCount > 20) $fontSize = 4.5;
                                elseif ($pointCount > 10) $fontSize = 5.5;
                            @endphp
                            <table class="dimension-table"
                                style="table-layout: fixed; width: 100%; border-collapse: collapse; border: none; font-size: {{ $fontSize }}px;">
                                <thead>
                                    <tr>
                                        <th style="width: 10%; border-top: none; border-left: none;">Cav</th>
                                        @foreach ($activePoints as $j)
                                            <th style="width: {{ $pointWidth }}%; border-top: none;">Ø{{ $j }}</th>
                                        @endforeach
                                    </tr>
                                    @php
                                        // Check which metadata rows have data
                                        $hasStdData = false;
                                        $hasMinMaxData = false;
                                        $hasTolData = false;
                                        foreach ($activePoints as $j) {
                                            if (isset($standards[$j])) {
                                                if ($standards[$j]['size'] !== null && $standards[$j]['size'] !== '' && $standards[$j]['size'] !== '-') $hasStdData = true;
                                                if (($standards[$j]['min'] !== null && $standards[$j]['min'] !== '' && $standards[$j]['min'] !== '-') || 
                                                    ($standards[$j]['max'] !== null && $standards[$j]['max'] !== '' && $standards[$j]['max'] !== '-')) $hasMinMaxData = true;
                                                if ($standards[$j]['tolerance'] !== null && $standards[$j]['tolerance'] !== '' && $standards[$j]['tolerance'] !== '-') $hasTolData = true;
                                            }
                                        }
                                    @endphp
                                    @if($hasStdData)
                                    <tr class="limit-row" style="background-color: #f8f8f8; font-size: {{ $fontSize * 0.8 }}px;">
                                        <th style="border-left: none; font-weight: normal;">Std</th>
                                        @foreach ($activePoints as $j)
                                            <th style="font-weight: normal; color: #666;">{{ isset($standards[$j]) ? $standards[$j]['size'] : '-' }}</th>
                                        @endforeach
                                    </tr>
                                    @endif
                                    @if($hasMinMaxData)
                                    <tr class="limit-row" style="background-color: #f8f8f8; font-size: {{ $fontSize * 0.8 }}px;">
                                        <th style="border-left: none; font-weight: normal;">Limit</th>
                                        @foreach ($activePoints as $j)
                                            <th style="font-weight: normal; color: #666;">
                                                @if(isset($standards[$j]))
                                                    @if($standards[$j]['min'] !== null && $standards[$j]['max'] !== null)
                                                        {{ $standards[$j]['min'] }}-{{ $standards[$j]['max'] }}
                                                    @elseif($standards[$j]['min'] !== null)
                                                        Min: {{ $standards[$j]['min'] }}
                                                    @elseif($standards[$j]['max'] !== null)
                                                        Max: {{ $standards[$j]['max'] }}
                                                    @else
                                                        -
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                    @endif
                                    @if($hasTolData)
                                    <tr class="limit-row" style="background-color: #f8f8f8; font-size: {{ $fontSize * 0.8 }}px;">
                                        <th style="border-left: none; font-weight: normal;">Tol</th>
                                        @foreach ($activePoints as $j)
                                            <th style="font-weight: normal; color: #666;">{{ isset($standards[$j]) ? '±' . $standards[$j]['tolerance'] : '-' }}</th>
                                        @endforeach
                                    </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= $displayMaxCavity; $i++)
                                        @php
                                            // Check if this cavity row has any actual data
                                            $rowHasData = false;
                                            foreach ($activePoints as $j) {
                                                $val = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null));
                                                if ($val !== null && $val !== '' && $val !== '-' && $val !== 0 && $val !== '0') {
                                                    $rowHasData = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if($rowHasData)
                                        <tr>
                                            <td style="background-color: #f9f9f9; border-left: none; text-align: center; font-weight: bold; @if($i == $displayMaxCavity) border-bottom: none; @endif">{{ $i }}</td>
                                            @foreach ($activePoints as $j)
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
                                                <td style="@if($isNG) color: #dc3545; font-weight: bold; background-color: #ffeef0; @endif @if($i == $displayMaxCavity) border-bottom: none; @endif @if($loop->last) border-right: none; @endif; text-align: center;">
                                                    {{ $val }}
                                                </td>
                                            @endforeach
                                        </tr>
                                        @endif
                                    @endfor
                                </tbody>
                            </table>
                        @else
                            <div style="padding: 5px;">-</div>
                        @endif
                    </td>
                    <td class="col-compact" style="white-space: nowrap; text-align: left;">
                        @php
                            $weights = is_array($checksheet->part_weight)
                                ? $checksheet->part_weight
                                : (is_string($checksheet->part_weight) && str_starts_with($checksheet->part_weight, '[')
                                    ? json_decode($checksheet->part_weight, true)
                                    : ($checksheet->part_weight ? [$checksheet->part_weight] : []));
                        @endphp
                        @forelse(array_filter($weights ?? [], fn($w) => $w !== null && $w !== '') as $ci => $wv)
                            CAV{{ $ci+1 }}: {{ $wv }}gr<br>
                        @empty
                            -
                        @endforelse
                    </td>

                    <td class="col-compact text-success">{{ $checksheet->total_ok }}</td>
                    <td class="col-compact text-danger">{{ $checksheet->total_ng }}</td>

                    @php
                        $defectsData = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
                        $pcsLines = [];
                        $nameLines = [];
                        if (is_array($defectsData)) {
                            foreach ($defectsData as $d) {
                                if (is_array($d) && isset($d['type'])) {
                                    $pcsLines[] = $d['qty'] ?? 1;
                                    $nameLines[] = $d['type'];
                                } elseif (is_string($d)) {
                                    $pcsLines[] = 1;
                                    $nameLines[] = $d;
                                }
                            }
                        }
                    @endphp

                    <td class="col-compact text-danger">{!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}</td>
                    <td class="col-compact text-danger">{!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}</td>

                    <td class="col-compact">
                        <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $checksheet->judgment }}
                        </span>
                    </td>
                    <td class="col-compact text-uppercase">{{ $checksheet->operator_initials }}</td>


                    <td>{{ $checksheet->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
