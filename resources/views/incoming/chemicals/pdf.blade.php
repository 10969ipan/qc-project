<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_chemicals', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Incoming Chemical</title>
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
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo"><img src="{{ public_path('master item/ipp.jpg') }}" style="max-width: 60px;"></td>
            <td class="title">LAPORAN CHECK SHEET INCOMING CHEMICAL</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-0214</td>
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
                <th rowspan="2" style="width: 45px;">Tgl Check</th>
                <th rowspan="2">Chemical Name</th>
                <th rowspan="2" style="width: 45px;">Tgl Datang</th>
                <th rowspan="2">Lot Number</th>
                <th colspan="3">Quantity (Kg)</th>
                <th rowspan="2" style="width: 45px;">Expired</th>
                <th rowspan="2" style="width: 25px;">Jdg</th>
                <th colspan="2">Detail NG</th>
                <th rowspan="2" style="width: 30px;">QC</th>
            </tr>
            <tr>
                <th style="width: 25px;">Total</th>
                <th style="width: 25px;">Komp.</th>
                <th style="width: 25px;">Samp.</th>
                <th style="width: 20px;">Pcs</th>
                <th>Jenis NG</th>
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
                    <td>{{ $cs->quantity_kg }}</td>
                    <td>{{ $cs->komper_jirigen_kg }}</td>
                    <td>{{ $cs->sampling_size_jirigen_kg }}</td>
                    <td>{{ date('d/m/y', strtotime($cs->expired_date)) }}</td>
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
