<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet In-Process</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

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
        tfoot { display: table-footer-group; }

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

        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Dimension nested table */
        .dimension-table { width: 100%; border-collapse: collapse; margin: 0; }
        .dimension-table td, .dimension-table th {
            padding: 1px !important;
            font-size: 5.5px;
            line-height: 1.1;
            border: 1px solid #000 !important;
            text-align: center;
        }
        .dimension-table th { background-color: #f2f2f2 !important; font-weight: bold; }

        .badge {
            display: inline-block;
            padding: .2em .3em;
            font-size: 70%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            border-radius: .2rem;
        }
        .badge-success { color: #fff; background-color: #28a745; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-danger  { color: #fff; background-color: #dc3545; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .text-success   { color: #28a745; }
        .text-danger    { color: #dc3545; }
        .text-uppercase { text-transform: uppercase; }

        .col-compact { white-space: nowrap; width: 1%; }
        .w-barang    { width: 12%; }
        .w-part-no   { width: 10%; }
        .w-cust      { width: 10%; }
        .w-dimensi   { width: 38%; }
        .w-ket       { width: 7%; }

        .print-footer { margin-top: 8mm; font-size: 7.5px; color: #666; }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ asset('master item/ipp.jpg') }}"
                     style="max-width: 75px; max-height: 55px; object-fit: contain;">
            </td>
            <td class="title">LAPORAN CHECK SHEET IN-PROCESS</td>
            <td class="doc-info">
                <table>
                    <tr><td>No. Dokumen</td><td>: {{ $plantCode === 'jakarta' ? 'QC-JKT-F-032/0' : 'QC-KRW-F-0212' }}</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: {{ $plantCode === 'jakarta' ? '21.02.2023' : '25/03/2015' }}</td></tr>
                    <tr><td>Revisi / Tgl</td><td>: {{ $plantCode === 'jakarta' ? '1 / 14.06.2023' : '3 / 22/12/2025' }}</td></tr>
                    <tr><td>Halaman</td><td>: 1/1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="sub-header">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
        @if(request('item_id'))
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Part:</strong> {{ \App\Models\Item::find(request('item_id'))->name ?? request('item_id') }}
        @endif
        @if(request('customer'))
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Cust:</strong> {{ request('customer') }}
        @endif
        @if(request('operator_initials'))
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Inisial:</strong> {{ request('operator_initials') }}
        @endif
        @if(request('search')) &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Search:</strong> "{{ request('search') }}" @endif
        @if(request('entry_method')) &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Tipe:</strong> {{ request('entry_method') === 'verification' ? 'Verification' : 'Regular' }} @endif
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Total Data:</strong> {{ $checksheets->count() }} baris
    </div>

    <table class="table">
        <thead>
            <tr>
                <td colspan="17" style="height:4mm; border:none; padding:0; background:#fff;"></td>
            </tr>
            <tr>
                <th rowspan="2" class="col-compact">No</th>
                <th rowspan="2" class="col-compact">Tgl</th>
                <th rowspan="2" class="col-compact">Jam (Before)</th>
                <th rowspan="2" class="col-compact">Jam (After)</th>
                <th rowspan="2" class="col-compact">Cycle</th>
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
                @php
                    $defectsData = is_array($checksheet->defects)
                        ? $checksheet->defects
                        : json_decode($checksheet->defects, true);
                    $pcsLines  = [];
                    $nameLines = [];
                    if (is_array($defectsData)) {
                        foreach ($defectsData as $d) {
                            if (is_array($d) && isset($d['type'])) {
                                $pcsLines[]  = $d['qty'] ?? 1;
                                $nameLines[] = $d['type'];
                            } elseif (is_string($d)) {
                                $pcsLines[]  = 1;
                                $nameLines[] = $d;
                            }
                        }
                    }

                    $dimensions = is_array($checksheet->dimension_check)
                        ? $checksheet->dimension_check
                        : json_decode($checksheet->dimension_check, true);
                    $dimensions = $dimensions ?: [];

                    $itemPartNumber = strtoupper(str_replace(
                        [' ', "\xc2\xa0", "\t", "\n", "\r"],
                        '',
                        str_replace(["\xe2\x80\x92", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"], '-', $checksheet->item->part_number ?? '')
                    ));
                    $standards = $partDimensionStandards[$itemPartNumber] ?? [];

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

                    $weights = is_array($checksheet->part_weight)
                        ? $checksheet->part_weight
                        : (is_string($checksheet->part_weight) && str_starts_with($checksheet->part_weight, '[')
                            ? json_decode($checksheet->part_weight, true)
                            : ($checksheet->part_weight ? [$checksheet->part_weight] : []));
                @endphp
                <tr>
                    <td class="col-compact">{{ $loop->iteration }}</td>
                    <td class="col-compact">{{ \Carbon\Carbon::parse($checksheet->date)->format('d/m/y') }}</td>
                    <td class="col-compact">{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                    <td class="col-compact">{{ $checksheet->created_at->format('H:i') }}</td>
                    <td class="col-compact">{{ $checksheet->cycle_time ?? '-' }}</td>
                    <td class="col-compact">{{ $checksheet->shift }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->name ?? '-' }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->customer ?? '-' }}</td>
                    <td class="col-compact">{{ $checksheet->total_qty }}</td>
                    <td class="col-compact">{{ $checksheet->sampling_qty }}</td>

                    {{-- Check Dimensi --}}
                    <td style="padding:0; vertical-align:top;">
                        @if(count($dimensions) > 0)
                            <table class="dimension-table"
                                style="table-layout:fixed; width:100%; font-size:{{ $fontSize }}px;">
                                <thead>
                                    <tr>
                                        <th style="width:10%;">Cav</th>
                                        @foreach($activePoints as $j)
                                            <th>Ø{{ $j }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 1; $i <= $displayMaxCavity; $i++)
                                        @php
                                            $rowHasData = false;
                                            foreach ($activePoints as $j) {
                                                $val = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null));
                                                if ($val !== null && $val !== '' && $val !== '-' && $val !== 0 && $val !== '0') {
                                                    $rowHasData = true; break;
                                                }
                                            }
                                        @endphp
                                        @if($rowHasData)
                                            <tr>
                                                <td style="font-weight:bold; background:#f9f9f9;">{{ $i }}</td>
                                                @foreach($activePoints as $j)
                                                    @php
                                                        $val = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? '-'));
                                                        $isNG = false;
                                                        if (isset($standards[$j]) && is_numeric($val)) {
                                                            $std = $standards[$j]; $fVal = (float)$val; $eps = 0.00001;
                                                            
                                                            $baseSize = null;
                                                            if (isset($std['size']) && $std['size'] !== '' && !str_starts_with((string)$std['size'], '+') && !str_starts_with((string)$std['size'], '-')) {
                                                                $baseSize = (float)$std['size'];
                                                            }

                                                            $check = function($v, $s, $m, $baseSize) use ($eps) {
                                                                if ($s === null || $s === '') return false;
                                                                $sStr = (string)$s;
                                                                if (strlen($sStr) > 1 && (str_starts_with($sStr, '+') || str_starts_with($sStr, '-'))) {
                                                                    $op = $sStr[0]; $lim = (float)substr($sStr, 1);
                                                                    if ($baseSize !== null) {
                                                                        $bound = ($op === '+') ? $baseSize + $lim : $baseSize - $lim;
                                                                        return ($op === '+') ? $v > ($bound + $eps) : $v < ($bound - $eps);
                                                                    }
                                                                    return ($op === '+') ? $v < ($lim - $eps) : $v > ($lim + $eps);
                                                                }
                                                                $sf = (float)$s;
                                                                if ($baseSize !== null) {
                                                                    if ($m === 'min') return $v < ($baseSize - $sf - $eps);
                                                                    if ($m === 'max') return $v > ($baseSize + $sf + $eps);
                                                                }
                                                                if ($m === 'min') return $v < ($sf - $eps);
                                                                if ($m === 'max') return $v > ($sf + $eps);
                                                                return false;
                                                            };

                                                            if (($std['min'] ?? null) !== null && $check($fVal, $std['min'], 'min', $baseSize)) $isNG = true;
                                                            if (!$isNG && ($std['max'] ?? null) !== null && $check($fVal, $std['max'], 'max', $baseSize)) $isNG = true;
                                                            
                                                            if (!$isNG && isset($std['size']) && (str_starts_with((string)$std['size'], '+') || str_starts_with((string)$std['size'], '-'))) {
                                                                if ($check($fVal, $std['size'], 'size', $baseSize)) $isNG = true;
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

                    {{-- Berat Part --}}
                    <td class="col-compact" style="white-space:nowrap; text-align:left; font-size:6px;">
                        @foreach(array_filter($weights ?? [], fn($w) => $w !== null && $w !== '') as $ci => $wv)
                            CAV{{ $ci+1 }}: {{ $wv }}gr<br>
                        @endforeach
                        @if(empty(array_filter($weights ?? [], fn($w) => $w !== null && $w !== '')))
                            -
                        @endif
                    </td>

                    <td class="col-compact text-success">{{ $checksheet->total_ok }}</td>
                    <td class="col-compact text-danger">{{ $checksheet->total_ng }}</td>
                    <td class="col-compact text-danger" style="font-size:6px;">
                        {!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}
                    </td>
                    <td class="col-compact text-danger" style="font-size:6px;">
                        {!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}
                    </td>
                    <td class="col-compact">
                        <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $checksheet->judgment }}
                        </span>
                    </td>
                    <td class="col-compact text-uppercase">{{ $checksheet->user->initials ?? $checksheet->operator_initials ?? '-' }}</td>
                    <td style="text-align:left; font-size:6px;">{{ $checksheet->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="print-footer" id="printFooter">
        <span id="footerDateTime"></span>
    </div>

    <script>
        (function () {
            var now = new Date();
            var pad = function(n){ return n < 10 ? '0' + n : n; };
            var dateStr = pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear()
                        + '  ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
            document.getElementById('footerDateTime').textContent = 'Dicetak: ' + dateStr;
        })();

        window.onload = function () { window.print(); };
    </script>

</body>
</html>
