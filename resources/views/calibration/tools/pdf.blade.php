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
        @page {
            size: a4 landscape;
            margin: 1cm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* ─── Document Header ─── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { border: 1pt solid #000; padding: 5px; vertical-align: middle; }
        .logo { width: 90px; text-align: center; }
        .title { text-align: center; font-size: 13pt; font-weight: bold; color: #000; }
        .doc-info { width: 160px; font-size: 8.5pt; }
        .doc-info table { width: 100%; border: none; border-collapse: collapse; }
        .doc-info td { border: none; padding: 1px 2px; }
        .sub-header { margin-bottom: 10px; font-size: 9pt; }

        /* ─── Data Table ─── */
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table th {
            border: 1pt solid #000;
            padding: 5px 2px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        .table td {
            border: 1pt solid #000;
            padding: 4px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5pt;
            word-wrap: break-word;
        }

        .badge-danger { color: #dc3545; font-weight: bold; }
        .text-left { text-align: left; }
        
        .footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0;
            right: 0;
            height: 0.5cm;
            font-size: 7pt;
            color: #666;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- ── Document Header ── --}}
    <table class="header-table">
        <tr>
            <td class="logo" style="width: 50px; text-align: center; vertical-align: middle;">
                <img src="{{ public_path('master item/ipp.jpg') }}" height="35">
            </td>
            <td class="title" style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 9pt; text-transform: uppercase;">
                MASTER DATA ALAT UKUR - {{ strtoupper($plantCode) }}
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
                                        <td style="border: 1px solid #000; padding: 1px; vertical-align: middle; height: 40px; background: #fff;">
                                            <img src="{{ public_path('signatures/mida.png') }}" style="max-height: 38px; max-width: 58px;">
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px; vertical-align: middle; height: 40px; background: #fff;">
                                            <img src="{{ public_path('signatures/iwan.png') }}" style="max-height: 38px; max-width: 65px;">
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px; vertical-align: middle; height: 40px; background: #fff;">
                                            <img src="{{ public_path('signatures/desti.png') }}" style="max-height: 38px; max-width: 58px;">
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
                <th style="width: 20pt;">NO.</th>
                <th style="width: 55pt;">BAGIAN</th>
                <th style="width: 100pt;">NAMA ALAT</th>
                <th style="width: 60pt;">MERK</th>
                <th style="width: 80pt;">NO. SERI</th>
                <th style="width: 60pt;">RANGE</th>
                <th style="width: 50pt;">RESOLUSI</th>
                <th style="width: 55pt;">TGL BELI</th>
                <th style="width: 60pt;">FREKUENSI</th>
                <th style="width: 45pt;">RIWAYAT</th>
                <th style="width: 45pt;">JENIS</th>
                <th style="width: 65pt;">SCHEDULE</th>
                <th style="width: 70pt;">PR NUMBER</th>
                <th style="width: 35pt;">STAT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tools as $index => $tool)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">
                        {{ $tool->bagian }}
                        @if($tool->status === 'BROKEN')
                            <br><span class="badge-danger">BROKEN</span>
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
                                <div style="font-size: 7pt;">{{ \Carbon\Carbon::parse($sch->schedule_date)->format('d/m/Y') }}</div>
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
                                <div style="font-size: 6pt; color: #dc3545; font-weight: bold;">
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

    <div class="footer">
        Dicetak: {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
