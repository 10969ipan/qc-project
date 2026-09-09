<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('master_alat_ukur', $headerPlantCode, [
            'no_dokumen' => strtolower($headerPlantCode) === 'jakarta' ? 'QC-JKT-F-0215' : 'QC-KRW-F-0215',
            'tgl_terbit' => '28/11/2019',
            'revisi' => '- / -',
            'halaman' => '1 / 1'
        ]);
    @endphp
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master Data Alat Ukur - {{ strtoupper($plantCode) }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            color: #333;
            margin: 0;
            padding: 0;
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
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }

        .table th {
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
        }
        .table td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        tbody tr { page-break-inside: avoid; break-inside: avoid; }

        .badge { display: inline-block; padding: .2em .4em; font-size: 70%; font-weight: 700;
                 line-height: 1; text-align: center; border-radius: .25rem; }
        .badge-danger  { color: #fff; background-color: #dc3545;
            -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .text-left { text-align: left; }
        .col-no { width: 25px; }
        .col-bagian { width: 60px; }
        .col-name { width: 120px; }
        .col-seri { width: 90px; }
        .col-range { width: 70px; }
        .col-freq { width: 70px; }
        .col-jenis { width: 60px; }
        .col-sch { width: 75px; }
        .col-status { width: 40px; }

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
                    MASTER DATA ALAT UKUR
                </div>
                <div style="font-size:9pt; font-weight:600; color:#000; text-align:center;">
                    PLANT {{ strtoupper($plantCode) }}
                </div>
            </td>

            {{-- No. Dokumen & Signatures --}}
            <td class="small p-0 align-middle" style="border:1px solid #000 !important; padding:0 !important; vertical-align:top; width:55%;">
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
                            <table style="border-collapse:collapse; border:1px solid #000; text-align:center; font-size:7pt; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:100%;">
                                <thead>
                                    <tr>
                                        <th style="border:1px solid #000; padding:2px 2px; font-weight:600; color:#495057; background:#fff; width:15%;">Tgl.</th>
                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:28.33%;">Dibuat</th>
                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:28.33%;">Diperiksa</th>
                                        <th style="border:1px solid #000; padding:2px 4px; font-weight:600; color:#495057; background:#fff; width:28.34%;">Diketahui</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td rowspan="3" style="border:1px solid #000; padding:2px; vertical-align:middle; text-align:center; width:22px;">
                                            <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:6.5pt; font-weight:400; margin:0 auto; color:#6c757d;">
                                                06-Jan-26
                                            </div>
                                        </td>
                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                            <img src="{{ asset('signatures/mida.png') }}" alt="Mida H" style="max-height:58px; max-width:110px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                        </td>
                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                            <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:58px; max-width:110px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                        </td>
                                        <td style="border:1px solid #000; padding:3px; vertical-align:middle; height:48px; background:#fff; text-align:center;">
                                            <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:58px; max-width:110px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
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
        <strong>Plant:</strong> {{ strtoupper($plant->name ?? $plantCode) }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Tahun:</strong> {{ strtoupper($year) }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Total Data:</strong> {{ $tools->count() }} baris
        @if(request('search'))
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Pencarian:</strong> "{{ request('search') }}"
        @endif
    </div>

    {{-- ── Data Table ── --}}
    <table class="table">
        <thead>
            <tr>
                <th class="col-no">NO.</th>
                <th class="col-bagian">BAGIAN</th>
                <th class="col-name">NAMA ALAT</th>
                <th style="width: 70px;">MERK</th>
                <th class="col-seri">NO. SERI</th>
                <th class="col-range">RANGE</th>
                <th style="width: 60px;">RESOLUSI</th>
                <th style="width: 65px;">TGL BELI</th>
                <th class="col-freq">FREKUENSI</th>
                <th style="width: 50px;">RIWAYAT</th>
                <th class="col-jenis">JENIS</th>
                <th class="col-sch">SCHEDULE</th>
                <th style="width: 80px;">PR NUMBER</th>
                <th class="col-status">STAT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tools as $index => $tool)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">
                        {{ $tool->bagian }}
                        @if($tool->status === 'BROKEN')
                            <br><span class="badge badge-danger">BROKEN</span>
                        @endif
                    </td>
                    <td class="text-left">{{ $tool->name_alat }}</td>
                    <td>{{ $tool->merk ?? '-' }}</td>
                    <td>{{ $tool->serial_number }}</td>
                    <td>{{ $tool->range }}</td>
                    <td>{{ $tool->resolusi }}</td>
                    <td>{{ $tool->tanggal_beli ? \Carbon\Carbon::parse($tool->tanggal_beli)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $tool->frekuensi_kalibrasi }}</td>
                    <td>{{ $tool->riwayat_kalibrasi }}</td>
                    <td>{{ ucfirst(strtolower($tool->jenis_kalibrasi)) }}</td>
                    <td>
                        @php
                            $scheduledStatuses = $tool->getScheduledStatuses($year);
                        @endphp
                        @if(!empty($scheduledStatuses))
                            @foreach($scheduledStatuses as $sch)
                                <div>{{ \Carbon\Carbon::parse($sch->schedule_date)->format('d/m/Y') }}</div>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $existingPr = null;
                            foreach ($tool->schedules as $sch) {
                                if ($sch->pr_number) {
                                    $existingPr = $sch->pr_number;
                                    break;
                                }
                            }
                        @endphp
                        {{ $existingPr ?? '-' }}
                    </td>
                    <td>
                        @php
                            $status = $tool->status_kalibrasi;
                        @endphp
                        @if($status === 'calibrated')
                            OK
                        @elseif($status === 'due_soon')
                            DUE SOON
                        @elseif($status === 'overdue')
                            OVERDUE
                            @if($tool->next_calibration_date)
                                <div style="font-size: 6px; color: #dc3545; font-weight: bold;">
                                    ({{ $tool->next_calibration_date->format('d/m/y') }})
                                </div>
                            @endif
                        @elseif($status === 'problem')
                            PROBLEM
                        @elseif($status === 'broken')
                            BROKEN
                        @else
                            UNKNOWN
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center; padding:12px; font-style:italic; color:#999;">
                        Tidak ada data alat ukur.
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
