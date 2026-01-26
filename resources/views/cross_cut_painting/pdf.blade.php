<!DOCTYPE html>
<html>

<head>
    <title>Laporan Cross Cut Painting</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 4px;
            text-align: center;
            word-wrap: break-word;
        }

        .header-table {
            border: none;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            text-align: left;
            padding: 2px;
        }

        .text-bold {
            font-weight: bold;
        }

        .bg-gray {
            background-color: #f2f2f2;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <h3>LAPORAN CROSS CUT PAINTING</h3>
        <p>Periode: {{ $startDate ?? '-' }} s/d {{ $endDate ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr class="bg-gray">
                <th rowspan="2">No</th>
                <th rowspan="2">Tgl Prod</th>
                <th rowspan="2">Shift Prod</th>
                <th rowspan="2">Tgl QC</th>
                <th rowspan="2">Shift QC</th>
                <th rowspan="2">Jam Bef</th>
                <th rowspan="2">Jam Aft</th>
                <th rowspan="2" style="width: 15%;">Nama Part</th>
                <th rowspan="2" style="width: 10%;">Pengujian</th>
                <th rowspan="2">Judgement</th>
                <th rowspan="2">Inisial</th>
                <th colspan="6">Approval Status</th>
            </tr>
            <tr class="bg-gray">
                <!-- Adjust Labels based on Plant -->
                @if(request('plant') == 'jakarta')
                    <th>Karu</th>
                @else
                    <th>Kepala Regu QC</th>
                @endif
                <th>Kepala Shift Plating</th>
                <th>Supervisor Quality</th>
                <th>Supervisor Plating</th>
                <th>Manager QC</th>
                <th>Manager Plating</th>
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
                        @if($row->tap_test) Tap: {{ $row->tap_test }} <br> @endif
                        @if($row->image_path) [Foto Check] @else - @endif
                    </td>

                    <td
                        style="{{ $row->position_remark_judgment == 'NG' ? 'color: red; font-weight: bold;' : 'color: green;' }}">
                        {{ $row->position_remark_judgment }}
                    </td>

                    <td>{{ $row->operator_initials }}</td>

                    <td>{{ $row->karu_qc ? 'Appr' : ($row->karu_qc === 'REJECTED' ? 'Rej' : '-') }}</td>
                    <td>{{ $row->kashift_plating ? 'Appr' : ($row->kashift_plating === 'REJECTED' ? 'Rej' : '-') }}</td>
                    <td>{{ $row->supervisor_plating ? 'Appr' : ($row->supervisor_plating === 'REJECTED' ? 'Rej' : '-') }}
                    </td>
                    <td>{{ $row->supervisor_qc ? 'Appr' : ($row->supervisor_qc === 'REJECTED' ? 'Rej' : '-') }}</td>
                    <td>{{ $row->manager_plating ? 'Appr' : ($row->manager_plating === 'REJECTED' ? 'Rej' : '-') }}</td>
                    <td>{{ $row->manager_qc ? 'Appr' : ($row->manager_qc === 'REJECTED' ? 'Rej' : '-') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 9px; text-align: right;">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>