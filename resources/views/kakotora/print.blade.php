<!DOCTYPE html>
    @php
        $headerPlantCode = isset($plantCode) ? $plantCode : (isset($plant) && is_string($plant) ? strtolower($plant) : 'karawang');
        $docHeader = \App\Models\GeneralSetting::getDocHeader('kakotora', $headerPlantCode, [
            'no_dokumen' => '-',
            'tgl_terbit' => '-',
            'revisi' => '-',
            'halaman' => '- / -'
        ]);
    @endphp
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Kakotora - {{ strtoupper($plant) }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 6.5px;
            color: #222;
            margin: 0;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .header-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .logo { width: 70px; text-align: center; }
        .title { text-align: center; font-size: 13px; font-weight: bold; }
        .doc-info { width: 175px; font-size: 8.5px; }
        .doc-info table { width: 100%; border: none; }
        .doc-info td { border: none; padding: 1px 2px; }

        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
        }
        .table thead { display: table-header-group; }
        .table tr { page-break-inside: avoid; break-inside: avoid; }
        .table th {
            background-color: #f2f2f2;
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
            line-height: 1.15;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .table td {
            border: 1px solid #000;
            padding: 2.5px 2.5px;
            text-align: center;
            vertical-align: top;
            font-size: 6px;
            line-height: 1.25;
            overflow-wrap: break-word;
            word-wrap: break-word;
            white-space: normal;
        }
        .td-left { text-align: left !important; }
        .list-clean {
            margin: 0;
            padding: 0;
            text-align: left;
            white-space: normal;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .list-clean-item {
            margin-bottom: 2px;
        }
        .list-clean-item:last-child {
            margin-bottom: 0;
        }
        .cm-block {
            margin-bottom: 3px;
        }
        .cm-block:last-child {
            margin-bottom: 0;
        }
        .table th.doc-header-wrapper {
            border: none !important;
            background-color: #fff !important;
            padding: 0 0 6px 0 !important;
            text-transform: none !important;
            font-weight: normal !important;
        }
        .table th.doc-header-wrapper .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .table th.doc-header-wrapper .header-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
            text-align: center;
        }
        .table th.doc-header-wrapper .header-table td.title {
            font-size: 13px;
            font-weight: bold;
        }
        .table th.doc-header-wrapper .header-table td.doc-info {
            width: 175px;
            font-size: 8.5px;
            text-align: left;
        }
        .table th.doc-header-wrapper .doc-info td {
            border: none;
            padding: 1px 2px;
            text-align: left;
        }
        .footer { margin-top: 6px; font-size: 7.5px; color: #666; text-align: right; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <table class="table">
        <colgroup>
            <col style="width: 2%;">   {{-- NO --}}
            <col style="width: 3.8%;"> {{-- TANGGAL --}}
            <col style="width: 4.5%;"> {{-- NO REGISTRASI --}}
            <col style="width: 3.8%;"> {{-- ISSUE DATE --}}
            <col style="width: 2.3%;"> {{-- REV --}}
            <col style="width: 3%;">   {{-- FAM --}}
            <col style="width: 2.8%;"> {{-- CAT --}}
            <col style="width: 3.5%;"> {{-- CLAIM --}}
            <col style="width: 3.5%;"> {{-- MODEL --}}
            <col style="width: 6.5%;"> {{-- PART NAME --}}
            <col style="width: 5.5%;"> {{-- PART NO --}}
            <col style="width: 3.2%;"> {{-- MOULD --}}
            <col style="width: 3.5%;"> {{-- OWNER --}}
            <col style="width: 10.4%;"> {{-- SIMILAR --}}
            <col style="width: 3%;">    {{-- SEC --}}
            <col style="width: 3.5%;">  {{-- PROSES --}}
            <col style="width: 7.5%;">  {{-- PROBLEM --}}
            <col style="width: 7.5%;">  {{-- CAUSE --}}
            <col style="width: 10.9%;"> {{-- COUNTERMEASURE --}}
            <col style="width: 2.8%;">  {{-- PIC --}}
            <col style="width: 3.5%;">  {{-- SUPPLIER --}}
            <col style="width: 3.8%;">  {{-- DEFECT --}}
            <col style="width: 4.2%;">  {{-- STATUS --}}
        </colgroup>
        <thead>
            <tr>
                <th colspan="23" class="doc-header-wrapper">
                    <table class="header-table">
                        <tr>
                            <td class="logo">
                                <img src="{{ asset('master item/ipp.jpg') }}" style="max-width: 60px; max-height: 45px;">
                            </td>
                            <td class="title">DATABASE KAKOTORA - {{ strtoupper($plant) }}</td>
                            <td class="doc-info">
                                <table>
                                    <tr>
                                        <td>No. Dokumen</td>
                                        <td>: {{ strtoupper($plant) === 'JAKARTA' ? 'ENG-JKT-F-037' : 'ENG-KRW-F-037' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tgl. Terbit</td>
                                        <td>: 17-06-2020</td>
                                    </tr>
                                    <tr>
                                        <td>Revisi / Tgl</td>
                                        <td>: 1 / 06-04-2023</td>
                                    </tr>
                                    <tr>
                                        <td>Halaman</td>
                                        <td>: 1 / 1</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </th>
            </tr>
            <tr>
                <th>NO</th>
                <th>TANGGAL</th>
                <th>NO REG</th>
                <th>ISSUE DATE</th>
                <th>REV</th>
                <th>FAM</th>
                <th>CAT</th>
                <th>CLAIM</th>
                <th>MODEL</th>
                <th>PART NAME</th>
                <th>PART NO</th>
                <th>MOULD</th>
                <th>OWNER</th>
                <th>SIMILAR</th>
                <th>SEC</th>
                <th>PROSES</th>
                <th>PROBLEM</th>
                <th>CAUSE</th>
                <th>COUNTERMEASURE</th>
                <th>PIC</th>
                <th>SUPPLIER</th>
                <th>DEFECT</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kakotoras as $item)
                @php
                    $cleanText = function ($val) {
                        $str = trim((string) ($val ?? ''), " \t\n\r\0\x0B\"");
                        $str = preg_replace("/\r\n|\r/", "\n", $str);
                        $lines = array_values(array_filter(array_map('trim', explode("\n", $str)), fn($l) => $l !== ''));
                        return $lines;
                    };
                    $similarLines = $cleanText($item->similar_part);
                    $cmLines      = $cleanText($item->countermeasure);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->formatDate('d/m/y') }}</td>
                    <td>{{ $item->no_reg ?? '-' }}</td>
                    <td>
                        @forelse($item->formatIssueDates('d/m/y') as $issueDate)
                            <div>{{ $issueDate }}</div>
                        @empty
                            -
                        @endforelse
                    </td>
                    <td>{{ $item->rev_model ?? '-' }}</td>
                    <td>{{ $item->family ?? '-' }}</td>
                    <td>{{ $item->category_nm_mp ?? '-' }}</td>
                    <td>{{ $item->category_claim ?? '-' }}</td>
                    <td>{{ $item->model ?? '-' }}</td>
                    <td class="td-left">{{ $item->part_name ?? '-' }}</td>
                    <td>{{ $item->part_number ?? '-' }}</td>
                    <td>{{ $item->mould ?? '-' }}</td>
                    <td>{{ $item->owner_mould ?? '-' }}</td>
                    <td class="td-left">
                        @if(count($similarLines) > 0)
                            <div class="list-clean">
                                @foreach($similarLines as $sLine)
                                    <div class="list-clean-item">{{ $sLine }}</div>
                                @endforeach
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->section ?? '-' }}</td>
                    <td>{{ $item->process ?? '-' }}</td>
                    <td class="td-left">{{ $item->problem ?? '-' }}</td>
                    <td class="td-left">{{ $item->cause ?? '-' }}</td>
                    <td class="td-left">
                        @if(count($cmLines) > 0)
                            <div class="list-clean">
                                @foreach($cmLines as $cmLine)
                                    @if(preg_match('/^(\d+\.)?\s*\[([^\]]+)\]\s*Corrective:\s*(.*?)\s*\|\s*Preventive:\s*(.*)$/i', $cmLine, $m))
                                        <div class="cm-block">
                                            <strong>{{ $m[1] ? $m[1] . ' ' : '' }}[{{ $m[2] }}]</strong><br>
                                            &bull; <strong>Corrective:</strong> {{ $m[3] }}<br>
                                            &bull; <strong>Preventive:</strong> {{ $m[4] }}
                                        </div>
                                    @else
                                        <div class="list-clean-item">{{ $cmLine }}</div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->pic ?? '-' }}</td>
                    <td>{{ $item->supplier ?? '-' }}</td>
                    <td>{{ $item->defect_category ?? '-' }}</td>
                    <td style="white-space: nowrap;">
                        @if(strtolower($item->status) == 'open')
                            <span style="color: #e74a3b; font-weight: bold;">OPEN</span>
                        @elseif(strtolower($item->status) == 'closed' || strtolower($item->status) == 'close')
                            <span style="color: #1cc88a; font-weight: bold;">CLOSED</span>
                        @else
                            {{ $item->status ?? '-' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="23" style="padding: 20px;">Tidak ada data yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>

    <script>
        window.onload = function() {
            window.print();
            // Optional: window.close(); // Only if opened in new tab
        };
    </script>
</body>
</html>
