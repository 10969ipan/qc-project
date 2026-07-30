<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Standard Performance Test</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 7px;
            color: #333;
            margin: 0;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .logo { width: 80px; text-align: center; }
        .title { text-align: center; font-size: 14px; font-weight: bold; }
        .doc-info { width: 180px; font-size: 9px; }
        .doc-info table { width: 100%; border: none; }
        .doc-info td { border: none; padding: 1px 2px; }

        .table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .table th {
            background-color: #f2f2f2;
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            font-size: 7px;
        }
        .table td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            font-size: 7px;
            word-break: break-word;
        }
        .footer { margin-top: 10px; font-size: 8px; color: #666; text-align: right; }
        
        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
        }
        .badge-ok { background-color: #1cc88a; }
        .badge-ng { background-color: #e74a3b; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ asset('master item/ipp.jpg') }}" style="max-width: 60px; max-height: 45px;">
            </td>
            <td class="title">
                @php
                    $displayTitle = ucwords(str_replace('_', ' ', $testType));
                    if (!str_ends_with(strtolower($displayTitle), 'test')) {
                        $displayTitle .= ' Test';
                    }
                @endphp
                LAPORAN {{ strtoupper($displayTitle) }}
            </td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: {{ $docHeader['no_dokumen'] }}</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: {{ $docHeader['tgl_terbit'] }}</td>
                    </tr>
                    <tr>
                        <td>Revisi / Tgl</td>
                        <td>: {{ $docHeader['revisi'] }}</td>
                    </tr>
                    <tr>
                        <td>Halaman</td>
                        <td>: {{ $docHeader['halaman'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="3">No.</th>
                <th rowspan="3">Name Part</th>
                <th rowspan="3">Customer</th>
                <th rowspan="3">Standard Customer</th>
                <th rowspan="3">Kategori</th>
                <th colspan="7">STANDARD</th>
                <th colspan="10">ACTUAL</th>
                <th rowspan="3">Tgl Produksi</th>
                <th rowspan="3">Shift</th>
                <th rowspan="3">No Lot</th>
                @if($testType == 'corrodkote')
                <th rowspan="3">Aktual % Corrosion</th>
                @endif
                <th rowspan="3">Tgl Check</th>
                <th rowspan="3">Result</th>
                <th rowspan="3">PIC</th>
                <th rowspan="3">Description</th>
            </tr>
            <tr>
                <!-- STANDAR -->
                <th colspan="3">Thickness (&micro;m)</th>
                <th>Corrodkote</th>
                <th>Cass Test</th>
                <th>Salt Spray</th>
                <th rowspan="2">Porecount Test</th>
                <!-- AKTUAL -->
                <th colspan="3">Thickness (&micro;m)</th>
                <th colspan="2">Corrodkote</th>
                <th colspan="2">Cass Test</th>
                <th colspan="2">Salt Spray</th>
                <th rowspan="2">Porecount Test</th>
            </tr>
            <tr>
                <!-- STANDAR THICKNESS -->
                <th>Cr</th>
                <th>Ni</th>
                <th>Cu</th>
                <th>Time (Hours)</th>
                <th>Time (Hours)</th>
                <th>Time (Hours)</th>
                <!-- AKTUAL THICKNESS -->
                <th>Cr</th>
                <th>Ni</th>
                <th>Cu</th>
                <!-- AKTUAL Corrodkote -->
                <th>Time</th>
                <th>Result</th>
                <!-- AKTUAL Cass -->
                <th>Time</th>
                <th>Result</th>
                <!-- AKTUAL Salt Spray -->
                <th>Time</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ optional($report->standard)->part_name ?? '-' }}</td>
                    <td>{{ optional($report->standard)->customer_name ?? '-' }}</td>
                    <td>{{ optional($report->standard)->customer_standard ?? '-' }}</td>
                    <td>{{ optional($report->standard)->category ?? '-' }}</td>
                    <!-- STANDAR -->
                    <td>{{ optional($report->standard)->thickness_cr ?? '-' }}</td>
                    <td>{{ optional($report->standard)->thickness_ni ?? '-' }}</td>
                    <td>{{ optional($report->standard)->thickness_cu ?? '-' }}</td>
                    <td>{{ optional($report->standard)->corrodkote_time ?? '-' }}</td>
                    <td>{{ optional($report->standard)->cass_time ?? '-' }}</td>
                    <td>{{ optional($report->standard)->salt_spray_time ?? '-' }}</td>
                    <td>{{ optional($report->standard)->porecount_std_min ?? '-' }}</td>
                    <!-- AKTUAL -->
                    <td>{{ $report->actual_cr ?? '-' }}</td>
                    <td>{{ $report->actual_ni ?? '-' }}</td>
                    <td>{{ $report->actual_cu ?? '-' }}</td>
                    <td>{{ $report->actual_corrodkote_waktu ?? '-' }}</td>
                    <td>{{ $report->standar_jam_corrodkote ?? '-' }}</td>
                    <td>{{ $report->actual_cass_waktu ?? '-' }}</td>
                    <td>{{ $report->standar_jam_cass ?? '-' }}</td>
                    <td>{{ $report->actual_salt_spray_waktu ?? '-' }}</td>
                    <td>{{ $report->standar_jam_salt_spray ?? '-' }}</td>
                    <td>{{ $report->actual_porecount ?? '-' }}</td>
                    <!-- INFO -->
                    <td>{{ $report->production_date ? \Carbon\Carbon::parse($report->production_date)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $report->shift ?? '-' }}</td>
                    <td>{{ $report->lot_no ?? '-' }}</td>
                    @if($testType == 'corrodkote')
                    <td>
                        {{ (isset($report->aktual_corrosion) && $report->aktual_corrosion !== '' && $report->aktual_corrosion !== '-') ? $report->aktual_corrosion . '%' : '-' }}
                    </td>
                    @endif
                    <td>{{ $report->tanggal_cek ? \Carbon\Carbon::parse($report->tanggal_cek)->format('d-m-Y') : '-' }}</td>
                    <td>
                        @php
                            $rjRaw = $report->result_judgment ?? '-';
                            if ($testType == 'salt_spray') {
                                $rjRaw = $report->result_judgment_salt_spray ?? $report->result_judgment ?? '-';
                            }
                            $rjLower = strtolower(trim($rjRaw));
                        @endphp
                        @if($testType == 'salt_spray')
                            @if(str_contains($rjLower, 'ok') || str_contains($rjLower, 'no rust'))
                                <span class="badge badge-ok">OK</span><br><span style="font-size:6px; color:#555;">No Rust</span>
                            @elseif(str_contains($rjLower, 'white'))
                                <span class="badge badge-ng">NG</span><br><span style="font-size:6px; color:#e74a3b; font-weight:bold;">White Rust</span>
                            @elseif(str_contains($rjLower, 'red'))
                                <span class="badge badge-ng">NG</span><br><span style="font-size:6px; color:#e74a3b; font-weight:bold;">Red Rust</span>
                            @else
                                <span class="badge badge-ng">NG</span>
                            @endif
                        @else
                            @if($rjLower === 'ok')
                                <span class="badge badge-ok">OK</span>
                            @elseif($rjLower === 'ng')
                                <span class="badge badge-ng">NG</span>
                            @else
                                {{ $rjRaw }}
                            @endif
                        @endif
                    </td>
                    <td>{{ optional($report->analyst)->name ?? '-' }}</td>
                    <td style="text-align: left;">{{ $report->description ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="29" style="padding: 20px;">Tidak ada data laporan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
