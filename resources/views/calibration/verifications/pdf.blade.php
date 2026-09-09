<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('hasil_verifikasi', $headerPlantCode, [
            'no_dokumen' => strtolower($headerPlantCode) === 'jakarta' ? 'QC-JKT-F-238' : 'QC-KRW-F-238',
            'tgl_terbit' => '14/07/2025',
            'revisi' => '-',
            'halaman' => '1/1'
        ]);
    @endphp
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hasil Verifikasi Alat Ukur</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            color: #333;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .logo {
            width: 100px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        .doc-info {
            width: 150px;
            font-size: 9px;
        }

        .doc-info table {
            width: 100%;
            border: none;
        }

        .doc-info td {
            border: none;
            padding: 1px 2px;
        }

        .text-dark {
            color: #000;
        }

        .list-unstyled {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .border-bottom {
            border-bottom: 1px solid #ccc;
        }

        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
        }

        .bg-success {
            background-color: #28a745;
        }

        .bg-danger {
            background-color: #dc3545;
        }

        .bg-secondary {
            background-color: #6c757d;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo" style="width: 50px; text-align: center; vertical-align: middle;">
                <img src="{{ public_path('master item/ipp.jpg') }}" height="35">
            </td>
            <td class="title" style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 9pt; text-transform: uppercase;">
                LAPORAN HASIL VERIFIKASI ALAT UKUR
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

    <div style="margin-bottom: 10px;">
        <strong>Periode:</strong>
        {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua' }} -
        {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua' }}
        <br>
        <strong>Plant:</strong> {{ strtoupper($plantCode) }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Tanggal Verifikasi</th>
                <th rowspan="2">Nama Alat</th>
                <th rowspan="2">Merk</th>
                <th rowspan="2">No Seri</th>
                <th rowspan="2">Rentang Ukur</th>
                <th rowspan="2">Resolusi</th>
                <th rowspan="2">Frekuensi</th>
                <th rowspan="2">Tanggal Kalibrasi</th>
                <th colspan="4">Detail Verifikasi Alat</th>
                <th rowspan="2">Std Toleransi</th>
                <th rowspan="2">Acuan Toleransi</th>
                <th rowspan="2">Judgment</th>
                <th rowspan="2">Next Kalibrasi</th>
            </tr>
            <tr>
                <th>Titik Ukur</th>
                <th>Koreksi</th>
                <th>Ketidakpastian</th>
                <th>Hasil</th>
            </tr>
        </thead>
        <tbody>
            @foreach($verifications as $v)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $v->tanggal_verifikasi ? $v->tanggal_verifikasi->format('d/m/Y') : '-' }}</td>
                    <td>{{ $v->name_alat }}</td>
                    <td>{{ $v->merk }}</td>
                    <td>{{ $v->serial_number }}</td>
                    <td>{{ $v->rentang_ukur }}</td>
                    <td>{{ $v->resolusi }}</td>
                    <td>{{ $v->frekuensi_kalibrasi }}</td>
                    <td>{{ $v->tanggal_kalibrasi ? $v->tanggal_kalibrasi->format('d/m/Y') : '-' }}</td>
                    @php
                        $arrAlat = is_array($v->nilai_alat) ? $v->nilai_alat : [$v->nilai_alat];
                        $arrKoreksi = is_array($v->nilai_koreksi) ? $v->nilai_koreksi : [$v->nilai_koreksi];
                        $arrKetidakpastian = is_array($v->nilai_ketidakpastian) ? $v->nilai_ketidakpastian : [$v->nilai_ketidakpastian];
                        $arrHasil = is_array($v->hasil_verifikasi) ? $v->hasil_verifikasi : [$v->hasil_verifikasi];
                        $maxRows = max(count($arrAlat), count($arrKoreksi), count($arrKetidakpastian), count($arrHasil));
                    @endphp
                    <td style="padding: 0;">
                        @for($i = 0; $i < $maxRows; $i++)
                            <div class="{{ $i < $maxRows - 1 ? 'border-bottom' : '' }}"
                                style="padding: 2px; height: 28px; text-align: center;">{{ $arrAlat[$i] ?? '' }}</div>
                        @endfor
                    </td>
                    <td style="padding: 0;">
                        @for($i = 0; $i < $maxRows; $i++)
                            <div class="{{ $i < $maxRows - 1 ? 'border-bottom' : '' }}"
                                style="padding: 2px; height: 28px; text-align: center;">{{ $arrKoreksi[$i] ?? '' }}</div>
                        @endfor
                    </td>
                    <td style="padding: 0;">
                        @for($i = 0; $i < $maxRows; $i++)
                            <div class="{{ $i < $maxRows - 1 ? 'border-bottom' : '' }}"
                                style="padding: 2px; height: 28px; text-align: center;">{{ $arrKetidakpastian[$i] ?? '' }}</div>
                        @endfor
                    </td>
                    <td style="padding: 0;">
                        @for($i = 0; $i < $maxRows; $i++)
                            <div class="{{ $i < $maxRows - 1 ? 'border-bottom' : '' }}"
                                style="padding: 2px; height: 28px; text-align: center;">{{ $arrHasil[$i] ?? '' }}</div>
                        @endfor
                    </td>
                    <td>{{ $v->std_toleransi ?? '-' }}</td>
                    <td>{{ $v->acuan_toleransi ?? '-' }}</td>
                    <td>
                        @if($v->judgment == 'OK' || $v->judgment == 'NG')
                            <span class="badge {{ $v->judgment == 'OK' ? 'bg-success' : 'bg-danger' }}">
                                {{ $v->judgment }}
                            </span>
                        @else
                            {{ $v->judgment ?: '-' }}
                        @endif
                    </td>
                    <td>{{ $v->next_kalibrasi ? $v->next_kalibrasi->format('d/m/Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 7px; text-align: right;">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>
</body>

</html>
