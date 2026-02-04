<!DOCTYPE html>
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
            table-layout: fixed;
            /* Fixed layout to respect widths */
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .table thead th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
            /* Slightly smaller header font */
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
            padding: 1px;
            font-size: 5px;
            line-height: 1;
            border: 1px solid #000;
            text-align: center;
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
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ public_path('master item/ipp.jpg') }}" style="max-width: 70px;">
            </td>
            <td class="title">LAPORAN CHECK SHEET INPROCESS</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-0004</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 25/09/2015</td>
                    </tr>
                    <tr>
                        <td>Revisi Ke</td>
                        <td>: 3</td>
                    </tr>
                    <tr>
                        <td>Tgl. Revisi</td>
                        <td>: 30/09/2020</td>
                    </tr>
                    <tr>
                        <td>Hal</td>
                        <td>: 1/1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 10px;">
        <strong>Periode:</strong>
        {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua' }} -
        {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua' }}
        <br>
        <strong>Barang:</strong> {{ $request->item_id ? $items->find($request->item_id)->name : 'Semua Barang' }}
        <br>
        <strong>Plant:</strong>
        {{ isset($plantCode) ? strtoupper($plantCode) : (isset($plantName) ? strtoupper($plantName) : 'KARAWANG') }}
    </div>

    <table class="table" style="table-layout: fixed; width: 100%;">
        <colgroup>
            <col style="width: 1.5%;"> <!-- No -->
            <col style="width: 8%;"> <!-- Tgl -->
            <col style="width: 4%;"> <!-- Jam Bef -->
            <col style="width: 4%;"> <!-- Jam Aft -->
            <col style="width: 2%;"> <!-- Cycle -->
            <col style="width: 2%;"> <!-- Shift -->
            <col style="width: 8%;"> <!-- Barang -->
            <col style="width: 8%;"> <!-- Part No -->
            <col style="width: 6%;"> <!-- Cust -->
            <col style="width: 2.5%;"> <!-- Total -->
            <col style="width: 2.5%;"> <!-- Smpl -->
            <col style="width: 30%;"> <!-- Check Dimensi -->
            <col style="width: 2%;"> <!-- OK -->
            <col style="width: 2%;"> <!-- NG -->
            <col style="width: 2%;"> <!-- Pcs -->
            <col style="width: 4%;"> <!-- Jenis NG -->
            <col style="width: 2.5%;"> <!-- Judg -->
            <col style="width: 2.5%;"> <!-- Insl -->
            <col style="width: 3%;"> <!-- Kashift -->
            <col style="width: 3%;"> <!-- SPV -->
            <col style="width: 3%;"> <!-- Asst. Mgr -->
            <col style="width: 5.5%;"> <!-- Ket -->
        </colgroup>
        <thead>
            <tr class="text-center">
                <th rowspan="2">No</th>
                <th rowspan="2">Tgl</th>
                <th rowspan="2">Jam (Bef)</th>
                <th rowspan="2">Jam (Aft)</th>
                <th rowspan="2">Cycle</th>
                <th rowspan="2">Shift</th>
                <th rowspan="2">Barang</th>
                <th rowspan="2">Part No</th>
                <th rowspan="2">Cust</th>
                <th rowspan="2">Total</th>
                <th rowspan="2">Smpl</th>
                <th rowspan="2">Check Dimensi</th>
                <th rowspan="2">OK</th>
                <th rowspan="2">NG</th>
                <th colspan="2">Detail NG</th>
                <th rowspan="2">Judg</th>
                <th rowspan="2">Insl</th>
                <th rowspan="2">Kashift</th>
                <th rowspan="2">SPV</th>
                <th rowspan="2">Asst. Mgr</th>
                <th rowspan="2">Ket</th>
            </tr>
            <tr>
                <th>Pcs</th>
                <th>Jenis NG</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $checksheet)
                <tr class="text-center">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->date)->format('d/m/y') }}</td>
                    <td>{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                    <td>{{ $checksheet->created_at->format('H:i') }}</td>
                    <td>{{ $checksheet->cycle_time ?? '-' }}</td>
                    <td>{{ $checksheet->shift }}</td>
                    <td>{{ $checksheet->item->name ?? '-' }}</td>
                    <td>{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td>{{ $checksheet->item->customer ?? '-' }}</td>
                    <td>{{ $checksheet->total_qty }}</td>
                    <td>{{ $checksheet->sampling_qty }}</td>

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
                                $fontSize = 5;
                                if ($pointCount > 20) $fontSize = 3.5;
                                elseif ($pointCount > 10) $fontSize = 4.5;
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
                                    <tr class="limit-row" style="background-color: #f8f8f8; font-size: {{ $fontSize * 0.8 }}px;">
                                        <th style="border-left: none; font-weight: normal;">Limit</th>
                                        @foreach ($activePoints as $j)
                                            <th style="font-weight: normal; color: #666;">
                                                @if(isset($standards[$j]))
                                                    @if($standards[$j]['min'] !== null && $standards[$j]['max'] !== null)
                                                        {{ $standards[$j]['min'] }}-{{ $standards[$j]['max'] }}
                                                    @else
                                                        {{ $standards[$j]['size'] }}±{{ $standards[$j]['tolerance'] }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= $displayMaxCavity; $i++)
                                        <tr>
                                            <td style="background-color: #f9f9f9; border-left: none; text-align: center; font-weight: bold; @if($i == $displayMaxCavity) border-bottom: none; @endif">{{ $i }}</td>
                                            @foreach ($activePoints as $j)
                                                @php
                                                    $val = $dimensions['cav'.$i][$j] ?? ($dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? '-'));
                                                    $isNG = false;
                                                    if (isset($standards[$j]) && is_numeric($val)) {
                                                        $std = $standards[$j];
                                                        if ($std['min'] !== null && $val < $std['min']) $isNG = true;
                                                        if ($std['max'] !== null && $val > $std['max']) $isNG = true;
                                                        if (!$isNG && $std['min'] === null && $std['max'] === null) {
                                                            if ($std['size'] !== null && $std['tolerance'] !== null) {
                                                                $min = $std['size'] - $std['tolerance'];
                                                                $max = $std['size'] + $std['tolerance'];
                                                                if ($val < $min || $val > $max) $isNG = true;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <td style="@if($isNG) color: #dc3545; font-weight: bold; background-color: #ffeef0; @endif @if($i == $displayMaxCavity) border-bottom: none; @endif @if($loop->last) border-right: none; @endif; text-align: center;">
                                                    {{ $val }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        @else
                            <div style="padding: 5px;">-</div>
                        @endif
                    </td>

                    <td class="text-success">{{ $checksheet->total_ok }}</td>
                    <td class="text-danger">{{ $checksheet->total_ng }}</td>

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

                    <td class="text-danger">{!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}</td>
                    <td class="text-danger">{!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}</td>

                    <td>
                        <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $checksheet->judgment }}
                        </span>
                    </td>
                    <td>{{ $checksheet->operator_initials }}</td>

                    <td>
                        @if($checksheet->kashift_qc === 'REJECTED')
                            <span class="badge badge-danger">REJECTED</span>
                        @elseif($checksheet->kashift_qc)
                            <span class="badge badge-success">APPROVED</span>
                        @else
                            <span class="badge badge-warning">PENDING</span>
                        @endif
                    </td>
                    <td>
                        @if($checksheet->supervisor_qc === 'REJECTED')
                            <span class="badge badge-danger">REJECTED</span>
                        @elseif($checksheet->supervisor_qc)
                            <span class="badge badge-success">APPROVED</span>
                        @else
                            <span class="badge badge-warning">PENDING</span>
                        @endif
                    </td>
                    <td>
                        @if($checksheet->asst_manager_qc === 'REJECTED')
                            <span class="badge badge-danger">REJECTED</span>
                        @elseif($checksheet->asst_manager_qc)
                            <span class="badge badge-success">APPROVED</span>
                        @else
                            <span class="badge badge-warning">PENDING</span>
                        @endif
                    </td>

                    <td>{{ $checksheet->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>