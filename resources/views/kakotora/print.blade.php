<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Kakotora - {{ strtoupper($plant) }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 7px;
            color: #333;
            margin: 0;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .logo { width: 80px; text-align: center; }
        .title { text-align: center; font-size: 14px; font-weight: bold; }
        .doc-info { width: 180px; font-size: 9px; }
        .doc-info table { width: 100%; border: none; }
        .doc-info td { border: none; padding: 1px 2px; }

        .table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .table th {
            background-color: #f2f2f2;
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
        }
        .table td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: center;
            font-size: 7px;
            word-break: break-word;
        }
        .footer { margin-top: 10px; font-size: 8px; color: #666; text-align: right; }
        
        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }
        .badge-open { background-color: #e74a3b; }
        .badge-closed { background-color: #1cc88a; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
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

    <table class="table">
        <thead>
            <tr>
                <th>NO</th>
                <th>TANGGAL</th>
                <th>NO REGISTRASI</th>
                <th>ISSUE DATE</th>
                <th>REV</th>
                <th>FAM</th>
                <th>CAT</th>
                <th>CLAIM</th>
                <th>MODEL</th>
                <th style="width: 80px;">PART NAME</th>
                <th>PART NO</th>
                <th>MOULD</th>
                <th>OWNER</th>
                <th style="width: 60px;">SIMILAR</th>
                <th>SEC</th>
                <th>PROSES</th>
                <th style="width: 80px;">PROBLEM</th>
                <th style="width: 80px;">CAUSE</th>
                <th style="width: 80px;">COUNTERMEASURE</th>
                <th>PIC</th>
                <th>SUPPLIER</th>
                <th>DEFECT</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kakotoras as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d/m/y') : '-' }}</td>
                    <td style="font-size: 6px;">{{ $item->no_reg ?? '-' }}</td>
                    <td>{{ $item->issue_date ? \Carbon\Carbon::parse($item->issue_date)->format('d/m/y') : '-' }}</td>
                    <td>{{ $item->rev_model ?? '-' }}</td>
                    <td>{{ $item->family ?? '-' }}</td>
                    <td>{{ $item->category_nm_mp ?? '-' }}</td>
                    <td>{{ $item->category_claim ?? '-' }}</td>
                    <td>{{ $item->model ?? '-' }}</td>
                    <td style="text-align: left; font-size: 6px;">{{ $item->part_name ?? '-' }}</td>
                    <td style="font-size: 6px;">{{ $item->part_number ?? '-' }}</td>
                    <td>{{ $item->mould ?? '-' }}</td>
                    <td>{{ $item->owner_mould ?? '-' }}</td>
                    <td style="text-align: left; font-size: 6px;">{{ $item->similar_part ?? '-' }}</td>
                    <td>{{ $item->section ?? '-' }}</td>
                    <td>{{ $item->process ?? '-' }}</td>
                    <td style="text-align: left; font-size: 6px;">{{ $item->problem ?? '-' }}</td>
                    <td style="text-align: left; font-size: 6px;">{{ $item->cause ?? '-' }}</td>
                    <td style="text-align: left; font-size: 6px;">{{ $item->countermeasure ?? '-' }}</td>
                    <td>{{ $item->pic ?? '-' }}</td>
                    <td>{{ $item->supplier ?? '-' }}</td>
                    <td>{{ $item->defect_category ?? '-' }}</td>
                    <td>
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
