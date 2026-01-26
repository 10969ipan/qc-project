<!DOCTYPE html>
<html>

<head>
    <title>Laporan Claim Customer - {{ $plantName }}</title>
    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 7px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .header-table .logo {
            width: 70px;
            text-align: center;
        }

        .header-table .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        .header-table .doc-info {
            font-size: 8px;
            text-align: left;
            width: 120px;
        }

        table.main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.main-table th {
            background-color: #f2f2f2;
            color: #000;
            border: 1px solid #000;
            padding: 3px;
            font-weight: bold;
            text-align: center;
        }

        table.main-table td {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Optimized Column Widths (Total 100%) */
        .col-no {
            width: 1.5%;
        }

        .col-date {
            width: 3.5%;
        }

        .col-cust {
            width: 4.5%;
        }

        .col-plant-up {
            width: 3.5%;
        }

        .col-type {
            width: 3.5%;
        }

        .col-report {
            width: 4.5%;
        }

        .col-source {
            width: 2.5%;
        }

        .col-project {
            width: 1.5%;
        }

        .col-plant-ipp {
            width: 2.5%;
        }

        .col-part {
            width: 5.5%;
        }

        .col-problem {
            width: 9%;
        }

        .col-defect {
            width: 3.5%;
        }

        .col-penyimpangan {
            width: 3.5%;
        }

        .col-qty {
            width: 1.5%;
        }

        .col-op {
            width: 2.5%;
        }

        .col-ins {
            width: 2.5%;
        }

        .col-frek {
            width: 2.5%;
        }

        .col-pfrek {
            width: 2.5%;
        }

        .col-action {
            width: 7.5%;
        }

        .col-cost {
            width: 3.5%;
        }

        .col-feed {
            width: 7.5%;
        }

        .col-sfeed {
            width: 2.5%;
        }

        .col-scm {
            width: 2.5%;
        }

        .col-mon {
            width: 4%;
        }

        .col-eval {
            width: 4%;
        }

        .col-smon {
            width: 2.5%;
        }

        .info-section {
            font-size: 8px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ public_path('master item/ipp.jpg') }}" style="max-width: 70px;">
            </td>
            <td class="title">LAPORAN DATA CLAIM CUSTOMER</td>
            <td class="doc-info">
                No. Dokumen: QC-IPP-F-00XX<br>
                Tgl. Terbit: {{ date('d/m/Y') }}<br>
                Revisi Ke: 0<br>
                Tgl. Revisi: -
            </td>
        </tr>
    </table>

    <div class="info-section">
        <strong>Plant:</strong> {{ $plantName }} |
        <strong>Exported at:</strong> {{ date('d/m/Y H:i') }}
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-date">Tanggal</th>
                <th class="col-cust">Customer</th>
                <th class="col-plant-up">Plant Cust</th>
                <th class="col-type">Type</th>
                <th class="col-report">No Report</th>
                <th class="col-source">Src</th>
                <th class="col-project">Prj</th>
                <th class="col-plant-ipp">IPP</th>
                <th class="col-part">Nama Part</th>
                <th class="col-problem">Problem</th>
                <th class="col-defect">Defect</th>
                <th class="col-penyimpangan">Penyimpangan</th>
                <th class="col-qty">Qty</th>
                <th class="col-op">OP</th>
                <th class="col-ins">INS</th>
                <th class="col-frek">Frek</th>
                <th class="col-pfrek">%</th>
                <th class="col-action">Action</th>
                <th class="col-cost">Cost</th>
                <th class="col-feed">Feedback</th>
                <th class="col-sfeed">S.Fd</th>
                <th class="col-scm">S.CM</th>
                <th class="col-mon">Mon</th>
                <th class="col-eval">Eval</th>
                <th class="col-smon">S.Mon</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $record)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $record->tanggal_claim ? $record->tanggal_claim->format('d/m/y') : '-' }}
                    </td>
                    <td>{{ $record->customer }}</td>
                    <td>{{ $record->plant_up_customer }}</td>
                    <td class="text-center">{{ $record->claim_type }}</td>
                    <td>{{ $record->no_report }}</td>
                    <td class="text-center">{{ $record->source_type }}</td>
                    <td class="text-center">{{ $record->project }}</td>
                    <td class="text-center">{{ $record->plant->code }}</td>
                    <td>{{ $record->nama_part }}</td>
                    <td>{{ $record->problem }}</td>
                    <td>{{ $record->kategori_defect }}</td>
                    <td>{{ $record->kategori_penyimpangan }}</td>
                    <td class="text-center">{{ $record->qty }}</td>
                    <td class="text-center">{{ $record->initial_operator }}</td>
                    <td class="text-center">{{ $record->initial_inspektor }}</td>
                    <td class="text-center">{{ $record->frek }}</td>
                    <td class="text-center">{{ $record->persen_frek }}</td>
                    <td>{{ $record->action_taken }}</td>
                    <td class="text-right">{{ number_format($record->total_cost, 0, ',', '.') }}</td>
                    <td>{{ $record->feedback }}</td>
                    <td class="text-center">{{ $record->status_feedback }}</td>
                    <td class="text-center">{{ $record->status_cm }}</td>
                    <td>{{ $record->monitoring }}</td>
                    <td>{{ $record->evaluasi }}</td>
                    <td class="text-center">{{ $record->monitoring_status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>