@extends('layouts.admin')

@section('title', 'Input Data Checksheet')

@section('content')


<style>
    /* ─── Create Form Table: Minimalist Industrial (selaras dengan index.blade.php) ─── */
    #checksheetTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: 1px solid #e2e8f0 !important;
        width: 100% !important;
        table-layout: auto !important;
    }

    #checksheetTable td, #checksheetTable th {
        border: 1px solid #e2e8f0 !important;
    }

    #checksheetTable > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 105 !important;
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.62rem !important;
        letter-spacing: 0.2px;
        padding: 8px 12px !important;
        border: 1px solid #e2e8f0 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        vertical-align: middle !important;
        line-height: 1.2;
        white-space: nowrap !important;
    }

    #checksheetTable > tbody > tr > td {
        border: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.8rem !important;
        padding: 8px 10px !important;
    }

    #checksheetTable .form-control {
        font-size: 0.78rem !important;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background-color: #f8fafc;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #checksheetTable .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        background-color: #fff;
    }

    #checksheetTable .btn {
        font-size: 0.7rem !important;
        padding: 0.25rem 0.5rem !important;
    }

    #totalQtyInput::-webkit-outer-spin-button,
    #totalQtyInput::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    #totalQtyInput { -moz-appearance: textfield; }

    /* ─── Inner Dimension Table ─── */
    .dimension-table,
    #dimensionTable,
    #checksheetTable .table-sm {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        margin: 0 !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
    }
    .dimension-table td,
    .dimension-table th,
    #dimensionTable td,
    #dimensionTable th {
        background-color: transparent !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: none !important;
        border-left: none !important;
        padding: 4px 6px !important;
        text-align: center !important;
        font-size: 0.68rem !important;
        white-space: nowrap !important;
    }
    .dimension-table th:last-child,
    .dimension-table td:last-child {
        border-right: none !important;
    }
    .dimension-table tr:last-child td {
        border-bottom: none !important;
    }
    .dimension-table th:first-child,
    .dimension-table td:first-child {
        min-width: 65px !important;
        width: 65px !important;
    }
    .dimension-table .point-header,
    .dimension-table .point-cell {
        min-width: 55px !important;
    }
    .dimension-table thead th,
    #dimensionTable thead th {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.58rem !important;
        border-bottom: 1px solid #cbd5e1 !important;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .dimension-table tbody td,
    #dimensionTable tbody td {
        color: #1e293b !important;
        font-size: 0.65rem !important;
    }
    .dimension-table .dimension-input,
    #dimensionTable .dimension-input {
        font-size: 0.68rem !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        background: #ffffff !important;
        text-align: center;
        padding: 3px 5px !important;
        width: 100%;
        min-width: 45px;
    }
    .dimension-table .dimension-input::placeholder {
        color: #94a3b8 !important;
        opacity: 0.8;
    }
    .dimension-table .dimension-input:focus,
    #dimensionTable .dimension-input:focus {
        border-color: #6366f1 !important;
        background: #fff !important;
        box-shadow: 0 0 0 2px rgba(99,102,241,0.1) !important;
    }
    .dimension-table .dimension-input.is-invalid,
    #dimensionTable .dimension-input.is-invalid {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        font-weight: 700 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25) !important;
        animation: ngPulse 1.2s infinite ease-in-out;
    }
    .dimension-table .dimension-input.is-valid,
    #dimensionTable .dimension-input.is-valid {
        border-color: #22c55e !important;
        background-color: #f0fdf4 !important;
        color: #15803d !important;
    }

    @keyframes ngPulse {
        0% {
            background-color: #fef2f2;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
            transform: scale(1);
        }
        50% {
            background-color: #fee2e2;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.4);
            transform: scale(1.02);
        }
        100% {
            background-color: #fef2f2;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
            transform: scale(1);
        }
    }

    /* ─── OK/NG Labels ─── */
    .ok-label  { background-color: #16a34a; color: #fff; }
    .ng-label  { background-color: #dc2626; color: #fff; }

    /* ─── Judgment Badge ─── */
    #judgmentBadge {
        font-size: 1rem !important;
        letter-spacing: 0.5px;
    }

    /* ─── Card header style ─── */
    .card-header h6.text-primary {
        font-size: 0.78rem;
        letter-spacing: 0.3px;
        font-weight: 700;
        text-transform: uppercase;
        color: #3b5bdb !important;
    }

    /* ─── Temporary Queue Card & Table ─── */
    #tempQueueCard {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
    }
    #tempQueueCard .card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    #tempQueueTable {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: 1px solid #cbd5e1 !important;
        width: 100% !important;
    }
    #tempQueueTable td, #tempQueueTable th {
        border: 1px solid #cbd5e1 !important;
        vertical-align: middle !important;
    }
    #tempQueueTable thead th {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.68rem !important;
        letter-spacing: 0.3px;
        padding: 10px 12px !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    #tempQueueTable tbody td {
        font-size: 0.8rem !important;
        color: #334155 !important;
        padding: 8px 10px !important;
    }
    .badge-info-premium {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 700;
        border: 1px solid #bae6fd;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
    }

</style>
    @php
        $plant = $plant ?? request('plant') ?? auth()->user()->plant_id;
        $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
        $plantCode = strtolower($plantCode ?: 'karawang');
    @endphp

    @php
        $docHeader = \App\Models\GeneralSetting::getDocHeader('in_process', $plantCode, [
            'no_dokumen' => 'QC-KRW-F-0201',
            'tgl_terbit' => '01/01/2026',
            'revisi' => '0',
            'halaman' => '- / -'
        ]);
        $jakartaMachineNumbers = [1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
        $karawangMachineNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19];
        $machineNumbers = ($plantCode === 'jakarta') ? $jakartaMachineNumbers : $karawangMachineNumbers;
    @endphp





    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($errors) && (is_array($errors) ? count($errors) > 0 : $errors->any()))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="font-weight-bold">Terjadi Kesalahan!</h6>
            <ul class="mb-0">
                @foreach(is_array($errors) ? $errors : $errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="mb-3">
                <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                CHECK SHEET IN PROCESS
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e6; border-left:none; padding:4px 8px; vertical-align:middle; white-space:nowrap;">
                            <table style="border-collapse:collapse; font-size:0.68rem;">
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">No. Dokumen</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['no_dokumen'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Tgl. Terbit</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Revisi / Tgl</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">Halaman</td>
                                    <td style="padding:1px 2px;">:</td>
                                    <td style="padding:1px 3px; font-weight:600; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Control Status Mesin (Manual) -->
            <div class="card border-0 shadow-none mb-3">
                <a href="#collapseMachineStatus" class="d-block card-header py-2 bg-light border-0 text-decoration-none" style="border-radius:4px;" data-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="collapseMachineStatus">
                    <h6 class="m-0 font-weight-bold text-info" style="font-size:0.85rem;"><i class="fas fa-sliders-h mr-1"></i> Control Status Mesin (Manual)</h6>
                </a>
                <div class="collapse" id="collapseMachineStatus">
                    <div class="card-body py-2">
                        <form action="{{ route('machine-status.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="machine">
                            <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                            <div class="row align-items-end">
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Pilih Mesin</label>
                                    <select name="number" class="form-control form-control-sm" required>
                                        <option value="">- Pilih Mesin -</option>
                                        @foreach($machineNumbers as $num)
                                            <option value="{{ $num }}">MESIN-{{ $num }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Status</label>
                                    <select name="status" class="form-control form-control-sm" required>
                                        <option value="normal">NORMAL (Auto)</option>
                                        <option value="maintenance">GANTI MOLD/SETTING (Kuning)</option>
                                        <option value="stopped">STAND BY (Hitam)</option>
                                        <option value="trouble">TROUBLE (Merah)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small font-weight-bold">Keterangan (Optional)</label>
                                    <input type="text" name="description" class="form-control form-control-sm"
                                        placeholder="Keterangan...">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="submit" class="btn btn-info btn-sm btn-block">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <form action="{{ route('in_process.store') }}" method="POST" id="checksheetForm" novalidate>
                @csrf
                <input type="hidden" name="plant" value="{{ request('plant') ?? auth()->user()->plant_id }}">
                <input type="hidden" name="qrcode" id="qrcodeInput">
                <input type="hidden" name="part_code" id="partCodeInput">
                <input type="hidden" name="supplier_id" id="supplierIdInput">
                <input type="hidden" name="quantity" id="quantityInput">
                <input type="hidden" name="unique_code_id" id="uniqueCodeInput">
                <input type="hidden" name="sap_code" id="sapCodeInputHidden">
                <input type="hidden" name="scan_method" id="scanMethodInput" value="manual">
                <div class="table-responsive" style="overflow-x: auto; border: none; box-shadow: inset 0 0 5px rgba(0,0,0,0.02);">
                    <table class="table" id="checksheetTable" width="100%" cellspacing="0">
                        <thead>
                        <tr class="text-center">
                            <th rowspan="2" class="align-middle">Item Part</th>
                            <th rowspan="2" class="align-middle">Tanggal / Shift</th>
                            <th rowspan="2" class="align-middle">Qty<br>(Total / Sampling)</th>
                            <th rowspan="2" class="align-middle">Check Dimensi</th>
                            <th rowspan="2" class="align-middle col-berat-part" style="display: none;">Berat Part</th>
                            <th rowspan="2" class="align-middle" style="min-width: 280px;">Jenis (OK/NG) &amp; Detail NG</th>
                            <th rowspan="2" class="align-middle">Total (OK/NG)</th>
                            <th rowspan="2" class="align-middle">Judgment</th>
                            <th rowspan="2" class="align-middle">Inisial QC</th>
                            <th rowspan="2" class="align-middle">DESCRIPTION</th>
                        </tr>
                        <tr></tr>
                        </thead>
                        <tbody>
                            <tr>

                                <!-- Pilihan Barang -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold small font-weight-bold mb-1">
                                        Scan Verifikasi Quanlity
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="sapCodeInput"
                                                placeholder="Tap kolom ini, lalu scan barcode label" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btnScanQR"
                                                    title="Buka QR Scanner">
                                                    <i class="fas fa-qrcode"></i>
                                                    <span class="d-none d-md-inline ml-1"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Arahkan kursor ke sini sebelum menembak QR</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Item Part</label>
                                        <select class="form-control" name="item_id" id="itemSelect" required
                                            style="min-width: 300px;">
                                            <option value="" disabled selected style="font-weight: bold; color: #6c757d;">
                                                Pilih
                                                Item Part</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                                                    data-file="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-files="{{ json_encode($item->file_paths ?? ($item->file_path ? [$item->file_path] : [])) }}"
                                                    data-standard="{{ $item->file_path ? route('items.pdf', $item->id) : '' }}"
                                                    data-similar="{{ $item->similar_part_file_path ? route('items.pdf', ['id' => $item->id, 'index' => 'similar']) : '' }}"
                                                    data-name="{{ $item->name }}" data-part-number="{{ $item->part_number }}"
                                                    data-description="{{ $item->description }}"
                                                    data-defects='@json($item->defects ?? [])'
                                                    data-sap_code="{{ $item->sap_code ?? '' }}"
                                                    data-cavity="{{ $item->cavity }}" data-customer="{{ $item->customer }}"
                                                    data-weight-standard="{{ $item->weight_standard }}"
                                                    data-dimension-standards='@json($item->dimension_standards ?? [])'>
                                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                                    {{ $item->sap_code ? '- SAP: ' . $item->sap_code : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Tanggal / Shift -->
                                <td class="align-middle">
                                    <div class="form-group mb-2">
                                        <label class="sr-only">Tanggal</label>
                                        <input type="date" class="form-control" style="min-width: 110px;" name="date"
                                            value="{{ $defaultDate }}" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="sr-only">Shift</label>
                                        <select class="form-control" style="min-width: 80px;" name="shift" required>
                                            <option value="1" {{ ($defaultShift ?? 1) == 1 ? 'selected' : '' }}>Shift 1
                                            </option>
                                            <option value="2" {{ ($defaultShift ?? 1) == 2 ? 'selected' : '' }}>Shift 2
                                            </option>
                                            <option value="3" {{ ($defaultShift ?? 1) == 3 ? 'selected' : '' }}>Shift 3
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label class="sr-only">No Mesin</label>
                                        <select name="code_machine" id="code_machine" class="form-control"
                                            style="min-width: 80px;" required>
                                            <option value="">Pilih Mesin</option>
                                            @foreach($machineNumbers as $num)
                                                <option value="{{ $num }}">Mesin {{ $num }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                 <!-- Qty (Total / Sampling) - 2 Kolom Input Shoot -->
                                <td class="align-top" style="min-width: 175px; max-width: 220px; padding-top: 44px;">
                                    <!-- Shoot 1 -->
                                    <div style="margin-bottom: 24px;">
                                        <small class="font-weight-bold text-muted d-block mb-1" style="font-size: 0.65rem;">Shoot 1:</small>
                                        <div class="d-flex align-items-center justify-content-center form-control form-control-sm px-2 py-0 overflow-hidden" style="background-color: #ffffff !important; border: 1px solid #d1d5db; height: 28px; gap: 2px;">
                                            <input type="number" class="border-0 text-center font-weight-bold shadow-none m-0 total-qty-pass" name="total_qty_1" id="totalQtyInput1" min="0" required style="background: transparent !important; box-shadow: none !important; width: 50%; min-width: 35px; font-size: 0.8rem; outline: none; padding: 0;">
                                            <span class="font-weight-bold text-dark text-nowrap sampling-display-pass" id="samplingDisplay1" style="user-select: none; font-size: 0.8rem; white-space: nowrap;">/ -</span>
                                        </div>
                                        <input type="hidden" name="sampling_qty_1" id="samplingQtyInput1" value="0">
                                    </div>
                                    <!-- Shoot 2 -->
                                    <div>
                                        <small class="font-weight-bold text-muted d-block mb-1" style="font-size: 0.65rem;">Shoot 2:</small>
                                        <div class="d-flex align-items-center justify-content-center form-control form-control-sm px-2 py-0 overflow-hidden" style="background-color: #ffffff !important; border: 1px solid #d1d5db; height: 28px; gap: 2px;">
                                            <input type="number" class="border-0 text-center font-weight-bold shadow-none m-0 total-qty-pass" name="total_qty_2" id="totalQtyInput2" min="0" required style="background: transparent !important; box-shadow: none !important; width: 50%; min-width: 35px; font-size: 0.8rem; outline: none; padding: 0;">
                                            <span class="font-weight-bold text-dark text-nowrap sampling-display-pass" id="samplingDisplay2" style="user-select: none; font-size: 0.8rem; white-space: nowrap;">/ -</span>
                                        </div>
                                        <input type="hidden" name="sampling_qty_2" id="samplingQtyInput2" value="0">
                                    </div>
                                     <!-- Hidden Inputs untuk Backend Submission -->
                                    <input type="hidden" name="total_qty" id="totalQtyInput" value="0">
                                    <input type="hidden" name="sampling_qty" id="samplingQtyInput" value="0">
                                </td>

                                 <!-- Cek Dimensi (Cavity & Titik - Sub-kolom SHOOT 1 | SHOOT 2) -->
                                <td class="align-middle">
                                    <div class="d-flex justify-content-center mb-2">
                                        <div class="btn-toolbar bg-white border rounded shadow-sm p-1" role="toolbar">
                                            <div class="btn-group mr-2" role="group">
                                                <button type="button" class="btn btn-primary btn-xs" id="addCavityBtn"
                                                    title="Tambah Cavity">
                                                    <i class="fas fa-plus"></i> Cavity
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-xs"
                                                    id="deleteCavityBtn" title="Hapus Cavity Terakhir">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-info btn-xs" id="addPointBtn"
                                                    title="Tambah Point">
                                                    <i class="fas fa-plus"></i> Point
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-xs"
                                                    id="deletePointBtn" title="Hapus Point Terakhir">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive" style="max-height: 450px; overflow: auto;">
                                        <!-- SHOOT 1 TABLE -->
                                        <div class="mb-3">
                                            <small class="font-weight-bold text-muted d-block mb-1" style="font-size: 0.65rem;">Shoot 1:</small>
                                            <table class="table table-sm table-bordered mb-0 dimension-table" id="dimensionTableShoot1">
                                                <thead class="text-center bg-light">
                                                    <tr id="dimensionHeadRowShoot1">
                                                        <th style="min-width: 65px; position: sticky; left: 0; z-index: 2; background: #f8f9fa; vertical-align: middle;">CAVITY</th>
                                                        @for ($j = 1; $j <= 5; $j++)
                                                            <th class="point-header text-center align-middle" data-point="{{ $j }}">POINT {{ $j }}</th>
                                                        @endfor
                                                    </tr>
                                                </thead>
                                                <tbody id="dimensionBodyShoot1">
                                                    @for ($i = 1; $i <= 2; $i++)
                                                        <tr class="cavity-row cavity-row-s1" data-cavity="{{ $i }}">
                                                            <td class="text-center font-weight-bold bg-light align-middle" style="position: sticky; left: 0; z-index: 1;">Cav {{ $i }}</td>
                                                            @for ($j = 1; $j <= 5; $j++)
                                                                <td class="point-cell p-1 text-center">
                                                                    <input type="text"
                                                                        class="form-control form-control-sm dimension-input dim-pass-1"
                                                                        style="width: 100%; min-width: 45px; font-size: 0.75rem; padding: 2px 3px; text-align: center;" 
                                                                        placeholder="P{{ $j }}"
                                                                        name="dimensions[{{ $i }}][{{ $j }}][p1]">
                                                                </td>
                                                            @endfor
                                                        </tr>
                                                    @endfor
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- SHOOT 2 TABLE -->
                                        <div>
                                            <small class="font-weight-bold text-muted d-block mb-1" style="font-size: 0.65rem;">Shoot 2:</small>
                                            <table class="table table-sm table-bordered mb-0 dimension-table" id="dimensionTableShoot2">
                                                <thead class="text-center bg-light">
                                                    <tr id="dimensionHeadRowShoot2">
                                                        <th style="min-width: 65px; position: sticky; left: 0; z-index: 2; background: #f8f9fa; vertical-align: middle;">CAVITY</th>
                                                        @for ($j = 1; $j <= 5; $j++)
                                                            <th class="point-header text-center align-middle" data-point="{{ $j }}">POINT {{ $j }}</th>
                                                        @endfor
                                                    </tr>
                                                </thead>
                                                <tbody id="dimensionBodyShoot2">
                                                    @for ($i = 1; $i <= 2; $i++)
                                                        <tr class="cavity-row cavity-row-s2" data-cavity="{{ $i }}">
                                                            <td class="text-center font-weight-bold bg-light align-middle" style="position: sticky; left: 0; z-index: 1;">Cav {{ $i }}</td>
                                                            @for ($j = 1; $j <= 5; $j++)
                                                                <td class="point-cell p-1 text-center">
                                                                    <input type="text"
                                                                        class="form-control form-control-sm dimension-input dim-pass-2"
                                                                        style="width: 100%; min-width: 45px; font-size: 0.75rem; padding: 2px 3px; text-align: center;" 
                                                                        placeholder="P{{ $j }}"
                                                                        name="dimensions[{{ $i }}][{{ $j }}][p2]">
                                                                </td>
                                                            @endfor
                                                        </tr>
                                                    @endfor
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>

                                <!-- Berat Part (Hanya untuk AHM) -->
                                <td class="align-middle col-berat-part" style="display: none; min-width: 280px;">
                                    <div class="px-2">
                                        {{-- Baris kontrol --}}
                                        <div class="d-flex align-items-center mb-2" style="gap:4px;">
                                            <button type="button" id="addWeightCavBtn" title="Tambah Cavity"
                                                style="width:22px;height:22px;border-radius:50%;border:none;background:#4e73df;color:#fff;font-size:15px;line-height:22px;padding:0;cursor:pointer;text-align:center;">+</button>
                                            <button type="button" id="removeWeightCavBtn" title="Hapus Cavity"
                                                style="width:22px;height:22px;border-radius:50%;border:1px solid #ccc;background:#fff;color:#aaa;font-size:15px;line-height:20px;padding:0;cursor:pointer;text-align:center;">−</button>
                                        </div>
                                        {{-- Baris per-cavity (disuntikkan oleh JS) --}}
                                        <div id="weightCavContainer"></div>
                                        {{-- Lencana standar --}}
                                        <div class="mt-1">
                                            <span id="weightStandardBadge" class="text-muted"
                                                style="display:none; font-size:0.7rem;">Std: <span
                                                    id="weightStandardDisplay">-</span> gr.</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="align-middle" style="min-width: 280px;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="font-weight-bold text-dark mb-0">Defect List (NG):</label>
                                        <button type="button" id="resetDefectsBtn" class="btn btn-xs btn-outline-danger" title="Reset Semua Defect" style="display: none;">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                    </div>
                                    <div id="defectContainer">
                                        <span class="text-muted small">Pilih Item Part untuk memuat daftar defect</span>
                                    </div>
                                </td>

                                <!-- Total OK / NG -->
                                <td class="align-middle" style="min-width: 130px;">
                                    <div class="d-flex align-items-center mb-1" style="gap:4px;">
                                        <span class="ok-label px-2 py-1 rounded-left font-weight-bold" style="font-size:0.7rem; border-radius:4px 0 0 4px;">OK</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0; font-size:0.78rem;"
                                            name="total_ok" placeholder="0" min="0" required>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:4px;">
                                        <span class="ng-label px-2 py-1 font-weight-bold" style="font-size:0.7rem; border-radius:4px 0 0 4px;">NG</span>
                                        <input type="number"
                                            class="form-control form-control-sm text-center flex-fill"
                                            style="border-radius:0 4px 4px 0; font-size:0.78rem;"
                                            name="total_ng" id="total_ng" placeholder="0" min="0" required>
                                    </div>
                                </td>

                                <!-- Judgment -->
                                <td class="align-middle text-center" style="min-width: 150px;">
                                    <div id="judgmentBadge" class="mb-2 p-3 font-weight-bold h4 rounded d-none shadow-sm"
                                        style="border: 2px solid transparent;">
                                        -
                                    </div>
                                    <select class="form-control font-weight-bold d-none" name="judgment" id="judgmentSelect"
                                        required>
                                        <option value="" disabled selected>-- Result --</option>
                                        <option value="OK" class="text-success">OK</option>
                                        <option value="NG" class="text-danger">NG</option>
                                    </select>
                                    <div id="aql_info" class="small mt-1 font-weight-bold text-center"
                                        style="display:none;">
                                        <span class="text-success">Acc: <span id="acc_val">-</span></span> |
                                        <span class="text-danger">Rej: <span id="rej_val">-</span></span>
                                    </div>
                                </td>

                                <!-- Inisial Operator -->
                                <td class="align-middle">
                                    <input type="text" class="form-control text-center bg-light font-weight-bold" style="min-width: 60px; text-transform: uppercase;"
                                        name="operator_initials" placeholder="Inisial"
                                        value="{{ auth()->user()->initials ?? '' }}" readonly required>
                                </td>

                                <!-- Keterangan -->
                                <td class="align-middle" style="min-width: 320px;">
                                    <div class="form-group mb-2" id="nextProsesContainer" style="display: none;">
                                        <label for="nextProses" class="font-weight-bold text-danger">Next
                                            Proses: <span class="text-danger">*</span></label>
                                        <select class="form-control" id="nextProses" name="next_proses">
                                            <option value="">-- Pilih Next Proses --</option>
                                            @foreach($nextProcesses as $opt)
                                                <option value="{{ $opt->name }}">{{ $opt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <textarea class="form-control" name="remarks" rows="6"
                                        style="min-height: 140px; min-width: 300px; width: 100%; resize: both;"
                                        placeholder="Catatan tambahan..."></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div><!-- .table-responsive -->

                <div class="row mt-4">
                    <div class="col-md-12 text-right d-flex justify-content-end align-items-center">
                        <h5 class="mr-3 mb-0 font-weight-bold text-gray-800" id="timerDisplay">00:00:00</h5>
                        <input type="hidden" name="cycle_time" id="cycleTimeInput" value="0">

                        <button type="button" class="btn btn-success mr-3" id="startTimerBtn">
                            <i class="fas fa-play"></i> Start
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                            <i class="fas fa-save fa-sm"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card Daftar Scan Sementara (Hanya untuk Karawang) -->
    @if($plantCode === 'karawang')
    <div class="card shadow mb-4 d-none" id="tempQueueCard">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                List Data 
            </h6>
            <span class="badge badge-info-premium" id="queueBadge">0 Data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table text-center table-striped table-hover mb-0" id="tempQueueTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>QR Raw</th>
                            <th>No Mesin</th>
                            <th>Qty</th>
                            <th>Judgment</th>
                            <th>Inisial QC</th>
                            <th style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tempQueueBody">
                        <!-- Dinamik via JS -->
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                <div id="saveProgressContainer" class="w-100 w-md-50 mb-3 mb-md-0 d-none">
                    <div class="progress" style="height: 18px; border-radius: 9px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="saveProgressBar" role="progressbar" style="width: 0%; font-size: 0.75rem; font-weight: 700;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <small class="text-muted mt-1 d-block font-weight-bold" id="saveProgressText">Menyimpan data...</small>
                </div>
                <div class="text-right ml-auto">
                    <button type="button" class="btn btn-danger btn-sm mr-2 shadow-sm" id="btnClearQueue">
                        <i class="fas fa-trash-alt mr-1"></i> Kosongkan List
                    </button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnSaveQueue">
                        <i class="fas fa-cloud-upload-alt mr-1"></i> Simpan Semua Data (<span id="queueCountDisplay">0</span> Data)
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bagian Tampilan PDF Berdampingan -->
    <div class="card shadow mb-4" id="pdfDisplaySection">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">STANDARD</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">{{ $plantCode === 'jakarta' ? 'PCCP DAN DIMENSI' : 'PCCP DAN SIMILAR PART' }}</h6>
                        <div class="d-flex align-items-center">
                            <!-- Kontrol Zoom -->
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutStandard"
                                    title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetStandard"
                                    title="Reset Zoom">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInStandard"
                                    title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center standard-nav-controls" style="display:none;">
                                <button type="button" class="btn btn-xs btn-dark mr-1" id="prevStandardPage"
                                    title="Previous Page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="standardPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-dark ml-1" id="nextStandardPage"
                                    title="Next Page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn mr-1" id="fullStandardBtn"
                                style="display:none;">
                                <i class="fas fa-expand"></i> Full
                            </button>
                            <a id="downloadStandardBtn" class="btn btn-sm btn-success" href="#" download title="Download PCCP PDF" style="display:none;">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div id="standardPdfContainer" class="rounded border"
                        style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <!-- Status default diperbarui: pesan ditampilkan saat tidak ada item yang dipilih -->
                        <div id="standardPdfPlaceholder"
                            class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-pdf fa-3x mb-3"></i>
                            <p class="mb-0">Pilih Item untuk menampilkan Standard PDF</p>
                        </div>
                        <canvas id="standardPdfCanvas" class="d-none" style="margin: 0 auto;"></canvas>
                        <div id="standardPdfLoading" class="h-100 d-none align-items-center justify-content-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-dark mb-0">{{ $plantCode === 'jakarta' ? 'SIMILAR PART' : 'DIMENSI' }}</h6>
                        <div class="d-flex align-items-center">
                            <!-- Kontrol Zoom Similar -->
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomOutSimilar"
                                    title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomResetSimilar"
                                    title="Reset Zoom">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="zoomInSimilar"
                                    title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center similar-nav-controls" style="display:none;">
                                <button type="button" class="btn btn-xs btn-secondary mr-1" id="prevSimilarPage"
                                    title="Previous Page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="similarPageInfo" class="small mx-1">P 1/1</span>
                                <button type="button" class="btn btn-xs btn-secondary ml-1" id="nextSimilarPage"
                                    title="Next Page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-info view-pdf-btn mr-1" id="fullSimilarBtn"
                                style="display:none;">
                                <i class="fas fa-expand"></i> Full
                            </button>
                            <a id="downloadSimilarBtn" class="btn btn-sm btn-info" href="#" download title="Download {{ $plantCode === 'jakarta' ? 'Similar Part' : 'Dimensi Part' }} PDF" style="display:none;">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div id="similarPdfContainer" class="rounded border"
                        style="height: 800px; position: relative; background-color: #eee; overflow: auto;">
                        <div id="similarPdfPlaceholder"
                            class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <p class="mb-0">Pilih Item untuk menampilkan {{ $plantCode === 'jakarta' ? 'Similar Part' : 'Dimensi Part' }}</p>
                            <p class="small mt-2" id="similarStatusText"></p>
                        </div>
                        <canvas id="similarPdfCanvas" class="d-none" style="margin: 0 auto;"></canvas>
                        <div id="similarPdfLoading" class="h-100 d-none align-items-center justify-content-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gambar -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">STANDARD (Image)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-2">
                        <button type="button" class="btn btn-primary btn-sm mr-2" id="zoomIn">
                            <i class="fas fa-search-plus"></i> Zoom In
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="zoomReset">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="zoomOut">
                            <i class="fas fa-search-minus"></i> Zoom Out
                        </button>
                    </div>
                    <div class="text-center" style="overflow: auto; max-height: 70vh;">
                        <img id="modalImage" src="" class="img-fluid mb-3" alt="Detail Gambar"
                            style="transition: transform 0.2s ease;">
                    </div>
                    <div class="text-center">
                        <h5 id="modalTitle" class="font-weight-bold"></h5>
                        <p id="modalDescription"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal PDF (Ditambahkan) -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-2 align-items-center flex-wrap">
                        <div class="mr-3 mb-2">
                            <button type="button" class="btn btn-dark btn-sm" id="prevPdf">
                                <i class="fas fa-file-pdf"></i> <i class="fas fa-arrow-left"></i>
                            </button>
                            <span id="pdfInfo" class="mx-2 font-weight-bold">File 1 of ?</span>
                            <button type="button" class="btn btn-dark btn-sm" id="nextPdf">
                                <i class="fas fa-arrow-right"></i> <i class="fas fa-file-pdf"></i>
                            </button>
                        </div>
                        <div class="mr-3 mb-2 border-left pl-3">
                            <button type="button" class="btn btn-secondary btn-sm" id="prevPage">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="pageInfo" class="mx-2">Page 1 of ?</span>
                            <button type="button" class="btn btn-secondary btn-sm" id="nextPage">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="border-left pl-3 mb-2">
                            <button type="button" class="btn btn-primary btn-sm mr-1" id="pdfZoomIn">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mr-1" id="pdfZoomReset">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="pdfZoomOut">
                                <i class="fas fa-search-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-center bg-dark" style="overflow: auto; max-height: 80vh;">
                        <canvas id="the-canvas" style="border: 1px solid black; direction: ltr;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pemindai QR -->
    <div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrScannerModalLabel"><i class="fas fa-qrcode mr-2"></i>QR Code Scanner</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="position-relative">
                        <video id="qr-video" class="w-100" autoplay muted playsinline style="border-radius: 8px;"></video>
                        <button type="button" id="toggleFlashBtn" class="btn btn-sm btn-dark position-absolute d-none" style="top: 10px; left: 10px; opacity: 0.7; z-index: 10;">
                            <i class="fas fa-bolt text-white"></i> Flash
                        </button>
                        <button type="button" id="toggleMirrorBtn" class="btn btn-sm btn-dark position-absolute" style="top: 10px; right: 10px; opacity: 0.7; z-index: 10;">
                            <i class="fas fa-arrows-alt-h text-white"></i> Flip
                        </button>
                    </div>
                    <style>
                        #qr-video.mirrored { transform: scaleX(-1) !important; }
                        #zoomContainer { background: rgba(0,0,0,0.5); border-radius: 0 0 8px 8px; }
                        #zoomSlider { height: 6px; cursor: pointer; }
                    </style>
                    <div id="zoomContainer" class="p-2 d-none">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-search-minus text-white mr-2"></i>
                            <input type="range" id="zoomSlider" class="custom-range flex-grow-1" min="1" max="1" step="0.1" value="1">
                            <i class="fas fa-search-plus text-white ml-2"></i>
                        </div>
                    </div>
                    <div id="qr-reader-results" class="p-3 text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Memuat...</span>
                        </div>
                        <p class="mt-2 text-muted">Memproses data QR...</p>
                    </div>
                    <div class="p-3 border-top bg-light">
                        <label class="font-weight-bold">Atau Unggah Gambar QR:</label>
                        <input type="file" id="qr-input-file" accept="image/*" class="form-control-file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection



@push('scripts')
    <script src="{{ asset('js/vendor/qr-scanner.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/vendor/pdf.min.js') }}"></script>
    <script src="{{ asset('js/vendor/item-search.js') }}"></script>
    <script src="{{ asset('js/checksheet/in-process.js') }}?v={{ time() }}"></script>
    <script>
        $(document).ready(function () {
            window.initInProcessCreate({
                itemSearchUrl: "{{ route('items.search-by-part') }}",
                qrUniqueUrl: "{{ route('items.check-qr-unique') }}",
                pdfUrlPattern: "{{ route('items.pdf', ['id' => 'ID_PLACEHOLDER', 'index' => 'INDEX_PLACEHOLDER']) }}",
                pdfWorkerSrc: "{{ asset('js/vendor/pdf.worker.min.js') }}",
                plantContext: "{{ request('plant') ?? auth()->user()->plant_id }}",
                useQueue: {{ $plantCode === 'karawang' ? 'true' : 'false' }},
                partDimensionStandards: {!! $partDimensionStandards !!}
            });
            // Pasang autocomplete di field Item Part
            window.initItemSearch('itemSelect');
        });
    </script>
@endpush
