<!DOCTYPE html>
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
        }

        .measurement-table th {
            background-color: #f2f2f2;
            font-weight: bold;
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
            <td class="logo">
                <img src="{{ public_path('master item/ipp.jpg') }}" style="max-height: 50px;">
            </td>
            <td class="title">HASIL VERIFIKASI ALAT UKUR</td>
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
            <td class="label">Lokasi Penyimpanan</td>
            <td>:</td>
            <td class="value">{{ $verification->lokasi_penyimpanan }}</td>
        </tr>
        <tr>
            <td class="label">Resolusi</td>
            <td>:</td>
            <td class="value">{{ $verification->resolusi }}</td>
            <td class="label">Plant</td>
            <td>:</td>
            <td class="value">{{ strtoupper($plantCode) }}</td>
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
        <div class="judgment-box {{ $verification->judgment == 'OK' ? 'judgment-ok' : 'judgment-ng' }}">
            {{ strtoupper($verification->judgment) }}
        </div>
    </div>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}<br>
        Quality Control Department - PT Indoplat Perkasa Purnama Jakarta & Karawang
    </div>
</body>

</html>