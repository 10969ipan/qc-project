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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Verifikasi Alat Ukur</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            color: #333;
            margin: 0;
            padding: 10mm 10mm 5mm 10mm;
        }

        /* ─── Document Header ─── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .logo { width: 90px; text-align: center; }
        .title { text-align: center; font-size: 13px; font-weight: bold; color: #000; }
        .doc-info { width: 160px; font-size: 8.5px; }
        .doc-info table { width: 100%; border: none; }
        .doc-info td { border: none; padding: 1px 2px; }
        .sub-header { margin-bottom: 8px; font-size: 9px; }

        /* ─── Data Table ─── */
        .table { width: 100%; border-collapse: collapse; table-layout: auto; }
        thead { display: table-header-group; }

        .table th {
            border: 1px solid #000;
            padding: 3px 3px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
            white-space: nowrap;
        }
        .table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
            word-wrap: break-word;
        }
        tbody tr { page-break-inside: avoid; break-inside: avoid; }

        .badge { display: inline-block; padding: .2em .4em; font-size: 70%; font-weight: 700;
                 line-height: 1; text-align: center; border-radius: .25rem; }
        .badge-success { color: #fff; background-color: #28a745;
            -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-danger  { color: #fff; background-color: #dc3545;
            -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge-warning { color: #212529; background-color: #ffc107;
            -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .text-left { text-align: left; }
        .col-compact { white-space: nowrap; width: 1%; }
        .print-footer { margin-top: 6mm; font-size: 7.5px; color: #666; }
    </style>
</head>
<body>

    {{-- ── Document Header ── --}}
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ asset('master item/ipp.jpg') }}"
                     style="max-width: 75px; max-height: 55px; object-fit: contain;">
            </td>
            <td class="title">LAPORAN HASIL VERIFIKASI ALAT UKUR</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-238' : 'QC-KRW-F-238' }}</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 14/07/2025</td>
                    </tr>
                    <tr>
                        <td>Revisi / Tgl</td>
                        <td>: -</td>
                    </tr>
                    <tr>
                        <td>Halaman</td>
                        <td>: 1/1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── Sub Header ── --}}
    <div class="sub-header">
        <strong>Periode:</strong>
        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d
        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Plant:</strong> {{ strtoupper($plantName) }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Tahun:</strong> {{ $year }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Total Data:</strong> {{ $verifications->count() }} baris
    </div>

    {{-- ── Data Table ── --}}
    <table class="table">
        <thead>
            <tr>
                {{-- Spacer row agar ada jarak konsisten di cetak ulang per halaman --}}
                <td colspan="16" style="height:3mm; border:none; padding:0; background:#fff;"></td>
            </tr>
            <tr>
                <th class="col-compact">No.</th>
                <th class="col-compact">Nama Alat</th>
                <th class="col-compact">Merk</th>
                <th class="col-compact">No. Seri</th>
                <th class="col-compact">Rentang Ukur</th>
                <th class="col-compact">Resolusi</th>
                <th class="col-compact">Frek. Kalibrasi</th>
                <th class="col-compact">Tgl. Kalibrasi</th>
                <th class="col-compact">Tgl. Verifikasi</th>
                <th class="col-compact">Next Kalibrasi</th>
                <th class="col-compact">Nilai Alat</th>
                <th class="col-compact">Nilai Koreksi</th>
                <th class="col-compact">Ketidakpastian</th>
                <th class="col-compact">Hasil Verifikasi</th>
                <th class="col-compact">Judgment</th>
                <th class="col-compact">Std. Toleransi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($verifications as $index => $v)
                @php
                    $arrAlat          = is_array($v->nilai_alat) ? $v->nilai_alat : [$v->nilai_alat];
                    $arrKoreksi       = is_array($v->nilai_koreksi) ? $v->nilai_koreksi : [$v->nilai_koreksi];
                    $arrKetidakpastian= is_array($v->nilai_ketidakpastian) ? $v->nilai_ketidakpastian : [$v->nilai_ketidakpastian];
                    $arrHasil         = is_array($v->hasil_verifikasi) ? $v->hasil_verifikasi : [$v->hasil_verifikasi];
                    $maxRows = max(count($arrAlat), count($arrKoreksi), count($arrKetidakpastian), count($arrHasil));
                @endphp
                <tr>
                    <td class="col-compact">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $v->name_alat }}</td>
                    <td class="text-left">{{ $v->merk }}</td>
                    <td class="col-compact">{{ $v->serial_number }}</td>
                    <td class="col-compact">{{ $v->rentang_ukur }}</td>
                    <td class="col-compact">{{ $v->resolusi }}</td>
                    <td class="col-compact">{{ $v->frekuensi_kalibrasi }}</td>
                    <td class="col-compact">
                        {{ $v->tanggal_kalibrasi ? \Carbon\Carbon::parse($v->tanggal_kalibrasi)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="col-compact">
                        {{ $v->tanggal_verifikasi ? \Carbon\Carbon::parse($v->tanggal_verifikasi)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="col-compact">
                        {{ $v->next_kalibrasi ? \Carbon\Carbon::parse($v->next_kalibrasi)->format('d/m/Y') : '-' }}
                    </td>
                    {{-- Multi-row measurement data --}}
                    <td class="col-compact" style="padding:0;">
                        @for($i = 0; $i < $maxRows; $i++)
                            <div style="padding:2px 4px; {{ $i < $maxRows - 1 ? 'border-bottom:1px solid #dee2e6;' : '' }}">
                                {{ $arrAlat[$i] ?? '-' }}
                            </div>
                        @endfor
                    </td>
                    <td class="col-compact" style="padding:0;">
                        @for($i = 0; $i < $maxRows; $i++)
                            <div style="padding:2px 4px; {{ $i < $maxRows - 1 ? 'border-bottom:1px solid #dee2e6;' : '' }}">
                                {{ $arrKoreksi[$i] ?? '-' }}
                            </div>
                        @endfor
                    </td>
                    <td class="col-compact" style="padding:0;">
                        @for($i = 0; $i < $maxRows; $i++)
                            <div style="padding:2px 4px; {{ $i < $maxRows - 1 ? 'border-bottom:1px solid #dee2e6;' : '' }}">
                                {{ $arrKetidakpastian[$i] ?? '-' }}
                            </div>
                        @endfor
                    </td>
                    <td class="col-compact" style="padding:0;">
                        @for($i = 0; $i < $maxRows; $i++)
                            <div style="padding:2px 4px; {{ $i < $maxRows - 1 ? 'border-bottom:1px solid #dee2e6;' : '' }}">
                                {{ $arrHasil[$i] ?? '-' }}
                            </div>
                        @endfor
                    </td>
                    <td class="col-compact">
                        @if($v->judgment === 'OK')
                            <span class="badge badge-success">OK</span>
                        @elseif($v->judgment === 'NG')
                            <span class="badge badge-danger">NG</span>
                        @else
                            {{ $v->judgment ?: '-' }}
                        @endif
                    </td>
                    <td class="col-compact">{{ $v->std_toleransi ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" style="text-align:center; padding:12px; font-style:italic; color:#999;">
                        Tidak ada data verifikasi untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="print-footer">
        <span id="footerDateTime"></span>
    </div>

    <script>
        (function () {
            var now = new Date();
            var pad = function(n){ return n < 10 ? '0' + n : n; };
            document.getElementById('footerDateTime').textContent =
                'Dicetak: '
                + pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear()
                + '  ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
        })();
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
