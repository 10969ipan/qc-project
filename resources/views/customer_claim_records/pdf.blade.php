<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('customer_claim', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
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
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            line-height: 1.1;
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
            width: 100px;
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
        }

        .doc-info table {
            width: 100%;
            border: none;
        }

        .doc-info td {
            border: none;
            padding: 1px 2px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .col-no { width: 1.5%; }
        .col-date { width: 4.5%; }
        .col-cust { width: 6%; }
        .col-plant-up { width: 4.5%; }
        .col-type { width: 4.5%; }
        .col-eks-int { width: 4.5%; }
        .col-report { width: 5%; }
        .col-project { width: 2.5%; }
        .col-part { width: 8%; }
        .col-problem { width: 13.5%; }
        .col-qty { width: 2%; }
        .col-defect { width: 4.5%; }
        .col-penyimpangan { width: 4.5%; }
        .col-op { width: 2.5%; }
        .col-ins { width: 2.5%; }
        .col-akom { width: 4.5%; }
        .col-ot { width: 4.5%; }
        .col-action { width: 6.5%; }
        .col-feed { width: 6%; }
        .col-sfeed { width: 3%; }
        .col-scm { width: 3%; }
        .col-eval { width: 8%; }
        .col-smon { width: 4.5%; }

    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ public_path('master item/ipp.jpg') }}" style="max-height: 50px;">
            </td>
            <td class="title">LAPORAN DATA CLAIM CUSTOMER</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-178</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 25/03/2015</td>
                    </tr>
                    <tr>
                        <td>Revisi</td>
                        <td>: 0</td>
                    </tr>
                    <tr>
                        <td>Tgl. Revisi</td>
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

    <div style="margin-bottom: 10px;">
        <strong>Periode:</strong>
        {{ $startDate }} - {{ $endDate }}
        <br>
        <strong>Plant:</strong> {{ strtoupper($plantName) }}<br>
        <strong>Customer:</strong> {{ $displayCustomer }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-date">Tanggal Claim</th>
                <th class="col-cust">Customer</th>
                <th class="col-plant-up">Plant / UP (Cust.)</th>
                <th class="col-type">Claim Type</th>
                <th class="col-eks-int">Eks/Int</th>
                <th class="col-report">No. Report</th>
                <th class="col-project">Proj</th>
                <th class="col-part">Nama Part</th>
                <th class="col-problem">Problem</th>
                <th class="col-qty">Qty</th>
                <th class="col-defect">Kategori Problem</th>
                <th class="col-penyimpangan">Kat. Penyimpangan</th>
                <th class="col-op">Op</th>
                <th class="col-ins">Ins</th>
                <th class="col-akom">Cost Akomodasi (Rp)</th>
                <th class="col-ot">Cost Overtime (Rp)</th>
                <th class="col-action">Temporary Action</th>
                <th class="col-feed">Feedback</th>
                <th class="col-sfeed">Status Feed.</th>
                <th class="col-scm">Status (C/M)</th>
                <th class="col-eval">Evaluasi Problem</th>
                <th class="col-smon">Status Mon.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $record->tanggal_claim ? $record->tanggal_claim->format('d/m/Y') : '-' }}</td>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $record->customer }}</td>
                    <td>{{ Str::title($record->plant_up_customer) }}</td>
                    <td>{{ $record->claim_type }}</td>
                    <td>{{ $record->eksternal_internal }}</td>
                    <td>{{ $record->no_report }}</td>
                    <td>{{ $record->project }}</td>
                    <td style="text-transform: uppercase;">{{ $record->nama_part }}</td>
                    <td>{{ Str::title($record->problem) }}</td>
                    <td>{{ $record->qty }}</td>
                    <td>{{ Str::title($record->kategori_defect) }}</td>
                    <td style="text-transform: uppercase;">{{ $record->kategori_penyimpangan }}</td>
                    <td>{{ $record->initial_operator }}</td>
                    <td>{{ $record->initial_inspektor }}</td>
                    <td class="text-right">{{ number_format($record->total_akomodasi, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($record->total_overtime, 0, ',', '.') }}</td>
                    <td>{{ Str::title($record->action_taken) }}</td>
                    <td>{{ Str::title($record->feedback) }}</td>
                    <td>{{ $record->status_feedback }}</td>
                    <td>{{ Str::title($record->status_cm) }}</td>
                    <td class="text-center">{{ $record->evaluasi_formatted }}</td>
                    <td>{{ $record->monitoring_status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 7px; text-align: right;">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>
</body>

</html>
