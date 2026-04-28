<!DOCTYPE html>
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
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ asset('master item/ipp.jpg') }}"
                     style="max-width: 75px; max-height: 55px; object-fit: contain;">
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
