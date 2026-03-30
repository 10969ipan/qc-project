@extends('layouts.admin')

@section('title', 'First Piece Approval')

@section('content')
<style>
    .table-responsive {
        max-height: 75vh !important;
        overflow: auto !important;
        border: 1px solid #dee2e6 !important;
    }
    #checksheetTable { border-collapse: separate !important; border-spacing: 0 !important; }
    
    /* Global TH sticky setup */
    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #4e73df !important;
        color: white !important;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 6px 4px !important;
        border: 1px solid #ffffff44 !important;
        vertical-align: middle !important;
        height: 35px !important;
    }

    /* First row sticky at top: 0 */
    #checksheetTable > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
    }

    /* Second row sticky at top: 35px (matching height of row 1) */
    #checksheetTable > thead > tr:nth-child(2) > th {
        top: 35px !important;
        z-index: 104 !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
    }

    /* Rowspan cell specifically handled to fill both rows correctly */
    #checksheetTable > thead > tr:nth-child(1) > th[rowspan="2"] {
        height: 70px !important;
    }
</style>
    @php
        $plant = request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');
    @endphp
    <div class="card shadow mb-2">
        <div class="card-body p-0">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                        <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                    </td>
                    <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                        <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                            LAPORAN DATA CHECKSHEET FIRST PIECE APPROVAL
                        </h1>
                    </td>
                    <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                        <table style="border-collapse:collapse; font-size:0.68rem;">
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? 'QC-JKT-F-032/0' : 'QC-KRW-F-0212' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? '21.02.2023' : '25/03/2015' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">
                                    {{ $plantCode === 'jakarta' ? '1 / 14.06.2023' : '3 / 22/12/2025' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                <td style="padding:1px 2px;">:</td>
                                <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">1 / 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <!-- Hidden Logo for PDF Export -->
    <img src="{{ asset('master item/ipp.jpg') }}" id="pdf-logo" style="display: none;" alt="Company Logo">

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('first_piece_approval.index') }}" method="GET"
                class="d-flex flex-wrap align-items-center bg-light p-2 rounded mb-3 shadow-sm"
                style="gap: 10px;" id="filterFormFpa">

                <input type="hidden" name="plant" value="{{ request('plant') }}">

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Cari:</label>
                    <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm"
                        style="width: 180px; border-radius: 0.35rem;" placeholder="Nama item, part no..."
                        value="{{ request('search') }}">
                </div>

                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Dari:</label>
                    <input type="date" name="start_date" class="form-control form-control-sm border-0 shadow-sm"
                        style="border-radius: 0.35rem;" value="{{ request('start_date') }}">
                </div>
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 small font-weight-bold text-gray-700">Sampai:</label>
                    <input type="date" name="end_date" class="form-control form-control-sm border-0 shadow-sm"
                        style="border-radius: 0.35rem;" value="{{ request('end_date') }}">
                </div>

                <div class="ml-auto d-flex" style="gap: 5px;">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3" title="Cari Data">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                    <a href="{{ route('first_piece_approval.index', ['plant' => request('plant')]) }}"
                        class="btn btn-secondary btn-sm shadow-sm rounded-pill px-3 no-loader" title="Reset Filter">
                        <i class="fas fa-undo fa-sm"></i>
                    </a>
                    <a href="{{ route('first_piece_approval.export_pdf', request()->query()) }}"
                        class="btn btn-danger btn-sm shadow-sm rounded-pill px-3 no-loader btn-download" title="Export to PDF">
                        <i class="fas fa-file-pdf fa-sm"></i>
                    </a>
                    <a href="{{ route('first_piece_approval.print', request()->query()) }}"
                        target="_blank"
                        class="btn btn-sm shadow-sm rounded-pill px-3 no-loader" title="Print"
                        style="background-color: #17a589; color: white;">
                        <i class="fas fa-print fa-sm"></i>
                    </a>
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="checksheetTable">
                    <thead>
                        @php
                            $requestPlant = request('plant');
                            $userPlantCode = optional(auth()->user()->plant)->code;
                            if (!empty($requestPlant)) {
                                $plant = \App\Models\Plant::where('code', $requestPlant)->orWhere('id', $requestPlant)->first();
                                $plantContext = strtolower($plant?->code ?? $requestPlant);
                            } else {
                                $plantContext = strtolower(!empty($userPlantCode) ? $userPlantCode : 'karawang');
                            }
                        @endphp
                        <tr class="text-center">
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Tanggal</th>
                            <th rowspan="2" class="align-middle">Jam (Before)</th>
                            <th rowspan="2" class="align-middle">Jam (After)</th>
                            <th rowspan="2" class="align-middle">Cycle Time (s)</th>
                            <th rowspan="2" class="align-middle">Shift</th>
                            <th rowspan="2" class="align-middle d-none">Kode SAP</th>
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Customer</th>
                            <th rowspan="2" class="align-middle">Part No</th>
                            <th rowspan="2" class="align-middle">Total Qty</th>
                            <th rowspan="2" class="align-middle">Sampling Qty</th>
                            <th rowspan="2" class="align-middle">Check Dimensi</th>
                            <th rowspan="2" class="align-middle">Berat Part</th>
                            <th rowspan="2" class="align-middle">OK</th>
                            <th rowspan="2" class="align-middle">NG</th>
                            <th colspan="2" class="align-middle">Detail NG</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial</th>

                            <th colspan="4" class="align-middle">Approval Status</th>
                            <th rowspan="2" class="align-middle">Keterangan</th>
                            @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                <th rowspan="2" class="no-export align-middle">Aksi</th>
                            @endif
                        </tr>
                        <tr class="text-center">
                            <th style="width: 5%">Pcs</th>
                            <th>Jenis NG</th>
                            <th style="font-size: 10px;">{{ $plantContext === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}
                            </th>
                            <th style="font-size: 10px;">Supervisor QC</th>
                            <th style="font-size: 10px;">Asst. Manager QC</th>
                            <th style="font-size: 10px;">Manager QC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checksheets as $checksheet)
                            <tr class="text-center">
                                <td class="align-middle">{{ $checksheets->firstItem() + $loop->index }}</td>
                                <td class="align-middle text-nowrap">
                                    {{ \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y') }}
                                </td>
                                <td class="align-middle">
                                    {{ $checksheet->created_at->copy()->subSeconds($checksheet->cycle_time ?? 0)->format('H:i') }}
                                </td>
                                <td class="align-middle">{{ $checksheet->created_at->format('H:i') }}</td>
                                <td class="align-middle">{{ $checksheet->cycle_time ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->shift }}</td>
                                <td class="align-middle text-nowrap d-none">{{ $checksheet->item->sap_code ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->name ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->customer ?? '-' }}</td>
                                <td class="align-middle text-nowrap">{{ $checksheet->item->part_number ?? '-' }}</td>
                                <td class="align-middle">{{ $checksheet->total_qty }}</td>
                                <td class="align-middle">{{ $checksheet->sampling_qty }}</td>

                                {{-- Dimension Check Detail --}}
                                <td class="align-middle p-0">
                                    @php
                                        $dimensions = is_array($checksheet->dimension_check) ? $checksheet->dimension_check : json_decode($checksheet->dimension_check, true);
                                        $dimensions = $dimensions ?: [];

                                        // Check if there is any actual user input
                                        $hasUserInputs = false;
                                        foreach ($dimensions as $cavPoints) {
                                            if (is_array($cavPoints)) {
                                                foreach ($cavPoints as $val) {
                                                    if ($val !== null && $val !== '' && $val !== '-') {
                                                        $hasUserInputs = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                        $itemPartNumber = str_replace([' ', "\xc2\xa0", "\t", "\n", "\r"], '', str_replace(["\xe2\x80\x92", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92"], '-', $checksheet->item->part_number ?? ''));
                                        $itemPartNumber = strtoupper($itemPartNumber);
                                        $standards = $partDimensionStandards[$itemPartNumber] ?? [];

                                        // Find active points (columns that have data or are defined in standards)
                                        $activePoints = [];
                                        foreach ($dimensions as $cavKey => $points) {
                                            if (is_array($points)) {
                                                foreach ($points as $pKey => $pVal) {
                                                    if ($pVal !== null && $pVal !== '' && $pVal !== '-' && $pVal !== 0 && $pVal !== '0') {
                                                        $activePoints[$pKey] = true;
                                                    }
                                                }
                                            }
                                        }
                                        foreach ($standards as $pKey => $std) {
                                            $activePoints[$pKey] = true;
                                        }
                                        $activePoints = array_keys($activePoints);
                                        sort($activePoints);

                                        // Default points if none found
                                        if (empty($activePoints)) {
                                            $activePoints = range(1, 5);
                                        }

                                        // Find max cavity for rendering rows
                                        $actualMaxCavity = 0;
                                        foreach ($dimensions as $cavKey => $pointsData) {
                                            $cavNum = (int) filter_var($cavKey, FILTER_SANITIZE_NUMBER_INT);
                                            $actualMaxCavity = max($actualMaxCavity, $cavNum);
                                        }
                                        $displayMaxCavity = max(5, $actualMaxCavity);
                                    @endphp
                                    @if($hasUserInputs)
                                        <div style="max-height: 200px; overflow-y: auto; font-size: 0.7rem;">
                                            <table class="table table-bordered table-sm m-0">
                                                <thead class="text-center" style="font-size: 0.6rem;">
                                                    {{-- Standard Row --}}
                                                    @php
                                                        $hasStdData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && ($standards[$j]['size'] !== null && $standards[$j]['size'] !== '' && $standards[$j]['size'] !== '-')) {
                                                                $hasStdData = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasStdData)
                                                        <tr class="bg-light" style="font-size: 0.55rem;">
                                                            <th class="p-1">Std</th>
                                                            @foreach ($activePoints as $j)
                                                                <th class="p-1 text-muted">
                                                                    {{ isset($standards[$j]) ? $standards[$j]['size'] : '-' }}
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Min Row --}}
                                                    @php
                                                        $hasMinData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && $standards[$j]['min'] !== null && $standards[$j]['min'] !== '' && $standards[$j]['min'] !== '-') {
                                                                $hasMinData = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasMinData)
                                                        <tr class="bg-light" style="font-size: 0.55rem;">
                                                            <th class="p-1">Min</th>
                                                            @foreach ($activePoints as $j)
                                                                <th class="p-1 text-muted">
                                                                    {{ (isset($standards[$j]) && $standards[$j]['min'] !== null) ? $standards[$j]['min'] : '-' }}
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Max Row --}}
                                                    @php
                                                        $hasMaxData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && $standards[$j]['max'] !== null && $standards[$j]['max'] !== '' && $standards[$j]['max'] !== '-') {
                                                                $hasMaxData = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasMaxData)
                                                        <tr class="bg-light" style="font-size: 0.55rem;">
                                                            <th class="p-1">Max</th>
                                                            @foreach ($activePoints as $j)
                                                                <th class="p-1 text-muted">
                                                                    {{ (isset($standards[$j]) && $standards[$j]['max'] !== null) ? $standards[$j]['max'] : '-' }}
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Tolerance Row --}}
                                                    @php
                                                        $hasTolData = false;
                                                        foreach ($activePoints as $j) {
                                                            if (isset($standards[$j]) && $standards[$j]['tolerance'] !== null && $standards[$j]['tolerance'] !== '' && $standards[$j]['tolerance'] !== '-') {
                                                                $hasTolData = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasTolData)
                                                        <tr class="bg-light" style="font-size: 0.55rem;">
                                                            <th class="p-1">Tol</th>
                                                            @foreach ($activePoints as $j)
                                                                <th class="p-1 text-muted">
                                                                    {{ isset($standards[$j]) ? '±' . $standards[$j]['tolerance'] : '-' }}
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    @endif

                                                    {{-- Main Header Row --}}
                                                    <tr>
                                                        <th>Cav</th>
                                                        @foreach ($activePoints as $j)
                                                            <th>Ø{{ $j }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- Actual Measurements --}}
                                                    @for ($i = 1; $i <= $displayMaxCavity; $i++)
                                                        @php
                                                            $rowHasData = false;
                                                            foreach ($activePoints as $j) {
                                                                $val = $dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? null);
                                                                if ($val !== null && $val !== '' && $val !== '-' && $val !== 0 && $val !== '0') {
                                                                    $rowHasData = true;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        @if($rowHasData)
                                                            <tr>
                                                                <td class="font-weight-bold p-1">{{ $i }}</td>
                                                                @foreach ($activePoints as $j)
                                                                    @php
                                                                        $val = $dimensions[$i][$j] ?? ($dimensions["$i"][$j] ?? '-');
                                                                        $isNG = false;
                                                                        if (isset($standards[$j]) && is_numeric($val)) {
                                                                            $std = $standards[$j];

                                                                            if ($std['min'] !== null && $val < $std['min']) {
                                                                                $isNG = true;
                                                                            }
                                                                            if ($std['max'] !== null && $val > $std['max']) {
                                                                                $isNG = true;
                                                                            }

                                                                            if (!$isNG && $std['min'] === null && $std['max'] === null) {
                                                                                if ($std['size'] !== null && $std['tolerance'] !== null) {
                                                                                    $min = $std['size'] - $std['tolerance'];
                                                                                    $max = $std['size'] + $std['tolerance'];
                                                                                    if ($val < $min || $val > $max) {
                                                                                        $isNG = true;
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <td class="p-1 {{ $isNG ? 'text-danger font-weight-bold' : '' }}" @if($isNG)
                                                                    style="color: #dc3545 !important; font-weight: bold !important;" @endif>
                                                                        {{ $val }}
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endif
                                                    @endfor
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <span class="text-dark font-weight-bold" style="font-size: 0.8rem;">TIDAK ADA PENGUKURAN
                                            DIMENSI</span>
                                    @endif
                                </td>

                                <td class="align-middle" style="min-width:90px;">
                                    @php
                                        $weights = is_array($checksheet->part_weight)
                                            ? $checksheet->part_weight
                                            : (is_string($checksheet->part_weight) && str_starts_with($checksheet->part_weight, '[')
                                                ? json_decode($checksheet->part_weight, true)
                                                : ($checksheet->part_weight ? [$checksheet->part_weight] : []));
                                    @endphp
                                    @if(!empty(array_filter($weights, fn($w) => $w !== null && $w !== '')))
                                        @foreach($weights as $ci => $wv)
                                            @if($wv !== null && $wv !== '')
                                                <div class="text-nowrap" style="font-size:0.75rem;">
                                                    <span class="text-muted">CAV{{ $ci + 1 }}:</span>
                                                    <strong>{{ $wv }}</strong><small class="text-muted"> gr</small>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="align-middle text-success font-weight-bold">{{ $checksheet->total_ok }}</td>
                                <td class="align-middle text-danger font-weight-bold">{{ $checksheet->total_ng }}</td>

                                @php
                                    $defectsData = is_array($checksheet->defects) ? $checksheet->defects : json_decode($checksheet->defects, true);
                                    $pcsLines = [];
                                    $nameLines = [];

                                    if (is_array($defectsData)) {
                                        foreach ($defectsData as $d) {
                                            if (is_array($d) && isset($d['type'])) {
                                                $qty = $d['qty'] ?? 1;
                                                $pcsLines[] = $qty;
                                                $nameLines[] = $d['type'];
                                            } elseif (is_string($d)) {
                                                $pcsLines[] = 1;
                                                $nameLines[] = $d;
                                            }
                                        }
                                    }
                                @endphp

                                <td class="text-center align-middle p-0">
                                    @if(count($pcsLines) > 0)
                                        @foreach($pcsLines as $index => $qty)
                                            <div class="{{ $index < count($pcsLines) - 1 ? 'border-bottom' : '' }} py-1">
                                                <small class="text-danger font-weight-bold">{{ $qty }}</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="py-1">-</div>
                                    @endif
                                </td>
                                <td class="text-center align-middle p-0">
                                    @if(count($nameLines) > 0)
                                        @foreach($nameLines as $index => $name)
                                            <div class="{{ $index < count($nameLines) - 1 ? 'border-bottom' : '' }} py-1 px-2">
                                                <small class="text-danger font-weight-bold">{{ $name }}</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="py-1 px-2">-</div>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    <span class="badge badge-{{ $checksheet->judgment == 'OK' ? 'success' : 'danger' }}">
                                        {{ $checksheet->judgment }}
                                    </span>
                                </td>
                                <td class="align-middle text-uppercase">{{ $checksheet->operator_initials }}</td>

                                {{-- Kashift QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->kashift_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                    @elseif($checksheet->kashift_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->kashift_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->kashift_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->kashift_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Supervisor QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->supervisor_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                    @elseif($checksheet->supervisor_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->supervisor_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->supervisor_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->supervisor_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Asst Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->asst_manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                    @elseif($checksheet->asst_manager_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->asst_manager_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->asst_manager_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->asst_manager_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                {{-- Manager QC --}}
                                <td class="align-middle text-center">
                                    @if($checksheet->manager_qc === 'REJECTED')
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-times-circle mr-1"></i> REJECTED
                                        </span>
                                        <br><small class="text-muted">oleh
                                            {{ getRejectorName($checksheet->rejection_remarks) }}</small>
                                    @elseif($checksheet->manager_qc)
                                        <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-check-circle mr-1"></i> APPROVED
                                        </span>
                                        <br><small class="text-muted">oleh {{ $checksheet->manager_qc }}</small>
                                    @else
                                        <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock mr-1"></i> PENDING
                                        </span>
                                    @endif
                                    @if($checksheet->manager_approved_at)
                                        <br><small
                                            class="text-muted">{{ \Carbon\Carbon::parse($checksheet->manager_approved_at)->format('d/m/Y H:i') }}</small>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    @if($checksheet->rejection_remarks)
                                        <div class="text-danger font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i> REJECTED
                                        </div>
                                        <small class="text-muted">{{ $checksheet->rejection_remarks }}</small>
                                    @else
                                        @if($checksheet->next_proses)
                                            <div class="mb-1">
                                                <span class="badge badge-danger px-2 py-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    LABEL MERAH: {{ $checksheet->next_proses }}
                                                </span>
                                                <br>
                                                @if(!str_contains($checksheet->remarks ?? '', '[SORTIR_CLOSED]'))
                                                    <span class="text-danger small font-weight-bold ml-1">
                                                        <i class="fas fa-clock"></i> STATUS: OPEN
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        {!! str_replace('[SORTIR_CLOSED]', '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> STATUS: CLOSE</span>', e($checksheet->remarks)) !!}
                                    @endif
                                </td>


                                @if(!in_array(auth()->user()->role, ['inspector', 'oshef']))
                                    <td class="align-middle text-center text-nowrap no-export" style="min-width: 350px;">
                                        {{-- Action Buttons for Approvals --}}
                                        @php
                                            $user = auth()->user();
                                            $isAdmin = $user->role === 'admin';
                                            $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                                            $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                                            $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;

                                            $canApproveKashift = ($user->role === 'kashift' || $isAdmin || $isSpvJakarta ||
                                                $isKaruJakarta) && (!$checksheet->kashift_qc || $checksheet->kashift_qc === 'REJECTED');
                                            $canApproveSupervisor = ($user->role === 'supervisor' || $isAdmin) &&
                                                (!$checksheet->supervisor_qc || $checksheet->supervisor_qc === 'REJECTED');
                                            $canApproveAsst = ($user->role === 'asst_manager' || $isAdmin) &&
                                                (!$checksheet->asst_manager_qc || $checksheet->asst_manager_qc === 'REJECTED');
                                            $canApproveManager = ($user->role === 'manager' || $isAdmin) && (!$checksheet->manager_qc ||
                                                $checksheet->manager_qc === 'REJECTED');
                                        @endphp

                                        @if($canApproveKashift)
                                            <form
                                                action="{{ route('first_piece_approval.approve', array_merge(['id' => $checksheet->id, 'type' => 'kashift'], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (Kashift)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ ($user->role === 'admin') ? ' KS' : (($isSpvJakarta || $isKaruJakarta) ? '' : '') }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (Kashift)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}kashift"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveSupervisor)
                                            <form
                                                action="{{ route('first_piece_approval.approve', array_merge(['id' => $checksheet->id, 'type' => 'supervisor'], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (SPV)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' SPV' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (SPV)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}supervisor"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveAsst)
                                            <form
                                                action="{{ route('first_piece_approval.approve', array_merge(['id' => $checksheet->id, 'type' => 'asst_manager'], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (AM)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' AM' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (AM)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}asst_manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                        @if($canApproveManager)
                                            <form
                                                action="{{ route('first_piece_approval.approve', array_merge(['id' => $checksheet->id, 'type' => 'manager'], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                                <input type="hidden" name="approval_status" value="{{ request('approval_status') }}">
                                                <input type="hidden" name="search" value="{{ request('search') }}">
                                                <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                <button type="submit" class="btn btn-success btn-sm m-1" title="Approve (MGR)"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-check"></i>
                                                    Approve{{ (auth()->user()->role === 'admin') ? ' MGR' : '' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm m-1" title="Reject (MGR)"
                                                data-toggle="modal" data-target="#rejectModal{{ $checksheet->id }}manager"
                                                style="min-width: 110px;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif

                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ route('admin.first_piece_approval.edit_approval', array_merge(['id' => $checksheet->id], request()->all())) }}"
                                                class="btn btn-info btn-sm m-1 btn-status-modal no-loader" title="Edit Approval Status"
                                                style="min-width: 110px;">
                                                <i class="fas fa-user-check"></i> Status
                                            </a>
                                        @endif
                                        @if(!in_array(auth()->user()->role, ['manager', 'asst_manager']))
                                            <a href="{{ route('first_piece_approval.edit', array_merge(['id' => $checksheet->id], request()->all())) }}"
                                                class="btn btn-warning btn-sm m-1 btn-edit-modal no-loader" title="Edit"
                                                style="min-width: 110px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form
                                                action="{{ route('first_piece_approval.destroy', array_merge(['id' => $checksheet->id], request()->all())) }}"
                                                method="POST" class="d-inline ajax-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm m-1 btn-delete" title="Delete"
                                                    style="min-width: 110px;">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $checksheets->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Checksheet First Piece Approval
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Modal (Admin) -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="statusModalLabel">
                        <i class="fas fa-tasks mr-2"></i> Update Status Approval Admin
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="statusModalBody">
                    <!-- Loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal for each checksheet and type -->
    @foreach($checksheets as $cs)
        @foreach(['kashift', 'supervisor', 'asst_manager', 'manager'] as $rejectType)
            @php
                $user = auth()->user();
                $isAdmin = $user->role === 'admin';
                $isJakarta = strtolower(optional($user->plant)->code) === 'jakarta';
                $isSpvJakarta = $user->role === 'supervisor' && $isJakarta;
                $isKaruJakarta = $user->role === 'karu_qc' && $isJakarta;
                $canReject = false;
                if (
                    $rejectType == 'kashift' && (($user->role === 'kashift' || $isAdmin || $isSpvJakarta || $isKaruJakarta) &&
                        (!$cs->kashift_qc || $cs->kashift_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'supervisor' && (($user->role === 'supervisor' || $isAdmin) && (!$cs->supervisor_qc ||
                        $cs->supervisor_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'asst_manager' && (($user->role === 'asst_manager' || $isAdmin) && (!$cs->asst_manager_qc ||
                        $cs->asst_manager_qc === 'REJECTED'))
                ) {
                    $canReject = true;
                } elseif (
                    $rejectType == 'manager' && (($user->role === 'manager' || $isAdmin) && (!$cs->manager_qc || $cs->manager_qc
                        === 'REJECTED'))
                ) {
                    $canReject = true;
                }
            @endphp
            @if($canReject)
                <div class="modal fade" id="rejectModal{{ $cs->id }}{{ $rejectType }}" tabindex="-1" role="dialog"
                    aria-labelledby="rejectModalLabel{{ $cs->id }}{{ $rejectType }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="rejectModalLabel{{ $cs->id }}{{ $rejectType }}">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Rejection
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form
                                action="{{ route('first_piece_approval.reject', array_merge(['id' => $cs->id, 'type' => $rejectType], request()->all())) }}"
                                method="POST" class="ajax-form">
                                @csrf
                                <div id="modal-errors" class="mx-3 mt-3" style="display: none;"></div>
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle"></i> Anda akan menolak checksheet ini sebagai
                                        <strong>{{ ($isJakarta && $rejectType === 'kashift') ? 'Kepala Regu (KR)' : ucfirst(str_replace('_', ' ', $rejectType)) }}</strong>
                                    </div>
                                    <div class="form-group">
                                        <label for="rejection_remarks{{ $cs->id }}{{ $rejectType }}" class="font-weight-bold">
                                            Alasan Rejection <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('rejection_remarks') is-invalid @enderror"
                                            id="rejection_remarks{{ $cs->id }}{{ $rejectType }}" name="rejection_remarks" rows="4"
                                            placeholder="Masukkan alasan rejection (minimal 10 karakter)" required minlength="10"
                                            maxlength="500">{{ old('rejection_remarks') }}</textarea>
                                        <small class="form-text text-muted">
                                            <span id="charCount{{ $cs->id }}{{ $rejectType }}">0</span>/500 karakter
                                        </small>
                                        @error('rejection_remarks')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-ban"></i> Tolak Checksheet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endforeach

@endsection
@push('scripts')
    <script src="{{ asset('public/js/checksheet/fpa.js') }}"></script>
    <script id="fpa-index-data" type="application/json" 
        data-route-index="{{ route('first_piece_approval.index') }}"
        data-plant="{{ request('plant') }}">
        @json($checksheets->pluck('id'))
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataEl = document.getElementById('fpa-index-data');
            if (!dataEl) return;

            window.initFpaIndex({
                routes: {
                    index: dataEl.getAttribute('data-route-index'),
                },
                plant: dataEl.getAttribute('data-plant'),
                checksheets: JSON.parse(dataEl.textContent)
            });
        });

        // Auto-submit filter
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('filterFormFpa');
            if (!form) return;

            function debounce(fn, delay) {
                var timer;
                return function () { clearTimeout(timer); timer = setTimeout(fn, delay); };
            }

            var searchInput = form.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(function () { form.submit(); }, 500));
            }

            form.querySelectorAll('input[type="date"]').forEach(function (el) {
                el.addEventListener('change', function () { form.submit(); });
            });
        });
    </script>
@endpush
