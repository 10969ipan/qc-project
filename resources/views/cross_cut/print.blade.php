<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Cross Cut Plating</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            color: #333;
            margin: 0;
            padding: 10mm 10mm 5mm 10mm;
        }

        /* ===== HEADER DOKUMEN ===== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .logo {
            width: 90px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #000;
        }

        .doc-info {
            width: 160px;
            font-size: 8.5px;
        }

        .doc-info table { width: 100%; border: none; }
        .doc-info td   { border: none; padding: 1px 2px; }

        /* ===== INFO PERIODE ===== */
        .sub-header {
            margin-bottom: 8px;
            font-size: 9px;
        }

        /* ===== TABEL DATA ===== */
        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        /* Header tabel mengikuti di setiap halaman baru */
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        .table th {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6.5px;
        }

        .table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            word-wrap: break-word;
        }

        /* Hindari baris terpotong di tengah halaman */
        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* ===== KOLOM TERSEMBUNYI ===== */
        .col-hidden { display: none; }

        .text-uppercase { text-transform: uppercase; }

        /* ===== FOOTER KUSTOM ===== */
        .print-footer {
            position: fixed;
            bottom: 5mm;
            left: 10mm;
            font-size: 7.5px;
            color: #666;
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
            padding: 1px;
            text-align: left;
            font-size: 6px;
        }

        .text-left { text-align: left !important; }

    </style>
</head>

<body>

    {{-- Header: Logo | Judul | Info Dokumen --}}
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ asset('master item/ipp.jpg') }}"
                     style="max-width: 75px; max-height: 55px; object-fit: contain;">
            </td>
            <td class="title">LAPORAN DATA CHECKSHEET CROSS CUT PLATING</td>
            <td class="doc-info">
                <table>
                    <tr><td>No. Dokumen</td><td>: QC-KRW-F-0214</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: 25/03/2015</td></tr>
                    <tr><td>Revisi Ke</td><td>: 3</td></tr>
                    <tr><td>Tgl. Revisi</td><td>: 22/12/2025</td></tr>
                    <tr><td>Hal</td><td>: 1/1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Sub-header: Periode & Item --}}
    <div class="sub-header">
        <strong>Periode:</strong>
        @if(isset($filters['start_date']) && isset($filters['end_date']))
            {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
        @else
            Semua Periode
        @endif
        <br>
        <strong>Item:</strong> {{ $itemName ?? 'Semua' }}
    </div>

    {{-- Tabel Data --}}
    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Tgl. Prod</th>
                <th rowspan="2">Tgl. QC</th>
                <th rowspan="2">Shift (P/Q)</th>
                <th rowspan="2">Jam (B)</th>
                <th rowspan="2">Jam (A)</th>
                <th rowspan="2">Cycle (s)</th>
                <th rowspan="2" class="col-hidden">Kode SAP</th>
                <th rowspan="2">Item Part</th>
                <th rowspan="2">Customer</th>
                <th rowspan="2">Part No</th>
                <th colspan="2">Bak No</th>
                <th rowspan="2">Posisi Remark</th>
                <th rowspan="2">Result Remark</th>
                <th rowspan="2">Ket</th>
                <th rowspan="2">Inisial</th>
            </tr>
            <tr>
                <th>C</th>
                <th>A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $checksheet)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('d-m-y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d-m-y') }}</td>
                    <td>{{ $checksheet->production_shift }} / {{ $checksheet->qc_shift }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }}</td>
                    <td>{{ $checksheet->cycle_time ?? '-' }}</td>
                    <td class="col-hidden">{{ $checksheet->item->sap_code ?? '-' }}</td>
                    <td class="text-left">{{ $checksheet->item->name ?? '-' }}</td>
                    <td class="text-left">{{ $checksheet->item->customer ?? '-' }}</td>
                    <td class="text-left text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td>{{ $checksheet->chemical_catalyst ?? '-' }}</td>
                    <td>{{ $checksheet->chemical_abu ?? '-' }}</td>
                    <td class="text-left">
                        {{ $checksheet->position_remark_judgment }} - {{ $checksheet->position_remark_no_lot }}
                    </td>
                    <td class="text-left">{{ $checksheet->result_remark ?? '-' }}</td>
                    <td class="text-left">{{ $checksheet->keterangan ?? '-' }}</td>
                    <td class="text-uppercase">{{ $checksheet->operator_initials }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="print-footer" id="printFooter">
        <span id="footerDateTime"></span>
    </div>

    <script>
        (function () {
            var now = new Date();
            var pad = function(n){ return n < 10 ? '0' + n : n; };
            var dateStr = pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear()
                        + '  ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
            document.getElementById('footerDateTime').textContent = 'Dicetak: ' + dateStr;
        })();

        window.onload = function () {
            window.print();
        };
    </script>

</body>
</html>
