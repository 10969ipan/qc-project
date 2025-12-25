<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Checksheet Cross Cut</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .header-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .header-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .header-table .logo { width: 80px; text-align: center; }
        .header-table .title { text-align: center; font-size: 14px; font-weight: bold; }
        .header-table .doc-info { font-size: 9px; text-align: left; }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .header p {
            font-size: 12px;
            margin: 5px 0;
        }
        .text-left {
            text-align: left;
        }
         .kimia-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kimia-table td, .kimia-table th {
            border: 1px solid #000;
            padding: 2px;
            text-align: left;
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
            <td class="doc-info" style="width: 120px;">
                No. Dokumen: QC-KRW-F-0005<br>
                Tgl. Terbit: 01/10/2015<br>
                Revisi Ke: 2<br>
                Tgl. Revisi: 01/10/2020
            </td>
        </tr>
    </table>

    <p style="font-size: 10px;">
        <strong>Periode:</strong>
        @if($startDate && $endDate)
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        @else
            Semua Tanggal
        @endif
        <br>
        <strong>Filter:</strong>
        @php
            $filters = [];
            if(isset($itemName)) {
                $filters[] = "Barang: " . $itemName;
            }
            if(isset($approval_status) && $approval_status) {
                $filters[] = "Status: " . ucfirst($approval_status);
            }
            echo count($filters) > 0 ? implode(' | ', $filters) : 'Tidak ada';
        @endphp
    </p>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Prod.</th>
                <th>Tanggal QC</th>
                <th>Shift Prod./QC</th>
                <th>Jam Before</th>
                <th>Jam After</th>
                <th>Cycle (s)</th>
                <th>Item Part</th>
                <th>Kimia</th>
                <th>Posisi Remark</th>
                <th>Result Remark</th>
                <th>Inisial</th>
                <th>Approval</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($checksheets as $checksheet)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('d/m/y') }}</td>
                <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d/m/y') }}</td>
                <td>{{ $checksheet->production_shift }} / {{ $checksheet->qc_shift }}</td>
                <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}</td>
                <td>{{ $checksheet->cycle_time ?? '-' }}</td>
                <td class="text-left">{{ $checksheet->item->name }}</td>
                <td>
                     <table class="kimia-table">
                        <tr><th>C</th><td>{{ $checksheet->chemical_copper ?? '-' }}</td></tr>
                        <tr><th>N</th><td>{{ $checksheet->chemical_nikel ?? '-' }}</td></tr>
                        <tr><th>E</th><td>{{ $checksheet->chemical_eching ?? '-' }}</td></tr>
                        <tr><th>A</th><td>{{ $checksheet->chemical_abu ?? '-' }}</td></tr>
                    </table>
                </td>
                <td class="text-left">{{ $checksheet->position_remark_judgment }} - {{ $checksheet->position_remark_no_lot }}</td>
                <td class="text-left">{{ $checksheet->result_remark }}</td>
                <td>{{ $checksheet->operator_initials }}</td>
                <td>
                    @if($checksheet->supervisor_qc && $checksheet->supervisor_qc !== 'REJECTED')
                        Approved
                    @elseif($checksheet->kashift_qc === 'REJECTED' || $checksheet->supervisor_qc === 'REJECTED' || $checksheet->asst_manager_qc === 'REJECTED' || $checksheet->manager_qc === 'REJECTED')
                        Rejected
                    @else
                        Pending
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center">Tidak ada data yang sesuai dengan filter yang diterapkan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
