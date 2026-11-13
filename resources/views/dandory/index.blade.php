@extends('layouts.admin')

@php
    $plantCode = strtolower($plantCode ?: 'karawang');
    $isJkt     = in_array($plantCode, ['jakarta', 'jkt']);
    $docHeader = \App\Models\GeneralSetting::getDocHeader('checksheet_dandory', $plantCode, [
        'no_dokumen' => $isJkt ? 'QC-JKT-F-0094' : 'QC-KRW-F-0094',
        'tgl_terbit' => '17/10/2025',
        'revisi'     => '-',
        'halaman'    => '1 / 1'
    ]);

    $pdfFileTitle = 'Checksheet Dandory Quality - ' . ($selectedOperator->name ?? 'Semua Operator') . ' - ' . $monthNames[$month] . ' ' . $year;
    if (!empty($shiftFilter)) $pdfFileTitle .= ' - ' . $shiftFilter;
    if (!empty($tableFilter)) $pdfFileTitle .= ' - ' . $tableFilter;
@endphp

@section('title', $pdfFileTitle)

@section('content')
<style>
    /* Styling Filter & Input Seragam Proyek QC */
    .custom-filter-wrapper .ips-wrapper { margin-bottom: 0 !important; }
    .custom-filter-wrapper .ips-input, .filter-select-custom {
        padding: 2px 8px !important;
        font-size: 0.75rem !important;
        border: 1px solid #cbd5e1 !important;
        background: #fff !important;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        height: 31px !important;
        border-radius: 4px !important;
        font-weight: 600 !important;
        color: #212529 !important;
    }
    .custom-filter-wrapper .ips-input { padding-right: 28px !important; }
    .custom-filter-wrapper .ips-input:focus, .filter-select-custom:focus {
        border-color: #4a6cf7 !important;
        box-shadow: 0 0 0 2px rgba(74,108,247,.18) !important;
        outline: none !important;
    }
    .custom-filter-wrapper .ips-clear {
        right: 8px !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        color: #64748b !important;
        line-height: 1 !important;
    }
    .custom-filter-wrapper .ips-clear:hover { color: #ef4444 !important; }

    /* Standar Tabel QC (Seragam dengan in_process/index.blade.php) */
    .table-responsive {
        max-height: none !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        border: none !important;
        box-shadow: none !important;
    }
    .table-dandory {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
        width: 100% !important;
        min-width: 1100px;
        table-layout: auto !important;
        margin-bottom: 0;
        background-color: #ffffff;
    }
    .table-dandory td, .table-dandory th {
        border-left: none;
        border-right: 1px solid #e2e8f0 !important;
    }
    .table-dandory > thead > tr > th:first-child,
    .table-dandory > tbody > tr > td:first-child {
        border-left: 1px solid #cbd5e1 !important;
    }
    .table-dandory > thead > tr:first-child > th:last-child,
    .table-dandory > thead > tr:last-child > th:last-child,
    .table-dandory > tbody > tr > td:last-child {
        border-right: 1px solid #cbd5e1 !important;
    }
    .table-dandory tbody td {
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: none !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.60rem !important;
        padding: 2px 4px !important;
        line-height: 1.1 !important;
        text-align: center;
    }
    .table-dandory > thead > tr:first-child > th {
        border-top: 1px solid #cbd5e1 !important;
    }
    .table-dandory > tbody > tr:last-child > td {
        border-bottom: 1px solid #cbd5e1 !important;
    }

    /* Global TH sticky setup (persis in_process) */
    .table-dandory > thead > tr > th {
        position: -webkit-sticky !important;
        position: sticky !important;
        background-color: #f8fafc !important;
        background-clip: padding-box !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.58rem !important;
        letter-spacing: 0.1px;
        padding: 3px 5px !important;
        border-left: none;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        line-height: 1.1 !important;
        white-space: nowrap !important;
        box-shadow: inset 0 -1px 0 #cbd5e1;
        text-align: center;
    }
    .table-dandory tbody tr:hover {
        background-color: #f1f5f9 !important;
        transition: background-color 0.2s ease;
    }
    .table-dandory .btn {
        min-width: 0 !important;
        padding: 0.1rem 0.35rem !important;
        font-size: 0.58rem !important;
        margin: 0px !important;
    }
    .dandory-action-group {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
    }
    .dandory-action-group > .btn {
        margin: 0 3px !important;
    }
    .table-dandory .badge {
        font-size: 0.58rem !important;
        padding: 0.1rem 0.3rem !important;
    }

    /* Exact sticky heights untuk 2-tier header */
    .table-dandory > thead > tr:nth-child(1) > th {
        top: 0 !important;
        z-index: 105 !important;
        height: 24px !important;
    }
    .table-dandory > thead > tr:nth-child(2) > th {
        top: 24px !important;
        z-index: 104 !important;
        height: 20px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    .table-dandory > thead > tr:nth-child(1) > th[rowspan="2"] {
        top: 0 !important;
        height: 44px !important;
        z-index: 106 !important;
    }

    /* Sel Matriks Interaktif Dandory */
    .dandory-cell {
        cursor: pointer;
        user-select: none;
        font-weight: 900 !important;
        font-size: 0.82rem !important;
        line-height: 1;
        transition: background-color 0.15s ease;
        width: 26px !important;
        max-width: 26px !important;
        padding: 2px 1px !important;
    }
    .dandory-cell:hover {
        background-color: #e2e8f0 !important;
    }
    .dandory-cell.cell-ok {
        background-color: #d1fae5 !important;
        color: #047857 !important;
    }
    .dandory-cell.cell-ng {
        background-color: #fee2e2 !important;
        color: #b91c1c !important;
    }
    .dandory-cell.cell-na {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
    }

    /* Split Container: Selalu Horizontal Side-by-Side (Termasuk di Layar Mobile) */
    .dandory-split-wrapper {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: flex-start !important;
        gap: 8px !important;
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .dandory-std-panel {
        flex: 0 0 280px !important;
        width: 280px !important;
        min-width: 280px !important;
        max-width: 280px !important;
    }
    .dandory-tx-panel {
        flex: 1 1 auto !important;
        min-width: 1100px !important;
    }
    .dandory-tx-panel .table-responsive {
        overflow-x: visible !important;
    }

    /* Panel Parameter Standar Kiri */
    .std-ref-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        font-size: 0.60rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }
    .std-ref-header {
        background-color: #f8fafc !important;
        font-weight: 700;
        font-size: 0.62rem;
        padding: 4px 8px;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        color: #475569 !important;
    }
    .table-std-ref {
        width: 100%;
        margin-bottom: 0;
        font-size: 0.58rem;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border: none !important;
    }
    .table-std-ref thead th,
    .table-std-ref > thead > tr > th,
    table.table-std-ref thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 0.58rem !important;
        text-transform: uppercase !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: none !important;
        border-left: none !important;
        padding: 3px 5px !important;
    }
    .table-std-ref tbody td {
        background-color: #ffffff !important;
        color: #334155 !important;
        border-right: 1px solid #f1f5f9 !important;
        border-bottom: 1px solid #f1f5f9 !important;
        border-top: none !important;
        border-left: none !important;
        padding: 2px 5px !important;
        vertical-align: middle;
    }

    /* Modal Dandory Table Thead Override */
    .modal-dandory table thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-color: #e2e8f0 !important;
    }

    /* Badge Legenda */
    .badge-legend {
        display: inline-block;
        padding: 2px 6px;
        font-size: 0.65rem;
        border-radius: 4px;
        font-weight: 800;
        line-height: 1.2;
    }
    .marker-ok { background-color: #10b981 !important; color: white !important; }
    .marker-ng { background-color: #ef4444 !important; color: white !important; }
    .marker-na { background-color: #64748b !important; color: white !important; }

    /* Modal Standard Styling */
    .modal-dandory .modal-content {
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 0;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .modal-dandory form.modal-form-flex {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        overflow: hidden;
        margin-bottom: 0;
        min-height: 0;
    }
    .modal-dandory form.modal-form-flex .modal-body {
        overflow-y: auto !important;
        flex: 1 1 auto;
        min-height: 0;
    }
    .modal-dandory form.modal-form-flex .modal-footer {
        flex-shrink: 0;
    }

    /* Checkbox Sederhana (Native) */
    .cat-checkbox-item {
        margin-bottom: 5px;
        padding-left: 1.35rem;
    }
    .cat-checkbox-item .form-check-input {
        cursor: pointer;
        margin-top: 2px;
        width: 14px;
        height: 14px;
    }
    .cat-checkbox-item .form-check-label {
        cursor: pointer;
        font-size: 0.72rem;
        color: #334155;
        user-select: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }
    .cat-checkbox-item:hover .form-check-label {
        color: #0f172a;
    }
</style>

<div class="container-fluid">
    <!-- Single Card Container -->
    <div class="card shadow mb-3">
        <div class="card-body p-2">

            <!-- 1. Header Dokumen ISO -->
            <div class="mb-2">
                <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                    <tr>
                        <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                            <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                        </td>
                        <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                            <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                CHECK SHEET DANDORY QUALITY
                            </h1>
                        </td>
                        <td style="width:1px; border:1px solid #dee2e6; padding:0 !important; vertical-align:top; white-space:nowrap;">
                            <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                                <tr style="height:100%;">
                                    <td style="border:none; padding:4px 6px 4px 4px; vertical-align:top; height:100%; white-space:nowrap;">
                                        <table style="border-collapse:collapse; border:1px solid #dee2e6; font-size:0.65rem; background:#fff; height:100%; width:100%;">
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057;">No. Dokumen</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:700; color:#212529;">{{ $docHeader['no_dokumen'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057;">Tgl. Terbit</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529;">{{ $docHeader['tgl_terbit'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057;">Revisi / Tgl</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529;">{{ $docHeader['revisi'] }}</td>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057;">Halaman</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:700; color:#212529;">{{ $docHeader['halaman'] }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="border:none; padding:4px 4px 4px 0; vertical-align:top; height:100%;">
                                        <table style="border-collapse:collapse; border:1px solid #dee2e6; text-align:center; font-size:0.65rem; line-height:1.1; background:#fff; height:100%; table-layout:fixed; width:388px;">
                                            <thead>
                                                <tr>
                                                    <th style="border:1px solid #dee2e6; padding:3px 2px; font-weight:600; color:#495057; background:#fff; width:28px;">Tgl.</th>
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Dibuat</th>
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Diperiksa</th>
                                                    <th style="border:1px solid #dee2e6; padding:3px 6px; font-weight:600; color:#495057; background:#fff; width:120px;">Diketahui</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td rowspan="3" style="border:1px solid #dee2e6; padding:2px; vertical-align:middle; text-align:center; width:28px;">
                                                        <div style="writing-mode:vertical-rl; transform:rotate(180deg); font-size:0.58rem; font-weight:400; margin:0 auto; color:#6c757d;">
                                                            {{ date('d-M-y') }}
                                                        </div>
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        @if(in_array(strtolower($plantCode), ['jakarta', 'jkt']))
                                                            <img src="{{ asset('signatures/suli.png') }}" alt="Masuli" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35);">
                                                        @else
                                                            <img src="{{ asset('signatures/arif.png') }}" alt="Arief H" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35);">
                                                        @endif
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35);">
                                                    </td>
                                                    <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                        <img src="{{ asset('signatures/desti.png') }}" alt="Desti K" style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35);">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529;">{{ in_array(strtolower($plantCode), ['jakarta', 'jkt']) ? 'Masuli' : 'Arief H' }}</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529;">Iwan S</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529;">Desti K</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057;">Spv. QC</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057;">Asst. Mgr Quality</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057;">Mgr. Quality</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 2. Bar Filter Data (Standar Clean White Proyek QC) -->
            <form action="{{ route('checksheet.dandory.index') }}" method="GET" id="filterFormDandory"
                  class="d-flex flex-wrap align-items-end bg-white p-0 rounded mb-2" style="gap: 12px;">

                <input type="hidden" name="plant" value="{{ $plantCode }}">

                <!-- Dropdown Operator / Inspector -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Operator / Inspector</label>
                    <div style="width: 220px;" class="custom-filter-wrapper">
                        @if(in_array(auth()->user()->role, ['operator', 'inspector']))
                            <select name="operator_id" id="filterOperator" class="form-control form-control-sm filter-select-custom bg-light" disabled>
                                <option value="{{ auth()->user()->id }}" selected>{{ auth()->user()->name }}</option>
                            </select>
                            <input type="hidden" name="operator_id" value="{{ auth()->user()->id }}">
                        @else
                            <select name="operator_id" id="filterOperator" class="form-control form-control-sm filter-select-custom" onchange="this.form.submit()">
                                @foreach($inspectors as $insp)
                                    <option value="{{ $insp->id }}" data-name="{{ $insp->name }}" {{ $selectedOperatorId == $insp->id ? 'selected' : '' }}>
                                        {{ $insp->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                <!-- Input Rentang Tanggal (Kalender: Dari - Sampai) -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Tanggal</label>
                    <div class="d-flex align-items-center rounded bg-white overflow-hidden" style="border: 1px solid #cbd5e1; height: 31px;">
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm border-0 shadow-none"
                               style="width: 125px; font-size: 0.72rem; height: 28px;" value="{{ $startDate }}" title="Dari Tanggal">
                        <span class="px-2 text-gray-500 font-weight-bold small">s/d</span>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm border-0 shadow-none"
                               style="width: 125px; font-size: 0.72rem; height: 28px;" value="{{ $endDate }}" title="Sampai Tanggal">
                    </div>
                </div>

                <!-- Dropdown Shift -->
                <div class="d-flex flex-column align-items-start">
                    <label class="mb-1 small font-weight-bold text-gray-700">Shift</label>
                    <div style="width: 120px;">
                        <select name="shift" class="form-control form-control-sm filter-select-custom">
                            <option value="">Semua Shift</option>
                            <option value="Shift 1" {{ $shiftFilter === 'Shift 1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="Shift 2" {{ $shiftFilter === 'Shift 2' ? 'selected' : '' }}>Shift 2</option>
                            <option value="Shift 3" {{ $shiftFilter === 'Shift 3' ? 'selected' : '' }}>Shift 3</option>
                            <option value="Non Shift" {{ $shiftFilter === 'Non Shift' ? 'selected' : '' }}>Non Shift</option>
                        </select>
                    </div>
                </div>

                <!-- Tombol Cari & Reset Filter -->
                <div class="d-flex align-items-center mt-auto" style="gap: 4px;">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center" style="height: 31px; font-size: 0.72rem;" title="Terapkan Filter">
                        <i class="fas fa-search fa-sm mr-1"></i> Filter
                    </button>
                    <a href="{{ route('checksheet.dandory.index', ['plant' => $plantCode, 'operator_id' => $selectedOperatorId]) }}"
                       class="btn btn-secondary btn-sm rounded-pill px-2 shadow-sm d-flex align-items-center no-loader" style="height: 31px; font-size: 0.72rem;" title="Reset Filter Tanggal & Shift">
                        <i class="fas fa-undo fa-sm mr-1"></i> Reset
                    </a>
                </div>

                <!-- Tombol Aksi di Kanan -->
                <div class="ml-auto d-flex flex-wrap align-items-center mt-auto" style="gap: 6px;">
                    <!-- Tombol + Mulai Dandory (Awal) -->
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center"
                            data-toggle="modal" data-target="#modalAwalDandory" id="btnOpenModalAwal" style="height: 31px;">
                        <i class="fas fa-plus mr-1"></i> Mulai Dandory
                    </button>

                    <!-- Tombol Kelola Operator & Meja (Non-Inspector) -->
                    @if(auth()->user()->role !== 'inspector')
                        <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center"
                                data-toggle="modal" data-target="#modalOperatorMeja" style="height: 31px;" title="Kelola Posisi/Bagian Operator, Kategori Part, & Meja">
                            <i class="fas fa-user-cog mr-1"></i> Kelola Operator & Meja
                        </button>
                    @endif

                    <!-- Tombol Kelola Master Item (Khusus Admin) -->
                    @if(auth()->user()->role === 'admin')
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center"
                                data-toggle="modal" data-target="#modalMasterItem" style="height: 31px;" title="Kelola Parameter Standar ISO Dandory">
                            <i class="fas fa-cogs mr-1"></i> Master Item
                        </button>
                    @endif

                    <!-- Tombol Cetak / Save PDF (Direct Print tanpa buka tab baru) -->
                    <a href="{{ route('checksheet.dandory.print', ['plant' => $plantCode, 'operator_id' => $selectedOperatorId, 'start_date' => $startDate, 'end_date' => $endDate, 'date' => $date, 'shift' => $shiftFilter]) }}"
                       class="btn btn-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center btn-print-direct no-loader" id="btnCetakDandory" style="height: 31px;" title="Cetak Dokumen Dandory Quality">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </a>
                </div>
            </form>

            <!-- 3. Sub Header Info Bar Operator & Legenda Simbol -->
            <div class="row bg-white p-2 rounded border mx-0 mb-3 shadow-xs align-items-center" style="font-size: 0.78rem;">
                <div class="col-md-3">
                    <span class="text-muted">Nama Operator:</span>
                    <strong class="text-dark ml-1">{{ $selectedOperator->name }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted">Bagian:</span>
                    <strong class="text-dark ml-1">{{ $opConfig->bagian ?: 'Quality Control' }}</strong>
                </div>
                <div class="col-md-6 text-md-right">
                    <span class="text-muted font-weight-bold mr-1">Keterangan:</span>
                    <span class="text-dark font-weight-bold">
                        <span class="text-secondary">-</span> Item tidak ada &nbsp;&nbsp;|&nbsp;&nbsp;
                        <span class="text-success">✓</span> Dilakukan &nbsp;&nbsp;|&nbsp;&nbsp;
                        <span class="text-danger">✕</span> Tidak dilakukan
                    </span>
                </div>
            </div>

            @php
                $canVerify = in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc']);
            @endphp

            <!-- 4. Split Container: Panel Parameter Standar + Matriks Transaksi (Tetap Side-by-Side di Mobile) -->
            <div class="dandory-split-wrapper mb-2">
                <!-- PANEL KIRI: PARAMETER STANDAR -->
                <div id="panelStdRef" class="dandory-std-panel">
                    <div class="std-ref-card shadow-xs mb-2">
                        <div class="std-ref-header d-flex justify-content-between align-items-center">
                            Awal Dandory
                            <span class="badge badge-light border">9 Item</span>
                        </div>
                        <table class="table-std-ref">
                            <thead>
                                <tr>
                                    <th style="width: 25px;" class="text-center">No</th>
                                    <th style="width: 90px;">Item Check</th>
                                    <th>Standard</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($awalItems as $it)
                                    <tr class="std-row-hover" data-phase="awal" data-order="{{ $it->order_no }}">
                                        <td class="text-center font-weight-bold text-dark">{{ $it->order_no }}</td>
                                        <td class="font-weight-semibold text-dark">{{ $it->item_check }}</td>
                                        <td class="text-muted">{{ $it->standard }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="std-ref-card shadow-xs">
                        <div class="std-ref-header d-flex justify-content-between align-items-center">
                            Akhir Dandory
                            <span class="badge badge-light border">10 Item</span>
                        </div>
                        <table class="table-std-ref">
                            <thead>
                                <tr>
                                    <th style="width: 25px;" class="text-center">No</th>
                                    <th style="width: 90px;">Item Check</th>
                                    <th>Standard</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($akhirItems as $it)
                                    <tr class="std-row-hover" data-phase="akhir" data-order="{{ $it->order_no }}">
                                        <td class="text-center font-weight-bold text-dark">{{ $it->order_no }}</td>
                                        <td class="font-weight-semibold text-dark">{{ $it->item_check }}</td>
                                        <td class="text-muted">{{ $it->standard }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PANEL KANAN: TABEL MATRIKS DATA TRANSAKSI -->
                <div id="panelTxData" class="dandory-tx-panel">
                    <div class="table-responsive">
                        <table class="table table-hover table-dandory" id="checksheetDandoryTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    @if($canVerify)
                                        <th rowspan="2" class="align-middle text-center" style="width: 46px;">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <span style="font-size: 9px; margin-bottom: 2px; white-space: nowrap;">Semua (<span id="checkedCountDisplay">0</span>)</span>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="checkAllRows">
                                                    <label class="custom-control-label" for="checkAllRows" style="cursor:pointer;"></label>
                                                </div>
                                            </div>
                                        </th>
                                    @endif
                                    <th rowspan="2" style="width: 32px;">NO</th>
                                    <th rowspan="2" style="width: 85px;">TANGGAL/SHIFT</th>
                                    <th rowspan="2" style="width: auto; white-space: nowrap;">{{ $locationLabelUpper ?? 'MEJA/RUANGAN' }}</th>
                                    <th rowspan="2" style="min-width: 175px;">NAME PART/PART NO/SAP/KET</th>
                                    <th colspan="{{ (count($awalItems) ?: 1) + 1 }}" style="border-bottom: 1px solid #e2e8f0 !important;">AWAL DANDORY</th>
                                    <th colspan="{{ (count($akhirItems) ?: 1) + 1 }}" style="border-bottom: 1px solid #e2e8f0 !important;">AKHIR DANDORY</th>
                                    <th rowspan="2" style="width: 115px;">DIPERIKSA<br>(LEADER)</th>
                                    <th rowspan="2" style="width: 130px;">AKSI</th>
                                </tr>
                                <tr>
                                    {{-- Kolom Jam Awal --}}
                                    <th class="font-weight-bold text-center text-primary" style="width: 48px; min-width: 44px; font-size: 0.60rem; letter-spacing: 0.2px;" title="Waktu Pelaksanaan Awal Dandory">
                                        JAM
                                    </th>

                                    {{-- Kolom Angka Awal --}}
                                    @foreach($awalItems as $it)
                                        @php
                                            $hdrTitle = "Awal #{$it->order_no}: {$it->item_check} - {$it->standard}";
                                        @endphp
                                        <th class="th-num-hover font-weight-bold" data-phase="awal" data-order="{{ $it->order_no }}" title="{{ $hdrTitle }}" style="cursor:help;">
                                            {{ $it->order_no }}
                                        </th>
                                    @endforeach

                                    {{-- Kolom Jam Akhir --}}
                                    <th class="font-weight-bold text-center text-success" style="width: 48px; min-width: 44px; font-size: 0.60rem; letter-spacing: 0.2px;" title="Waktu Pelaksanaan Akhir Dandory">
                                        JAM
                                    </th>

                                    {{-- Kolom Angka Akhir --}}
                                    @foreach($akhirItems as $it)
                                        @php
                                            $hdrTitle = "Akhir #{$it->order_no}: {$it->item_check} - {$it->standard}";
                                        @endphp
                                        <th class="th-num-hover font-weight-bold" data-phase="akhir" data-order="{{ $it->order_no }}" title="{{ $hdrTitle }}" style="cursor:help;">
                                            {{ $it->order_no }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($checksheets as $index => $cs)
                                    @php
                                        $awalMap  = (array) ($cs->awal_checks ?? []);
                                        $akhirMap = (array) ($cs->akhir_checks ?? []);
                                        $isComplete = $cs->is_complete ?? false;
                                    @endphp
                                    <tr id="row-dandory-{{ $cs->id }}" data-id="{{ $cs->id }}" data-part-name="{{ $cs->part_name }}" data-part-number="{{ $cs->item?->part_number ?? '' }}" data-sap-code="{{ $cs->item?->sap_code ?? '' }}">
                                        @if($canVerify)
                                            <td class="align-middle text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input row-checkbox" id="checkRow{{ $cs->id }}" value="{{ $cs->id }}" data-approved="{{ $cs->leader_checked ? '1' : '0' }}" data-complete="{{ $isComplete ? '1' : '0' }}">
                                                    <label class="custom-control-label" for="checkRow{{ $cs->id }}" style="cursor:pointer;"></label>
                                                </div>
                                            </td>
                                        @endif
                                        <td>{{ $checksheets->firstItem() ? ($checksheets->firstItem() + $index) : ($index + 1) }}</td>
                                        <td class="font-weight-bold text-dark">
                                            <div>{{ \Carbon\Carbon::parse($cs->check_date)->format('d/m/y') }}</div>
                                            <div class="text-muted font-weight-normal" style="font-size: 0.62rem; line-height: 1.15; margin-top: 1px;">
                                                {{ $cs->shift }}
                                            </div>
                                        </td>
                                        <td class="text-nowrap px-2" style="width: auto; white-space: nowrap;">
                                            {{ $cs->display_table_no ?? $cs->formatted_table_no ?? $cs->table_no }}
                                        </td>
                                        <td class="text-left px-2" style="min-width: 175px; max-width: 260px;" title="{{ $cs->part_name }} {{ $cs->item?->part_number ? ' | Part Number: ' . $cs->item->part_number : '' }} {{ $cs->item?->sap_code ? ' | SAP: ' . $cs->item->sap_code : '' }}">
                                            <div class="font-weight-bold text-dark text-truncate">{{ $cs->part_name }}</div>
                                            @if($cs->item && ($cs->item->part_number || $cs->item->sap_code))
                                                <div class="text-muted font-weight-normal text-truncate" style="font-size: 0.62rem; line-height: 1.15; margin-top: 1px;">
                                                    @if($cs->item->part_number)
                                                        <span>Part Number: <span class="text-secondary font-weight-bold">{{ $cs->item->part_number }}</span></span>
                                                    @endif
                                                    @if($cs->item->part_number && $cs->item->sap_code)
                                                        <span class="mx-1 text-black-50">|</span>
                                                    @endif
                                                    @if($cs->item->sap_code)
                                                        <span>SAP: <span class="text-secondary font-weight-bold">{{ $cs->item->sap_code }}</span></span>
                                                    @endif
                                                </div>
                                            @endif
                                            @php
                                                $ketAwal = $cs->notes_awal ?: ($cs->notes_akhir ? null : $cs->notes);
                                                $ketAkhir = $cs->notes_akhir;
                                            @endphp
                                            <div id="notesContainer_{{ $cs->id }}">
                                                @if($ketAwal && $ketAkhir)
                                                    <div class="text-muted font-weight-normal text-truncate" style="font-size: 0.62rem; line-height: 1.15; margin-top: 1px;" title="Keterangan Awal Dandory: {{ $ketAwal }}">
                                                        <span>Keterangan Awal Dandory: <span class="text-secondary font-weight-bold">{{ $ketAwal }}</span></span>
                                                    </div>
                                                    <div class="text-muted font-weight-normal text-truncate" style="font-size: 0.62rem; line-height: 1.15; margin-top: 1px;" title="Keterangan Akhir Dandory: {{ $ketAkhir }}">
                                                        <span>Keterangan Akhir Dandory: <span class="text-secondary font-weight-bold">{{ $ketAkhir }}</span></span>
                                                    </div>
                                                @elseif($ketAwal)
                                                    <div class="text-muted font-weight-normal text-truncate" style="font-size: 0.62rem; line-height: 1.15; margin-top: 1px;" title="Keterangan Awal Dandory: {{ $ketAwal }}">
                                                        <span>Keterangan Awal Dandory: <span class="text-secondary font-weight-bold">{{ $ketAwal }}</span></span>
                                                    </div>
                                                @elseif($ketAkhir)
                                                    <div class="text-muted font-weight-normal text-truncate" style="font-size: 0.62rem; line-height: 1.15; margin-top: 1px;" title="Keterangan Akhir Dandory: {{ $ketAkhir }}">
                                                        <span>Keterangan Akhir Dandory: <span class="text-secondary font-weight-bold">{{ $ketAkhir }}</span></span>
                                                    </div>
                                                @elseif($cs->notes)
                                                    <div class="text-muted font-weight-normal text-truncate" style="font-size: 0.62rem; line-height: 1.15; margin-top: 1px;" title="Keterangan Awal Dandory: {{ $cs->notes }}">
                                                        <span>Keterangan Awal Dandory: <span class="text-secondary font-weight-bold">{{ $cs->notes }}</span></span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Jam Awal Dandory --}}
                                        <td class="align-middle text-center font-weight-bold px-1" id="jamAwal_{{ $cs->id }}" style="font-size: 0.65rem; color: #1e293b; background-color: rgba(248, 250, 252, 0.85);" title="Waktu Awal: {{ $cs->awal_completed_at ? \Carbon\Carbon::parse($cs->awal_completed_at)->format('d/m/Y H:i') : '-' }}">
                                            <span>{{ $cs->awal_completed_at ? \Carbon\Carbon::parse($cs->awal_completed_at)->format('H:i') : ($cs->created_at ? \Carbon\Carbon::parse($cs->created_at)->format('H:i') : '-') }}</span>
                                        </td>

                                        {{-- Sel Awal Dandory --}}
                                        @foreach($awalItems as $it)
                                            @php
                                                $val = $awalMap[(string)$it->order_no] ?? null;
                                                $cellClass = '';
                                                $char = '';
                                                if ($val === 'OK') { $cellClass = 'cell-ok'; $char = '✓'; }
                                                elseif ($val === 'NG') { $cellClass = 'cell-ng'; $char = '✕'; }
                                                elseif ($val === 'NA') { $cellClass = 'cell-na'; $char = '-'; }
                                            @endphp
                                            <td class="dandory-cell {{ $cellClass }}"
                                                data-id="{{ $cs->id }}"
                                                data-phase="awal"
                                                data-order="{{ $it->order_no }}"
                                                data-status="{{ $val }}"
                                                title="Klik ubah: - (NA) -> ✓ (OK) -> ✕ (NG) -> Kosong">
                                                {{ $char }}
                                            </td>
                                        @endforeach

                                        {{-- Jam Akhir Dandory --}}
                                        <td class="align-middle text-center font-weight-bold px-1" id="jamAkhir_{{ $cs->id }}" style="font-size: 0.65rem; color: #1e293b; background-color: rgba(248, 250, 252, 0.85);">
                                            @if($cs->is_akhir_done && $cs->akhir_completed_at)
                                                <span title="Waktu Akhir: {{ \Carbon\Carbon::parse($cs->akhir_completed_at)->format('d/m/Y H:i') }}">
                                                    {{ \Carbon\Carbon::parse($cs->akhir_completed_at)->format('H:i') }}
                                                </span>
                                            @else
                                                <span class="text-muted font-weight-normal" title="Akhir Dandory belum diselesaikan">-</span>
                                            @endif
                                        </td>

                                        {{-- Sel Akhir Dandory --}}
                                        @foreach($akhirItems as $it)
                                            @php
                                                $val = $akhirMap[(string)$it->order_no] ?? null;
                                                $cellClass = '';
                                                $char = '';
                                                if ($val === 'OK') { $cellClass = 'cell-ok'; $char = '✓'; }
                                                elseif ($val === 'NG') { $cellClass = 'cell-ng'; $char = '✕'; }
                                                elseif ($val === 'NA') { $cellClass = 'cell-na'; $char = '-'; }
                                            @endphp
                                            <td class="dandory-cell {{ $cellClass }}"
                                                data-id="{{ $cs->id }}"
                                                data-phase="akhir"
                                                data-order="{{ $it->order_no }}"
                                                data-status="{{ $val }}"
                                                title="Klik ubah: - (NA) -> ✓ (OK) -> ✕ (NG) -> Kosong">
                                                {{ $char }}
                                            </td>
                                        @endforeach

                                        {{-- Kolom Diperiksa Leader (Status Approval Murni) --}}
                                        <td class="leader-cell align-middle text-center text-nowrap p-1" style="min-width: 115px;" id="approvalCell_{{ $cs->id }}">
                                            @if($cs->leader_checked)
                                                <span class="badge badge-success px-2 py-1 shadow-sm"><i class="fas fa-check-double mr-1"></i> APPROVED</span>
                                                <br><small class="text-muted">oleh {{ $cs->leader_name }}</small>
                                                @if($cs->leader_checked_at)
                                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($cs->leader_checked_at)->format('d/m/Y H:i') }}</small>
                                                @endif
                                            @elseif($cs->is_rejected)
                                                <span class="badge badge-danger px-2 py-1 shadow-sm"><i class="fas fa-times-circle mr-1"></i> REJECTED</span>
                                                <br><small class="text-muted">oleh {{ $cs->rejector_name }}</small>
                                                @if($cs->rejected_at)
                                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($cs->rejected_at)->format('d/m/Y H:i') }}</small>
                                                @endif
                                                @if($cs->rejection_remarks)
                                                    <br><small class="text-danger font-italic d-inline-block text-truncate" style="max-width: 110px;" title="Alasan: {{ $cs->rejection_remarks }}">
                                                        * {{ $cs->rejection_remarks }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> PENDING</span>
                                            @endif
                                        </td>

                                        {{-- Kolom Aksi --}}
                                        <td class="p-1 text-nowrap align-middle text-center" id="actionCell_{{ $cs->id }}">
                                            <div class="dandory-action-group">
                                                {{-- Tombol Input Akhir Dandory: Khusus Inspector/Operator saja --}}
                                                @if(in_array(auth()->user()->role, ['inspector', 'operator']))
                                                    @if(!$cs->is_akhir_done)
                                                        <button type="button" class="btn btn-outline-success btn-xs rounded-pill px-2 shadow-xs btn-open-akhir"
                                                                data-id="{{ $cs->id }}"
                                                                data-part="{{ $cs->part_name }}"
                                                                data-part-number="{{ $cs->item?->part_number ?? '' }}"
                                                                data-sap-code="{{ $cs->item?->sap_code ?? '' }}"
                                                                data-item-id="{{ $cs->item_id }}"
                                                                data-notes-awal="{{ $cs->notes_awal ?: $cs->notes }}"
                                                                data-notes-akhir="{{ $cs->notes_akhir }}"
                                                                style="font-size:0.6rem; padding: 2px 6px; font-weight: 700; color: #16a34a; border-color: #16a34a;" title="Selesaikan Akhir Dandory">
                                                            <i class="fas fa-check-circle mr-1"></i> Akhir Dandory
                                                        </button>
                                                    @endif
                                                @endif

                                                {{-- Tombol Approval / Batal / Reject untuk Leader ke Atas --}}
                                                @if($canVerify)
                                                    @if($cs->leader_checked)
                                                        <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill px-2 shadow-xs btn-unapprove-row"
                                                                data-id="{{ $cs->id }}" style="font-size:0.6rem; padding: 2px 6px;" title="Batalkan Approval">
                                                            <i class="fas fa-undo mr-1"></i> Batal
                                                        </button>
                                                    @else
                                                        {{-- Tombol Verifikasi Leader (Approve & Reject): Hanya muncul jika Dandory Awal & Akhir sudah lengkap --}}
                                                        @if($isComplete)
                                                            <button type="button" class="btn btn-success btn-xs rounded-pill px-2 shadow-xs btn-approve-row"
                                                                    data-id="{{ $cs->id }}" style="font-size:0.6rem; padding: 2px 6px;" title="Approve Sesi Dandory">
                                                                <i class="fas fa-check mr-1"></i> Approve
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-xs rounded-pill px-2 shadow-xs btn-reject-row"
                                                                    data-id="{{ $cs->id }}" data-part="{{ $cs->part_name }}" style="font-size:0.6rem; padding: 2px 6px;" title="Reject Sesi Dandory">
                                                                <i class="fas fa-times mr-1"></i> Reject
                                                            </button>
                                                        @endif
                                                    @endif
                                                @endif

                                                {{-- Tombol Hapus: Khusus Data Belum Diverifikasi untuk Inspector, atau Bebas untuk Admin --}}
                                                @if((!$cs->leader_checked && in_array(auth()->user()->role, ['inspector', 'operator'])) || auth()->user()->role === 'admin')
                                                    <button type="button" class="btn btn-outline-danger btn-xs rounded-circle shadow-xs btn-delete-dandory"
                                                            data-id="{{ $cs->id }}" data-part="{{ $cs->part_name }}"
                                                            style="width:24px; height:24px; padding:0;" title="Hapus Sesi Dandory">
                                                        <i class="fas fa-trash-alt fa-xs"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($awalItems) + count($akhirItems) + ($canVerify ? 9 : 8) }}" class="py-4 text-center text-muted font-italic">
                                            <i class="fas fa-info-circle mr-1 text-primary"></i> Belum ada data checksheet Dandory Quality untuk operator <strong>{{ $selectedOperator->name }}</strong>
                                            @if(!empty($startDate) && !empty($endDate))
                                                pada periode <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                                            @elseif(!empty($startDate))
                                                mulai tanggal <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong>
                                            @elseif(!empty($endDate))
                                                sampai tanggal <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                                            @elseif(!empty($date))
                                                pada tanggal <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong>
                                            @endif
                                            {{ $shiftFilter ? ' (' . $shiftFilter . ')' : '' }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Paginasi Dandory (20 data per halaman) --}}
            @if(method_exists($checksheets, 'links'))
                <div class="mt-3 px-1">
                    {{ $checksheets->withQueryString()->links() }}
                </div>
            @endif

            @if($canVerify)
                <!-- Floating Bar Bulk Action (Standar Checksheet In-Process) -->
                <div id="bulkActionMenu" class="position-fixed shadow-lg rounded" style="bottom: 30px; left: 50%; transform: translateX(-50%); display: none; z-index: 1050; background: white; padding: 10px 20px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <span class="font-weight-bold text-gray-800"><span id="bulkSelectedCount">0</span> Data Terpilih</span>
                        <button type="button" class="btn btn-success btn-sm shadow-sm" id="btnBulkApproveFloating">
                            <i class="fas fa-check-double mr-1"></i> Verifikasi Terpilih
                        </button>
                        @if(auth()->user()->role === 'admin')
                            <button type="button" class="btn btn-danger btn-sm shadow-sm" id="btnBulkDeleteFloating">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih
                            </button>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL 1: MULAI DANDORY (AWAL DANDORY) --}}
<div class="modal fade modal-dandory" id="modalAwalDandory" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 px-3 bg-light border-bottom">
                <h6 class="modal-title font-weight-bold text-primary mb-0">
                    <i class="fas fa-plus-circle mr-1"></i> Mulai Sesi Dandory Baru (Awal Dandory)
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAwalDandory" class="modal-form-flex" novalidate>
                <div class="modal-body p-3" style="font-size: 0.8rem;">
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-gray-700 mb-1">Tanggal</label>
                            <input type="date" name="check_date" id="awalDate" class="form-control form-control-sm border" value="{{ $date ?: date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-gray-700 mb-1">Shift</label>
                            @php
                                $modalShift = $shiftFilter ?: $defaultShift;
                            @endphp
                            <select name="shift" id="awalShift" class="form-control form-control-sm border" required>
                                <option value="Shift 1" {{ $modalShift === 'Shift 1' ? 'selected' : '' }}>Shift 1</option>
                                <option value="Shift 2" {{ $modalShift === 'Shift 2' ? 'selected' : '' }}>Shift 2</option>
                                <option value="Shift 3" {{ $modalShift === 'Shift 3' ? 'selected' : '' }}>Shift 3</option>
                                <option value="Non Shift" {{ $modalShift === 'Non Shift' ? 'selected' : '' }}>Non Shift</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-gray-700 mb-1">{{ $locationLabel ?? 'Meja/Ruangan' }} <span class="text-danger">*</span></label>
                            <select name="table_no" id="awalTableNo" class="form-control form-control-sm border" required>
                                <option value="">-- Pilih {{ $locationLabel ?? 'Meja/Ruangan' }} --</option>
                                @foreach(($locationOptions ?? $plantTables) as $tbl)
                                    <option value="{{ $tbl }}">{{ $tbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-gray-700 mb-1">Operator</label>
                            <input type="hidden" name="operator_id" value="{{ $selectedOperatorId }}">
                            <input type="text" class="form-control form-control-sm border bg-light text-dark font-weight-bold" value="{{ $selectedOperator->name }}" readonly>
                        </div>
                    </div>

                    <!-- Pilihan Part (Dropdown Searchable & Input QR) -->
                    <div class="form-group mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="small font-weight-bold text-gray-700 mb-0">Pilih Part <span class="text-danger">*</span></label>
                            @if(!empty($opConfig->bagian) && !empty($opConfig->allowed_categories) && count((array)$opConfig->allowed_categories) > 0)
                                <small class="text-muted"><i class="fas fa-filter mr-1"></i> Disaring sesuai posisi: <strong>{{ $opConfig->bagian }}</strong></small>
                            @else
                                <small class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i> Posisi / kategori belum diatur</small>
                            @endif
                        </div>
                        <div class="custom-filter-wrapper">
                            <select name="item_id" id="awalItemId" class="form-control form-control-sm filter-select-custom">
                                <option value="">-- Ketik / Pilih Nama Part --</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}" data-name="{{ $item->name }}" data-part-no="{{ $item->part_number }}" data-sap-code="{{ $item->sap_code }}">
                                        {{ $item->name }} {{ $item->part_number ? "({$item->part_number})" : '' }} {{ $item->sap_code ? "[SAP: {$item->sap_code}]" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if($availableItems->isEmpty())
                            <div class="alert alert-warning py-1 px-2 mt-2 mb-0 d-flex align-items-center" style="font-size: 0.7rem;">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span>Daftar part kosong karena posisi/kategori belum dipilih. Silakan atur di menu <strong>Kelola Posisi Operator & Daftar Meja</strong>.</span>
                            </div>
                        @endif
                    </div>

                    <!-- Checklist 9 Parameter Awal Dandory -->
                    <div class="d-flex justify-content-between align-items-center mb-2 pt-2 border-top">
                        <span class="font-weight-bold text-primary">
                            <i class="fas fa-tasks mr-1"></i> Parameter Pemeriksaan Awal Dandory (9 Item)
                        </span>
                        <div style="gap: 4px;" class="d-flex align-items-center">
                            <button type="button" class="btn btn-xs btn-success rounded-pill px-2" id="btnSetAllAwalOk">
                                <i class="fas fa-check mr-1"></i> Set Semua ✓ (Dilakukan)
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive border rounded mb-2">
                        <table class="table table-sm table-bordered mb-0" style="font-size:0.75rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 30px;" class="text-center">No</th>
                                    <th style="width: 140px;">Item Check</th>
                                    <th>Standard</th>
                                    <th style="width: 180px;" class="text-center">Penilaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($awalItems as $it)
                                    <tr data-order="{{ $it->order_no }}" data-item-check="{{ $it->item_check }}" data-standard="{{ $it->standard }}">
                                        <td class="text-center font-weight-bold align-middle">{{ $it->order_no }}</td>
                                        <td class="font-weight-bold text-dark align-middle">
                                            {{ $it->item_check }}
                                            <div class="ng-reason-container mt-1" style="display:none;"></div>
                                        </td>
                                        <td class="align-middle text-muted">{{ $it->standard }}</td>
                                        <td class="text-center align-middle py-1">
                                            <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
                                                <label class="btn btn-outline-secondary btn-sm px-2 py-1" title="Item tidak ada di proses">
                                                    <input type="radio" name="awal_checks[{{ $it->order_no }}]" value="NA"> - NA
                                                </label>
                                                <label class="btn btn-outline-success btn-sm px-2 py-1 active" title="Dilakukan">
                                                    <input type="radio" name="awal_checks[{{ $it->order_no }}]" value="OK" checked> ✓ Dilakukan
                                                </label>
                                                <label class="btn btn-outline-danger btn-sm px-2 py-1 btn-label-ng" title="Tidak dilakukan">
                                                    <input type="radio" name="awal_checks[{{ $it->order_no }}]" value="NG"> ✕ Tidak Dilakukan
                                                </label>
                                            </div>
                                            <input type="hidden" name="item_ng_reasons[{{ $it->order_no }}]" class="item-ng-reason" value="">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="notes" id="awalNotes" class="form-control form-control-sm border" placeholder="Tuliskan catatan kondisi khusus jika ada">
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top">
                    <button type="button" class="btn btn-sm btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary shadow-sm" id="btnSubmitAwal">
                        <i class="fas fa-save mr-1"></i> Simpan Awal Dandory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: SELESAIKAN DANDORY (AKHIR DANDORY) --}}
<div class="modal fade modal-dandory" id="modalAkhirDandory" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 px-3 bg-light border-bottom">
                <h6 class="modal-title font-weight-bold text-success mb-0">
                    <i class="fas fa-check-circle mr-1"></i> Selesaikan Sesi Dandory (Akhir Dandory)
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAkhirDandory" class="modal-form-flex">
                <input type="hidden" name="dandory_id" id="akhirDandoryId">
                <div class="modal-body p-3" style="font-size: 0.8rem;">
                    <!-- Info Part -->
                    <div class="alert alert-info py-2 px-3 mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Part yang sedang diperiksa:</span>
                            <strong class="d-block text-dark font-weight-bold" id="akhirPartName">-</strong>
                            <div class="text-secondary font-weight-normal" id="akhirPartMeta" style="font-size: 0.75rem; line-height: 1.2; margin-top: 2px; display: none;">
                                <span id="akhirPartNumberWrap">Part Number: <strong class="text-secondary" id="akhirPartNumber">-</strong></span>
                                <span id="akhirPartSapDivider" class="mx-1 text-black-50">|</span>
                                <span id="akhirPartSapWrap">SAP: <strong class="text-secondary" id="akhirPartSap">-</strong></span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-xs btn-success rounded-pill px-2" id="btnSetAllAkhirOk">
                            <i class="fas fa-check mr-1"></i> Set Semua ✓ (Dilakukan)
                        </button>
                    </div>

                    <!-- Checklist 10 Parameter Akhir Dandory -->
                    <div class="table-responsive border rounded mb-2">
                        <table class="table table-sm table-bordered mb-0" style="font-size:0.75rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 30px;" class="text-center">No</th>
                                    <th style="width: 140px;">Item Check</th>
                                    <th>Standard</th>
                                    <th style="width: 180px;" class="text-center">Penilaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($akhirItems as $it)
                                    <tr data-order="{{ $it->order_no }}" data-item-check="{{ $it->item_check }}" data-standard="{{ $it->standard }}">
                                        <td class="text-center font-weight-bold align-middle">{{ $it->order_no }}</td>
                                        <td class="font-weight-bold text-dark align-middle">
                                            {{ $it->item_check }}
                                            <div class="ng-reason-container mt-1" style="display:none;"></div>
                                        </td>
                                        <td class="align-middle text-muted">{{ $it->standard }}</td>
                                        <td class="text-center align-middle py-1">
                                            <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
                                                <label class="btn btn-outline-secondary btn-sm px-2 py-1" title="Item tidak ada di proses">
                                                    <input type="radio" name="akhir_checks[{{ $it->order_no }}]" value="NA"> - NA
                                                </label>
                                                <label class="btn btn-outline-success btn-sm px-2 py-1 active" title="Dilakukan">
                                                    <input type="radio" name="akhir_checks[{{ $it->order_no }}]" value="OK" checked> ✓ Dilakukan
                                                </label>
                                                <label class="btn btn-outline-danger btn-sm px-2 py-1 btn-label-ng" title="Tidak dilakukan">
                                                    <input type="radio" name="akhir_checks[{{ $it->order_no }}]" value="NG"> ✕ Tidak Dilakukan
                                                </label>
                                            </div>
                                            <input type="hidden" name="item_ng_reasons[{{ $it->order_no }}]" class="item-ng-reason" value="">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div id="akhirNotesAwalInfo" class="alert alert-light border py-1 px-2 mb-2" style="display:none; font-size: 0.72rem;">
                        <i class="fas fa-info-circle text-primary mr-1"></i>
                        <span class="text-muted">Keterangan Awal Dandory:</span>
                        <strong class="text-dark ml-1" id="akhirNotesAwalText">-</strong>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-gray-700 mb-1">Catatan / Keterangan Akhir Dandory (Opsional)</label>
                        <input type="text" name="notes" id="akhirNotes" class="form-control form-control-sm border" placeholder="Contoh: Sisa 5 pcs ditaruh di balance">
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top">
                    <button type="button" class="btn btn-sm btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success shadow-sm" id="btnSubmitAkhir">
                        <i class="fas fa-check mr-1"></i> Simpan Akhir Dandory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 3: KELOLA OPERATOR & MEJA (NON-INSPECTOR ONLY) --}}
@if(auth()->user()->role !== 'inspector')
<div class="modal fade modal-dandory" id="modalOperatorMeja" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 px-3 bg-light border-bottom">
                <h6 class="modal-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-user-cog mr-1 text-primary"></i> Kelola Posisi Operator & Daftar Meja
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" style="font-size: 0.8rem;">
                <ul class="nav nav-tabs mb-3" id="tabOpMeja" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="tab-operator-tab" data-toggle="tab" href="#tab-operator" role="tab">
                            <i class="fas fa-user mr-1"></i> Posisi Operator & Kategori Part
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-meja-tab" data-toggle="tab" href="#tab-meja" role="tab">
                            <i class="fas fa-table mr-1"></i> Daftar Meja Plant {{ ucfirst($plantCode) }}
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="tabOpMejaContent">
                    <!-- Tab 1: Posisi Operator & Kategori Part -->
                    <div class="tab-pane fade show active" id="tab-operator" role="tabpanel">
                        <form id="formOperatorConfig">
                            <input type="hidden" name="plant_code" value="{{ $plantCode }}">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-gray-700">Pilih Operator</label>
                                <select name="operator_id" id="cfgOperatorId" class="form-control form-control-sm border">
                                    @foreach($inspectors as $insp)
                                        <option value="{{ $insp->id }}" {{ $selectedOperatorId == $insp->id ? 'selected' : '' }}>
                                            {{ $insp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-gray-700">Posisi / Bagian</label>
                                <select name="bagian" id="cfgBagian" class="form-control form-control-sm border">
                                    @php
                                        $bagianOptions = [
                                            'Inspector Outgoing Plating',
                                            'Inspector Outgoing Painting',
                                            'Inspector Outgoing Export',
                                            'Inspector Outgoing Sub Assy',
                                            'Inspector Inproses',
                                            'Inspector Incoming Sub-Part/Material/Chemical',
                                            'Inspector Incoming Plating',
                                            'Inspector Outgoing Double Tape',
                                            'Performance Test',
                                            'Claim Customer',
                                            'Final ISD',
                                            'Digitalisasi Support',
                                        ];
                                    @endphp
                                    <option value="">-- Pilih Bagian --</option>
                                    @foreach($bagianOptions as $bOpt)
                                        <option value="{{ $bOpt }}" {{ ($opConfig->bagian ?? '') === $bOpt ? 'selected' : '' }}>
                                            {{ $bOpt }}
                                        </option>
                                    @endforeach
                                    @if(!empty($opConfig->bagian) && !in_array($opConfig->bagian, $bagianOptions))
                                        <option value="{{ $opConfig->bagian }}" selected>{{ $opConfig->bagian }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-gray-700 d-block mb-2">
                                    Kategori Part yang Diizinkan untuk Posisi Ini
                                    <small class="text-muted font-weight-normal">(Pilih kategori agar dropdown part terfilter rapi)</small>
                                </label>
                                <div class="row px-1">
                                    @php $allowed = (array) ($opConfig->allowed_categories ?? []); @endphp
                                    @foreach($categories as $cat)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="form-check cat-checkbox-item">
                                                <input type="checkbox" class="form-check-input cfg-cat-checkbox"
                                                       id="cat_{{ $cat->id }}" name="allowed_categories[]" value="{{ $cat->id }}"
                                                       {{ in_array($cat->id, $allowed) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="cat_{{ $cat->id }}" title="{{ $cat->name }}">
                                                    {{ $cat->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-sm btn-primary shadow-sm" id="btnSaveOperatorConfig">
                                    <i class="fas fa-save mr-1"></i> Simpan Posisi Operator
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Daftar Meja per Plant -->
                    <div class="tab-pane fade" id="tab-meja" role="tabpanel">
                        <form id="formPlantTables">
                            <input type="hidden" name="plant_code" value="{{ $plantCode }}">
                            <p class="text-muted small mb-2">
                                Tambah atau sesuaikan nama meja inspeksi untuk Plant <strong>{{ strtoupper($plantCode) }}</strong>:
                            </p>
                            <div id="tableListContainer" class="mb-2">
                                @foreach($plantTables as $tbl)
                                    <div class="input-group input-group-sm mb-1 table-item-row">
                                        <input type="hidden" name="old_tables[]" value="{{ $tbl }}">
                                        <input type="text" name="tables[]" class="form-control form-control-sm border no-autoupper" value="{{ $tbl }}" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-danger btn-remove-table" title="Hapus Meja">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-xs btn-outline-primary" id="btnAddTableItem">
                                    <i class="fas fa-plus mr-1"></i> Tambah Meja
                                </button>
                                <button type="submit" class="btn btn-sm btn-success shadow-sm" id="btnSavePlantTables">
                                    <i class="fas fa-save mr-1"></i> Simpan Daftar Meja
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- MODAL 4: KELOLA MASTER ITEM PARAMETER STANDAR (KHUSUS ADMIN) --}}
@if(auth()->user()->role === 'admin')
<div class="modal fade modal-dandory" id="modalMasterItem" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 px-3 bg-light border-bottom">
                <h6 class="modal-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-cogs mr-1 text-primary"></i> Kelola Parameter Standar ISO Dandory Quality
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" style="font-size: 0.8rem;">
                <div class="row">
                    <!-- Form Tambah / Edit Master Item -->
                    <div class="col-md-4 border-right pr-3">
                        <form id="formMasterItem">
                            <input type="hidden" name="item_id" id="masterItemId">
                            <h6 class="font-weight-bold text-primary mb-2" id="formMasterItemTitle">Tambah Parameter</h6>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-gray-700">Fase Dandory</label>
                                <select name="phase" id="masterItemPhase" class="form-control form-control-sm border" required>
                                    <option value="awal">Awal Dandory</option>
                                    <option value="akhir">Akhir Dandory</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-gray-700">Nomor Urut Kolom</label>
                                <input type="number" name="order_no" id="masterItemOrder" class="form-control form-control-sm border" min="1" max="50" required>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-gray-700">Nama Item Check</label>
                                <input type="text" name="item_check" id="masterItemCheck" class="form-control form-control-sm border" placeholder="Contoh: IK, Limit Sample, Sisa Part..." required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-gray-700">Standard Pemeriksaan</label>
                                <textarea name="standard" id="masterItemStandard" class="form-control form-control-sm border" rows="3" placeholder="Penjelasan standar pemeriksaan..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-light border d-none" id="btnCancelEditMaster">Batal</button>
                                <button type="submit" class="btn btn-sm btn-primary shadow-sm" id="btnSaveMasterItem">
                                    <i class="fas fa-save mr-1"></i> Simpan Parameter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tabel Daftar Master Item -->
                    <div class="col-md-8 pl-3">
                        <div class="table-responsive" style="max-height: 480px;">
                            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.72rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 45px;">Fase</th>
                                        <th style="width: 30px;" class="text-center">No</th>
                                        <th style="width: 120px;">Item Check</th>
                                        <th>Standard</th>
                                        <th style="width: 70px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($awalItems as $it)
                                        <tr>
                                            <td class="text-primary font-weight-bold">Awal</td>
                                            <td class="text-center font-weight-bold">{{ $it->order_no }}</td>
                                            <td class="font-weight-bold text-dark">{{ $it->item_check }}</td>
                                            <td>{{ $it->standard }}</td>
                                            <td class="text-center p-1 text-nowrap">
                                                <button type="button" class="btn btn-xs btn-outline-primary btn-edit-master mr-1"
                                                        data-id="{{ $it->id }}" data-phase="{{ $it->phase }}" data-order="{{ $it->order_no }}"
                                                        data-check="{{ $it->item_check }}" data-standard="{{ $it->standard }}" title="Edit">
                                                    <i class="fas fa-edit fa-xs"></i>
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-danger btn-delete-master"
                                                        data-id="{{ $it->id }}" data-check="{{ $it->item_check }}" title="Hapus">
                                                    <i class="fas fa-trash-alt fa-xs"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @foreach($akhirItems as $it)
                                        <tr>
                                            <td class="text-success font-weight-bold">Akhir</td>
                                            <td class="text-center font-weight-bold">{{ $it->order_no }}</td>
                                            <td class="font-weight-bold text-dark">{{ $it->item_check }}</td>
                                            <td>{{ $it->standard }}</td>
                                            <td class="text-center p-1 text-nowrap">
                                                <button type="button" class="btn btn-xs btn-outline-primary btn-edit-master mr-1"
                                                        data-id="{{ $it->id }}" data-phase="{{ $it->phase }}" data-order="{{ $it->order_no }}"
                                                        data-check="{{ $it->item_check }}" data-standard="{{ $it->standard }}" title="Edit">
                                                    <i class="fas fa-edit fa-xs"></i>
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-danger btn-delete-master"
                                                        data-id="{{ $it->id }}" data-check="{{ $it->item_check }}" title="Hapus">
                                                    <i class="fas fa-trash-alt fa-xs"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- CONFIG DATA CONTAINER UNTUK EXTERNAL JS --}}
<div id="dandory-config" class="d-none"
     data-plant="{{ $plantCode }}"
     data-date="{{ $date ?: date('Y-m-d') }}"
     data-start-date="{{ $startDate }}"
     data-end-date="{{ $endDate }}"
     data-shift="{{ $shiftFilter }}"
     data-month="{{ $month }}"
     data-year="{{ $year }}"
     data-user-role="{{ auth()->user()->role }}"
     data-operator-id="{{ $selectedOperatorId }}"
     data-is-machine="{{ !empty($isMachineBased) ? '1' : '0' }}"
     data-location-label="{{ $locationLabel ?? 'Meja' }}"
     data-awal-store-url="{{ route('checksheet.dandory.awal.store') }}"
     data-akhir-base-url="{{ url('/checksheet/dandory') }}"
     data-part-preset-url="{{ route('checksheet.dandory.part_preset') }}"
     data-bulk-verify-url="{{ route('checksheet.dandory.bulk_verify') }}"
     data-bulk-destroy-url="{{ route('checksheet.dandory.bulk_destroy') }}"
     data-op-cfg-save-url="{{ route('checksheet.dandory.operator_config.save') }}"
     data-tables-save-url="{{ route('checksheet.dandory.tables.save') }}"
     data-master-item-url="{{ route('checksheet.dandory.master_item.store') }}"
     data-csrf-token="{{ csrf_token() }}"
     data-op-configs='@json($allOpConfigs ?? [])'
     data-bagian-categories='@json($latestBagianCategories ?? [])'
></div>

@endsection

@push('scripts')
<script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
<script src="{{ asset('js/checksheet/dandory-quality.js') }}?v={{ time() }}"></script>
@endpush

