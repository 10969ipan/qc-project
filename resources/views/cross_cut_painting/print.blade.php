<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Cross Cut Painting</title>
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
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
        }

        .table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 8px;
            word-wrap: break-word;
        }

        /* Hindari baris terpotong di tengah halaman */
        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .text-uppercase { text-transform: uppercase; }
        .text-left { text-align: left !important; }

        /* ===== FOOTER KUSTOM ===== */
        .print-footer {
            position: fixed;
            bottom: 5mm;
            left: 10mm;
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
            <td class="title">LAPORAN DATA CHECKSHEET CROSS CUT PAINTING</td>
            <td class="doc-info">
                <table>
                    <tr><td>No. Dokumen</td><td>: QC-KRW-F-XXXX</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: -</td></tr>
                    <tr><td>Revisi Ke</td><td>: 0</td></tr>
                    <tr><td>Tgl. Revisi</td><td>: -</td></tr>
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
        <br>
        <strong>Plant:</strong> {{ isset($plantName) ? strtoupper($plantName) : 'KARAWANG' }}
    </div>

    {{-- Tabel Data --}}
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Prod</th>
                <th>Shift Prod</th>
                <th>Tgl QC</th>
                <th>Shift QC</th>
                <th>Jam Bef</th>
                <th>Jam Aft</th>
                <th style="width: 20%;">Nama Part</th>
                <th style="width: 20%;">Hasil CC, PS &amp; TT</th>
                <th>Judgement</th>
                <th>Inisial</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->production_datetime)->format('d/m/y') }}</td>
                    <td>{{ $row->production_shift }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->qc_datetime)->format('d/m/y') }}</td>
                    <td>{{ $row->qc_shift }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->qc_datetime)->copy()->subSeconds($row->cycle_time ?? 0)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->qc_datetime)->format('H:i') }}</td>
                    <td class="text-left">{{ $row->item->name ?? '-' }}</td>
                    <td>
                        CC: {{ $row->defects['cross_cut'] ?? 'OK' }} | 
                        PS: {{ $row->pencil_scratch ?? 'OK' }} | 
                        TT: {{ $row->tap_test ?? 'OK' }}
                    </td>
                    <td style="{{ $row->position_remark_judgment == 'NG' ? 'color: red; font-weight: bold;' : 'color: green; font-weight: bold;' }}">
                        {{ $row->position_remark_judgment }}
                    </td>
                    <td class="text-uppercase">{{ $row->operator_initials }}</td>
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
