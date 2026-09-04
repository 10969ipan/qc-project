<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
    $docHeader = isset($docHeader) ? $docHeader : \App\Models\GeneralSetting::getDocHeader('plating', $headerPlantCode, [
        'judul'      => 'LAPORAN CHECK SHEET PLATING',
        'no_dokumen' => $headerPlantCode === 'jakarta' ? 'QC-JKT-F-034/0' : 'QC-KRW-F-0183',
        'tgl_terbit' => $headerPlantCode === 'jakarta' ? '18.02.2022' : '25/03/2015',
        'revisi'     => $headerPlantCode === 'jakarta' ? '0 / 30-Dec-99' : '3 / 22/12/2025',
        'halaman'    => '1/1'
    ]);
    $isVerification = request('view_mode') === 'verifikasi';
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
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000 !important;
            background: #fff !important;
            margin: 0;
            padding: 8mm 8mm 5mm 8mm;
        }

        /* ===== HEADER DOKUMEN ===== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            color: #000 !important;
        }

        .header-table td {
            border: 1px solid #000 !important;
            padding: 4px;
            vertical-align: middle;
            color: #000 !important;
        }

        .logo {
            width: 90px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #000 !important;
            text-transform: uppercase;
        }

        .doc-info {
            width: 170px;
            font-size: 8px;
            color: #000 !important;
        }

        .doc-info table { width: 100%; border: none !important; }
        .doc-info td   { border: none !important; padding: 1px 2px; color: #000 !important; }

        /* ===== INFO PERIODE ===== */
        .sub-header {
            margin-bottom: 8px;
            font-size: 8.5px;
            color: #000 !important;
        }

        /* ===== TABEL DATA ===== */
        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            margin-top: 0;
            color: #000 !important;
        }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        .table th {
            border: 1px solid #000 !important;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
            color: #000 !important;
        }

        .table td {
            border: 1px solid #000 !important;
            padding: 2px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            color: #000 !important;
        }

        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .print-footer {
            margin-top: 6mm;
            font-size: 7.5px;
            color: #000 !important;
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
                     style="max-width: 75px; max-height: 50px; object-fit: contain;">
            </td>
            <td class="title">{{ $docHeader['judul'] ?? 'LAPORAN CHECK SHEET PLATING' }}</td>
            <td class="doc-info">
                <table>
                    <tr><td style="width: 70px;">No. Dokumen</td><td>: {{ $docHeader['no_dokumen'] ?? '-' }}</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: {{ $docHeader['tgl_terbit'] ?? '-' }}</td></tr>
                    <tr><td>Revisi Ke</td><td>: {{ $docHeader['revisi'] ?? '-' }}</td></tr>
                    <tr><td>Hal</td><td>: {{ $docHeader['halaman'] ?? '1/1' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Sub-header: Periode --}}
    <div class="sub-header">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}
        &nbsp;&nbsp;|&nbsp;&nbsp;<strong>Plant:</strong> {{ strtoupper($plantName ?? $headerPlantCode) }}
        @if(isset($selectedItem) && $selectedItem)
            &nbsp;&nbsp;|&nbsp;&nbsp;<strong>Item Part / Part No:</strong> {{ $selectedItem->name }} ({{ $selectedItem->part_number ?? '-' }})
        @endif
    </div>

    {{-- Tabel Data --}}
    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Injection<br>(Tgl / Shift / Inisial)</th>
                <th rowspan="2">Plating<br>(Tgl / Shift / Lot)</th>
                <th rowspan="2">Checked<br>(Tgl / Shift / Inisial)</th>
                <th rowspan="2">Waktu Check<br>(Start - Finish / CT)</th>
                <th rowspan="2" style="min-width: 140px;">ITEM PART / PART NO / CUSTOMER</th>
                <th rowspan="2">Qty<br>(Total / Sampling)</th>
                <th rowspan="2">OK</th>
                <th rowspan="2">NG</th>
                @if(!$isVerification)
                    <th colspan="2">Detail NG</th>
                @endif
                <th rowspan="2">Judgment</th>
                @if(!$isVerification)
                    <th colspan="4">Approval Status</th>
                @endif
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr>
                @if(!$isVerification)
                    <th>Pcs</th>
                    <th>Jenis</th>
                    <th style="font-size: 5.5px;">{{ $headerPlantCode === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                    <th style="font-size: 5.5px;">Supervisor QC</th>
                    <th style="font-size: 5.5px;">Asst Mgr QC</th>
                    <th style="font-size: 5.5px;">Manager QC</th>
                @endif
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

                    $sec = (int) ($checksheet->cycle_time ?? 0);
                    $ctStr = ($sec > 0) ? (($sec < 60) ? ($sec . 's') : (floor($sec / 60) . 'm' . (($sec % 60 > 0) ? ' ' . ($sec % 60) . 's' : ''))) : '-';
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="white-space: nowrap;">
                        {{ $checksheet->injection_date ? $checksheet->injection_date->format('d/m/y') : '-' }} / {{ $checksheet->injection_shift ?? '-' }} / {{ $checksheet->injection_initials ?? '-' }}
                    </td>
                    <td style="white-space: nowrap;">
                        {{ $checksheet->plating_date ? $checksheet->plating_date->format('d/m/y') : '-' }} / {{ $checksheet->plating_shift ?? '-' }} / {{ $checksheet->no_lot ?? '-' }}
                    </td>
                    <td style="white-space: nowrap;">
                        {{ \Carbon\Carbon::parse($checksheet->date)->format('d/m/y') }} / {{ $checksheet->shift }} / {{ strtoupper($checksheet->user->initials ?? $checksheet->operator_initials ?? '-') }}
                    </td>
                    <td style="white-space: nowrap;">
                        {{ $checksheet->created_at ? $checksheet->created_at->copy()->subSeconds($sec)->format('H:i') : '-' }} - {{ $checksheet->created_at ? $checksheet->created_at->format('H:i') : '-' }} ({{ $ctStr }})
                    </td>

                    {{-- Item Part / Part No / Customer Combined Column --}}
                    <td style="text-align: left;">
                        <div style="font-weight: bold; font-size: 8.5px; color: #000;">{{ $checksheet->item->name ?? '-' }}</div>
                        <div style="font-size: 7px; color: #000;">{{ $checksheet->item->part_number ?? '-' }}</div>
                        <div style="font-size: 7px; color: #000;">{{ $checksheet->item->customer ?? '-' }}</div>
                    </td>

                    <td style="white-space: nowrap;">
                        {{ number_format($checksheet->total_qty) }} / {{ number_format($checksheet->sampling_qty) }} Pcs
                    </td>
                    <td style="font-weight: bold; color: #000;">{{ $checksheet->total_ok }}</td>
                    <td style="font-weight: bold; color: #000;">{{ $checksheet->total_ng }}</td>

                    @if(!$isVerification)
                        <td style="font-size: 6.5px; color: #000;">
                            {!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}
                        </td>
                        <td style="font-size: 6.5px; color: #000;">
                            {!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}
                        </td>
                    @endif

                    {{-- Judgment (Full Black Bold Text) --}}
                    <td style="font-weight: bold; white-space: nowrap; color: #000;">
                        {{ $checksheet->judgment }}
                    </td>

                    @if(!$isVerification)
                        {{-- 4 Approval Columns: Full Black Bold Text --}}
                        @foreach(['kashift_qc' => 'kashift_approved_at', 'supervisor_qc' => 'supervisor_approved_at', 'asst_manager_qc' => 'asst_manager_approved_at', 'manager_qc' => 'manager_approved_at'] as $field => $timeField)
                            <td style="white-space: nowrap; font-size: 6.5px; vertical-align: middle; color: #000;">
                                @if($checksheet->$field === 'REJECTED')
                                    <div style="font-weight: bold; font-size: 7px; color: #000;">REJECTED</div>
                                    <div style="font-size: 5.5px; color: #000; line-height: 1.1;">
                                        {{ getRejectorName($checksheet->rejection_remarks) }}
                                        @if($checksheet->$timeField)
                                            <br>{{ \Carbon\Carbon::parse($checksheet->$timeField)->format('d/m/y H:i') }}
                                        @endif
                                    </div>
                                @elseif($checksheet->$field)
                                    <div style="font-weight: bold; font-size: 7px; color: #000;">APPROVED</div>
                                    <div style="font-size: 5.5px; color: #000; line-height: 1.1;">
                                        {{ $checksheet->$field }}
                                        @if($checksheet->$timeField)
                                            <br>{{ \Carbon\Carbon::parse($checksheet->$timeField)->format('d/m/y H:i') }}
                                        @endif
                                    </div>
                                @else
                                    <div style="font-weight: bold; font-size: 7px; color: #000;">PENDING</div>
                                @endif
                            </td>
                        @endforeach
                    @endif

                    <td style="text-align:left; font-size:7px; min-width:70px; word-break: break-word; color: #000;">
                        @if($checksheet->rejection_remarks)
                            <span>REJECTED: {{ $checksheet->rejection_remarks }}</span>
                        @else
                            {{ $checksheet->remarks ?? '-' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
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
