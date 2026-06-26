<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('hasil_verifikasi', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
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
            <td class="logo">
                <img src="{{ public_path('master item/ipp.jpg') }}" style="max-height: 50px;">
            </td>
            <td class="title">LAPORAN HASIL VERIFIKASI ALAT UKUR</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: QC-KRW-F-0230</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 06-Dec-21</td>
                    </tr>
                    <tr>
                        <td>Revisi</td>
                        <td>: 1</td>
                    </tr>
                    <tr>
                        <td>Tgl. Revisi</td>
                        <td>: 19-Jul-22</td>
                    </tr>
                    <tr>
                        <td>Hal</td>
                        <td>: 1/1</td>
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
