<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('cross_cut_painting', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html>

<head>
    <title>Laporan Cross Cut Painting</title>
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

        .text-bold {
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ public_path('master item/ipp.jpg') }}" style="max-width: 70px;">
            </td>
            <td class="title">LAPORAN CROSS CUT PAINTING</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-XXXX</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: -</td>
                    </tr>
                    <tr>
                        <td>Revisi Ke</td>
                        <td>: 0</td>
                    </tr>
                    <tr>
                        <td>Tgl. Revisi</td>
                        <td>: -</td>
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
        {{ $startDate ?? '-' }} s/d {{ $endDate ?? '-' }}
        <br>
        <strong>Plant:</strong> {{ isset($plantName) ? strtoupper($plantName) : 'KARAWANG' }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Tgl Prod</th>
                <th rowspan="2">Shift Prod</th>
                <th rowspan="2">Tgl QC</th>
                <th rowspan="2">Shift QC</th>
                <th rowspan="2">Jam Bef</th>
                <th rowspan="2">Jam Aft</th>
                <th rowspan="2" style="width: 15%;">Nama Part</th>
                <th rowspan="2" style="width: 15%;">Hasil CC, PS &amp; TT</th>
                <th rowspan="2">Judgement</th>
                <th rowspan="2">Inisial</th>
            </tr>
            <tr>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->production_datetime)->format('d/m') }}</td>
                    <td>{{ $row->production_shift }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->qc_datetime)->format('d/m') }}</td>
                    <td>{{ $row->qc_shift }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->qc_datetime)->copy()->subSeconds($row->cycle_time ?? 0)->format('H:i') }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($row->qc_datetime)->format('H:i') }}</td>
                    <td style="text-align: left;">{{ $row->item->name ?? '-' }}</td>

                    {{-- Unified Pengujian --}}
                    <td>
                        CC: {{ $row->defects['cross_cut'] ?? 'OK' }} <br>
                        PS: {{ $row->pencil_scratch ?? 'OK' }} <br>
                        TT: {{ $row->tap_test ?? 'OK' }}
                    </td>

                    <td
                        style="{{ $row->position_remark_judgment == 'NG' ? 'color: red; font-weight: bold;' : 'color: green;' }}">
                        {{ $row->position_remark_judgment }}
                    </td>

                    <td>{{ $row->operator_initials }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 9px; text-align: right;">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
