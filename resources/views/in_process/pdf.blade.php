
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Checksheet Inprocess</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 8px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 4px; text-align: center; }
        .table thead th { background-color: #f2f2f2; font-weight: bold; }
        .header-table { width: 100%; margin-bottom: 15px; }
        .header-table td { border: 1px solid #000; padding: 5px; vertical-align: middle; }
        .header-table .logo { width: 80px; text-align: center; }
        .header-table .title { text-align: center; font-size: 14px; font-weight: bold; }
        .header-table .doc-info { font-size: 9px; text-align: left; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .badge { display: inline-block; padding: .25em .4em; font-size: 75%; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .25rem; }
        .badge-success { color: #fff; background-color: #28a745; }
        .badge-danger { color: #fff; background-color: #dc3545; }
        .badge-warning { color: #212529; background-color: #ffc107; }
        .page-break { page-break-after: always; }
        .dimension-table { width: 100%; font-size: 7px; }
        .dimension-table td, .dimension-table th { padding: 2px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ public_path('master item/ipp.png') }}" style="max-width: 70px;">
            </td>
            <td class="title">LAPORAN CHECK SHEET INPROCESS</td>
            <td class="doc-info" style="width: 120px;">
                No. Dokumen: QC-KRW-F-0004<br>
                Tgl. Terbit: 25/09/2015<br>
                Revisi Ke: 3<br>
                Tgl. Revisi: 30/09/2020
            </td>
        </tr>
    </table>

    <p style="font-size: 10px;">
        <strong>Periode:</strong> {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua' }} - {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua' }}
        <br>
        <strong>Barang:</strong> {{ $request->item_id ? $items->find($request->item_id)->name : 'Semua Barang' }}
    </p>

    <table class="table">
        <thead>
            <tr class="text-center">
                <th rowspan="2">No</th>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Jam (Before)</th>
                <th rowspan="2">Jam (After)</th>
                <th rowspan="2">Cycle Time</th>
                <th rowspan="2">Shift</th>
                <th rowspan="2">Barang</th>
                <th rowspan="2">Part No</th>
                <th rowspan="2">Customer</th>
                <th rowspan="2">Total Qty</th>
                <th rowspan="2">Sampling Qty</th>
                <th rowspan="2">Check Dimensi</th>
                <th rowspan="2">OK</th>
                <th rowspan="2">NG</th>
                <th colspan="2">Detail NG</th>
                <th rowspan="2">Judgment</th>
                <th rowspan="2">Inisial</th>
                <th rowspan="2">Kashift QC</th>
                <th rowspan="2">Supervisor QC</th>
                <th rowspan="2">Asst. Manager QC</th>
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th>Pcs</th>
                <th>Jenis NG</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checksheets as $checksheet)
            <tr class="text-center">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $checksheet->date }}</td>
                <td>{{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}</td>
                <td>{{ $checksheet->created_at->format('H:i') }}</td>
                <td>{{ $checksheet->cycle_time ?? '-' }}</td>
                <td>{{ $checksheet->shift }}</td>
                <td>{{ $checksheet->item->name ?? '-' }}</td>
                <td>{{ $checksheet->item->part_number ?? '-' }}</td>
                <td>{{ $checksheet->item->customer ?? '-' }}</td>
                <td>{{ $checksheet->total_qty }}</td>
                <td>{{ $checksheet->sampling_qty }}</td>
                
                <td style="padding: 0;">
                    @php $dimensions = json_decode($checksheet->dimension_check, true); @endphp
                    @if(is_array($dimensions) && count($dimensions) > 0)
                        <table class="dimension-table">
                            <thead>
                                <tr>
                                    <th>Cav</th>
                                    @php
                                        // Find all unique dimension points across all cavities for this checksheet
                                        $points = [];
                                        foreach ($dimensions as $cavityData) {
                                            $points = array_merge($points, array_keys($cavityData));
                                        }
                                        $uniquePoints = array_unique($points);
                                        sort($uniquePoints);
                                    @endphp
                                    @foreach($uniquePoints as $point)
                                        <th>Ø{{ $point }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dimensions as $cavity => $cavityData)
                                    <tr>
                                        <td>{{ $cavity }}</td>
                                        @foreach($uniquePoints as $point)
                                            <td>{{ $cavityData[$point] ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        -
                    @endif
                </td>

                <td class="text-success">{{ $checksheet->total_ok }}</td>
                <td class="text-danger">{{ $checksheet->total_ng }}</td>
                
                @php
                    $defectsData = json_decode($checksheet->defects, true);
                    $pcsLines = [];
                    $nameLines = [];
                    if (is_array($defectsData)) {
                        foreach ($defectsData as $d) {
                            if (is_array($d) && isset($d['type'])) {
                                $pcsLines[] = $d['qty'] ?? 1;
                                $nameLines[] = $d['type'];
                            } elseif (is_string($d)) {
                                 $pcsLines[] = 1;
                                 $nameLines[] = $d;
                            }
                        }
                    }
                @endphp

                <td class="text-danger">{!! count($pcsLines) > 0 ? implode('<br>', $pcsLines) : '-' !!}</td>
                <td class="text-danger">{!! count($nameLines) > 0 ? implode('<br>', $nameLines) : '-' !!}</td>

                <td>
                    <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                        {{ $checksheet->judgment }}
                    </span>
                </td>
                <td>{{ $checksheet->operator_initials }}</td>
                
                <td>
                    @if($checksheet->kashift_qc === 'REJECTED')
                        <span class="badge badge-danger">REJECTED</span>
                    @elseif($checksheet->kashift_qc)
                        <span class="badge badge-success">APPROVED</span>
                    @else
                        <span class="badge badge-warning">PENDING</span>
                    @endif
                </td>
                <td>
                    @if($checksheet->supervisor_qc === 'REJECTED')
                        <span class="badge badge-danger">REJECTED</span>
                    @elseif($checksheet->supervisor_qc)
                        <span class="badge badge-success">APPROVED</span>
                    @else
                        <span class="badge badge-warning">PENDING</span>
                    @endif
                </td>
                 <td>
                    @if($checksheet->asst_manager_qc === 'REJECTED')
                        <span class="badge badge-danger">REJECTED</span>
                    @elseif($checksheet->asst_manager_qc)
                        <span class="badge badge-success">APPROVED</span>
                    @else
                        <span class="badge badge-warning">PENDING</span>
                    @endif
                </td>
                
                <td>{{ $checksheet->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
