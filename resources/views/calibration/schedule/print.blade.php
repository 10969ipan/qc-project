<!DOCTYPE html>
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

        <table class="table table-bordered mb-3" style="width: 100%;">
            <tr>
                <td width="80" class="text-center align-middle">
                    <img src="{{ asset('master item/ipp.jpg') }}" height="40">
                </td>
                <td class="text-center align-middle">
                    <h5 class="mb-0 font-weight-bold">SCHEDULE KALIBRASI ALAT UKUR - {{ $year }}</h5>
                    <div class="small">PLANT {{ strtoupper($plantCode) }}</div>
                </td>
                <td width="200" class="small">
                    No. Dokumen: {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-052' : 'QC-KRW-F-052' }}<br>
                    Tgl. Terbit: 25/03/2015<br>
                    Revisi / Tgl: 1 / 21/03/2018
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

        <table class="table table-borderless mt-5 text-center" style="width: 100%; page-break-inside: avoid;">
            <tr>
                <td width="33%">Dibuat Oleh,</td>
                <td width="33%">Diperiksa Oleh,</td>
                <td width="33%">Diketahui Oleh,</td>
            </tr>
            <tr>
                <td style="padding-top: 60px;">____________________</td>
                <td style="padding-top: 60px;">____________________</td>
                <td style="padding-top: 60px;">____________________</td>
            </tr>
            <tr>
                <td>QC Staff</td>
                <td>Spv QC</td>
                <td>Asst. Manager</td>
            </tr>
        </table>
    </div>
</body>
</html>
