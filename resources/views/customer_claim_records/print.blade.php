<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Claim Customer</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 7px;
            color: #333;
            margin: 0;
            padding: 10mm 5mm 5mm 5mm;
        }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .logo { width: 90px; text-align: center; }
        .title { text-align: center; font-size: 12px; font-weight: bold; color: #000; }
        .doc-info { width: 160px; font-size: 7.5px; }
        .doc-info table { width: 100%; border: none; }
        .doc-info td { border: none; padding: 1px 2px; }

        .sub-header { margin-bottom: 8px; font-size: 8.5px; }

        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        .table th {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 5.5px;
        }

        .table td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 6.5px;
            word-wrap: break-word;
            line-height: 1.1;
        }

        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-weight-bold { font-weight: bold; }

        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Column Widths matching PDF */
        .col-no { width: 1.5%; }
        .col-date { width: 4.5%; }
        .col-cust { width: 6%; }
        .col-plant-up { width: 4.5%; }
        .col-type { width: 4.5%; }
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

        .print-footer { margin-top: 5mm; font-size: 7px; color: #666; text-align: right; }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ asset('master item/ipp.jpg') }}"
                     style="max-width: 65px; max-height: 50px; object-fit: contain;">
            </td>
            <td class="title">LAPORAN DATA CLAIM CUSTOMER</td>
            <td class="doc-info">
                <table>
                    <tr><td>No. Dokumen</td><td>: {{ $plantCode === 'jakarta' ? 'QC-JKT-F-045/0' : 'QC-KRW-F-0210' }}</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: {{ $plantCode === 'jakarta' ? '15.01.2023' : '20/05/2015' }}</td></tr>
                    <tr><td>Revisi / Tgl</td><td>: {{ $plantCode === 'jakarta' ? '0 / 15.01.2023' : '2 / 10/11/2024' }}</td></tr>
                    <tr><td>Hal</td><td>: 1/1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="sub-header">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}<br>
        <strong>Plant:</strong> {{ strtoupper($plantName) }}<br>
        <strong>Customer:</strong> {{ request('customer') ?: 'SEMUA' }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-date">Tanggal</th>
                <th class="col-cust">Customer</th>
                <th class="col-plant-up">Plant/UP</th>
                <th class="col-type">Type</th>
                <th class="col-report">Report</th>
                <th class="col-project">Proj</th>
                <th class="col-part">Part</th>
                <th class="col-problem">Problem</th>
                <th class="col-qty">Qty</th>
                <th class="col-defect">Kat. Problem</th>
                <th class="col-penyimpangan">Kat. Penyimp.</th>
                <th class="col-op">Op</th>
                <th class="col-ins">Ins</th>
                <th class="col-akom">Akom (Rp)</th>
                <th class="col-ot">OT (Rp)</th>
                <th class="col-action">Temp Action</th>
                <th class="col-feed">Feedback</th>
                <th class="col-sfeed">S.Feed</th>
                <th class="col-scm">S.C/M</th>
                <th class="col-eval">Evaluasi</th>
                <th class="col-smon">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $record->tanggal_claim ? \Carbon\Carbon::parse($record->tanggal_claim)->format('d/m/Y') : '-' }}</td>
                    <td class="text-left font-weight-bold" style="text-transform: uppercase;">{{ $record->customer }}</td>
                    <td class="text-left">{{ $record->plant_up_customer }}</td>
                    <td class="text-center">{{ $record->claim_type }}</td>
                    <td class="text-center">{{ $record->no_report }}</td>
                    <td class="text-center">{{ $record->project }}</td>
                    <td class="text-left font-weight-bold" style="text-transform: uppercase;">{{ $record->nama_part }}</td>
                    <td class="text-left">{{ $record->problem }}</td>
                    <td class="text-center">{{ $record->qty }}</td>
                    <td class="text-left">{{ $record->kategori_defect }}</td>
                    <td class="text-left" style="text-transform: uppercase;">{{ $record->kategori_penyimpangan }}</td>
                    <td class="text-center">{{ $record->initial_operator }}</td>
                    <td class="text-center">{{ $record->initial_inspektor }}</td>
                    <td class="text-right">{{ number_format($record->total_akomodasi, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($record->total_overtime, 0, ',', '.') }}</td>
                    <td class="text-left">{{ $record->action_taken }}</td>
                    <td class="text-left">{{ $record->feedback }}</td>
                    <td class="text-center">{{ $record->status_feedback }}</td>
                    <td class="text-center">{{ $record->status_cm }}</td>
                    <td class="text-left">{{ $record->evaluasi }}</td>
                    <td class="text-center">
                        <span class="font-weight-bold {{ $record->monitoring_status === 'CLOSED' ? 'text-success' : 'text-danger' }}">
                            {{ $record->monitoring_status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="22" class="text-center">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="print-footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} oleh {{ auth()->user()->name }}
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
