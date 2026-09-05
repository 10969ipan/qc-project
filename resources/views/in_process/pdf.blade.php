<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('in_process', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Inprocess</title>
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
        .w-dimensi { width: 41%; }
        .w-weight { width: 4%; }
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
            <td class="title">LAPORAN CHECK SHEET IN-PROCESS</td>
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
        @if(request('item_id')) | <strong>Part:</strong> {{ \App\Models\Item::find(request('item_id'))->name ?? request('item_id') }} @endif
        @if(request('customer')) | <strong>Cust:</strong> {{ request('customer') }} @endif
        @if(request('operator_initials')) | <strong>Inisial:</strong> {{ request('operator_initials') }} @endif
        @if(request('search')) | <strong>Search:</strong> "{{ request('search') }}" @endif
        @if(request('entry_method')) | <strong>Tipe:</strong> {{ request('entry_method') === 'verification' ? 'Verification' : 'Regular' }} @endif
        | <strong>Total:</strong> {{ $checksheets->count() }} baris
    </div>

    <table class="table">
        <thead>
            <tr class="text-center">
                <th rowspan="2" class="col-compact">No</th>
                <th rowspan="2" class="col-compact">Tgl</th>
                <th rowspan="2" class="col-compact">Jam (Bef)</th>
                <th rowspan="2" class="col-compact">Jam (Aft)</th>
                <th rowspan="2" class="col-compact">Cycle</th>
                <th rowspan="2" class="col-compact">Shift</th>
                <th rowspan="2" class="w-barang">Barang</th>
                <th rowspan="2" class="w-part-no">Part No</th>
                <th rowspan="2" class="w-cust">Cust</th>
                <th rowspan="2" class="col-compact">Total</th>
                <th rowspan="2" class="col-compact">Smpl</th>
                <th rowspan="2" class="w-dimensi">Check Dimensi</th>
                <th rowspan="2" class="w-weight">Berat Part</th>
                <th rowspan="2" class="col-compact">OK</th>
                <th rowspan="2" class="col-compact">NG</th>
                <th colspan="2" class="col-compact">Detail NG</th>
                <th rowspan="2" class="col-compact">Judg</th>
                <th rowspan="2" class="col-compact">Inspector</th>
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
                                $itemPartNumber = str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', str_replace(["\xe2\x80\x92", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"], '-', $checksheet->item->part_number ?? ''));
                                $itemPartNumber = strtoupper($itemPartNumber);
                                $standards = $partDimensionStandards[$itemPartNumber] ?? [];
                            }

                            // Find active points
                            $activePoints = [];
                            foreach ($dimensions as $cavKey => $points) {
                                if (is_array($points)) {
                                    foreach ($points as $pKey => $pVal) {
                                        if (is_array($pVal)) {
                                            foreach ($pVal as $subV) {
                                                if ($subV !== null && $subV !== '' && $subV !== '-' && $subV !== 0 && $subV !== '0') {
                                                    $activePoints[$pKey] = true;
                                                    break;
                                                }
                                            }
                                        } else {
                                            if ($pVal !== null && $pVal !== '' && $pVal !== '-' && $pVal !== 0 && $pVal !== '0') {
                                                $activePoints[$pKey] = true;
                                            }
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

                            $checkValIsNG = function($val, $std) {
                                if ($val === null || $val === '' || $val === '-' || !is_numeric($val) || empty($std)) return false;
                                $fVal = (float)$val;
                                $epsilon = 0.00001;
                                if (($std['min'] ?? null) !== null && $fVal < ((float)$std['min'] - $epsilon)) return true;
                                if (($std['max'] ?? null) !== null && $fVal > ((float)$std['max'] + $epsilon)) return true;
                                return false;
                            };

                            $hasShoot1Data = false;
                            for($i = 1; $i <= $displayMaxCavity; $i++) {
                                foreach ($activePoints as $j) {
                                    $valCheck = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null));
                                    $v1 = is_array($valCheck) ? ($valCheck['p1'] ?? ($valCheck['s1'] ?? ($valCheck[0] ?? null))) : $valCheck;
                                    if ($v1 !== null && $v1 !== '' && $v1 !== '-' && $v1 !== 0 && $v1 !== '0') {
                                        $hasShoot1Data = true; break 2;
                                    }
                                }
                            }

                            $hasShoot2Data = false;
                            for($i = 1; $i <= $displayMaxCavity; $i++) {
                                foreach ($activePoints as $j) {
                                    $valCheck = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null));
                                    $v2 = is_array($valCheck) ? ($valCheck['p2'] ?? ($valCheck['s2'] ?? ($valCheck[1] ?? null))) : null;
                                    if ($v2 !== null && $v2 !== '' && $v2 !== '-' && $v2 !== 0 && $v2 !== '0') {
                                        $hasShoot2Data = true; break 2;
                                    }
                                }
                            }
                        @endphp
                        @if(count($dimensions) > 0 || $displayMaxCavity > 0)
                            @php
                                $pointCount = count($activePoints);
                                $pointWidth = $pointCount > 0 ? (90 / $pointCount) : 0;
                                $fontSize = 6.5;
                                if ($pointCount > 20) $fontSize = 4.5;
                                elseif ($pointCount > 10) $fontSize = 5.5;

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

                            @if($hasShoot1Data)
                                <div style="font-weight: bold; font-size: {{ $fontSize }}px; background-color: #f2f2f2; text-align: center; border-bottom: 1px solid #000; padding: 1px;">Shoot 1</div>
                                <table class="dimension-table"
                                    style="table-layout: fixed; width: 100%; border-collapse: collapse; border: none; font-size: {{ $fontSize }}px; margin-bottom: 2px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%; border-top: none; border-left: none;">Cav</th>
                                            @foreach ($activePoints as $j)
                                                <th style="width: {{ $pointWidth }}%; border-top: none;">Ø{{ $j }}</th>
                                            @endforeach
                                        </tr>
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
                                                $rowHasData1 = false;
                                                foreach ($activePoints as $j) {
                                                    $valCheck = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null));
                                                    $v1 = is_array($valCheck) ? ($valCheck['p1'] ?? ($valCheck['s1'] ?? ($valCheck[0] ?? null))) : $valCheck;
                                                    if ($v1 !== null && $v1 !== '' && $v1 !== '-' && $v1 !== 0 && $v1 !== '0') {
                                                        $rowHasData1 = true; break;
                                                    }
                                                }
                                            @endphp
                                            @if($rowHasData1)
                                            <tr>
                                                <td style="background-color: #f9f9f9; border-left: none; text-align: center; font-weight: bold;">{{ $i }}</td>
                                                @foreach ($activePoints as $j)
                                                    @php
                                                        $valRaw = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? '-'));
                                                        $val1 = is_array($valRaw) ? ($valRaw['p1'] ?? ($valRaw['s1'] ?? ($valRaw[0] ?? '-'))) : $valRaw;
                                                        $std = $standards[$j] ?? null;
                                                        $isNG1 = $checkValIsNG($val1, $std);
                                                    @endphp
                                                    <td style="@if($isNG1) color: red; font-weight: bold; background-color: #ffeef0; @endif text-align: center;">
                                                        {{ ($val1 !== '' && $val1 !== null) ? $val1 : '-' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                            @endif
                                        @endfor
                                    </tbody>
                                </table>
                            @endif

                            @if($hasShoot2Data)
                                <div style="font-weight: bold; font-size: {{ $fontSize }}px; background-color: #f2f2f2; text-align: center; border-bottom: 1px solid #000; border-top: 1px solid #000; padding: 1px;">Shoot 2</div>
                                <table class="dimension-table"
                                    style="table-layout: fixed; width: 100%; border-collapse: collapse; border: none; font-size: {{ $fontSize }}px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%; border-top: none; border-left: none;">Cav</th>
                                            @foreach ($activePoints as $j)
                                                <th style="width: {{ $pointWidth }}%; border-top: none;">Ø{{ $j }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($i = 1; $i <= $displayMaxCavity; $i++)
                                            @php
                                                $rowHasData2 = false;
                                                foreach ($activePoints as $j) {
                                                    $valCheck = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null));
                                                    $v2 = is_array($valCheck) ? ($valCheck['p2'] ?? ($valCheck['s2'] ?? ($valCheck[1] ?? null))) : null;
                                                    if ($v2 !== null && $v2 !== '' && $v2 !== '-' && $v2 !== 0 && $v2 !== '0') {
                                                        $rowHasData2 = true; break;
                                                    }
                                                }
                                            @endphp
                                            @if($rowHasData2)
                                            <tr>
                                                <td style="background-color: #f9f9f9; border-left: none; text-align: center; font-weight: bold;">{{ $i }}</td>
                                                @foreach ($activePoints as $j)
                                                    @php
                                                        $valRaw = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? '-'));
                                                        $val2 = is_array($valRaw) ? ($valRaw['p2'] ?? ($valRaw['s2'] ?? ($valRaw[1] ?? '-'))) : '-';
                                                        $std = $standards[$j] ?? null;
                                                        $isNG2 = $checkValIsNG($val2, $std);
                                                    @endphp
                                                    <td style="@if($isNG2) color: red; font-weight: bold; background-color: #ffeef0; @endif text-align: center;">
                                                        {{ ($val2 !== '' && $val2 !== null) ? $val2 : '-' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                            @endif
                                        @endfor
                                    </tbody>
                                </table>
                            @endif

                            @if(!$hasShoot1Data && !$hasShoot2Data)
                                <div style="text-align: center; font-size: 6px; padding: 2px;">-</div>
                            @endif
                        @else
                            <div style="text-align: center; font-size: 6px; padding: 2px;">-</div>
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
                    <td class="col-compact text-uppercase">{{ $checksheet->user->initials ?? $checksheet->operator_initials ?? '-' }}</td>


                    <td>{{ $checksheet->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
