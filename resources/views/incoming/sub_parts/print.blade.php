<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
    $docHeader = \App\Models\GeneralSetting::getDocHeader('incoming_sub_parts', $headerPlantCode, [
        'no_dokumen' => 'QC-KRW-F-0212',
        'tgl_terbit' => '01/01/2026',
        'revisi'     => '-',
        'halaman'    => '- / -'
    ]);
    $isVerification = (request('view_mode') === 'verifikasi');
    $approvalOrder = $approvalOrder ?? ['kashift', 'supervisor', 'asst_manager', 'manager'];
@endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Incoming Sub-Part</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            color: #000;
            margin: 0;
            padding: 6mm 6mm 5mm 6mm;
        }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .header-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .logo { width: 90px; text-align: center; }
        .title { text-align: center; font-size: 13px; font-weight: bold; color: #000; }
        .doc-info { width: 160px; font-size: 8.5px; }
        .doc-info table { width: 100%; border: none; }
        .doc-info td { border: none; padding: 1px 2px; }

        .sub-header { margin-bottom: 6px; font-size: 9px; color: #000; }

        .table { width: 100%; border-collapse: collapse; table-layout: auto; max-width: 100%; }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        .table th {
            border: 1px solid #000;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
            word-wrap: break-word;
        }

        .table td {
            border: 1px solid #000;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
            color: #000;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .font-weight-bold { font-weight: bold !important; }
        .text-nowrap { white-space: nowrap !important; }

        .print-footer { margin-top: 6mm; font-size: 7.5px; color: #000; }

        /* Dimension table */
        .dimension-table { width: 100%; border-collapse: collapse; margin: 0; color: #000 !important; }
        .dimension-table td, .dimension-table th {
            padding: 1px 2px !important;
            font-size: 6px;
            line-height: 1.1;
            border: 1px solid #000 !important;
            text-align: center;
            color: #000 !important;
        }
        .dimension-table th { background-color: #f2f2f2 !important; font-weight: bold; color: #000 !important; }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ asset('master item/ipp.jpg') }}"
                     style="max-width: 75px; max-height: 55px; object-fit: contain;">
            </td>
            <td class="title">LAPORAN DATA CHECKSHEET INCOMING SUB-PART</td>
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
        @if(!empty($selectedItem))
            <strong>Sub-Part Name / Part No:</strong> {{ $selectedItem->name }} ({{ $selectedItem->part_number ?? '-' }})
        @else
            <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}
        @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                @if($isVerification)
                    <th rowspan="2">QR-Code</th>
                @endif
                <th rowspan="2">Checked<br>(Tgl / Shift / Inisial)</th>
                <th rowspan="2">Waktu Check<br>(Start - Finish / CT)</th>
                <th rowspan="2">SUB-PART NAME / PART NO / SUPPLIER</th>
                <th rowspan="2">Tanggal Datang</th>
                <th rowspan="2">Lot/Batch</th>
                <th colspan="2">Qty (Pcs)</th>
                <th rowspan="2" style="min-width: 120px;">Check Dimensi</th>
                <th rowspan="2">Judgment</th>
                @if(!$isVerification)
                    <th colspan="2">Detail NG</th>
                    <th colspan="4">Approval Status</th>
                @endif
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th>Total (Pcs)</th>
                <th>Sampling Size</th>
                @if(!$isVerification)
                    <th>Pcs</th>
                    <th>Jenis NG</th>
                    <th style="font-size: 5.5px;">{{ $headerPlantCode === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
                    <th style="font-size: 5.5px;">Supervisor QC</th>
                    <th style="font-size: 5.5px;">Asst Mgr QC</th>
                    <th style="font-size: 5.5px;">Manager QC</th>
                @endif
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

                    $sec = (int) ($cs->cycle_time ?? 0);
                    $ctStr = ($sec > 0) ? (($sec < 60) ? ($sec . 's') : (floor($sec / 60) . 'm' . (($sec % 60 > 0) ? ' ' . ($sec % 60) . 's' : ''))) : '-';
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @if($isVerification)
                        <td style="font-size: 6.5px;">{{ $cs->qrcode ?? '-' }}</td>
                    @endif
                    <td class="text-nowrap">
                        {{ date('d-m-Y', strtotime($cs->date)) }} / {{ $cs->shift ?? '1' }} / {{ $cs->operator_initials ?? '-' }}
                    </td>
                    <td class="text-nowrap">
                        {{ $cs->created_at ? $cs->created_at->copy()->subSeconds($sec)->format('H:i') : '-' }} - {{ $cs->created_at ? $cs->created_at->format('H:i') : '-' }} ({{ $ctStr }})
                    </td>
                    <td class="text-left" style="line-height: 1.1;">
                        <strong style="font-size: 8px;">{{ $cs->item->name ?? '-' }}</strong><br>
                        <span style="font-size: 6.5px;">{{ $cs->item->part_number ?? '-' }}</span><br>
                        <span style="font-size: 6.5px;">{{ $cs->item->customer ?? '-' }}</span>
                    </td>
                    <td class="text-nowrap">{{ date('d-m-Y', strtotime($cs->tanggal_datang)) }}</td>
                    <td class="text-nowrap font-weight-bold">{{ $cs->lot_batch_number }}</td>
                    <td class="font-weight-bold">{{ (float) $cs->quantity }}</td>
                    <td>{{ (float) $cs->sampling_size_pcs }}</td>
                    @php
                        $dimData = is_array($cs->check_dimensi)
                            ? $cs->check_dimensi
                            : json_decode($cs->check_dimensi ?? '', true);
                        $dimData = is_array($dimData) ? $dimData : [];

                        $hasUserInputs = false;
                        $flatPoints = [];
                        foreach ($dimData as $k => $v) {
                            if (is_array($v)) {
                                foreach ($v as $pIdx => $pVal) {
                                    if ($pVal !== null && $pVal !== '' && $pVal !== '-') {
                                        $hasUserInputs = true;
                                        $flatPoints[$pIdx] = $pVal;
                                    }
                                }
                            } else {
                                if ($v !== null && $v !== '' && $v !== '-') {
                                    $hasUserInputs = true;
                                    $pIdx = is_numeric($k) ? (int) $k : $k;
                                    $flatPoints[$pIdx] = $v;
                                }
                            }
                        }

                        $itemStandardsRaw = $cs->item->dimension_standards ?? null;
                        $standards = [];
                        if (!empty($itemStandardsRaw) && is_array($itemStandardsRaw)) {
                            foreach ($itemStandardsRaw as $idx => $std) {
                                if (is_array($std)) {
                                    $pKey = (string)($std['point'] ?? ($idx + 1));
                                    $standards[$pKey] = [
                                        'size' => $std['size'] ?? null,
                                        'tolerance' => $std['tolerance'] ?? null,
                                        'min' => $std['min'] ?? null,
                                        'max' => $std['max'] ?? null,
                                    ];
                                }
                            }
                        }

                        $activePoints = [];
                        foreach ($flatPoints as $pKey => $pVal) {
                            $activePoints[$pKey] = true;
                        }
                        foreach ($standards as $pKey => $std) {
                            $activePoints[$pKey] = true;
                        }
                        $activePoints = array_keys($activePoints);
                        sort($activePoints);

                        if (empty($activePoints) && $hasUserInputs) {
                            $activePoints = range(1, count($flatPoints));
                        }
                    @endphp
                    <td style="padding: 1px; vertical-align: middle;">
                        @if($hasUserInputs && !empty($activePoints))
                            <table class="dimension-table">
                                <thead>
                                    @php
                                        $hasStdData = false;
                                        foreach ($activePoints as $j) {
                                            if (isset($standards[$j]) && ($standards[$j]['size'] !== null && $standards[$j]['size'] !== '' && $standards[$j]['size'] !== '-')) {
                                                $hasStdData = true; break;
                                            }
                                        }
                                    @endphp
                                    @if($hasStdData)
                                        <tr>
                                            @foreach ($activePoints as $j)
                                                <th>{{ isset($standards[$j]) ? $standards[$j]['size'] : '-' }}</th>
                                            @endforeach
                                        </tr>
                                    @endif
                                    <tr>
                                        @foreach($activePoints as $j)
                                            <th>Ø{{ $j }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach($activePoints as $j)
                                            @php
                                                $val = $flatPoints[$j] ?? '-';
                                            @endphp
                                            <td>{{ ($val !== '' && $val !== null) ? $val : '-' }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        @else
                            {{ (is_string($cs->check_dimensi) && $cs->check_dimensi && $cs->check_dimensi !== '[]' && $cs->check_dimensi !== '{}') ? $cs->check_dimensi : '-' }}
                        @endif
                    </td>
                    <td>
                        <strong>{{ $cs->judgment }}</strong>
                    </td>
                    @if(!$isVerification)
                        <td style="font-size: 6.5px;">
                            {!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}
                        </td>
                        <td style="font-size: 6.5px; text-align: left;">
                            {!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}
                        </td>
                        @foreach ($approvalOrder as $role)
                            @php
                                $field = getApprovalField($role);
                                $dateField = getApprovalDateField($role);
                                $status = $cs->$field;
                                $date = $cs->$dateField;
                            @endphp
                            <td class="text-center" style="font-size: 6px; line-height: 1.1;">
                                @if($status === 'REJECTED')
                                    <strong>REJECTED</strong><br>
                                    <span>oleh {{ getRejectorName($cs->rejection_remarks) }}</span>
                                    @if($date)
                                        <br><span>{{ \Carbon\Carbon::parse($date)->format('d/m/y H:i') }}</span>
                                    @endif
                                @elseif($status && $status !== 'Pending')
                                    <strong>APPROVED</strong><br>
                                    <span>oleh {{ $status }}</span>
                                    @if($date)
                                        <br><span>{{ \Carbon\Carbon::parse($date)->format('d/m/y H:i') }}</span>
                                    @endif
                                @else
                                    <span>PENDING</span>
                                @endif
                            </td>
                        @endforeach
                    @endif
                    <td class="text-left" style="font-size: 6.5px; word-break: break-all;">{{ $cs->remarks ?? '-' }}</td>
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
