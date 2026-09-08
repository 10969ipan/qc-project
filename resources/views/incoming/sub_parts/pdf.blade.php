<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_sub_parts', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Incoming Sub-Part</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 7px;
            margin: 0;
            padding: 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .table thead th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .logo {
            width: 70px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }

        .doc-info {
            width: 130px;
            font-size: 8px;
        }

        .doc-info table {
            width: 100%;
            border: none;
        }

        .doc-info td {
            border: none;
            padding: 1px;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .badge {
            display: inline-block;
            padding: .1em .3em;
            font-weight: 700;
            border-radius: .2rem;
        }

        .badge-success {
            color: #fff;
            background-color: #28a745;
        }

        .badge-danger {
            color: #fff;
            background-color: #dc3545;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        /* Dimension table */
        .dimension-table { width: 100%; border-collapse: collapse; margin: 0; color: #000 !important; }
        .dimension-table td, .dimension-table th {
            padding: 1px !important;
            font-size: 5.5px;
            line-height: 1.1;
            border: 1px solid #000 !important;
            text-align: center;
            color: #000 !important;
        }
        .dimension-table th { background-color: #f2f2f2 !important; font-weight: bold; color: #000 !important; }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo"><img src="{{ public_path('master item/ipp.jpg') }}" style="max-width: 60px;"></td>
            <td class="title">LAPORAN CHECK SHEET INCOMING SUB-PART</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-0212</td>
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

    <div style="margin-bottom: 5px; font-size: 9px;">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }} |
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" style="width: 45px;">Tanggal</th>
                <th rowspan="2">Sub-Part Name</th>
                <th rowspan="2" style="width: 45px;">Tgl Datang</th>
                <th rowspan="2">Lot Number</th>
                <th rowspan="2" style="width: 35px;">Qty (Pcs)</th>
                <th rowspan="2" style="width: 30px;">Samp.</th>
                <th rowspan="2">Dimensi</th>
                <th rowspan="2" style="width: 25px;">Jdg</th>
                <th colspan="2">Detail NG</th>
                <th rowspan="2" style="width: 30px;">QC</th>
            </tr>
            <tr>
                <th style="width: 20px;">Pcs</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $cs)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ date('d/m/y', strtotime($cs->date)) }}</td>
                    <td style="text-align: left;">{{ $cs->item->name ?? '-' }}</td>
                    <td>{{ date('d/m/y', strtotime($cs->tanggal_datang)) }}</td>
                    <td>{{ $cs->lot_batch_number }}</td>
                    <td>{{ $cs->quantity }}</td>
                    <td>{{ $cs->sampling_size_pcs }}</td>
                    @php
                        $dimData = is_array($cs->check_dimensi)
                            ? $cs->check_dimensi
                            : json_decode($cs->check_dimensi ?? '', true);
                        $dimData = is_array($dimData) ? $dimData : [];

                        $hasUserInputs = false;
                        $flatPoints = [];
                        foreach ($dimData as $k => $v) {
                            if (is_array($v)) {
                                foreach ($v as $pIdx => $pVal) {
                                    if ($pVal !== null && $pVal !== '' && $pVal !== '-') {
                                        $hasUserInputs = true;
                                        $flatPoints[$pIdx] = $pVal;
                                    }
                                }
                            } else {
                                if ($v !== null && $v !== '' && $v !== '-') {
                                    $hasUserInputs = true;
                                    $pIdx = is_numeric($k) ? (int) $k : $k;
                                    $flatPoints[$pIdx] = $v;
                                }
                            }
                        }

                        $itemStandardsRaw = $cs->item->dimension_standards ?? null;
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

                        $activePoints = [];
                        foreach ($flatPoints as $pKey => $pVal) {
                            $activePoints[$pKey] = true;
                        }
                        foreach ($standards as $pKey => $std) {
                            $activePoints[$pKey] = true;
                        }
                        $activePoints = array_keys($activePoints);
                        sort($activePoints);

                        if (empty($activePoints) && $hasUserInputs) {
                            $activePoints = range(1, count($flatPoints));
                        }
                    @endphp
                    <td style="padding: 1px; vertical-align: middle;">
                        @if($hasUserInputs && !empty($activePoints))
                            <table class="dimension-table">
                                <thead>
                                    @php
                                        $hasStdData = false;
                                        foreach ($activePoints as $j) {
                                            if (isset($standards[$j]) && ($standards[$j]['size'] !== null && $standards[$j]['size'] !== '' && $standards[$j]['size'] !== '-')) {
                                                $hasStdData = true; break;
                                            }
                                        }
                                    @endphp
                                    @if($hasStdData)
                                        <tr>
                                            @foreach ($activePoints as $j)
                                                <th>{{ isset($standards[$j]) ? $standards[$j]['size'] : '-' }}</th>
                                            @endforeach
                                        </tr>
                                    @endif
                                    <tr>
                                        @foreach($activePoints as $j)
                                            <th>Ø{{ $j }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach($activePoints as $j)
                                            @php
                                                $val = $flatPoints[$j] ?? '-';
                                            @endphp
                                            <td>{{ ($val !== '' && $val !== null) ? $val : '-' }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        @else
                            {{ (is_string($cs->check_dimensi) && $cs->check_dimensi && $cs->check_dimensi !== '[]' && $cs->check_dimensi !== '{}') ? $cs->check_dimensi : '-' }}
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $cs->judgment }}
                        </span>
                    </td>
                    @php $defects = is_array($cs->defects) ? $cs->defects : json_decode($cs->defects, true); @endphp
                    <td class="text-danger p-0">
                        @foreach($defects ?? [] as $d) <div style="border-bottom: 0.1px solid #ddd;">{{ $d['qty'] ?? 0 }}
                        </div> @endforeach
                    </td>
                    <td class="text-danger p-0" style="font-size: 5px;">
                        @foreach($defects ?? [] as $d) <div style="border-bottom: 0.1px solid #ddd;">{{ $d['type'] ?? '-' }}
                        </div> @endforeach
                    </td>
                    <td class="text-uppercase">{{ $cs->operator_initials }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
