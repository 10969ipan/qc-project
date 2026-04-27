<!DOCTYPE html>
<html>
<head>
    <title>Schedule Kalibrasi - {{ $year }}</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: sans-serif; font-size: 8pt; margin: 0; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { border: 1px solid #000; padding: 5px; }
        .logo { width: 50px; text-align: center; }
        .title { text-align: center; font-weight: bold; font-size: 10pt; text-transform: uppercase; }
        .doc-info { width: 150px; font-size: 7pt; }
        
        .schedule-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .schedule-table th, .schedule-table td { border: 1px solid #000; padding: 2px; text-align: center; vertical-align: middle; }
        .schedule-table th { background-color: #f0f0f0; }
        
        .tool-name { text-align: left; padding-left: 4px; font-weight: bold; }
        .serial { text-align: left; padding-left: 4px; color: #555; }
        
        .marker-p { background-color: #d1e7dd; }
        .marker-a { background-color: #cfe2ff; }
        
        .footer { margin-top: 20px; width: 100%; }
        .footer td { text-align: center; padding: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo"><img src="{{ public_path('master item/ipp.jpg') }}" height="35"></td>
            <td class="title">SCHEDULE KALIBRASI ALAT UKUR - {{ $year }}<br>PLANT {{ strtoupper($plantCode) }}</td>
            <td class="doc-info">
                No. Dokumen: {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-052' : 'QC-KRW-F-052' }}<br>
                Tgl. Terbit: 25/03/2015<br>
                Revisi / Tgl: 1 / 21/03/2018
            </td>
        </tr>
    </table>

    <table class="schedule-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 120px;">NAMA ALAT</th>
                <th rowspan="2" style="width: 80px;">NO. SERI</th>
                <th rowspan="2" style="width: 60px;">JENIS</th>
                <th rowspan="2" style="width: 20px;">P/A</th>
                @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $m)
                    <th colspan="4">{{ $m }}</th>
                @endforeach
            </tr>
            <tr>
                @for($i = 0; $i < 12; $i++)
                    @for($w = 1; $w <= 4; $w++)
                        <th>{{ $w }}</th>
                    @endfor
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($tools as $tool)
                @php
                    $plans = [];
                    foreach ($tool->schedules as $s) {
                        if ($s->schedule_date->format('Y') != $year) continue;
                        $m = (int) $s->schedule_date->format('n');
                        $d = (int) $s->schedule_date->format('j');
                        $w = (int) ceil($d / 7.75); if ($w > 4) $w = 4;
                        $plans[$m][$w] = true;
                    }
                    $actuals = [];
                    foreach ($tool->verifications as $v) {
                        if ($v->tanggal_verifikasi->format('Y') != $year) continue;
                        $m = (int) $v->tanggal_verifikasi->format('n');
                        $d = (int) $v->tanggal_verifikasi->format('j');
                        $w = (int) ceil($d / 7.75); if ($w > 4) $w = 4;
                        $actuals[$m][$w] = true;
                    }
                @endphp
                <tr>
                    <td rowspan="2" class="tool-name">{{ $tool->name_alat }}</td>
                    <td rowspan="2" class="serial">{{ $tool->serial_number }}</td>
                    <td rowspan="2">{{ $tool->jenis_kalibrasi }}</td>
                    <td style="background-color: #fafafa;">P</td>
                    @for($m = 1; $m <= 12; $m++)
                        @for($w = 1; $w <= 4; $w++)
                            <td class="{{ isset($plans[$m][$w]) ? 'marker-p' : '' }}">{{ isset($plans[$m][$w]) ? 'P' : '' }}</td>
                        @endfor
                    @endfor
                </tr>
                <tr>
                    <td style="background-color: #fafafa;">A</td>
                    @for($m = 1; $m <= 12; $m++)
                        @for($w = 1; $w <= 4; $w++)
                            <td class="{{ isset($actuals[$m][$w]) ? 'marker-a' : '' }}">{{ isset($actuals[$m][$w]) ? 'A' : '' }}</td>
                        @endfor
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td width="33%">Dibuat Oleh,<br><br><br><br>____________________<br>QC Staff</td>
            <td width="33%">Diperiksa Oleh,<br><br><br><br>____________________<br>Asst. Manager</td>
            <td width="33%">Diketahui Oleh,<br><br><br><br>____________________<br>Manager</td>
        </tr>
    </table>
</body>
</html>
