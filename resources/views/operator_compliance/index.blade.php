@extends('layouts.admin')

@section('title', 'Checksheet Kepatuhan Operator')

@section('content')
    @php
        $plantCode = strtolower($plantCode ?: 'karawang');
        $isJkt = in_array($plantCode, ['jakarta', 'jkt']);
        $docHeader = \App\Models\GeneralSetting::getDocHeader('kepatuhan_operator', $plantCode, [
            'no_dokumen' => $isJkt ? 'QC-JKT-F-051' : 'PI-KRW-F-051',
            'tgl_terbit' => '31/03/2022',
            'revisi'     => '08/09/2023',
            'halaman'    => '1 / 1'
        ]);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    @endphp

    <div class="container-fluid">
        <!-- Unified Single Card Container -->
        <div class="card shadow mb-2">
            <div class="card-body p-2">

                <!-- 1. Header Dokumen ISO dengan Signatures persis In-Process -->
                <div class="mb-2">
                    <table style="width:100%; border-collapse:collapse; border: 1px solid #dee2e6;">
                        <tr>
                            <td style="width:75px; border:1px solid #dee2e6; padding:5px; text-align:center; vertical-align:middle;">
                                <img src="{{ asset('master item/ipp.jpg') }}" alt="IPP Logo" style="max-width:58px; max-height:44px; object-fit:contain;">
                            </td>
                            <td style="border:1px solid #dee2e6; border-left:none; padding:5px 8px; text-align:center; vertical-align:middle;">
                                <h1 class="mb-0 font-weight-bold text-uppercase text-gray-800" style="font-size:0.85rem; letter-spacing:0.3px;">
                                    CHECKSHEET KEPATUHAN OPERATOR QUALITY
                                </h1>
                            </td>
                            <td style="width:1px; border:1px solid #dee2e6; padding:0 !important; vertical-align:top; white-space:nowrap;">

                                {{-- Wrapper baris — menyamakan tinggi kedua tabel anak --}}
                                <table style="border-collapse:collapse; width:100%; height:100%; border:none; margin:0;">
                                    <tr style="height:100%;">

                                        {{-- ===== [3A] Tabel No. Dokumen ===== --}}
                                        <td style="border:none; padding:4px 6px 4px 4px; vertical-align:top; height:100%; white-space:nowrap;">
                                            <table style="border-collapse:collapse; border:1px solid #dee2e6; font-size:0.65rem; background:#fff; height:100%; width:100%;">
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">No. Dokumen</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:700; color:#212529; white-space:nowrap;">
                                                        {{ $docHeader['no_dokumen'] }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Tgl. Terbit</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['tgl_terbit'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Revisi / Tgl</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['revisi'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#495057; white-space:nowrap;">Halaman</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 4px; text-align:center; color:#495057;">:</td>
                                                    <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; color:#212529; white-space:nowrap;">{{ $docHeader['halaman'] }}</td>
                                                </tr>
                                            </table>
                                        </td>

                                        {{-- ===== [3B] Tabel Signatures ===== --}}
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
                                                    {{-- BARIS 1: Gambar tanda tangan --}}
                                                    <tr>
                                                        <td rowspan="3" style="border:1px solid #dee2e6; padding:2px; vertical-align:middle; text-align:center; width:28px;">
                                                            <div style="writing-mode:vertical-rl; transform:rotate(180deg); -webkit-transform:rotate(180deg); white-space:nowrap; font-size:0.58rem; font-weight:400; margin:0 auto; color:#6c757d;">
                                                                {{ date('d-M-y') }}
                                                            </div>
                                                        </td>

                                                        {{-- Tanda tangan Dibuat --}}
                                                        <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                            @if(in_array(strtolower($plantCode ?? 'karawang'), ['jakarta', 'jkt']))
                                                                <img src="{{ asset('signatures/suli.png') }}" alt="Masuli"
                                                                     style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                            @else
                                                                <img src="{{ asset('signatures/arif.png') }}" alt="Arief H"
                                                                     style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                            @endif
                                                        </td>

                                                        {{-- Tanda tangan Diperiksa --}}
                                                        <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                            <img src="{{ asset('signatures/iwan.png') }}" alt="Iwan S"
                                                                 style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        </td>

                                                        {{-- Tanda tangan Diketahui --}}
                                                        <td style="border:1px solid #dee2e6; padding:4px; vertical-align:middle; height:58px; background:#fff; text-align:center;">
                                                            <img src="{{ asset('signatures/desti.png') }}" alt="Desti K"
                                                                 style="max-height:68px; max-width:130px; object-fit:contain; mix-blend-mode:multiply; transform:scale(1.35); transform-origin:center;">
                                                        </td>
                                                    </tr>

                                                    {{-- BARIS 2: Nama --}}
                                                    <tr>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">{{ in_array(strtolower($plantCode ?? 'karawang'), ['jakarta', 'jkt']) ? 'Masuli' : 'Arief H' }}</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Iwan S</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-weight:600; font-size:0.63rem; color:#212529; white-space:nowrap;">Desti K</td>
                                                    </tr>

                                                    {{-- BARIS 3: Jabatan --}}
                                                    <tr>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Spv. QC</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Asst. Mgr Quality</td>
                                                        <td style="border:1px solid #dee2e6; padding:2px 6px; font-size:0.63rem; color:#495057; white-space:nowrap;">Mgr. Quality</td>
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

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <style>
                    /* Table Grid & Sticky Columns Styling (Tanpa Scrollbar Vertikal) */
                    #content-wrapper .op-compliance-grid-wrapper {
                        overflow-x: auto !important;
                        overflow-y: visible !important;
                        max-height: none !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 8px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                        background-color: #fff !important;
                    }

                    body #content-wrapper .op-compliance-grid {
                        table-layout: fixed !important;
                        border-collapse: separate !important;
                        border-spacing: 0 !important;
                        width: 2200px !important;
                        min-width: 2200px !important;
                        background-color: #fff !important;
                        border: none !important;
                        margin-bottom: 0 !important;
                    }

                    body #content-wrapper .op-compliance-grid thead th {
                        background-color: #f8fafc !important;
                        color: #475569 !important;
                        font-weight: 700 !important;
                        text-transform: uppercase !important;
                        font-size: 0.68rem !important;
                        padding: 8px 4px !important;
                        border: 1px solid #e2e8f0 !important;
                        vertical-align: middle !important;
                        text-align: center !important;
                        position: sticky !important;
                        top: 0 !important;
                        z-index: 100 !important;
                    }

                    /* Sunday Column Red Highlight */
                    body #content-wrapper .op-compliance-grid thead th.day-sunday,
                    body #content-wrapper .op-compliance-grid tbody td.day-sunday,
                    body #content-wrapper .op-compliance-grid tfoot td.day-sunday {
                        color: #e11d48 !important;
                        background-color: #fff1f2 !important;
                    }

                    body #content-wrapper .op-compliance-grid thead th.day-sunday {
                        background-color: #ffe4e6 !important;
                    }

                    /* Sticky Columns Positioning */
                    body #content-wrapper .op-compliance-grid .col-no { width: 35px !important; left: 0 !important; position: sticky !important; z-index: 10 !important; }
                    body #content-wrapper .op-compliance-grid .col-prinsip { width: 55px !important; left: 35px !important; position: sticky !important; z-index: 10 !important; text-align: center !important; }
                    body #content-wrapper .op-compliance-grid .col-item { width: 170px !important; left: 90px !important; position: sticky !important; z-index: 10 !important; }
                    body #content-wrapper .op-compliance-grid .col-standard { width: 325px !important; left: 260px !important; position: sticky !important; z-index: 10 !important; border-right: 2px solid #cbd5e1 !important; }

                    /* PRINSIP DASAR Vertical Styling */
                    .prinsip-vertical-wrapper {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100%;
                        min-height: 40px;
                        width: 100%;
                        margin: 0 auto;
                    }

                    .prinsip-vertical-text {
                        writing-mode: vertical-rl;
                        transform: rotate(180deg);
                        -webkit-transform: rotate(180deg);
                        white-space: nowrap;
                        text-align: center;
                        font-weight: 700;
                        font-size: 0.70rem;
                        color: #1e293b;
                        letter-spacing: 0.5px;
                        margin: 0 auto;
                        text-transform: uppercase;
                    }

                    body #content-wrapper .op-compliance-grid thead th.col-no,
                    body #content-wrapper .op-compliance-grid thead th.col-prinsip,
                    body #content-wrapper .op-compliance-grid thead th.col-item,
                    body #content-wrapper .op-compliance-grid thead th.col-standard {
                        z-index: 110 !important;
                        background-color: #f1f5f9 !important;
                    }

                    body #content-wrapper .op-compliance-grid tbody td.col-no,
                    body #content-wrapper .op-compliance-grid tbody td.col-prinsip,
                    body #content-wrapper .op-compliance-grid tbody td.col-item,
                    body #content-wrapper .op-compliance-grid tbody td.col-standard {
                        background-color: #fff !important;
                        z-index: 10 !important;
                        color: #334155 !important;
                        font-size: 0.68rem !important;
                        padding: 6px 8px !important;
                        border-bottom: 1px solid #f1f5f9 !important;
                    }

                    /* Day Cell Matrix Toggle (Bigger & Bolder Symbols ✓, ✕, -) */
                    .day-cell {
                        width: 42px !important;
                        text-align: center !important;
                        vertical-align: middle !important;
                        font-weight: 900 !important;
                        font-size: 1.35rem !important;
                        line-height: 1 !important;
                        cursor: pointer;
                        user-select: none;
                        transition: background-color 0.15s ease;
                        border: 1px solid #cbd5e1 !important;
                    }

                    .day-cell:hover {
                        background-color: #cbd5e1 !important;
                    }

                    .day-cell.cell-ok {
                        background-color: #d1fae5 !important;
                        color: #047857 !important;
                    }

                    .day-cell.cell-ng {
                        background-color: #fee2e2 !important;
                        color: #b91c1c !important;
                    }

                    .day-cell.cell-na {
                        background-color: #f1f5f9 !important;
                        color: #334155 !important;
                    }

                    .score-cell {
                        width: 90px !important;
                        min-width: 90px !important;
                        text-align: center !important;
                        vertical-align: middle !important;
                        font-weight: 700;
                        font-size: 0.72rem;
                        background-color: #f8fafc;
                        white-space: nowrap !important;
                    }

                    /* Schedule Style for Modal Tables */
                    .table-schedule-style {
                        border-collapse: collapse !important;
                        border: 1px solid #e2e8f0 !important;
                    }
                    .table-schedule-style thead th {
                        background-color: #f8fafc !important;
                        color: #475569 !important;
                        font-weight: 700 !important;
                        font-size: 0.72rem !important;
                        text-transform: uppercase !important;
                        border: 1px solid #e2e8f0 !important;
                        padding: 8px 6px !important;
                    }
                    .table-schedule-style tbody td {
                        border: 1px solid #e2e8f0 !important;
                        font-size: 0.78rem !important;
                        padding: 8px 10px !important;
                    }

                    /* Custom Filter Wrapper & Select Styling matching Operator / Inspector */
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
                    .custom-filter-wrapper .ips-input:focus, .filter-select-custom:focus { border-color: #4a6cf7 !important; box-shadow: 0 0 0 2px rgba(74,108,247,.18) !important; outline: none !important; }
                    .custom-filter-wrapper .ips-clear { right: 8px !important; font-size: 18px !important; font-weight: 700 !important; color: #64748b !important; line-height: 1 !important; }
                    .custom-filter-wrapper .ips-clear:hover { color: #ef4444 !important; }
                    .custom-filter-wrapper { position: relative; top: 0px; }

                    /* Shake animation for modal when user clicks backdrop */
                    @keyframes modalShakeAnim {
                        0%, 100% { transform: translateX(0); }
                        20%, 60% { transform: translateX(-10px); }
                        40%, 80% { transform: translateX(10px); }
                    }

                    .modal-shake {
                        animation: modalShakeAnim 0.4s ease-in-out !important;
                    }

                    /* Pulsing highlight for X close button when user clicks backdrop */
                    @keyframes xPulseAnim {
                        0% { transform: scale(1); color: #6c757d; }
                        50% { transform: scale(1.6); color: #dc3545; text-shadow: 0 0 12px rgba(220, 53, 69, 0.8); }
                        100% { transform: scale(1); color: #6c757d; }
                    }

                    .close-btn-highlight {
                        animation: xPulseAnim 0.6s ease-in-out infinite !important;
                    }

                    /* Custom Styling for Form Input Item Masalah Abnormal matching user requests */
                    .form-control-custom-pill {
                        background-color: #f8fafc !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 12px !important;
                        height: 44px !important;
                        padding: 8px 16px !important;
                        font-size: 0.95rem !important;
                        color: #0f172a !important;
                        font-weight: 500 !important;
                    }
                    .form-control-custom-pill:focus {
                        background-color: #ffffff !important;
                        border-color: #475569 !important;
                        box-shadow: 0 0 0 2px rgba(71, 85, 105, 0.15) !important;
                        outline: none !important;
                    }

                    .form-control-custom-area {
                        background-color: #f8fafc !important;
                        border: 1px solid #cbd5e1 !important;
                        border-radius: 16px !important;
                        padding: 12px 16px !important;
                        font-size: 0.95rem !important;
                        color: #0f172a !important;
                        resize: none !important;
                        font-weight: 500 !important;
                    }
                    .form-control-custom-area:focus {
                        background-color: #ffffff !important;
                        border-color: #475569 !important;
                        box-shadow: 0 0 0 2px rgba(71, 85, 105, 0.15) !important;
                        outline: none !important;
                    }

                    .status-pill-btn {
                        background-color: #f1f5f9;
                        border: 1px solid #cbd5e1;
                        border-radius: 25px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        user-select: none;
                        height: 42px;
                        color: #1e293b;
                        width: 100%;
                    }
                    .status-pill-btn:hover {
                        background-color: #e2e8f0;
                    }

                    .status-check-indicator {
                        width: 24px;
                        height: 24px;
                        border-radius: 50%;
                        border: 2px solid #94a3b8;
                        background-color: #ffffff;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                        transition: all 0.2s ease;
                    }

                    .status-check-indicator .check-icon {
                        font-size: 0.75rem;
                        color: #ffffff;
                        display: none;
                    }

                    .status-radio-input:checked + .status-check-indicator {
                        background-color: #2563eb;
                        border-color: #2563eb;
                    }

                    .status-radio-input:checked + .status-check-indicator .check-icon {
                        display: block;
                    }

                    .status-radio-input:checked ~ .status-label-text {
                        color: #0f172a;
                    }

                    .status-label-text {
                        font-weight: 400 !important;
                    }

                    .btn-custom-simpan {
                        background-color: #475569 !important;
                        color: #ffffff !important;
                        border: none !important;
                        border-radius: 12px !important;
                        height: 42px !important;
                        padding: 0 32px !important;
                        font-size: 0.95rem !important;
                        font-weight: 600 !important;
                        transition: all 0.2s ease;
                    }
                    .btn-custom-simpan:hover {
                        background-color: #334155 !important;
                        color: #ffffff !important;
                        transform: translateY(-1px);
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
                    }
                </style>

                <div id="op-compliance-config" class="d-none"
                     data-checksheet-id="{{ $checksheet ? $checksheet->id : '' }}"
                     data-plant="{{ $plantCode }}"
                     data-month="{{ $month }}"
                     data-year="{{ $year }}"
                     data-toggle-url="{{ route('checksheet.operator_compliance.toggle') }}"
                     data-verify-url="{{ route('checksheet.operator_compliance.verify') }}"
                     data-problem-store-url="{{ route('checksheet.operator_compliance.problem.store') }}"
                     data-problem-base-url="{{ url('/checksheet/kepatuhan-operator/problem') }}"
                     data-master-item-store-url="{{ route('checksheet.operator_compliance.master_item.store') }}"
                     data-master-item-base-url="{{ url('/checksheet/kepatuhan-operator/master-item') }}"
                     data-schedule-store-url="{{ route('checksheet.operator_compliance.schedule.store') }}"
                     data-schedule-base-url="{{ url('/checksheet/kepatuhan-operator/schedule') }}"
                     data-month-name="{{ $monthNames[$month] }} {{ $year }}"
                     data-plan-days='@json($planDays ?? [])'
                ></div>

                <!-- 2. Bar Filter Data (Dropdown Operator, Bulan, & Tahun) -->
                <form action="{{ route('checksheet.operator_compliance.index') }}" method="GET"
                      class="d-flex flex-wrap align-items-end bg-white p-0 rounded mb-3"
                      style="gap: 14px;">

                    <input type="hidden" name="plant" value="{{ $plantCode }}">

                    <!-- Dropdown Pilihan Operator (User Inspector Aktif) -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700">Operator / Inspector</label>
                        <div style="width: 230px;" class="custom-filter-wrapper">
                            <select name="operator_id" id="filterOperator" class="form-control form-control-sm filter-select-custom" onchange="if(this.value) this.form.submit()">
                                @foreach($inspectors as $insp)
                                    <option value="{{ $insp->id }}" data-name="{{ $insp->name }}" {{ $selectedOperatorId == $insp->id ? 'selected' : '' }}>
                                        {{ $insp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Bulan -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700">Bulan</label>
                        <div style="width: 130px;">
                            <select name="month" class="form-control form-control-sm filter-select-custom" onchange="this.form.submit()">
                                @foreach($monthNames as $mNum => $mName)
                                    <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Pilihan Tahun -->
                    <div class="d-flex flex-column align-items-start">
                        <label class="mb-1 small font-weight-bold text-gray-700">Tahun</label>
                        <div style="width: 95px;">
                            <select name="year" class="form-control form-control-sm filter-select-custom" onchange="this.form.submit()">
                                @php $cy = date('Y'); @endphp
                                @for($y = $cy - 2; $y <= $cy + 2; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Tombol Aksi (Daftar Masalah, Kelola Master, & Cetak) -->
                    <div class="ml-auto d-flex flex-wrap align-items-center mt-auto" style="gap: 6px;">
                        <!-- Tombol Modal Daftar Item Masalah Abnormal -->
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm"
                                data-toggle="modal" data-target="#problemLogModal" id="btnProblemLogModal">
                            Daftar Item Masalah
                            <span class="badge badge-light ml-1" id="problemCountBadge" style="{{ count($problems) > 0 ? '' : 'display: none;' }}">{{ count($problems) }}</span>
                        </button>

                        <!-- Tombol Kelola Jadwal Operator (Semua Role Kecuali Inspector) -->
                        @if(auth()->user()->role !== 'inspector')
                            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm"
                                    data-toggle="modal" data-target="#kelolaOperatorModal">
                                <i class="fas fa-calendar-alt fa-sm mr-1"></i> Kelola Jadwal Operator
                            </button>
                        @endif

                        <!-- Tombol Pengaturan Master Item (Role SPV ke Atas) -->
                        @if(in_array(auth()->user()->role, ['admin', 'manager', 'manager_plating', 'asst_manager', 'asst_manager_plating', 'supervisor', 'supervisor_plating']))
                            <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 shadow-sm"
                                    data-toggle="modal" data-target="#masterItemModal">
                                <i class="fas fa-cog fa-sm mr-1"></i> Kelola Master Item
                            </button>
                        @endif

                        <a href="{{ route('checksheet.operator_compliance.schedule', ['plant' => $plantCode, 'month' => $month, 'year' => $year]) }}" class="btn btn-info btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height: 31px;">
                            <i class="fas fa-calendar-alt fa-sm mr-1"></i> Schedule Kepatuhan
                        </a>

                        <button type="button" id="btnCetakCompliance"
                                data-print-url="{{ route('checksheet.operator_compliance.print', ['plant' => $plantCode, 'operator_id' => $selectedOperatorId, 'month' => $month, 'year' => $year]) }}"
                                class="btn btn-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" style="height: 31px;">
                            <i class="fas fa-print fa-sm mr-1"></i> Cetak
                        </button>
                    </div>
                </form>

                <!-- Informasi Header Data Operator -->
                <div class="row bg-white p-2 rounded border mx-0 mb-3 shadow-xs" style="font-size: 0.8rem;">
                    <div class="col-md-4">
                        <span class="text-muted">Nama Operator:</span>
                        <strong class="text-dark ml-1">{{ $selectedOperator->name }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted">Bagian / Unit:</span>
                        <strong class="text-dark ml-1">{{ $checksheet->bagian ?: 'Quality Control' }}</strong>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <span class="text-muted">Periode Audit:</span>
                        <strong class="text-dark ml-1">{{ $monthNames[$month] }} {{ $year }}</strong>
                    </div>
                </div>

                <!-- 3. Main Matrix Table Grid -->
                <div class="op-compliance-grid-wrapper">
                    <table class="table table-bordered op-compliance-grid mb-0">
                        <thead>
                            <tr>
                                <th class="align-middle col-no text-center">NO</th>
                                <th class="align-middle col-prinsip text-center" style="padding: 4px 2px !important;">
                                    <div style="line-height: 1.15; font-weight: 700; font-size: 0.65rem;">
                                        PRINSIP<br>DASAR
                                    </div>
                                </th>
                                <th class="align-middle col-item text-center">ITEM CHECK</th>
                                <th class="align-middle col-standard text-center">STANDARD</th>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $isSunday = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday();
                                    @endphp
                                    <th class="align-middle text-center day-cell-head {{ $isSunday ? 'day-sunday' : '' }}" style="width:42px;">{{ $d }}</th>
                                @endfor
                                <th class="align-middle text-center score-cell" style="white-space: nowrap !important;">RATA-RATA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $itemIndex = 1; @endphp
                            @foreach($groupedItems as $prinsip => $itemList)
                                @php $firstInGroup = true; $groupCount = count($itemList); @endphp
                                @foreach($itemList as $item)
                                    <tr>
                                        <td class="col-no text-center font-weight-bold align-middle">{{ $itemIndex++ }}</td>
                                        @if($firstInGroup)
                                            <td class="col-prinsip align-middle text-center font-weight-bold bg-light" rowspan="{{ $groupCount }}" style="padding: 4px 2px !important;">
                                                <div class="prinsip-vertical-wrapper">
                                                    <div class="prinsip-vertical-text">
                                                        {{ $prinsip }}
                                                    </div>
                                                </div>
                                            </td>
                                            @php $firstInGroup = false; @endphp
                                        @endif
                                        <td class="col-item align-middle font-weight-semibold">{{ $item->item_check }}</td>
                                        <td class="col-standard align-middle">{{ $item->standard }}</td>

                                        <!-- Days Checkbox Cells -->
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            @php
                                                $st = $entriesMatrix[$item->id][$d] ?? null;
                                                $isSunday = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday();
                                                
                                                // Check if the day is in the Plan
                                                $isPlanDay = in_array($d, $planDays);
                                                
                                                $cellClass = $isSunday ? 'day-sunday' : '';
                                                $cellChar = '';
                                                if (!$isPlanDay && empty($st)) {
                                                    $cellClass .= ' bg-light text-muted'; 
                                                    $cellChar = ''; // Dikosongkan, jangan pakai 'x' biar tidak berantakan
                                                } else {
                                                    if ($st === 'OK') {
                                                        $cellClass .= ' cell-ok';
                                                        $cellChar = '✓';
                                                    } elseif ($st === 'NG') {
                                                        $cellClass .= ' cell-ng';
                                                        $cellChar = '✕';
                                                    } elseif ($st === 'NA') {
                                                        $cellClass .= ' cell-na';
                                                        $cellChar = '-';
                                                    }
                                                }
                                            @endphp
                                            <td class="day-cell {{ $cellClass }} {{ !$isPlanDay ? 'disabled-cell' : '' }}"
                                                data-checksheet-id="{{ $checksheet->id }}"
                                                data-item-id="{{ $item->id }}"
                                                data-day="{{ $d }}"
                                                data-status="{{ $st }}"
                                                data-is-plan="{{ $isPlanDay ? '1' : '0' }}"
                                                title="Hari Ke-{{ $d }}: {{ $item->item_check }} {{ !$isPlanDay ? '(Bukan Jadwal)' : '(Klik: OK -> NG -> NA -> Hapus)' }}">
                                                {{ $cellChar }}
                                            </td>
                                        @endfor

                                        <!-- Item Score Average -->
                                        @php
                                            $itemOkCount = 0;
                                            $itemTotalFilled = 0;
                                            for($d = 1; $d <= $daysInMonth; $d++) {
                                                $st = $entriesMatrix[$item->id][$d] ?? null;
                                                if($st === 'OK') { $itemOkCount++; $itemTotalFilled++; }
                                                elseif($st === 'NG') { $itemTotalFilled++; }
                                            }
                                            $itemPct = $itemTotalFilled > 0 ? round(($itemOkCount / $itemTotalFilled) * 100) : '-';
                                        @endphp
                                        <td class="score-cell align-middle text-center font-weight-bold text-dark" style="white-space: nowrap !important;">
                                            {{ $itemPct }}{{ is_numeric($itemPct) ? '%' : '' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>

                        <!-- Summary & Verification Rows (DI DALAM TABEL TFOOT) -->
                        <tfoot class="bg-light font-weight-bold" style="font-size:0.75rem;">
                            <!-- Summary Row 1: Jumlah Sesuai -->
                            <tr>
                                <td colspan="4" class="text-right py-2 pr-3 align-middle bg-light text-uppercase" style="white-space: nowrap !important;">
                                    <strong>Jumlah Sesuai (✓)</strong>
                                </td>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php $isSunday = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday(); @endphp
                                    <td class="text-center align-middle text-success font-weight-bold {{ $isSunday ? 'day-sunday' : '' }}" id="dailyOk_{{ $d }}">
                                        {{ $dailyScores[$d]['ok'] }}
                                    </td>
                                @endfor
                                <td class="score-cell text-center align-middle text-success font-weight-bold" id="totalOkMonth" style="white-space: nowrap !important;">
                                    {{ $totalOkMonth }}
                                </td>
                            </tr>

                            <!-- Summary Row 2: Persentase Kepatuhan (%) -->
                            <tr>
                                <td colspan="4" class="text-right py-2 pr-3 align-middle bg-light text-uppercase" style="white-space: nowrap !important;">
                                    <strong>Persentase Kepatuhan (%)</strong>
                                </td>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $pct = $dailyScores[$d]['pct'];
                                        $isSunday = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday();
                                    @endphp
                                    <td class="text-center align-middle font-weight-bold {{ $isSunday ? 'day-sunday' : '' }} {{ $pct !== null && $pct < 100 ? 'text-danger' : 'text-primary' }}" id="dailyPct_{{ $d }}">
                                        {{ $pct !== null ? $pct.'%' : '-' }}
                                    </td>
                                @endfor
                                <td class="score-cell text-center align-middle font-weight-bold text-primary" id="monthlyPctTotal" style="font-size:0.85rem; white-space: nowrap !important;">
                                    {{ $monthlyPct }}%
                                </td>
                            </tr>

                            <!-- Verification Rows (Role-Restricted Checkboxes) -->
                            @php
                                $userRole = auth()->check() ? auth()->user()->role : '';
                                $canApproveLeader = in_array($userRole, ['admin', 'kashift', 'kashift_qc', 'karu_qc', 'kashift_plating', 'karu_prod']);
                                $canApproveSpv    = in_array($userRole, ['admin', 'supervisor', 'supervisor_qc', 'supervisor_plating']);
                                $canApproveMgr    = in_array($userRole, ['admin', 'asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_qc', 'manager_plating']);
                            @endphp

                            <!-- Verification Row 1: DINILAI (KASHIFT / KARU) = PER HARI (Tanggal 1-31) -->
                            <tr>
                                <td colspan="4" class="text-right py-2 pr-3 align-middle bg-light text-uppercase" style="white-space: nowrap !important;">
                                    <strong>DINILAI (KASHIFT / KARU)</strong>
                                </td>
                                @php $leaderChecks = $checksheet->leader_checks ?? []; @endphp
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $isSunday = \Carbon\Carbon::createFromDate($year, $month, $d)->isSunday();
                                        $lCheck = $leaderChecks[$d] ?? null;
                                        $isChecked = !empty($lCheck['checked']);
                                        $uName = $lCheck['user_name'] ?? '';
                                        $uTime = $lCheck['time'] ?? '';
                                    @endphp
                                    <td class="text-center align-middle p-1 {{ $isSunday ? 'day-sunday' : '' }}" style="vertical-align:top !important; padding:4px 2px !important; overflow:hidden;" title="Verifikasi Leader/Kashift Tgl {{ $d }} {{ !empty($uName) ? 'by '.$uName.' ('.$uTime.')' : '' }}">
                                        @if($canApproveLeader)
                                            <input type="checkbox" class="verify-daily-checkbox cursor-pointer"
                                                   data-type="leader" data-day="{{ $d }}" {{ $isChecked ? 'checked' : '' }}>
                                        @else
                                            @if($isChecked)
                                                <div class="text-success font-weight-bold" style="font-size:0.75rem; line-height:1;">✓</div>
                                            @else
                                                <span class="text-muted" style="font-size:0.65rem;">-</span>
                                            @endif
                                        @endif
                                        <div id="leaderDetail_{{ $d }}" class="small text-muted text-center mt-1" style="font-size:0.50rem; line-height:1.1; overflow:hidden;">
                                            @if($isChecked && !empty($uName))
                                                @php
                                                    $formattedTime = $uTime;
                                                    if (preg_match('/^(\d{2}\/\d{2}\/)\d{2}(\d{2})\s+(.+)$/', $uTime, $m)) {
                                                        $formattedTime = $m[1] . $m[2] . '<br>' . $m[3];
                                                    } elseif (strpos($uTime, ' ') !== false) {
                                                        $formattedTime = str_replace(' ', '<br>', $uTime);
                                                    }
                                                @endphp
                                                <div class="text-success font-weight-bold" style="font-size:0.46rem; white-space:nowrap; line-height:1.2;">✓ Approved</div>
                                                <div class="text-dark font-weight-bold" style="font-size:0.48rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $uName }}">{{ $uName }}</div>
                                                <div class="text-muted" style="font-size:0.45rem; line-height:1.1; margin-top:1px;">{!! $formattedTime !!}</div>
                                            @endif
                                        </div>
                                    </td>
                                @endfor
                                <td class="score-cell text-center align-middle font-weight-bold text-dark" style="font-size:0.65rem; white-space: nowrap !important;">Harian</td>
                            </tr>

                            <!-- Verification Row 2: DIPERIKSA / DIKONTROL (SPV) = PER MINGGU (W1..W5) -->
                            <tr>
                                <td colspan="4" class="text-right py-2 pr-3 align-middle bg-light text-uppercase" style="white-space: nowrap !important;">
                                    <strong>DIPERIKSA / DIKONTROL (SPV)</strong>
                                </td>
                                @php
                                    $spvChecks = $checksheet->spv_checks ?? [];
                                    $weeks = [
                                        1 => ['start' => 1,  'end' => 7],
                                        2 => ['start' => 8,  'end' => 14],
                                        3 => ['start' => 15, 'end' => 21],
                                        4 => ['start' => 22, 'end' => 28],
                                        5 => ['start' => 29, 'end' => $daysInMonth],
                                    ];
                                @endphp
                                @foreach($weeks as $wNum => $wRange)
                                    @if($wRange['start'] <= $daysInMonth)
                                        @php
                                            $wEnd = min($wRange['end'], $daysInMonth);
                                            $wSpan = ($wEnd - $wRange['start']) + 1;
                                            $sCheck = $spvChecks[$wNum] ?? null;
                                            $isSpvChecked = !empty($sCheck['checked']);
                                            $spvUser = $sCheck['user_name'] ?? '';
                                            $spvTime = $sCheck['time'] ?? '';
                                        @endphp
                                        <td colspan="{{ $wSpan }}" class="text-center align-middle py-1 px-2 bg-white border-right" style="white-space: nowrap !important;">
                                            @if($canApproveSpv)
                                                <div class="custom-control custom-checkbox d-inline-block">
                                                    <input type="checkbox" class="custom-control-input verify-weekly-checkbox" id="verifySpv_W{{ $wNum }}"
                                                           data-type="spv" data-week="{{ $wNum }}" {{ $isSpvChecked ? 'checked' : '' }}>
                                                    <label class="custom-control-label cursor-pointer font-weight-bold small mb-0" for="verifySpv_W{{ $wNum }}" style="font-size:0.72rem;">
                                                        <span id="spvText_W{{ $wNum }}" class="{{ $isSpvChecked ? 'text-success' : 'text-muted' }}">
                                                            {{ $isSpvChecked ? '✓ Approved' : ('Minggu-' . $wNum) }}
                                                        </span>
                                                    </label>
                                                </div>
                                            @else
                                                <div class="font-weight-bold small mb-0" style="font-size:0.72rem;">
                                                    @if($isSpvChecked)
                                                        <span class="text-success font-weight-bold">✓ Approved</span>
                                                    @else
                                                        <span class="text-muted font-weight-bold">Minggu-{{ $wNum }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            <div id="spvDetail_W{{ $wNum }}" class="small text-muted mt-1" style="font-size:0.62rem; line-height:1.2; white-space: nowrap !important;">
                                                @if($isSpvChecked && !empty($spvUser))
                                                    <span class="text-success font-weight-bold">{{ $spvUser }}</span>
                                                    <span class="text-muted ml-1">({{ $spvTime }})</span>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                @endforeach
                                <td class="score-cell text-center align-middle font-weight-bold text-dark" style="font-size:0.65rem; white-space: nowrap !important;">Mingguan</td>
                            </tr>

                            <!-- Verification Row 3: DIKETAHUI (ASST. MNGR) = SATU BULAN SEKALI -->
                            <tr>
                                <td colspan="4" class="text-right py-2 pr-3 align-middle bg-light text-uppercase" style="white-space: nowrap !important;">
                                    <strong>DIKETAHUI (ASST. MNGR)</strong>
                                </td>
                                <td colspan="{{ $daysInMonth }}" class="text-center align-middle py-2 bg-white" style="white-space: nowrap !important;">
                                    @if($canApproveMgr)
                                        <div class="custom-control custom-checkbox d-inline-block">
                                            <input type="checkbox" class="custom-control-input verify-monthly-checkbox" id="verifyMgr"
                                                   data-type="mgr" {{ $checksheet->mgr_checked ? 'checked' : '' }}>
                                            <label class="custom-control-label cursor-pointer font-weight-bold mb-0" for="verifyMgr">
                                                <span id="mgrStatusText" class="{{ $checksheet->mgr_checked ? 'text-success' : 'text-muted' }}" data-default-text="{{ $monthNames[$month] }} {{ $year }}">
                                                    {{ $checksheet->mgr_checked ? '✓ Approved' : ($monthNames[$month] . ' ' . $year) }}
                                                </span>
                                            </label>
                                        </div>
                                    @else
                                        <div class="font-weight-bold mb-0" style="font-size:0.8rem;">
                                            @if($checksheet->mgr_checked)
                                                <span class="text-success font-weight-bold">✓ Approved</span>
                                            @else
                                                <span class="text-muted font-weight-bold">{{ $monthNames[$month] }} {{ $year }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <div id="mgrDetailText" class="small text-muted mt-1" style="font-size:0.62rem; line-height:1.2; white-space: nowrap !important;">
                                        @if($checksheet->mgr_checked && $checksheet->manager)
                                            <span class="text-success font-weight-bold">{{ $checksheet->manager->name }}</span>
                                            <span class="text-muted ml-1">({{ $checksheet->mgr_checked_at ? $checksheet->mgr_checked_at->format('d/m/Y H:i') : '' }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="score-cell text-center align-middle font-weight-bold text-dark" style="font-size:0.65rem; white-space: nowrap !important;">Bulanan</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 4. Kotak Keterangan Cara Pengisian (Full Width) -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-left-primary shadow-xs">
                            <div class="card-body p-3">
                                <h6 class="font-weight-bold text-primary mb-2">
                                    <i class="fas fa-info-circle mr-1"></i> Cara Pengisian & Metode Input Data:
                                </h6>
                                <ol class="pl-3 mb-0 small text-gray-800" style="line-height: 1.6;">
                                    <li>Kategori input data terdiri dari tiga jenis: <strong>√ (OK)</strong> jika sesuai, <strong>X (NG)</strong> jika tidak sesuai, dan <strong>- (Tidak Dinilai / NA)</strong> jika item tidak dinilai/tidak relevan pada hari tersebut.</li>
                                    <li>Data dengan status <strong>- (Tidak Dinilai / NA)</strong> <strong>TIDAK dihitung</strong> dalam kalkulasi persentase kepatuhan.</li>
                                    <li>Hasil nilai kepatuhan diperoleh dari perhitungan jumlah <strong>√ (OK)</strong> dibagi total item yang dinilai (<strong>OK + NG</strong>).</li>
                                    <li>Jika terdapat ketidaksesuaian (tanda <strong>X</strong>), wajib dimasukkan ke dalam <strong>Daftar Item Masalah</strong>.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal 1: Daftar Item Masalah (Desain Schedule Style) -->
    <div class="modal fade" id="problemLogModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="problemLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-white" style="border-bottom: 2px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem;">
                    <h5 class="modal-title text-primary font-weight-bold" id="problemLogModalLabel">
                        Daftar Item Masalah (Kontrol Kepatuhan)
                    </h5>
                    <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light px-4 py-4">

                    <!-- Section 1: Form Input Item Masalah -->
                    <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;" id="formProblemTitle">
                        FORM INPUT ITEM MASALAH ABNORMAL
                    </div>

                    <form id="problemForm" class="bg-white p-4 mb-4 shadow-sm border" style="border-radius: 16px;">
                        @csrf
                        <input type="hidden" name="checksheet_id" value="{{ $checksheet->id }}">
                        <input type="hidden" name="problem_id" id="problem_id">
                        <input type="hidden" name="item_id" id="problem_item_id">

                        <!-- Row 1: Tanggal Temuan, PIC (Penanggung Jawab), Target Selesai -->
                        <div class="form-row mb-4">
                            <div class="form-group col-md-4 mb-3 mb-md-0">
                                <label class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">Tanggal Temuan <span class="text-danger">*</span></label>
                                <input type="date" name="problem_date" id="problem_date" class="form-control form-control-custom-pill" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group col-md-4 mb-3 mb-md-0">
                                <label class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">PIC (Penanggung Jawab) <span class="text-danger">*</span></label>
                                <input type="text" name="pic_name" id="pic_name" class="form-control form-control-custom-pill" placeholder="Nama PIC..." required>
                            </div>
                            <div class="form-group col-md-4 mb-0">
                                <label class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">Target Selesai</label>
                                <input type="date" name="target_date" id="target_date" class="form-control form-control-custom-pill">
                            </div>
                        </div>

                        <!-- Row 2: Kondisi Abnormal / Item Masalah, Tindakan Perbaikan / Action, Status Ceklis -->
                        <div class="form-row align-items-stretch">
                            <div class="form-group col-md-4 mb-3 mb-md-0 d-flex flex-column">
                                <label class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">Kondisi Abnormal / Item Masalah <span class="text-danger">*</span></label>
                                <textarea name="problem_description" id="problem_description" rows="5" class="form-control form-control-custom-area flex-grow-1" placeholder="Jelaskan ketidaksesuaian yang ditemukan..." required></textarea>
                            </div>
                            <div class="form-group col-md-4 mb-3 mb-md-0 d-flex flex-column">
                                <label class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">Tindakan Perbaikan / Action</label>
                                <textarea name="corrective_action" id="corrective_action" rows="5" class="form-control form-control-custom-area flex-grow-1" placeholder="Tindakan perbaikan yang dilakukan..."></textarea>
                            </div>
                            <div class="form-group col-md-4 mb-0 d-flex flex-column">
                                <label class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem; opacity: 0;">Status</label>
                                <!-- Status Ceklis Option Group -->
                                <div class="status-radio-group d-flex flex-column" style="gap: 12px;">
                                    <label class="status-pill-btn d-flex align-items-center mb-0 px-3">
                                        <input type="radio" name="status" value="Open" checked class="d-none status-radio-input">
                                        <span class="status-check-indicator mr-3"><i class="fas fa-check check-icon"></i></span>
                                        <span class="status-label-text flex-grow-1" style="font-size: 0.95rem;">Open</span>
                                    </label>

                                    <label class="status-pill-btn d-flex align-items-center mb-0 px-3">
                                        <input type="radio" name="status" value="Closed" class="d-none status-radio-input">
                                        <span class="status-check-indicator mr-3"><i class="fas fa-check check-icon"></i></span>
                                        <span class="status-label-text flex-grow-1" style="font-size: 0.95rem;">Close</span>
                                    </label>

                                    <label class="status-pill-btn d-flex align-items-center mb-0 px-3">
                                        <input type="radio" name="status" value="In Progress" class="d-none status-radio-input">
                                        <span class="status-check-indicator mr-3"><i class="fas fa-check check-icon"></i></span>
                                        <span class="status-label-text flex-grow-1" style="font-size: 0.95rem;">In Progres</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Tombol Simpan Terpisah (Tidak Sejajar / Offset Di Bawah Kanan) -->
                        <div class="d-flex justify-content-end mt-4 pt-2">
                            <button type="submit" class="btn btn-custom-simpan shadow-sm">
                                <i class="fas fa-save mr-2"></i>Simpan
                            </button>
                        </div>
                    </form>

                    <!-- Section 2: Tabel Daftar Masalah (Schedule Grid Style) -->
                    <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                        DAFTAR RIWAYAT ITEM MASALAH TERDAFTAR
                    </div>

                    <div class="bg-white p-3 shadow-sm border" style="border-radius: 8px;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0 table-schedule-style">
                                <thead>
                                    <tr class="text-center">
                                        <th style="width: 45px;">NO</th>
                                        <th style="width: 100px;">TGL</th>
                                        <th>ITEM MASALAH / KONDISI ABNORMAL</th>
                                        <th>PERBAIKAN / ACTION</th>
                                        <th style="width: 130px;">PIC</th>
                                        <th style="width: 110px;">TARGET</th>
                                        <th style="width: 95px;">STATUS</th>
                                        <th style="width: 80px;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="problemTableBody">
                                    @forelse($problems as $pIdx => $prob)
                                        <tr>
                                            <td class="text-center align-middle font-weight-bold">{{ $pIdx + 1 }}</td>
                                            <td class="text-center align-middle">{{ $prob->problem_date ? $prob->problem_date->format('d/m/Y') : '-' }}</td>
                                            <td class="align-middle">{{ $prob->problem_description }}</td>
                                            <td class="align-middle">{{ $prob->corrective_action ?: '-' }}</td>
                                            <td class="align-middle font-weight-bold">{{ $prob->pic_name ?: '-' }}</td>
                                            <td class="text-center align-middle">{{ $prob->target_date ? $prob->target_date->format('d/m/Y') : '-' }}</td>
                                            <td class="text-center align-middle">
                                                @if($prob->status === 'Closed')
                                                    <span class="badge badge-success px-2 py-1">Closed</span>
                                                @elseif($prob->status === 'In Progress')
                                                    <span class="badge badge-warning px-2 py-1 text-dark">In Progress</span>
                                                @else
                                                    <span class="badge badge-danger px-2 py-1">Open</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <button class="btn btn-sm btn-info py-0 px-2 rounded-circle btn-edit-prob"
                                                        data-id="{{ $prob->id }}"
                                                        data-date="{{ $prob->problem_date ? $prob->problem_date->format('Y-m-d') : '' }}"
                                                        data-item-id="{{ $prob->item_id }}"
                                                        data-pic="{{ $prob->pic_name }}"
                                                        data-target="{{ $prob->target_date ? $prob->target_date->format('Y-m-d') : '' }}"
                                                        data-desc="{{ $prob->problem_description }}"
                                                        data-action="{{ $prob->corrective_action }}"
                                                        data-status="{{ $prob->status }}"
                                                        title="Edit Item Masalah">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger py-0 px-2 rounded-circle btn-del-prob" data-id="{{ $prob->id }}" title="Hapus Item Masalah">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada daftar item masalah yang tercatat.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Kelola Master Item Audit (Role SPV ke Atas - Schedule Style & Tanpa Scrollbar) -->
    @if(in_array(auth()->user()->role, ['admin', 'manager', 'manager_plating', 'asst_manager', 'asst_manager_plating', 'supervisor', 'supervisor_plating']))
        <div class="modal fade" id="masterItemModal" tabindex="-1" role="dialog" aria-labelledby="masterItemModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-white" style="border-bottom: 2px solid #e2e8f0; border-radius: 12px 12px 0 0; padding: 1rem 1.5rem;">
                        <h5 class="modal-title text-primary font-weight-bold" id="masterItemModalLabel">
                            <i class="fas fa-cogs mr-2"></i>Kelola Master Item Audit
                        </h5>
                        <button type="button" class="close text-secondary" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body bg-light px-4 py-4">
                        
                        <!-- Form Section -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;" id="masterItemFormTitle">
                            <i class="fas fa-plus-circle mr-1"></i> TAMBAH ITEM AUDIT BARU
                        </div>

                        <form id="masterItemForm" class="bg-white p-3 mb-4 shadow-sm border" style="border-radius: 8px;">
                            @csrf
                            <input type="hidden" name="master_item_id" id="master_item_id">

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label class="small font-weight-bold text-gray-700">Prinsip Dasar <span class="text-danger">*</span></label>
                                    <input type="text" name="prinsip_dasar" id="mi_prinsip_dasar" class="form-control form-control-sm border-0 shadow-sm" placeholder="Contoh: Mematuhi Standar Kerja..." required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="small font-weight-bold text-gray-700">Item Check <span class="text-danger">*</span></label>
                                    <input type="text" name="item_check" id="mi_item_check" class="form-control form-control-sm border-0 shadow-sm" placeholder="Contoh: WI/IK, APD..." required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="small font-weight-bold text-gray-700">Urutan (Order No)</label>
                                    <input type="number" name="order_no" id="mi_order_no" class="form-control form-control-sm border-0 shadow-sm" placeholder="1, 2, 3...">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold text-gray-700">Standard / Kriteria Audit <span class="text-danger">*</span></label>
                                <textarea name="standard" id="mi_standard" rows="2" class="form-control form-control-sm border-0 shadow-sm" placeholder="Penjelasan standar kriteria audit..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark btn-sm font-weight-bold px-4 shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Item Audit
                            </button>
                        </form>

                        <!-- Master Item List Table (Schedule Style & Tanpa Scrollbar) -->
                        <div class="font-weight-bold text-primary mb-3 pb-2" style="border-bottom: 2px solid #e2e8f0; font-size: 0.9rem;">
                            <i class="fas fa-table mr-1"></i> DAFTAR MASTER ITEM AUDIT TERDAFTAR (SELURUH DATA)
                        </div>

                        <div class="bg-white p-3 shadow-sm border" style="border-radius: 8px;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0 table-schedule-style">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width:40px;">NO</th>
                                            <th>PRINSIP DASAR</th>
                                            <th>ITEM CHECK</th>
                                            <th>STANDARD</th>
                                            <th style="width:80px;">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $mIdx => $mi)
                                            <tr>
                                                <td class="text-center font-weight-bold align-middle">{{ $mi->order_no }}</td>
                                                <td class="align-middle">{{ $mi->prinsip_dasar }}</td>
                                                <td class="font-weight-bold align-middle">{{ $mi->item_check }}</td>
                                                <td class="align-middle">{{ $mi->standard }}</td>
                                                <td class="text-center align-middle">
                                                    <button class="btn btn-sm btn-info py-0 px-2 rounded-circle btn-edit-mi"
                                                            data-id="{{ $mi->id }}"
                                                            data-prinsip="{{ $mi->prinsip_dasar }}"
                                                            data-item="{{ $mi->item_check }}"
                                                            data-standard="{{ $mi->standard }}"
                                                            data-order="{{ $mi->order_no }}"
                                                            title="Edit Item Audit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger py-0 px-2 rounded-circle btn-del-mi" data-id="{{ $mi->id }}" title="Hapus Item Audit">
                                                        <i class="fas fa-trash"></i>
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
    @endif

    @if(auth()->user()->role !== 'inspector')
    <!-- Modal Kelola Jadwal Operator (Semua role kecuali Inspector) -->
    <div class="modal fade" id="kelolaOperatorModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 0;">
                <div class="modal-header bg-white py-3 px-4 d-flex align-items-center justify-between" style="border-radius: 12px 12px 0 0; border-bottom: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <h5 class="modal-title font-weight-bold text-dark m-0" style="font-size: 1.1rem;">
                            Kelola Jadwal Operator
                        </h5>
                        @if(auth()->user()->role === 'admin')
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm btnSyncHistoricalSchedule" data-url="{{ route('checksheet.operator_compliance.schedule.sync_historical') }}" style="height: 28px; font-size: 0.72rem;" title="Sinkronkan entri riwayat checksheet yang ada ke Plan Schedule">
                            <i class="fas fa-sync-alt fa-sm mr-1"></i> Sinkronkan Riwayat
                        </button>
                        @endif
                    </div>
                    <button type="button" class="close btn-close-modal ml-auto" data-dismiss="modal" aria-label="Close" style="color: #6c757d; opacity: 1; outline: none; transition: all 0.2s;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light px-4 py-4" style="max-height: 65vh; overflow-y: auto;">
                    <div class="row">
                        <!-- Form Tambah Jadwal -->
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 mb-3" style="border-radius: 8px;">
                                <div class="card-header bg-white py-2" style="border-radius: 8px 8px 0 0;">
                                    <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.9rem;" id="formScheduleTitle">Form Jadwal</h6>
                                </div>
                                <div class="card-body p-3">
                                    <form id="formOperatorSchedule" novalidate>
                                        <input type="hidden" id="schEditId" value="">
                                        <div class="form-group mb-2">
                                            <label for="schOperatorId" class="small font-weight-bold text-gray-700">Operator / Inspector <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm border-0 shadow-sm" id="schOperatorId">
                                                <option value="">-- Pilih Operator / Inspector --</option>
                                                @foreach(($plantOperators ?? $inspectors ?? []) as $op)
                                                    <option value="{{ $op->id }}" data-bagian="{{ $op->bagian ?? '' }}">{{ $op->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="schBagian" class="small font-weight-bold text-gray-700">Bagian</label>
                                            <select class="form-control form-control-sm border-0 shadow-sm" id="schBagian">
                                                <option value="">-- Pilih Bagian --</option>
                                                <option value="Inspector Outgoing Plating">Inspector Outgoing Plating</option>
                                                <option value="Inspector Outgoing Painting">Inspector Outgoing Painting</option>
                                                <option value="Inspector Outgoing Export">Inspector Outgoing Export</option>
                                                <option value="Inspector Outgoing Sub Assy">Inspector Outgoing Sub Assy</option>
                                                <option value="Inspector Inproses">Inspector Inproses</option>
                                                <option value="Inspector Incoming Sub-Part/Material/Chemical">Inspector Incoming Sub-Part/Material/Chemical</option>
                                                <option value="Inspector Incoming Plating">Inspector Incoming Plating</option>
                                                <option value="Inspector Outgoing Double Tape">Inspector Outgoing Double Tape</option>
                                                <option value="Performance Test">Performance Test</option>
                                                <option value="Claim Customer">Claim Customer</option>
                                                <option value="Final ISD">Final ISD</option>
                                                <option value="Digitalisasi Support">Digitalisasi Support</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label for="schShift" class="small font-weight-bold text-gray-700">Shift</label>
                                            <select class="form-control form-control-sm border-0 shadow-sm" id="schShift">
                                                <option value="Non Shift">Non Shift</option>
                                                <option value="Shift 1">Shift 1</option>
                                                <option value="Shift 2">Shift 2</option>
                                                <option value="Shift 3">Shift 3</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-gray-700 mb-1">Tanggal Plan <span class="text-danger">*</span></label>
                                            
                                            <div id="schDatesContainer" class="d-flex flex-column" style="gap: 8px;">
                                                <div class="input-group input-group-sm sch-date-row">
                                                    <input type="date" class="form-control form-control-sm border-0 shadow-sm sch-date-input" style="cursor: pointer; background-color: #fff;" onclick="try{this.showPicker()}catch(e){}">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary shadow-sm" id="btnAddDateRow" title="Tambah Tanggal Plan" style="width: 34px; padding: 0;">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="text-muted font-italic mt-1 d-block" id="schDatesHint" style="font-size: 0.65rem;">
                                                * Klik <strong>+</strong> jika dalam 1 bulan terdapat beberapa tanggal plan.
                                            </small>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm btn-block shadow-sm font-weight-bold" id="btnSaveSchedule" style="position: relative; z-index: 10; cursor: pointer;">
                                            <i class="fas fa-save mr-1"></i> Simpan Jadwal
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm btn-block border shadow-sm font-weight-bold mt-2 d-none" id="btnCancelEditSchedule">
                                            <i class="fas fa-times mr-1"></i> Batal Edit
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Tabel Daftar Jadwal -->
                        <div class="col-md-8">
                            <div class="card shadow-sm border-0" style="border-radius: 8px;">
                                <div class="card-header bg-white py-2" style="border-radius: 8px 8px 0 0;">
                                    <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.9rem;">Daftar Jadwal Inspector</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 400px;">
                                        <table class="table table-bordered table-schedule-style mb-0 w-100" id="tableSchedules">
                                            <thead style="position: sticky; top: 0; z-index: 10;">
                                                <tr>
                                                    <th class="text-nowrap" style="width: auto;">OPERATOR / INSPECTOR</th>
                                                    <th class="text-nowrap" style="width: auto;">BAGIAN</th>
                                                    <th class="text-nowrap text-center" style="width: 90px;">SHIFT</th>
                                                    <th class="text-nowrap text-center" style="width: 120px;">TANGGAL PLAN</th>
                                                    <th class="text-nowrap text-center" style="width: 80px;">AKSI</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($allSchedules as $sch)
                                                <tr class="sch-row" data-operator-id="{{ $sch->operator_id }}" style="display: none;">
                                                    <td class="align-middle font-weight-bold text-dark text-nowrap">{{ $sch->operator->name ?? '-' }}</td>
                                                    <td class="align-middle text-nowrap">{{ $sch->bagian }}</td>
                                                    <td class="align-middle text-nowrap text-center">{{ $sch->shift }}</td>
                                                    <td class="align-middle text-primary font-weight-bold text-nowrap text-center">{{ \Carbon\Carbon::parse($sch->schedule_date)->format('d M Y') }}</td>
                                                    <td class="align-middle text-center text-nowrap">
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-schedule py-0 px-2 mr-1" style="font-size: 0.7rem;" 
                                                                data-id="{{ $sch->id }}" 
                                                                data-operator-id="{{ $sch->operator_id }}" 
                                                                data-bagian="{{ $sch->bagian }}" 
                                                                data-shift="{{ $sch->shift }}" 
                                                                data-date="{{ \Carbon\Carbon::parse($sch->schedule_date)->format('Y-m-d') }}"
                                                                title="Edit Jadwal">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-schedule py-0 px-2" style="font-size: 0.7rem;" 
                                                                data-id="{{ $sch->id }}" 
                                                                data-operator-id="{{ $sch->operator_id }}" 
                                                                title="Hapus Jadwal">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                <tr class="sch-info-row">
                                                    <td colspan="5" class="text-center text-muted font-italic py-4">
                                                        <i class="fas fa-info-circle mr-1 text-primary"></i> Silakan pilih Operator / Inspector pada form di sebelah kiri untuk melihat daftar jadwal.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection

@push('scripts')
<script src="{{ asset('js/vendor/item-search.js') }}?v=1.4"></script>
<script src="{{ asset('js/checksheet/operator-compliance.js') }}?v={{ time() }}"></script>
@endpush
