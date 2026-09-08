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
    <title>Print Schedule Kalibrasi - {{ $year }}</title>
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; background-color: white; font-size: 8pt; }
            .container-fluid { width: 100%; padding: 0; }
            .card { border: none !important; box-shadow: none !important; }
            .table-responsive { overflow: visible !important; }
            .schedule-table { width: 100% !important; border-collapse: collapse !important; }
            .schedule-table th, .schedule-table td { border: 1px solid #000 !important; padding: 2px !important; }
            .marker-p { background-color: #d1e7dd !important; -webkit-print-color-adjust: exact; }
            .marker-a { background-color: #cfe2ff !important; -webkit-print-color-adjust: exact; }
        }
        .schedule-table { font-size: 8pt; text-align: center; }
        .schedule-table th { background-color: #f8f9fc; }
        .marker-p { background-color: #d1e7dd; }
        .marker-a { background-color: #cfe2ff; }
    </style>
</head>
<body onload="window.print()">
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4 class="mb-0">Preview Cetak Jadwal Kalibrasi</h4>
            <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print mr-1"></i> Cetak Sekarang</button>
        </div>

        <table class="table table-bordered mb-3" style="width: 100%; border-collapse: collapse; border: 1px solid #000 !important;">
            <tr>
                <td width="80" class="text-center align-middle" style="border: 1px solid #000 !important; padding: 5px;">
                    <img src="{{ asset('master item/ipp.jpg') }}" height="40">
                </td>
                <td class="align-middle" style="border: 1px solid #000 !important; padding: 5px; text-align: center; vertical-align: middle;">
                    <div style="font-size: 11pt; font-weight: 700; color: #000; text-align: center; margin-bottom: 2px;">SCHEDULE KALIBRASI ALAT UKUR - {{ $year }}</div>
                    <div style="font-size: 9pt; font-weight: 600; color: #000; text-align: center;">PLANT {{ strtoupper($plantCode) }}</div>
                </td>
                <td width="420" class="small p-0 align-middle" style="border: 1px solid #000 !important; padding: 0 !important; white-space: nowrap; vertical-align: top;">
                    <table style="border-collapse: collapse; width: 100%; height: 100%; border: none; margin: 0;">
                        <tr style="height: 100%;">
                            <td style="border: none; padding: 4px 6px 4px 4px; vertical-align: top; height: 100%; white-space: nowrap;">
                                <table style="border-collapse: collapse; border: 1px solid #000; font-size: 7.5pt; line-height: 1.2; background: #fff; height: 100%; width: 100%;">
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 2px 6px; font-weight: 600; color: #495057; white-space: nowrap;">No. Dokumen</td>
                                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #495057;">:</td>
                                        <td style="border: 1px solid #000; padding: 2px 6px; font-weight: 700; color: #212529; white-space: nowrap;">{{ $docHeader['no_dokumen'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 2px 6px; font-weight: 600; color: #495057; white-space: nowrap;">Tgl. Terbit</td>
                                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #495057;">:</td>
                                        <td style="border: 1px solid #000; padding: 2px 6px; font-weight: 600; color: #212529; white-space: nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 2px 6px; font-weight: 600; color: #495057; white-space: nowrap;">Revisi / Tgl</td>
                                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #495057;">:</td>
                                        <td style="border: 1px solid #000; padding: 2px 6px; font-weight: 600; color: #212529; white-space: nowrap;">{{ $docHeader['revisi'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 2px 6px; font-weight: 600; color: #495057; white-space: nowrap;">Halaman</td>
                                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #495057;">:</td>
                                        <td style="border: 1px solid #000; padding: 2px 6px; font-weight: 600; color: #212529; white-space: nowrap;">{{ $docHeader['halaman'] }}</td>
                                    </tr>
                                </table>
                            </td>
                            <td style="border: none; padding: 4px 4px 4px 0; vertical-align: top; height: 100%;">
                                <table style="border-collapse: collapse; border: 1px solid #000; text-align: center; font-size: 7pt; line-height: 1.1; background: #fff; height: 100%; table-layout: fixed; width: 322px;">
                                    <thead>
                                        <tr>
                                            <th style="border: 1px solid #000; padding: 2px 2px; font-weight: 600; color: #495057; background: #fff; width: 22px;">Tgl.</th>
                                            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; color: #495057; background: #fff; width: 100px;">Dibuat</th>
                                            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; color: #495057; background: #fff; width: 100px;">Diperiksa</th>
                                            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; color: #495057; background: #fff; width: 100px;">Diketahui</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td rowspan="3" style="border: 1px solid #000; padding: 2px; vertical-align: middle; text-align: center; width: 22px;">
                                                <div style="writing-mode: vertical-rl; transform: rotate(180deg); -webkit-transform: rotate(180deg); white-space: nowrap; font-size: 6.5pt; font-weight: 400; margin: 0 auto; color: #6c757d;">
                                                    06-Jan-26
                                                </div>
                                            </td>
                                            <td style="border: 1px solid #000; padding: 3px; vertical-align: middle; height: 48px; background: #fff;">
                                                <img src="{{ asset('signatures/mida.png') }}" alt="Mida H" style="max-height: 46px; max-width: 92px; object-fit: contain; mix-blend-mode: multiply;">
                                            </td>
                                            <td style="border: 1px solid #000; padding: 3px; vertical-align: middle; height: 48px; background: #fff;">
                                                <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height: 46px; max-width: 92px; object-fit: contain; mix-blend-mode: multiply;">
                                            </td>
                                            <td style="border: 1px solid #000; padding: 3px; vertical-align: middle; height: 48px; background: #fff;">
                                                <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height: 46px; max-width: 92px; object-fit: contain; mix-blend-mode: multiply;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 1px 5px; font-weight: 600; font-size: 6.5pt; color: #212529; white-space: nowrap;">Mida H</td>
                                            <td style="border: 1px solid #000; padding: 1px 5px; font-weight: 600; font-size: 6.5pt; color: #212529; white-space: nowrap;">Iwan S</td>
                                            <td style="border: 1px solid #000; padding: 1px 5px; font-weight: 600; font-size: 6.5pt; color: #212529; white-space: nowrap;">Desti K</td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 1px 5px; font-size: 6.5pt; color: #495057; white-space: nowrap;">Spv. QS</td>
                                            <td style="border: 1px solid #000; padding: 1px 5px; font-size: 6.5pt; color: #495057; white-space: nowrap;">Asst. Mgr Quality</td>
                                            <td style="border: 1px solid #000; padding: 1px 5px; font-size: 6.5pt; color: #495057; white-space: nowrap;">Mgr. Quality</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="table-responsive">
            <table class="table table-bordered schedule-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle">NAMA ALAT</th>
                        <th rowspan="2" class="align-middle">NO. SERI</th>
                        <th rowspan="2" class="align-middle">JENIS</th>
                        <th rowspan="2" class="align-middle">P/A</th>
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
                            <td rowspan="2" class="text-left font-weight-bold">{{ $tool->name_alat }}</td>
                            <td rowspan="2" class="text-left small">{{ $tool->serial_number }}</td>
                            <td rowspan="2">{{ $tool->jenis_kalibrasi }}</td>
                            <td class="bg-light">P</td>
                            @for($m = 1; $m <= 12; $m++)
                                @for($w = 1; $w <= 4; $w++)
                                    <td class="{{ isset($plans[$m][$w]) ? 'marker-p' : '' }}">{{ isset($plans[$m][$w]) ? 'P' : '' }}</td>
                                @endfor
                            @endfor
                        </tr>
                        <tr>
                            <td class="bg-light">A</td>
                            @for($m = 1; $m <= 12; $m++)
                                @for($w = 1; $w <= 4; $w++)
                                    <td class="{{ isset($actuals[$m][$w]) ? 'marker-a' : '' }}">{{ isset($actuals[$m][$w]) ? 'A' : '' }}</td>
                                @endfor
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
