<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('cross_cut', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Checksheet Cross Cut</title>
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

        .table th {
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

        .text-left {
            text-align: left;
        }

        .kimia-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .kimia-table td,
        .kimia-table th {
            border: 1px solid #ccc;
            /* Lighter border for inner table */
            padding: 2px;
            text-align: left;
            font-size: 6px;
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
            <td class="title">LAPORAN CHECK SHEET CROSS CUT</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-0005</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 01/10/2015</td>
                    </tr>
                    <tr>
                        <td>Revisi Ke</td>
                        <td>: 2</td>
                    </tr>
                    <tr>
                        <td>Tgl. Revisi</td>
                        <td>: 01/10/2020</td>
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
        @if($startDate && $endDate && $startDate !== 'Semua' && $endDate !== 'Semua')
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        @else
            Semua Tanggal
        @endif
        <br>
        <strong>Filter:</strong>
        @php
            $filters = [];
            if (isset($itemName)) {
                $filters[] = "Barang: " . $itemName;
            }
            if (isset($approval_status) && $approval_status) {
                $filters[] = "Status: " . ucfirst($approval_status);
            }
            echo count($filters) > 0 ? implode(' | ', $filters) : 'Tidak ada';
        @endphp
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Prod.</th>
                <th>Tanggal QC</th>
                <th>Shift Prod.</th>
                <th>Shift QC</th>
                <th>Jam Before</th>
                <th>Jam After</th>
                <th>Cycle (s)</th>
                <th>Item Part</th>
                <th>Bak No</th>
                <th>Posisi Remark</th>
                <th>Result Remark</th>
                <th>Inisial</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($checksheets as $checksheet)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('d/m/y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d/m/y') }}</td>
                    <td>{{ $checksheet->production_shift }}</td>
                    <td>{{ $checksheet->qc_shift }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}</td>
                    <td>{{ $checksheet->cycle_time ?? '-' }}</td>
                    <td class="text-left">{{ $checksheet->item->name }}</td>
                    <td>
                        <table class="kimia-table">
                            <tr>
                                <th>Catalyst</th>
                                <td>{{ $checksheet->chemical_catalyst ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Copper</th>
                                <td>{{ $checksheet->chemical_abu ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="text-left">{{ $checksheet->position_remark_judgment }} -
                        {{ $checksheet->position_remark_no_lot }}
                    </td>
                    <td class="text-left">{{ $checksheet->result_remark }}</td>
                    <td class="text-uppercase">{{ $checksheet->operator_initials }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data yang sesuai dengan filter yang diterapkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
