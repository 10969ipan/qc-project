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
    <table class="table table-bordered mb-3" style="width:100%; border-collapse:collapse; border:1px solid #000 !important; margin-bottom:8px;">
        <tr>
            {{-- Logo --}}
            <td width="80" class="text-center align-middle" style="border:1px solid #000 !important; padding:5px; text-align:center; vertical-align:middle;">
                <img src="{{ asset('master item/ipp.jpg') }}" style="max-width:75px; max-height:55px; object-fit:contain;">
            </td>

            {{-- Judul --}}
            <td class="align-middle" style="border:1px solid #000 !important; padding:5px; text-align:center; vertical-align:middle;">
                <div style="font-size:11pt; font-weight:700; color:#000; text-align:center; margin-bottom:2px;">
                    LAPORAN HASIL VERIFIKASI ALAT UKUR
                </div>
                <div style="font-size:9pt; font-weight:600; color:#000; text-align:center;">
                    PLANT {{ strtoupper($plantName) }}
                </div>
            </td>

            {{-- No. Dokumen & Signatures --}}
            <td width="420" class="small p-0 align-middle" style="border:1px solid #000 !important; padding:0 !important; white-space:nowrap; vertical-align:top;">
                <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                    <tr style="height:100%;">
                        {{-- No. Dokumen --}}
                        <td style="border:none; padding:4px 6px 4px 4px; vertical-align:top; height:100%; white-space:nowrap;">
                            <table style="border-collapse:collapse; border:1px solid #000; font-size:7.5pt; line-height:1.2; background:#fff; height:100%; width:100%;">
                                <tr>
                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:700; color:#212529; white-space:nowrap;">
                                        {{ $docHeader['no_dokumen'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                    <td style="border:1px solid #000; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                                </tr>
                            </table>
                        </td>

                        {{-- Signatures --}}
                        <td style="border:none; padding:4px 4px 4px 0; vertical-align:top; height:100%;">
                            <table style="border-collapse:collapse; border:1px solid #000; text-align:center; font-size:7pt; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:322px;">
                                <thead>
                                    <tr>
                                        <th style="border:1px solid #000; padding:2px 2px; font-weight:600; color:#495057; background:#fff; width:22px;">Tgl.</th>
                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:100px;">Dibuat</th>
                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:100px;">Diperiksa</th>
                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:100px;">Diketahui</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td rowspan="3" style="border:1px solid #000; padding:2px; vertical-align:middle; text-align:center; width:22px;">
                                            <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:6.5pt; font-weight:400; margin:0 auto; color:#6c757d;">
                                                06-Jan-26
                                            </div>
                                        </td>
                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff;">
                                            <img src="{{ asset('signatures/mida.png') }}" alt="Mida H" style="max-height:46px; max-width:92px; object-fit:contain; mix-blend-mode:multiply;">
                                        </td>
                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff;">
                                            <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:46px; max-width:92px; object-fit:contain; mix-blend-mode:multiply;">
                                        </td>
                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff;">
                                            <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:46px; max-width:92px; object-fit:contain; mix-blend-mode:multiply;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #000; padding:1px 5px; font-weight:600; font-size:6.5pt; color:#212529; white-space:nowrap;">Mida H</td>
                                        <td style="border:1px solid #000; padding:1px 5px; font-weight:600; font-size:6.5pt; color:#212529; white-space:nowrap;">Iwan S</td>
                                        <td style="border:1px solid #000; padding:1px 5px; font-weight:600; font-size:6.5pt; color:#212529; white-space:nowrap;">Desti K</td>
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #000; padding:1px 5px; font-size:6.5pt; color:#495057; white-space:nowrap;">Spv. QS</td>
                                        <td style="border:1px solid #000; padding:1px 5px; font-size:6.5pt; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                        <td style="border:1px solid #000; padding:1px 5px; font-size:6.5pt; color:#495057; white-space:nowrap;">Mgr. Quality</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
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
