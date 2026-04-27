<!DOCTYPE html>
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
            <td class="logo">
                <img src="{{ public_path('master item/ipp.jpg') }}"
                     style="max-width: 70px; max-height: 50px;">
            </td>
            <td class="title">MASTER DATA ALAT UKUR</td>
            <td class="doc-info">
                <table>
                    <tr>
                        <td>No. Dokumen</td>
                        <td>: {{ strtolower($plantCode) === 'jakarta' ? 'QC-JKT-F-0215' : 'QC-KRW-F-0215' }}</td>
                    </tr>
                    <tr>
                        <td>Tgl. Terbit</td>
                        <td>: 28/11/2019</td>
                    </tr>
                    <tr>
                        <td>Revisi / Tgl</td>
                        <td>: - / -</td>
                    </tr>
                    <tr>
                        <td>Halaman</td>
                        <td>: 1 / 1</td>
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
                    <td>{{ $tool->all_verifications_count }} Kali</td>
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
                            $hasVerification = $tool->all_verifications_count > 0;
                            $hasPendingLog = $tool->pendingLogs->count() > 0;
                            
                            $nextUnverifiedSchedule = null;
                            $isOverdue = false;
                            if (!empty($scheduledStatuses)) {
                                foreach ($scheduledStatuses as $sch) {
                                    if (!$sch->is_ok) {
                                        $nextUnverifiedSchedule = $sch;
                                        break;
                                    }
                                }
                                if ($nextUnverifiedSchedule) {
                                    $isOverdue = \Carbon\Carbon::parse((string)$nextUnverifiedSchedule->schedule_date)->startOfDay()->isPast();
                                }
                            }
                        @endphp

                        @if($hasPendingLog)
                            PROBLEM
                        @elseif($isOverdue)
                            OVERDUE
                        @elseif($hasVerification)
                            OK
                        @elseif($existingPr)
                            PR OUT
                        @else
                            PLAN
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
