<!DOCTYPE html>
@php
    $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
    $docHeader = \App\Models\GeneralSetting::getDocHeader('schedule_kalibrasi', $headerPlantCode, [
        'no_dokumen' => strtolower($headerPlantCode) === 'jakarta' ? 'QC-JKT-F-052' : 'QC-KRW-F-052',
        'tgl_terbit' => '25/03/2015',
        'revisi' => '1 / 21/03/2018',
        'halaman' => '1 / 1'
    ]);
@endphp
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
            <td class="logo" style="width: 50px; text-align: center; vertical-align: middle;">
                <img src="{{ public_path('master item/ipp.jpg') }}" height="35">
            </td>
            <td class="title" style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 9pt; text-transform: uppercase;">
                SCHEDULE KALIBRASI ALAT UKUR - {{ $year }}<br>PLANT {{ strtoupper($plantCode) }}
            </td>
            <td class="doc-info" style="width: 320px; padding: 2px; vertical-align: middle;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="border: none; padding: 0 4px 0 0; vertical-align: middle; width: 42%;">
                            <table style="width: 100%; border-collapse: collapse; border: 1px solid #666; font-size: 6.5pt; line-height: 1.2; background: #fff;">
                                <tr>
                                    <td style="border: 1px solid #666; padding: 1px 4px; font-weight: bold; color: #333; white-space: nowrap;">No. Dokumen</td>
                                    <td style="border: 1px solid #666; padding: 1px 2px; text-align: center; color: #333;">:</td>
                                    <td style="border: 1px solid #666; padding: 1px 4px; font-weight: bold; color: #000; white-space: nowrap;">{{ $docHeader['no_dokumen'] }}</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #666; padding: 1px 4px; font-weight: bold; color: #333; white-space: nowrap;">Tgl. Terbit</td>
                                    <td style="border: 1px solid #666; padding: 1px 2px; text-align: center; color: #333;">:</td>
                                    <td style="border: 1px solid #666; padding: 1px 4px; font-weight: bold; color: #000; white-space: nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #666; padding: 1px 4px; font-weight: bold; color: #333; white-space: nowrap;">Revisi / Tgl</td>
                                    <td style="border: 1px solid #666; padding: 1px 2px; text-align: center; color: #333;">:</td>
                                    <td style="border: 1px solid #666; padding: 1px 4px; font-weight: bold; color: #000; white-space: nowrap;">{{ $docHeader['revisi'] }}</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #666; padding: 1px 4px; font-weight: bold; color: #333; white-space: nowrap;">Halaman</td>
                                    <td style="border: 1px solid #666; padding: 1px 2px; text-align: center; color: #333;">:</td>
                                    <td style="border: 1px solid #666; padding: 1px 4px; font-weight: bold; color: #000; white-space: nowrap;">{{ $docHeader['halaman'] }}</td>
                                </tr>
                            </table>
                        </td>
                        <td style="border: none; padding: 0; vertical-align: middle; width: 58%;">
                            <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; text-align: center; font-size: 4.5pt; line-height: 1.0; background: #fff;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #000; padding: 1px; font-weight: 600; background: #fff; width: 14%; font-size: 4.5pt;">Tgl.</th>
                                        <th style="border: 1px solid #000; padding: 1px; font-weight: 600; background: #fff; width: 28%; font-size: 4.5pt;">Dibuat</th>
                                        <th style="border: 1px solid #000; padding: 1px; font-weight: 600; background: #fff; width: 30%; font-size: 4.5pt;">Diperiksa</th>
                                        <th style="border: 1px solid #000; padding: 1px; font-weight: 600; background: #fff; width: 28%; font-size: 4.5pt;">Diketahui</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td rowspan="3" style="border: 1px solid #000; padding: 0; vertical-align: middle; text-align: center;">
                                            <div style="font-size: 4.5pt; line-height: 0.95;">
                                                06<br>-<br>Jan<br>-<br>26
                                            </div>
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px; vertical-align: middle; height: 40px; background: #fff; text-align: center;">
                                            <img src="{{ public_path('signatures/mida.png') }}" style="max-height: 48px; max-width: 75px; transform: scale(1.35); transform-origin: center;">
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px; vertical-align: middle; height: 40px; background: #fff; text-align: center;">
                                            <img src="{{ public_path('signatures/iwan.png') }}" style="max-height: 48px; max-width: 75px; transform: scale(1.35); transform-origin: center;">
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px; vertical-align: middle; height: 40px; background: #fff; text-align: center;">
                                            <img src="{{ public_path('signatures/desti.png') }}" style="max-height: 48px; max-width: 75px; transform: scale(1.35); transform-origin: center;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 0px 1px; font-weight: 600; font-size: 4.5pt; line-height: 1.0;">Mida H</td>
                                        <td style="border: 1px solid #000; padding: 0px 1px; font-weight: 600; font-size: 4.5pt; line-height: 1.0;">Iwan S</td>
                                        <td style="border: 1px solid #000; padding: 0px 1px; font-weight: 600; font-size: 4.5pt; line-height: 1.0;">Desti K</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 0px 1px; font-size: 4.2pt; line-height: 1.0; color: #444;">Spv. QS</td>
                                        <td style="border: 1px solid #000; padding: 0px 1px; font-size: 4.2pt; line-height: 1.0; color: #444;">Asst. Mgr Quality</td>
                                        <td style="border: 1px solid #000; padding: 0px 1px; font-size: 4.2pt; line-height: 1.0; color: #444;">Mgr. Quality</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
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
</body>
</html>
