<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Incoming Part</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
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
            font-size: 7px;
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
        }

        .doc-info {
            width: 150px;
            font-size: 9px;
        }

        .doc-info table {
            width: 100%;
            border: none;
        }

        .doc-info td {
            border: none;
            padding: 1px 2px;
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
            <td class="title">LAPORAN CHECK SHEET INCOMING PART</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-0210</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 01/01/2026</td>
                    </tr>
                    <tr>
                        <td>Revisi</td>
                        <td>: 0</td>
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
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}<br>
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Shift</th>
                <th rowspan="2">Item Part</th>
                <th rowspan="2">Total Check</th>
                <th rowspan="2">Tgl Datang</th>
                <th rowspan="2">OK</th>
                <th rowspan="2">NG</th>
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
            @foreach($checksheets as $cs)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ date('d-m-y', strtotime($cs->date)) }}</td>
                    <td>{{ $cs->shift }}</td>
                    <td>{{ $cs->item->name ?? '-' }}</td>
                    <td>{{ $cs->total_check }}</td>
                    <td>{{ $cs->tanggal_datang ? date('d-m-y', strtotime($cs->tanggal_datang)) : '-' }}</td>
                    <td class="text-success">{{ $cs->total_check - $cs->total_ng }}</td>
                    <td class="text-danger">{{ $cs->total_ng }}</td>

                    @php
                        $defects = is_array($cs->defects) ? $cs->defects : json_decode($cs->defects, true);
                    @endphp
                    <td class="text-danger p-0">
                        @foreach($defects ?? [] as $d)
                            <div style="border-bottom: 0.1px solid #ddd;">{{ $d['qty'] ?? 0 }}</div>
                        @endforeach
                    </td>
                    <td class="text-danger p-0" style="font-size: 6px;">
                        @foreach($defects ?? [] as $d)
                            <div style="border-bottom: 0.1px solid #ddd;">{{ $d['type'] ?? '-' }}</div>
                        @endforeach
                    </td>

                    <td>
                        <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $cs->judgment }}
                        </span>
                    </td>
                    <td class="text-uppercase">{{ $cs->operator_initials }}</td>
                    <td>{{ $cs->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
