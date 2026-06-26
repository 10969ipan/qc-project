<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('sortir', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Hasil Sortir</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
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
            font-size: 6px;
            white-space: nowrap;
        }
        .table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
            word-wrap: break-word;
        }
        tbody tr { page-break-inside: avoid; break-inside: avoid; }

        .badge { display: inline-block; padding: .2em .3em; font-size: 70%; font-weight: 700; line-height: 1; text-align: center; border-radius: .2rem; }
        .badge-success { color: #fff; background-color: #28a745; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-danger { color: #fff; background-color: #dc3545; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-warning { color: #212529; background-color: #ffc107; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-info { color: #fff; background-color: #17a2b8; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-primary { color: #fff; background-color: #007bff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-secondary { color: #fff; background-color: #6c757d; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .text-success { color: #28a745; }
        .text-danger  { color: #dc3545; }
        .text-uppercase { text-transform: uppercase; }
        .col-compact { white-space: nowrap; width: 1%; }
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
            <td class="title">LAPORAN DATA HASIL SORTIR</td>
            <td class="doc-info">
                <table>
                    <tr><td>No. Dokumen</td><td>: {{ $plantCode === 'jakarta' ? 'QC-JKT-F-034/0' : 'QC-KRW-F-0213' }}</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: {{ $plantCode === 'jakarta' ? '18.02.2022' : '25/03/2015' }}</td></tr>
                    <tr><td>Revisi / Tgl</td><td>: {{ $plantCode === 'jakarta' ? '0 / 30-Dec-99' : '3 / 22/12/2025' }}</td></tr>
                    <tr><td>Halaman</td><td>: 1/1</td></tr>
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
                <td colspan="17" style="height:4mm; border:none; padding:0; background:#fff;"></td>
            </tr>
            <tr>
                <th rowspan="2" class="col-compact">No</th>
                <th rowspan="2" class="col-compact">Tanggal</th>
                <th rowspan="2" class="col-compact">Shift</th>
                <th rowspan="2" class="col-compact">Line</th>
                <th rowspan="2" class="col-compact">Sumber</th>
                <th rowspan="2" style="width:14%">Item Part</th>
                <th rowspan="2" style="width:10%">Customer</th>
                <th rowspan="2" style="width:10%">Part No</th>
                <th rowspan="2" class="col-compact">Total</th>
                <th rowspan="2" class="col-compact">Sample</th>
                <th rowspan="2" class="col-compact">OK</th>
                <th rowspan="2" class="col-compact">NG</th>
                <th colspan="2" class="col-compact">Detail NG</th>
                <th rowspan="2" class="col-compact">Judgment</th>
                <th rowspan="2" class="col-compact">Inisial</th>
                <th rowspan="2" style="width:10%">Keterangan</th>
            </tr>
            <tr>
                <th class="col-compact">Pcs</th>
                <th class="col-compact">Jenis NG</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $checksheet)
                @php
                    $defectsData = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
                    $pcsLines  = [];
                    $nameLines = [];
                    if (is_array($defectsData)) {
                        foreach ($defectsData as $d) {
                            if (is_array($d) && isset($d['type'])) { $pcsLines[] = $d['qty'] ?? 1; $nameLines[] = $d['type']; }
                            elseif (is_string($d)) { $pcsLines[] = 1; $nameLines[] = $d; }
                        }
                    }
                    $badgeMap = ['sub_assy' => 'warning', 'in_process' => 'info', 'cross_cut' => 'primary'];
                    $badgeClass = $badgeMap[$checksheet->source_type] ?? 'secondary';
                @endphp
                <tr>
                    <td class="col-compact">{{ $loop->iteration }}</td>
                    <td class="col-compact">{{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-y') }}</td>
                    <td class="col-compact">{{ $checksheet->shift }}</td>
                    <td class="col-compact">{{ $checksheet->line ?? '-' }}</td>
                    <td class="col-compact">
                        <span class="badge badge-{{ $badgeClass }}">
                            {{ strtoupper(str_replace('_', ' ', $checksheet->source_type)) }}
                        </span>
                    </td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->name ?? '-' }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->customer ?? '-' }}</td>
                    <td style="text-align:left; font-size:6.5px;">{{ $checksheet->item->part_number ?? '-' }}</td>
                    <td class="col-compact">{{ $checksheet->total_qty }}</td>
                    <td class="col-compact">{{ $checksheet->sampling_qty }}</td>
                    <td class="col-compact text-success">{{ $checksheet->total_ok }}</td>
                    <td class="col-compact text-danger">{{ $checksheet->total_ng }}</td>
                    <td class="col-compact text-danger" style="font-size:6px;">{!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}</td>
                    <td class="col-compact text-danger" style="font-size:6px;">{!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}</td>
                    <td class="col-compact">
                        <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">{{ $checksheet->judgment }}</span>
                    </td>
                    <td class="col-compact text-uppercase">{{ $checksheet->operator_initials ?? '-' }}</td>
                    <td style="text-align:left; font-size:6px;">
                        @if($checksheet->next_proses)
                            <span class="badge badge-warning">{{ $checksheet->next_proses }}</span><br>
                        @endif
                        {{ str_replace('[SORTIR_CLOSED]', '[CLOSE]', $checksheet->remarks ?? '-') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="print-footer"><span id="footerDateTime"></span></div>

    <script>
        (function () {
            var now = new Date();
            var pad = function(n){ return n < 10 ? '0' + n : n; };
            document.getElementById('footerDateTime').textContent = 'Dicetak: '
                + pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear()
                + '  ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
        })();
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
