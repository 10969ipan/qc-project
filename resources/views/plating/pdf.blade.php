<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Plating</title>
    <style>
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
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 4px;
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
            <td class="logo">
                <img src="{{ public_path('master item/ipp.jpg') }}" style="max-width: 70px;">
            </td>
            <td class="title">LAPORAN CHECK SHEET PLATING</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-0213</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 25/03/2015</td>
                    </tr>
                    <tr>
                        <td>Revisi Ke</td>
                        <td>: 3</td>
                    </tr>
                    <tr>
                        <td>Tgl. Revisi</td>
                        <td>: 22/12/2025</td>
                    </tr>
                    <tr>
                        <td>Hal</td>
                        <td>: 1/1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 10px; font-size: 10px;">
        <strong>Periode:</strong>
        {{ $startDate }} s/d {{ $endDate }}
        <br>
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2">Injection<br>(Tgl/Shf)</th>
                <th rowspan="2">Plating<br>(Tgl/Shf/Lot)</th>
                <th rowspan="2">Quality<br>(Tgl/Shf)</th>
                <th rowspan="2">Jam (Bef)</th>
                <th rowspan="2">Jam (Aft)</th>
                <th rowspan="2">Cycle (s)</th>
                <th rowspan="2">Kode SAP</th>
                <th rowspan="2" style="width: 80px;">Item Part</th>
                <th rowspan="2" style="width: 40px;">Cust</th>
                <th rowspan="2" style="width: 70px;">Part No</th>
                <th rowspan="2" style="width: 30px;">Total</th>
                <th rowspan="2" style="width: 30px;">OK</th>
                <th rowspan="2" style="width: 30px;">NG</th>
                <th colspan="2">Detail NG</th>
                <th rowspan="2">Jdg</th>
                <th rowspan="2">Inisial</th>
                <th rowspan="2">Ket</th>
            </tr>
            <tr>
                <th>Pcs</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $checksheet)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $checksheet->injection_date ? $checksheet->injection_date->format('d/m/y') : '-' }} /
                        {{ $checksheet->injection_shift ?? '-' }}
                    </td>
                    <td>{{ $checksheet->plating_date ? $checksheet->plating_date->format('d/m/y') : '-' }} /
                        {{ $checksheet->plating_shift ?? '-' }} / {{ $checksheet->no_lot ?? '-' }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->date)->format('d/m/y') }} / {{ $checksheet->shift }}</td>
                    <td>{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                    <td>{{ $checksheet->created_at->format('H:i') }}</td>
                    <td>{{ $checksheet->cycle_time ?? '-' }}</td>
                    <td>{{ $checksheet->item->sap_code ?? '-' }}</td>
                    <td>{{ $checksheet->item->name ?? '-' }}</td>
                    <td>{{ $checksheet->item->customer ?? '-' }}</td>
                    <td>{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td>{{ $checksheet->total_qty }}</td>
                    <td class="text-success">{{ $checksheet->total_ok }}</td>
                    <td class="text-danger">{{ $checksheet->total_ng }}</td>

                    @php
                        $defectsData = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
                        $pcsLines = [];
                        $nameLines = [];

                        if (is_array($defectsData)) {
                            foreach ($defectsData as $d) {
                                if (is_array($d) && isset($d['type'])) {
                                    $qty = $d['qty'] ?? 1;
                                    $pcsLines[] = $qty;
                                    $nameLines[] = $d['type'];
                                } elseif (is_string($d)) {
                                    $pcsLines[] = 1;
                                    $nameLines[] = $d;
                                }
                            }
                        }
                    @endphp

                    <td class="text-danger p-0">
                        {!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}
                    </td>
                    <td class="text-danger p-0" style="font-size: 7px;">
                        {!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}
                    </td>

                    <td>
                        <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $checksheet->judgment }}
                        </span>
                    </td>
                    <td class="text-uppercase">{{ $checksheet->operator_initials }}</td>
                    <td>{{ $checksheet->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
