<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
    $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_parts', $headerPlantCode, [
        'no_dokumen' => 'QC-KRW-F-0210',
        'tgl_terbit' => '01/01/2026',
        'revisi'     => '-',
        'halaman'    => '- / -'
    ]);
@endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Incoming Part</title>
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
            font-size: 6.5px;
            white-space: nowrap;
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
        .badge-warning { color: #212529; background-color: #ffc107; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

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
            <td class="title">LAPORAN DATA CHECKSHEET INCOMING PART</td>
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
                <th rowspan="2">No</th>
                <th rowspan="2">Tgl & Shift Check</th>
                <th rowspan="2">Item Part</th>
                <th rowspan="2">Customer / Supplier</th>
                <th rowspan="2">Qty Datang Awal</th>
                <th rowspan="2">Qty Balance Sisa</th>
                <th rowspan="2">Total Check</th>
                <th rowspan="2">Tgl & Shift Datang</th>
                <th rowspan="2">OK</th>
                <th rowspan="2">NG</th>
                <th colspan="2">Detail NG</th>
                <th rowspan="2">Judgment</th>
                <th rowspan="2">QC Inisial</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Pcs</th>
                <th>Jenis NG</th>
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
                    <td>
                        {{ date('d/m/Y', strtotime($cs->date)) }}
                        <br><small style="color:#666;">Shift {{ $cs->shift }}</small>
                    </td>
                    <td style="text-align:left;">
                        <strong>{{ $cs->item->name ?? '-' }}</strong><br>
                        <small style="color:#666;">{{ $cs->item->part_number ?? '-' }}</small>
                    </td>
                    <td>{{ $cs->item->customer ?? '-' }}</td>
                    <td>{{ number_format($cs->arrival ? $cs->arrival->qty_datang : ($cs->lot_qty ?? 0)) }} pcs</td>
                    <td>
                        @php
                            $printSisa = isset($cs->qty_balance_sisa) ? $cs->qty_balance_sisa : ($cs->arrival ? $cs->arrival->qty_sisa : 0);
                            $printStatus = ($printSisa <= 0) ? 'COMPLETED' : 'OPEN';
                        @endphp
                        {{ number_format($printSisa) }} pcs ({{ $printStatus }})
                    </td>
                    <td><strong>{{ number_format($cs->total_check) }} pcs</strong></td>
                    <td>
                        @if($cs->tanggal_datang)
                            {{ date('d/m/Y', strtotime($cs->tanggal_datang)) }}
                            @if($cs->arrival && $cs->arrival->shift_datang)
                                <br><small style="color:#666;">Shift {{ $cs->arrival->shift_datang }}</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-success font-weight-bold">{{ number_format(max(0, $cs->total_check - $cs->total_ng)) }}</td>
                    <td class="text-danger font-weight-bold">{{ number_format($cs->total_ng) }}</td>
                    <td class="text-danger" style="font-size:6.5px;">
                        {!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}
                    </td>
                    <td class="text-danger" style="font-size:6.5px;">
                        {!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}
                    </td>
                    <td>
                        <span class="badge badge-{{ $cs->judgment == 'OK' ? 'success' : 'danger' }}">
                            {{ $cs->judgment }}
                        </span>
                    </td>
                    <td class="text-uppercase">{{ $cs->operator_initials ?? '-' }}</td>
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
