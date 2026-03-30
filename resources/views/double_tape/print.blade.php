<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Double Tape</title>
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
            font-size: 6px;
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

        /* ===== BADGE ===== */
        .badge {
            display: inline-block;
            padding: .2em .35em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            border-radius: .25rem;
        }

        .badge-success {
            color: #fff;
            background-color: #28a745;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .badge-danger {
            color: #fff;
            background-color: #dc3545;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .text-success   { color: #28a745; }
        .text-danger    { color: #dc3545; }
        .text-uppercase { text-transform: uppercase; }

        /* ===== FOOTER KUSTOM ===== */
        .print-footer {
            margin-top: 8mm;
            font-size: 7.5px;
            color: #666;
            text-align: left;
        }
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
            <td class="title">LAPORAN CHECK SHEET DOUBLE TAPE</td>
            <td class="doc-info">
                <table>
                    <tr><td>No. Dokumen</td><td>: QC-KRW-F-0213</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: 25/03/2015</td></tr>
                    <tr><td>Revisi Ke</td><td>: 3</td></tr>
                    <tr><td>Tgl. Revisi</td><td>: 22/12/2025</td></tr>
                    <tr><td>Hal</td><td>: 1/1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Sub-header: Periode & Plant --}}
    <div class="sub-header">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}<br>
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
    </div>

    {{-- Tabel Data --}}
    <table class="table">
        <thead>
            {{-- Spacer row: muncul di setiap halaman (header group repeat) untuk memberi jarak atas --}}
            <tr class="thead-spacer">
                <td colspan="19" style="height:4mm; border:none; padding:0; background:#fff;"></td>
            </tr>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Jam (Before)</th>
                <th rowspan="2">Jam (After)</th>
                <th rowspan="2">Cycle (s)</th>
                <th rowspan="2">Shift</th>
                <th rowspan="2" class="col-hidden">Kode SAP</th>
                <th rowspan="2">Item Part</th>
                <th rowspan="2">Customer</th>
                <th rowspan="2">Part No</th>
                <th rowspan="2">Total</th>
                <th rowspan="2">Sample</th>
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
            @foreach($checksheets as $checksheet)
                @php
                    $defectsData = is_array($checksheet->defects)
                        ? $checksheet->defects
                        : json_decode($checksheet->defects, true);
                    $pcsLines  = [];
                    $nameLines = [];

                    if (is_array($defectsData)) {
                        foreach ($defectsData as $d) {
                            if (is_array($d) && isset($d['type'])) {
                                $pcsLines[]  = $d['qty'] ?? 1;
                                $nameLines[] = $d['type'];
                            } elseif (is_string($d)) {
                                $pcsLines[]  = 1;
                                $nameLines[] = $d;
                            }
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-y') }}</td>
                    <td>{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                    <td>{{ $checksheet->created_at->format('H:i') }}</td>
                    <td>{{ $checksheet->cycle_time ?? '-' }}</td>
                    <td>{{ $checksheet->shift }}</td>
                    <td class="col-hidden">{{ $checksheet->item->sap_code ?? '-' }}</td>
                    <td>{{ $checksheet->item->name ?? '-' }}</td>
                    <td>{{ $checksheet->item->customer ?? '-' }}</td>
                    <td>{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td>{{ $checksheet->total_qty }}</td>
                    <td>{{ $checksheet->sampling_qty }}</td>
                    <td class="text-success">{{ $checksheet->total_ok }}</td>
                    <td class="text-danger">{{ $checksheet->total_ng }}</td>
                    <td class="text-danger" style="font-size: 6.5px;">
                        {!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}
                    </td>
                    <td class="text-danger" style="font-size: 6.5px;">
                        {!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}
                    </td>
                    <td>
                        <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $checksheet->judgment }}
                        </span>
                    </td>
                    <td class="text-uppercase">{{ $checksheet->operator_initials }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer: tanggal cetak di pojok bawah kiri --}}
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
