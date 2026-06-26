<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('plating', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Plating</title>
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
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6.5px;
            white-space: nowrap;
        }

        .table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            word-wrap: break-word;
        }

        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .badge {
            display: inline-block;
            padding: .2em .3em;
            font-size: 70%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            border-radius: .2rem;
        }
        .badge-success { color: #fff; background-color: #28a745; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-danger  { color: #fff; background-color: #dc3545; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .text-success   { color: #28a745; }
        .text-danger    { color: #dc3545; }
        .text-uppercase { text-transform: uppercase; }

        .col-compact { white-space: nowrap; width: 1%; }
        .w-barang    { width: 12%; }
        .w-part-no   { width: 10%; }
        .w-cust      { width: 10%; }
        .w-ket       { width: 15%; }

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
            <td class="title">LAPORAN DATA CHECKSHEET PLATING</td>
            <td class="doc-info">
                <table>
                    <tr><td>No. Dokumen</td><td>: QC-KRW-F-0183</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: 25/03/2015</td></tr>
                    <tr><td>Revisi / Tgl</td><td>: 3 / 22/12/2025</td></tr>
                    <tr><td>Halaman</td><td>: 1/1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="sub-header">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}<br>
        <strong>Plant:</strong> Karawang
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2" class="col-compact">No</th>
                <th rowspan="2" class="col-compact">Injection<br>(Tgl / Shift)</th>
                <th rowspan="2" class="col-compact">Plating<br>(Tgl / Shift / Lot)</th>
                <th rowspan="2" class="col-compact">Quality<br>(Tgl / Shift)</th>
                <th rowspan="2" class="col-compact">Jam (Before)</th>
                <th rowspan="2" class="col-compact">Jam (After)</th>
                <th rowspan="2" class="col-compact">Cycle</th>
                <th rowspan="2" class="w-barang">Barang</th>
                <th rowspan="2" class="w-part-no">Part No</th>
                <th rowspan="2" class="w-cust">Customer</th>
                <th rowspan="2" class="col-compact">Total</th>
                <th rowspan="2" class="col-compact">OK</th>
                <th rowspan="2" class="col-compact">NG</th>
                <th colspan="2" class="col-compact">Detail NG</th>
                <th rowspan="2" class="col-compact">Judgment</th>
                <th rowspan="2" class="col-compact">Inisial</th>
                <th rowspan="2" class="w-ket">Ket</th>
            </tr>
            <tr>
                <th style="width: 30px; min-width: 30px;">Pcs</th>
                <th style="width: 80px; min-width: 80px;">Jenis NG</th>
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
                    <td class="col-compact">{{ $loop->iteration }}</td>
                    <td class="col-compact">
                        {{ $checksheet->injection_date ? $checksheet->injection_date->format('d/m/y') : '-' }} / {{ $checksheet->injection_shift ?? '-' }}
                    </td>
                    <td class="col-compact">
                        {{ $checksheet->plating_date ? $checksheet->plating_date->format('d/m/y') : '-' }} / {{ $checksheet->plating_shift ?? '-' }} / {{ $checksheet->no_lot ?? '-' }}
                    </td>
                    <td class="col-compact">
                        {{ \Carbon\Carbon::parse($checksheet->date)->format('d/m/y') }} / {{ $checksheet->shift }}
                    </td>
                    <td class="col-compact">{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                    <td class="col-compact">{{ $checksheet->created_at->format('H:i') }}</td>
                    <td class="col-compact">{{ $checksheet->cycle_time ?? '-' }}</td>
                    <td style="text-align:left;">{{ $checksheet->item->name ?? '-' }}</td>
                    <td style="text-align:left;">{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td style="text-align:left;">{{ $checksheet->item->customer ?? '-' }}</td>
                    <td class="col-compact">{{ $checksheet->total_qty }}</td>
                    <td class="col-compact text-success">{{ $checksheet->total_ok }}</td>
                    <td class="col-compact text-danger">{{ $checksheet->total_ng }}</td>
                    <td colspan="2" class="p-0 align-middle">
                        @if(count($pcsLines) > 0)
                            <table style="width: 100%; border-collapse: collapse; margin: 0; border: none; table-layout: fixed;">
                                <tbody>
                                    @foreach($pcsLines as $index => $qty)
                                        <tr style="border-bottom: {{ $index < count($pcsLines) - 1 ? '1px solid #000' : 'none' }};">
                                            <td style="width: 30px; min-width: 30px; border: none !important; border-right: 1px solid #000 !important; padding: 2px 3px; text-align: center; vertical-align: middle;" class="text-danger">
                                                {{ $qty }}
                                            </td>
                                            <td style="width: 80px; min-width: 80px; border: none !important; padding: 2px 3px; text-align: center; vertical-align: middle; font-size: 7px;" class="text-danger">
                                                {{ $nameLines[$index] ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            -
                        @endif
                    </td>
                    <td class="col-compact">
                        <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $checksheet->judgment }}
                        </span>
                    </td>
                    <td class="col-compact text-uppercase">{{ $checksheet->operator_initials }}</td>
                    <td style="text-align:left;">{{ $checksheet->remarks ?? '-' }}</td>
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
