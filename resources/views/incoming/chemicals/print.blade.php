<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_chemicals', $headerPlantCode, [
            'no_dokumen' => 'QC-KRW-F-0214',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Incoming Material</title>
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

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .logo { width: 90px; text-align: center; }
        .title { text-align: center; font-size: 13px; font-weight: bold; color: #000; }
        .doc-info { width: 160px; font-size: 8.5px; }
        .doc-info table { width: 100%; border: none; }
        .doc-info td { border: none; padding: 1px 2px; }

        .sub-header { margin-bottom: 8px; font-size: 9px; }

        .table { width: 100%; border-collapse: collapse; table-layout: auto; }

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
            white-space: nowrap;
        }

        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .col-hidden { display: none; }

        .badge {
            display: inline-block;
            padding: .2em .35em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            border-radius: .25rem;
        }
        .badge-success { color: #fff; background-color: #28a745; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-danger  { color: #fff; background-color: #dc3545; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-warning { color: #212529; background-color: #ffc107; }

        .text-success   { color: #28a745; }
        .text-danger    { color: #dc3545; }
        .text-uppercase { text-transform: uppercase; }

        .print-footer { margin-top: 8mm; font-size: 7.5px; color: #666; }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ asset('master item/ipp.jpg') }}"
                     style="max-width: 75px; max-height: 55px; object-fit: contain;">
            </td>
            <td class="title">LAPORAN DATA INCOMING CHEMICAL</td>
            <td class="doc-info">
                <table>
                    <tr><td>No. Dokumen</td><td>: {{ $docHeader['no_dokumen'] }}</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: {{ $docHeader['tgl_terbit'] }}</td></tr>
                    <tr><td>Revisi / Tgl</td><td>: {{ $docHeader['revisi'] }}</td></tr>
                    <tr><td>Halaman</td><td>: {{ $docHeader['halaman'] }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="sub-header">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}<br>
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <td colspan="19" style="height:4mm; border:none; padding:0; background:#fff;"></td>
            </tr>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Jam (Before)</th>
                <th rowspan="2">Jam (After)</th>
                <th rowspan="2">Cycle (s)</th>
                <th rowspan="2">Chemical Name</th>
                <th rowspan="2">Supplier</th>
                <th rowspan="2">Part No</th>
                <th rowspan="2">Tgl Datang</th>
                <th rowspan="2">Expired</th>
                <th rowspan="2">Lot/Batch</th>
                <th colspan="3">Qty (Kg)</th>
                <th rowspan="2">Result</th>
                <th colspan="2">Detail NG</th>
                <th rowspan="2">QC</th>
                <th rowspan="2">Ket</th>
            </tr>
            <tr>
                <th>Total</th>
                <th>Komp.</th>
                <th>Samp.</th>
                <th>Pcs</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $cs)
                @php
                    $defectsData = is_array($cs->defects)
                        ? $cs->defects
                        : json_decode($cs->defects, true);
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
                    <td>{{ date('d/m/y', strtotime($cs->date)) }}</td>
                    <td>{{ $cs->created_at->copy()->subSeconds($cs->cycle_time ?? 0)->format('H:i') }}</td>
                    <td>{{ $cs->created_at->format('H:i') }}</td>
                    <td>{{ $cs->cycle_time ?? '-' }}</td>
                    <td>{{ $cs->item->name ?? '-' }}</td>
                    <td>{{ $cs->item->customer ?? '-' }}</td>
                    <td>{{ $cs->item->part_number ?? '-' }}</td>
                    <td>{{ date('d/m/y', strtotime($cs->tanggal_datang)) }}</td>
                    <td>{{ date('d/m/y', strtotime($cs->expired_date)) }}</td>
                    <td>{{ $cs->lot_batch_number }}</td>
                    <td>{{ (float) $cs->quantity_kg }}</td>
                    <td>{{ (float) $cs->komper_jirigen_kg }}</td>
                    <td>{{ (float) $cs->sampling_size_jirigen_kg }}</td>
                    <td>
                        <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $cs->judgment }}
                        </span>
                    </td>
                    <td class="text-danger" style="font-size:6.5px;">
                        {!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}
                    </td>
                    <td class="text-danger" style="font-size:6.5px;">
                        {!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}
                    </td>
                    <td class="text-uppercase">{{ $cs->operator_initials }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $cs->remarks ?? '-' }}</td>
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

        window.onload = function () { window.print(); };
    </script>

</body>
</html>
