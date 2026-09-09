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
    <title>Hasil Verifikasi Alat Ukur - {{ $verification->serial_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 20px;
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

        .clear {
            clear: both;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .info-table td {
            padding: 5px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 150px;
        }

        .value {
            border-bottom: 1px dotted #ccc;
        }

        .measurement-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .measurement-table th,
        .measurement-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
            /* Added to center merged content */
        }

        .measurement-table {
            border-collapse: separate;
            /* Changed for better rowspan border reliability */
            border-spacing: 0;
            width: 100%;
            margin-top: 30px;
        }

        /* Fix for separate borders looking like single lines */
        .measurement-table th,
        .measurement-table td {
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
        }

        .measurement-table th:first-child,
        .measurement-table td:first-child {
            border-left: 1px solid #000;
        }

        .measurement-table thead tr:first-child th {
            border-top: 1px solid #000;
        }

        .judgment-section {
            margin-top: 30px;
            text-align: center;
        }

        .judgment-box {
            display: inline-block;
            padding: 10px 40px;
            font-size: 18px;
            font-weight: bold;
            border: 3px solid #000;
            border-radius: 5px;
        }

        .judgment-ok {
            color: #28a745;
            border-color: #28a745;
        }

        .judgment-ng {
            color: #dc3545;
            border-color: #dc3545;
        }

        .judgment-neutral {
            color: #6c757d;
            border-color: #6c757d;
        }

        .footer {
            margin-top: 50px;
            font-size: 8px;
            text-align: right;
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
                HASIL VERIFIKASI ALAT UKUR
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

    <table class="info-table">
        <tr>
            <td class="label">Nama Alat Ukur</td>
            <td>:</td>
            <td class="value">{{ $verification->name_alat }}</td>
            <td class="label">Tanggal Verifikasi</td>
            <td>:</td>
            <td class="value">
                {{ $verification->tanggal_verifikasi ? $verification->tanggal_verifikasi->format('d/m/Y') : '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Merk</td>
            <td>:</td>
            <td class="value">{{ $verification->merk }}</td>
            <td class="label">Tanggal Kalibrasi</td>
            <td>:</td>
            <td class="value">
                {{ $verification->tanggal_kalibrasi ? $verification->tanggal_kalibrasi->format('d/m/Y') : '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Nomor Seri</td>
            <td>:</td>
            <td class="value">{{ $verification->serial_number }}</td>
            <td class="label">Next Kalibrasi</td>
            <td>:</td>
            <td class="value">{{ $verification->next_kalibrasi ? $verification->next_kalibrasi->format('d/m/Y') : '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Rentang Ukur</td>
            <td>:</td>
            <td class="value">{{ $verification->rentang_ukur }}</td>
            <td class="label">Resolusi</td>
            <td>:</td>
            <td class="value">{{ $verification->resolusi }}</td>
        </tr>
        <tr>
            <td class="label">Plant</td>
            <td>:</td>
            <td class="value">{{ strtoupper($plantCode) }}</td>
            <td colspan="3"></td>
        </tr>
    </table>

    <table class="measurement-table">
        <thead>
            <tr>
                <th width="50">No.</th>
                <th>Nilai yang Ditunjukkan Alat</th>
                <th>Nilai Koreksi Alat</th>
                <th>Nilai Ketidakpastian</th>
                <th>Hasil Verifikasi</th>
                <th>Std Toleransi</th>
                <th>Acuan Toleransi</th>
            </tr>
        </thead>
        <tbody>
            @if(is_array($verification->nilai_alat))
                @foreach($verification->nilai_alat as $index => $nilai)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $nilai }}</td>
                        <td>{{ $verification->nilai_koreksi[$index] ?? '-' }}</td>
                        <td>{{ $verification->nilai_ketidakpastian[$index] ?? '-' }}</td>
                        <td>{{ $verification->hasil_verifikasi[$index] ?? '-' }}</td>
                        @if($index === 0)
                            <td rowspan="{{ count($verification->nilai_alat) }}">{{ $verification->std_toleransi ?? '-' }}</td>
                            <td rowspan="{{ count($verification->nilai_alat) }}">{{ $verification->acuan_toleransi ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>1</td>
                    <td>{{ $verification->nilai_alat }}</td>
                    <td>{{ $verification->nilai_koreksi }}</td>
                    <td>{{ $verification->nilai_ketidakpastian }}</td>
                    <td>{{ $verification->hasil_verifikasi }}</td>
                    <td>{{ $verification->std_toleransi ?? '-' }}</td>
                    <td>{{ $verification->acuan_toleransi ?? '-' }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="judgment-section">
        <p style="font-weight: bold; margin-bottom: 10px;">KESIMPULAN / JUDGMENT:</p>
        @if($verification->judgment == 'OK' || $verification->judgment == 'NG')
            @php
                $judgmentClass = $verification->judgment == 'OK' ? 'judgment-ok' : 'judgment-ng';
            @endphp
            <div class="judgment-box {{ $judgmentClass }}">
                {{ strtoupper($verification->judgment) }}
            </div>
        @else
            <div style="font-size: 18px; font-weight: bold; color: #6c757d;">
                {{ $verification->judgment ?: '-' }}
            </div>
        @endif
    </div>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}<br>
        Quality Control Department - PT Indoplat Perkasa Purnama Jakarta & Karawang
    </div>
</body>

</html>
