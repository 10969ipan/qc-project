<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
    $docHeader = isset($docHeader) ? $docHeader : \App\Models\GeneralSetting::getDocHeader('cross_cut_painting', $headerPlantCode, [
        'judul'      => 'LAPORAN CHECK SHEET CROSS CUT PAINTING',
        'no_dokumen' => 'QC-KRW-F-0215',
        'tgl_terbit' => '25/03/2015',
        'revisi'     => '3',
        'tgl_revisi' => '22/12/2025',
        'halaman'    => '1/1'
    ]);
    $isVerification = request('view_mode') === 'verifikasi';
@endphp
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
            color: #000;
            margin: 0;
            padding: 8mm 8mm 5mm 8mm;
        }

        /* ===== HEADER DOKUMEN ===== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
            color: #000;
        }

        .logo {
            width: 90px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }

        .doc-info {
            width: 170px;
            font-size: 8px;
            color: #000;
        }

        .doc-info table { width: 100%; border: none; }
        .doc-info td   { border: none; padding: 1px 2px; color: #000; }

        /* ===== INFO PERIODE ===== */
        .sub-header {
            margin-bottom: 8px;
            font-size: 8.5px;
            color: #000;
        }

        /* ===== TABEL DATA ===== */
        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            margin-top: 0;
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
            color: #000;
        }

        .table td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            color: #000;
        }

        /* ===== FOOTER KUSTOM ===== */
        .print-footer {
            margin-top: 6mm;
            font-size: 7.5px;
            color: #000;
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
            <td class="title">{{ $docHeader['judul'] }}</td>
            <td class="doc-info">
                <table>
                    <tr><td style="width: 70px;">No. Dokumen</td><td>: {{ $docHeader['no_dokumen'] }}</td></tr>
                    <tr><td>Tgl. Terbit</td><td>: {{ $docHeader['tgl_terbit'] }}</td></tr>
                    <tr><td>Revisi Ke</td><td>: {{ $docHeader['revisi'] }}</td></tr>
                    <tr><td>Tgl. Revisi</td><td>: {{ $docHeader['tgl_revisi'] ?? '-' }}</td></tr>
                    <tr><td>Hal</td><td>: {{ $docHeader['halaman'] }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Sub-header: Periode --}}
    <div class="sub-header">
        <strong>Periode:</strong> {{ $startDate }} s/d {{ $endDate }}
        @if(isset($selectedItem) && $selectedItem)
            &nbsp;&nbsp;|&nbsp;&nbsp;<strong>Item Part / Part No:</strong> {{ $selectedItem->name }} ({{ $selectedItem->part_number ?? '-' }})
        @endif
    </div>

    {{-- Tabel Data --}}
    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Prod.<br>(Tgl / Shift)</th>
                <th rowspan="2">Checked<br>(Tgl / Shift / Inisial)</th>
                <th rowspan="2">Waktu Check<br>(Start - Finish / CT)</th>
                <th rowspan="2" style="min-width: 140px;">ITEM PART / PART NO / CUSTOMER</th>
                <th colspan="3">Hasil Testing</th>
                <th rowspan="2">Judgement</th>
                <th colspan="6">Approval Status</th>
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th>Cross Cut</th>
                <th>Pencil Scratch</th>
                <th>Tap Test</th>
                <th style="font-size: 5.5px;">Karu QC</th>
                <th style="font-size: 5.5px;">Kashift Painting</th>
                <th style="font-size: 5.5px;">Supervisor QC</th>
                <th style="font-size: 5.5px;">Supervisor Painting</th>
                <th style="font-size: 5.5px;">Asst Mgr QC</th>
                <th style="font-size: 5.5px;">Asst Mgr Painting</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $checksheet)
                @php
                    $sec = (int) ($checksheet->cycle_time ?? 0);
                    $ctStr = ($sec > 0) ? (($sec < 60) ? ($sec . 's') : (floor($sec / 60) . 'm' . (($sec % 60 > 0) ? ' ' . ($sec % 60) . 's' : ''))) : '-';
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('d/m/y') }} / {{ $checksheet->production_shift }}</td>
                    <td style="white-space: nowrap;">
                        {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('d/m/y') }} / {{ $checksheet->qc_shift }} / {{ $checksheet->operator_initials ?? '-' }}
                    </td>
                    <td style="white-space: nowrap;">
                        {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->copy()->subSeconds($sec)->format('H:i') }} - {{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('H:i') }} ({{ $ctStr }})
                    </td>

                    {{-- Item Part / Part No / Customer Combined Column --}}
                    <td style="text-align: left;">
                        <div style="font-weight: bold; font-size: 8.5px; color: #000;">{{ $checksheet->item->name ?? '-' }}</div>
                        <div style="font-size: 7px; color: #000;">{{ $checksheet->item->part_number ?? '-' }}</div>
                        <div style="font-size: 7px; color: #000;">{{ $checksheet->item->customer ?? '-' }}</div>
                    </td>

                    <td style="font-weight: bold; color: #000;">{{ $checksheet->defects['cross_cut'] ?? 'OK' }}</td>
                    <td style="font-weight: bold; color: #000;">{{ $checksheet->pencil_scratch ?? 'OK' }}</td>
                    <td style="font-weight: bold; color: #000;">{{ $checksheet->tap_test ?? 'OK' }}</td>

                    <td style="font-weight: bold; color: #000;">
                        {{ $checksheet->position_remark_judgment ?? '-' }}
                    </td>

                    {{-- 6 Approval Columns: Full Black Bold Text --}}
                    @php
                        $approvalFields = [
                            'karu_qc'              => 'karu_qc_approved_at',
                            'kashift_plating'      => 'kashift_plating_approved_at',
                            'supervisor_qc'        => 'supervisor_approved_at',
                            'supervisor_plating'   => 'supervisor_plating_approved_at',
                            'asst_manager_qc'      => 'asst_manager_approved_at',
                            'asst_manager_plating' => 'asst_manager_plating_approved_at',
                        ];
                    @endphp
                    @foreach($approvalFields as $field => $timeField)
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

                    <td style="text-align:left; font-size:7px; min-width:70px; word-break: break-word; color: #000;">
                        @if($checksheet->rejection_remarks)
                            <span>REJECTED: {{ $checksheet->rejection_remarks }}</span>
                        @else
                            {{ $checksheet->keterangan ?? '-' }}
                        @endif
                    </td>
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
